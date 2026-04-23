<?php

class NordicblocksCatalogDraftRegistry {

    public static function loadEntriesWithRaw($root_path) {
        $default_entries = self::getDefaultEntries();
        $entries         = [];
        $raw_json_map    = [];

        foreach ($default_entries as $entry) {
            $slug      = (string) ($entry['slug'] ?? '');
            $file_name = strtoupper(str_replace('-', '_', $slug));
            $file_path = self::resolveDraftFilePath($root_path, 'CATALOG-ENTRY-' . $file_name . '.json');
            $loaded    = null;

            if (is_file($file_path)) {
                $raw = (string) @file_get_contents($file_path);
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $loaded = $decoded;
                    $raw_json_map[$slug] = self::normalizeJsonString($raw, $decoded);
                }
            }

            if (!$loaded) {
                $loaded = $entry;
                $raw_json_map[$slug] = self::encodeJson($entry);
            }

            $entries[] = $loaded;
        }

        return [
            'entries' => $entries,
            'raw'     => $raw_json_map,
        ];
    }

    public static function loadSchemaJson($root_path) {
        $file_path = self::resolveDraftFilePath($root_path, 'CATALOG-ENTRY-SCHEMA-V1.json');

        if (is_file($file_path)) {
            $raw     = (string) @file_get_contents($file_path);
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return self::normalizeJsonString($raw, $decoded);
            }
        }

        return self::encodeJson(self::getDefaultSchema());
    }

    public static function findEntryBySlug($root_path, $slug) {
        $slug = trim((string) $slug);
        $payload = self::loadEntriesWithRaw($root_path);

        foreach ($payload['entries'] as $entry) {
            if ((string) ($entry['slug'] ?? '') === $slug) {
                return $entry;
            }
        }

        return null;
    }

    public static function resolveDraftFilePath($root_path, $file_name) {
        $root_path = rtrim((string) $root_path, '/\\');
        $system_path = $root_path . '/system/controllers/nordicblocks/catalog_drafts/' . $file_name;

        if (is_file($system_path)) {
            return $system_path;
        }

        return $root_path . '/docs/nordicblocks/' . $file_name;
    }

    public static function encodeJson(array $data) {
        return (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private static function normalizeJsonString($raw, array $decoded) {
        $trimmed = trim((string) $raw);
        return $trimmed !== '' ? $trimmed : self::encodeJson($decoded);
    }

    private static function getDefaultEntries() {
        return [
            [
                'slug' => 'hero_panels_wide',
                'title' => 'Hero: Wide Panels',
                'family' => 'hero',
                'category' => 'Hero',
                'subtitle' => 'Широкий hero с акцентом на CTA и быстрый старт лендинга',
                'summary' => 'Готовая стартовая секция для сервисов, малого бизнеса и offer-first страниц. После установки редактируется через design block editor.',
                'tags' => ['hero', 'cta', 'business', 'landing', 'panels'],
                'preview' => [
                    'type' => 'schematic',
                    'layout' => 'wide_panels',
                    'theme' => 'sunstone',
                    'aspectRatio' => '16:10',
                    'alt' => 'Превью блока Hero: Wide Panels',
                ],
                'availability' => 'free',
                'distributionModel' => 'local_install',
                'version' => '1.0.0-pilot',
                'editor' => [
                    'mode' => 'design_block_managed',
                    'installTarget' => 'managed_block_entry',
                ],
                'curation' => [
                    'status' => 'pilot_ready',
                    'featured' => true,
                    'sortOrder' => 10,
                ],
            ],
            [
                'slug' => 'hero_panels_editorial',
                'title' => 'Hero: Editorial Split',
                'family' => 'hero',
                'category' => 'Hero',
                'subtitle' => 'Редакционный hero с напряжённой типографикой и image-led подачей',
                'summary' => 'Подходит для студий, контентных проектов и брендовых лендингов, где нужен более характерный и журнальный первый экран.',
                'tags' => ['hero', 'editorial', 'brand', 'magazine', 'split'],
                'preview' => [
                    'type' => 'schematic',
                    'layout' => 'editorial_split',
                    'theme' => 'paper_ink',
                    'aspectRatio' => '16:10',
                    'alt' => 'Превью блока Hero: Editorial Split',
                ],
                'availability' => 'free',
                'distributionModel' => 'local_install',
                'version' => '1.0.0-pilot',
                'editor' => [
                    'mode' => 'design_block_managed',
                    'installTarget' => 'managed_block_entry',
                ],
                'curation' => [
                    'status' => 'pilot_ready',
                    'featured' => true,
                    'sortOrder' => 20,
                ],
            ],
            [
                'slug' => 'hero_media_showcase',
                'title' => 'Hero: Media Showcase',
                'family' => 'hero',
                'category' => 'Hero',
                'subtitle' => 'Media-first hero для product, app и showcase сценариев',
                'summary' => 'Показывает более образный визуальный тип hero-блока с акцентом на mockup, product shot или медиа-носитель.',
                'tags' => ['hero', 'media', 'showcase', 'product', 'launch'],
                'preview' => [
                    'type' => 'schematic',
                    'layout' => 'media_showcase',
                    'theme' => 'sea_glass',
                    'aspectRatio' => '16:10',
                    'alt' => 'Превью блока Hero: Media Showcase',
                ],
                'availability' => 'coming_soon',
                'distributionModel' => 'local_install',
                'version' => '0.9.0-concept',
                'editor' => [
                    'mode' => 'design_block_managed',
                    'installTarget' => 'managed_block_entry',
                ],
                'curation' => [
                    'status' => 'concept',
                    'featured' => false,
                    'sortOrder' => 30,
                ],
            ],
        ];
    }

    private static function getDefaultSchema() {
        return [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'title' => 'NordicBlocks Catalog Entry V1',
            'type' => 'object',
            'required' => [
                'slug', 'title', 'family', 'category', 'subtitle', 'summary', 'preview', 'availability', 'distributionModel', 'editor', 'curation',
            ],
            'properties' => [
                'slug' => ['type' => 'string', 'pattern' => '^[a-z0-9_\\-]+$'],
                'title' => ['type' => 'string'],
                'family' => ['type' => 'string'],
                'category' => ['type' => 'string'],
                'subtitle' => ['type' => 'string'],
                'summary' => ['type' => 'string'],
                'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
                'preview' => [
                    'type' => 'object',
                    'required' => ['type', 'layout', 'theme', 'aspectRatio', 'alt'],
                    'properties' => [
                        'type' => ['type' => 'string', 'enum' => ['schematic', 'image']],
                        'layout' => ['type' => 'string'],
                        'theme' => ['type' => 'string'],
                        'aspectRatio' => ['type' => 'string'],
                        'alt' => ['type' => 'string'],
                        'imageUrl' => ['type' => 'string'],
                    ],
                ],
                'availability' => ['type' => 'string', 'enum' => ['free', 'coming_soon', 'unavailable']],
                'distributionModel' => ['type' => 'string', 'enum' => ['local_install']],
                'version' => ['type' => 'string'],
                'requiresVersion' => ['type' => 'string'],
                'editor' => [
                    'type' => 'object',
                    'required' => ['mode', 'installTarget'],
                    'properties' => [
                        'mode' => ['type' => 'string', 'enum' => ['design_block_managed']],
                        'installTarget' => ['type' => 'string', 'enum' => ['managed_block_entry']],
                    ],
                ],
                'curation' => [
                    'type' => 'object',
                    'required' => ['status', 'featured', 'sortOrder'],
                    'properties' => [
                        'status' => ['type' => 'string', 'enum' => ['pilot_ready', 'concept', 'planned']],
                        'featured' => ['type' => 'boolean'],
                        'sortOrder' => ['type' => 'integer'],
                    ],
                ],
            ],
        ];
    }
}