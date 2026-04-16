<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DataSourceResolver.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BindingMapper.php';

class NordicblocksBlockPayloadHydrator {

    public static function hydrate(array $contract, array $context = []) {
        $resolved_sources = NordicblocksDataSourceResolver::resolve($contract, $context);
        $mapped = NordicblocksBindingMapper::map($contract, $resolved_sources, $context);
        $hydrated = $contract;

        if (!empty($mapped['replace']['content.items'])) {
            $hydrated['content']['items'] = is_array($mapped['content']['items'] ?? null) ? $mapped['content']['items'] : [];
        }

        if (!empty($mapped['runtime']['adapter']) && is_array($mapped['runtime']['adapter'])) {
            $hydrated['runtime']['adapter'] = $mapped['runtime']['adapter'];
            $hydrated['runtime']['featureFlags']['useAdapter'] = true;
        }

        return $hydrated;
    }
}