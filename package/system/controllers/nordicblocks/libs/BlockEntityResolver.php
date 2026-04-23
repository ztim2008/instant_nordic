<?php

class NordicblocksBlockEntityResolver {

    public static function resolve($block_type, array $contract, array $entity_registry) {
        $resolved = [];
        $contract_entities = is_array($contract['entities'] ?? null) ? $contract['entities'] : [];

        foreach ($contract_entities as $key => $entity) {
            if (!isset($entity_registry[$key])) {
                continue;
            }

            $resolved[$key] = array_merge($entity_registry[$key], is_array($entity) ? $entity : []);
        }

        if (isset($entity_registry['section'])) {
            $section_contract = [];

            if (isset($contract['design']['section']) && is_array($contract['design']['section'])) {
                $section_contract['design'] = $contract['design']['section'];
            }

            if (isset($contract['layout']) && is_array($contract['layout'])) {
                $section_contract['layout'] = $contract['layout'];
            }

            if ($section_contract) {
                $resolved['section'] = array_merge($entity_registry['section'], $section_contract);
            }
        }

        return $resolved;
    }
}