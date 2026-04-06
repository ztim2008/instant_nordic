<?php

class actionNordicbuilderView extends cmsAction {

    public function run($page_key = 'homepage') {

        $landingbuilder = cmsCore::getController('landingbuilder', $this->request, false);
        if (!$landingbuilder) {
            return cmsCore::error404();
        }

        return $landingbuilder->runAction('view', [$page_key]);
    }
}
