@section('title', __('Add New FAQ'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => 'FAQs', 'url' => route('admin.faqs.index')],
            ['label' => 'Add New FAQ'],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add New FAQ')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.faqs.store') }}">
        @csrf
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
                                   value="{{ old('question') }}"
                                   placeholder="{{ __('Question') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Answer" name="answer" required translatable>
                            <textarea name="answer"
                                      id="answer"
                                      rows="8"
                                      class="form-control form-control-solid"
                                      placeholder="{{ __('Answer') }}">{{ old('answer') }}</textarea>
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
                            :checked="old('publish', 'Published') === 'Published'"
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
                                   value="{{ old('rank', 0) }}"
                                   placeholder="0"/>
                        </x-admin.form-group>
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
