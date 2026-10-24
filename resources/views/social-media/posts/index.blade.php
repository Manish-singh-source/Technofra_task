@extends('layout.master')

@section('content')
<div class="page-wrapper"><div class="page-content social-media-page">
 <div class="page-heading"><div><h4 class="page-title">All Posts</h4><p class="page-subtitle">Manage every post from planning to publishing in one place.</p></div>@if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))<a class="btn btn-primary" href="{{ route('social-media.posts.create') }}"><i class="bx bx-plus me-1"></i>Create Post</a>@endif</div>
 <div class="card"><div class="card-body">
  <form class="row g-2 mb-4" method="GET"><div class="col-md-3"><select name="client_id" class="form-select"><option value="">All clients</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>{{ $client->name }}</option>@endforeach</select></div><div class="col-md-3"><select name="status" class="form-select"><option value="">All statuses</option>@foreach(\App\Models\SocialPost::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select></div><div class="col-md-auto"><button class="btn btn-outline-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button></div><div class="col-md-auto"><a class="btn btn-light w-100" href="{{ route('social-media.posts.index') }}">Reset</a></div></form>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Post</th><th>Client</th><th>Platforms</th><th>Planned</th><th>Status</th><th></th></tr></thead><tbody>@forelse($posts as $post)<tr><td><strong>{{ $post->title }}</strong><br><small class="text-muted">{{ ucwords($post->post_type) }}</small></td><td>{{ $post->client->name ?? '-' }}</td><td>{{ $post->platforms->pluck('platform')->join(', ') ?: '-' }}</td><td>{{ optional($post->planned_at)->format('d M Y H:i') ?: '-' }}</td><td><span class="badge bg-primary">{{ ucwords(str_replace('_',' ',$post->status)) }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('social-media.posts.show',$post) }}">Open</a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">No posts found.</td></tr>@endforelse</tbody></table></div>
  {{ $posts->links() }}
 </div></div>
</div></div>
@endsection
