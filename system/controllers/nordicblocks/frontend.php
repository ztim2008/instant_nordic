<?php

class nordicblocks extends cmsFrontend {

    protected $useOptions = true;

    public function route($uri) {

        $action_name = $this->parseRoute($this->cms_core->uri);

        if (!$action_name) {
            return cmsCore::error404();
        }

        $this->runAction($action_name);
    }

    public function actionIndex() {
        return cmsCore::error404();
    }
}
