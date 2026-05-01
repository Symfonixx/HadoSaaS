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
            ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant")),
        });
    });
</script>
