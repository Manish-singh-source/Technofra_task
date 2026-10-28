<?php

namespace Database\Seeders;

use App\Models\{SeoActivity, SeoAudit, SeoAuditFinding, SeoBacklinkActivity, SeoBaseline, SeoCampaign, SeoCompetitor, SeoKeyword, SeoMonthlyCycle, SeoPerformanceRecord, SeoReport, SeoTechnicalIssue, User};
use Illuminate\Database\Seeder;

class SeoDemoCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::query()->whereIn('role', ['client', 'customer'])->firstOrFail();
        $staff = User::query()->where('role', 'staff')->take(2)->get();
        $manager = $staff->first() ?? User::query()->firstOrFail();
        $executive = $staff->get(1) ?? $manager;

        $campaign = SeoCampaign::firstOrCreate(['name' => 'Demo SEO Growth Campaign — GreenLeaf Interiors'], [
            'client_id' => $client->id, 'website_url' => 'https://example.com', 'industry' => 'Interior Design',
            'target_country' => 'India', 'target_locations' => ['Mumbai', 'Pune'], 'target_language' => 'en',
            'search_engine' => 'google', 'devices' => ['desktop', 'mobile'], 'start_date' => '2026-07-01',
            'goals' => 'Increase qualified organic traffic, improve local visibility and grow top-10 keyword coverage.',
            'reporting_frequency' => 'monthly', 'status' => 'active', 'created_by' => $manager->id,
        ]);
        $campaign->members()->updateOrCreate(['user_id' => $manager->id, 'campaign_role' => 'manager'], ['unassigned_at' => null]);
        $campaign->members()->updateOrCreate(['user_id' => $executive->id, 'campaign_role' => 'executive'], ['unassigned_at' => null]);

        $audit = $campaign->audit()->firstOrCreate([], ['prepared_by' => $executive->id]);
        $audit->update(['status' => 'approved', 'summary' => 'Initial audit complete: indexing, page speed, metadata and local SEO opportunities identified.', 'prepared_by' => $executive->id, 'reviewed_by' => $manager->id, 'submitted_at' => now()->subMonths(3), 'reviewed_at' => now()->subMonths(3), 'review_feedback' => 'Approved for execution.']);
        foreach ([['technical','Missing XML sitemap','high'],['on_page','Service pages need unique meta titles','medium'],['performance','Core Web Vitals need improvement','high']] as [$category,$title,$severity]) {
            SeoAuditFinding::firstOrCreate(['seo_audit_id' => $audit->id, 'title' => $title], ['category' => $category, 'severity' => $severity, 'affected_url' => 'https://example.com/services', 'description' => 'Demo audit finding for presentation.', 'recommended_solution' => 'Implement and verify the recommended SEO fix.', 'assigned_to' => $executive->id, 'status' => 'verified']);
        }
        $baseline = $campaign->baseline()->firstOrCreate([], ['seo_audit_id' => $audit->id, 'captured_on' => '2026-07-01', 'captured_by' => $executive->id]);
        $baseline->update(['status' => 'approved', 'seo_audit_id' => $audit->id, 'captured_on' => '2026-07-01', 'captured_by' => $executive->id, 'approved_by' => $manager->id, 'approved_at' => now()->subMonths(3), 'notes' => 'Approved starting benchmark for demo reporting.']);
        foreach (['organic_clicks'=>420,'organic_impressions'=>12400,'organic_sessions'=>380,'average_ctr'=>3.39,'average_position'=>29.4,'indexed_pages'=>86,'top_10_keywords'=>4] as $key => $value) $baseline->metrics()->updateOrCreate(['metric_key' => $key], ['metric_value' => $value, 'source' => 'Google Search Console / GA4']);

        foreach ([['interior designer mumbai',22,8],['modular kitchen mumbai',31,14],['home renovation pune',45,19],['office interior design',18,9]] as [$word,$initial,$current]) {
            $keyword = SeoKeyword::firstOrCreate(['seo_campaign_id' => $campaign->id, 'normalized_keyword' => $word, 'location' => 'Mumbai', 'device' => 'mobile', 'search_engine' => 'google'], ['keyword' => $word, 'target_page_url' => 'https://example.com/services', 'language' => 'en', 'search_volume' => 900, 'keyword_difficulty' => 42, 'priority' => 'high', 'created_by' => $executive->id]);
            $keyword->rankings()->updateOrCreate(['checked_on' => '2026-07-01', 'source' => 'manual'], ['rank_position' => $initial, 'recorded_by' => $executive->id]);
            $keyword->rankings()->updateOrCreate(['checked_on' => '2026-10-01', 'source' => 'manual'], ['rank_position' => $current, 'recorded_by' => $executive->id, 'remarks' => 'Improved after on-page optimisation.']);
        }
        foreach ([
            ['UrbanNest Interiors', 'https://example.org', 'Strong portfolio pages and frequent project case studies.', 'Weak local landing-page coverage.', 'Create location-specific service pages and stronger before/after case studies.'],
            ['Studio Habitat', 'https://example.net', 'High visibility for modular kitchen terms and strong Google Business Profile reviews.', 'Slow mobile experience on service pages.', 'Win mobile users with faster pages and a review-generation campaign.'],
            ['DesignCraft Mumbai', 'https://example.edu', 'Consistent backlink profile from local directories and design publications.', 'Thin content for commercial interior services.', 'Build commercial-office content cluster and relevant industry backlinks.'],
        ] as [$name,$url,$strengths,$weaknesses,$opportunities]) {
            SeoCompetitor::updateOrCreate(['seo_campaign_id' => $campaign->id, 'website_url' => $url], ['name' => $name, 'strengths' => $strengths, 'weaknesses' => $weaknesses, 'opportunities' => $opportunities, 'data_source' => 'Manual SERP review — October 2026', 'created_by' => $executive->id]);
        }
        $cycle = SeoMonthlyCycle::updateOrCreate(['seo_campaign_id' => $campaign->id, 'month' => '2026-10-01'], ['objectives' => 'Improve service page visibility, resolve technical issues and build relevant local links.', 'status' => 'active', 'created_by' => $manager->id]);
        foreach ([['Optimise service page metadata','on_page','approved'],['Fix XML sitemap and submit to Search Console','technical','approved'],['Publish local citation backlinks','off_page','submitted'],['Prepare October performance report','reporting','in_progress']] as [$title,$category,$status]) SeoActivity::updateOrCreate(['seo_campaign_id'=>$campaign->id,'title'=>$title], ['seo_monthly_cycle_id'=>$cycle->id,'category'=>$category,'assigned_to'=>$executive->id,'deadline'=>'2026-10-31','priority'=>'high','status'=>$status,'reviewed_by'=>in_array($status,['approved'])?$manager->id:null,'reviewed_at'=>in_array($status,['approved'])?now():null,'completed_at'=>in_array($status,['approved'])?now():null]);
        SeoTechnicalIssue::firstOrCreate(['seo_campaign_id'=>$campaign->id,'title'=>'XML sitemap missing service URLs'], ['issue_type'=>'sitemap','affected_url'=>'https://example.com/sitemap.xml','severity'=>'high','status'=>'verified_closed','assigned_to'=>$executive->id,'verified_by'=>$manager->id,'verified_at'=>now()]);
        SeoBacklinkActivity::firstOrCreate(['seo_campaign_id'=>$campaign->id,'activity_type'=>'Local business citation'], ['target_website'=>'https://example.com','published_url'=>'https://example.com/demo-citation','link_type'=>'dofollow','verification_status'=>'verified','assigned_to'=>$executive->id,'live_on'=>'2026-10-12']);
        foreach (['organic_clicks'=>1250,'organic_impressions'=>31800,'organic_sessions'=>1090,'average_position'=>14.2,'average_ctr'=>3.93,'top_10_keywords'=>11] as $key=>$value) SeoPerformanceRecord::updateOrCreate(['seo_campaign_id'=>$campaign->id,'period_start'=>'2026-10-01','period_end'=>'2026-10-31','source'=>'search_console','metric_key'=>$key], ['metric_value'=>$value,'recorded_by'=>$executive->id,'recorded_at'=>now()]);
        SeoReport::updateOrCreate(['seo_campaign_id'=>$campaign->id,'title'=>'October 2026 SEO Performance Report'], ['seo_monthly_cycle_id'=>$cycle->id,'period_start'=>'2026-10-01','period_end'=>'2026-10-31','status'=>'published','observations'=>'Organic visibility, rankings and traffic improved. Continue content optimisation and local link acquisition next month.','snapshot'=>['keywords_tracked'=>4,'top_10_keywords'=>11,'activities_approved'=>2],'prepared_by'=>$executive->id,'reviewed_by'=>$manager->id,'reviewed_at'=>now(),'review_feedback'=>'Approved and published for the client.']);
        $this->command?->info("SEO demo campaign ready: /seo/campaigns/{$campaign->id}");
    }
}
