<?php

if (!function_exists('nb_block_get_heading_tag')) {
    function nb_block_get_heading_tag(array $props, $base_key = 'heading', $default = 'h2') {
        $tag = strtolower(trim((string) ($props[$base_key . '_tag'] ?? $default)));
        return in_array($tag, ['div', 'h1', 'h2', 'h3'], true) ? $tag : $default;
    }
}

if (!function_exists('nb_block_get_font_weight')) {
    function nb_block_get_font_weight(array $props, $base_key = 'heading', $default = 800) {
        $weight = (string) ($props[$base_key . '_weight'] ?? $default);
        return in_array($weight, ['400', '500', '600', '700', '800', '900'], true) ? $weight : (string) $default;
    }
}

if (!function_exists('nb_block_get_reveal_settings')) {
    function nb_block_get_reveal_settings(array $props) {
        $animation = strtolower(trim((string) ($props['block_animation'] ?? 'none')));
        if (!in_array($animation, ['none', 'fade-up', 'fade-in', 'zoom-in'], true)) {
            $animation = 'none';
        }

        $delay = is_numeric($props['block_animation_delay'] ?? null) ? (int) $props['block_animation_delay'] : 0;
        $delay = max(0, min(1500, $delay));

        if ($animation === 'none') {
            return ['class' => '', 'style' => ''];
        }

        return [
            'class' => ' nb-anim nb-anim--' . $animation,
            'style' => '--nb-anim-delay:' . $delay . 'ms;',
        ];
    }
}

if (!function_exists('nb_block_append_style')) {
    function nb_block_append_style($style, $append) {
        $style  = trim((string) $style);
        $append = trim((string) $append);

        if ($append === '') {
            return $style;
        }

        if ($style === '') {
            return $append;
        }

        return rtrim($style, ';') . ';' . ltrim($append, ';');
    }
}

if (!function_exists('nb_block_css_url')) {
    function nb_block_css_url($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        return 'url("' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '")';
    }
}

if (!function_exists('nb_block_color_with_opacity')) {
    function nb_block_color_with_opacity($color, $opacity, $fallback = 'rgba(15,23,42,0.45)') {
        $color = trim((string) $color);
        $opacity = is_numeric($opacity) ? (float) $opacity : 0.45;
        $opacity = max(0, min(1, $opacity));

        if (preg_match('/^#([0-9a-f]{3})$/i', $color, $matches)) {
            $hex = $matches[1];
            $color = '#' . $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (!preg_match('/^#([0-9a-f]{6})$/i', $color, $matches)) {
            return $fallback;
        }

        $hex = $matches[1];
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return sprintf('rgba(%d,%d,%d,%.3F)', $red, $green, $blue, $opacity);
    }
}

if (!function_exists('nb_block_build_background_style')) {
    function nb_block_build_background_style(array $background) {
        $mode = strtolower(trim((string) ($background['mode'] ?? $background['type'] ?? 'theme')));
        if (!in_array($mode, ['theme', 'color', 'gradient', 'image'], true)) {
            $mode = 'theme';
        }

        $style = '';

        if ($mode === 'color') {
            $color = trim((string) ($background['color'] ?? ''));
            if ($color !== '') {
                $style = nb_block_append_style($style, 'background:' . $color . ';');
            }
        }

        if ($mode === 'gradient') {
            $from = trim((string) ($background['gradientFrom'] ?? ''));
            $to = trim((string) ($background['gradientTo'] ?? ''));
            $angle = is_numeric($background['gradientAngle'] ?? null) ? (int) $background['gradientAngle'] : 135;
            $angle = max(0, min(360, $angle));

            if ($from !== '' && $to !== '') {
                $style = nb_block_append_style($style, 'background-image:linear-gradient(' . $angle . 'deg, ' . $from . ' 0%, ' . $to . ' 100%);');
            }
        }

        if ($mode === 'image') {
            $image = trim((string) ($background['image'] ?? ''));
            if ($image !== '') {
                $overlay_color = nb_block_color_with_opacity(
                    $background['overlayColor'] ?? '#0f172a',
                    ((int) ($background['overlayOpacity'] ?? 45)) / 100,
                    'rgba(15,23,42,0.45)'
                );
                $style = nb_block_append_style($style, 'background-image:linear-gradient(' . $overlay_color . ', ' . $overlay_color . '), ' . nb_block_css_url($image) . ';');
                $style = nb_block_append_style($style, 'background-size:cover;');
                $style = nb_block_append_style($style, 'background-position:center;');
                $style = nb_block_append_style($style, 'background-repeat:no-repeat;');
            }
        }

        return $style;
    }
}