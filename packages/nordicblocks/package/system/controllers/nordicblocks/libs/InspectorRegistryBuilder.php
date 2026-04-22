<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/InspectorDefinitionRegistry.php';

class NordicblocksInspectorRegistryBuilder {

    public static function build($block_type = '') {
        $block_type = self::normalizeBlockType($block_type);
        $manifest   = self::loadBlockManifest($block_type);

        if ($manifest) {
            return self::buildFromManifest($block_type, $manifest);
        }

        $entities        = NordicblocksInspectorDefinitionRegistry::getEntities();
        $panels          = array_map([__CLASS__, 'normalizePanelDefinition'], NordicblocksInspectorDefinitionRegistry::getPanels());
        $label_overrides = self::getLabelOverrides($block_type);

        $entities = self::applyEntityLabelOverrides($entities, (array) ($label_overrides['entities'] ?? []));
        $panels   = self::applyPanelLabelOverrides($panels, (array) ($label_overrides['panels'] ?? []));

        return [
            'tabs'             => NordicblocksInspectorDefinitionRegistry::getTabs(),
            'entities'         => $entities,
            'entityGroups'     => NordicblocksInspectorDefinitionRegistry::getEntityGroups(),
            'capabilities'     => NordicblocksInspectorDefinitionRegistry::getCapabilities(),
            'capabilityMatrix' => self::getCapabilityMatrix(),
            'controls'         => NordicblocksInspectorDefinitionRegistry::getControls(),
            'controlPresets'   => NordicblocksInspectorDefinitionRegistry::getControls(),
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
        $manifest_entities = (array) ($manifest['entities'] ?? []);
        $manifest_groups = (array) ($manifest['entityGroups'] ?? []);
        $manifest_panels = (array) ($manifest['panels'] ?? []);
        $manifest_capabilities = (array) ($manifest['capabilities'] ?? []);
        $entities     = self::buildManifestEntities($manifest_entities);
        $entity_groups= self::buildManifestEntityGroups($manifest_groups);
        $panels       = self::buildManifestPanels($manifest_panels);
        $capabilities = self::buildManifestCapabilities($manifest_capabilities);
        $diagnostics  = self::buildManifestDiagnostics($manifest_entities, $manifest_groups, $manifest_panels, $manifest_capabilities);

        return [
            'tabs'             => NordicblocksInspectorDefinitionRegistry::getTabs(),
            'entities'         => $entities,
            'entityGroups'     => $entity_groups,
            'capabilities'     => NordicblocksInspectorDefinitionRegistry::getCapabilities(),
            'capabilityMatrix' => [
                $block_type => [
                    'entities'     => array_keys($entities),
                    'capabilities' => $capabilities,
                ],
            ],
            'controls'         => NordicblocksInspectorDefinitionRegistry::getControls(),
            'controlPresets'   => NordicblocksInspectorDefinitionRegistry::getControls(),
            'panels'           => $panels,
            'labelOverrides'   => [
                'entities' => self::extractManifestLabels($entities),
                'panels'   => self::extractPanelLabels($panels),
            ],
            'manifest'         => [
                'blockType'      => $block_type,
                'title'          => (string) ($manifest['title'] ?? $block_type),
                'declaredEntityKeys' => array_keys($manifest_entities),
                'entityKeys'     => array_keys($entities),
                'declaredPanelKeys' => array_keys($manifest_panels),
                'panelKeys'      => array_values(array_map(function ($panel) {
                    return (string) ($panel['key'] ?? '');
                }, $panels)),
                'declaredCapabilityKeys' => array_keys(array_filter($manifest_capabilities)),
                'capabilityKeys' => array_keys(array_filter($capabilities)),
                'diagnostics'    => $diagnostics,
                'source'         => (string) ($manifest['__file'] ?? ''),
            ],
        ];
    }

    private static function buildManifestDiagnostics(array $manifest_entities, array $manifest_groups, array $manifest_panels, array $manifest_capabilities) {
        $shared_entities = NordicblocksInspectorDefinitionRegistry::getEntities();
        $shared_panels = NordicblocksInspectorDefinitionRegistry::getPanelMap();
        $shared_capabilities = NordicblocksInspectorDefinitionRegistry::getCapabilities();
        $unknown_group_entities = [];

        foreach ($manifest_groups as $group_key => $group) {
            $group_entities = array_values(array_filter((array) ($group['entities'] ?? []), function ($entity_key) {
                return is_string($entity_key) && $entity_key !== '';
            }));
            $unknown_entities = array_values(array_diff($group_entities, array_keys($shared_entities)));

            if ($unknown_entities) {
                $unknown_group_entities[$group_key] = $unknown_entities;
            }
        }

        return [
            'unknownEntityKeys' => array_values(array_diff(array_keys($manifest_entities), array_keys($shared_entities))),
            'unknownPanelKeys' => array_values(array_diff(array_keys($manifest_panels), array_keys($shared_panels))),
            'unknownCapabilityKeys' => array_values(array_diff(array_keys(array_filter($manifest_capabilities)), array_keys($shared_capabilities))),
            'unknownGroupEntities' => $unknown_group_entities,
        ];
    }

    private static function buildManifestEntities(array $manifest_entities) {
        $shared_entities = NordicblocksInspectorDefinitionRegistry::getEntities();
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
        $shared_groups = NordicblocksInspectorDefinitionRegistry::getEntityGroups();
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
        $shared_panels = NordicblocksInspectorDefinitionRegistry::getPanelMap();
        $panels = [];

        foreach ($manifest_panels as $panel_key => $overrides) {
            if (!isset($shared_panels[$panel_key])) {
                continue;
            }

            $panels[] = self::normalizePanelDefinition(array_merge($shared_panels[$panel_key], is_array($overrides) ? $overrides : [], [
                'key' => $panel_key,
            ]));
        }

        return $panels;
    }

    private static function buildManifestCapabilities(array $manifest_capabilities) {
        $shared_capabilities = NordicblocksInspectorDefinitionRegistry::getCapabilities();
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

    private static function normalizePanelDefinition(array $panel) {
        if (empty($panel['control']) && !empty($panel['controlPreset'])) {
            $panel['control'] = (string) $panel['controlPreset'];
        }

        if (empty($panel['controlPreset']) && !empty($panel['control'])) {
            $panel['controlPreset'] = (string) $panel['control'];
        }

        return $panel;
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
        $overrides = [
            'entities' => [],
            'panels'   => [],
        ];

        switch ($block_type) {
            case 'hero':
                $overrides['entities'] = [
                    'title'     => 'Заголовок',
                    'subtitle'  => 'Подзаголовок',
                    'eyebrow'   => 'Бейдж',
                    'actions'   => 'Кнопки',
                    'primaryCta'=> 'Основная CTA',
                    'secondaryCta' => 'Вторичная CTA',
                    'media'     => 'Изображение',
                ];
                break;
        }

        return $overrides;
    }

    private static function getCapabilityMatrix() {
        return NordicblocksInspectorDefinitionRegistry::getCapabilityMatrix();
    }
}