<?php

require_once dirname(__DIR__) . '/render_helpers.php';

$escape = function ($value) {
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
};

$clamp_int = function ($value, $min, $max, $default) {
    $value = is_numeric($value) ? (int) $value : (int) $default;
    return max($min, min($max, $value));
};

$sanitize_color = function ($value, $default) {
    $value = trim((string) $value);
    if (preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $value)) {
        return $value;
    }
    return $default;
};

$hex_to_rgba = function ($hex, $opacity_percent) {
    $hex = ltrim((string) $hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        $hex = '000000';
    }
    $opacity = max(0, min(100, (int) $opacity_percent)) / 100;
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return sprintf('rgba(%d, %d, %d, %.3F)', $r, $g, $b, $opacity);
};

$extract_media = function ($value) {
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $value = $decoded;
        }
    }

    if (!is_array($value)) {
        $value = ['display' => (string) $value, 'original' => (string) $value, 'alt' => ''];
    }

    return [
        'display'  => trim((string) ($value['display'] ?? $value['original'] ?? '')),
        'original' => trim((string) ($value['original'] ?? '')),
        'alt'      => trim((string) ($value['alt'] ?? '')),
    ];
};

$title = $escape($props['title'] ?? '');
if ($title === '') {
    $title = 'Hero Classic';
}

$subtitle = $escape($props['subtitle'] ?? '');
$badge    = $escape($props['badge'] ?? '');
$title_tag = nb_block_get_heading_tag((array) $props, 'title', 'h1');
$title_weight = nb_block_get_font_weight((array) $props, 'title', 900);
$reveal = nb_block_get_reveal_settings((array) $props);

$show_divider = in_array(strtolower((string) ($props['show_divider'] ?? '1')), ['1', 'true', 'yes', 'on'], true);
$show_button  = in_array(strtolower((string) ($props['show_button'] ?? '1')), ['1', 'true', 'yes', 'on'], true);

$button_text   = $escape($props['button_text'] ?? '');
$button_link   = $escape($props['button_link'] ?? '#');
$button_style  = strtolower(trim((string) ($props['button_style'] ?? 'primary')));
$button_style  = in_array($button_style, ['primary', 'outline'], true) ? $button_style : 'primary';
$button_target = strtolower(trim((string) ($props['button_target'] ?? '_self')));
$allowed_targets = ['_self', '_blank', '_parent', '_top'];
$button_target = in_array($button_target, $allowed_targets, true) ? $button_target : '_self';
$button_rel    = $button_target === '_blank' ? 'noopener noreferrer' : '';

$text_align = strtolower(trim((string) ($props['text_align'] ?? 'center')));
if (!in_array($text_align, ['left', 'center', 'right'], true)) {
    $text_align = 'center';
}

$background_mode     = strtolower(trim((string) ($props['background_mode'] ?? 'solid')));
$background_mode     = in_array($background_mode, ['solid', 'gradient', 'image'], true) ? $background_mode : 'solid';
$bg_color            = $sanitize_color($props['bg_color'] ?? '#f4efe8', '#f4efe8');
$bg_gradient_start   = $sanitize_color($props['bg_gradient_start'] ?? '#111827', '#111827');
$bg_gradient_end     = $sanitize_color($props['bg_gradient_end'] ?? '#334155', '#334155');
$bg_gradient_angle   = $clamp_int($props['bg_gradient_angle'] ?? 135, 0, 360, 135);
$bg_image_media      = $extract_media($props['bg_image'] ?? '');
$bg_image            = $bg_image_media['display'];
$bg_image_position   = trim((string) ($props['bg_image_position'] ?? 'center center'));
$allowed_positions   = ['center center', 'center top', 'center bottom', 'left center', 'right center'];
$bg_image_position   = in_array($bg_image_position, $allowed_positions, true) ? $bg_image_position : 'center center';
$bg_overlay_color    = $sanitize_color($props['bg_overlay_color'] ?? '#0f172a', '#0f172a');
$bg_overlay_opacity  = $clamp_int($props['bg_overlay_opacity'] ?? 35, 0, 90, 35);

$content_max_width   = $clamp_int($props['content_max_width'] ?? 980, 480, 1440, 980);
$min_height_desktop  = $clamp_int($props['min_height_desktop'] ?? 0, 0, 1400, 0);
$min_height_mobile   = $clamp_int($props['min_height_mobile'] ?? 0, 0, 1000, 0);
$title_desktop_px    = $clamp_int($props['title_desktop_px'] ?? 96, 24, 200, 96);
$title_mobile_px     = $clamp_int($props['title_mobile_px'] ?? 52, 18, 140, 52);
$subtitle_desktop_px = $clamp_int($props['subtitle_desktop_px'] ?? 24, 10, 64, 24);
$subtitle_mobile_px  = $clamp_int($props['subtitle_mobile_px'] ?? 18, 10, 48, 18);
$padding_top_desktop = $clamp_int($props['padding_top_desktop'] ?? 96, 0, 240, 96);
$padding_bottom_desktop = $clamp_int($props['padding_bottom_desktop'] ?? 96, 0, 240, 96);
$padding_top_mobile  = $clamp_int($props['padding_top_mobile'] ?? 56, 0, 200, 56);
$padding_bottom_mobile = $clamp_int($props['padding_bottom_mobile'] ?? 56, 0, 200, 56);

$badge_color         = $sanitize_color($props['badge_color'] ?? '#b42318', '#b42318');
$title_color         = $sanitize_color($props['title_color'] ?? '#111827', '#111827');
$subtitle_color      = $sanitize_color($props['subtitle_color'] ?? '#475569', '#475569');
$button_text_color   = $sanitize_color($props['button_text_color'] ?? '#ffffff', '#ffffff');
$button_bg_color     = $sanitize_color($props['button_bg_color'] ?? '#111827', '#111827');
$button_border_color = $sanitize_color($props['button_border_color'] ?? '#111827', '#111827');
$button_radius       = $clamp_int($props['button_radius'] ?? 999, 0, 999, 999);

$background_style = 'background:' . $bg_color . ';';
if ($background_mode === 'gradient') {
    $background_style = 'background: linear-gradient(' . $bg_gradient_angle . 'deg, ' . $bg_gradient_start . ', ' . $bg_gradient_end . ');';
} elseif ($background_mode === 'image' && preg_match('#^(\/|https?:\/\/)#i', $bg_image)) {
    $overlay = $hex_to_rgba($bg_overlay_color, $bg_overlay_opacity);
    $background_style = 'background-image: linear-gradient(' . $overlay . ', ' . $overlay . '), url(' . htmlspecialchars($bg_image, ENT_QUOTES, 'UTF-8') . ');'
        . 'background-size: cover;background-position:' . $bg_image_position . ';background-repeat:no-repeat;';
}

$section_class = 'nb-section nb-hero-classic nb-hero-classic--' . $text_align;
$section_class .= $reveal['class'];
$section_style = $background_style . sprintf(
    '--nb-hero-classic-align:%s;--nb-hero-classic-content-width:%dpx;--nb-hero-classic-min-height-desktop:%dpx;--nb-hero-classic-min-height-mobile:%dpx;--nb-hero-classic-title-desktop:%dpx;--nb-hero-classic-title-mobile:%dpx;--nb-hero-classic-subtitle-desktop:%dpx;--nb-hero-classic-subtitle-mobile:%dpx;--nb-hero-classic-badge-color:%s;--nb-hero-classic-title-color:%s;--nb-hero-classic-subtitle-color:%s;--nb-hero-classic-button-text:%s;--nb-hero-classic-button-bg:%s;--nb-hero-classic-button-border:%s;--nb-hero-classic-button-radius:%dpx;--nb-hero-classic-padding-top-desktop:%dpx;--nb-hero-classic-padding-bottom-desktop:%dpx;--nb-hero-classic-padding-top-mobile:%dpx;--nb-hero-classic-padding-bottom-mobile:%dpx;',
    $text_align,
    $content_max_width,
    $min_height_desktop,
    $min_height_mobile,
    $title_desktop_px,
    $title_mobile_px,
    $subtitle_desktop_px,
    $subtitle_mobile_px,
    $badge_color,
    $title_color,
    $subtitle_color,
    $button_text_color,
    $button_bg_color,
    $button_border_color,
    $button_radius,
    $padding_top_desktop,
    $padding_bottom_desktop,
    $padding_top_mobile,
    $padding_bottom_mobile
);
$section_style = nb_block_append_style($section_style, $reveal['style']);
?>
<section
    class="<?= htmlspecialchars($section_class, ENT_QUOTES, 'UTF-8') ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    style="<?= htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') ?>"
>
    <div class="nb-container">
        <div class="nb-hero-classic__inner">
            <?php if ($badge): ?>
            <div class="nb-hero-classic__badge"><?= $badge ?></div>
            <?php endif; ?>

            <<?= $title_tag ?> class="nb-hero-classic__title" style="font-weight:<?= (int) $title_weight ?>"><?= $title ?></<?= $title_tag ?>>

            <?php if ($show_divider): ?>
            <div class="nb-hero-classic__divider" aria-hidden="true"></div>
            <?php endif; ?>

            <?php if ($subtitle): ?>
            <p class="nb-hero-classic__subtitle"><?= nl2br($subtitle) ?></p>
            <?php endif; ?>

            <?php if ($show_button && $button_text): ?>
            <div class="nb-hero-classic__actions">
                <a
                    href="<?= $button_link ?>"
                    class="nb-btn nb-btn--<?= htmlspecialchars($button_style, ENT_QUOTES, 'UTF-8') ?>"
                    target="<?= htmlspecialchars($button_target, ENT_QUOTES, 'UTF-8') ?>"
                    <?= $button_rel ? 'rel="' . htmlspecialchars($button_rel, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                >
                    <?= $button_text ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>