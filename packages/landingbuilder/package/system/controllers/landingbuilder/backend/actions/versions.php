<?php

class actionLandingbuilderVersions extends cmsAction {

    public function run() {

        if (!$this->request->isAjax() || !cmsUser::isAdmin()) {
            return cmsCore::error404();
        }

        $page_key = $this->request->get('page_key', '');
        if (!$page_key) {
            return $this->cms_template->renderJSON(['error' => true, 'message' => 'Page key is required']);
        }

        return $this->cms_template->renderJSON([
            'error'    => false,
            'versions' => $this->model->getPageVersionsByKey($page_key)
        ]);
    }
}