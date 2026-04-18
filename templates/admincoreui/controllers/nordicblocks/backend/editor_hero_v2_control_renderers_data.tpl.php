function nbhBuildDataControlRenderers() {
    return {
        'data-source-panel': function() {
            if (nbhUsesCollectionData()) {
                var listOptions = nbhDataOptions();
                var listSource = nbhListSource();
                var listCtypeOptions = [{ value: '', label: 'Выберите тип контента' }].concat(listOptions.contentTypes.map(function(ctype) {
                    return { value: ctype.name, label: ctype.title };
                }));
                var listBody = nbhField('Источник данных', nbhSelect('data.listSource.type', listOptions.listModes.length ? listOptions.listModes : [
                    { value: 'manual', label: 'Ручной список' },
                    { value: 'content_list', label: 'Список записей InstantCMS' }
                ], 'manual'));

                if (listSource.type !== 'content_list') {
                    return listBody + '<div class="nbh-note">Сейчас блок использует ручной список из вкладки Контент. Переключите источник на список записей InstantCMS, если коллекция должна собираться автоматически.</div>';
                }

                if (!listOptions.contentTypes.length) {
                    return listBody + '<div class="nbh-note">В системе не найдено включённых типов контента, поэтому режим списка записей пока недоступен.</div>';
                }

                listBody += nbhField('Тип контента', nbhSelect('data.listSource.ctype', listCtypeOptions, ''));
                listBody += '<div class="nbh-grid-2">'
                    + nbhField('Лимит записей', nbhInput('data.listSource.limit', { inputType: 'number', type: 'number', fallback: 3 }))
                    + nbhField('Сортировка', nbhSelect('data.listSource.sort', listOptions.sortOptions.length ? listOptions.sortOptions : [{ value: 'date_pub_desc', label: 'Сначала новые' }], 'date_pub_desc'))
                    + '</div>';

                if (!listSource.ctype) {
                    return listBody + '<div class="nbh-note">Сначала выберите тип контента. После этого на соседней панели появятся совместимые поля для привязки элементов коллекции.</div>';
                }

                return listBody + '<div class="nbh-note">Ручные элементы из вкладки Контент остаются резервным слоем. Предпросмотр и публичный вывод продолжают использовать один и тот же SSR-конвейер данных.</div>';
            }

            var options = nbhDataOptions();
            var source = nbhSingleSource();
            nbhSingleBindings();

            var body = nbhField('Источник данных', nbhSelect('data.source.type', options.sourceModes.length ? options.sourceModes : [
                { value: 'manual', label: 'Ручной контент' },
                { value: 'content_item', label: 'Одна запись InstantCMS' }
            ], 'manual'));

            if (source.type !== 'content_item') {
                return body + '<div class="nbh-note">Сейчас блок использует ручной контент из вкладки Контент. Переключите источник на запись InstantCMS, если заголовок, подзаголовок, медиа и мета должны подтягиваться из системы.</div>';
            }

            if (!options.contentTypes.length) {
                return body + '<div class="nbh-note">В системе не найдено доступных типов контента, поэтому режим одной записи пока недоступен.</div>';
            }

            var ctypeOptions = [{ value: '', label: 'Выберите тип контента' }].concat(options.contentTypes.map(function(ctype) {
                return { value: ctype.name, label: ctype.title };
            }));

            body += nbhField('Тип контента', nbhSelect('data.source.ctype', ctypeOptions, ''));
            body += nbhField('Режим выборки', nbhSelect('data.source.resolver.mode', options.itemResolverModes.length ? options.itemResolverModes : [
                { value: 'current', label: 'Текущая запись страницы' },
                { value: 'by_id', label: 'Запись по ID' },
                { value: 'latest', label: 'Последняя запись' }
            ], 'current'));

            if (source.resolver.mode === 'by_id') {
                body += nbhField('ID записи', nbhInput('data.source.resolver.id', { inputType: 'number', type: 'number', fallback: 0 }));
            }

            if (source.resolver.mode === 'current') {
                body += '<div class="nbh-note">Режим текущей записи работает на реальной странице материала. В админском предпросмотре без контекста записи блок останется на ручных резервных значениях.</div>';
            }

            if (!source.ctype) {
                return body + '<div class="nbh-note">Сначала выберите тип контента, после этого появятся совместимые поля для привязки слотов.</div>';
            }

            var fields = options.fieldsByType[source.ctype] || [];
            if (!fields.length) {
                return body + '<div class="nbh-note">У выбранного типа контента не найдено доступных полей для привязки. Выберите другой тип контента или оставьте блок в ручном режиме.</div>';
            }

            var textOptions = nbhFieldOptionsByKinds(fields, ['text'], 'Оставить ручное значение');
            var metaTextOptions = nbhFieldOptionsByKinds(fields, ['text'], 'Скрыть поле');
            var imageOptions = nbhFieldOptionsByKinds(fields, ['image'], 'Оставить ручное изображение');
            var dateOptions = nbhFieldOptionsByKinds(fields, ['date', 'text'], 'Скрыть дату');
            var numberOptions = nbhFieldOptionsByKinds(fields, ['number', 'text'], 'Скрыть метрику');
            var urlOptions = nbhFieldOptionsByKinds(fields, ['url', 'text'], 'Оставить ручной URL');

            body += '<div class="nbh-grid-2">'
                + nbhField('Надзаголовок', nbhSelect('data.bindings.eyebrow.field', textOptions, ''))
                + nbhField('Заголовок', nbhSelect('data.bindings.title.field', textOptions, ''))
                + nbhField('Подзаголовок', nbhSelect('data.bindings.subtitle.field', textOptions, ''))
                + nbhField('Изображение', nbhSelect('data.bindings.image.field', imageOptions, ''))
                + nbhField('Alt изображения', nbhSelect('data.bindings.imageAlt.field', textOptions, ''))
                + '</div>';

            body += '<div class="nbh-grid-2">'
                + nbhField('Категория', nbhSelect('data.bindings.category.field', metaTextOptions, ''))
                + nbhField('Автор', nbhSelect('data.bindings.author.field', metaTextOptions, ''))
                + nbhField('Дата', nbhSelect('data.bindings.date.field', dateOptions, ''))
                + nbhField('Просмотры', nbhSelect('data.bindings.views.field', numberOptions, ''))
                + nbhField('Комментарии', nbhSelect('data.bindings.comments.field', numberOptions, ''))
                + nbhField('Ссылка основной кнопки', nbhSelect('data.bindings.primaryButtonUrl.field', urlOptions, ''))
                + '</div>';

            body += '<div class="nbh-note">Пустой выбор скрывает категорию, автора, дату и метрики. Для надзаголовка, заголовка, подзаголовка и изображения ручные значения остаются резервным слоем. Это же позволяет подключать и кастомные текстовые поля, если они есть у типа контента.</div>';

            return body;
        },
        'data-collection-panel': function() {
            var options = nbhDataOptions();
            var listSource = nbhListSource();
            var fields = listSource.ctype && options.fieldsByType[listSource.ctype] ? options.fieldsByType[listSource.ctype] : [];

            if (listSource.type !== 'content_list') {
                return '<div class="nbh-note">Коллекция сейчас использует ручные элементы из вкладки Контент. Когда источник переключён на список записей, здесь появляются привязки полей элементов.</div>';
            }

            if (!listSource.ctype) {
                return '<div class="nbh-note">Сначала выберите тип контента на панели источника данных, после этого появятся совместимые поля для привязки элементов.</div>';
            }

            if (!fields.length) {
                return '<div class="nbh-note">У выбранного типа контента не найдено текстовых полей для привязки элементов. Можно использовать системный заголовок или выбрать другой тип контента.</div>';
            }

            var fieldOptions = [{ value: '', label: 'Не выбрано' }].concat(fields.map(function(field) {
                return { value: field.name, label: field.label + ' [' + field.type + ']' };
            }));
            var body = '<div class="nbh-grid-2">';

            if (nbhHasEntity('itemTitle')) {
                body += nbhField('Заголовок элемента', nbhSelect('data.listSource.map.title', fieldOptions, 'title'));
            }
            if (nbhHasEntity('itemText')) {
                body += nbhField('Текст элемента', nbhSelect('data.listSource.map.text', fieldOptions, ''));
            }

            body += '</div>';

            body += nbhField('Если записей нет', nbhSelect('data.listSource.emptyBehavior', [
                { value: 'fallback', label: 'Показать ручной резерв' },
                { value: 'empty', label: 'Показать пустой список' }
            ], 'fallback'));
            body += '<div class="nbh-note">Ручные элементы из вкладки Контент остаются резервным списком. Предпросмотр и публичный вывод уже используют один и тот же SSR-конвейер данных.</div>';

            return body;
        }
    };
}