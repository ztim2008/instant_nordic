<?php

require_once dirname(__DIR__) . '/render_helpers.php';

$bento_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'bento_feed'))
    ? $block_contract
    : null;

if (!function_exists('nb_bento_feed_visible')) {
    function nb_bento_feed_visible($value, $default = true) {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('nb_bento_feed_prop_int')) {
    function nb_bento_feed_prop_int(array $props, $key, $default, $min, $max) {
        $value = $props[$key] ?? $default;
        if (!is_numeric($value)) {
            $value = $default;
        }

        $value = (int) round((float) $value);
        if ($value < $min) {
            $value = $min;
        }
        if ($value > $max) {
            $value = $max;
        }

        return $value;
    }
}

if (!function_exists('nb_bento_feed_prop_value')) {
    function nb_bento_feed_prop_value(array $props, array $keys, $default = null) {
        foreach ($keys as $key) {
            if ($key !== '' && array_key_exists($key, $props) && $props[$key] !== '' && $props[$key] !== null) {
                return $props[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('nb_bento_feed_entity_value')) {
    function nb_bento_feed_entity_value(array $entity, $branch, $key, $default = null) {
        if (isset($entity[$branch]) && is_array($entity[$branch]) && array_key_exists($key, $entity[$branch]) && $entity[$branch][$key] !== '' && $entity[$branch][$key] !== null) {
            return $entity[$branch][$key];
        }

        if (array_key_exists($key, $entity) && $entity[$key] !== '' && $entity[$key] !== null) {
            return $entity[$key];
        }

        return $default;
    }
}

if (!function_exists('nb_bento_feed_normalize_item')) {
    function nb_bento_feed_normalize_item(array $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $excerpt = trim((string) ($item['excerpt'] ?? ($item['text'] ?? '')));
        $category = trim((string) ($item['category'] ?? ''));
        $category_url = trim((string) ($item['categoryUrl'] ?? ($item['category_url'] ?? '')));
        $link_label = trim((string) ($item['linkLabel'] ?? ($item['link_label'] ?? ($item['ctaLabel'] ?? ($item['cta_label'] ?? '')))));
        $url = trim((string) ($item['url'] ?? ''));
        $date = trim((string) ($item['date'] ?? ''));
        $views = trim((string) ($item['views'] ?? ''));
        $comments = trim((string) ($item['comments'] ?? ''));
        $media = nb_block_extract_media($item['image'] ?? '', $item['imageAlt'] ?? ($item['alt'] ?? $title));

        if ($title === '' && $excerpt === '' && $category === '' && $url === '' && $media['display'] === '' && $media['original'] === '') {
            return null;
        }

        return [
            'category' => htmlspecialchars($category, ENT_QUOTES, 'UTF-8'),
            'categoryUrl' => htmlspecialchars($category_url, ENT_QUOTES, 'UTF-8'),
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'excerpt' => nl2br(htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8')),
            'linkLabel' => htmlspecialchars($link_label !== '' ? $link_label : 'Подробнее', ENT_QUOTES, 'UTF-8'),
            'url' => htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            'date' => htmlspecialchars($date, ENT_QUOTES, 'UTF-8'),
            'views' => htmlspecialchars($views, ENT_QUOTES, 'UTF-8'),
            'comments' => htmlspecialchars($comments, ENT_QUOTES, 'UTF-8'),
            'image' => htmlspecialchars((string) ($media['display'] ?: $media['original']), ENT_QUOTES, 'UTF-8'),
            'imageAlt' => htmlspecialchars((string) ($media['alt'] ?: $title), ENT_QUOTES, 'UTF-8'),
        ];
    }
}

$props = (array) ($props ?? []);

if ($bento_contract) {
    $theme = in_array($bento_contract['design']['section']['theme'] ?? 'light', ['light', 'alt', 'dark'], true)
        ? (string) ($bento_contract['design']['section']['theme'] ?? 'light') : 'light';
    $align = in_array($bento_contract['layout']['desktop']['align'] ?? 'left', ['left', 'center'], true)
        ? (string) ($bento_contract['layout']['desktop']['align'] ?? 'left') : 'left';
    $layout_preset = in_array($bento_contract['layout']['preset'] ?? 'editorial_mix', ['editorial_mix', 'feature_stack'], true)
        ? (string) ($bento_contract['layout']['preset'] ?? 'editorial_mix') : 'editorial_mix';
    $background_style = nb_block_build_background_style((array) ($bento_contract['design']['section']['background'] ?? []));
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($bento_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($bento_contract['runtime']['animation']['delay'] ?? 0),
    ]);
    $heading = trim((string) ($bento_contract['content']['title'] ?? 'Компактная бенто-лента'));
    $intro = trim((string) ($bento_contract['content']['subtitle'] ?? ''));
    $section_link_label = trim((string) ($bento_contract['content']['primaryButton']['label'] ?? ''));
    $section_link_url = trim((string) ($bento_contract['content']['primaryButton']['url'] ?? ''));
    $items_source = is_array($bento_contract['content']['items'] ?? null) ? $bento_contract['content']['items'] : [];
    $entities = is_array($bento_contract['design']['entities'] ?? null) ? $bento_contract['design']['entities'] : [];
    $title_entity = (array) ($entities['title'] ?? []);
    $subtitle_entity = (array) ($entities['subtitle'] ?? []);
    $meta_entity = (array) ($entities['meta'] ?? []);
    $item_title_entity = (array) ($entities['itemTitle'] ?? []);
    $item_text_entity = (array) ($entities['itemText'] ?? []);
    $item_link_entity = (array) ($entities['itemLink'] ?? []);
    $item_surface = (array) ($entities['itemSurface'] ?? []);
    $media_entity = (array) ($entities['media'] ?? []);
    $title_visible = !array_key_exists('visible', $title_entity) || !empty($title_entity['visible']);
    $subtitle_visible = !array_key_exists('visible', $subtitle_entity) || !empty($subtitle_entity['visible']);
    $show_more_link = !array_key_exists('moreLink', (array) ($bento_contract['runtime']['visibility'] ?? [])) || !empty($bento_contract['runtime']['visibility']['moreLink']);
    $show_image = !array_key_exists('image', (array) ($bento_contract['runtime']['visibility'] ?? [])) || !empty($bento_contract['runtime']['visibility']['image']);
    $show_category = !array_key_exists('category', (array) ($bento_contract['runtime']['visibility'] ?? [])) || !empty($bento_contract['runtime']['visibility']['category']);
    $show_excerpt = !array_key_exists('excerpt', (array) ($bento_contract['runtime']['visibility'] ?? [])) || !empty($bento_contract['runtime']['visibility']['excerpt']);
    $show_item_link = !array_key_exists('itemLink', (array) ($bento_contract['runtime']['visibility'] ?? [])) || !empty($bento_contract['runtime']['visibility']['itemLink']);
    $show_date = !array_key_exists('date', (array) ($bento_contract['runtime']['visibility'] ?? [])) || !empty($bento_contract['runtime']['visibility']['date']);
    $show_views = !array_key_exists('views', (array) ($bento_contract['runtime']['visibility'] ?? [])) || !empty($bento_contract['runtime']['visibility']['views']);
    $show_comments = !array_key_exists('comments', (array) ($bento_contract['runtime']['visibility'] ?? [])) || !empty($bento_contract['runtime']['visibility']['comments']);
    $content_width = (int) ($bento_contract['layout']['desktop']['contentWidth'] ?? 1360);
    $padding_top_desktop = (int) ($bento_contract['layout']['desktop']['paddingTop'] ?? 0);
    $padding_bottom_desktop = (int) ($bento_contract['layout']['desktop']['paddingBottom'] ?? 0);
    $padding_top_mobile = (int) ($bento_contract['layout']['mobile']['paddingTop'] ?? 0);
    $padding_bottom_mobile = (int) ($bento_contract['layout']['mobile']['paddingBottom'] ?? 0);
    $columns_desktop = (int) ($bento_contract['layout']['desktop']['columns'] ?? 3);
    $columns_mobile = (int) ($bento_contract['layout']['mobile']['columns'] ?? 1);
    $card_gap_desktop = (int) ($bento_contract['layout']['desktop']['cardGap'] ?? 0);
    $card_gap_mobile = (int) ($bento_contract['layout']['mobile']['cardGap'] ?? 0);
    $header_gap_desktop = (int) ($bento_contract['layout']['desktop']['headerGap'] ?? 18);
    $header_gap_mobile = (int) ($bento_contract['layout']['mobile']['headerGap'] ?? 14);
    $collection_mode = in_array($bento_contract['runtime']['collectionMode'] ?? 'load_more', ['all', 'load_more', 'pagination'], true)
        ? (string) ($bento_contract['runtime']['collectionMode'] ?? 'load_more') : 'load_more';
    $items_per_page = (int) ($bento_contract['runtime']['itemsPerPage'] ?? 4);
    $items_initial = (int) ($bento_contract['runtime']['initialItemsCount'] ?? $items_per_page);
    $load_more_label = trim((string) ($bento_contract['runtime']['loadMoreLabel'] ?? 'Показать ещё'));
    $show_bottom_navigation = !array_key_exists('showBottomNavigation', (array) ($bento_contract['runtime'] ?? [])) || !empty($bento_contract['runtime']['showBottomNavigation']);
    $heading_tag = htmlspecialchars((string) ($title_entity['tag'] ?? 'h2'), ENT_QUOTES, 'UTF-8');
    $title_weight_desktop = (int) nb_bento_feed_entity_value($title_entity, 'desktop', 'weight', 800);
    $title_weight_mobile = (int) nb_bento_feed_entity_value($title_entity, 'mobile', 'weight', $title_weight_desktop);
    $title_size_desktop = (int) nb_bento_feed_entity_value($title_entity, 'desktop', 'fontSize', 44);
    $title_size_mobile = (int) nb_bento_feed_entity_value($title_entity, 'mobile', 'fontSize', 30);
    $title_margin_bottom_desktop = (int) nb_bento_feed_entity_value($title_entity, 'desktop', 'marginBottom', 0);
    $title_margin_bottom_mobile = (int) nb_bento_feed_entity_value($title_entity, 'mobile', 'marginBottom', 0);
    $title_color_desktop = nb_block_css_color((string) nb_bento_feed_entity_value($title_entity, 'desktop', 'color', ''));
    $title_color_mobile = nb_block_css_color((string) nb_bento_feed_entity_value($title_entity, 'mobile', 'color', $title_color_desktop));
    $title_line_height_desktop = ((float) nb_bento_feed_entity_value($title_entity, 'desktop', 'lineHeightPercent', 104)) / 100;
    $title_line_height_mobile = ((float) nb_bento_feed_entity_value($title_entity, 'mobile', 'lineHeightPercent', $title_line_height_desktop * 100)) / 100;
    $title_letter_spacing_desktop = (float) nb_bento_feed_entity_value($title_entity, 'desktop', 'letterSpacing', -1);
    $title_letter_spacing_mobile = (float) nb_bento_feed_entity_value($title_entity, 'mobile', 'letterSpacing', $title_letter_spacing_desktop);
    $title_max_width_desktop = (int) nb_bento_feed_entity_value($title_entity, 'desktop', 'maxWidth', 920);
    $title_max_width_mobile = (int) nb_bento_feed_entity_value($title_entity, 'mobile', 'maxWidth', $title_max_width_desktop);
    $subtitle_weight_desktop = (int) nb_bento_feed_entity_value($subtitle_entity, 'desktop', 'weight', 400);
    $subtitle_weight_mobile = (int) nb_bento_feed_entity_value($subtitle_entity, 'mobile', 'weight', $subtitle_weight_desktop);
    $subtitle_size_desktop = (int) nb_bento_feed_entity_value($subtitle_entity, 'desktop', 'fontSize', 16);
    $subtitle_size_mobile = (int) nb_bento_feed_entity_value($subtitle_entity, 'mobile', 'fontSize', 14);
    $subtitle_margin_bottom_desktop = (int) nb_bento_feed_entity_value($subtitle_entity, 'desktop', 'marginBottom', 0);
    $subtitle_margin_bottom_mobile = (int) nb_bento_feed_entity_value($subtitle_entity, 'mobile', 'marginBottom', 0);
    $subtitle_color_desktop = nb_block_css_color((string) nb_bento_feed_entity_value($subtitle_entity, 'desktop', 'color', ''));
    $subtitle_color_mobile = nb_block_css_color((string) nb_bento_feed_entity_value($subtitle_entity, 'mobile', 'color', $subtitle_color_desktop));
    $subtitle_line_height_desktop = ((float) nb_bento_feed_entity_value($subtitle_entity, 'desktop', 'lineHeightPercent', 155)) / 100;
    $subtitle_line_height_mobile = ((float) nb_bento_feed_entity_value($subtitle_entity, 'mobile', 'lineHeightPercent', $subtitle_line_height_desktop * 100)) / 100;
    $subtitle_letter_spacing_desktop = (float) nb_bento_feed_entity_value($subtitle_entity, 'desktop', 'letterSpacing', 0);
    $subtitle_letter_spacing_mobile = (float) nb_bento_feed_entity_value($subtitle_entity, 'mobile', 'letterSpacing', $subtitle_letter_spacing_desktop);
    $subtitle_max_width_desktop = (int) nb_bento_feed_entity_value($subtitle_entity, 'desktop', 'maxWidth', 760);
    $subtitle_max_width_mobile = (int) nb_bento_feed_entity_value($subtitle_entity, 'mobile', 'maxWidth', $subtitle_max_width_desktop);
    $meta_weight_desktop = (int) nb_bento_feed_entity_value($meta_entity, 'desktop', 'weight', 600);
    $meta_weight_mobile = (int) nb_bento_feed_entity_value($meta_entity, 'mobile', 'weight', $meta_weight_desktop);
    $meta_size_desktop = (int) nb_bento_feed_entity_value($meta_entity, 'desktop', 'fontSize', 11);
    $meta_size_mobile = (int) nb_bento_feed_entity_value($meta_entity, 'mobile', 'fontSize', 11);
    $meta_color_desktop = nb_block_css_color((string) nb_bento_feed_entity_value($meta_entity, 'desktop', 'color', ''));
    $meta_color_mobile = nb_block_css_color((string) nb_bento_feed_entity_value($meta_entity, 'mobile', 'color', $meta_color_desktop));
    $meta_line_height_desktop = ((float) nb_bento_feed_entity_value($meta_entity, 'desktop', 'lineHeightPercent', 130)) / 100;
    $meta_line_height_mobile = ((float) nb_bento_feed_entity_value($meta_entity, 'mobile', 'lineHeightPercent', $meta_line_height_desktop * 100)) / 100;
    $meta_letter_spacing_desktop = (float) nb_bento_feed_entity_value($meta_entity, 'desktop', 'letterSpacing', 1);
    $meta_letter_spacing_mobile = (float) nb_bento_feed_entity_value($meta_entity, 'mobile', 'letterSpacing', $meta_letter_spacing_desktop);
    $item_title_weight_desktop = (int) nb_bento_feed_entity_value($item_title_entity, 'desktop', 'weight', 700);
    $item_title_weight_mobile = (int) nb_bento_feed_entity_value($item_title_entity, 'mobile', 'weight', $item_title_weight_desktop);
    $item_title_size_desktop = (int) nb_bento_feed_entity_value($item_title_entity, 'desktop', 'fontSize', 22);
    $item_title_size_mobile = (int) nb_bento_feed_entity_value($item_title_entity, 'mobile', 'fontSize', 18);
    $item_title_color_desktop = nb_block_css_color((string) nb_bento_feed_entity_value($item_title_entity, 'desktop', 'color', ''));
    $item_title_color_mobile = nb_block_css_color((string) nb_bento_feed_entity_value($item_title_entity, 'mobile', 'color', $item_title_color_desktop));
    $item_title_line_height_desktop = ((float) nb_bento_feed_entity_value($item_title_entity, 'desktop', 'lineHeightPercent', 118)) / 100;
    $item_title_line_height_mobile = ((float) nb_bento_feed_entity_value($item_title_entity, 'mobile', 'lineHeightPercent', $item_title_line_height_desktop * 100)) / 100;
    $item_title_letter_spacing_desktop = (float) nb_bento_feed_entity_value($item_title_entity, 'desktop', 'letterSpacing', 0);
    $item_title_letter_spacing_mobile = (float) nb_bento_feed_entity_value($item_title_entity, 'mobile', 'letterSpacing', $item_title_letter_spacing_desktop);
    $item_text_weight_desktop = (int) nb_bento_feed_entity_value($item_text_entity, 'desktop', 'weight', 400);
    $item_text_weight_mobile = (int) nb_bento_feed_entity_value($item_text_entity, 'mobile', 'weight', $item_text_weight_desktop);
    $item_text_size_desktop = (int) nb_bento_feed_entity_value($item_text_entity, 'desktop', 'fontSize', 15);
    $item_text_size_mobile = (int) nb_bento_feed_entity_value($item_text_entity, 'mobile', 'fontSize', 14);
    $item_text_color_desktop = nb_block_css_color((string) nb_bento_feed_entity_value($item_text_entity, 'desktop', 'color', ''));
    $item_text_color_mobile = nb_block_css_color((string) nb_bento_feed_entity_value($item_text_entity, 'mobile', 'color', $item_text_color_desktop));
    $item_text_line_height_desktop = ((float) nb_bento_feed_entity_value($item_text_entity, 'desktop', 'lineHeightPercent', 150)) / 100;
    $item_text_line_height_mobile = ((float) nb_bento_feed_entity_value($item_text_entity, 'mobile', 'lineHeightPercent', $item_text_line_height_desktop * 100)) / 100;
    $item_text_letter_spacing_desktop = (float) nb_bento_feed_entity_value($item_text_entity, 'desktop', 'letterSpacing', 0);
    $item_text_letter_spacing_mobile = (float) nb_bento_feed_entity_value($item_text_entity, 'mobile', 'letterSpacing', $item_text_letter_spacing_desktop);
    $item_link_weight_desktop = (int) nb_bento_feed_entity_value($item_link_entity, 'desktop', 'weight', 700);
    $item_link_weight_mobile = (int) nb_bento_feed_entity_value($item_link_entity, 'mobile', 'weight', $item_link_weight_desktop);
    $item_link_size_desktop = (int) nb_bento_feed_entity_value($item_link_entity, 'desktop', 'fontSize', 12);
    $item_link_size_mobile = (int) nb_bento_feed_entity_value($item_link_entity, 'mobile', 'fontSize', 12);
    $item_link_color_desktop = nb_block_css_color((string) nb_bento_feed_entity_value($item_link_entity, 'desktop', 'color', ''));
    $item_link_color_mobile = nb_block_css_color((string) nb_bento_feed_entity_value($item_link_entity, 'mobile', 'color', $item_link_color_desktop));
    $item_link_line_height_desktop = ((float) nb_bento_feed_entity_value($item_link_entity, 'desktop', 'lineHeightPercent', 120)) / 100;
    $item_link_line_height_mobile = ((float) nb_bento_feed_entity_value($item_link_entity, 'mobile', 'lineHeightPercent', $item_link_line_height_desktop * 100)) / 100;
    $item_link_letter_spacing_desktop = (float) nb_bento_feed_entity_value($item_link_entity, 'desktop', 'letterSpacing', 1);
    $item_link_letter_spacing_mobile = (float) nb_bento_feed_entity_value($item_link_entity, 'mobile', 'letterSpacing', $item_link_letter_spacing_desktop);
    $media_aspect_ratio = (string) ($media_entity['aspectRatio'] ?? '4:3');
    $media_object_fit = (string) ($media_entity['objectFit'] ?? 'cover');
    $media_radius = (int) ($media_entity['radius'] ?? 0);
    $item_surface_variant = in_array($item_surface['variant'] ?? 'card', ['card', 'plain'], true)
        ? (string) ($item_surface['variant'] ?? 'card') : 'card';
    $item_surface_radius = (int) ($item_surface['radius'] ?? 0);
    $item_surface_border_width = (int) ($item_surface['borderWidth'] ?? 1);
    $item_surface_border_color = nb_block_css_color((string) ($item_surface['borderColor'] ?? '#d9dde4'), '#d9dde4');
    $item_surface_shadow = in_array($item_surface['shadow'] ?? 'none', ['none', 'sm', 'md', 'lg'], true)
        ? (string) ($item_surface['shadow'] ?? 'none') : 'none';
} else {
    $theme = in_array($props['theme'] ?? 'light', ['light', 'alt', 'dark'], true) ? (string) ($props['theme'] ?? 'light') : 'light';
    $align = in_array($props['align'] ?? 'left', ['left', 'center'], true) ? (string) ($props['align'] ?? 'left') : 'left';
    $layout_preset = in_array($props['layout_preset'] ?? 'editorial_mix', ['editorial_mix', 'feature_stack'], true) ? (string) ($props['layout_preset'] ?? 'editorial_mix') : 'editorial_mix';
    $background_style = nb_block_build_background_style([
        'mode' => $props['background_mode'] ?? 'theme',
        'color' => $props['background_color'] ?? '',
        'gradientFrom' => $props['background_gradient_from'] ?? '',
        'gradientTo' => $props['background_gradient_to'] ?? '',
        'gradientAngle' => $props['background_gradient_angle'] ?? 135,
        'image' => $props['background_image'] ?? '',
        'imagePosition' => $props['background_image_position'] ?? 'center center',
        'imageSize' => $props['background_image_size'] ?? 'cover',
        'imageRepeat' => $props['background_image_repeat'] ?? 'no-repeat',
        'overlayColor' => $props['background_overlay_color'] ?? '#0f172a',
        'overlayOpacity' => $props['background_overlay_opacity'] ?? 0,
    ]);
    $reveal = nb_block_get_reveal_settings($props);
    $heading = trim((string) ($props['heading'] ?? 'Компактная бенто-лента'));
    $intro = trim((string) ($props['subheading'] ?? ''));
    $section_link_label = trim((string) ($props['section_link_label'] ?? 'Все материалы'));
    $section_link_url = trim((string) ($props['section_link_url'] ?? '/news'));
    $items_source = is_array($props['items'] ?? null) ? $props['items'] : [];
    $title_visible = nb_bento_feed_visible($props['title_visible'] ?? '1', true);
    $subtitle_visible = nb_bento_feed_visible($props['subtitle_visible'] ?? '1', true);
    $show_more_link = nb_bento_feed_visible($props['show_more_link'] ?? '1', true);
    $show_image = nb_bento_feed_visible($props['show_image'] ?? '1', true);
    $show_category = nb_bento_feed_visible($props['show_category'] ?? '1', true);
    $show_excerpt = nb_bento_feed_visible($props['show_excerpt'] ?? '1', true);
    $show_item_link = nb_bento_feed_visible($props['show_item_link'] ?? '1', true);
    $show_date = nb_bento_feed_visible($props['show_date'] ?? '1', true);
    $show_views = nb_bento_feed_visible($props['show_views'] ?? '1', true);
    $show_comments = nb_bento_feed_visible($props['show_comments'] ?? '1', true);
    $content_width = nb_bento_feed_prop_int($props, 'content_width', 1360, 320, 1600);
    $padding_top_desktop = nb_bento_feed_prop_int($props, 'padding_top_desktop', 0, 0, 240);
    $padding_bottom_desktop = nb_bento_feed_prop_int($props, 'padding_bottom_desktop', 0, 0, 240);
    $padding_top_mobile = nb_bento_feed_prop_int($props, 'padding_top_mobile', 0, 0, 240);
    $padding_bottom_mobile = nb_bento_feed_prop_int($props, 'padding_bottom_mobile', 0, 0, 240);
    $columns_desktop = nb_bento_feed_prop_int($props, 'columns_desktop', 3, 2, 4);
    $columns_mobile = nb_bento_feed_prop_int($props, 'columns_mobile', 1, 1, 2);
    $card_gap_desktop = nb_bento_feed_prop_int($props, 'card_gap_desktop', 0, 0, 80);
    $card_gap_mobile = nb_bento_feed_prop_int($props, 'card_gap_mobile', 0, 0, 80);
    $header_gap_desktop = nb_bento_feed_prop_int($props, 'header_gap_desktop', 18, 0, 80);
    $header_gap_mobile = nb_bento_feed_prop_int($props, 'header_gap_mobile', 14, 0, 80);
    $collection_mode = in_array($props['collection_mode'] ?? 'load_more', ['all', 'load_more', 'pagination'], true) ? (string) ($props['collection_mode'] ?? 'load_more') : 'load_more';
    $items_per_page = nb_bento_feed_prop_int($props, 'items_per_page', 4, 1, 48);
    $items_initial = nb_bento_feed_prop_int($props, 'items_initial', $items_per_page, 1, 48);
    $load_more_label = trim((string) ($props['load_more_label'] ?? 'Показать ещё'));
    $show_bottom_navigation = nb_bento_feed_visible($props['show_bottom_navigation'] ?? '1', true);
    $heading_tag = htmlspecialchars((string) ($props['heading_tag'] ?? 'h2'), ENT_QUOTES, 'UTF-8');
    $title_weight_desktop = nb_bento_feed_prop_int($props, 'title_weight_desktop', (int) nb_bento_feed_prop_value($props, ['title_weight'], 800), 100, 900);
    $title_weight_mobile = nb_bento_feed_prop_int($props, 'title_weight_mobile', $title_weight_desktop, 100, 900);
    $title_size_desktop = nb_bento_feed_prop_int($props, 'title_size_desktop', 44, 12, 120);
    $title_size_mobile = nb_bento_feed_prop_int($props, 'title_size_mobile', 30, 12, 120);
    $title_margin_bottom_desktop = nb_bento_feed_prop_int($props, 'title_margin_bottom_desktop', 0, 0, 240);
    $title_margin_bottom_mobile = nb_bento_feed_prop_int($props, 'title_margin_bottom_mobile', 0, 0, 240);
    $title_color_desktop = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['title_color_desktop', 'title_color'], ''));
    $title_color_mobile = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['title_color_mobile'], $title_color_desktop));
    $title_line_height_desktop = nb_bento_feed_prop_int($props, 'title_line_height_percent_desktop', (int) nb_bento_feed_prop_value($props, ['title_line_height_percent'], 104), 80, 220) / 100;
    $title_line_height_mobile = nb_bento_feed_prop_int($props, 'title_line_height_percent_mobile', (int) round($title_line_height_desktop * 100), 80, 220) / 100;
    $title_letter_spacing_desktop = (float) nb_bento_feed_prop_value($props, ['title_letter_spacing_desktop', 'title_letter_spacing'], -1);
    $title_letter_spacing_mobile = (float) nb_bento_feed_prop_value($props, ['title_letter_spacing_mobile'], $title_letter_spacing_desktop);
    $title_max_width_desktop = nb_bento_feed_prop_int($props, 'title_max_width_desktop', (int) nb_bento_feed_prop_value($props, ['title_max_width'], 920), 240, 1440);
    $title_max_width_mobile = nb_bento_feed_prop_int($props, 'title_max_width_mobile', $title_max_width_desktop, 240, 1440);
    $subtitle_weight_desktop = nb_bento_feed_prop_int($props, 'subtitle_weight_desktop', 400, 100, 900);
    $subtitle_weight_mobile = nb_bento_feed_prop_int($props, 'subtitle_weight_mobile', $subtitle_weight_desktop, 100, 900);
    $subtitle_size_desktop = nb_bento_feed_prop_int($props, 'subtitle_size_desktop', 16, 10, 80);
    $subtitle_size_mobile = nb_bento_feed_prop_int($props, 'subtitle_size_mobile', 14, 10, 80);
    $subtitle_margin_bottom_desktop = nb_bento_feed_prop_int($props, 'subtitle_margin_bottom_desktop', 0, 0, 240);
    $subtitle_margin_bottom_mobile = nb_bento_feed_prop_int($props, 'subtitle_margin_bottom_mobile', 0, 0, 240);
    $subtitle_color_desktop = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['subtitle_color_desktop', 'subtitle_color'], ''));
    $subtitle_color_mobile = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['subtitle_color_mobile'], $subtitle_color_desktop));
    $subtitle_line_height_desktop = nb_bento_feed_prop_int($props, 'subtitle_line_height_percent_desktop', (int) nb_bento_feed_prop_value($props, ['subtitle_line_height_percent'], 155), 80, 240) / 100;
    $subtitle_line_height_mobile = nb_bento_feed_prop_int($props, 'subtitle_line_height_percent_mobile', (int) round($subtitle_line_height_desktop * 100), 80, 240) / 100;
    $subtitle_letter_spacing_desktop = (float) nb_bento_feed_prop_value($props, ['subtitle_letter_spacing_desktop', 'subtitle_letter_spacing'], 0);
    $subtitle_letter_spacing_mobile = (float) nb_bento_feed_prop_value($props, ['subtitle_letter_spacing_mobile'], $subtitle_letter_spacing_desktop);
    $subtitle_max_width_desktop = nb_bento_feed_prop_int($props, 'subtitle_max_width_desktop', (int) nb_bento_feed_prop_value($props, ['subtitle_max_width'], 760), 240, 1440);
    $subtitle_max_width_mobile = nb_bento_feed_prop_int($props, 'subtitle_max_width_mobile', $subtitle_max_width_desktop, 240, 1440);
    $meta_weight_desktop = nb_bento_feed_prop_int($props, 'meta_weight_desktop', 600, 100, 900);
    $meta_weight_mobile = nb_bento_feed_prop_int($props, 'meta_weight_mobile', $meta_weight_desktop, 100, 900);
    $meta_size_desktop = nb_bento_feed_prop_int($props, 'meta_size_desktop', 11, 10, 40);
    $meta_size_mobile = nb_bento_feed_prop_int($props, 'meta_size_mobile', 11, 10, 40);
    $meta_color_desktop = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['meta_color_desktop', 'meta_color'], ''));
    $meta_color_mobile = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['meta_color_mobile'], $meta_color_desktop));
    $meta_line_height_desktop = nb_bento_feed_prop_int($props, 'meta_line_height_percent_desktop', (int) nb_bento_feed_prop_value($props, ['meta_line_height_percent'], 130), 80, 240) / 100;
    $meta_line_height_mobile = nb_bento_feed_prop_int($props, 'meta_line_height_percent_mobile', (int) round($meta_line_height_desktop * 100), 80, 240) / 100;
    $meta_letter_spacing_desktop = (float) nb_bento_feed_prop_value($props, ['meta_letter_spacing_desktop', 'meta_letter_spacing'], 1);
    $meta_letter_spacing_mobile = (float) nb_bento_feed_prop_value($props, ['meta_letter_spacing_mobile'], $meta_letter_spacing_desktop);
    $item_title_weight_desktop = nb_bento_feed_prop_int($props, 'item_title_weight_desktop', (int) nb_bento_feed_prop_value($props, ['item_title_weight'], 700), 100, 900);
    $item_title_weight_mobile = nb_bento_feed_prop_int($props, 'item_title_weight_mobile', $item_title_weight_desktop, 100, 900);
    $item_title_size_desktop = nb_bento_feed_prop_int($props, 'item_title_size_desktop', 22, 10, 80);
    $item_title_size_mobile = nb_bento_feed_prop_int($props, 'item_title_size_mobile', 18, 10, 80);
    $item_title_color_desktop = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['item_title_color_desktop', 'item_title_color'], ''));
    $item_title_color_mobile = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['item_title_color_mobile'], $item_title_color_desktop));
    $item_title_line_height_desktop = nb_bento_feed_prop_int($props, 'item_title_line_height_percent_desktop', (int) nb_bento_feed_prop_value($props, ['item_title_line_height_percent'], 118), 80, 220) / 100;
    $item_title_line_height_mobile = nb_bento_feed_prop_int($props, 'item_title_line_height_percent_mobile', (int) round($item_title_line_height_desktop * 100), 80, 220) / 100;
    $item_title_letter_spacing_desktop = (float) nb_bento_feed_prop_value($props, ['item_title_letter_spacing_desktop', 'item_title_letter_spacing'], 0);
    $item_title_letter_spacing_mobile = (float) nb_bento_feed_prop_value($props, ['item_title_letter_spacing_mobile'], $item_title_letter_spacing_desktop);
    $item_text_weight_desktop = nb_bento_feed_prop_int($props, 'item_text_weight_desktop', (int) nb_bento_feed_prop_value($props, ['item_text_weight'], 400), 100, 900);
    $item_text_weight_mobile = nb_bento_feed_prop_int($props, 'item_text_weight_mobile', $item_text_weight_desktop, 100, 900);
    $item_text_size_desktop = nb_bento_feed_prop_int($props, 'item_text_size_desktop', 15, 10, 80);
    $item_text_size_mobile = nb_bento_feed_prop_int($props, 'item_text_size_mobile', 14, 10, 80);
    $item_text_color_desktop = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['item_text_color_desktop', 'item_text_color'], ''));
    $item_text_color_mobile = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['item_text_color_mobile'], $item_text_color_desktop));
    $item_text_line_height_desktop = nb_bento_feed_prop_int($props, 'item_text_line_height_percent_desktop', (int) nb_bento_feed_prop_value($props, ['item_text_line_height_percent'], 150), 80, 260) / 100;
    $item_text_line_height_mobile = nb_bento_feed_prop_int($props, 'item_text_line_height_percent_mobile', (int) round($item_text_line_height_desktop * 100), 80, 260) / 100;
    $item_text_letter_spacing_desktop = (float) nb_bento_feed_prop_value($props, ['item_text_letter_spacing_desktop', 'item_text_letter_spacing'], 0);
    $item_text_letter_spacing_mobile = (float) nb_bento_feed_prop_value($props, ['item_text_letter_spacing_mobile'], $item_text_letter_spacing_desktop);
    $item_link_weight_desktop = nb_bento_feed_prop_int($props, 'item_link_weight_desktop', (int) nb_bento_feed_prop_value($props, ['item_link_weight'], 700), 100, 900);
    $item_link_weight_mobile = nb_bento_feed_prop_int($props, 'item_link_weight_mobile', $item_link_weight_desktop, 100, 900);
    $item_link_size_desktop = nb_bento_feed_prop_int($props, 'item_link_size_desktop', 12, 10, 80);
    $item_link_size_mobile = nb_bento_feed_prop_int($props, 'item_link_size_mobile', 12, 10, 80);
    $item_link_color_desktop = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['item_link_color_desktop', 'item_link_color'], ''));
    $item_link_color_mobile = nb_block_css_color((string) nb_bento_feed_prop_value($props, ['item_link_color_mobile'], $item_link_color_desktop));
    $item_link_line_height_desktop = nb_bento_feed_prop_int($props, 'item_link_line_height_percent_desktop', (int) nb_bento_feed_prop_value($props, ['item_link_line_height_percent'], 120), 80, 220) / 100;
    $item_link_line_height_mobile = nb_bento_feed_prop_int($props, 'item_link_line_height_percent_mobile', (int) round($item_link_line_height_desktop * 100), 80, 220) / 100;
    $item_link_letter_spacing_desktop = (float) nb_bento_feed_prop_value($props, ['item_link_letter_spacing_desktop', 'item_link_letter_spacing'], 1);
    $item_link_letter_spacing_mobile = (float) nb_bento_feed_prop_value($props, ['item_link_letter_spacing_mobile'], $item_link_letter_spacing_desktop);
    $media_aspect_ratio = (string) ($props['media_aspect_ratio'] ?? '4:3');
    $media_object_fit = (string) ($props['media_object_fit'] ?? 'cover');
    $media_radius = nb_bento_feed_prop_int($props, 'media_radius', 0, 0, 80);
    $item_surface_variant = in_array($props['item_surface_variant'] ?? 'card', ['card', 'plain'], true) ? (string) ($props['item_surface_variant'] ?? 'card') : 'card';
    $item_surface_radius = nb_bento_feed_prop_int($props, 'item_surface_radius', 0, 0, 80);
    $item_surface_border_width = nb_bento_feed_prop_int($props, 'item_surface_border_width', 1, 0, 20);
    $item_surface_border_color = nb_block_css_color((string) ($props['item_surface_border_color'] ?? '#d9dde4'), '#d9dde4');
    $item_surface_shadow = in_array($props['item_surface_shadow'] ?? 'none', ['none', 'sm', 'md', 'lg'], true) ? (string) ($props['item_surface_shadow'] ?? 'none') : 'none';
}

$items = [];
foreach ($items_source as $item) {
    if (!is_array($item)) {
        continue;
    }
    $normalized_item = nb_bento_feed_normalize_item($item);
    if ($normalized_item) {
        $items[] = $normalized_item;
    }
}

$media_aspect_ratio_css = '4 / 3';
if ($media_aspect_ratio === '16:9') {
    $media_aspect_ratio_css = '16 / 9';
} elseif ($media_aspect_ratio === '1:1') {
    $media_aspect_ratio_css = '1 / 1';
} elseif ($media_aspect_ratio === '3:4') {
    $media_aspect_ratio_css = '3 / 4';
} elseif ($media_aspect_ratio === 'auto') {
    $media_aspect_ratio_css = 'auto';
}

$section_class = 'nb-section nb-bento-feed nb-bento-feed--align-' . $align . ' nb-bento-feed--surface-' . $item_surface_variant . ' nb-bento-feed--preset-' . $layout_preset . ($reveal['class'] ?? '');
$theme_attr = $theme !== 'light' ? ' data-nb-theme="' . htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') . '"' : '';
$section_style = '--nb-bento-content-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-bento-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-padding-top-mobile:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-padding-bottom-mobile:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-columns:' . $columns_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-columns-mobile:' . $columns_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-card-gap:' . $card_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-card-gap-mobile:' . $card_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-header-gap:' . $header_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-header-gap-mobile:' . $header_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-weight:' . $title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-weight-mobile:' . $title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-margin-bottom:' . $title_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-margin-bottom-mobile:' . $title_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-line-height:' . max(0.8, min(2.2, $title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-line-height-mobile:' . max(0.8, min(2.2, $title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-letter-spacing:' . $title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-letter-spacing-mobile:' . $title_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-max-width:' . $title_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-title-max-width-mobile:' . $title_max_width_mobile . 'px;');
$section_style = $title_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-bento-title-color:' . $title_color_desktop . ';') : $section_style;
$section_style = $title_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-bento-title-color-mobile:' . $title_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-weight:' . $subtitle_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-weight-mobile:' . $subtitle_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-margin-bottom:' . $subtitle_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-margin-bottom-mobile:' . $subtitle_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-line-height:' . max(0.8, min(2.4, $subtitle_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-line-height-mobile:' . max(0.8, min(2.4, $subtitle_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-letter-spacing:' . $subtitle_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-letter-spacing-mobile:' . $subtitle_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-max-width:' . $subtitle_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-subtitle-max-width-mobile:' . $subtitle_max_width_mobile . 'px;');
$section_style = $subtitle_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-bento-subtitle-color:' . $subtitle_color_desktop . ';') : $section_style;
$section_style = $subtitle_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-bento-subtitle-color-mobile:' . $subtitle_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-bento-meta-weight:' . $meta_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-meta-weight-mobile:' . $meta_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-meta-size:' . $meta_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-meta-size-mobile:' . $meta_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-meta-line-height:' . max(0.8, min(2.4, $meta_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-meta-line-height-mobile:' . max(0.8, min(2.4, $meta_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-meta-letter-spacing:' . $meta_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-meta-letter-spacing-mobile:' . $meta_letter_spacing_mobile . 'px;');
$section_style = $meta_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-bento-meta-color:' . $meta_color_desktop . ';') : $section_style;
$section_style = $meta_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-bento-meta-color-mobile:' . $meta_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-bento-item-title-weight:' . $item_title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-title-weight-mobile:' . $item_title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-title-size:' . $item_title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-title-size-mobile:' . $item_title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-title-line-height:' . max(0.8, min(2.2, $item_title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-title-line-height-mobile:' . max(0.8, min(2.2, $item_title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-title-letter-spacing:' . $item_title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-title-letter-spacing-mobile:' . $item_title_letter_spacing_mobile . 'px;');
$section_style = $item_title_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-bento-item-title-color:' . $item_title_color_desktop . ';') : $section_style;
$section_style = $item_title_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-bento-item-title-color-mobile:' . $item_title_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-bento-item-text-weight:' . $item_text_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-text-weight-mobile:' . $item_text_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-text-size:' . $item_text_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-text-size-mobile:' . $item_text_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-text-line-height:' . max(0.8, min(2.6, $item_text_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-text-line-height-mobile:' . max(0.8, min(2.6, $item_text_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-text-letter-spacing:' . $item_text_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-text-letter-spacing-mobile:' . $item_text_letter_spacing_mobile . 'px;');
$section_style = $item_text_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-bento-item-text-color:' . $item_text_color_desktop . ';') : $section_style;
$section_style = $item_text_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-bento-item-text-color-mobile:' . $item_text_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-bento-item-link-weight:' . $item_link_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-link-weight-mobile:' . $item_link_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-link-size:' . $item_link_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-link-size-mobile:' . $item_link_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-link-line-height:' . max(0.8, min(2.2, $item_link_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-link-line-height-mobile:' . max(0.8, min(2.2, $item_link_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-link-letter-spacing:' . $item_link_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-item-link-letter-spacing-mobile:' . $item_link_letter_spacing_mobile . 'px;');
$section_style = $item_link_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-bento-item-link-color:' . $item_link_color_desktop . ';') : $section_style;
$section_style = $item_link_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-bento-item-link-color-mobile:' . $item_link_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-bento-media-aspect-ratio:' . $media_aspect_ratio_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-media-object-fit:' . $media_object_fit . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-media-radius:' . $media_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-card-radius:' . $item_surface_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-card-border-width:' . $item_surface_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-bento-card-border-color:' . $item_surface_border_color . ';');
$section_style = nb_block_append_style($section_style, '--nb-bento-card-shadow:' . 'var(--nb-shadow-' . $item_surface_shadow . ', none);');
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style'] ?? '');
$block_dom_id = 'block-' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) $block_uid);
$intro_html = $intro !== '' ? nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) : '';
?>
<section
    class="<?= htmlspecialchars($section_class, ENT_QUOTES, 'UTF-8') ?>"
    id="<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-block="bento_feed"
    data-nb-entity="section"
    data-nb-grid-desktop="<?= (int) $columns_desktop ?>"
    data-nb-grid-mobile="<?= (int) $columns_mobile ?>"
    data-nb-collection-mode="<?= htmlspecialchars($collection_mode, ENT_QUOTES, 'UTF-8') ?>"
    <?= $theme_attr ?>
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <style>
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed {
            position: relative;
            padding-top: var(--nb-bento-padding-top, 0);
            padding-bottom: var(--nb-bento-padding-bottom, 0);
            color: var(--nb-color-text, #111827);
            background: var(--nb-section-background, transparent);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__container {
            width: min(100%, var(--nb-bento-content-width, 1360px));
            display: grid;
            gap: var(--nb-bento-header-gap, 18px);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: var(--nb-bento-header-gap, 18px);
            padding: clamp(1rem, 2vw, 1.4rem);
            border: var(--nb-bento-card-border-width, 1px) solid var(--nb-bento-card-border-color, #d9dde4);
            border-radius: var(--nb-bento-card-radius, 0);
            box-shadow: var(--nb-bento-card-shadow, none);
            background: var(--nb-color-surface, #fff);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--align-center .nb-bento-feed__header {
            align-items: center;
            flex-direction: column;
            text-align: center;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__copy {
            display: grid;
            gap: .4rem;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__title {
            margin: 0;
            margin-bottom: var(--nb-bento-title-margin-bottom, 0);
            max-width: min(100%, var(--nb-bento-title-max-width, 920px));
            font-family: var(--nb-font-head, inherit);
            font-size: var(--nb-bento-title-size, 44px);
            font-weight: var(--nb-bento-title-weight, 800);
            line-height: var(--nb-bento-title-line-height, 1.04);
            letter-spacing: var(--nb-bento-title-letter-spacing, -1px);
            color: var(--nb-bento-title-color, var(--nb-color-text, #111827));
            text-wrap: balance;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__subtitle {
            margin: 0;
            margin-bottom: var(--nb-bento-subtitle-margin-bottom, 0);
            max-width: 68ch;
            max-width: min(100%, var(--nb-bento-subtitle-max-width, 760px));
            color: var(--nb-bento-subtitle-color, var(--nb-color-text-muted, #5b6472));
            font-size: var(--nb-bento-subtitle-size, 16px);
            font-weight: var(--nb-bento-subtitle-weight, 400);
            line-height: var(--nb-bento-subtitle-line-height, 1.55);
            letter-spacing: var(--nb-bento-subtitle-letter-spacing, 0);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__more {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            color: var(--nb-color-text, #111827);
            font-size: .84rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-decoration: none;
            text-transform: uppercase;
            white-space: nowrap;
            border-bottom: 1px solid currentColor;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__more::after {
            content: '→';
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__grid {
            display: grid;
            grid-template-columns: repeat(var(--nb-bento-columns, 3), minmax(0, 1fr));
            gap: var(--nb-bento-card-gap, 0);
            border-top: 1px solid var(--nb-bento-card-border-color, #d9dde4);
            border-left: 1px solid var(--nb-bento-card-border-color, #d9dde4);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__card {
            display: grid;
            grid-template-rows: auto 1fr;
            min-height: 100%;
            background: var(--nb-color-surface, #fff);
            border-right: var(--nb-bento-card-border-width, 1px) solid var(--nb-bento-card-border-color, #d9dde4);
            border-bottom: var(--nb-bento-card-border-width, 1px) solid var(--nb-bento-card-border-color, #d9dde4);
            border-radius: var(--nb-bento-card-radius, 0);
            box-shadow: var(--nb-bento-card-shadow, none);
            overflow: hidden;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--surface-plain .nb-bento-feed__card,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--surface-plain .nb-bento-feed__header,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--surface-plain .nb-bento-feed__footer {
            box-shadow: none;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--preset-editorial_mix .nb-bento-feed__card:first-child {
            grid-column: span 2;
            grid-row: span 2;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--preset-feature_stack .nb-bento-feed__card:first-child {
            grid-column: span 2;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--preset-feature_stack .nb-bento-feed__card:nth-child(2),
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--preset-feature_stack .nb-bento-feed__card:nth-child(3) {
            min-height: 20rem;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__media {
            display: block;
            aspect-ratio: var(--nb-bento-media-aspect-ratio, 4/3);
            overflow: hidden;
            border-radius: var(--nb-bento-media-radius, 0);
            background: color-mix(in srgb, var(--nb-color-text, #111827) 6%, var(--nb-color-surface, #fff));
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__media img {
            width: 100%;
            height: 100%;
            object-fit: var(--nb-bento-media-object-fit, cover);
            display: block;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__body {
            display: grid;
            align-content: start;
            gap: .72rem;
            padding: clamp(1rem, 1.8vw, 1.25rem);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__category {
            display: inline-flex;
            width: fit-content;
            color: var(--nb-bento-meta-color, var(--nb-color-accent, #d92e1c));
            font-size: var(--nb-bento-meta-size, 11px);
            font-weight: var(--nb-bento-meta-weight, 600);
            line-height: var(--nb-bento-meta-line-height, 1.15);
            letter-spacing: var(--nb-bento-meta-letter-spacing, 1px);
            text-transform: uppercase;
            text-decoration: none;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__item-title {
            margin: 0;
            font-family: var(--nb-font-head, inherit);
            font-size: var(--nb-bento-item-title-size, 22px);
            font-weight: var(--nb-bento-item-title-weight, 700);
            line-height: var(--nb-bento-item-title-line-height, 1.18);
            letter-spacing: var(--nb-bento-item-title-letter-spacing, 0);
            color: var(--nb-bento-item-title-color, var(--nb-color-text, #111827));
            text-wrap: balance;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__item-title a {
            color: inherit;
            text-decoration: none;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__item-text {
            color: var(--nb-bento-item-text-color, var(--nb-color-text-muted, #5b6472));
            font-size: var(--nb-bento-item-text-size, 15px);
            font-weight: var(--nb-bento-item-text-weight, 400);
            line-height: var(--nb-bento-item-text-line-height, 1.5);
            letter-spacing: var(--nb-bento-item-text-letter-spacing, 0);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__item-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            width: fit-content;
            color: var(--nb-bento-item-link-color, var(--nb-color-text, #111827));
            font-size: var(--nb-bento-item-link-size, 12px);
            font-weight: var(--nb-bento-item-link-weight, 700);
            line-height: var(--nb-bento-item-link-line-height, 1.2);
            letter-spacing: var(--nb-bento-item-link-letter-spacing, 1px);
            text-transform: uppercase;
            text-decoration: none;
            border-bottom: 1px solid currentColor;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__item-link::after {
            content: '→';
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__meta {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem .8rem;
            margin-top: auto;
            color: var(--nb-bento-meta-color, var(--nb-color-text-muted, #5b6472));
            font-size: var(--nb-bento-meta-size, 11px);
            font-weight: var(--nb-bento-meta-weight, 600);
            line-height: var(--nb-bento-meta-line-height, 1.3);
            letter-spacing: var(--nb-bento-meta-letter-spacing, 1px);
            text-transform: uppercase;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__footer {
            display: flex;
            justify-content: center;
            padding: 1rem;
            border: var(--nb-bento-card-border-width, 1px) solid var(--nb-bento-card-border-color, #d9dde4);
            border-top: 0;
            border-radius: var(--nb-bento-card-radius, 0);
            box-shadow: var(--nb-bento-card-shadow, none);
            background: var(--nb-color-surface, #fff);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__button,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__page {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.8rem;
            padding: .78rem 1rem;
            border: 1px solid var(--nb-bento-card-border-color, #d9dde4);
            background: var(--nb-color-surface, #fff);
            color: var(--nb-color-text, #111827);
            font-size: .84rem;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__pagination {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .5rem;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__page.is-active {
            background: var(--nb-color-text, #111827);
            color: var(--nb-color-bg, #fff);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="alt"] .nb-bento-feed__header,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="alt"] .nb-bento-feed__card,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="alt"] .nb-bento-feed__footer {
            background: #f6f2eb;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] {
            color: #f3f5f8;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-bento-feed__header,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-bento-feed__card,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-bento-feed__footer,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-bento-feed__button,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-bento-feed__page {
            background: #12161c;
            color: #f3f5f8;
            border-color: rgba(255,255,255,.14);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-bento-feed__subtitle,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-bento-feed__item-text,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-bento-feed__meta {
            color: #b8c0cc;
        }
        @media (max-width: 1023px) {
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__grid {
                grid-template-columns: repeat(min(2, var(--nb-bento-columns, 3)), minmax(0, 1fr));
            }
        }
        @media (max-width: 767px) {
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed {
                padding-top: var(--nb-bento-padding-top-mobile, var(--nb-bento-padding-top, 0));
                padding-bottom: var(--nb-bento-padding-bottom-mobile, var(--nb-bento-padding-bottom, 0));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__container {
                gap: var(--nb-bento-header-gap-mobile, var(--nb-bento-header-gap, 14px));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__header {
                align-items: flex-start;
                flex-direction: column;
                gap: var(--nb-bento-header-gap-mobile, var(--nb-bento-header-gap, 14px));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__title {
                font-size: var(--nb-bento-title-size-mobile, 30px);
                margin-bottom: var(--nb-bento-title-margin-bottom-mobile, var(--nb-bento-title-margin-bottom, 0));
                max-width: min(100%, var(--nb-bento-title-max-width-mobile, var(--nb-bento-title-max-width, 920px)));
                font-weight: var(--nb-bento-title-weight-mobile, var(--nb-bento-title-weight, 800));
                line-height: var(--nb-bento-title-line-height-mobile, var(--nb-bento-title-line-height, 1.04));
                letter-spacing: var(--nb-bento-title-letter-spacing-mobile, var(--nb-bento-title-letter-spacing, -1px));
                color: var(--nb-bento-title-color-mobile, var(--nb-bento-title-color, var(--nb-color-text, #111827)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__subtitle {
                font-size: var(--nb-bento-subtitle-size-mobile, 14px);
                margin-bottom: var(--nb-bento-subtitle-margin-bottom-mobile, var(--nb-bento-subtitle-margin-bottom, 0));
                max-width: min(100%, var(--nb-bento-subtitle-max-width-mobile, var(--nb-bento-subtitle-max-width, 760px)));
                font-weight: var(--nb-bento-subtitle-weight-mobile, var(--nb-bento-subtitle-weight, 400));
                line-height: var(--nb-bento-subtitle-line-height-mobile, var(--nb-bento-subtitle-line-height, 1.55));
                letter-spacing: var(--nb-bento-subtitle-letter-spacing-mobile, var(--nb-bento-subtitle-letter-spacing, 0));
                color: var(--nb-bento-subtitle-color-mobile, var(--nb-bento-subtitle-color, var(--nb-color-text-muted, #5b6472)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__grid {
                grid-template-columns: repeat(var(--nb-bento-columns-mobile, 1), minmax(0, 1fr));
                gap: var(--nb-bento-card-gap-mobile, var(--nb-bento-card-gap, 0));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--preset-editorial_mix .nb-bento-feed__card:first-child,
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-bento-feed--preset-feature_stack .nb-bento-feed__card:first-child {
                grid-column: auto;
                grid-row: auto;
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__item-title {
                font-size: var(--nb-bento-item-title-size-mobile, 18px);
                font-weight: var(--nb-bento-item-title-weight-mobile, var(--nb-bento-item-title-weight, 700));
                line-height: var(--nb-bento-item-title-line-height-mobile, var(--nb-bento-item-title-line-height, 1.18));
                letter-spacing: var(--nb-bento-item-title-letter-spacing-mobile, var(--nb-bento-item-title-letter-spacing, 0));
                color: var(--nb-bento-item-title-color-mobile, var(--nb-bento-item-title-color, var(--nb-color-text, #111827)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__item-text {
                font-size: var(--nb-bento-item-text-size-mobile, 14px);
                font-weight: var(--nb-bento-item-text-weight-mobile, var(--nb-bento-item-text-weight, 400));
                line-height: var(--nb-bento-item-text-line-height-mobile, var(--nb-bento-item-text-line-height, 1.5));
                letter-spacing: var(--nb-bento-item-text-letter-spacing-mobile, var(--nb-bento-item-text-letter-spacing, 0));
                color: var(--nb-bento-item-text-color-mobile, var(--nb-bento-item-text-color, var(--nb-color-text-muted, #5b6472)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__item-link {
                font-size: var(--nb-bento-item-link-size-mobile, 12px);
                font-weight: var(--nb-bento-item-link-weight-mobile, var(--nb-bento-item-link-weight, 700));
                line-height: var(--nb-bento-item-link-line-height-mobile, var(--nb-bento-item-link-line-height, 1.2));
                letter-spacing: var(--nb-bento-item-link-letter-spacing-mobile, var(--nb-bento-item-link-letter-spacing, 1px));
                color: var(--nb-bento-item-link-color-mobile, var(--nb-bento-item-link-color, var(--nb-color-text, #111827)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-bento-feed__meta {
                font-size: var(--nb-bento-meta-size-mobile, 11px);
                font-weight: var(--nb-bento-meta-weight-mobile, var(--nb-bento-meta-weight, 600));
                line-height: var(--nb-bento-meta-line-height-mobile, var(--nb-bento-meta-line-height, 1.3));
                letter-spacing: var(--nb-bento-meta-letter-spacing-mobile, var(--nb-bento-meta-letter-spacing, 1px));
                color: var(--nb-bento-meta-color-mobile, var(--nb-bento-meta-color, var(--nb-color-text-muted, #5b6472)));
            }
        }
    </style>
    <div class="nb-container nb-bento-feed__container">
        <?php if (($title_visible && $heading !== '') || ($subtitle_visible && $intro_html !== '') || ($show_more_link && $section_link_label !== '' && $section_link_url !== '')): ?>
        <header class="nb-bento-feed__header" data-nb-entity="header">
            <div class="nb-bento-feed__copy">
                <?php if ($title_visible && $heading !== ''): ?>
                <<?= $heading_tag ?> class="nb-bento-feed__title" data-nb-entity="title"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></<?= $heading_tag ?>>
                <?php endif; ?>
                <?php if ($subtitle_visible && $intro_html !== ''): ?>
                <div class="nb-bento-feed__subtitle" data-nb-entity="subtitle"><?= $intro_html ?></div>
                <?php endif; ?>
            </div>
            <?php if ($show_more_link && $section_link_label !== '' && $section_link_url !== ''): ?>
            <a class="nb-bento-feed__more" href="<?= htmlspecialchars($section_link_url, ENT_QUOTES, 'UTF-8') ?>" data-nb-entity="primaryButton"><?= htmlspecialchars($section_link_label, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endif; ?>
        </header>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-bento-feed__grid" data-role="bento-grid" data-nb-entity="items">
            <?php foreach ($items as $index => $item): ?>
            <article class="nb-bento-feed__card" data-role="bento-card" data-order="<?= (int) $index ?>" data-nb-entity="itemSurface">
                <?php if ($show_image && $item['image'] !== ''): ?>
                <a class="nb-bento-feed__media" href="<?= $item['url'] !== '' ? $item['url'] : '#' ?>"<?= $item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                    <img src="<?= $item['image'] ?>" alt="<?= $item['imageAlt'] ?>">
                </a>
                <?php endif; ?>
                <div class="nb-bento-feed__body">
                    <?php if ($show_category && $item['category'] !== ''): ?>
                    <?php if ($item['categoryUrl'] !== ''): ?>
                    <a class="nb-bento-feed__category" href="<?= $item['categoryUrl'] ?>" data-nb-entity="meta"><?= $item['category'] ?></a>
                    <?php else: ?>
                    <div class="nb-bento-feed__category" data-nb-entity="meta"><?= $item['category'] ?></div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($item['title'] !== ''): ?>
                    <h3 class="nb-bento-feed__item-title" data-nb-entity="itemTitle">
                        <?php if ($item['url'] !== ''): ?>
                        <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                        <?php else: ?>
                        <?= $item['title'] ?>
                        <?php endif; ?>
                    </h3>
                    <?php endif; ?>
                    <?php if ($show_excerpt && $item['excerpt'] !== ''): ?>
                    <div class="nb-bento-feed__item-text" data-nb-entity="itemText"><?= $item['excerpt'] ?></div>
                    <?php endif; ?>
                    <?php if ($show_item_link && $item['linkLabel'] !== '' && $item['url'] !== ''): ?>
                    <a class="nb-bento-feed__item-link" href="<?= $item['url'] ?>" data-nb-entity="itemLink"><?= $item['linkLabel'] ?></a>
                    <?php endif; ?>
                    <?php if (($show_date && $item['date'] !== '') || ($show_views && $item['views'] !== '') || ($show_comments && $item['comments'] !== '')): ?>
                    <div class="nb-bento-feed__meta" data-nb-entity="meta">
                        <?php if ($show_date && $item['date'] !== ''): ?><span><?= $item['date'] ?></span><?php endif; ?>
                        <?php if ($show_views && $item['views'] !== ''): ?><span><?= $item['views'] ?></span><?php endif; ?>
                        <?php if ($show_comments && $item['comments'] !== ''): ?><span><?= $item['comments'] ?></span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php if ($show_bottom_navigation): ?>
        <div class="nb-bento-feed__footer" data-role="bento-footer" hidden>
            <button type="button" class="nb-bento-feed__button" data-role="bento-more" hidden><?= htmlspecialchars($load_more_label !== '' ? $load_more_label : 'Показать ещё', ENT_QUOTES, 'UTF-8') ?></button>
            <div class="nb-bento-feed__pagination" data-role="bento-pagination" hidden></div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="nb-bento-feed__header">Лента пока пуста. Добавьте первую карточку.</div>
        <?php endif; ?>
    </div>
    <?php if ($items): ?>
    <script>
    (function() {
        var root = document.getElementById(<?= json_encode($block_dom_id, JSON_UNESCAPED_UNICODE) ?>);
        if (!root) {
            return;
        }

        var cards = Array.prototype.slice.call(root.querySelectorAll('[data-role="bento-card"]'));
        var footer = root.querySelector('[data-role="bento-footer"]');
        var moreButton = root.querySelector('[data-role="bento-more"]');
        var pagination = root.querySelector('[data-role="bento-pagination"]');
        var collectionMode = <?= json_encode($collection_mode, JSON_UNESCAPED_UNICODE) ?>;
        var step = Math.max(1, parseInt(<?= json_encode((int) $items_per_page, JSON_UNESCAPED_UNICODE) ?>, 10) || 1);
        var initial = Math.max(1, parseInt(<?= json_encode((int) $items_initial, JSON_UNESCAPED_UNICODE) ?>, 10) || step);
        var moreLabel = <?= json_encode($load_more_label !== '' ? $load_more_label : 'Показать ещё', JSON_UNESCAPED_UNICODE) ?>;
        var currentPage = 1;
        var visibleCount = initial;

        function buildPageItems(totalPages, page) {
            var items = [];
            if (totalPages <= 7) {
                for (var index = 1; index <= totalPages; index++) {
                    items.push(index);
                }
                return items;
            }

            items.push(1);
            if (page > 3) {
                items.push('dots-start');
            }
            for (var middle = Math.max(2, page - 1); middle <= Math.min(totalPages - 1, page + 1); middle++) {
                items.push(middle);
            }
            if (page < totalPages - 2) {
                items.push('dots-end');
            }
            items.push(totalPages);
            return items;
        }

        function render() {
            var total = cards.length;
            var totalPages = Math.max(1, Math.ceil(total / step));
            var visible = [];

            if (collectionMode === 'pagination') {
                currentPage = Math.min(Math.max(1, currentPage), totalPages);
                visible = cards.slice((currentPage - 1) * step, currentPage * step);
            } else if (collectionMode === 'load_more') {
                visibleCount = Math.min(Math.max(initial, visibleCount), total);
                visible = cards.slice(0, visibleCount);
            } else {
                visible = cards.slice();
            }

            cards.forEach(function(card) {
                card.hidden = visible.indexOf(card) === -1;
            });

            if (!footer) {
                return;
            }

            footer.hidden = true;
            if (moreButton) {
                moreButton.hidden = true;
                moreButton.textContent = moreLabel;
            }
            if (pagination) {
                pagination.hidden = true;
                pagination.innerHTML = '';
            }

            if (collectionMode === 'load_more' && visible.length < total && moreButton) {
                footer.hidden = false;
                moreButton.hidden = false;
                moreButton.textContent = moreLabel;
            }

            if (collectionMode === 'pagination' && totalPages > 1 && pagination) {
                footer.hidden = false;
                pagination.hidden = false;
                pagination.innerHTML = buildPageItems(totalPages, currentPage).map(function(item) {
                    if (typeof item !== 'number') {
                        return '<span class="nb-bento-feed__page">…</span>';
                    }

                    return '<button type="button" class="nb-bento-feed__page' + (item === currentPage ? ' is-active' : '') + '" data-role="bento-page" data-page="' + item + '">' + item + '</button>';
                }).join('');
            }
        }

        if (moreButton) {
            moreButton.addEventListener('click', function() {
                visibleCount += step;
                render();
            });
        }

        root.addEventListener('click', function(event) {
            var page = event.target.closest('[data-role="bento-page"]');
            if (!page) {
                return;
            }

            currentPage = parseInt(page.getAttribute('data-page') || '1', 10) || 1;
            render();
        });

        render();
    })();
    </script>
    <?php endif; ?>
</section>
