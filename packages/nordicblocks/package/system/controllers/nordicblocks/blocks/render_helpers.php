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