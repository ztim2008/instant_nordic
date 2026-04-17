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

if (!function_exists('nb_block_css_color')) {
    function nb_block_css_color($color, $fallback = '') {
        $color = trim((string) $color);

        if ($color === '') {
            return $fallback;
        }

        if (preg_match('/^#([0-9a-f]{3})$/i', $color, $matches)) {
            $hex = $matches[1];
            return '#' . $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (preg_match('/^#([0-9a-f]{6})$/i', $color)) {
            return strtolower($color);
        }

        return $fallback;
    }
}

if (!function_exists('nb_block_render_icon_markup')) {
    function nb_block_render_icon_markup($icon, $fallback = '') {
        $icon = trim((string) $icon);
        if ($icon === '') {
            $icon = trim((string) $fallback);
        }

        if ($icon === '') {
            return '';
        }

        if (strpos($icon, ':') !== false) {
            list($sprite, $name) = array_pad(explode(':', $icon, 2), 2, '');
            $sprite = preg_replace('/[^a-z0-9_\-]/i', '', strtolower($sprite));
            $name   = preg_replace('/[^a-z0-9_\-]/i', '', strtolower($name));

            if ($sprite !== '' && $name !== '' && function_exists('html_svg_icon')) {
                return html_svg_icon($sprite, $name, 20, false);
            }
        }

        $classes = preg_replace('/[^a-z0-9_\-\s]/i', '', $icon);
        $classes = trim(preg_replace('/\s+/', ' ', $classes));
        if ($classes === '') {
            return '';
        }

        if (strpos($classes, 'fa ') !== 0) {
            if (strpos($classes, 'fa-') === 0) {
                $classes = 'fa ' . $classes;
            } elseif (strpos($classes, 'fa-') === false) {
                $classes = 'fa fa-' . $classes;
            }
        }

        return '<i class="' . htmlspecialchars($classes, ENT_QUOTES, 'UTF-8') . '"></i>';
    }
}

if (!function_exists('nb_block_extract_media')) {
    function nb_block_extract_media($value, $fallback_alt = '') {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (!is_array($value)) {
            $string_value = trim((string) $value);

            return [
                'display'  => $string_value,
                'original' => $string_value,
                'alt'      => trim((string) $fallback_alt),
            ];
        }

        return [
            'display'  => trim((string) ($value['display'] ?? $value['original'] ?? '')),
            'original' => trim((string) ($value['original'] ?? '')),
            'alt'      => trim((string) ($value['alt'] ?? $fallback_alt)),
        ];
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
                $position = strtolower(trim((string) ($background['imagePosition'] ?? 'center center')));
                if (!in_array($position, ['center center', 'top center', 'bottom center', 'center left', 'center right', 'top left', 'top right', 'bottom left', 'bottom right'], true)) {
                    $position = 'center center';
                }

                $size = strtolower(trim((string) ($background['imageSize'] ?? 'cover')));
                if (!in_array($size, ['cover', 'contain', 'auto'], true)) {
                    $size = 'cover';
                }

                $repeat = strtolower(trim((string) ($background['imageRepeat'] ?? 'no-repeat')));
                if (!in_array($repeat, ['no-repeat', 'repeat', 'repeat-x', 'repeat-y'], true)) {
                    $repeat = 'no-repeat';
                }

                $overlay_color = nb_block_color_with_opacity(
                    $background['overlayColor'] ?? '#0f172a',
                    ((int) ($background['overlayOpacity'] ?? 45)) / 100,
                    'rgba(15,23,42,0.45)'
                );
                $style = nb_block_append_style($style, 'background-image:linear-gradient(' . $overlay_color . ', ' . $overlay_color . '), ' . nb_block_css_url($image) . ';');
                $style = nb_block_append_style($style, 'background-size:' . $size . ';');
                $style = nb_block_append_style($style, 'background-position:' . $position . ';');
                $style = nb_block_append_style($style, 'background-repeat:' . $repeat . ';');
            }
        }

        return $style;
    }
}