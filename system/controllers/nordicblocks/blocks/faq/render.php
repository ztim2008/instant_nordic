<?php
/**
 * NordicBlocks — блок: FAQ
 * Переменные контекста: $props (array), $block_type, $block_uid
 */

require_once dirname(__DIR__) . '/render_helpers.php';

$eyebrow = htmlspecialchars(trim((string) ($props['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
$heading = htmlspecialchars(trim((string) ($props['heading'] ?? '')), ENT_QUOTES, 'UTF-8');
$intro   = trim((string) ($props['intro'] ?? ''));
$intro_html = $intro !== '' ? nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) : '';
$theme   = in_array($props['theme'] ?? 'light', ['light', 'alt', 'dark'], true) ? $props['theme'] : 'light';
$open_first = in_array(strtolower((string) ($props['open_first'] ?? '1')), ['1', 'true', 'yes', 'on'], true);
$heading_tag = nb_block_get_heading_tag((array) $props, 'heading', 'h2');
$heading_weight = nb_block_get_font_weight((array) $props, 'heading', 800);
$reveal = nb_block_get_reveal_settings((array) $props);

$items = [];
if (!empty($props['items']) && is_array($props['items'])) {
    foreach ($props['items'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $question = trim((string) ($item['question'] ?? ''));
        $answer   = trim((string) ($item['answer'] ?? ''));
        if ($question === '' && $answer === '') {
            continue;
        }

        $items[] = [
            'question' => htmlspecialchars($question, ENT_QUOTES, 'UTF-8'),
            'answer'   => $answer !== '' ? nl2br(htmlspecialchars($answer, ENT_QUOTES, 'UTF-8')) : '',
        ];
    }
}

$theme_attr = ($theme !== 'light') ? ' data-nb-theme="' . $theme . '"' : '';
$bg_class   = ($theme === 'alt') ? ' nb-section--alt' : '';
$bg_class  .= $reveal['class'];
?>
<section
    class="nb-section nb-faq<?= $bg_class ?>"
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

        <?php if ($intro_html): ?>
        <div class="nb-faq__intro"><?= $intro_html ?></div>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-faq__list">
            <?php foreach ($items as $index => $item): ?>
            <details class="nb-faq__item nb-card" <?= ($open_first && $index === 0) ? 'open' : '' ?>>
                <summary class="nb-faq__question">
                    <span><?= $item['question'] ?></span>
                    <span class="nb-faq__icon" aria-hidden="true"></span>
                </summary>
                <?php if ($item['answer']): ?>
                <div class="nb-faq__answer">
                    <p><?= $item['answer'] ?></p>
                </div>
                <?php endif; ?>
            </details>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>