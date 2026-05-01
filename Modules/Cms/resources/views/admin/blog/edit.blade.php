@section('title', __('Edit Blog'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'Blogs', 'url' => route('admin.blogs.index')],
            ['label' => 'Edit Blog'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit Blog')" :breadcrumbItems="$breadcrumbItems"/>
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
    <form method="POST" action="{{ route('admin.blogs.update', $blog->id) }}" enctype="multipart/form-data"
          id="cms-blog-form">
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
                                <i class="bi bi-journal-text text-primary fs-3 me-2"></i>
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
                                   value="{{ old('title', $blog->title) }}"
                                   placeholder="{{ __('Title') }}"/>
                        </x-admin.form-group>

                        {{-- Slug (read-only) --}}
                        <x-admin.form-group label="Url" name="slug">
                            <input type="text" id="slug" name="slug" value="{{ $blog->slug }}"
                                   class="form-control form-control-solid" readonly/>
                        </x-admin.form-group>

                        {{-- Featured Image --}}
                        <x-admin.form-group label="Featured Image"
                                            helper="Recommended dimensions: 900px × 600px.">
                            <x-admin.image-input name="img" :preview="$blog->image_link"/>
                        </x-admin.form-group>

                        {{-- Short Description --}}
                        <x-admin.seo-field
                            name="description"
                            label="Short Description"
                            tip="Write a clear, concise summary (120–160 chars) — it may be shown in search results."
                            optimal-label="Optimal: 120–160 characters"
                            :value="old('description', $blog->description)"
                            type="textarea"
                            placeholder="{{ __('Short Description') }}..."
                            translatable
                            required
                            :optimal-min="120"
                            :optimal-max="160"
                            :hard-max="500"
                            unit="characters"
                        />

                        {{-- Content --}}
                        <x-admin.form-group label="Content" name="content" required translatable>
                            <textarea name="content"
                                      class="form-control form-control-solid"
                                      id="tinymce">{!! old('content', $blog->content) !!}</textarea>
                        </x-admin.form-group>
                    </div>
                </div>

                {{-- SEO Section --}}
                @include('cms::admin.partials._seo_section', [
                    'metaTitle' => old('meta_title', $blog->meta_title),
                    'metaDescription' => old('meta_description', $blog->meta_description),
                    'metaKeywords' => old('meta_keywords', $blog->meta_keywords),
                    'metaImagePreview' => $blog->meta_image_link,
                    'titleSource' => '#title',
                    'descSource' => '#meta_description',
                    'slugSource' => '#slug',
                    'baseUrl' => url('/') . '/blog/',
                ])
            </div>

            {{-- ===================== ASIDE COLUMN ===================== --}}
            <div class="col-xxl-4 col-xl-4">
                {{-- Status --}}
                @include('cms::admin.partials._status_aside', [
                    'isActive' => old('publish', $blog->status) === 'Published',
                    'isFeatured' => (bool) old('featured', $blog->featured),
                    'showTranslations' => true,
                    'updateTranslations' => (bool) old('update_translations', false),
                ])

                {{-- Category --}}
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-bookmark-fill text-primary fs-3 me-2"></i>
                                {{ __('Category') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Category" name="category_id" required>
                            <select name="category_id" id="category_id"
                                    class="form-select form-select-solid" required>
                                <option value="">{{ __('Select Category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                            @selected(old('category_id', $blog->category_id) == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </x-admin.form-group>
                    </div>
                </div>

                @include('cms::admin.partials._seo_aside', [
                    'hasFeaturedImage' => (bool) $blog->image,
                    'hasMetaImage' => (bool) $blog->meta_image,
                    'includeShortDescription' => true,
                ])
            </div>
        </div>

        {{-- Footer --}}
        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.blogs.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" id="submit">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
