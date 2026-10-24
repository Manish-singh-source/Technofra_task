@extends('layout.master')
@section('content')
<div class="page-wrapper"><div class="page-content"><h4 class="mb-3">{{ $title }}</h4><div class="card"><div class="card-body"><table class="table"><thead><tr><th>Post</th><th>Client</th><th>Status</th><th></th></tr></thead><tbody>@forelse($posts as $post)<tr><td>{{ $post->title }}</td><td>{{ $post->client->name ?? '-' }}</td><td>{{ ucwords(str_replace('_',' ',$post->status)) }}</td><td><a class="btn btn-sm btn-primary" href="{{ route('social-media.posts.show',$post) }}">Review</a></td></tr>@empty<tr><td colspan="4" class="text-center">Nothing in this queue.</td></tr>@endforelse</tbody></table>{{ $posts->links() }}</div></div></div></div>
@endsection
