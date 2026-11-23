@extends('layout.master')

@section('content')
<div class="page-wrapper"><div class="page-content social-media-page">
    <div class="page-heading">
        <div>
            <h4 class="page-title">{{ $plan->title ?: 'Monthly Content Plan' }}</h4>
            <p class="page-subtitle">{{ $plan->client?->name }} · {{ \Carbon\Carbon::create($plan->plan_year, $plan->plan_month, 1)->format('F Y') }}</p>
        </div>
        <a href="{{ route('social-media.plans.index') }}" class="btn btn-light"><i class="bx bx-left-arrow-alt me-1"></i>Back to Plans</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4"><div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div><h6 class="mb-1">Plan Brief</h6><p class="mb-0 text-muted">{{ $plan->notes ?: 'No plan notes added yet.' }}</p></div>
            <span class="badge {{ $plan->status === 'client_approved' ? 'bg-success' : (in_array($plan->status, ['changes_requested', 'not_required'], true) ? 'bg-danger' : ($plan->status === 'pending_client_review' ? 'bg-warning text-dark' : 'bg-secondary')) }}">{{ ucwords(str_replace('_', ' ', $plan->status)) }}</span>
        </div>
        @if($plan->client_feedback)<div class="alert alert-warning mb-0 mt-3"><strong>Client feedback:</strong> {{ $plan->client_feedback }}</div>@endif
    </div></div>

    @if($canManageContentPlans && $planFestivals->isNotEmpty())
        <div class="card mb-4"><div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div><h6 class="mb-1"><i class="bx bx-party me-1"></i>Festivals in this month</h6><p class="mb-0 text-muted small">Select a festival to create a pre-filled social media post for this plan.</p></div>
                <a href="{{ route('social-media.plans.index', ['tab' => 'festivals']) }}" class="btn btn-sm btn-light">Manage Festival Master</a>
            </div>
            <div class="row g-2">
                @foreach($planFestivals as $festival)
                    @php($linkedPost = $plan->posts->firstWhere('social_festival_event_id', $festival->id))
                    <div class="col-md-6 col-xl-4"><div class="border rounded p-3 h-100 d-flex justify-content-between gap-2">
                        <div><strong>{{ $festival->name }}</strong><small class="d-block text-muted">{{ $festival->event_date?->format('d M Y') }} · {{ $festival->category }}</small></div>
                        @if($linkedPost)
                            <span class="badge bg-success align-self-start">Post created</span>
                        @else
                            <a class="btn btn-sm btn-outline-primary align-self-start" href="{{ route('social-media.posts.create', ['client_id' => $plan->client_id, 'plan_id' => $plan->id, 'festival_id' => $festival->id, 'planned_at' => $festival->event_date?->format('Y-m-d').' 10:00']) }}">Create post</a>
                        @endif
                    </div></div>
                @endforeach
            </div>
        </div></div>
    @endif

    @if($canManageContentPlans)
        <div class="d-flex justify-content-end mb-3"><a href="{{ route('social-media.posts.create', ['client_id' => $plan->client_id, 'plan_id' => $plan->id]) }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Add Content Item</a></div>
    @endif

    <div class="row g-3">
        @if($plan->posts->isNotEmpty())
            @foreach($plan->posts as $post)
            <div class="col-md-6 col-xl-4"><div class="card h-100 mb-0">
                @if($post->can_see_creative && $post->preview_file)
                    <img src="{{ route('social-media.posts.version-files.preview', [$post, $post->preview_file]) }}" alt="{{ $post->title }}" class="card-img-top" style="height:180px;object-fit:cover">
                @else
                    <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:180px"><i class="bx bx-image-alt fs-1"></i></div>
                @endif
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between gap-2"><h6 class="mb-1">{{ $post->title }}</h6><span class="badge bg-light text-dark border">{{ ucwords(str_replace('_', ' ', $post->status)) }}</span></div>
                    <small class="text-muted mb-2"><i class="bx bx-calendar me-1"></i>{{ $post->planned_at?->format('d M Y, h:i A') ?: 'Date not planned' }}</small>
                    <p class="small text-muted mb-2">{{ \Illuminate\Support\Str::limit($post->creative_brief ?: $post->caption ?: 'No description added.', 145) }}</p>
                    <div class="small text-muted mb-3">{{ $post->platforms->pluck('platform')->join(', ') ?: 'Platform not selected' }}</div>
                    @if(in_array($post->id, $visiblePostIds, true))
                        <a href="{{ route('social-media.posts.show', $post) }}" class="btn btn-outline-primary btn-sm mt-auto">View details</a>
                    @endif
                </div>
            </div></div>
            @endforeach
        @else
            <div class="col-12"><div class="card"><div class="empty-state"><i class="bx bx-calendar-x fs-2 d-block mb-2"></i>No content items have been added to this monthly plan yet.</div></div></div>
        @endif
    </div>
</div></div>
@endsection
