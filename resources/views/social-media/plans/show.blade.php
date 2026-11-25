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
        <div class="card mb-4"><div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div><h6 class="mb-1">Add planned posts</h6><small class="text-muted">Create one post manually or upload multiple posts from Excel.</small></div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importPlanPostsModal"><i class="bx bx-upload me-1"></i>Import Excel</button>
                    <a href="{{ route('social-media.posts.create', ['client_id' => $plan->client_id, 'plan_id' => $plan->id]) }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i>Create Post</a>
                </div>
            </div>
        </div></div>

        <div class="modal fade" id="importPlanPostsModal" tabindex="-1" aria-labelledby="importPlanPostsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="importPlanPostsModalLabel"><i class="bx bx-upload me-1"></i>Import posts from Excel</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <form method="POST" action="{{ route('social-media.plans.posts.import', $plan) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <label class="form-label fw-semibold">Choose Excel file</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="small text-muted mt-2">Required columns: date, type, category, topic / creative direction.</div>
                            <div class="small text-muted">Dates must be within {{ \Carbon\Carbon::create($plan->plan_year, $plan->plan_month, 1)->format('F Y') }}.</div>
                            <a href="{{ route('social-media.plans.posts.template', $plan) }}" class="btn btn-outline-secondary btn-sm mt-3"><i class="bx bx-download me-1"></i>Download sample Excel</a>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="bx bx-upload me-1"></i>Import Excel</button></div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        @if($plan->posts->isNotEmpty())
            <div class="table-responsive"><table class="table align-middle table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>Date</th><th>Type</th><th>Category</th><th>Topic / Creative Direction</th><th>Platform</th><th>Status</th><th class="text-end">Action</th>
                </tr></thead>
                <tbody>
                    @foreach($plan->posts as $post)
                        <tr>
                            <td class="text-nowrap">{{ $post->planned_at?->format('d M Y, h:i A') ?: 'Not planned' }}</td>
                            <td class="text-nowrap">{{ ucfirst($post->post_type) }}</td>
                            <td>{{ $post->content_category ?: '-' }}</td>
                            <td><strong>{{ $post->title }}</strong><small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($post->creative_brief ?: $post->caption ?: 'No description added.', 150) }}</small></td>
                            <td class="text-nowrap">{{ $post->platforms->pluck('platform')->join(', ') ?: '-' }}</td>
                            <td class="text-nowrap"><span class="badge bg-light text-dark border">{{ ucwords(str_replace('_', ' ', $post->status)) }}</span></td>
                            <td class="text-end">
                                @if(! $isClient || in_array($post->id, $visiblePostIds, true))
                                    <a href="{{ route('social-media.posts.show', $post) }}" class="btn btn-outline-primary btn-sm">View details</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @else
            <div class="empty-state"><i class="bx bx-calendar-x fs-2 d-block mb-2"></i>No content items have been added to this monthly plan yet.</div>
        @endif
    </div>
</div></div>
@endsection
