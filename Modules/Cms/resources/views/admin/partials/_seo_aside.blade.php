@props([
    'hasFeaturedImage' => false,
    'hasMetaImage' => false,
    'includeShortDescription' => false,
])

@php
    $checks = collect([
        [
            'target' => '#title',
            'rule' => 'length',
            'min' => 30,
            'max' => 70,
            'label' => 'Title length',
            'hint' => 'Aim for 30–70 characters.',
        ],
        [
            'target' => '#slug',
            'rule' => 'length',
            'min' => 3,
            'max' => 60,
            'label' => 'URL slug',
            'hint' => 'Short, descriptive, hyphen-separated.',
        ],
        $includeShortDescription ? [
            'target' => '#description',
            'rule' => 'length',
            'min' => 120,
            'max' => 160,
            'hardMax' => 500,
            'label' => 'Short description',
            'hint' => '120–160 characters work best.',
        ] : null,
        [
            'target' => '#meta_title',
            'rule' => 'length',
            'min' => 50,
            'max' => 60,
            'hardMax' => 70,
            'label' => 'Meta title',
            'hint' => '50–60 characters, keyword first.',
        ],
        [
            'target' => '#meta_description',
            'rule' => 'length',
            'min' => 120,
            'max' => 160,
            'hardMax' => 255,
            'label' => 'Meta description',
            'hint' => 'Compelling 120–160 character summary.',
        ],
        [
            'target' => '#meta_keywords',
            'rule' => 'count',
            'min' => 3,
            'max' => 8,
            'label' => 'Meta keywords',
            'hint' => '3–8 relevant keywords.',
        ],
        [
            'target' => "input[name='img'], input[name='img_media_path']",
            'rule' => 'image',
            'initial' => $hasFeaturedImage ? '1' : '',
            'label' => 'Featured image',
            'hint' => '900×600 recommended.',
        ],
        [
            'target' => "input[name='meta_img'], input[name='meta_img_media_path']",
            'rule' => 'image',
            'initial' => $hasMetaImage ? '1' : '',
            'label' => 'Social share image',
            'hint' => '1200×630 for Open Graph cards.',
        ],
        [
            'target' => '#tinymce',
            'rule' => 'length',
            'min' => 300,
            'label' => 'Body content',
            'hint' => 'Aim for at least 300 characters.',
        ],
    ])->filter()->values()->all();
@endphp

<x-admin.seo-aside :checks="$checks"/>
