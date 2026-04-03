<?php

class actionLandingbuilderCanvas extends cmsAction {

    public function run($page_key = 'homepage') {
        $page = $this->model->getPageByKey($page_key);
        if (!$page) {
            return cmsCore::error404();
        }

        $screen = $this->model->getCanvasScreen($page, $this->options);
        $screen['api'] = [
            'widgets_catalog_url' => href_to($this->controller->root_url, 'widgets_catalog'),
            'widget_options_url'  => href_to($this->controller->root_url, 'widget_options'),
            'canvas_save_url'     => href_to($this->controller->root_url, 'canvas_save'),
            'versions_url'        => href_to($this->controller->root_url, 'versions'),
            'version_restore_url' => href_to($this->controller->root_url, 'version_restore')
        ];

        return $this->cms_template->render('backend/canvas', [
            'menu'   => $this->controller->getBackendMenu(),
            'page'   => $page,
            'screen' => $screen
        ]);
    }
}