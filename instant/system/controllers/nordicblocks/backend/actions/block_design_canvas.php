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

        $bridge_css = '[data-nb-entity^="element:"]{cursor:pointer;transition:outline-color .14s ease,box-shadow .14s ease}[data-nb-entity^="element:"]:hover{outline:2px solid rgba(234,88,12,.35);outline-offset:4px}.nbd-canvas-selected{outline:3px solid #ea580c !important;outline-offset:4px;box-shadow:0 0 0 6px rgba(251,146,60,.18)}';
        $bridge_js = <<<'JS'
<script>
(function () {
    var selectedNode = null;

    function emit(type, payload) {
        if (!window.parent) {
            return;
        }

        window.parent.postMessage(Object.assign({
            source: 'nordicblocks-design-canvas',
            type: type
        }, payload || {}), '*');
    }

    function collectEntities() {
        var seen = {};

        return Array.prototype.reduce.call(document.querySelectorAll('[data-nb-entity^="element:"]'), function (result, node) {
            var raw = node.getAttribute('data-nb-entity') || '';
            var id = raw.indexOf('element:') === 0 ? raw.slice(8) : '';

            if (!id || seen[id]) {
                return result;
            }

            seen[id] = true;
            result.push(id);
            return result;
        }, []);
    }

    function metrics() {
        var body = document.body;
        var html = document.documentElement;
        var height = Math.max(
            body ? body.scrollHeight : 0,
            body ? body.offsetHeight : 0,
            html ? html.scrollHeight : 0,
            html ? html.offsetHeight : 0,
            html ? html.clientHeight : 0
        );

        emit('canvas:metrics', { height: height });
    }

    function clearSelection() {
        if (selectedNode) {
            selectedNode.classList.remove('nbd-canvas-selected');
        }
        selectedNode = null;
    }

    function findNode(id) {
        if (!id) {
            return null;
        }

        return document.querySelector('[data-nb-entity="element:' + String(id).replace(/"/g, '\\"') + '"]');
    }

    function selectElement(id, shouldScroll) {
        var node = findNode(id);

        clearSelection();
        if (!node) {
            return false;
        }

        selectedNode = node;
        selectedNode.classList.add('nbd-canvas-selected');

        if (shouldScroll && typeof selectedNode.scrollIntoView === 'function') {
            selectedNode.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
        }

        return true;
    }

    document.addEventListener('click', function (event) {
        var target = event.target && event.target.closest ? event.target.closest('[data-nb-entity^="element:"]') : null;

        if (!target) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        var raw = target.getAttribute('data-nb-entity') || '';
        var id = raw.indexOf('element:') === 0 ? raw.slice(8) : '';
        if (!id) {
            return;
        }

        selectElement(id, false);
        emit('select-element', { elementId: id, entities: collectEntities(), reason: 'canvas-click' });
    }, true);

    window.addEventListener('message', function (event) {
        var data = event.data || {};
        if (data.source !== 'nordicblocks-design-editor') {
            return;
        }

        if (data.type === 'select-element') {
            selectElement(data.elementId || '', !!data.scrollIntoView);
            return;
        }

        if (data.type === 'request-metrics') {
            metrics();
        }
    });

    window.addEventListener('load', function () {
        emit('ready', { entities: collectEntities() });
        metrics();
    });

    window.addEventListener('resize', metrics);
    if (typeof ResizeObserver !== 'undefined') {
        new ResizeObserver(metrics).observe(document.body);
    }
})();
</script>
JS;

        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN');

        echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<style>' . $tokens_css . $inline_css . $base_css . $blocks_css . $bridge_css . '</style>';
        echo $bridge_js;
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