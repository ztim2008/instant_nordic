<?php

require_once dirname(__DIR__) . '/render_helpers.php';

$feed_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'content_feed'))
    ? $block_contract
    : null;

if (!function_exists('nb_content_feed_prop_int')) {
    function nb_content_feed_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_content_feed_visible')) {
    function nb_content_feed_visible($value, $default = true) {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('nb_content_feed_prop_value')) {
    function nb_content_feed_prop_value(array $props, array $keys, $default = null) {
        foreach ($keys as $key) {
            if ($key !== '' && array_key_exists($key, $props) && $props[$key] !== '' && $props[$key] !== null) {
                return $props[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('nb_content_feed_entity_value')) {
    function nb_content_feed_entity_value(array $entity, $branch, $key, $default = null) {
        if (isset($entity[$branch]) && is_array($entity[$branch]) && array_key_exists($key, $entity[$branch]) && $entity[$branch][$key] !== '' && $entity[$branch][$key] !== null) {
            return $entity[$branch][$key];
        }

        if (array_key_exists($key, $entity) && $entity[$key] !== '' && $entity[$key] !== null) {
            return $entity[$key];
        }

        return $default;
    }
}

if (!function_exists('nb_content_feed_normalize_item')) {
    function nb_content_feed_normalize_item(array $item) {
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

if ($feed_contract) {
    $layout_preset = in_array($feed_contract['layout']['preset'] ?? 'default', ['default', 'swiss'], true)
        ? (string) ($feed_contract['layout']['preset'] ?? 'default') : 'default';
    $theme = in_array($feed_contract['design']['section']['theme'] ?? 'light', ['light', 'alt', 'dark'], true)
        ? (string) $feed_contract['design']['section']['theme'] : 'light';
    $align = in_array($feed_contract['layout']['desktop']['align'] ?? 'left', ['left', 'center'], true)
        ? (string) $feed_contract['layout']['desktop']['align'] : 'left';
    $background_mode = (string) ($feed_contract['design']['section']['background']['mode'] ?? 'theme');
    $background_style = nb_block_build_background_style((array) ($feed_contract['design']['section']['background'] ?? []));
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($feed_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($feed_contract['runtime']['animation']['delay'] ?? 0),
    ]);

    $heading = htmlspecialchars(trim((string) ($feed_contract['content']['title'] ?? 'Последние новости')), ENT_QUOTES, 'UTF-8');
    $intro = trim((string) ($feed_contract['content']['subtitle'] ?? ''));
    $more_label = htmlspecialchars(trim((string) ($feed_contract['content']['primaryButton']['label'] ?? 'Все материалы')), ENT_QUOTES, 'UTF-8');
    $more_url = htmlspecialchars(trim((string) ($feed_contract['content']['primaryButton']['url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $items_source = is_array($feed_contract['content']['items'] ?? null) ? $feed_contract['content']['items'] : [];

    $title_visible = !array_key_exists('visible', (array) ($feed_contract['design']['entities']['title'] ?? [])) || !empty($feed_contract['design']['entities']['title']['visible']);
    $subtitle_visible = !array_key_exists('visible', (array) ($feed_contract['design']['entities']['subtitle'] ?? [])) || !empty($feed_contract['design']['entities']['subtitle']['visible']);
    $show_more_link = !array_key_exists('moreLink', (array) ($feed_contract['runtime']['visibility'] ?? [])) || !empty($feed_contract['runtime']['visibility']['moreLink']);
    $show_image = !array_key_exists('image', (array) ($feed_contract['runtime']['visibility'] ?? [])) || !empty($feed_contract['runtime']['visibility']['image']);
    $show_category = !array_key_exists('category', (array) ($feed_contract['runtime']['visibility'] ?? [])) || !empty($feed_contract['runtime']['visibility']['category']);
    $show_excerpt = !array_key_exists('excerpt', (array) ($feed_contract['runtime']['visibility'] ?? [])) || !empty($feed_contract['runtime']['visibility']['excerpt']);
    $show_date = !array_key_exists('date', (array) ($feed_contract['runtime']['visibility'] ?? [])) || !empty($feed_contract['runtime']['visibility']['date']);
    $show_views = !array_key_exists('views', (array) ($feed_contract['runtime']['visibility'] ?? [])) || !empty($feed_contract['runtime']['visibility']['views']);
    $show_comments = !array_key_exists('comments', (array) ($feed_contract['runtime']['visibility'] ?? [])) || !empty($feed_contract['runtime']['visibility']['comments']);

    $title_entity = (array) ($feed_contract['design']['entities']['title'] ?? []);
    $subtitle_entity = (array) ($feed_contract['design']['entities']['subtitle'] ?? []);
    $meta_entity = (array) ($feed_contract['design']['entities']['meta'] ?? []);
    $item_title_entity = (array) ($feed_contract['design']['entities']['itemTitle'] ?? []);
    $item_text_entity = (array) ($feed_contract['design']['entities']['itemText'] ?? []);

    $heading_tag = htmlspecialchars((string) ($title_entity['tag'] ?? 'h2'), ENT_QUOTES, 'UTF-8');
    $title_weight_desktop = (int) nb_content_feed_entity_value($title_entity, 'desktop', 'weight', 800);
    $title_weight_mobile = (int) nb_content_feed_entity_value($title_entity, 'mobile', 'weight', $title_weight_desktop);
    $title_size_desktop = (int) nb_content_feed_entity_value($title_entity, 'desktop', 'fontSize', 36);
    $title_size_mobile = (int) nb_content_feed_entity_value($title_entity, 'mobile', 'fontSize', 27);
    $title_margin_bottom_desktop = (int) nb_content_feed_entity_value($title_entity, 'desktop', 'marginBottom', 0);
    $title_margin_bottom_mobile = (int) nb_content_feed_entity_value($title_entity, 'mobile', 'marginBottom', 0);
    $title_color_desktop = nb_block_css_color((string) nb_content_feed_entity_value($title_entity, 'desktop', 'color', ''));
    $title_color_mobile = nb_block_css_color((string) nb_content_feed_entity_value($title_entity, 'mobile', 'color', $title_color_desktop));
    $title_line_height_desktop = ((float) nb_content_feed_entity_value($title_entity, 'desktop', 'lineHeightPercent', 110)) / 100;
    $title_line_height_mobile = ((float) nb_content_feed_entity_value($title_entity, 'mobile', 'lineHeightPercent', $title_line_height_desktop * 100)) / 100;
    $title_letter_spacing_desktop = (float) nb_content_feed_entity_value($title_entity, 'desktop', 'letterSpacing', 0);
    $title_letter_spacing_mobile = (float) nb_content_feed_entity_value($title_entity, 'mobile', 'letterSpacing', $title_letter_spacing_desktop);
    $title_max_width_desktop = (int) nb_content_feed_entity_value($title_entity, 'desktop', 'maxWidth', 760);
    $title_max_width_mobile = (int) nb_content_feed_entity_value($title_entity, 'mobile', 'maxWidth', $title_max_width_desktop);

    $subtitle_weight_desktop = (int) nb_content_feed_entity_value($subtitle_entity, 'desktop', 'weight', 400);
    $subtitle_weight_mobile = (int) nb_content_feed_entity_value($subtitle_entity, 'mobile', 'weight', $subtitle_weight_desktop);
    $subtitle_size_desktop = (int) nb_content_feed_entity_value($subtitle_entity, 'desktop', 'fontSize', 18);
    $subtitle_size_mobile = (int) nb_content_feed_entity_value($subtitle_entity, 'mobile', 'fontSize', 16);
    $subtitle_margin_bottom_desktop = (int) nb_content_feed_entity_value($subtitle_entity, 'desktop', 'marginBottom', 0);
    $subtitle_margin_bottom_mobile = (int) nb_content_feed_entity_value($subtitle_entity, 'mobile', 'marginBottom', 0);
    $subtitle_color_desktop = nb_block_css_color((string) nb_content_feed_entity_value($subtitle_entity, 'desktop', 'color', ''));
    $subtitle_color_mobile = nb_block_css_color((string) nb_content_feed_entity_value($subtitle_entity, 'mobile', 'color', $subtitle_color_desktop));
    $subtitle_line_height_desktop = ((float) nb_content_feed_entity_value($subtitle_entity, 'desktop', 'lineHeightPercent', 160)) / 100;
    $subtitle_line_height_mobile = ((float) nb_content_feed_entity_value($subtitle_entity, 'mobile', 'lineHeightPercent', $subtitle_line_height_desktop * 100)) / 100;
    $subtitle_letter_spacing_desktop = (float) nb_content_feed_entity_value($subtitle_entity, 'desktop', 'letterSpacing', 0);
    $subtitle_letter_spacing_mobile = (float) nb_content_feed_entity_value($subtitle_entity, 'mobile', 'letterSpacing', $subtitle_letter_spacing_desktop);
    $subtitle_max_width_desktop = (int) nb_content_feed_entity_value($subtitle_entity, 'desktop', 'maxWidth', 620);
    $subtitle_max_width_mobile = (int) nb_content_feed_entity_value($subtitle_entity, 'mobile', 'maxWidth', $subtitle_max_width_desktop);

    $meta_size_desktop = (int) nb_content_feed_entity_value($meta_entity, 'desktop', 'fontSize', 14);
    $meta_size_mobile = (int) nb_content_feed_entity_value($meta_entity, 'mobile', 'fontSize', 13);
    $meta_weight_desktop = (int) nb_content_feed_entity_value($meta_entity, 'desktop', 'weight', 600);
    $meta_weight_mobile = (int) nb_content_feed_entity_value($meta_entity, 'mobile', 'weight', $meta_weight_desktop);
    $meta_color_desktop = nb_block_css_color((string) nb_content_feed_entity_value($meta_entity, 'desktop', 'color', ''));
    $meta_color_mobile = nb_block_css_color((string) nb_content_feed_entity_value($meta_entity, 'mobile', 'color', $meta_color_desktop));
    $meta_line_height_desktop = ((float) nb_content_feed_entity_value($meta_entity, 'desktop', 'lineHeightPercent', 140)) / 100;
    $meta_line_height_mobile = ((float) nb_content_feed_entity_value($meta_entity, 'mobile', 'lineHeightPercent', $meta_line_height_desktop * 100)) / 100;
    $meta_letter_spacing_desktop = (float) nb_content_feed_entity_value($meta_entity, 'desktop', 'letterSpacing', 0);
    $meta_letter_spacing_mobile = (float) nb_content_feed_entity_value($meta_entity, 'mobile', 'letterSpacing', $meta_letter_spacing_desktop);

    $item_title_size_desktop = (int) nb_content_feed_entity_value($item_title_entity, 'desktop', 'fontSize', 20);
    $item_title_size_mobile = (int) nb_content_feed_entity_value($item_title_entity, 'mobile', 'fontSize', 18);
    $item_title_weight_desktop = (int) nb_content_feed_entity_value($item_title_entity, 'desktop', 'weight', 800);
    $item_title_weight_mobile = (int) nb_content_feed_entity_value($item_title_entity, 'mobile', 'weight', $item_title_weight_desktop);
    $item_title_color_desktop = nb_block_css_color((string) nb_content_feed_entity_value($item_title_entity, 'desktop', 'color', ''));
    $item_title_color_mobile = nb_block_css_color((string) nb_content_feed_entity_value($item_title_entity, 'mobile', 'color', $item_title_color_desktop));
    $item_title_line_height_desktop = ((float) nb_content_feed_entity_value($item_title_entity, 'desktop', 'lineHeightPercent', 130)) / 100;
    $item_title_line_height_mobile = ((float) nb_content_feed_entity_value($item_title_entity, 'mobile', 'lineHeightPercent', $item_title_line_height_desktop * 100)) / 100;
    $item_title_letter_spacing_desktop = (float) nb_content_feed_entity_value($item_title_entity, 'desktop', 'letterSpacing', 0);
    $item_title_letter_spacing_mobile = (float) nb_content_feed_entity_value($item_title_entity, 'mobile', 'letterSpacing', $item_title_letter_spacing_desktop);

    $item_text_size_desktop = (int) nb_content_feed_entity_value($item_text_entity, 'desktop', 'fontSize', 16);
    $item_text_size_mobile = (int) nb_content_feed_entity_value($item_text_entity, 'mobile', 'fontSize', 15);
    $item_text_weight_desktop = (int) nb_content_feed_entity_value($item_text_entity, 'desktop', 'weight', 400);
    $item_text_weight_mobile = (int) nb_content_feed_entity_value($item_text_entity, 'mobile', 'weight', $item_text_weight_desktop);
    $item_text_color_desktop = nb_block_css_color((string) nb_content_feed_entity_value($item_text_entity, 'desktop', 'color', ''));
    $item_text_color_mobile = nb_block_css_color((string) nb_content_feed_entity_value($item_text_entity, 'mobile', 'color', $item_text_color_desktop));
    $item_text_line_height_desktop = ((float) nb_content_feed_entity_value($item_text_entity, 'desktop', 'lineHeightPercent', 165)) / 100;
    $item_text_line_height_mobile = ((float) nb_content_feed_entity_value($item_text_entity, 'mobile', 'lineHeightPercent', $item_text_line_height_desktop * 100)) / 100;
    $item_text_letter_spacing_desktop = (float) nb_content_feed_entity_value($item_text_entity, 'desktop', 'letterSpacing', 0);
    $item_text_letter_spacing_mobile = (float) nb_content_feed_entity_value($item_text_entity, 'mobile', 'letterSpacing', $item_text_letter_spacing_desktop);

    $item_link_entity = (array) ($feed_contract['design']['entities']['itemLink'] ?? []);
    $item_link_size_desktop = (int) nb_content_feed_entity_value($item_link_entity, 'desktop', 'fontSize', 13);
    $item_link_size_mobile = (int) nb_content_feed_entity_value($item_link_entity, 'mobile', 'fontSize', 12);
    $item_link_weight_desktop = (int) nb_content_feed_entity_value($item_link_entity, 'desktop', 'weight', 700);
    $item_link_weight_mobile = (int) nb_content_feed_entity_value($item_link_entity, 'mobile', 'weight', $item_link_weight_desktop);
    $item_link_color_desktop = nb_block_css_color((string) nb_content_feed_entity_value($item_link_entity, 'desktop', 'color', ''));
    $item_link_color_mobile = nb_block_css_color((string) nb_content_feed_entity_value($item_link_entity, 'mobile', 'color', $item_link_color_desktop));
    $item_link_line_height_desktop = ((float) nb_content_feed_entity_value($item_link_entity, 'desktop', 'lineHeightPercent', 120)) / 100;
    $item_link_line_height_mobile = ((float) nb_content_feed_entity_value($item_link_entity, 'mobile', 'lineHeightPercent', $item_link_line_height_desktop * 100)) / 100;
    $item_link_letter_spacing_desktop = (float) nb_content_feed_entity_value($item_link_entity, 'desktop', 'letterSpacing', 1);
    $item_link_letter_spacing_mobile = (float) nb_content_feed_entity_value($item_link_entity, 'mobile', 'letterSpacing', $item_link_letter_spacing_desktop);

    $media_aspect_ratio = in_array($feed_contract['design']['entities']['media']['aspectRatio'] ?? '16:10', ['auto', '16:10', '16:9', '4:3', '1:1', '3:4'], true)
        ? (string) ($feed_contract['design']['entities']['media']['aspectRatio'] ?? '16:10') : '16:10';
    $media_object_fit = in_array($feed_contract['design']['entities']['media']['objectFit'] ?? 'cover', ['cover', 'contain'], true)
        ? (string) ($feed_contract['design']['entities']['media']['objectFit'] ?? 'cover') : 'cover';
    $media_radius = (int) ($feed_contract['design']['entities']['media']['radius'] ?? 20);

    $item_surface_variant = in_array($feed_contract['design']['entities']['itemSurface']['variant'] ?? 'card', ['card', 'plain'], true)
        ? (string) ($feed_contract['design']['entities']['itemSurface']['variant'] ?? 'card') : 'card';
    $item_surface_radius = (int) ($feed_contract['design']['entities']['itemSurface']['radius'] ?? 22);
    $item_surface_border_width = (int) ($feed_contract['design']['entities']['itemSurface']['borderWidth'] ?? 1);
    $item_surface_border_color = nb_block_css_color((string) ($feed_contract['design']['entities']['itemSurface']['borderColor'] ?? '#e2e8f0'), '#e2e8f0');
    $item_surface_shadow = in_array($feed_contract['design']['entities']['itemSurface']['shadow'] ?? 'md', ['none', 'sm', 'md', 'lg'], true)
        ? (string) ($feed_contract['design']['entities']['itemSurface']['shadow'] ?? 'md') : 'md';

    $content_width = (int) ($feed_contract['layout']['desktop']['contentWidth'] ?? 1080);
    $padding_top_desktop = (int) ($feed_contract['layout']['desktop']['paddingTop'] ?? 64);
    $padding_bottom_desktop = (int) ($feed_contract['layout']['desktop']['paddingBottom'] ?? 64);
    $padding_top_mobile = (int) ($feed_contract['layout']['mobile']['paddingTop'] ?? 44);
    $padding_bottom_mobile = (int) ($feed_contract['layout']['mobile']['paddingBottom'] ?? 44);
    $columns_desktop = (int) ($feed_contract['layout']['desktop']['columns'] ?? 3);
    $columns_mobile = (int) ($feed_contract['layout']['mobile']['columns'] ?? 1);
    $card_gap_desktop = (int) ($feed_contract['layout']['desktop']['cardGap'] ?? 18);
    $card_gap_mobile = (int) ($feed_contract['layout']['mobile']['cardGap'] ?? 16);
    $header_gap_desktop = (int) ($feed_contract['layout']['desktop']['headerGap'] ?? 18);
    $header_gap_mobile = (int) ($feed_contract['layout']['mobile']['headerGap'] ?? 14);
} else {
    $layout_preset = in_array($props['layout_preset'] ?? 'default', ['default', 'swiss'], true) ? (string) ($props['layout_preset'] ?? 'default') : 'default';
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

    $heading = htmlspecialchars(trim((string) ($props['heading'] ?? 'Последние новости')), ENT_QUOTES, 'UTF-8');
    $intro = trim((string) ($props['intro'] ?? ''));
    $more_label = htmlspecialchars(trim((string) ($props['more_link_label'] ?? 'Все материалы')), ENT_QUOTES, 'UTF-8');
    $more_url = htmlspecialchars(trim((string) ($props['more_link_url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $items_source = is_array($props['items'] ?? null) ? $props['items'] : [];

    $title_visible = nb_content_feed_visible($props['title_visible'] ?? '1', true);
    $subtitle_visible = nb_content_feed_visible($props['subtitle_visible'] ?? '1', true);
    $show_more_link = nb_content_feed_visible($props['show_more_link'] ?? '1', true);
    $show_image = nb_content_feed_visible($props['show_image'] ?? '1', true);
    $show_category = nb_content_feed_visible($props['show_category'] ?? '1', true);
    $show_excerpt = nb_content_feed_visible($props['show_excerpt'] ?? '1', true);
    $show_date = nb_content_feed_visible($props['show_date'] ?? '1', true);
    $show_views = nb_content_feed_visible($props['show_views'] ?? '1', true);
    $show_comments = nb_content_feed_visible($props['show_comments'] ?? '1', true);

    $heading_tag = nb_block_get_heading_tag((array) $props, 'heading', 'h2');
    $title_weight_desktop = (int) nb_content_feed_prop_int((array) $props, 'title_weight_desktop', (int) nb_content_feed_prop_value((array) $props, ['heading_weight'], 800), 100, 900);
    $title_weight_mobile = (int) nb_content_feed_prop_int((array) $props, 'title_weight_mobile', $title_weight_desktop, 100, 900);
    $title_size_desktop = nb_content_feed_prop_int((array) $props, 'title_size_desktop', 36, 12, 160);
    $title_size_mobile = nb_content_feed_prop_int((array) $props, 'title_size_mobile', 27, 12, 160);
    $title_margin_bottom_desktop = nb_content_feed_prop_int((array) $props, 'title_margin_bottom_desktop', 0, 0, 240);
    $title_margin_bottom_mobile = nb_content_feed_prop_int((array) $props, 'title_margin_bottom_mobile', 0, 0, 240);
    $title_color_desktop = nb_block_css_color((string) nb_content_feed_prop_value((array) $props, ['title_color_desktop', 'title_color'], ''));
    $title_color_mobile = nb_block_css_color((string) nb_content_feed_prop_value((array) $props, ['title_color_mobile'], $title_color_desktop));
    $title_line_height_desktop = nb_content_feed_prop_int((array) $props, 'title_line_height_percent_desktop', (int) nb_content_feed_prop_value((array) $props, ['title_line_height_percent'], 110), 80, 220) / 100;
    $title_line_height_mobile = nb_content_feed_prop_int((array) $props, 'title_line_height_percent_mobile', (int) round($title_line_height_desktop * 100), 80, 220) / 100;
    $title_letter_spacing_desktop = (float) nb_content_feed_prop_value((array) $props, ['title_letter_spacing_desktop', 'title_letter_spacing'], 0);
    $title_letter_spacing_mobile = (float) nb_content_feed_prop_value((array) $props, ['title_letter_spacing_mobile'], $title_letter_spacing_desktop);
    $title_max_width_desktop = nb_content_feed_prop_int((array) $props, 'title_max_width_desktop', (int) nb_content_feed_prop_value((array) $props, ['title_max_width'], 700), 240, 1440);
    $title_max_width_mobile = nb_content_feed_prop_int((array) $props, 'title_max_width_mobile', $title_max_width_desktop, 240, 1440);

    $subtitle_weight_desktop = (int) nb_content_feed_prop_int((array) $props, 'subtitle_weight_desktop', 400, 100, 900);
    $subtitle_weight_mobile = (int) nb_content_feed_prop_int((array) $props, 'subtitle_weight_mobile', $subtitle_weight_desktop, 100, 900);
    $subtitle_size_desktop = nb_content_feed_prop_int((array) $props, 'subtitle_size_desktop', 18, 10, 80);
    $subtitle_size_mobile = nb_content_feed_prop_int((array) $props, 'subtitle_size_mobile', 16, 10, 80);
    $subtitle_margin_bottom_desktop = nb_content_feed_prop_int((array) $props, 'subtitle_margin_bottom_desktop', 0, 0, 240);
    $subtitle_margin_bottom_mobile = nb_content_feed_prop_int((array) $props, 'subtitle_margin_bottom_mobile', 0, 0, 240);
    $subtitle_color_desktop = nb_block_css_color((string) nb_content_feed_prop_value((array) $props, ['subtitle_color_desktop', 'subtitle_color'], ''));
    $subtitle_color_mobile = nb_block_css_color((string) nb_content_feed_prop_value((array) $props, ['subtitle_color_mobile'], $subtitle_color_desktop));
    $subtitle_line_height_desktop = nb_content_feed_prop_int((array) $props, 'subtitle_line_height_percent_desktop', (int) nb_content_feed_prop_value((array) $props, ['subtitle_line_height_percent'], 160), 80, 240) / 100;
    $subtitle_line_height_mobile = nb_content_feed_prop_int((array) $props, 'subtitle_line_height_percent_mobile', (int) round($subtitle_line_height_desktop * 100), 80, 240) / 100;
    $subtitle_letter_spacing_desktop = (float) nb_content_feed_prop_value((array) $props, ['subtitle_letter_spacing_desktop', 'subtitle_letter_spacing'], 0);
    $subtitle_letter_spacing_mobile = (float) nb_content_feed_prop_value((array) $props, ['subtitle_letter_spacing_mobile'], $subtitle_letter_spacing_desktop);
    $subtitle_max_width_desktop = nb_content_feed_prop_int((array) $props, 'subtitle_max_width_desktop', (int) nb_content_feed_prop_value((array) $props, ['subtitle_max_width'], 620), 240, 1440);
    $subtitle_max_width_mobile = nb_content_feed_prop_int((array) $props, 'subtitle_max_width_mobile', $subtitle_max_width_desktop, 240, 1440);

    $meta_size_desktop = nb_content_feed_prop_int((array) $props, 'meta_size_desktop', 13, 10, 120);
    $meta_size_mobile = nb_content_feed_prop_int((array) $props, 'meta_size_mobile', 12, 10, 120);
    $meta_weight_desktop = nb_content_feed_prop_int((array) $props, 'meta_weight_desktop', 600, 400, 900);
    $meta_weight_mobile = nb_content_feed_prop_int((array) $props, 'meta_weight_mobile', $meta_weight_desktop, 400, 900);
    $meta_color_desktop = nb_block_css_color((string) ($props['meta_color_desktop'] ?? ($props['meta_color'] ?? '')));
    $meta_color_mobile = nb_block_css_color((string) ($props['meta_color_mobile'] ?? ($props['meta_color'] ?? '')));
    $meta_line_height_desktop = nb_content_feed_prop_int((array) $props, 'meta_line_height_percent', 140, 80, 240) / 100;
    $meta_line_height_mobile = $meta_line_height_desktop;
    $meta_letter_spacing_desktop = (float) ($props['meta_letter_spacing'] ?? 0);
    $meta_letter_spacing_mobile = $meta_letter_spacing_desktop;

    $item_title_size_desktop = nb_content_feed_prop_int((array) $props, 'item_title_size_desktop', 20, 10, 80);
    $item_title_size_mobile = nb_content_feed_prop_int((array) $props, 'item_title_size_mobile', 18, 10, 80);
    $item_title_weight_desktop = nb_content_feed_prop_int((array) $props, 'item_title_weight_desktop', (int) nb_content_feed_prop_value((array) $props, ['item_title_weight'], 800), 100, 900);
    $item_title_weight_mobile = nb_content_feed_prop_int((array) $props, 'item_title_weight_mobile', $item_title_weight_desktop, 100, 900);
    $item_title_color_desktop = nb_block_css_color((string) nb_content_feed_prop_value((array) $props, ['item_title_color_desktop', 'item_title_color'], ''));
    $item_title_color_mobile = nb_block_css_color((string) nb_content_feed_prop_value((array) $props, ['item_title_color_mobile'], $item_title_color_desktop));
    $item_title_line_height_desktop = nb_content_feed_prop_int((array) $props, 'item_title_line_height_percent_desktop', (int) nb_content_feed_prop_value((array) $props, ['item_title_line_height_percent'], 130), 80, 220) / 100;
    $item_title_line_height_mobile = nb_content_feed_prop_int((array) $props, 'item_title_line_height_percent_mobile', (int) round($item_title_line_height_desktop * 100), 80, 220) / 100;
    $item_title_letter_spacing_desktop = (float) nb_content_feed_prop_value((array) $props, ['item_title_letter_spacing_desktop', 'item_title_letter_spacing'], 0);
    $item_title_letter_spacing_mobile = (float) nb_content_feed_prop_value((array) $props, ['item_title_letter_spacing_mobile'], $item_title_letter_spacing_desktop);

    $item_text_size_desktop = nb_content_feed_prop_int((array) $props, 'item_text_size_desktop', 16, 10, 80);
    $item_text_size_mobile = nb_content_feed_prop_int((array) $props, 'item_text_size_mobile', 15, 10, 80);
    $item_text_weight_desktop = nb_content_feed_prop_int((array) $props, 'item_text_weight_desktop', 400, 100, 900);
    $item_text_weight_mobile = nb_content_feed_prop_int((array) $props, 'item_text_weight_mobile', $item_text_weight_desktop, 100, 900);
    $item_text_color_desktop = nb_block_css_color((string) nb_content_feed_prop_value((array) $props, ['item_text_color_desktop', 'item_text_color'], ''));
    $item_text_color_mobile = nb_block_css_color((string) nb_content_feed_prop_value((array) $props, ['item_text_color_mobile'], $item_text_color_desktop));
    $item_text_line_height_desktop = nb_content_feed_prop_int((array) $props, 'item_text_line_height_percent_desktop', (int) nb_content_feed_prop_value((array) $props, ['item_text_line_height_percent'], 165), 80, 260) / 100;
    $item_text_line_height_mobile = nb_content_feed_prop_int((array) $props, 'item_text_line_height_percent_mobile', (int) round($item_text_line_height_desktop * 100), 80, 260) / 100;
    $item_text_letter_spacing_desktop = (float) nb_content_feed_prop_value((array) $props, ['item_text_letter_spacing_desktop', 'item_text_letter_spacing'], 0);
    $item_text_letter_spacing_mobile = (float) nb_content_feed_prop_value((array) $props, ['item_text_letter_spacing_mobile'], $item_text_letter_spacing_desktop);

    $item_link_size_desktop = 13;
    $item_link_size_mobile = 12;
    $item_link_weight_desktop = 700;
    $item_link_weight_mobile = 700;
    $item_link_color_desktop = '';
    $item_link_color_mobile = '';
    $item_link_line_height_desktop = 1.2;
    $item_link_line_height_mobile = 1.2;
    $item_link_letter_spacing_desktop = 1;
    $item_link_letter_spacing_mobile = 1;

    $media_aspect_ratio = in_array($props['media_aspect_ratio'] ?? '16:10', ['auto', '16:10', '16:9', '4:3', '1:1', '3:4'], true) ? (string) ($props['media_aspect_ratio'] ?? '16:10') : '16:10';
    $media_object_fit = in_array($props['media_object_fit'] ?? 'cover', ['cover', 'contain'], true) ? (string) ($props['media_object_fit'] ?? 'cover') : 'cover';
    $media_radius = nb_content_feed_prop_int((array) $props, 'media_radius', 20, 0, 80);

    $item_surface_variant = in_array($props['item_surface_variant'] ?? 'card', ['card', 'plain'], true) ? (string) ($props['item_surface_variant'] ?? 'card') : 'card';
    $item_surface_radius = nb_content_feed_prop_int((array) $props, 'item_surface_radius', 22, 0, 100);
    $item_surface_border_width = nb_content_feed_prop_int((array) $props, 'item_surface_border_width', 1, 0, 20);
    $item_surface_border_color = nb_block_css_color((string) ($props['item_surface_border_color'] ?? '#e2e8f0'), '#e2e8f0');
    $item_surface_shadow = in_array($props['item_surface_shadow'] ?? 'md', ['none', 'sm', 'md', 'lg'], true) ? (string) ($props['item_surface_shadow'] ?? 'md') : 'md';

    $content_width = nb_content_feed_prop_int((array) $props, 'content_width', 1080, 320, 1440);
    $padding_top_desktop = nb_content_feed_prop_int((array) $props, 'padding_top_desktop', 64, 0, 300);
    $padding_bottom_desktop = nb_content_feed_prop_int((array) $props, 'padding_bottom_desktop', 64, 0, 300);
    $padding_top_mobile = nb_content_feed_prop_int((array) $props, 'padding_top_mobile', 44, 0, 300);
    $padding_bottom_mobile = nb_content_feed_prop_int((array) $props, 'padding_bottom_mobile', 44, 0, 300);
    $columns_desktop = nb_content_feed_prop_int((array) $props, 'columns_desktop', 3, 1, 4);
    $columns_mobile = nb_content_feed_prop_int((array) $props, 'columns_mobile', 1, 1, 2);
    $card_gap_desktop = nb_content_feed_prop_int((array) $props, 'card_gap_desktop', 18, 0, 120);
    $card_gap_mobile = nb_content_feed_prop_int((array) $props, 'card_gap_mobile', 14, 0, 120);
    $header_gap_desktop = nb_content_feed_prop_int((array) $props, 'header_gap_desktop', 18, 0, 120);
    $header_gap_mobile = nb_content_feed_prop_int((array) $props, 'header_gap_mobile', 14, 0, 120);
}

$intro_html = $intro !== '' ? nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) : '';
$items = [];
foreach ($items_source as $item) {
    if (!is_array($item)) {
        continue;
    }
    $normalized_item = nb_content_feed_normalize_item($item);
    if ($normalized_item) {
        $items[] = $normalized_item;
    }
}

$theme_attr = ($theme !== 'light') ? ' data-nb-theme="' . htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') . '"' : '';
$section_class = 'nb-section nb-content-feed nb-content-feed--align-' . $align . ' nb-content-feed--' . $item_surface_variant . ' nb-content-feed--preset-' . $layout_preset . $reveal['class'];
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
$section_style = nb_block_append_style($section_style, '--nb-feed-title-margin-bottom:' . $title_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-margin-bottom-mobile:' . $title_margin_bottom_mobile . 'px;');
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
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-margin-bottom:' . $subtitle_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-margin-bottom-mobile:' . $subtitle_margin_bottom_mobile . 'px;');
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
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-size:' . $item_link_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-size-mobile:' . $item_link_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-weight:' . $item_link_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-weight-mobile:' . $item_link_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-line-height:' . max(0.8, min(2.2, $item_link_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-line-height-mobile:' . max(0.8, min(2.2, $item_link_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-letter-spacing:' . $item_link_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-letter-spacing-mobile:' . $item_link_letter_spacing_mobile . 'px;');
$section_style = $item_link_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-feed-item-link-color:' . $item_link_color_desktop . ';') : $section_style;
$section_style = $item_link_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-feed-item-link-color-mobile:' . $item_link_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-feed-media-aspect-ratio:' . $media_aspect_ratio . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-media-object-fit:' . $media_object_fit . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-media-radius:' . $media_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-radius:' . $item_surface_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-border-width:' . $item_surface_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-border-color:' . $item_surface_border_color . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-shadow:' . 'var(--nb-shadow-' . $item_surface_shadow . ', none);');
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
        <?php if (($title_visible && $heading) || ($subtitle_visible && $intro_html) || ($show_more_link && $more_label && $more_url && $more_url !== '#')): ?>
        <div class="nb-content-feed__header" data-nb-entity="header">
            <div class="nb-content-feed__copy">
                <?php if ($title_visible && $heading): ?>
                <<?= $heading_tag ?> class="nb-content-feed__title" data-nb-entity="title"><?= $heading ?></<?= $heading_tag ?>>
                <?php endif; ?>
                <?php if ($subtitle_visible && $intro_html): ?>
                <div class="nb-content-feed__subtitle" data-nb-entity="subtitle"><?= $intro_html ?></div>
                <?php endif; ?>
            </div>
            <?php if ($show_more_link && $more_label && $more_url && $more_url !== '#'): ?>
            <a class="nb-content-feed__more" href="<?= $more_url ?>" data-nb-entity="primaryButton"><?= $more_label ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-content-feed__grid" data-nb-entity="items">
            <?php foreach ($items as $item): ?>
            <article class="nb-content-feed__card nb-card" data-nb-entity="itemSurface">
                <?php if ($show_image && (!empty($item['image']) || $layout_preset === 'swiss')): ?>
                <a class="nb-content-feed__media<?= empty($item['image']) ? ' nb-content-feed__media--placeholder' : '' ?>" href="<?= $item['url'] !== '' ? $item['url'] : '#' ?>"<?= $item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                    <?php if (!empty($item['image'])): ?>
                    <img class="nb-content-feed__image" src="<?= $item['image'] ?>" alt="<?= $item['imageAlt'] ?>">
                    <?php else: ?>
                    <span class="nb-content-feed__placeholder-icon" aria-hidden="true">+</span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                <div class="nb-content-feed__body">
                    <?php if ($show_category && $item['category'] !== ''): ?>
                    <?php if ($item['categoryUrl'] !== ''): ?>
                    <a class="nb-content-feed__category" href="<?= $item['categoryUrl'] ?>" data-nb-entity="meta"><?= $item['category'] ?></a>
                    <?php else: ?>
                    <div class="nb-content-feed__category" data-nb-entity="meta"><?= $item['category'] ?></div>
                    <?php endif; ?>
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
                    <?php $card_link_label = $item['linkLabel'] !== '' ? $item['linkLabel'] : ($layout_preset === 'swiss' && $item['url'] !== '' ? 'Подробнее' : ''); ?>
                    <?php if ($card_link_label !== '' && $item['url'] !== ''): ?>
                    <a class="nb-content-feed__item-link" href="<?= $item['url'] ?>" data-nb-entity="itemLink"><?= $card_link_label ?></a>
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