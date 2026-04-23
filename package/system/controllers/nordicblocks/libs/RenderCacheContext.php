<?php

class NordicblocksRenderCacheContext {

    public static function build(array $block, array $render_context = []) {
        $contract = isset($block['contract']) && is_array($block['contract']) ? $block['contract'] : [];
        $adapter  = isset($contract['runtime']['adapter']) && is_array($contract['runtime']['adapter'])
            ? $contract['runtime']['adapter']
            : [];

        if (empty($adapter['isDynamic'])) {
            return [
                'isDynamic'          => false,
                'cacheEligible'      => true,
                'sourceType'         => 'manual',
                'contextScope'       => 'manual',
                'reason'             => '',
                'sourceConfigHash'   => 'manual',
                'resultIdentityHash' => 'manual',
                'hash'               => 'manual',
            ];
        }

        $source_type = trim((string) ($adapter['source'] ?? ''));
        if ($source_type === 'content_item') {
            return self::buildContentItemContext($contract, $adapter, $render_context);
        }

        if ($source_type === 'content_list') {
            return self::buildContentListContext($contract, $adapter, $render_context);
        }

        return [
            'isDynamic'          => true,
            'cacheEligible'      => false,
            'sourceType'         => $source_type !== '' ? $source_type : 'unknown',
            'contextScope'       => 'dynamic',
            'reason'             => 'unknown_dynamic_source',
            'sourceConfigHash'   => '',
            'resultIdentityHash' => '',
            'hash'               => '',
        ];
    }

    private static function buildContentItemContext(array $contract, array $adapter, array $render_context) {
        $source = isset($contract['data']['source']) && is_array($contract['data']['source'])
            ? $contract['data']['source']
            : [];
        $resolver = isset($source['resolver']) && is_array($source['resolver']) ? $source['resolver'] : [];

        $ctype         = trim((string) ($source['ctype'] ?? $adapter['ctype'] ?? ''));
        $resolver_mode = trim((string) ($resolver['mode'] ?? $adapter['resolverMode'] ?? 'current'));
        $resolver_id   = (int) ($resolver['id'] ?? $resolver['itemId'] ?? $resolver['item_id'] ?? 0);
        $record_id     = (int) ($adapter['recordId'] ?? 0);

        $source_config = [
            'source'       => 'content_item',
            'ctype'        => $ctype,
            'resolverMode' => $resolver_mode,
        ];

        if ($resolver_id > 0) {
            $source_config['resolverId'] = $resolver_id;
        }

        if ($resolver_mode === 'current') {
            $current_ctype = trim((string) ($render_context['current_ctype'] ?? $render_context['ctype'] ?? ''));
            $current_id    = (int) ($render_context['current_item_id'] ?? $render_context['item_id'] ?? 0);

            if ($current_ctype !== '') {
                $source_config['currentCtype'] = $current_ctype;
            }
            if ($current_id > 0) {
                $source_config['currentItemId'] = $current_id;
            }
        }

        $result_identity = [];
        $cache_eligible  = true;
        $reason          = '';

        if ($record_id > 0) {
            $result_identity['recordId'] = $record_id;
        } elseif ($resolver_mode === 'by_id' && $resolver_id > 0) {
            $result_identity['recordId'] = $resolver_id;
            $result_identity['resolved'] = false;
        } else {
            $cache_eligible = false;
            $reason = 'content_item_identity_missing';
        }

        if ($ctype === '') {
            $cache_eligible = false;
            $reason = 'content_item_ctype_missing';
        }

        return self::finalizeContext('content_item', 'record', $source_config, $result_identity, $cache_eligible, $reason);
    }

    private static function buildContentListContext(array $contract, array $adapter, array $render_context) {
        $list_source = isset($contract['data']['listSource']) && is_array($contract['data']['listSource'])
            ? $contract['data']['listSource']
            : [];

        $ctype = trim((string) ($list_source['ctype'] ?? $adapter['ctype'] ?? ''));
        $item_ids = [];

        foreach ((array) ($adapter['itemIds'] ?? []) as $item_id) {
            $item_id = (int) $item_id;
            if ($item_id > 0) {
                $item_ids[] = $item_id;
            }
        }

        $source_config = [
            'source'        => 'content_list',
            'ctype'         => $ctype,
            'sort'          => (string) ($list_source['sort'] ?? $adapter['sort'] ?? 'date_pub_desc'),
            'limit'         => (int) ($list_source['limit'] ?? $adapter['limit'] ?? 0),
            'offset'        => (int) ($list_source['offset'] ?? $adapter['offset'] ?? 0),
            'emptyBehavior' => (string) ($list_source['emptyBehavior'] ?? 'fallback'),
            'map'           => (array) ($list_source['map'] ?? []),
        ];

        $result_identity = [
            'itemIds' => $item_ids,
            'count'   => count($item_ids),
        ];

        $cache_eligible = true;
        $reason = '';

        if ($ctype === '') {
            $cache_eligible = false;
            $reason = 'content_list_ctype_missing';
        }

        if (empty($item_ids) && (int) ($adapter['count'] ?? 0) > 0) {
            $cache_eligible = false;
            $reason = 'content_list_item_ids_missing';
        }

        return self::finalizeContext('content_list', 'collection', $source_config, $result_identity, $cache_eligible, $reason);
    }

    private static function finalizeContext($source_type, $context_scope, array $source_config, array $result_identity, $cache_eligible, $reason) {
        $source_hash = self::hashValue($source_config);
        $result_hash = self::hashValue($result_identity);
        $hash = ($cache_eligible && $source_hash !== '' && $result_hash !== '')
            ? self::hashValue([
                'sourceType'   => $source_type,
                'contextScope' => $context_scope,
                'source'       => $source_config,
                'result'       => $result_identity,
            ])
            : '';

        return [
            'isDynamic'          => true,
            'cacheEligible'      => (bool) $cache_eligible,
            'sourceType'         => (string) $source_type,
            'contextScope'       => (string) $context_scope,
            'reason'             => (string) $reason,
            'sourceConfigHash'   => $source_hash,
            'resultIdentityHash' => $result_hash,
            'hash'               => $hash,
        ];
    }

    private static function hashValue($value) {
        $json = self::stableJsonEncode($value);
        if ($json === '') {
            return '';
        }

        return substr(md5($json), 0, 16);
    }

    private static function stableJsonEncode($value) {
        $normalized = self::normalizeValue($value);
        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return is_string($json) ? $json : '';
    }

    private static function normalizeValue($value) {
        if (!is_array($value)) {
            return $value;
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[$key] = self::normalizeValue($item);
        }

        if (self::isAssoc($normalized)) {
            ksort($normalized);
        }

        return $normalized;
    }

    private static function isAssoc(array $value) {
        if (!$value) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }
}