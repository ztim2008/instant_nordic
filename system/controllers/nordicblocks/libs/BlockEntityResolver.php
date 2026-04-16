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

        return $resolved;
    }
}