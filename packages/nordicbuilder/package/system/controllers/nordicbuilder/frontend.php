<?php

class nordicbuilder extends cmsFrontend {

    protected $useOptions = true;

    public function actionIndex() {
        return cmsCore::error404();
    }
}