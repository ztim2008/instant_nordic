<?php

class actionLandingbuilderPages extends cmsAction {

    public function run() {
        $pages = $this->model->getPagesForAdmin();

        foreach ($pages as &$page) {
            $page['canvas_url'] = href_to($this->controller->root_url, 'canvas', [$page['key']]);
        }

        return $this->cms_template->render('backend/pages', [
            'menu'               => $this->controller->getBackendMenu(),
            'pages'              => $pages,
            'is_schema_installed'=> $this->model->hasInstalledSchema()
        ]);
    }
}