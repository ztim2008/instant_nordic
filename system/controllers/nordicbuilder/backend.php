<?php

class backendNordicbuilder extends cmsBackend {

    protected $useOptions = true;

    public $useDefaultOptionsAction = true;

    public function actionIndex() {
        $this->redirectToAction('pages');
    }

    public function getBackendMenu() {
        return [
            [
                'title' => 'Мой сайт',
				'url'   => href_to_abs('admin', 'controllers', ['edit', $this->name, 'canvas']),
                'options' => [
                    'icon' => 'home'
                ]
            ],
            [
                'title' => 'Все страницы',
				'url'   => href_to_abs('admin', 'controllers', ['edit', $this->name, 'pages']),
                'options' => [
                    'icon' => 'file-alt'
                ]
            ],
            [
                'title' => 'Внешний вид',
				'url'   => href_to_abs('admin', 'controllers', ['edit', $this->name, 'defaults']),
                'options' => [
                    'icon' => 'paint-brush'
                ]
            ],
            [
                'title' => 'Каркас сайта',
				'url'   => href_to_abs('admin', 'controllers', ['edit', $this->name, 'shell']),
                'options' => [
                    'icon' => 'columns'
                ]
            ],
            [
                'title' => 'Правила применения',
				'url'   => href_to_abs('admin', 'controllers', ['edit', $this->name, 'bindings']),
                'options' => [
                    'icon'  => 'random'
                ]
            ]
        ];
    }
}