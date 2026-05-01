@props([
    'isActive' => true,
    'isFeatured' => false,
    'showTranslations' => false,
    'updateTranslations' => false,
])

<div class="card card-flush mb-7">
    <div class="card-header">
        <div class="card-title">
            <h2 class="d-flex align-items-center">
                <i class="bi bi-toggles text-primary fs-3 me-2"></i>
                {{ __('Status') }}
            </h2>
        </div>
    </div>
    <div class="card-body pt-0">
        <p class="text-muted fs-7 mb-5">
            {{ __('Set the publishing state and visibility of this content.') }}
        </p>

        <x-admin.toggle-switch
            name="publish"
            label="Active"
            helper="When active, the content is visible to the public."
            icon="bi bi-broadcast-pin"
            tone="success"
            :checked="$isActive"
            value="Published"
        />

        <x-admin.toggle-switch
            name="featured"
            label="Featured"
            helper="Mark as featured to highlight on listings and homepages."
            icon="bi bi-star-fill"
            tone="warning"
            :checked="$isFeatured"
            last
        />
    </div>
</div>

@if($showTranslations)
    <div class="card card-flush mb-7">
        <div class="card-header">
            <div class="card-title">
                <h2 class="d-flex align-items-center">
                    <i class="bi bi-translate text-primary fs-3 me-2"></i>
                    {{ __('Update Other Languages') }}
                </h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="form-check form-check-custom form-check-solid">
                <input class="form-check-input"
                       type="checkbox"
                       name="update_translations"
                       id="update_translations"
                       value="1"
                       @checked($updateTranslations)/>
                <label class="form-check-label fs-7 ms-2" for="update_translations">
                    {{ __('Use Google Translate to update all other languages.') }}
                </label>
            </div>
        </div>
    </div>
@endif
