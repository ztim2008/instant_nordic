<?php

class widgetNordicblocksBlock extends cmsWidget {

    public function run() {

        $block_id = (int) ($this->options['block_id'] ?? 0);
        if (!$block_id) {
            return ['html' => '', 'inline_css' => ''];
        }

        $model = cmsCore::getModel('nordicblocks');
        if (!$model) {
            return ['html' => '', 'inline_css' => ''];
        }

        $block = $model->getBlockById($block_id);
        if (!$block || $block['status'] !== 'active') {
            return ['html' => '', 'inline_css' => ''];
        }

        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
        if (!$type) {
            return ['html' => '', 'inline_css' => ''];
        }

        $render_file = cmsConfig::get('root_path')
            . 'system/controllers/nordicblocks/blocks/' . $type . '/render.php';

        if (!file_exists($render_file)) {
            return ['html' => '', 'inline_css' => ''];
        }

        $tokens     = $model->getDesignTokens();
        $inline_css = $model->buildInlineCss($tokens);

        $props      = (array) ($block['props'] ?? []);
        $block_type = $type;
        $block_uid  = 'widget_block_' . $block_id;

        ob_start();
        include $render_file;
        $html = ob_get_clean();

        return [
            'html'       => $html,
            'inline_css' => $inline_css,
        ];
    }
}
