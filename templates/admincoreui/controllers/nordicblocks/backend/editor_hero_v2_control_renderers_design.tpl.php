function nbhBuildDesignControlRenderers() {
    return {
        'section-background-panel': function() {
            var profile = nbhBlockUiProfile();
            var backgroundMode = String(nbhGet(nbhState.draft, 'design.section.background.mode', 'theme') || 'theme');
            var body = nbhField('Тема блока', nbhSelect('design.section.theme', profile.themeOptions, 'light'));
            body += nbhField('Режим фона', nbhSelect('design.section.background.mode', [
                { value: 'theme', label: 'Из темы блока' },
                { value: 'color', label: 'Сплошной цвет' },
                { value: 'gradient', label: 'Градиент' },
                { value: 'image', label: 'Фото + затемнение' }
            ], 'theme'));

            if (backgroundMode === 'color') {
                body += nbhField('Цвет фона', nbhInput('design.section.background.color', { inputType: 'color', fallback: '#f8fafc' }));
            }

            if (backgroundMode === 'gradient') {
                body += '<div class="nbh-grid-2">'
                    + nbhField('Цвет 1', nbhInput('design.section.background.gradientFrom', { inputType: 'color', fallback: '#f8fafc' }))
                    + nbhField('Цвет 2', nbhInput('design.section.background.gradientTo', { inputType: 'color', fallback: '#dbeafe' }))
                    + nbhField('Угол', nbhInput('design.section.background.gradientAngle', { inputType: 'number', type: 'number', fallback: 135 }))
                    + '</div>';
            }

            if (backgroundMode === 'image') {
                body += nbhField('Путь к фото', nbhInput('design.section.background.image', { picker: 'image' }));
                body += '<div class="nbh-grid-2">'
                    + nbhField('Цвет затемнения', nbhInput('design.section.background.overlayColor', { inputType: 'color', fallback: '#0f172a' }))
                    + nbhField('Сила затемнения, %', nbhInput('design.section.background.overlayOpacity', { inputType: 'number', type: 'number', fallback: 45 }))
                    + '</div>';
                body += '<div class="nbh-grid-2">'
                    + nbhField('Позиция фото', nbhSelect('design.section.background.imagePosition', [
                        { value: 'center center', label: 'Центр' },
                        { value: 'top center', label: 'Сверху по центру' },
                        { value: 'bottom center', label: 'Снизу по центру' },
                        { value: 'center left', label: 'Слева по центру' },
                        { value: 'center right', label: 'Справа по центру' },
                        { value: 'top left', label: 'Левый верх' },
                        { value: 'top right', label: 'Правый верх' },
                        { value: 'bottom left', label: 'Левый низ' },
                        { value: 'bottom right', label: 'Правый низ' }
                    ], 'center center'))
                    + nbhField('Масштаб', nbhSelect('design.section.background.imageSize', [
                        { value: 'cover', label: 'Заполнить' },
                        { value: 'contain', label: 'Уместить целиком' },
                        { value: 'auto', label: 'Оригинал' }
                    ], 'cover'))
                    + '</div>';
                body += nbhField('Повтор', nbhSelect('design.section.background.imageRepeat', [
                    { value: 'no-repeat', label: 'Без повтора' },
                    { value: 'repeat', label: 'Повторять' },
                    { value: 'repeat-x', label: 'Только по горизонтали' },
                    { value: 'repeat-y', label: 'Только по вертикали' }
                ], 'no-repeat'));
            }

            return body;
        },
        'section-container-panel': function() {
            var profile = nbhBlockUiProfile();
            return nbhField('Ширина контента', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: profile.contentWidth }));
        },
        'typography-text-panel': function(panel, bp) {
            var profile = nbhBlockUiProfile();
            var body = nbhBreakpointToggle();
            if (panel.entityScope === 'title') {
                body += '<div class="nbh-grid-2">'
                    + nbhField('Размер', nbhInput('design.entities.title.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.title.desktopFontSize : profile.title.mobileFontSize }))
                    + nbhField('Отступ снизу', nbhInput('design.entities.title.' + bp + '.marginBottom', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.title.desktopMarginBottom : profile.title.mobileMarginBottom }))
                    + (bp === 'desktop'
                        ? nbhField('Жирность', nbhSelect('design.entities.title.weight', [
                            { value: '400', label: '400' },
                            { value: '500', label: '500' },
                            { value: '600', label: '600' },
                            { value: '700', label: '700' },
                            { value: '800', label: '800' },
                            { value: '900', label: '900' }
                        ], profile.title.weight))
                        : '')
                    + '</div>';
                if (profile.title.desktopExtras && bp === 'desktop') {
                    body += '<div class="nbh-grid-2">'
                        + nbhField('Цвет', nbhInput('design.entities.title.color', { inputType: 'color', fallback: '#0f172a' }))
                        + nbhField('Высота строки, %', nbhInput('design.entities.title.lineHeightPercent', { inputType: 'number', type: 'number', fallback: 110 }))
                        + nbhField('Трекинг, px', nbhInput('design.entities.title.letterSpacing', { inputType: 'number', type: 'number', fallback: 0 }))
                        + nbhField('Макс. ширина, px', nbhInput('design.entities.title.maxWidth', { inputType: 'number', type: 'number', fallback: 600 }))
                        + '</div>';
                }
                if (bp === 'desktop') {
                    body += nbhField('HTML тег', nbhSelect('design.entities.title.tag', [
                        { value: 'div', label: 'DIV' },
                        { value: 'h1', label: 'H1' },
                        { value: 'h2', label: 'H2' },
                        { value: 'h3', label: 'H3' }
                    ], profile.title.tag));
                }
                return body;
            }
            if (panel.entityScope === 'subtitle') {
                body += '<div class="nbh-grid-2">'
                    + nbhField('Размер', nbhInput('design.entities.subtitle.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.subtitle.desktopFontSize : profile.subtitle.mobileFontSize }))
                    + nbhField('Отступ снизу', nbhInput('design.entities.subtitle.' + bp + '.marginBottom', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.subtitle.desktopMarginBottom : profile.subtitle.mobileMarginBottom }))
                    + '</div>';
                if (profile.subtitle.desktopExtras && bp === 'desktop') {
                    body += '<div class="nbh-grid-2">'
                        + nbhField('Цвет', nbhInput('design.entities.subtitle.color', { inputType: 'color', fallback: '#475569' }))
                        + nbhField('Высота строки, %', nbhInput('design.entities.subtitle.lineHeightPercent', { inputType: 'number', type: 'number', fallback: 165 }))
                        + nbhField('Трекинг, px', nbhInput('design.entities.subtitle.letterSpacing', { inputType: 'number', type: 'number', fallback: 0 }))
                        + nbhField('Макс. ширина, px', nbhInput('design.entities.subtitle.maxWidth', { inputType: 'number', type: 'number', fallback: 720 }))
                        + '</div>';
                }
                return body;
            }
            if (panel.entityScope === 'items' && profile.itemTypography.enabled) {
                body += '<div class="nbh-grid-2">'
                    + nbhField('Размер вопроса', nbhInput('design.entities.itemTitle.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.itemTypography.questionDesktopSize : profile.itemTypography.questionMobileSize }))
                    + nbhField('Размер ответа', nbhInput('design.entities.itemText.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.itemTypography.answerDesktopSize : profile.itemTypography.answerMobileSize }))
                    + '</div>';
                if (bp === 'desktop') {
                    body += nbhField('Жирность вопроса', nbhSelect('design.entities.itemTitle.weight', [
                        { value: '400', label: '400' },
                        { value: '500', label: '500' },
                        { value: '600', label: '600' },
                        { value: '700', label: '700' },
                        { value: '800', label: '800' }
                    ], '700'));
                    body += '<div class="nbh-grid-2">'
                        + nbhField('Цвет вопроса', nbhInput('design.entities.itemTitle.color', { inputType: 'color', fallback: '#0f172a' }))
                        + nbhField('Высота строки вопроса, %', nbhInput('design.entities.itemTitle.lineHeightPercent', { inputType: 'number', type: 'number', fallback: 135 }))
                        + nbhField('Трекинг вопроса, px', nbhInput('design.entities.itemTitle.letterSpacing', { inputType: 'number', type: 'number', fallback: 0 }))
                        + nbhField('Цвет ответа', nbhInput('design.entities.itemText.color', { inputType: 'color', fallback: '#475569' }))
                        + nbhField('Высота строки ответа, %', nbhInput('design.entities.itemText.lineHeightPercent', { inputType: 'number', type: 'number', fallback: 170 }))
                        + nbhField('Трекинг ответа, px', nbhInput('design.entities.itemText.letterSpacing', { inputType: 'number', type: 'number', fallback: 0 }))
                        + '</div>';
                }
                return body;
            }
            return body + '<div class="nbh-note">Этот набор настроек зарезервирован под типографику сущностей и будет расширен следующим этапом.</div>';
        },
        'button-style-panel': function() {
            return '<div class="nbh-grid-2">'
                + nbhField('Стиль основной кнопки', nbhSelect('design.entities.primaryButton.style', [
                    { value: 'primary', label: 'Основная' },
                    { value: 'outline', label: 'Контурная' },
                    { value: 'ghost', label: 'Прозрачная' }
                ], 'primary'))
                + nbhField('Стиль вторичной кнопки', nbhSelect('design.entities.secondaryButton.style', [
                    { value: 'primary', label: 'Основная' },
                    { value: 'outline', label: 'Контурная' },
                    { value: 'ghost', label: 'Прозрачная' }
                ], 'outline'))
                + '</div>';
        },
        'surface-style-panel': function() {
            if (nbhHasEntity('itemSurface') && nbhHasEntity('items')) {
                return nbhField('Стиль карточек', nbhSelect('design.entities.itemSurface.variant', [
                    { value: 'card', label: 'Карточки' },
                    { value: 'plain', label: 'Без карточек' }
                ], 'card'));
            }
            return '<div class="nbh-note">Настройки поверхности пойдут следующим слоем. Сейчас панель показывает, что сущность уже распознана и готова к общему стилевому контракту.</div>';
        }
    };
}