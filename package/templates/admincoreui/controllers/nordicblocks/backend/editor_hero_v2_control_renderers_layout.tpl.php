function nbhBuildLayoutControlRenderers() {
    return {
        'spacing-layout-panel': function(panel, bp) {
            var profile = nbhBlockUiProfile();
            var body = nbhBreakpointToggle();
            if (profile.kind === 'cards_slider') {
                if (bp === 'desktop') {
                    return body + '<div class="nbh-grid-2">'
                        + nbhField('Ширина контейнера', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: profile.contentWidth }))
                        + nbhField('Отступ сверху', nbhInput('layout.desktop.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingTop }))
                        + nbhField('Отступ снизу', nbhInput('layout.desktop.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingBottom }))
                        + nbhField('Слайдов в ряд', nbhInput('layout.desktop.slidesPerView', { inputType: 'number', type: 'number', fallback: profile.layout.desktopSlidesPerView }))
                        + nbhField('Gap между слайдами', nbhInput('layout.desktop.slideGap', { inputType: 'number', type: 'number', fallback: profile.layout.desktopGap }))
                        + nbhField('Отступ header/slider', nbhInput('layout.desktop.headerGap', { inputType: 'number', type: 'number', fallback: profile.layout.desktopHeaderGap }))
                        + '</div>'
                        + '<div class="nbh-note">Cards Slider всегда рендерится как full-width rail. Здесь задаются только ритм секции и плотность rail на desktop.</div>';
                }

                return body + '<div class="nbh-grid-2">'
                    + nbhField('Отступ сверху', nbhInput('layout.mobile.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingTop }))
                    + nbhField('Отступ снизу', nbhInput('layout.mobile.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingBottom }))
                    + nbhField('Слайдов в ряд', nbhInput('layout.mobile.slidesPerView', { inputType: 'number', type: 'number', fallback: profile.layout.mobileSlidesPerView }))
                    + nbhField('Gap между слайдами', nbhInput('layout.mobile.slideGap', { inputType: 'number', type: 'number', fallback: profile.layout.mobileGap }))
                    + nbhField('Отступ header/slider', nbhInput('layout.mobile.headerGap', { inputType: 'number', type: 'number', fallback: profile.layout.mobileHeaderGap }))
                    + '</div>'
                    + '<div class="nbh-note">Mobile rail остаётся свайповым. Поле "Слайдов в ряд" задаёт, сколько карточек видно в viewport одновременно.</div>';
            }

            if (profile.kind === 'headline_feed') {
                if (bp === 'desktop') {
                    return body + '<div class="nbh-grid-2">'
                        + nbhField('Visual preset', nbhSelect('layout.preset', profile.presets || [
                            { value: 'split', label: 'Lead слева + лента' },
                            { value: 'stack', label: 'Lead сверху + сетка' },
                            { value: 'cover', label: 'Lead cover + сетка' }
                        ], 'split'))
                        + nbhField('Ширина контейнера', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: profile.contentWidth }))
                        + nbhField('Отступ сверху', nbhInput('layout.desktop.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingTop }))
                        + nbhField('Отступ снизу', nbhInput('layout.desktop.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingBottom }))
                        + nbhField('Колонки продолжения', nbhInput('layout.desktop.columns', { inputType: 'number', type: 'number', fallback: profile.layout.desktopColumns }))
                        + nbhField('Gap карточек', nbhInput('layout.desktop.cardGap', { inputType: 'number', type: 'number', fallback: profile.layout.desktopCardGap }))
                        + nbhField('Отступ header/layout', nbhInput('layout.desktop.headerGap', { inputType: 'number', type: 'number', fallback: profile.layout.desktopHeaderGap }))
                        + '</div>'
                        + '<div class="nbh-note">Первая карточка всегда становится главной статьёй. Preset меняет подачу lead-материала и расположение продолжения ленты.</div>';
                }

                return body + '<div class="nbh-grid-2">'
                    + nbhField('Отступ сверху', nbhInput('layout.mobile.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingTop }))
                    + nbhField('Отступ снизу', nbhInput('layout.mobile.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingBottom }))
                    + nbhField('Колонки продолжения', nbhInput('layout.mobile.columns', { inputType: 'number', type: 'number', fallback: profile.layout.mobileColumns }))
                    + nbhField('Gap карточек', nbhInput('layout.mobile.cardGap', { inputType: 'number', type: 'number', fallback: profile.layout.mobileCardGap }))
                    + nbhField('Отступ header/layout', nbhInput('layout.mobile.headerGap', { inputType: 'number', type: 'number', fallback: profile.layout.mobileHeaderGap }))
                    + '</div>';
            }

            if (profile.kind === 'content_feed' || profile.kind === 'category_cards' || profile.kind === 'swiss_grid' || profile.kind === 'catalog_browser') {
                var desktopColumnsLabel = profile.kind === 'catalog_browser' ? 'Колонки desktop (1-6)' : 'Колонки';
                var mobileColumnsLabel = profile.kind === 'catalog_browser' ? 'Колонки mobile (1-2)' : 'Колонки';
                var catalogNote = profile.kind === 'catalog_browser'
                    ? '<div class="nbh-note">Сетка каталога задаётся отдельно для desktop и mobile. Оба поля обязательны: desktop поддерживает 1-6 колонок, mobile 1-2. При 5-6 колонках runtime автоматически уплотняет карточки.</div>'
                    : '';
                if (bp === 'desktop') {
                    return body + '<div class="nbh-grid-2">'
                        + (profile.kind === 'content_feed'
                            ? nbhField('Visual preset', nbhSelect('layout.preset', profile.presets || [
                                { value: 'default', label: 'Default editorial' },
                                { value: 'swiss', label: 'Swiss grid' }
                            ], 'default'))
                            : '')
                        + nbhField('Отступ сверху', nbhInput('layout.desktop.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingTop }))
                        + nbhField('Отступ снизу', nbhInput('layout.desktop.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingBottom }))
                        + nbhField('Ширина контейнера', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: profile.contentWidth }))
                        + nbhField(desktopColumnsLabel, nbhInput('layout.desktop.columns', { inputType: 'number', type: 'number', fallback: profile.layout.desktopColumns }))
                        + nbhField('Gap карточек', nbhInput('layout.desktop.cardGap', { inputType: 'number', type: 'number', fallback: profile.layout.desktopCardGap }))
                        + nbhField('Отступ header/grid', nbhInput('layout.desktop.headerGap', { inputType: 'number', type: 'number', fallback: profile.layout.desktopHeaderGap }))
                        + '</div>'
                        + catalogNote;
                }

                return body + '<div class="nbh-grid-2">'
                    + nbhField('Отступ сверху', nbhInput('layout.mobile.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingTop }))
                    + nbhField('Отступ снизу', nbhInput('layout.mobile.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingBottom }))
                    + nbhField(mobileColumnsLabel, nbhInput('layout.mobile.columns', { inputType: 'number', type: 'number', fallback: profile.layout.mobileColumns }))
                    + nbhField('Gap карточек', nbhInput('layout.mobile.cardGap', { inputType: 'number', type: 'number', fallback: profile.layout.mobileCardGap }))
                    + nbhField('Отступ header/grid', nbhInput('layout.mobile.headerGap', { inputType: 'number', type: 'number', fallback: profile.layout.mobileHeaderGap }))
                    + '</div>'
                    + catalogNote;
            }

            if (bp === 'desktop') {
                var blockType = nbhBlockType();
                var heroPresetOptions = (blockType === 'hero_panels_wide' || blockType === 'hero_panels_editorial')
                    ? [
                        { value: 'classic', label: 'Широкий baseline' },
                        { value: 'split-left', label: 'Фото слева' },
                        { value: 'split-right', label: 'Фото справа' },
                        { value: 'edge-left', label: 'Full-bleed слева' },
                        { value: 'edge-right', label: 'Full-bleed справа' },
                        { value: 'strip', label: 'Без вертикальных отступов' }
                    ]
                    : (profile.presets || [
                        { value: 'classic', label: 'Текст по центру' },
                        { value: 'split-left', label: 'Фото слева' },
                        { value: 'split-right', label: 'Фото справа' },
                        { value: 'edge-left', label: 'Фото слева до края' },
                        { value: 'edge-right', label: 'Фото справа до края' },
                        { value: 'strip', label: 'Без вертикальных отступов' }
                    ]);
                return body + '<div class="nbh-grid-2">'
                    + (profile.kind === 'hero'
                        ? nbhField('Hero preset', nbhSelect('layout.preset', heroPresetOptions, profile.layoutPreset || 'classic'))
                        : '')
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
        'slider-motion-panel': function() {
            var profile = nbhBlockUiProfile();
            return '<div class="nbh-grid-2">'
                + nbhField('Swipe на mobile', nbhSelect('runtime.slider.swipe', nbhYesNoOptions(), profile.layout.swipe || '1'))
                + nbhField('Autoplay', nbhSelect('runtime.slider.autoplay', nbhYesNoOptions(), profile.layout.autoplay || '0'))
                + nbhField('Loop', nbhSelect('runtime.slider.loop', nbhYesNoOptions(), profile.layout.loop || '0'))
                + nbhField('Autoplay delay, мс', nbhInput('runtime.slider.autoplayDelay', { inputType: 'number', type: 'number', fallback: profile.layout.autoplayDelay || 4500 }))
                + nbhField('Transition, мс', nbhInput('runtime.slider.transitionMs', { inputType: 'number', type: 'number', fallback: profile.layout.transitionMs || 450 }))
                + '</div>'
                + '<div class="nbh-note">Эти параметры описывают поведение rail во frontend runtime. Swipe должен оставаться включённым для мобильного сценария.</div>';
        },
        'slider-navigation-layout-panel': function(panel, bp) {
            var profile = nbhBlockUiProfile();
            var prefix = 'layout.' + bp;
            return nbhBreakpointToggle() + '<div class="nbh-grid-2">'
                + nbhField('Позиция navigation', nbhSelect(prefix + '.navigationPosition', [
                    { value: 'overlay', label: 'Поверх rail' },
                    { value: 'below', label: 'Под rail' },
                    { value: 'hidden', label: 'Скрыть' }
                ], profile.layout.navigationPosition || 'overlay'))
                + nbhField('Позиция pagination', nbhSelect(prefix + '.paginationPosition', [
                    { value: 'below', label: 'Под rail' },
                    { value: 'overlay', label: 'Поверх rail' },
                    { value: 'hidden', label: 'Скрыть' }
                ], profile.layout.paginationPosition || 'below'))
                + nbhField('Позиция progress', nbhSelect(prefix + '.progressPosition', [
                    { value: 'below', label: 'Под rail' },
                    { value: 'overlay', label: 'Поверх rail' },
                    { value: 'hidden', label: 'Скрыть' }
                ], profile.layout.progressPosition || 'below'))
                + '</div>'
                + '<div class="nbh-note">Layout управляет размещением контролов вокруг viewport. Фактические цвета и размеры задаются на вкладке Дизайн.</div>';
        },
        'alignment-layout-panel': function() {
            var profile = nbhBlockUiProfile();
            if (profile.layout.primaryControl === 'headline-feed') {
                return nbhField('Выравнивание header', nbhSelect('layout.desktop.align', [
                    { value: 'left', label: 'Слева' },
                    { value: 'center', label: 'По центру' }
                ], 'left'));
            }
            if (profile.layout.primaryControl === 'feed-grid') {
                return nbhField('Выравнивание header', nbhSelect('layout.desktop.align', [
                    { value: 'left', label: 'Слева' },
                    { value: 'center', label: 'По центру' }
                ], 'left'));
            }
            if (profile.layout.primaryControl === 'align') {
                return nbhField('Выравнивание', nbhSelect('layout.desktop.align', [
                    { value: 'center', label: 'По центру' },
                    { value: 'left', label: 'Слева' }
                ], 'center'));
            }
            if (profile.kind === 'hero') {
                return '<div class="nbh-grid-2">'
                    + nbhField('Компоновка блока', nbhSelect('layout.desktop.mode', [
                        { value: 'centered', label: 'По центру' },
                        { value: 'left', label: 'Слева' },
                        { value: 'split', label: 'Текст и медиа' }
                    ], 'centered'))
                    + nbhField('Позиция фото на desktop', nbhSelect('layout.desktop.mediaPosition', [
                        { value: 'start', label: 'Слева' },
                        { value: 'end', label: 'Справа' }
                    ], profile.layout.mediaPositionDesktop || 'start'))
                    + nbhField('Позиция фото на mobile', nbhSelect('layout.mobile.mediaPosition', [
                        { value: 'top', label: 'Сверху текста' },
                        { value: 'bottom', label: 'Под текстом' }
                    ], profile.layout.mediaPositionMobile || 'top'))
                    + '</div>'
                    + '<div class="nbh-note">Preset выставляет стартовую композицию, а эти поля позволяют вручную дожать hero под конкретный экран.</div>';
            }

            return nbhField('Компоновка блока', nbhSelect('layout.desktop.mode', [
                { value: 'centered', label: 'По центру' },
                { value: 'left', label: 'Слева' },
                { value: 'split', label: 'Текст и медиа' }
            ], 'centered'));
        }
    };
}