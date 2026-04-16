<?php

class NordicblocksBindingMapper {

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
        if ($block_type !== 'faq' || empty($resolved_sources['active'])) {
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
            'count'     => count($items),
        ];

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

    private static function extractValue(array $record, $path) {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
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