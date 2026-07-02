@extends('layouts.app')

@section('title', $document['title'])

@section('content')
    <div class="mx-auto max-w-4xl bg-white p-6 sm:p-8">
        <h1 class="text-2xl font-semibold text-slate-900">{{ $document['title'] }}</h1>

        @if(!empty($document['updated_at']))
            <p class="mt-2 text-sm text-slate-500">Last updated: {{ $document['updated_at'] }}</p>
        @endif

        <div class="mt-6 prose prose-slate max-w-none">
            {!! \Illuminate\Support\Str::markdown($document['content'] ?: '_No legal content is currently available._') !!}
        </div>
    </div>
@endsection
