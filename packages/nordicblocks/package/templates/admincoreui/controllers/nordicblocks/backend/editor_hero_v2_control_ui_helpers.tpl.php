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
        var question = nbhFaqItemValue(item, 'title');
        var answer = nbhFaqItemValue(item, 'text');
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
        cards = '<div class="nbh-note">Список FAQ пока пуст. Добавьте первый вопрос.</div>';
    }

    if (listSource.type === 'content_list') {
        cards = '<div class="nbh-note">Ручные вопросы ниже остаются резервным списком, если источник данных не вернёт записей.</div>' + cards;
    }

    return nbhField('Первый вопрос открыт', nbhSelect('runtime.disclosure.openFirst', [
        { value: '1', label: 'Да' },
        { value: '0', label: 'Нет' }
    ], '1'))
        + cards
        + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="add" style="align-self:flex-start;"><i class="fa fa-plus"></i> Добавить вопрос</button>';
}