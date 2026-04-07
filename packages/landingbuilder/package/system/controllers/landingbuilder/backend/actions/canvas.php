<?php

class actionLandingbuilderCanvas extends cmsAction {

    public function run($page_key = 'homepage') {
        $this->model->migrateLegacyPageModesToInstantContentBody($this->cms_user->id);
        $page = $this->model->getPageByKey($page_key);
        if (!$page) {
            return cmsCore::error404();
        }

        $screen = $this->model->getCanvasScreen($page, $this->options);
        $screen['api'] = [
            'widgets_catalog_url' => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'widgets_catalog']),
            'widget_options_url'  => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'widget_options']),
            'canvas_save_url'     => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'canvas_save']),
            'versions_url'        => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'versions']),
            'version_restore_url' => href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'version_restore'])
        ];
        $screen['preview_url'] = href_to('landingbuilder', 'view', [$page['key']]);
        $screen['design_url'] = href_to('admin', 'controllers', ['edit', $this->controller->name, 'design']);

        return $this->cms_template->render('backend/canvas', [
            'menu'   => $this->controller->getBackendMenu(),
            'page'   => $page,
            'screen' => $screen
        ]);
    }
}