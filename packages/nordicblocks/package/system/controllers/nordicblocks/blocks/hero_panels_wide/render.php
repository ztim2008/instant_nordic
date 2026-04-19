<?php

require_once dirname(__DIR__) . '/render_helpers.php';

$nb_hero_panels_wide_contract = (isset($block_contract) && is_array($block_contract) && ((string) ($block_contract['meta']['blockType'] ?? '') === 'hero_panels_wide'))
    ? $block_contract
    : [];

if (!function_exists('nb_hero_panels_wide_visible')) {
    function nb_hero_panels_wide_visible($value, $default = true) {
        if ($value === null || $value === '') {
            return (bool) $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return !in_array(strtolower((string) $value), ['0', 'false', 'off', 'no'], true);
    }
}

if (!function_exists('nb_hero_panels_wide_get')) {
    function nb_hero_panels_wide_get(array $source, array $path, $default = null) {
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

if (!function_exists('nb_hero_panels_wide_prop_int')) {
    function nb_hero_panels_wide_prop_int($value, $default, $min, $max) {
        if (!is_numeric($value)) {
            $value = $default;
        }

        $value = (int) round((float) $value);

        return max($min, min($max, $value));
    }
}

if (!function_exists('nb_hero_panels_wide_paragraphs')) {
    function nb_hero_panels_wide_paragraphs($value) {
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

if (!function_exists('nb_hero_panels_wide_button_class')) {
    function nb_hero_panels_wide_button_class($style) {
        $style = strtolower(trim((string) $style));

        if ($style === 'primary') {
            return 'nb-btn nb-btn--white';
        }

        return 'nb-btn nb-btn--outline-white';
    }
}

$content = is_array($nb_hero_panels_wide_contract['content'] ?? null) ? $nb_hero_panels_wide_contract['content'] : [];
$design = is_array($nb_hero_panels_wide_contract['design'] ?? null) ? $nb_hero_panels_wide_contract['design'] : [];
$entities = is_array($design['entities'] ?? null) ? $design['entities'] : [];
$layout = is_array($nb_hero_panels_wide_contract['layout'] ?? null) ? $nb_hero_panels_wide_contract['layout'] : [];
$runtime = is_array($nb_hero_panels_wide_contract['runtime'] ?? null) ? $nb_hero_panels_wide_contract['runtime'] : [];

$theme = in_array((string) ($design['section']['theme'] ?? 'light'), ['light', 'dark', 'accent'], true)
    ? (string) $design['section']['theme']
    : 'light';
$background_style = nb_block_build_background_style((array) ($design['section']['background'] ?? []));
$reveal = nb_block_get_reveal_settings([
    'block_animation' => (string) ($runtime['animation']['name'] ?? 'none'),
    'block_animation_delay' => (int) ($runtime['animation']['delay'] ?? 0),
]);

$eyebrow = htmlspecialchars(trim((string) ($content['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
$title = trim((string) ($content['title'] ?? 'Hero: широкие панели'));
$title_html = $title !== '' ? nl2br(htmlspecialchars($title, ENT_QUOTES, 'UTF-8')) : '';
$subtitle = trim((string) ($content['subtitle'] ?? ''));
$subtitle_html = $subtitle !== '' ? htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') : '';
$body_html = nb_hero_panels_wide_paragraphs($content['body'] ?? '');

$title_visible = nb_hero_panels_wide_visible($entities['title']['visible'] ?? true, true);
$subtitle_visible = nb_hero_panels_wide_visible($entities['subtitle']['visible'] ?? true, true);
$title_tag = htmlspecialchars((string) nb_hero_panels_wide_get($entities, ['title', 'tag'], 'h2'), ENT_QUOTES, 'UTF-8');

$title_weight = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['title', 'desktop', 'weight'], 800), 800, 400, 900);
$title_size_desktop = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['title', 'desktop', 'fontSize'], 76), 76, 18, 180);
$title_size_mobile = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['title', 'mobile', 'fontSize'], 42), 42, 16, 140);

$eyebrow_size_desktop = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['eyebrow', 'desktop', 'fontSize'], 14), 14, 10, 40);
$eyebrow_size_mobile = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['eyebrow', 'mobile', 'fontSize'], 13), 13, 10, 36);
$eyebrow_weight = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['eyebrow', 'desktop', 'weight'], 700), 700, 400, 900);
$eyebrow_color = trim((string) nb_hero_panels_wide_get($entities, ['eyebrow', 'desktop', 'color'], ''));

$subtitle_size_desktop = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['subtitle', 'desktop', 'fontSize'], 14), 14, 10, 48);
$subtitle_size_mobile = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['subtitle', 'mobile', 'fontSize'], 13), 13, 10, 40);
$subtitle_weight = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['subtitle', 'desktop', 'weight'], 700), 700, 400, 900);
$subtitle_color = trim((string) nb_hero_panels_wide_get($entities, ['subtitle', 'desktop', 'color'], ''));

$body_size_desktop = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['body', 'desktop', 'fontSize'], 18), 18, 12, 56);
$body_size_mobile = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['body', 'mobile', 'fontSize'], 17), 17, 12, 48);
$body_weight = nb_hero_panels_wide_prop_int(nb_hero_panels_wide_get($entities, ['body', 'desktop', 'weight'], 400), 400, 400, 900);
$body_color = trim((string) nb_hero_panels_wide_get($entities, ['body', 'desktop', 'color'], ''));

$button_label = htmlspecialchars(trim((string) ($content['primaryButton']['label'] ?? '')), ENT_QUOTES, 'UTF-8');
$button_url = htmlspecialchars(trim((string) ($content['primaryButton']['url'] ?? '#')), ENT_QUOTES, 'UTF-8');
$button_style = nb_hero_panels_wide_button_class((string) nb_hero_panels_wide_get($entities, ['primaryButton', 'style'], 'outline'));

$image_data = nb_block_extract_media($content['media']['image'] ?? '', $content['media']['alt'] ?? '');
$image = htmlspecialchars($image_data['display'], ENT_QUOTES, 'UTF-8');
$image_alt = htmlspecialchars($image_data['alt'], ENT_QUOTES, 'UTF-8');

$layout_preset = strtolower(trim((string) ($layout['preset'] ?? 'split-left')));
if (!in_array($layout_preset, ['classic', 'split-left', 'split-right', 'edge-left', 'edge-right', 'strip'], true)) {
    $layout_preset = 'split-left';
}

$desktop_media_position = strtolower(trim((string) ($layout['desktop']['mediaPosition'] ?? 'start')));
if (!in_array($desktop_media_position, ['start', 'end'], true)) {
    $desktop_media_position = 'start';
}

if (in_array($layout_preset, ['split-left', 'edge-left', 'classic'], true)) {
    $desktop_media_position = 'start';
}

if (in_array($layout_preset, ['split-right', 'edge-right'], true)) {
    $desktop_media_position = 'end';
}

$mobile_media_position = strtolower(trim((string) ($layout['mobile']['mediaPosition'] ?? 'top')));
if (!in_array($mobile_media_position, ['top', 'bottom'], true)) {
    $mobile_media_position = 'top';
}

$desktop_order = $desktop_media_position === 'end'
    ? ['media' => 3, 'red' => 1, 'black' => 2]
    : ['media' => 1, 'red' => 2, 'black' => 3];
$mobile_order = $mobile_media_position === 'bottom'
    ? ['media' => 3, 'red' => 1, 'black' => 2]
    : ['media' => 1, 'red' => 2, 'black' => 3];

$content_width = nb_hero_panels_wide_prop_int($layout['desktop']['contentWidth'] ?? 1560, 1560, 960, 1800);
$padding_top_desktop = nb_hero_panels_wide_prop_int($layout['desktop']['paddingTop'] ?? 20, 20, 0, 220);
$padding_bottom_desktop = nb_hero_panels_wide_prop_int($layout['desktop']['paddingBottom'] ?? 20, 20, 0, 220);
$padding_top_mobile = nb_hero_panels_wide_prop_int($layout['mobile']['paddingTop'] ?? 12, 12, 0, 160);
$padding_bottom_mobile = nb_hero_panels_wide_prop_int($layout['mobile']['paddingBottom'] ?? 12, 12, 0, 160);
$min_height_desktop = nb_hero_panels_wide_prop_int($layout['desktop']['minHeight'] ?? 680, 680, 0, 1400);
$min_height_mobile = nb_hero_panels_wide_prop_int($layout['mobile']['minHeight'] ?? 0, 0, 0, 1200);
$actions_gap_desktop = nb_hero_panels_wide_prop_int($layout['desktop']['actionsGap'] ?? 12, 12, 0, 80);
$actions_gap_mobile = nb_hero_panels_wide_prop_int($layout['mobile']['actionsGap'] ?? 10, 10, 0, 80);

if ($layout_preset === 'strip') {
    $padding_top_desktop = 0;
    $padding_bottom_desktop = 0;
    $padding_top_mobile = 0;
    $padding_bottom_mobile = 0;
}

$widths = $content_width >= 1500
    ? ['media' => '45%', 'red' => '31%', 'black' => '24%']
    : ['media' => '42%', 'red' => '33%', 'black' => '25%'];

$section_modifiers = ['nb-hero-panels--managed'];
if (in_array($layout_preset, ['edge-left', 'edge-right'], true)) {
    $section_modifiers[] = 'nb-hero-panels--edge';
    $widths = ['media' => '54%', 'red' => '28%', 'black' => '18%'];
}

$section_style = '--nb-hero-panels-content-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-padding-top-mobile:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-padding-bottom-mobile:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-min-height:' . $min_height_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-min-height-mobile:' . $min_height_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-media-width:' . $widths['media'] . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-red-width:' . $widths['red'] . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-black-width:' . $widths['black'] . ';');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-actions-gap:' . $actions_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-actions-gap-mobile:' . $actions_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-body-size:' . $body_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-body-size-mobile:' . $body_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-section-bg:var(--nb-page-bg, #f6f4ef);');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-red-bg:var(--nb-color-accent, #e12525);');
$section_style = nb_block_append_style($section_style, '--nb-hero-panels-black-bg:var(--nb-color-text, #1d1d1f);');
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style']);

$eyebrow_style = 'font-size:' . $eyebrow_size_desktop . 'px;font-weight:' . $eyebrow_weight . ';';
if ($eyebrow_color !== '') {
    $eyebrow_style = nb_block_append_style($eyebrow_style, 'color:' . $eyebrow_color . ';');
}

$subtitle_style = 'font-size:' . $subtitle_size_desktop . 'px;font-weight:' . $subtitle_weight . ';';
if ($subtitle_color !== '') {
    $subtitle_style = nb_block_append_style($subtitle_style, 'color:' . $subtitle_color . ';');
}

$body_style = 'font-size:' . $body_size_desktop . 'px;font-weight:' . $body_weight . ';';
if ($body_color !== '') {
    $body_style = nb_block_append_style($body_style, 'color:' . $body_color . ';');
}
?>
<section
    class="nb-section nb-hero-panels <?= htmlspecialchars(implode(' ', $section_modifiers), ENT_QUOTES, 'UTF-8') ?> nb-hero-panels--<?= htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') ?><?= $reveal['class'] ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container">
        <div class="nb-hero-panels__rail">
            <div class="nb-hero-panels__media" style="--nb-hero-panels-order:<?= (int) $desktop_order['media'] ?>;--nb-hero-panels-order-mobile:<?= (int) $mobile_order['media'] ?>">
                <?php if ($image !== '') { ?>
                    <img src="<?= $image ?>" alt="<?= $image_alt ?>" class="nb-hero-panels__image" loading="lazy" decoding="async">
                <?php } else { ?>
                    <div class="nb-hero-panels__placeholder">Добавьте фото</div>
                <?php } ?>
            </div>

            <div class="nb-hero-panels__panel nb-hero-panels__panel--red" style="--nb-hero-panels-order:<?= (int) $desktop_order['red'] ?>;--nb-hero-panels-order-mobile:<?= (int) $mobile_order['red'] ?>">
                <?php if ($eyebrow !== '') { ?>
                    <div class="nb-hero-panels__kicker" style="<?= htmlspecialchars($eyebrow_style, ENT_QUOTES, 'UTF-8') ?>"><?= $eyebrow ?></div>
                <?php } ?>

                <?php if ($title_visible && $title_html !== '') { ?>
                    <<?= $title_tag ?> class="nb-hero-panels__title" style="font-weight:<?= (int) $title_weight ?>"><?= $title_html ?></<?= $title_tag ?>>
                <?php } ?>

                <?php if ($subtitle_visible && $subtitle_html !== '') { ?>
                    <div class="nb-hero-panels__subtitle" style="<?= htmlspecialchars($subtitle_style, ENT_QUOTES, 'UTF-8') ?>"><?= $subtitle_html ?></div>
                <?php } ?>

                <?php if ($button_label !== '') { ?>
                    <div class="nb-hero-panels__actions">
                        <a href="<?= $button_url ?>" class="<?= htmlspecialchars($button_style, ENT_QUOTES, 'UTF-8') ?>"><?= $button_label ?></a>
                    </div>
                <?php } ?>
            </div>

            <div class="nb-hero-panels__panel nb-hero-panels__panel--dark" style="--nb-hero-panels-order:<?= (int) $desktop_order['black'] ?>;--nb-hero-panels-order-mobile:<?= (int) $mobile_order['black'] ?>">
                <?php if ($body_html !== '') { ?>
                    <div class="nb-hero-panels__body" style="<?= htmlspecialchars($body_style, ENT_QUOTES, 'UTF-8') ?>"><?= $body_html ?></div>
                <?php } ?>
                <div class="nb-hero-panels__divider" aria-hidden="true"></div>
            </div>
        </div>
    </div>
</section>
