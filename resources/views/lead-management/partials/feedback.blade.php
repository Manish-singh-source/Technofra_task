@foreach (['success' => 'success', 'error' => 'danger'] as $key => $tone)
    @if (session($key))
        <div class="alert alert-{{ $tone }} alert-dismissible fade show" role="alert">{{ session($key) }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    @endif
@endforeach
@if ($errors->any())
    <div class="alert alert-danger" role="alert"><strong>Please check the following:</strong><ul class="mb-0 mt-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
