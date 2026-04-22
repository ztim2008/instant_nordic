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
            var urlOptions = nbhFieldOptionsByKinds(fields, ['url', 'text'], 'Оставить ручную ссылку');

            var slotBindings = [];
            if (nbhHasEntity('eyebrow')) {
                slotBindings.push(nbhField('Надзаголовок', nbhSelect('data.bindings.eyebrow.field', textOptions, '')));
            }
            if (nbhHasEntity('title')) {
                slotBindings.push(nbhField('Заголовок', nbhSelect('data.bindings.title.field', textOptions, '')));
            }
            if (nbhHasEntity('subtitle')) {
                slotBindings.push(nbhField('Подзаголовок', nbhSelect('data.bindings.subtitle.field', textOptions, '')));
            }
            if (nbhHasEntity('body')) {
                slotBindings.push(nbhField('Основной текст', nbhSelect('data.bindings.body.field', textOptions, '')));
            }
            if (nbhHasEntity('media')) {
                slotBindings.push(nbhField('Изображение', nbhSelect('data.bindings.image.field', imageOptions, '')));
                slotBindings.push(nbhField('Alt изображения', nbhSelect('data.bindings.imageAlt.field', textOptions, '')));
            }
            if (slotBindings.length) {
                body += '<div class="nbh-grid-2">' + slotBindings.join('') + '</div>';
            }

            var metaBindings = [];
            if (nbhHasEntity('meta')) {
                metaBindings.push(nbhField('Категория', nbhSelect('data.bindings.category.field', metaTextOptions, '')));
                metaBindings.push(nbhField('Автор', nbhSelect('data.bindings.author.field', metaTextOptions, '')));
                metaBindings.push(nbhField('Дата', nbhSelect('data.bindings.date.field', dateOptions, '')));
                metaBindings.push(nbhField('Просмотры', nbhSelect('data.bindings.views.field', numberOptions, '')));
                metaBindings.push(nbhField('Комментарии', nbhSelect('data.bindings.comments.field', numberOptions, '')));
            }
            if (nbhHasEntity('primaryButton')) {
                metaBindings.push(nbhField('Ссылка основной кнопки', nbhSelect('data.bindings.primaryButtonUrl.field', urlOptions, '')));
            }
            if (nbhHasEntity('secondaryButton')) {
                metaBindings.push(nbhField('Ссылка вторичной кнопки', nbhSelect('data.bindings.secondaryButtonUrl.field', urlOptions, '')));
            }
            if (nbhHasEntity('tertiaryButton')) {
                metaBindings.push(nbhField('Ссылка третьей кнопки', nbhSelect('data.bindings.tertiaryButtonUrl.field', urlOptions, '')));
            }
            if (metaBindings.length) {
                body += '<div class="nbh-grid-2">' + metaBindings.join('') + '</div>';
            }

            body += '<div class="nbh-note">Пустой выбор скрывает категорию, автора, дату и метрики. Для надзаголовка, заголовка, подзаголовка и изображения ручные значения остаются резервным слоем. Это же позволяет подключать и кастомные текстовые поля, если они есть у типа контента.</div>';

            return body;
        },
        'slider-data-source-panel': function() {
            var listOptions = nbhDataOptions();
            var listSource = nbhListSource();
            var ctypeOptions = [{ value: '', label: 'Выберите тип контента' }].concat(listOptions.contentTypes.map(function(ctype) {
                return { value: ctype.name, label: ctype.title };
            }));
            var body = nbhField('Источник данных', nbhSelect('data.listSource.type', listOptions.listModes.length ? listOptions.listModes : [
                { value: 'manual', label: 'Ручной список' },
                { value: 'content_list', label: 'Список записей InstantCMS' }
            ], 'manual'));

            if (listSource.type !== 'content_list') {
                return body + '<div class="nbh-note">Сейчас slider использует ручные fallback-слайды из вкладки Контент. Переключите источник на список записей InstantCMS, если rail должен наполняться автоматически.</div>';
            }

            if (!listOptions.contentTypes.length) {
                return body + '<div class="nbh-note">В системе не найдено включённых типов контента, поэтому режим списка записей пока недоступен.</div>';
            }

            body += nbhField('Тип контента', nbhSelect('data.listSource.ctype', ctypeOptions, ''));
            body += '<div class="nbh-grid-2">'
                + nbhField('Лимит записей', nbhInput('data.listSource.limit', { inputType: 'number', type: 'number', fallback: 6 }))
                + nbhField('Сортировка', nbhSelect('data.listSource.sort', listOptions.sortOptions.length ? listOptions.sortOptions : [{ value: 'date_pub_desc', label: 'Сначала новые' }], 'date_pub_desc'))
                + '</div>';

            if (!listSource.ctype) {
                return body + '<div class="nbh-note">Сначала выберите тип контента. После этого на соседней панели появятся mapping controls для полей слайда.</div>';
            }

            return body + '<div class="nbh-note">Ручные слайды из вкладки Контент остаются fallback-слоем. Preview и публичный runtime продолжают идти через один SSR pipeline.</div>';
        },
        'slider-data-query-panel': function() {
            var options = nbhDataOptions();
            var listSource = nbhListSource();
            var fields = listSource.ctype && options.fieldsByType[listSource.ctype] ? options.fieldsByType[listSource.ctype] : [];

            if (listSource.type !== 'content_list') {
                return '<div class="nbh-note">Поля слайда маппятся только в режиме content_list. В ручном режиме slider читает значения прямо из fallback-слайдов на вкладке Контент.</div>';
            }

            if (!listSource.ctype) {
                return '<div class="nbh-note">Сначала выберите тип контента на панели источника данных.</div>';
            }

            if (!fields.length) {
                return '<div class="nbh-note">У выбранного типа контента не найдено совместимых полей для slider mapping.</div>';
            }

            var textOptions = nbhFieldOptionsByKinds(fields, ['text'], 'Не выбрано');
            var imageOptions = nbhFieldOptionsByKinds(fields, ['image'], 'Не выбрано');
            var dateOptions = nbhFieldOptionsByKinds(fields, ['date', 'text'], 'Не выбрано');
            var urlOptions = nbhFieldOptionsByKinds(fields, ['url', 'text'], 'Не выбрано');
            var body = '<div class="nbh-grid-2">';

            if (nbhHasEntity('slideEyebrow')) {
                body += nbhField('Eyebrow', nbhSelect('data.listSource.map.eyebrow', textOptions, 'category.title'));
            }
            if (nbhHasEntity('slideTitle')) {
                body += nbhField('Заголовок слайда', nbhSelect('data.listSource.map.title', textOptions, 'title'));
            }
            if (nbhHasEntity('slideText')) {
                body += nbhField('Текст слайда', nbhSelect('data.listSource.map.text', textOptions, 'teaser'));
            }
            if (nbhHasEntity('slideMedia')) {
                body += nbhField('Изображение', nbhSelect('data.listSource.map.image', imageOptions, 'record_image_url'));
                body += nbhField('Alt изображения', nbhSelect('data.listSource.map.imageAlt', textOptions, 'title'));
            }
            if (nbhHasEntity('slideMeta')) {
                body += nbhField('Meta label', nbhSelect('data.listSource.map.metaLabel', textOptions, 'category.title'));
                body += nbhField('Дата', nbhSelect('data.listSource.map.date', dateOptions, 'date_pub'));
            }
            if (nbhHasEntity('slidePrimaryAction')) {
                body += nbhField('Primary CTA текст', nbhSelect('data.listSource.map.primaryCtaLabel', textOptions, 'title'));
                body += nbhField('Primary CTA URL', nbhSelect('data.listSource.map.primaryCtaUrl', urlOptions, 'record_url'));
            }
            if (nbhHasEntity('slideSecondaryAction')) {
                body += nbhField('Secondary CTA текст', nbhSelect('data.listSource.map.secondaryCtaLabel', textOptions, ''));
                body += nbhField('Secondary CTA URL', nbhSelect('data.listSource.map.secondaryCtaUrl', urlOptions, ''));
            }
            body += nbhField('Record URL', nbhSelect('data.listSource.map.recordUrl', urlOptions, 'record_url'));
            body += '</div>';
            body += nbhField('Если записей нет', nbhSelect('data.listSource.emptyBehavior', [
                { value: 'fallback', label: 'Показать ручной fallback rail' },
                { value: 'empty', label: 'Показать пустой rail' }
            ], 'fallback'));
            body += '<div class="nbh-note">Mappings задают, как запись InstantCMS превращается в полноценный slide contract: eyebrow, title, text, media, meta и CTA.</div>';
            return body;
        },
        'slider-data-visibility-panel': function() {
            return '<div class="nbh-grid-2">'
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
                + '<div class="nbh-note">Visibility controls работают поверх manual и content_list одинаково, чтобы slider не расходился между preview и публичным runtime.</div>';
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

            if (nbhIsCardCollectionBlock()) {
                body += nbhField('Заголовок карточки', nbhSelect('data.listSource.map.title', fieldOptions, 'title'))
                    + nbhField('Анонс карточки', nbhSelect('data.listSource.map.excerpt', fieldOptions, 'teaser'))
                    + nbhField('Изображение карточки', nbhSelect('data.listSource.map.image', fieldOptions, 'photo'))
                    + nbhField('Alt изображения', nbhSelect('data.listSource.map.imageAlt', fieldOptions, 'title'))
                    + nbhField('Рубрика', nbhSelect('data.listSource.map.category', fieldOptions, 'category.title'))
                    + nbhField('Ссылка рубрики', nbhSelect('data.listSource.map.categoryUrl', fieldOptions, 'category.url'))
                    + (nbhCollectionBlockKind() === 'catalog_browser'
                        ? nbhField('Цена', nbhSelect('data.listSource.map.price', fieldOptions, 'price'))
                            + nbhField('Старая цена', nbhSelect('data.listSource.map.priceOld', fieldOptions, 'price_old'))
                            + nbhField('Валюта', nbhSelect('data.listSource.map.currency', fieldOptions, 'currency'))
                            + nbhField('Бейдж', nbhSelect('data.listSource.map.badge', fieldOptions, 'badge'))
                            + nbhField('Теги', nbhSelect('data.listSource.map.tags', fieldOptions, 'tags'))
                            + nbhField('Наличие', nbhSelect('data.listSource.map.availability', fieldOptions, 'availability'))
                            + nbhField('Текст кнопки', nbhSelect('data.listSource.map.ctaLabel', fieldOptions, 'cta_label'))
                            + nbhField('Ссылка кнопки', nbhSelect('data.listSource.map.ctaUrl', fieldOptions, 'cta_url'))
                            + nbhField('Галерея', nbhSelect('data.listSource.map.gallery', fieldOptions, 'gallery'))
                        : nbhField('Дата', nbhSelect('data.listSource.map.date', fieldOptions, 'date_pub'))
                            + nbhField('Просмотры', nbhSelect('data.listSource.map.views', fieldOptions, 'hits_count'))
                            + nbhField('Комментарии', nbhSelect('data.listSource.map.comments', fieldOptions, 'comments_count')))
                    + nbhField('Ссылка карточки', nbhSelect('data.listSource.map.url', fieldOptions, 'record_url'));
            } else {
                if (nbhHasEntity('itemTitle')) {
                    body += nbhField('Заголовок элемента', nbhSelect('data.listSource.map.title', fieldOptions, 'title'));
                }
                if (nbhHasEntity('itemText')) {
                    body += nbhField('Текст элемента', nbhSelect('data.listSource.map.text', fieldOptions, ''));
                }
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