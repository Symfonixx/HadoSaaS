@if(isset($modal) && $modal)
@php
    $supported = array_keys(config('laravellocalization.supportedLocales', []));
    $langs = collect($supported)->isEmpty()
        ? ['en']
        : collect($supported)->sort(function ($a, $b) {
            if ($a === 'en') {
                return -1;
            }
            if ($b === 'en') {
                return 1;
            }

            return strcmp((string) $a, (string) $b);
        })->values()->all();
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    @foreach($langs as $lang)
                        <div class="mb-3">
                            <label for="name_{{$blogs_category->id}}_{{$lang}}" class="form-label">
                                {{ __('Name') }}
                                @if($lang === 'en')
                                    <span class="text-muted">({{ __('English') }})</span>
                                @else
                                    ({{ strtoupper($lang) }})
                                @endif
                            </label>
                            <input id="name_{{$blogs_category->id}}_{{$lang}}"
                                   type="text"
                                   class="form-control"
                                   lang="{{ $lang === 'ar' ? 'ar' : 'en' }}"
                                   dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}"
                                   name="name[{{$lang}}]"
                                   value="{{ old('name.'.$lang, $blogs_category->getTranslation('name', $lang, false)) }}"
                                   @if($lang === 'en') required @endif>
                        </div>
                    @endforeach
                    <div class="mb-3">
                        <label for="slug_readonly_{{ $blogs_category->id }}" class="form-label">{{ __('Slug') }}</label>
                        <input id="slug_readonly_{{ $blogs_category->id }}" type="text" class="form-control" name="slug"
                               value="{{ $blogs_category->slug }}" required readonly>
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
                    <button type="submit" class="btn btn-primary">{{ __('Save and Close') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif 