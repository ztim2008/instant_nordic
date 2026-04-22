<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/ManagedScaffoldRegistry.php';

class NordicblocksBindingMapper {

    private static $hero_slot_definitions = [
        'eyebrow' => ['path' => 'eyebrow'],
        'title' => ['path' => 'title'],
        'subtitle' => ['path' => 'subtitle'],
        'image' => ['path' => 'media.image'],
        'imageAlt' => ['path' => 'media.alt'],
        'category' => ['path' => 'meta.category'],
        'author' => ['path' => 'meta.author'],
        'date' => ['path' => 'meta.date'],
        'views' => ['path' => 'meta.views'],
        'comments' => ['path' => 'meta.comments'],
        'primaryButtonUrl' => ['path' => 'primaryButton.url'],
        'secondaryButtonUrl' => ['path' => 'secondaryButton.url'],
        'tertiaryButtonUrl' => ['path' => 'tertiaryButton.url'],
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

        if (NordicblocksManagedScaffoldRegistry::supportsContentItem($block_type)) {
            return self::mapManagedContentItem($contract, $resolved_sources);
        }

        if (!in_array($block_type, ['faq', 'content_feed', 'category_cards', 'headline_feed', 'swiss_grid', 'catalog_browser'], true)
            && !NordicblocksManagedScaffoldRegistry::supportsContentList($block_type)) {
            return $mapped;
        }

        $list_source = is_array($resolved_sources['listSource'] ?? null) ? $resolved_sources['listSource'] : [];
        $is_slider_collection = NordicblocksManagedScaffoldRegistry::usesSliderCollectionMapping($block_type);
        $items = (in_array($block_type, ['content_feed', 'category_cards', 'headline_feed', 'swiss_grid', 'catalog_browser'], true)
            || NordicblocksManagedScaffoldRegistry::usesCardCollectionMapping($block_type))
            ? self::mapContentFeedItems((array) ($resolved_sources['listItems'] ?? []), $list_source)
            : ($is_slider_collection
                ? self::mapSliderSlides((array) ($resolved_sources['listItems'] ?? []), $list_source)
                : self::mapFaqItems((array) ($resolved_sources['listItems'] ?? []), $list_source));
        $empty_behavior = (string) ($list_source['emptyBehavior'] ?? 'fallback');

        if ($items || $empty_behavior === 'empty') {
            if ($is_slider_collection) {
                $mapped['content']['slides'] = $items;
                $mapped['replace']['content.slides'] = true;
            } else {
                $mapped['content']['items'] = $items;
                $mapped['replace']['content.items'] = true;
            }
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

    private static function mapManagedContentItem(array $contract, array $resolved_sources) {
        $mapped = [
            'content' => [],
            'replace' => [],
            'runtime' => [
                'adapter' => [
                    'isDynamic' => true,
                    'source' => 'content_item',
                    'ctype' => (string) (($resolved_sources['source']['ctype'] ?? '')),
                    'resolverMode' => (string) (($resolved_sources['source']['resolver']['mode'] ?? 'current')),
                    'recordId' => (int) (($resolved_sources['record']['id'] ?? 0)),
                    'resolved' => !empty($resolved_sources['record']),
                ],
            ],
        ];

        $record = is_array($resolved_sources['record'] ?? null) ? $resolved_sources['record'] : [];
        if (!$record) {
            return $mapped;
        }

        $bindings = is_array($contract['data']['bindings'] ?? null) ? $contract['data']['bindings'] : [];
        $block_type = (string) ($contract['meta']['blockType'] ?? '');

        $mapped['content']['title'] = self::normalizeText(self::extractValue($record, 'title'));
        $mapped['content']['subtitle'] = self::normalizeText(self::extractValue($record, 'teaser'));
        $mapped['content']['body'] = self::normalizeText(self::extractValue($record, 'body'));
        $mapped['content']['media'] = [
            'image' => self::normalizeImageUrl(self::extractValue($record, 'record_image_url')),
            'alt' => self::normalizeText(self::extractValue($record, 'title')),
        ];
        $mapped['content']['primaryButton'] = [
            'label' => '',
            'url' => self::normalizeUrl(self::extractValue($record, 'record_url')),
        ];
        $mapped['content']['meta'] = [
            'category' => self::normalizeText(self::extractValue($record, 'category.title')),
            'author' => self::normalizeText(self::extractValue($record, 'user.nickname')),
            'date' => self::normalizeDate(self::extractValue($record, 'date_pub')),
            'views' => self::normalizeNumber(self::extractValue($record, 'hits_count')),
            'comments' => self::normalizeNumber(self::extractValue($record, 'comments_count')),
        ];

        foreach (self::getManagedContentItemSlotDefinitions($block_type, $contract) as $binding_key => $definition) {
            $binding = self::resolveHeroBinding($bindings, $binding_key);
            if (($binding['mode'] ?? 'manual') === 'manual' || empty($binding['field'])) {
                continue;
            }

            $manual_value = self::getValueByPath((array) ($contract['content'] ?? []), $definition['path'], self::getValueByPath($mapped['content'], $definition['path'], ''));
            $value = self::formatValue(
                self::extractValue($record, (string) $binding['field']),
                (string) ($binding['formatter'] ?? 'plain_text'),
                $record
            );

            if (self::isEmptyValue($value)) {
                $empty_behavior = (string) ($binding['emptyBehavior'] ?? 'fallback');
                if ($empty_behavior === 'fallback' || (string) ($binding['mode'] ?? '') === 'mixed') {
                    $value = $manual_value;
                } else {
                    $value = '';
                }
            }

            self::setValueByPath($mapped['content'], $definition['path'], $value);
        }

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

    private static function mapSliderSlides(array $records, array $list_source) {
        $map = is_array($list_source['map'] ?? null) ? $list_source['map'] : [];

        $slides = [];
        foreach ($records as $record) {
            if (!is_array($record)) {
                continue;
            }

            $title = self::normalizeText(self::extractValue($record, (string) ($map['title'] ?? 'title')));
            $text = self::normalizeText(self::extractValue($record, (string) ($map['text'] ?? 'teaser')));
            $eyebrow = self::normalizeText(self::extractValue($record, (string) ($map['eyebrow'] ?? 'category.title')));
            $meta_label = self::normalizeText(self::extractValue($record, (string) ($map['metaLabel'] ?? 'category.title')));
            $date = self::normalizeDate(self::extractValue($record, (string) ($map['date'] ?? 'date_pub')));
            $image = self::normalizeImageUrl(self::extractValue($record, (string) ($map['image'] ?? 'record_image_url')));
            $image_alt = self::normalizeText(self::extractValue($record, (string) ($map['imageAlt'] ?? 'title')));
            $record_url = self::normalizeUrl(self::extractValue($record, (string) ($map['recordUrl'] ?? 'record_url')));
            $primary_cta_label = self::normalizeText(self::extractValue($record, (string) ($map['primaryCtaLabel'] ?? '')));
            $primary_cta_url = self::normalizeUrl(self::extractValue($record, (string) ($map['primaryCtaUrl'] ?? 'record_url')));
            $secondary_cta_label = self::normalizeText(self::extractValue($record, (string) ($map['secondaryCtaLabel'] ?? '')));
            $secondary_cta_url = self::normalizeUrl(self::extractValue($record, (string) ($map['secondaryCtaUrl'] ?? '')));

            if ($record_url === '') {
                $record_url = self::normalizeUrl(self::extractValue($record, 'record_url'));
            }

            if ($primary_cta_url === '') {
                $primary_cta_url = $record_url;
            }

            if ($image === '') {
                $image = self::normalizeImageUrl(self::extractValue($record, 'record_image_url'));
            }

            if ($title === '' && $text === '' && $eyebrow === '' && $image === '' && $record_url === '') {
                continue;
            }

            $slides[] = self::buildSliderSlidePayload([
                'eyebrow' => $eyebrow,
                'title' => $title,
                'text' => $text,
                'image' => $image,
                'imageAlt' => $image_alt !== '' ? $image_alt : $title,
                'date' => $date,
                'metaLabel' => $meta_label,
                'recordUrl' => $record_url,
                'primaryCtaLabel' => $primary_cta_label,
                'primaryCtaUrl' => $primary_cta_url,
                'secondaryCtaLabel' => $secondary_cta_label,
                'secondaryCtaUrl' => $secondary_cta_url,
            ]);
        }

        return $slides;
    }

    private static function mapFaqItems(array $records, array $list_source) {
        $map = is_array($list_source['map'] ?? null) ? $list_source['map'] : [];
        $question_field = (string) ($map['title'] ?? ($map['question'] ?? 'title'));
        $answer_field   = (string) ($map['text'] ?? ($map['answer'] ?? ''));

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

            $items[] = self::buildFaqItemPayload($question, $answer);
        }

        return $items;
    }

    private static function mapContentFeedItems(array $records, array $list_source) {
        $map = is_array($list_source['map'] ?? null) ? $list_source['map'] : [];

        $items = [];
        foreach ($records as $record) {
            if (!is_array($record)) {
                continue;
            }

            $title = self::normalizeText(self::extractValue($record, (string) ($map['title'] ?? 'title')));
            $excerpt = self::normalizeText(self::extractValue($record, (string) ($map['excerpt'] ?? 'teaser')));
            $category = self::normalizeText(self::extractValue($record, (string) ($map['category'] ?? 'category.title')));
            $category_url = self::normalizeUrl(self::extractValue($record, (string) ($map['categoryUrl'] ?? 'category.url')));
            $date = self::normalizeDate(self::extractValue($record, (string) ($map['date'] ?? 'date_pub')));
            $views = self::normalizeNumber(self::extractValue($record, (string) ($map['views'] ?? 'hits_count')));
            $comments = self::normalizeNumber(self::extractValue($record, (string) ($map['comments'] ?? 'comments_count')));
            $price = self::normalizeText(self::extractValue($record, (string) ($map['price'] ?? 'price')));
            $price_old = self::normalizeText(self::extractValue($record, (string) ($map['priceOld'] ?? 'price_old')));
            $currency = self::normalizeText(self::extractValue($record, (string) ($map['currency'] ?? 'currency')));
            $badge = self::normalizeText(self::extractValue($record, (string) ($map['badge'] ?? 'badge')));
            $tags = self::normalizeTags(self::extractValue($record, (string) ($map['tags'] ?? 'tags')));
            $url = self::normalizeUrl(self::extractValue($record, (string) ($map['url'] ?? 'record_url')));
            $image = self::normalizeImageUrl(self::extractValue($record, (string) ($map['image'] ?? 'record_image_url')));
            $image_alt = self::normalizeText(self::extractValue($record, (string) ($map['imageAlt'] ?? 'title')));
            $cta_label = self::normalizeText(self::extractValue($record, (string) ($map['ctaLabel'] ?? 'cta_label')));
            $cta_url = self::normalizeUrl(self::extractValue($record, (string) ($map['ctaUrl'] ?? 'cta_url')));
            $availability = self::normalizeText(self::extractValue($record, (string) ($map['availability'] ?? 'availability')));
            $gallery = self::normalizeGallery(self::extractValue($record, (string) ($map['gallery'] ?? 'gallery')));

            if ($url === '') {
                $url = self::normalizeUrl(self::extractValue($record, 'record_url'));
            }

            if ($image === '') {
                $image = self::normalizeImageUrl(self::extractValue($record, 'record_image_url'));
            }

            if ($title === '' && $excerpt === '' && $category === '' && $image === '' && $url === '') {
                continue;
            }

            $items[] = self::buildContentFeedItemPayload([
                'category' => $category,
                'categoryUrl' => $category_url,
                'title' => $title,
                'excerpt' => $excerpt,
                'url' => $url,
                'image' => $image,
                'imageAlt' => $image_alt !== '' ? $image_alt : $title,
                'date' => $date,
                'views' => $views,
                'comments' => $comments,
                'price' => $price,
                'priceOld' => $price_old,
                'currency' => $currency,
                'badge' => $badge,
                'tags' => $tags,
                'ctaLabel' => $cta_label,
                'ctaUrl' => $cta_url,
                'availability' => $availability,
                'gallery' => $gallery,
            ]);
        }

        return $items;
    }
    private static function buildFaqItemPayload($title, $text) {
        $title = self::normalizeText($title);
        $text  = self::normalizeText($text);

        return [
            'title'    => $title,
            'text'     => $text,
            'question' => $title,
            'answer'   => $text,
        ];
    }

    private static function buildSliderSlidePayload(array $slide) {
        $title = self::normalizeText($slide['title'] ?? '');
        $text = self::normalizeText($slide['text'] ?? '');
        $image_alt = self::normalizeText($slide['imageAlt'] ?? ($slide['image_alt'] ?? ''));
        $record_url = self::normalizeUrl($slide['recordUrl'] ?? ($slide['record_url'] ?? ($slide['url'] ?? '')));
        $primary_cta_label = self::normalizeText($slide['primaryCtaLabel'] ?? ($slide['primary_cta_label'] ?? ''));
        $primary_cta_url = self::normalizeUrl($slide['primaryCtaUrl'] ?? ($slide['primary_cta_url'] ?? ''));
        $secondary_cta_label = self::normalizeText($slide['secondaryCtaLabel'] ?? ($slide['secondary_cta_label'] ?? ''));
        $secondary_cta_url = self::normalizeUrl($slide['secondaryCtaUrl'] ?? ($slide['secondary_cta_url'] ?? ''));

        return [
            'eyebrow' => self::normalizeText($slide['eyebrow'] ?? ''),
            'title' => $title,
            'text' => $text,
            'image' => self::normalizeImageUrl($slide['image'] ?? ''),
            'imageAlt' => $image_alt,
            'image_alt' => $image_alt,
            'date' => self::normalizeText($slide['date'] ?? ''),
            'metaLabel' => self::normalizeText($slide['metaLabel'] ?? ($slide['meta_label'] ?? '')),
            'meta_label' => self::normalizeText($slide['metaLabel'] ?? ($slide['meta_label'] ?? '')),
            'recordUrl' => $record_url,
            'record_url' => $record_url,
            'url' => $record_url,
            'primaryAction' => [
                'label' => $primary_cta_label,
                'url' => $primary_cta_url,
            ],
            'secondaryAction' => [
                'label' => $secondary_cta_label,
                'url' => $secondary_cta_url,
            ],
            'primaryCtaLabel' => $primary_cta_label,
            'primary_cta_label' => $primary_cta_label,
            'primaryCtaUrl' => $primary_cta_url,
            'primary_cta_url' => $primary_cta_url,
            'secondaryCtaLabel' => $secondary_cta_label,
            'secondary_cta_label' => $secondary_cta_label,
            'secondaryCtaUrl' => $secondary_cta_url,
            'secondary_cta_url' => $secondary_cta_url,
        ];
    }

    private static function buildContentFeedItemPayload(array $item) {
        $title = self::normalizeText($item['title'] ?? '');
        $excerpt = self::normalizeText($item['excerpt'] ?? ($item['text'] ?? ''));
        $image_alt = self::normalizeText($item['imageAlt'] ?? ($item['alt'] ?? ''));
        $category_url = self::normalizeUrl($item['categoryUrl'] ?? ($item['category_url'] ?? ''));
        $price = self::normalizeText($item['price'] ?? '');
        $price_old = self::normalizeText($item['priceOld'] ?? ($item['price_old'] ?? ''));
        $currency = self::normalizeText($item['currency'] ?? '');
        $badge = self::normalizeText($item['badge'] ?? '');
        $cta_label = self::normalizeText($item['ctaLabel'] ?? ($item['cta_label'] ?? ''));
        $cta_url = self::normalizeUrl($item['ctaUrl'] ?? ($item['cta_url'] ?? ''));
        $availability = self::normalizeText($item['availability'] ?? '');
        $tags = self::normalizeTags($item['tags'] ?? []);
        $gallery = self::normalizeGallery($item['gallery'] ?? []);

        return [
            'category' => self::normalizeText($item['category'] ?? ''),
            'categoryUrl' => $category_url,
            'category_url' => $category_url,
            'title' => $title,
            'excerpt' => $excerpt,
            'text' => $excerpt,
            'url' => self::normalizeUrl($item['url'] ?? ''),
            'image' => self::normalizeImageUrl($item['image'] ?? ''),
            'imageAlt' => $image_alt,
            'alt' => $image_alt,
            'date' => self::normalizeText($item['date'] ?? ''),
            'views' => self::normalizeText($item['views'] ?? ''),
            'comments' => self::normalizeText($item['comments'] ?? ''),
            'price' => $price,
            'priceOld' => $price_old,
            'price_old' => $price_old,
            'currency' => $currency,
            'badge' => $badge,
            'ctaLabel' => $cta_label,
            'cta_label' => $cta_label,
            'ctaUrl' => $cta_url,
            'cta_url' => $cta_url,
            'availability' => $availability,
            'tags' => $tags,
            'gallery' => $gallery,
        ];
    }

    private static function normalizeTags($value) {
        if (is_string($value)) {
            $parts = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
            return array_values(array_map([__CLASS__, 'normalizeText'], is_array($parts) ? $parts : []));
        }

        if (!is_array($value)) {
            return [];
        }

        $tags = [];
        foreach ($value as $tag) {
            $tag = self::normalizeText($tag);
            if ($tag !== '') {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    private static function normalizeGallery($value) {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            } else {
                return [];
            }
        }

        if (!is_array($value)) {
            return [];
        }

        $gallery = [];
        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }

            $src = self::normalizeImageUrl($item['src'] ?? ($item['image'] ?? ''));
            if ($src === '') {
                continue;
            }

            $gallery[] = [
                'src' => $src,
                'alt' => self::normalizeText($item['alt'] ?? ''),
                'type' => self::normalizeText($item['type'] ?? 'image'),
                'caption' => self::normalizeText($item['caption'] ?? ''),
            ];
        }

        return $gallery;
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

        $legacy_button_map = [
            'primaryButtonUrl' => 'primaryButton',
            'secondaryButtonUrl' => 'secondaryButton',
            'tertiaryButtonUrl' => 'tertiaryButton',
        ];

        if (isset($legacy_button_map[$key])) {
            $button_key = $legacy_button_map[$key];
            if (isset($bindings[$button_key]['url']) && is_array($bindings[$button_key]['url'])) {
                return $bindings[$button_key]['url'];
            }
        }

        return [];
    }

    private static function getManagedContentItemSlotDefinitions($block_type, array $contract) {
        if (!NordicblocksManagedScaffoldRegistry::supportsContentItem($block_type)) {
            return [];
        }

        $entities = array_keys((array) ($contract['entities'] ?? []));
        $definitions = [];

        foreach (['eyebrow', 'title', 'subtitle', 'body'] as $key) {
            if (in_array($key, $entities, true)) {
                $definitions[$key] = ['path' => $key];
            }
        }

        if (in_array('media', $entities, true)) {
            $definitions['image'] = ['path' => 'media.image'];
            $definitions['imageAlt'] = ['path' => 'media.alt'];
        }

        if (in_array('meta', $entities, true)) {
            $definitions['category'] = ['path' => 'meta.category'];
            $definitions['author'] = ['path' => 'meta.author'];
            $definitions['date'] = ['path' => 'meta.date'];
            $definitions['views'] = ['path' => 'meta.views'];
            $definitions['comments'] = ['path' => 'meta.comments'];
        }

        if (in_array('primaryButton', $entities, true)) {
            $definitions['primaryButtonUrl'] = ['path' => 'primaryButton.url'];
        }

        if (in_array('secondaryButton', $entities, true)) {
            $definitions['secondaryButtonUrl'] = ['path' => 'secondaryButton.url'];
        }

        if (in_array('tertiaryButton', $entities, true)) {
            $definitions['tertiaryButtonUrl'] = ['path' => 'tertiaryButton.url'];
        }

        return $definitions;
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
        $model = cmsCore::getModel('nordicblocks');
        if ($model && method_exists($model, 'normalizeImageFieldValue')) {
            $normalized = $model->normalizeImageFieldValue($value);
            if (is_array($normalized)) {
                return trim((string) ($normalized['display'] ?? $normalized['original'] ?? ''));
            }
        }

        if (is_array($value)) {
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