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
                'title' => 'Страницы',
                'url'   => href_to($this->root_url, 'pages'),
                'options' => [
                    'icon' => 'file-alt'
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