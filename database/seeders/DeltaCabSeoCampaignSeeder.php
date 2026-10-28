<?php

namespace Database\Seeders;

use App\Models\{SeoCampaign, SeoKeyword, User};
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DeltaCabSeoCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::query()->where('email', 'heena@deltacab.co.in')->firstOrFail();
        $staff = User::query()->where('role', 'staff')->take(2)->get();
        $manager = $staff->firstOrFail();
        $executive = $staff->get(1) ?? $manager;
        $campaign = SeoCampaign::firstOrCreate(['client_id' => $client->id, 'website_url' => 'https://deltacab.co.in/'], ['name' => 'Delta Cab — Organic Search Growth', 'industry' => 'Cables and Wires Manufacturing', 'target_country' => 'India', 'target_locations' => ['Mumbai', 'India'], 'target_language' => 'en', 'search_engine' => 'google', 'devices' => ['desktop', 'mobile'], 'start_date' => now()->toDateString(), 'goals' => 'Improve organic visibility for cable, wire and manufacturing product keywords in Mumbai and across India.', 'reporting_frequency' => 'monthly', 'status' => 'active', 'created_by' => $manager->id]);
        $campaign->members()->updateOrCreate(['user_id' => $manager->id, 'campaign_role' => 'manager'], ['unassigned_at' => null]);
        $campaign->members()->updateOrCreate(['user_id' => $executive->id, 'campaign_role' => 'executive'], ['unassigned_at' => null]);
        $campaign->audit()->firstOrCreate([], ['prepared_by' => $executive->id, 'summary' => 'Initial SEO audit to be completed. June keyword positions were imported from the client-provided Excel report.']);
        $rows = IOFactory::load('C:/Users/Manish-Technofra/Downloads/5810c286-13be-4cb7-b3e1-a0ac3a0a7483.xlsx')->getActiveSheet()->toArray(null, true, true, false);
        $imported = 0;
        foreach ($rows as $row) {
            $keyword = trim((string) ($row[4] ?? ''));
            $position = trim((string) ($row[5] ?? ''));
            if ($keyword === '' || !is_numeric($position) || (int) $position < 1) continue;
            $normalized = mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $keyword)));
            $location = str_contains($normalized, 'mumbai') ? 'Mumbai' : 'India';
            $seoKeyword = SeoKeyword::firstOrCreate(['seo_campaign_id' => $campaign->id, 'normalized_keyword' => $normalized, 'location' => $location, 'device' => 'desktop', 'search_engine' => 'google'], ['keyword' => $keyword, 'category' => 'Imported June keyword report', 'language' => 'en', 'priority' => (int) $position <= 10 ? 'high' : 'medium', 'created_by' => $executive->id]);
            $seoKeyword->rankings()->updateOrCreate(['checked_on' => '2026-06-30', 'source' => 'Client Excel — June 2026'], ['rank_position' => (int) $position, 'remarks' => 'Imported from client-provided June position report.', 'recorded_by' => $executive->id]);
            $imported++;
        }
        $this->command?->info("Delta Cab campaign ready: /seo/campaigns/{$campaign->id}; {$imported} June ranking rows imported.");
    }
}
