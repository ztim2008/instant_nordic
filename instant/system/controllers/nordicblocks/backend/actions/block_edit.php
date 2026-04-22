<?php

class actionNordicblocksBlockEdit extends cmsAction {

    public function run($block_id = 0) {
        $block_id = (int) $block_id;
        $block    = $this->model->getBlockById($block_id);
        $editor_mode = $block ? $this->model->getBlockEditorMode((string) ($block['type'] ?? '')) : 'inspector_shell';

        if (!$block) {
            return cmsCore::error404();
        }

        if (!$this->model->isEditorSupportedBlockType((string) ($block['type'] ?? ''))) {
            cmsCore::addFlashMessage('info', 'Этот тип блока сейчас не поддерживается в активном editor flow NordicBlocks.');
            return $this->redirect(href_to($this->controller->root_url, 'blocks'));
        }

        $block['props'] = $this->model->normalizeImagePropsByType((string) ($block['type'] ?? ''), (array) ($block['props'] ?? []));

        if ($editor_mode === 'design_canvas') {
            $template_name  = (string) cmsConfig::get('template');
            $place_url = href_to('admin', 'widgets') . '?' . http_build_query([
                'template_name'               => $template_name,
                'open_tab'                    => 'all-widgets',
                'highlight_widget'            => 'nordicblocks_block',
                'highlight_widget_controller' => '',
                'nb_block_id'                 => $block_id,
                'nb_block_title'              => (string) ($block['title'] ?? ''),
            ]);

            return $this->cms_template->render('backend/editor_design_block', [
                'menu'       => $this->controller->getBackendMenu(),
                'block'      => $block,
                'editor_mode'=> $editor_mode,
                'save_url'   => href_to($this->controller->root_url, 'block_save', [$block_id]),
                'state_url'  => href_to($this->controller->root_url, 'block_design_state', $block_id),
                'canvas_url' => href_to($this->controller->root_url, 'block_design_canvas', $block_id),
                'place_url'  => $place_url,
                'back_url'   => href_to($this->controller->root_url, 'blocks'),
            ]);
        }

        $block_registry = $this->loadBlockRegistry();
        $tokens         = $this->model->getDesignTokens();
        $inline_css     = $this->model->buildInlineCss($tokens);
        $template_name  = (string) cmsConfig::get('template');
        $render_version = $this->model->getRenderCacheVersion((string) ($block['type'] ?? ''));
        $css_overlay_enabled = $this->model->supportsBlockCssOverlay((string) ($block['type'] ?? ''));
        $css_overlay_storage_ready = $css_overlay_enabled && $this->model->hasBlockCssOverlayStorage();
        $css_overlay_publish_ready = $css_overlay_enabled && $this->model->hasBlockCssOverlayPublishStorage();
        $css_overlay_revisions_ready = $css_overlay_enabled && $this->model->hasBlockCssOverlayRevisionStorage();

        $canvas_url       = href_to($this->controller->root_url, 'block_canvas', $block_id);
        $editor_state_url = href_to($this->controller->root_url, 'block_editor_state', $block_id);
        $css_overlay_state_url = $css_overlay_storage_ready ? href_to($this->controller->root_url, 'block_css_state', $block_id) : null;
        $css_overlay_save_url = $css_overlay_storage_ready ? href_to($this->controller->root_url, 'block_css_save', $block_id) : null;
        $css_overlay_publish_url = $css_overlay_publish_ready ? href_to($this->controller->root_url, 'block_css_publish', $block_id) : null;
        $css_overlay_revisions_url = $css_overlay_revisions_ready ? href_to($this->controller->root_url, 'block_css_revisions', $block_id) : null;
        $css_overlay_restore_url = $css_overlay_revisions_ready ? href_to($this->controller->root_url, 'block_css_restore', $block_id) : null;
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
            'editor_mode'    => $editor_mode,
            'block_registry' => $block_registry,
            'image_presets'  => $this->getImagePresetOptions(),
            'inline_css'     => $inline_css,
            'save_url'       => href_to($this->controller->root_url, 'block_save', [$block_id]),
            'editor_state_url' => $editor_state_url,
            'canvas_url'     => $canvas_url,
            'css_overlay_enabled' => $css_overlay_enabled,
            'css_overlay_state_url' => $css_overlay_state_url,
            'css_overlay_save_url' => $css_overlay_save_url,
            'css_overlay_publish_url' => $css_overlay_publish_url,
            'css_overlay_revisions_url' => $css_overlay_revisions_url,
            'css_overlay_restore_url' => $css_overlay_restore_url,
            'place_url'      => $place_url,
            'render_version' => $render_version,
            'render_version_label' => (string) ($block['type'] ?? 'block') . ' SSR',
            'back_url'       => href_to($this->controller->root_url, 'blocks'),
            'widgets_url'    => href_to('admin', 'widgets'),
        ]);
    }

    private function loadBlockRegistry() {
        return $this->model->getEditorSupportedBlockDefinitions();
    }

    private function getImagePresetOptions() {
        $presets = cmsCore::getModel('images')->getPresetsList(true);
        return ['original' => defined('LANG_PARSER_IMAGE_SIZE_ORIGINAL') ? LANG_PARSER_IMAGE_SIZE_ORIGINAL : 'Оригинал'] + $presets;
    }
}
