<?php
/**
 * NordicBlocks — блок: Features (сетка преимуществ)
 * Переменные контекста: $props (array), $block_type, $block_uid
 */

$eyebrow = htmlspecialchars(trim((string) ($props['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
$heading = htmlspecialchars(trim((string) ($props['heading'] ?? '')), ENT_QUOTES, 'UTF-8');
$cols    = in_array($props['columns'] ?? '3', ['2','3','4'], true) ? (int) $props['columns'] : 3;
$theme   = in_array($props['theme'] ?? 'light', ['light','alt','dark'], true) ? $props['theme'] : 'light';

// Собираем items из плоских полей item1..item4
$items = [];
for ($i = 1; $i <= 4; $i++) {
    $title = trim((string) ($props["item{$i}_title"] ?? ''));
    if ($title === '') { continue; }
    $items[] = [
        'icon'  => preg_replace('/[^a-z0-9\-_]/', '', strtolower((string) ($props["item{$i}_icon"] ?? 'fa-check'))),
        'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
        'text'  => htmlspecialchars(trim((string) ($props["item{$i}_text"] ?? '')), ENT_QUOTES, 'UTF-8'),
    ];
}

$theme_attr = ($theme !== 'light') ? ' data-nb-theme="' . $theme . '"' : '';
$bg_class   = ($theme === 'alt') ? ' nb-section--alt' : '';
?>
<section
    class="nb-section nb-features<?= $bg_class ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    <?= $theme_attr ?>
>
    <div class="nb-container">

        <?php if ($eyebrow || $heading): ?>
        <div class="nb-section-header">
            <?php if ($eyebrow): ?>
            <p class="nb-eyebrow"><?= $eyebrow ?></p>
            <?php endif; ?>
            <?php if ($heading): ?>
            <h2 class="nb-section-title"><?= $heading ?></h2>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-features__grid nb-features__grid--cols-<?= $cols ?>">
            <?php foreach ($items as $item): ?>
            <div class="nb-feature-card">
                <?php if ($item['icon']): ?>
                <div class="nb-feature-card__icon">
                    <i class="fa <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
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
