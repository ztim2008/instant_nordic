<?php

class NordicblocksBlockCapabilityResolver {

    public static function resolve($block_type, array $capability_matrix) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $block_type));
        $matrix = $capability_matrix[$type]['capabilities'] ?? [];
        $resolved = [];

        foreach ($matrix as $key => $value) {
            $resolved[$key] = (bool) $value;
        }

        return $resolved;
    }
}