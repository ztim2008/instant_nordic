<?php

class frontendLandingbuilder extends cmsFrontend {

    public function actionIndex() {
        return cmsCore::error404();
    }
}