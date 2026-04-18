function nbhBuildDesignControlRenderers() {
    function nbhTypographyWeightOptions(includeBlack) {
        var options = [
            { value: '400', label: '400' },
            { value: '500', label: '500' },
            { value: '600', label: '600' },
            { value: '700', label: '700' },
            { value: '800', label: '800' }
        ];

        if (includeBlack) {
            options.push({ value: '900', label: '900' });
        }

        return options;
    }

    function nbhResponsiveTypographyPanel(basePath, defaults, bp, options) {
        options = options || {};
        var body = '<div class="nbh-grid-2">'
            + nbhField('Размер', nbhInput(basePath + '.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? defaults.desktopFontSize : defaults.mobileFontSize }))
            + (options.hasMarginBottom === false
                ? ''
                : nbhField('Отступ снизу', nbhInput(basePath + '.' + bp + '.marginBottom', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? defaults.desktopMarginBottom : defaults.mobileMarginBottom })))
            + nbhField('Жирность', nbhSelect(basePath + '.' + bp + '.weight', nbhTypographyWeightOptions(!!options.includeBlackWeight), bp === 'desktop' ? defaults.desktopWeight : defaults.mobileWeight))
            + '</div>';

        body += '<div class="nbh-grid-2">'
            + nbhField('Цвет', nbhInput(basePath + '.' + bp + '.color', { inputType: 'color', fallback: bp === 'desktop' ? defaults.desktopColor : defaults.mobileColor }))
            + nbhField('Высота строки, %', nbhInput(basePath + '.' + bp + '.lineHeightPercent', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? defaults.desktopLineHeightPercent : defaults.mobileLineHeightPercent }))
            + nbhField('Трекинг, px', nbhInput(basePath + '.' + bp + '.letterSpacing', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? defaults.desktopLetterSpacing : defaults.mobileLetterSpacing }));

        if (options.hasMaxWidth) {
            body += nbhField('Макс. ширина, px', nbhInput(basePath + '.' + bp + '.maxWidth', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? defaults.desktopMaxWidth : defaults.mobileMaxWidth }));
        }

        body += '</div>';

        return body;
    }

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
            if (panel.entityScope === 'eyebrow') {
                body += nbhResponsiveTypographyPanel('design.entities.eyebrow', profile.eyebrow, bp, { hasMaxWidth: false, includeBlackWeight: false });
                if (bp === 'desktop') {
                    body += nbhField('Регистр', nbhSelect('design.entities.eyebrow.textTransform', [
                        { value: 'uppercase', label: 'Верхний' },
                        { value: 'none', label: 'Как в тексте' }
                    ], profile.eyebrow.textTransform));
                }
                return body;
            }
            if (panel.entityScope === 'title') {
                body += nbhResponsiveTypographyPanel('design.entities.title', profile.title, bp, { hasMaxWidth: true, includeBlackWeight: true });
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
                return body + nbhResponsiveTypographyPanel('design.entities.subtitle', profile.subtitle, bp, { hasMaxWidth: true, includeBlackWeight: true });
            }
            if (panel.entityScope === 'meta') {
                return body + nbhResponsiveTypographyPanel('design.entities.meta', profile.meta, bp, { hasMaxWidth: false, includeBlackWeight: true });
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
        'button-style-panel': function(panel, bp) {
            var profile = nbhBlockUiProfile();
            var body = '<div class="nbh-grid-2">'
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

            body += nbhBreakpointToggle();
            body += nbhResponsiveTypographyPanel('design.entities.buttonsText', profile.buttonsText, bp, { hasMarginBottom: false, hasMaxWidth: false, includeBlackWeight: true });
            body += '<div class="nbh-note">Цвет текста кнопок можно задать отдельно для desktop и mobile. Если поле не меняли, кнопка продолжает брать цвет из выбранного стиля.</div>';

            return body;
        },
        'media-style-panel': function(panel) {
            var profile = nbhBlockUiProfile();
            if (!panel || panel.entityScope !== 'media') {
                return '<div class="nbh-note">Панель стиля медиа активируется только для сущности изображения.</div>';
            }

            return '<div class="nbh-grid-2">'
                + nbhField('Формат кадра', nbhSelect('design.entities.media.aspectRatio', [
                    { value: 'auto', label: 'По размеру изображения' },
                    { value: '16:10', label: '16:10' },
                    { value: '16:9', label: '16:9' },
                    { value: '4:3', label: '4:3' },
                    { value: '1:1', label: '1:1' },
                    { value: '3:4', label: '3:4' }
                ], profile.media.aspectRatio))
                + nbhField('Вписывание', nbhSelect('design.entities.media.objectFit', [
                    { value: 'cover', label: 'Заполнить кадр' },
                    { value: 'contain', label: 'Показать целиком' }
                ], profile.media.objectFit))
                + nbhField('Скругление изображения', nbhInput('design.entities.media.radius', { inputType: 'number', type: 'number', fallback: profile.media.radius }))
                + '</div>'
                + '<div class="nbh-note">Эти настройки управляют самим изображением: форматом кадра, способом вписывания и собственным радиусом.</div>';
        },
        'surface-style-panel': function(panel) {
            var profile = nbhBlockUiProfile();
            if (panel && panel.entityScope === 'mediaSurface') {
                return '<div class="nbh-grid-2">'
                    + nbhField('Подложка', nbhSelect('design.entities.mediaSurface.backgroundMode', [
                        { value: 'transparent', label: 'Прозрачная' },
                        { value: 'solid', label: 'Цветная' }
                    ], profile.mediaSurface.backgroundMode))
                    + nbhField('Фон поверхности', nbhInput('design.entities.mediaSurface.backgroundColor', { inputType: 'color', fallback: profile.mediaSurface.backgroundColor }))
                    + nbhField('Внутренний отступ', nbhInput('design.entities.mediaSurface.padding', { inputType: 'number', type: 'number', fallback: profile.mediaSurface.padding }))
                    + nbhField('Скругление поверхности', nbhInput('design.entities.mediaSurface.radius', { inputType: 'number', type: 'number', fallback: profile.mediaSurface.radius }))
                    + nbhField('Толщина рамки', nbhInput('design.entities.mediaSurface.borderWidth', { inputType: 'number', type: 'number', fallback: profile.mediaSurface.borderWidth }))
                    + nbhField('Цвет рамки', nbhInput('design.entities.mediaSurface.borderColor', { inputType: 'color', fallback: profile.mediaSurface.borderColor }))
                    + nbhField('Тень', nbhSelect('design.entities.mediaSurface.shadow', [
                        { value: 'none', label: 'Без тени' },
                        { value: 'sm', label: 'Мягкая' },
                        { value: 'md', label: 'Средняя' },
                        { value: 'lg', label: 'Выразительная' }
                    ], profile.mediaSurface.shadow))
                        + '</div>'
                        + '<div class="nbh-note">По умолчанию подложка прозрачная. Это удобно для PNG без фона. Если нужен цветной фон под изображением, переключите подложку в режим "Цветная".</div>';
            }

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