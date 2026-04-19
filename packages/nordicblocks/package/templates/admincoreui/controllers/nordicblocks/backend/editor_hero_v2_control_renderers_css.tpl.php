function nbhBuildCssControlRenderers() {
    return {
        'css-overlay-panel': function(panel) {
            var targetKey = panel && panel.entityScope ? panel.entityScope : '';
            var targetMeta = nbhCssOverlayTargetMeta(targetKey);
            var selectorPreview;
            var currentValue;
            var canPersist;
            var statusText;
            var savingDisabled;
            var revertDisabled;
            var publishDisabled;
            var publishLabel;
            var diffInfo;
            var diffSummary;
            var presets;

            if (!targetMeta) {
                return '<div class="nbh-css-overlay-empty">Для текущей сущности CSS overlay сейчас не доступен.</div>';
            }

            selectorPreview = String((nbhState.cssOverlay && nbhState.cssOverlay.scopeSelector ? nbhState.cssOverlay.scopeSelector : '') + ' ' + targetMeta.selector).trim();
            currentValue = nbhCssOverlayGetTargetCss(targetKey);
            canPersist = nbhCssOverlayCanPersist();
            statusText = nbhCssOverlayStatusText();
            savingDisabled = nbhState.cssOverlay && (nbhState.cssOverlay.saving || nbhState.cssOverlay.publishing) ? ' disabled' : '';
            revertDisabled = nbhCssOverlayCanRevert() ? '' : ' disabled';
            publishDisabled = nbhCssOverlayCanPublish() ? '' : ' disabled';
            publishLabel = nbhCssOverlayPublishLabel();
            diffInfo = nbhCssOverlayTargetDiff(targetKey);
            diffSummary = nbhCssOverlayDiffSummary();
            presets = nbhState.cssOverlay && Array.isArray(nbhState.cssOverlay.presets) ? nbhState.cssOverlay.presets : [];

            return '<div class="nbh-css-overlay-panel">'
                + '<div class="nbh-css-overlay-hint">' + nbhEscapeHtml(canPersist
                    ? 'Overlay живет отдельно от block contract: применяется сразу в iframe, сохраняется отдельным backend API и публикуется в runtime только после сохранения CSS-документа.'
                    : 'Overlay работает в session-only режиме: применяется сразу в iframe canvas, но без persistence слоя не переживет полный reload страницы.') + '</div>'
                + '<div class="nbh-css-overlay-status">' + nbhEscapeHtml(statusText) + '</div>'
                + (canPersist
                    ? '<div class="nbh-css-overlay-runtime">Runtime сейчас на published версии ' + nbhEscapeHtml(String(nbhState.cssOverlay.publishedVersion || 0)) + '. Draft версия: ' + nbhEscapeHtml(String(nbhState.cssOverlay.version || 0)) + '.</div>'
                    : '')
                + (diffSummary.length
                    ? '<div class="nbh-css-overlay-chip-list">' + diffSummary.map(function(label) {
                        return '<span class="nbh-css-overlay-chip">diff: ' + nbhEscapeHtml(label) + '</span>';
                    }).join('') + '</div>'
                    : '')
                + (presets.length
                    ? '<div class="nbh-css-overlay-presets"><label>Selector-safe presets</label><div class="nbh-css-overlay-preset-list">' + presets.map(function(preset) {
                        return '<button type="button" class="nbh-css-overlay-preset" data-css-overlay-action="preset" data-css-overlay-preset="' + nbhEscapeHtml(preset.key) + '" title="' + nbhEscapeHtml(preset.description || '') + '">' + nbhEscapeHtml(preset.label) + '</button>';
                    }).join('') + '</div></div>'
                    : '')
                + '<div class="nbh-css-overlay-meta">'
                + '<label>Скоуп и target selector</label>'
                + '<code>' + nbhEscapeHtml(selectorPreview) + '</code>'
                + '</div>'
                + '<div class="nbh-css-overlay-diff">'
                + '<strong>' + nbhEscapeHtml(diffInfo.title) + '</strong>'
                + '<label>Draft targetCss</label>'
                + '<pre>' + nbhEscapeHtml(diffInfo.currentValue || '/* empty */') + '</pre>'
                + '<label>Published runtime targetCss</label>'
                + '<pre>' + nbhEscapeHtml(diffInfo.publishedValue || '/* empty */') + '</pre>'
                + '</div>'
                + nbhField('CSS declarations', '<textarea class="nbh-css-overlay-textarea" data-css-overlay-target="' + nbhEscapeHtml(targetKey) + '" placeholder="' + nbhEscapeHtml(targetMeta.placeholder || '') + '">' + nbhEscapeHtml(currentValue) + '</textarea>')
                + '<div class="nbh-css-overlay-actions">'
                + '<button type="button" data-css-overlay-action="example" data-css-overlay-target="' + nbhEscapeHtml(targetKey) + '">' + nbhEscapeHtml(nbhCssOverlayExampleLabel(targetMeta)) + '</button>'
                + '<button type="button" data-css-overlay-action="reset" data-css-overlay-target="' + nbhEscapeHtml(targetKey) + '">Очистить target</button>'
                + (canPersist
                    ? '<button type="button" data-css-overlay-action="save"' + savingDisabled + '>Сохранить draft</button>'
                        + '<button type="button" data-css-overlay-action="publish"' + publishDisabled + '>' + nbhEscapeHtml(publishLabel) + '</button>'
                        + '<button type="button" data-css-overlay-action="revert"' + revertDisabled + '>Вернуть saved draft</button>'
                    : '')
                + '</div>'
                + '</div>';
        }
    };
}
