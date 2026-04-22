<?php

require_once dirname(__DIR__) . '/render_helpers.php';

if (!function_exists('nb_hero_magazine_prop_int')) {
    function nb_hero_magazine_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_hero_magazine_items')) {
    function nb_hero_magazine_items($value) {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (!is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));
            $link = trim((string) ($item['link'] ?? ''));
            if ($title === '' && $text === '') {
                continue;
            }

            $items[] = [
                'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
                'text'  => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
                'link'  => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            ];
        }

        return $items;
    }
}

$background_media = nb_block_extract_media($props['background_image'] ?? '');
$cover_media = nb_block_extract_media($props['cover_image'] ?? '', $props['cover_image_alt'] ?? '');

$background_mode = strtolower(trim((string) ($props['background_mode'] ?? 'color')));
if (!in_array($background_mode, ['color', 'gradient', 'image'], true)) {
    $background_mode = 'color';
}

$background_style = nb_block_build_background_style([
    'mode' => $background_mode,
    'color' => $props['background_color'] ?? '#fbfaf6',
    'gradientFrom' => $props['background_gradient_from'] ?? '#fbfaf6',
    'gradientTo' => $props['background_gradient_to'] ?? '#f0ebe2',
    'gradientAngle' => $props['background_gradient_angle'] ?? 135,
    'image' => $background_media['display'],
    'imagePosition' => $props['background_image_position'] ?? 'center center',
    'imageSize' => 'cover',
    'imageRepeat' => 'no-repeat',
    'overlayColor' => $props['background_overlay_color'] ?? '#ffffff',
    'overlayOpacity' => $props['background_overlay_opacity'] ?? 22,
]);

$kicker = htmlspecialchars(trim((string) ($props['kicker'] ?? '')), ENT_QUOTES, 'UTF-8');
$title = trim((string) ($props['title'] ?? ''));
$title_html = $title !== '' ? nl2br(htmlspecialchars($title, ENT_QUOTES, 'UTF-8')) : 'Hero Magazine';
$meta_line = htmlspecialchars(trim((string) ($props['meta_line'] ?? '')), ENT_QUOTES, 'UTF-8');
$subtitle = trim((string) ($props['subtitle'] ?? ''));
$subtitle_html = $subtitle !== '' ? nl2br(htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8')) : '';
$cover_label = htmlspecialchars(trim((string) ($props['cover_label'] ?? '')), ENT_QUOTES, 'UTF-8');
$cover_card_title_value = trim((string) ($props['cover_card_title'] ?? ''));
$cover_card_title = htmlspecialchars($cover_card_title_value !== '' ? $cover_card_title_value : $title, ENT_QUOTES, 'UTF-8');

$title_tag = nb_block_get_heading_tag((array) $props, 'title', 'h1');
$title_weight = nb_block_get_font_weight((array) $props, 'title', 900);
$reveal = nb_block_get_reveal_settings((array) $props);

$button_text = htmlspecialchars(trim((string) ($props['button_text'] ?? '')), ENT_QUOTES, 'UTF-8');
$button_link = htmlspecialchars(trim((string) ($props['button_link'] ?? '#')), ENT_QUOTES, 'UTF-8');
$button_target = strtolower(trim((string) ($props['button_target'] ?? '_self')));
if (!in_array($button_target, ['_self', '_blank'], true)) {
    $button_target = '_self';
}
$button_rel = $button_target === '_blank' ? 'noopener noreferrer' : '';
$show_button = in_array(strtolower((string) ($props['show_button'] ?? '1')), ['1', 'true', 'yes', 'on'], true) && $button_text !== '';

$show_highlights = in_array(strtolower((string) ($props['show_highlights'] ?? '1')), ['1', 'true', 'yes', 'on'], true);
$highlights = nb_hero_magazine_items($props['highlights'] ?? []);

$content_width = nb_hero_magazine_prop_int((array) $props, 'content_width', 1380, 960, 1680);
$padding_top_desktop = nb_hero_magazine_prop_int((array) $props, 'padding_top_desktop', 88, 0, 220);
$padding_bottom_desktop = nb_hero_magazine_prop_int((array) $props, 'padding_bottom_desktop', 64, 0, 220);
$padding_top_mobile = nb_hero_magazine_prop_int((array) $props, 'padding_top_mobile', 32, 0, 160);
$padding_bottom_mobile = nb_hero_magazine_prop_int((array) $props, 'padding_bottom_mobile', 28, 0, 160);
$title_size_desktop = nb_hero_magazine_prop_int((array) $props, 'title_size_desktop', 78, 28, 180);
$title_size_mobile = nb_hero_magazine_prop_int((array) $props, 'title_size_mobile', 42, 18, 120);
$subtitle_size_desktop = nb_hero_magazine_prop_int((array) $props, 'subtitle_size_desktop', 21, 12, 60);
$subtitle_size_mobile = nb_hero_magazine_prop_int((array) $props, 'subtitle_size_mobile', 18, 12, 48);

$section_style = '--nb-hero-magazine-content-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-hero-magazine-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-magazine-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-magazine-padding-top-mobile:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-magazine-padding-bottom-mobile:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-magazine-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-magazine-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-magazine-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-magazine-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style']);
?>
<section
    class="nb-section nb-hero-magazine<?= $reveal['class'] ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container">
        <div class="nb-hero-magazine__grid">
            <div class="nb-hero-magazine__content">
                <?php if ($kicker): ?>
                <div class="nb-hero-magazine__kicker"><?= $kicker ?></div>
                <?php endif; ?>

                <<?= $title_tag ?> class="nb-hero-magazine__title" style="font-weight:<?= (int) $title_weight ?>"><?= $title_html ?></<?= $title_tag ?>>

                <?php if ($meta_line): ?>
                <div class="nb-hero-magazine__meta"><?= $meta_line ?></div>
                <?php endif; ?>

                <?php if ($subtitle_html): ?>
                <div class="nb-hero-magazine__subtitle"><?= $subtitle_html ?></div>
                <?php endif; ?>

                <?php if ($show_button): ?>
                <div class="nb-hero-magazine__actions">
                    <a
                        href="<?= $button_link ?>"
                        class="nb-btn nb-hero-magazine__button"
                        target="<?= htmlspecialchars($button_target, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $button_rel ? 'rel="' . htmlspecialchars($button_rel, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                    >
                        <?= $button_text ?>
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($show_highlights && $highlights): ?>
                <div class="nb-hero-magazine__highlights">
                    <?php foreach ($highlights as $index => $item): ?>
                    <?php $tag = $item['link'] !== '' ? 'a' : 'div'; ?>
                    <<?= $tag ?>
                        class="nb-card nb-hero-magazine__highlight"
                        <?= $item['link'] !== '' ? 'href="' . $item['link'] . '"' : '' ?>
                    >
                        <div class="nb-hero-magazine__highlight-index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></div>
                        <div class="nb-hero-magazine__highlight-title"><?= $item['title'] ?></div>
                        <?php if ($item['text']): ?>
                        <div class="nb-hero-magazine__highlight-text"><?= $item['text'] ?></div>
                        <?php endif; ?>
                    </<?= $tag ?>>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="nb-hero-magazine__media-wrap">
                <div class="nb-hero-magazine__media-glow" aria-hidden="true"></div>
                <div class="nb-card nb-hero-magazine__cover">
                    <div class="nb-hero-magazine__cover-media">
                        <?php if ($cover_media['display']): ?>
                        <img
                            src="<?= htmlspecialchars($cover_media['display'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($cover_media['alt'], ENT_QUOTES, 'UTF-8') ?>"
                            class="nb-hero-magazine__cover-image"
                            loading="lazy"
                            decoding="async"
                        >
                        <?php else: ?>
                        <div class="nb-hero-magazine__cover-placeholder">Добавьте обложку</div>
                        <?php endif; ?>
                    </div>
                    <div class="nb-hero-magazine__cover-footer">
                        <?php if ($cover_label): ?>
                        <div class="nb-hero-magazine__cover-label"><?= $cover_label ?></div>
                        <?php endif; ?>
                        <div class="nb-hero-magazine__cover-title"><?= $cover_card_title ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>