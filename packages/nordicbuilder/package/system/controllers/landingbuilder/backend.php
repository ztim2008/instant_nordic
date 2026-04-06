<?php

class backendLandingbuilder extends cmsBackend {

    protected $useOptions = true;

    public $useDefaultOptionsAction = true;

    private function isNordicbuilderEnabled() {
        $admin_model = cmsCore::getModel('admin');
        if (!$admin_model) {
            return false;
        }

        $info = $admin_model->getControllerInfo('nordicbuilder');

        return !empty($info) && !empty($info['is_enabled']);
    }

    public function actionIndex() {
        if ($this->isNordicbuilderEnabled()) {
            $this->redirect(href_to_abs('admin', 'controllers', ['edit', 'nordicbuilder', 'pages']));
        }

        $this->redirectToAction('pages');
    }

    public function getBackendMenu() {
        if ($this->isNordicbuilderEnabled()) {
            return [];
        }

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