<?php

namespace Tests\Unit;

use App\Services\LeadManagement\LeadWorklistService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class LeadWorklistServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_closed_leads_never_appear_as_overdue_even_with_old_dates(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-17 12:00:00'));
        $service = new LeadWorklistService();
        foreach (LeadWorklistService::CLOSED as $status) {
            $this->assertSame('closed', $service->followupState(Carbon::parse('2026-10-16 09:00:00'), $status));
        }
    }

    public function test_worklist_distinguishes_overdue_today_future_and_unscheduled(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-17 12:00:00'));
        $service = new LeadWorklistService();
        $this->assertSame('overdue', $service->followupState(Carbon::parse('2026-10-17 11:59:00'), 'new'));
        $this->assertSame('today', $service->followupState(Carbon::parse('2026-10-17 14:00:00'), 'new'));
        $this->assertSame('upcoming', $service->followupState(Carbon::parse('2026-10-18 09:00:00'), 'qualified'));
        $this->assertSame('unscheduled', $service->followupState(null, 'new'));
    }

    public function test_today_includes_earlier_today_but_excludes_closed_and_yesterday(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-17 12:00:00'));
        $service = new LeadWorklistService();
        $rows = collect([
            ['followup_state' => 'overdue', 'next_followup_at' => Carbon::parse('2026-10-17 09:00:00')],
            ['followup_state' => 'today', 'next_followup_at' => Carbon::parse('2026-10-17 16:00:00')],
            ['followup_state' => 'overdue', 'next_followup_at' => Carbon::parse('2026-10-16 16:00:00')],
            ['followup_state' => 'closed', 'next_followup_at' => Carbon::parse('2026-10-17 09:00:00')],
        ]);
        $this->assertSame(2, $service->summary($rows)['today']);
        $this->assertSame(2, $rows->filter(fn ($row) => $service->matchesWorkFilter($row, 'today'))->count());
        $this->assertSame(2, $service->summary($rows)['overdue']);
    }
}
