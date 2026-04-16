<?php

/**
 * NordicBlocks — Canvas preview
 * Выдаёт standalone HTML для iframe редактора.
 *
 * Маршруты:
 *   GET /nordicblocks/canvas/block/{id}   — превью одиночного блока (новая архитектура)
 *   GET /nordicblocks/canvas/{page_key}   — превью страницы (legacy)
 *
 * Доступно только администратору.
 */
class actionNordicblocksCanvas extends cmsAction {

    public function run() {

        // Только для администратора
        if (!$this->cms_user->is_admin) {
            http_response_code(403);
            exit('Forbidden');
        }

        $tokens     = $this->model->getDesignTokens();
        $inline_css = $this->model->buildInlineCss($tokens);
        $assets_dir = dirname(__DIR__) . '/assets';
        $tokens_css = @file_get_contents("{$assets_dir}/tokens.css") ?: '';
        $blocks_css = @file_get_contents("{$assets_dir}/blocks.css") ?: '';

        $base_css = '
*,*::before,*::after{box-sizing:border-box}
html,body{margin:0;padding:0;font-family:var(--nb-font-body,system-ui,sans-serif);background:#fff;color:var(--nb-color-text,#1a1a1a)}
img{max-width:100%;height:auto;display:block}
a{color:inherit;text-decoration:none}
        .nb-container{width:min(100%,1200px);margin-inline:auto;padding-inline:clamp(1rem,4vw,2.5rem)}
';

        // Режим: одиночный блок
        $canvas_block_id = (int) $this->request->get('canvas_block_id', 0);
        if ($canvas_block_id) {
            $block       = $this->model->getBlockById($canvas_block_id);
            $blocks_html = $block ? $this->renderSingleBlock($block) : '';
            $empty_html  = $blocks_html ? '' : '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;gap:1rem;color:#94a3b8;text-align:center;padding:2rem">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/></svg>
                <p style="font-size:1.1rem;font-weight:600;color:#64748b;margin:0">Блок пустой</p>
                <p style="font-size:.9rem;margin:0">Настройте параметры в инспекторе справа</p>
            </div>';
            $this->outputHtml($tokens_css, $inline_css, $base_css, $blocks_css, $blocks_html ?: $empty_html);
        }

        // Режим: страница (legacy)
        $canvas_page_key = preg_replace('/[^a-z0-9\-_]/i', '', (string) $this->request->get('canvas_page_key', ''));
        $page            = $canvas_page_key ? $this->model->getPageByKey($canvas_page_key) : null;
        $blocks_html     = $page ? $this->renderPageBlocks($page) : '';
        $empty_html      = $blocks_html ? '' : '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;gap:1rem;color:#94a3b8;text-align:center;padding:2rem">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <p style="font-size:1.1rem;font-weight:600;color:#64748b;margin:0">Страница пустая</p>
        </div>';
        $this->outputHtml($tokens_css, $inline_css, $base_css, $blocks_css, $blocks_html ?: $empty_html);
    }

    private function outputHtml($tokens_css, $inline_css, $base_css, $blocks_css, $content) {
        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN');
        echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>Preview</title>';
        echo '<style>' . $tokens_css . '</style>';
        echo '<style>' . $inline_css . '</style>';
        echo '<style>' . $base_css . '</style>';
        echo '<style>' . $blocks_css . '</style>';
        echo '</head><body>';
        echo $content;
        echo '</body></html>';
        exit;
    }

    private function renderSingleBlock(array $block) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
        if (!$type) { return ''; }

        $block = $this->model->hydrateBlockForRender($block, ['mode' => 'legacy_canvas_single']);

        $render_file = dirname(__DIR__) . "/blocks/{$type}/render.php";
        if (!file_exists($render_file)) { return ''; }

        $props          = (array) ($block['props'] ?? []);
        $block_contract = (array) ($block['contract'] ?? []);
        $block_type     = $type;
        $block_uid      = 'block_' . (int) $block['id'];

        ob_start();
        include $render_file;
        return ob_get_clean();
    }

    private function renderPageBlocks(array $page) {
        $blocks = $page['blocks'] ?? [];
        if (!$blocks) { return ''; }

        $html        = '';
        $blocks_base = dirname(__DIR__) . '/blocks';

        foreach ($blocks as $block) {
            $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
            $uid  = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($block['uid'] ?? 'x'));
            if (!$type) { continue; }

            $render_file = "{$blocks_base}/{$type}/render.php";
            if (!file_exists($render_file)) { continue; }

            $block = $this->model->hydrateBlockForRender($block, [
                'mode'    => 'legacy_canvas_page',
                'page_id' => (int) ($page['id'] ?? 0),
                'uid'     => $uid,
            ]);

            $props      = (array) ($block['props'] ?? []);
            $block_contract = (array) ($block['contract'] ?? []);
            $block_type = $type;
            $block_uid  = $uid;

            ob_start();
            include $render_file;
            $html .= ob_get_clean();
        }

        return $html;
    }
}
