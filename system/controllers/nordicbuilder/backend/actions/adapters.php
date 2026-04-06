<?php

class actionNordicbuilderAdapters extends cmsAction {

    public function run() {
        return $this->cms_template->render([
            'page_title' => 'Адаптеры',
            'page_note'  => 'Здесь будет реестр адаптеров данных и системных виджетов.'
        ]);
    }
}