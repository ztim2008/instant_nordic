<?php

class actionNordicbuilderDefaults extends cmsAction {

    public function run() {
        return $this->cms_template->render([
            'page_title' => 'Глобальные стили',
            'page_note'  => 'Secondary UI для site-wide defaults нового компонента `nordicbuilder`.'
        ]);
    }
}