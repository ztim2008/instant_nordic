<?php

class actionNordicblocksBlocks extends cmsAction {

    public function run() {
        $blocks = $this->model->getBlocks();
        $block_definitions = $this->model->getBlockDefinitions();
        $block_types       = [];

        foreach ($block_definitions as $block_name => $definition) {
            $block_types[$block_name] = $definition['title'];
        }

        foreach ($blocks as &$block) {
            $block['editor_url'] = href_to($this->controller->root_url, 'block_edit', (int) $block['id']);
            $block['place_url']  = href_to($this->controller->root_url, 'block_place', (int) $block['id']);
        }
        unset($block);

        return $this->cms_template->render('backend/blocks', [
            'menu'             => $this->controller->getBackendMenu(),
            'blocks'           => $blocks,
            'block_types'      => $block_types,
            'create_block_url' => href_to($this->controller->root_url, 'block_create'),
            'delete_block_url' => href_to($this->controller->root_url, 'block_delete'),
            'widgets_url'      => href_to('admin', 'widgets'),
        ]);
    }
}
