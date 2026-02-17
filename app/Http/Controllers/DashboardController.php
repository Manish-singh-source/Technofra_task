<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ClientIssue;
use App\Services\NotificationService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with renewal statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get current date
        $today = Carbon::today();
        $weekFromNow = $today->copy()->addWeek();
        $fiveDaysFromNow = $today->copy()->addDays(5);

        // Calculate renewal statistics
        $totalRenewals = Service::count();

        // Renewals due this week (services ending within 7 days)
        $renewalsDueThisWeek = Service::whereBetween('end_date', [$today, $weekFromNow])->count();

        // Overdue renewals (services that ended before today)
        $overdueRenewals = Service::where('end_date', '<', $today)->count();

        // Combined critical renewals (overdue + upcoming in next 5 days)
        $criticalRenewals = Service::with(['client', 'vendor'])
            ->where(function($query) use ($today, $fiveDaysFromNow) {
                $query->where('end_date', '<', $today) // Overdue
                      ->orWhereBetween('end_date', [$today, $fiveDaysFromNow]); // Upcoming
            })
            ->orderByRaw('CASE WHEN end_date < ? THEN 0 ELSE 1 END, end_date ASC', [$today])
            ->get();

        // Get support tickets (client issues) - Recent 10
        $supportTickets = ClientIssue::with(['project', 'customer'])
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        // Get notification data
        $renewalNotifications = NotificationService::getUrgentNotifications(10);
        $notificationCounts = NotificationService::getNotificationCounts();
        $hasCriticalNotifications = NotificationService::hasCriticalNotifications();

        return view('index', compact(
            'totalRenewals',
            'renewalsDueThisWeek',
            'overdueRenewals',
            'criticalRenewals',
            'supportTickets',
            'renewalNotifications',
            'notificationCounts',
            'hasCriticalNotifications'
        ));
    }
}
