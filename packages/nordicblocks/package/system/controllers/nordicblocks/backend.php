<?php

class backendNordicblocks extends cmsBackend {

    protected $useOptions = false;

    public function actionIndex() {
        $this->redirectToAction('pages');
    }

    public function getBackendMenu() {
        return [
            [
                'title'   => 'Страницы',
                'url'     => href_to($this->root_url, 'pages'),
                'options' => ['icon' => 'file-alt']
            ],
            [
                'title'   => 'Дизайн-система',
                'url'     => href_to($this->root_url, 'design'),
                'options' => ['icon' => 'paint-brush']
            ],
        ];
    }
}
