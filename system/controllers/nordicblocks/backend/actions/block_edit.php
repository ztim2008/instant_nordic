<?php

class actionNordicblocksBlockEdit extends cmsAction {

    public function run($block_id = 0) {
        $block_id = (int) $block_id;
        $block    = $this->model->getBlockById($block_id);

        if (!$block) {
            return cmsCore::error404();
        }

        $block['props'] = $this->model->normalizeImagePropsByType((string) ($block['type'] ?? ''), (array) ($block['props'] ?? []));

        $block_registry = $this->loadBlockRegistry();
        $tokens         = $this->model->getDesignTokens();
        $inline_css     = $this->model->buildInlineCss($tokens);

        $canvas_url       = href_to($this->controller->root_url, 'block_canvas', $block_id);
        $editor_state_url = href_to($this->controller->root_url, 'block_editor_state', $block_id);
        $template_name    = ((string) ($block['type'] ?? '') === 'hero') ? 'backend/editor_hero_v2' : 'backend/editor';

        return $this->cms_template->render($template_name, [
            'menu'           => $this->controller->getBackendMenu(),
            'block'          => $block,
            'block_registry' => $block_registry,
            'image_presets'  => $this->getImagePresetOptions(),
            'inline_css'     => $inline_css,
            'save_url'       => href_to($this->controller->root_url, 'block_save', [$block_id]),
            'editor_state_url' => $editor_state_url,
            'canvas_url'     => $canvas_url,
            'place_url'      => href_to($this->controller->root_url, 'block_place', $block_id),
            'back_url'       => href_to($this->controller->root_url, 'blocks'),
            'widgets_url'    => href_to('admin', 'widgets'),
        ]);
    }

    private function loadBlockRegistry() {
        return $this->model->getBlockDefinitions();
    }

    private function getImagePresetOptions() {
        $presets = cmsCore::getModel('images')->getPresetsList(true);
        return ['original' => defined('LANG_PARSER_IMAGE_SIZE_ORIGINAL') ? LANG_PARSER_IMAGE_SIZE_ORIGINAL : 'Оригинал'] + $presets;
    }
}
