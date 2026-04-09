<?php
/**
 * Nordic Style: дочерний шаблон от Modern.
 * Задумка: максимум совместимости с InstantCMS/Modern, минимум переопределений.
 */
return [
    'inherit' => ['admincoreui', 'modern'],
    'title'   => 'Nordics',
    'author'  => [
        'name' => 'Nordic Builder',
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
            'class' => 'min-vh-100'
        ]
    ]
];
