<?php
/**
 * NordicBlocks — блок: Features (сетка преимуществ)
 * Переменные контекста: $props (array), $block_type, $block_uid
 */

require_once dirname(__DIR__) . '/render_helpers.php';

$eyebrow = htmlspecialchars(trim((string) ($props['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
$heading = htmlspecialchars(trim((string) ($props['heading'] ?? '')), ENT_QUOTES, 'UTF-8');
$cols    = in_array($props['columns'] ?? '3', ['2','3','4'], true) ? (int) $props['columns'] : 3;
$theme   = in_array($props['theme'] ?? 'light', ['light','alt','dark'], true) ? $props['theme'] : 'light';
$heading_tag = nb_block_get_heading_tag((array) $props, 'heading', 'h2');
$heading_weight = nb_block_get_font_weight((array) $props, 'heading', 800);
$reveal = nb_block_get_reveal_settings((array) $props);

// Собираем items из плоских полей item1..item4
$items = [];
for ($i = 1; $i <= 4; $i++) {
    $title = trim((string) ($props["item{$i}_title"] ?? ''));
    if ($title === '') { continue; }
    $items[] = [
        'icon'  => trim((string) ($props["item{$i}_icon"] ?? 'fa-check')),
        'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
        'text'  => htmlspecialchars(trim((string) ($props["item{$i}_text"] ?? '')), ENT_QUOTES, 'UTF-8'),
    ];
}

$theme_attr = ($theme !== 'light') ? ' data-nb-theme="' . $theme . '"' : '';
$bg_class   = ($theme === 'alt') ? ' nb-section--alt' : '';
$bg_class  .= $reveal['class'];
?>
<section
    class="nb-section nb-features<?= $bg_class ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    <?= $theme_attr ?>
    <?= $reveal['style'] ? ' style="' . htmlspecialchars($reveal['style'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container">

        <?php if ($eyebrow || $heading): ?>
        <div class="nb-section-header">
            <?php if ($eyebrow): ?>
            <p class="nb-eyebrow"><?= $eyebrow ?></p>
            <?php endif; ?>
            <?php if ($heading): ?>
            <<?= $heading_tag ?> class="nb-section-title" style="font-weight:<?= (int) $heading_weight ?>"><?= $heading ?></<?= $heading_tag ?>>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-features__grid nb-features__grid--cols-<?= $cols ?>">
            <?php foreach ($items as $item): ?>
            <div class="nb-feature-card">
                <?php $icon_markup = nb_block_render_icon_markup($item['icon']); ?>
                <?php if ($icon_markup): ?>
                <div class="nb-feature-card__icon">
                    <?= $icon_markup ?>
                </div>
                <?php endif; ?>
                <h3 class="nb-feature-card__title"><?= $item['title'] ?></h3>
                <?php if ($item['text']): ?>
                <p class="nb-feature-card__text"><?= $item['text'] ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>
