<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAssignment;
use App\Models\LeadFollowup;
use App\Models\LeadStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StaffLeadAnalyticsService
{
    private const CLOSED_STATUSES = ['converted', 'won', 'lost', 'loss', 'junk'];

    private const WON_STATUSES = ['won', 'converted'];

    private const LOST_STATUSES = ['lost', 'loss'];

    private const ASSIGNED_LEAD_MODEL = 'lead';

    private const ASSIGNMENT_SOURCES = [
        // lead_model => [table, created_timestamp_column]
        'lead' => ['leads', 'created_at'],
        'digital_marketing' => ['digital_marketing_leads', 'created_at'],
        'webapp' => ['webapp_leads', 'created_at'],
        'meta' => ['meta_leads', 'created_time'],
        'google' => ['google_leads', 'lead_submit_time'],
    ];

    public function buildDashboard(int $staffId, array $filters = []): array
    {
        $range = $this->resolveDateRange($filters);
        $cacheKey = 'staff-analytics:' . $staffId . ':' . md5(json_encode($range));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($staffId, $range) {
            return [
                'kpis' => $this->kpis($staffId, $range),
                'charts' => $this->charts($staffId, $range),
                'overdue_followups' => $this->overdueFollowups($staffId, $range),
                'recent_activities' => $this->recentActivities($staffId, $range),
                'top_metrics' => $this->topMetrics($staffId, $range),
            ];
        });
    }

    public function leadChart(int $staffId, array $filters = []): array
    {
        return $this->charts($staffId, $this->resolveDateRange($filters))['monthly_lead_conversion'];
    }

    public function followupChart(int $staffId, array $filters = []): array
    {
        return $this->charts($staffId, $this->resolveDateRange($filters))['monthly_followup_activity'];
    }

    private function kpis(int $staffId, array $range): array
    {
        $assignedLeadsBase = $this->assignedAnyLeadQuery($staffId)
            ->whereBetween('assigned_at', [$range['from'], $range['to']]);

        // Assigned leads total should include all sources in lead-management (assigned_leads is the source of truth).
        $totalLeads = (clone $assignedLeadsBase)->count();

        $activeLeads = (clone $assignedLeadsBase)->whereNotIn('normalized_status', self::CLOSED_STATUSES)->count();
        $convertedLeads = (clone $assignedLeadsBase)->whereIn('normalized_status', self::WON_STATUSES)->count();
        $lostLeads = (clone $assignedLeadsBase)->whereIn('normalized_status', self::LOST_STATUSES)->count();

        $followupsBase = $this->followupsForAssignedLeadsQuery($staffId)
            ->whereBetween('followup_date', [$range['from'], $range['to']]);

        $totalFollowups = (clone $followupsBase)->count();
        $pendingFollowups = $this->assignedLeadQuery($staffId)
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '>', now())
            ->count();

        $overdueFollowups = $this->assignedLeadQuery($staffId)
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->whereNotIn('status', ['converted', 'won', 'lost'])
            ->count();

        $todayFollowups = $this->followupsForAssignedLeadsQuery($staffId)
            ->whereDate('followup_date', now()->toDateString())
            ->count();

        $meetingsScheduled = $this->followupsForAssignedLeadsQuery($staffId)
            ->whereIn('followup_type', ['meeting', 'demo', 'video_call', 'site_visit'])
            ->count();

        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;

        $avgResponseHours = (float) DB::table('leads as l')
            ->leftJoin('lead_followups as f', function ($join) use ($staffId) {
                $join->on('l.id', '=', 'f.lead_id')->where('f.staff_id', '=', $staffId);
            })
            ->whereIn('l.id', $this->assignedLeadIdsSubQuery($staffId))
            ->whereNotNull('f.followup_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, l.created_at, f.followup_date)) as avg_hours')
            ->value('avg_hours');

        $avgConversionDays = (float) $this->assignedLeadQuery($staffId)
            ->whereIn('status', ['converted', 'won'])
            ->whereNotNull('converted_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, converted_at)) as avg_days')
            ->value('avg_days');

        return [
            'total_leads_assigned' => $totalLeads,
            'active_leads' => $activeLeads,
            'converted_leads' => $convertedLeads,
            'lost_leads' => $lostLeads,
            'total_followups' => $totalFollowups,
            'pending_followups' => $pendingFollowups,
            'overdue_followups' => $overdueFollowups,
            'todays_followups' => $todayFollowups,
            'meetings_scheduled' => $meetingsScheduled,
            'conversion_rate' => $conversionRate,
            'avg_response_time_hours' => round($avgResponseHours, 2),
            'avg_conversion_time_days' => round($avgConversionDays, 2),
        ];
    }

    private function charts(int $staffId, array $range): array
    {
        $months = collect(range(11, 0))->map(fn($i) => now()->startOfMonth()->subMonths($i));
        $monthLabels = $months->map(fn($m) => $m->format('M Y'))->values();

        $assignedRows = $this->assignedAnyLeadQuery($staffId)
            ->whereBetween('assigned_at', [$months->first()->copy()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('DATE_FORMAT(assigned_at, "%Y-%m") ym, COUNT(*) total')
            ->groupBy('ym')
            ->get();
        $assignedByMonth = collect($assignedRows)->pluck('total', 'ym');

        $convertedRows = $this->assignedAnyLeadQuery($staffId)
            ->whereIn('normalized_status', self::WON_STATUSES)
            ->whereBetween('status_changed_at', [$months->first()->copy()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('DATE_FORMAT(status_changed_at, "%Y-%m") ym, COUNT(*) total')
            ->groupBy('ym')
            ->get();
        $convertedByMonth = collect($convertedRows)->pluck('total', 'ym');

        $assignedSeries = $months->map(fn($m) => (int) ($assignedByMonth[$m->format('Y-m')] ?? 0))->values();
        $convertedSeries = $months->map(fn($m) => (int) ($convertedByMonth[$m->format('Y-m')] ?? 0))->values();

        $defaultFollowupTypes = collect([
            'call',
            'whatsapp',
            'email',
            'meeting',
            'demo',
            'video_call',
            'site_visit',
            'proposal_sent',
            'quotation_sent',
        ]);

        $distinctFollowupTypes = $this->followupsForAssignedLeadsQuery($staffId)
            ->whereBetween('followup_date', [$months->first()->copy()->startOfMonth(), now()->endOfMonth()])
            ->whereNotNull('followup_type')
            ->select('followup_type')
            ->distinct()
            ->pluck('followup_type')
            ->map(fn($type) => strtolower(trim((string) $type)))
            ->filter()
            ->values();

        $followupTypes = $defaultFollowupTypes
            ->merge($distinctFollowupTypes)
            ->unique()
            ->values()
            ->all();
        $followupTypeSeries = [];
        foreach ($followupTypes as $key => $type) {
            $rows = $this->followupsForAssignedLeadsQuery($staffId)
                ->where('followup_type', $type)
                ->whereBetween('followup_date', [$months->first()->copy()->startOfMonth(), now()->endOfMonth()])
                ->selectRaw('DATE_FORMAT(followup_date, "%Y-%m") ym, COUNT(*) total')
                ->groupBy('ym')->pluck('total', 'ym');

            $followupTypeSeries[] = [
                'name' => ucfirst(str_replace('_', ' ', $type)),
                'data' => $months->map(fn($m) => (int) ($rows[$m->format('Y-m')] ?? 0))->values()->all(),
            ];
        }
        $statusDefs = collect(config('lead_statuses', []))
            ->filter(fn($row) => is_array($row) && ! empty($row['slug']) && ! empty($row['name']))
            ->sortBy(fn($row) => (int) ($row['order'] ?? 999))
            ->values();

        $statusSlugs = $statusDefs->pluck('slug')->map(fn($v) => (string) $v)->filter()->values();
        $statusLabels = $statusDefs->pluck('name')->map(fn($v) => (string) $v)->filter()->values();

        // Respect the selected dashboard period for this distribution chart.
        $statusBase = $this->assignedAnyLeadQuery($staffId)
            ->whereBetween('assigned_at', [$range['from'], $range['to']]);

        $statusCounts = (clone $statusBase)
            ->when($statusSlugs->isNotEmpty(), fn($q) => $q->whereIn('normalized_status', $statusSlugs->all()))
            ->selectRaw('normalized_status as status, COUNT(*) total')
            ->groupBy('normalized_status')
            ->pluck('total', 'status');

        $otherCount = 0;
        if ($statusSlugs->isNotEmpty()) {
            $otherCount = (int) (clone $statusBase)
                ->where(function ($q) use ($statusSlugs) {
                    $q->whereNotIn('normalized_status', $statusSlugs->all())
                        ->orWhereNull('normalized_status')
                        ->orWhere('normalized_status', '');
                })
                ->count();
        }

        $outcomeLabels = ['interested', 'callback_later', 'no_response', 'converted', 'negotiation', 'lost'];
        $outcomeCounts = $this->followupsForAssignedLeadsQuery($staffId)
            ->selectRaw('outcome, COUNT(*) total')->groupBy('outcome')->pluck('total', 'outcome');

        $days = collect(range(29, 0))->map(fn($i) => now()->startOfDay()->subDays($i));
        $dayLabels = $days->map(fn($d) => $d->format('d M'))->values();

        $followupsPerDay = $this->followupsForAssignedLeadsQuery($staffId)
            ->whereBetween('followup_date', [$days->first()->copy()->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(followup_date) d, COUNT(*) total')->groupBy('d')->pluck('total', 'd');

        $activitiesPerDay = LeadActivity::query()
            ->where('user_id', $staffId)
            ->whereBetween('created_at', [$days->first()->copy()->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) d, COUNT(*) total')->groupBy('d')->pluck('total', 'd');

        $overdueTrendRows = $this->assignedLeadQuery($staffId)
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->whereNotIn('status', ['converted', 'won', 'lost'])
            ->selectRaw('DATE(next_followup_at) d, COUNT(*) total')
            ->groupBy('d')
            ->pluck('total', 'd');

        return [
            'monthly_lead_conversion' => [
                'labels' => $monthLabels,
                'assigned' => $assignedSeries,
                'converted' => $convertedSeries,
            ],
            'monthly_followup_activity' => [
                'labels' => $monthLabels,
                'series' => $followupTypeSeries,
            ],
            'lead_status_distribution' => [
                'labels' => $statusLabels->isNotEmpty()
                    ? ($otherCount > 0 ? $statusLabels->concat(['Other'])->values() : $statusLabels)
                    : collect(['New', 'Contacted', 'Qualified', 'Proposal Sent', 'Negotiation', 'Converted', 'Lost']),
                'series' => $statusSlugs->isNotEmpty()
                    ? ($otherCount > 0
                        ? $statusSlugs->map(fn($s) => (int) ($statusCounts[$s] ?? 0))->concat([$otherCount])->values()
                        : $statusSlugs->map(fn($s) => (int) ($statusCounts[$s] ?? 0))->values())
                    : collect(['new', 'contacted', 'qualified', 'proposal_sent', 'negotiation', 'converted', 'lost'])
                    ->map(fn($s) => (int) ($statusCounts[$s] ?? 0))
                    ->values(),
            ],
            'followup_outcome_distribution' => [
                'labels' => $outcomeLabels,
                'series' => collect($outcomeLabels)->map(fn($o) => (int) ($outcomeCounts[$o] ?? 0))->values(),
            ],
            'daily_activity_timeline' => [
                'labels' => $dayLabels,
                'followups' => $days->map(fn($d) => (int) ($followupsPerDay[$d->toDateString()] ?? 0))->values(),
                'activities' => $days->map(fn($d) => (int) ($activitiesPerDay[$d->toDateString()] ?? 0))->values(),
            ],
            'monthly_assigned_vs_converted' => [
                'labels' => $monthLabels,
                'assigned' => $assignedSeries,
                'converted' => $convertedSeries,
            ],
            'overdue_followup_trend' => [
                'labels' => $dayLabels,
                'series' => $days->map(fn($d) => (int) ($overdueTrendRows[$d->toDateString()] ?? 0))->values(),
            ],
        ];
    }

    private function overdueFollowups(int $staffId, array $range): array
    {
        return $this->assignedLeadQuery($staffId)
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->whereNotIn('status', ['converted', 'won', 'lost'])
            ->orderBy('next_followup_at')
            ->limit(20)
            ->get(['id', 'name', 'priority', 'status', 'next_followup_at'])
            ->map(function (Lead $lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'next_followup_at' => optional($lead->next_followup_at)->format('d M Y h:i A'),
                    'overdue_days' => $lead->next_followup_at ? Carbon::parse($lead->next_followup_at)->diffInDays(now()) : 0,
                    'priority' => $lead->priority ?: 'normal',
                    'status' => $lead->status,
                ];
            })->all();
    }

    private function recentActivities(int $staffId, array $range): array
    {
        return LeadActivity::query()
            ->with('lead:id,name')
            ->where('user_id', $staffId)
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($a) => [
                'activity_type' => $a->activity_type,
                'description' => $a->description,
                'lead_name' => optional($a->lead)->name,
                'created_at' => optional($a->created_at)->format('d M Y h:i A'),
            ])->all();
    }

    private function topMetrics(int $staffId, array $range): array
    {
        $fastestResponse = DB::table('leads as l')
            ->join('lead_followups as f', 'l.id', '=', 'f.lead_id')
            ->whereIn('l.id', $this->assignedLeadIdsSubQuery($staffId))
            ->selectRaw('MIN(TIMESTAMPDIFF(HOUR, l.created_at, f.followup_date)) as min_hours')
            ->value('min_hours');

        $highestLead = $this->assignedLeadQuery($staffId)->orderByDesc('won_value')->first(['name', 'won_value']);

        $bestMonth = $this->assignedLeadQuery($staffId)->whereIn('status', ['converted', 'won'])
            ->selectRaw('DATE_FORMAT(updated_at, "%Y-%m") ym, COUNT(*) total')
            ->groupBy('ym')->orderByDesc('total')->first();

        $activityDay = LeadActivity::query()->where('user_id', $staffId)
            ->selectRaw('DATE(created_at) d, COUNT(*) total')->groupBy('d')->orderByDesc('total')->first();

        $avgFollowupsPerLead = DB::table('lead_followups as f')
            ->join('leads as l', 'l.id', '=', 'f.lead_id')
            ->whereIn('l.id', $this->assignedLeadIdsSubQuery($staffId))
            ->selectRaw('COUNT(f.id) / NULLIF(COUNT(DISTINCT l.id),0) as avg_value')
            ->value('avg_value');

        return [
            'fastest_response_time_hours' => (int) ($fastestResponse ?? 0),
            'highest_conversion_lead' => $highestLead?->name ?: '-',
            'highest_conversion_value' => (float) ($highestLead?->won_value ?? 0),
            'best_conversion_month' => $bestMonth?->ym ?: '-',
            'most_active_day' => $activityDay?->d ?: '-',
            'avg_followups_per_lead' => round((float) ($avgFollowupsPerLead ?? 0), 2),
        ];
    }

    private function resolveDateRange(array $filters): array
    {
        $period = $filters['period'] ?? '30d';
        $now = now();

        return match ($period) {
            '7d' => ['from' => $now->copy()->subDays(7)->startOfDay(), 'to' => $now->copy()->endOfDay(), 'period' => '7d'],
            'this_month' => ['from' => $now->copy()->startOfMonth(), 'to' => $now->copy()->endOfMonth(), 'period' => 'this_month'],
            'this_quarter' => ['from' => $now->copy()->firstOfQuarter(), 'to' => $now->copy()->lastOfQuarter(), 'period' => 'this_quarter'],
            'this_year' => ['from' => $now->copy()->startOfYear(), 'to' => $now->copy()->endOfYear(), 'period' => 'this_year'],
            'custom' => [
                'from' => ! empty($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : $now->copy()->subDays(30)->startOfDay(),
                'to' => ! empty($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : $now->copy()->endOfDay(),
                'period' => 'custom',
            ],
            default => ['from' => $now->copy()->subDays(30)->startOfDay(), 'to' => $now->copy()->endOfDay(), 'period' => '30d'],
        };
    }

    private function assignedLeadQuery(int $staffId)
    {
        return Lead::query()->whereIn('id', $this->assignedLeadIdsSubQuery($staffId));
    }

    private function assignedLeadIdsSubQuery(int $staffId)
    {
        return Lead::query()
            ->select('leads.id')
            ->where(function ($query) use ($staffId) {
                $query->whereExists(function ($subQuery) use ($staffId) {
                    $subQuery->selectRaw('1')
                        ->from('assigned_leads')
                        ->where('assigned_leads.lead_model', self::ASSIGNED_LEAD_MODEL)
                        ->whereColumn('assigned_leads.lead_id', 'leads.id')
                        ->where(function ($jsonQuery) use ($staffId) {
                            $jsonQuery
                                // Matches numeric JSON arrays like [1,2,3]
                                ->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $staffId])
                                // Matches string JSON arrays like ["1","2","3"]
                                ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $staffId]);
                        });
                });
            });
    }

    private function assignedAnyLeadQuery(int $staffId): QueryBuilder
    {
        $queries = [];

        foreach (self::ASSIGNMENT_SOURCES as $leadModel => [$table, $timestampColumn]) {
            $queries[] = $this->assignedLeadSourceQuery($staffId, $leadModel, $table, $timestampColumn);
        }

        $base = array_shift($queries);
        foreach ($queries as $q) {
            $base->unionAll($q);
        }

        return DB::query()->fromSub($base, 'assigned_any');
    }

    private function assignedLeadSourceQuery(int $staffId, string $leadModel, string $table, string $timestampColumn): QueryBuilder
    {
        $statusExpression = $this->normalizedStatusSql($leadModel, 't.status');
        $statusChangedExpression = $this->statusChangedAtSql($leadModel, $timestampColumn);

        return DB::table('assigned_leads as al')
            ->join($table . ' as t', function ($join) use ($leadModel) {
                $join->on('al.lead_id', '=', 't.id')
                    ->where('al.lead_model', '=', $leadModel);
            })
            ->where(function ($jsonQuery) use ($staffId) {
                $jsonQuery
                    ->whereRaw('JSON_CONTAINS(al.staff_ids, ?, "$")', [(string) $staffId])
                    ->orWhereRaw('JSON_CONTAINS(al.staff_ids, JSON_QUOTE(?), "$")', [(string) $staffId]);
            })
            ->selectRaw(
                '? as lead_model, al.lead_id as lead_id, al.created_at as assigned_at, t.' . $timestampColumn . ' as lead_created_at, ' . $statusExpression . ' as normalized_status, ' . $statusChangedExpression . ' as status_changed_at',
                [$leadModel]
            );
    }

    private function normalizedStatusSql(string $leadModel, string $column): string
    {
        // Non-pipeline sources can use converted/loss while pipeline may still have legacy won/lost.
        if ($leadModel === self::ASSIGNED_LEAD_MODEL) {
            return 'LOWER(TRIM(COALESCE(' . $column . ', "")))';
        }

        return 'CASE LOWER(TRIM(COALESCE(' . $column . ', "")))
            WHEN "won" THEN "converted"
            WHEN "loss" THEN "lost"
            ELSE LOWER(TRIM(COALESCE(' . $column . ', "")))
        END';
    }

    private function statusChangedAtSql(string $leadModel, string $timestampColumn): string
    {
        if ($leadModel === self::ASSIGNED_LEAD_MODEL) {
            return 'COALESCE(t.converted_at, t.lost_at, t.status_updated_at, t.updated_at, t.' . $timestampColumn . ')';
        }

        // digital_marketing/webapp tables do not have updated_at.
        $hasUpdatedAt = in_array($leadModel, ['meta', 'google'], true);

        if ($hasUpdatedAt) {
            return 'COALESCE(t.updated_at, t.' . $timestampColumn . ')';
        }

        return 't.' . $timestampColumn;
    }

    private function followupsForAssignedLeadsQuery(int $staffId)
    {
        return LeadFollowup::query()
            ->where(function ($query) use ($staffId) {
                // Preferred path: use explicit source mapping saved on followup rows.
                $query->whereExists(function ($subQuery) use ($staffId) {
                    $subQuery->selectRaw('1')
                        ->from('assigned_leads as al')
                        ->whereColumn('al.lead_model', 'lead_followups.source_type')
                        ->whereColumn('al.lead_id', 'lead_followups.source_id')
                        ->where(function ($jsonQuery) use ($staffId) {
                            $jsonQuery
                                ->whereRaw('JSON_CONTAINS(al.staff_ids, ?, "$")', [(string) $staffId])
                                ->orWhereRaw('JSON_CONTAINS(al.staff_ids, JSON_QUOTE(?), "$")', [(string) $staffId]);
                        });
                })
                // Legacy fallback: old followups without source columns still linked by pipeline lead_id.
                ->orWhere(function ($legacyQuery) use ($staffId) {
                    $legacyQuery
                        ->whereNull('lead_followups.source_type')
                        ->whereNull('lead_followups.source_id')
                        ->whereIn('lead_followups.lead_id', $this->assignedLeadIdsSubQuery($staffId));
                });
            });
    }
}
