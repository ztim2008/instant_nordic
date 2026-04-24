<?php

class backendLandingbuilder extends cmsBackend {

    protected $useOptions = true;

    public $useDefaultOptionsAction = true;

    public function actionIndex() {
        $this->redirectToAction('pages');
    }

    public function getBackendMenu() {
        return [
            [
                'title' => 'Макеты',
                'url'   => href_to($this->root_url, 'pages'),
                'options' => [
                    'icon' => 'file-alt'
                ]
            ],
            [
                'title' => 'Каркас',
                'url'   => href_to($this->root_url, 'shell'),
                'options' => [
                    'icon' => 'window-maximize'
                ]
            ],
            [
                'title' => 'Глобальный стиль',
                'url'   => href_to($this->root_url, 'design'),
                'options' => [
                    'icon' => 'paint-brush'
                ]
            ],
            [
                'title' => 'Опции',
                'url'   => href_to($this->root_url, 'options'),
                'options' => [
                    'icon' => 'cog'
                ]
            ]
        ];
    }
}