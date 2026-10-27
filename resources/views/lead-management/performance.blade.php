@extends('layout.master')

@push('styles')
<style>
.lead-performance .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: #fff;
    -webkit-overflow-scrolling: touch;
}
.lead-performance .table-responsive::-webkit-scrollbar { height: 7px; }
.lead-performance .table-responsive::-webkit-scrollbar-track { background: #f1f3f7; border-radius: 4px; }
.lead-performance .table-responsive::-webkit-scrollbar-thumb { background: #cbd3e1; border-radius: 4px; }
.lead-performance .perf-table {
    width: 100% !important;
    min-width: 900px;
    table-layout: fixed;
    margin-bottom: 0;
    font-size: 13px;
}
.lead-performance .perf-table th:nth-child(1), .lead-performance .perf-table td:nth-child(1) { width: 22%; }
.lead-performance .perf-table th:nth-child(2), .lead-performance .perf-table td:nth-child(2) { width: 13%; }
.lead-performance .perf-table th:nth-child(3), .lead-performance .perf-table td:nth-child(3) { width: 11%; }
.lead-performance .perf-table th:nth-child(4), .lead-performance .perf-table td:nth-child(4) { width: 9%; }
.lead-performance .perf-table th:nth-child(5), .lead-performance .perf-table td:nth-child(5) { width: 14%; }
.lead-performance .perf-table th:nth-child(6), .lead-performance .perf-table td:nth-child(6) { width: 10%; }
.lead-performance .perf-table th:nth-child(7), .lead-performance .perf-table td:nth-child(7) { width: 10%; }
.lead-performance .perf-table th:nth-child(8), .lead-performance .perf-table td:nth-child(8) { width: 11%; }
.lead-performance .perf-table th {
    background: #f8f9fa;
    color: #5c6879;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 10px 8px;
    white-space: nowrap;
    border-bottom: 1px solid #dee2e6;
}
.lead-performance .perf-table td {
    padding: 9px 8px;
    vertical-align: middle;
    overflow-wrap: anywhere;
}
.lead-performance .stat-card {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    transition: all .15s ease;
}
.lead-performance .stat-card .card-body {
    padding: 12px 14px;
}
.lead-performance .stat-card small {
    color: #64748b;
    font-size: 11px;
    font-weight: 500;
    display: block;
}
.lead-performance .stat-card h5 {
    font-size: 20px;
    margin-bottom: 0;
    margin-top: 4px;
    font-weight: 700;
    color: #1e293b;
}
</style>
@endpush

@section('content')
<div class="page-wrapper"><div class="page-content lead-performance">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ route('lead-management.index') }}" class="text-decoration-none small text-muted"><i class="bx bx-arrow-back me-1"></i>Back to Lead Management</a>
        <h1 class="h4 mb-0 mt-1 fw-bold text-dark">Staff & Pipeline Performance</h1>
    </div>
</div>

<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-2 mb-3">
    <div class="col"><div class="card stat-card"><div class="card-body"><small>Total Leads</small><h5>{{ $totalLeads }}</h5></div></div></div>
    <div class="col"><div class="card stat-card"><div class="card-body"><small>Converted</small><h5 class="text-success">{{ $convertedLeads }}</h5></div></div></div>
    <div class="col"><div class="card stat-card"><div class="card-body"><small>Lost</small><h5 class="text-danger">{{ $lostLeads }}</h5></div></div></div>
    <div class="col"><div class="card stat-card"><div class="card-body"><small>Pending Followups</small><h5 class="text-warning">{{ $pendingFollowups }}</h5></div></div></div>
    <div class="col"><div class="card stat-card"><div class="card-body"><small>Today's Followups</small><h5 class="text-primary">{{ $todayFollowups }}</h5></div></div></div>
    <div class="col"><div class="card stat-card"><div class="card-body"><small>Conversion Rate</small><h5 class="text-success">{{ $conversionRate }}%</h5></div></div></div>
    <div class="col"><div class="card stat-card"><div class="card-body"><small>Lost Rate</small><h5 class="text-secondary">{{ $lostRate }}%</h5></div></div></div>
    <div class="col"><div class="card stat-card"><div class="card-body"><small>Monthly Converted</small><h5 class="text-success">{{ $monthlyWonLeads }}</h5></div></div></div>
    <div class="col"><div class="card stat-card"><div class="card-body"><small>Monthly Lost</small><h5 class="text-danger">{{ $monthlyLostLeads }}</h5></div></div></div>
</div>

<div class="card mb-3"><div class="card-body p-3">
<h6 class="mb-2 fs-6 fw-semibold text-secondary">Leads Per Status</h6>
<div class="d-flex flex-wrap gap-2">
@foreach($leadsPerStatus as $status => $total)
<span class="badge bg-light text-dark border">{{ strtoupper((string)$status) }}: <strong class="text-primary">{{ $total }}</strong></span>
@endforeach
</div>
</div></div>

<div class="card"><div class="card-body p-3 p-lg-4">
<h5 class="mb-3 fs-6 fw-bold">Staff Performance Breakdown</h5>
<div class="table-responsive"><table class="table table-hover perf-table">
<thead><tr><th>Staff</th><th class="text-center">Assigned Leads</th><th class="text-center">Conversions</th><th class="text-center">Lost</th><th class="text-center">Pending Followups</th><th class="text-center">Total Calls</th><th class="text-center">Total Meetings</th><th class="text-center">Conversion Rate</th></tr></thead>
<tbody>
@forelse($staffStats as $stat)
<tr>
<td class="fw-semibold text-dark">{{ optional($stat->staff)->name ?? '-' }}</td>
<td class="text-center">{{ $stat->total_leads }}</td>
<td class="text-center text-success fw-semibold">{{ $stat->converted_leads }}</td>
<td class="text-center text-danger">{{ $stat->lost_leads }}</td>
<td class="text-center text-warning fw-semibold">{{ $stat->pending_followups }}</td>
<td class="text-center">{{ $stat->total_calls }}</td>
<td class="text-center">{{ $stat->total_meetings }}</td>
<td class="text-center fw-bold text-primary">{{ $stat->conversion_rate }}%</td>
</tr>
@empty
<tr><td colspan="8" class="text-center text-muted py-3">No staff stats found.</td></tr>
@endforelse
</tbody></table></div>
</div></div>
</div></div>
@endsection
