<?php

namespace App\Imports;

use App\Models\SocialContentPlan;
use App\Models\SocialPost;
use App\Models\SocialPostPlatform;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SocialMediaPlanPostsImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;
    public array $errors = [];

    public function __construct(private readonly SocialContentPlan $plan)
    {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $date = $row['date'] ?? '';
            $type = $this->normaliseType((string) ($row['type'] ?? ''));
            $category = trim((string) ($row['category'] ?? ''));
            $topic = trim((string) ($row['topic_creative_direction'] ?? $row['topic'] ?? $row['creative_direction'] ?? ''));

            if (trim((string) $date) === '' && $type === null && $category === '' && $topic === '') {
                continue;
            }

            if ($type === null) {
                $this->errors[] = "Row {$line}: type could not be recognised. Use Static Post, Motion, Reel, Carousel, Video, Story or Image.";
                continue;
            }

            try {
                $plannedAt = $this->parseDate($date)->setTime(10, 0);
            } catch (\Throwable) {
                $this->errors[] = "Row {$line}: date is invalid.";
                continue;
            }

            if ((int) $plannedAt->year !== (int) $this->plan->plan_year || (int) $plannedAt->month !== (int) $this->plan->plan_month) {
                $this->errors[] = "Row {$line}: date must be inside the monthly plan period.";
                continue;
            }

            if ($topic === '') {
                $this->errors[] = "Row {$line}: topic / creative direction is required.";
                continue;
            }

            DB::transaction(function () use ($plannedAt, $type, $category, $topic) {
                $post = SocialPost::create([
                    'social_content_plan_id' => $this->plan->id,
                    'client_id' => $this->plan->client_id,
                    'title' => $topic,
                    'content_category' => $category ?: null,
                    'post_type' => $type,
                    'creative_brief' => $topic,
                    'priority' => 'medium',
                    'planned_at' => $plannedAt,
                    'status' => 'planned',
                    'created_by' => auth()->id(),
                ]);

                SocialPostPlatform::create(['social_post_id' => $post->id, 'platform' => 'Instagram']);
                $this->created++;
            });
        }
    }

    private function normaliseType(string $value): ?string
    {
        $value = strtolower(trim($value));
        if ($value === '') return null;
        if (str_contains($value, 'carousel')) return 'carousel';
        if (str_contains($value, 'reel')) return 'reel';
        if (str_contains($value, 'motion') || str_contains($value, 'video')) return 'video';
        if (str_contains($value, 'static') || str_contains($value, 'image')) return 'image';
        if (str_contains($value, 'story')) return 'story';
        if ($value === 'other') return 'other';
        return null;
    }

    private function parseDate(mixed $value): Carbon
    {
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
        }

        $value = trim((string) $value);
        foreach (['j-M-Y', 'j-M-y', 'd-M-Y', 'd-M-y', 'Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false) return $date;
            } catch (\Throwable) {
                // Try the next supported spreadsheet date format.
            }
        }

        $date = Carbon::createFromFormat('j-M-Y', $value.'-'.$this->plan->plan_year);
        if ($date !== false) return $date;

        return Carbon::parse($value);
    }
}
