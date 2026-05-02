@push('scripts')
    <script src="https://cdn.tiny.cloud/1/{{Config::get('core.tinymce_key')}}/tinymce/8/tinymce.min.js"
            referrerpolicy="origin"></script>
@endpush

<script>
    $(document).ready(function (e) {

        tinymce.init({
            selector: '#tinymce',
            height: 750,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks  fontsize | bold italic underline strikethrough | link image media table mergetags | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'Author name',
            @if(app()->getLocale() == 'ar') language: 'ar', @endif
            file_picker_types: 'image',
            file_picker_callback: function (callback, value, meta) {
                if (meta.filetype === 'image' && typeof window.openMediaLibraryForTinyMce === 'function') {
                    window.openMediaLibraryForTinyMce({
                        insertContent: function (html) {
                            var parser = new DOMParser();
                            var doc = parser.parseFromString(html, 'text/html');
                            var image = doc.querySelector('img');
                            if (image) {
                                callback(image.getAttribute('src'), {alt: image.getAttribute('alt') || ''});
                            }
                        }
                    });
                }
            },
            ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant")),
        });
    });
</script>
