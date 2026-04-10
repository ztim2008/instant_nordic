<?php

class backendNordicstyl extends cmsBackend {

    public function actionIndex() {
        $this->redirectToAction('rules');
    }

    public function getBackendMenu() {
        return [
            [
                'title' => 'Правила',
                'url'   => href_to_abs('admin', 'controllers', ['edit', $this->name, 'rules']),
                'options' => [
                    'icon' => 'paint-brush'
                ]
            ],
            [
                'title' => 'Пикер',
                'url'   => href_to_abs('admin', 'controllers', ['edit', $this->name, 'picker']),
                'options' => [
                    'icon' => 'mouse-pointer'
                ]
            ]
        ];
    }
}
