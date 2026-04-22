<?php

class actionNordicblocksPages extends cmsAction {

    public function run() {
        $pages = $this->model->getPages();

        foreach ($pages as &$page) {
            $blocks = !empty($page['blocks_json'])
                ? (array) json_decode($page['blocks_json'], true)
                : [];
            $page['blocks_count'] = count($blocks);
            $page['editor_url']   = href_to('admin', 'controllers', ['edit', $this->controller->root_url, 'editor', $page['id']]);
            $page['view_url']     = href_to('nordicblocks', 'view', [$page['key']]);
        }
        unset($page);

        return $this->cms_template->render('backend/pages', [
            'menu'             => $this->controller->getBackendMenu(),
            'pages'            => $pages,
            'create_page_url'  => href_to($this->controller->root_url, 'create_page'),
            'set_status_url'   => href_to($this->controller->root_url, 'set_page_status'),
        ]);
    }
}
