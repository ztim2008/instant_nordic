<?php

require_once dirname(__DIR__) . '/render_helpers.php';

if (!function_exists('nb_hero_figma_prop_int')) {
    function nb_hero_figma_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_hero_figma_paragraphs')) {
    function nb_hero_figma_paragraphs($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $chunks = preg_split('/\r?\n\r?\n/', $value);
        $html = '';
        foreach ($chunks as $chunk) {
            $chunk = trim((string) $chunk);
            if ($chunk === '') {
                continue;
            }

            $html .= '<p>' . nl2br(htmlspecialchars($chunk, ENT_QUOTES, 'UTF-8')) . '</p>';
        }

        return $html;
    }
}

$background_media = nb_block_extract_media($props['background_image'] ?? '');
$background_mode = strtolower(trim((string) ($props['background_mode'] ?? 'image')));
if (!in_array($background_mode, ['image', 'color', 'gradient'], true)) {
    $background_mode = 'image';
}

$background_style = nb_block_build_background_style([
    'mode' => $background_mode,
    'color' => $props['background_color'] ?? '#f7f3ec',
    'gradientFrom' => $props['background_gradient_from'] ?? '#f8f4ed',
    'gradientTo' => $props['background_gradient_to'] ?? '#efe8dd',
    'gradientAngle' => $props['background_gradient_angle'] ?? 135,
    'image' => $background_media['display'],
    'imagePosition' => $props['background_image_position'] ?? 'center center',
    'imageSize' => 'cover',
    'imageRepeat' => 'no-repeat',
    'overlayColor' => $props['background_overlay_color'] ?? '#ffffff',
    'overlayOpacity' => $props['background_overlay_opacity'] ?? 18,
]);

$variant = strtolower(trim((string) ($props['variant'] ?? 'hero10')));
if (!in_array($variant, ['hero10', 'hero11'], true)) {
    $variant = 'hero10';
}

$kicker = htmlspecialchars(trim((string) ($props['kicker'] ?? '')), ENT_QUOTES, 'UTF-8');
$subtitle = htmlspecialchars(trim((string) ($props['subtitle'] ?? '')), ENT_QUOTES, 'UTF-8');
$title = trim((string) ($props['title'] ?? ''));
$title_html = $title !== '' ? nl2br(htmlspecialchars($title, ENT_QUOTES, 'UTF-8')) : 'Hero';
$body_html = nb_hero_figma_paragraphs($props['body'] ?? '');

$title_tag = nb_block_get_heading_tag((array) $props, 'title', 'h1');
$title_weight = nb_block_get_font_weight((array) $props, 'title', 900);
$reveal = nb_block_get_reveal_settings((array) $props);

$content_width = nb_hero_figma_prop_int((array) $props, 'content_width', 1280, 960, 1680);
$min_height_desktop = nb_hero_figma_prop_int((array) $props, 'min_height_desktop', 720, 0, 1400);
$min_height_mobile = nb_hero_figma_prop_int((array) $props, 'min_height_mobile', 0, 0, 1000);
$padding_top_desktop = nb_hero_figma_prop_int((array) $props, 'padding_top_desktop', 84, 0, 220);
$padding_bottom_desktop = nb_hero_figma_prop_int((array) $props, 'padding_bottom_desktop', 84, 0, 220);
$padding_top_mobile = nb_hero_figma_prop_int((array) $props, 'padding_top_mobile', 28, 0, 160);
$padding_bottom_mobile = nb_hero_figma_prop_int((array) $props, 'padding_bottom_mobile', 28, 0, 160);

$show_primary = in_array(strtolower((string) ($props['show_primary'] ?? '1')), ['1', 'true', 'yes', 'on'], true);
$primary_text = htmlspecialchars(trim((string) ($props['primary_text'] ?? '')), ENT_QUOTES, 'UTF-8');
$primary_url = htmlspecialchars(trim((string) ($props['primary_url'] ?? '#')), ENT_QUOTES, 'UTF-8');
$primary_target = strtolower(trim((string) ($props['primary_target'] ?? '_self')));
if (!in_array($primary_target, ['_self', '_blank'], true)) {
    $primary_target = '_self';
}
$primary_rel = $primary_target === '_blank' ? 'noopener noreferrer' : '';

$show_secondary = in_array(strtolower((string) ($props['show_secondary'] ?? '1')), ['1', 'true', 'yes', 'on'], true);
$secondary_text = htmlspecialchars(trim((string) ($props['secondary_text'] ?? '')), ENT_QUOTES, 'UTF-8');
$secondary_url = htmlspecialchars(trim((string) ($props['secondary_url'] ?? '#')), ENT_QUOTES, 'UTF-8');
$secondary_target = strtolower(trim((string) ($props['secondary_target'] ?? '_self')));
if (!in_array($secondary_target, ['_self', '_blank'], true)) {
    $secondary_target = '_self';
}
$secondary_rel = $secondary_target === '_blank' ? 'noopener noreferrer' : '';

$section_style = '--nb-hero-figma-content-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-hero-figma-min-height:' . $min_height_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-figma-min-height-mobile:' . $min_height_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-figma-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-figma-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-figma-padding-top-mobile:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-figma-padding-bottom-mobile:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style']);
?>
<section
    class="nb-section nb-hero-figma-light nb-hero-figma-light--<?= htmlspecialchars($variant, ENT_QUOTES, 'UTF-8') ?><?= $reveal['class'] ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container">
        <div class="nb-hero-figma-light__frame">
            <div class="nb-surface nb-hero-figma-light__lead">
                <?php if ($kicker): ?>
                <div class="nb-hero-figma-light__kicker"><?= $kicker ?></div>
                <?php endif; ?>

                <?php if ($subtitle): ?>
                <div class="nb-hero-figma-light__subtitle"><?= $subtitle ?></div>
                <?php endif; ?>

                <<?= $title_tag ?> class="nb-hero-figma-light__title" style="font-weight:<?= (int) $title_weight ?>"><?= $title_html ?></<?= $title_tag ?>>
            </div>

            <div class="nb-surface nb-hero-figma-light__aside">
                <?php if ($body_html): ?>
                <div class="nb-hero-figma-light__body"><?= $body_html ?></div>
                <?php endif; ?>

                <?php if (($show_primary && $primary_text) || ($show_secondary && $secondary_text)): ?>
                <div class="nb-hero-figma-light__actions">
                    <?php if ($show_primary && $primary_text): ?>
                    <a
                        href="<?= $primary_url ?>"
                        class="nb-btn nb-hero-figma-light__button nb-hero-figma-light__button--primary"
                        target="<?= htmlspecialchars($primary_target, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $primary_rel ? 'rel="' . htmlspecialchars($primary_rel, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                    >
                        <?= $primary_text ?>
                    </a>
                    <?php endif; ?>

                    <?php if ($show_secondary && $secondary_text): ?>
                    <a
                        href="<?= $secondary_url ?>"
                        class="nb-btn nb-hero-figma-light__button nb-hero-figma-light__button--secondary"
                        target="<?= htmlspecialchars($secondary_target, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $secondary_rel ? 'rel="' . htmlspecialchars($secondary_rel, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                    >
                        <?= $secondary_text ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>