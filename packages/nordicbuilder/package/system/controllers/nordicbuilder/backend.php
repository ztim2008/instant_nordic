<?php

class backendNordicbuilder extends cmsBackend {

    protected $useOptions = true;

    public $useDefaultOptionsAction = true;

    public function actionIndex() {
        $this->redirectToAction('workspace');
    }

    public function getBackendMenu() {
        return [
            [
                'title' => 'Workspace',
                'url'   => href_to($this->root_url, 'workspace'),
                'options' => [
                    'icon' => 'window-maximize'
                ]
            ],
            [
                'title' => 'Контракты',
                'url'   => href_to($this->root_url, 'contracts'),
                'options' => [
                    'icon' => 'code'
                ]
            ],
            [
                'title' => 'Глобальные стили',
                'url'   => href_to($this->root_url, 'defaults'),
                'options' => [
                    'icon' => 'paint-brush'
                ]
            ],
            [
                'title' => 'Компоненты',
                'url'   => href_to($this->root_url, 'components'),
                'options' => [
                    'icon' => 'th-large'
                ]
            ],
            [
                'title' => 'Адаптеры',
                'url'   => href_to($this->root_url, 'adapters'),
                'options' => [
                    'icon' => 'plug'
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