@if(isset($modal) && $modal)
@php
    $langs = config('translatable.locales', ['en']);
@endphp
<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal{{$blogs_category->id}}" tabindex="-1" aria-labelledby="editCategoryModalLabel{{$blogs_category->id}}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.blogs_categories.update', $blogs_category->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel{{$blogs_category->id}}">{{ __('Edit Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @foreach($langs as $lang)
                        <div class="mb-3">
                            <label for="name_{{$lang}}" class="form-label">{{ __('Name') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" class="form-control" name="name[{{$lang}}]" value="{{ $blogs_category->getTranslation('name', $lang, false) }}" required>
                        </div>
                    @endforeach
                    <div class="mb-3">
                        <label for="slug" class="form-label">{{ __('Slug') }}</label>
                        <input type="text" class="form-control" name="slug" value="{{$blogs_category->slug}}" required readonly>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="update_translations"
                                   id="update_translations_{{$blogs_category->id}}" value="1" @checked(old('update_translations'))>
                            <label class="form-check-label" for="update_translations_{{$blogs_category->id}}">
                                {{ __('Use Google Translate to update all other languages.') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif 