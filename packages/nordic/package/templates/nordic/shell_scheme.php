<?php

return [
    'key' => 'nordic_shell_v1',
    'slot_positions' => [
        'site_top'              => ['site_top'],
        'header_primary'        => ['header_primary'],
        'header_secondary'      => ['header_secondary'],
        'hero'                  => ['hero'],
        'before_content'        => ['before_content'],
        'content_body'          => ['content_body'],
        'content_sidebar_left'  => ['content_sidebar_left'],
        'content_sidebar_right' => ['content_sidebar_right'],
        'after_content'         => ['after_content'],
        'footer_primary'        => ['footer_primary'],
        'footer_secondary'      => ['footer_secondary']
    ],
    'reserved_positions' => [
        'site_top',
        'header_primary',
        'header_secondary',
        'hero',
        'before_content',
        'content_body',
        'content_sidebar_left',
        'content_sidebar_right',
        'after_content',
        'footer_primary',
        'footer_secondary',
        'top',
        'header',
        'footer'
    ],
    'legacy_bind_map' => [
        'top'            => 'site_top',
        'header'         => 'header_primary',
        'footer'         => 'footer_secondary',
        'main'           => 'content_body',
        'native_content' => 'content_body',
        'sidebar'        => 'content_sidebar_right',
        'pos_22'         => 'site_top',
        'pos_26'         => 'header_secondary',
        'pos_27'         => 'header_primary',
        'pos_29'         => 'header_primary',
        'pos_31'         => 'header_primary',
        'pos_33'         => 'hero',
        'con_header'     => 'before_content',
        'pos_10'         => 'before_content',
        'pos_8'          => 'content_body',
        'pos_34'         => 'content_sidebar_left',
        'pos_9'          => 'content_sidebar_right',
        'pos_17'         => 'after_content',
        'pos_18'         => 'after_content',
        'pos_38'         => 'footer_primary',
        'pos_39'         => 'footer_primary',
        'pos_40'         => 'footer_primary',
        'pos_11'         => 'footer_secondary',
        'pos_32'         => 'footer_secondary'
    ],
    'layout_rows' => [
        [
            'title' => 'Служебная верхняя зона',
            'tag'   => 'section',
            'class' => 'nordic-layout-row nordic-layout-row--site-top',
            'cols'  => [
                ['title' => 'Верхняя служебная зона', 'name' => 'site_top', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12']
            ]
        ],
        [
            'title' => 'Основной хедер',
            'tag'   => 'section',
            'class' => 'nordic-layout-row nordic-layout-row--header-primary',
            'cols'  => [
                ['title' => 'Основной хедер', 'name' => 'header_primary', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12']
            ]
        ],
        [
            'title' => 'Вторичный хедер',
            'tag'   => 'section',
            'class' => 'nordic-layout-row nordic-layout-row--header-secondary',
            'cols'  => [
                ['title' => 'Вторичный хедер', 'name' => 'header_secondary', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12']
            ]
        ],
        [
            'title' => 'Hero зона',
            'tag'   => 'section',
            'class' => 'nordic-layout-row nordic-layout-row--hero',
            'cols'  => [
                ['title' => 'Hero', 'name' => 'hero', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12']
            ]
        ],
        [
            'title' => 'Перед основным контентом',
            'tag'   => 'section',
            'class' => 'nordic-layout-row nordic-layout-row--before-content',
            'cols'  => [
                ['title' => 'Перед контентом', 'name' => 'before_content', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12']
            ]
        ],
        [
            'title' => 'Контентная область',
            'tag'   => 'div',
            'class' => 'nordic-layout-row nordic-layout-row--content',
            'cols'  => [
                ['title' => 'Левая колонка', 'name' => 'content_sidebar_left', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12 col-xl-3'],
                ['title' => 'Тело контента', 'name' => 'content_body', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12 col-xl-6'],
                ['title' => 'Правая колонка', 'name' => 'content_sidebar_right', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12 col-xl-3']
            ]
        ],
        [
            'title' => 'После основного контента',
            'tag'   => 'section',
            'class' => 'nordic-layout-row nordic-layout-row--after-content',
            'cols'  => [
                ['title' => 'После контента', 'name' => 'after_content', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12']
            ]
        ],
        [
            'title' => 'Основной футер',
            'tag'   => 'section',
            'class' => 'nordic-layout-row nordic-layout-row--footer-primary',
            'cols'  => [
                ['title' => 'Основной футер', 'name' => 'footer_primary', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12']
            ]
        ],
        [
            'title' => 'Нижний футер',
            'tag'   => 'section',
            'class' => 'nordic-layout-row nordic-layout-row--footer-secondary',
            'cols'  => [
                ['title' => 'Нижний футер', 'name' => 'footer_secondary', 'type' => 'typical', 'tag' => 'div', 'class' => 'col-12']
            ]
        ]
    ]
];