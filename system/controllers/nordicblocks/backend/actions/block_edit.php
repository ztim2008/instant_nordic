<?php

class actionNordicblocksBlockEdit extends cmsAction {

    public function run($block_id = 0) {
        $block_id = (int) $block_id;
        $block    = $this->model->getBlockById($block_id);

        if (!$block) {
            return cmsCore::error404();
        }

        if (!$this->model->isFirstWaveBlockType((string) ($block['type'] ?? ''))) {
            cmsCore::addFlashMessage('info', 'Этот тип блока выведен из активного потока первой волны. Сейчас редактор поддерживается только для hero и faq.');
            return $this->redirect(href_to($this->controller->root_url, 'blocks'));
        }

        $block['props'] = $this->model->normalizeImagePropsByType((string) ($block['type'] ?? ''), (array) ($block['props'] ?? []));

        $block_registry = $this->loadBlockRegistry();
        $tokens         = $this->model->getDesignTokens();
        $inline_css     = $this->model->buildInlineCss($tokens);
        $template_name  = (string) cmsConfig::get('template');

        $canvas_url       = href_to($this->controller->root_url, 'block_canvas', $block_id);
        $editor_state_url = href_to($this->controller->root_url, 'block_editor_state', $block_id);
        $template_name_view = 'backend/editor_hero_v2';
        $place_url = href_to('admin', 'widgets') . '?' . http_build_query([
            'template_name'               => $template_name,
            'open_tab'                    => 'all-widgets',
            'highlight_widget'            => 'nordicblocks_block',
            'highlight_widget_controller' => '',
            'nb_block_id'                 => $block_id,
            'nb_block_title'              => (string) ($block['title'] ?? ''),
        ]);

        return $this->cms_template->render($template_name_view, [
            'menu'           => $this->controller->getBackendMenu(),
            'block'          => $block,
            'block_registry' => $block_registry,
            'image_presets'  => $this->getImagePresetOptions(),
            'inline_css'     => $inline_css,
            'save_url'       => href_to($this->controller->root_url, 'block_save', [$block_id]),
            'editor_state_url' => $editor_state_url,
            'canvas_url'     => $canvas_url,
            'place_url'      => $place_url,
            'back_url'       => href_to($this->controller->root_url, 'blocks'),
            'widgets_url'    => href_to('admin', 'widgets'),
        ]);
    }

    private function loadBlockRegistry() {
        return $this->model->getFirstWaveBlockDefinitions();
    }

    private function getImagePresetOptions() {
        $presets = cmsCore::getModel('images')->getPresetsList(true);
        return ['original' => defined('LANG_PARSER_IMAGE_SIZE_ORIGINAL') ? LANG_PARSER_IMAGE_SIZE_ORIGINAL : 'Оригинал'] + $presets;
    }
}
