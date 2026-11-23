<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SocialMediaPlanPostsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return ['date', 'type', 'category', 'topic / creative direction'];
    }

    public function array(): array
    {
        return [
            ['2026-12-25', 'image', 'International / Christian', 'Christmas greeting for the client audience'],
            ['2026-12-28', 'reel', 'Promotional', 'Year-end offer and customer appreciation reel'],
        ];
    }
}
