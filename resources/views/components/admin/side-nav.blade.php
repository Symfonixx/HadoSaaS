<div class="menu-item">
    <a class="menu-link {{ isset($active['dashboard']) ? 'active' : '' }}"
       href="{{ route('admin.dashboard.index') }}">
                <span class="menu-icon">
                    <i class="bi bi-speedometer2"></i>
                </span>
        <span class="menu-title">{{ __('Dashboard') }}</span>
    </a>
</div>

@can('Settings Management')
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['settings']) ? 'show hover' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="bi bi-gear"></i>
                </span>
                <span class="menu-title">{{ __('Settings') }}</span>
                <span class="menu-arrow"></span>
            </span>


        <div class="menu-sub menu-sub-accordion {{ isset($active['websiteConfigurations'])  ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['websiteConfigurations']) ? 'active' : '' }}"
                   href="{{ route('admin.settings.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Website Configurations') }}</span>
                </a>
            </div>

        </div>

        <div class="menu-sub menu-sub-accordion {{ isset($active['seo'])  ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['seo']) ? 'active' : '' }}"
                   href="{{ route('admin.seo.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Seo Configurations') }}</span>
                </a>
            </div>

        </div>

    </div>
@endcan

@can('CMS Management')
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['cms']) ? 'show hover' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                   <i class="bi bi-intersect"></i>
                </span>
                <span class="menu-title">{{ __('CMS') }}</span>
                <span class="menu-arrow"></span>
            </span>


        <div class="menu-sub menu-sub-accordion {{ isset($active['pages'])  ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['pages']) ? 'active' : '' }}"
                   href="{{ route('admin.pages.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Pages') }}</span>
                </a>
            </div>

        </div>

        <div class="menu-sub menu-sub-accordion {{ isset($active['blogs_categories'])  ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['blogs_categories']) ? 'active' : '' }}"
                   href="{{ route('admin.blogs_categories.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Blog Categories') }}</span>
                </a>
            </div>

        </div>

        <div class="menu-sub menu-sub-accordion {{ isset($active['blogs'])  ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['blogs']) ? 'active' : '' }}"
                   href="{{ route('admin.blogs.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Blogs') }}</span>
                </a>
            </div>

        </div>

        <div class="menu-sub menu-sub-accordion {{ isset($active['faqs'])  ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['faqs']) ? 'active' : '' }}"
                   href="{{ route('admin.faqs.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('FAQs') }}</span>
                </a>
            </div>

        </div>

    </div>
@endcan

@can('Media Library Management')
    <div class="menu-item">
        <a class="menu-link {{ isset($active['media_library']) ? 'active' : '' }}"
           href="{{ route('admin.media_library.index') }}">
                <span class="menu-icon">
                    <i class="bi bi-images"></i>
                </span>
            <span class="menu-title">{{ __('Media Library') }}</span>
        </a>
    </div>
@endcan


@can('Support Management')
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['support']) ? 'show hover' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                   <i class="bi bi-headset"></i>
                </span>
                <span class="menu-title">{{ __('Support Hub') }}</span>
                <span class="menu-arrow"></span>
            </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['contact_forms'])  ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['contact_forms']) ? 'active' : '' }}"
                   href="{{ route('admin.contact_forms.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Contacts') }}</span>
                </a>
            </div>

        </div>
        <div class="menu-sub menu-sub-accordion {{ isset($active['subscribers'])  ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['subscribers']) ? 'active' : '' }}"
                   href="{{ route('admin.subscribers.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Newsletter Subscribers') }}</span>
                </a>
            </div>

        </div>


    </div>
@endcan


@can('Hr Management')
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['hr']) ? 'show hover' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="bi bi-journal-text"></i>
                </span>
                <span class="menu-title">{{ __('HR') }}</span>
                <span class="menu-arrow"></span>
            </span>


        <div
            class="menu-sub menu-sub-accordion {{ isset($active['roles']) || isset($active['staffs']) || isset($active['users']) ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['roles']) ? 'active' : '' }}"
                   href="{{ route('admin.roles.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Roles') }}</span>
                </a>
            </div>

            <div class="menu-item">
                <a class="menu-link {{ isset($active['staffs']) ? 'active' : '' }}"
                   href="{{ route('admin.staffs.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Staffs') }}</span>
                </a>
            </div>

            <div class="menu-item">
                <a class="menu-link {{ isset($active['users']) ? 'active' : '' }}"
                   href="{{ route('admin.users.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                    <span class="menu-title">{{ __('Users') }}</span>
                </a>
            </div>

        </div>

    </div>
@endcan
@can('Logs Management')
    <div class="menu-item">
        <a class="menu-link {{ isset($active['logs']) ? 'active' : '' }}"
           href="{{ route('admin.logs.index') }}">
                <span class="menu-icon">
                    <i class="bi bi-window-stack"></i>
                </span>
            <span class="menu-title">{{ __('Logs & Bugs') }}</span>
        </a>
    </div>
@endcan
