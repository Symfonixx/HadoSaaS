@section('title', __('Edit FAQ'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'FAQs', 'url' => route('admin.faqs.index')],
            ['label' => 'Edit FAQ'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit FAQ')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.faqs.update', $faq->id) }}">
        @csrf
        @method('PUT')

        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-patch-question text-primary fs-3 me-2"></i>
                                {{ __('General') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Question" name="question" required translatable>
                            <input type="text"
                                   id="question"
                                   name="question"
                                   class="form-control form-control-solid"
                                   value="{{ old('question', $faq->question) }}"
                                   placeholder="{{ __('Question') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Answer" name="answer" required translatable>
                            <textarea name="answer"
                                      id="answer"
                                      rows="8"
                                      class="form-control form-control-solid"
                                      placeholder="{{ __('Answer') }}">{{ old('answer', $faq->answer) }}</textarea>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-xl-4">
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
                        <x-admin.toggle-switch
                            name="publish"
                            label="Active"
                            helper="When active, the content is visible to the public."
                            icon="bi bi-broadcast-pin"
                            tone="success"
                            :checked="old('publish', $faq->status) === 'Published'"
                            value="Published"
                            last
                        />
                    </div>
                </div>

                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-sort-numeric-down text-primary fs-3 me-2"></i>
                                {{ __('Rank') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Rank" name="rank" required>
                            <input type="number"
                                   min="0"
                                   name="rank"
                                   class="form-control form-control-solid"
                                   value="{{ old('rank', $faq->rank) }}"
                                   placeholder="0"/>
                        </x-admin.form-group>
                    </div>
                </div>

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
                                   @checked(old('update_translations', false))/>
                            <label class="form-check-label fs-7 ms-2" for="update_translations">
                                {{ __('Use Google Translate to update all other languages.') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.faqs.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" id="submit">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
