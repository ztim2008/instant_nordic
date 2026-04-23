<?php

/**
 * Публичный рендер страницы NordicBlocks.
 * SSR: каждый блок рендерится PHP, HTML кэшируется в БД.
 * Весь HTML страницы склеивается и выдаётся шаблону.
 */
class actionNordicblocksView extends cmsAction {

    public function run($page_key = '') {
        $page_key = preg_replace('/[^a-z0-9\-_]/i', '', (string) $page_key);

        $page = $this->model->getPageByKey($page_key);
        if (!$page) {
            return cmsCore::error404();
        }

        // Неопубликованная страница — только для администратора
        if ($page['status'] !== 'published' && !$this->cms_user->is_admin) {
            return cmsCore::error404();
        }

        $tokens     = $this->model->getDesignTokens();
        $inline_css = $this->model->buildInlineCss($tokens);
        $blocks_html = $this->renderPageBlocks($page);

        $this->cms_template->setPageTitle($page['title']);

        return $this->cms_template->render('view', [
            'page'        => $page,
            'blocks_html' => $blocks_html,
            'inline_css'  => $inline_css,
        ]);
    }

    private function renderPageBlocks(array $page) {
        $blocks = $page['blocks'] ?? [];
        if (!$blocks) {
            return '';
        }

        $html           = '';
        $blocks_base    = dirname(__DIR__) . '/blocks';
        $design_version = $this->model->getDesignCacheVersion();

        foreach ($blocks as $block) {
            $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
            $uid  = preg_replace('/[^a-zA-Z0-9_\-]/',  '', (string) ($block['uid'] ?? 'x'));
            if (!$type) {
                continue;
            }

            $block = $this->model->hydrateBlockForRender($block, [
                'mode'    => 'legacy_view',
                'page_id' => (int) ($page['id'] ?? 0),
                'uid'     => $uid,
            ]);

            $cache_profile = $this->model->buildRenderCacheProfile($block, [
                'surface'        => 'legacy_view',
                'mode'           => 'legacy_view',
                'page_id'        => (int) ($page['id'] ?? 0),
                'uid'            => $uid,
                'design_version' => $design_version,
            ]);

            if (!empty($cache_profile['cacheEligible'])) {
                $cached = $this->model->getCachedBlock((string) $cache_profile['cacheKey']);
                if ($cached !== null) {
                    $html .= $cached;
                    continue;
                }
            }

            // Рендерим блок через render.php
            $render_file = "{$blocks_base}/{$type}/render.php";
            if (!file_exists($render_file)) {
                continue;
            }

            $props          = isset($block['props']) && is_array($block['props']) ? $block['props'] : [];
            $block_contract = isset($block['contract']) && is_array($block['contract']) ? $block['contract'] : [];
            $block_html     = $this->renderBlock($render_file, $type, $uid, $props, $block_contract, $this->model->buildBlockCssOverlayRuntimeCss($block, $uid));

            if (!empty($cache_profile['cacheEligible'])) {
                $this->model->setCachedBlock((string) $cache_profile['cacheKey'], $block_html, 3600);
            }
            $html .= $block_html;
        }

        return $html;
    }

    private function renderBlock($render_file, $type, $uid, array $props, array $block_contract = [], $block_css_overlay_css = '') {
        // Каждый блок получает $props и $uid; возвращает HTML-строку
        ob_start();
        $block_type = $type;
        $block_uid  = $uid;
        include $render_file;
        $block_html = ob_get_clean();

        if ($block_css_overlay_css !== '') {
            $block_html = '<style data-nb-block-css-overlay="' . htmlspecialchars((string) $uid, ENT_QUOTES, 'UTF-8') . '">' . str_ireplace('</style', '<\\/style', (string) $block_css_overlay_css) . '</style>' . $block_html;
        }

        return $block_html;
    }
}
