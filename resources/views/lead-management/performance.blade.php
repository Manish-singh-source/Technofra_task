@extends('layout.master')
@section('content')
<div class="page-wrapper"><div class="page-content">
<div class="row g-3 mb-3">
<div class="col-md-2"><div class="card"><div class="card-body"><small>Total Leads</small><h5>{{ $totalLeads }}</h5></div></div></div>
<div class="col-md-2"><div class="card"><div class="card-body"><small>Converted</small><h5>{{ $convertedLeads }}</h5></div></div></div>
<div class="col-md-2"><div class="card"><div class="card-body"><small>Lost</small><h5>{{ $lostLeads }}</h5></div></div></div>
<div class="col-md-2"><div class="card"><div class="card-body"><small>Pending Followups</small><h5>{{ $pendingFollowups }}</h5></div></div></div>
<div class="col-md-2"><div class="card"><div class="card-body"><small>Today's Followups</small><h5>{{ $todayFollowups }}</h5></div></div></div>
<div class="col-md-2"><div class="card"><div class="card-body"><small>Conversion Rate</small><h5>{{ $conversionRate }}%</h5></div></div></div>
<div class="col-md-2"><div class="card"><div class="card-body"><small>Lost Rate</small><h5>{{ $lostRate }}%</h5></div></div></div>
<div class="col-md-2"><div class="card"><div class="card-body"><small>Monthly Won</small><h5>{{ $monthlyWonLeads }}</h5></div></div></div>
<div class="col-md-2"><div class="card"><div class="card-body"><small>Monthly Lost</small><h5>{{ $monthlyLostLeads }}</h5></div></div></div>
</div>
<div class="card mb-3"><div class="card-body">
<h6 class="mb-2">Leads Per Status</h6>
<div class="d-flex flex-wrap gap-2">
@foreach($leadsPerStatus as $status => $total)
<span class="badge bg-secondary">{{ strtoupper((string)$status) }}: {{ $total }}</span>
@endforeach
</div>
</div></div>
<div class="card"><div class="card-body">
<h5 class="mb-3">Staff Performance</h5>
<div class="table-responsive"><table class="table table-bordered">
<thead><tr><th>Staff</th><th>Assigned Leads</th><th>Conversions</th><th>Lost</th><th>Pending Followups</th><th>Total Calls</th><th>Total Meetings</th><th>Conversion Rate</th></tr></thead>
<tbody>
@forelse($staffStats as $stat)
<tr>
<td>{{ optional($stat->staff)->name ?? '-' }}</td>
<td>{{ $stat->total_leads }}</td>
<td>{{ $stat->converted_leads }}</td>
<td>{{ $stat->lost_leads }}</td>
<td>{{ $stat->pending_followups }}</td>
<td>{{ $stat->total_calls }}</td>
<td>{{ $stat->total_meetings }}</td>
<td>{{ $stat->conversion_rate }}%</td>
</tr>
@empty
<tr><td colspan="8" class="text-center">No staff stats found.</td></tr>
@endforelse
</tbody></table></div>
</div></div>
</div></div>
@endsection
