<?php

namespace App\Http\Controllers;

use App\Models\AssignedLead;
use App\Models\DigitalMarketingLead;
use App\Models\GoogleLead;
use App\Models\Lead;
use App\Models\MetaLead;
use App\Models\User;
use App\Models\WebappLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadManagementController extends Controller
{
    private const STATUSES = ['new', 'contacted', 'qualified', 'converted', 'loss'];
    private const SOURCE_LEAD = 'lead';
    private const SOURCE_DIGITAL_MARKETING = 'digital_marketing';
    private const SOURCE_WEBAPP = 'webapp';
    private const SOURCE_META = 'meta';
    private const SOURCE_GOOGLE = 'google';

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->can('view_leads'), 403);

        $search = trim((string) $request->query('search', ''));
        $sourceFilter = trim((string) $request->query('source', ''));
        $sourceLabels = [
            self::SOURCE_LEAD => 'Leads',
            self::SOURCE_DIGITAL_MARKETING => 'Digital Marketing',
            self::SOURCE_WEBAPP => 'Web App',
            self::SOURCE_META => 'Meta',
            self::SOURCE_GOOGLE => 'Google',
        ];

        $filteredBySearch = $this->mergedLeads()
            ->when($search !== '', function (Collection $items) use ($search) {
                return $items->filter(function (array $row) use ($search) {
                    $needle = mb_strtolower($search);
                    $haystack = mb_strtolower(implode(' ', [
                        (string) ($row['name'] ?? ''),
                        (string) ($row['email'] ?? ''),
                        (string) ($row['number'] ?? ''),
                        (string) ($row['company'] ?? ''),
                        (string) ($row['source'] ?? ''),
                    ]));

                    return str_contains($haystack, $needle);
                });
            });

        $tabCounts = ['all' => $filteredBySearch->count()];
        foreach (array_keys($sourceLabels) as $sourceKey) {
            $tabCounts[$sourceKey] = $filteredBySearch
                ->where('source_type', $sourceKey)
                ->count();
        }

        $merged = $filteredBySearch
            ->when($sourceFilter !== '', function (Collection $items) use ($sourceFilter) {
                return $items->filter(fn (array $row) => $row['source_type'] === $sourceFilter);
            })
            ->sortByDesc('created_at_ts')
            ->values();

        // Lead management uses client-side DataTable features, so provide all filtered rows.
        $perPage = max($merged->count(), 1);
        $page = 1;
        $paginated = new LengthAwarePaginator(
            $merged->forPage($page, $perPage)->values(),
            $merged->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $staff = User::staffMembers()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('lead-management.index', [
            'leads' => $paginated,
            'staff' => $staff,
            'filters' => [
                'search' => $search,
                'source' => $sourceFilter,
            ],
            'sources' => $sourceLabels,
            'tabCounts' => $tabCounts,
        ]);
    }

    public function show(string $source, int $id): View
    {
        abort_unless(auth()->user()?->can('view_leads'), 403);
        $lead = $this->findNormalizedLeadOrFail($source, $id);

        return view('lead-management.show', compact('lead'));
    }

    public function assign(Request $request, string $source, int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->can('edit_leads'), 403);

        $validated = $request->validate([
            'assigned_user_ids' => ['required', 'array', 'min:1'],
            'assigned_user_ids.*' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'staff')),
            ],
        ]);

        $normalized = $this->findNormalizedLeadOrFail($source, $id);
        $existingAssignment = AssignedLead::query()
            ->where('lead_model', $normalized['source_type'])
            ->where('lead_id', (int) $normalized['source_id'])
            ->first();

        $assigned = collect($existingAssignment?->staff_ids ?? [])
            ->map(fn ($value) => (int) $value)
            ->merge(collect($validated['assigned_user_ids'])->map(fn ($value) => (int) $value))
            ->unique()
            ->values()
            ->all();

        AssignedLead::updateOrCreate(
            [
                'lead_model' => $normalized['source_type'],
                'lead_id' => (int) $normalized['source_id'],
            ],
            [
                'staff_ids' => $assigned,
            ]
        );

        return redirect()
            ->route('lead-management.index')
            ->with('success', 'Lead assigned successfully.');
    }

    public function bulkAssign(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('edit_leads'), 403);

        $validated = $request->validate([
            'assigned_user_ids' => ['required', 'array', 'min:1'],
            'assigned_user_ids.*' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'staff')),
            ],
            'selected_leads' => ['required', 'array', 'min:1'],
            'selected_leads.*.source' => ['required', 'string'],
            'selected_leads.*.id' => ['required', 'integer'],
        ]);

        $selectedStaffIds = collect($validated['assigned_user_ids'])
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();
        $assignedCount = 0;

        foreach ($validated['selected_leads'] as $selectedLead) {
            $source = (string) $selectedLead['source'];
            $id = (int) $selectedLead['id'];

            // Ensure the selected pair is a valid visible lead source/id.
            $this->findNormalizedLeadOrFail($source, $id);

            $existingAssignment = AssignedLead::query()
                ->where('lead_model', $source)
                ->where('lead_id', $id)
                ->first();

            $assigned = collect($existingAssignment?->staff_ids ?? [])
                ->map(fn ($value) => (int) $value)
                ->merge($selectedStaffIds)
                ->unique()
                ->values()
                ->all();

            AssignedLead::updateOrCreate(
                [
                    'lead_model' => $source,
                    'lead_id' => $id,
                ],
                [
                    'staff_ids' => $assigned,
                ]
            );

            $assignedCount++;
        }

        return redirect()
            ->route('lead-management.index')
            ->with('success', $assignedCount.' lead(s) assigned successfully.');
    }

    public function destroy(string $source, int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->can('delete_leads'), 403);

        $this->deleteBySource($source, $id);

        return redirect()
            ->route('lead-management.index')
            ->with('success', 'Lead deleted successfully.');
    }

    public function updateStatus(Request $request, string $source, int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->can('edit_leads'), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
        ]);

        $lead = match ($source) {
            self::SOURCE_LEAD => Lead::findOrFail($id),
            self::SOURCE_DIGITAL_MARKETING => DigitalMarketingLead::findOrFail($id),
            self::SOURCE_WEBAPP => WebappLead::findOrFail($id),
            self::SOURCE_META => MetaLead::findOrFail($id),
            self::SOURCE_GOOGLE => GoogleLead::findOrFail($id),
            default => abort(404),
        };

        if (($lead->status ?? null) === 'converted' && $validated['status'] !== 'converted') {
            return redirect()->route('lead-management.index')->with('error', 'Converted lead status cannot be changed.');
        }

        if (($lead->status ?? null) === 'converted' && $validated['status'] === 'converted') {
            return redirect()->route('lead-management.index')->with('success', 'Lead is already converted.');
        }

        if ($validated['status'] === 'converted') {
            if ($source === self::SOURCE_LEAD) {
                $name = trim((string) ($lead->name ?? ''));
                $email = $lead->email ?? '';
                $phone = $lead->phone ?? '';
                $company = $lead->company ?? '';
                $website = $lead->website ?? '';
                $city = $lead->city ?? '';
                $state = $lead->state ?? '';
                $country = $lead->country ?? '';
            } elseif ($source === self::SOURCE_META) {
                $name = trim((string) ($lead->full_name ?? ''));
                $email = $lead->email ?? '';
                $phone = $lead->phone ?? '';
                $company = '';
                $website = '';
                $city = $lead->city ?? '';
                $state = $lead->state ?? '';
                $country = '';
            } elseif ($source === self::SOURCE_GOOGLE) {
                $name = trim((string) ($lead->full_name ?? ''));
                $email = $lead->email ?? '';
                $phone = $lead->phone ?? '';
                $company = $lead->company ?? '';
                $website = '';
                $city = '';
                $state = '';
                $country = '';
            } else {
                $name = trim((string) ($lead->name ?? ''));
                $email = $lead->email ?? '';
                $phone = $lead->phone ?? '';
                $company = $lead->company ?? '';
                $website = $lead->website ?? '';
                $city = '';
                $state = '';
                $country = '';
            }

            $nameParts = preg_split('/\s+/', $name, 2);

            return redirect()
                ->route('client.create')
                ->withInput([
                    'convert_source' => $source,
                    'convert_id' => $lead->id,
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $email,
                    'phone' => $phone,
                    'status' => 'active',
                    'company_name' => $company,
                    'website' => $website,
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                ])
                ->with('success', 'Conversion pending. Please complete client creation.');
        }

        $lead->status = $validated['status'];
        $lead->save();

        return redirect()->route('lead-management.index')->with('success', 'Lead status updated successfully.');
    }

    private function mergedLeads(): Collection
    {
        $digital = DigitalMarketingLead::query()->get()->map(function (DigitalMarketingLead $lead) {
            return $this->normalizeRow(
                self::SOURCE_DIGITAL_MARKETING,
                $lead->id,
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->company,
                'Digital Marketing',
                $lead->created_at,
                $lead->status
            );
        });

        $webapp = WebappLead::query()->get()->map(function (WebappLead $lead) {
            return $this->normalizeRow(
                self::SOURCE_WEBAPP,
                $lead->id,
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->company,
                'Web App',
                $lead->created_at,
                $lead->status
            );
        });

        $meta = MetaLead::query()->get()->map(function (MetaLead $lead) {
            return $this->normalizeRow(
                self::SOURCE_META,
                $lead->id,
                $lead->full_name,
                $lead->email,
                $lead->phone,
                null,
                'Meta',
                $lead->created_time,
                $lead->status
            );
        });

        $google = GoogleLead::query()->get()->map(function (GoogleLead $lead) {
            return $this->normalizeRow(
                self::SOURCE_GOOGLE,
                $lead->id,
                $lead->full_name,
                $lead->email,
                $lead->phone,
                $lead->company,
                'Google',
                $lead->created_at ?? $lead->lead_submit_time,
                $lead->status
            );
        });

        // Staff assignment for non-Lead sources creates helper rows in `leads`.
        // Exclude those mirror rows from the listing so entries don't appear doubled.
        $digitalContactKeys = $this->buildContactKeysFromRows($digital);
        $webappContactKeys = $this->buildContactKeysFromRows($webapp);
        $metaContactKeys = $this->buildContactKeysFromRows($meta);
        $googleContactKeys = $this->buildContactKeysFromRows($google);

        $leads = Lead::query()->get()
            ->reject(function (Lead $lead) use (
                $digitalContactKeys,
                $webappContactKeys,
                $metaContactKeys,
                $googleContactKeys
            ) {
                $source = trim((string) ($lead->source ?? ''));
                $emailKey = $this->normalizeContact((string) ($lead->email ?? ''));
                $phoneKey = $this->normalizeContact((string) ($lead->phone ?? ''));

                return match ($source) {
                    'Digital Marketing' => $this->hasAnyContactMatch($emailKey, $phoneKey, $digitalContactKeys),
                    'Web App' => $this->hasAnyContactMatch($emailKey, $phoneKey, $webappContactKeys),
                    'Meta' => $this->hasAnyContactMatch($emailKey, $phoneKey, $metaContactKeys),
                    'Google' => $this->hasAnyContactMatch($emailKey, $phoneKey, $googleContactKeys),
                    default => false,
                };
            })
            ->values()
            ->map(function (Lead $lead) {
                return $this->normalizeRow(
                    self::SOURCE_LEAD,
                    $lead->id,
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->company,
                    $lead->source ?: 'Leads',
                    $lead->created_at,
                    $lead->status
                );
            });

        return $leads
            ->concat($digital)
            ->concat($webapp)
            ->concat($meta)
            ->concat($google);
    }

    private function buildContactKeysFromRows(Collection $rows): array
    {
        $emails = $rows
            ->pluck('email')
            ->map(fn ($value) => $this->normalizeContact((string) $value))
            ->filter()
            ->values()
            ->all();

        $phones = $rows
            ->pluck('number')
            ->map(fn ($value) => $this->normalizeContact((string) $value))
            ->filter()
            ->values()
            ->all();

        return [
            'emails' => array_fill_keys($emails, true),
            'phones' => array_fill_keys($phones, true),
        ];
    }

    private function hasAnyContactMatch(string $emailKey, string $phoneKey, array $contactKeys): bool
    {
        return ($emailKey !== '' && isset($contactKeys['emails'][$emailKey]))
            || ($phoneKey !== '' && isset($contactKeys['phones'][$phoneKey]));
    }

    private function normalizeContact(string $value): string
    {
        $value = trim($value);

        if ($value === '' || $value === '-') {
            return '';
        }

        return mb_strtolower($value);
    }

    private function normalizeRow(
        string $sourceType,
        int $id,
        ?string $name,
        ?string $email,
        ?string $number,
        ?string $company,
        ?string $source,
        mixed $createdAt,
        ?string $status = null
    ): array {
        $date = $createdAt ? Carbon::parse($createdAt) : null;

        return [
            'source_type' => $sourceType,
            'source_id' => $id,
            'name' => $name ?: '-',
            'email' => $email ?: '-',
            'number' => $number ?: '-',
            'company' => $company ?: '-',
            'source' => $source ?: '-',
            'assigned_to' => $this->resolveAssignedTo(
                $sourceType,
                $id,
                $email,
                $number
            ),
            'created_at' => $date?->format('d M Y h:i A') ?: '-',
            'created_at_ts' => $date?->timestamp ?: 0,
            'status' => $status ?: 'new',
        ];
    }

    private function resolveAssignedTo(string $sourceType, int $id, ?string $email, ?string $number): string
    {
        $assignment = AssignedLead::query()
            ->where('lead_model', $sourceType)
            ->where('lead_id', $id)
            ->first();

        if (! $assignment || empty($assignment->staff_ids) || ! is_array($assignment->staff_ids)) {
            return '-';
        }

        $names = User::staffMembers()
            ->whereIn('id', $assignment->staff_ids)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (User $user) => trim(($user->first_name ?? '').' '.($user->last_name ?? '')))
            ->filter()
            ->values();

        return $names->isNotEmpty() ? $names->implode(', ') : '-';
    }

    private function findNormalizedLeadOrFail(string $source, int $id): array
    {
        $matched = $this->mergedLeads()->first(function (array $row) use ($source, $id) {
            return $row['source_type'] === $source && (int) $row['source_id'] === $id;
        });

        abort_unless((bool) $matched, 404);

        return $matched;
    }

    private function deleteBySource(string $source, int $id): void
    {
        match ($source) {
            self::SOURCE_LEAD => Lead::findOrFail($id)->delete(),
            self::SOURCE_DIGITAL_MARKETING => DigitalMarketingLead::findOrFail($id)->delete(),
            self::SOURCE_WEBAPP => WebappLead::findOrFail($id)->delete(),
            self::SOURCE_META => MetaLead::findOrFail($id)->delete(),
            self::SOURCE_GOOGLE => GoogleLead::findOrFail($id)->delete(),
            default => abort(404),
        };
    }
}
