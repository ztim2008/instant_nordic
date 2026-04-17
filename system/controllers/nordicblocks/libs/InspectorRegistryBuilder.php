<?php

class NordicblocksInspectorRegistryBuilder {

    public static function build($block_type = '') {
        $block_type = self::normalizeBlockType($block_type);
        $manifest   = self::loadBlockManifest($block_type);

        if ($manifest) {
            return self::buildFromManifest($block_type, $manifest);
        }

        $entities        = self::getEntityRegistry();
        $panels          = self::getPanelRegistry();
        $label_overrides = self::getLabelOverrides($block_type);

        $entities = self::applyEntityLabelOverrides($entities, (array) ($label_overrides['entities'] ?? []));
        $panels   = self::applyPanelLabelOverrides($panels, (array) ($label_overrides['panels'] ?? []));

        return [
            'tabs'             => self::getTabs(),
            'entities'         => $entities,
            'entityGroups'     => self::getEntityGroups(),
            'capabilities'     => self::getCapabilityRegistry(),
            'capabilityMatrix' => self::getCapabilityMatrix(),
            'controlPresets'   => self::getControlPresets(),
            'panels'           => $panels,
            'labelOverrides'   => $label_overrides,
            'manifest'         => null,
        ];
    }

    private static function normalizeBlockType($block_type) {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $block_type));
    }

    private static function loadBlockManifest($block_type) {
        if ($block_type === '') {
            return [];
        }

        $manifest_file = cmsConfig::get('root_path') . 'system/controllers/nordicblocks/blocks/' . $block_type . '/manifest.php';
        if (!is_file($manifest_file)) {
            return [];
        }

        $manifest = require $manifest_file;
        if (!is_array($manifest)) {
            return [];
        }

        $manifest['__file'] = $manifest_file;

        return $manifest;
    }

    private static function buildFromManifest($block_type, array $manifest) {
        $entities     = self::buildManifestEntities((array) ($manifest['entities'] ?? []));
        $entity_groups= self::buildManifestEntityGroups((array) ($manifest['entityGroups'] ?? []));
        $panels       = self::buildManifestPanels((array) ($manifest['panels'] ?? []));
        $capabilities = self::buildManifestCapabilities((array) ($manifest['capabilities'] ?? []));

        return [
            'tabs'             => self::getTabs(),
            'entities'         => $entities,
            'entityGroups'     => $entity_groups,
            'capabilities'     => self::getCapabilityRegistry(),
            'capabilityMatrix' => [
                $block_type => [
                    'entities'     => array_keys($entities),
                    'capabilities' => $capabilities,
                ],
            ],
            'controlPresets'   => self::getControlPresets(),
            'panels'           => $panels,
            'labelOverrides'   => [
                'entities' => self::extractManifestLabels($entities),
                'panels'   => self::extractPanelLabels($panels),
            ],
            'manifest'         => [
                'blockType'      => $block_type,
                'title'          => (string) ($manifest['title'] ?? $block_type),
                'entityKeys'     => array_keys($entities),
                'panelKeys'      => array_values(array_map(function ($panel) {
                    return (string) ($panel['key'] ?? '');
                }, $panels)),
                'capabilityKeys' => array_keys(array_filter($capabilities)),
                'source'         => (string) ($manifest['__file'] ?? ''),
            ],
        ];
    }

    private static function buildManifestEntities(array $manifest_entities) {
        $shared_entities = self::getEntityRegistry();
        $entities = [];

        foreach ($manifest_entities as $entity_key => $overrides) {
            if (!isset($shared_entities[$entity_key])) {
                continue;
            }

            $entities[$entity_key] = array_merge($shared_entities[$entity_key], is_array($overrides) ? $overrides : []);
        }

        return $entities;
    }

    private static function buildManifestEntityGroups(array $manifest_groups) {
        $shared_groups = self::getEntityGroups();
        $groups = [];

        foreach ($manifest_groups as $group_key => $group) {
            $base_group = isset($shared_groups[$group_key]) && is_array($shared_groups[$group_key])
                ? $shared_groups[$group_key]
                : ['key' => $group_key, 'label' => $group_key, 'entities' => []];

            $group = is_array($group) ? $group : [];
            $entities = array_values(array_filter((array) ($group['entities'] ?? $base_group['entities']), function ($entity_key) {
                return is_string($entity_key) && $entity_key !== '';
            }));

            if (!$entities) {
                continue;
            }

            $groups[$group_key] = array_merge($base_group, $group, [
                'key'      => $group_key,
                'entities' => $entities,
            ]);
        }

        return $groups;
    }

    private static function buildManifestPanels(array $manifest_panels) {
        $shared_panels = self::getPanelRegistryMap();
        $panels = [];

        foreach ($manifest_panels as $panel_key => $overrides) {
            if (!isset($shared_panels[$panel_key])) {
                continue;
            }

            $panels[] = array_merge($shared_panels[$panel_key], is_array($overrides) ? $overrides : [], [
                'key' => $panel_key,
            ]);
        }

        return $panels;
    }

    private static function buildManifestCapabilities(array $manifest_capabilities) {
        $shared_capabilities = self::getCapabilityRegistry();
        $resolved = [];

        foreach ($shared_capabilities as $capability_key => $definition) {
            $resolved[$capability_key] = !empty($manifest_capabilities[$capability_key]);
        }

        return $resolved;
    }

    private static function extractManifestLabels(array $entities) {
        $labels = [];

        foreach ($entities as $entity_key => $entity) {
            if (!empty($entity['label'])) {
                $labels[$entity_key] = (string) $entity['label'];
            }
        }

        return $labels;
    }

    private static function extractPanelLabels(array $panels) {
        $labels = [];

        foreach ($panels as $panel) {
            $panel_key = (string) ($panel['key'] ?? '');
            if ($panel_key === '' || empty($panel['label'])) {
                continue;
            }

            $labels[$panel_key] = (string) $panel['label'];
        }

        return $labels;
    }

    private static function applyEntityLabelOverrides(array $entities, array $overrides) {
        foreach ($overrides as $entity_key => $label) {
            if (!isset($entities[$entity_key])) {
                continue;
            }

            $entities[$entity_key]['label'] = (string) $label;
        }

        return $entities;
    }

    private static function applyPanelLabelOverrides(array $panels, array $overrides) {
        foreach ($panels as &$panel) {
            $panel_key = (string) ($panel['key'] ?? '');
            if ($panel_key !== '' && isset($overrides[$panel_key])) {
                $panel['label'] = (string) $overrides[$panel_key];
            }
        }
        unset($panel);

        return $panels;
    }

    private static function getLabelOverrides($block_type) {
        $block_type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $block_type));

        if ($block_type === 'faq') {
            return [
                'entities' => [
                    'items' => 'FAQ',
                    'itemSurface' => 'Карточка вопроса',
                    'itemTitle' => 'Вопрос',
                    'itemText' => 'Ответ',
                ],
                'panels' => [
                    'repeaterItems' => 'Вопросы',
                    'itemSurface' => 'Карточка вопроса',
                    'itemTypography' => 'Вопрос и ответ',
                    'repeaterBindings' => 'Поля FAQ',
                ],
            ];
        }

        return ['entities' => [], 'panels' => []];
    }

    private static function getTabs() {
        return [
            ['key' => 'content', 'label' => 'Контент', 'order' => 10],
            ['key' => 'design',  'label' => 'Дизайн',  'order' => 20],
            ['key' => 'layout',  'label' => 'Макет',   'order' => 30],
            ['key' => 'data',    'label' => 'Данные',  'order' => 40],
        ];
    }

    private static function getEntityRegistry() {
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

    private static function getEntityGroups() {
        return [
            'buttons' => [
                'key'      => 'buttons',
                'label'    => 'Кнопки',
                'entities' => ['primaryButton', 'secondaryButton'],
            ],
            'media' => [
                'key'      => 'media',
                'label'    => 'Медиа',
                'entities' => ['media', 'mediaSurface'],
            ],
            'items' => [
                'key'      => 'items',
                'label'    => 'Элементы',
                'entities' => ['items', 'itemSurface', 'itemTitle', 'itemText'],
            ],
        ];
    }

    private static function getCapabilityRegistry() {
        return [
            'sectionBackground'   => ['key' => 'sectionBackground', 'label' => 'Фон секции', 'tab' => 'design', 'default' => true],
            'sectionContainer'    => ['key' => 'sectionContainer', 'label' => 'Контейнер секции', 'tab' => 'design', 'default' => true],
            'titleContent'        => ['key' => 'titleContent', 'label' => 'Контент заголовка', 'tab' => 'content', 'default' => false],
            'subtitleContent'     => ['key' => 'subtitleContent', 'label' => 'Контент подзаголовка', 'tab' => 'content', 'default' => false],
            'bodyContent'         => ['key' => 'bodyContent', 'label' => 'Контент основного текста', 'tab' => 'content', 'default' => false],
            'buttonsContent'      => ['key' => 'buttonsContent', 'label' => 'Контент кнопок', 'tab' => 'content', 'default' => false],
            'mediaContent'        => ['key' => 'mediaContent', 'label' => 'Контент медиа', 'tab' => 'content', 'default' => false],
            'repeaterContent'     => ['key' => 'repeaterContent', 'label' => 'Контент повторов', 'tab' => 'content', 'default' => false],
            'titleTypography'     => ['key' => 'titleTypography', 'label' => 'Типографика заголовка', 'tab' => 'design', 'default' => false],
            'subtitleTypography'  => ['key' => 'subtitleTypography', 'label' => 'Типографика подзаголовка', 'tab' => 'design', 'default' => false],
            'bodyTypography'      => ['key' => 'bodyTypography', 'label' => 'Типографика основного текста', 'tab' => 'design', 'default' => false],
            'buttonsStyle'        => ['key' => 'buttonsStyle', 'label' => 'Стиль кнопок', 'tab' => 'design', 'default' => false],
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

    private static function getControlPresets() {
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

    private static function getCapabilityMatrix() {
        return [
            'hero' => [
                'entities' => ['eyebrow', 'title', 'subtitle', 'primaryButton', 'secondaryButton', 'media', 'mediaSurface'],
                'capabilities' => [
                    'sectionBackground'   => true,
                    'sectionContainer'    => true,
                    'titleContent'        => true,
                    'subtitleContent'     => true,
                    'bodyContent'         => false,
                    'buttonsContent'      => true,
                    'mediaContent'        => true,
                    'repeaterContent'     => false,
                    'titleTypography'     => true,
                    'subtitleTypography'  => true,
                    'bodyTypography'      => false,
                    'buttonsStyle'        => true,
                    'mediaSurface'        => true,
                    'itemSurface'         => false,
                    'itemTypography'      => false,
                    'spacingLayout'       => true,
                    'alignmentLayout'     => true,
                    'responsiveTypography'=> true,
                    'responsiveSpacing'   => true,
                    'dataBindings'        => true,
                    'repeaterBindings'    => false,
                ],
            ],
            'faq' => [
                'entities' => ['eyebrow', 'title', 'subtitle', 'items', 'itemSurface', 'itemTitle', 'itemText'],
                'capabilities' => [
                    'sectionBackground'   => true,
                    'sectionContainer'    => true,
                    'titleContent'        => true,
                    'subtitleContent'     => true,
                    'bodyContent'         => false,
                    'buttonsContent'      => false,
                    'mediaContent'        => false,
                    'repeaterContent'     => true,
                    'titleTypography'     => true,
                    'subtitleTypography'  => true,
                    'bodyTypography'      => false,
                    'buttonsStyle'        => false,
                    'mediaSurface'        => false,
                    'itemSurface'         => true,
                    'itemTypography'      => true,
                    'spacingLayout'       => true,
                    'alignmentLayout'     => true,
                    'responsiveTypography'=> true,
                    'responsiveSpacing'   => true,
                    'dataBindings'        => true,
                    'repeaterBindings'    => true,
                ],
            ],
        ];
    }

    private static function getPanelRegistry() {
        return [
            ['key' => 'textEyebrowContent', 'label' => 'Надзаголовок', 'tab' => 'content', 'section' => 'text', 'group' => 'eyebrow', 'order' => 110, 'requiresCapabilities' => ['titleContent'], 'requiresEntities' => ['eyebrow'], 'entityScope' => 'eyebrow', 'controlPreset' => 'textContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'textTitleContent', 'label' => 'Заголовок', 'tab' => 'content', 'section' => 'text', 'group' => 'title', 'order' => 120, 'requiresCapabilities' => ['titleContent'], 'requiresEntities' => ['title'], 'entityScope' => 'title', 'controlPreset' => 'textContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'textSubtitleContent', 'label' => 'Подзаголовок', 'tab' => 'content', 'section' => 'text', 'group' => 'subtitle', 'order' => 130, 'requiresCapabilities' => ['subtitleContent'], 'requiresEntities' => ['subtitle'], 'entityScope' => 'subtitle', 'controlPreset' => 'textContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'buttonsContent', 'label' => 'Кнопки', 'tab' => 'content', 'section' => 'actions', 'group' => 'buttons', 'order' => 210, 'requiresCapabilities' => ['buttonsContent'], 'requiresAnyEntities' => ['primaryButton', 'secondaryButton'], 'entityScope' => 'buttons', 'controlPreset' => 'buttonContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'mediaContent', 'label' => 'Медиа', 'tab' => 'content', 'section' => 'media', 'group' => 'media', 'order' => 310, 'requiresCapabilities' => ['mediaContent'], 'requiresEntities' => ['media'], 'entityScope' => 'media', 'controlPreset' => 'mediaContent', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'repeaterItems', 'label' => 'Элементы', 'tab' => 'content', 'section' => 'repeaters', 'group' => 'items', 'order' => 410, 'requiresCapabilities' => ['repeaterContent'], 'requiresEntities' => ['items'], 'entityScope' => 'items', 'controlPreset' => 'repeaterItems', 'breakpointAware' => false, 'repeatable' => true],
            ['key' => 'sectionBackground', 'label' => 'Фон секции', 'tab' => 'design', 'section' => 'section', 'group' => 'background', 'order' => 110, 'requiresCapabilities' => ['sectionBackground'], 'entityScope' => 'section', 'controlPreset' => 'sectionBackground', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'sectionContainer', 'label' => 'Контейнер', 'tab' => 'design', 'section' => 'section', 'group' => 'container', 'order' => 120, 'requiresCapabilities' => ['sectionContainer'], 'entityScope' => 'section', 'controlPreset' => 'sectionContainer', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'titleTypography', 'label' => 'Заголовок', 'tab' => 'design', 'section' => 'typography', 'group' => 'title', 'order' => 210, 'requiresCapabilities' => ['titleTypography'], 'requiresEntities' => ['title'], 'entityScope' => 'title', 'controlPreset' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'subtitleTypography', 'label' => 'Подзаголовок', 'tab' => 'design', 'section' => 'typography', 'group' => 'subtitle', 'order' => 220, 'requiresCapabilities' => ['subtitleTypography'], 'requiresEntities' => ['subtitle'], 'entityScope' => 'subtitle', 'controlPreset' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'bodyTypography', 'label' => 'Основной текст', 'tab' => 'design', 'section' => 'typography', 'group' => 'body', 'order' => 230, 'requiresCapabilities' => ['bodyTypography'], 'requiresEntities' => ['body'], 'entityScope' => 'body', 'controlPreset' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'buttonsStyle', 'label' => 'Кнопки', 'tab' => 'design', 'section' => 'actions', 'group' => 'buttons', 'order' => 310, 'requiresCapabilities' => ['buttonsStyle'], 'requiresAnyEntities' => ['primaryButton', 'secondaryButton'], 'entityScope' => 'buttons', 'controlPreset' => 'buttonStyle', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'mediaSurface', 'label' => 'Поверхность медиа', 'tab' => 'design', 'section' => 'surfaces', 'group' => 'mediaSurface', 'order' => 410, 'requiresCapabilities' => ['mediaSurface'], 'requiresEntities' => ['mediaSurface'], 'entityScope' => 'mediaSurface', 'controlPreset' => 'surfaceStyle', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'itemSurface', 'label' => 'Поверхность элементов', 'tab' => 'design', 'section' => 'surfaces', 'group' => 'itemSurface', 'order' => 420, 'requiresCapabilities' => ['itemSurface'], 'requiresEntities' => ['itemSurface'], 'entityScope' => 'itemSurface', 'controlPreset' => 'surfaceStyle', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'itemTypography', 'label' => 'Типографика элементов', 'tab' => 'design', 'section' => 'typography', 'group' => 'items', 'order' => 430, 'requiresCapabilities' => ['itemTypography'], 'requiresAnyEntities' => ['itemTitle', 'itemText'], 'entityScope' => 'items', 'controlPreset' => 'typographyText', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'spacingLayout', 'label' => 'Отступы', 'tab' => 'layout', 'section' => 'spacing', 'group' => 'spacing', 'order' => 110, 'requiresCapabilities' => ['spacingLayout'], 'entityScope' => 'section', 'controlPreset' => 'spacingLayout', 'breakpointAware' => true, 'repeatable' => false],
            ['key' => 'alignmentLayout', 'label' => 'Выравнивание', 'tab' => 'layout', 'section' => 'alignment', 'group' => 'alignment', 'order' => 120, 'requiresCapabilities' => ['alignmentLayout'], 'entityScope' => 'section', 'controlPreset' => 'alignmentLayout', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'dataBindings', 'label' => 'Источник данных', 'tab' => 'data', 'section' => 'bindings', 'group' => 'source', 'order' => 110, 'requiresCapabilities' => ['dataBindings'], 'entityScope' => 'block', 'controlPreset' => 'dataSource', 'breakpointAware' => false, 'repeatable' => false],
            ['key' => 'repeaterBindings', 'label' => 'Привязки коллекции', 'tab' => 'data', 'section' => 'bindings', 'group' => 'collection', 'order' => 120, 'requiresCapabilities' => ['repeaterBindings'], 'requiresEntities' => ['items'], 'entityScope' => 'items', 'controlPreset' => 'dataCollection', 'breakpointAware' => false, 'repeatable' => true],
        ];
    }

    private static function getPanelRegistryMap() {
        $map = [];

        foreach (self::getPanelRegistry() as $panel) {
            $panel_key = (string) ($panel['key'] ?? '');
            if ($panel_key === '') {
                continue;
            }

            $map[$panel_key] = $panel;
        }

        return $map;
    }
}