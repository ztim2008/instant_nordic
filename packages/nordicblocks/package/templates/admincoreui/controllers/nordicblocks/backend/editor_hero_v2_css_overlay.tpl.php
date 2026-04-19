function nbhCssOverlayEnsureEditorStyle() {
    var styleNode = document.getElementById('nbh-css-overlay-editor-style');

    if (styleNode) {
        return;
    }

    styleNode = document.createElement('style');
    styleNode.id = 'nbh-css-overlay-editor-style';
    styleNode.textContent = ''
        + '.nbh-css-overlay-panel{display:flex;flex-direction:column;gap:.7rem;}'
        + '.nbh-css-overlay-hint{padding:.7rem .8rem;border-radius:10px;background:#eff6ff;color:#1d4ed8;font-size:.73rem;line-height:1.5;}'
        + '.nbh-css-overlay-meta{display:flex;flex-direction:column;gap:.3rem;}'
        + '.nbh-css-overlay-meta code{display:block;padding:.55rem .65rem;border-radius:10px;background:#0f172a;color:#dbeafe;font-size:.68rem;white-space:normal;word-break:break-word;}'
        + '.nbh-css-overlay-textarea{width:100%;min-height:126px;resize:vertical;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,monospace;font-size:.75rem;line-height:1.55;}'
        + '.nbh-css-overlay-actions{display:flex;gap:.45rem;flex-wrap:wrap;}'
        + '.nbh-css-overlay-actions button{border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#334155;cursor:pointer;font-size:.72rem;font-weight:700;padding:.45rem .7rem;}'
        + '.nbh-css-overlay-actions button:hover{border-color:#93c5fd;color:#1d4ed8;background:#f8fbff;}'
        + '.nbh-css-overlay-empty{border:1px dashed #cbd5e1;border-radius:10px;padding:.8rem;background:#f8fafc;color:#64748b;font-size:.74rem;line-height:1.5;}';
    document.head.appendChild(styleNode);
}

function nbhCssOverlayBuildState(meta) {
    meta = meta && typeof meta === 'object' ? meta : {};

    return {
        enabled: !!(nbhCssOverlayEnabled && meta.enabled),
        mode: meta.mode || 'disabled',
        scopeSelector: meta.scopeSelector || '',
        targets: meta.targets && typeof meta.targets === 'object' ? meta.targets : {},
        allowedTargets: Array.isArray(meta.allowedTargets) ? meta.allowedTargets.slice() : [],
        targetCss: {}
    };
}

function nbhCssOverlayIsEnabled() {
    return !!(nbhState.cssOverlay && nbhState.cssOverlay.enabled);
}

function nbhCssOverlayTargetMeta(targetKey) {
    if (!nbhCssOverlayIsEnabled()) {
        return null;
    }

    return nbhState.cssOverlay.targets && nbhState.cssOverlay.targets[targetKey]
        ? nbhState.cssOverlay.targets[targetKey]
        : null;
}

function nbhCssOverlayNormalizeDeclarations(value) {
    var normalized = String(value || '').replace(/<\/?style[^>]*>/gi, '').trim();
    var openBrace = normalized.indexOf('{');
    var closeBrace = normalized.lastIndexOf('}');

    if (openBrace !== -1 && closeBrace > openBrace) {
        normalized = normalized.slice(openBrace + 1, closeBrace);
    }

    return normalized.trim();
}

function nbhCssOverlayGetTargetCss(targetKey) {
    if (!nbhCssOverlayIsEnabled()) {
        return '';
    }

    return String((nbhState.cssOverlay.targetCss || {})[targetKey] || '');
}

function nbhCssOverlayBuildCssText() {
    var cssParts = [];
    var scopeSelector;

    if (!nbhCssOverlayIsEnabled()) {
        return '';
    }

    scopeSelector = String(nbhState.cssOverlay.scopeSelector || '').trim();
    Object.keys(nbhState.cssOverlay.targetCss || {}).forEach(function(targetKey) {
        var targetMeta = nbhCssOverlayTargetMeta(targetKey);
        var declarations = nbhCssOverlayNormalizeDeclarations(nbhCssOverlayGetTargetCss(targetKey));

        if (!targetMeta || !targetMeta.selector || !declarations) {
            return;
        }

        cssParts.push(scopeSelector + ' ' + targetMeta.selector + ' {' + declarations + '}');
    });

    return cssParts.join('\n\n');
}

function nbhCssOverlayPostMessage(type, cssText) {
    var frame = document.getElementById('nbh-canvas-frame');

    if (!frame || !frame.contentWindow) {
        return;
    }

    frame.contentWindow.postMessage({
        source: 'nordicblocks-editor',
        type: type,
        cssText: cssText || ''
    }, '*');
}

function nbhCssOverlaySyncFrame() {
    var cssText = nbhCssOverlayBuildCssText();

    if (!nbhCssOverlayIsEnabled()) {
        return;
    }

    if (cssText) {
        nbhCssOverlayPostMessage('css:set', cssText);
        return;
    }

    nbhCssOverlayPostMessage('css:clear', '');
}

function nbhCssOverlayUpdateTarget(targetKey, value, rerender) {
    var normalized;

    if (!nbhCssOverlayTargetMeta(targetKey)) {
        return;
    }

    normalized = nbhCssOverlayNormalizeDeclarations(value);

    if (normalized) {
        nbhState.cssOverlay.targetCss[targetKey] = normalized;
    } else {
        delete nbhState.cssOverlay.targetCss[targetKey];
    }

    if (rerender) {
        nbhRenderPanels();
    }

    nbhCssOverlaySyncFrame();
}

function nbhCssOverlayExampleLabel(targetMeta) {
    return targetMeta && targetMeta.example ? 'Подставить пример' : 'Применить';
}

nbhCssOverlayEnsureEditorStyle();

document.getElementById('nbh-panel-body').addEventListener('input', function(event) {
    var target = event.target.closest('[data-css-overlay-target]');

    if (!target) {
        return;
    }

    nbhCssOverlayUpdateTarget(target.dataset.cssOverlayTarget || '', target.value, false);
});

document.getElementById('nbh-panel-body').addEventListener('click', function(event) {
    var action = event.target.closest('[data-css-overlay-action]');
    var targetKey;
    var targetMeta;

    if (!action) {
        return;
    }

    event.preventDefault();
    targetKey = action.dataset.cssOverlayTarget || '';
    targetMeta = nbhCssOverlayTargetMeta(targetKey);

    if (!targetMeta) {
        return;
    }

    if (action.dataset.cssOverlayAction === 'reset') {
        nbhCssOverlayUpdateTarget(targetKey, '', true);
        return;
    }

    if (action.dataset.cssOverlayAction === 'example') {
        nbhCssOverlayUpdateTarget(targetKey, targetMeta.example || targetMeta.placeholder || '', true);
    }
});

document.getElementById('nbh-canvas-frame').addEventListener('load', function() {
    setTimeout(nbhCssOverlaySyncFrame, 30);
});

window.addEventListener('message', function(event) {
    var data = event.data || {};

    if (data.source !== 'nordicblocks-canvas') {
        return;
    }

    if (data.type === 'canvas:ready') {
        nbhCssOverlaySyncFrame();
    }
});
