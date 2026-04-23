function nbhBuildContentControlRenderers() {
    return {
        'text-content-panel': function(panel) {
            if (panel.entityScope === 'eyebrow') {
                return nbhField('Текст', nbhInput('content.eyebrow'));
            }
            if (panel.entityScope === 'title') {
                return nbhField('Отображение', nbhSelect('design.entities.title.visible', nbhYesNoOptions(), '1'))
                    + nbhField('Текст', nbhTextarea('content.title', ''));
            }
            if (panel.entityScope === 'subtitle') {
                return nbhField('Отображение', nbhSelect('design.entities.subtitle.visible', nbhYesNoOptions(), '1'))
                    + nbhField('Текст', nbhTextarea('content.subtitle', ''));
            }
            if (panel.entityScope === 'body') {
                return nbhField('Текст', nbhTextarea('content.body', ''));
            }
            return '<div class="nbh-note">Для сущности ' + panel.entityScope + ' пока не подключена отдельная контентная панель.</div>';
        },
        'button-content-panel': function() {
            if (nbhIsCardCollectionBlock()) {
                return '<div class="nbh-grid-2">'
                    + nbhField('Показывать ссылку', nbhSelect('runtime.visibility.moreLink', nbhYesNoOptions(), '1'))
                    + nbhField('Текст ссылки', nbhInput('content.primaryButton.label'))
                    + nbhField('Ссылка кнопки', nbhInput('content.primaryButton.url'))
                    + '</div>';
            }

            return '<div class="nbh-grid-2">'
                + nbhField('Текст основной кнопки', nbhInput('content.primaryButton.label'))
                + nbhField('Ссылка основной кнопки', nbhInput('content.primaryButton.url'))
                + nbhField('Текст вторичной кнопки', nbhInput('content.secondaryButton.label'))
                + nbhField('Ссылка вторичной кнопки', nbhInput('content.secondaryButton.url'))
                + nbhField('Текст третьей кнопки', nbhInput('content.tertiaryButton.label'))
                + nbhField('Ссылка третьей кнопки', nbhInput('content.tertiaryButton.url'))
                + '</div>';
        },
        'media-content-panel': function() {
            var body = nbhField('Путь к изображению', nbhInput('content.media.image', { picker: 'image' }))
                + nbhField('Alt-текст', nbhInput('content.media.alt'));

            if (nbhIsCardCollectionBlock()) {
                body += '<div class="nbh-grid-2">'
                    + nbhField('Формат кадра', nbhSelect('design.entities.media.aspectRatio', nbhCatalogAspectRatioOptions(), '16:10'))
                    + nbhField('Вписывание', nbhSelect('design.entities.media.objectFit', nbhCatalogObjectFitOptions(), 'cover'))
                    + '</div>';
            }

            return body;
        }
    };
}