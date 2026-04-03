<?php

class actionLandingbuilderWidgetsCatalog extends cmsAction {

    public function run() {

        if (!$this->request->isAjax() || !cmsUser::isAdmin()) {
            return cmsCore::error404();
        }

        return $this->cms_template->renderJSON([
            'error'   => false,
            'widgets' => $this->model->getAvailableSystemWidgets()
        ]);
    }
}