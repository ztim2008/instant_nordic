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
        + '.nbh-css-overlay-presets{display:flex;flex-direction:column;gap:.45rem;}'
        + '.nbh-css-overlay-preset-list{display:flex;flex-wrap:wrap;gap:.4rem;}'
        + '.nbh-css-overlay-preset{border:1px solid #bfdbfe;border-radius:999px;background:#eff6ff;color:#1d4ed8;cursor:pointer;font-size:.68rem;font-weight:700;padding:.35rem .62rem;}'
        + '.nbh-css-overlay-preset:hover{background:#dbeafe;border-color:#60a5fa;}'
        + '.nbh-css-overlay-runtime{padding:.6rem .75rem;border-radius:10px;border:1px solid #fed7aa;background:#fff7ed;color:#9a3412;font-size:.72rem;line-height:1.5;}'
        + '.nbh-css-overlay-chip-list{display:flex;flex-wrap:wrap;gap:.35rem;}'
        + '.nbh-css-overlay-chip{display:inline-flex;align-items:center;border-radius:999px;padding:.22rem .5rem;background:#e2e8f0;color:#334155;font-size:.66rem;font-weight:700;}'
        + '.nbh-css-overlay-diff{display:flex;flex-direction:column;gap:.5rem;padding:.7rem .75rem;border:1px solid #e2e8f0;border-radius:10px;background:#fff;}'
        + '.nbh-css-overlay-diff strong{font-size:.72rem;color:#0f172a;}'
        + '.nbh-css-overlay-diff pre{margin:0;padding:.55rem .65rem;border-radius:8px;background:#0f172a;color:#dbeafe;font-size:.68rem;line-height:1.5;white-space:pre-wrap;word-break:break-word;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,monospace;}'
        + '.nbh-css-overlay-revisions{display:flex;flex-direction:column;gap:.45rem;}'
        + '.nbh-css-overlay-revision-list{display:flex;flex-direction:column;gap:.5rem;max-height:18rem;overflow:auto;padding-right:.15rem;}'
        + '.nbh-css-overlay-revision{display:flex;flex-direction:column;gap:.45rem;padding:.7rem .75rem;border:1px solid #e2e8f0;border-radius:10px;background:#fff;}'
        + '.nbh-css-overlay-revision-head{display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;}'
        + '.nbh-css-overlay-revision-title{font-size:.72rem;font-weight:700;color:#0f172a;}'
        + '.nbh-css-overlay-revision-meta{font-size:.67rem;color:#64748b;line-height:1.45;}'
        + '.nbh-css-overlay-revision-actions{display:flex;gap:.4rem;flex-wrap:wrap;}'
        + '.nbh-css-overlay-revision-preview{margin:0;padding:.5rem .6rem;border-radius:8px;background:#f8fafc;color:#334155;font-size:.68rem;line-height:1.5;white-space:pre-wrap;word-break:break-word;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,monospace;}'
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

function nbhCssOverlayNormalizePresetList(presets, allowedTargets) {
    return (Array.isArray(presets) ? presets : []).map(function(preset) {
        preset = preset && typeof preset === 'object' ? preset : {};

        return {
            key: String(preset.key || ''),
            label: String(preset.label || preset.key || ''),
            description: String(preset.description || ''),
            targetCss: nbhCssOverlayNormalizeTargetMap(preset.targetCss || {}, allowedTargets)
        };
    }).filter(function(preset) {
        return !!preset.key && Object.keys(preset.targetCss || {}).length > 0;
    });
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
        presets: nbhCssOverlayNormalizePresetList(meta.presets || [], allowedTargets),
        targetCss: nbhClone(initialTargetCss),
        savedTargetCss: nbhClone(initialTargetCss),
        publishedTargetCss: nbhCssOverlayNormalizeTargetMap(meta.publishedTargetCss || {}, allowedTargets),
        version: parseInt(meta.version || 0, 10) || 0,
        updatedAt: meta.updatedAt || '',
        updatedBy: parseInt(meta.updatedBy || 0, 10) || 0,
        publishedVersion: parseInt(meta.publishedVersion || 0, 10) || 0,
        publishedAt: meta.publishedAt || '',
        publishedBy: parseInt(meta.publishedBy || 0, 10) || 0,
        dirty: false,
        loading: false,
        saving: false,
        publishing: false,
        restoring: false,
        error: '',
        revisionsError: '',
        stateUrl: nbhCssOverlayStateUrl || '',
        saveUrl: nbhCssOverlaySaveUrl || '',
        publishUrl: nbhCssOverlayPublishUrl || '',
        publishReady: !!meta.publishReady,
        revisionsReady: !!meta.revisionsReady,
        revisionsUrl: nbhCssOverlayRevisionsUrl || '',
        restoreUrl: nbhCssOverlayRestoreUrl || '',
        revisionsLoading: false,
        revisionsLoaded: false,
        revisions: []
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

function nbhCssOverlayGetPublishedTargetCss(targetKey) {
    if (!nbhCssOverlayIsEnabled()) {
        return '';
    }

    return String((nbhState.cssOverlay.publishedTargetCss || {})[targetKey] || '');
}

function nbhCssOverlayCanPersist() {
    return !!(nbhCssOverlayIsEnabled()
        && nbhState.cssOverlay.mode === 'persistent'
        && nbhState.cssOverlay.stateUrl
        && nbhState.cssOverlay.saveUrl);
}

function nbhCssOverlayCanPublish() {
    if (!nbhCssOverlayIsEnabled()) {
        return false;
    }

    return !!(nbhCssOverlayCanPersist()
        && nbhState.cssOverlay.publishReady
        && nbhState.cssOverlay.publishUrl
        && !nbhState.cssOverlay.dirty
        && !nbhState.cssOverlay.saving
        && !nbhState.cssOverlay.publishing
        && nbhState.cssOverlay.version > 0
        && nbhCssOverlaySignature(nbhState.cssOverlay.savedTargetCss || {}) !== nbhCssOverlaySignature(nbhState.cssOverlay.publishedTargetCss || {}));
}

    function nbhCssOverlayCanUseRevisions() {
        return !!(nbhCssOverlayCanPersist()
        && nbhState.cssOverlay.revisionsReady
        && nbhState.cssOverlay.revisionsUrl);
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
    nbhState.cssOverlay.publishedTargetCss = nbhCssOverlayNormalizeTargetMap(nbhState.cssOverlay.publishedTargetCss || {}, nbhState.cssOverlay.allowedTargets || []);
    nbhState.cssOverlay.dirty = nbhCssOverlaySignature(nbhState.cssOverlay.targetCss) !== nbhCssOverlaySignature(nbhState.cssOverlay.savedTargetCss);
}

function nbhCssOverlayCanRevert() {
    return !!(nbhCssOverlayIsEnabled() && nbhState.cssOverlay.dirty && !nbhState.cssOverlay.saving && !nbhState.cssOverlay.publishing && !nbhState.cssOverlay.restoring);
}

function nbhCssOverlayRevisionMatchesSaved(revision) {
    if (!nbhCssOverlayIsEnabled() || !revision || typeof revision !== 'object') {
        return false;
    }

    return nbhCssOverlaySignature(revision.targetCss || {}) === nbhCssOverlaySignature(nbhState.cssOverlay.savedTargetCss || {});
}

function nbhCssOverlayCanRestoreRevision(revision) {
    return !!(nbhCssOverlayCanUseRevisions()
        && nbhState.cssOverlay.restoreUrl
        && !nbhState.cssOverlay.dirty
        && !nbhState.cssOverlay.saving
        && !nbhState.cssOverlay.publishing
        && !nbhState.cssOverlay.restoring
        && revision
        && revision.id
        && !nbhCssOverlayRevisionMatchesSaved(revision));
}

function nbhCssOverlayPublishLabel() {
    if (!nbhCssOverlayIsEnabled()) {
        return 'Опубликовать draft';
    }

    return Object.keys(nbhState.cssOverlay.savedTargetCss || {}).length ? 'Опубликовать draft' : 'Снять overlay с runtime';
}

function nbhCssOverlayDiffSummary() {
    var changedKeys;

    if (!nbhCssOverlayIsEnabled()) {
        return [];
    }

    changedKeys = (nbhState.cssOverlay.allowedTargets || []).filter(function(targetKey) {
        return nbhCssOverlayGetTargetCss(targetKey) !== nbhCssOverlayGetPublishedTargetCss(targetKey);
    });

    return changedKeys.map(function(targetKey) {
        var targetMeta = nbhCssOverlayTargetMeta(targetKey);
        return targetMeta && targetMeta.label ? targetMeta.label : targetKey;
    });
}

function nbhCssOverlayTargetDiff(targetKey) {
    var currentValue = nbhCssOverlayGetTargetCss(targetKey);
    var publishedValue = nbhCssOverlayGetPublishedTargetCss(targetKey);
    var targetMeta = nbhCssOverlayTargetMeta(targetKey);
    var label = targetMeta && targetMeta.label ? targetMeta.label : targetKey;

    if (currentValue === publishedValue) {
        return {
            state: 'same',
            title: label + ': draft совпадает с runtime',
            currentValue: currentValue,
            publishedValue: publishedValue
        };
    }

    if (currentValue && !publishedValue) {
        return {
            state: 'added',
            title: label + ': draft добавит новый runtime target',
            currentValue: currentValue,
            publishedValue: publishedValue
        };
    }

    if (!currentValue && publishedValue) {
        return {
            state: 'removed',
            title: label + ': draft снимет этот target с runtime',
            currentValue: currentValue,
            publishedValue: publishedValue
        };
    }

    return {
        state: 'modified',
        title: label + ': draft изменит опубликованный runtime target',
        currentValue: currentValue,
        publishedValue: publishedValue
    };
}

function nbhCssOverlayFindPreset(presetKey) {
    var presets = nbhState.cssOverlay && Array.isArray(nbhState.cssOverlay.presets) ? nbhState.cssOverlay.presets : [];

    return presets.find(function(preset) {
        return preset.key === presetKey;
    }) || null;
}

function nbhCssOverlayApplyPreset(presetKey) {
    var preset = nbhCssOverlayFindPreset(presetKey);

    if (!preset) {
        return;
    }

    nbhState.cssOverlay.targetCss = nbhClone(preset.targetCss || {});
    nbhState.cssOverlay.error = '';
    nbhCssOverlayRefreshDirty();
    nbhCssOverlaySyncFrame();
    nbhRenderPanels();
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
        return 'Сохраняю draft overlay в persistence слой...';
    }

    if (nbhState.cssOverlay.publishing) {
        return 'Публикую draft overlay в runtime...';
    }

    if (nbhState.cssOverlay.restoring) {
        return 'Возвращаю выбранную revision обратно в draft...';
    }

    if (!nbhCssOverlayCanPersist()) {
        return 'Session-only режим: изменения живут только в текущей browser session.';
    }

    if (nbhState.cssOverlay.dirty) {
        if (nbhState.cssOverlay.version > 0) {
            return 'Есть локальные несохранённые изменения поверх saved draft v' + nbhState.cssOverlay.version + '. Runtime продолжает использовать published версию ' + nbhState.cssOverlay.publishedVersion + '.';
        }

        return 'Есть локальный draft overlay, но он ещё не сохранён и не опубликован.';
    }

    if (nbhState.cssOverlay.version > 0 && nbhCssOverlaySignature(nbhState.cssOverlay.savedTargetCss || {}) !== nbhCssOverlaySignature(nbhState.cssOverlay.publishedTargetCss || {})) {
        if (nbhState.cssOverlay.publishedVersion > 0) {
            return 'Сохранён draft v' + nbhState.cssOverlay.version + ', но runtime всё ещё на published v' + nbhState.cssOverlay.publishedVersion + '. Следующий шаг: publish.';
        }

        return 'Сохранён draft v' + nbhState.cssOverlay.version + ', но runtime overlay ещё пуст. Следующий шаг: publish.';
    }

    if (nbhState.cssOverlay.version > 0 && nbhState.cssOverlay.publishedVersion > 0) {
        return 'Draft v' + nbhState.cssOverlay.version + ' синхронизирован с published v' + nbhState.cssOverlay.publishedVersion + '.';
    }

    if (nbhState.cssOverlay.version > 0) {
        return 'Сохранён draft overlay, но runtime overlay пока пуст.';
    }

    if (nbhState.cssOverlay.publishedVersion > 0) {
        return 'Runtime overlay опубликован, но текущий draft пуст.';
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
    nbhState.cssOverlay.publishedTargetCss = nbhCssOverlayNormalizeTargetMap(meta.publishedTargetCss || {}, nbhState.cssOverlay.allowedTargets || []);
    nbhState.cssOverlay.version = parseInt(meta.version || 0, 10) || 0;
    nbhState.cssOverlay.updatedAt = meta.updatedAt || '';
    nbhState.cssOverlay.updatedBy = parseInt(meta.updatedBy || 0, 10) || 0;
    nbhState.cssOverlay.publishedVersion = parseInt(meta.publishedVersion || 0, 10) || 0;
    nbhState.cssOverlay.publishedAt = meta.publishedAt || '';
    nbhState.cssOverlay.publishedBy = parseInt(meta.publishedBy || 0, 10) || 0;
    nbhState.cssOverlay.publishReady = !!meta.publishReady;
    nbhState.cssOverlay.revisionsReady = !!meta.revisionsReady;
    nbhState.cssOverlay.error = '';
    nbhCssOverlayRefreshDirty();
}

function nbhCssOverlayLoadRevisions() {
    if (!nbhCssOverlayCanUseRevisions()) {
        return Promise.resolve();
    }

    nbhState.cssOverlay.revisionsLoading = true;
    nbhState.cssOverlay.revisionsError = '';
    nbhRenderPanels();

    return fetch(nbhState.cssOverlay.revisionsUrl + '?limit=12', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function(response) { return response.json(); })
        .then(function(payload) {
            if (!payload.ok) {
                throw new Error(payload.error || 'css_overlay_revisions_error');
            }

            nbhState.cssOverlay.revisions = Array.isArray(payload.revisions) ? payload.revisions : [];
            nbhState.cssOverlay.revisionsLoaded = true;
        })
        .catch(function(error) {
            nbhState.cssOverlay.revisions = [];
            nbhState.cssOverlay.revisionsLoaded = true;
            nbhState.cssOverlay.revisionsError = error && error.message ? error.message : 'revisions_load_failed';
        })
        .finally(function() {
            nbhState.cssOverlay.revisionsLoading = false;
            nbhRenderPanels();
        });
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
            return nbhCssOverlayLoadRevisions();
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
            return nbhCssOverlayLoadRevisions().then(function() {
                nbhRenderPanels();
            });
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

function nbhCssOverlayPublishPersisted() {
    if (!nbhCssOverlayCanPublish()) {
        return Promise.resolve();
    }

    nbhState.cssOverlay.publishing = true;
    nbhState.cssOverlay.error = '';
    nbhRenderPanels();

    return fetch(nbhState.cssOverlay.publishUrl + '?csrf_token=' + encodeURIComponent(nbhCsrfToken), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            csrf_token: nbhCsrfToken,
            version: nbhState.cssOverlay.version
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

                throw new Error(payload.error || 'css_overlay_publish_error');
            }

            nbhCssOverlayApplyPersistedState(payload.cssOverlay || {});
            nbhRenderPanels();
        })
        .catch(function(error) {
            nbhState.cssOverlay.error = error && error.message ? error.message : 'publish_failed';
            nbhRenderPanels();
        })
        .finally(function() {
            nbhState.cssOverlay.publishing = false;
            nbhRenderPanels();
        });
}

function nbhCssOverlayRestoreRevision(revisionId) {
    if (!nbhCssOverlayCanUseRevisions()) {
        return Promise.resolve();
    }

    nbhState.cssOverlay.restoring = true;
    nbhState.cssOverlay.error = '';
    nbhRenderPanels();

    return fetch(nbhState.cssOverlay.restoreUrl + '?csrf_token=' + encodeURIComponent(nbhCsrfToken), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            csrf_token: nbhCsrfToken,
            version: nbhState.cssOverlay.version,
            revisionId: revisionId
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

                throw new Error(payload.error || 'css_overlay_restore_error');
            }

            nbhCssOverlayApplyPersistedState(payload.cssOverlay || {});
            nbhCssOverlaySyncFrame();

            return nbhCssOverlayLoadRevisions().then(function() {
                nbhRenderPanels();
            });
        })
        .catch(function(error) {
            nbhState.cssOverlay.error = error && error.message ? error.message : 'restore_failed';
            nbhRenderPanels();
        })
        .finally(function() {
            nbhState.cssOverlay.restoring = false;
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

    if (action.dataset.cssOverlayAction === 'publish') {
        nbhCssOverlayPublishPersisted();
        return;
    }

    if (action.dataset.cssOverlayAction === 'restore-revision') {
        nbhCssOverlayRestoreRevision(parseInt(action.dataset.cssOverlayRevisionId || '0', 10) || 0);
        return;
    }

    if (action.dataset.cssOverlayAction === 'revert') {
        nbhCssOverlayRevertPersisted();
        return;
    }

    if (action.dataset.cssOverlayAction === 'preset') {
        nbhCssOverlayApplyPreset(action.dataset.cssOverlayPreset || '');
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
