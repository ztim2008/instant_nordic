<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DataSourceResolver.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BindingMapper.php';

class NordicblocksBlockPayloadHydrator {

    public static function hydrate(array $contract, array $context = []) {
        $resolved_sources = NordicblocksDataSourceResolver::resolve($contract, $context);
        $mapped = NordicblocksBindingMapper::map($contract, $resolved_sources, $context);
        $hydrated = $contract;

        if (!empty($mapped['content']) && is_array($mapped['content'])) {
            $hydrated['content'] = self::mergeContent((array) ($hydrated['content'] ?? []), $mapped['content']);
        }

        if (!empty($mapped['runtime']['adapter']) && is_array($mapped['runtime']['adapter'])) {
            $hydrated['runtime']['adapter'] = $mapped['runtime']['adapter'];
            $hydrated['runtime']['featureFlags']['useAdapter'] = true;
        }

        return $hydrated;
    }

    private static function mergeContent(array $base, array $overlay) {
        foreach ($overlay as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key]) && self::isAssoc($value) && self::isAssoc($base[$key])) {
                $base[$key] = self::mergeContent($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    private static function isAssoc(array $value) {
        if (!$value) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }
}