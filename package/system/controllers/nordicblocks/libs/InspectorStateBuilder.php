<?php

class NordicblocksInspectorStateBuilder {

    public static function build(array $registry, array $resolved_entities, array $resolved_capabilities, array $ui = []) {
        $selected_entity   = (string) ($ui['selectedEntity'] ?? 'title');
        $active_tab        = (string) ($ui['activeTab'] ?? 'content');
        $active_breakpoint = (string) ($ui['activeBreakpoint'] ?? 'desktop');

        $available_panels = self::getAvailablePanels(
            (array) ($registry['panels'] ?? []),
            $resolved_entities,
            $resolved_capabilities,
            (array) ($registry['controls'] ?? []),
            (array) ($registry['controlPresets'] ?? [])
        );

        $visible_panels = self::filterPanelsBySelection(
            $available_panels,
            $selected_entity,
            (array) ($registry['entityGroups'] ?? [])
        );

        return [
            'entityGroups'   => self::getResolvedEntityGroups((array) ($registry['entityGroups'] ?? []), $resolved_entities),
            'controls'       => (array) ($registry['controls'] ?? $registry['controlPresets'] ?? []),
            'controlPresets' => (array) ($registry['controlPresets'] ?? []),
            'availablePanels'=> $available_panels,
            'visiblePanels'  => array_values(array_filter($visible_panels, function ($panel) use ($active_tab) {
                return (string) ($panel['tab'] ?? '') === $active_tab;
            })),
            'tabs'           => self::buildTabs((array) ($registry['tabs'] ?? []), $available_panels),
            'selectionModel' => [
                'mode'                 => 'single',
                'interaction'          => 'click',
                'supportsHoverPreview' => true,
                'supportsRepeaterPath' => false,
                'supportsMultiSelect'  => false,
            ],
            'ui' => [
                'selectedEntity'   => $selected_entity,
                'activeTab'        => $active_tab,
                'activeBreakpoint' => $active_breakpoint,
            ],
        ];
    }

    private static function getAvailablePanels(array $panels, array $resolved_entities, array $resolved_capabilities, array $controls, array $control_presets) {
        $available = [];

        foreach ($panels as $panel) {
            if (!self::isPanelAvailable($panel, $resolved_entities, $resolved_capabilities)) {
                continue;
            }

            $panel = self::attachControlDefinition($panel, $controls, $control_presets);
            $available[] = $panel;
        }

        usort($available, function ($left, $right) {
            return ((int) ($left['order'] ?? 0)) <=> ((int) ($right['order'] ?? 0));
        });

        return $available;
    }

    private static function attachControlDefinition(array $panel, array $controls, array $control_presets) {
        $control_key = (string) ($panel['control'] ?? $panel['controlPreset'] ?? '');
        $control = (array) ($controls[$control_key] ?? $control_presets[$control_key] ?? []);

        $panel['controlKey'] = $control_key;
        $panel['controlLabel'] = (string) ($control['label'] ?? $control_key);
        $panel['controlComponent'] = (string) ($control['component'] ?? '');

        return $panel;
    }

    private static function isPanelAvailable(array $panel, array $resolved_entities, array $resolved_capabilities) {
        foreach ((array) ($panel['requiresCapabilities'] ?? []) as $capability_key) {
            if (empty($resolved_capabilities[$capability_key])) {
                return false;
            }
        }

        foreach ((array) ($panel['requiresEntities'] ?? []) as $entity_key) {
            if (empty($resolved_entities[$entity_key])) {
                return false;
            }
        }

        $requires_any = (array) ($panel['requiresAnyEntities'] ?? []);
        if ($requires_any) {
            $has_any = false;
            foreach ($requires_any as $entity_key) {
                if (!empty($resolved_entities[$entity_key])) {
                    $has_any = true;
                    break;
                }
            }

            if (!$has_any) {
                return false;
            }
        }

        return true;
    }

    private static function filterPanelsBySelection(array $panels, $selected_entity, array $entity_groups) {
        if ($selected_entity === '') {
            return $panels;
        }

        return array_values(array_filter($panels, function ($panel) use ($selected_entity, $entity_groups) {
            $scope = (string) ($panel['entityScope'] ?? 'block');

            if ($scope === '' || $scope === 'block' || $scope === 'section') {
                return true;
            }

            if ($scope === $selected_entity) {
                return true;
            }

            $group_entities = (array) ($entity_groups[$scope]['entities'] ?? []);
            return in_array($selected_entity, $group_entities, true);
        }));
    }

    private static function buildTabs(array $tabs, array $available_panels) {
        $counts = [];

        foreach ($available_panels as $panel) {
            $tab_key = (string) ($panel['tab'] ?? '');
            if ($tab_key === '') {
                continue;
            }

            $counts[$tab_key] = ($counts[$tab_key] ?? 0) + 1;
        }

        foreach ($tabs as &$tab) {
            $tab['panelCount'] = (int) ($counts[$tab['key']] ?? 0);
        }
        unset($tab);

        return $tabs;
    }

    private static function getResolvedEntityGroups(array $entity_groups, array $resolved_entities) {
        $groups = [];

        foreach ($entity_groups as $group_key => $group) {
            $entities = array_values(array_filter((array) ($group['entities'] ?? []), function ($entity_key) use ($resolved_entities) {
                return !empty($resolved_entities[$entity_key]);
            }));

            if (!$entities) {
                continue;
            }

            $group['entities'] = $entities;
            $groups[$group_key] = $group;
        }

        return $groups;
    }
}