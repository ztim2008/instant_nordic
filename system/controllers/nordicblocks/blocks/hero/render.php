<?php
/**
 * NordicBlocks — блок: Hero
 * Переменные контекста: $props (array), $block_type, $block_uid
 */

require_once dirname(__DIR__) . '/render_helpers.php';

// ── Значения по умолчанию ──────────────────────────────────────────────────
$layout    = in_array($props['layout'] ?? '', ['centered', 'left', 'split'], true)
    ? $props['layout'] : 'centered';
$theme     = in_array($props['theme']  ?? '', ['light', 'dark', 'accent'], true)
    ? $props['theme']  : 'light';

$eyebrow  = htmlspecialchars(trim((string) ($props['eyebrow']  ?? '')), ENT_QUOTES, 'UTF-8');
$heading  = htmlspecialchars(trim((string) ($props['heading']  ?? 'Заголовок')), ENT_QUOTES, 'UTF-8');
$subhead  = htmlspecialchars(trim((string) ($props['subheading'] ?? '')), ENT_QUOTES, 'UTF-8');
$heading_tag = nb_block_get_heading_tag((array) $props, 'heading', 'h1');
$heading_weight = nb_block_get_font_weight((array) $props, 'heading', 900);
$reveal = nb_block_get_reveal_settings((array) $props);

$btn1_label = htmlspecialchars(trim((string) ($props['btn_primary_label']   ?? '')), ENT_QUOTES, 'UTF-8');
$btn1_url   = htmlspecialchars(trim((string) ($props['btn_primary_url']     ?? '#')), ENT_QUOTES, 'UTF-8');
$btn2_label = htmlspecialchars(trim((string) ($props['btn_secondary_label'] ?? '')), ENT_QUOTES, 'UTF-8');
$btn2_url   = htmlspecialchars(trim((string) ($props['btn_secondary_url']   ?? '#')), ENT_QUOTES, 'UTF-8');

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

$image     = htmlspecialchars(trim((string) ($image_value['display'] ?? $image_value['original'] ?? '')), ENT_QUOTES, 'UTF-8');
$image_alt = htmlspecialchars(trim((string) ($image_value['alt'] ?? ($props['image_alt'] ?? ''))), ENT_QUOTES, 'UTF-8');

// ── CSS-классы на основе опций ─────────────────────────────────────────────
$section_class = 'nb-section nb-hero nb-hero--' . $layout;
$section_class .= $reveal['class'];
$data_theme    = $theme !== 'light' ? ' data-nb-theme="' . $theme . '"' : '';
// для темы accent — переопределяем bg через inline style
$section_style = '';
if ($theme === 'accent') {
    $section_style = 'background:var(--nb-color-accent);color:#fff;';
}
$section_style = nb_block_append_style($section_style, $reveal['style']);
?>
<section
    class="<?= $section_class ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    <?= $data_theme ?><?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container nb-hero__container">

        <?php if ($layout === 'split' && $image): ?>
        <div class="nb-hero__media">
            <img
                src="<?= $image ?>"
                alt="<?= $image_alt ?>"
                class="nb-hero__image"
                loading="lazy"
                decoding="async"
            >
        </div>
        <?php endif; ?>

        <div class="nb-hero__content">

            <?php if ($eyebrow): ?>
            <p class="nb-hero__eyebrow"><?= $eyebrow ?></p>
            <?php endif; ?>

            <<?= $heading_tag ?> class="nb-hero__heading" style="font-weight:<?= (int) $heading_weight ?>"><?= $heading ?></<?= $heading_tag ?>>

            <?php if ($subhead): ?>
            <p class="nb-hero__subheading"><?= $subhead ?></p>
            <?php endif; ?>

            <?php if ($btn1_label || $btn2_label): ?>
            <div class="nb-hero__actions">
                <?php if ($btn1_label): ?>
                <a href="<?= $btn1_url ?>" class="nb-btn nb-btn--primary">
                    <?= $btn1_label ?>
                </a>
                <?php endif; ?>

                <?php if ($btn2_label): ?>
                <a href="<?= $btn2_url ?>" class="nb-btn nb-btn--outline">
                    <?= $btn2_label ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div><!-- /.nb-hero__content -->

    </div><!-- /.nb-container -->
</section>
