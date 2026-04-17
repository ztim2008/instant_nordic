<?php

class actionNordicblocksBlocks extends cmsAction {

    public function run() {
        $blocks = $this->model->getBlocks();
        $block_definitions = $this->model->getBlockDefinitions();
        $block_types       = [];
        $widgets_url       = href_to('admin', 'widgets');
        $template_name     = (string) cmsConfig::get('template');

        foreach ($block_definitions as $block_name => $definition) {
            $block_types[$block_name] = $definition['title'];
        }

        foreach ($blocks as &$block) {
            $block_type = (string) ($block['type'] ?? '');
            $block['editor_url'] = href_to($this->controller->root_url, 'block_edit', (int) $block['id']);
            $block['place_url']  = $widgets_url . '?' . http_build_query([
                'template_name'               => $template_name,
                'open_tab'                    => 'all-widgets',
                'highlight_widget'            => 'nordicblocks_block',
                'highlight_widget_controller' => '',
                'nb_block_id'                 => (int) ($block['id'] ?? 0),
                'nb_block_title'              => (string) ($block['title'] ?? ''),
            ]);
            $block['definition'] = $block_definitions[$block_type] ?? null;
        }
        unset($block);

        return $this->cms_template->render('backend/blocks', [
            'menu'             => $this->controller->getBackendMenu(),
            'blocks'           => $blocks,
            'block_types'      => $block_types,
            'create_block_url' => href_to($this->controller->root_url, 'block_create'),
            'delete_block_url' => href_to($this->controller->root_url, 'block_delete'),
            'widgets_url'      => $widgets_url,
        ]);
    }
}
