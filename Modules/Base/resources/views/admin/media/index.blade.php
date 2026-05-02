@section('title', __('Media Library'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Media Library'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Media Library')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <div class="card card-flush">
        <div class="card-header align-items-center">
            <div class="card-title">
                <h2>{{ __('Media Library') }}</h2>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" data-media-open="true">{{ __('Open Picker') }}</button>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">{{ __('Manage uploaded files and reuse them in content, SEO images, and editors.') }}</p>
        </div>
    </div>
</x-admin-layout>
