<?php

class actionNordicbuilderComponents extends cmsAction {

    public function run() {
        return $this->cms_template->render([
            'page_title' => 'Компоненты',
            'page_note'  => 'Здесь будет библиотека компонентов для `nordicbuilder`.'
        ]);
    }
}