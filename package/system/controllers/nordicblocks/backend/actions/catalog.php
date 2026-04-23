<?php

class actionNordicblocksCatalog extends cmsAction {

    public function run() {
        return $this->redirect(href_to($this->controller->root_url, 'blocks'));
    }
}