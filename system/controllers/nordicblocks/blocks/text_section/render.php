<?php
/**
 * NordicBlocks — блок: Text Section
 * Переменные контекста: $props (array), $block_type, $block_uid
 */

$eyebrow   = htmlspecialchars(trim((string) ($props['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
$heading   = htmlspecialchars(trim((string) ($props['heading'] ?? '')), ENT_QUOTES, 'UTF-8');
$body      = trim((string) ($props['body'] ?? ''));
$align     = in_array($props['alignment'] ?? 'center', ['left','center'], true) ? $props['alignment'] : 'center';
$theme     = in_array($props['theme'] ?? 'light', ['light','alt','dark'], true) ? $props['theme'] : 'light';
$max_width = in_array($props['max_width'] ?? 'md', ['sm','md','lg'], true) ? $props['max_width'] : 'md';
$btn_label = htmlspecialchars(trim((string) ($props['btn_label'] ?? '')), ENT_QUOTES, 'UTF-8');
$btn_url   = htmlspecialchars(trim((string) ($props['btn_url'] ?? '#')), ENT_QUOTES, 'UTF-8');

// Конвертируем переносы строк в <br> внутри абзацев
$body_html = '';
if ($body !== '') {
    $paragraphs = preg_split('/\r?\n\r?\n/', $body);
    foreach ($paragraphs as $para) {
        $para = htmlspecialchars(trim($para), ENT_QUOTES, 'UTF-8');
        $para = nl2br($para);
        $body_html .= '<p class="nb-text-section__body">' . $para . '</p>';
    }
}

$theme_attr = ($theme !== 'light') ? ' data-nb-theme="' . $theme . '"' : '';
$bg_class   = ($theme === 'alt') ? ' nb-section--alt' : '';
$mw_map     = ['sm' => '560px', 'md' => '720px', 'lg' => '960px'];
$mw_style   = 'max-width:' . $mw_map[$max_width] . ';' . ($align === 'center' ? 'margin:0 auto;' : '');
?>
<section
    class="nb-section nb-text-section<?= $bg_class ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    <?= $theme_attr ?>
>
    <div class="nb-container">
        <div class="nb-text-section__inner" style="text-align:<?= $align ?>;<?= $mw_style ?>">

            <?php if ($eyebrow): ?>
            <p class="nb-eyebrow"><?= $eyebrow ?></p>
            <?php endif; ?>

            <?php if ($heading): ?>
            <h2 class="nb-section-title"><?= $heading ?></h2>
            <?php endif; ?>

            <?= $body_html ?>

            <?php if ($btn_label): ?>
            <div class="nb-text-section__actions">
                <a href="<?= $btn_url ?>" class="nb-btn nb-btn--primary"><?= $btn_label ?></a>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
