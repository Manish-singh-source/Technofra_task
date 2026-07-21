<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeadsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly ?int $userId = null)
    {
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $leads = $this->visibleLeadQuery()->get();
        $staff = User::staffMembers()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->keyBy('id');

        $leads->transform(function ($lead) use ($staff) {
            $assignedNames = [];
            if ($lead->assigned) {
                foreach ($lead->assigned as $staffId) {
                    if (isset($staff[$staffId])) {
                        $assignedNames[] = $staff[$staffId]->first_name . ' ' . ($staff[$staffId]->last_name ?? '');
                    }
                }
            }
            $lead->assigned_names = implode(', ', $assignedNames);
            return $lead;
        });

        return $leads;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Company',
            'Email',
            'Phone',
            'Position',
            'Website',
            'Address',
            'City',
            'State',
            'Country',
            'Zip Code',
            'Lead Value',
            'Source',
            'Tags',
            'Assigned To',
            'Status',
            'Description',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * @param mixed $lead
     * @return array
     */
    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->name,
            $lead->company,
            $lead->email,
            $lead->phone,
            $lead->position,
            $lead->website,
            $lead->address,
            $lead->city,
            $lead->state,
            $lead->country,
            $lead->zipCode,
            $lead->lead_value,
            $lead->source,
            $lead->tags ? implode(', ', $lead->tags) : '',
            $lead->assigned_names,
            ucfirst($lead->status),
            $lead->description,
            $lead->created_at,
            $lead->updated_at,
        ];
    }

    private function visibleLeadQuery()
    {
        $query = Lead::query();
        $userId = (int) ($this->userId ?? 0);
        $user = $userId > 0 ? User::query()->find($userId) : null;
        $role = strtolower((string) ($user?->getRawOriginal('role') ?? $user?->role ?? ''));

        if ($role === 'staff') {
            return $query->whereExists(function ($subQuery) use ($userId) {
                $subQuery->selectRaw('1')
                    ->from('assigned_leads')
                    ->where('assigned_leads.lead_model', 'lead')
                    ->whereColumn('assigned_leads.lead_id', 'leads.id')
                    ->where(function ($jsonQuery) use ($userId) {
                        $jsonQuery
                            ->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                            ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                    });
            });
        }

        return $query->where('created_by', $userId);
    }
}