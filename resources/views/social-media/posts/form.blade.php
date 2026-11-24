@extends('layout.master')
@php($editing = isset($post))
@php($selectedFestival = $festival ?? null)
@php($backToPostsUrl = ($post->social_content_plan_id ?? request('plan_id')) ? route('social-media.plans.show', $post->social_content_plan_id ?? request('plan_id')) : route('social-media.posts.index'))
@section('content')
<div class="page-wrapper"><div class="page-content social-media-page"><div class="page-heading"><div><h4 class="page-title">{{ $editing ? 'Edit Post' : 'Create New Post' }}</h4><p class="page-subtitle">Add the brief, plan the date and assign the right people.</p></div><a href="{{ $backToPostsUrl }}" class="btn btn-light">Back to {{ ($post->social_content_plan_id ?? request('plan_id')) ? 'monthly plan' : 'posts' }}</a></div><div class="card"><div class="card-body">
@if($errors->any())<div class="alert alert-danger"><strong>Please fix the following:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if($clients->isEmpty())<div class="alert alert-warning">No active client users were found. Create a client first from Client Management, then return here.</div>@endif
<form method="POST" action="{{ $editing ? route('social-media.posts.update',$post) : route('social-media.posts.store') }}">@csrf @if($editing) @method('PUT') @endif
<input type="hidden" name="social_content_plan_id" value="{{ old('social_content_plan_id', $post->social_content_plan_id ?? request('plan_id')) }}">
<input type="hidden" name="social_festival_event_id" value="{{ old('social_festival_event_id', $post->social_festival_event_id ?? ($selectedFestival->id ?? request('festival_id'))) }}">
@if($selectedFestival && !$editing)<div class="alert alert-info py-2"><i class="bx bx-party me-1"></i>This post is being created for <strong>{{ $selectedFestival->name }}</strong> on {{ $selectedFestival->event_date?->format('d M Y') }}.</div>@endif
<div class="row g-3">
 <div class="col-md-6"><label class="form-label">Client *</label><select class="form-select" name="client_id" required><option value="">Select client</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected(old('client_id',$post->client_id ?? request('client_id')) == $client->id)>{{ $client->name }}</option>@endforeach</select></div>
 <div class="col-md-6"><label class="form-label">Project</label><select class="form-select" name="project_id"><option value="">No linked project</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected(old('project_id',$post->project_id ?? '') == $project->id)>{{ $project->project_name }}</option>@endforeach</select></div>
 <div class="col-md-6"><label class="form-label">Post title *</label><input class="form-control" name="title" value="{{ old('title',$post->title ?? ($selectedFestival->name ?? '')) }}" required></div>
 <div class="col-md-3"><label class="form-label">Category</label><input class="form-control" name="content_category" value="{{ old('content_category',$post->content_category ?? ($selectedFestival->category ?? '')) }}" placeholder="Festival, Promotional..."></div>
 <div class="col-md-3"><label class="form-label">Post type *</label><select class="form-select" name="post_type">@foreach(['image','carousel','reel','video','story','other'] as $type)<option value="{{ $type }}" @selected(old('post_type',$post->post_type ?? 'image') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
 <div class="col-md-6"><label class="form-label">Platforms</label><input class="form-control" name="platforms[]" value="{{ old('platforms.0',isset($post) ? $post->platforms->pluck('platform')->join(', ') : ($selectedFestival ? 'Instagram' : '')) }}" placeholder="Facebook, Instagram (one or more; currently one field)"></div>
 <div class="col-md-3"><label class="form-label">Priority</label><select class="form-select" name="priority">@foreach(['low','medium','high','urgent'] as $p)<option value="{{ $p }}" @selected(old('priority',$post->priority ?? 'medium') === $p)>{{ ucfirst($p) }}</option>@endforeach</select></div>
 <div class="col-md-3"><label class="form-label">Planned date/time</label><input type="datetime-local" class="form-control" name="planned_at" value="{{ old('planned_at', isset($post->planned_at) ? $post->planned_at->format('Y-m-d\\TH:i') : (request('planned_at') ? \Carbon\Carbon::parse(request('planned_at'))->format('Y-m-d\\TH:i') : '')) }}"></div>
 <div class="col-md-6"><label class="form-label">Internal deadline</label><input type="datetime-local" class="form-control" name="internal_deadline_at" value="{{ old('internal_deadline_at',isset($post->internal_deadline_at) ? $post->internal_deadline_at->format('Y-m-d\\TH:i') : '') }}"></div><div class="col-md-6"><label class="form-label">Client approval deadline</label><input type="datetime-local" class="form-control" name="client_approval_deadline_at" value="{{ old('client_approval_deadline_at',isset($post->client_approval_deadline_at) ? $post->client_approval_deadline_at->format('Y-m-d\\TH:i') : '') }}"></div>
 <div class="col-12"><label class="form-label">Creative brief</label><textarea class="form-control" name="creative_brief" rows="3">{{ old('creative_brief',$post->creative_brief ?? ($selectedFestival->description ?? '')) }}</textarea></div><div class="col-12"><label class="form-label">Caption</label><textarea class="form-control" name="caption" rows="3">{{ old('caption',$post->caption ?? '') }}</textarea></div><div class="col-md-6"><label class="form-label">Hashtags</label><textarea class="form-control" name="hashtags">{{ old('hashtags',$post->hashtags ?? '') }}</textarea></div><div class="col-md-6"><label class="form-label">Reference links</label><textarea class="form-control" name="reference_links">{{ old('reference_links',$post->reference_links ?? '') }}</textarea></div>
  <div class="col-12"><label class="form-label">Staff assignments</label><div class="row g-2">@foreach(['designer'=>'Designer','writer'=>'Content Writer','reviewer'=>'Reviewer','publisher'=>'Publisher'] as $role=>$label)
    <div class="col-md-3"><label class="small d-block">{{ $label }}</label><div class="dropdown assignment-dropdown"><button type="button" class="btn btn-outline-secondary dropdown-toggle w-100 text-start" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><span class="assignment-label">Select staff</span></button><div class="dropdown-menu w-100 p-2" style="max-height: 240px; overflow-y: auto;">@forelse($staff as $member)<label class="dropdown-item d-flex align-items-center gap-2 rounded"><input class="form-check-input mt-0 assignment-checkbox" type="checkbox" name="assignments[{{ $role }}][]" value="{{ $member->id }}" @checked(in_array((string) $member->id, array_map('strval', (array) old('assignments.'.$role, isset($post) ? $post->assignments->where('assignment_role', $role)->pluck('user_id')->all() : [])), true))><span>{{ $member->name }}</span></label>@empty<span class="dropdown-item-text text-muted">No staff available</span>@endforelse</div></div><small class="text-muted">Select one or more staff members.</small></div>
 @endforeach</div></div>
</div><div class="mt-4 d-flex flex-wrap gap-2">@if(!$editing)<button class="btn btn-outline-primary" name="save_as_draft" value="1">Save as Draft</button>@endif<button class="btn btn-primary">{{ $editing ? 'Update Post' : 'Create & Assign' }}</button><a class="btn btn-light" href="{{ route('social-media.posts.index') }}">Cancel</a></div></form>
</div></div></div></div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.assignment-dropdown').forEach(function (dropdown) {
    const label = dropdown.querySelector('.assignment-label');
    const checkboxes = dropdown.querySelectorAll('.assignment-checkbox');
    const updateLabel = function () {
        const selected = Array.from(checkboxes).filter(function (checkbox) { return checkbox.checked; });
        label.textContent = selected.length ? selected.length + ' selected' : 'Select staff';
    };
    checkboxes.forEach(function (checkbox) { checkbox.addEventListener('change', updateLabel); });
    updateLabel();
});
</script>
@endpush
