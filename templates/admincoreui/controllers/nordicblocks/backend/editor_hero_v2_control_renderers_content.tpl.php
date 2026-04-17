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
            return '<div class="nbh-note">Для сущности ' + panel.entityScope + ' пока не подключена отдельная контентная панель.</div>';
        },
        'button-content-panel': function() {
            return '<div class="nbh-grid-2">'
                + nbhField('Текст основной кнопки', nbhInput('content.primaryButton.label'))
                + nbhField('Ссылка основной кнопки', nbhInput('content.primaryButton.url'))
                + nbhField('Текст вторичной кнопки', nbhInput('content.secondaryButton.label'))
                + nbhField('Ссылка вторичной кнопки', nbhInput('content.secondaryButton.url'))
                + '</div>';
        },
        'media-content-panel': function() {
            return nbhField('Путь к изображению', nbhInput('content.media.image', { picker: 'image' }))
                + nbhField('Alt-текст', nbhInput('content.media.alt'));
        }
    };
}