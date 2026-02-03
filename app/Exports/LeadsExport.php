<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\Staff;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeadsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $leads = Lead::all();
        $staff = Staff::all()->keyBy('id');

        // Add assigned staff names to each lead
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
}
