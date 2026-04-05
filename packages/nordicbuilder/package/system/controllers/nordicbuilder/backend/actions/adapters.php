<?php

class actionNordicbuilderAdapters extends cmsAction {

    public function run() {
        return $this->cms_template->render([
            'page_title' => 'Адаптеры',
            'page_note'  => 'Здесь будет registry widget/data adapters для `nordicbuilder`.'
        ]);
    }
}