@props([
    'name',
    'label' => null,
    'accept' => null,
    'help' => null,
    'showImage' => null,
])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    
    <input 
        type="file" 
        class="form-control @error($name) is-invalid @enderror" 
        id="{{ $name }}" 
        name="{{ $name }}"
        accept="{{ $accept ?? '*/*' }}"
        {{ $attributes }}
    >
    
    @if($help)
        <small class="text-muted d-block mt-1">{{ $help }}</small>
    @endif
    
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    
    @if($showImage && file_exists(public_path($showImage)))
        <div class="mt-2">
            <img src="{{ asset($showImage) }}" alt="Preview" class="img-thumbnail" style="max-height: 100px;">
        </div>
    @endif
</div>
