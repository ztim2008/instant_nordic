<?php

class NordicblocksInspectorDefinitionRegistry {

    public static function getTabs() {
        return [
            ['key' => 'content', 'label' => 'Контент', 'order' => 10],
            ['key' => 'design',  'label' => 'Дизайн',  'order' => 20],
            ['key' => 'layout',  'label' => 'Макет',   'order' => 30],
            ['key' => 'data',    'label' => 'Данные',  'order' => 40],
        ];
    }

    public static function getEntities() {
        return [
            'eyebrow' => [
                'key'         => 'eyebrow',
                'label'       => 'Надзаголовок',
                'kind'        => 'text',
                'level'       => 'block',
                'styleSlot'   => 'eyebrow',
                'dataSlot'    => 'badge',
                'contentPath' => 'content.eyebrow',
                'designPath'  => 'design.entities.eyebrow',
            ],
            'title' => [
                'key'         => 'title',
                'label'       => 'Заголовок',
                'kind'        => 'text',
                'level'       => 'block',
                'styleSlot'   => 'title',
                'dataSlot'    => 'title',
                'contentPath' => 'content.title',
                'designPath'  => 'design.entities.title',
            ],
            'subtitle' => [
                'key'         => 'subtitle',
                'label'       => 'Подзаголовок',
                'kind'        => 'text',
                'level'       => 'block',
                'styleSlot'   => 'subtitle',
                'dataSlot'    => 'subtitle',
                'contentPath' => 'content.subtitle',
                'designPath'  => 'design.entities.subtitle',
            ],
            'meta' => [
                'key'         => 'meta',
                'label'       => 'Мета',
                'kind'        => 'text',
                'level'       => 'block',
                'styleSlot'   => 'meta',
                'dataSlot'    => 'meta',
                'contentPath' => 'content.meta',
                'designPath'  => 'design.entities.meta',
            ],
            'body' => [
                'key'         => 'body',
                'label'       => 'Основной текст',
                'kind'        => 'text',
                'level'       => 'block',
                'styleSlot'   => 'body',
                'dataSlot'    => 'body',
                'contentPath' => 'content.body',
                'designPath'  => 'design.entities.body',
            ],
            'primaryButton' => [
                'key'         => 'primaryButton',
                'label'       => 'Основная кнопка',
                'kind'        => 'button',
                'level'       => 'block',
                'styleSlot'   => 'primaryButton',
                'dataSlot'    => 'primaryButton',
                'contentPath' => 'content.primaryButton',
                'designPath'  => 'design.entities.primaryButton',
            ],
            'secondaryButton' => [
                'key'         => 'secondaryButton',
                'label'       => 'Вторичная кнопка',
                'kind'        => 'button',
                'level'       => 'block',
                'styleSlot'   => 'secondaryButton',
                'dataSlot'    => 'secondaryButton',
                'contentPath' => 'content.secondaryButton',
                'designPath'  => 'design.entities.secondaryButton',
            ],
            'tertiaryButton' => [
                'key'         => 'tertiaryButton',
                'label'       => 'Третья кнопка',
                'kind'        => 'button',
                'level'       => 'block',
                'styleSlot'   => 'tertiaryButton',
                'dataSlot'    => 'tertiaryButton',
                'contentPath' => 'content.tertiaryButton',
                'designPath'  => 'design.entities.tertiaryButton',
            ],
            'media' => [
                'key'         => 'media',
                'label'       => 'Медиа',
                'kind'        => 'media',
                'level'       => 'block',
                'styleSlot'   => 'media',
                'dataSlot'    => 'image',
                'contentPath' => 'content.media',
                'designPath'  => 'design.entities.media',
            ],
            'mediaSurface' => [
                'key'         => 'mediaSurface',
                'label'       => 'Поверхность медиа',
                'kind'        => 'surface',
                'level'       => 'block',
                'styleSlot'   => 'mediaSurface',
                'dataSlot'    => null,
                'contentPath' => null,
                'designPath'  => 'design.entities.mediaSurface',
            ],
            'items' => [
                'key'         => 'items',
                'label'       => 'Элементы',
                'kind'        => 'repeater',
                'level'       => 'block',
                'styleSlot'   => 'items',
                'dataSlot'    => 'items',
                'contentPath' => 'content.items',
                'designPath'  => 'design.entities.items',
            ],
            'itemSurface' => [
                'key'         => 'itemSurface',
                'label'       => 'Поверхность элемента',
                'kind'        => 'surface',
                'level'       => 'item',
                'styleSlot'   => 'itemSurface',
                'dataSlot'    => null,
                'contentPath' => null,
                'designPath'  => 'design.entities.itemSurface',
            ],
            'itemTitle' => [
                'key'         => 'itemTitle',
                'label'       => 'Заголовок элемента',
                'kind'        => 'text',
                'level'       => 'item',
                'styleSlot'   => 'itemTitle',
                'dataSlot'    => 'items[].title',
                'contentPath' => 'content.items[].title',
                'designPath'  => 'design.entities.itemTitle',
            ],
            'itemText' => [
                'key'         => 'itemText',
                'label'       => 'Текст элемента',
                'kind'        => 'text',
                'level'       => 'item',
                'styleSlot'   => 'itemText',
                'dataSlot'    => 'items[].text',
                'contentPath' => 'content.items[].text',
                'designPath'  => 'design.entities.itemText',
            ],
        ];
    }

    public static function getEntityGroups() {
        return [
            'buttons' => [
                'key'      => 'buttons',
                'label'    => 'Кнопки',
                'entities' => ['primaryButton', 'secondaryButton', 'tertiaryButton'],
            ],
            'media' => [
                'key'      => 'media',
                'label'    => 'Медиа',
                'entities' => ['media', 'mediaSurface'],
            ],
            'meta' => [
                'key'      => 'meta',
                'label'    => 'Мета',
                'entities' => ['meta'],
            ],
            'items' => [
                'key'      => 'items',
                'label'    => 'Элементы',
                'entities' => ['items', 'itemSurface', 'itemTitle', 'itemText'],
            ],
        ];
    }

    public static function getCapabilities() {
        return [
            'sectionBackground'   => ['key' => 'sectionBackground', 'label' => 'Фон секции', 'tab' => 'design', 'default' => true],
            'sectionContainer'    => ['key' => 'sectionContainer', 'label' => 'Контейнер секции', 'tab' => 'design', 'default' => true],
            'titleContent'        => ['key' => 'titleContent', 'label' => 'Контент заголовка', 'tab' => 'content', 'default' => false],
            'subtitleContent'     => ['key' => 'subtitleContent', 'label' => 'Контент подзаголовка', 'tab' => 'content', 'default' => false],
            'bodyContent'         => ['key' => 'bodyContent', 'label' => 'Контент основного текста', 'tab' => 'content', 'default' => false],
            'buttonsContent'      => ['key' => 'buttonsContent', 'label' => 'Контент кнопок', 'tab' => 'content', 'default' => false],
            'mediaContent'        => ['key' => 'mediaContent', 'label' => 'Контент медиа', 'tab' => 'content', 'default' => false],
            'repeaterContent'     => ['key' => 'repeaterContent', 'label' => 'Контент повторов', 'tab' => 'content', 'default' => false],
            'eyebrowTypography'   => ['key' => 'eyebrowTypography', 'label' => 'Типографика надзаголовка', 'tab' => 'design', 'default' => false],
            'titleTypography'     => ['key' => 'titleTypography', 'label' => 'Типографика заголовка', 'tab' => 'design', 'default' => false],
            'subtitleTypography'  => ['key' => 'subtitleTypography', 'label' => 'Типографика подзаголовка', 'tab' => 'design', 'default' => false],
            'metaTypography'      => ['key' => 'metaTypography', 'label' => 'Типографика мета', 'tab' => 'design', 'default' => false],
            'bodyTypography'      => ['key' => 'bodyTypography', 'label' => 'Типографика основного текста', 'tab' => 'design', 'default' => false],
            'buttonsStyle'        => ['key' => 'buttonsStyle', 'label' => 'Стиль кнопок', 'tab' => 'design', 'default' => false],
            'mediaStyle'          => ['key' => 'mediaStyle', 'label' => 'Стиль медиа', 'tab' => 'design', 'default' => false],
            'mediaSurface'        => ['key' => 'mediaSurface', 'label' => 'Поверхность медиа', 'tab' => 'design', 'default' => false],
            'itemSurface'         => ['key' => 'itemSurface', 'label' => 'Поверхность элементов', 'tab' => 'design', 'default' => false],
            'itemTypography'      => ['key' => 'itemTypography', 'label' => 'Типографика элементов', 'tab' => 'design', 'default' => false],
            'spacingLayout'       => ['key' => 'spacingLayout', 'label' => 'Отступы', 'tab' => 'layout', 'default' => true],
            'alignmentLayout'     => ['key' => 'alignmentLayout', 'label' => 'Выравнивание', 'tab' => 'layout', 'default' => true],
            'responsiveTypography'=> ['key' => 'responsiveTypography', 'label' => 'Адаптивная типографика', 'tab' => 'layout', 'default' => false],
            'responsiveSpacing'   => ['key' => 'responsiveSpacing', 'label' => 'Адаптивные отступы', 'tab' => 'layout', 'default' => false],
            'dataBindings'        => ['key' => 'dataBindings', 'label' => 'Источник данных', 'tab' => 'data', 'default' => false],
            'repeaterBindings'    => ['key' => 'repeaterBindings', 'label' => 'Привязки коллекции', 'tab' => 'data', 'default' => false],
        ];
    }

    public static function getControls() {
        return [
            'textContent' => [
                'key'       => 'textContent',
                'component' => 'text-content-panel',
                'label'     => 'Текстовый контент',
            ],
            'buttonContent' => [
                'key'       => 'buttonContent',
                'component' => 'button-content-panel',
                'label'     => 'Контент кнопок',
            ],
            'mediaContent' => [
                'key'       => 'mediaContent',
                'component' => 'media-content-panel',
                'label'     => 'Контент медиа',
            ],
            'sectionBackground' => [
                'key'       => 'sectionBackground',
                'component' => 'section-background-panel',
                'label'     => 'Фон секции',
            ],
            'sectionContainer' => [
                'key'       => 'sectionContainer',
                'component' => 'section-container-panel',
                'label'     => 'Контейнер секции',
            ],
            'typographyText' => [
                'key'       => 'typographyText',
                'component' => 'typography-text-panel',
                'label'     => 'Типографика текста',
            ],
            'buttonStyle' => [
                'key'       => 'buttonStyle',
                'component' => 'button-style-panel',
                'label'     => 'Стиль кнопок',
            ],
            'mediaStyle' => [
                'key'       => 'mediaStyle',
                'component' => 'media-style-panel',
                'label'     => 'Стиль медиа',
            ],
            'surfaceStyle' => [
                'key'       => 'surfaceStyle',
                'component' => 'surface-style-panel',
                'label'     => 'Стиль поверхности',
            ],
            'spacingLayout' => [
                'key'       => 'spacingLayout',
                'component' => 'spacing-layout-panel',
                'label'     => 'Отступы',
            ],
            'alignmentLayout' => [
                'key'       => 'alignmentLayout',
                'component' => 'alignment-layout-panel',
                'label'     => 'Выравнивание',
            ],
            'dataSource' => [
                'key'       => 'dataSource',
                'component' => 'data-source-panel',
                'label'     => 'Источник данных',
            ],
            'dataCollection' => [
                'key'       => 'dataCollection',
                'component' => 'data-collection-panel',
                'label'     => 'Коллекция данных',
            ],
            'repeaterItems' => [
                'key'       => 'repeaterItems',
                'component' => 'repeater-items-panel',
                'label'     => 'Элементы списка',
            ],
        ];
    }

    public static function getPanels() {
        return [
            ['key' => 'textEyebrowContent', 'label' => 'Надзаголовок', 'tab' => 'content', 'section' => 'text', 'group' => 'eyebrow', 'order' => 110, 'requiresCapabilities' => ['titleContent'], 'requiresEntities' => ['eyebrow'], 'entityScope' => 'eyebrow', 'control' => 'textContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'textTitleContent', 'label' => 'Заголовок', 'tab' => 'content', 'section' => 'text', 'group' => 'title', 'order' => 120, 'requiresCapabilities' => ['titleContent'], 'requiresEntities' => ['title'], 'entityScope' => 'title', 'control' => 'textContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'textSubtitleContent', 'label' => 'Подзаголовок', 'tab' => 'content', 'section' => 'text', 'group' => 'subtitle', 'order' => 130, 'requiresCapabilities' => ['subtitleContent'], 'requiresEntities' => ['subtitle'], 'entityScope' => 'subtitle', 'control' => 'textContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'bodyContent', 'label' => 'Основной текст', 'tab' => 'content', 'section' => 'text', 'group' => 'body', 'order' => 140, 'requiresCapabilities' => ['bodyContent'], 'requiresEntities' => ['body'], 'entityScope' => 'body', 'control' => 'textContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'buttonsContent', 'label' => 'Кнопки', 'tab' => 'content', 'section' => 'actions', 'group' => 'buttons', 'order' => 210, 'requiresCapabilities' => ['buttonsContent'], 'requiresAnyEntities' => ['primaryButton', 'secondaryButton', 'tertiaryButton'], 'entityScope' => 'buttons', 'control' => 'buttonContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'mediaContent', 'label' => 'Медиа', 'tab' => 'content', 'section' => 'media', 'group' => 'media', 'order' => 310, 'requiresCapabilities' => ['mediaContent'], 'requiresEntities' => ['media'], 'entityScope' => 'media', 'control' => 'mediaContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'repeaterItems', 'label' => 'Элементы', 'tab' => 'content', 'section' => 'repeaters', 'group' => 'items', 'order' => 410, 'requiresCapabilities' => ['repeaterContent'], 'requiresEntities' => ['items'], 'entityScope' => 'items', 'control' => 'repeaterItems', 'breakpointAware' => false, 'repeatable' => true],
            ['key' => 'sectionBackground', 'label' => 'Фон секции', 'tab' => 'design', 'section' => 'section', 'group' => 'background', 'order' => 110, 'requiresCapabilities' => ['sectionBackground'], 'entityScope' => 'section', 'control' => 'sectionBackground', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'sectionContainer', 'label' => 'Контейнер', 'tab' => 'design', 'section' => 'section', 'group' => 'container', 'order' => 120, 'requiresCapabilities' => ['sectionContainer'], 'entityScope' => 'section', 'control' => 'sectionContainer', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'eyebrowTypography', 'label' => 'Надзаголовок', 'tab' => 'design', 'section' => 'typography', 'group' => 'eyebrow', 'order' => 205, 'requiresCapabilities' => ['eyebrowTypography'], 'requiresEntities' => ['eyebrow'], 'entityScope' => 'eyebrow', 'control' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'titleTypography', 'label' => 'Заголовок', 'tab' => 'design', 'section' => 'typography', 'group' => 'title', 'order' => 210, 'requiresCapabilities' => ['titleTypography'], 'requiresEntities' => ['title'], 'entityScope' => 'title', 'control' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'subtitleTypography', 'label' => 'Подзаголовок', 'tab' => 'design', 'section' => 'typography', 'group' => 'subtitle', 'order' => 220, 'requiresCapabilities' => ['subtitleTypography'], 'requiresEntities' => ['subtitle'], 'entityScope' => 'subtitle', 'control' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'metaTypography', 'label' => 'Мета', 'tab' => 'design', 'section' => 'typography', 'group' => 'meta', 'order' => 225, 'requiresCapabilities' => ['metaTypography'], 'requiresEntities' => ['meta'], 'entityScope' => 'meta', 'control' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'bodyTypography', 'label' => 'Основной текст', 'tab' => 'design', 'section' => 'typography', 'group' => 'body', 'order' => 230, 'requiresCapabilities' => ['bodyTypography'], 'requiresEntities' => ['body'], 'entityScope' => 'body', 'control' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'buttonsStyle', 'label' => 'Кнопки', 'tab' => 'design', 'section' => 'actions', 'group' => 'buttons', 'order' => 310, 'requiresCapabilities' => ['buttonsStyle'], 'requiresAnyEntities' => ['primaryButton', 'secondaryButton', 'tertiaryButton'], 'entityScope' => 'buttons', 'control' => 'buttonStyle', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'mediaStyle', 'label' => 'Медиа', 'tab' => 'design', 'section' => 'media', 'group' => 'media', 'order' => 320, 'requiresCapabilities' => ['mediaStyle'], 'requiresEntities' => ['media'], 'entityScope' => 'media', 'control' => 'mediaStyle', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'mediaSurface', 'label' => 'Поверхность медиа', 'tab' => 'design', 'section' => 'surfaces', 'group' => 'mediaSurface', 'order' => 410, 'requiresCapabilities' => ['mediaSurface'], 'requiresEntities' => ['mediaSurface'], 'entityScope' => 'mediaSurface', 'control' => 'surfaceStyle', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'itemSurface', 'label' => 'Поверхность элементов', 'tab' => 'design', 'section' => 'surfaces', 'group' => 'itemSurface', 'order' => 420, 'requiresCapabilities' => ['itemSurface'], 'requiresEntities' => ['itemSurface'], 'entityScope' => 'itemSurface', 'control' => 'surfaceStyle', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'itemTypography', 'label' => 'Типографика элементов', 'tab' => 'design', 'section' => 'typography', 'group' => 'items', 'order' => 430, 'requiresCapabilities' => ['itemTypography'], 'requiresAnyEntities' => ['itemTitle', 'itemText'], 'entityScope' => 'items', 'control' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'spacingLayout', 'label' => 'Отступы', 'tab' => 'layout', 'section' => 'spacing', 'group' => 'spacing', 'order' => 110, 'requiresCapabilities' => ['spacingLayout'], 'entityScope' => 'section', 'control' => 'spacingLayout', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'alignmentLayout', 'label' => 'Выравнивание', 'tab' => 'layout', 'section' => 'alignment', 'group' => 'alignment', 'order' => 120, 'requiresCapabilities' => ['alignmentLayout'], 'entityScope' => 'section', 'control' => 'alignmentLayout', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'dataBindings', 'label' => 'Источник данных', 'tab' => 'data', 'section' => 'bindings', 'group' => 'source', 'order' => 110, 'requiresCapabilities' => ['dataBindings'], 'entityScope' => 'block', 'control' => 'dataSource', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'repeaterBindings', 'label' => 'Привязки коллекции', 'tab' => 'data', 'section' => 'bindings', 'group' => 'collection', 'order' => 120, 'requiresCapabilities' => ['repeaterBindings'], 'requiresEntities' => ['items'], 'entityScope' => 'items', 'control' => 'dataCollection', 'breakpointAware' => false, 'repeatable' => true],
        ];
    }

    public static function getPanelMap() {
        $map = [];

        foreach (self::getPanels() as $panel) {
            $panel_key = (string) ($panel['key'] ?? '');
            if ($panel_key === '') {
                continue;
            }

            $map[$panel_key] = $panel;
        }

        return $map;
    }
}