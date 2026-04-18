function nbhBuildLayoutControlRenderers() {
    return {
        'spacing-layout-panel': function(panel, bp) {
            var profile = nbhBlockUiProfile();
            var body = nbhBreakpointToggle();
            if (bp === 'desktop') {
                return body + '<div class="nbh-grid-2">'
                    + nbhField('Отступ сверху', nbhInput('layout.desktop.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingTop }))
                    + nbhField('Отступ снизу', nbhInput('layout.desktop.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingBottom }))
                    + (profile.layout.supportsMinHeight ? nbhField('Мин. высота', nbhInput('layout.desktop.minHeight', { inputType: 'number', type: 'number', fallback: 0 })) : '')
                    + nbhField('Ширина контента', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: profile.contentWidth }))
                    + nbhField('Зазор контент/медиа', nbhInput('layout.desktop.contentGap', { inputType: 'number', type: 'number', fallback: profile.layout.desktopContentGap }))
                    + nbhField('Зазор между кнопками', nbhInput('layout.desktop.actionsGap', { inputType: 'number', type: 'number', fallback: profile.layout.desktopActionsGap }))
                    + '</div>';
            }
            return body + '<div class="nbh-grid-2">'
                + nbhField('Отступ сверху', nbhInput('layout.mobile.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingTop }))
                + nbhField('Отступ снизу', nbhInput('layout.mobile.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingBottom }))
                + (profile.layout.supportsMinHeight ? nbhField('Мин. высота', nbhInput('layout.mobile.minHeight', { inputType: 'number', type: 'number', fallback: 0 })) : '')
                + nbhField('Зазор контент/медиа', nbhInput('layout.mobile.contentGap', { inputType: 'number', type: 'number', fallback: profile.layout.mobileContentGap }))
                + nbhField('Зазор между кнопками', nbhInput('layout.mobile.actionsGap', { inputType: 'number', type: 'number', fallback: profile.layout.mobileActionsGap }))
                + '</div>';
        },
        'alignment-layout-panel': function() {
            var profile = nbhBlockUiProfile();
            if (profile.layout.primaryControl === 'align') {
                return nbhField('Выравнивание', nbhSelect('layout.desktop.align', [
                    { value: 'center', label: 'По центру' },
                    { value: 'left', label: 'Слева' }
                ], 'center'));
            }
            return nbhField('Компоновка блока', nbhSelect('layout.desktop.mode', [
                { value: 'centered', label: 'По центру' },
                { value: 'left', label: 'Слева' },
                { value: 'split', label: 'Текст и медиа' }
            ], 'centered'));
        }
    };
}