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
        'pos_32'         => 'footer_secondary',
        // Nordic scheme: nm-style → shell slots
        'nordic_top_left'           => 'site_top',
        'nordic_top_right'          => 'site_top',
        'nordic_header_logo'        => 'header_primary',
        'nordic_header_right'       => 'header_primary',
        'nordic_nav'                => 'header_primary',
        'nordic_hero'               => 'hero',
        'nordic_before_content'     => 'before_content',
        'nordic_sidebar'            => 'content_sidebar_left',
        'nordic_content'            => 'content_body',
        'nordic_aside'              => 'content_sidebar_right',
        'nordic_footer_1'           => 'footer_primary',
        'nordic_footer_2'           => 'footer_primary',
        'nordic_footer_3'           => 'footer_primary',
        'nordic_footer_links'       => 'footer_primary',
        'nordic_footer_social'      => 'footer_primary',
        'nordic_footer_copy'        => 'footer_secondary',
        'nordic_footer_bottom_nav'  => 'footer_secondary',
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
    ],

    /**
     * context_rules
     *
     * Правила активации shell-слотов по типу страницы.
     * Используется когда landing builder не переопределяет active_slots.
     *
     * shell_preset (значения):
     *   no_sidebars   — только content_body, без боковых колонок
     *   right_sidebar — content_body + content_sidebar_right
     *   left_sidebar  — content_body + content_sidebar_left
     *   both_sidebars — все три колонки
     *
     * Слоты header_primary, hero, footer_primary, footer_secondary
     * всегда включены независимо от preset.
     */
    'context_rules' => [

        // Пресеты слотов боковых колонок
        'shell_presets' => [
            'no_sidebars' => [
                'content_sidebar_left'  => false,
                'content_sidebar_right' => false,
            ],
            'right_sidebar' => [
                'content_sidebar_left'  => false,
                'content_sidebar_right' => true,
            ],
            'left_sidebar' => [
                'content_sidebar_left'  => true,
                'content_sidebar_right' => false,
            ],
            'both_sidebars' => [
                'content_sidebar_left'  => true,
                'content_sidebar_right' => true,
            ],
        ],

        // Слоты, которые всегда активны (независимо от preset)
        'always_active' => [
            'site_top',
            'header_primary',
            'header_secondary',
            'hero',
            'before_content',
            'content_body',
            'after_content',
            'footer_primary',
            'footer_secondary',
        ],

    ],
];