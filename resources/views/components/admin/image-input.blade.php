@props([
    'name' => 'img',
    'preview' => null,
    'required' => false,
    'accept' => '.png, .jpg, .jpeg, .webp',
    'size' => '125px',
])

@php
    $defaultPreview = asset('images/default.jpg');
    $previewUrl = $preview ?: $defaultPreview;
@endphp

<div class="image-input image-input-outline" data-kt-image-input="true"
     style="background-image: url('{{ $defaultPreview }}')">
    <div class="image-input-wrapper bgi-position-center"
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
