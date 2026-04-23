<?php

class backendNordicblocks extends cmsBackend {

    protected $useOptions = false;

    public function actionIndex() {
        $this->redirectToAction('blocks');
    }

    public function getBackendMenu() {
        return [
            [
                'title'   => 'Блоки',
                'url'     => href_to($this->root_url, 'blocks'),
                'options' => ['icon' => 'cube']
            ],
            [
                'title'   => 'Каталог блоков',
                'url'     => href_to($this->root_url, 'catalog'),
                'options' => ['icon' => 'th-large']
            ],
            [
                'title'   => 'Дизайн-система',
                'url'     => href_to($this->root_url, 'design'),
                'options' => ['icon' => 'paint-brush']
            ],
        ];
    }
}
