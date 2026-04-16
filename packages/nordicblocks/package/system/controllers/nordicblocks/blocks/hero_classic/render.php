<?php

$title = htmlspecialchars(trim((string) ($props['title'] ?? '')), ENT_QUOTES, 'UTF-8');
if ($title === '') {
    $title = 'Hero Classic';
}

$subtitle = htmlspecialchars(trim((string) ($props['subtitle'] ?? '')), ENT_QUOTES, 'UTF-8');
$badge    = htmlspecialchars(trim((string) ($props['badge'] ?? '')), ENT_QUOTES, 'UTF-8');

$show_divider = in_array(strtolower((string) ($props['show_divider'] ?? '1')), ['1', 'true', 'yes', 'on'], true);
$show_button  = in_array(strtolower((string) ($props['show_button'] ?? '1')), ['1', 'true', 'yes', 'on'], true);

$button_text = htmlspecialchars(trim((string) ($props['button_text'] ?? '')), ENT_QUOTES, 'UTF-8');
$button_link = htmlspecialchars(trim((string) ($props['button_link'] ?? '#')), ENT_QUOTES, 'UTF-8');

$text_align = strtolower(trim((string) ($props['text_align'] ?? 'center')));
if (!in_array($text_align, ['left', 'center', 'right'], true)) {
    $text_align = 'center';
}

$title_desktop_px    = max(24, min(200, (int) ($props['title_desktop_px'] ?? 96)));
$title_mobile_px     = max(18, min(140, (int) ($props['title_mobile_px'] ?? 52)));
$subtitle_desktop_px = max(10, min(64, (int) ($props['subtitle_desktop_px'] ?? 24)));
$subtitle_mobile_px  = max(10, min(48, (int) ($props['subtitle_mobile_px'] ?? 18)));

$section_class = 'nb-section nb-hero-classic nb-hero-classic--' . $text_align;
$section_style = sprintf(
    '--nb-hero-classic-align:%s;--nb-hero-classic-title-desktop:%dpx;--nb-hero-classic-title-mobile:%dpx;--nb-hero-classic-subtitle-desktop:%dpx;--nb-hero-classic-subtitle-mobile:%dpx;',
    $text_align,
    $title_desktop_px,
    $title_mobile_px,
    $subtitle_desktop_px,
    $subtitle_mobile_px
);
?>
<section
    class="<?= htmlspecialchars($section_class, ENT_QUOTES, 'UTF-8') ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    style="<?= htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') ?>"
>
    <div class="nb-container">
        <div class="nb-hero-classic__inner">
            <?php if ($badge): ?>
            <div class="nb-hero-classic__badge"><?= $badge ?></div>
            <?php endif; ?>

            <h1 class="nb-hero-classic__title"><?= $title ?></h1>

            <?php if ($show_divider): ?>
            <div class="nb-hero-classic__divider" aria-hidden="true"></div>
            <?php endif; ?>

            <?php if ($subtitle): ?>
            <p class="nb-hero-classic__subtitle"><?= nl2br($subtitle) ?></p>
            <?php endif; ?>

            <?php if ($show_button && $button_text): ?>
            <div class="nb-hero-classic__actions">
                <a href="<?= $button_link ?>" class="nb-btn nb-btn--primary">
                    <?= $button_text ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>