function nbhRenderTabs() {
    var tabs = (nbhState.inspector && nbhState.inspector.tabs ? nbhState.inspector.tabs : []).slice().sort(function(a, b) { return (a.order || 0) - (b.order || 0); });
    document.getElementById('nbhTabs').innerHTML = tabs.map(function(tab) {
        return '<button type="button" class="nbh-tab' + (tab.key === nbhState.activeTab ? ' is-active' : '') + '" data-tab="' + tab.key + '">' + tab.label + '</button>';
    }).join('');
}

function nbhField(label, controlHtml) {
    return '<div class="nbh-field"><label>' + label + '</label>' + controlHtml + '</div>';
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
        || path.indexOf('design.section.background.') === 0;
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

function nbhRepeaterEditor() {
    var items = nbhRepeaterItems();
    var listSource = nbhListSource();
    var cards = items.map(function(item, index) {
        if (nbhCollectionBlockKind() === 'content_feed') {
            var category = nbhCollectionItemValue(item, 'category');
            var title = nbhCollectionItemValue(item, 'title');
            var excerpt = nbhCollectionItemValue(item, 'excerpt');
            var url = nbhCollectionItemValue(item, 'url');
            var image = nbhCollectionItemValue(item, 'image');
            var imageAlt = nbhCollectionItemValue(item, 'imageAlt');
            var date = nbhCollectionItemValue(item, 'date');
            var views = nbhCollectionItemValue(item, 'views');
            var comments = nbhCollectionItemValue(item, 'comments');

            return '<div class="nbh-note" style="background:#fff;border:1px solid #dbe4ef;">'
                + '<div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-bottom:.75rem;">'
                + '<strong>Карточка ' + (index + 1) + '</strong>'
                + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="remove" data-item-index="' + index + '" style="padding:.32rem .7rem;font-size:.72rem;">Удалить</button>'
                + '</div>'
                + '<div class="nbh-grid-2">'
                + nbhField('Рубрика', '<input type="text" data-item-field="category" data-item-index="' + index + '" value="' + nbhEscapeAttr(category) + '">')
                + nbhField('Дата', '<input type="text" data-item-field="date" data-item-index="' + index + '" value="' + nbhEscapeAttr(date) + '">')
                + '</div>'
                + nbhField('Заголовок', '<input type="text" data-item-field="title" data-item-index="' + index + '" value="' + nbhEscapeAttr(title) + '">')
                + nbhField('Анонс', '<textarea data-item-field="excerpt" data-item-index="' + index + '">' + nbhEscapeHtml(excerpt) + '</textarea>')
                + '<div class="nbh-grid-2">'
                + nbhField('URL', '<input type="text" data-item-field="url" data-item-index="' + index + '" value="' + nbhEscapeAttr(url) + '">')
                + nbhField('Изображение', '<input type="text" data-item-field="image" data-item-index="' + index + '" value="' + nbhEscapeAttr(image) + '">')
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
        cards = nbhCollectionBlockKind() === 'content_feed'
            ? '<div class="nbh-note">Лента пока пустая. Добавьте первую карточку.</div>'
            : '<div class="nbh-note">Список FAQ пока пуст. Добавьте первый вопрос.</div>';
    }

    if (listSource.type === 'content_list') {
        cards = (nbhCollectionBlockKind() === 'content_feed'
            ? '<div class="nbh-note">Ручные карточки ниже остаются резервной лентой, если источник данных не вернёт записей.</div>'
            : '<div class="nbh-note">Ручные вопросы ниже остаются резервным списком, если источник данных не вернёт записей.</div>') + cards;
    }

    if (nbhCollectionBlockKind() === 'content_feed') {
        return '<div class="nbh-grid-2">'
            + nbhField('Показывать изображение', nbhSelect('runtime.visibility.image', nbhYesNoOptions(), '1'))
            + nbhField('Показывать рубрику', nbhSelect('runtime.visibility.category', nbhYesNoOptions(), '1'))
            + nbhField('Показывать анонс', nbhSelect('runtime.visibility.excerpt', nbhYesNoOptions(), '1'))
            + nbhField('Показывать дату', nbhSelect('runtime.visibility.date', nbhYesNoOptions(), '1'))
            + nbhField('Показывать просмотры', nbhSelect('runtime.visibility.views', nbhYesNoOptions(), '1'))
            + nbhField('Показывать комментарии', nbhSelect('runtime.visibility.comments', nbhYesNoOptions(), '1'))
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