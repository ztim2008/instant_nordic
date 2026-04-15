<?php

class widgetNordicblocksPage extends cmsWidget {

    public function run() {

        $page_key = trim((string) ($this->options['page_key'] ?? ''));
        if ($page_key === '') {
            return ['html' => '', 'inline_css' => ''];
        }

        $model = cmsCore::getModel('nordicblocks');
        if (!$model) {
            return ['html' => '', 'inline_css' => ''];
        }

        $page = $model->getPageByKey($page_key);
        if (!$page || $page['status'] !== 'published') {
            return ['html' => '', 'inline_css' => ''];
        }

        $tokens     = $model->getDesignTokens();
        $inline_css = $model->buildInlineCss($tokens);
        $html       = $this->renderBlocks($page, $model);

        return [
            'html'       => $html,
            'inline_css' => $inline_css,
        ];
    }

    private function renderBlocks(array $page, $model) {
        $blocks = $page['blocks'] ?? [];
        if (!$blocks) {
            return '';
        }

        $html        = '';
        $blocks_base = cmsConfig::get('root_path') . 'system/controllers/nordicblocks/blocks';

        foreach ($blocks as $block) {
            $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
            $uid  = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($block['uid'] ?? 'x'));
            if (!$type) {
                continue;
            }

            $cache_key = 'page_' . $page['id'] . '_' . $uid . '_'
                . substr(md5(json_encode($block)), 0, 8);

            $cached = $model->getCachedBlock($cache_key);
            if ($cached !== null) {
                $html .= $cached;
                continue;
            }

            $render_file = "{$blocks_base}/{$type}/render.php";
            if (!file_exists($render_file)) {
                continue;
            }

            $props      = isset($block['props']) && is_array($block['props']) ? $block['props'] : [];
            $block_type = $type;
            $block_uid  = $uid;

            ob_start();
            include $render_file;
            $block_html = ob_get_clean();

            $model->setCachedBlock($cache_key, $block_html, 3600);
            $html .= $block_html;
        }

        return $html;
    }
}
