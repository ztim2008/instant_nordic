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
        + '.nbh-css-overlay-status{padding:.55rem .7rem;border-radius:10px;background:#f8fafc;color:#475569;font-size:.72rem;line-height:1.45;}'
        + '.nbh-css-overlay-meta code{display:block;padding:.55rem .65rem;border-radius:10px;background:#0f172a;color:#dbeafe;font-size:.68rem;white-space:normal;word-break:break-word;}'
        + '.nbh-css-overlay-textarea{width:100%;min-height:126px;resize:vertical;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,monospace;font-size:.75rem;line-height:1.55;}'
        + '.nbh-css-overlay-actions{display:flex;gap:.45rem;flex-wrap:wrap;}'
        + '.nbh-css-overlay-actions button{border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#334155;cursor:pointer;font-size:.72rem;font-weight:700;padding:.45rem .7rem;}'
        + '.nbh-css-overlay-actions button:hover{border-color:#93c5fd;color:#1d4ed8;background:#f8fbff;}'
        + '.nbh-css-overlay-actions button[disabled]{opacity:.55;cursor:not-allowed;border-color:#cbd5e1;color:#94a3b8;background:#f8fafc;}'
        + '.nbh-css-overlay-empty{border:1px dashed #cbd5e1;border-radius:10px;padding:.8rem;background:#f8fafc;color:#64748b;font-size:.74rem;line-height:1.5;}';
    document.head.appendChild(styleNode);
}

function nbhCssOverlayNormalizeTargetMap(targetCss, allowedTargets) {
    var normalizedMap = {};

    (Array.isArray(allowedTargets) ? allowedTargets : []).forEach(function(targetKey) {
        var normalized = nbhCssOverlayNormalizeDeclarations(targetCss && Object.prototype.hasOwnProperty.call(targetCss, targetKey) ? targetCss[targetKey] : '');
        if (normalized) {
            normalizedMap[targetKey] = normalized;
        }
    });

    return normalizedMap;
}

function nbhCssOverlayBuildState(meta) {
    meta = meta && typeof meta === 'object' ? meta : {};

    var allowedTargets = Array.isArray(meta.allowedTargets) ? meta.allowedTargets.slice() : [];
    var initialTargetCss = nbhCssOverlayNormalizeTargetMap(meta.targetCss || {}, allowedTargets);

    return {
        enabled: !!(nbhCssOverlayEnabled && meta.enabled),
        mode: meta.mode || 'disabled',
        scopeSelector: meta.scopeSelector || '',
        targets: meta.targets && typeof meta.targets === 'object' ? meta.targets : {},
        allowedTargets: allowedTargets,
        targetCss: nbhClone(initialTargetCss),
        savedTargetCss: nbhClone(initialTargetCss),
        version: parseInt(meta.version || 0, 10) || 0,
        updatedAt: meta.updatedAt || '',
        updatedBy: parseInt(meta.updatedBy || 0, 10) || 0,
        dirty: false,
        loading: false,
        saving: false,
        error: '',
        stateUrl: nbhCssOverlayStateUrl || '',
        saveUrl: nbhCssOverlaySaveUrl || ''
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

function nbhCssOverlayCanPersist() {
    return !!(nbhCssOverlayIsEnabled()
        && nbhState.cssOverlay.mode === 'persistent'
        && nbhState.cssOverlay.stateUrl
        && nbhState.cssOverlay.saveUrl);
}

function nbhCssOverlaySignature(targetCss) {
    if (!nbhCssOverlayIsEnabled()) {
        return '{}';
    }

    return JSON.stringify(nbhCssOverlayNormalizeTargetMap(targetCss || {}, nbhState.cssOverlay.allowedTargets || []));
}

function nbhCssOverlayRefreshDirty() {
    if (!nbhCssOverlayIsEnabled()) {
        return;
    }

    nbhState.cssOverlay.targetCss = nbhCssOverlayNormalizeTargetMap(nbhState.cssOverlay.targetCss || {}, nbhState.cssOverlay.allowedTargets || []);
    nbhState.cssOverlay.savedTargetCss = nbhCssOverlayNormalizeTargetMap(nbhState.cssOverlay.savedTargetCss || {}, nbhState.cssOverlay.allowedTargets || []);
    nbhState.cssOverlay.dirty = nbhCssOverlaySignature(nbhState.cssOverlay.targetCss) !== nbhCssOverlaySignature(nbhState.cssOverlay.savedTargetCss);
}

function nbhCssOverlayCanRevert() {
    return !!(nbhCssOverlayIsEnabled() && nbhState.cssOverlay.dirty && !nbhState.cssOverlay.saving);
}

function nbhCssOverlayStatusText() {
    if (!nbhCssOverlayIsEnabled()) {
        return 'CSS overlay отключен для этого блока.';
    }

    if (nbhState.cssOverlay.error) {
        return 'Ошибка overlay: ' + nbhState.cssOverlay.error;
    }

    if (nbhState.cssOverlay.loading) {
        return 'Загрузка сохранённого overlay...';
    }

    if (nbhState.cssOverlay.saving) {
        return 'Сохраняю overlay в persistence слой...';
    }

    if (!nbhCssOverlayCanPersist()) {
        return 'Session-only режим: изменения живут только в текущей browser session.';
    }

    if (nbhState.cssOverlay.version > 0 && nbhState.cssOverlay.dirty) {
        return 'Есть несохранённые CSS-изменения поверх сохранённой версии ' + nbhState.cssOverlay.version + '.';
    }

    if (nbhState.cssOverlay.version > 0) {
        return 'Сохранена версия overlay ' + nbhState.cssOverlay.version + '. Runtime использует именно её.';
    }

    if (nbhState.cssOverlay.dirty) {
        return 'Overlay ещё не сохранён: runtime продолжает использовать чистый SSR.';
    }

    return 'Persisted overlay для этого блока пока не создан.';
}

function nbhCssOverlayApplyPersistedState(meta) {
    meta = meta && typeof meta === 'object' ? meta : {};

    if (!nbhCssOverlayIsEnabled()) {
        return;
    }

    nbhState.cssOverlay.targetCss = nbhCssOverlayNormalizeTargetMap(meta.targetCss || {}, nbhState.cssOverlay.allowedTargets || []);
    nbhState.cssOverlay.savedTargetCss = nbhClone(nbhState.cssOverlay.targetCss);
    nbhState.cssOverlay.version = parseInt(meta.version || 0, 10) || 0;
    nbhState.cssOverlay.updatedAt = meta.updatedAt || '';
    nbhState.cssOverlay.updatedBy = parseInt(meta.updatedBy || 0, 10) || 0;
    nbhState.cssOverlay.error = '';
    nbhCssOverlayRefreshDirty();
}

function nbhCssOverlayLoadPersisted() {
    if (!nbhCssOverlayCanPersist()) {
        return Promise.resolve();
    }

    nbhState.cssOverlay.loading = true;

    return fetch(nbhState.cssOverlay.stateUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function(response) { return response.json(); })
        .then(function(payload) {
            if (!payload.ok) {
                throw new Error(payload.error || 'css_overlay_state_error');
            }

            nbhCssOverlayApplyPersistedState(payload.cssOverlay || {});
        })
        .catch(function(error) {
            nbhState.cssOverlay.error = error && error.message ? error.message : 'state_load_failed';
        })
        .finally(function() {
            nbhState.cssOverlay.loading = false;
        });
}

function nbhCssOverlaySavePersisted() {
    if (!nbhCssOverlayCanPersist() || nbhState.cssOverlay.saving) {
        return Promise.resolve();
    }

    nbhState.cssOverlay.saving = true;
    nbhState.cssOverlay.error = '';
    nbhRenderPanels();

    return fetch(nbhState.cssOverlay.saveUrl + '?csrf_token=' + encodeURIComponent(nbhCsrfToken), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            csrf_token: nbhCsrfToken,
            version: nbhState.cssOverlay.version,
            targetCss: nbhCssOverlayNormalizeTargetMap(nbhState.cssOverlay.targetCss || {}, nbhState.cssOverlay.allowedTargets || [])
        })
    })
        .then(function(response) { return response.json(); })
        .then(function(payload) {
            if (!payload.ok) {
                if (payload.error === 'version_conflict' && payload.cssOverlay) {
                    nbhCssOverlayApplyPersistedState(payload.cssOverlay);
                    nbhCssOverlaySyncFrame();
                    nbhRenderPanels();
                }

                throw new Error(payload.error || 'css_overlay_save_error');
            }

            nbhCssOverlayApplyPersistedState(payload.cssOverlay || {});
            nbhRenderPanels();
        })
        .catch(function(error) {
            nbhState.cssOverlay.error = error && error.message ? error.message : 'save_failed';
            nbhRenderPanels();
        })
        .finally(function() {
            nbhState.cssOverlay.saving = false;
            nbhRenderPanels();
        });
}

function nbhCssOverlayRevertPersisted() {
    if (!nbhCssOverlayIsEnabled()) {
        return;
    }

    nbhState.cssOverlay.targetCss = nbhClone(nbhState.cssOverlay.savedTargetCss || {});
    nbhState.cssOverlay.error = '';
    nbhCssOverlayRefreshDirty();
    nbhCssOverlaySyncFrame();
    nbhRenderPanels();
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

    nbhCssOverlayRefreshDirty();

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

    if (action.dataset.cssOverlayAction === 'save') {
        nbhCssOverlaySavePersisted();
        return;
    }

    if (action.dataset.cssOverlayAction === 'revert') {
        nbhCssOverlayRevertPersisted();
        return;
    }

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
