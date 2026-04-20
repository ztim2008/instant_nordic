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
            if (profile.kind === 'hero') {
                return '<div class="nbh-grid-2">'
                    + nbhField('Ширина контента', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: profile.contentWidth }))
                    + nbhField('Контейнер секции', nbhSelect('layout.desktop.containerMode', [
                        { value: 'contained', label: 'Внутри контейнера' },
                        { value: 'fluid', label: 'Фото тянется к краю окна' }
                    ], profile.layout.containerMode || 'contained'))
                    + '</div>'
                    + '<div class="nbh-note">Для hero контейнер можно оставить обычным или сделать edge-to-browser для фото. Текст при этом остаётся в читаемой колонке.</div>';
            }

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
                body += nbhResponsiveTypographyPanel('design.entities.title', profile.title, bp, { hasMarginBottom: profile.kind !== 'swiss_grid', hasMaxWidth: true, includeBlackWeight: true });
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
                return body + nbhResponsiveTypographyPanel('design.entities.subtitle', profile.subtitle, bp, { hasMarginBottom: profile.kind !== 'swiss_grid', hasMaxWidth: true, includeBlackWeight: true });
            }
            if (panel.entityScope === 'body') {
                return body + nbhResponsiveTypographyPanel('design.entities.body', profile.body || {
                    desktopFontSize: 18,
                    mobileFontSize: 17,
                    desktopWeight: '400',
                    mobileWeight: '400',
                    desktopColor: '#f8fafc',
                    mobileColor: '#f8fafc',
                    desktopLineHeightPercent: 170,
                    mobileLineHeightPercent: 170,
                    desktopLetterSpacing: 0,
                    mobileLetterSpacing: 0
                }, bp, { hasMarginBottom: false, hasMaxWidth: false, includeBlackWeight: false });
            }
            if (panel.entityScope === 'meta') {
                return body + nbhResponsiveTypographyPanel('design.entities.meta', profile.meta, bp, { hasMarginBottom: profile.kind !== 'swiss_grid', hasMaxWidth: false, includeBlackWeight: true });
            }
            if (panel.entityScope === 'cardPrice') {
                body += nbhResponsiveTypographyPanel('design.entities.cardPrice', profile.cardPrice || profile.title, bp, { hasMarginBottom: false, hasMaxWidth: false, includeBlackWeight: true });
                body += '<div class="nbh-note">Этот контрол управляет именно текущей ценой. Зачеркнутая старая цена продолжает использовать мета-типографику.</div>';
                return body;
            }
            if ((panel.entityScope === 'items' || panel.entityScope === 'itemTitle' || panel.entityScope === 'itemText' || panel.entityScope === 'itemLink') && profile.itemTypography.enabled) {
                if (profile.kind === 'content_feed' || profile.kind === 'category_cards' || profile.kind === 'headline_feed' || profile.kind === 'swiss_grid' || profile.kind === 'catalog_browser' || profile.kind === 'bento_feed') {
                    var titleDefaults = profile.itemTypography.title || {};
                    var textDefaults = profile.itemTypography.text || {};
                    var linkDefaults = profile.itemTypography.link || null;

                    body += '<div class="nbh-grid-2">'
                        + nbhField('Размер заголовка карточки', nbhInput('design.entities.itemTitle.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? titleDefaults.desktopFontSize : titleDefaults.mobileFontSize }))
                        + nbhField('Размер анонса карточки', nbhInput('design.entities.itemText.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? textDefaults.desktopFontSize : textDefaults.mobileFontSize }))
                        + (linkDefaults
                            ? nbhField('Размер CTA карточки', nbhInput('design.entities.itemLink.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? linkDefaults.desktopFontSize : linkDefaults.mobileFontSize }))
                            : '')
                        + nbhField('Жирность заголовка', nbhSelect('design.entities.itemTitle.' + bp + '.weight', nbhTypographyWeightOptions(false), bp === 'desktop' ? titleDefaults.desktopWeight : titleDefaults.mobileWeight))
                        + nbhField('Жирность анонса', nbhSelect('design.entities.itemText.' + bp + '.weight', nbhTypographyWeightOptions(false), bp === 'desktop' ? textDefaults.desktopWeight : textDefaults.mobileWeight))
                        + (linkDefaults
                            ? nbhField('Жирность CTA', nbhSelect('design.entities.itemLink.' + bp + '.weight', nbhTypographyWeightOptions(false), bp === 'desktop' ? linkDefaults.desktopWeight : linkDefaults.mobileWeight))
                            : '')
                        + '</div>';

                    body += '<div class="nbh-grid-2">'
                        + nbhField('Цвет заголовка', nbhInput('design.entities.itemTitle.' + bp + '.color', { inputType: 'color', fallback: bp === 'desktop' ? titleDefaults.desktopColor : titleDefaults.mobileColor }))
                        + nbhField('Цвет анонса', nbhInput('design.entities.itemText.' + bp + '.color', { inputType: 'color', fallback: bp === 'desktop' ? textDefaults.desktopColor : textDefaults.mobileColor }))
                        + (linkDefaults
                            ? nbhField('Цвет CTA', nbhInput('design.entities.itemLink.' + bp + '.color', { inputType: 'color', fallback: bp === 'desktop' ? linkDefaults.desktopColor : linkDefaults.mobileColor }))
                            : '')
                        + nbhField('Высота строки заголовка, %', nbhInput('design.entities.itemTitle.' + bp + '.lineHeightPercent', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? titleDefaults.desktopLineHeightPercent : titleDefaults.mobileLineHeightPercent }))
                        + nbhField('Высота строки анонса, %', nbhInput('design.entities.itemText.' + bp + '.lineHeightPercent', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? textDefaults.desktopLineHeightPercent : textDefaults.mobileLineHeightPercent }))
                        + (linkDefaults
                            ? nbhField('Высота строки CTA, %', nbhInput('design.entities.itemLink.' + bp + '.lineHeightPercent', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? linkDefaults.desktopLineHeightPercent : linkDefaults.mobileLineHeightPercent }))
                            : '')
                        + nbhField('Трекинг заголовка, px', nbhInput('design.entities.itemTitle.' + bp + '.letterSpacing', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? titleDefaults.desktopLetterSpacing : titleDefaults.mobileLetterSpacing }))
                        + nbhField('Трекинг анонса, px', nbhInput('design.entities.itemText.' + bp + '.letterSpacing', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? textDefaults.desktopLetterSpacing : textDefaults.mobileLetterSpacing }))
                        + (linkDefaults
                            ? nbhField('Трекинг CTA, px', nbhInput('design.entities.itemLink.' + bp + '.letterSpacing', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? linkDefaults.desktopLetterSpacing : linkDefaults.mobileLetterSpacing }))
                            : '')
                        + '</div>';

                    return body;
                }

                var titleLabel = profile.itemTypography.titleLabel || 'Размер вопроса';
                var textLabel = profile.itemTypography.textLabel || 'Размер ответа';
                var titleDesktopSize = profile.itemTypography.titleDesktopSize || profile.itemTypography.questionDesktopSize;
                var titleMobileSize = profile.itemTypography.titleMobileSize || profile.itemTypography.questionMobileSize;
                var textDesktopSize = profile.itemTypography.textDesktopSize || profile.itemTypography.answerDesktopSize;
                var textMobileSize = profile.itemTypography.textMobileSize || profile.itemTypography.answerMobileSize;
                var titleWeightFallback = '700';
                var titleColorFallback = '#0f172a';
                var textColorFallback = '#475569';
                var titleLineHeightFallback = 135;
                var textLineHeightFallback = 170;
                body += '<div class="nbh-grid-2">'
                    + nbhField(titleLabel, nbhInput('design.entities.itemTitle.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? titleDesktopSize : titleMobileSize }))
                    + nbhField(textLabel, nbhInput('design.entities.itemText.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? textDesktopSize : textMobileSize }))
                    + '</div>';
                if (bp === 'desktop') {
                    body += nbhField('Жирность вопроса', nbhSelect('design.entities.itemTitle.weight', [
                        { value: '400', label: '400' },
                        { value: '500', label: '500' },
                        { value: '600', label: '600' },
                        { value: '700', label: '700' },
                        { value: '800', label: '800' }
                    ], titleWeightFallback));
                    body += '<div class="nbh-grid-2">'
                        + nbhField('Цвет вопроса', nbhInput('design.entities.itemTitle.color', { inputType: 'color', fallback: titleColorFallback }))
                        + nbhField('Высота строки вопроса, %', nbhInput('design.entities.itemTitle.lineHeightPercent', { inputType: 'number', type: 'number', fallback: titleLineHeightFallback }))
                        + nbhField('Трекинг вопроса, px', nbhInput('design.entities.itemTitle.letterSpacing', { inputType: 'number', type: 'number', fallback: 0 }))
                        + nbhField('Цвет ответа', nbhInput('design.entities.itemText.color', { inputType: 'color', fallback: textColorFallback }))
                        + nbhField('Высота строки ответа, %', nbhInput('design.entities.itemText.lineHeightPercent', { inputType: 'number', type: 'number', fallback: textLineHeightFallback }))
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
                + nbhField('Стиль третьей кнопки', nbhSelect('design.entities.tertiaryButton.style', [
                    { value: 'primary', label: 'Основная' },
                    { value: 'outline', label: 'Контурная' },
                    { value: 'ghost', label: 'Прозрачная' }
                ], 'ghost'))
                + '</div>';

            body += nbhBreakpointToggle();
            body += nbhResponsiveTypographyPanel('design.entities.buttonsText', profile.buttonsText, bp, { hasMarginBottom: false, hasMaxWidth: false, includeBlackWeight: true });
            body += '<div class="nbh-note">Цвет текста кнопок можно задать отдельно для desktop и mobile. Если поле не меняли, кнопка продолжает брать цвет из выбранного стиля.</div>';

            return body;
        },
        'slider-item-typography-panel': function(panel, bp) {
            var profile = nbhBlockUiProfile();
            var typo = profile.slideTypography || {};
            var body = nbhBreakpointToggle();

            function section(label, path, defaults) {
                return '<div class="nbh-note" style="margin-top:.75rem;background:#fff;border:1px solid #dbe4ef;">'
                    + '<strong style="display:block;margin-bottom:.65rem;color:#0f172a;">' + label + '</strong>'
                    + nbhResponsiveTypographyPanel(path, defaults, bp, { hasMarginBottom: false, hasMaxWidth: false, includeBlackWeight: true })
                    + '</div>';
            }

            if (nbhHasEntity('slideEyebrow')) {
                body += section('Eyebrow', 'design.entities.slideEyebrow', typo.eyebrow || profile.subtitle);
            }
            if (nbhHasEntity('slideTitle')) {
                body += section('Заголовок слайда', 'design.entities.slideTitle', typo.title || profile.title);
            }
            if (nbhHasEntity('slideText')) {
                body += section('Текст слайда', 'design.entities.slideText', typo.text || profile.subtitle);
            }
            if (nbhHasEntity('slideMeta')) {
                body += section('Meta', 'design.entities.slideMeta', typo.meta || profile.subtitle);
            }
            if (nbhHasEntity('slidePrimaryAction')) {
                body += section('Primary CTA', 'design.entities.slidePrimaryAction', typo.primaryAction || profile.buttonsText);
            }
            if (nbhHasEntity('slideSecondaryAction')) {
                body += section('Secondary CTA', 'design.entities.slideSecondaryAction', typo.secondaryAction || profile.buttonsText);
            }

            return body + '<div class="nbh-note">Typography slider items хранится отдельно от секционного title/subtitle, чтобы rail можно было настраивать независимо.</div>';
        },
        'slider-media-design-panel': function() {
            var profile = nbhBlockUiProfile();
            var defaults = profile.slideMedia || { aspectRatio: '4:3', objectFit: 'cover', radius: 24 };
            return '<div class="nbh-grid-2">'
                + nbhField('Формат кадра', nbhSelect('design.entities.slideMedia.aspectRatio', [
                    { value: 'auto', label: 'По размеру изображения' },
                    { value: '16:9', label: '16:9' },
                    { value: '4:3', label: '4:3' },
                    { value: '1:1', label: '1:1' },
                    { value: '3:4', label: '3:4' }
                ], defaults.aspectRatio || '4:3'))
                + nbhField('Вписывание', nbhSelect('design.entities.slideMedia.objectFit', [
                    { value: 'cover', label: 'Заполнить кадр' },
                    { value: 'contain', label: 'Показать целиком' }
                ], defaults.objectFit || 'cover'))
                + nbhField('Скругление', nbhInput('design.entities.slideMedia.radius', { inputType: 'number', type: 'number', fallback: defaults.radius || 24 }))
                + '</div>'
                + '<div class="nbh-note">Этот контрол отвечает именно за медиа внутри slide, а не за фон секции.</div>';
        },
        'slider-surface-design-panel': function() {
            var profile = nbhBlockUiProfile();
            var defaults = profile.slideSurface || { backgroundMode: 'solid', backgroundColor: '#ffffff', padding: 0, radius: 28, borderWidth: 1, borderColor: '#dbe4ef', shadow: 'sm' };
            return '<div class="nbh-grid-2">'
                + nbhField('Подложка', nbhSelect('design.entities.slideSurface.backgroundMode', [
                    { value: 'solid', label: 'Цветная' },
                    { value: 'transparent', label: 'Прозрачная' }
                ], defaults.backgroundMode || 'solid'))
                + nbhField('Цвет поверхности', nbhInput('design.entities.slideSurface.backgroundColor', { inputType: 'color', fallback: defaults.backgroundColor || '#ffffff' }))
                + nbhField('Внутренний отступ', nbhInput('design.entities.slideSurface.padding', { inputType: 'number', type: 'number', fallback: defaults.padding || 0 }))
                + nbhField('Скругление', nbhInput('design.entities.slideSurface.radius', { inputType: 'number', type: 'number', fallback: defaults.radius || 28 }))
                + nbhField('Толщина рамки', nbhInput('design.entities.slideSurface.borderWidth', { inputType: 'number', type: 'number', fallback: defaults.borderWidth || 1 }))
                + nbhField('Цвет рамки', nbhInput('design.entities.slideSurface.borderColor', { inputType: 'color', fallback: defaults.borderColor || '#dbe4ef' }))
                + nbhField('Тень', nbhSelect('design.entities.slideSurface.shadow', [
                    { value: 'none', label: 'Без тени' },
                    { value: 'sm', label: 'Мягкая' },
                    { value: 'md', label: 'Средняя' },
                    { value: 'lg', label: 'Выразительная' }
                ], defaults.shadow || 'sm'))
                + '</div>'
                + '<div class="nbh-note">Slide surface управляет карточкой целиком: фон, рамка, radius и shadow rail-элемента.</div>';
        },
        'slider-navigation-design-panel': function() {
            var profile = nbhBlockUiProfile();
            var defaults = profile.navigation || { size: 46, radius: 999, backgroundColor: '#0f172a', textColor: '#ffffff', borderColor: '#0f172a', shadow: 'md' };
            return '<div class="nbh-grid-2">'
                + nbhField('Размер кнопок', nbhInput('design.entities.navigation.size', { inputType: 'number', type: 'number', fallback: defaults.size || 46 }))
                + nbhField('Скругление', nbhInput('design.entities.navigation.radius', { inputType: 'number', type: 'number', fallback: defaults.radius || 999 }))
                + nbhField('Фон кнопок', nbhInput('design.entities.navigation.backgroundColor', { inputType: 'color', fallback: defaults.backgroundColor || '#0f172a' }))
                + nbhField('Цвет иконок', nbhInput('design.entities.navigation.textColor', { inputType: 'color', fallback: defaults.textColor || '#ffffff' }))
                + nbhField('Цвет рамки', nbhInput('design.entities.navigation.borderColor', { inputType: 'color', fallback: defaults.borderColor || '#0f172a' }))
                + nbhField('Тень', nbhSelect('design.entities.navigation.shadow', [
                    { value: 'none', label: 'Без тени' },
                    { value: 'sm', label: 'Мягкая' },
                    { value: 'md', label: 'Средняя' },
                    { value: 'lg', label: 'Выразительная' }
                ], defaults.shadow || 'md'))
                + '</div>'
                + '<div class="nbh-note">Оформление navigation применяется сразу к prev/next controls slider rail.</div>';
        },
        'slider-pagination-design-panel': function() {
            var profile = nbhBlockUiProfile();
            var defaults = profile.pagination || { dotSize: 10, gap: 8, color: '#cbd5e1', activeColor: '#0f172a' };
            return '<div class="nbh-grid-2">'
                + nbhField('Размер точки', nbhInput('design.entities.pagination.dotSize', { inputType: 'number', type: 'number', fallback: defaults.dotSize || 10 }))
                + nbhField('Gap между точками', nbhInput('design.entities.pagination.gap', { inputType: 'number', type: 'number', fallback: defaults.gap || 8 }))
                + nbhField('Неактивный цвет', nbhInput('design.entities.pagination.color', { inputType: 'color', fallback: defaults.color || '#cbd5e1' }))
                + nbhField('Активный цвет', nbhInput('design.entities.pagination.activeColor', { inputType: 'color', fallback: defaults.activeColor || '#0f172a' }))
                + '</div>'
                + '<div class="nbh-note">Pagination dots отделены от navigation, чтобы у slider был самостоятельный visual vocabulary.</div>';
        },
        'slider-progress-design-panel': function() {
            var profile = nbhBlockUiProfile();
            var defaults = profile.progress || { trackColor: '#e2e8f0', fillColor: '#0f172a', height: 4, radius: 999 };
            return '<div class="nbh-grid-2">'
                + nbhField('Высота трека', nbhInput('design.entities.progress.height', { inputType: 'number', type: 'number', fallback: defaults.height || 4 }))
                + nbhField('Скругление', nbhInput('design.entities.progress.radius', { inputType: 'number', type: 'number', fallback: defaults.radius || 999 }))
                + nbhField('Цвет трека', nbhInput('design.entities.progress.trackColor', { inputType: 'color', fallback: defaults.trackColor || '#e2e8f0' }))
                + nbhField('Цвет заполнения', nbhInput('design.entities.progress.fillColor', { inputType: 'color', fallback: defaults.fillColor || '#0f172a' }))
                + '</div>'
                + '<div class="nbh-note">Progress bar можно использовать отдельно от dots, если нужен более редакционный rail indicator.</div>';
        },
        'media-style-panel': function(panel) {
            var profile = nbhBlockUiProfile();
            if (!panel || panel.entityScope !== 'media') {
                return '<div class="nbh-note">Панель стиля медиа активируется только для сущности изображения.</div>';
            }

            var isCatalogBrowser = nbhCollectionBlockKind() === 'catalog_browser';
            var mediaInheritGlobal = String(nbhGet(
                nbhState.draft,
                'design.entities.media.inheritGlobalStyle',
                profile.media && profile.media.inheritGlobalStyle === false ? '0' : '1'
            )) !== '0';

            var body = '<div class="nbh-grid-2">'
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
                ], profile.media.objectFit));

            if (isCatalogBrowser) {
                body += nbhField('Источник скругления', nbhSelect('design.entities.media.inheritGlobalStyle', [
                    { value: '1', label: 'Наследовать глобальную дизайн-систему' },
                    { value: '0', label: 'Локально переопределить' }
                ], mediaInheritGlobal ? '1' : '0'));
            }

            if (!isCatalogBrowser || !mediaInheritGlobal) {
                body += nbhField('Скругление изображения', nbhInput('design.entities.media.radius', { inputType: 'number', type: 'number', fallback: profile.media.radius }));
            }

            body += '</div>';

            if (isCatalogBrowser && mediaInheritGlobal) {
                body += '<div class="nbh-note">Изображение и его базовое скругление сейчас берутся из глобальной дизайн-системы. Чтобы задать локальный radius, переключите режим на локальное переопределение.</div>';
            } else {
                body += '<div class="nbh-note">Эти настройки управляют самим изображением: форматом кадра, способом вписывания и собственным радиусом.</div>';
            }

            return body;
        },
        'surface-style-panel': function(panel) {
            var profile = nbhBlockUiProfile();
            if (panel && panel.entityScope === 'mediaSurface') {
                var catalogMediaInheritGlobal = nbhCollectionBlockKind() === 'catalog_browser'
                    && String(nbhGet(
                        nbhState.draft,
                        'design.entities.media.inheritGlobalStyle',
                        profile.media && profile.media.inheritGlobalStyle === false ? '0' : '1'
                    )) !== '0';
                var mediaSurfaceBody = '<div class="nbh-grid-2">'
                    + nbhField('Подложка', nbhSelect('design.entities.mediaSurface.backgroundMode', [
                        { value: 'transparent', label: 'Прозрачная' },
                        { value: 'solid', label: 'Цветная' }
                    ], profile.mediaSurface.backgroundMode))
                    + nbhField('Фон поверхности', nbhInput('design.entities.mediaSurface.backgroundColor', { inputType: 'color', fallback: profile.mediaSurface.backgroundColor }))
                    + nbhField('Внутренний отступ', nbhInput('design.entities.mediaSurface.padding', { inputType: 'number', type: 'number', fallback: profile.mediaSurface.padding }))
                    + nbhField('Толщина рамки', nbhInput('design.entities.mediaSurface.borderWidth', { inputType: 'number', type: 'number', fallback: profile.mediaSurface.borderWidth }))
                    + nbhField('Цвет рамки', nbhInput('design.entities.mediaSurface.borderColor', { inputType: 'color', fallback: profile.mediaSurface.borderColor }))
                    + nbhField('Тень', nbhSelect('design.entities.mediaSurface.shadow', [
                        { value: 'none', label: 'Без тени' },
                        { value: 'sm', label: 'Мягкая' },
                        { value: 'md', label: 'Средняя' },
                        { value: 'lg', label: 'Выразительная' }
                    ], profile.mediaSurface.shadow));

                if (!catalogMediaInheritGlobal) {
                    mediaSurfaceBody += nbhField('Скругление поверхности', nbhInput('design.entities.mediaSurface.radius', { inputType: 'number', type: 'number', fallback: profile.mediaSurface.radius }));
                }

                mediaSurfaceBody += '</div>';
                if (catalogMediaInheritGlobal) {
                    mediaSurfaceBody += '<div class="nbh-note">Скругление подложки сейчас следует за глобальным radius для медиа. Если нужен отдельный локальный radius, переключите изображение в режим локального переопределения.</div>';
                } else {
                    mediaSurfaceBody += '<div class="nbh-note">По умолчанию подложка прозрачная. Это удобно для PNG без фона. Если нужен цветной фон под изображением, переключите подложку в режим "Цветная".</div>';
                }

                return mediaSurfaceBody;
            }

            if (panel && (panel.entityScope === 'accentSurface' || panel.entityScope === 'bodySurface')) {
                var surfaceDefaults = panel.entityScope === 'accentSurface'
                    ? (profile.accentSurface || { backgroundMode: 'solid', backgroundColor: '#2563eb' })
                    : (profile.bodySurface || { backgroundMode: 'solid', backgroundColor: '#1d1d1f' });
                var surfacePath = 'design.entities.' + panel.entityScope;
                var surfaceLabel = panel.entityScope === 'accentSurface' ? 'Акцентная панель' : 'Темная панель';
                var surfaceBody = '<div class="nbh-grid-2">'
                    + nbhField('Подложка', nbhSelect(surfacePath + '.backgroundMode', [
                        { value: 'solid', label: 'Цветная' },
                        { value: 'transparent', label: 'Прозрачная' }
                    ], surfaceDefaults.backgroundMode || 'solid'))
                    + nbhField('Цвет панели', nbhInput(surfacePath + '.backgroundColor', { inputType: 'color', fallback: surfaceDefaults.backgroundColor || '#2563eb' }))
                    + '</div>';

                surfaceBody += '<div class="nbh-note">Этот контрол управляет заливкой сущности "' + surfaceLabel + '" в live preview и после сохранения.</div>';

                return surfaceBody;
            }

            if (panel && panel.entityScope === 'toolbar') {
                var toolbarDefaults = profile.toolbarSurface || { backgroundMode: 'solid', backgroundColor: '#ffffff', padding: 16, radius: 22, borderWidth: 1, borderColor: '#dbe4ef', shadow: 'sm' };
                return '<div class="nbh-grid-2">'
                    + nbhField('Подложка', nbhSelect('design.entities.toolbar.backgroundMode', [
                        { value: 'transparent', label: 'Прозрачная' },
                        { value: 'solid', label: 'Цветная' }
                    ], toolbarDefaults.backgroundMode || 'solid'))
                    + nbhField('Цвет панели', nbhInput('design.entities.toolbar.backgroundColor', { inputType: 'color', fallback: toolbarDefaults.backgroundColor || '#ffffff' }))
                    + nbhField('Внутренний отступ', nbhInput('design.entities.toolbar.padding', { inputType: 'number', type: 'number', fallback: toolbarDefaults.padding || 16 }))
                    + nbhField('Скругление', nbhInput('design.entities.toolbar.radius', { inputType: 'number', type: 'number', fallback: toolbarDefaults.radius || 22 }))
                    + nbhField('Толщина рамки', nbhInput('design.entities.toolbar.borderWidth', { inputType: 'number', type: 'number', fallback: toolbarDefaults.borderWidth || 1 }))
                    + nbhField('Цвет рамки', nbhInput('design.entities.toolbar.borderColor', { inputType: 'color', fallback: toolbarDefaults.borderColor || '#dbe4ef' }))
                    + nbhField('Тень', nbhSelect('design.entities.toolbar.shadow', [
                        { value: 'none', label: 'Без тени' },
                        { value: 'sm', label: 'Мягкая' },
                        { value: 'md', label: 'Средняя' },
                        { value: 'lg', label: 'Выразительная' }
                    ], toolbarDefaults.shadow || 'sm'))
                    + '</div>'
                    + '<div class="nbh-note">Этот контрол управляет внешней оболочкой панели фильтров целиком.</div>';
            }

            if (panel && panel.entityScope === 'toolbarControls') {
                var toolbarControlsDefaults = profile.toolbarControlsSurface || { backgroundMode: 'solid', backgroundColor: '#ffffff', radius: 16, borderWidth: 1, borderColor: '#d5dfeb', shadow: 'none' };
                return '<div class="nbh-grid-2">'
                    + nbhField('Подложка', nbhSelect('design.entities.toolbarControls.backgroundMode', [
                        { value: 'transparent', label: 'Прозрачная' },
                        { value: 'solid', label: 'Цветная' }
                    ], toolbarControlsDefaults.backgroundMode || 'solid'))
                    + nbhField('Цвет поля', nbhInput('design.entities.toolbarControls.backgroundColor', { inputType: 'color', fallback: toolbarControlsDefaults.backgroundColor || '#ffffff' }))
                    + nbhField('Скругление', nbhInput('design.entities.toolbarControls.radius', { inputType: 'number', type: 'number', fallback: toolbarControlsDefaults.radius || 16 }))
                    + nbhField('Толщина рамки', nbhInput('design.entities.toolbarControls.borderWidth', { inputType: 'number', type: 'number', fallback: toolbarControlsDefaults.borderWidth || 1 }))
                    + nbhField('Цвет рамки', nbhInput('design.entities.toolbarControls.borderColor', { inputType: 'color', fallback: toolbarControlsDefaults.borderColor || '#d5dfeb' }))
                    + nbhField('Тень', nbhSelect('design.entities.toolbarControls.shadow', [
                        { value: 'none', label: 'Без тени' },
                        { value: 'sm', label: 'Мягкая' },
                        { value: 'md', label: 'Средняя' },
                        { value: 'lg', label: 'Выразительная' }
                    ], toolbarControlsDefaults.shadow || 'none'))
                    + '</div>'
                    + '<div class="nbh-note">Этот контрол меняет chrome полей поиска, select и диапазона цены без смешивания с типографикой меты.</div>';
            }

            if (nbhHasEntity('itemSurface') && nbhHasEntity('items')) {
                var isCatalogBrowser = nbhCollectionBlockKind() === 'catalog_browser';
                var itemSurfaceInheritGlobal = String(nbhGet(
                    nbhState.draft,
                    'design.entities.itemSurface.inheritGlobalStyle',
                    profile.itemSurface && profile.itemSurface.inheritGlobalStyle === false ? '0' : '1'
                )) !== '0';
                var body = nbhField('Стиль карточек', nbhSelect('design.entities.itemSurface.variant', [
                    { value: 'card', label: 'Карточки' },
                    { value: 'plain', label: 'Без карточек' }
                ], 'card'));

                if (isCatalogBrowser) {
                    body += nbhField('Источник оформления', nbhSelect('design.entities.itemSurface.inheritGlobalStyle', [
                        { value: '1', label: 'Наследовать глобальную дизайн-систему' },
                        { value: '0', label: 'Локально переопределить' }
                    ], itemSurfaceInheritGlobal ? '1' : '0'));
                }

                if (nbhIsCardCollectionBlock()) {
                    if (!isCatalogBrowser || !itemSurfaceInheritGlobal) {
                        body += '<div class="nbh-grid-2">'
                            + nbhField('Скругление карточки', nbhInput('design.entities.itemSurface.radius', { inputType: 'number', type: 'number', fallback: profile.itemSurface.radius }))
                            + nbhField('Толщина рамки', nbhInput('design.entities.itemSurface.borderWidth', { inputType: 'number', type: 'number', fallback: profile.itemSurface.borderWidth }))
                            + nbhField('Цвет рамки', nbhInput('design.entities.itemSurface.borderColor', { inputType: 'color', fallback: profile.itemSurface.borderColor }))
                            + nbhField('Тень', nbhSelect('design.entities.itemSurface.shadow', [
                                { value: 'none', label: 'Без тени' },
                                { value: 'sm', label: 'Мягкая' },
                                { value: 'md', label: 'Средняя' },
                                { value: 'lg', label: 'Выразительная' }
                            ], profile.itemSurface.shadow))
                            + '</div>';
                    }
                }

                if (isCatalogBrowser && itemSurfaceInheritGlobal) {
                    body += '<div class="nbh-note">Карточки сейчас используют глобальные radius, border и shadow из дизайн-системы. Переключите режим на локальный, если нужен отдельный стиль именно для этого каталога.</div>';
                }

                return body;
            }
            return '<div class="nbh-note">Настройки поверхности пойдут следующим слоем. Сейчас панель показывает, что сущность уже распознана и готова к общему стилевому контракту.</div>';
        }
    };
}