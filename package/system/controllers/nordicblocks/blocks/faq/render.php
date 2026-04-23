<?php
/**
 * NordicBlocks — блок: FAQ
 * Переменные контекста: $props (array), $block_type, $block_uid
 */

require_once dirname(__DIR__) . '/render_helpers.php';

$faq_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'faq'))
    ? $block_contract
    : null;

if (!function_exists('nb_faq_prop_int')) {
    function nb_faq_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_faq_normalize_item')) {
    function nb_faq_normalize_item(array $item) {
        $title = trim((string) ($item['title'] ?? ($item['question'] ?? '')));
        $text  = trim((string) ($item['text'] ?? ($item['answer'] ?? '')));

        if ($title === '' && $text === '') {
            return null;
        }

        $title_escaped = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $text_html = $text !== '' ? nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) : '';

        return [
            'title'    => $title_escaped,
            'text'     => $text_html,
            'question' => $title_escaped,
            'answer'   => $text_html,
        ];
    }
}

if ($faq_contract) {
    $eyebrow = htmlspecialchars(trim((string) ($faq_contract['content']['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
    $heading = htmlspecialchars(trim((string) ($faq_contract['content']['title'] ?? '')), ENT_QUOTES, 'UTF-8');
    $intro   = trim((string) ($faq_contract['content']['subtitle'] ?? ''));
    $theme   = in_array($faq_contract['design']['section']['theme'] ?? 'light', ['light', 'alt', 'dark'], true) ? (string) $faq_contract['design']['section']['theme'] : 'light';
    $background_mode = (string) ($faq_contract['design']['section']['background']['mode'] ?? 'theme');
    $background_style = nb_block_build_background_style((array) ($faq_contract['design']['section']['background'] ?? []));
    $title_visible = !array_key_exists('visible', (array) ($faq_contract['design']['entities']['title'] ?? [])) || !empty($faq_contract['design']['entities']['title']['visible']);
    $subtitle_visible = !array_key_exists('visible', (array) ($faq_contract['design']['entities']['subtitle'] ?? [])) || !empty($faq_contract['design']['entities']['subtitle']['visible']);
    $open_first = !empty($faq_contract['runtime']['disclosure']['openFirst']);
    $heading_tag = htmlspecialchars((string) ($faq_contract['design']['entities']['title']['tag'] ?? 'h2'), ENT_QUOTES, 'UTF-8');
    $heading_weight = (int) ($faq_contract['design']['entities']['title']['weight'] ?? 800);
    $title_size_desktop = (int) ($faq_contract['design']['entities']['title']['desktop']['fontSize'] ?? 48);
    $title_size_mobile = (int) ($faq_contract['design']['entities']['title']['mobile']['fontSize'] ?? 32);
    $title_color = nb_block_css_color((string) ($faq_contract['design']['entities']['title']['color'] ?? ''));
    $title_line_height = ((float) ($faq_contract['design']['entities']['title']['lineHeightPercent'] ?? 110)) / 100;
    $title_letter_spacing = (float) ($faq_contract['design']['entities']['title']['letterSpacing'] ?? 0);
    $title_max_width = (int) ($faq_contract['design']['entities']['title']['maxWidth'] ?? 600);
    $subtitle_size_desktop = (int) ($faq_contract['design']['entities']['subtitle']['desktop']['fontSize'] ?? 18);
    $subtitle_size_mobile = (int) ($faq_contract['design']['entities']['subtitle']['mobile']['fontSize'] ?? 16);
    $subtitle_color = nb_block_css_color((string) ($faq_contract['design']['entities']['subtitle']['color'] ?? ''));
    $subtitle_line_height = ((float) ($faq_contract['design']['entities']['subtitle']['lineHeightPercent'] ?? 165)) / 100;
    $subtitle_letter_spacing = (float) ($faq_contract['design']['entities']['subtitle']['letterSpacing'] ?? 0);
    $subtitle_max_width = (int) ($faq_contract['design']['entities']['subtitle']['maxWidth'] ?? 720);
    $title_margin_bottom_desktop = (int) ($faq_contract['design']['entities']['title']['desktop']['marginBottom'] ?? 0);
    $title_margin_bottom_mobile = (int) ($faq_contract['design']['entities']['title']['mobile']['marginBottom'] ?? 0);
    $subtitle_margin_bottom_desktop = (int) ($faq_contract['design']['entities']['subtitle']['desktop']['marginBottom'] ?? 32);
    $subtitle_margin_bottom_mobile = (int) ($faq_contract['design']['entities']['subtitle']['mobile']['marginBottom'] ?? 24);
    $item_title_size_desktop = (int) ($faq_contract['design']['entities']['itemTitle']['desktop']['fontSize'] ?? 18);
    $item_title_size_mobile = (int) ($faq_contract['design']['entities']['itemTitle']['mobile']['fontSize'] ?? 17);
    $item_title_weight = (int) ($faq_contract['design']['entities']['itemTitle']['weight'] ?? 700);
    $item_title_color = nb_block_css_color((string) ($faq_contract['design']['entities']['itemTitle']['color'] ?? ''));
    $item_title_line_height = ((float) ($faq_contract['design']['entities']['itemTitle']['lineHeightPercent'] ?? 135)) / 100;
    $item_title_letter_spacing = (float) ($faq_contract['design']['entities']['itemTitle']['letterSpacing'] ?? 0);
    $item_text_size_desktop = (int) ($faq_contract['design']['entities']['itemText']['desktop']['fontSize'] ?? 16);
    $item_text_size_mobile = (int) ($faq_contract['design']['entities']['itemText']['mobile']['fontSize'] ?? 15);
    $item_text_color = nb_block_css_color((string) ($faq_contract['design']['entities']['itemText']['color'] ?? ''));
    $item_text_line_height = ((float) ($faq_contract['design']['entities']['itemText']['lineHeightPercent'] ?? 170)) / 100;
    $item_text_letter_spacing = (float) ($faq_contract['design']['entities']['itemText']['letterSpacing'] ?? 0);
    $item_surface_variant = in_array($faq_contract['design']['entities']['itemSurface']['variant'] ?? 'card', ['card', 'plain'], true)
        ? (string) $faq_contract['design']['entities']['itemSurface']['variant'] : 'card';
    $content_width = (int) ($faq_contract['layout']['desktop']['contentWidth'] ?? 760);
    $padding_top_desktop = (int) ($faq_contract['layout']['desktop']['paddingTop'] ?? 88);
    $padding_bottom_desktop = (int) ($faq_contract['layout']['desktop']['paddingBottom'] ?? 88);
    $padding_top_mobile = (int) ($faq_contract['layout']['mobile']['paddingTop'] ?? 56);
    $padding_bottom_mobile = (int) ($faq_contract['layout']['mobile']['paddingBottom'] ?? 56);
    $align = in_array($faq_contract['layout']['desktop']['align'] ?? 'center', ['left', 'center'], true)
        ? (string) $faq_contract['layout']['desktop']['align'] : 'center';
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($faq_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($faq_contract['runtime']['animation']['delay'] ?? 0),
    ]);

    $items_source = is_array($faq_contract['content']['items'] ?? null) ? $faq_contract['content']['items'] : [];
} else {
    $eyebrow = htmlspecialchars(trim((string) ($props['eyebrow'] ?? '')), ENT_QUOTES, 'UTF-8');
    $heading = htmlspecialchars(trim((string) ($props['heading'] ?? '')), ENT_QUOTES, 'UTF-8');
    $intro   = trim((string) ($props['intro'] ?? ''));
    $theme   = in_array($props['theme'] ?? 'light', ['light', 'alt', 'dark'], true) ? $props['theme'] : 'light';
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
    $title_visible = !isset($props['title_visible']) || in_array(strtolower((string) $props['title_visible']), ['1', 'true', 'yes', 'on'], true);
    $subtitle_visible = !isset($props['subtitle_visible']) || in_array(strtolower((string) $props['subtitle_visible']), ['1', 'true', 'yes', 'on'], true);
    $open_first = in_array(strtolower((string) ($props['open_first'] ?? '1')), ['1', 'true', 'yes', 'on'], true);
    $heading_tag = nb_block_get_heading_tag((array) $props, 'heading', 'h2');
    $heading_weight = nb_block_get_font_weight((array) $props, 'heading', 800);
    $title_size_desktop = nb_faq_prop_int((array) $props, 'title_size_desktop', 48, 12, 160);
    $title_size_mobile = nb_faq_prop_int((array) $props, 'title_size_mobile', 32, 12, 160);
    $title_color = nb_block_css_color((string) ($props['title_color'] ?? ''));
    $title_line_height = nb_faq_prop_int((array) $props, 'title_line_height_percent', 110, 80, 220) / 100;
    $title_letter_spacing = nb_faq_prop_int((array) $props, 'title_letter_spacing', 0, -40, 80);
    $title_max_width = nb_faq_prop_int((array) $props, 'title_max_width', 600, 240, 1440);
    $subtitle_size_desktop = nb_faq_prop_int((array) $props, 'subtitle_size_desktop', 18, 10, 80);
    $subtitle_size_mobile = nb_faq_prop_int((array) $props, 'subtitle_size_mobile', 16, 10, 80);
    $subtitle_color = nb_block_css_color((string) ($props['subtitle_color'] ?? ''));
    $subtitle_line_height = nb_faq_prop_int((array) $props, 'subtitle_line_height_percent', 165, 80, 240) / 100;
    $subtitle_letter_spacing = nb_faq_prop_int((array) $props, 'subtitle_letter_spacing', 0, -40, 80);
    $subtitle_max_width = nb_faq_prop_int((array) $props, 'subtitle_max_width', 720, 240, 1440);
    $title_margin_bottom_desktop = nb_faq_prop_int((array) $props, 'title_margin_bottom_desktop', 0, 0, 240);
    $title_margin_bottom_mobile = nb_faq_prop_int((array) $props, 'title_margin_bottom_mobile', 0, 0, 240);
    $subtitle_margin_bottom_desktop = nb_faq_prop_int((array) $props, 'subtitle_margin_bottom_desktop', 32, 0, 240);
    $subtitle_margin_bottom_mobile = nb_faq_prop_int((array) $props, 'subtitle_margin_bottom_mobile', 24, 0, 240);
    $item_title_size_desktop = nb_faq_prop_int((array) $props, 'item_title_size_desktop', 18, 10, 80);
    $item_title_size_mobile = nb_faq_prop_int((array) $props, 'item_title_size_mobile', 17, 10, 80);
    $item_title_weight = nb_faq_prop_int((array) $props, 'item_title_weight', 700, 100, 900);
    $item_title_color = nb_block_css_color((string) ($props['item_title_color'] ?? ''));
    $item_title_line_height = nb_faq_prop_int((array) $props, 'item_title_line_height_percent', 135, 80, 220) / 100;
    $item_title_letter_spacing = nb_faq_prop_int((array) $props, 'item_title_letter_spacing', 0, -40, 80);
    $item_text_size_desktop = nb_faq_prop_int((array) $props, 'item_text_size_desktop', 16, 10, 80);
    $item_text_size_mobile = nb_faq_prop_int((array) $props, 'item_text_size_mobile', 15, 10, 80);
    $item_text_color = nb_block_css_color((string) ($props['item_text_color'] ?? ''));
    $item_text_line_height = nb_faq_prop_int((array) $props, 'item_text_line_height_percent', 170, 80, 260) / 100;
    $item_text_letter_spacing = nb_faq_prop_int((array) $props, 'item_text_letter_spacing', 0, -40, 80);
    $item_surface_variant = in_array($props['item_surface_variant'] ?? 'card', ['card', 'plain'], true) ? (string) $props['item_surface_variant'] : 'card';
    $content_width = nb_faq_prop_int((array) $props, 'content_width', 760, 320, 1440);
    $padding_top_desktop = nb_faq_prop_int((array) $props, 'padding_top_desktop', 88, 0, 300);
    $padding_bottom_desktop = nb_faq_prop_int((array) $props, 'padding_bottom_desktop', 88, 0, 300);
    $padding_top_mobile = nb_faq_prop_int((array) $props, 'padding_top_mobile', 56, 0, 300);
    $padding_bottom_mobile = nb_faq_prop_int((array) $props, 'padding_bottom_mobile', 56, 0, 300);
    $align = in_array($props['align'] ?? 'center', ['left', 'center'], true) ? (string) $props['align'] : 'center';
    $reveal = nb_block_get_reveal_settings((array) $props);

    $items_source = !empty($props['items']) && is_array($props['items']) ? $props['items'] : [];
}

$intro_html = $intro !== '' ? nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) : '';

$items = [];
if ($items_source) {
    foreach ($items_source as $item) {
        if (!is_array($item)) {
            continue;
        }

        $normalized_item = nb_faq_normalize_item($item);
        if (!$normalized_item) {
            continue;
        }

        $items[] = $normalized_item;
    }
}

$theme_attr = ($theme !== 'light') ? ' data-nb-theme="' . $theme . '"' : '';
$bg_class   = ($theme === 'alt' && $background_mode === 'theme') ? ' nb-section--alt' : '';
$bg_class  .= ($align === 'left') ? ' nb-faq--align-left' : ' nb-faq--align-center';
$bg_class  .= $reveal['class'];

$section_style = '--nb-faq-content-max-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-faq-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-mobile-padding-top:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-mobile-padding-bottom:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-title-line-height:' . max(0.8, min(2.2, $title_line_height)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-faq-title-letter-spacing:' . $title_letter_spacing . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-title-max-width:' . $title_max_width . 'px;');
$section_style = $title_color !== '' ? nb_block_append_style($section_style, '--nb-faq-title-color:' . $title_color . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-faq-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-subtitle-line-height:' . max(0.8, min(2.4, $subtitle_line_height)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-faq-subtitle-letter-spacing:' . $subtitle_letter_spacing . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-subtitle-max-width:' . $subtitle_max_width . 'px;');
$section_style = $subtitle_color !== '' ? nb_block_append_style($section_style, '--nb-faq-subtitle-color:' . $subtitle_color . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-section-title-margin-bottom:' . $title_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-section-title-margin-bottom-mobile:' . $title_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-subtitle-margin-top:' . ($title_visible ? 'calc(var(--nb-space-sm, .75rem) * -1)' : '0px') . ';');
$section_style = nb_block_append_style($section_style, '--nb-faq-subtitle-margin-bottom:' . $subtitle_margin_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-subtitle-margin-bottom-mobile:' . $subtitle_margin_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-item-title-size:' . $item_title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-item-title-size-mobile:' . $item_title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-item-title-weight:' . $item_title_weight . ';');
$section_style = nb_block_append_style($section_style, '--nb-faq-item-title-line-height:' . max(0.8, min(2.2, $item_title_line_height)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-faq-item-title-letter-spacing:' . $item_title_letter_spacing . 'px;');
$section_style = $item_title_color !== '' ? nb_block_append_style($section_style, '--nb-faq-item-title-color:' . $item_title_color . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-faq-item-text-size:' . $item_text_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-item-text-size-mobile:' . $item_text_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-faq-item-text-line-height:' . max(0.8, min(2.6, $item_text_line_height)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-faq-item-text-letter-spacing:' . $item_text_letter_spacing . 'px;');
$section_style = $item_text_color !== '' ? nb_block_append_style($section_style, '--nb-faq-item-text-color:' . $item_text_color . ';') : $section_style;
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style']);
$item_class = $item_surface_variant === 'plain' ? 'nb-faq__item nb-faq__item--plain' : 'nb-faq__item nb-card';
?>
<section
    class="nb-section nb-faq<?= $bg_class ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-entity="section"
    <?= $theme_attr ?>
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container">
        <?php if ($eyebrow || ($title_visible && $heading)): ?>
        <div class="nb-section-header">
            <?php if ($eyebrow): ?>
            <p class="nb-eyebrow" data-nb-entity="eyebrow"><?= $eyebrow ?></p>
            <?php endif; ?>
            <?php if ($title_visible && $heading): ?>
            <<?= $heading_tag ?> class="nb-section-title" style="font-weight:<?= (int) $heading_weight ?>" data-nb-entity="title"><?= $heading ?></<?= $heading_tag ?>>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($subtitle_visible && $intro_html): ?>
        <div class="nb-faq__intro" data-nb-entity="subtitle"><?= $intro_html ?></div>
        <?php endif; ?>

        <?php if ($items): ?>
        <div class="nb-faq__list" data-nb-entity="items">
            <?php foreach ($items as $index => $item): ?>
            <details class="<?= $item_class ?>" data-nb-entity="itemSurface" <?= ($open_first && $index === 0) ? 'open' : '' ?>>
                <summary class="nb-faq__question">
                    <span data-nb-entity="itemTitle"><?= $item['title'] ?></span>
                    <span class="nb-faq__icon" aria-hidden="true"></span>
                </summary>
                <?php if ($item['text']): ?>
                <div class="nb-faq__answer" data-nb-entity="itemText">
                    <p><?= $item['text'] ?></p>
                </div>
                <?php endif; ?>
            </details>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>