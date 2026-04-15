<?php
/**
 * NordicBlocks — блок: CTA (призыв к действию)
 * Переменные контекста: $props (array), $block_type, $block_uid
 */

$heading  = htmlspecialchars(trim((string) ($props['heading']    ?? 'Готовы начать?')), ENT_QUOTES, 'UTF-8');
$subhead  = htmlspecialchars(trim((string) ($props['subheading'] ?? '')), ENT_QUOTES, 'UTF-8');
$theme    = in_array($props['theme'] ?? 'accent', ['accent','dark','light'], true) ? $props['theme'] : 'accent';

$btn1_label = htmlspecialchars(trim((string) ($props['btn_primary_label']   ?? '')), ENT_QUOTES, 'UTF-8');
$btn1_url   = htmlspecialchars(trim((string) ($props['btn_primary_url']     ?? '#')), ENT_QUOTES, 'UTF-8');
$btn2_label = htmlspecialchars(trim((string) ($props['btn_secondary_label'] ?? '')), ENT_QUOTES, 'UTF-8');
$btn2_url   = htmlspecialchars(trim((string) ($props['btn_secondary_url']   ?? '#')), ENT_QUOTES, 'UTF-8');

// Стили секции: accent — акцентный фон, dark — тёмный, light — обычный
$section_style = '';
$btn1_class    = 'nb-btn nb-btn--primary';
$btn2_class    = 'nb-btn nb-btn--outline';

if ($theme === 'accent') {
    $section_style = 'background:var(--nb-color-accent);color:#fff;';
    $btn1_class    = 'nb-btn nb-btn--white';
    $btn2_class    = 'nb-btn nb-btn--outline-white';
} elseif ($theme === 'dark') {
    $section_style = 'background:var(--nb-color-text);color:#fff;';
    $btn1_class    = 'nb-btn nb-btn--accent';
    $btn2_class    = 'nb-btn nb-btn--outline-white';
}
?>
<section
    class="nb-section nb-cta"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    style="<?= $section_style ?>"
>
    <div class="nb-container">
        <div class="nb-cta__inner">

            <div class="nb-cta__copy">
                <h2 class="nb-cta__heading"><?= $heading ?></h2>
                <?php if ($subhead): ?>
                <p class="nb-cta__subheading"><?= $subhead ?></p>
                <?php endif; ?>
            </div>

            <?php if ($btn1_label || $btn2_label): ?>
            <div class="nb-cta__actions">
                <?php if ($btn1_label): ?>
                <a href="<?= $btn1_url ?>" class="<?= $btn1_class ?>"><?= $btn1_label ?></a>
                <?php endif; ?>
                <?php if ($btn2_label): ?>
                <a href="<?= $btn2_url ?>" class="<?= $btn2_class ?>"><?= $btn2_label ?></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
