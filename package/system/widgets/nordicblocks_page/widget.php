<?php

class widgetNordicblocksPage extends cmsWidget {

    public function run() {

        $page_key = trim((string) ($this->options['page_key'] ?? ''));
        if ($page_key === '') {
            return ['html' => '', 'inline_css' => '', 'blocks_css' => ''];
        }

        $model = cmsCore::getModel('nordicblocks');
        if (!$model) {
            return ['html' => '', 'inline_css' => '', 'blocks_css' => ''];
        }

        $page = $model->getPageByKey($page_key);
        if (!$page || $page['status'] !== 'published') {
            return ['html' => '', 'inline_css' => '', 'blocks_css' => ''];
        }

        $tokens     = $model->getDesignTokens();
        $inline_css = $model->buildInlineCss($tokens);
        $blocks_css = @file_get_contents(cmsConfig::get('root_path') . 'system/controllers/nordicblocks/assets/blocks.css') ?: '';
        $html       = $this->renderBlocks($page, $model);

        return [
            'html'       => $html,
            'inline_css' => $inline_css,
            'blocks_css' => $blocks_css,
        ];
    }

    private function renderBlocks(array $page, $model) {
        $blocks = $page['blocks'] ?? [];
        if (!$blocks) {
            return '';
        }

        $html           = '';
        $blocks_base    = cmsConfig::get('root_path') . 'system/controllers/nordicblocks/blocks';
        $design_version = $model->getDesignCacheVersion();

        foreach ($blocks as $block) {
            $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
            $uid  = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($block['uid'] ?? 'x'));
            if (!$type) {
                continue;
            }

            $block = $model->hydrateBlockForRender($block, [
                'mode'    => 'widget_page',
                'page_id' => (int) ($page['id'] ?? 0),
                'uid'     => $uid,
            ]);

            $cache_profile = $model->buildRenderCacheProfile($block, [
                'surface'        => 'widget_page',
                'mode'           => 'widget_page',
                'page_id'        => (int) ($page['id'] ?? 0),
                'uid'            => $uid,
                'design_version' => $design_version,
            ]);

            if (!empty($cache_profile['cacheEligible'])) {
                $cached = $model->getCachedBlock((string) $cache_profile['cacheKey']);
                if ($cached !== null) {
                    $html .= $cached;
                    continue;
                }
            }

            $render_file = "{$blocks_base}/{$type}/render.php";
            if (!file_exists($render_file)) {
                continue;
            }

            $props          = isset($block['props']) && is_array($block['props']) ? $block['props'] : [];
            $block_contract = isset($block['contract']) && is_array($block['contract']) ? $block['contract'] : [];
            $block_type     = $type;
            $block_uid      = $uid;
            $block_css_overlay_css = $model->buildBlockCssOverlayRuntimeCss($block, $uid);

            ob_start();
            include $render_file;
            $block_html = ob_get_clean();

            if ($block_css_overlay_css !== '') {
                $block_html = '<style data-nb-block-css-overlay="' . htmlspecialchars((string) $uid, ENT_QUOTES, 'UTF-8') . '">' . str_ireplace('</style', '<\\/style', (string) $block_css_overlay_css) . '</style>' . $block_html;
            }

            if (!empty($cache_profile['cacheEligible'])) {
                $model->setCachedBlock((string) $cache_profile['cacheKey'], $block_html, 3600);
            }
            $html .= $block_html;
        }

        return $html;
    }
}
