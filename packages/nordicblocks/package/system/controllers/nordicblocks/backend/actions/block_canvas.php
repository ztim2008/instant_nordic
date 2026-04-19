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
';

        $block_html = $this->renderSingleBlock($block);
        $overlay_css = $this->model->buildBlockCssOverlayEditorCss($block, 'block_' . (int) ($block['id'] ?? 0));
        $empty_html = $block_html ? '' : '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;gap:1rem;color:#94a3b8;text-align:center;padding:2rem">'
            . '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/></svg>'
            . '<p style="font-size:1.1rem;font-weight:600;color:#64748b;margin:0">Блок пустой</p>'
            . '<p style="font-size:.9rem;margin:0">Настройте параметры в инспекторе справа</p>'
            . '</div>';

        $this->outputHtml($tokens_css, $inline_css, $base_css, $blocks_css, $block_html ?: $empty_html, $overlay_css);
    }

    private function outputHtml($tokens_css, $inline_css, $base_css, $blocks_css, $content, $overlay_css = '') {
        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN');

        $bridge_css = '
[data-nb-entity]{position:relative;cursor:pointer}
[data-nb-entity]:hover{outline:2px solid rgba(37,99,235,.35);outline-offset:4px}
.nb-editor-selected{outline:2px solid #2563eb !important;outline-offset:4px}
        ';

        $bridge_js = <<<'JS'
<script>
(function () {
    var selectedNode = null;
    var resizeFrameTimer = null;
    var overlayStyleNode = null;

    function emitToParent(type, payload) {
        if (!window.parent) {
            return;
        }

        window.parent.postMessage(Object.assign({
            source: 'nordicblocks-canvas',
            type: type
        }, payload || {}), '*');
    }

    function listAvailableEntities() {
        var seen = {};

        return Array.prototype.reduce.call(document.querySelectorAll('[data-nb-entity]'), function(result, node) {
            var entityKey = node && node.getAttribute ? String(node.getAttribute('data-nb-entity') || '') : '';

            if (!entityKey || seen[entityKey]) {
                return result;
            }

            seen[entityKey] = true;
            result.push(entityKey);

            return result;
        }, []);
    }

    function ensureOverlayStyleNode() {
        if (overlayStyleNode && overlayStyleNode.parentNode) {
            return overlayStyleNode;
        }

        overlayStyleNode = document.getElementById('nbh-css-overlay-style');
        if (overlayStyleNode) {
            return overlayStyleNode;
        }

        overlayStyleNode = document.createElement('style');
        overlayStyleNode.id = 'nbh-css-overlay-style';
        document.head.appendChild(overlayStyleNode);

        return overlayStyleNode;
    }

    function setOverlayCss(cssText) {
        ensureOverlayStyleNode().textContent = String(cssText || '');
        scheduleCanvasMetrics();
    }

    function findEntityNode(target) {
        while (target && target !== document.body) {
            if (target.nodeType === 1 && target.hasAttribute('data-nb-entity')) {
                return target;
            }
            target = target.parentNode;
        }
        return null;
    }

    function clearSelection() {
        if (selectedNode) {
            selectedNode.classList.remove('nb-editor-selected');
        }

        selectedNode = null;
    }

    function getSelectedEntity() {
        return selectedNode && selectedNode.getAttribute ? String(selectedNode.getAttribute('data-nb-entity') || '') : '';
    }

    function emitSelectionChange(reason) {
        emitToParent('canvas:selection', {
            entity: getSelectedEntity(),
            entities: listAvailableEntities(),
            reason: reason || 'unknown'
        });
    }

    function emitCanvasMetrics() {
        var body = document.body;
        var html = document.documentElement;
        var height = 0;

        if (body) {
            height = Math.max(height, body.scrollHeight, body.offsetHeight);
        }
        if (html) {
            height = Math.max(height, html.scrollHeight, html.offsetHeight, html.clientHeight);
        }

        emitToParent('canvas:metrics', { height: height });
    }

    function scheduleCanvasMetrics() {
        if (resizeFrameTimer) {
            clearTimeout(resizeFrameTimer);
        }

        resizeFrameTimer = setTimeout(emitCanvasMetrics, 16);
    }

    function selectEntity(entityKey, shouldScroll) {
        var safeKey;

        clearSelection();

        if (!entityKey) {
            return false;
        }

        safeKey = String(entityKey).replace(/"/g, '\\"');
        selectedNode = document.querySelector('[data-nb-entity="' + safeKey + '"]');
        if (!selectedNode) {
            return false;
        }

        selectedNode.classList.add('nb-editor-selected');
        if (shouldScroll && typeof selectedNode.scrollIntoView === 'function') {
            selectedNode.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
        }

        return true;
    }

    document.addEventListener('click', function (event) {
        var entityNode = findEntityNode(event.target);
        var clickTarget = event.target && event.target.nodeType === 1 ? event.target : event.target.parentNode;
        var summaryNode = clickTarget && typeof clickTarget.closest === 'function' ? clickTarget.closest('summary') : null;
        var allowNativeToggle = !!(summaryNode && summaryNode.closest('details'));

        if (!entityNode) {
            return;
        }

        if (!allowNativeToggle) {
            event.preventDefault();
            event.stopPropagation();
        }

        selectEntity(entityNode.getAttribute('data-nb-entity') || '', false);
        emitSelectionChange('canvas-click');
    }, true);

    window.addEventListener('message', function (event) {
        var data = event.data || {};
        if (data.source !== 'nordicblocks-editor') {
            return;
        }

        if (data.type === 'entity:select' || data.type === 'canvas:select-entity') {
            selectEntity(data.entity || '', true);
            emitSelectionChange(data.type === 'canvas:select-entity' ? 'editor-sync-v2' : 'editor-sync');
            return;
        }

        if (data.type === 'canvas:request-state') {
            emitSelectionChange('editor-request-state');
            return;
        }

        if (data.type === 'css:set') {
            setOverlayCss(data.cssText || '');
            return;
        }

        if (data.type === 'css:clear') {
            setOverlayCss('');
        }
    });

    if (typeof ResizeObserver === 'function') {
        var resizeObserver = new ResizeObserver(function () {
            scheduleCanvasMetrics();
        });

        if (document.body) {
            resizeObserver.observe(document.body);
        }
        if (document.documentElement) {
            resizeObserver.observe(document.documentElement);
        }
    }

    window.addEventListener('load', scheduleCanvasMetrics);
    window.addEventListener('resize', scheduleCanvasMetrics);

    Array.prototype.forEach.call(document.images || [], function (img) {
        if (!img || img.complete) {
            return;
        }

        img.addEventListener('load', scheduleCanvasMetrics, { once: true });
        img.addEventListener('error', scheduleCanvasMetrics, { once: true });
    });

    emitToParent('canvas:ready', {
        entity: getSelectedEntity(),
        entities: listAvailableEntities()
    });

    scheduleCanvasMetrics();
})();
</script>
JS;

        echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>Preview</title>';
        echo '<style>' . $tokens_css . '</style>';
        echo '<style>' . $inline_css . '</style>';
        echo '<style>' . $base_css . '</style>';
        echo '<style>' . $blocks_css . '</style>';
        echo '<style>' . $bridge_css . '</style>';
        echo '<style id="nbh-css-overlay-style">' . str_ireplace('</style', '<\\/style', (string) $overlay_css) . '</style>';
        echo '</head><body>';
        echo $content;
        echo $bridge_js;
        echo '</body></html>';
        exit;
    }

    private function renderSingleBlock(array $block) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
        if (!$type) {
            return '';
        }

        $block = $this->model->hydrateBlockForRender($block, ['mode' => 'backend_canvas']);

        $render_file = dirname(dirname(__DIR__)) . "/blocks/{$type}/render.php";
        if (!file_exists($render_file)) {
            return '';
        }

        $props          = (array) ($block['props'] ?? []);
        $block_contract = (array) ($block['contract'] ?? []);
        $block_type     = $type;
        $block_uid      = 'block_' . (int) $block['id'];

        ob_start();
        include $render_file;
        return ob_get_clean();
    }
}
