<?php

class actionNordicblocksBlockDesignCanvas extends cmsAction {

    public function run($block_id = 0) {
        if (!$this->cms_user->is_admin) {
            http_response_code(403);
            exit('Forbidden');
        }

        $block = $this->model->getBlockById((int) $block_id);
        if (!$block || !$this->model->isDesignBlockType((string) ($block['type'] ?? ''))) {
            return cmsCore::error404();
        }

        $tokens     = $this->model->getDesignTokens();
        $inline_css = $this->model->buildInlineCss($tokens);
        $assets_dir = dirname(dirname(__DIR__)) . '/assets';
        $tokens_css = @file_get_contents("{$assets_dir}/tokens.css") ?: '';
        $blocks_css = @file_get_contents("{$assets_dir}/blocks.css") ?: '';
        $base_css   = '*,*::before,*::after{box-sizing:border-box}html,body{margin:0;padding:0;background:#e2e8f0;color:#0f172a;font-family:var(--nb-font-body,system-ui,sans-serif)}body{padding:24px}img{max-width:100%;height:auto;display:block}';

        $block_html = $this->renderSingleBlock($block);
        if ($block_html === '') {
            $block_html = '<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;border:1px dashed #94a3b8;border-radius:24px;background:#fff;color:#64748b;padding:2rem;text-align:center">Пока нечего рендерить. Добавьте элементы в JSON contract.</div>';
        }

        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN');

        echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<style>' . $tokens_css . $inline_css . $base_css . $blocks_css . '</style>';
        echo '<script>window.addEventListener("load",function(){if(window.parent){window.parent.postMessage({source:"nordicblocks-design-canvas",type:"canvas:metrics",height:document.documentElement.scrollHeight||document.body.scrollHeight||0},"*");}});</script>';
        echo '</head><body>' . $block_html . '</body></html>';
        exit;
    }

    private function renderSingleBlock(array $block) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
        if ($type === '') {
            return '';
        }

        $block = $this->model->hydrateBlockForRender($block, ['mode' => 'backend_design_canvas']);
        $render_file = dirname(dirname(__DIR__)) . '/blocks/' . $type . '/render.php';
        if (!file_exists($render_file)) {
            return '';
        }

        $props          = (array) ($block['props'] ?? []);
        $block_contract = (array) ($block['contract'] ?? []);
        $block_type     = $type;
        $block_uid      = 'block_' . (int) ($block['id'] ?? 0);

        ob_start();
        include $render_file;
        return ob_get_clean();
    }
}