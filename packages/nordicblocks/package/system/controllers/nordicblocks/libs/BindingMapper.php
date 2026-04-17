<?php

class NordicblocksBindingMapper {

    private static $hero_slot_definitions = [
        'title' => ['path' => 'title'],
        'subtitle' => ['path' => 'subtitle'],
        'image' => ['path' => 'media.image'],
        'imageAlt' => ['path' => 'media.alt'],
        'date' => ['path' => 'meta.date'],
        'views' => ['path' => 'meta.views'],
        'comments' => ['path' => 'meta.comments'],
        'primaryButtonUrl' => ['path' => 'primaryButton.url'],
    ];

    private static $field_aliases = [
        'comments_count' => 'comments',
    ];

    public static function map(array $contract, array $resolved_sources, array $context = []) {
        $mapped = [
            'content' => [],
            'replace' => [],
            'runtime' => [],
        ];

        $block_type = (string) ($contract['meta']['blockType'] ?? '');
        if (empty($resolved_sources['active'])) {
            return $mapped;
        }

        if ($block_type === 'hero') {
            return self::mapHero($contract, $resolved_sources);
        }

        if ($block_type !== 'faq') {
            return $mapped;
        }

        $list_source = is_array($resolved_sources['listSource'] ?? null) ? $resolved_sources['listSource'] : [];
        $items = self::mapFaqItems((array) ($resolved_sources['listItems'] ?? []), $list_source);
        $empty_behavior = (string) ($list_source['emptyBehavior'] ?? 'fallback');

        if ($items || $empty_behavior === 'empty') {
            $mapped['content']['items'] = $items;
            $mapped['replace']['content.items'] = true;
        }

        $mapped['runtime']['adapter'] = [
            'isDynamic' => true,
            'source'    => 'content_list',
            'ctype'     => (string) ($list_source['ctype'] ?? ''),
            'sort'      => (string) ($list_source['sort'] ?? 'date_pub_desc'),
            'limit'     => (int) ($list_source['limit'] ?? 0),
            'count'     => count($items),
            'itemIds'   => self::extractRecordIds((array) ($resolved_sources['listItems'] ?? [])),
        ];

        return $mapped;
    }

    private static function mapHero(array $contract, array $resolved_sources) {
        $mapped = [
            'content' => [],
            'replace' => [],
            'runtime' => [
                'adapter' => [
                    'isDynamic' => true,
                    'source'    => 'content_item',
                    'ctype'     => (string) (($resolved_sources['source']['ctype'] ?? '')),
                    'resolverMode' => (string) (($resolved_sources['source']['resolver']['mode'] ?? 'current')),
                    'recordId'  => (int) (($resolved_sources['record']['id'] ?? 0)),
                    'resolved'  => !empty($resolved_sources['record']),
                ],
            ],
        ];

        $record = is_array($resolved_sources['record'] ?? null) ? $resolved_sources['record'] : [];
        if (!$record) {
            return $mapped;
        }

        $bindings = is_array($contract['data']['bindings'] ?? null) ? $contract['data']['bindings'] : [];

        foreach (self::$hero_slot_definitions as $binding_key => $definition) {
            $binding = self::resolveHeroBinding($bindings, $binding_key);
            if (($binding['mode'] ?? 'manual') === 'manual' || empty($binding['field'])) {
                continue;
            }

            $manual_value = self::getValueByPath((array) ($contract['content'] ?? []), $definition['path'], '');
            $value = self::formatValue(
                self::extractValue($record, (string) $binding['field']),
                (string) ($binding['formatter'] ?? 'plain_text'),
                $record
            );

            if (self::isEmptyValue($value)) {
                $empty_behavior = (string) ($binding['emptyBehavior'] ?? 'fallback');
                if ($empty_behavior === 'fallback' || (string) ($binding['mode'] ?? '') === 'mixed') {
                    $value = $manual_value;
                } elseif ($empty_behavior === 'hide') {
                    $value = '';
                }
            }

            self::setValueByPath($mapped['content'], $definition['path'], $value);
        }

        return $mapped;
    }

    private static function mapFaqItems(array $records, array $list_source) {
        $map = is_array($list_source['map'] ?? null) ? $list_source['map'] : [];
        $question_field = (string) ($map['question'] ?? 'title');
        $answer_field   = (string) ($map['answer'] ?? '');

        $items = [];
        foreach ($records as $record) {
            if (!is_array($record)) {
                continue;
            }

            $question = self::normalizeText(self::extractValue($record, $question_field));
            $answer   = self::normalizeText(self::extractValue($record, $answer_field));

            if ($question === '' && $answer === '') {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer'   => $answer,
            ];
        }

        return $items;
    }

    private static function extractRecordIds(array $records) {
        $ids = [];

        foreach ($records as $record) {
            if (!is_array($record)) {
                continue;
            }

            $record_id = (int) ($record['id'] ?? 0);
            if ($record_id > 0) {
                $ids[] = $record_id;
            }
        }

        return $ids;
    }

    private static function extractValue(array $record, $path) {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        if ($path === 'record_url') {
            return self::buildRecordUrl($record);
        }

        if ($path === 'record_image_url') {
            return self::buildRecordImageUrl($record);
        }

        if (!array_key_exists($path, $record) && isset(self::$field_aliases[$path]) && array_key_exists(self::$field_aliases[$path], $record)) {
            return $record[self::$field_aliases[$path]];
        }

        $parts = explode('.', $path);
        $value = $record;

        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return '';
            }
            $value = $value[$part];
        }

        return $value;
    }

    private static function resolveHeroBinding(array $bindings, $key) {
        if (isset($bindings[$key]) && is_array($bindings[$key])) {
            return $bindings[$key];
        }

        if ($key === 'primaryButtonUrl' && isset($bindings['primaryButton']['url']) && is_array($bindings['primaryButton']['url'])) {
            return $bindings['primaryButton']['url'];
        }

        return [];
    }

    private static function formatValue($value, $formatter, array $record) {
        switch ((string) $formatter) {
            case 'image_url':
                return self::normalizeImageUrl($value);

            case 'record_url':
                $url = self::normalizeUrl($value);
                return $url !== '' ? $url : self::buildRecordUrl($record);

            case 'date_human':
                return self::normalizeDate($value);

            case 'number':
                return self::normalizeNumber($value);

            case 'plain_text':
            default:
                return self::normalizeText($value);
        }
    }

    private static function normalizeImageUrl($value) {
        if (is_array($value)) {
            $model = cmsCore::getModel('nordicblocks');
            if ($model && method_exists($model, 'normalizeImageFieldValue')) {
                $normalized = $model->normalizeImageFieldValue($value);
                if (is_array($normalized)) {
                    return trim((string) ($normalized['display'] ?? $normalized['original'] ?? ''));
                }
            }

            foreach (['display', 'original', 'url', 'src'] as $key) {
                if (!empty($value[$key]) && is_string($value[$key])) {
                    return trim($value[$key]);
                }
            }

            return '';
        }

        return self::normalizeUrl($value);
    }

    private static function normalizeUrl($value) {
        if (!is_string($value)) {
            return '';
        }

        return trim($value);
    }

    private static function normalizeDate($value) {
        if (!is_string($value) || trim($value) === '') {
            return '';
        }

        $timestamp = strtotime($value);
        if (!$timestamp) {
            return self::normalizeText($value);
        }

        return date('d.m.Y', $timestamp);
    }

    private static function normalizeNumber($value) {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_numeric($value)) {
            return (string) ((int) $value);
        }

        return self::normalizeText($value);
    }

    private static function buildRecordUrl(array $record) {
        if (!empty($record['url']) && is_string($record['url'])) {
            return trim($record['url']);
        }

        $ctype_name = trim((string) ($record['ctype_name'] ?? ''));
        $slug = trim((string) ($record['slug'] ?? ''));
        if ($ctype_name !== '' && $slug !== '') {
            return href_to($ctype_name, $slug . '.html');
        }

        return '';
    }

    private static function buildRecordImageUrl(array $record) {
        foreach (['image', 'cover', 'cover_image', 'photo'] as $candidate) {
            $value = self::extractValue($record, $candidate);
            $url = self::normalizeImageUrl($value);
            if ($url !== '') {
                return $url;
            }
        }

        return '';
    }

    private static function getValueByPath(array $payload, $path, $fallback = '') {
        $parts = explode('.', (string) $path);
        $value = $payload;

        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $fallback;
            }
            $value = $value[$part];
        }

        return $value;
    }

    private static function setValueByPath(array &$payload, $path, $value) {
        $parts = explode('.', (string) $path);
        $last = array_pop($parts);
        $cursor = &$payload;

        foreach ($parts as $part) {
            if (!isset($cursor[$part]) || !is_array($cursor[$part])) {
                $cursor[$part] = [];
            }
            $cursor = &$cursor[$part];
        }

        $cursor[$last] = $value;
    }

    private static function isEmptyValue($value) {
        if (is_array($value)) {
            return !$value;
        }

        return trim((string) $value) === '';
    }

    private static function normalizeText($value) {
        if (is_array($value)) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (!is_string($value)) {
            return '';
        }

        $value = preg_replace('#<br\s*/?>#i', "\n", $value);
        $value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
        $value = preg_replace("/\r\n?|\n/u", "\n", $value);

        return trim($value);
    }
}