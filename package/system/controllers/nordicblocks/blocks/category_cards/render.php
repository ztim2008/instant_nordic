<?php

require_once dirname(__DIR__) . '/render_helpers.php';

$category_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'category_cards'))
    ? $block_contract
    : null;

if (!function_exists('nb_category_cards_prop_int')) {
    function nb_category_cards_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_category_cards_prop_value')) {
    function nb_category_cards_prop_value(array $props, array $keys, $default = null) {
        foreach ($keys as $key) {
            if ($key !== '' && array_key_exists($key, $props) && $props[$key] !== '' && $props[$key] !== null) {
                return $props[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('nb_category_cards_visible')) {
    function nb_category_cards_visible($value, $default = true) {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('nb_category_cards_entity_value')) {
    function nb_category_cards_entity_value(array $entity, $branch, $key, $default = null) {
        if (isset($entity[$branch]) && is_array($entity[$branch]) && array_key_exists($key, $entity[$branch]) && $entity[$branch][$key] !== '' && $entity[$branch][$key] !== null) {
            return $entity[$branch][$key];
        }

        if (array_key_exists($key, $entity) && $entity[$key] !== '' && $entity[$key] !== null) {
            return $entity[$key];
        }

        return $default;
    }
}

if (!function_exists('nb_category_cards_normalize_item')) {
    function nb_category_cards_normalize_item(array $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $excerpt = trim((string) ($item['excerpt'] ?? ($item['text'] ?? '')));
        $category = trim((string) ($item['category'] ?? ''));
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
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'excerpt' => nl2br(htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8')),
            'url' => htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            'date' => htmlspecialchars($date, ENT_QUOTES, 'UTF-8'),
            'views' => htmlspecialchars($views, ENT_QUOTES, 'UTF-8'),
            'comments' => htmlspecialchars($comments, ENT_QUOTES, 'UTF-8'),
            'image' => htmlspecialchars((string) ($media['display'] ?: $media['original']), ENT_QUOTES, 'UTF-8'),
            'imageAlt' => htmlspecialchars((string) ($media['alt'] ?: $title), ENT_QUOTES, 'UTF-8'),
        ];
    }
}

if ($category_contract) {
    $theme = in_array($category_contract['design']['section']['theme'] ?? 'light', ['light', 'alt', 'dark'], true)
        ? (string) $category_contract['design']['section']['theme'] : 'light';
    $align = in_array($category_contract['layout']['desktop']['align'] ?? 'left', ['left', 'center'], true)
        ? (string) $category_contract['layout']['desktop']['align'] : 'left';
    $background_style = nb_block_build_background_style((array) ($category_contract['design']['section']['background'] ?? []));
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($category_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($category_contract['runtime']['animation']['delay'] ?? 0),
    ]);

    $eyebrow = trim((string) ($category_contract['content']['eyebrow'] ?? ''));
    $heading = trim((string) ($category_contract['content']['title'] ?? 'Рубрика недели'));
    $intro = trim((string) ($category_contract['content']['subtitle'] ?? ''));
    $more_label = trim((string) ($category_contract['content']['primaryButton']['label'] ?? 'Открыть раздел'));
    $more_url = trim((string) ($category_contract['content']['primaryButton']['url'] ?? '#'));
    $items_source = is_array($category_contract['content']['items'] ?? null) ? $category_contract['content']['items'] : [];

    $eyebrow_entity = (array) ($category_contract['design']['entities']['eyebrow'] ?? []);
    $title_entity = (array) ($category_contract['design']['entities']['title'] ?? []);
    $subtitle_entity = (array) ($category_contract['design']['entities']['subtitle'] ?? []);
    $meta_entity = (array) ($category_contract['design']['entities']['meta'] ?? []);
    $item_title_entity = (array) ($category_contract['design']['entities']['itemTitle'] ?? []);
    $item_text_entity = (array) ($category_contract['design']['entities']['itemText'] ?? []);

    $title_visible = !array_key_exists('visible', $title_entity) || !empty($title_entity['visible']);
    $subtitle_visible = !array_key_exists('visible', $subtitle_entity) || !empty($subtitle_entity['visible']);
    $show_more_link = !array_key_exists('moreLink', (array) ($category_contract['runtime']['visibility'] ?? [])) || !empty($category_contract['runtime']['visibility']['moreLink']);
    $show_image = !array_key_exists('image', (array) ($category_contract['runtime']['visibility'] ?? [])) || !empty($category_contract['runtime']['visibility']['image']);
    $show_category = !array_key_exists('category', (array) ($category_contract['runtime']['visibility'] ?? [])) || !empty($category_contract['runtime']['visibility']['category']);
    $show_excerpt = !array_key_exists('excerpt', (array) ($category_contract['runtime']['visibility'] ?? [])) || !empty($category_contract['runtime']['visibility']['excerpt']);
    $show_date = !array_key_exists('date', (array) ($category_contract['runtime']['visibility'] ?? [])) || !empty($category_contract['runtime']['visibility']['date']);
    $show_views = !array_key_exists('views', (array) ($category_contract['runtime']['visibility'] ?? [])) || !empty($category_contract['runtime']['visibility']['views']);
    $show_comments = !array_key_exists('comments', (array) ($category_contract['runtime']['visibility'] ?? [])) || !empty($category_contract['runtime']['visibility']['comments']);

    $heading_tag = htmlspecialchars((string) ($title_entity['tag'] ?? 'h2'), ENT_QUOTES, 'UTF-8');
    $content_width = (int) ($category_contract['layout']['desktop']['contentWidth'] ?? 1180);
    $padding_top_desktop = (int) ($category_contract['layout']['desktop']['paddingTop'] ?? 56);
    $padding_bottom_desktop = (int) ($category_contract['layout']['desktop']['paddingBottom'] ?? 56);
    $padding_top_mobile = (int) ($category_contract['layout']['mobile']['paddingTop'] ?? 40);
    $padding_bottom_mobile = (int) ($category_contract['layout']['mobile']['paddingBottom'] ?? 40);
    $columns_desktop = (int) ($category_contract['layout']['desktop']['columns'] ?? 4);
    $columns_mobile = (int) ($category_contract['layout']['mobile']['columns'] ?? 2);
    $card_gap_desktop = (int) ($category_contract['layout']['desktop']['cardGap'] ?? 16);
    $card_gap_mobile = (int) ($category_contract['layout']['mobile']['cardGap'] ?? 14);
    $header_gap_desktop = (int) ($category_contract['layout']['desktop']['headerGap'] ?? 14);
    $header_gap_mobile = (int) ($category_contract['layout']['mobile']['headerGap'] ?? 12);
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

    $eyebrow = trim((string) ($props['eyebrow'] ?? 'Раздел'));
    $heading = trim((string) ($props['heading'] ?? 'Рубрика недели'));
    $intro = trim((string) ($props['intro'] ?? ''));
    $more_label = trim((string) ($props['more_link_label'] ?? 'Открыть раздел'));
    $more_url = trim((string) ($props['more_link_url'] ?? '#'));
    $items_source = is_array($props['items'] ?? null) ? $props['items'] : [];

    $eyebrow_entity = [];
    $title_entity = [];
    $subtitle_entity = [];
    $meta_entity = [];
    $item_title_entity = [];
    $item_text_entity = [];

    $title_visible = nb_category_cards_visible($props['title_visible'] ?? '1', true);
    $subtitle_visible = nb_category_cards_visible($props['subtitle_visible'] ?? '1', true);
    $show_more_link = nb_category_cards_visible($props['show_more_link'] ?? '1', true);
    $show_image = nb_category_cards_visible($props['show_image'] ?? '1', true);
    $show_category = nb_category_cards_visible($props['show_category'] ?? '1', true);
    $show_excerpt = nb_category_cards_visible($props['show_excerpt'] ?? '1', true);
    $show_date = nb_category_cards_visible($props['show_date'] ?? '1', true);
    $show_views = nb_category_cards_visible($props['show_views'] ?? '1', true);
    $show_comments = nb_category_cards_visible($props['show_comments'] ?? '1', true);

    $heading_tag = nb_block_get_heading_tag((array) $props, 'heading', 'h2');
    $content_width = nb_category_cards_prop_int((array) $props, 'content_width', 1180, 320, 1600);
    $padding_top_desktop = nb_category_cards_prop_int((array) $props, 'padding_top_desktop', 56, 0, 300);
    $padding_bottom_desktop = nb_category_cards_prop_int((array) $props, 'padding_bottom_desktop', 56, 0, 300);
    $padding_top_mobile = nb_category_cards_prop_int((array) $props, 'padding_top_mobile', 40, 0, 300);
    $padding_bottom_mobile = nb_category_cards_prop_int((array) $props, 'padding_bottom_mobile', 40, 0, 300);
    $columns_desktop = nb_category_cards_prop_int((array) $props, 'columns_desktop', 4, 1, 4);
    $columns_mobile = nb_category_cards_prop_int((array) $props, 'columns_mobile', 2, 1, 2);
    $card_gap_desktop = nb_category_cards_prop_int((array) $props, 'card_gap_desktop', 16, 0, 120);
    $card_gap_mobile = nb_category_cards_prop_int((array) $props, 'card_gap_mobile', 12, 0, 120);
    $header_gap_desktop = nb_category_cards_prop_int((array) $props, 'header_gap_desktop', 14, 0, 120);
    $header_gap_mobile = nb_category_cards_prop_int((array) $props, 'header_gap_mobile', 12, 0, 120);
}

$eyebrow_html = htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8');
$heading_html = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
$intro_html = $intro !== '' ? nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) : '';
$more_label_html = htmlspecialchars($more_label, ENT_QUOTES, 'UTF-8');
$more_url_html = htmlspecialchars($more_url, ENT_QUOTES, 'UTF-8');

$title_size_desktop = (int) nb_category_cards_entity_value($title_entity, 'desktop', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'title_size_desktop', 30, 12, 160));
$title_size_mobile = (int) nb_category_cards_entity_value($title_entity, 'mobile', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'title_size_mobile', 24, 12, 160));
$title_weight_desktop = (int) nb_category_cards_entity_value($title_entity, 'desktop', 'weight', nb_category_cards_prop_value((array) ($props ?? []), ['title_weight_desktop', 'heading_weight'], 800));
$title_weight_mobile = (int) nb_category_cards_entity_value($title_entity, 'mobile', 'weight', $title_weight_desktop);
$title_color_desktop = nb_block_css_color((string) nb_category_cards_entity_value($title_entity, 'desktop', 'color', nb_category_cards_prop_value((array) ($props ?? []), ['title_color_desktop', 'title_color'], '')));
$title_color_mobile = nb_block_css_color((string) nb_category_cards_entity_value($title_entity, 'mobile', 'color', $title_color_desktop));
$title_line_height_desktop = ((float) nb_category_cards_entity_value($title_entity, 'desktop', 'lineHeightPercent', nb_category_cards_prop_value((array) ($props ?? []), ['title_line_height_percent_desktop', 'title_line_height_percent'], 110))) / 100;
$title_line_height_mobile = ((float) nb_category_cards_entity_value($title_entity, 'mobile', 'lineHeightPercent', $title_line_height_desktop * 100)) / 100;
$title_letter_spacing_desktop = (float) nb_category_cards_entity_value($title_entity, 'desktop', 'letterSpacing', nb_category_cards_prop_value((array) ($props ?? []), ['title_letter_spacing_desktop', 'title_letter_spacing'], 0));
$title_letter_spacing_mobile = (float) nb_category_cards_entity_value($title_entity, 'mobile', 'letterSpacing', $title_letter_spacing_desktop);
$title_max_width_desktop = (int) nb_category_cards_entity_value($title_entity, 'desktop', 'maxWidth', nb_category_cards_prop_value((array) ($props ?? []), ['title_max_width_desktop', 'title_max_width'], 720));
$title_max_width_mobile = (int) nb_category_cards_entity_value($title_entity, 'mobile', 'maxWidth', $title_max_width_desktop);

$subtitle_size_desktop = (int) nb_category_cards_entity_value($subtitle_entity, 'desktop', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'subtitle_size_desktop', 15, 10, 80));
$subtitle_size_mobile = (int) nb_category_cards_entity_value($subtitle_entity, 'mobile', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'subtitle_size_mobile', 14, 10, 80));
$subtitle_weight_desktop = (int) nb_category_cards_entity_value($subtitle_entity, 'desktop', 'weight', nb_category_cards_prop_value((array) ($props ?? []), ['subtitle_weight_desktop', 'subtitle_weight'], 400));
$subtitle_weight_mobile = (int) nb_category_cards_entity_value($subtitle_entity, 'mobile', 'weight', $subtitle_weight_desktop);
$subtitle_color_desktop = nb_block_css_color((string) nb_category_cards_entity_value($subtitle_entity, 'desktop', 'color', nb_category_cards_prop_value((array) ($props ?? []), ['subtitle_color_desktop', 'subtitle_color'], '')));
$subtitle_color_mobile = nb_block_css_color((string) nb_category_cards_entity_value($subtitle_entity, 'mobile', 'color', $subtitle_color_desktop));
$subtitle_line_height_desktop = ((float) nb_category_cards_entity_value($subtitle_entity, 'desktop', 'lineHeightPercent', nb_category_cards_prop_value((array) ($props ?? []), ['subtitle_line_height_percent_desktop', 'subtitle_line_height_percent'], 155))) / 100;
$subtitle_line_height_mobile = ((float) nb_category_cards_entity_value($subtitle_entity, 'mobile', 'lineHeightPercent', $subtitle_line_height_desktop * 100)) / 100;
$subtitle_letter_spacing_desktop = (float) nb_category_cards_entity_value($subtitle_entity, 'desktop', 'letterSpacing', nb_category_cards_prop_value((array) ($props ?? []), ['subtitle_letter_spacing_desktop', 'subtitle_letter_spacing'], 0));
$subtitle_letter_spacing_mobile = (float) nb_category_cards_entity_value($subtitle_entity, 'mobile', 'letterSpacing', $subtitle_letter_spacing_desktop);
$subtitle_max_width_desktop = (int) nb_category_cards_entity_value($subtitle_entity, 'desktop', 'maxWidth', nb_category_cards_prop_value((array) ($props ?? []), ['subtitle_max_width_desktop', 'subtitle_max_width'], 720));
$subtitle_max_width_mobile = (int) nb_category_cards_entity_value($subtitle_entity, 'mobile', 'maxWidth', $subtitle_max_width_desktop);

$eyebrow_size_desktop = (int) nb_category_cards_entity_value($eyebrow_entity, 'desktop', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'eyebrow_size_desktop', 13, 10, 120));
$eyebrow_size_mobile = (int) nb_category_cards_entity_value($eyebrow_entity, 'mobile', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'eyebrow_size_mobile', 12, 10, 120));
$eyebrow_margin_bottom_desktop = (int) nb_category_cards_entity_value($eyebrow_entity, 'desktop', 'marginBottom', nb_category_cards_prop_int((array) ($props ?? []), 'eyebrow_margin_bottom_desktop', 8, 0, 240));
$eyebrow_margin_bottom_mobile = (int) nb_category_cards_entity_value($eyebrow_entity, 'mobile', 'marginBottom', nb_category_cards_prop_int((array) ($props ?? []), 'eyebrow_margin_bottom_mobile', 6, 0, 240));
$eyebrow_weight_desktop = (int) nb_category_cards_entity_value($eyebrow_entity, 'desktop', 'weight', nb_category_cards_prop_value((array) ($props ?? []), ['eyebrow_weight_desktop', 'eyebrow_weight'], 700));
$eyebrow_weight_mobile = (int) nb_category_cards_entity_value($eyebrow_entity, 'mobile', 'weight', $eyebrow_weight_desktop);
$eyebrow_color_desktop = nb_block_css_color((string) nb_category_cards_entity_value($eyebrow_entity, 'desktop', 'color', nb_category_cards_prop_value((array) ($props ?? []), ['eyebrow_color_desktop', 'eyebrow_color'], '#0f766e')), '#0f766e');
$eyebrow_color_mobile = nb_block_css_color((string) nb_category_cards_entity_value($eyebrow_entity, 'mobile', 'color', $eyebrow_color_desktop), $eyebrow_color_desktop);
$eyebrow_line_height_desktop = ((float) nb_category_cards_entity_value($eyebrow_entity, 'desktop', 'lineHeightPercent', nb_category_cards_prop_value((array) ($props ?? []), ['eyebrow_line_height_percent_desktop', 'eyebrow_line_height_percent'], 140))) / 100;
$eyebrow_line_height_mobile = ((float) nb_category_cards_entity_value($eyebrow_entity, 'mobile', 'lineHeightPercent', $eyebrow_line_height_desktop * 100)) / 100;
$eyebrow_letter_spacing_desktop = (float) nb_category_cards_entity_value($eyebrow_entity, 'desktop', 'letterSpacing', nb_category_cards_prop_value((array) ($props ?? []), ['eyebrow_letter_spacing_desktop', 'eyebrow_letter_spacing'], 1));
$eyebrow_letter_spacing_mobile = (float) nb_category_cards_entity_value($eyebrow_entity, 'mobile', 'letterSpacing', $eyebrow_letter_spacing_desktop);
$eyebrow_text_transform = (string) ($eyebrow_entity['textTransform'] ?? nb_category_cards_prop_value((array) ($props ?? []), ['eyebrow_text_transform'], 'uppercase'));

$meta_size_desktop = (int) nb_category_cards_entity_value($meta_entity, 'desktop', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'meta_size_desktop', 12, 10, 120));
$meta_size_mobile = (int) nb_category_cards_entity_value($meta_entity, 'mobile', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'meta_size_mobile', 11, 10, 120));
$meta_weight_desktop = (int) nb_category_cards_entity_value($meta_entity, 'desktop', 'weight', nb_category_cards_prop_value((array) ($props ?? []), ['meta_weight_desktop', 'meta_weight'], 600));
$meta_weight_mobile = (int) nb_category_cards_entity_value($meta_entity, 'mobile', 'weight', $meta_weight_desktop);
$meta_color_desktop = nb_block_css_color((string) nb_category_cards_entity_value($meta_entity, 'desktop', 'color', nb_category_cards_prop_value((array) ($props ?? []), ['meta_color_desktop', 'meta_color'], '')));
$meta_color_mobile = nb_block_css_color((string) nb_category_cards_entity_value($meta_entity, 'mobile', 'color', $meta_color_desktop));
$meta_line_height_desktop = ((float) nb_category_cards_entity_value($meta_entity, 'desktop', 'lineHeightPercent', nb_category_cards_prop_value((array) ($props ?? []), ['meta_line_height_percent_desktop', 'meta_line_height_percent'], 140))) / 100;
$meta_line_height_mobile = ((float) nb_category_cards_entity_value($meta_entity, 'mobile', 'lineHeightPercent', $meta_line_height_desktop * 100)) / 100;
$meta_letter_spacing_desktop = (float) nb_category_cards_entity_value($meta_entity, 'desktop', 'letterSpacing', nb_category_cards_prop_value((array) ($props ?? []), ['meta_letter_spacing_desktop', 'meta_letter_spacing'], 0));
$meta_letter_spacing_mobile = (float) nb_category_cards_entity_value($meta_entity, 'mobile', 'letterSpacing', $meta_letter_spacing_desktop);

$item_title_size_desktop = (int) nb_category_cards_entity_value($item_title_entity, 'desktop', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'item_title_size_desktop', 18, 10, 80));
$item_title_size_mobile = (int) nb_category_cards_entity_value($item_title_entity, 'mobile', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'item_title_size_mobile', 16, 10, 80));
$item_title_weight_desktop = (int) nb_category_cards_entity_value($item_title_entity, 'desktop', 'weight', nb_category_cards_prop_value((array) ($props ?? []), ['item_title_weight_desktop', 'item_title_weight'], 800));
$item_title_weight_mobile = (int) nb_category_cards_entity_value($item_title_entity, 'mobile', 'weight', $item_title_weight_desktop);
$item_title_color_desktop = nb_block_css_color((string) nb_category_cards_entity_value($item_title_entity, 'desktop', 'color', nb_category_cards_prop_value((array) ($props ?? []), ['item_title_color_desktop', 'item_title_color'], '')));
$item_title_color_mobile = nb_block_css_color((string) nb_category_cards_entity_value($item_title_entity, 'mobile', 'color', $item_title_color_desktop));
$item_title_line_height_desktop = ((float) nb_category_cards_entity_value($item_title_entity, 'desktop', 'lineHeightPercent', nb_category_cards_prop_value((array) ($props ?? []), ['item_title_line_height_percent_desktop', 'item_title_line_height_percent'], 130))) / 100;
$item_title_line_height_mobile = ((float) nb_category_cards_entity_value($item_title_entity, 'mobile', 'lineHeightPercent', $item_title_line_height_desktop * 100)) / 100;
$item_title_letter_spacing_desktop = (float) nb_category_cards_entity_value($item_title_entity, 'desktop', 'letterSpacing', nb_category_cards_prop_value((array) ($props ?? []), ['item_title_letter_spacing_desktop', 'item_title_letter_spacing'], 0));
$item_title_letter_spacing_mobile = (float) nb_category_cards_entity_value($item_title_entity, 'mobile', 'letterSpacing', $item_title_letter_spacing_desktop);

$item_text_size_desktop = (int) nb_category_cards_entity_value($item_text_entity, 'desktop', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'item_text_size_desktop', 14, 10, 80));
$item_text_size_mobile = (int) nb_category_cards_entity_value($item_text_entity, 'mobile', 'fontSize', nb_category_cards_prop_int((array) ($props ?? []), 'item_text_size_mobile', 13, 10, 80));
$item_text_weight_desktop = (int) nb_category_cards_entity_value($item_text_entity, 'desktop', 'weight', nb_category_cards_prop_value((array) ($props ?? []), ['item_text_weight_desktop', 'item_text_weight'], 400));
$item_text_weight_mobile = (int) nb_category_cards_entity_value($item_text_entity, 'mobile', 'weight', $item_text_weight_desktop);
$item_text_color_desktop = nb_block_css_color((string) nb_category_cards_entity_value($item_text_entity, 'desktop', 'color', nb_category_cards_prop_value((array) ($props ?? []), ['item_text_color_desktop', 'item_text_color'], '')));
$item_text_color_mobile = nb_block_css_color((string) nb_category_cards_entity_value($item_text_entity, 'mobile', 'color', $item_text_color_desktop));
$item_text_line_height_desktop = ((float) nb_category_cards_entity_value($item_text_entity, 'desktop', 'lineHeightPercent', nb_category_cards_prop_value((array) ($props ?? []), ['item_text_line_height_percent_desktop', 'item_text_line_height_percent'], 160))) / 100;
$item_text_line_height_mobile = ((float) nb_category_cards_entity_value($item_text_entity, 'mobile', 'lineHeightPercent', $item_text_line_height_desktop * 100)) / 100;
$item_text_letter_spacing_desktop = (float) nb_category_cards_entity_value($item_text_entity, 'desktop', 'letterSpacing', nb_category_cards_prop_value((array) ($props ?? []), ['item_text_letter_spacing_desktop', 'item_text_letter_spacing'], 0));
$item_text_letter_spacing_mobile = (float) nb_category_cards_entity_value($item_text_entity, 'mobile', 'letterSpacing', $item_text_letter_spacing_desktop);

$media_aspect_ratio = (string) nb_category_cards_entity_value((array) ($category_contract['design']['entities']['media'] ?? []), '', 'aspectRatio', $props['media_aspect_ratio'] ?? '4:3');
$media_object_fit = (string) nb_category_cards_entity_value((array) ($category_contract['design']['entities']['media'] ?? []), '', 'objectFit', $props['media_object_fit'] ?? 'cover');
$media_radius = (int) nb_category_cards_entity_value((array) ($category_contract['design']['entities']['media'] ?? []), '', 'radius', nb_category_cards_prop_int((array) ($props ?? []), 'media_radius', 18, 0, 80));
$item_surface_variant = (string) nb_category_cards_entity_value((array) ($category_contract['design']['entities']['itemSurface'] ?? []), '', 'variant', $props['item_surface_variant'] ?? 'card');
$item_surface_radius = (int) nb_category_cards_entity_value((array) ($category_contract['design']['entities']['itemSurface'] ?? []), '', 'radius', nb_category_cards_prop_int((array) ($props ?? []), 'item_surface_radius', 18, 0, 100));
$item_surface_border_width = (int) nb_category_cards_entity_value((array) ($category_contract['design']['entities']['itemSurface'] ?? []), '', 'borderWidth', nb_category_cards_prop_int((array) ($props ?? []), 'item_surface_border_width', 1, 0, 20));
$item_surface_border_color = nb_block_css_color((string) nb_category_cards_entity_value((array) ($category_contract['design']['entities']['itemSurface'] ?? []), '', 'borderColor', $props['item_surface_border_color'] ?? '#dbe4ef'), '#dbe4ef');
$item_surface_shadow = (string) nb_category_cards_entity_value((array) ($category_contract['design']['entities']['itemSurface'] ?? []), '', 'shadow', $props['item_surface_shadow'] ?? 'sm');

$items = [];
foreach ($items_source as $item) {
    if (!is_array($item)) {
        continue;
    }
    $normalized_item = nb_category_cards_normalize_item($item);
    if ($normalized_item) {
        $items[] = $normalized_item;
    }
}

$section_class = 'nb-section nb-content-feed nb-category-cards nb-content-feed--align-' . $align . ' nb-content-feed--' . $item_surface_variant . $reveal['class'];
$theme_attr = ($theme !== 'light') ? ' data-nb-theme="' . htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') . '"' : '';
$section_style = '--nb-feed-content-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-feed-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-mobile-padding-top:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-mobile-padding-bottom:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-columns:' . $columns_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-columns-mobile:' . $columns_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-gap:' . $card_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-gap-mobile:' . $card_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-header-gap:' . $header_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-header-gap-mobile:' . $header_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-weight:' . $title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-weight-mobile:' . $title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-line-height:' . max(0.8, min(2.2, $title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-line-height-mobile:' . max(0.8, min(2.2, $title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-letter-spacing:' . $title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-letter-spacing-mobile:' . $title_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-max-width:' . $title_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-max-width-mobile:' . $title_max_width_mobile . 'px;');
$section_style = $title_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-feed-title-color:' . $title_color_desktop . ';') : $section_style;
$section_style = $title_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-feed-title-color-mobile:' . $title_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-weight:' . $subtitle_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-weight-mobile:' . $subtitle_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-line-height:' . max(0.8, min(2.4, $subtitle_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-line-height-mobile:' . max(0.8, min(2.4, $subtitle_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-letter-spacing:' . $subtitle_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-letter-spacing-mobile:' . $subtitle_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-max-width:' . $subtitle_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-max-width-mobile:' . $subtitle_max_width_mobile . 'px;');
$section_style = $subtitle_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-feed-subtitle-color:' . $subtitle_color_desktop . ';') : $section_style;
$section_style = $subtitle_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-feed-subtitle-color-mobile:' . $subtitle_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-size:' . $meta_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-size-mobile:' . $meta_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-weight:' . $meta_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-weight-mobile:' . $meta_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-line-height:' . max(0.8, min(2.4, $meta_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-line-height-mobile:' . max(0.8, min(2.4, $meta_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-letter-spacing:' . $meta_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-letter-spacing-mobile:' . $meta_letter_spacing_mobile . 'px;');
$section_style = $meta_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-feed-meta-color:' . $meta_color_desktop . ';') : $section_style;
$section_style = $meta_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-feed-meta-color-mobile:' . $meta_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-size:' . $item_title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-size-mobile:' . $item_title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-weight:' . $item_title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-weight-mobile:' . $item_title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-line-height:' . max(0.8, min(2.2, $item_title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-line-height-mobile:' . max(0.8, min(2.2, $item_title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-letter-spacing:' . $item_title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-letter-spacing-mobile:' . $item_title_letter_spacing_mobile . 'px;');
$section_style = $item_title_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-feed-item-title-color:' . $item_title_color_desktop . ';') : $section_style;
$section_style = $item_title_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-feed-item-title-color-mobile:' . $item_title_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-size:' . $item_text_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-size-mobile:' . $item_text_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-weight:' . $item_text_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-weight-mobile:' . $item_text_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-line-height:' . max(0.8, min(2.6, $item_text_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-line-height-mobile:' . max(0.8, min(2.6, $item_text_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-letter-spacing:' . $item_text_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-letter-spacing-mobile:' . $item_text_letter_spacing_mobile . 'px;');
$section_style = $item_text_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-feed-item-text-color:' . $item_text_color_desktop . ';') : $section_style;
$section_style = $item_text_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-feed-item-text-color-mobile:' . $item_text_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-feed-media-aspect-ratio:' . $media_aspect_ratio . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-media-object-fit:' . $media_object_fit . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-media-radius:' . $media_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-radius:' . $item_surface_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-border-width:' . $item_surface_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-border-color:' . $item_surface_border_color . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-shadow:' . 'var(--nb-shadow-' . $item_surface_shadow . ', none);');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-size:' . $eyebrow_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-size-mobile:' . $eyebrow_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-margin-bottom:' . $eyebrow_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-margin-bottom-mobile:' . $eyebrow_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-weight:' . $eyebrow_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-weight-mobile:' . $eyebrow_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-line-height:' . max(0.8, min(2.4, $eyebrow_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-line-height-mobile:' . max(0.8, min(2.4, $eyebrow_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-letter-spacing:' . $eyebrow_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-letter-spacing-mobile:' . $eyebrow_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-category-cards-eyebrow-transform:' . htmlspecialchars($eyebrow_text_transform, ENT_QUOTES, 'UTF-8') . ';');
$section_style = $eyebrow_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-category-cards-eyebrow-color:' . $eyebrow_color_desktop . ';') : $section_style;
$section_style = $eyebrow_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-category-cards-eyebrow-color-mobile:' . $eyebrow_color_mobile . ';') : $section_style;
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
    <div class="nb-container nb-content-feed__container">
        <?php if ($eyebrow_html !== '' || ($title_visible && $heading_html !== '') || ($subtitle_visible && $intro_html !== '') || ($show_more_link && $more_label_html !== '' && $more_url_html !== '' && $more_url_html !== '#')): ?>
        <div class="nb-content-feed__header nb-category-cards__header">
            <div class="nb-content-feed__copy">
                <?php if ($eyebrow_html !== ''): ?>
                <div class="nb-category-cards__eyebrow" data-nb-entity="eyebrow"><?= $eyebrow_html ?></div>
                <?php endif; ?>
                <?php if ($title_visible && $heading_html !== ''): ?>
                <<?= $heading_tag ?> class="nb-content-feed__title" data-nb-entity="title"><?= $heading_html ?></<?= $heading_tag ?>>
                <?php endif; ?>
                <?php if ($subtitle_visible && $intro_html !== ''): ?>
                <div class="nb-content-feed__subtitle" data-nb-entity="subtitle"><?= $intro_html ?></div>
                <?php endif; ?>
            </div>
            <?php if ($show_more_link && $more_label_html !== '' && $more_url_html !== '' && $more_url_html !== '#'): ?>
            <a class="nb-content-feed__more" href="<?= $more_url_html ?>" data-nb-entity="primaryButton"><?= $more_label_html ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-content-feed__grid" data-nb-entity="items">
            <?php foreach ($items as $item): ?>
            <article class="nb-content-feed__card nb-card" data-nb-entity="itemSurface">
                <?php if ($show_image && !empty($item['image'])): ?>
                <a class="nb-content-feed__media" href="<?= $item['url'] !== '' ? $item['url'] : '#' ?>"<?= $item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                    <img class="nb-content-feed__image" src="<?= $item['image'] ?>" alt="<?= $item['imageAlt'] ?>">
                </a>
                <?php endif; ?>
                <div class="nb-content-feed__body">
                    <?php if ($show_category && $item['category'] !== ''): ?>
                    <div class="nb-content-feed__category" data-nb-entity="meta"><?= $item['category'] ?></div>
                    <?php endif; ?>
                    <?php if ($item['title'] !== ''): ?>
                    <h3 class="nb-content-feed__card-title" data-nb-entity="itemTitle">
                        <?php if ($item['url'] !== ''): ?>
                        <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                        <?php else: ?>
                        <?= $item['title'] ?>
                        <?php endif; ?>
                    </h3>
                    <?php endif; ?>
                    <?php if ($show_excerpt && $item['excerpt'] !== ''): ?>
                    <div class="nb-content-feed__excerpt" data-nb-entity="itemText"><?= $item['excerpt'] ?></div>
                    <?php endif; ?>
                    <?php if (($show_date && $item['date'] !== '') || ($show_views && $item['views'] !== '') || ($show_comments && $item['comments'] !== '')): ?>
                    <div class="nb-content-feed__meta" data-nb-entity="meta">
                        <?php if ($show_date && $item['date'] !== ''): ?><span><?= $item['date'] ?></span><?php endif; ?>
                        <?php if ($show_views && $item['views'] !== ''): ?><span><?= $item['views'] ?> просмотров</span><?php endif; ?>
                        <?php if ($show_comments && $item['comments'] !== ''): ?><span><?= $item['comments'] ?> комментариев</span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>