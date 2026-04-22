<?php

require_once dirname(__DIR__) . '/render_helpers.php';

$catalog_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'catalog_browser'))
    ? $block_contract
    : null;

if (!function_exists('nb_catalog_browser_visible')) {
    function nb_catalog_browser_visible($value, $default = true) {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('nb_catalog_browser_prop_int')) {
    function nb_catalog_browser_prop_int(array $props, $key, $default, $min, $max) {
        $value = $props[$key] ?? $default;
        if (!is_numeric($value)) {
            $value = $default;
        }

        $value = (int) round($value);
        if ($value < $min) {
            $value = $min;
        }
        if ($value > $max) {
            $value = $max;
        }

        return $value;
    }
}

if (!function_exists('nb_catalog_browser_prop_value')) {
    function nb_catalog_browser_prop_value(array $props, array $keys, $default = null) {
        foreach ($keys as $key) {
            if ($key !== '' && array_key_exists($key, $props) && $props[$key] !== '' && $props[$key] !== null) {
                return $props[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('nb_catalog_browser_entity_value')) {
    function nb_catalog_browser_entity_value(array $entity, $branch, $key, $default = null) {
        if (isset($entity[$branch]) && is_array($entity[$branch]) && array_key_exists($key, $entity[$branch]) && $entity[$branch][$key] !== '' && $entity[$branch][$key] !== null) {
            return $entity[$branch][$key];
        }

        if (array_key_exists($key, $entity) && $entity[$key] !== '' && $entity[$key] !== null) {
            return $entity[$key];
        }

        return $default;
    }
}

if (!function_exists('nb_catalog_browser_shadow_css')) {
    function nb_catalog_browser_shadow_css($token, $default = 'md') {
        $token = in_array($token, ['none', 'sm', 'md', 'lg'], true) ? $token : $default;
        return $token === 'none' ? 'none' : 'var(--nb-shadow-' . $token . ', none)';
    }
}

if (!function_exists('nb_catalog_browser_normalize_price')) {
    function nb_catalog_browser_normalize_price($price, $currency) {
        $value = trim((string) $price);
        if ($value === '' || $value === '0') {
            return '';
        }

        $suffix = trim((string) $currency);
        return trim($value . ($suffix !== '' ? ' ' . $suffix : ''));
    }
}

if (!function_exists('nb_catalog_browser_price_value')) {
    function nb_catalog_browser_price_value($price) {
        $value = preg_replace('/[^0-9,\.]/', '', (string) $price);
        if ($value === null || $value === '') {
            return 0;
        }

        $value = str_replace(',', '.', $value);
        return (float) $value;
    }
}

if (!function_exists('nb_catalog_browser_normalize_gallery')) {
    function nb_catalog_browser_normalize_gallery($gallery, $fallback_image, $fallback_alt) {
        if (is_string($gallery) && $gallery !== '') {
            $decoded = json_decode($gallery, true);
            if (is_array($decoded)) {
                $gallery = $decoded;
            }
        }

        if (!is_array($gallery)) {
            $gallery = [];
        }

        $slides = [];
        foreach ($gallery as $entry) {
            if (is_string($entry)) {
                $media = nb_block_extract_media($entry, $fallback_alt);
                $src = (string) ($media['display'] ?: $media['original']);
                if ($src === '') {
                    continue;
                }

                $slides[] = [
                    'src' => $src,
                    'alt' => (string) ($media['alt'] ?: $fallback_alt),
                    'caption' => '',
                ];
                continue;
            }

            if (!is_array($entry)) {
                continue;
            }

            $media = nb_block_extract_media($entry['src'] ?? ($entry['image'] ?? ''), $entry['alt'] ?? $fallback_alt);
            $src = (string) ($media['display'] ?: $media['original']);
            if ($src === '') {
                continue;
            }

            $slides[] = [
                'src' => $src,
                'alt' => (string) ($media['alt'] ?: $fallback_alt),
                'caption' => trim((string) ($entry['caption'] ?? '')),
            ];
        }

        if (!$slides && $fallback_image !== '') {
            $slides[] = [
                'src' => $fallback_image,
                'alt' => $fallback_alt,
                'caption' => '',
            ];
        }

        return $slides;
    }
}

if (!function_exists('nb_catalog_browser_build_search_text')) {
    function nb_catalog_browser_build_search_text(array $item, array $search_fields) {
        $index = (isset($item['searchIndex']) && is_array($item['searchIndex'])) ? $item['searchIndex'] : [];
        $parts = [];

        foreach ($search_fields as $field => $enabled) {
            if (!$enabled) {
                continue;
            }

            $value = trim((string) ($index[$field] ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return trim(implode(' ', $parts));
    }
}

if (!function_exists('nb_catalog_browser_availability_label')) {
    function nb_catalog_browser_availability_label($value) {
        $value = trim((string) $value);
        $map = [
            'available' => 'Доступно',
            'limited' => 'Ограничено',
            'on_request' => 'По запросу',
            'hidden' => '',
        ];

        if (array_key_exists($value, $map)) {
            return $map[$value];
        }

        return $value;
    }
}

if (!function_exists('nb_catalog_browser_build_cta_payload')) {
    function nb_catalog_browser_build_cta_payload($cta_kind, $cta_url, $fallback_url, $messenger_type = 'none') {
        $kind = trim((string) $cta_kind);
        $kind = in_array($kind, ['url', 'whatsapp', 'telegram', 'phone', 'none'], true) ? $kind : 'url';
        $value = trim((string) $cta_url);
        $fallback = trim((string) $fallback_url);
        $messenger_type = trim((string) $messenger_type);

        if ($kind === 'url' && $value === '' && in_array($messenger_type, ['whatsapp', 'telegram'], true)) {
            $kind = $messenger_type;
        }

        if ($kind === 'none') {
            return ['href' => '', 'target' => '', 'rel' => '', 'kind' => $kind];
        }

        if ($kind === 'url') {
            $href = $value !== '' ? $value : $fallback;
            return nb_catalog_browser_harden_link_payload($href, '', '', $kind);
        }

        if ($kind === 'phone') {
            $phone = preg_replace('/[^0-9\+]/', '', $value !== '' ? $value : $fallback);
            return ['href' => $phone !== '' ? 'tel:' . $phone : '', 'target' => '', 'rel' => '', 'kind' => $kind];
        }

        if ($kind === 'whatsapp') {
            $phone = preg_replace('/[^0-9]/', '', $value !== '' ? $value : $fallback);
            $href = '';
            if ($phone !== '') {
                $href = 'https://wa.me/' . $phone;
            } elseif (preg_match('~^https?://~i', $value)) {
                $href = $value;
            }

            return ['href' => $href, 'target' => '_blank', 'rel' => 'noopener noreferrer', 'kind' => $kind];
        }

        if ($kind === 'telegram') {
            $href = '';
            $candidate = $value !== '' ? $value : $fallback;
            if (preg_match('~^https?://~i', $candidate)) {
                $href = $candidate;
            } else {
                $handle = ltrim($candidate, '@/');
                if ($handle !== '') {
                    $href = 'https://t.me/' . $handle;
                }
            }

            return ['href' => $href, 'target' => '_blank', 'rel' => 'noopener noreferrer', 'kind' => $kind];
        }

        return ['href' => '', 'target' => '', 'rel' => '', 'kind' => 'none'];
    }
}

if (!function_exists('nb_catalog_browser_harden_link_payload')) {
    function nb_catalog_browser_harden_link_payload($href, $target = '', $rel = '', $kind = 'url') {
        $href = trim((string) $href);
        $target = trim((string) $target);
        $rel = trim((string) $rel);
        $kind = trim((string) $kind);

        if ($href === '') {
            return ['href' => '', 'target' => '', 'rel' => '', 'kind' => $kind !== '' ? $kind : 'url'];
        }

        $host = strtolower((string) parse_url($href, PHP_URL_HOST));
        $site_host = strtolower((string) parse_url((string) cmsConfig::getInstance()->host, PHP_URL_HOST));
        $is_http = preg_match('~^(https?:)?//~i', $href) === 1;
        $is_external = $is_http && $host !== '' && $site_host !== ''
            && $host !== $site_host
            && $host !== 'www.' . $site_host
            && $site_host !== 'www.' . $host;

        if ($is_external && $target === '') {
            $target = '_blank';
        }

        if ($target === '_blank') {
            $rel_parts = preg_split('/\s+/', $rel, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach (['noopener', 'noreferrer'] as $required_rel) {
                if (!in_array($required_rel, $rel_parts, true)) {
                    $rel_parts[] = $required_rel;
                }
            }
            $rel = trim(implode(' ', $rel_parts));
        }

        return ['href' => $href, 'target' => $target, 'rel' => $rel, 'kind' => $kind !== '' ? $kind : 'url'];
    }
}

if (!function_exists('nb_catalog_browser_render_debug_version')) {
    function nb_catalog_browser_render_debug_version() {
        static $version = null;

        if ($version !== null) {
            return $version;
        }

        $parts = [
            'helpers:' . (@filemtime(dirname(__DIR__) . '/render_helpers.php') ?: 0),
            'catalog_browser:' . (@filemtime(__FILE__) ?: 0),
        ];

        $version = substr(md5(implode('|', $parts)), 0, 16);
        return $version;
    }
}

if (!function_exists('nb_catalog_browser_normalize_item')) {
    function nb_catalog_browser_normalize_item(array $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $excerpt = trim((string) ($item['excerpt'] ?? ($item['text'] ?? '')));
        $category = trim((string) ($item['category'] ?? ''));
        $category_url = trim((string) ($item['categoryUrl'] ?? ($item['category_url'] ?? '')));
        $url = trim((string) ($item['url'] ?? ''));
        $cta_label = trim((string) ($item['ctaLabel'] ?? ($item['cta_label'] ?? '')));
        $cta_kind = trim((string) ($item['ctaKind'] ?? ($item['cta_kind'] ?? 'url')));
        $cta_url = trim((string) ($item['ctaUrl'] ?? ($item['cta_url'] ?? '')));
        $messenger_type = trim((string) ($item['messengerType'] ?? ($item['messenger_type'] ?? 'none')));
        $price = trim((string) ($item['price'] ?? ''));
        $price_old = trim((string) ($item['priceOld'] ?? ($item['price_old'] ?? '')));
        $currency = trim((string) ($item['currency'] ?? ''));
        $badge = trim((string) ($item['badge'] ?? ''));
        $availability_value = trim((string) ($item['availability'] ?? ''));
        $availability = nb_catalog_browser_availability_label($availability_value);
        $tags_value = $item['tags'] ?? '';
        if (is_array($tags_value)) {
            $tags_value = implode(', ', array_filter(array_map('trim', $tags_value)));
        }
        $tags = trim((string) $tags_value);
        $media = nb_block_extract_media($item['image'] ?? '', $item['imageAlt'] ?? ($item['alt'] ?? $title));
        $image_src = (string) ($media['display'] ?: $media['original']);
        $image_alt = (string) ($media['alt'] ?: $title);
        $gallery = nb_catalog_browser_normalize_gallery($item['gallery'] ?? [], $image_src, $image_alt);
        $cta = nb_catalog_browser_build_cta_payload($cta_kind, $cta_url, $url, $messenger_type);

        if ($title === '' && $excerpt === '' && $category === '' && $badge === '' && $price === '' && $image_src === '') {
            return null;
        }

        $normalized_price = nb_catalog_browser_normalize_price($price, $currency);

        return [
            'category' => htmlspecialchars($category, ENT_QUOTES, 'UTF-8'),
            'categoryUrl' => htmlspecialchars($category_url, ENT_QUOTES, 'UTF-8'),
            'categoryValue' => $category,
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'excerpt' => nl2br(htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8')),
            'url' => htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            'ctaLabel' => htmlspecialchars($cta_label, ENT_QUOTES, 'UTF-8'),
            'ctaHref' => htmlspecialchars((string) ($cta['href'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'ctaTarget' => htmlspecialchars((string) ($cta['target'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'ctaRel' => htmlspecialchars((string) ($cta['rel'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'price' => htmlspecialchars($normalized_price, ENT_QUOTES, 'UTF-8'),
            'priceOld' => htmlspecialchars(nb_catalog_browser_normalize_price($price_old, $currency), ENT_QUOTES, 'UTF-8'),
            'priceValue' => nb_catalog_browser_price_value($price),
            'badge' => htmlspecialchars($badge, ENT_QUOTES, 'UTF-8'),
            'availability' => htmlspecialchars($availability, ENT_QUOTES, 'UTF-8'),
            'image' => htmlspecialchars($image_src, ENT_QUOTES, 'UTF-8'),
            'imageAlt' => htmlspecialchars($image_alt, ENT_QUOTES, 'UTF-8'),
            'gallery' => $gallery,
            'searchIndex' => [
                'title' => mb_strtolower($title, 'UTF-8'),
                'excerpt' => mb_strtolower($excerpt, 'UTF-8'),
                'category' => mb_strtolower($category, 'UTF-8'),
                'badge' => mb_strtolower($badge, 'UTF-8'),
                'tags' => mb_strtolower($tags, 'UTF-8'),
                'price' => mb_strtolower($normalized_price, 'UTF-8'),
                'availability' => mb_strtolower($availability, 'UTF-8'),
            ],
        ];
    }
}

$props = (array) ($props ?? []);

if ($catalog_contract) {
    $theme = in_array($catalog_contract['design']['section']['theme'] ?? 'light', ['light', 'alt', 'dark'], true)
        ? (string) ($catalog_contract['design']['section']['theme'] ?? 'light')
        : 'light';
    $align = in_array($catalog_contract['layout']['desktop']['align'] ?? 'left', ['left', 'center'], true)
        ? (string) ($catalog_contract['layout']['desktop']['align'] ?? 'left')
        : 'left';
    $background_mode = (string) ($catalog_contract['design']['section']['background']['mode'] ?? 'theme');
    $background_style = nb_block_build_background_style((array) ($catalog_contract['design']['section']['background'] ?? []));
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($catalog_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($catalog_contract['runtime']['animation']['delay'] ?? 0),
    ]);

    $heading = trim((string) ($catalog_contract['content']['title'] ?? 'Каталог'));
    $intro = trim((string) ($catalog_contract['content']['subtitle'] ?? ''));
    $section_link_label = trim((string) ($catalog_contract['content']['primaryButton']['label'] ?? ''));
    $section_link_url = trim((string) ($catalog_contract['content']['primaryButton']['url'] ?? ''));
    $items_source = is_array($catalog_contract['content']['items'] ?? null) ? $catalog_contract['content']['items'] : [];

    $title_visible = !array_key_exists('visible', (array) ($catalog_contract['design']['entities']['title'] ?? [])) || !empty($catalog_contract['design']['entities']['title']['visible']);
    $subtitle_visible = !array_key_exists('visible', (array) ($catalog_contract['design']['entities']['subtitle'] ?? [])) || !empty($catalog_contract['design']['entities']['subtitle']['visible']);
    $show_section_link = !array_key_exists('moreLink', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['moreLink']);
    $show_search = !array_key_exists('search', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['search']);
    $show_category_filter = !array_key_exists('categoryFilter', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['categoryFilter']);
    $show_price_filter = !array_key_exists('priceFilter', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['priceFilter']);
    $show_sort = !array_key_exists('sort', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['sort']);
    $show_active_filters = !array_key_exists('activeFilters', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['activeFilters']);
    $show_image = !array_key_exists('image', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['image']);
    $show_category = !array_key_exists('category', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['category']);
    $show_badge = !array_key_exists('badge', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['badge']);
    $show_price = !array_key_exists('price', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['price']);
    $show_old_price = !array_key_exists('oldPrice', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['oldPrice']);
    $show_excerpt = !array_key_exists('excerpt', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['excerpt']);
    $show_cta = !array_key_exists('cta', (array) ($catalog_contract['runtime']['visibility'] ?? [])) || !empty($catalog_contract['runtime']['visibility']['cta']);

    $catalog_runtime = isset($catalog_contract['runtime']['catalog']) && is_array($catalog_contract['runtime']['catalog'])
        ? $catalog_contract['runtime']['catalog']
        : [];
    $collection_mode = in_array($catalog_runtime['collectionMode'] ?? 'all', ['all', 'load_more', 'pagination'], true)
        ? (string) ($catalog_runtime['collectionMode'] ?? 'all')
        : 'all';
    $items_per_page = max(1, min(48, (int) ($catalog_runtime['itemsPerPage'] ?? 6)));
    $show_results_count = !array_key_exists('showResultsCount', $catalog_runtime) || !empty($catalog_runtime['showResultsCount']);
    $catalog_search_fields = isset($catalog_runtime['searchFields']) && is_array($catalog_runtime['searchFields'])
        ? $catalog_runtime['searchFields']
        : [];
    $search_fields = [
        'title' => !array_key_exists('title', $catalog_search_fields) || !empty($catalog_search_fields['title']),
        'excerpt' => !array_key_exists('excerpt', $catalog_search_fields) || !empty($catalog_search_fields['excerpt']),
        'category' => !array_key_exists('category', $catalog_search_fields) || !empty($catalog_search_fields['category']),
        'badge' => !array_key_exists('badge', $catalog_search_fields) || !empty($catalog_search_fields['badge']),
        'tags' => !array_key_exists('tags', $catalog_search_fields) || !empty($catalog_search_fields['tags']),
        'price' => !empty($catalog_search_fields['price']),
        'availability' => !array_key_exists('availability', $catalog_search_fields) || !empty($catalog_search_fields['availability']),
    ];

    $title_entity = (array) ($catalog_contract['design']['entities']['title'] ?? []);
    $subtitle_entity = (array) ($catalog_contract['design']['entities']['subtitle'] ?? []);
    $meta_entity = (array) ($catalog_contract['design']['entities']['meta'] ?? []);
    $buttons_text_entity = (array) ($catalog_contract['design']['entities']['buttonsText'] ?? []);
    $item_title_entity = (array) ($catalog_contract['design']['entities']['itemTitle'] ?? []);
    $item_text_entity = (array) ($catalog_contract['design']['entities']['itemText'] ?? []);
    $media_entity = (array) ($catalog_contract['design']['entities']['media'] ?? []);
    $media_surface_entity = (array) ($catalog_contract['design']['entities']['mediaSurface'] ?? []);
    $item_surface_entity = (array) ($catalog_contract['design']['entities']['itemSurface'] ?? []);
    $primary_button_entity = (array) ($catalog_contract['design']['entities']['primaryButton'] ?? []);
    $modal_surface_entity = (array) ($catalog_contract['design']['entities']['mediaModal'] ?? []);

    $heading_tag = in_array((string) ($title_entity['tag'] ?? 'h2'), ['div', 'h1', 'h2', 'h3'], true)
        ? (string) ($title_entity['tag'] ?? 'h2')
        : 'h2';
    $title_weight_desktop = (int) nb_catalog_browser_entity_value($title_entity, 'desktop', 'weight', 800);
    $title_weight_mobile = (int) nb_catalog_browser_entity_value($title_entity, 'mobile', 'weight', $title_weight_desktop);
    $title_size_desktop = (int) nb_catalog_browser_entity_value($title_entity, 'desktop', 'fontSize', 34);
    $title_size_mobile = (int) nb_catalog_browser_entity_value($title_entity, 'mobile', 'fontSize', 26);
    $title_margin_bottom_desktop = (int) nb_catalog_browser_entity_value($title_entity, 'desktop', 'marginBottom', 0);
    $title_margin_bottom_mobile = (int) nb_catalog_browser_entity_value($title_entity, 'mobile', 'marginBottom', 0);
    $title_color_desktop = nb_block_css_color((string) nb_catalog_browser_entity_value($title_entity, 'desktop', 'color', ''));
    $title_color_mobile = nb_block_css_color((string) nb_catalog_browser_entity_value($title_entity, 'mobile', 'color', $title_color_desktop));
    $title_line_height_desktop = ((float) nb_catalog_browser_entity_value($title_entity, 'desktop', 'lineHeightPercent', 112)) / 100;
    $title_line_height_mobile = ((float) nb_catalog_browser_entity_value($title_entity, 'mobile', 'lineHeightPercent', 112)) / 100;
    $title_letter_spacing_desktop = (float) nb_catalog_browser_entity_value($title_entity, 'desktop', 'letterSpacing', 0);
    $title_letter_spacing_mobile = (float) nb_catalog_browser_entity_value($title_entity, 'mobile', 'letterSpacing', $title_letter_spacing_desktop);
    $title_max_width_desktop = (int) nb_catalog_browser_entity_value($title_entity, 'desktop', 'maxWidth', 760);
    $title_max_width_mobile = (int) nb_catalog_browser_entity_value($title_entity, 'mobile', 'maxWidth', $title_max_width_desktop);

    $subtitle_weight_desktop = (int) nb_catalog_browser_entity_value($subtitle_entity, 'desktop', 'weight', 400);
    $subtitle_weight_mobile = (int) nb_catalog_browser_entity_value($subtitle_entity, 'mobile', 'weight', $subtitle_weight_desktop);
    $subtitle_size_desktop = (int) nb_catalog_browser_entity_value($subtitle_entity, 'desktop', 'fontSize', 16);
    $subtitle_size_mobile = (int) nb_catalog_browser_entity_value($subtitle_entity, 'mobile', 'fontSize', 14);
    $subtitle_margin_bottom_desktop = (int) nb_catalog_browser_entity_value($subtitle_entity, 'desktop', 'marginBottom', 0);
    $subtitle_margin_bottom_mobile = (int) nb_catalog_browser_entity_value($subtitle_entity, 'mobile', 'marginBottom', 0);
    $subtitle_color_desktop = nb_block_css_color((string) nb_catalog_browser_entity_value($subtitle_entity, 'desktop', 'color', ''));
    $subtitle_color_mobile = nb_block_css_color((string) nb_catalog_browser_entity_value($subtitle_entity, 'mobile', 'color', $subtitle_color_desktop));
    $subtitle_line_height_desktop = ((float) nb_catalog_browser_entity_value($subtitle_entity, 'desktop', 'lineHeightPercent', 160)) / 100;
    $subtitle_line_height_mobile = ((float) nb_catalog_browser_entity_value($subtitle_entity, 'mobile', 'lineHeightPercent', 160)) / 100;
    $subtitle_letter_spacing_desktop = (float) nb_catalog_browser_entity_value($subtitle_entity, 'desktop', 'letterSpacing', 0);
    $subtitle_letter_spacing_mobile = (float) nb_catalog_browser_entity_value($subtitle_entity, 'mobile', 'letterSpacing', $subtitle_letter_spacing_desktop);
    $subtitle_max_width_desktop = (int) nb_catalog_browser_entity_value($subtitle_entity, 'desktop', 'maxWidth', 720);
    $subtitle_max_width_mobile = (int) nb_catalog_browser_entity_value($subtitle_entity, 'mobile', 'maxWidth', $subtitle_max_width_desktop);

    $meta_size_desktop = (int) nb_catalog_browser_entity_value($meta_entity, 'desktop', 'fontSize', 12);
    $meta_size_mobile = (int) nb_catalog_browser_entity_value($meta_entity, 'mobile', 'fontSize', 11);
    $meta_weight_desktop = (int) nb_catalog_browser_entity_value($meta_entity, 'desktop', 'weight', 600);
    $meta_weight_mobile = (int) nb_catalog_browser_entity_value($meta_entity, 'mobile', 'weight', $meta_weight_desktop);
    $meta_color_desktop = nb_block_css_color((string) nb_catalog_browser_entity_value($meta_entity, 'desktop', 'color', ''));
    $meta_color_mobile = nb_block_css_color((string) nb_catalog_browser_entity_value($meta_entity, 'mobile', 'color', $meta_color_desktop));
    $meta_line_height_desktop = ((float) nb_catalog_browser_entity_value($meta_entity, 'desktop', 'lineHeightPercent', 140)) / 100;
    $meta_line_height_mobile = ((float) nb_catalog_browser_entity_value($meta_entity, 'mobile', 'lineHeightPercent', 140)) / 100;
    $meta_letter_spacing_desktop = (float) nb_catalog_browser_entity_value($meta_entity, 'desktop', 'letterSpacing', 0);
    $meta_letter_spacing_mobile = (float) nb_catalog_browser_entity_value($meta_entity, 'mobile', 'letterSpacing', $meta_letter_spacing_desktop);

    $buttons_text_size_desktop = (int) nb_catalog_browser_entity_value($buttons_text_entity, 'desktop', 'fontSize', 15);
    $buttons_text_size_mobile = (int) nb_catalog_browser_entity_value($buttons_text_entity, 'mobile', 'fontSize', 14);
    $buttons_text_weight_desktop = (int) nb_catalog_browser_entity_value($buttons_text_entity, 'desktop', 'weight', 700);
    $buttons_text_weight_mobile = (int) nb_catalog_browser_entity_value($buttons_text_entity, 'mobile', 'weight', $buttons_text_weight_desktop);
    $buttons_text_color_desktop = nb_block_css_color((string) nb_catalog_browser_entity_value($buttons_text_entity, 'desktop', 'color', ''));
    $buttons_text_color_mobile = nb_block_css_color((string) nb_catalog_browser_entity_value($buttons_text_entity, 'mobile', 'color', $buttons_text_color_desktop));
    $buttons_text_line_height_desktop = ((float) nb_catalog_browser_entity_value($buttons_text_entity, 'desktop', 'lineHeightPercent', 120)) / 100;
    $buttons_text_line_height_mobile = ((float) nb_catalog_browser_entity_value($buttons_text_entity, 'mobile', 'lineHeightPercent', 120)) / 100;
    $buttons_text_letter_spacing_desktop = (float) nb_catalog_browser_entity_value($buttons_text_entity, 'desktop', 'letterSpacing', 0);
    $buttons_text_letter_spacing_mobile = (float) nb_catalog_browser_entity_value($buttons_text_entity, 'mobile', 'letterSpacing', $buttons_text_letter_spacing_desktop);
    $button_style = in_array($primary_button_entity['style'] ?? 'primary', ['primary', 'outline', 'ghost'], true)
        ? (string) ($primary_button_entity['style'] ?? 'primary')
        : 'primary';

    $item_title_size_desktop = (int) nb_catalog_browser_entity_value($item_title_entity, 'desktop', 'fontSize', 20);
    $item_title_size_mobile = (int) nb_catalog_browser_entity_value($item_title_entity, 'mobile', 'fontSize', 18);
    $item_title_weight_desktop = (int) nb_catalog_browser_entity_value($item_title_entity, 'desktop', 'weight', 800);
    $item_title_weight_mobile = (int) nb_catalog_browser_entity_value($item_title_entity, 'mobile', 'weight', $item_title_weight_desktop);
    $item_title_color_desktop = nb_block_css_color((string) nb_catalog_browser_entity_value($item_title_entity, 'desktop', 'color', ''));
    $item_title_color_mobile = nb_block_css_color((string) nb_catalog_browser_entity_value($item_title_entity, 'mobile', 'color', $item_title_color_desktop));
    $item_title_line_height_desktop = ((float) nb_catalog_browser_entity_value($item_title_entity, 'desktop', 'lineHeightPercent', 130)) / 100;
    $item_title_line_height_mobile = ((float) nb_catalog_browser_entity_value($item_title_entity, 'mobile', 'lineHeightPercent', 130)) / 100;
    $item_title_letter_spacing_desktop = (float) nb_catalog_browser_entity_value($item_title_entity, 'desktop', 'letterSpacing', 0);
    $item_title_letter_spacing_mobile = (float) nb_catalog_browser_entity_value($item_title_entity, 'mobile', 'letterSpacing', $item_title_letter_spacing_desktop);

    $item_text_size_desktop = (int) nb_catalog_browser_entity_value($item_text_entity, 'desktop', 'fontSize', 14);
    $item_text_size_mobile = (int) nb_catalog_browser_entity_value($item_text_entity, 'mobile', 'fontSize', 13);
    $item_text_weight_desktop = (int) nb_catalog_browser_entity_value($item_text_entity, 'desktop', 'weight', 400);
    $item_text_weight_mobile = (int) nb_catalog_browser_entity_value($item_text_entity, 'mobile', 'weight', $item_text_weight_desktop);
    $item_text_color_desktop = nb_block_css_color((string) nb_catalog_browser_entity_value($item_text_entity, 'desktop', 'color', ''));
    $item_text_color_mobile = nb_block_css_color((string) nb_catalog_browser_entity_value($item_text_entity, 'mobile', 'color', $item_text_color_desktop));
    $item_text_line_height_desktop = ((float) nb_catalog_browser_entity_value($item_text_entity, 'desktop', 'lineHeightPercent', 160)) / 100;
    $item_text_line_height_mobile = ((float) nb_catalog_browser_entity_value($item_text_entity, 'mobile', 'lineHeightPercent', 160)) / 100;
    $item_text_letter_spacing_desktop = (float) nb_catalog_browser_entity_value($item_text_entity, 'desktop', 'letterSpacing', 0);
    $item_text_letter_spacing_mobile = (float) nb_catalog_browser_entity_value($item_text_entity, 'mobile', 'letterSpacing', $item_text_letter_spacing_desktop);
    $toolbar_entity = isset($catalog_contract['design']['entities']['toolbar']) && is_array($catalog_contract['design']['entities']['toolbar'])
        ? $catalog_contract['design']['entities']['toolbar']
        : [];
    $toolbar_controls_entity = isset($catalog_contract['design']['entities']['toolbarControls']) && is_array($catalog_contract['design']['entities']['toolbarControls'])
        ? $catalog_contract['design']['entities']['toolbarControls']
        : [];
    $card_price_entity = isset($catalog_contract['design']['entities']['cardPrice']) && is_array($catalog_contract['design']['entities']['cardPrice'])
        ? $catalog_contract['design']['entities']['cardPrice']
        : [];
    $toolbar_background_mode = (string) ($toolbar_entity['backgroundMode'] ?? 'solid');
    $toolbar_background_color = nb_block_css_color((string) ($toolbar_entity['backgroundColor'] ?? ''), '');
    $toolbar_padding = (int) ($toolbar_entity['padding'] ?? 16);
    $toolbar_radius = (int) ($toolbar_entity['radius'] ?? 22);
    $toolbar_border_width = (int) ($toolbar_entity['borderWidth'] ?? 1);
    $toolbar_border_color = nb_block_css_color((string) ($toolbar_entity['borderColor'] ?? ''), '');
    $toolbar_shadow_css = nb_catalog_browser_shadow_css((string) ($toolbar_entity['shadow'] ?? 'sm'), 'sm');
    $toolbar_controls_background_mode = (string) ($toolbar_controls_entity['backgroundMode'] ?? 'solid');
    $toolbar_controls_background_color = nb_block_css_color((string) ($toolbar_controls_entity['backgroundColor'] ?? ''), '');
    $toolbar_controls_radius = (int) ($toolbar_controls_entity['radius'] ?? 16);
    $toolbar_controls_border_width = (int) ($toolbar_controls_entity['borderWidth'] ?? 1);
    $toolbar_controls_border_color = nb_block_css_color((string) ($toolbar_controls_entity['borderColor'] ?? ''), '');
    $toolbar_controls_shadow_css = nb_catalog_browser_shadow_css((string) ($toolbar_controls_entity['shadow'] ?? 'none'), 'none');
    $card_price_size_desktop = (int) nb_catalog_browser_entity_value($card_price_entity, 'desktop', 'fontSize', 19);
    $card_price_size_mobile = (int) nb_catalog_browser_entity_value($card_price_entity, 'mobile', 'fontSize', 17);
    $card_price_weight_desktop = (int) nb_catalog_browser_entity_value($card_price_entity, 'desktop', 'weight', 800);
    $card_price_weight_mobile = (int) nb_catalog_browser_entity_value($card_price_entity, 'mobile', 'weight', $card_price_weight_desktop);
    $card_price_color_desktop = nb_block_css_color((string) nb_catalog_browser_entity_value($card_price_entity, 'desktop', 'color', ''), '');
    $card_price_color_mobile = nb_block_css_color((string) nb_catalog_browser_entity_value($card_price_entity, 'mobile', 'color', $card_price_color_desktop), '');
    $card_price_line_height_desktop = ((float) nb_catalog_browser_entity_value($card_price_entity, 'desktop', 'lineHeightPercent', 120)) / 100;
    $card_price_line_height_mobile = ((float) nb_catalog_browser_entity_value($card_price_entity, 'mobile', 'lineHeightPercent', 120)) / 100;
    $card_price_letter_spacing_desktop = (float) nb_catalog_browser_entity_value($card_price_entity, 'desktop', 'letterSpacing', 0);
    $card_price_letter_spacing_mobile = (float) nb_catalog_browser_entity_value($card_price_entity, 'mobile', 'letterSpacing', $card_price_letter_spacing_desktop);

    $media_aspect_ratio = in_array($media_entity['aspectRatio'] ?? '4:3', ['auto', '16:10', '16:9', '4:3', '1:1', '3:4'], true)
        ? (string) ($media_entity['aspectRatio'] ?? '4:3')
        : '4:3';
    $media_object_fit = in_array($media_entity['objectFit'] ?? 'cover', ['cover', 'contain'], true)
        ? (string) ($media_entity['objectFit'] ?? 'cover')
        : 'cover';
    $media_radius = (int) ($media_entity['radius'] ?? 20);
    $media_inherit_global = array_key_exists('inheritGlobalStyle', $media_entity)
        ? nb_catalog_browser_visible($media_entity['inheritGlobalStyle'], true)
        : ($media_radius === 20);
    $media_surface_background_mode = (string) ($media_surface_entity['backgroundMode'] ?? 'transparent');
    $media_surface_background_color = nb_block_css_color((string) ($media_surface_entity['backgroundColor'] ?? ''), '');
    $media_surface_padding = (int) ($media_surface_entity['padding'] ?? 0);
    $media_surface_radius = (int) ($media_surface_entity['radius'] ?? $media_radius);
    $media_surface_border_width = (int) ($media_surface_entity['borderWidth'] ?? 0);
    $media_surface_border_color = nb_block_css_color((string) ($media_surface_entity['borderColor'] ?? ''), '');
    $media_surface_shadow_token = (string) ($media_surface_entity['shadow'] ?? 'none');
    $media_surface_shadow_css = nb_catalog_browser_shadow_css($media_surface_shadow_token, 'none');

    $item_surface_variant = in_array($item_surface_entity['variant'] ?? 'card', ['card', 'plain'], true)
        ? (string) ($item_surface_entity['variant'] ?? 'card')
        : 'card';
    $item_surface_radius = (int) ($item_surface_entity['radius'] ?? 22);
    $item_surface_border_width = (int) ($item_surface_entity['borderWidth'] ?? 1);
    $item_surface_border_color = nb_block_css_color((string) ($item_surface_entity['borderColor'] ?? '#dbe4ef'), '#dbe4ef');
    $item_surface_shadow_token = (string) ($item_surface_entity['shadow'] ?? 'md');
    $item_surface_shadow_css = nb_catalog_browser_shadow_css($item_surface_shadow_token, 'md');
    $item_surface_inherit_global = array_key_exists('inheritGlobalStyle', $item_surface_entity)
        ? nb_catalog_browser_visible($item_surface_entity['inheritGlobalStyle'], true)
        : (($item_surface_radius === 22 || $item_surface_radius === 1)
            && $item_surface_border_width === 1
            && strtolower($item_surface_border_color) === '#dbe4ef'
            && $item_surface_shadow_token === 'md');

    $modal_background_mode = (string) ($modal_surface_entity['backgroundMode'] ?? 'solid');
    $modal_background_color = nb_block_css_color((string) ($modal_surface_entity['backgroundColor'] ?? '#0f172a'), '#0f172a');
    $modal_padding = (int) ($modal_surface_entity['padding'] ?? 18);
    $modal_radius = (int) ($modal_surface_entity['radius'] ?? 24);
    $modal_border_width = (int) ($modal_surface_entity['borderWidth'] ?? 0);
    $modal_border_color = nb_block_css_color((string) ($modal_surface_entity['borderColor'] ?? ''), '');
    $modal_shadow_css = nb_catalog_browser_shadow_css((string) ($modal_surface_entity['shadow'] ?? 'lg'), 'lg');
    $modal_overlay_color = nb_block_color_with_opacity($modal_background_color, 0.82, 'rgba(2,6,23,.88)');

    $content_width = (int) ($catalog_contract['layout']['desktop']['contentWidth'] ?? 1180);
    $padding_top_desktop = (int) ($catalog_contract['layout']['desktop']['paddingTop'] ?? 64);
    $padding_bottom_desktop = (int) ($catalog_contract['layout']['desktop']['paddingBottom'] ?? 64);
    $padding_top_mobile = (int) ($catalog_contract['layout']['mobile']['paddingTop'] ?? 44);
    $padding_bottom_mobile = (int) ($catalog_contract['layout']['mobile']['paddingBottom'] ?? 44);
    $columns_desktop = (int) ($catalog_contract['layout']['desktop']['columns'] ?? 3);
    $columns_mobile = (int) ($catalog_contract['layout']['mobile']['columns'] ?? 1);
    $card_gap_desktop = (int) ($catalog_contract['layout']['desktop']['cardGap'] ?? 20);
    $card_gap_mobile = (int) ($catalog_contract['layout']['mobile']['cardGap'] ?? 14);
    $header_gap_desktop = (int) ($catalog_contract['layout']['desktop']['headerGap'] ?? 20);
    $header_gap_mobile = (int) ($catalog_contract['layout']['mobile']['headerGap'] ?? 14);
} else {
    $theme = in_array($props['theme'] ?? 'light', ['light', 'alt', 'dark'], true) ? (string) ($props['theme'] ?? 'light') : 'light';
    $align = in_array($props['align'] ?? 'left', ['left', 'center'], true) ? (string) ($props['align'] ?? 'left') : 'left';
    $background_mode = (string) ($props['background_mode'] ?? 'theme');
    $background_style = nb_block_build_background_style([
        'mode' => $background_mode,
        'color' => $props['background_color'] ?? '',
        'gradientFrom' => $props['background_gradient_from'] ?? '',
        'gradientTo' => $props['background_gradient_to'] ?? '',
        'gradientAngle' => $props['background_gradient_angle'] ?? 135,
        'image' => $props['background_image'] ?? '',
        'imagePosition' => $props['background_image_position'] ?? 'center center',
        'imageSize' => $props['background_image_size'] ?? 'cover',
        'imageRepeat' => $props['background_image_repeat'] ?? 'no-repeat',
        'overlayColor' => $props['background_overlay_color'] ?? '#0f172a',
        'overlayOpacity' => $props['background_overlay_opacity'] ?? 45,
    ]);
    $reveal = nb_block_get_reveal_settings((array) $props);

    $heading = trim((string) ($props['heading'] ?? 'Каталог'));
    $intro = trim((string) ($props['intro'] ?? ''));
    $section_link_label = trim((string) ($props['section_link_label'] ?? ''));
    $section_link_url = trim((string) ($props['section_link_url'] ?? ''));
    $items_source = is_array($props['items'] ?? null) ? $props['items'] : [];

    $title_visible = nb_catalog_browser_visible($props['title_visible'] ?? '1', true);
    $subtitle_visible = nb_catalog_browser_visible($props['subtitle_visible'] ?? '1', true);
    $show_section_link = nb_catalog_browser_visible($props['show_more_link'] ?? '1', true);
    $show_search = nb_catalog_browser_visible($props['show_search'] ?? '1', true);
    $show_category_filter = nb_catalog_browser_visible($props['show_category_filter'] ?? '1', true);
    $show_price_filter = nb_catalog_browser_visible($props['show_price_filter'] ?? '1', true);
    $show_sort = nb_catalog_browser_visible($props['show_sort'] ?? '1', true);
    $show_active_filters = nb_catalog_browser_visible($props['show_active_filters'] ?? '1', true);
    $show_image = nb_catalog_browser_visible($props['show_image'] ?? '1', true);
    $show_category = nb_catalog_browser_visible($props['show_category'] ?? '1', true);
    $show_badge = nb_catalog_browser_visible($props['show_badge'] ?? '1', true);
    $show_price = nb_catalog_browser_visible($props['show_price'] ?? '1', true);
    $show_old_price = nb_catalog_browser_visible($props['show_old_price'] ?? '1', true);
    $show_excerpt = nb_catalog_browser_visible($props['show_excerpt'] ?? '1', true);
    $show_cta = nb_catalog_browser_visible($props['show_cta'] ?? '1', true);

    $collection_mode = in_array($props['collection_mode'] ?? 'all', ['all', 'load_more', 'pagination'], true)
        ? (string) ($props['collection_mode'] ?? 'all')
        : 'all';
    $items_per_page = nb_catalog_browser_prop_int($props, 'items_per_page', 6, 1, 48);
    $show_results_count = nb_catalog_browser_visible($props['show_results_count'] ?? '1', true);
    $search_fields = [
        'title' => nb_catalog_browser_visible($props['search_in_title'] ?? '1', true),
        'excerpt' => nb_catalog_browser_visible($props['search_in_excerpt'] ?? '1', true),
        'category' => nb_catalog_browser_visible($props['search_in_category'] ?? '1', true),
        'badge' => nb_catalog_browser_visible($props['search_in_badge'] ?? '1', true),
        'tags' => nb_catalog_browser_visible($props['search_in_tags'] ?? '1', true),
        'price' => nb_catalog_browser_visible($props['search_in_price'] ?? '0', false),
        'availability' => nb_catalog_browser_visible($props['search_in_availability'] ?? '1', true),
    ];

    $heading_tag = nb_block_get_heading_tag($props, 'heading', 'h2');
    $title_weight_desktop = nb_catalog_browser_prop_int($props, 'title_weight_desktop', 800, 100, 900);
    $title_weight_mobile = nb_catalog_browser_prop_int($props, 'title_weight_mobile', $title_weight_desktop, 100, 900);
    $title_size_desktop = nb_catalog_browser_prop_int($props, 'title_size_desktop', 34, 12, 160);
    $title_size_mobile = nb_catalog_browser_prop_int($props, 'title_size_mobile', 26, 12, 160);
    $title_margin_bottom_desktop = nb_catalog_browser_prop_int($props, 'title_margin_bottom_desktop', 0, 0, 240);
    $title_margin_bottom_mobile = nb_catalog_browser_prop_int($props, 'title_margin_bottom_mobile', 0, 0, 240);
    $title_color_desktop = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['title_color_desktop', 'title_color'], ''));
    $title_color_mobile = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['title_color_mobile'], $title_color_desktop));
    $title_line_height_desktop = nb_catalog_browser_prop_int($props, 'title_line_height_percent_desktop', 112, 80, 220) / 100;
    $title_line_height_mobile = nb_catalog_browser_prop_int($props, 'title_line_height_percent_mobile', 112, 80, 220) / 100;
    $title_letter_spacing_desktop = (float) nb_catalog_browser_prop_value($props, ['title_letter_spacing_desktop', 'title_letter_spacing'], 0);
    $title_letter_spacing_mobile = (float) nb_catalog_browser_prop_value($props, ['title_letter_spacing_mobile'], $title_letter_spacing_desktop);
    $title_max_width_desktop = nb_catalog_browser_prop_int($props, 'title_max_width_desktop', 760, 240, 1440);
    $title_max_width_mobile = nb_catalog_browser_prop_int($props, 'title_max_width_mobile', $title_max_width_desktop, 240, 1440);

    $subtitle_weight_desktop = nb_catalog_browser_prop_int($props, 'subtitle_weight_desktop', 400, 100, 900);
    $subtitle_weight_mobile = nb_catalog_browser_prop_int($props, 'subtitle_weight_mobile', $subtitle_weight_desktop, 100, 900);
    $subtitle_size_desktop = nb_catalog_browser_prop_int($props, 'subtitle_size_desktop', 16, 10, 80);
    $subtitle_size_mobile = nb_catalog_browser_prop_int($props, 'subtitle_size_mobile', 14, 10, 80);
    $subtitle_margin_bottom_desktop = nb_catalog_browser_prop_int($props, 'subtitle_margin_bottom_desktop', 0, 0, 240);
    $subtitle_margin_bottom_mobile = nb_catalog_browser_prop_int($props, 'subtitle_margin_bottom_mobile', 0, 0, 240);
    $subtitle_color_desktop = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['subtitle_color_desktop', 'subtitle_color'], ''));
    $subtitle_color_mobile = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['subtitle_color_mobile'], $subtitle_color_desktop));
    $subtitle_line_height_desktop = nb_catalog_browser_prop_int($props, 'subtitle_line_height_percent_desktop', 160, 80, 240) / 100;
    $subtitle_line_height_mobile = nb_catalog_browser_prop_int($props, 'subtitle_line_height_percent_mobile', 160, 80, 240) / 100;
    $subtitle_letter_spacing_desktop = (float) nb_catalog_browser_prop_value($props, ['subtitle_letter_spacing_desktop', 'subtitle_letter_spacing'], 0);
    $subtitle_letter_spacing_mobile = (float) nb_catalog_browser_prop_value($props, ['subtitle_letter_spacing_mobile'], $subtitle_letter_spacing_desktop);
    $subtitle_max_width_desktop = nb_catalog_browser_prop_int($props, 'subtitle_max_width_desktop', 720, 240, 1440);
    $subtitle_max_width_mobile = nb_catalog_browser_prop_int($props, 'subtitle_max_width_mobile', $subtitle_max_width_desktop, 240, 1440);

    $meta_size_desktop = nb_catalog_browser_prop_int($props, 'meta_size_desktop', 12, 10, 120);
    $meta_size_mobile = nb_catalog_browser_prop_int($props, 'meta_size_mobile', 11, 10, 120);
    $meta_weight_desktop = nb_catalog_browser_prop_int($props, 'meta_weight_desktop', 600, 100, 900);
    $meta_weight_mobile = nb_catalog_browser_prop_int($props, 'meta_weight_mobile', $meta_weight_desktop, 100, 900);
    $meta_color_desktop = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['meta_color_desktop', 'meta_color'], ''));
    $meta_color_mobile = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['meta_color_mobile'], $meta_color_desktop));
    $meta_line_height_desktop = nb_catalog_browser_prop_int($props, 'meta_line_height_percent_desktop', 140, 80, 240) / 100;
    $meta_line_height_mobile = nb_catalog_browser_prop_int($props, 'meta_line_height_percent_mobile', 140, 80, 240) / 100;
    $meta_letter_spacing_desktop = (float) nb_catalog_browser_prop_value($props, ['meta_letter_spacing_desktop', 'meta_letter_spacing'], 0);
    $meta_letter_spacing_mobile = (float) nb_catalog_browser_prop_value($props, ['meta_letter_spacing_mobile'], $meta_letter_spacing_desktop);

    $buttons_text_size_desktop = nb_catalog_browser_prop_int($props, 'button_text_size_desktop', 15, 10, 120);
    $buttons_text_size_mobile = nb_catalog_browser_prop_int($props, 'button_text_size_mobile', 14, 10, 120);
    $buttons_text_weight_desktop = nb_catalog_browser_prop_int($props, 'button_text_weight_desktop', 700, 100, 900);
    $buttons_text_weight_mobile = nb_catalog_browser_prop_int($props, 'button_text_weight_mobile', $buttons_text_weight_desktop, 100, 900);
    $buttons_text_color_desktop = nb_block_css_color((string) ($props['button_text_color_desktop'] ?? ($props['button_text_color'] ?? '')));
    $buttons_text_color_mobile = nb_block_css_color((string) ($props['button_text_color_mobile'] ?? $buttons_text_color_desktop));
    $buttons_text_line_height_desktop = nb_catalog_browser_prop_int($props, 'button_text_line_height_percent_desktop', 120, 80, 220) / 100;
    $buttons_text_line_height_mobile = nb_catalog_browser_prop_int($props, 'button_text_line_height_percent_mobile', 120, 80, 220) / 100;
    $buttons_text_letter_spacing_desktop = (float) ($props['button_text_letter_spacing_desktop'] ?? ($props['button_text_letter_spacing'] ?? 0));
    $buttons_text_letter_spacing_mobile = (float) ($props['button_text_letter_spacing_mobile'] ?? $buttons_text_letter_spacing_desktop);
    $button_style = in_array($props['btn_primary_style'] ?? 'primary', ['primary', 'outline', 'ghost'], true)
        ? (string) ($props['btn_primary_style'] ?? 'primary')
        : 'primary';

    $item_title_size_desktop = nb_catalog_browser_prop_int($props, 'item_title_size_desktop', 20, 10, 80);
    $item_title_size_mobile = nb_catalog_browser_prop_int($props, 'item_title_size_mobile', 18, 10, 80);
    $item_title_weight_desktop = nb_catalog_browser_prop_int($props, 'item_title_weight_desktop', 800, 100, 900);
    $item_title_weight_mobile = nb_catalog_browser_prop_int($props, 'item_title_weight_mobile', $item_title_weight_desktop, 100, 900);
    $item_title_color_desktop = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['item_title_color_desktop', 'item_title_color'], ''));
    $item_title_color_mobile = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['item_title_color_mobile'], $item_title_color_desktop));
    $item_title_line_height_desktop = nb_catalog_browser_prop_int($props, 'item_title_line_height_percent_desktop', 130, 80, 220) / 100;
    $item_title_line_height_mobile = nb_catalog_browser_prop_int($props, 'item_title_line_height_percent_mobile', 130, 80, 220) / 100;
    $item_title_letter_spacing_desktop = (float) nb_catalog_browser_prop_value($props, ['item_title_letter_spacing_desktop', 'item_title_letter_spacing'], 0);
    $item_title_letter_spacing_mobile = (float) nb_catalog_browser_prop_value($props, ['item_title_letter_spacing_mobile'], $item_title_letter_spacing_desktop);

    $item_text_size_desktop = nb_catalog_browser_prop_int($props, 'item_text_size_desktop', 14, 10, 80);
    $item_text_size_mobile = nb_catalog_browser_prop_int($props, 'item_text_size_mobile', 13, 10, 80);
    $item_text_weight_desktop = nb_catalog_browser_prop_int($props, 'item_text_weight_desktop', 400, 100, 900);
    $item_text_weight_mobile = nb_catalog_browser_prop_int($props, 'item_text_weight_mobile', $item_text_weight_desktop, 100, 900);
    $item_text_color_desktop = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['item_text_color_desktop', 'item_text_color'], ''));
    $item_text_color_mobile = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['item_text_color_mobile'], $item_text_color_desktop));
    $item_text_line_height_desktop = nb_catalog_browser_prop_int($props, 'item_text_line_height_percent_desktop', 160, 80, 260) / 100;
    $item_text_line_height_mobile = nb_catalog_browser_prop_int($props, 'item_text_line_height_percent_mobile', 160, 80, 260) / 100;
    $item_text_letter_spacing_desktop = (float) nb_catalog_browser_prop_value($props, ['item_text_letter_spacing_desktop', 'item_text_letter_spacing'], 0);
    $item_text_letter_spacing_mobile = (float) nb_catalog_browser_prop_value($props, ['item_text_letter_spacing_mobile'], $item_text_letter_spacing_desktop);
    $toolbar_background_mode = in_array($props['toolbar_background_mode'] ?? 'solid', ['transparent', 'solid'], true) ? (string) ($props['toolbar_background_mode'] ?? 'solid') : 'solid';
    $toolbar_background_color = nb_block_css_color((string) ($props['toolbar_background_color'] ?? ''), '');
    $toolbar_padding = nb_catalog_browser_prop_int($props, 'toolbar_padding', 16, 0, 120);
    $toolbar_radius = nb_catalog_browser_prop_int($props, 'toolbar_radius', 22, 0, 120);
    $toolbar_border_width = nb_catalog_browser_prop_int($props, 'toolbar_border_width', 1, 0, 20);
    $toolbar_border_color = nb_block_css_color((string) ($props['toolbar_border_color'] ?? ''), '');
    $toolbar_shadow_css = nb_catalog_browser_shadow_css((string) ($props['toolbar_shadow'] ?? 'sm'), 'sm');
    $toolbar_controls_background_mode = in_array($props['toolbar_controls_background_mode'] ?? 'solid', ['transparent', 'solid'], true) ? (string) ($props['toolbar_controls_background_mode'] ?? 'solid') : 'solid';
    $toolbar_controls_background_color = nb_block_css_color((string) ($props['toolbar_controls_background_color'] ?? ''), '');
    $toolbar_controls_radius = nb_catalog_browser_prop_int($props, 'toolbar_controls_radius', 16, 0, 80);
    $toolbar_controls_border_width = nb_catalog_browser_prop_int($props, 'toolbar_controls_border_width', 1, 0, 20);
    $toolbar_controls_border_color = nb_block_css_color((string) ($props['toolbar_controls_border_color'] ?? ''), '');
    $toolbar_controls_shadow_css = nb_catalog_browser_shadow_css((string) ($props['toolbar_controls_shadow'] ?? 'none'), 'none');
    $card_price_size_desktop = nb_catalog_browser_prop_int($props, 'card_price_size_desktop', 19, 10, 120);
    $card_price_size_mobile = nb_catalog_browser_prop_int($props, 'card_price_size_mobile', 17, 10, 120);
    $card_price_weight_desktop = nb_catalog_browser_prop_int($props, 'card_price_weight_desktop', 800, 100, 900);
    $card_price_weight_mobile = nb_catalog_browser_prop_int($props, 'card_price_weight_mobile', $card_price_weight_desktop, 100, 900);
    $card_price_color_desktop = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['card_price_color_desktop', 'card_price_color'], ''), '');
    $card_price_color_mobile = nb_block_css_color((string) nb_catalog_browser_prop_value($props, ['card_price_color_mobile'], $card_price_color_desktop), '');
    $card_price_line_height_desktop = nb_catalog_browser_prop_int($props, 'card_price_line_height_percent_desktop', 120, 80, 220) / 100;
    $card_price_line_height_mobile = nb_catalog_browser_prop_int($props, 'card_price_line_height_percent_mobile', 120, 80, 220) / 100;
    $card_price_letter_spacing_desktop = (float) nb_catalog_browser_prop_value($props, ['card_price_letter_spacing_desktop', 'card_price_letter_spacing'], 0);
    $card_price_letter_spacing_mobile = (float) nb_catalog_browser_prop_value($props, ['card_price_letter_spacing_mobile'], $card_price_letter_spacing_desktop);

    $media_aspect_ratio = in_array($props['media_aspect_ratio'] ?? '4:3', ['auto', '16:10', '16:9', '4:3', '1:1', '3:4'], true) ? (string) ($props['media_aspect_ratio'] ?? '4:3') : '4:3';
    $media_object_fit = in_array($props['media_object_fit'] ?? 'cover', ['cover', 'contain'], true) ? (string) ($props['media_object_fit'] ?? 'cover') : 'cover';
    $media_radius = nb_catalog_browser_prop_int($props, 'media_radius', 20, 0, 80);
    $media_inherit_global = array_key_exists('media_inherit_global', $props)
        ? nb_catalog_browser_visible($props['media_inherit_global'], true)
        : ($media_radius === 20);
    $media_surface_background_mode = (string) ($props['media_surface_background_mode'] ?? 'transparent');
    $media_surface_background_color = nb_block_css_color((string) ($props['media_surface_background_color'] ?? ''), '');
    $media_surface_padding = nb_catalog_browser_prop_int($props, 'media_surface_padding', 0, 0, 160);
    $media_surface_radius = nb_catalog_browser_prop_int($props, 'media_surface_radius', $media_radius, 0, 160);
    $media_surface_border_width = nb_catalog_browser_prop_int($props, 'media_surface_border_width', 0, 0, 20);
    $media_surface_border_color = nb_block_css_color((string) ($props['media_surface_border_color'] ?? ''), '');
    $media_surface_shadow_token = (string) ($props['media_surface_shadow'] ?? 'none');
    $media_surface_shadow_css = nb_catalog_browser_shadow_css($media_surface_shadow_token, 'none');

    $item_surface_variant = in_array($props['item_surface_variant'] ?? 'card', ['card', 'plain'], true) ? (string) ($props['item_surface_variant'] ?? 'card') : 'card';
    $item_surface_radius = nb_catalog_browser_prop_int($props, 'item_surface_radius', 22, 0, 100);
    $item_surface_border_width = nb_catalog_browser_prop_int($props, 'item_surface_border_width', 1, 0, 20);
    $item_surface_border_color = nb_block_css_color((string) ($props['item_surface_border_color'] ?? '#dbe4ef'), '#dbe4ef');
    $item_surface_shadow_token = (string) ($props['item_surface_shadow'] ?? 'md');
    $item_surface_shadow_css = nb_catalog_browser_shadow_css($item_surface_shadow_token, 'md');
    $item_surface_inherit_global = array_key_exists('item_surface_inherit_global', $props)
        ? nb_catalog_browser_visible($props['item_surface_inherit_global'], true)
        : (($item_surface_radius === 22 || $item_surface_radius === 1)
            && $item_surface_border_width === 1
            && strtolower($item_surface_border_color) === '#dbe4ef'
            && $item_surface_shadow_token === 'md');

    $modal_background_mode = 'solid';
    $modal_background_color = '#0f172a';
    $modal_padding = 18;
    $modal_radius = 24;
    $modal_border_width = 0;
    $modal_border_color = '';
    $modal_shadow_css = nb_catalog_browser_shadow_css('lg', 'lg');
    $modal_overlay_color = 'rgba(2,6,23,.88)';

    $content_width = nb_catalog_browser_prop_int($props, 'content_width', 1180, 320, 1440);
    $padding_top_desktop = nb_catalog_browser_prop_int($props, 'padding_top_desktop', 64, 0, 300);
    $padding_bottom_desktop = nb_catalog_browser_prop_int($props, 'padding_bottom_desktop', 64, 0, 300);
    $padding_top_mobile = nb_catalog_browser_prop_int($props, 'padding_top_mobile', 44, 0, 300);
    $padding_bottom_mobile = nb_catalog_browser_prop_int($props, 'padding_bottom_mobile', 44, 0, 300);
    $columns_desktop = nb_catalog_browser_prop_int($props, 'columns_desktop', 3, 1, 6);
    $columns_mobile = nb_catalog_browser_prop_int($props, 'columns_mobile', 1, 1, 2);
    $card_gap_desktop = nb_catalog_browser_prop_int($props, 'card_gap_desktop', 20, 0, 120);
    $card_gap_mobile = nb_catalog_browser_prop_int($props, 'card_gap_mobile', 14, 0, 120);
    $header_gap_desktop = nb_catalog_browser_prop_int($props, 'header_gap_desktop', 20, 0, 120);
    $header_gap_mobile = nb_catalog_browser_prop_int($props, 'header_gap_mobile', 14, 0, 120);
}

$catalog_media_radius_css = $media_inherit_global
    ? 'var(--nb-radius-media, 20px)'
    : $media_radius . 'px';
$catalog_media_surface_radius_css = $media_inherit_global || $media_surface_radius === $media_radius
    ? 'var(--nb-catalog-media-radius, var(--nb-radius-media, 20px))'
    : $media_surface_radius . 'px';
$catalog_item_surface_radius_css = $item_surface_inherit_global
    ? 'var(--nb-radius-card, 22px)'
    : $item_surface_radius . 'px';
$catalog_item_surface_border_width_css = $item_surface_inherit_global
    ? 'var(--nb-border-width, 1px)'
    : $item_surface_border_width . 'px';
$catalog_item_surface_border_color_css = $item_surface_inherit_global
    ? 'var(--nb-color-border, #dbe4ef)'
    : $item_surface_border_color;
$catalog_item_surface_shadow_css = $item_surface_inherit_global
    ? 'var(--nb-shadow-card, ' . nb_catalog_browser_shadow_css('md', 'md') . ')'
    : $item_surface_shadow_css;

$intro_html = $intro !== '' ? nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) : '';
$layout_variant_class = $columns_desktop >= 5 ? ' nb-catalog-browser--dense' : ($columns_desktop >= 4 ? ' nb-catalog-browser--compact' : '');
$layout_variant_class .= $item_surface_variant === 'plain' ? ' nb-catalog-browser--plain' : '';
$button_classes = [
    'primary' => 'nb-btn nb-btn--primary',
    'outline' => 'nb-btn nb-btn--outline',
    'ghost' => 'nb-btn nb-btn--ghost',
];
$action_button_class = $button_classes[$button_style] ?? $button_classes['primary'];
$section_link_payload = nb_catalog_browser_harden_link_payload($section_link_url);
$section_link_url = (string) ($section_link_payload['href'] ?? '');
$section_link_target = (string) ($section_link_payload['target'] ?? '');
$section_link_rel = (string) ($section_link_payload['rel'] ?? '');
$media_aspect_ratio_map = [
    'auto' => 'auto',
    '16:10' => '16 / 10',
    '16:9' => '16 / 9',
    '4:3' => '4 / 3',
    '1:1' => '1 / 1',
    '3:4' => '3 / 4',
];
$media_aspect_ratio_css = $media_aspect_ratio_map[$media_aspect_ratio] ?? '4 / 3';

$items = [];
foreach ($items_source as $item) {
    if (!is_array($item)) {
        continue;
    }

    $normalized_item = nb_catalog_browser_normalize_item($item);
    if ($normalized_item) {
        $normalized_item['searchText'] = nb_catalog_browser_build_search_text($normalized_item, $search_fields);
        $items[] = $normalized_item;
    }
}

$category_options = [];
foreach ($items as $item) {
    $category_value = trim((string) ($item['categoryValue'] ?? ''));
    if ($category_value === '') {
        continue;
    }

    $category_options[$category_value] = $category_value;
}
ksort($category_options, SORT_NATURAL | SORT_FLAG_CASE);

$section_class = 'nb-section nb-catalog-browser nb-catalog-browser--align-' . $align . $layout_variant_class . ($reveal['class'] ?? '');
$section_style = '--nb-catalog-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-catalog-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-padding-top-mobile:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-padding-bottom-mobile:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-columns:' . $columns_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-columns-mobile:' . $columns_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-gap:' . $card_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-gap-mobile:' . $card_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-header-gap:' . $header_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-header-gap-mobile:' . $header_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-weight:' . $title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-weight-mobile:' . $title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-margin-bottom:' . $title_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-margin-bottom-mobile:' . $title_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-line-height:' . max(0.8, min(2.2, $title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-line-height-mobile:' . max(0.8, min(2.2, $title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-letter-spacing:' . $title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-letter-spacing-mobile:' . $title_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-max-width:' . $title_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-title-max-width-mobile:' . $title_max_width_mobile . 'px;');
if ($title_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-title-color:' . $title_color_desktop . ';');
}
if ($title_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-title-color-mobile:' . $title_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-weight:' . $subtitle_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-weight-mobile:' . $subtitle_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-margin-bottom:' . $subtitle_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-margin-bottom-mobile:' . $subtitle_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-line-height:' . max(0.8, min(2.4, $subtitle_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-line-height-mobile:' . max(0.8, min(2.4, $subtitle_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-letter-spacing:' . $subtitle_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-letter-spacing-mobile:' . $subtitle_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-max-width:' . $subtitle_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-max-width-mobile:' . $subtitle_max_width_mobile . 'px;');
if ($subtitle_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-color:' . $subtitle_color_desktop . ';');
}
if ($subtitle_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-subtitle-color-mobile:' . $subtitle_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-meta-size:' . $meta_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-meta-size-mobile:' . $meta_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-meta-weight:' . $meta_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-meta-weight-mobile:' . $meta_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-meta-line-height:' . max(0.8, min(2.4, $meta_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-meta-line-height-mobile:' . max(0.8, min(2.4, $meta_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-meta-letter-spacing:' . $meta_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-meta-letter-spacing-mobile:' . $meta_letter_spacing_mobile . 'px;');
if ($meta_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-meta-color:' . $meta_color_desktop . ';');
}
if ($meta_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-meta-color-mobile:' . $meta_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-toolbar-padding:' . $toolbar_padding . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-toolbar-radius:' . $toolbar_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-toolbar-border-width:' . $toolbar_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-toolbar-shadow:' . $toolbar_shadow_css . ';');
if ($toolbar_background_mode === 'solid' && $toolbar_background_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-toolbar-background:' . $toolbar_background_color . ';');
}
if ($toolbar_border_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-toolbar-border-color:' . $toolbar_border_color . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-control-radius:' . $toolbar_controls_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-control-border-width:' . $toolbar_controls_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-control-shadow:' . $toolbar_controls_shadow_css . ';');
if ($toolbar_controls_background_mode === 'solid' && $toolbar_controls_background_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-control-background:' . $toolbar_controls_background_color . ';');
}
if ($toolbar_controls_border_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-control-border-color:' . $toolbar_controls_border_color . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-size:' . $buttons_text_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-size-mobile:' . $buttons_text_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-weight:' . $buttons_text_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-weight-mobile:' . $buttons_text_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-line-height:' . max(0.8, min(2.2, $buttons_text_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-line-height-mobile:' . max(0.8, min(2.2, $buttons_text_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-letter-spacing:' . $buttons_text_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-letter-spacing-mobile:' . $buttons_text_letter_spacing_mobile . 'px;');
if ($buttons_text_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-color:' . $buttons_text_color_desktop . ';');
}
if ($buttons_text_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-buttons-text-color-mobile:' . $buttons_text_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-size:' . $item_title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-size-mobile:' . $item_title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-weight:' . $item_title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-weight-mobile:' . $item_title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-line-height:' . max(0.8, min(2.2, $item_title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-line-height-mobile:' . max(0.8, min(2.2, $item_title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-letter-spacing:' . $item_title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-letter-spacing-mobile:' . $item_title_letter_spacing_mobile . 'px;');
if ($item_title_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-color:' . $item_title_color_desktop . ';');
}
if ($item_title_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-item-title-color-mobile:' . $item_title_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-size:' . $item_text_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-size-mobile:' . $item_text_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-weight:' . $item_text_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-weight-mobile:' . $item_text_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-line-height:' . max(0.8, min(2.6, $item_text_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-line-height-mobile:' . max(0.8, min(2.6, $item_text_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-letter-spacing:' . $item_text_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-letter-spacing-mobile:' . $item_text_letter_spacing_mobile . 'px;');
if ($item_text_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-color:' . $item_text_color_desktop . ';');
}
if ($item_text_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-item-text-color-mobile:' . $item_text_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-price-size:' . $card_price_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-price-size-mobile:' . $card_price_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-price-weight:' . $card_price_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-price-weight-mobile:' . $card_price_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-price-line-height:' . max(0.8, min(2.2, $card_price_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-price-line-height-mobile:' . max(0.8, min(2.2, $card_price_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-price-letter-spacing:' . $card_price_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-price-letter-spacing-mobile:' . $card_price_letter_spacing_mobile . 'px;');
if ($card_price_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-price-color:' . $card_price_color_desktop . ';');
}
if ($card_price_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-price-color-mobile:' . $card_price_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-media-aspect-ratio:' . $media_aspect_ratio_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-media-radius:' . $catalog_media_radius_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-media-object-fit:' . $media_object_fit . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-media-surface-padding:' . $media_surface_padding . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-media-surface-radius:' . $catalog_media_surface_radius_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-media-surface-border-width:' . $media_surface_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-media-surface-shadow:' . $media_surface_shadow_css . ';');
if ($media_surface_background_mode === 'solid' && $media_surface_background_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-media-surface-background:' . $media_surface_background_color . ';');
}
if ($media_surface_border_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-media-surface-border-color:' . $media_surface_border_color . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-catalog-card-radius:' . $catalog_item_surface_radius_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-card-border-width:' . $catalog_item_surface_border_width_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-card-border-color:' . $catalog_item_surface_border_color_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-card-shadow:' . $catalog_item_surface_shadow_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-modal-padding:' . $modal_padding . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-modal-radius:' . $modal_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-modal-border-width:' . $modal_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-catalog-modal-shadow:' . $modal_shadow_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-catalog-modal-overlay:' . $modal_overlay_color . ';');
if ($modal_background_mode === 'solid' && $modal_background_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-modal-background:' . $modal_background_color . ';');
}
if ($modal_border_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-catalog-modal-border-color:' . $modal_border_color . ';');
}
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style'] ?? '');

$theme_attr = $theme !== 'light' ? ' data-nb-theme="' . htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') . '"' : '';
$block_dom_id = 'block-' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) $block_uid);
$render_debug_version = nb_catalog_browser_render_debug_version();
?>
<section
    class="<?= htmlspecialchars($section_class, ENT_QUOTES, 'UTF-8') ?>"
    id="<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-block="catalog_browser"
    data-nb-entity="section"
    data-nb-render-version="<?= htmlspecialchars($render_debug_version, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-grid-desktop="<?= (int) $columns_desktop ?>"
    data-nb-grid-mobile="<?= (int) $columns_mobile ?>"
    data-nb-collection-mode="<?= htmlspecialchars($collection_mode, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-items-per-page="<?= (int) $items_per_page ?>"
    <?= $theme_attr ?>
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container nb-catalog-browser__container">
        <?php if (($title_visible && $heading !== '') || ($subtitle_visible && $intro_html !== '') || ($show_section_link && $section_link_label !== '' && $section_link_url !== '')): ?>
        <header class="nb-catalog-browser__header" data-nb-entity="header">
            <div class="nb-catalog-browser__header-main">
                <?php if ($title_visible && $heading !== ''): ?>
                <<?= htmlspecialchars($heading_tag, ENT_QUOTES, 'UTF-8') ?> class="nb-catalog-browser__title" data-nb-entity="title"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></<?= htmlspecialchars($heading_tag, ENT_QUOTES, 'UTF-8') ?>>
                <?php endif; ?>
                <?php if ($subtitle_visible && $intro_html !== ''): ?>
                <div class="nb-catalog-browser__subtitle" data-nb-entity="subtitle"><?= $intro_html ?></div>
                <?php endif; ?>
            </div>
            <?php if ($show_section_link && $section_link_label !== '' && $section_link_url !== ''): ?>
            <a class="nb-catalog-browser__button nb-catalog-browser__section-link <?= htmlspecialchars($action_button_class, ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($section_link_url, ENT_QUOTES, 'UTF-8') ?>"<?= $section_link_target !== '' ? ' target="' . htmlspecialchars($section_link_target, ENT_QUOTES, 'UTF-8') . '"' : '' ?><?= $section_link_rel !== '' ? ' rel="' . htmlspecialchars($section_link_rel, ENT_QUOTES, 'UTF-8') . '"' : '' ?> data-nb-entity="primaryButton"><?= htmlspecialchars($section_link_label, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endif; ?>
        </header>
        <?php endif; ?>

        <?php if ($items && ($show_search || $show_category_filter || $show_price_filter || $show_sort)): ?>
            <div class="nb-catalog-browser__toolbar" data-role="catalog-toolbar" data-nb-entity="toolbar">
            <?php if ($show_search): ?>
                <label class="nb-catalog-browser__control nb-catalog-browser__control--search" data-nb-entity="searchField">
                    <span class="nb-catalog-browser__control-label" data-nb-entity="searchField">Поиск</span>
                    <input type="search" class="nb-catalog-browser__input" placeholder="Найти по названию или описанию" data-role="catalog-search" data-nb-entity="searchField">
            </label>
            <?php endif; ?>

            <?php if ($show_category_filter): ?>
                <label class="nb-catalog-browser__control" data-nb-entity="categoryFilter">
                    <span class="nb-catalog-browser__control-label" data-nb-entity="categoryFilter">Категория</span>
                    <select class="nb-catalog-browser__select" data-role="catalog-category" data-nb-entity="categoryFilter">
                    <option value="">Все категории</option>
                    <?php foreach ($category_options as $category_option): ?>
                    <option value="<?= htmlspecialchars($category_option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category_option, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php endif; ?>

            <?php if ($show_price_filter): ?>
            <div class="nb-catalog-browser__control nb-catalog-browser__control--price" data-nb-entity="priceFilter">
                <span class="nb-catalog-browser__control-label" data-nb-entity="priceFilter">Цена</span>
                <div class="nb-catalog-browser__price-filters">
                    <input type="number" class="nb-catalog-browser__input" placeholder="от" data-role="catalog-price-min" data-nb-entity="priceFilter">
                    <input type="number" class="nb-catalog-browser__input" placeholder="до" data-role="catalog-price-max" data-nb-entity="priceFilter">
                </div>
            </div>
            <?php endif; ?>

            <?php if ($show_sort): ?>
            <label class="nb-catalog-browser__control" data-nb-entity="sortControl">
                <span class="nb-catalog-browser__control-label" data-nb-entity="sortControl">Сортировка</span>
                <select class="nb-catalog-browser__select" data-role="catalog-sort" data-nb-entity="sortControl">
                    <option value="default">По порядку блока</option>
                    <option value="title-asc">По названию A-Z</option>
                    <option value="price-asc">Сначала дешевле</option>
                    <option value="price-desc">Сначала дороже</option>
                </select>
            </label>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($show_active_filters && $items): ?>
        <div class="nb-catalog-browser__active-filters" data-role="catalog-active" data-nb-entity="activeFilters" hidden></div>
        <?php endif; ?>

        <?php if ($items && $show_results_count): ?>
        <div class="nb-catalog-browser__results-row" data-role="catalog-results-row" hidden>
            <div class="nb-catalog-browser__results" data-role="catalog-results" data-nb-entity="meta"></div>
        </div>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-catalog-browser__grid" data-role="catalog-grid" data-nb-entity="items">
            <?php foreach ($items as $index => $item): ?>
            <?php $gallery_json = htmlspecialchars(json_encode($item['gallery'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>
            <article class="nb-catalog-browser__card" data-nb-entity="itemSurface" data-order="<?= $index ?>" data-search="<?= htmlspecialchars($item['searchText'], ENT_QUOTES, 'UTF-8') ?>" data-category="<?= htmlspecialchars((string) ($item['categoryValue'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-price="<?= htmlspecialchars((string) ($item['priceValue'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($show_image && $item['image'] !== ''): ?>
                <div class="nb-catalog-browser__media-surface" data-nb-entity="mediaSurface">
                    <button type="button" class="nb-catalog-browser__media" data-nb-entity="media" data-role="media-open" data-gallery="<?= $gallery_json ?>" data-title="<?= $item['title'] ?>">
                        <img class="nb-catalog-browser__image" src="<?= $item['image'] ?>" alt="<?= $item['imageAlt'] ?>">
                    </button>
                </div>
                <?php endif; ?>
                <div class="nb-catalog-browser__body">
                    <?php if ($show_badge && $item['badge'] !== ''): ?>
                    <div class="nb-catalog-browser__badge" data-nb-entity="cardBadge"><?= $item['badge'] ?></div>
                    <?php endif; ?>
                    <?php if ($show_category && $item['category'] !== ''): ?>
                    <?php if ($item['categoryUrl'] !== ''): ?>
                    <a class="nb-catalog-browser__category" href="<?= $item['categoryUrl'] ?>" data-nb-entity="meta"><?= $item['category'] ?></a>
                    <?php else: ?>
                    <div class="nb-catalog-browser__category" data-nb-entity="meta"><?= $item['category'] ?></div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($item['title'] !== ''): ?>
                    <h3 class="nb-catalog-browser__card-title" data-nb-entity="itemTitle">
                        <?php if ($item['url'] !== ''): ?>
                        <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                        <?php else: ?>
                        <?= $item['title'] ?>
                        <?php endif; ?>
                    </h3>
                    <?php endif; ?>
                    <?php if ($show_excerpt && $item['excerpt'] !== ''): ?>
                    <div class="nb-catalog-browser__excerpt" data-nb-entity="itemText"><?= $item['excerpt'] ?></div>
                    <?php endif; ?>
                    <?php if ($show_price && ($item['price'] !== '' || ($show_old_price && $item['priceOld'] !== ''))): ?>
                    <div class="nb-catalog-browser__price-line" data-nb-entity="cardPrice">
                        <?php if ($show_old_price && $item['priceOld'] !== ''): ?>
                        <span class="nb-catalog-browser__price-old" data-nb-entity="meta"><?= $item['priceOld'] ?></span>
                        <?php endif; ?>
                        <?php if ($item['price'] !== ''): ?>
                        <span class="nb-catalog-browser__price" data-nb-entity="cardPrice"><?= $item['price'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($item['availability'] !== ''): ?>
                    <div class="nb-catalog-browser__availability" data-nb-entity="meta"><?= $item['availability'] ?></div>
                    <?php endif; ?>
                    <?php if ($show_cta && $item['ctaLabel'] !== '' && $item['ctaHref'] !== ''): ?>
                    <a class="nb-catalog-browser__button nb-catalog-browser__cta <?= htmlspecialchars($action_button_class, ENT_QUOTES, 'UTF-8') ?>" href="<?= $item['ctaHref'] ?>"<?= $item['ctaTarget'] !== '' ? ' target="' . $item['ctaTarget'] . '"' : '' ?><?= $item['ctaRel'] !== '' ? ' rel="' . $item['ctaRel'] . '"' : '' ?> data-nb-entity="cardPrimaryAction"><?= $item['ctaLabel'] ?></a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="nb-catalog-browser__empty nb-catalog-browser__empty--filtered" data-role="catalog-empty" data-nb-entity="emptyState" hidden>Ничего не найдено по текущим фильтрам.</div>
        <div class="nb-catalog-browser__footer" data-role="catalog-footer" hidden>
            <button type="button" class="nb-catalog-browser__button nb-catalog-browser__more <?= htmlspecialchars($action_button_class, ENT_QUOTES, 'UTF-8') ?>" data-role="catalog-more" hidden>Показать ещё</button>
            <div class="nb-catalog-browser__pagination" data-role="catalog-pagination" hidden></div>
        </div>
        <?php else: ?>
        <div class="nb-catalog-browser__empty" data-nb-entity="emptyState">Каталог пока пуст. Добавьте первую карточку.</div>
        <?php endif; ?>
    </div>

    <?php if ($items): ?>
    <div class="nb-catalog-browser__modal" data-role="media-modal" data-nb-entity="mediaModal" hidden>
        <div class="nb-catalog-browser__modal-backdrop" data-role="media-close"></div>
        <div class="nb-catalog-browser__modal-dialog" role="dialog" aria-modal="true" aria-label="Просмотр изображений">
            <button type="button" class="nb-catalog-browser__modal-close" data-role="media-close" aria-label="Закрыть">&times;</button>
            <button type="button" class="nb-catalog-browser__modal-nav nb-catalog-browser__modal-nav--prev" data-role="media-prev" aria-label="Предыдущее изображение">&#8249;</button>
            <div class="nb-catalog-browser__modal-stage" data-role="media-stage">
                <img class="nb-catalog-browser__modal-image" data-role="media-image" alt="">
                <div class="nb-catalog-browser__modal-caption" data-role="media-caption"></div>
                <div class="nb-catalog-browser__modal-counter" data-role="media-counter"></div>
            </div>
            <button type="button" class="nb-catalog-browser__modal-nav nb-catalog-browser__modal-nav--next" data-role="media-next" aria-label="Следующее изображение">&#8250;</button>
        </div>
    </div>
    <script>
    (function() {
        var root = document.getElementById(<?= json_encode($block_dom_id, JSON_UNESCAPED_UNICODE) ?>);
        if (!root) {
            return;
        }

        var grid = root.querySelector('[data-role="catalog-grid"]');
        var cards = grid ? Array.prototype.slice.call(grid.querySelectorAll('.nb-catalog-browser__card')) : [];
        var emptyState = root.querySelector('[data-role="catalog-empty"]');
        var activeFilters = root.querySelector('[data-role="catalog-active"]');
        var resultsRow = root.querySelector('[data-role="catalog-results-row"]');
        var resultsBar = root.querySelector('[data-role="catalog-results"]');
        var footer = root.querySelector('[data-role="catalog-footer"]');
        var moreButton = root.querySelector('[data-role="catalog-more"]');
        var pagination = root.querySelector('[data-role="catalog-pagination"]');
        var searchInput = root.querySelector('[data-role="catalog-search"]');
        var categorySelect = root.querySelector('[data-role="catalog-category"]');
        var priceMinInput = root.querySelector('[data-role="catalog-price-min"]');
        var priceMaxInput = root.querySelector('[data-role="catalog-price-max"]');
        var sortSelect = root.querySelector('[data-role="catalog-sort"]');
        var collectionMode = <?= json_encode($collection_mode, JSON_UNESCAPED_UNICODE) ?>;
        var itemsPerPage = Math.max(1, parseInt(<?= json_encode((int) $items_per_page, JSON_UNESCAPED_UNICODE) ?>, 10) || 1);
        var showResultsCount = <?= $show_results_count ? 'true' : 'false' ?>;
        var actionButtonClass = <?= json_encode($action_button_class, JSON_UNESCAPED_UNICODE) ?>;
        var currentPage = 1;
        var visibleLimit = itemsPerPage;

        function sanitizeLabel(value) {
            return String(value || '').replace(/[&<>"']/g, '');
        }

        function renderActiveFilters(filters) {
            if (!activeFilters) {
                return;
            }

            var chips = [];
            if (filters.search) {
                chips.push('<span class="nb-catalog-browser__filter-chip">Поиск: ' + sanitizeLabel(filters.search) + '</span>');
            }
            if (filters.category) {
                chips.push('<span class="nb-catalog-browser__filter-chip">Категория: ' + sanitizeLabel(filters.category) + '</span>');
            }
            if (filters.minPrice) {
                chips.push('<span class="nb-catalog-browser__filter-chip">Цена от ' + filters.minPrice + '</span>');
            }
            if (filters.maxPrice) {
                chips.push('<span class="nb-catalog-browser__filter-chip">Цена до ' + filters.maxPrice + '</span>');
            }

            if (!chips.length) {
                activeFilters.hidden = true;
                activeFilters.innerHTML = '';
                return;
            }

            activeFilters.hidden = false;
            activeFilters.innerHTML = chips.join('') + '<button type="button" class="nb-catalog-browser__filter-reset" data-role="catalog-reset">Сбросить</button>';
        }

        function buildPaginationItems(totalPages, page) {
            var items = [];
            if (totalPages <= 7) {
                for (var index = 1; index <= totalPages; index++) {
                    items.push(index);
                }
                return items;
            }

            items.push(1);
            var start = Math.max(2, page - 1);
            var end = Math.min(totalPages - 1, page + 1);

            if (start > 2) {
                items.push('ellipsis-start');
            }

            for (var middle = start; middle <= end; middle++) {
                items.push(middle);
            }

            if (end < totalPages - 1) {
                items.push('ellipsis-end');
            }

            items.push(totalPages);
            return items;
        }

        function renderResults(totalMatches, displayedCount, totalPages) {
            if (!resultsBar || !resultsRow) {
                return;
            }

            if (!showResultsCount || totalMatches < 1) {
                resultsRow.hidden = true;
                resultsBar.textContent = '';
                return;
            }

            resultsRow.hidden = false;
            if (collectionMode === 'pagination' && totalPages > 1) {
                resultsBar.textContent = 'Страница ' + currentPage + ' из ' + totalPages + ' · ' + totalMatches + ' карточек';
                return;
            }

            if (collectionMode === 'load_more' && displayedCount < totalMatches) {
                resultsBar.textContent = 'Показано ' + displayedCount + ' из ' + totalMatches + ' карточек';
                return;
            }

            resultsBar.textContent = 'Найдено ' + totalMatches + ' карточек';
        }

        function renderCollectionNavigation(totalMatches, displayedCount, totalPages) {
            if (!footer) {
                return;
            }

            var hasControls = false;

            if (moreButton) {
                moreButton.hidden = true;
            }

            if (pagination) {
                pagination.hidden = true;
                pagination.innerHTML = '';
            }

            if (collectionMode === 'load_more' && moreButton && displayedCount < totalMatches) {
                hasControls = true;
                moreButton.hidden = false;
                moreButton.textContent = 'Показать ещё ' + Math.min(itemsPerPage, totalMatches - displayedCount);
            }

            if (collectionMode === 'pagination' && pagination && totalPages > 1) {
                hasControls = true;
                pagination.hidden = false;
                pagination.innerHTML = ''
                    + '<button type="button" class="nb-catalog-browser__button nb-catalog-browser__page-control ' + actionButtonClass + '" data-role="catalog-page" data-page="' + Math.max(1, currentPage - 1) + '"' + (currentPage === 1 ? ' disabled' : '') + '>Назад</button>'
                    + buildPaginationItems(totalPages, currentPage).map(function(item) {
                        if (typeof item !== 'number') {
                            return '<span class="nb-catalog-browser__page-gap">…</span>';
                        }

                        return '<button type="button" class="nb-catalog-browser__button nb-catalog-browser__page ' + actionButtonClass + (item === currentPage ? ' is-active' : '') + '" data-role="catalog-page" data-page="' + item + '"' + (item === currentPage ? ' aria-current="page"' : '') + '>' + item + '</button>';
                    }).join('')
                    + '<button type="button" class="nb-catalog-browser__button nb-catalog-browser__page-control ' + actionButtonClass + '" data-role="catalog-page" data-page="' + Math.min(totalPages, currentPage + 1) + '"' + (currentPage === totalPages ? ' disabled' : '') + '>Вперёд</button>';
            }

            footer.hidden = !hasControls || totalMatches < 1;
        }

        function applyFilters(options) {
            if (!grid) {
                return;
            }

            options = options || {};
            if (options.resetCollectionState !== false) {
                currentPage = 1;
                visibleLimit = itemsPerPage;
            }

            var filters = {
                search: searchInput ? String(searchInput.value || '').toLowerCase().trim() : '',
                category: categorySelect ? String(categorySelect.value || '').trim() : '',
                minPrice: priceMinInput ? parseFloat(priceMinInput.value || 0) || 0 : 0,
                maxPrice: priceMaxInput ? parseFloat(priceMaxInput.value || 0) || 0 : 0,
                sort: sortSelect ? String(sortSelect.value || 'default') : 'default'
            };

            var visibleCards = cards.filter(function(card) {
                var searchValue = String(card.getAttribute('data-search') || '').toLowerCase();
                var categoryValue = String(card.getAttribute('data-category') || '').trim();
                var priceValue = parseFloat(card.getAttribute('data-price') || '0') || 0;

                if (filters.search && searchValue.indexOf(filters.search) === -1) {
                    return false;
                }
                if (filters.category && categoryValue !== filters.category) {
                    return false;
                }
                if (filters.minPrice && (!priceValue || priceValue < filters.minPrice)) {
                    return false;
                }
                if (filters.maxPrice && priceValue > filters.maxPrice) {
                    return false;
                }
                return true;
            });

            var sortedCards = visibleCards.slice();
            var displayedCards = [];
            var totalPages = 1;
            if (filters.sort === 'title-asc') {
                sortedCards.sort(function(a, b) {
                    return String(a.getAttribute('data-search') || '').localeCompare(String(b.getAttribute('data-search') || ''), 'ru');
                });
            } else if (filters.sort === 'price-asc') {
                sortedCards.sort(function(a, b) {
                    return (parseFloat(a.getAttribute('data-price') || '0') || 0) - (parseFloat(b.getAttribute('data-price') || '0') || 0);
                });
            } else if (filters.sort === 'price-desc') {
                sortedCards.sort(function(a, b) {
                    return (parseFloat(b.getAttribute('data-price') || '0') || 0) - (parseFloat(a.getAttribute('data-price') || '0') || 0);
                });
            } else {
                sortedCards.sort(function(a, b) {
                    return (parseInt(a.getAttribute('data-order') || '0', 10) || 0) - (parseInt(b.getAttribute('data-order') || '0', 10) || 0);
                });
            }

            if (collectionMode === 'pagination') {
                totalPages = Math.max(1, Math.ceil(sortedCards.length / itemsPerPage));
                currentPage = Math.min(Math.max(1, currentPage), totalPages);
                displayedCards = sortedCards.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
            } else if (collectionMode === 'load_more') {
                visibleLimit = Math.max(itemsPerPage, visibleLimit);
                displayedCards = sortedCards.slice(0, Math.min(visibleLimit, sortedCards.length));
            } else {
                displayedCards = sortedCards.slice();
            }

            cards.forEach(function(card) {
                card.hidden = true;
            });
            displayedCards.forEach(function(card) {
                card.hidden = false;
                grid.appendChild(card);
            });

            if (emptyState) {
                emptyState.hidden = sortedCards.length > 0;
            }

            renderActiveFilters(filters);
            renderResults(sortedCards.length, displayedCards.length, totalPages);
            renderCollectionNavigation(sortedCards.length, displayedCards.length, totalPages);
        }

        root.addEventListener('click', function(event) {
            var reset = event.target.closest('[data-role="catalog-reset"]');
            if (reset) {
                if (searchInput) searchInput.value = '';
                if (categorySelect) categorySelect.value = '';
                if (priceMinInput) priceMinInput.value = '';
                if (priceMaxInput) priceMaxInput.value = '';
                if (sortSelect) sortSelect.value = 'default';
                applyFilters();
                return;
            }

            var moreTrigger = event.target.closest('[data-role="catalog-more"]');
            if (moreTrigger) {
                visibleLimit += itemsPerPage;
                applyFilters({ resetCollectionState: false });
                return;
            }

            var pageTrigger = event.target.closest('[data-role="catalog-page"]');
            if (pageTrigger && !pageTrigger.disabled) {
                currentPage = Math.max(1, parseInt(pageTrigger.getAttribute('data-page') || '1', 10) || 1);
                applyFilters({ resetCollectionState: false });
            }
        });

        [searchInput, categorySelect, priceMinInput, priceMaxInput, sortSelect].forEach(function(control) {
            if (!control) {
                return;
            }
            control.addEventListener('input', applyFilters);
            control.addEventListener('change', applyFilters);
        });

        applyFilters();

        var modal = root.querySelector('[data-role="media-modal"]');
        if (!modal) {
            return;
        }

        var modalImage = modal.querySelector('[data-role="media-image"]');
        var modalCaption = modal.querySelector('[data-role="media-caption"]');
        var modalCounter = modal.querySelector('[data-role="media-counter"]');
        var prevButton = modal.querySelector('[data-role="media-prev"]');
        var nextButton = modal.querySelector('[data-role="media-next"]');
        var stage = modal.querySelector('[data-role="media-stage"]');
        var slides = [];
        var currentSlide = 0;
        var touchStartX = 0;

        function renderSlide() {
            if (!slides.length) {
                return;
            }

            var slide = slides[currentSlide];
            modalImage.src = slide.src || '';
            modalImage.alt = slide.alt || '';
            modalCaption.textContent = slide.caption || slide.alt || '';
            modalCounter.textContent = slides.length > 1 ? (currentSlide + 1) + ' / ' + slides.length : '';
            prevButton.hidden = slides.length < 2;
            nextButton.hidden = slides.length < 2;
        }

        function openModal(gallery) {
            if (!Array.isArray(gallery) || !gallery.length) {
                return;
            }

            slides = gallery;
            currentSlide = 0;
            renderSlide();
            modal.hidden = false;
            document.documentElement.classList.add('nb-catalog-browser-modal-open');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modal.hidden = true;
            modalImage.removeAttribute('src');
            modalCaption.textContent = '';
            modalCounter.textContent = '';
            document.documentElement.classList.remove('nb-catalog-browser-modal-open');
            modal.setAttribute('aria-hidden', 'true');
        }

        function stepModal(direction) {
            if (slides.length < 2) {
                return;
            }
            currentSlide = (currentSlide + direction + slides.length) % slides.length;
            renderSlide();
        }

        root.addEventListener('click', function(event) {
            var trigger = event.target.closest('[data-role="media-open"]');
            if (!trigger) {
                return;
            }

            event.preventDefault();
            var gallery = [];
            try {
                gallery = JSON.parse(trigger.getAttribute('data-gallery') || '[]');
            } catch (error) {
                gallery = [];
            }
            openModal(gallery);
        });

        modal.addEventListener('click', function(event) {
            if (event.target.closest('[data-role="media-close"]')) {
                closeModal();
            }
            if (event.target.closest('[data-role="media-prev"]')) {
                stepModal(-1);
            }
            if (event.target.closest('[data-role="media-next"]')) {
                stepModal(1);
            }
        });

        document.addEventListener('keydown', function(event) {
            if (modal.hidden) {
                return;
            }
            if (event.key === 'Escape') {
                closeModal();
            } else if (event.key === 'ArrowLeft') {
                stepModal(-1);
            } else if (event.key === 'ArrowRight') {
                stepModal(1);
            }
        });

        stage.addEventListener('touchstart', function(event) {
            touchStartX = event.changedTouches && event.changedTouches[0] ? event.changedTouches[0].clientX : 0;
        }, { passive: true });

        stage.addEventListener('touchend', function(event) {
            var endX = event.changedTouches && event.changedTouches[0] ? event.changedTouches[0].clientX : 0;
            var deltaX = endX - touchStartX;
            if (Math.abs(deltaX) < 40) {
                return;
            }
            stepModal(deltaX < 0 ? 1 : -1);
        }, { passive: true });
    })();
    </script>
    <?php endif; ?>
</section>