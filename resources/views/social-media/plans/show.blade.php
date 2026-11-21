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

    @if($canManageContentPlans)
        <div class="d-flex justify-content-end mb-3"><a href="{{ route('social-media.posts.create', ['client_id' => $plan->client_id, 'plan_id' => $plan->id]) }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Add Content Item</a></div>
    @endif

    <div class="row g-3">
        @forelse($plan->posts as $post)
            @php
                $latestVersion = $post->versions->sortByDesc('version_number')->first();
                $preview = $latestVersion?->files->first(fn($file) => str_starts_with((string) $file->mime_type, 'image/'));
                $canSeeCreative = ! $isClient || in_array($post->status, ['pending_client_approval', 'client_changes_requested', 'client_approved', 'ready_for_publishing', 'scheduled', 'published', 'completed', 'rejected'], true);
            @endphp
            <div class="col-md-6 col-xl-4"><div class="card h-100 mb-0">
                @if($preview && $canSeeCreative)<img src="{{ route('social-media.posts.version-files.preview', [$post, $preview]) }}" alt="{{ $post->title }}" class="card-img-top" style="height:180px;object-fit:cover">
                @else<div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:180px"><i class="bx bx-image-alt fs-1"></i></div>@endif
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
        @empty
            <div class="col-12"><div class="card"><div class="empty-state"><i class="bx bx-calendar-x fs-2 d-block mb-2"></i>No content items have been added to this monthly plan yet.</div></div></div>
        @endforelse
    </div>
</div></div>
@endsection
