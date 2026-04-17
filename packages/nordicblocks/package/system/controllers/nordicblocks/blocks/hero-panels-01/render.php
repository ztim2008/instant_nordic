<?php

require_once dirname(__DIR__) . '/render_helpers.php';

if (!function_exists('nb_hero_panels_prop_int')) {
    function nb_hero_panels_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_hero_panels_paragraphs')) {
    function nb_hero_panels_paragraphs($value) {
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

$image_media = nb_block_extract_media($props['image'] ?? '', $props['image_alt'] ?? '');
$image = $image_media['display'];
$image_alt = htmlspecialchars($image_media['alt'], ENT_QUOTES, 'UTF-8');

$kicker = htmlspecialchars(trim((string) ($props['kicker'] ?? '')), ENT_QUOTES, 'UTF-8');
$title = trim((string) ($props['title'] ?? ''));
$title_html = $title !== '' ? nl2br(htmlspecialchars($title, ENT_QUOTES, 'UTF-8')) : 'Hero Panels';
$subtitle = htmlspecialchars(trim((string) ($props['subtitle'] ?? '')), ENT_QUOTES, 'UTF-8');
$body_html = nb_hero_panels_paragraphs($props['body'] ?? '');

$title_tag = nb_block_get_heading_tag((array) $props, 'title', 'h1');
$title_weight = nb_block_get_font_weight((array) $props, 'title', 900);
$reveal = nb_block_get_reveal_settings((array) $props);

$media_position = strtolower(trim((string) ($props['media_position'] ?? 'left')));
if (!in_array($media_position, ['left', 'center', 'right'], true)) {
    $media_position = 'left';
}

$columns_preset = trim((string) ($props['columns_preset'] ?? '44-30-26'));
$width_map = [
    '50-25-25' => ['media' => 50, 'red' => 25, 'black' => 25],
    '40-30-30' => ['media' => 40, 'red' => 30, 'black' => 30],
    '44-30-26' => ['media' => 44, 'red' => 30, 'black' => 26],
];
$widths = $width_map[$columns_preset] ?? $width_map['44-30-26'];

$desktop_orders = [
    'left' => ['media' => 1, 'red' => 2, 'black' => 3],
    'center' => ['media' => 2, 'red' => 1, 'black' => 3],
    'right' => ['media' => 3, 'red' => 1, 'black' => 2],
];
$desktop_order = $desktop_orders[$media_position];

$mobile_order_mode = strtolower(trim((string) ($props['mobile_order_mode'] ?? 'image-first')));
if (!in_array($mobile_order_mode, ['image-first', 'follow-desktop'], true)) {
    $mobile_order_mode = 'image-first';
}

$mobile_order = $mobile_order_mode === 'follow-desktop'
    ? $desktop_order
    : ['media' => 1, 'red' => 2, 'black' => 3];

$button_text = htmlspecialchars(trim((string) ($props['button_text'] ?? '')), ENT_QUOTES, 'UTF-8');
$button_link = htmlspecialchars(trim((string) ($props['button_link'] ?? '#')), ENT_QUOTES, 'UTF-8');
$button_target = strtolower(trim((string) ($props['button_target'] ?? '_self')));
if (!in_array($button_target, ['_self', '_blank'], true)) {
    $button_target = '_self';
}
$button_rel = $button_target === '_blank' ? 'noopener noreferrer' : '';
$button_style = trim((string) ($props['button_style'] ?? 'outline-white'));
$button_class = $button_style === 'white' ? 'nb-btn nb-btn--white' : 'nb-btn nb-btn--outline-white';
$show_button = in_array(strtolower((string) ($props['show_button'] ?? '0')), ['1', 'true', 'yes', 'on'], true) && $button_text !== '';

$section_background = nb_block_css_color($props['section_background'] ?? '#f6f4ef', '#f6f4ef');
$red_panel_background = nb_block_css_color($props['red_panel_background'] ?? '#e12525', '#e12525');
$black_panel_background = nb_block_css_color($props['black_panel_background'] ?? '#1d1d1f', '#1d1d1f');

$content_width = nb_hero_panels_prop_int((array) $props, 'content_width', 1320, 960, 1680);
$min_height_desktop = nb_hero_panels_prop_int((array) $props, 'min_height_desktop', 720, 0, 1400);
$min_height_mobile = nb_hero_panels_prop_int((array) $props, 'min_height_mobile', 0, 0, 1000);
$padding_top_desktop = nb_hero_panels_prop_int((array) $props, 'padding_top_desktop', 28, 0, 200);
$padding_bottom_desktop = nb_hero_panels_prop_int((array) $props, 'padding_bottom_desktop', 28, 0, 200);
$padding_top_mobile = nb_hero_panels_prop_int((array) $props, 'padding_top_mobile', 16, 0, 160);
$padding_bottom_mobile = nb_hero_panels_prop_int((array) $props, 'padding_bottom_mobile', 16, 0, 160);
$title_size_desktop = nb_hero_panels_prop_int((array) $props, 'title_size_desktop', 84, 28, 180);
$title_size_mobile = nb_hero_panels_prop_int((array) $props, 'title_size_mobile', 42, 18, 120);
$body_size_desktop = nb_hero_panels_prop_int((array) $props, 'body_size_desktop', 18, 12, 48);
$body_size_mobile = nb_hero_panels_prop_int((array) $props, 'body_size_mobile', 17, 12, 40);

$section_style = '--nb-hero-panels-content-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-min-height:' . $min_height_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-min-height-mobile:' . $min_height_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-padding-top-mobile:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-padding-bottom-mobile:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-media-width:' . $widths['media'] . '%;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-red-width:' . $widths['red'] . '%;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-black-width:' . $widths['black'] . '%;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-body-size:' . $body_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-body-size-mobile:' . $body_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-section-bg:' . $section_background . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-red-bg:' . $red_panel_background . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-black-bg:' . $black_panel_background . ';');
$section_style = nb_block_append_style($section_style, $reveal['style']);
?>
<section
    class="nb-section nb-hero-panels<?= $reveal['class'] ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container">
        <div class="nb-hero-panels__rail">
            <div
                class="nb-hero-panels__media"
                style="--nb-hero-panels-order:<?= (int) $desktop_order['media'] ?>;--nb-hero-panels-order-mobile:<?= (int) $mobile_order['media'] ?>"
            >
                <?php if ($image): ?>
                <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $image_alt ?>" class="nb-hero-panels__image" loading="lazy" decoding="async">
                <?php else: ?>
                <div class="nb-hero-panels__placeholder">Добавьте фото</div>
                <?php endif; ?>
            </div>

            <div
                class="nb-hero-panels__panel nb-hero-panels__panel--red"
                style="--nb-hero-panels-order:<?= (int) $desktop_order['red'] ?>;--nb-hero-panels-order-mobile:<?= (int) $mobile_order['red'] ?>"
            >
                <?php if ($kicker): ?>
                <div class="nb-hero-panels__kicker"><?= $kicker ?></div>
                <?php endif; ?>

                <<?= $title_tag ?> class="nb-hero-panels__title" style="font-weight:<?= (int) $title_weight ?>"><?= $title_html ?></<?= $title_tag ?>>

                <?php if ($subtitle): ?>
                <div class="nb-hero-panels__subtitle"><?= $subtitle ?></div>
                <?php endif; ?>

                <?php if ($show_button): ?>
                <div class="nb-hero-panels__actions">
                    <a
                        href="<?= $button_link ?>"
                        class="<?= htmlspecialchars($button_class, ENT_QUOTES, 'UTF-8') ?>"
                        target="<?= htmlspecialchars($button_target, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $button_rel ? 'rel="' . htmlspecialchars($button_rel, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                    >
                        <?= $button_text ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <div
                class="nb-hero-panels__panel nb-hero-panels__panel--dark"
                style="--nb-hero-panels-order:<?= (int) $desktop_order['black'] ?>;--nb-hero-panels-order-mobile:<?= (int) $mobile_order['black'] ?>"
            >
                <?php if ($body_html): ?>
                <div class="nb-hero-panels__body"><?= $body_html ?></div>
                <?php endif; ?>
                <div class="nb-hero-panels__divider" aria-hidden="true"></div>
            </div>
        </div>
    </div>
</section>