<?php

class actionNordicblocksBlocks extends cmsAction {

    public function run() {
        $blocks = $this->model->getBlocks();

        foreach ($blocks as &$block) {
            $block['editor_url'] = href_to($this->controller->root_url, 'block_edit', (int) $block['id']);
            $block['place_url']  = href_to($this->controller->root_url, 'block_place', (int) $block['id']);
        }
        unset($block);

        return $this->cms_template->render('backend/blocks', [
            'menu'             => $this->controller->getBackendMenu(),
            'blocks'           => $blocks,
            'create_block_url' => href_to($this->controller->root_url, 'block_create'),
            'delete_block_url' => href_to($this->controller->root_url, 'block_delete'),
            'widgets_url'      => href_to('admin', 'widgets'),
        ]);
    }
}
