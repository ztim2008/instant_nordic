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

            if (!targetMeta) {
                return '<div class="nbh-css-overlay-empty">Для текущей сущности CSS overlay сейчас не доступен.</div>';
            }

            selectorPreview = String((nbhState.cssOverlay && nbhState.cssOverlay.scopeSelector ? nbhState.cssOverlay.scopeSelector : '') + ' ' + targetMeta.selector).trim();
            currentValue = nbhCssOverlayGetTargetCss(targetKey);
            canPersist = nbhCssOverlayCanPersist();
            statusText = nbhCssOverlayStatusText();
            savingDisabled = nbhState.cssOverlay && nbhState.cssOverlay.saving ? ' disabled' : '';
            revertDisabled = nbhCssOverlayCanRevert() ? '' : ' disabled';

            return '<div class="nbh-css-overlay-panel">'
                + '<div class="nbh-css-overlay-hint">' + nbhEscapeHtml(canPersist
                    ? 'Overlay живет отдельно от block contract: применяется сразу в iframe, сохраняется отдельным backend API и публикуется в runtime только после сохранения CSS-документа.'
                    : 'Overlay работает в session-only режиме: применяется сразу в iframe canvas, но без persistence слоя не переживет полный reload страницы.') + '</div>'
                + '<div class="nbh-css-overlay-status">' + nbhEscapeHtml(statusText) + '</div>'
                + '<div class="nbh-css-overlay-meta">'
                + '<label>Скоуп и target selector</label>'
                + '<code>' + nbhEscapeHtml(selectorPreview) + '</code>'
                + '</div>'
                + nbhField('CSS declarations', '<textarea class="nbh-css-overlay-textarea" data-css-overlay-target="' + nbhEscapeHtml(targetKey) + '" placeholder="' + nbhEscapeHtml(targetMeta.placeholder || '') + '">' + nbhEscapeHtml(currentValue) + '</textarea>')
                + '<div class="nbh-css-overlay-actions">'
                + '<button type="button" data-css-overlay-action="example" data-css-overlay-target="' + nbhEscapeHtml(targetKey) + '">' + nbhEscapeHtml(nbhCssOverlayExampleLabel(targetMeta)) + '</button>'
                + '<button type="button" data-css-overlay-action="reset" data-css-overlay-target="' + nbhEscapeHtml(targetKey) + '">Очистить target</button>'
                + (canPersist
                    ? '<button type="button" data-css-overlay-action="save"' + savingDisabled + '>Сохранить overlay</button>'
                        + '<button type="button" data-css-overlay-action="revert"' + revertDisabled + '>Вернуть сохранённое</button>'
                    : '')
                + '</div>'
                + '</div>';
        }
    };
}
