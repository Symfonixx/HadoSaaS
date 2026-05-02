@once
    @push('scripts')
        <script>
            (function () {
                var state = {
                    targetInput: null,
                    targetPreview: null,
                    tinyEditor: null,
                    page: 1,
                    search: '',
                    items: [],
                    selectedId: null,
                    selectedIds: [],
                };

                function modalEl() { return document.getElementById('mediaLibraryModal'); }
                function gridEl() { return document.getElementById('mediaLibraryGrid'); }
                function paginationEl() { return document.getElementById('mediaLibraryPagination'); }
                function searchEl() { return document.getElementById('mediaLibrarySearch'); }
                function detailsEl() { return document.getElementById('mediaLibraryDetails'); }
                function useBtnEl() { return document.getElementById('mediaLibraryUseBtn'); }
                function bulkDeleteBtnEl() { return document.getElementById('mediaLibraryBulkDeleteBtn'); }
                function dropZoneEl() { return document.getElementById('mediaLibraryDropZone'); }

                function csrf() {
                    var token = document.querySelector('meta[name="csrf-token"]');
                    return token ? token.getAttribute('content') : '';
                }

                function setBulkButtonState() {
                    var btn = bulkDeleteBtnEl();
                    if (!btn) return;
                    var count = state.selectedIds.length;
                    btn.disabled = count === 0;
                    btn.textContent = '{{ __('Delete Selected') }}' + (count ? ' (' + count + ')' : '');
                }

                function openModal() {
                    var modal = bootstrap.Modal.getOrCreateInstance(modalEl());
                    modal.show();
                    state.selectedId = null;
                    state.selectedIds = [];
                    setBulkButtonState();
                    loadMedia(1);
                }

                function closeModal() {
                    var modal = bootstrap.Modal.getOrCreateInstance(modalEl());
                    modal.hide();
                }

                function formatSize(bytes) {
                    if (!bytes) return '0 B';
                    var units = ['B', 'KB', 'MB', 'GB'];
                    var i = Math.floor(Math.log(bytes) / Math.log(1024));
                    return (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
                }

                function isSelected(id) {
                    return state.selectedIds.indexOf(id) !== -1;
                }

                function toggleSelected(id) {
                    var index = state.selectedIds.indexOf(id);
                    if (index === -1) {
                        state.selectedIds.push(id);
                    } else {
                        state.selectedIds.splice(index, 1);
                    }
                    state.selectedId = id;
                    setBulkButtonState();
                }

                function renderItems(items) {
                    var grid = gridEl();
                    if (!grid) return;
                    if (!items.length) {
                        grid.innerHTML = '<div class="col-12 text-center text-muted py-10">No media found.</div>';
                        renderDetails(null);
                        setBulkButtonState();
                        return;
                    }

                    state.items = items;
                    grid.innerHTML = items.map(function (item) {
                        var active = state.selectedId === item.id ? 'border-primary' : '';
                        var checked = isSelected(item.id) ? 'checked' : '';
                        return '' +
                            '<div class="col-md-3 col-6">' +
                            '  <div class="card border ' + active + ' cursor-pointer h-100 media-item position-relative" data-id="' + item.id + '">' +
                            '    <div class="position-absolute top-0 end-0 p-2 z-index-3">' +
                            '      <input type="checkbox" class="form-check-input media-item-check" data-media-check-id="' + item.id + '" ' + checked + '>' +
                            '    </div>' +
                            '    <div class="card-body p-2">' +
                            '      <div class="ratio ratio-1x1 mb-2"><img src="' + item.url + '" class="w-100 h-100 object-fit-cover rounded" alt=""></div>' +
                            '      <div class="small text-truncate fw-semibold" title="' + item.name + '">' + item.name + '</div>' +
                            '      <div class="small text-muted">' + formatSize(item.size) + '</div>' +
                            '    </div>' +
                            '  </div>' +
                            '</div>';
                    }).join('');

                    if (!state.selectedId && items[0]) {
                        state.selectedId = items[0].id;
                    }
                    renderDetails(getSelectedItem());
                    setBulkButtonState();
                }

                function renderPagination(meta) {
                    var el = paginationEl();
                    if (!el) return;
                    if ((meta.last_page || 1) <= 1) {
                        el.innerHTML = '';
                        return;
                    }

                    var prevDisabled = meta.current_page <= 1 ? 'disabled' : '';
                    var nextDisabled = meta.current_page >= meta.last_page ? 'disabled' : '';
                    el.innerHTML = '' +
                        '<button class="btn btn-sm btn-light ' + prevDisabled + '" data-media-page="' + (meta.current_page - 1) + '">Prev</button>' +
                        '<span class="mx-3 small text-muted">' + meta.current_page + ' / ' + meta.last_page + '</span>' +
                        '<button class="btn btn-sm btn-light ' + nextDisabled + '" data-media-page="' + (meta.current_page + 1) + '">Next</button>';
                }

                function loadMedia(page) {
                    state.page = page || 1;
                    var grid = gridEl();
                    if (grid) {
                        grid.innerHTML = '<div class="col-12 text-center text-muted py-10">Loading...</div>';
                    }

                    var url = '{{ route('admin.media_library.list') }}?page=' + state.page + '&q=' + encodeURIComponent(state.search || '');
                    fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            renderItems(data.data || []);
                            renderPagination(data);
                        });
                }

                function getSelectedItem() {
                    if (!state.selectedId) return null;
                    return state.items.find(function (it) { return it.id === state.selectedId; }) || null;
                }

                function selectMedia(path, url) {
                    if (state.tinyEditor) {
                        state.tinyEditor.insertContent('<img src="' + url + '" alt="">');
                        closeModal();
                        state.tinyEditor = null;
                        return;
                    }

                    if (state.targetInput) {
                        state.targetInput.value = path;
                    }
                    if (state.targetPreview) {
                        state.targetPreview.style.backgroundImage = "url('" + url + "')";
                    }
                    closeModal();
                }

                function renderDetails(item) {
                    var el = detailsEl();
                    if (!el) return;
                    if (!item) {
                        el.innerHTML = '<div class="text-muted">{{ __('Select an image to see details.') }}</div>';
                        if (useBtnEl()) useBtnEl().disabled = true;
                        return;
                    }

                    el.innerHTML = '' +
                        '<div class="mb-4"><img src="' + item.url + '" class="w-100 rounded border" alt=""></div>' +
                        '<div class="small mb-2"><strong>{{ __('Name') }}:</strong> ' + (item.name || '-') + '</div>' +
                        '<div class="small mb-2"><strong>{{ __('Type') }}:</strong> ' + (item.mime_type || '-') + '</div>' +
                        '<div class="small mb-2"><strong>{{ __('Size') }}:</strong> ' + formatSize(item.size) + '</div>' +
                        '<div class="small mb-3 text-break"><strong>URL:</strong> <span>' + item.url + '</span></div>' +
                        '<div class="d-flex gap-2">' +
                        '  <button type="button" class="btn btn-light-primary btn-sm" data-media-copy-url="true">{{ __('Copy URL') }}</button>' +
                        '  <button type="button" class="btn btn-light-danger btn-sm" data-media-delete="true">{{ __('Delete') }}</button>' +
                        '</div>';

                    if (useBtnEl()) useBtnEl().disabled = false;
                }

                function copyCurrentUrl() {
                    var item = getSelectedItem();
                    if (!item) return;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(item.url);
                    }
                }

                function deleteCurrentItem() {
                    var item = getSelectedItem();
                    if (!item) return;
                    if (!confirm('{{ __('Are you sure you want to delete it?') }}')) return;
                    var deleteUrl = '{{ route('admin.media_library.destroy', ['media' => '__id__']) }}'.replace('__id__', item.id);

                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).then(function () {
                        state.selectedId = null;
                        state.selectedIds = state.selectedIds.filter(function (id) { return id !== item.id; });
                        loadMedia(state.page);
                    });
                }

                function deleteSelectedItems() {
                    if (state.selectedIds.length === 0) return;
                    if (!confirm('{{ __('Are you sure you want to delete it?') }}')) return;

                    fetch('{{ route('admin.media_library.delete_multi') }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ids: state.selectedIds}),
                    }).then(function () {
                        state.selectedId = null;
                        state.selectedIds = [];
                        loadMedia(state.page);
                    });
                }

                function uploadFiles(fileList) {
                    if (!fileList || fileList.length === 0) return Promise.resolve();
                    var form = new FormData();
                    Array.prototype.forEach.call(fileList, function (file) {
                        form.append('files[]', file);
                    });

                    return fetch('{{ route('admin.media_library.store') }}', {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest'},
                        body: form,
                    }).then(function () {
                        loadMedia(1);
                    });
                }

                function bindDropZone() {
                    var zone = dropZoneEl();
                    if (!zone) return;

                    ['dragenter', 'dragover'].forEach(function (eventName) {
                        zone.addEventListener(eventName, function (event) {
                            event.preventDefault();
                            zone.classList.add('border-primary');
                        });
                    });

                    ['dragleave', 'drop'].forEach(function (eventName) {
                        zone.addEventListener(eventName, function (event) {
                            event.preventDefault();
                            zone.classList.remove('border-primary');
                        });
                    });

                    zone.addEventListener('drop', function (event) {
                        var files = event.dataTransfer ? event.dataTransfer.files : null;
                        uploadFiles(files);
                    });
                }

                function bindPickerButtons() {
                    document.querySelectorAll('[data-media-picker-target]').forEach(function (button) {
                        button.addEventListener('click', function () {
                            var inputSelector = button.getAttribute('data-media-picker-target');
                            var previewSelector = button.getAttribute('data-media-preview-target');
                            state.targetInput = document.querySelector(inputSelector);
                            state.targetPreview = previewSelector ? document.querySelector(previewSelector) : null;
                            state.tinyEditor = null;
                            openModal();
                        });
                    });
                }

                function bindGlobalHandlers() {
                    document.addEventListener('click', function (event) {
                        var mediaCheck = event.target.closest('[data-media-check-id]');
                        if (mediaCheck) {
                            event.stopPropagation();
                            toggleSelected(parseInt(mediaCheck.getAttribute('data-media-check-id'), 10));
                            renderItems(state.items);
                            return;
                        }

                        var item = event.target.closest('.media-item');
                        if (item) {
                            state.selectedId = parseInt(item.getAttribute('data-id'), 10);
                            renderItems(state.items);
                            return;
                        }

                        var pageBtn = event.target.closest('[data-media-page]');
                        if (pageBtn && !pageBtn.classList.contains('disabled')) {
                            loadMedia(parseInt(pageBtn.getAttribute('data-media-page'), 10));
                            return;
                        }

                        var openBtn = event.target.closest('[data-media-open="true"]');
                        if (openBtn) {
                            state.targetInput = null;
                            state.targetPreview = null;
                            state.tinyEditor = null;
                            openModal();
                            return;
                        }

                        var useBtn = event.target.closest('[data-media-use="true"]');
                        if (useBtn) {
                            var selected = getSelectedItem();
                            if (selected) {
                                selectMedia(selected.path, selected.url);
                            }
                            return;
                        }

                        if (event.target.closest('[data-media-copy-url="true"]')) {
                            copyCurrentUrl();
                            return;
                        }

                        if (event.target.closest('[data-media-delete="true"]')) {
                            deleteCurrentItem();
                            return;
                        }

                        if (event.target.closest('[data-media-delete-selected="true"]')) {
                            deleteSelectedItems();
                        }
                    });

                    var search = searchEl();
                    if (search) {
                        search.addEventListener('input', function () {
                            state.search = search.value || '';
                            loadMedia(1);
                        });
                    }

                    var uploadInput = document.getElementById('mediaLibraryUpload');
                    if (uploadInput) {
                        uploadInput.addEventListener('change', function () {
                            uploadFiles(uploadInput.files).then(function () {
                                uploadInput.value = '';
                            });
                        });
                    }
                }

                window.openMediaLibraryForTinyMce = function (editor) {
                    state.tinyEditor = editor || null;
                    state.targetInput = null;
                    state.targetPreview = null;
                    openModal();
                };

                document.addEventListener('DOMContentLoaded', function () {
                    bindPickerButtons();
                    bindGlobalHandlers();
                    bindDropZone();
                });
            })();
        </script>
    @endpush
@endonce

<div class="modal fade" id="mediaLibraryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('Media Library') }}</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
                    <i class="bi bi-x fs-2"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-3 align-items-center mb-5">
                    <input type="text" class="form-control form-control-solid w-250px" id="mediaLibrarySearch" placeholder="{{ __('Search') }}...">
                    <label class="btn btn-light-primary mb-0">
                        {{ __('Upload') }}
                        <input type="file" class="d-none" id="mediaLibraryUpload" accept=".png,.jpg,.jpeg,.webp,.gif" multiple>
                    </label>
                    <button type="button" class="btn btn-light-danger" id="mediaLibraryBulkDeleteBtn" data-media-delete-selected="true" disabled>
                        {{ __('Delete Selected') }}
                    </button>
                </div>
                <div id="mediaLibraryDropZone" class="border border-dashed rounded p-5 text-center text-muted mb-5">
                    {{ __('Drop images here to upload multiple files') }}
                </div>
                <div class="row g-6">
                    <div class="col-xl-8">
                        <div class="row g-4" id="mediaLibraryGrid"></div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-flush border">
                            <div class="card-header min-h-40px">
                                <h5 class="mb-0">{{ __('Attachment Details') }}</h5>
                            </div>
                            <div class="card-body" id="mediaLibraryDetails">
                                <div class="text-muted">{{ __('Select an image to see details.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-6">
                    <div id="mediaLibraryPagination"></div>
                    <button type="button" class="btn btn-primary" id="mediaLibraryUseBtn" data-media-use="true" disabled>
                        {{ __('Use this image') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
