@props([
    'name' => 'img',
    'preview' => null,
    'required' => false,
    'accept' => '.png, .jpg, .jpeg, .webp',
    'size' => '125px',
    'mediaInputName' => null,
])

@php
    $defaultPreview = asset('images/default.jpg');
    $previewUrl = $preview ?: $defaultPreview;
    $mediaField = $mediaInputName ?: $name . '_media_path';
    $previewId = 'media-preview-' . md5($name . $size . ($preview ?? ''));
@endphp

<div class="image-input image-input-outline" data-kt-image-input="true"
     style="background-image: url('{{ $defaultPreview }}')">
    <div class="image-input-wrapper bgi-position-center"
         id="{{ $previewId }}"
         style="width: {{ $size }}; height: {{ $size }}; background-size: cover; background-image: url('{{ $previewUrl }}')"></div>

    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-white shadow"
           data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ __('Change') }}">
        <i class="bi bi-pencil-fill fs-7"></i>
        <input type="file" name="{{ $name }}" accept="{{ $accept }}" @if($required) required @endif/>
        <input type="hidden" name="{{ $name }}_remove"/>
    </label>

    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-white shadow"
          data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ __('Cancel') }}">
        <i class="bi bi-x fs-2"></i>
    </span>

    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-white shadow"
          data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ __('Remove') }}">
        <i class="bi bi-x fs-2"></i>
    </span>
</div>
<div class="mt-3 d-flex gap-2">
    <button type="button"
            class="btn btn-light-primary btn-sm"
            data-media-picker-target="[name='{{ $mediaField }}']"
            data-media-preview-target="#{{ $previewId }}">
        <i class="bi bi-images me-1"></i>{{ __('Choose from library') }}
    </button>
</div>
<input type="hidden" name="{{ $mediaField }}" value="">
