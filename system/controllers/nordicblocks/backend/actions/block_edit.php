<?php

class actionNordicblocksBlockEdit extends cmsAction {

    public function run($block_id = 0) {
        $block_id = (int) $block_id;
        $block    = $this->model->getBlockById($block_id);

        if (!$block) {
            return cmsCore::error404();
        }

        $block_registry = $this->loadBlockRegistry();
        $tokens         = $this->model->getDesignTokens();
        $inline_css     = $this->model->buildInlineCss($tokens);

        $canvas_url = href_to($this->controller->root_url, 'block_canvas', $block_id);

        return $this->cms_template->render('backend/editor', [
            'menu'           => $this->controller->getBackendMenu(),
            'block'          => $block,
            'block_registry' => $block_registry,
            'inline_css'     => $inline_css,
            'save_url'       => href_to($this->controller->root_url, 'block_save', [$block_id]),
            'canvas_url'     => $canvas_url,
            'place_url'      => href_to($this->controller->root_url, 'block_place', $block_id),
            'back_url'       => href_to($this->controller->root_url, 'blocks'),
            'widgets_url'    => href_to('admin', 'widgets'),
        ]);
    }

    private function loadBlockRegistry() {
        $blocks_dir = cmsConfig::get('root_path') . 'system/controllers/nordicblocks/blocks';
        $registry   = [];

        if (!is_dir($blocks_dir)) {
            return $registry;
        }

        foreach (scandir($blocks_dir) as $block_name) {
            if ($block_name[0] === '.') {
                continue;
            }
            $schema_file = "{$blocks_dir}/{$block_name}/schema.json";
            if (!file_exists($schema_file)) {
                continue;
            }
            $schema = json_decode(file_get_contents($schema_file), true);
            if (!is_array($schema)) {
                continue;
            }
            $registry[$block_name] = [
                'name'     => $block_name,
                'title'    => $schema['title']    ?? $block_name,
                'category' => $schema['category'] ?? 'content',
                'schema'   => $schema,
            ];
        }

        return $registry;
    }
}
