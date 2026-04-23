<?php
/**
 * NordicBlocks — блок: Hero
 * Переменные контекста: $props (array), $block_type, $block_uid
 */

require_once dirname(__DIR__) . '/render_helpers.php';

$hero_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'hero'))
    ? $block_contract
    : null;

if (!function_exists('nb_hero_prop_int')) {
    function nb_hero_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_hero_prop_select')) {
    function nb_hero_prop_select(array $props, $key, array $allowed, $default) {
        $value = (string) ($props[$key] ?? $default);
        return in_array($value, $allowed, true) ? $value : $default;
    }
}

if (!function_exists('nb_hero_nested_has')) {
    function nb_hero_nested_has(array $source, array $path) {
        $cursor = $source;
        foreach ($path as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return false;
            }
            $cursor = $cursor[$segment];
        }

        return true;
    }
}

if (!function_exists('nb_hero_nested_get')) {
    function nb_hero_nested_get(array $source, array $path, $default = null) {
        $cursor = $source;
        foreach ($path as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }
}

if (!function_exists('nb_hero_preset_defaults')) {
    function nb_hero_preset_defaults($preset) {
        $presets = [
            'classic' => [
                'mode' => 'centered',
                'containerMode' => 'contained',
                'mediaPositionDesktop' => 'start',
                'mediaPositionMobile' => 'top',
                'paddingTopDesktop' => 96,
                'paddingBottomDesktop' => 96,
                'paddingTopMobile' => 56,
                'paddingBottomMobile' => 56,
                'mediaRadius' => 28,
                'mediaSurfaceRadius' => 28,
                'mediaSurfaceShadow' => 'lg',
            ],
            'split-left' => [
                'mode' => 'split',
                'containerMode' => 'contained',
                'mediaPositionDesktop' => 'start',
                'mediaPositionMobile' => 'top',
                'paddingTopDesktop' => 96,
                'paddingBottomDesktop' => 96,
                'paddingTopMobile' => 56,
                'paddingBottomMobile' => 56,
                'mediaRadius' => 28,
                'mediaSurfaceRadius' => 28,
                'mediaSurfaceShadow' => 'lg',
            ],
            'split-right' => [
                'mode' => 'split',
                'containerMode' => 'contained',
                'mediaPositionDesktop' => 'end',
                'mediaPositionMobile' => 'top',
                'paddingTopDesktop' => 96,
                'paddingBottomDesktop' => 96,
                'paddingTopMobile' => 56,
                'paddingBottomMobile' => 56,
                'mediaRadius' => 28,
                'mediaSurfaceRadius' => 28,
                'mediaSurfaceShadow' => 'lg',
            ],
            'edge-left' => [
                'mode' => 'split',
                'containerMode' => 'fluid',
                'mediaPositionDesktop' => 'start',
                'mediaPositionMobile' => 'top',
                'paddingTopDesktop' => 96,
                'paddingBottomDesktop' => 96,
                'paddingTopMobile' => 56,
                'paddingBottomMobile' => 56,
                'mediaRadius' => 0,
                'mediaSurfaceRadius' => 0,
                'mediaSurfaceShadow' => 'none',
            ],
            'edge-right' => [
                'mode' => 'split',
                'containerMode' => 'fluid',
                'mediaPositionDesktop' => 'end',
                'mediaPositionMobile' => 'top',
                'paddingTopDesktop' => 96,
                'paddingBottomDesktop' => 96,
                'paddingTopMobile' => 56,
                'paddingBottomMobile' => 56,
                'mediaRadius' => 0,
                'mediaSurfaceRadius' => 0,
                'mediaSurfaceShadow' => 'none',
            ],
            'strip' => [
                'mode' => 'split',
                'containerMode' => 'fluid',
                'mediaPositionDesktop' => 'start',
                'mediaPositionMobile' => 'top',
                'paddingTopDesktop' => 0,
                'paddingBottomDesktop' => 0,
                'paddingTopMobile' => 0,
                'paddingBottomMobile' => 0,
                'mediaRadius' => 0,
                'mediaSurfaceRadius' => 0,
                'mediaSurfaceShadow' => 'none',
            ],
        ];

        return $presets[$preset] ?? $presets['classic'];
    }
}

$hero_preset = 'classic';
$preset_defaults = nb_hero_preset_defaults($hero_preset);

if ($hero_contract) {
    $hero_preset = (string) nb_hero_nested_get($hero_contract, ['layout', 'preset'], 'classic');
    if (!in_array($hero_preset, ['classic', 'split-left', 'split-right', 'edge-left', 'edge-right', 'strip'], true)) {
        $hero_preset = 'classic';
    }
    $preset_defaults = nb_hero_preset_defaults($hero_preset);

    $layout = in_array(nb_hero_nested_get($hero_contract, ['layout', 'desktop', 'mode'], ''), ['centered', 'left', 'split'], true)
        ? (string) nb_hero_nested_get($hero_contract, ['layout', 'desktop', 'mode'], 'centered')
        : $preset_defaults['mode'];
    $container_mode = in_array(nb_hero_nested_get($hero_contract, ['layout', 'desktop', 'containerMode'], ''), ['contained', 'fluid'], true)
        ? (string) nb_hero_nested_get($hero_contract, ['layout', 'desktop', 'containerMode'], 'contained')
        : $preset_defaults['containerMode'];
    $media_position_desktop = in_array(nb_hero_nested_get($hero_contract, ['layout', 'desktop', 'mediaPosition'], ''), ['start', 'end'], true)
        ? (string) nb_hero_nested_get($hero_contract, ['layout', 'desktop', 'mediaPosition'], 'start')
        : $preset_defaults['mediaPositionDesktop'];
    $media_position_mobile = in_array(nb_hero_nested_get($hero_contract, ['layout', 'mobile', 'mediaPosition'], ''), ['top', 'bottom'], true)
        ? (string) nb_hero_nested_get($hero_contract, ['layout', 'mobile', 'mediaPosition'], 'top')
        : $preset_defaults['mediaPositionMobile'];
    $theme = in_array($hero_contract['design']['section']['theme'] ?? '', ['light', 'dark', 'accent'], true)
        ? (string) $hero_contract['design']['section']['theme'] : 'light';
    $background_mode = (string) ($hero_contract['design']['section']['background']['mode'] ?? 'theme');
    $background_style = nb_block_build_background_style((array) ($hero_contract['design']['section']['background'] ?? []));

    $eyebrow = htmlspecialchars(trim((string) ($hero_contract['content']['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
    $heading = htmlspecialchars(trim((string) ($hero_contract['content']['title'] ?? 'Заголовок')), ENT_QUOTES, 'UTF-8');
    $subhead = htmlspecialchars(trim((string) ($hero_contract['content']['subtitle'] ?? '')), ENT_QUOTES, 'UTF-8');
    $title_visible = !array_key_exists('visible', (array) ($hero_contract['design']['entities']['title'] ?? [])) || !empty($hero_contract['design']['entities']['title']['visible']);
    $subtitle_visible = !array_key_exists('visible', (array) ($hero_contract['design']['entities']['subtitle'] ?? [])) || !empty($hero_contract['design']['entities']['subtitle']['visible']);
    $heading_tag = htmlspecialchars((string) ($hero_contract['design']['entities']['title']['tag'] ?? 'h1'), ENT_QUOTES, 'UTF-8');
    $eyebrow_size_desktop = (int) ($hero_contract['design']['entities']['eyebrow']['desktop']['fontSize'] ?? 14);
    $eyebrow_size_mobile = (int) ($hero_contract['design']['entities']['eyebrow']['mobile']['fontSize'] ?? 13);
    $eyebrow_margin_bottom_desktop = (int) ($hero_contract['design']['entities']['eyebrow']['desktop']['marginBottom'] ?? 16);
    $eyebrow_margin_bottom_mobile = (int) ($hero_contract['design']['entities']['eyebrow']['mobile']['marginBottom'] ?? 14);
    $eyebrow_weight_desktop = (int) ($hero_contract['design']['entities']['eyebrow']['desktop']['weight'] ?? 600);
    $eyebrow_weight_mobile = (int) ($hero_contract['design']['entities']['eyebrow']['mobile']['weight'] ?? $eyebrow_weight_desktop);
    $eyebrow_color_desktop = trim((string) ($hero_contract['design']['entities']['eyebrow']['desktop']['color'] ?? ''));
    $eyebrow_color_mobile = trim((string) ($hero_contract['design']['entities']['eyebrow']['mobile']['color'] ?? ''));
    $eyebrow_line_height_desktop = (float) (($hero_contract['design']['entities']['eyebrow']['desktop']['lineHeightPercent'] ?? 140) / 100);
    $eyebrow_line_height_mobile = (float) (($hero_contract['design']['entities']['eyebrow']['mobile']['lineHeightPercent'] ?? 140) / 100);
    $eyebrow_letter_spacing_desktop = (float) ($hero_contract['design']['entities']['eyebrow']['desktop']['letterSpacing'] ?? 1);
    $eyebrow_letter_spacing_mobile = (float) ($hero_contract['design']['entities']['eyebrow']['mobile']['letterSpacing'] ?? $eyebrow_letter_spacing_desktop);
    $eyebrow_text_transform = (string) ($hero_contract['design']['entities']['eyebrow']['textTransform'] ?? 'uppercase');
    $title_weight_desktop = (int) ($hero_contract['design']['entities']['title']['desktop']['weight'] ?? 900);
    $title_weight_mobile = (int) ($hero_contract['design']['entities']['title']['mobile']['weight'] ?? $title_weight_desktop);
    $title_size_desktop = (int) ($hero_contract['design']['entities']['title']['desktop']['fontSize'] ?? 64);
    $title_size_mobile = (int) ($hero_contract['design']['entities']['title']['mobile']['fontSize'] ?? 40);
    $title_color_desktop = trim((string) ($hero_contract['design']['entities']['title']['desktop']['color'] ?? ''));
    $title_color_mobile = trim((string) ($hero_contract['design']['entities']['title']['mobile']['color'] ?? ''));
    $title_line_height_desktop = (float) (($hero_contract['design']['entities']['title']['desktop']['lineHeightPercent'] ?? 110) / 100);
    $title_line_height_mobile = (float) (($hero_contract['design']['entities']['title']['mobile']['lineHeightPercent'] ?? 110) / 100);
    $title_letter_spacing_desktop = (float) ($hero_contract['design']['entities']['title']['desktop']['letterSpacing'] ?? 0);
    $title_letter_spacing_mobile = (float) ($hero_contract['design']['entities']['title']['mobile']['letterSpacing'] ?? $title_letter_spacing_desktop);
    $title_max_width_desktop = (int) ($hero_contract['design']['entities']['title']['desktop']['maxWidth'] ?? 600);
    $title_max_width_mobile = (int) ($hero_contract['design']['entities']['title']['mobile']['maxWidth'] ?? $title_max_width_desktop);
    $subtitle_weight_desktop = (int) ($hero_contract['design']['entities']['subtitle']['desktop']['weight'] ?? 400);
    $subtitle_weight_mobile = (int) ($hero_contract['design']['entities']['subtitle']['mobile']['weight'] ?? $subtitle_weight_desktop);
    $subtitle_size_desktop = (int) ($hero_contract['design']['entities']['subtitle']['desktop']['fontSize'] ?? 20);
    $subtitle_size_mobile = (int) ($hero_contract['design']['entities']['subtitle']['mobile']['fontSize'] ?? 18);
    $subtitle_color_desktop = trim((string) ($hero_contract['design']['entities']['subtitle']['desktop']['color'] ?? ''));
    $subtitle_color_mobile = trim((string) ($hero_contract['design']['entities']['subtitle']['mobile']['color'] ?? ''));
    $subtitle_line_height_desktop = (float) (($hero_contract['design']['entities']['subtitle']['desktop']['lineHeightPercent'] ?? 165) / 100);
    $subtitle_line_height_mobile = (float) (($hero_contract['design']['entities']['subtitle']['mobile']['lineHeightPercent'] ?? 165) / 100);
    $subtitle_letter_spacing_desktop = (float) ($hero_contract['design']['entities']['subtitle']['desktop']['letterSpacing'] ?? 0);
    $subtitle_letter_spacing_mobile = (float) ($hero_contract['design']['entities']['subtitle']['mobile']['letterSpacing'] ?? $subtitle_letter_spacing_desktop);
    $subtitle_max_width_desktop = (int) ($hero_contract['design']['entities']['subtitle']['desktop']['maxWidth'] ?? 720);
    $subtitle_max_width_mobile = (int) ($hero_contract['design']['entities']['subtitle']['mobile']['maxWidth'] ?? $subtitle_max_width_desktop);
    $title_margin_bottom_desktop = (int) ($hero_contract['design']['entities']['title']['desktop']['marginBottom'] ?? 16);
    $title_margin_bottom_mobile = (int) ($hero_contract['design']['entities']['title']['mobile']['marginBottom'] ?? 14);
    $subtitle_margin_bottom_desktop = (int) ($hero_contract['design']['entities']['subtitle']['desktop']['marginBottom'] ?? 24);
    $subtitle_margin_bottom_mobile = (int) ($hero_contract['design']['entities']['subtitle']['mobile']['marginBottom'] ?? 20);
    $meta_size_desktop = (int) ($hero_contract['design']['entities']['meta']['desktop']['fontSize'] ?? 14);
    $meta_size_mobile = (int) ($hero_contract['design']['entities']['meta']['mobile']['fontSize'] ?? 13);
    $meta_margin_bottom_desktop = (int) ($hero_contract['design']['entities']['meta']['desktop']['marginBottom'] ?? 24);
    $meta_margin_bottom_mobile = (int) ($hero_contract['design']['entities']['meta']['mobile']['marginBottom'] ?? 20);
    $meta_weight_desktop = (int) ($hero_contract['design']['entities']['meta']['desktop']['weight'] ?? 600);
    $meta_weight_mobile = (int) ($hero_contract['design']['entities']['meta']['mobile']['weight'] ?? $meta_weight_desktop);
    $meta_line_height_desktop = (float) (($hero_contract['design']['entities']['meta']['desktop']['lineHeightPercent'] ?? 140) / 100);
    $meta_line_height_mobile = (float) (($hero_contract['design']['entities']['meta']['mobile']['lineHeightPercent'] ?? 140) / 100);
    $meta_letter_spacing_desktop = (float) ($hero_contract['design']['entities']['meta']['desktop']['letterSpacing'] ?? 0);
    $meta_letter_spacing_mobile = (float) ($hero_contract['design']['entities']['meta']['mobile']['letterSpacing'] ?? $meta_letter_spacing_desktop);
    $meta_color_desktop = trim((string) ($hero_contract['design']['entities']['meta']['desktop']['color'] ?? ''));
    $meta_color_mobile = trim((string) ($hero_contract['design']['entities']['meta']['mobile']['color'] ?? ''));
    $buttons_text_size_desktop = (int) ($hero_contract['design']['entities']['buttonsText']['desktop']['fontSize'] ?? 16);
    $buttons_text_size_mobile = (int) ($hero_contract['design']['entities']['buttonsText']['mobile']['fontSize'] ?? 15);
    $buttons_text_weight_desktop = (int) ($hero_contract['design']['entities']['buttonsText']['desktop']['weight'] ?? 600);
    $buttons_text_weight_mobile = (int) ($hero_contract['design']['entities']['buttonsText']['mobile']['weight'] ?? $buttons_text_weight_desktop);
    $buttons_text_color_desktop = trim((string) ($hero_contract['design']['entities']['buttonsText']['desktop']['color'] ?? ''));
    $buttons_text_color_mobile = trim((string) ($hero_contract['design']['entities']['buttonsText']['mobile']['color'] ?? ''));
    $buttons_text_line_height_desktop = (float) (($hero_contract['design']['entities']['buttonsText']['desktop']['lineHeightPercent'] ?? 120) / 100);
    $buttons_text_line_height_mobile = (float) (($hero_contract['design']['entities']['buttonsText']['mobile']['lineHeightPercent'] ?? 120) / 100);
    $buttons_text_letter_spacing_desktop = (float) ($hero_contract['design']['entities']['buttonsText']['desktop']['letterSpacing'] ?? 0);
    $buttons_text_letter_spacing_mobile = (float) ($hero_contract['design']['entities']['buttonsText']['mobile']['letterSpacing'] ?? $buttons_text_letter_spacing_desktop);
    $content_width = (int) ($hero_contract['layout']['desktop']['contentWidth'] ?? 640);
    $content_gap_desktop = (int) ($hero_contract['layout']['desktop']['contentGap'] ?? 40);
    $content_gap_mobile = (int) ($hero_contract['layout']['mobile']['contentGap'] ?? 24);
    $actions_gap_desktop = (int) ($hero_contract['layout']['desktop']['actionsGap'] ?? 12);
    $actions_gap_mobile = (int) ($hero_contract['layout']['mobile']['actionsGap'] ?? 10);
    $padding_top_desktop = (int) (nb_hero_nested_has($hero_contract, ['layout', 'desktop', 'paddingTop']) ? nb_hero_nested_get($hero_contract, ['layout', 'desktop', 'paddingTop'], 96) : $preset_defaults['paddingTopDesktop']);
    $padding_bottom_desktop = (int) (nb_hero_nested_has($hero_contract, ['layout', 'desktop', 'paddingBottom']) ? nb_hero_nested_get($hero_contract, ['layout', 'desktop', 'paddingBottom'], 96) : $preset_defaults['paddingBottomDesktop']);
    $padding_top_mobile = (int) (nb_hero_nested_has($hero_contract, ['layout', 'mobile', 'paddingTop']) ? nb_hero_nested_get($hero_contract, ['layout', 'mobile', 'paddingTop'], 56) : $preset_defaults['paddingTopMobile']);
    $padding_bottom_mobile = (int) (nb_hero_nested_has($hero_contract, ['layout', 'mobile', 'paddingBottom']) ? nb_hero_nested_get($hero_contract, ['layout', 'mobile', 'paddingBottom'], 56) : $preset_defaults['paddingBottomMobile']);
    $min_height_desktop = (int) ($hero_contract['layout']['desktop']['minHeight'] ?? 0);
    $min_height_mobile = (int) ($hero_contract['layout']['mobile']['minHeight'] ?? 0);
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($hero_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($hero_contract['runtime']['animation']['delay'] ?? 0),
    ]);

    $btn1_label = htmlspecialchars(trim((string) ($hero_contract['content']['primaryButton']['label'] ?? '')), ENT_QUOTES, 'UTF-8');
    $btn1_url = htmlspecialchars(trim((string) ($hero_contract['content']['primaryButton']['url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $title_url = $btn1_url !== '#' ? $btn1_url : '';
    $btn1_style = in_array($hero_contract['design']['entities']['primaryButton']['style'] ?? '', ['primary', 'outline', 'ghost'], true)
        ? (string) $hero_contract['design']['entities']['primaryButton']['style'] : 'primary';
    $btn2_label = htmlspecialchars(trim((string) ($hero_contract['content']['secondaryButton']['label'] ?? '')), ENT_QUOTES, 'UTF-8');
    $btn2_url = htmlspecialchars(trim((string) ($hero_contract['content']['secondaryButton']['url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $btn2_style = in_array($hero_contract['design']['entities']['secondaryButton']['style'] ?? '', ['primary', 'outline', 'ghost'], true)
        ? (string) $hero_contract['design']['entities']['secondaryButton']['style'] : 'outline';
    $btn3_label = htmlspecialchars(trim((string) ($hero_contract['content']['tertiaryButton']['label'] ?? '')), ENT_QUOTES, 'UTF-8');
    $btn3_url = htmlspecialchars(trim((string) ($hero_contract['content']['tertiaryButton']['url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $btn3_style = in_array($hero_contract['design']['entities']['tertiaryButton']['style'] ?? '', ['primary', 'outline', 'ghost'], true)
        ? (string) $hero_contract['design']['entities']['tertiaryButton']['style'] : 'ghost';

    $media_aspect_ratio = in_array($hero_contract['design']['entities']['media']['aspectRatio'] ?? '16:10', ['auto', '16:10', '16:9', '4:3', '1:1', '3:4'], true)
        ? (string) $hero_contract['design']['entities']['media']['aspectRatio'] : '16:10';
    $media_object_fit = in_array($hero_contract['design']['entities']['media']['objectFit'] ?? 'cover', ['cover', 'contain'], true)
        ? (string) $hero_contract['design']['entities']['media']['objectFit'] : 'cover';
    $media_radius = (int) (nb_hero_nested_has($hero_contract, ['design', 'entities', 'media', 'radius']) ? nb_hero_nested_get($hero_contract, ['design', 'entities', 'media', 'radius'], 28) : $preset_defaults['mediaRadius']);
    $media_surface_background_color = trim((string) ($hero_contract['design']['entities']['mediaSurface']['backgroundColor'] ?? ''));
    $media_surface_background_mode = in_array($hero_contract['design']['entities']['mediaSurface']['backgroundMode'] ?? '', ['transparent', 'solid'], true)
        ? (string) $hero_contract['design']['entities']['mediaSurface']['backgroundMode']
        : ($media_surface_background_color !== '' ? 'solid' : 'transparent');
    $media_surface_padding = (int) ($hero_contract['design']['entities']['mediaSurface']['padding'] ?? 0);
    $media_surface_radius = (int) (nb_hero_nested_has($hero_contract, ['design', 'entities', 'mediaSurface', 'radius']) ? nb_hero_nested_get($hero_contract, ['design', 'entities', 'mediaSurface', 'radius'], 28) : $preset_defaults['mediaSurfaceRadius']);
    $media_surface_border_width = (int) ($hero_contract['design']['entities']['mediaSurface']['borderWidth'] ?? 0);
    $media_surface_border_color = trim((string) ($hero_contract['design']['entities']['mediaSurface']['borderColor'] ?? ''));
    $media_surface_shadow = in_array($hero_contract['design']['entities']['mediaSurface']['shadow'] ?? '', ['none', 'sm', 'md', 'lg'], true)
        ? (string) $hero_contract['design']['entities']['mediaSurface']['shadow'] : $preset_defaults['mediaSurfaceShadow'];

    $image = htmlspecialchars(trim((string) ($hero_contract['content']['media']['image'] ?? '')), ENT_QUOTES, 'UTF-8');
    $image_alt = htmlspecialchars(trim((string) ($hero_contract['content']['media']['alt'] ?? '')), ENT_QUOTES, 'UTF-8');
    $meta_category = htmlspecialchars(trim((string) ($hero_contract['content']['meta']['category'] ?? '')), ENT_QUOTES, 'UTF-8');
    $meta_author = htmlspecialchars(trim((string) ($hero_contract['content']['meta']['author'] ?? '')), ENT_QUOTES, 'UTF-8');
    $meta_date = htmlspecialchars(trim((string) ($hero_contract['content']['meta']['date'] ?? '')), ENT_QUOTES, 'UTF-8');
    $meta_views = htmlspecialchars(trim((string) ($hero_contract['content']['meta']['views'] ?? '')), ENT_QUOTES, 'UTF-8');
    $meta_comments = htmlspecialchars(trim((string) ($hero_contract['content']['meta']['comments'] ?? '')), ENT_QUOTES, 'UTF-8');
} else {
    $hero_preset = nb_hero_prop_select((array) $props, 'layout_preset', ['classic', 'split-left', 'split-right', 'edge-left', 'edge-right', 'strip'], 'classic');
    $preset_defaults = nb_hero_preset_defaults($hero_preset);

    $layout = nb_hero_prop_select((array) $props, 'layout', ['centered', 'left', 'split'], $preset_defaults['mode']);
    $container_mode = nb_hero_prop_select((array) $props, 'container_mode', ['contained', 'fluid'], $preset_defaults['containerMode']);
    $media_position_desktop = nb_hero_prop_select((array) $props, 'media_position_desktop', ['start', 'end'], $preset_defaults['mediaPositionDesktop']);
    $media_position_mobile = nb_hero_prop_select((array) $props, 'media_position_mobile', ['top', 'bottom'], $preset_defaults['mediaPositionMobile']);
    $theme = in_array($props['theme'] ?? '', ['light', 'dark', 'accent'], true)
        ? $props['theme'] : 'light';
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

    $eyebrow = htmlspecialchars(trim((string) ($props['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
    $heading = htmlspecialchars(trim((string) ($props['heading'] ?? 'Заголовок')), ENT_QUOTES, 'UTF-8');
    $subhead = htmlspecialchars(trim((string) ($props['subheading'] ?? '')), ENT_QUOTES, 'UTF-8');
    $title_visible = !isset($props['title_visible']) || in_array(strtolower((string) $props['title_visible']), ['1', 'true', 'yes', 'on'], true);
    $subtitle_visible = !isset($props['subtitle_visible']) || in_array(strtolower((string) $props['subtitle_visible']), ['1', 'true', 'yes', 'on'], true);
    $heading_tag = nb_block_get_heading_tag((array) $props, 'heading', 'h1');
    $eyebrow_size_desktop = nb_hero_prop_int((array) $props, 'eyebrow_size_desktop', 14, 10, 120);
    $eyebrow_size_mobile = nb_hero_prop_int((array) $props, 'eyebrow_size_mobile', 13, 10, 120);
    $eyebrow_margin_bottom_desktop = nb_hero_prop_int((array) $props, 'eyebrow_margin_bottom_desktop', 16, 0, 240);
    $eyebrow_margin_bottom_mobile = nb_hero_prop_int((array) $props, 'eyebrow_margin_bottom_mobile', 14, 0, 240);
    $eyebrow_weight_desktop = nb_hero_prop_int((array) $props, 'eyebrow_weight_desktop', nb_hero_prop_int((array) $props, 'eyebrow_weight', 600, 400, 900), 400, 900);
    $eyebrow_weight_mobile = nb_hero_prop_int((array) $props, 'eyebrow_weight_mobile', $eyebrow_weight_desktop, 400, 900);
    $eyebrow_color_desktop = trim((string) ($props['eyebrow_color_desktop'] ?? ($props['eyebrow_color'] ?? '')));
    $eyebrow_color_mobile = trim((string) ($props['eyebrow_color_mobile'] ?? ($props['eyebrow_color'] ?? '')));
    $eyebrow_line_height_desktop = max(0.8, nb_hero_prop_int((array) $props, 'eyebrow_line_height_percent_desktop', nb_hero_prop_int((array) $props, 'eyebrow_line_height_percent', 140, 80, 240), 80, 240) / 100);
    $eyebrow_line_height_mobile = max(0.8, nb_hero_prop_int((array) $props, 'eyebrow_line_height_percent_mobile', (int) round($eyebrow_line_height_desktop * 100), 80, 240) / 100);
    $eyebrow_letter_spacing_desktop = (float) ($props['eyebrow_letter_spacing_desktop'] ?? ($props['eyebrow_letter_spacing'] ?? 1));
    $eyebrow_letter_spacing_mobile = (float) ($props['eyebrow_letter_spacing_mobile'] ?? $eyebrow_letter_spacing_desktop);
    $eyebrow_text_transform = (string) ($props['eyebrow_text_transform'] ?? 'uppercase');
    $title_weight_desktop = nb_hero_prop_int((array) $props, 'title_weight_desktop', nb_block_get_font_weight((array) $props, 'heading', 900), 400, 900);
    $title_weight_mobile = nb_hero_prop_int((array) $props, 'title_weight_mobile', $title_weight_desktop, 400, 900);
    $title_size_desktop = nb_hero_prop_int((array) $props, 'title_size_desktop', 64, 12, 240);
    $title_size_mobile = nb_hero_prop_int((array) $props, 'title_size_mobile', 40, 12, 240);
    $title_color_desktop = trim((string) ($props['title_color_desktop'] ?? ($props['title_color'] ?? '')));
    $title_color_mobile = trim((string) ($props['title_color_mobile'] ?? ($props['title_color'] ?? '')));
    $title_line_height_desktop = max(0.8, nb_hero_prop_int((array) $props, 'title_line_height_percent_desktop', nb_hero_prop_int((array) $props, 'title_line_height_percent', 110, 80, 220), 80, 220) / 100);
    $title_line_height_mobile = max(0.8, nb_hero_prop_int((array) $props, 'title_line_height_percent_mobile', (int) round($title_line_height_desktop * 100), 80, 220) / 100);
    $title_letter_spacing_desktop = (float) ($props['title_letter_spacing_desktop'] ?? ($props['title_letter_spacing'] ?? 0));
    $title_letter_spacing_mobile = (float) ($props['title_letter_spacing_mobile'] ?? $title_letter_spacing_desktop);
    $title_max_width_desktop = nb_hero_prop_int((array) $props, 'title_max_width_desktop', nb_hero_prop_int((array) $props, 'title_max_width', 600, 240, 1440), 240, 1440);
    $title_max_width_mobile = nb_hero_prop_int((array) $props, 'title_max_width_mobile', $title_max_width_desktop, 240, 1440);
    $subtitle_weight_desktop = nb_hero_prop_int((array) $props, 'subtitle_weight_desktop', nb_hero_prop_int((array) $props, 'subtitle_weight', 400, 400, 900), 400, 900);
    $subtitle_weight_mobile = nb_hero_prop_int((array) $props, 'subtitle_weight_mobile', $subtitle_weight_desktop, 400, 900);
    $subtitle_size_desktop = nb_hero_prop_int((array) $props, 'subtitle_size_desktop', 20, 10, 120);
    $subtitle_size_mobile = nb_hero_prop_int((array) $props, 'subtitle_size_mobile', 18, 10, 120);
    $subtitle_color_desktop = trim((string) ($props['subtitle_color_desktop'] ?? ($props['subtitle_color'] ?? '')));
    $subtitle_color_mobile = trim((string) ($props['subtitle_color_mobile'] ?? ($props['subtitle_color'] ?? '')));
    $subtitle_line_height_desktop = max(0.8, nb_hero_prop_int((array) $props, 'subtitle_line_height_percent_desktop', nb_hero_prop_int((array) $props, 'subtitle_line_height_percent', 165, 80, 240), 80, 240) / 100);
    $subtitle_line_height_mobile = max(0.8, nb_hero_prop_int((array) $props, 'subtitle_line_height_percent_mobile', (int) round($subtitle_line_height_desktop * 100), 80, 240) / 100);
    $subtitle_letter_spacing_desktop = (float) ($props['subtitle_letter_spacing_desktop'] ?? ($props['subtitle_letter_spacing'] ?? 0));
    $subtitle_letter_spacing_mobile = (float) ($props['subtitle_letter_spacing_mobile'] ?? $subtitle_letter_spacing_desktop);
    $subtitle_max_width_desktop = nb_hero_prop_int((array) $props, 'subtitle_max_width_desktop', nb_hero_prop_int((array) $props, 'subtitle_max_width', 720, 240, 1440), 240, 1440);
    $subtitle_max_width_mobile = nb_hero_prop_int((array) $props, 'subtitle_max_width_mobile', $subtitle_max_width_desktop, 240, 1440);
    $title_margin_bottom_desktop = nb_hero_prop_int((array) $props, 'title_margin_bottom_desktop', 16, 0, 240);
    $title_margin_bottom_mobile = nb_hero_prop_int((array) $props, 'title_margin_bottom_mobile', 14, 0, 240);
    $subtitle_margin_bottom_desktop = nb_hero_prop_int((array) $props, 'subtitle_margin_bottom_desktop', 24, 0, 240);
    $subtitle_margin_bottom_mobile = nb_hero_prop_int((array) $props, 'subtitle_margin_bottom_mobile', 20, 0, 240);
    $meta_size_desktop = 14;
    $meta_size_mobile = 13;
    $meta_margin_bottom_desktop = 24;
    $meta_margin_bottom_mobile = 20;
    $meta_weight_desktop = nb_hero_prop_int((array) $props, 'meta_weight_desktop', nb_hero_prop_int((array) $props, 'meta_weight', 600, 400, 900), 400, 900);
    $meta_weight_mobile = nb_hero_prop_int((array) $props, 'meta_weight_mobile', $meta_weight_desktop, 400, 900);
    $meta_line_height_desktop = max(0.8, nb_hero_prop_int((array) $props, 'meta_line_height_percent_desktop', nb_hero_prop_int((array) $props, 'meta_line_height_percent', 140, 80, 240), 80, 240) / 100);
    $meta_line_height_mobile = max(0.8, nb_hero_prop_int((array) $props, 'meta_line_height_percent_mobile', (int) round($meta_line_height_desktop * 100), 80, 240) / 100);
    $meta_letter_spacing_desktop = (float) ($props['meta_letter_spacing_desktop'] ?? ($props['meta_letter_spacing'] ?? 0));
    $meta_letter_spacing_mobile = (float) ($props['meta_letter_spacing_mobile'] ?? $meta_letter_spacing_desktop);
    $meta_color_desktop = trim((string) ($props['meta_color_desktop'] ?? ($props['meta_color'] ?? '')));
    $meta_color_mobile = trim((string) ($props['meta_color_mobile'] ?? ($props['meta_color'] ?? '')));
    $buttons_text_size_desktop = nb_hero_prop_int((array) $props, 'button_text_size_desktop', 16, 10, 120);
    $buttons_text_size_mobile = nb_hero_prop_int((array) $props, 'button_text_size_mobile', 15, 10, 120);
    $buttons_text_weight_desktop = nb_hero_prop_int((array) $props, 'button_text_weight_desktop', nb_hero_prop_int((array) $props, 'button_text_weight', 600, 400, 900), 400, 900);
    $buttons_text_weight_mobile = nb_hero_prop_int((array) $props, 'button_text_weight_mobile', $buttons_text_weight_desktop, 400, 900);
    $buttons_text_color_desktop = trim((string) ($props['button_text_color_desktop'] ?? ($props['button_text_color'] ?? '')));
    $buttons_text_color_mobile = trim((string) ($props['button_text_color_mobile'] ?? ($props['button_text_color'] ?? '')));
    $buttons_text_line_height_desktop = max(0.8, nb_hero_prop_int((array) $props, 'button_text_line_height_percent_desktop', nb_hero_prop_int((array) $props, 'button_text_line_height_percent', 120, 80, 220), 80, 220) / 100);
    $buttons_text_line_height_mobile = max(0.8, nb_hero_prop_int((array) $props, 'button_text_line_height_percent_mobile', (int) round($buttons_text_line_height_desktop * 100), 80, 220) / 100);
    $buttons_text_letter_spacing_desktop = (float) ($props['button_text_letter_spacing_desktop'] ?? ($props['button_text_letter_spacing'] ?? 0));
    $buttons_text_letter_spacing_mobile = (float) ($props['button_text_letter_spacing_mobile'] ?? $buttons_text_letter_spacing_desktop);
    $content_width = nb_hero_prop_int((array) $props, 'content_width', 640, 280, 1440);
    $content_gap_desktop = nb_hero_prop_int((array) $props, 'content_gap_desktop', 40, 0, 240);
    $content_gap_mobile = nb_hero_prop_int((array) $props, 'content_gap_mobile', 24, 0, 240);
    $actions_gap_desktop = nb_hero_prop_int((array) $props, 'actions_gap_desktop', 12, 0, 120);
    $actions_gap_mobile = nb_hero_prop_int((array) $props, 'actions_gap_mobile', 10, 0, 120);
    $padding_top_desktop = nb_hero_prop_int((array) $props, 'padding_top_desktop', $preset_defaults['paddingTopDesktop'], 0, 300);
    $padding_bottom_desktop = nb_hero_prop_int((array) $props, 'padding_bottom_desktop', $preset_defaults['paddingBottomDesktop'], 0, 300);
    $padding_top_mobile = nb_hero_prop_int((array) $props, 'padding_top_mobile', $preset_defaults['paddingTopMobile'], 0, 300);
    $padding_bottom_mobile = nb_hero_prop_int((array) $props, 'padding_bottom_mobile', $preset_defaults['paddingBottomMobile'], 0, 300);
    $min_height_desktop = nb_hero_prop_int((array) $props, 'min_height_desktop', 0, 0, 1200);
    $min_height_mobile = nb_hero_prop_int((array) $props, 'min_height_mobile', 0, 0, 1200);
    $reveal = nb_block_get_reveal_settings((array) $props);

    $btn1_label = htmlspecialchars(trim((string) ($props['btn_primary_label'] ?? '')), ENT_QUOTES, 'UTF-8');
    $btn1_url = htmlspecialchars(trim((string) ($props['btn_primary_url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $title_url = $btn1_url !== '#' ? $btn1_url : '';
    $btn1_style = in_array($props['btn_primary_style'] ?? '', ['primary', 'outline', 'ghost'], true)
        ? (string) $props['btn_primary_style'] : 'primary';
    $btn2_label = htmlspecialchars(trim((string) ($props['btn_secondary_label'] ?? '')), ENT_QUOTES, 'UTF-8');
    $btn2_url = htmlspecialchars(trim((string) ($props['btn_secondary_url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $btn2_style = in_array($props['btn_secondary_style'] ?? '', ['primary', 'outline', 'ghost'], true)
        ? (string) $props['btn_secondary_style'] : 'outline';
    $btn3_label = htmlspecialchars(trim((string) ($props['btn_tertiary_label'] ?? '')), ENT_QUOTES, 'UTF-8');
    $btn3_url = htmlspecialchars(trim((string) ($props['btn_tertiary_url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $btn3_style = in_array($props['btn_tertiary_style'] ?? '', ['primary', 'outline', 'ghost'], true)
        ? (string) $props['btn_tertiary_style'] : 'ghost';

    $media_aspect_ratio = in_array($props['media_aspect_ratio'] ?? '16:10', ['auto', '16:10', '16:9', '4:3', '1:1', '3:4'], true)
        ? (string) ($props['media_aspect_ratio'] ?? '16:10') : '16:10';
    $media_object_fit = in_array($props['media_object_fit'] ?? 'cover', ['cover', 'contain'], true)
        ? (string) ($props['media_object_fit'] ?? 'cover') : 'cover';
    $media_radius = nb_hero_prop_int((array) $props, 'media_radius', $preset_defaults['mediaRadius'], 0, 80);
    $media_surface_background_color = trim((string) ($props['media_surface_background_color'] ?? ''));
    $media_surface_background_mode = in_array($props['media_surface_background_mode'] ?? '', ['transparent', 'solid'], true)
        ? (string) ($props['media_surface_background_mode'] ?? 'transparent')
        : ($media_surface_background_color !== '' ? 'solid' : 'transparent');
    $media_surface_padding = nb_hero_prop_int((array) $props, 'media_surface_padding', 0, 0, 80);
    $media_surface_radius = nb_hero_prop_int((array) $props, 'media_surface_radius', $preset_defaults['mediaSurfaceRadius'], 0, 100);
    $media_surface_border_width = nb_hero_prop_int((array) $props, 'media_surface_border_width', 0, 0, 20);
    $media_surface_border_color = trim((string) ($props['media_surface_border_color'] ?? ''));
    $media_surface_shadow = in_array($props['media_surface_shadow'] ?? '', ['none', 'sm', 'md', 'lg'], true)
        ? (string) ($props['media_surface_shadow'] ?? $preset_defaults['mediaSurfaceShadow']) : $preset_defaults['mediaSurfaceShadow'];

    $image_value = $props['image'] ?? '';
    if (is_string($image_value)) {
        $decoded = json_decode($image_value, true);
        if (is_array($decoded)) {
            $image_value = $decoded;
        }
    }
    if (!is_array($image_value)) {
        $image_value = ['display' => (string) $image_value, 'original' => (string) $image_value, 'alt' => ''];
    }

    $image = htmlspecialchars(trim((string) ($image_value['display'] ?? $image_value['original'] ?? '')), ENT_QUOTES, 'UTF-8');
    $image_alt = htmlspecialchars(trim((string) ($image_value['alt'] ?? ($props['image_alt'] ?? ''))), ENT_QUOTES, 'UTF-8');
    $meta_category = '';
    $meta_author = '';
    $meta_date = '';
    $meta_views = '';
    $meta_comments = '';
}

$has_meta_category = $meta_category !== '';
$has_meta_author = $meta_author !== '';
$has_meta_date = $meta_date !== '';
$has_meta_views = $meta_views !== '';
$has_meta_comments = $meta_comments !== '';

$section_class = 'nb-section nb-hero nb-hero--' . $layout;
$section_class .= ' nb-hero--' . $container_mode;
$section_class .= ' nb-hero--media-' . $media_position_desktop;
$section_class .= ' nb-hero--mobile-media-' . $media_position_mobile;
$section_class .= $reveal['class'];
$data_theme = $theme !== 'light' ? ' data-nb-theme="' . $theme . '"' : '';

$section_style = '--nb-hero-content-max-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-hero-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-mobile-padding-top:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-mobile-padding-bottom:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-min-height:' . $min_height_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-mobile-min-height:' . $min_height_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-content-gap:' . $content_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-mobile-content-gap:' . $content_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-actions-gap:' . $actions_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-mobile-actions-gap:' . $actions_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-size:' . $eyebrow_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-size-mobile:' . $eyebrow_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-weight:' . $eyebrow_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-weight-mobile:' . $eyebrow_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-line-height:' . max(0.8, $eyebrow_line_height_desktop) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-line-height-mobile:' . max(0.8, $eyebrow_line_height_mobile) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-letter-spacing:' . $eyebrow_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-letter-spacing-mobile:' . $eyebrow_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-margin-bottom:' . $eyebrow_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-margin-bottom-mobile:' . $eyebrow_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-text-transform:' . ($eyebrow_text_transform === 'none' ? 'none' : 'uppercase') . ';');
if ($eyebrow_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-color:' . $eyebrow_color_desktop . ';');
}
if ($eyebrow_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-eyebrow-color-mobile:' . $eyebrow_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-hero-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-weight:' . $title_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-weight-mobile:' . $title_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-line-height:' . max(0.8, $title_line_height_desktop) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-line-height-mobile:' . max(0.8, $title_line_height_mobile) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-letter-spacing:' . $title_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-letter-spacing-mobile:' . $title_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-max-width:' . $title_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-max-width-mobile:' . $title_max_width_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-weight:' . $subtitle_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-weight-mobile:' . $subtitle_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-line-height:' . max(0.8, $subtitle_line_height_desktop) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-line-height-mobile:' . max(0.8, $subtitle_line_height_mobile) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-letter-spacing:' . $subtitle_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-letter-spacing-mobile:' . $subtitle_letter_spacing_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-max-width:' . $subtitle_max_width_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-max-width-mobile:' . $subtitle_max_width_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-margin-bottom:' . $title_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-margin-bottom-mobile:' . $title_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-margin-bottom:' . $subtitle_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-margin-bottom-mobile:' . $subtitle_margin_bottom_mobile . 'px;');
if ($title_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-title-color:' . $title_color_desktop . ';');
}
if ($title_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-title-color-mobile:' . $title_color_mobile . ';');
}
if ($subtitle_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-color:' . $subtitle_color_desktop . ';');
}
if ($subtitle_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-color-mobile:' . $subtitle_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-size:' . $meta_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-size-mobile:' . $meta_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-margin-bottom:' . $meta_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-margin-bottom-mobile:' . $meta_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-weight:' . $meta_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-weight-mobile:' . $meta_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-line-height:' . max(0.8, $meta_line_height_desktop) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-line-height-mobile:' . max(0.8, $meta_line_height_mobile) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-letter-spacing:' . $meta_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-meta-letter-spacing-mobile:' . $meta_letter_spacing_mobile . 'px;');
if ($meta_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-meta-color:' . $meta_color_desktop . ';');
} elseif ($theme === 'dark' || $theme === 'accent') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-meta-color:rgba(255,255,255,.82);');
}
if ($meta_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-meta-color-mobile:' . $meta_color_mobile . ';');
}
$section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-size:' . $buttons_text_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-size-mobile:' . $buttons_text_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-weight:' . $buttons_text_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-weight-mobile:' . $buttons_text_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-line-height:' . max(0.8, $buttons_text_line_height_desktop) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-line-height-mobile:' . max(0.8, $buttons_text_line_height_mobile) . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-letter-spacing:' . $buttons_text_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-letter-spacing-mobile:' . $buttons_text_letter_spacing_mobile . 'px;');
if ($buttons_text_color_desktop !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-color:' . $buttons_text_color_desktop . ';');
}
if ($buttons_text_color_mobile !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-buttons-text-color-mobile:' . $buttons_text_color_mobile . ';');
}
if ($theme === 'accent' && $background_mode === 'theme') {
    $section_style = nb_block_append_style($section_style, 'background:var(--nb-color-accent);color:#fff;');
}
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style']);

$button_classes = [
    'primary' => 'nb-btn nb-btn--primary',
    'outline' => 'nb-btn nb-btn--outline',
    'ghost'   => 'nb-btn nb-btn--ghost',
];

$media_aspect_ratio_map = [
    '16:10' => '16 / 10',
    '16:9'  => '16 / 9',
    '4:3'   => '4 / 3',
    '1:1'   => '1 / 1',
    '3:4'   => '3 / 4',
];
$media_aspect_ratio_css = $media_aspect_ratio_map[$media_aspect_ratio] ?? 'auto';
$media_shadow_map = [
    'none' => 'none',
    'sm'   => '0 8px 20px rgba(15,23,42,.10)',
    'md'   => '0 18px 36px rgba(15,23,42,.16)',
    'lg'   => '0 28px 64px rgba(15,23,42,.22)',
];
$media_surface_shadow_css = $media_shadow_map[$media_surface_shadow] ?? $media_shadow_map['lg'];
$media_content_height = $media_aspect_ratio === 'auto' ? 'auto' : '100%';

$section_style = nb_block_append_style($section_style, '--nb-hero-media-aspect-ratio:' . $media_aspect_ratio_css . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-media-content-height:' . $media_content_height . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-media-radius:' . $media_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-media-object-fit:' . $media_object_fit . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-media-surface-padding:' . $media_surface_padding . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-media-surface-radius:' . $media_surface_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-media-surface-border-width:' . $media_surface_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-media-surface-shadow:' . $media_surface_shadow_css . ';');
if ($media_surface_background_mode === 'solid' && $media_surface_background_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-media-surface-background:' . $media_surface_background_color . ';');
}
if ($media_surface_border_color !== '') {
    $section_style = nb_block_append_style($section_style, '--nb-hero-media-surface-border-color:' . $media_surface_border_color . ';');
}

$block_dom_id = 'block-' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) $block_uid);
$hero_shell_class = $container_mode === 'fluid'
    ? 'nb-hero__shell nb-hero__shell--fluid'
    : 'nb-container nb-hero__shell nb-hero__shell--contained';
?>
<section
    class="<?= $section_class ?>"
    id="<?= htmlspecialchars($block_dom_id, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-entity="section"
    <?= $data_theme ?><?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="<?= $hero_shell_class ?>">
        <div class="nb-hero__container">

            <?php if ($layout === 'split'): ?>
            <div class="nb-hero__media" data-nb-entity="mediaSurface">
                <div class="nb-hero__media-frame">
                    <?php if ($image): ?>
                    <img
                        src="<?= $image ?>"
                        alt="<?= $image_alt ?>"
                        class="nb-hero__image"
                        loading="lazy"
                        decoding="async"
                        data-nb-entity="media"
                    >
                    <?php else: ?>
                    <div class="nb-hero__media-placeholder" data-nb-entity="media">Добавьте изображение</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="nb-hero__content">
                <div class="nb-hero__content-inner">
                    <?php if ($eyebrow): ?>
                    <p class="nb-hero__eyebrow" data-nb-entity="eyebrow"><?= $eyebrow ?></p>
                    <?php endif; ?>

                    <?php if ($title_visible && $heading): ?>
                    <<?= $heading_tag ?> class="nb-hero__heading" data-nb-entity="title"><?php if ($title_url): ?><a href="<?= $title_url ?>" class="nb-hero__heading-link"><?= $heading ?></a><?php else: ?><?= $heading ?><?php endif; ?></<?= $heading_tag ?>>
                    <?php endif; ?>

                    <?php if ($subtitle_visible && $subhead): ?>
                    <p class="nb-hero__subheading" data-nb-entity="subtitle"><?= $subhead ?></p>
                    <?php endif; ?>

                    <?php if ($has_meta_category || $has_meta_author || $has_meta_date || $has_meta_views || $has_meta_comments): ?>
                    <div class="nb-hero__meta" data-nb-entity="meta">
                        <?php if ($has_meta_category): ?>
                        <span class="nb-hero__meta-item">Категория: <?= $meta_category ?></span>
                        <?php endif; ?>
                        <?php if ($has_meta_author): ?>
                        <span class="nb-hero__meta-item">Автор: <?= $meta_author ?></span>
                        <?php endif; ?>
                        <?php if ($has_meta_date): ?>
                        <span class="nb-hero__meta-item"><?= $meta_date ?></span>
                        <?php endif; ?>
                        <?php if ($has_meta_views): ?>
                        <span class="nb-hero__meta-item"><?= $meta_views ?> просмотров</span>
                        <?php endif; ?>
                        <?php if ($has_meta_comments): ?>
                        <span class="nb-hero__meta-item"><?= $meta_comments ?> комментариев</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($btn1_label || $btn2_label || $btn3_label): ?>
                    <div class="nb-hero__actions">
                        <?php if ($btn1_label): ?>
                        <a href="<?= $btn1_url ?>" class="<?= $button_classes[$btn1_style] ?>" data-nb-entity="primaryButton">
                            <?= $btn1_label ?>
                        </a>
                        <?php endif; ?>

                        <?php if ($btn2_label): ?>
                        <a href="<?= $btn2_url ?>" class="<?= $button_classes[$btn2_style] ?>" data-nb-entity="secondaryButton">
                            <?= $btn2_label ?>
                        </a>
                        <?php endif; ?>

                        <?php if ($btn3_label): ?>
                        <a href="<?= $btn3_url ?>" class="<?= $button_classes[$btn3_style] ?>" data-nb-entity="tertiaryButton">
                            <?= $btn3_label ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>
