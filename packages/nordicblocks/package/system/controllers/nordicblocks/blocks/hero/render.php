<?php
/**
 * NordicBlocks — блок: Hero
 * Переменные контекста: $props (array), $block_type, $block_uid
 */

require_once dirname(__DIR__) . '/render_helpers.php';

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

$layout = in_array($props['layout'] ?? '', ['centered', 'left', 'split'], true)
    ? $props['layout'] : 'centered';
$theme = in_array($props['theme'] ?? '', ['light', 'dark', 'accent'], true)
    ? $props['theme'] : 'light';

$eyebrow = htmlspecialchars(trim((string) ($props['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
$heading = htmlspecialchars(trim((string) ($props['heading'] ?? 'Заголовок')), ENT_QUOTES, 'UTF-8');
$subhead = htmlspecialchars(trim((string) ($props['subheading'] ?? '')), ENT_QUOTES, 'UTF-8');
$heading_tag = nb_block_get_heading_tag((array) $props, 'heading', 'h1');
$heading_weight = nb_block_get_font_weight((array) $props, 'heading', 900);
$title_size_desktop = nb_hero_prop_int((array) $props, 'title_size_desktop', 64, 12, 240);
$title_size_mobile = nb_hero_prop_int((array) $props, 'title_size_mobile', 40, 12, 240);
$subtitle_size_desktop = nb_hero_prop_int((array) $props, 'subtitle_size_desktop', 20, 10, 120);
$subtitle_size_mobile = nb_hero_prop_int((array) $props, 'subtitle_size_mobile', 18, 10, 120);
$content_width = nb_hero_prop_int((array) $props, 'content_width', 640, 280, 1440);
$padding_top_desktop = nb_hero_prop_int((array) $props, 'padding_top_desktop', 96, 0, 300);
$padding_bottom_desktop = nb_hero_prop_int((array) $props, 'padding_bottom_desktop', 96, 0, 300);
$padding_top_mobile = nb_hero_prop_int((array) $props, 'padding_top_mobile', 56, 0, 300);
$padding_bottom_mobile = nb_hero_prop_int((array) $props, 'padding_bottom_mobile', 56, 0, 300);
$min_height_desktop = nb_hero_prop_int((array) $props, 'min_height_desktop', 0, 0, 1200);
$min_height_mobile = nb_hero_prop_int((array) $props, 'min_height_mobile', 0, 0, 1200);
$reveal = nb_block_get_reveal_settings((array) $props);

$btn1_label = htmlspecialchars(trim((string) ($props['btn_primary_label'] ?? '')), ENT_QUOTES, 'UTF-8');
$btn1_url = htmlspecialchars(trim((string) ($props['btn_primary_url'] ?? '#')), ENT_QUOTES, 'UTF-8');
$btn1_style = in_array($props['btn_primary_style'] ?? '', ['primary', 'outline', 'ghost'], true)
    ? (string) $props['btn_primary_style'] : 'primary';
$btn2_label = htmlspecialchars(trim((string) ($props['btn_secondary_label'] ?? '')), ENT_QUOTES, 'UTF-8');
$btn2_url = htmlspecialchars(trim((string) ($props['btn_secondary_url'] ?? '#')), ENT_QUOTES, 'UTF-8');
$btn2_style = in_array($props['btn_secondary_style'] ?? '', ['primary', 'outline', 'ghost'], true)
    ? (string) $props['btn_secondary_style'] : 'outline';

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

$section_class = 'nb-section nb-hero nb-hero--' . $layout;
$section_class .= $reveal['class'];
$data_theme = $theme !== 'light' ? ' data-nb-theme="' . $theme . '"' : '';

$section_style = '--nb-hero-content-max-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-hero-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-mobile-padding-top:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-mobile-padding-bottom:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-min-height:' . $min_height_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-mobile-min-height:' . $min_height_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
if ($theme === 'accent') {
    $section_style = nb_block_append_style($section_style, 'background:var(--nb-color-accent);color:#fff;');
}
$section_style = nb_block_append_style($section_style, $reveal['style']);

$button_classes = [
    'primary' => 'nb-btn nb-btn--primary',
    'outline' => 'nb-btn nb-btn--outline',
    'ghost'   => 'nb-btn nb-btn--ghost',
];
?>
<section
    class="<?= $section_class ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-entity="section"
    <?= $data_theme ?><?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container nb-hero__container">

        <?php if ($layout === 'split'): ?>
        <div class="nb-hero__media" data-nb-entity="mediaSurface">
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
        <?php endif; ?>

        <div class="nb-hero__content">

            <?php if ($eyebrow): ?>
            <p class="nb-hero__eyebrow" data-nb-entity="eyebrow"><?= $eyebrow ?></p>
            <?php endif; ?>

            <<?= $heading_tag ?> class="nb-hero__heading" style="font-weight:<?= (int) $heading_weight ?>" data-nb-entity="title"><?= $heading ?></<?= $heading_tag ?>>

            <?php if ($subhead): ?>
            <p class="nb-hero__subheading" data-nb-entity="subtitle"><?= $subhead ?></p>
            <?php endif; ?>

            <?php if ($btn1_label || $btn2_label): ?>
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
            </div>
            <?php endif; ?>

        </div>

    </div>
</section>
