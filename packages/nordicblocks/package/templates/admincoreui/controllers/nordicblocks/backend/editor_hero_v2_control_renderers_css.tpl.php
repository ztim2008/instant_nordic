function nbhBuildCssControlRenderers() {
    return {
        'css-overlay-panel': function(panel) {
            var targetKey = panel && panel.entityScope ? panel.entityScope : '';
            var targetMeta = nbhCssOverlayTargetMeta(targetKey);
            var selectorPreview;
            var currentValue;

            if (!targetMeta) {
                return '<div class="nbh-css-overlay-empty">Для текущей сущности preview-only CSS overlay в этом MVP не доступен.</div>';
            }

            selectorPreview = String((nbhState.cssOverlay && nbhState.cssOverlay.scopeSelector ? nbhState.cssOverlay.scopeSelector : '') + ' ' + targetMeta.selector).trim();
            currentValue = nbhCssOverlayGetTargetCss(targetKey);

            return '<div class="nbh-css-overlay-panel">'
                + '<div class="nbh-css-overlay-hint">Preview-only overlay для точной визуальной подстройки. Изменения применяются сразу в iframe canvas, но в MVP-A не сохраняются в БД и не пишутся в contract.</div>'
                + '<div class="nbh-css-overlay-meta">'
                + '<label>Скоуп и target selector</label>'
                + '<code>' + nbhEscapeHtml(selectorPreview) + '</code>'
                + '</div>'
                + nbhField('CSS declarations', '<textarea class="nbh-css-overlay-textarea" data-css-overlay-target="' + nbhEscapeHtml(targetKey) + '" placeholder="' + nbhEscapeHtml(targetMeta.placeholder || '') + '">' + nbhEscapeHtml(currentValue) + '</textarea>')
                + '<div class="nbh-css-overlay-actions">'
                + '<button type="button" data-css-overlay-action="example" data-css-overlay-target="' + nbhEscapeHtml(targetKey) + '">' + nbhEscapeHtml(nbhCssOverlayExampleLabel(targetMeta)) + '</button>'
                + '<button type="button" data-css-overlay-action="reset" data-css-overlay-target="' + nbhEscapeHtml(targetKey) + '">Очистить target</button>'
                + '</div>'
                + '</div>';
        }
    };
}
