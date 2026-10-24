@extends('layout.master')

@push('styles')
<style>
.social-overview-head{background:linear-gradient(110deg,#f4f8ff,#fff);border:1px solid #e7eef9;border-radius:16px;padding:22px 24px}.social-overview-head h4{font-weight:700;color:#172b4d}.social-overview-head .btn-primary{border-radius:9px;padding:.6rem 1rem}.social-dashboard .card{border:0;border-radius:16px;box-shadow:0 4px 18px rgba(27,59,107,.07)}.social-dashboard .card-body{padding:1.35rem}.social-dashboard .table thead th{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#75839b;background:#f7f9fc;border:0}.social-dashboard .table td{border-color:#edf1f6;padding:.85rem .75rem}.social-dashboard .form-select,.social-dashboard .form-control{border-color:#dce5f2;border-radius:8px}.social-dashboard .radius-10{position:relative;overflow:hidden}.social-dashboard .radius-10:before{content:'';position:absolute;left:0;top:0;width:4px;height:100%;background:#1674ea}.social-dashboard .radius-10 h3{color:#1a315a;font-weight:700}.social-dashboard .row-cols-xl-4 .col:nth-child(2n) .radius-10:before{background:#8b5cf6}
</style>
@endpush

@section('content')
<div class="page-wrapper"><div class="page-content social-media-page social-dashboard">
    <div class="social-overview-head d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div><h4 class="mb-1">Social Media Overview</h4><p class="text-muted mb-0">Plan, review, approve and publish client content.</p></div>
        @if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
            <a href="{{ route('social-media.posts.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> Create Post</a>
        @endif
    </div>
    @if(in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
    <div class="card"><div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><div><h5 class="mb-1">My Monthly Content Plan</h5><p class="text-muted mb-0">Your planned social-media content for the selected month.</p></div><form method="GET"><input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"></form></div>
        <div class="row text-center mb-3"><div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Total Planned Posts</small><h3 class="mb-0">{{ $monthPosts->count() }}</h3></div></div><div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Awaiting Approval</small><h3 class="mb-0">{{ $monthPosts->where('status','pending_client_approval')->count() }}</h3></div></div><div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Approved</small><h3 class="mb-0">{{ $monthPosts->whereIn('status',['client_approved','ready_for_publishing','scheduled','published','completed'])->count() }}</h3></div></div><div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Published</small><h3 class="mb-0">{{ $monthPosts->whereIn('status',['published','completed'])->count() }}</h3></div></div></div>
        @if($monthlyPlan)<p class="small text-muted">Plan: {{ $monthlyPlan->title ?: \Carbon\Carbon::create($monthlyPlan->plan_year,$monthlyPlan->plan_month,1)->format('F Y').' Content Plan' }}</p>@endif
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Publishing Date</th><th>Post</th><th>Platforms</th><th>Status</th><th></th></tr></thead><tbody>@forelse($monthPosts as $post)<tr><td>{{ optional($post->planned_at)->format('d M, D') ?: 'Date to be confirmed' }}</td><td>{{ $post->title }}</td><td>{{ $post->platforms->pluck('platform')->join(', ') ?: '-' }}</td><td><span class="badge bg-primary">{{ ucwords(str_replace('_',' ',$post->status)) }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('social-media.posts.show',$post) }}">View</a></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-3">No posts planned for this month yet.</td></tr>@endforelse</tbody></table></div>
    </div></div>
    @endif
    @if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
    <div class="card"><div class="card-body"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><div><h5 class="mb-1">Client Monthly Content Snapshot</h5><p class="text-muted mb-0">Select a client and month to review planned publishing work.</p></div><form method="GET" class="d-flex gap-2"><select name="client_id" class="form-select"><option value="">All clients</option>@foreach($dashboardClients as $client)<option value="{{ $client->id }}" @selected(request('client_id')==$client->id)>{{ $client->name }}</option>@endforeach</select><input type="month" name="month" value="{{ $selectedMonth }}" class="form-control"><button class="btn btn-outline-primary">View</button></form></div><div class="row text-center mb-3"><div class="col-md-3"><div class="border rounded p-2"><small>Planned</small><h4>{{ $monthPosts->count() }}</h4></div></div><div class="col-md-3"><div class="border rounded p-2"><small>Client Approval</small><h4>{{ $monthPosts->where('status','pending_client_approval')->count() }}</h4></div></div><div class="col-md-3"><div class="border rounded p-2"><small>Scheduled</small><h4>{{ $monthPosts->where('status','scheduled')->count() }}</h4></div></div><div class="col-md-3"><div class="border rounded p-2"><small>Published</small><h4>{{ $monthPosts->whereIn('status',['published','completed'])->count() }}</h4></div></div></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Date</th><th>Client</th><th>Post</th><th>Platforms</th><th>Status</th></tr></thead><tbody>@forelse($monthPosts as $post)<tr><td>{{ optional($post->planned_at)->format('d M') }}</td><td>{{ $post->client->name ?? '-' }}</td><td><a href="{{ route('social-media.posts.show',$post) }}">{{ $post->title }}</a></td><td>{{ $post->platforms->pluck('platform')->join(', ') }}</td><td>{{ ucwords(str_replace('_',' ',$post->status)) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">No planned posts for selected filters.</td></tr>@endforelse</tbody></table></div></div></div>
    @endif
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
        @foreach(['planned'=>'Planned','in_progress'=>'In Design','internal_review'=>'Internal Review','pending_client_approval'=>'Client Approvals','client_changes_requested'=>'Changes Requested','scheduled'=>'Scheduled','published'=>'Published','rejected'=>'Rejected'] as $key => $label)
            <div class="col"><div class="card radius-10"><div class="card-body"><p class="mb-1 text-muted">{{ $label }}</p><h3 class="mb-0">{{ $counts[$key] ?? 0 }}</h3></div></div></div>
        @endforeach
    </div>
    <div class="card"><div class="card-body d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="{{ route('social-media.posts.index') }}">All Posts</a>
        <a class="btn btn-outline-secondary" href="{{ route('social-media.posts.index', ['status' => 'pending_client_approval']) }}">Pending Approvals</a>
        <a class="btn btn-outline-success" href="{{ route('social-media.posts.index', ['status' => 'scheduled']) }}">Publishing Tracker</a>
    </div></div>
    @if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
    <div class="card"><div class="card-body"><div class="d-flex justify-content-between"><h5>My Assigned Work</h5><a href="{{ route('social-media.my-assigned') }}">View all</a></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Post</th><th>Client</th><th>Status</th><th></th></tr></thead><tbody>@forelse($assignedPosts->take(5) as $post)<tr><td>{{ $post->title }}</td><td>{{ $post->client->name ?? '-' }}</td><td>{{ ucwords(str_replace('_',' ',$post->status)) }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('social-media.posts.show',$post) }}">Open</a></td></tr>@empty<tr><td colspan="4" class="text-muted">No assigned posts.</td></tr>@endforelse</tbody></table></div></div></div>
    @endif
</div></div>
@endsection
