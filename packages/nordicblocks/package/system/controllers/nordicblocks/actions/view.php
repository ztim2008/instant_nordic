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

        $html        = '';
        $blocks_base = dirname(__DIR__) . '/blocks';

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

            $is_dynamic = !empty($block['contract']['runtime']['adapter']['isDynamic']);

            // Пробуем SSR‑кэш
            $cache_key = 'page_' . $page['id'] . '_' . $uid . '_'
                . substr(md5(json_encode($block)), 0, 8);

            if (!$is_dynamic) {
                $cached = $this->model->getCachedBlock($cache_key);
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
            $block_html     = $this->renderBlock($render_file, $type, $uid, $props, $block_contract);

            // Кэшируем на 1 час
            if (!$is_dynamic) {
                $this->model->setCachedBlock($cache_key, $block_html, 3600);
            }
            $html .= $block_html;
        }

        return $html;
    }

    private function renderBlock($render_file, $type, $uid, array $props, array $block_contract = []) {
        // Каждый блок получает $props и $uid; возвращает HTML-строку
        ob_start();
        $block_type = $type;
        $block_uid  = $uid;
        include $render_file;
        return ob_get_clean();
    }
}
