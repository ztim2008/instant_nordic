<?php

class actionNordicblocksBlocks extends cmsAction {

    public function run() {
        $blocks                  = $this->model->getBlocks();
        $block_definitions       = $this->model->getEditorSupportedBlockDefinitions();
        $cache_stats             = $this->model->getCacheStats();
        $catalog_renderer_version = $this->model->getRenderCacheVersion('catalog_browser');
        $block_types             = [];
        $visible_blocks          = [];
        $hidden_legacy_count     = 0;
        $widgets_url             = href_to('admin', 'widgets');
        $template_name           = (string) cmsConfig::get('template');

        foreach ($block_definitions as $block_name => $definition) {
            $block_types[$block_name] = $definition['title'];
        }

        foreach ($blocks as $block) {
            $block_type = (string) ($block['type'] ?? '');

            if (!$this->model->isEditorSupportedBlockType($block_type)) {
                $hidden_legacy_count++;
                continue;
            }

            $block['editor_url'] = href_to($this->controller->root_url, 'block_edit', (int) $block['id']);
            $block['editor_mode'] = $this->model->getBlockEditorMode($block_type);
            $block['place_url']  = $widgets_url . '?' . http_build_query([
                'template_name'               => $template_name,
                'open_tab'                    => 'all-widgets',
                'highlight_widget'            => 'nordicblocks_block',
                'highlight_widget_controller' => '',
                'nb_block_id'                 => (int) ($block['id'] ?? 0),
                'nb_block_title'              => (string) ($block['title'] ?? ''),
            ]);
            $block['definition'] = $block_definitions[$block_type] ?? null;
            $visible_blocks[] = $block;
        }

        return $this->cms_template->render('backend/blocks', [
            'menu'             => $this->controller->getBackendMenu(),
            'blocks'           => $visible_blocks,
            'block_types'      => $block_types,
            'hidden_legacy_count' => $hidden_legacy_count,
            'cache_stats'      => $cache_stats,
            'catalog_renderer_version' => $catalog_renderer_version,
            'create_block_url' => href_to($this->controller->root_url, 'block_create'),
            'delete_block_url' => href_to($this->controller->root_url, 'block_delete'),
            'flush_cache_url'  => href_to($this->controller->root_url, 'flush_ssr_cache'),
            'widgets_url'      => $widgets_url,
        ]);
    }
}
