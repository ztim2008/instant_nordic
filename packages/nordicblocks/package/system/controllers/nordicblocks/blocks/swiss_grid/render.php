<?php

require_once dirname(__DIR__) . '/render_helpers.php';

$swiss_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'swiss_grid'))
    ? $block_contract
    : null;

if (!function_exists('nb_swiss_grid_prop_int')) {
    function nb_swiss_grid_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_swiss_grid_visible')) {
    function nb_swiss_grid_visible($value, $default = true) {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('nb_swiss_grid_entity_value')) {
    function nb_swiss_grid_entity_value(array $entity, $branch, $key, $default = null) {
        if (isset($entity[$branch]) && is_array($entity[$branch]) && array_key_exists($key, $entity[$branch]) && $entity[$branch][$key] !== '' && $entity[$branch][$key] !== null) {
            return $entity[$branch][$key];
        }

        if (array_key_exists($key, $entity) && $entity[$key] !== '' && $entity[$key] !== null) {
            return $entity[$key];
        }

        return $default;
    }
}

if (!function_exists('nb_swiss_grid_normalize_item')) {
    function nb_swiss_grid_normalize_item(array $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $excerpt = trim((string) ($item['excerpt'] ?? ($item['text'] ?? '')));
        $category = trim((string) ($item['category'] ?? ''));
        $category_url = trim((string) ($item['categoryUrl'] ?? ($item['category_url'] ?? '')));
        $link_label = trim((string) ($item['linkLabel'] ?? ($item['link_label'] ?? '')));
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
            'linkLabel' => htmlspecialchars($link_label, ENT_QUOTES, 'UTF-8'),
            'url' => htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            'date' => htmlspecialchars($date, ENT_QUOTES, 'UTF-8'),
            'views' => htmlspecialchars($views, ENT_QUOTES, 'UTF-8'),
            'comments' => htmlspecialchars($comments, ENT_QUOTES, 'UTF-8'),
            'image' => htmlspecialchars((string) ($media['display'] ?: $media['original']), ENT_QUOTES, 'UTF-8'),
            'imageAlt' => htmlspecialchars((string) ($media['alt'] ?: $title), ENT_QUOTES, 'UTF-8'),
        ];
    }
}

if ($swiss_contract) {
    $theme = in_array($swiss_contract['design']['section']['theme'] ?? 'light', ['light', 'alt', 'dark'], true)
        ? (string) ($swiss_contract['design']['section']['theme'] ?? 'light') : 'light';
    $align = in_array($swiss_contract['layout']['desktop']['align'] ?? 'left', ['left', 'center'], true)
        ? (string) ($swiss_contract['layout']['desktop']['align'] ?? 'left') : 'left';
    $background_style = nb_block_build_background_style((array) ($swiss_contract['design']['section']['background'] ?? []));
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($swiss_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($swiss_contract['runtime']['animation']['delay'] ?? 0),
    ]);

    $heading = trim((string) ($swiss_contract['content']['title'] ?? 'Swiss Style Grid'));
    $intro = trim((string) ($swiss_contract['content']['subtitle'] ?? ''));
    $items_source = is_array($swiss_contract['content']['items'] ?? null) ? $swiss_contract['content']['items'] : [];

    $title_entity = (array) ($swiss_contract['design']['entities']['title'] ?? []);
    $subtitle_entity = (array) ($swiss_contract['design']['entities']['subtitle'] ?? []);
    $meta_entity = (array) ($swiss_contract['design']['entities']['meta'] ?? []);
    $item_title_entity = (array) ($swiss_contract['design']['entities']['itemTitle'] ?? []);
    $item_text_entity = (array) ($swiss_contract['design']['entities']['itemText'] ?? []);

    $title_visible = !array_key_exists('visible', $title_entity) || !empty($title_entity['visible']);
    $subtitle_visible = !array_key_exists('visible', $subtitle_entity) || !empty($subtitle_entity['visible']);
    $show_image = !array_key_exists('image', (array) ($swiss_contract['runtime']['visibility'] ?? [])) || !empty($swiss_contract['runtime']['visibility']['image']);
    $show_category = !array_key_exists('category', (array) ($swiss_contract['runtime']['visibility'] ?? [])) || !empty($swiss_contract['runtime']['visibility']['category']);
    $show_excerpt = !array_key_exists('excerpt', (array) ($swiss_contract['runtime']['visibility'] ?? [])) || !empty($swiss_contract['runtime']['visibility']['excerpt']);
    $show_date = !array_key_exists('date', (array) ($swiss_contract['runtime']['visibility'] ?? [])) || !empty($swiss_contract['runtime']['visibility']['date']);
    $show_views = !array_key_exists('views', (array) ($swiss_contract['runtime']['visibility'] ?? [])) || !empty($swiss_contract['runtime']['visibility']['views']);
    $show_comments = !array_key_exists('comments', (array) ($swiss_contract['runtime']['visibility'] ?? [])) || !empty($swiss_contract['runtime']['visibility']['comments']);

    $heading_tag = htmlspecialchars((string) ($title_entity['tag'] ?? 'h2'), ENT_QUOTES, 'UTF-8');
    $content_width = (int) ($swiss_contract['layout']['desktop']['contentWidth'] ?? 1400);
    $padding_top_desktop = (int) ($swiss_contract['layout']['desktop']['paddingTop'] ?? 0);
    $padding_bottom_desktop = (int) ($swiss_contract['layout']['desktop']['paddingBottom'] ?? 0);
    $padding_top_mobile = (int) ($swiss_contract['layout']['mobile']['paddingTop'] ?? 0);
    $padding_bottom_mobile = (int) ($swiss_contract['layout']['mobile']['paddingBottom'] ?? 0);
    $columns_desktop = (int) ($swiss_contract['layout']['desktop']['columns'] ?? 3);
    $columns_mobile = (int) ($swiss_contract['layout']['mobile']['columns'] ?? 1);
    $columns_tablet = max(1, min(2, $columns_desktop));
    $title_size_desktop = (int) nb_swiss_grid_entity_value($title_entity, 'desktop', 'fontSize', 48);
    $title_size_mobile = (int) nb_swiss_grid_entity_value($title_entity, 'mobile', 'fontSize', 32);
    $title_weight_desktop = (int) nb_swiss_grid_entity_value($title_entity, 'desktop', 'weight', 700);
    $title_weight_mobile = (int) nb_swiss_grid_entity_value($title_entity, 'mobile', 'weight', $title_weight_desktop);
    $title_max_width_desktop = (int) nb_swiss_grid_entity_value($title_entity, 'desktop', 'maxWidth', 1400);
    $title_max_width_mobile = (int) nb_swiss_grid_entity_value($title_entity, 'mobile', 'maxWidth', $title_max_width_desktop);
    $title_color_desktop = nb_block_css_color((string) nb_swiss_grid_entity_value($title_entity, 'desktop', 'color', ''));
    $title_color_mobile = nb_block_css_color((string) nb_swiss_grid_entity_value($title_entity, 'mobile', 'color', $title_color_desktop));
    $title_line_height_desktop = ((float) nb_swiss_grid_entity_value($title_entity, 'desktop', 'lineHeightPercent', 110)) / 100;
    $title_line_height_mobile = ((float) nb_swiss_grid_entity_value($title_entity, 'mobile', 'lineHeightPercent', $title_line_height_desktop * 100)) / 100;
    $title_letter_spacing_desktop = (float) nb_swiss_grid_entity_value($title_entity, 'desktop', 'letterSpacing', 0);
    $title_letter_spacing_mobile = (float) nb_swiss_grid_entity_value($title_entity, 'mobile', 'letterSpacing', $title_letter_spacing_desktop);
    $subtitle_size_desktop = (int) nb_swiss_grid_entity_value($subtitle_entity, 'desktop', 'fontSize', 16);
    $subtitle_size_mobile = (int) nb_swiss_grid_entity_value($subtitle_entity, 'mobile', 'fontSize', 12);
    $subtitle_weight_desktop = (int) nb_swiss_grid_entity_value($subtitle_entity, 'desktop', 'weight', 500);
    $subtitle_weight_mobile = (int) nb_swiss_grid_entity_value($subtitle_entity, 'mobile', 'weight', $subtitle_weight_desktop);
    $subtitle_max_width_desktop = (int) nb_swiss_grid_entity_value($subtitle_entity, 'desktop', 'maxWidth', 1400);
    $subtitle_max_width_mobile = (int) nb_swiss_grid_entity_value($subtitle_entity, 'mobile', 'maxWidth', $subtitle_max_width_desktop);
    $subtitle_color_desktop = nb_block_css_color((string) nb_swiss_grid_entity_value($subtitle_entity, 'desktop', 'color', ''));
    $subtitle_color_mobile = nb_block_css_color((string) nb_swiss_grid_entity_value($subtitle_entity, 'mobile', 'color', $subtitle_color_desktop));
    $subtitle_line_height_desktop = ((float) nb_swiss_grid_entity_value($subtitle_entity, 'desktop', 'lineHeightPercent', 140)) / 100;
    $subtitle_line_height_mobile = ((float) nb_swiss_grid_entity_value($subtitle_entity, 'mobile', 'lineHeightPercent', $subtitle_line_height_desktop * 100)) / 100;
    $subtitle_letter_spacing_desktop = (float) nb_swiss_grid_entity_value($subtitle_entity, 'desktop', 'letterSpacing', 1);
    $subtitle_letter_spacing_mobile = (float) nb_swiss_grid_entity_value($subtitle_entity, 'mobile', 'letterSpacing', $subtitle_letter_spacing_desktop);
    $meta_size_desktop = (int) nb_swiss_grid_entity_value($meta_entity, 'desktop', 'fontSize', 11);
    $meta_size_mobile = (int) nb_swiss_grid_entity_value($meta_entity, 'mobile', 'fontSize', 11);
    $meta_weight_desktop = (int) nb_swiss_grid_entity_value($meta_entity, 'desktop', 'weight', 700);
    $meta_weight_mobile = (int) nb_swiss_grid_entity_value($meta_entity, 'mobile', 'weight', $meta_weight_desktop);
    $meta_color_desktop = nb_block_css_color((string) nb_swiss_grid_entity_value($meta_entity, 'desktop', 'color', ''));
    $meta_color_mobile = nb_block_css_color((string) nb_swiss_grid_entity_value($meta_entity, 'mobile', 'color', $meta_color_desktop));
    $meta_line_height_desktop = ((float) nb_swiss_grid_entity_value($meta_entity, 'desktop', 'lineHeightPercent', 120)) / 100;
    $meta_line_height_mobile = ((float) nb_swiss_grid_entity_value($meta_entity, 'mobile', 'lineHeightPercent', $meta_line_height_desktop * 100)) / 100;
    $meta_letter_spacing_desktop = (float) nb_swiss_grid_entity_value($meta_entity, 'desktop', 'letterSpacing', 1);
    $meta_letter_spacing_mobile = (float) nb_swiss_grid_entity_value($meta_entity, 'mobile', 'letterSpacing', $meta_letter_spacing_desktop);
    $item_title_size_desktop = (int) nb_swiss_grid_entity_value($item_title_entity, 'desktop', 'fontSize', 20);
    $item_title_size_mobile = (int) nb_swiss_grid_entity_value($item_title_entity, 'mobile', 'fontSize', 17);
    $item_title_weight_desktop = (int) nb_swiss_grid_entity_value($item_title_entity, 'desktop', 'weight', 700);
    $item_title_weight_mobile = (int) nb_swiss_grid_entity_value($item_title_entity, 'mobile', 'weight', $item_title_weight_desktop);
    $item_title_color_desktop = nb_block_css_color((string) nb_swiss_grid_entity_value($item_title_entity, 'desktop', 'color', ''));
    $item_title_color_mobile = nb_block_css_color((string) nb_swiss_grid_entity_value($item_title_entity, 'mobile', 'color', $item_title_color_desktop));
    $item_title_line_height_desktop = ((float) nb_swiss_grid_entity_value($item_title_entity, 'desktop', 'lineHeightPercent', 124)) / 100;
    $item_title_line_height_mobile = ((float) nb_swiss_grid_entity_value($item_title_entity, 'mobile', 'lineHeightPercent', $item_title_line_height_desktop * 100)) / 100;
    $item_title_letter_spacing_desktop = (float) nb_swiss_grid_entity_value($item_title_entity, 'desktop', 'letterSpacing', 0);
    $item_title_letter_spacing_mobile = (float) nb_swiss_grid_entity_value($item_title_entity, 'mobile', 'letterSpacing', $item_title_letter_spacing_desktop);
    $item_text_size_desktop = (int) nb_swiss_grid_entity_value($item_text_entity, 'desktop', 'fontSize', 14);
    $item_text_size_mobile = (int) nb_swiss_grid_entity_value($item_text_entity, 'mobile', 'fontSize', 13);
    $item_text_weight_desktop = (int) nb_swiss_grid_entity_value($item_text_entity, 'desktop', 'weight', 400);
    $item_text_weight_mobile = (int) nb_swiss_grid_entity_value($item_text_entity, 'mobile', 'weight', $item_text_weight_desktop);
    $item_text_color_desktop = nb_block_css_color((string) nb_swiss_grid_entity_value($item_text_entity, 'desktop', 'color', ''));
    $item_text_color_mobile = nb_block_css_color((string) nb_swiss_grid_entity_value($item_text_entity, 'mobile', 'color', $item_text_color_desktop));
    $item_text_line_height_desktop = ((float) nb_swiss_grid_entity_value($item_text_entity, 'desktop', 'lineHeightPercent', 148)) / 100;
    $item_text_line_height_mobile = ((float) nb_swiss_grid_entity_value($item_text_entity, 'mobile', 'lineHeightPercent', $item_text_line_height_desktop * 100)) / 100;
    $item_text_letter_spacing_desktop = (float) nb_swiss_grid_entity_value($item_text_entity, 'desktop', 'letterSpacing', 0);
    $item_text_letter_spacing_mobile = (float) nb_swiss_grid_entity_value($item_text_entity, 'mobile', 'letterSpacing', $item_text_letter_spacing_desktop);
    $item_link_entity = (array) ($swiss_contract['design']['entities']['itemLink'] ?? []);
    $item_link_size_desktop = (int) nb_swiss_grid_entity_value($item_link_entity, 'desktop', 'fontSize', 12);
    $item_link_size_mobile = (int) nb_swiss_grid_entity_value($item_link_entity, 'mobile', 'fontSize', $item_link_size_desktop);
    $item_link_weight_desktop = (int) nb_swiss_grid_entity_value($item_link_entity, 'desktop', 'weight', 700);
    $item_link_weight_mobile = (int) nb_swiss_grid_entity_value($item_link_entity, 'mobile', 'weight', $item_link_weight_desktop);
    $item_link_color_desktop = nb_block_css_color((string) nb_swiss_grid_entity_value($item_link_entity, 'desktop', 'color', ''));
    $item_link_color_mobile = nb_block_css_color((string) nb_swiss_grid_entity_value($item_link_entity, 'mobile', 'color', $item_link_color_desktop));
    $item_link_line_height_desktop = ((float) nb_swiss_grid_entity_value($item_link_entity, 'desktop', 'lineHeightPercent', 120)) / 100;
    $item_link_line_height_mobile = ((float) nb_swiss_grid_entity_value($item_link_entity, 'mobile', 'lineHeightPercent', $item_link_line_height_desktop * 100)) / 100;
    $item_link_letter_spacing_desktop = (float) nb_swiss_grid_entity_value($item_link_entity, 'desktop', 'letterSpacing', 1);
    $item_link_letter_spacing_mobile = (float) nb_swiss_grid_entity_value($item_link_entity, 'mobile', 'letterSpacing', $item_link_letter_spacing_desktop);
    $media_aspect_ratio = (string) ($swiss_contract['design']['entities']['media']['aspectRatio'] ?? '4:3');
    $media_object_fit = (string) ($swiss_contract['design']['entities']['media']['objectFit'] ?? 'cover');
    $border_width = (int) ($swiss_contract['design']['entities']['itemSurface']['borderWidth'] ?? 1);
    $border_color = nb_block_css_color((string) ($swiss_contract['design']['entities']['itemSurface']['borderColor'] ?? '#eaeaea'), '#eaeaea');
} else {
    $theme = in_array($props['theme'] ?? 'light', ['light', 'alt', 'dark'], true) ? (string) ($props['theme'] ?? 'light') : 'light';
    $align = in_array($props['align'] ?? 'left', ['left', 'center'], true) ? (string) ($props['align'] ?? 'left') : 'left';
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
        'overlayOpacity' => $props['background_overlay_opacity'] ?? 45,
    ]);
    $reveal = nb_block_get_reveal_settings((array) $props);
    $heading = trim((string) ($props['heading'] ?? 'Swiss Style Grid'));
    $intro = trim((string) ($props['intro'] ?? ''));
    $items_source = is_array($props['items'] ?? null) ? $props['items'] : [];
    $title_visible = nb_swiss_grid_visible($props['title_visible'] ?? '1', true);
    $subtitle_visible = nb_swiss_grid_visible($props['subtitle_visible'] ?? '1', true);
    $show_image = nb_swiss_grid_visible($props['show_image'] ?? '1', true);
    $show_category = nb_swiss_grid_visible($props['show_category'] ?? '1', true);
    $show_excerpt = nb_swiss_grid_visible($props['show_excerpt'] ?? '1', true);
    $show_date = nb_swiss_grid_visible($props['show_date'] ?? '0', false);
    $show_views = nb_swiss_grid_visible($props['show_views'] ?? '0', false);
    $show_comments = nb_swiss_grid_visible($props['show_comments'] ?? '0', false);
    $heading_tag = nb_block_get_heading_tag((array) $props, 'heading', 'h2');
    $content_width = nb_swiss_grid_prop_int((array) $props, 'content_width', 1400, 320, 1600);
    $padding_top_desktop = nb_swiss_grid_prop_int((array) $props, 'padding_top_desktop', 0, 0, 300);
    $padding_bottom_desktop = nb_swiss_grid_prop_int((array) $props, 'padding_bottom_desktop', 0, 0, 300);
    $padding_top_mobile = nb_swiss_grid_prop_int((array) $props, 'padding_top_mobile', 0, 0, 300);
    $padding_bottom_mobile = nb_swiss_grid_prop_int((array) $props, 'padding_bottom_mobile', 0, 0, 300);
    $columns_desktop = nb_swiss_grid_prop_int((array) $props, 'columns_desktop', 3, 1, 4);
    $columns_mobile = nb_swiss_grid_prop_int((array) $props, 'columns_mobile', 1, 1, 2);
    $columns_tablet = max(1, min(2, $columns_desktop));
    $title_size_desktop = nb_swiss_grid_prop_int((array) $props, 'title_size_desktop', 48, 12, 160);
    $title_size_mobile = nb_swiss_grid_prop_int((array) $props, 'title_size_mobile', 32, 12, 160);
    $title_weight_desktop = nb_swiss_grid_prop_int((array) $props, 'heading_weight', 700, 100, 900);
    $title_weight_mobile = $title_weight_desktop;
    $title_max_width_desktop = nb_swiss_grid_prop_int((array) $props, 'title_max_width', 1400, 120, 1600);
    $title_max_width_mobile = $title_max_width_desktop;
    $title_color_desktop = nb_block_css_color((string) ($props['title_color_desktop'] ?? ($props['title_color'] ?? '')));
    $title_color_mobile = $title_color_desktop;
    $title_line_height_desktop = ((float) ($props['title_line_height_percent_desktop'] ?? ($props['title_line_height_percent'] ?? 110))) / 100;
    $title_line_height_mobile = $title_line_height_desktop;
    $title_letter_spacing_desktop = (float) ($props['title_letter_spacing_desktop'] ?? ($props['title_letter_spacing'] ?? 0));
    $title_letter_spacing_mobile = $title_letter_spacing_desktop;
    $subtitle_size_desktop = nb_swiss_grid_prop_int((array) $props, 'subtitle_size_desktop', 16, 10, 80);
    $subtitle_size_mobile = nb_swiss_grid_prop_int((array) $props, 'subtitle_size_mobile', 12, 10, 80);
    $subtitle_weight_desktop = nb_swiss_grid_prop_int((array) $props, 'subtitle_weight_desktop', 500, 100, 900);
    $subtitle_weight_mobile = nb_swiss_grid_prop_int((array) $props, 'subtitle_weight_mobile', $subtitle_weight_desktop, 100, 900);
    $subtitle_max_width_desktop = nb_swiss_grid_prop_int((array) $props, 'subtitle_max_width', 1400, 120, 1600);
    $subtitle_max_width_mobile = $subtitle_max_width_desktop;
    $subtitle_color_desktop = nb_block_css_color((string) ($props['subtitle_color_desktop'] ?? ($props['subtitle_color'] ?? '')));
    $subtitle_color_mobile = $subtitle_color_desktop;
    $subtitle_line_height_desktop = ((float) ($props['subtitle_line_height_percent_desktop'] ?? ($props['subtitle_line_height_percent'] ?? 140))) / 100;
    $subtitle_line_height_mobile = $subtitle_line_height_desktop;
    $subtitle_letter_spacing_desktop = (float) ($props['subtitle_letter_spacing_desktop'] ?? ($props['subtitle_letter_spacing'] ?? 1));
    $subtitle_letter_spacing_mobile = $subtitle_letter_spacing_desktop;
    $meta_size_desktop = nb_swiss_grid_prop_int((array) $props, 'meta_size_desktop', 11, 10, 120);
    $meta_size_mobile = nb_swiss_grid_prop_int((array) $props, 'meta_size_mobile', 11, 10, 120);
    $meta_weight_desktop = nb_swiss_grid_prop_int((array) $props, 'meta_weight_desktop', 700, 100, 900);
    $meta_weight_mobile = nb_swiss_grid_prop_int((array) $props, 'meta_weight_mobile', $meta_weight_desktop, 100, 900);
    $meta_color_desktop = nb_block_css_color((string) ($props['meta_color_desktop'] ?? ($props['meta_color'] ?? '')));
    $meta_color_mobile = $meta_color_desktop;
    $meta_line_height_desktop = ((float) ($props['meta_line_height_percent_desktop'] ?? ($props['meta_line_height_percent'] ?? 120))) / 100;
    $meta_line_height_mobile = $meta_line_height_desktop;
    $meta_letter_spacing_desktop = (float) ($props['meta_letter_spacing_desktop'] ?? ($props['meta_letter_spacing'] ?? 1));
    $meta_letter_spacing_mobile = $meta_letter_spacing_desktop;
    $item_title_size_desktop = nb_swiss_grid_prop_int((array) $props, 'item_title_size_desktop', 20, 10, 80);
    $item_title_size_mobile = nb_swiss_grid_prop_int((array) $props, 'item_title_size_mobile', 17, 10, 80);
    $item_title_weight_desktop = nb_swiss_grid_prop_int((array) $props, 'item_title_weight', 700, 100, 900);
    $item_title_weight_mobile = $item_title_weight_desktop;
    $item_title_color_desktop = nb_block_css_color((string) ($props['item_title_color_desktop'] ?? ($props['item_title_color'] ?? '')));
    $item_title_color_mobile = $item_title_color_desktop;
    $item_title_line_height_desktop = ((float) ($props['item_title_line_height_percent_desktop'] ?? ($props['item_title_line_height_percent'] ?? 124))) / 100;
    $item_title_line_height_mobile = $item_title_line_height_desktop;
    $item_title_letter_spacing_desktop = (float) ($props['item_title_letter_spacing_desktop'] ?? ($props['item_title_letter_spacing'] ?? 0));
    $item_title_letter_spacing_mobile = $item_title_letter_spacing_desktop;
    $item_text_size_desktop = nb_swiss_grid_prop_int((array) $props, 'item_text_size_desktop', 14, 10, 80);
    $item_text_size_mobile = nb_swiss_grid_prop_int((array) $props, 'item_text_size_mobile', 13, 10, 80);
    $item_text_weight_desktop = nb_swiss_grid_prop_int((array) $props, 'item_text_weight_desktop', 400, 100, 900);
    $item_text_weight_mobile = nb_swiss_grid_prop_int((array) $props, 'item_text_weight_mobile', $item_text_weight_desktop, 100, 900);
    $item_text_color_desktop = nb_block_css_color((string) ($props['item_text_color_desktop'] ?? ($props['item_text_color'] ?? '')));
    $item_text_color_mobile = $item_text_color_desktop;
    $item_text_line_height_desktop = ((float) ($props['item_text_line_height_percent_desktop'] ?? ($props['item_text_line_height_percent'] ?? 148))) / 100;
    $item_text_line_height_mobile = $item_text_line_height_desktop;
    $item_text_letter_spacing_desktop = (float) ($props['item_text_letter_spacing_desktop'] ?? ($props['item_text_letter_spacing'] ?? 0));
    $item_text_letter_spacing_mobile = $item_text_letter_spacing_desktop;
    $item_link_size_desktop = 12;
    $item_link_size_mobile = 12;
    $item_link_weight_desktop = 700;
    $item_link_weight_mobile = 700;
    $item_link_color_desktop = '';
    $item_link_color_mobile = '';
    $item_link_line_height_desktop = 1.2;
    $item_link_line_height_mobile = 1.2;
    $item_link_letter_spacing_desktop = 1;
    $item_link_letter_spacing_mobile = 1;
    $media_aspect_ratio = (string) ($props['media_aspect_ratio'] ?? '4:3');
    $media_object_fit = (string) ($props['media_object_fit'] ?? 'cover');
    $border_width = nb_swiss_grid_prop_int((array) $props, 'item_surface_border_width', 1, 0, 20);
    $border_color = nb_block_css_color((string) ($props['item_surface_border_color'] ?? '#eaeaea'), '#eaeaea');
}

$heading_html = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
$intro_html = $intro !== '' ? nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) : '';
$items = [];
foreach ($items_source as $item) {
    if (!is_array($item)) {
        continue;
    }
    $normalized_item = nb_swiss_grid_normalize_item($item);
    if ($normalized_item) {
        $items[] = $normalized_item;
    }
}

$section_class = 'nb-section nb-swiss-grid nb-swiss-grid--align-' . $align . $reveal['class'];
$theme_attr = ($theme !== 'light') ? ' data-nb-theme="' . htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') . '"' : '';
$section_style = '--nb-swiss-content-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-swiss-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-mobile-padding-top:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-mobile-padding-bottom:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-columns:' . $columns_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-columns-tablet:' . $columns_tablet . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-columns-mobile:' . $columns_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-weight:' . $title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-weight-mobile:' . $title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-max-width:' . $title_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-max-width-mobile:' . $title_max_width_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-line-height:' . max(0.8, min(2.2, $title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-line-height-mobile:' . max(0.8, min(2.2, $title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-letter-spacing:' . $title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-title-letter-spacing-mobile:' . $title_letter_spacing_mobile . 'px;');
$section_style = $title_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-swiss-title-color:' . $title_color_desktop . ';') : $section_style;
$section_style = $title_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-swiss-title-color-mobile:' . $title_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-weight:' . $subtitle_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-weight-mobile:' . $subtitle_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-max-width:' . $subtitle_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-max-width-mobile:' . $subtitle_max_width_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-line-height:' . max(0.8, min(2.2, $subtitle_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-line-height-mobile:' . max(0.8, min(2.2, $subtitle_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-letter-spacing:' . $subtitle_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-subtitle-letter-spacing-mobile:' . $subtitle_letter_spacing_mobile . 'px;');
$section_style = $subtitle_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-swiss-subtitle-color:' . $subtitle_color_desktop . ';') : $section_style;
$section_style = $subtitle_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-swiss-subtitle-color-mobile:' . $subtitle_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-swiss-meta-size:' . $meta_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-meta-size-mobile:' . $meta_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-meta-weight:' . $meta_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-meta-weight-mobile:' . $meta_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-meta-line-height:' . max(0.8, min(2.2, $meta_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-meta-line-height-mobile:' . max(0.8, min(2.2, $meta_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-meta-letter-spacing:' . $meta_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-meta-letter-spacing-mobile:' . $meta_letter_spacing_mobile . 'px;');
$section_style = $meta_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-swiss-meta-color:' . $meta_color_desktop . ';') : $section_style;
$section_style = $meta_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-swiss-meta-color-mobile:' . $meta_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-title-size:' . $item_title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-title-size-mobile:' . $item_title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-title-weight:' . $item_title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-title-weight-mobile:' . $item_title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-title-line-height:' . max(0.8, min(2.2, $item_title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-title-line-height-mobile:' . max(0.8, min(2.2, $item_title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-title-letter-spacing:' . $item_title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-title-letter-spacing-mobile:' . $item_title_letter_spacing_mobile . 'px;');
$section_style = $item_title_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-swiss-item-title-color:' . $item_title_color_desktop . ';') : $section_style;
$section_style = $item_title_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-swiss-item-title-color-mobile:' . $item_title_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-text-size:' . $item_text_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-text-size-mobile:' . $item_text_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-text-weight:' . $item_text_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-text-weight-mobile:' . $item_text_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-text-line-height:' . max(0.8, min(2.4, $item_text_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-text-line-height-mobile:' . max(0.8, min(2.4, $item_text_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-text-letter-spacing:' . $item_text_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-item-text-letter-spacing-mobile:' . $item_text_letter_spacing_mobile . 'px;');
$section_style = $item_text_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-swiss-item-text-color:' . $item_text_color_desktop . ';') : $section_style;
$section_style = $item_text_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-swiss-item-text-color-mobile:' . $item_text_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-swiss-link-size:' . $item_link_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-link-size-mobile:' . $item_link_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-link-weight:' . $item_link_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-link-weight-mobile:' . $item_link_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-link-line-height:' . max(0.8, min(2.2, $item_link_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-link-line-height-mobile:' . max(0.8, min(2.2, $item_link_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-link-letter-spacing:' . $item_link_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-link-letter-spacing-mobile:' . $item_link_letter_spacing_mobile . 'px;');
$section_style = $item_link_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-swiss-link-color:' . $item_link_color_desktop . ';') : $section_style;
$section_style = $item_link_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-swiss-link-color-mobile:' . $item_link_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-swiss-media-aspect-ratio:' . $media_aspect_ratio . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-media-object-fit:' . $media_object_fit . ';');
$section_style = nb_block_append_style($section_style, '--nb-swiss-border-width:' . $border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-swiss-border-color:' . $border_color . ';');
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style']);
?>
<section
    class="<?= htmlspecialchars($section_class, ENT_QUOTES, 'UTF-8') ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-entity="section"
    <?= $theme_attr ?>
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container nb-swiss-grid__container">
        <?php if (($title_visible && $heading_html !== '') || ($subtitle_visible && $intro_html !== '')): ?>
        <div class="nb-swiss-grid__header" data-nb-entity="header">
            <?php if ($title_visible && $heading_html !== ''): ?>
            <<?= $heading_tag ?> class="nb-swiss-grid__title" data-nb-entity="title"><?= $heading_html ?></<?= $heading_tag ?>>
            <?php endif; ?>
            <?php if ($subtitle_visible && $intro_html !== ''): ?>
            <div class="nb-swiss-grid__subtitle" data-nb-entity="subtitle"><?= $intro_html ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-swiss-grid__grid" data-nb-entity="items">
            <?php foreach ($items as $item): ?>
            <article class="nb-swiss-grid__card" data-nb-entity="itemSurface">
                <?php if ($show_image): ?>
                <a class="nb-swiss-grid__media<?= empty($item['image']) ? ' nb-swiss-grid__media--placeholder' : '' ?>" href="<?= $item['url'] !== '' ? $item['url'] : '#' ?>"<?= $item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                    <?php if (!empty($item['image'])): ?>
                    <img class="nb-swiss-grid__image" src="<?= $item['image'] ?>" alt="<?= $item['imageAlt'] ?>">
                    <?php else: ?>
                    <span class="nb-swiss-grid__placeholder-icon" aria-hidden="true">+</span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                <div class="nb-swiss-grid__card-content">
                    <?php $primary_meta = ''; ?>
                    <?php if ($show_category && $item['category'] !== '') {
                        $primary_meta = $item['category'];
                    } elseif ($show_date && $item['date'] !== '') {
                        $primary_meta = $item['date'];
                    } ?>
                    <?php if ($primary_meta !== ''): ?>
                    <?php if ($show_category && $item['category'] !== '' && $item['categoryUrl'] !== ''): ?>
                    <a class="nb-swiss-grid__meta nb-swiss-grid__meta--link" href="<?= $item['categoryUrl'] ?>" data-nb-entity="meta"><?= $primary_meta ?></a>
                    <?php else: ?>
                    <div class="nb-swiss-grid__meta" data-nb-entity="meta"><?= $primary_meta ?></div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($item['title'] !== ''): ?>
                    <h3 class="nb-swiss-grid__item-title" data-nb-entity="itemTitle">
                        <?php if ($item['url'] !== ''): ?>
                        <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                        <?php else: ?>
                        <?= $item['title'] ?>
                        <?php endif; ?>
                    </h3>
                    <?php endif; ?>
                    <?php if ($show_excerpt && $item['excerpt'] !== ''): ?>
                    <div class="nb-swiss-grid__excerpt" data-nb-entity="itemText"><?= $item['excerpt'] ?></div>
                    <?php endif; ?>
                    <?php $card_link_label = $item['linkLabel'] !== '' ? $item['linkLabel'] : ($item['url'] !== '' ? 'Подробнее' : ''); ?>
                    <?php if ($card_link_label !== '' && $item['url'] !== ''): ?>
                    <a class="nb-swiss-grid__link" href="<?= $item['url'] ?>" data-nb-entity="itemLink"><?= $card_link_label ?></a>
                    <?php endif; ?>
                    <?php if (($show_date && $item['date'] !== '' && $primary_meta !== $item['date']) || ($show_views && $item['views'] !== '') || ($show_comments && $item['comments'] !== '')): ?>
                        <div class="nb-swiss-grid__aux-meta" data-nb-entity="meta">
                            <?php if ($show_date && $item['date'] !== '' && $primary_meta !== $item['date']): ?><span data-nb-entity="meta"><?= $item['date'] ?></span><?php endif; ?>
                            <?php if ($show_views && $item['views'] !== ''): ?><span data-nb-entity="meta"><?= $item['views'] ?> просмотров</span><?php endif; ?>
                            <?php if ($show_comments && $item['comments'] !== ''): ?><span data-nb-entity="meta"><?= $item['comments'] ?> комментариев</span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>