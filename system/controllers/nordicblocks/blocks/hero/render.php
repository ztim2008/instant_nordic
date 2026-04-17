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

if ($hero_contract) {
    $layout = in_array($hero_contract['layout']['desktop']['mode'] ?? '', ['centered', 'left', 'split'], true)
        ? (string) $hero_contract['layout']['desktop']['mode'] : 'centered';
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
    $heading_weight = (int) ($hero_contract['design']['entities']['title']['weight'] ?? 900);
    $title_size_desktop = (int) ($hero_contract['design']['entities']['title']['desktop']['fontSize'] ?? 64);
    $title_size_mobile = (int) ($hero_contract['design']['entities']['title']['mobile']['fontSize'] ?? 40);
    $subtitle_size_desktop = (int) ($hero_contract['design']['entities']['subtitle']['desktop']['fontSize'] ?? 20);
    $subtitle_size_mobile = (int) ($hero_contract['design']['entities']['subtitle']['mobile']['fontSize'] ?? 18);
    $title_margin_bottom_desktop = (int) ($hero_contract['design']['entities']['title']['desktop']['marginBottom'] ?? 16);
    $title_margin_bottom_mobile = (int) ($hero_contract['design']['entities']['title']['mobile']['marginBottom'] ?? 14);
    $subtitle_margin_bottom_desktop = (int) ($hero_contract['design']['entities']['subtitle']['desktop']['marginBottom'] ?? 24);
    $subtitle_margin_bottom_mobile = (int) ($hero_contract['design']['entities']['subtitle']['mobile']['marginBottom'] ?? 20);
    $content_width = (int) ($hero_contract['layout']['desktop']['contentWidth'] ?? 640);
    $padding_top_desktop = (int) ($hero_contract['layout']['desktop']['paddingTop'] ?? 96);
    $padding_bottom_desktop = (int) ($hero_contract['layout']['desktop']['paddingBottom'] ?? 96);
    $padding_top_mobile = (int) ($hero_contract['layout']['mobile']['paddingTop'] ?? 56);
    $padding_bottom_mobile = (int) ($hero_contract['layout']['mobile']['paddingBottom'] ?? 56);
    $min_height_desktop = (int) ($hero_contract['layout']['desktop']['minHeight'] ?? 0);
    $min_height_mobile = (int) ($hero_contract['layout']['mobile']['minHeight'] ?? 0);
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($hero_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($hero_contract['runtime']['animation']['delay'] ?? 0),
    ]);

    $btn1_label = htmlspecialchars(trim((string) ($hero_contract['content']['primaryButton']['label'] ?? '')), ENT_QUOTES, 'UTF-8');
    $btn1_url = htmlspecialchars(trim((string) ($hero_contract['content']['primaryButton']['url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $btn1_style = in_array($hero_contract['design']['entities']['primaryButton']['style'] ?? '', ['primary', 'outline', 'ghost'], true)
        ? (string) $hero_contract['design']['entities']['primaryButton']['style'] : 'primary';
    $btn2_label = htmlspecialchars(trim((string) ($hero_contract['content']['secondaryButton']['label'] ?? '')), ENT_QUOTES, 'UTF-8');
    $btn2_url = htmlspecialchars(trim((string) ($hero_contract['content']['secondaryButton']['url'] ?? '#')), ENT_QUOTES, 'UTF-8');
    $btn2_style = in_array($hero_contract['design']['entities']['secondaryButton']['style'] ?? '', ['primary', 'outline', 'ghost'], true)
        ? (string) $hero_contract['design']['entities']['secondaryButton']['style'] : 'outline';

    $image = htmlspecialchars(trim((string) ($hero_contract['content']['media']['image'] ?? '')), ENT_QUOTES, 'UTF-8');
    $image_alt = htmlspecialchars(trim((string) ($hero_contract['content']['media']['alt'] ?? '')), ENT_QUOTES, 'UTF-8');
    $meta_date = htmlspecialchars(trim((string) ($hero_contract['content']['meta']['date'] ?? '')), ENT_QUOTES, 'UTF-8');
    $meta_views = htmlspecialchars(trim((string) ($hero_contract['content']['meta']['views'] ?? '')), ENT_QUOTES, 'UTF-8');
    $meta_comments = htmlspecialchars(trim((string) ($hero_contract['content']['meta']['comments'] ?? '')), ENT_QUOTES, 'UTF-8');
} else {
    $layout = in_array($props['layout'] ?? '', ['centered', 'left', 'split'], true)
        ? $props['layout'] : 'centered';
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
    $heading_weight = nb_block_get_font_weight((array) $props, 'heading', 900);
    $title_size_desktop = nb_hero_prop_int((array) $props, 'title_size_desktop', 64, 12, 240);
    $title_size_mobile = nb_hero_prop_int((array) $props, 'title_size_mobile', 40, 12, 240);
    $subtitle_size_desktop = nb_hero_prop_int((array) $props, 'subtitle_size_desktop', 20, 10, 120);
    $subtitle_size_mobile = nb_hero_prop_int((array) $props, 'subtitle_size_mobile', 18, 10, 120);
    $title_margin_bottom_desktop = nb_hero_prop_int((array) $props, 'title_margin_bottom_desktop', 16, 0, 240);
    $title_margin_bottom_mobile = nb_hero_prop_int((array) $props, 'title_margin_bottom_mobile', 14, 0, 240);
    $subtitle_margin_bottom_desktop = nb_hero_prop_int((array) $props, 'subtitle_margin_bottom_desktop', 24, 0, 240);
    $subtitle_margin_bottom_mobile = nb_hero_prop_int((array) $props, 'subtitle_margin_bottom_mobile', 20, 0, 240);
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
    $meta_date = '';
    $meta_views = '';
    $meta_comments = '';
}

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
$section_style = nb_block_append_style($section_style, '--nb-hero-title-margin-bottom:' . $title_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-title-margin-bottom-mobile:' . $title_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-margin-bottom:' . $subtitle_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-hero-subtitle-margin-bottom-mobile:' . $subtitle_margin_bottom_mobile . 'px;');
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

            <?php if ($title_visible && $heading): ?>
            <<?= $heading_tag ?> class="nb-hero__heading" style="font-weight:<?= (int) $heading_weight ?>" data-nb-entity="title"><?= $heading ?></<?= $heading_tag ?>>
            <?php endif; ?>

            <?php if ($subtitle_visible && $subhead): ?>
            <p class="nb-hero__subheading" data-nb-entity="subtitle"><?= $subhead ?></p>
            <?php endif; ?>

            <?php if ($meta_date || $meta_views || $meta_comments): ?>
            <div class="nb-hero__meta" data-nb-entity="meta">
                <?php if ($meta_date): ?>
                <span class="nb-hero__meta-item"><?= $meta_date ?></span>
                <?php endif; ?>
                <?php if ($meta_views): ?>
                <span class="nb-hero__meta-item"><?= $meta_views ?> просмотров</span>
                <?php endif; ?>
                <?php if ($meta_comments): ?>
                <span class="nb-hero__meta-item"><?= $meta_comments ?> комментариев</span>
                <?php endif; ?>
            </div>
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
