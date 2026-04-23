<?php

require_once dirname(__DIR__) . '/render_helpers.php';

$cards_slider_contract = (isset($block_contract) && is_array($block_contract) && ((string) ($block_contract['meta']['blockType'] ?? '') === 'cards_slider'))
    ? $block_contract
    : null;

if (!function_exists('nb_cards_slider_visible')) {
    function nb_cards_slider_visible($value, $default = true) {
        if ($value === null || $value === '') {
            return (bool) $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('nb_cards_slider_prop_int')) {
    function nb_cards_slider_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_cards_slider_entity_value')) {
    function nb_cards_slider_entity_value(array $entity, $branch, $key, $default = null) {
        if (isset($entity[$branch]) && is_array($entity[$branch]) && array_key_exists($key, $entity[$branch]) && $entity[$branch][$key] !== '' && $entity[$branch][$key] !== null) {
            return $entity[$branch][$key];
        }

        if (array_key_exists($key, $entity) && $entity[$key] !== '' && $entity[$key] !== null) {
            return $entity[$key];
        }

        return $default;
    }
}

if (!function_exists('nb_cards_slider_shadow_css')) {
    function nb_cards_slider_shadow_css($token, $default = 'sm') {
        $token = in_array($token, ['none', 'sm', 'md', 'lg'], true) ? $token : $default;
        return $token === 'none' ? 'none' : 'var(--nb-shadow-' . $token . ', none)';
    }
}

if (!function_exists('nb_cards_slider_normalize_position')) {
    function nb_cards_slider_normalize_position($value, $default = 'below') {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['overlay', 'below', 'hidden'], true) ? $value : $default;
    }
}

if (!function_exists('nb_cards_slider_aspect_ratio_css')) {
    function nb_cards_slider_aspect_ratio_css($value) {
        $value = trim((string) $value);

        if ($value === '16:9') {
            return '16 / 9';
        }
        if ($value === '1:1') {
            return '1 / 1';
        }
        if ($value === '3:4') {
            return '3 / 4';
        }
        if ($value === 'auto') {
            return 'auto';
        }

        return '4 / 3';
    }
}

if (!function_exists('nb_cards_slider_normalize_slide')) {
    function nb_cards_slider_normalize_slide(array $slide) {
        $title = trim((string) ($slide['title'] ?? ''));
        $text = trim((string) ($slide['text'] ?? ''));
        $eyebrow = trim((string) ($slide['eyebrow'] ?? ''));
        $meta_label = trim((string) ($slide['metaLabel'] ?? ($slide['meta_label'] ?? '')));
        $date = trim((string) ($slide['date'] ?? ''));
        $record_url = trim((string) ($slide['recordUrl'] ?? ($slide['record_url'] ?? ($slide['url'] ?? ''))));
        $primary_label = trim((string) ($slide['primaryCtaLabel'] ?? ($slide['primary_cta_label'] ?? (($slide['primaryAction']['label'] ?? '')))));
        $primary_url = trim((string) ($slide['primaryCtaUrl'] ?? ($slide['primary_cta_url'] ?? (($slide['primaryAction']['url'] ?? '')))));
        $secondary_label = trim((string) ($slide['secondaryCtaLabel'] ?? ($slide['secondary_cta_label'] ?? (($slide['secondaryAction']['label'] ?? '')))));
        $secondary_url = trim((string) ($slide['secondaryCtaUrl'] ?? ($slide['secondary_cta_url'] ?? (($slide['secondaryAction']['url'] ?? '')))));
        $media = nb_block_extract_media($slide['image'] ?? '', $slide['imageAlt'] ?? ($slide['image_alt'] ?? $title));
        $image = (string) ($media['display'] ?: $media['original']);
        $image_alt = (string) ($media['alt'] ?: $title);

        if ($title === '' && $text === '' && $eyebrow === '' && $meta_label === '' && $date === '' && $record_url === '' && $image === '') {
            return null;
        }

        if ($primary_url === '' && $record_url !== '') {
            $primary_url = $record_url;
        }

        return [
            'eyebrow' => htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8'),
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'text' => $text !== '' ? nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) : '',
            'metaLabel' => htmlspecialchars($meta_label, ENT_QUOTES, 'UTF-8'),
            'date' => htmlspecialchars($date, ENT_QUOTES, 'UTF-8'),
            'recordUrl' => htmlspecialchars($record_url, ENT_QUOTES, 'UTF-8'),
            'primaryLabel' => htmlspecialchars($primary_label, ENT_QUOTES, 'UTF-8'),
            'primaryUrl' => htmlspecialchars($primary_url, ENT_QUOTES, 'UTF-8'),
            'secondaryLabel' => htmlspecialchars($secondary_label, ENT_QUOTES, 'UTF-8'),
            'secondaryUrl' => htmlspecialchars($secondary_url, ENT_QUOTES, 'UTF-8'),
            'image' => htmlspecialchars($image, ENT_QUOTES, 'UTF-8'),
            'imageAlt' => htmlspecialchars($image_alt, ENT_QUOTES, 'UTF-8'),
        ];
    }
}

$props = (array) ($props ?? []);

if ($cards_slider_contract) {
    $theme = in_array($cards_slider_contract['design']['section']['theme'] ?? 'light', ['light', 'alt', 'dark'], true)
        ? (string) ($cards_slider_contract['design']['section']['theme'] ?? 'light') : 'light';
    $background_style = nb_block_build_background_style((array) ($cards_slider_contract['design']['section']['background'] ?? []));
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($cards_slider_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($cards_slider_contract['runtime']['animation']['delay'] ?? 0),
    ]);
    $content = is_array($cards_slider_contract['content'] ?? null) ? $cards_slider_contract['content'] : [];
    $entities = is_array($cards_slider_contract['design']['entities'] ?? null) ? $cards_slider_contract['design']['entities'] : [];
    $visibility = is_array($cards_slider_contract['runtime']['visibility'] ?? null) ? $cards_slider_contract['runtime']['visibility'] : [];
    $slider_runtime = is_array($cards_slider_contract['runtime']['slider'] ?? null) ? $cards_slider_contract['runtime']['slider'] : [];
    $layout_desktop = is_array($cards_slider_contract['layout']['desktop'] ?? null) ? $cards_slider_contract['layout']['desktop'] : [];
    $layout_mobile = is_array($cards_slider_contract['layout']['mobile'] ?? null) ? $cards_slider_contract['layout']['mobile'] : [];
    $heading = trim((string) ($content['title'] ?? 'Подборка материалов'));
    $intro = trim((string) ($content['subtitle'] ?? ''));
    $section_link_label = trim((string) ($content['primaryButton']['label'] ?? ''));
    $section_link_url = trim((string) ($content['primaryButton']['url'] ?? ''));
    $slides_source = is_array($content['slides'] ?? null) ? $content['slides'] : [];
    $title_entity = (array) ($entities['title'] ?? []);
    $subtitle_entity = (array) ($entities['subtitle'] ?? []);
    $slide_surface = (array) ($entities['slideSurface'] ?? []);
    $slide_media = (array) ($entities['slideMedia'] ?? []);
    $slide_eyebrow = (array) ($entities['slideEyebrow'] ?? []);
    $slide_title = (array) ($entities['slideTitle'] ?? []);
    $slide_text = (array) ($entities['slideText'] ?? []);
    $slide_meta = (array) ($entities['slideMeta'] ?? []);
    $slide_primary = (array) ($entities['slidePrimaryAction'] ?? []);
    $slide_secondary = (array) ($entities['slideSecondaryAction'] ?? []);
    $navigation = (array) ($entities['navigation'] ?? []);
    $pagination = (array) ($entities['pagination'] ?? []);
    $progress = (array) ($entities['progress'] ?? []);
    $title_visible = !array_key_exists('visible', $title_entity) || !empty($title_entity['visible']);
    $subtitle_visible = !array_key_exists('visible', $subtitle_entity) || !empty($subtitle_entity['visible']);
    $show_navigation = !array_key_exists('navigation', $visibility) || !empty($visibility['navigation']);
    $show_pagination = !array_key_exists('pagination', $visibility) || !empty($visibility['pagination']);
    $show_progress = !empty($visibility['progress']);
    $show_media = !array_key_exists('slideMedia', $visibility) || !empty($visibility['slideMedia']);
    $show_eyebrow = !array_key_exists('slideEyebrow', $visibility) || !empty($visibility['slideEyebrow']);
    $show_text = !array_key_exists('slideText', $visibility) || !empty($visibility['slideText']);
    $show_meta = !array_key_exists('slideMeta', $visibility) || !empty($visibility['slideMeta']);
    $show_primary = !array_key_exists('slidePrimaryAction', $visibility) || !empty($visibility['slidePrimaryAction']);
    $show_secondary = !array_key_exists('slideSecondaryAction', $visibility) || !empty($visibility['slideSecondaryAction']);
    $content_width = (int) ($layout_desktop['contentWidth'] ?? 1600);
    $padding_top_desktop = (int) ($layout_desktop['paddingTop'] ?? 72);
    $padding_bottom_desktop = (int) ($layout_desktop['paddingBottom'] ?? 72);
    $padding_top_mobile = (int) ($layout_mobile['paddingTop'] ?? 40);
    $padding_bottom_mobile = (int) ($layout_mobile['paddingBottom'] ?? 40);
    $slides_per_view_desktop = (int) ($layout_desktop['slidesPerView'] ?? 3);
    $slides_per_view_mobile = (int) ($layout_mobile['slidesPerView'] ?? 1);
    $slide_gap_desktop = (int) ($layout_desktop['slideGap'] ?? 24);
    $slide_gap_mobile = (int) ($layout_mobile['slideGap'] ?? 16);
    $header_gap_desktop = (int) ($layout_desktop['headerGap'] ?? 28);
    $header_gap_mobile = (int) ($layout_mobile['headerGap'] ?? 18);
    $min_height_desktop = (int) ($layout_desktop['minHeight'] ?? 0);
    $min_height_mobile = (int) ($layout_mobile['minHeight'] ?? 0);
    $navigation_position_desktop = nb_cards_slider_normalize_position($layout_desktop['navigationPosition'] ?? 'overlay', 'overlay');
    $navigation_position_mobile = nb_cards_slider_normalize_position($layout_mobile['navigationPosition'] ?? 'hidden', 'hidden');
    $pagination_position_desktop = nb_cards_slider_normalize_position($layout_desktop['paginationPosition'] ?? 'below', 'below');
    $pagination_position_mobile = nb_cards_slider_normalize_position($layout_mobile['paginationPosition'] ?? 'below', 'below');
    $progress_position_desktop = nb_cards_slider_normalize_position($layout_desktop['progressPosition'] ?? 'below', 'below');
    $progress_position_mobile = nb_cards_slider_normalize_position($layout_mobile['progressPosition'] ?? 'below', 'below');
    $swipe = !array_key_exists('swipe', $slider_runtime) || !empty($slider_runtime['swipe']);
    $autoplay = !empty($slider_runtime['autoplay']);
    $loop = !empty($slider_runtime['loop']);
    $autoplay_delay = (int) ($slider_runtime['autoplayDelay'] ?? 4500);
    $transition_ms = (int) ($slider_runtime['transitionMs'] ?? 450);
} else {
    $theme = in_array($props['theme'] ?? 'light', ['light', 'alt', 'dark'], true) ? (string) ($props['theme'] ?? 'light') : 'light';
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
    $heading = trim((string) ($props['heading'] ?? 'Подборка материалов'));
    $intro = trim((string) ($props['subheading'] ?? ''));
    $section_link_label = trim((string) ($props['section_link_label'] ?? ''));
    $section_link_url = trim((string) ($props['section_link_url'] ?? ''));
    $slides_source = is_array($props['slides'] ?? null) ? $props['slides'] : [];
    $title_visible = nb_cards_slider_visible($props['title_visible'] ?? '1', true);
    $subtitle_visible = nb_cards_slider_visible($props['subtitle_visible'] ?? '1', true);
    $show_navigation = nb_cards_slider_visible($props['show_navigation'] ?? '1', true);
    $show_pagination = nb_cards_slider_visible($props['show_pagination'] ?? '1', true);
    $show_progress = nb_cards_slider_visible($props['show_progress'] ?? '0', false);
    $show_media = nb_cards_slider_visible($props['show_media'] ?? '1', true);
    $show_eyebrow = nb_cards_slider_visible($props['show_eyebrow'] ?? '1', true);
    $show_text = nb_cards_slider_visible($props['show_text'] ?? '1', true);
    $show_meta = nb_cards_slider_visible($props['show_meta'] ?? '1', true);
    $show_primary = nb_cards_slider_visible($props['show_primary_cta'] ?? '1', true);
    $show_secondary = nb_cards_slider_visible($props['show_secondary_cta'] ?? '1', true);
    $content_width = nb_cards_slider_prop_int($props, 'content_width', 1600, 320, 1920);
    $padding_top_desktop = nb_cards_slider_prop_int($props, 'padding_top_desktop', 72, 0, 240);
    $padding_bottom_desktop = nb_cards_slider_prop_int($props, 'padding_bottom_desktop', 72, 0, 240);
    $padding_top_mobile = nb_cards_slider_prop_int($props, 'padding_top_mobile', 40, 0, 240);
    $padding_bottom_mobile = nb_cards_slider_prop_int($props, 'padding_bottom_mobile', 40, 0, 240);
    $slides_per_view_desktop = nb_cards_slider_prop_int($props, 'slides_per_view_desktop', 3, 1, 6);
    $slides_per_view_mobile = nb_cards_slider_prop_int($props, 'slides_per_view_mobile', 1, 1, 3);
    $slide_gap_desktop = nb_cards_slider_prop_int($props, 'slide_gap_desktop', 24, 0, 120);
    $slide_gap_mobile = nb_cards_slider_prop_int($props, 'slide_gap_mobile', 16, 0, 120);
    $header_gap_desktop = nb_cards_slider_prop_int($props, 'header_gap_desktop', 28, 0, 120);
    $header_gap_mobile = nb_cards_slider_prop_int($props, 'header_gap_mobile', 18, 0, 120);
    $min_height_desktop = nb_cards_slider_prop_int($props, 'min_height_desktop', 0, 0, 1200);
    $min_height_mobile = nb_cards_slider_prop_int($props, 'min_height_mobile', 0, 0, 1200);
    $navigation_position_desktop = nb_cards_slider_normalize_position($props['navigation_position_desktop'] ?? 'overlay', 'overlay');
    $navigation_position_mobile = nb_cards_slider_normalize_position($props['navigation_position_mobile'] ?? 'hidden', 'hidden');
    $pagination_position_desktop = nb_cards_slider_normalize_position($props['pagination_position_desktop'] ?? 'below', 'below');
    $pagination_position_mobile = nb_cards_slider_normalize_position($props['pagination_position_mobile'] ?? 'below', 'below');
    $progress_position_desktop = nb_cards_slider_normalize_position($props['progress_position_desktop'] ?? 'below', 'below');
    $progress_position_mobile = nb_cards_slider_normalize_position($props['progress_position_mobile'] ?? 'below', 'below');
    $swipe = nb_cards_slider_visible($props['swipe'] ?? '1', true);
    $autoplay = nb_cards_slider_visible($props['autoplay'] ?? '0', false);
    $loop = nb_cards_slider_visible($props['loop'] ?? '0', false);
    $autoplay_delay = nb_cards_slider_prop_int($props, 'autoplay_delay', 4500, 1000, 20000);
    $transition_ms = nb_cards_slider_prop_int($props, 'transition_ms', 450, 100, 5000);

    $title_entity = [
        'tag' => nb_block_get_heading_tag($props, 'heading', 'h2'),
        'desktop' => [
            'fontSize' => nb_cards_slider_prop_int($props, 'title_size_desktop', 48, 12, 120),
            'weight' => nb_cards_slider_prop_int($props, 'title_weight_desktop', 800, 100, 900),
            'lineHeightPercent' => nb_cards_slider_prop_int($props, 'title_line_height_percent_desktop', 105, 80, 220),
            'letterSpacing' => (float) ($props['title_letter_spacing_desktop'] ?? -1),
            'color' => $props['title_color_desktop'] ?? ($props['title_color'] ?? ''),
        ],
        'mobile' => [
            'fontSize' => nb_cards_slider_prop_int($props, 'title_size_mobile', 32, 12, 120),
            'weight' => nb_cards_slider_prop_int($props, 'title_weight_mobile', 800, 100, 900),
            'lineHeightPercent' => nb_cards_slider_prop_int($props, 'title_line_height_percent_mobile', 108, 80, 220),
            'letterSpacing' => (float) ($props['title_letter_spacing_mobile'] ?? -0.5),
            'color' => $props['title_color_mobile'] ?? ($props['title_color'] ?? ''),
        ],
    ];
    $subtitle_entity = [
        'desktop' => [
            'fontSize' => nb_cards_slider_prop_int($props, 'subtitle_size_desktop', 17, 10, 80),
            'weight' => nb_cards_slider_prop_int($props, 'subtitle_weight_desktop', 400, 100, 900),
            'lineHeightPercent' => nb_cards_slider_prop_int($props, 'subtitle_line_height_percent_desktop', 155, 80, 260),
            'letterSpacing' => (float) ($props['subtitle_letter_spacing_desktop'] ?? 0),
            'color' => $props['subtitle_color_desktop'] ?? ($props['subtitle_color'] ?? ''),
        ],
        'mobile' => [
            'fontSize' => nb_cards_slider_prop_int($props, 'subtitle_size_mobile', 15, 10, 80),
            'weight' => nb_cards_slider_prop_int($props, 'subtitle_weight_mobile', 400, 100, 900),
            'lineHeightPercent' => nb_cards_slider_prop_int($props, 'subtitle_line_height_percent_mobile', 155, 80, 260),
            'letterSpacing' => (float) ($props['subtitle_letter_spacing_mobile'] ?? 0),
            'color' => $props['subtitle_color_mobile'] ?? ($props['subtitle_color'] ?? ''),
        ],
    ];
    $slide_surface = [
        'backgroundMode' => $props['slide_surface_background_mode'] ?? 'solid',
        'backgroundColor' => $props['slide_surface_background_color'] ?? '#ffffff',
        'padding' => nb_cards_slider_prop_int($props, 'slide_surface_padding', 0, 0, 80),
        'radius' => nb_cards_slider_prop_int($props, 'slide_surface_radius', 28, 0, 80),
        'borderWidth' => nb_cards_slider_prop_int($props, 'slide_surface_border_width', 1, 0, 20),
        'borderColor' => $props['slide_surface_border_color'] ?? '#dbe4ef',
        'shadow' => $props['slide_surface_shadow'] ?? 'sm',
    ];
    $slide_media = [
        'aspectRatio' => $props['media_aspect_ratio'] ?? '4:3',
        'objectFit' => $props['media_object_fit'] ?? 'cover',
        'radius' => nb_cards_slider_prop_int($props, 'media_radius', 24, 0, 80),
    ];
    $slide_eyebrow = [
        'desktop' => ['fontSize' => 13, 'weight' => 700, 'lineHeightPercent' => 140, 'letterSpacing' => 1, 'color' => '#0f766e'],
        'mobile' => ['fontSize' => 12, 'weight' => 700, 'lineHeightPercent' => 140, 'letterSpacing' => 1, 'color' => '#0f766e'],
    ];
    $slide_title = [
        'desktop' => ['fontSize' => 24, 'weight' => 800, 'lineHeightPercent' => 120, 'letterSpacing' => 0, 'color' => ''],
        'mobile' => ['fontSize' => 19, 'weight' => 800, 'lineHeightPercent' => 120, 'letterSpacing' => 0, 'color' => ''],
    ];
    $slide_text = [
        'desktop' => ['fontSize' => 16, 'weight' => 400, 'lineHeightPercent' => 160, 'letterSpacing' => 0, 'color' => ''],
        'mobile' => ['fontSize' => 14, 'weight' => 400, 'lineHeightPercent' => 160, 'letterSpacing' => 0, 'color' => ''],
    ];
    $slide_meta = [
        'desktop' => ['fontSize' => 12, 'weight' => 600, 'lineHeightPercent' => 135, 'letterSpacing' => 1, 'color' => ''],
        'mobile' => ['fontSize' => 11, 'weight' => 600, 'lineHeightPercent' => 135, 'letterSpacing' => 1, 'color' => ''],
    ];
    $slide_primary = [
        'desktop' => ['fontSize' => 14, 'weight' => 700, 'lineHeightPercent' => 120, 'letterSpacing' => 0, 'color' => ''],
        'mobile' => ['fontSize' => 13, 'weight' => 700, 'lineHeightPercent' => 120, 'letterSpacing' => 0, 'color' => ''],
    ];
    $slide_secondary = [
        'desktop' => ['fontSize' => 14, 'weight' => 600, 'lineHeightPercent' => 120, 'letterSpacing' => 0, 'color' => ''],
        'mobile' => ['fontSize' => 13, 'weight' => 600, 'lineHeightPercent' => 120, 'letterSpacing' => 0, 'color' => ''],
    ];
    $navigation = [
        'size' => nb_cards_slider_prop_int($props, 'navigation_size', 46, 24, 96),
        'radius' => nb_cards_slider_prop_int($props, 'navigation_radius', 999, 0, 999),
        'backgroundColor' => $props['navigation_background_color'] ?? '#0f172a',
        'textColor' => $props['navigation_text_color'] ?? '#ffffff',
        'borderColor' => $props['navigation_border_color'] ?? '#0f172a',
        'shadow' => $props['navigation_shadow'] ?? 'md',
    ];
    $pagination = [
        'dotSize' => nb_cards_slider_prop_int($props, 'pagination_dot_size', 10, 4, 32),
        'gap' => nb_cards_slider_prop_int($props, 'pagination_gap', 8, 0, 40),
        'color' => $props['pagination_color'] ?? '#cbd5e1',
        'activeColor' => $props['pagination_active_color'] ?? '#0f172a',
    ];
    $progress = [
        'height' => nb_cards_slider_prop_int($props, 'progress_height', 4, 2, 24),
        'radius' => nb_cards_slider_prop_int($props, 'progress_radius', 999, 0, 999),
        'trackColor' => $props['progress_track_color'] ?? '#e2e8f0',
        'fillColor' => $props['progress_fill_color'] ?? '#0f172a',
    ];
}

$slides = [];
foreach ($slides_source as $slide) {
    if (!is_array($slide)) {
        continue;
    }

    $normalized_slide = nb_cards_slider_normalize_slide($slide);
    if ($normalized_slide) {
        $slides[] = $normalized_slide;
    }
}

$heading_tag = htmlspecialchars((string) ($title_entity['tag'] ?? 'h2'), ENT_QUOTES, 'UTF-8');
$title_size_desktop = (int) nb_cards_slider_entity_value($title_entity, 'desktop', 'fontSize', 48);
$title_size_mobile = (int) nb_cards_slider_entity_value($title_entity, 'mobile', 'fontSize', 32);
$title_weight_desktop = (int) nb_cards_slider_entity_value($title_entity, 'desktop', 'weight', 800);
$title_weight_mobile = (int) nb_cards_slider_entity_value($title_entity, 'mobile', 'weight', $title_weight_desktop);
$title_line_height_desktop = ((float) nb_cards_slider_entity_value($title_entity, 'desktop', 'lineHeightPercent', 105)) / 100;
$title_line_height_mobile = ((float) nb_cards_slider_entity_value($title_entity, 'mobile', 'lineHeightPercent', 108)) / 100;
$title_letter_spacing_desktop = (float) nb_cards_slider_entity_value($title_entity, 'desktop', 'letterSpacing', -1);
$title_letter_spacing_mobile = (float) nb_cards_slider_entity_value($title_entity, 'mobile', 'letterSpacing', -0.5);
$title_color_desktop = nb_block_css_color((string) nb_cards_slider_entity_value($title_entity, 'desktop', 'color', ''));
$title_color_mobile = nb_block_css_color((string) nb_cards_slider_entity_value($title_entity, 'mobile', 'color', $title_color_desktop));
$subtitle_size_desktop = (int) nb_cards_slider_entity_value($subtitle_entity, 'desktop', 'fontSize', 17);
$subtitle_size_mobile = (int) nb_cards_slider_entity_value($subtitle_entity, 'mobile', 'fontSize', 15);
$subtitle_weight_desktop = (int) nb_cards_slider_entity_value($subtitle_entity, 'desktop', 'weight', 400);
$subtitle_weight_mobile = (int) nb_cards_slider_entity_value($subtitle_entity, 'mobile', 'weight', $subtitle_weight_desktop);
$subtitle_line_height_desktop = ((float) nb_cards_slider_entity_value($subtitle_entity, 'desktop', 'lineHeightPercent', 155)) / 100;
$subtitle_line_height_mobile = ((float) nb_cards_slider_entity_value($subtitle_entity, 'mobile', 'lineHeightPercent', 155)) / 100;
$subtitle_letter_spacing_desktop = (float) nb_cards_slider_entity_value($subtitle_entity, 'desktop', 'letterSpacing', 0);
$subtitle_letter_spacing_mobile = (float) nb_cards_slider_entity_value($subtitle_entity, 'mobile', 'letterSpacing', 0);
$subtitle_color_desktop = nb_block_css_color((string) nb_cards_slider_entity_value($subtitle_entity, 'desktop', 'color', ''));
$subtitle_color_mobile = nb_block_css_color((string) nb_cards_slider_entity_value($subtitle_entity, 'mobile', 'color', $subtitle_color_desktop));
$slide_eyebrow_size_desktop = (int) nb_cards_slider_entity_value($slide_eyebrow, 'desktop', 'fontSize', 13);
$slide_eyebrow_size_mobile = (int) nb_cards_slider_entity_value($slide_eyebrow, 'mobile', 'fontSize', 12);
$slide_eyebrow_weight_desktop = (int) nb_cards_slider_entity_value($slide_eyebrow, 'desktop', 'weight', 700);
$slide_eyebrow_weight_mobile = (int) nb_cards_slider_entity_value($slide_eyebrow, 'mobile', 'weight', $slide_eyebrow_weight_desktop);
$slide_eyebrow_line_height_desktop = ((float) nb_cards_slider_entity_value($slide_eyebrow, 'desktop', 'lineHeightPercent', 140)) / 100;
$slide_eyebrow_line_height_mobile = ((float) nb_cards_slider_entity_value($slide_eyebrow, 'mobile', 'lineHeightPercent', 140)) / 100;
$slide_eyebrow_letter_spacing_desktop = (float) nb_cards_slider_entity_value($slide_eyebrow, 'desktop', 'letterSpacing', 1);
$slide_eyebrow_letter_spacing_mobile = (float) nb_cards_slider_entity_value($slide_eyebrow, 'mobile', 'letterSpacing', 1);
$slide_eyebrow_color_desktop = nb_block_css_color((string) nb_cards_slider_entity_value($slide_eyebrow, 'desktop', 'color', '#0f766e'), '#0f766e');
$slide_eyebrow_color_mobile = nb_block_css_color((string) nb_cards_slider_entity_value($slide_eyebrow, 'mobile', 'color', $slide_eyebrow_color_desktop), $slide_eyebrow_color_desktop);
$slide_title_size_desktop = (int) nb_cards_slider_entity_value($slide_title, 'desktop', 'fontSize', 24);
$slide_title_size_mobile = (int) nb_cards_slider_entity_value($slide_title, 'mobile', 'fontSize', 19);
$slide_title_weight_desktop = (int) nb_cards_slider_entity_value($slide_title, 'desktop', 'weight', 800);
$slide_title_weight_mobile = (int) nb_cards_slider_entity_value($slide_title, 'mobile', 'weight', $slide_title_weight_desktop);
$slide_title_line_height_desktop = ((float) nb_cards_slider_entity_value($slide_title, 'desktop', 'lineHeightPercent', 120)) / 100;
$slide_title_line_height_mobile = ((float) nb_cards_slider_entity_value($slide_title, 'mobile', 'lineHeightPercent', 120)) / 100;
$slide_title_letter_spacing_desktop = (float) nb_cards_slider_entity_value($slide_title, 'desktop', 'letterSpacing', 0);
$slide_title_letter_spacing_mobile = (float) nb_cards_slider_entity_value($slide_title, 'mobile', 'letterSpacing', 0);
$slide_title_color_desktop = nb_block_css_color((string) nb_cards_slider_entity_value($slide_title, 'desktop', 'color', ''));
$slide_title_color_mobile = nb_block_css_color((string) nb_cards_slider_entity_value($slide_title, 'mobile', 'color', $slide_title_color_desktop));
$slide_text_size_desktop = (int) nb_cards_slider_entity_value($slide_text, 'desktop', 'fontSize', 16);
$slide_text_size_mobile = (int) nb_cards_slider_entity_value($slide_text, 'mobile', 'fontSize', 14);
$slide_text_weight_desktop = (int) nb_cards_slider_entity_value($slide_text, 'desktop', 'weight', 400);
$slide_text_weight_mobile = (int) nb_cards_slider_entity_value($slide_text, 'mobile', 'weight', $slide_text_weight_desktop);
$slide_text_line_height_desktop = ((float) nb_cards_slider_entity_value($slide_text, 'desktop', 'lineHeightPercent', 160)) / 100;
$slide_text_line_height_mobile = ((float) nb_cards_slider_entity_value($slide_text, 'mobile', 'lineHeightPercent', 160)) / 100;
$slide_text_letter_spacing_desktop = (float) nb_cards_slider_entity_value($slide_text, 'desktop', 'letterSpacing', 0);
$slide_text_letter_spacing_mobile = (float) nb_cards_slider_entity_value($slide_text, 'mobile', 'letterSpacing', 0);
$slide_text_color_desktop = nb_block_css_color((string) nb_cards_slider_entity_value($slide_text, 'desktop', 'color', ''));
$slide_text_color_mobile = nb_block_css_color((string) nb_cards_slider_entity_value($slide_text, 'mobile', 'color', $slide_text_color_desktop));
$slide_meta_size_desktop = (int) nb_cards_slider_entity_value($slide_meta, 'desktop', 'fontSize', 12);
$slide_meta_size_mobile = (int) nb_cards_slider_entity_value($slide_meta, 'mobile', 'fontSize', 11);
$slide_meta_weight_desktop = (int) nb_cards_slider_entity_value($slide_meta, 'desktop', 'weight', 600);
$slide_meta_weight_mobile = (int) nb_cards_slider_entity_value($slide_meta, 'mobile', 'weight', $slide_meta_weight_desktop);
$slide_meta_line_height_desktop = ((float) nb_cards_slider_entity_value($slide_meta, 'desktop', 'lineHeightPercent', 135)) / 100;
$slide_meta_line_height_mobile = ((float) nb_cards_slider_entity_value($slide_meta, 'mobile', 'lineHeightPercent', 135)) / 100;
$slide_meta_letter_spacing_desktop = (float) nb_cards_slider_entity_value($slide_meta, 'desktop', 'letterSpacing', 1);
$slide_meta_letter_spacing_mobile = (float) nb_cards_slider_entity_value($slide_meta, 'mobile', 'letterSpacing', 1);
$slide_meta_color_desktop = nb_block_css_color((string) nb_cards_slider_entity_value($slide_meta, 'desktop', 'color', ''));
$slide_meta_color_mobile = nb_block_css_color((string) nb_cards_slider_entity_value($slide_meta, 'mobile', 'color', $slide_meta_color_desktop));
$slide_primary_size_desktop = (int) nb_cards_slider_entity_value($slide_primary, 'desktop', 'fontSize', 14);
$slide_primary_size_mobile = (int) nb_cards_slider_entity_value($slide_primary, 'mobile', 'fontSize', 13);
$slide_primary_weight_desktop = (int) nb_cards_slider_entity_value($slide_primary, 'desktop', 'weight', 700);
$slide_primary_weight_mobile = (int) nb_cards_slider_entity_value($slide_primary, 'mobile', 'weight', $slide_primary_weight_desktop);
$slide_primary_color_desktop = nb_block_css_color((string) nb_cards_slider_entity_value($slide_primary, 'desktop', 'color', ''));
$slide_primary_color_mobile = nb_block_css_color((string) nb_cards_slider_entity_value($slide_primary, 'mobile', 'color', $slide_primary_color_desktop));
$slide_secondary_size_desktop = (int) nb_cards_slider_entity_value($slide_secondary, 'desktop', 'fontSize', 14);
$slide_secondary_size_mobile = (int) nb_cards_slider_entity_value($slide_secondary, 'mobile', 'fontSize', 13);
$slide_secondary_weight_desktop = (int) nb_cards_slider_entity_value($slide_secondary, 'desktop', 'weight', 600);
$slide_secondary_weight_mobile = (int) nb_cards_slider_entity_value($slide_secondary, 'mobile', 'weight', $slide_secondary_weight_desktop);
$slide_secondary_color_desktop = nb_block_css_color((string) nb_cards_slider_entity_value($slide_secondary, 'desktop', 'color', ''));
$slide_secondary_color_mobile = nb_block_css_color((string) nb_cards_slider_entity_value($slide_secondary, 'mobile', 'color', $slide_secondary_color_desktop));
$slide_surface_bg_mode = in_array($slide_surface['backgroundMode'] ?? 'solid', ['transparent', 'solid'], true) ? (string) ($slide_surface['backgroundMode'] ?? 'solid') : 'solid';
$slide_surface_bg_color = nb_block_css_color((string) ($slide_surface['backgroundColor'] ?? '#ffffff'), '#ffffff');
$slide_surface_padding = (int) ($slide_surface['padding'] ?? 0);
$slide_surface_radius = (int) ($slide_surface['radius'] ?? 28);
$slide_surface_border_width = (int) ($slide_surface['borderWidth'] ?? 1);
$slide_surface_border_color = nb_block_css_color((string) ($slide_surface['borderColor'] ?? '#dbe4ef'), '#dbe4ef');
$slide_surface_shadow = nb_cards_slider_shadow_css($slide_surface['shadow'] ?? 'sm', 'sm');
$media_aspect_ratio_css = nb_cards_slider_aspect_ratio_css($slide_media['aspectRatio'] ?? '4:3');
$media_object_fit = in_array($slide_media['objectFit'] ?? 'cover', ['cover', 'contain', 'fill', 'scale-down'], true)
    ? (string) ($slide_media['objectFit'] ?? 'cover') : 'cover';
$media_radius = (int) ($slide_media['radius'] ?? 24);
$navigation_size = (int) ($navigation['size'] ?? 46);
$navigation_radius = (int) ($navigation['radius'] ?? 999);
$navigation_background = nb_block_css_color((string) ($navigation['backgroundColor'] ?? '#0f172a'), '#0f172a');
$navigation_text = nb_block_css_color((string) ($navigation['textColor'] ?? '#ffffff'), '#ffffff');
$navigation_border = nb_block_css_color((string) ($navigation['borderColor'] ?? '#0f172a'), '#0f172a');
$navigation_shadow = nb_cards_slider_shadow_css($navigation['shadow'] ?? 'md', 'md');
$pagination_dot_size = (int) ($pagination['dotSize'] ?? 10);
$pagination_gap = (int) ($pagination['gap'] ?? 8);
$pagination_color = nb_block_css_color((string) ($pagination['color'] ?? '#cbd5e1'), '#cbd5e1');
$pagination_active_color = nb_block_css_color((string) ($pagination['activeColor'] ?? '#0f172a'), '#0f172a');
$progress_height = (int) ($progress['height'] ?? 4);
$progress_radius = (int) ($progress['radius'] ?? 999);
$progress_track_color = nb_block_css_color((string) ($progress['trackColor'] ?? '#e2e8f0'), '#e2e8f0');
$progress_fill_color = nb_block_css_color((string) ($progress['fillColor'] ?? '#0f172a'), '#0f172a');

$section_class = 'nb-section nb-cards-slider' . (!empty($reveal['class']) ? $reveal['class'] : '');
$theme_attr = $theme !== 'light' ? ' data-nb-theme="' . htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') . '"' : '';
$block_uid_value = isset($block_uid) && $block_uid !== '' ? (string) $block_uid : uniqid('nb-cards-slider-', false);
$block_dom_id = 'block-' . preg_replace('/[^A-Za-z0-9_-]/', '', $block_uid_value);
$section_style = '--nb-cards-slider-content-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-padding-top-mobile:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-padding-bottom-mobile:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slides-per-view:' . $slides_per_view_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slides-per-view-mobile:' . $slides_per_view_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-gap:' . $slide_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-gap-mobile:' . $slide_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-header-gap:' . $header_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-header-gap-mobile:' . $header_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-min-height:' . $min_height_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-min-height-mobile:' . $min_height_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-title-weight:' . $title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-title-weight-mobile:' . $title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-title-line-height:' . max(0.8, min(2.2, $title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-title-line-height-mobile:' . max(0.8, min(2.2, $title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-title-letter-spacing:' . $title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-title-letter-spacing-mobile:' . $title_letter_spacing_mobile . 'px;');
$section_style = $title_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-title-color:' . $title_color_desktop . ';') : $section_style;
$section_style = $title_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-title-color-mobile:' . $title_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-subtitle-weight:' . $subtitle_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-subtitle-weight-mobile:' . $subtitle_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-subtitle-line-height:' . max(0.8, min(2.6, $subtitle_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-subtitle-line-height-mobile:' . max(0.8, min(2.6, $subtitle_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-subtitle-letter-spacing:' . $subtitle_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-subtitle-letter-spacing-mobile:' . $subtitle_letter_spacing_mobile . 'px;');
$section_style = $subtitle_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-subtitle-color:' . $subtitle_color_desktop . ';') : $section_style;
$section_style = $subtitle_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-subtitle-color-mobile:' . $subtitle_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-size:' . $slide_eyebrow_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-size-mobile:' . $slide_eyebrow_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-weight:' . $slide_eyebrow_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-weight-mobile:' . $slide_eyebrow_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-line-height:' . max(0.8, min(2.2, $slide_eyebrow_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-line-height-mobile:' . max(0.8, min(2.2, $slide_eyebrow_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-letter-spacing:' . $slide_eyebrow_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-letter-spacing-mobile:' . $slide_eyebrow_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-color:' . $slide_eyebrow_color_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-eyebrow-color-mobile:' . $slide_eyebrow_color_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-title-size:' . $slide_title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-title-size-mobile:' . $slide_title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-title-weight:' . $slide_title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-title-weight-mobile:' . $slide_title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-title-line-height:' . max(0.8, min(2.2, $slide_title_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-title-line-height-mobile:' . max(0.8, min(2.2, $slide_title_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-title-letter-spacing:' . $slide_title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-title-letter-spacing-mobile:' . $slide_title_letter_spacing_mobile . 'px;');
$section_style = $slide_title_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-slide-title-color:' . $slide_title_color_desktop . ';') : $section_style;
$section_style = $slide_title_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-slide-title-color-mobile:' . $slide_title_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-text-size:' . $slide_text_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-text-size-mobile:' . $slide_text_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-text-weight:' . $slide_text_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-text-weight-mobile:' . $slide_text_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-text-line-height:' . max(0.8, min(2.6, $slide_text_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-text-line-height-mobile:' . max(0.8, min(2.6, $slide_text_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-text-letter-spacing:' . $slide_text_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-text-letter-spacing-mobile:' . $slide_text_letter_spacing_mobile . 'px;');
$section_style = $slide_text_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-slide-text-color:' . $slide_text_color_desktop . ';') : $section_style;
$section_style = $slide_text_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-slide-text-color-mobile:' . $slide_text_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-size:' . $slide_meta_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-size-mobile:' . $slide_meta_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-weight:' . $slide_meta_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-weight-mobile:' . $slide_meta_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-line-height:' . max(0.8, min(2.2, $slide_meta_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-line-height-mobile:' . max(0.8, min(2.2, $slide_meta_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-letter-spacing:' . $slide_meta_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-letter-spacing-mobile:' . $slide_meta_letter_spacing_mobile . 'px;');
$section_style = $slide_meta_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-color:' . $slide_meta_color_desktop . ';') : $section_style;
$section_style = $slide_meta_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-slide-meta-color-mobile:' . $slide_meta_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-primary-size:' . $slide_primary_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-primary-size-mobile:' . $slide_primary_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-primary-weight:' . $slide_primary_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-primary-weight-mobile:' . $slide_primary_weight_mobile . ';');
$section_style = $slide_primary_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-primary-color:' . $slide_primary_color_desktop . ';') : $section_style;
$section_style = $slide_primary_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-primary-color-mobile:' . $slide_primary_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-secondary-size:' . $slide_secondary_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-secondary-size-mobile:' . $slide_secondary_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-secondary-weight:' . $slide_secondary_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-secondary-weight-mobile:' . $slide_secondary_weight_mobile . ';');
$section_style = $slide_secondary_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-secondary-color:' . $slide_secondary_color_desktop . ';') : $section_style;
$section_style = $slide_secondary_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-cards-slider-secondary-color-mobile:' . $slide_secondary_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-surface-background:' . ($slide_surface_bg_mode === 'transparent' ? 'transparent' : $slide_surface_bg_color) . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-surface-padding:' . $slide_surface_padding . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-surface-radius:' . $slide_surface_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-surface-border-width:' . $slide_surface_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-surface-border-color:' . $slide_surface_border_color . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-surface-shadow:' . $slide_surface_shadow . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-media-aspect-ratio:' . $media_aspect_ratio_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-media-object-fit:' . $media_object_fit . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-media-radius:' . $media_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-nav-size:' . $navigation_size . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-nav-radius:' . $navigation_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-nav-bg:' . $navigation_background . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-nav-color:' . $navigation_text . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-nav-border:' . $navigation_border . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-nav-shadow:' . $navigation_shadow . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-dot-size:' . $pagination_dot_size . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-dot-gap:' . $pagination_gap . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-dot-color:' . $pagination_color . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-dot-active-color:' . $pagination_active_color . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-progress-height:' . $progress_height . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-progress-radius:' . $progress_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-progress-track:' . $progress_track_color . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-progress-fill:' . $progress_fill_color . ';');
$section_style = nb_block_append_style($section_style, '--nb-cards-slider-scroll-duration:' . $transition_ms . 'ms;');
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style'] ?? '');
$intro_html = $intro !== '' ? nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) : '';
$slides_count = count($slides);
$controls_visible = $slides_count > 1;
?>
<section
    class="<?= htmlspecialchars($section_class, ENT_QUOTES, 'UTF-8') ?>"
    id="<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-block="cards_slider"
    data-nb-entity="section"
    data-slides-per-view-desktop="<?= (int) $slides_per_view_desktop ?>"
    data-slides-per-view-mobile="<?= (int) $slides_per_view_mobile ?>"
    data-swipe="<?= $swipe ? '1' : '0' ?>"
    data-autoplay="<?= ($autoplay && $controls_visible) ? '1' : '0' ?>"
    data-loop="<?= ($loop && $controls_visible) ? '1' : '0' ?>"
    data-autoplay-delay="<?= (int) $autoplay_delay ?>"
    data-nav-desktop-pos="<?= htmlspecialchars($navigation_position_desktop, ENT_QUOTES, 'UTF-8') ?>"
    data-nav-mobile-pos="<?= htmlspecialchars($navigation_position_mobile, ENT_QUOTES, 'UTF-8') ?>"
    data-pagination-desktop-pos="<?= htmlspecialchars($pagination_position_desktop, ENT_QUOTES, 'UTF-8') ?>"
    data-pagination-mobile-pos="<?= htmlspecialchars($pagination_position_mobile, ENT_QUOTES, 'UTF-8') ?>"
    data-progress-desktop-pos="<?= htmlspecialchars($progress_position_desktop, ENT_QUOTES, 'UTF-8') ?>"
    data-progress-mobile-pos="<?= htmlspecialchars($progress_position_mobile, ENT_QUOTES, 'UTF-8') ?>"
    <?= $theme_attr ?>
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <style>
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-cards-slider {
            position: relative;
            padding-top: var(--nb-cards-slider-padding-top, 72px);
            padding-bottom: var(--nb-cards-slider-padding-bottom, 72px);
            color: var(--nb-color-text, #111827);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__container {
            width: min(100%, var(--nb-cards-slider-content-width, 1600px));
            display: grid;
            gap: var(--nb-cards-slider-header-gap, 28px);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: var(--nb-cards-slider-header-gap, 28px);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__copy {
            display: grid;
            gap: .65rem;
            max-width: min(100%, 78rem);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__title {
            margin: 0;
            font-family: var(--nb-font-head, inherit);
            font-size: var(--nb-cards-slider-title-size, 48px);
            font-weight: var(--nb-cards-slider-title-weight, 800);
            line-height: var(--nb-cards-slider-title-line-height, 1.05);
            letter-spacing: var(--nb-cards-slider-title-letter-spacing, -1px);
            color: var(--nb-cards-slider-title-color, var(--nb-color-text, #111827));
            text-wrap: balance;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__subtitle {
            margin: 0;
            max-width: 72ch;
            font-size: var(--nb-cards-slider-subtitle-size, 17px);
            font-weight: var(--nb-cards-slider-subtitle-weight, 400);
            line-height: var(--nb-cards-slider-subtitle-line-height, 1.55);
            letter-spacing: var(--nb-cards-slider-subtitle-letter-spacing, 0);
            color: var(--nb-cards-slider-subtitle-color, var(--nb-color-text-muted, #5b6472));
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__more {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            width: fit-content;
            color: var(--nb-color-text, #111827);
            font-size: .84rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            text-decoration: none;
            white-space: nowrap;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__more::after {
            content: '→';
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__rail {
            position: relative;
            display: grid;
            gap: 1rem;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__viewport {
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__viewport::-webkit-scrollbar {
            display: none;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-swipe="0"] .nb-cards-slider__viewport {
            overflow-x: hidden;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__track {
            display: flex;
            gap: var(--nb-cards-slider-slide-gap, 24px);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__slide {
            flex: 0 0 calc((100% - (var(--nb-cards-slider-slides-per-view, 3) - 1) * var(--nb-cards-slider-slide-gap, 24px)) / var(--nb-cards-slider-slides-per-view, 3));
            min-width: 0;
            min-height: max(0px, var(--nb-cards-slider-min-height, 0px));
            scroll-snap-align: start;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__card {
            display: grid;
            grid-template-rows: auto 1fr;
            height: 100%;
            padding: var(--nb-cards-slider-surface-padding, 0);
            border: var(--nb-cards-slider-surface-border-width, 1px) solid var(--nb-cards-slider-surface-border-color, #dbe4ef);
            border-radius: var(--nb-cards-slider-surface-radius, 28px);
            background: var(--nb-cards-slider-surface-background, #ffffff);
            box-shadow: var(--nb-cards-slider-surface-shadow, var(--nb-shadow-sm, none));
            overflow: hidden;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__media {
            display: block;
            aspect-ratio: var(--nb-cards-slider-media-aspect-ratio, 4 / 3);
            overflow: hidden;
            border-radius: var(--nb-cards-slider-media-radius, 24px);
            background: color-mix(in srgb, var(--nb-color-text, #111827) 7%, var(--nb-color-surface, #fff));
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__media img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: var(--nb-cards-slider-media-object-fit, cover);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__body {
            display: grid;
            align-content: start;
            gap: .85rem;
            padding: clamp(1rem, 1.6vw, 1.4rem);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__eyebrow {
            font-size: var(--nb-cards-slider-eyebrow-size, 13px);
            font-weight: var(--nb-cards-slider-eyebrow-weight, 700);
            line-height: var(--nb-cards-slider-eyebrow-line-height, 1.4);
            letter-spacing: var(--nb-cards-slider-eyebrow-letter-spacing, 1px);
            text-transform: uppercase;
            color: var(--nb-cards-slider-eyebrow-color, #0f766e);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__slide-title {
            margin: 0;
            font-family: var(--nb-font-head, inherit);
            font-size: var(--nb-cards-slider-slide-title-size, 24px);
            font-weight: var(--nb-cards-slider-slide-title-weight, 800);
            line-height: var(--nb-cards-slider-slide-title-line-height, 1.2);
            letter-spacing: var(--nb-cards-slider-slide-title-letter-spacing, 0);
            color: var(--nb-cards-slider-slide-title-color, var(--nb-color-text, #111827));
            text-wrap: balance;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__slide-title a {
            color: inherit;
            text-decoration: none;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__text {
            margin: 0;
            font-size: var(--nb-cards-slider-slide-text-size, 16px);
            font-weight: var(--nb-cards-slider-slide-text-weight, 400);
            line-height: var(--nb-cards-slider-slide-text-line-height, 1.6);
            letter-spacing: var(--nb-cards-slider-slide-text-letter-spacing, 0);
            color: var(--nb-cards-slider-slide-text-color, var(--nb-color-text-muted, #5b6472));
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__meta {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem .7rem;
            align-items: center;
            font-size: var(--nb-cards-slider-slide-meta-size, 12px);
            font-weight: var(--nb-cards-slider-slide-meta-weight, 600);
            line-height: var(--nb-cards-slider-slide-meta-line-height, 1.35);
            letter-spacing: var(--nb-cards-slider-slide-meta-letter-spacing, 1px);
            text-transform: uppercase;
            color: var(--nb-cards-slider-slide-meta-color, var(--nb-color-text-muted, #64748b));
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__meta-dot::before {
            content: '•';
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__actions {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            margin-top: .2rem;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: .72rem 1rem;
            border-radius: 999px;
            text-decoration: none;
            transition: transform .18s ease, background-color .18s ease, border-color .18s ease, color .18s ease;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__action:hover {
            transform: translateY(-1px);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__action--primary {
            background: var(--nb-color-text, #111827);
            border: 1px solid var(--nb-color-text, #111827);
            color: var(--nb-cards-slider-primary-color, #ffffff);
            font-size: var(--nb-cards-slider-primary-size, 14px);
            font-weight: var(--nb-cards-slider-primary-weight, 700);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__action--secondary {
            background: transparent;
            border: 1px solid color-mix(in srgb, var(--nb-color-text, #111827) 18%, transparent);
            color: var(--nb-cards-slider-secondary-color, var(--nb-color-text, #111827));
            font-size: var(--nb-cards-slider-secondary-size, 14px);
            font-weight: var(--nb-cards-slider-secondary-weight, 600);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__nav {
            display: flex;
            gap: .7rem;
            z-index: 2;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__nav-button {
            width: var(--nb-cards-slider-nav-size, 46px);
            height: var(--nb-cards-slider-nav-size, 46px);
            border-radius: var(--nb-cards-slider-nav-radius, 999px);
            border: 1px solid var(--nb-cards-slider-nav-border, #0f172a);
            background: var(--nb-cards-slider-nav-bg, #0f172a);
            color: var(--nb-cards-slider-nav-color, #ffffff);
            box-shadow: var(--nb-cards-slider-nav-shadow, var(--nb-shadow-md, none));
            cursor: pointer;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__nav-button[disabled] {
            opacity: .45;
            cursor: default;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--nb-cards-slider-dot-gap, 8px);
            z-index: 2;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__dot {
            width: var(--nb-cards-slider-dot-size, 10px);
            height: var(--nb-cards-slider-dot-size, 10px);
            padding: 0;
            border: 0;
            border-radius: 999px;
            background: var(--nb-cards-slider-dot-color, #cbd5e1);
            cursor: pointer;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__dot.is-active {
            background: var(--nb-cards-slider-dot-active-color, #0f172a);
            transform: scale(1.08);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__progress {
            width: min(100%, 16rem);
            height: var(--nb-cards-slider-progress-height, 4px);
            border-radius: var(--nb-cards-slider-progress-radius, 999px);
            background: var(--nb-cards-slider-progress-track, #e2e8f0);
            overflow: hidden;
            z-index: 2;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__progress-fill {
            display: block;
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: var(--nb-cards-slider-progress-fill, #0f172a);
            transition: width .2s ease;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__footer-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nav-desktop-pos="overlay"] .nb-cards-slider__nav {
            position: absolute;
            top: clamp(.9rem, 2vw, 1.4rem);
            right: clamp(.9rem, 2vw, 1.4rem);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nav-desktop-pos="below"] .nb-cards-slider__nav {
            position: static;
            justify-self: end;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nav-desktop-pos="hidden"] .nb-cards-slider__nav,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-pagination-desktop-pos="hidden"] .nb-cards-slider__pagination,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-progress-desktop-pos="hidden"] .nb-cards-slider__progress {
            display: none;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-pagination-desktop-pos="overlay"] .nb-cards-slider__pagination,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-progress-desktop-pos="overlay"] .nb-cards-slider__progress {
            position: absolute;
            left: clamp(1rem, 2vw, 1.4rem);
            bottom: clamp(1rem, 2vw, 1.4rem);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="alt"] .nb-cards-slider__card {
            background: #f8fafc;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] {
            color: #e5edf5;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-cards-slider__card {
            background: #0f172a;
            border-color: rgba(148, 163, 184, .25);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-cards-slider__title,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-cards-slider__slide-title,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-cards-slider__more {
            color: #f8fafc;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-cards-slider__subtitle,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-cards-slider__text,
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-cards-slider__meta {
            color: rgba(226, 232, 240, .78);
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-cards-slider__action--primary {
            background: #f8fafc;
            border-color: #f8fafc;
            color: #0f172a;
        }
        #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nb-theme="dark"] .nb-cards-slider__action--secondary {
            border-color: rgba(226, 232, 240, .22);
            color: #f8fafc;
        }
        @media (max-width: 767px) {
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>.nb-cards-slider {
                padding-top: var(--nb-cards-slider-padding-top-mobile, 40px);
                padding-bottom: var(--nb-cards-slider-padding-bottom-mobile, 40px);
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__container {
                gap: var(--nb-cards-slider-header-gap-mobile, 18px);
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__header {
                flex-direction: column;
                align-items: start;
                gap: .9rem;
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__title {
                font-size: var(--nb-cards-slider-title-size-mobile, 32px);
                font-weight: var(--nb-cards-slider-title-weight-mobile, 800);
                line-height: var(--nb-cards-slider-title-line-height-mobile, 1.08);
                letter-spacing: var(--nb-cards-slider-title-letter-spacing-mobile, -.5px);
                color: var(--nb-cards-slider-title-color-mobile, var(--nb-cards-slider-title-color, var(--nb-color-text, #111827)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__subtitle {
                font-size: var(--nb-cards-slider-subtitle-size-mobile, 15px);
                font-weight: var(--nb-cards-slider-subtitle-weight-mobile, 400);
                line-height: var(--nb-cards-slider-subtitle-line-height-mobile, 1.55);
                letter-spacing: var(--nb-cards-slider-subtitle-letter-spacing-mobile, 0);
                color: var(--nb-cards-slider-subtitle-color-mobile, var(--nb-cards-slider-subtitle-color, var(--nb-color-text-muted, #5b6472)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__track {
                gap: var(--nb-cards-slider-slide-gap-mobile, 16px);
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__slide {
                flex-basis: calc((100% - (var(--nb-cards-slider-slides-per-view-mobile, 1) - 1) * var(--nb-cards-slider-slide-gap-mobile, 16px)) / var(--nb-cards-slider-slides-per-view-mobile, 1));
                min-height: max(0px, var(--nb-cards-slider-min-height-mobile, 0px));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__eyebrow {
                font-size: var(--nb-cards-slider-eyebrow-size-mobile, 12px);
                font-weight: var(--nb-cards-slider-eyebrow-weight-mobile, 700);
                line-height: var(--nb-cards-slider-eyebrow-line-height-mobile, 1.4);
                letter-spacing: var(--nb-cards-slider-eyebrow-letter-spacing-mobile, 1px);
                color: var(--nb-cards-slider-eyebrow-color-mobile, var(--nb-cards-slider-eyebrow-color, #0f766e));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__slide-title {
                font-size: var(--nb-cards-slider-slide-title-size-mobile, 19px);
                font-weight: var(--nb-cards-slider-slide-title-weight-mobile, 800);
                line-height: var(--nb-cards-slider-slide-title-line-height-mobile, 1.2);
                letter-spacing: var(--nb-cards-slider-slide-title-letter-spacing-mobile, 0);
                color: var(--nb-cards-slider-slide-title-color-mobile, var(--nb-cards-slider-slide-title-color, var(--nb-color-text, #111827)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__text {
                font-size: var(--nb-cards-slider-slide-text-size-mobile, 14px);
                font-weight: var(--nb-cards-slider-slide-text-weight-mobile, 400);
                line-height: var(--nb-cards-slider-slide-text-line-height-mobile, 1.6);
                letter-spacing: var(--nb-cards-slider-slide-text-letter-spacing-mobile, 0);
                color: var(--nb-cards-slider-slide-text-color-mobile, var(--nb-cards-slider-slide-text-color, var(--nb-color-text-muted, #5b6472)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__meta {
                font-size: var(--nb-cards-slider-slide-meta-size-mobile, 11px);
                font-weight: var(--nb-cards-slider-slide-meta-weight-mobile, 600);
                line-height: var(--nb-cards-slider-slide-meta-line-height-mobile, 1.35);
                letter-spacing: var(--nb-cards-slider-slide-meta-letter-spacing-mobile, 1px);
                color: var(--nb-cards-slider-slide-meta-color-mobile, var(--nb-cards-slider-slide-meta-color, var(--nb-color-text-muted, #64748b)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__action--primary {
                font-size: var(--nb-cards-slider-primary-size-mobile, 13px);
                font-weight: var(--nb-cards-slider-primary-weight-mobile, 700);
                color: var(--nb-cards-slider-primary-color-mobile, var(--nb-cards-slider-primary-color, #ffffff));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__action--secondary {
                font-size: var(--nb-cards-slider-secondary-size-mobile, 13px);
                font-weight: var(--nb-cards-slider-secondary-weight-mobile, 600);
                color: var(--nb-cards-slider-secondary-color-mobile, var(--nb-cards-slider-secondary-color, var(--nb-color-text, #111827)));
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nav-mobile-pos="hidden"] .nb-cards-slider__nav,
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-pagination-mobile-pos="hidden"] .nb-cards-slider__pagination,
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-progress-mobile-pos="hidden"] .nb-cards-slider__progress {
                display: none;
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nav-mobile-pos="overlay"] .nb-cards-slider__nav {
                position: absolute;
                top: .85rem;
                right: .85rem;
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-nav-mobile-pos="below"] .nb-cards-slider__nav {
                position: static;
                justify-self: start;
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-pagination-mobile-pos="overlay"] .nb-cards-slider__pagination,
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>[data-progress-mobile-pos="overlay"] .nb-cards-slider__progress {
                position: absolute;
                left: .9rem;
                bottom: .9rem;
            }
            #<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?> .nb-cards-slider__footer-controls {
                justify-content: start;
            }
        }
    </style>
    <div class="nb-container nb-cards-slider__container">
        <?php if (($title_visible && $heading !== '') || ($subtitle_visible && $intro_html !== '') || ($section_link_label !== '' && $section_link_url !== '')) { ?>
            <header class="nb-cards-slider__header" data-nb-entity="header">
                <div class="nb-cards-slider__copy">
                    <?php if ($title_visible && $heading !== '') { ?>
                        <<?= $heading_tag ?> class="nb-cards-slider__title" data-nb-entity="title"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></<?= $heading_tag ?>>
                    <?php } ?>
                    <?php if ($subtitle_visible && $intro_html !== '') { ?>
                        <div class="nb-cards-slider__subtitle" data-nb-entity="subtitle"><?= $intro_html ?></div>
                    <?php } ?>
                </div>
                <?php if ($section_link_label !== '' && $section_link_url !== '') { ?>
                    <a class="nb-cards-slider__more" href="<?= htmlspecialchars($section_link_url, ENT_QUOTES, 'UTF-8') ?>" data-nb-entity="primaryButton"><?= htmlspecialchars($section_link_label, ENT_QUOTES, 'UTF-8') ?></a>
                <?php } ?>
            </header>
        <?php } ?>

        <?php if ($slides_count > 0) { ?>
            <div class="nb-cards-slider__rail">
                <div class="nb-cards-slider__viewport" data-role="slider-viewport">
                    <div class="nb-cards-slider__track" data-role="slider-track" data-slide-count="<?= (int) $slides_count ?>" data-nb-entity="slides">
                        <?php foreach ($slides as $index => $slide) { ?>
                            <article class="nb-cards-slider__slide" data-role="slide" data-order="<?= (int) $index ?>" data-nb-entity="slide">
                                <div class="nb-cards-slider__card" data-nb-entity="slideSurface">
                                    <?php if ($show_media && $slide['image'] !== '') { ?>
                                        <?php if ($slide['recordUrl'] !== '') { ?>
                                            <a class="nb-cards-slider__media" href="<?= $slide['recordUrl'] ?>" data-nb-entity="slideMedia">
                                                <img src="<?= $slide['image'] ?>" alt="<?= $slide['imageAlt'] ?>" loading="lazy">
                                            </a>
                                        <?php } else { ?>
                                            <div class="nb-cards-slider__media" data-nb-entity="slideMedia">
                                                <img src="<?= $slide['image'] ?>" alt="<?= $slide['imageAlt'] ?>" loading="lazy">
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                    <div class="nb-cards-slider__body">
                                        <?php if ($show_eyebrow && $slide['eyebrow'] !== '') { ?>
                                            <div class="nb-cards-slider__eyebrow" data-nb-entity="slideEyebrow"><?= $slide['eyebrow'] ?></div>
                                        <?php } ?>
                                        <?php if ($slide['title'] !== '') { ?>
                                            <h3 class="nb-cards-slider__slide-title" data-nb-entity="slideTitle">
                                                <?php if ($slide['recordUrl'] !== '') { ?>
                                                    <a href="<?= $slide['recordUrl'] ?>"><?= $slide['title'] ?></a>
                                                <?php } else { ?>
                                                    <?= $slide['title'] ?>
                                                <?php } ?>
                                            </h3>
                                        <?php } ?>
                                        <?php if ($show_text && $slide['text'] !== '') { ?>
                                            <div class="nb-cards-slider__text" data-nb-entity="slideText"><?= $slide['text'] ?></div>
                                        <?php } ?>
                                        <?php if ($show_meta && ($slide['metaLabel'] !== '' || $slide['date'] !== '')) { ?>
                                            <div class="nb-cards-slider__meta" data-nb-entity="slideMeta">
                                                <?php if ($slide['metaLabel'] !== '') { ?>
                                                    <span><?= $slide['metaLabel'] ?></span>
                                                <?php } ?>
                                                <?php if ($slide['metaLabel'] !== '' && $slide['date'] !== '') { ?>
                                                    <span class="nb-cards-slider__meta-dot" aria-hidden="true"></span>
                                                <?php } ?>
                                                <?php if ($slide['date'] !== '') { ?>
                                                    <span><?= $slide['date'] ?></span>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                        <?php if (($show_primary && $slide['primaryLabel'] !== '' && $slide['primaryUrl'] !== '') || ($show_secondary && $slide['secondaryLabel'] !== '' && $slide['secondaryUrl'] !== '')) { ?>
                                            <div class="nb-cards-slider__actions">
                                                <?php if ($show_primary && $slide['primaryLabel'] !== '' && $slide['primaryUrl'] !== '') { ?>
                                                    <a class="nb-cards-slider__action nb-cards-slider__action--primary" href="<?= $slide['primaryUrl'] ?>" data-nb-entity="slidePrimaryAction"><?= $slide['primaryLabel'] ?></a>
                                                <?php } ?>
                                                <?php if ($show_secondary && $slide['secondaryLabel'] !== '' && $slide['secondaryUrl'] !== '') { ?>
                                                    <a class="nb-cards-slider__action nb-cards-slider__action--secondary" href="<?= $slide['secondaryUrl'] ?>" data-nb-entity="slideSecondaryAction"><?= $slide['secondaryLabel'] ?></a>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                </div>

                <?php if ($show_navigation && $controls_visible) { ?>
                    <div class="nb-cards-slider__nav" data-role="slider-nav">
                        <button class="nb-cards-slider__nav-button" type="button" data-role="slider-prev" aria-label="Предыдущие карточки">←</button>
                        <button class="nb-cards-slider__nav-button" type="button" data-role="slider-next" aria-label="Следующие карточки">→</button>
                    </div>
                <?php } ?>

                <?php if (($show_pagination || $show_progress) && $controls_visible) { ?>
                    <div class="nb-cards-slider__footer-controls">
                        <?php if ($show_pagination) { ?>
                            <div class="nb-cards-slider__pagination" data-role="slider-pagination"></div>
                        <?php } ?>
                        <?php if ($show_progress) { ?>
                            <div class="nb-cards-slider__progress" data-role="slider-progress">
                                <span class="nb-cards-slider__progress-fill" data-role="slider-progress-fill"></span>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <?php if ($controls_visible) { ?>
        <script>
            (function() {
                var root = document.getElementById(<?= json_encode($block_dom_id) ?>);
                if (!root) {
                    return;
                }

                var viewport = root.querySelector('[data-role="slider-viewport"]');
                var slides = Array.prototype.slice.call(root.querySelectorAll('[data-role="slide"]'));
                if (!viewport || slides.length < 2) {
                    return;
                }

                var prevButton = root.querySelector('[data-role="slider-prev"]');
                var nextButton = root.querySelector('[data-role="slider-next"]');
                var pagination = root.querySelector('[data-role="slider-pagination"]');
                var progressFill = root.querySelector('[data-role="slider-progress-fill"]');
                var autoplayDelay = parseInt(root.getAttribute('data-autoplay-delay') || '4500', 10);
                var autoplayEnabled = root.getAttribute('data-autoplay') === '1';
                var loop = root.getAttribute('data-loop') === '1';
                var autoplayTimer = null;

                function isMobile() {
                    return window.matchMedia('(max-width: 767px)').matches;
                }

                function slidesPerView() {
                    var attr = isMobile() ? 'data-slides-per-view-mobile' : 'data-slides-per-view-desktop';
                    var value = parseInt(root.getAttribute(attr) || '1', 10);
                    return Math.max(1, value || 1);
                }

                function pageCount() {
                    return Math.max(1, Math.ceil(slides.length / slidesPerView()));
                }

                function pageWidth() {
                    return viewport.clientWidth || 1;
                }

                function currentPage() {
                    return Math.max(0, Math.min(pageCount() - 1, Math.round(viewport.scrollLeft / pageWidth())));
                }

                function scrollToPage(index) {
                    var max = pageCount() - 1;
                    var target = index;
                    if (loop) {
                        if (target < 0) {
                            target = max;
                        }
                        if (target > max) {
                            target = 0;
                        }
                    } else {
                        target = Math.max(0, Math.min(max, target));
                    }

                    viewport.scrollTo({ left: target * pageWidth(), behavior: 'smooth' });
                }

                function renderPagination(activePage) {
                    if (!pagination) {
                        return;
                    }

                    var total = pageCount();
                    pagination.innerHTML = '';
                    for (var index = 0; index < total; index += 1) {
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'nb-cards-slider__dot' + (index === activePage ? ' is-active' : '');
                        button.setAttribute('aria-label', 'Перейти к группе ' + (index + 1));
                        button.setAttribute('data-page', String(index));
                        button.addEventListener('click', function(event) {
                            var page = parseInt(event.currentTarget.getAttribute('data-page') || '0', 10);
                            scrollToPage(page);
                            restartAutoplay();
                        });
                        pagination.appendChild(button);
                    }
                }

                function updateState() {
                    var activePage = currentPage();
                    var total = pageCount();

                    if (prevButton) {
                        prevButton.disabled = !loop && activePage <= 0;
                    }
                    if (nextButton) {
                        nextButton.disabled = !loop && activePage >= total - 1;
                    }

                    renderPagination(activePage);

                    if (progressFill) {
                        progressFill.style.width = ((activePage + 1) / total * 100) + '%';
                    }
                }

                function stopAutoplay() {
                    if (autoplayTimer) {
                        window.clearInterval(autoplayTimer);
                        autoplayTimer = null;
                    }
                }

                function startAutoplay() {
                    stopAutoplay();
                    if (!autoplayEnabled || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        return;
                    }

                    autoplayTimer = window.setInterval(function() {
                        var active = currentPage();
                        var total = pageCount();
                        if (!loop && active >= total - 1) {
                            stopAutoplay();
                            return;
                        }
                        scrollToPage(active + 1);
                    }, Math.max(1000, autoplayDelay || 4500));
                }

                function restartAutoplay() {
                    if (!autoplayEnabled) {
                        return;
                    }
                    startAutoplay();
                }

                if (prevButton) {
                    prevButton.addEventListener('click', function() {
                        scrollToPage(currentPage() - 1);
                        restartAutoplay();
                    });
                }

                if (nextButton) {
                    nextButton.addEventListener('click', function() {
                        scrollToPage(currentPage() + 1);
                        restartAutoplay();
                    });
                }

                viewport.addEventListener('scroll', updateState, { passive: true });
                viewport.addEventListener('mouseenter', stopAutoplay);
                viewport.addEventListener('mouseleave', startAutoplay);
                viewport.addEventListener('focusin', stopAutoplay);
                viewport.addEventListener('focusout', startAutoplay);
                window.addEventListener('resize', updateState);

                updateState();
                startAutoplay();
            })();
        </script>
    <?php } ?>
</section>
