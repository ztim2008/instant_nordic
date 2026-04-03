<?php

class actionLandingbuilderPages extends cmsAction {

    public function run() {

        $pages = [
            [
                'key'        => 'homepage',
                'title'      => 'Главная страница',
                'status'     => 'draft',
                'mode'       => 'full_takeover',
                'updated_at' => '2026-04-03 13:00',
                'canvas_url' => href_to($this->controller->root_url, 'canvas', ['homepage'])
            ],
            [
                'key'        => 'ads-category',
                'title'      => 'Категория Объявлений',
                'status'     => 'prototype',
                'mode'       => 'hybrid_overlay',
                'updated_at' => '2026-04-03 13:05',
                'canvas_url' => href_to($this->controller->root_url, 'canvas', ['ads-category'])
            ],
            [
                'key'        => 'profile-cover',
                'title'      => 'Профиль пользователя',
                'status'     => 'idea',
                'mode'       => 'zone_injection',
                'updated_at' => '2026-04-03 13:10',
                'canvas_url' => href_to($this->controller->root_url, 'canvas', ['profile-cover'])
            ]
        ];

        return $this->cms_template->render('backend/pages', [
            'menu'  => $this->controller->getBackendMenu(),
            'pages' => $pages
        ]);
    }
}