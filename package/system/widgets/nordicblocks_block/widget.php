<?php

class widgetNordicblocksBlock extends cmsWidget {

    public function run() {

        $block_id = (int) ($this->options['block_id'] ?? 0);
        if (!$block_id) {
            return ['html' => '', 'inline_css' => '', 'blocks_css' => ''];
        }

        $model = cmsCore::getModel('nordicblocks');
        if (!$model) {
            return ['html' => '', 'inline_css' => '', 'blocks_css' => ''];
        }

        $block = $model->getBlockById($block_id);
        if (!$block || $block['status'] !== 'active') {
            return ['html' => '', 'inline_css' => '', 'blocks_css' => ''];
        }

        $block = $model->hydrateBlockForRender($block, ['mode' => 'widget']);

        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
        if (!$type) {
            return ['html' => '', 'inline_css' => '', 'blocks_css' => ''];
        }

        $render_file = cmsConfig::get('root_path')
            . 'system/controllers/nordicblocks/blocks/' . $type . '/render.php';

        if (!file_exists($render_file)) {
            return ['html' => '', 'inline_css' => '', 'blocks_css' => ''];
        }

        $tokens     = $model->getDesignTokens();
        $inline_css = $model->buildInlineCss($tokens);
        $blocks_css = @file_get_contents(cmsConfig::get('root_path') . 'system/controllers/nordicblocks/assets/blocks.css') ?: '';
        $cache_profile = $model->buildRenderCacheProfile($block, [
            'surface'        => 'widget',
            'mode'           => 'widget',
            'design_version' => $model->getDesignCacheVersion(),
        ]);

        if (!empty($cache_profile['cacheEligible'])) {
            $cached = $model->getCachedBlock((string) $cache_profile['cacheKey']);
            if ($cached !== null) {
                return [
                    'html'       => $cached,
                    'inline_css' => $inline_css,
                    'blocks_css' => $blocks_css,
                ];
            }
        }

        $props          = (array) ($block['props'] ?? []);
        $block_contract = (array) ($block['contract'] ?? []);
        $block_type     = $type;
        $block_uid      = 'widget_block_' . $block_id;
        $block_css_overlay_css = $model->buildBlockCssOverlayRuntimeCss($block, $block_uid);

        ob_start();
        include $render_file;
        $html = ob_get_clean();

        if ($block_css_overlay_css !== '') {
            $html = '<style data-nb-block-css-overlay="' . htmlspecialchars((string) $block_uid, ENT_QUOTES, 'UTF-8') . '">' . str_ireplace('</style', '<\\/style', (string) $block_css_overlay_css) . '</style>' . $html;
        }

        if (!empty($cache_profile['cacheEligible'])) {
            $model->setCachedBlock((string) $cache_profile['cacheKey'], (string) $html, 3600);
        }

        return [
            'html'       => $html,
            'inline_css' => $inline_css,
            'blocks_css' => $blocks_css,
        ];
    }
}