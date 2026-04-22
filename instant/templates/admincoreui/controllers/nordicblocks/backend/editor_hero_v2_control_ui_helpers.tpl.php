function nbhRenderTabs() {
    var tabs = (nbhState.inspector && nbhState.inspector.tabs ? nbhState.inspector.tabs : []).slice().sort(function(a, b) { return (a.order || 0) - (b.order || 0); });
    document.getElementById('nbhTabs').innerHTML = tabs.map(function(tab) {
        return '<button type="button" class="nbh-tab' + (tab.key === nbhState.activeTab ? ' is-active' : '') + '" data-tab="' + tab.key + '">' + tab.label + '</button>';
    }).join('');
}

function nbhField(label, controlHtml) {
    return '<div class="nbh-field"><label>' + label + '</label>' + controlHtml + '</div>';
}

function nbhColorInput(path, value) {
    var normalized = nbhNormalizeColor(value, '#000000');

    return '<div class="nbh-color-control" data-color-control="' + nbhEscapeAttr(path) + '">'
        + '<label class="nbh-color-swatch" title="Выбрать цвет">'
        + '<input data-color-path="' + nbhEscapeAttr(path) + '" type="color" value="' + nbhEscapeAttr(normalized) + '">'
        + '<span class="nbh-color-swatch__face" style="background:' + nbhEscapeAttr(normalized) + ';"></span>'
        + '</label>'
        + '<input class="nbh-color-code" data-path="' + nbhEscapeAttr(path) + '" data-color-text="1" type="text" value="' + nbhEscapeAttr(normalized.toUpperCase()) + '" spellcheck="false" autocapitalize="characters">'
        + '</div>';
}

function nbhInput(path, options) {
    options = options || {};
    var fallback = Object.prototype.hasOwnProperty.call(options, 'fallback') ? options.fallback : '';
    var value = nbhGet(nbhState.draft, path, fallback);
    var attrs = 'data-path="' + path + '"';
    var inputType = options.inputType || 'text';
    if (options.type) attrs += ' data-type="' + options.type + '"';
    if (inputType === 'color') {
        value = nbhNormalizeColor(value, fallback || '#000000');
        return nbhColorInput(path, value);
    }

    var inputHtml = '<input ' + attrs + ' type="' + inputType + '" value="' + nbhEscapeAttr(value) + '">';

    if (options.picker === 'image' || options.picker === 'icon') {
        return '<div class="nbh-input-row">'
            + inputHtml
            + '<button type="button" class="nbh-picker-btn" data-picker-action="pick" data-picker-kind="' + options.picker + '" data-path="' + path + '">Выбрать</button>'
            + '<button type="button" class="nbh-picker-btn nbh-picker-btn--clear" data-picker-action="clear" data-path="' + path + '">Очистить</button>'
            + '</div>';
    }

    return inputHtml;
}

function nbhTextarea(path, fallback) {
    var value = nbhGet(nbhState.draft, path, fallback || '');
    return '<textarea data-path="' + path + '">' + String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</textarea>';
}

function nbhSelect(path, options, fallback) {
    var value = nbhGet(nbhState.draft, path, fallback || '');
    if (typeof value === 'boolean') {
        value = value ? '1' : '0';
    }
    value = String(value);
    return '<select data-path="' + path + '">' + options.map(function(option) {
        return '<option value="' + option.value + '"' + (value === option.value ? ' selected' : '') + '>' + option.label + '</option>';
    }).join('') + '</select>';
}

function nbhShouldRerenderPanels(path) {
    return path.indexOf('data.listSource.') === 0
        || path.indexOf('data.source.') === 0
        || path.indexOf('data.bindings.') === 0
    || path.indexOf('design.section.background.') === 0
    || path === 'design.entities.media.inheritGlobalStyle'
    || path === 'design.entities.itemSurface.inheritGlobalStyle'
    || path === 'design.entities.slideSurface.inheritGlobalStyle';
}

function nbhYesNoOptions() {
    return [
        { value: '1', label: 'Показывать' },
        { value: '0', label: 'Скрыть' }
    ];
}

function nbhBreakpointToggle() {
    return '<div class="nbh-breakpoints"><button type="button" data-breakpoint="desktop" class="' + (nbhState.activeBreakpoint === 'desktop' ? 'is-active' : '') + '">Компьютер</button><button type="button" data-breakpoint="mobile" class="' + (nbhState.activeBreakpoint === 'mobile' ? 'is-active' : '') + '">Мобильный</button></div>';
}

function nbhRepeaterImageField(index, image) {
    var path = nbhCollectionRepeaterPath() + '.' + index + '.image';
    var hasImage = typeof image === 'string' && image !== '';
    var preview = hasImage
        ? '<div style="margin-top:.6rem;border:1px solid #dbe4ef;border-radius:14px;overflow:hidden;background:#f8fafc;">'
            + '<img src="' + nbhEscapeAttr(image) + '" alt="Превью изображения" style="display:block;width:100%;max-height:180px;object-fit:cover;">'
            + '</div>'
        : '<div class="nbh-note" style="margin-top:.6rem;padding:.7rem .85rem;">Изображение пока не выбрано. Добавление доступно только через медиабиблиотеку.</div>';

    return '<div>'
        + '<div class="nbh-input-row">'
        + '<button type="button" class="nbh-picker-btn" data-picker-action="pick" data-picker-kind="image" data-path="' + path + '">Выбрать из медиабиблиотеки</button>'
        + '<button type="button" class="nbh-picker-btn nbh-picker-btn--clear" data-picker-action="clear" data-path="' + path + '"' + (hasImage ? '' : ' disabled') + '>Очистить</button>'
        + '</div>'
        + preview
        + '</div>';
}

function nbhItemSelectOptions(options, value) {
    value = String(value == null ? '' : value);
    return '<select data-item-field="__FIELD__" data-item-index="__INDEX__">' + options.map(function(option) {
        var optionValue = String(option.value == null ? '' : option.value);
        return '<option value="' + nbhEscapeAttr(optionValue) + '"' + (value === optionValue ? ' selected' : '') + '>' + option.label + '</option>';
    }).join('') + '</select>';
}

function nbhCatalogAvailabilityOptions() {
    return [
        { value: 'available', label: 'Доступно' },
        { value: 'limited', label: 'Ограничено' },
        { value: 'on_request', label: 'По запросу' },
        { value: 'hidden', label: 'Скрыто' }
    ];
}

function nbhCatalogCtaKindOptions() {
    return [
        { value: 'url', label: 'Ссылка' },
        { value: 'whatsapp', label: 'WhatsApp' },
        { value: 'telegram', label: 'Telegram' },
        { value: 'phone', label: 'Телефон' },
        { value: 'none', label: 'Без кнопки' }
    ];
}

function nbhCatalogMessengerOptions() {
    return [
        { value: 'none', label: 'Нет' },
        { value: 'whatsapp', label: 'WhatsApp' },
        { value: 'telegram', label: 'Telegram' }
    ];
}

function nbhRepeaterActionButton(action, index, label, disabled) {
    return '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="' + action + '" data-item-index="' + index + '" style="padding:.32rem .7rem;font-size:.72rem;"' + (disabled ? ' disabled' : '') + '>' + label + '</button>';
}

function nbhRepeaterEditor() {
    var items = nbhRepeaterItems();
    var listSource = nbhListSource();
    var kind = nbhCollectionBlockKind();
    var cards = items.map(function(item, index) {
        if (nbhIsSliderCollectionBlock()) {
            var eyebrow = nbhCollectionItemValue(item, 'eyebrow');
            var title = nbhCollectionItemValue(item, 'title');
            var text = nbhCollectionItemValue(item, 'text');
            var primaryCtaLabel = nbhCollectionItemValue(item, 'primary_cta_label');
            var primaryCtaUrl = nbhCollectionItemValue(item, 'primary_cta_url');
            var secondaryCtaLabel = nbhCollectionItemValue(item, 'secondary_cta_label');
            var secondaryCtaUrl = nbhCollectionItemValue(item, 'secondary_cta_url');
            var image = nbhCollectionItemValue(item, 'image');
            var imageAlt = nbhCollectionItemValue(item, 'imageAlt');
            var date = nbhCollectionItemValue(item, 'date');
            var metaLabel = nbhCollectionItemValue(item, 'meta_label');
            var recordUrl = nbhCollectionItemValue(item, 'record_url');

            return '<div class="nbh-note" style="background:#fff;border:1px solid #dbe4ef;">'
                + '<div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-bottom:.75rem;">'
                + '<strong>Слайд ' + (index + 1) + '</strong>'
                + '<div style="display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.4rem;">'
                + nbhRepeaterActionButton('move-up', index, 'Выше', index === 0)
                + nbhRepeaterActionButton('move-down', index, 'Ниже', index === items.length - 1)
                + nbhRepeaterActionButton('duplicate', index, 'Дублировать', false)
                + nbhRepeaterActionButton('remove', index, 'Удалить', false)
                + '</div>'
                + '</div>'
                + '<div class="nbh-grid-2">'
                + nbhField('Eyebrow', '<input type="text" data-item-field="eyebrow" data-item-index="' + index + '" value="' + nbhEscapeAttr(eyebrow) + '">')
                + nbhField('Meta label', '<input type="text" data-item-field="meta_label" data-item-index="' + index + '" value="' + nbhEscapeAttr(metaLabel) + '">')
                + nbhField('Дата', '<input type="text" data-item-field="date" data-item-index="' + index + '" value="' + nbhEscapeAttr(date) + '">')
                + nbhField('Record URL', '<input type="text" data-item-field="record_url" data-item-index="' + index + '" value="' + nbhEscapeAttr(recordUrl) + '">')
                + '</div>'
                + nbhField('Заголовок', '<input type="text" data-item-field="title" data-item-index="' + index + '" value="' + nbhEscapeAttr(title) + '">')
                + nbhField('Текст', '<textarea data-item-field="text" data-item-index="' + index + '">' + nbhEscapeHtml(text) + '</textarea>')
                + nbhField('Изображение', nbhRepeaterImageField(index, image))
                + '<div class="nbh-grid-2">'
                + nbhField('Alt изображения', '<input type="text" data-item-field="imageAlt" data-item-index="' + index + '" value="' + nbhEscapeAttr(imageAlt) + '">')
                + nbhField('Primary CTA текст', '<input type="text" data-item-field="primary_cta_label" data-item-index="' + index + '" value="' + nbhEscapeAttr(primaryCtaLabel) + '">')
                + nbhField('Primary CTA URL', '<input type="text" data-item-field="primary_cta_url" data-item-index="' + index + '" value="' + nbhEscapeAttr(primaryCtaUrl) + '">')
                + nbhField('Secondary CTA текст', '<input type="text" data-item-field="secondary_cta_label" data-item-index="' + index + '" value="' + nbhEscapeAttr(secondaryCtaLabel) + '">')
                + nbhField('Secondary CTA URL', '<input type="text" data-item-field="secondary_cta_url" data-item-index="' + index + '" value="' + nbhEscapeAttr(secondaryCtaUrl) + '">')
                + '</div>'
                + '</div>';
        }

        if (nbhIsCardCollectionBlock()) {
            var category = nbhCollectionItemValue(item, 'category');
            var categoryUrl = nbhCollectionItemValue(item, 'category_url');
            var title = nbhCollectionItemValue(item, 'title');
            var excerpt = nbhCollectionItemValue(item, 'excerpt');
            var linkLabel = nbhCollectionItemValue(item, 'link_label');
            var url = nbhCollectionItemValue(item, 'url');
            var image = nbhCollectionItemValue(item, 'image');
            var imageAlt = nbhCollectionItemValue(item, 'imageAlt');
            var date = nbhCollectionItemValue(item, 'date');
            var views = nbhCollectionItemValue(item, 'views');
            var comments = nbhCollectionItemValue(item, 'comments');
            var badge = nbhCollectionItemValue(item, 'badge');
            var price = nbhCollectionItemValue(item, 'price');
            var priceOld = nbhCollectionItemValue(item, 'priceOld');
            var availability = nbhCollectionItemValue(item, 'availability');
            var ctaLabel = nbhCollectionItemValue(item, 'cta_label');
            var ctaKind = nbhCollectionItemValue(item, 'cta_kind') || 'url';
            var ctaUrl = nbhCollectionItemValue(item, 'cta_url');
            var messengerType = nbhCollectionItemValue(item, 'messenger_type') || 'none';
            var tags = nbhCollectionItemValue(item, 'tags');
            var gallery = nbhCollectionItemValue(item, 'gallery');
            var currency = nbhCollectionItemValue(item, 'currency');
            var cardLabel = (kind === 'headline_feed' && index === 0) ? 'Главная статья' : 'Карточка ' + (index + 1);

            if (kind === 'catalog_browser') {
                return '<div class="nbh-note" style="background:#fff;border:1px solid #dbe4ef;">'
                    + '<div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-bottom:.75rem;">'
                    + '<strong>' + cardLabel + '</strong>'
                    + '<div style="display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.4rem;">'
                    + nbhRepeaterActionButton('move-up', index, 'Выше', index === 0)
                    + nbhRepeaterActionButton('move-down', index, 'Ниже', index === items.length - 1)
                    + nbhRepeaterActionButton('duplicate', index, 'Дублировать', false)
                    + nbhRepeaterActionButton('remove', index, 'Удалить', false)
                    + '</div>'
                    + '</div>'
                    + '<div class="nbh-grid-2">'
                    + nbhField('ID импорта', '<input type="text" data-item-field="id" data-item-index="' + index + '" value="' + nbhEscapeAttr(nbhCollectionItemValue(item, 'id')) + '" placeholder="sku-oak-desk-140">')
                    + nbhField('Категория', '<input type="text" data-item-field="category" data-item-index="' + index + '" value="' + nbhEscapeAttr(category) + '">')
                    + nbhField('Ссылка категории', '<input type="text" data-item-field="category_url" data-item-index="' + index + '" value="' + nbhEscapeAttr(categoryUrl) + '">')
                    + nbhField('Бейдж', '<input type="text" data-item-field="badge" data-item-index="' + index + '" value="' + nbhEscapeAttr(badge) + '">')
                    + nbhField('Наличие', nbhItemSelectOptions(nbhCatalogAvailabilityOptions(), availability).replace('__FIELD__', 'availability').replace('__INDEX__', String(index)))
                    + '</div>'
                    + nbhField('Заголовок', '<input type="text" data-item-field="title" data-item-index="' + index + '" value="' + nbhEscapeAttr(title) + '">')
                    + nbhField('Описание', '<textarea data-item-field="excerpt" data-item-index="' + index + '">' + nbhEscapeHtml(excerpt) + '</textarea>')
                    + nbhField('Изображение', nbhRepeaterImageField(index, image))
                    + '<div class="nbh-grid-2">'
                    + nbhField('Цена', '<input type="text" data-item-field="price" data-item-index="' + index + '" value="' + nbhEscapeAttr(price) + '">')
                    + nbhField('Старая цена', '<input type="text" data-item-field="priceOld" data-item-index="' + index + '" value="' + nbhEscapeAttr(priceOld) + '">')
                    + nbhField('Валюта', '<input type="text" data-item-field="currency" data-item-index="' + index + '" value="' + nbhEscapeAttr(currency) + '">')
                    + nbhField('Кнопка карточки', '<input type="text" data-item-field="cta_label" data-item-index="' + index + '" value="' + nbhEscapeAttr(ctaLabel) + '">')
                    + nbhField('Тип кнопки', nbhItemSelectOptions(nbhCatalogCtaKindOptions(), ctaKind).replace('__FIELD__', 'cta_kind').replace('__INDEX__', String(index)))
                    + nbhField('Ссылка кнопки', '<input type="text" data-item-field="cta_url" data-item-index="' + index + '" value="' + nbhEscapeAttr(ctaUrl) + '">')
                    + nbhField('Тип мессенджера', nbhItemSelectOptions(nbhCatalogMessengerOptions(), messengerType).replace('__FIELD__', 'messenger_type').replace('__INDEX__', String(index)))
                    + nbhField('Ссылка карточки', '<input type="text" data-item-field="url" data-item-index="' + index + '" value="' + nbhEscapeAttr(url) + '">')
                    + nbhField('Alt изображения', '<input type="text" data-item-field="imageAlt" data-item-index="' + index + '" value="' + nbhEscapeAttr(imageAlt) + '">')
                    + '</div>'
                    + nbhField('Теги через запятую', '<input type="text" data-item-field="tags" data-item-index="' + index + '" value="' + nbhEscapeAttr(tags) + '">')
                    + nbhField('Галерея JSON', '<textarea data-item-field="gallery" data-item-index="' + index + '" placeholder="[{&quot;src&quot;:&quot;/upload/...jpg&quot;,&quot;alt&quot;:&quot;Слайд&quot;,&quot;caption&quot;:&quot;Подпись&quot;}]">' + nbhEscapeHtml(gallery) + '</textarea>')
                    + '<div class="nbh-note" style="margin-top:.75rem;">Карточки можно переставлять, дублировать и настраивать отдельно для URL, телефона и мессенджеров. Для полноэкранного modal передайте JSON-массив слайдов; если поле пустое, блок использует cover image карточки.</div>'
                    + '</div>';
            }

            return '<div class="nbh-note" style="background:#fff;border:1px solid #dbe4ef;">'
                + '<div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-bottom:.75rem;">'
                + '<strong>' + cardLabel + '</strong>'
                + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="remove" data-item-index="' + index + '" style="padding:.32rem .7rem;font-size:.72rem;">Удалить</button>'
                + '</div>'
                + '<div class="nbh-grid-2">'
                + nbhField('Рубрика', '<input type="text" data-item-field="category" data-item-index="' + index + '" value="' + nbhEscapeAttr(category) + '">')
                + nbhField('Ссылка рубрики', '<input type="text" data-item-field="category_url" data-item-index="' + index + '" value="' + nbhEscapeAttr(categoryUrl) + '">')
                + nbhField('Дата', '<input type="text" data-item-field="date" data-item-index="' + index + '" value="' + nbhEscapeAttr(date) + '">')
                + '</div>'
                + nbhField('Заголовок', '<input type="text" data-item-field="title" data-item-index="' + index + '" value="' + nbhEscapeAttr(title) + '">')
                + nbhField('Анонс', '<textarea data-item-field="excerpt" data-item-index="' + index + '">' + nbhEscapeHtml(excerpt) + '</textarea>')
                + nbhField('CTA карточки', '<input type="text" data-item-field="link_label" data-item-index="' + index + '" value="' + nbhEscapeAttr(linkLabel) + '">')
                + nbhField('Изображение', nbhRepeaterImageField(index, image))
                + '<div class="nbh-grid-2">'
                + nbhField('Ссылка карточки', '<input type="text" data-item-field="url" data-item-index="' + index + '" value="' + nbhEscapeAttr(url) + '">')
                + nbhField('Alt изображения', '<input type="text" data-item-field="imageAlt" data-item-index="' + index + '" value="' + nbhEscapeAttr(imageAlt) + '">')
                + nbhField('Просмотры', '<input type="text" data-item-field="views" data-item-index="' + index + '" value="' + nbhEscapeAttr(views) + '">')
                + nbhField('Комментарии', '<input type="text" data-item-field="comments" data-item-index="' + index + '" value="' + nbhEscapeAttr(comments) + '">')
                + '</div>'
                + '</div>';
        }

        var question = nbhCollectionItemValue(item, 'title');
        var answer = nbhCollectionItemValue(item, 'text');
        return '<div class="nbh-note" style="background:#fff;border:1px solid #dbe4ef;">'
            + '<div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-bottom:.75rem;">'
            + '<strong>Вопрос ' + (index + 1) + '</strong>'
            + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="remove" data-item-index="' + index + '" style="padding:.32rem .7rem;font-size:.72rem;">Удалить</button>'
            + '</div>'
            + nbhField('Вопрос', '<input type="text" data-item-field="title" data-item-index="' + index + '" value="' + nbhEscapeAttr(question) + '">')
            + nbhField('Ответ', '<textarea data-item-field="text" data-item-index="' + index + '">' + nbhEscapeHtml(answer) + '</textarea>')
            + '</div>';
    }).join('');

    if (!cards) {
        cards = nbhIsSliderCollectionBlock()
            ? '<div class="nbh-note">Слайдер пока пуст. Добавьте первый слайд.</div>'
            : nbhIsCardCollectionBlock()
            ? '<div class="nbh-note">' + (kind === 'headline_feed' ? 'Секция пока пуста. Добавьте главную статью и продолжение ленты.' : (kind === 'swiss_grid' ? 'Swiss grid пока пуст. Добавьте первую карточку.' : 'Лента пока пустая. Добавьте первую карточку.')) + '</div>'
            : '<div class="nbh-note">Список FAQ пока пуст. Добавьте первый вопрос.</div>';
    }

    if (listSource.type === 'content_list') {
        cards = (nbhIsSliderCollectionBlock()
            ? '<div class="nbh-note">Ручные слайды ниже остаются резервным сценарием, если источник данных не вернёт записей. Один и тот же SSR pipeline будет использоваться и в preview, и на публичной странице.</div>'
            : nbhIsCardCollectionBlock()
            ? '<div class="nbh-note">' + (kind === 'headline_feed' ? 'Первый материал из content_list станет главной статьёй, а ручные карточки ниже останутся резервным сценарием, если данных не хватит.' : 'Ручные карточки ниже остаются резервной лентой, если источник данных не вернёт записей.') + '</div>'
            : '<div class="nbh-note">Ручные вопросы ниже остаются резервным списком, если источник данных не вернёт записей.</div>') + cards;
    }

    if (nbhIsSliderCollectionBlock()) {
        return '<div class="nbh-note">Cards Slider работает как full-width rail: header остаётся секционным, а ниже вы управляете ручным fallback-набором слайдов для режима manual и для content_list fallback.</div>'
            + '<div class="nbh-grid-2">'
            + nbhField('Показывать navigation', nbhSelect('runtime.visibility.navigation', nbhYesNoOptions(), '1'))
            + nbhField('Показывать pagination', nbhSelect('runtime.visibility.pagination', nbhYesNoOptions(), '1'))
            + nbhField('Показывать progress', nbhSelect('runtime.visibility.progress', nbhYesNoOptions(), '0'))
            + nbhField('Показывать media', nbhSelect('runtime.visibility.slideMedia', nbhYesNoOptions(), '1'))
            + nbhField('Показывать eyebrow', nbhSelect('runtime.visibility.slideEyebrow', nbhYesNoOptions(), '1'))
            + nbhField('Показывать текст', nbhSelect('runtime.visibility.slideText', nbhYesNoOptions(), '1'))
            + nbhField('Показывать meta', nbhSelect('runtime.visibility.slideMeta', nbhYesNoOptions(), '1'))
            + nbhField('Показывать primary CTA', nbhSelect('runtime.visibility.slidePrimaryAction', nbhYesNoOptions(), '1'))
            + nbhField('Показывать secondary CTA', nbhSelect('runtime.visibility.slideSecondaryAction', nbhYesNoOptions(), '1'))
            + '</div>'
            + cards
            + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="add" style="align-self:flex-start;"><i class="fa fa-plus"></i> Добавить слайд</button>';
    }

    if (nbhIsCardCollectionBlock()) {
        return (kind === 'headline_feed' ? '<div class="nbh-note">Первая карточка всегда становится главной статьёй. Остальные карточки продолжают ленту и перестраиваются по выбранному visual preset.</div>' : '')
            + (kind === 'catalog_browser'
                ? '<div class="nbh-note">Каталог можно держать целиком открытым, резать по кнопке Показать ещё или по клиентской пагинации. Поиск тоже можно сузить до нужных полей карточки, а порядок карточек теперь управляется прямо здесь.<div style="margin-top:.7rem;display:flex;flex-wrap:wrap;gap:.55rem;"><button type="button" class="nbh-btn nbh-btn--catalog" data-catalog-table-action="open"><i class="fa fa-table"></i> Открыть табличный режим</button><span style="align-self:center;color:#475569;">Вставьте строки из Excel или Google Sheets и проверьте preview перед импортом.</span></div></div>'
                : '')
            + '<div class="nbh-grid-2">'
            + nbhField('Показывать изображение', nbhSelect('runtime.visibility.image', nbhYesNoOptions(), '1'))
            + nbhField('Показывать рубрику', nbhSelect('runtime.visibility.category', nbhYesNoOptions(), '1'))
            + nbhField('Показывать анонс', nbhSelect('runtime.visibility.excerpt', nbhYesNoOptions(), '1'))
            + (kind === 'catalog_browser'
                ? nbhField('Формат кадра карточек', nbhSelect('design.entities.media.aspectRatio', nbhCatalogAspectRatioOptions(), '16:10'))
                    + nbhField('Вписывание изображения', nbhSelect('design.entities.media.objectFit', nbhCatalogObjectFitOptions(), 'cover'))
                    + nbhField('Режим длинного каталога', nbhSelect('runtime.catalog.collectionMode', [
                        { value: 'all', label: 'Все карточки сразу' },
                        { value: 'load_more', label: 'Кнопка Показать ещё' },
                        { value: 'pagination', label: 'Пагинация' }
                    ], 'all'))
                    + nbhField('Карточек на шаг/страницу', nbhInput('runtime.catalog.itemsPerPage', { inputType: 'number', type: 'number', fallback: 6 }))
                    + nbhField('Показывать счётчик результатов', nbhSelect('runtime.catalog.showResultsCount', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать поиск', nbhSelect('runtime.visibility.search', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать фильтр категорий', nbhSelect('runtime.visibility.categoryFilter', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать фильтр цены', nbhSelect('runtime.visibility.priceFilter', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать сортировку', nbhSelect('runtime.visibility.sort', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать активные фильтры', nbhSelect('runtime.visibility.activeFilters', nbhYesNoOptions(), '1'))
                    + nbhField('Поиск по заголовку', nbhSelect('runtime.catalog.searchFields.title', nbhYesNoOptions(), '1'))
                    + nbhField('Поиск по описанию', nbhSelect('runtime.catalog.searchFields.excerpt', nbhYesNoOptions(), '1'))
                    + nbhField('Поиск по категории', nbhSelect('runtime.catalog.searchFields.category', nbhYesNoOptions(), '1'))
                    + nbhField('Поиск по badge', nbhSelect('runtime.catalog.searchFields.badge', nbhYesNoOptions(), '1'))
                    + nbhField('Поиск по тегам', nbhSelect('runtime.catalog.searchFields.tags', nbhYesNoOptions(), '1'))
                    + nbhField('Поиск по цене', nbhSelect('runtime.catalog.searchFields.price', nbhYesNoOptions(), '0'))
                    + nbhField('Поиск по наличию', nbhSelect('runtime.catalog.searchFields.availability', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать badge', nbhSelect('runtime.visibility.badge', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать цену', nbhSelect('runtime.visibility.price', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать старую цену', nbhSelect('runtime.visibility.oldPrice', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать CTA', nbhSelect('runtime.visibility.cta', nbhYesNoOptions(), '1'))
                : nbhField('Показывать дату', nbhSelect('runtime.visibility.date', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать просмотры', nbhSelect('runtime.visibility.views', nbhYesNoOptions(), '1'))
                    + nbhField('Показывать комментарии', nbhSelect('runtime.visibility.comments', nbhYesNoOptions(), '1')))
            + '</div>'
            + cards
            + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="add" style="align-self:flex-start;"><i class="fa fa-plus"></i> Добавить карточку</button>';
    }

    return nbhField('Первый вопрос открыт', nbhSelect('runtime.disclosure.openFirst', [
        { value: '1', label: 'Да' },
        { value: '0', label: 'Нет' }
    ], '1'))
        + cards
        + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="add" style="align-self:flex-start;"><i class="fa fa-plus"></i> Добавить вопрос</button>';
}