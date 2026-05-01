@section('title', __('Edit Page'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Pages', 'url' => route('admin.pages.index')],
            ['label' => 'Edit Page'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit Page')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

@section('js')
    @include('base::shared._tinymce')
    <script>
        $(document).ready(function () {
            var keywordsInput = document.querySelector('#meta_keywords');
            if (keywordsInput && typeof Tagify !== 'undefined') {
                new Tagify(keywordsInput);
            }
        });
    </script>
@endsection

@include('cms::admin.partials._seo_assets')

<x-admin-layout>
    <form method="POST" action="{{ route('admin.pages.update', $page->id) }}" enctype="multipart/form-data"
          id="cms-page-form">
        @csrf
        @method('PUT')

        <div class="row gx-5 gx-xl-10">
            {{-- ===================== MAIN COLUMN ===================== --}}
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">

                {{-- General --}}
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text text-primary fs-3 me-2"></i>
                                {{ __('General') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        {{-- Title --}}
                        <x-admin.form-group label="Title" name="title" required translatable>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="form-control form-control-solid"
                                   value="{{ old('title', $page->title) }}"
                                   placeholder="{{ __('About us') }}"/>
                        </x-admin.form-group>

                        {{-- Slug (read-only) --}}
                        <x-admin.form-group label="Url" name="slug">
                            <input type="text" id="slug" name="slug" value="{{ $page->slug }}"
                                   class="form-control form-control-solid" readonly/>
                        </x-admin.form-group>

                        {{-- Featured Image --}}
                        <x-admin.form-group label="Featured Image"
                                            helper="Recommended dimensions: 900px × 600px.">
                            <x-admin.image-input name="img" :preview="$page->image_link"/>
                        </x-admin.form-group>

                        {{-- Content --}}
                        <x-admin.form-group label="Content" name="content" required translatable>
                            <textarea name="content"
                                      class="form-control form-control-solid"
                                      id="tinymce">{!! old('content', $page->content) !!}</textarea>
                        </x-admin.form-group>
                    </div>
                </div>

                {{-- SEO Section --}}
                @include('cms::admin.partials._seo_section', [
                    'metaTitle' => old('meta_title', $page->meta_title),
                    'metaDescription' => old('meta_description', $page->meta_description),
                    'metaKeywords' => old('meta_keywords', $page->meta_keywords),
                    'metaImagePreview' => $page->meta_image_link,
                    'titleSource' => '#title',
                    'descSource' => '#meta_description',
                    'slugSource' => '#slug',
                    'baseUrl' => url('/') . '/',
                ])
            </div>

            {{-- ===================== ASIDE COLUMN ===================== --}}
            <div class="col-xxl-4 col-xl-4">
                @include('cms::admin.partials._status_aside', [
                    'isActive' => old('publish', $page->status) === 'Published',
                    'isFeatured' => (bool) old('featured', $page->featured),
                    'showTranslations' => true,
                    'updateTranslations' => (bool) old('update_translations', false),
                ])

                @include('cms::admin.partials._seo_aside', [
                    'hasFeaturedImage' => (bool) $page->image,
                    'hasMetaImage' => (bool) $page->meta_image,
                    'includeShortDescription' => false,
                ])
            </div>
        </div>

        {{-- Footer --}}
        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.pages.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" id="submit">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
