<?php

class actionNordicblocksBlockCanvas extends cmsAction {

    public function run($block_id = 0) {
        if (!$this->cms_user->is_admin) {
            http_response_code(403);
            exit('Forbidden');
        }

        $block_id = (int) $block_id;
        $block    = $block_id ? $this->model->getBlockById($block_id) : null;

        if (!$block) {
            return cmsCore::error404();
        }

        $tokens     = $this->model->getDesignTokens();
        $inline_css = $this->model->buildInlineCss($tokens);
        $assets_dir = dirname(dirname(__DIR__)) . '/assets';
        $tokens_css = @file_get_contents("{$assets_dir}/tokens.css") ?: '';
        $blocks_css = @file_get_contents("{$assets_dir}/blocks.css") ?: '';

        $base_css = '
*,*::before,*::after{box-sizing:border-box}
html,body{margin:0;padding:0;font-family:var(--nb-font-body,system-ui,sans-serif);background:#fff;color:var(--nb-color-text,#1a1a1a)}
img{max-width:100%;height:auto;display:block}
a{color:inherit;text-decoration:none}
.nb-container{width:min(100%,1200px);margin-inline:auto;padding-inline:clamp(1rem,4vw,2.5rem)}
.nb-btn{display:inline-flex;align-items:center;gap:.4em;padding:.65em 1.5em;border-radius:var(--nb-radius-btn,.5rem);font-size:var(--nb-text-base,1rem);font-weight:600;cursor:pointer;border:2px solid transparent;transition:opacity .15s,transform .1s;text-decoration:none}
.nb-btn:active{transform:scale(.97)}
.nb-btn--primary{background:var(--nb-color-accent,#b42318);color:#fff;border-color:var(--nb-color-accent,#b42318)}
.nb-btn--primary:hover{opacity:.9}
.nb-btn--outline{background:transparent;color:var(--nb-color-accent,#b42318);border-color:var(--nb-color-accent,#b42318)}
.nb-btn--outline:hover{background:var(--nb-color-accent,#b42318);color:#fff}
';

        $block_html = $this->renderSingleBlock($block);
        $empty_html = $block_html ? '' : '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;gap:1rem;color:#94a3b8;text-align:center;padding:2rem">'
            . '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/></svg>'
            . '<p style="font-size:1.1rem;font-weight:600;color:#64748b;margin:0">Блок пустой</p>'
            . '<p style="font-size:.9rem;margin:0">Настройте параметры в инспекторе справа</p>'
            . '</div>';

        $this->outputHtml($tokens_css, $inline_css, $base_css, $blocks_css, $block_html ?: $empty_html);
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
        if (!$type) {
            return '';
        }

        $render_file = dirname(dirname(__DIR__)) . "/blocks/{$type}/render.php";
        if (!file_exists($render_file)) {
            return '';
        }

        $props      = (array) ($block['props'] ?? []);
        $block_type = $type;
        $block_uid  = 'block_' . (int) $block['id'];

        ob_start();
        include $render_file;
        return ob_get_clean();
    }
}
