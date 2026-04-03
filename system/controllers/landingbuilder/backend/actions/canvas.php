<?php

class actionLandingbuilderCanvas extends cmsAction {

    public function run($page_key = 'homepage') {

        $page = [
            'key'    => $page_key,
            'title'  => $this->getPageTitleByKey($page_key),
            'status' => 'draft',
            'mode'   => $this->getPageModeByKey($page_key)
        ];

        $screen = [
            'devices' => ['desktop', 'tablet', 'mobile'],
            'left_tabs' => ['Блоки Нордик', 'Системные widgets'],
            'sections' => [
                [
                    'title'   => 'Hero / Intro',
                    'layout'  => '2col_equal',
                    'columns' => [
                        [
                            'title' => 'Колонка 1',
                            'nodes' => [
                                ['type' => 'block', 'label' => 'core.hero-heading'],
                                ['type' => 'block', 'label' => 'core.hero-actions']
                            ]
                        ],
                        [
                            'title' => 'Колонка 2',
                            'nodes' => [
                                ['type' => 'system_widget', 'label' => 'auth.register']
                            ]
                        ]
                    ]
                ],
                [
                    'title'   => 'Контентная сетка',
                    'layout'  => '3col_equal',
                    'columns' => [
                        [
                            'title' => 'Колонка 1',
                            'nodes' => [
                                ['type' => 'block', 'label' => 'core.cards-grid']
                            ]
                        ],
                        [
                            'title' => 'Колонка 2',
                            'nodes' => [
                                ['type' => 'block', 'label' => 'core.feature-list']
                            ]
                        ],
                        [
                            'title' => 'Колонка 3',
                            'nodes' => [
                                ['type' => 'system_widget', 'label' => 'content.list']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $this->cms_template->render('backend/canvas', [
            'menu'   => $this->controller->getBackendMenu(),
            'page'   => $page,
            'screen' => $screen
        ]);
    }

    private function getPageTitleByKey($page_key) {

        $titles = [
            'homepage'     => 'Главная страница',
            'ads-category' => 'Категория Объявлений',
            'profile-cover' => 'Профиль пользователя'
        ];

        return $titles[$page_key] ?? 'Страница конструктора';
    }

    private function getPageModeByKey($page_key) {

        $modes = [
            'homepage'      => 'full_takeover',
            'ads-category'  => 'hybrid_overlay',
            'profile-cover' => 'zone_injection'
        ];

        return $modes[$page_key] ?? 'full_takeover';
    }
}