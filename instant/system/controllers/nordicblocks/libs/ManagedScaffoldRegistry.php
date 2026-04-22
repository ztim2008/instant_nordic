<?php

class NordicblocksManagedScaffoldRegistry {

    private static $schema_cache = [];
    private static $manifest_cache = [];

    public static function isManagedType($type) {
        $meta = self::getGeneratorMeta($type);

        return !empty($meta['managed']) && (string) ($meta['name'] ?? '') === 'nordicblocks-scaffold';
    }

    public static function getManagedTypes() {
        $types = [];
        $blocks_dir = self::getBlocksDir();

        if (!is_dir($blocks_dir)) {
            return $types;
        }

        foreach (scandir($blocks_dir) as $block_name) {
            if ($block_name === '.' || $block_name === '..' || $block_name === '') {
                continue;
            }

            if (!is_dir($blocks_dir . '/' . $block_name)) {
                continue;
            }

            if (self::isManagedType($block_name)) {
                $types[] = $block_name;
            }
        }

        sort($types);

        return array_values(array_unique($types));
    }

    public static function getGeneratorMeta($type) {
        $schema = self::getBlockSchema($type);
        $manifest = self::getBlockManifest($type);

        $schema_meta = is_array($schema['generator'] ?? null) ? $schema['generator'] : [];
        $manifest_meta = is_array($manifest['generator'] ?? null) ? $manifest['generator'] : [];

        return $schema_meta ?: $manifest_meta;
    }

    public static function getProfile($type) {
        $schema = self::getBlockSchema($type);
        $meta = self::getGeneratorMeta($type);

        return (string) ($schema['profile'] ?? ($meta['profile'] ?? ''));
    }

    public static function getSourceModeProfile($type) {
        $meta = self::getGeneratorMeta($type);

        return (string) ($meta['sourceModeProfile'] ?? 'manual');
    }

    public static function supportsContentList($type) {
        return self::isManagedType($type) && self::getSourceModeProfile($type) === 'content_list';
    }

    public static function supportsContentItem($type) {
        return self::isManagedType($type) && self::getSourceModeProfile($type) === 'content_item';
    }

    public static function usesCardCollectionMapping($type) {
        return in_array(self::getProfile($type), ['card_collection', 'catalog_like'], true);
    }

    public static function usesSliderCollectionMapping($type) {
        return self::getProfile($type) === 'slider_cards';
    }

    public static function usesFaqMapping($type) {
        return self::getProfile($type) === 'faq_like';
    }

    public static function getSchemaDefaults($type) {
        $defaults = [];
        $schema = self::getBlockSchema($type);
        $fields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            $key = trim((string) ($field['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $defaults[$key] = $field['default'] ?? '';
        }

        return $defaults;
    }

    public static function getEntityKeys($type) {
        $manifest = self::getBlockManifest($type);

        return array_keys((array) ($manifest['entities'] ?? []));
    }

    public static function hasEntity($type, $entity_key) {
        return in_array((string) $entity_key, self::getEntityKeys($type), true);
    }

    public static function getBlockSchema($type) {
        $type = self::normalizeBlockType($type);
        if ($type === '') {
            return [];
        }

        if (isset(self::$schema_cache[$type])) {
            return self::$schema_cache[$type];
        }

        $schema_file = self::getBlocksDir() . '/' . $type . '/schema.json';
        if (!is_file($schema_file)) {
            self::$schema_cache[$type] = [];
            return [];
        }

        $decoded = json_decode((string) file_get_contents($schema_file), true);
        self::$schema_cache[$type] = is_array($decoded) ? $decoded : [];

        return self::$schema_cache[$type];
    }

    public static function getBlockManifest($type) {
        $type = self::normalizeBlockType($type);
        if ($type === '') {
            return [];
        }

        if (isset(self::$manifest_cache[$type])) {
            return self::$manifest_cache[$type];
        }

        $manifest_file = self::getBlocksDir() . '/' . $type . '/manifest.php';
        if (!is_file($manifest_file)) {
            self::$manifest_cache[$type] = [];
            return [];
        }

        $manifest = require $manifest_file;
        self::$manifest_cache[$type] = is_array($manifest) ? $manifest : [];

        return self::$manifest_cache[$type];
    }

    private static function getBlocksDir() {
        return rtrim((string) cmsConfig::get('root_path'), '/\\') . '/system/controllers/nordicblocks/blocks';
    }

    private static function normalizeBlockType($type) {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $type));
    }
}