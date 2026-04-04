<?php
/**
 * Массив опций и свойств шаблона
 */
return [
    'inherit' => ['modern'],
    'title' => 'Nordic',
    'author' => [
        'name' => 'Nordic',
        'url'  => 'https://nordic-builder.store'
    ],
    'properties' => [
        'vendor'                     => 'bootstrap4',
        'style_middleware'           => 'scss',
        'has_options'                => true,
        'has_profile_themes_support' => false,
        'has_profile_themes_options' => false,
        'is_dynamic_layout'          => true,
        'is_backend'                 => false,
        'is_frontend'                => true,
        'html_attr'                  => [
            'class' => 'min-vh-100 nordic-html'
        ]
    ]
];