<?php

require_once dirname(__DIR__) . '/render_helpers.php';

$headline_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'headline_feed'))
    ? $block_contract
    : null;

if (!function_exists('nb_headline_feed_prop_int')) {
    function nb_headline_feed_prop_int(array $props, $key, $default, $min, $max) {
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

if (!function_exists('nb_headline_feed_visible')) {
    function nb_headline_feed_visible($value, $default = true) {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('nb_headline_feed_normalize_item')) {
    function nb_headline_feed_normalize_item(array $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $excerpt = trim((string) ($item['excerpt'] ?? ($item['text'] ?? '')));
        $category = trim((string) ($item['category'] ?? ''));
        $link_label = trim((string) ($item['linkLabel'] ?? ($item['link_label'] ?? '')));
        $url = trim((string) ($item['url'] ?? ''));
        $date = trim((string) ($item['date'] ?? ''));
        $views = trim((string) ($item['views'] ?? ''));
        $comments = trim((string) ($item['comments'] ?? ''));
        $media = nb_block_extract_media($item['image'] ?? '', $item['imageAlt'] ?? ($item['alt'] ?? $title));

        if ($title === '' && $excerpt === '' && $category === '' && $url === '' && $media['display'] === '' && $media['original'] === '') {
            return null;
        }

        return [
            'category' => htmlspecialchars($category, ENT_QUOTES, 'UTF-8'),
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'excerpt' => nl2br(htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8')),
            'linkLabel' => htmlspecialchars($link_label, ENT_QUOTES, 'UTF-8'),
            'url' => htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            'date' => htmlspecialchars($date, ENT_QUOTES, 'UTF-8'),
            'views' => htmlspecialchars($views, ENT_QUOTES, 'UTF-8'),
            'comments' => htmlspecialchars($comments, ENT_QUOTES, 'UTF-8'),
            'image' => htmlspecialchars((string) ($media['display'] ?: $media['original']), ENT_QUOTES, 'UTF-8'),
            'imageAlt' => htmlspecialchars((string) ($media['alt'] ?: $title), ENT_QUOTES, 'UTF-8'),
        ];
    }
}

if (!function_exists('nb_headline_feed_card_link_label')) {
    function nb_headline_feed_card_link_label(array $item) {
        if (($item['url'] ?? '') === '') {
            return '';
        }

        $label = trim((string) ($item['linkLabel'] ?? ''));

        return $label !== '' ? $label : 'Подробнее';
    }
}

$props = (array) ($props ?? []);

if ($headline_contract) {
    $theme = in_array($headline_contract['design']['section']['theme'] ?? 'light', ['light', 'alt', 'dark'], true)
        ? (string) $headline_contract['design']['section']['theme'] : 'light';
    $align = in_array($headline_contract['layout']['desktop']['align'] ?? 'left', ['left', 'center'], true)
        ? (string) $headline_contract['layout']['desktop']['align'] : 'left';
    $preset = in_array($headline_contract['layout']['preset'] ?? 'split', ['split', 'stack', 'cover'], true)
        ? (string) $headline_contract['layout']['preset'] : 'split';
    $background_style = nb_block_build_background_style((array) ($headline_contract['design']['section']['background'] ?? []));
    $reveal = nb_block_get_reveal_settings([
        'block_animation' => (string) ($headline_contract['runtime']['animation']['name'] ?? 'none'),
        'block_animation_delay' => (int) ($headline_contract['runtime']['animation']['delay'] ?? 0),
    ]);

    $heading = trim((string) ($headline_contract['content']['title'] ?? 'Главная статья и лента'));
    $intro = trim((string) ($headline_contract['content']['subtitle'] ?? ''));
    $more_label = trim((string) ($headline_contract['content']['primaryButton']['label'] ?? 'Смотреть все материалы'));
    $more_url = trim((string) ($headline_contract['content']['primaryButton']['url'] ?? '#'));
    $items_source = is_array($headline_contract['content']['items'] ?? null) ? $headline_contract['content']['items'] : [];

    $title_entity = (array) ($headline_contract['design']['entities']['title'] ?? []);
    $subtitle_entity = (array) ($headline_contract['design']['entities']['subtitle'] ?? []);
    $meta_entity = (array) ($headline_contract['design']['entities']['meta'] ?? []);
    $item_title_entity = (array) ($headline_contract['design']['entities']['itemTitle'] ?? []);
    $item_text_entity = (array) ($headline_contract['design']['entities']['itemText'] ?? []);
    $item_link_entity = (array) ($headline_contract['design']['entities']['itemLink'] ?? []);
    $media_entity = (array) ($headline_contract['design']['entities']['media'] ?? []);
    $surface_entity = (array) ($headline_contract['design']['entities']['itemSurface'] ?? []);

    $title_visible = !array_key_exists('visible', $title_entity) || !empty($title_entity['visible']);
    $subtitle_visible = !array_key_exists('visible', $subtitle_entity) || !empty($subtitle_entity['visible']);
    $show_more_link = !array_key_exists('moreLink', (array) ($headline_contract['runtime']['visibility'] ?? [])) || !empty($headline_contract['runtime']['visibility']['moreLink']);
    $show_image = !array_key_exists('image', (array) ($headline_contract['runtime']['visibility'] ?? [])) || !empty($headline_contract['runtime']['visibility']['image']);
    $show_category = !array_key_exists('category', (array) ($headline_contract['runtime']['visibility'] ?? [])) || !empty($headline_contract['runtime']['visibility']['category']);
    $show_excerpt = !array_key_exists('excerpt', (array) ($headline_contract['runtime']['visibility'] ?? [])) || !empty($headline_contract['runtime']['visibility']['excerpt']);
    $show_date = !array_key_exists('date', (array) ($headline_contract['runtime']['visibility'] ?? [])) || !empty($headline_contract['runtime']['visibility']['date']);
    $show_views = !array_key_exists('views', (array) ($headline_contract['runtime']['visibility'] ?? [])) || !empty($headline_contract['runtime']['visibility']['views']);
    $show_comments = !array_key_exists('comments', (array) ($headline_contract['runtime']['visibility'] ?? [])) || !empty($headline_contract['runtime']['visibility']['comments']);

    $heading_tag = htmlspecialchars((string) ($title_entity['tag'] ?? 'h2'), ENT_QUOTES, 'UTF-8');
    $content_width = (int) ($headline_contract['layout']['desktop']['contentWidth'] ?? 1140);
    $padding_top_desktop = (int) ($headline_contract['layout']['desktop']['paddingTop'] ?? 64);
    $padding_bottom_desktop = (int) ($headline_contract['layout']['desktop']['paddingBottom'] ?? 64);
    $padding_top_mobile = (int) ($headline_contract['layout']['mobile']['paddingTop'] ?? 44);
    $padding_bottom_mobile = (int) ($headline_contract['layout']['mobile']['paddingBottom'] ?? 44);
    $columns_desktop = (int) ($headline_contract['layout']['desktop']['columns'] ?? 3);
    $columns_mobile = (int) ($headline_contract['layout']['mobile']['columns'] ?? 1);
    $card_gap_desktop = (int) ($headline_contract['layout']['desktop']['cardGap'] ?? 18);
    $card_gap_mobile = (int) ($headline_contract['layout']['mobile']['cardGap'] ?? 16);
    $header_gap_desktop = (int) ($headline_contract['layout']['desktop']['headerGap'] ?? 18);
    $header_gap_mobile = (int) ($headline_contract['layout']['mobile']['headerGap'] ?? 14);
    $title_size_desktop = (int) (($title_entity['desktop']['fontSize'] ?? 32));
    $title_size_mobile = (int) (($title_entity['mobile']['fontSize'] ?? 24));
    $subtitle_size_desktop = (int) (($subtitle_entity['desktop']['fontSize'] ?? 16));
    $subtitle_size_mobile = (int) (($subtitle_entity['mobile']['fontSize'] ?? 14));
    $meta_size_desktop = (int) (($meta_entity['desktop']['fontSize'] ?? 12));
    $meta_size_mobile = (int) (($meta_entity['mobile']['fontSize'] ?? 12));
    $item_title_size_desktop = (int) (($item_title_entity['desktop']['fontSize'] ?? 17));
    $item_title_size_mobile = (int) (($item_title_entity['mobile']['fontSize'] ?? 16));
    $item_text_size_desktop = (int) (($item_text_entity['desktop']['fontSize'] ?? 14));
    $item_text_size_mobile = (int) (($item_text_entity['mobile']['fontSize'] ?? 14));
    $item_link_size_desktop = (int) (($item_link_entity['desktop']['fontSize'] ?? 12));
    $item_link_size_mobile = (int) (($item_link_entity['mobile']['fontSize'] ?? $item_link_size_desktop));
    $item_link_weight_desktop = (int) (($item_link_entity['desktop']['weight'] ?? ($item_link_entity['weight'] ?? 700)));
    $item_link_weight_mobile = (int) (($item_link_entity['mobile']['weight'] ?? ($item_link_entity['weight'] ?? $item_link_weight_desktop)));
    $item_link_color_desktop = nb_block_css_color((string) ($item_link_entity['desktop']['color'] ?? ($item_link_entity['color'] ?? '')));
    $item_link_color_mobile = nb_block_css_color((string) ($item_link_entity['mobile']['color'] ?? ($item_link_entity['color'] ?? $item_link_color_desktop)));
    $item_link_line_height_desktop = ((float) ($item_link_entity['desktop']['lineHeightPercent'] ?? ($item_link_entity['lineHeightPercent'] ?? 120))) / 100;
    $item_link_line_height_mobile = ((float) ($item_link_entity['mobile']['lineHeightPercent'] ?? ($item_link_entity['lineHeightPercent'] ?? ($item_link_line_height_desktop * 100)))) / 100;
    $item_link_letter_spacing_desktop = (float) ($item_link_entity['desktop']['letterSpacing'] ?? ($item_link_entity['letterSpacing'] ?? 1));
    $item_link_letter_spacing_mobile = (float) ($item_link_entity['mobile']['letterSpacing'] ?? ($item_link_entity['letterSpacing'] ?? $item_link_letter_spacing_desktop));
    $media_radius = (int) ($media_entity['radius'] ?? 22);
    $media_aspect_ratio = (string) ($media_entity['aspectRatio'] ?? '4:3');
    $media_object_fit = (string) ($media_entity['objectFit'] ?? 'cover');
    $item_surface_variant = in_array($surface_entity['variant'] ?? 'card', ['card', 'plain'], true)
        ? (string) ($surface_entity['variant'] ?? 'card') : 'card';
    $item_surface_radius = (int) ($surface_entity['radius'] ?? 22);
    $item_surface_border_width = (int) ($surface_entity['borderWidth'] ?? 1);
    $item_surface_border_color = nb_block_css_color((string) ($surface_entity['borderColor'] ?? '#dbe4ef'), '#dbe4ef');
    $item_surface_shadow = in_array($surface_entity['shadow'] ?? 'md', ['none', 'sm', 'md', 'lg'], true)
        ? (string) ($surface_entity['shadow'] ?? 'md') : 'md';
} else {
    $theme = in_array($props['theme'] ?? 'light', ['light', 'alt', 'dark'], true) ? (string) ($props['theme'] ?? 'light') : 'light';
    $align = in_array($props['align'] ?? 'left', ['left', 'center'], true) ? (string) ($props['align'] ?? 'left') : 'left';
    $preset = in_array($props['layout_preset'] ?? 'split', ['split', 'stack', 'cover'], true) ? (string) ($props['layout_preset'] ?? 'split') : 'split';
    $background_style = nb_block_build_background_style([
        'mode' => $props['background_mode'] ?? 'theme',
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
    $reveal = nb_block_get_reveal_settings($props);

    $heading = trim((string) ($props['heading'] ?? 'Главная статья и лента'));
    $intro = trim((string) ($props['intro'] ?? ''));
    $more_label = trim((string) ($props['more_link_label'] ?? 'Смотреть все материалы'));
    $more_url = trim((string) ($props['more_link_url'] ?? '#'));
    $items_source = is_array($props['items'] ?? null) ? $props['items'] : [];

    $title_visible = nb_headline_feed_visible($props['title_visible'] ?? '1', true);
    $subtitle_visible = nb_headline_feed_visible($props['subtitle_visible'] ?? '1', true);
    $show_more_link = nb_headline_feed_visible($props['show_more_link'] ?? '1', true);
    $show_image = nb_headline_feed_visible($props['show_image'] ?? '1', true);
    $show_category = nb_headline_feed_visible($props['show_category'] ?? '1', true);
    $show_excerpt = nb_headline_feed_visible($props['show_excerpt'] ?? '1', true);
    $show_date = nb_headline_feed_visible($props['show_date'] ?? '1', true);
    $show_views = nb_headline_feed_visible($props['show_views'] ?? '1', true);
    $show_comments = nb_headline_feed_visible($props['show_comments'] ?? '1', true);

    $heading_tag = nb_block_get_heading_tag($props, 'heading', 'h2');
    $content_width = nb_headline_feed_prop_int($props, 'content_width', 1140, 320, 1600);
    $padding_top_desktop = nb_headline_feed_prop_int($props, 'padding_top_desktop', 64, 0, 300);
    $padding_bottom_desktop = nb_headline_feed_prop_int($props, 'padding_bottom_desktop', 64, 0, 300);
    $padding_top_mobile = nb_headline_feed_prop_int($props, 'padding_top_mobile', 44, 0, 300);
    $padding_bottom_mobile = nb_headline_feed_prop_int($props, 'padding_bottom_mobile', 44, 0, 300);
    $columns_desktop = nb_headline_feed_prop_int($props, 'columns_desktop', 3, 1, 4);
    $columns_mobile = nb_headline_feed_prop_int($props, 'columns_mobile', 1, 1, 2);
    $card_gap_desktop = nb_headline_feed_prop_int($props, 'card_gap_desktop', 18, 0, 120);
    $card_gap_mobile = nb_headline_feed_prop_int($props, 'card_gap_mobile', 16, 0, 120);
    $header_gap_desktop = nb_headline_feed_prop_int($props, 'header_gap_desktop', 18, 0, 120);
    $header_gap_mobile = nb_headline_feed_prop_int($props, 'header_gap_mobile', 14, 0, 120);
    $title_size_desktop = nb_headline_feed_prop_int($props, 'title_size_desktop', 32, 12, 160);
    $title_size_mobile = nb_headline_feed_prop_int($props, 'title_size_mobile', 24, 12, 160);
    $subtitle_size_desktop = nb_headline_feed_prop_int($props, 'subtitle_size_desktop', 16, 10, 80);
    $subtitle_size_mobile = nb_headline_feed_prop_int($props, 'subtitle_size_mobile', 14, 10, 80);
    $meta_size_desktop = nb_headline_feed_prop_int($props, 'meta_size_desktop', 12, 10, 120);
    $meta_size_mobile = nb_headline_feed_prop_int($props, 'meta_size_mobile', 12, 10, 120);
    $item_title_size_desktop = nb_headline_feed_prop_int($props, 'item_title_size_desktop', 17, 10, 80);
    $item_title_size_mobile = nb_headline_feed_prop_int($props, 'item_title_size_mobile', 16, 10, 80);
    $item_text_size_desktop = nb_headline_feed_prop_int($props, 'item_text_size_desktop', 14, 10, 80);
    $item_text_size_mobile = nb_headline_feed_prop_int($props, 'item_text_size_mobile', 14, 10, 80);
    $item_link_size_desktop = 12;
    $item_link_size_mobile = 12;
    $item_link_weight_desktop = 700;
    $item_link_weight_mobile = 700;
    $item_link_color_desktop = '';
    $item_link_color_mobile = '';
    $item_link_line_height_desktop = 1.2;
    $item_link_line_height_mobile = 1.2;
    $item_link_letter_spacing_desktop = 1;
    $item_link_letter_spacing_mobile = 1;
    $media_radius = nb_headline_feed_prop_int($props, 'media_radius', 22, 0, 80);
    $media_aspect_ratio = (string) ($props['media_aspect_ratio'] ?? '4:3');
    $media_object_fit = (string) ($props['media_object_fit'] ?? 'cover');
    $item_surface_variant = in_array($props['item_surface_variant'] ?? 'card', ['card', 'plain'], true) ? (string) ($props['item_surface_variant'] ?? 'card') : 'card';
    $item_surface_radius = nb_headline_feed_prop_int($props, 'item_surface_radius', 22, 0, 100);
    $item_surface_border_width = nb_headline_feed_prop_int($props, 'item_surface_border_width', 1, 0, 20);
    $item_surface_border_color = nb_block_css_color((string) ($props['item_surface_border_color'] ?? '#dbe4ef'), '#dbe4ef');
    $item_surface_shadow = in_array($props['item_surface_shadow'] ?? 'md', ['none', 'sm', 'md', 'lg'], true) ? (string) ($props['item_surface_shadow'] ?? 'md') : 'md';
}

$items = [];
foreach ($items_source as $item) {
    if (!is_array($item)) {
        continue;
    }
    $normalized_item = nb_headline_feed_normalize_item($item);
    if ($normalized_item) {
        $items[] = $normalized_item;
    }
}

$lead_item = $items ? array_shift($items) : null;
$rail_items = $items;
$split_feature_item = null;
$split_top_items = [];
$split_bottom_items = [];
$split_layout_modifier = '';

if ($preset === 'split' && $rail_items) {
    $split_pool = $rail_items;
    if (count($split_pool) >= 3) {
        $split_feature_item = array_pop($split_pool);
    }

    $split_top_items = array_slice($split_pool, 0, 2);
    $split_bottom_items = array_slice($split_pool, 2);

    if ($split_feature_item && !$split_bottom_items) {
        $split_layout_modifier = ' nb-headline-feed__layout--split-minimal';
    }
}

$heading_html = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
$intro_html = $intro !== '' ? nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')) : '';
$more_label_html = htmlspecialchars($more_label, ENT_QUOTES, 'UTF-8');
$more_url_html = htmlspecialchars($more_url, ENT_QUOTES, 'UTF-8');

$theme_attr = ($theme !== 'light') ? ' data-nb-theme="' . htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') . '"' : '';
$section_class = 'nb-section nb-content-feed nb-headline-feed nb-headline-feed--preset-' . $preset . ' nb-content-feed--align-' . $align . ' nb-content-feed--' . $item_surface_variant . $reveal['class'];
$section_style = '--nb-feed-content-width:' . $content_width . 'px;';
$section_style = nb_block_append_style($section_style, '--nb-feed-padding-top:' . $padding_top_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-padding-bottom:' . $padding_bottom_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-mobile-padding-top:' . $padding_top_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-mobile-padding-bottom:' . $padding_bottom_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-columns:' . $columns_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-columns-mobile:' . $columns_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-gap:' . $card_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-gap-mobile:' . $card_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-header-gap:' . $header_gap_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-header-gap-mobile:' . $header_gap_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-size:' . $title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-title-size-mobile:' . $title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-size:' . $subtitle_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-subtitle-size-mobile:' . $subtitle_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-size:' . $meta_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-meta-size-mobile:' . $meta_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-size:' . $item_title_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-title-size-mobile:' . $item_title_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-size:' . $item_text_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-text-size-mobile:' . $item_text_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-size:' . $item_link_size_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-size-mobile:' . $item_link_size_mobile . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-weight:' . $item_link_weight_desktop . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-weight-mobile:' . $item_link_weight_mobile . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-line-height:' . max(0.8, min(2.2, $item_link_line_height_desktop)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-line-height-mobile:' . max(0.8, min(2.2, $item_link_line_height_mobile)) . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-letter-spacing:' . $item_link_letter_spacing_desktop . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-item-link-letter-spacing-mobile:' . $item_link_letter_spacing_mobile . 'px;');
$section_style = $item_link_color_desktop !== '' ? nb_block_append_style($section_style, '--nb-feed-item-link-color:' . $item_link_color_desktop . ';') : $section_style;
$section_style = $item_link_color_mobile !== '' ? nb_block_append_style($section_style, '--nb-feed-item-link-color-mobile:' . $item_link_color_mobile . ';') : $section_style;
$section_style = nb_block_append_style($section_style, '--nb-feed-media-aspect-ratio:' . $media_aspect_ratio . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-media-object-fit:' . $media_object_fit . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-media-radius:' . $media_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-radius:' . $item_surface_radius . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-border-width:' . $item_surface_border_width . 'px;');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-border-color:' . $item_surface_border_color . ';');
$section_style = nb_block_append_style($section_style, '--nb-feed-card-shadow:' . 'var(--nb-shadow-' . $item_surface_shadow . ', none);');
$section_style = nb_block_append_style($section_style, $background_style);
$section_style = nb_block_append_style($section_style, $reveal['style']);
?>
<section
    class="<?= htmlspecialchars($section_class, ENT_QUOTES, 'UTF-8') ?>"
    id="block-<?= htmlspecialchars($block_uid, ENT_QUOTES, 'UTF-8') ?>"
    data-nb-entity="section"
    <?= $theme_attr ?>
    <?= $section_style ? ' style="' . htmlspecialchars($section_style, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
>
    <div class="nb-container nb-content-feed__container">
        <?php if (($title_visible && $heading_html !== '') || ($subtitle_visible && $intro_html !== '') || ($show_more_link && $more_label_html !== '' && $more_url_html !== '' && $more_url_html !== '#')): ?>
        <div class="nb-content-feed__header">
            <div class="nb-content-feed__copy">
                <?php if ($title_visible && $heading_html !== ''): ?>
                <<?= $heading_tag ?> class="nb-content-feed__title" data-nb-entity="title"><?= $heading_html ?></<?= $heading_tag ?>>
                <?php endif; ?>
                <?php if ($subtitle_visible && $intro_html !== ''): ?>
                <div class="nb-content-feed__subtitle" data-nb-entity="subtitle"><?= $intro_html ?></div>
                <?php endif; ?>
            </div>
            <?php if ($show_more_link && $more_label_html !== '' && $more_url_html !== '' && $more_url_html !== '#'): ?>
            <a class="nb-content-feed__more" href="<?= $more_url_html ?>" data-nb-entity="primaryButton"><?= $more_label_html ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($lead_item): ?>
        <div class="nb-headline-feed__layout<?= htmlspecialchars($preset === 'split' ? $split_layout_modifier : '', ENT_QUOTES, 'UTF-8') ?>" data-nb-entity="items">
            <?php if ($preset === 'split'): ?>
            <article class="nb-content-feed__card nb-headline-feed__lead nb-card" data-nb-entity="itemSurface">
                <?php if ($show_image && $lead_item['image'] !== ''): ?>
                <a class="nb-content-feed__media nb-headline-feed__lead-media" href="<?= $lead_item['url'] !== '' ? $lead_item['url'] : '#' ?>"<?= $lead_item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                    <img class="nb-content-feed__image" src="<?= $lead_item['image'] ?>" alt="<?= $lead_item['imageAlt'] ?>">
                </a>
                <?php endif; ?>
                <div class="nb-content-feed__body nb-headline-feed__lead-body">
                    <?php if ($show_category && $lead_item['category'] !== ''): ?>
                    <div class="nb-content-feed__category" data-nb-entity="meta"><?= $lead_item['category'] ?></div>
                    <?php endif; ?>
                    <?php if ($lead_item['title'] !== ''): ?>
                    <h3 class="nb-content-feed__card-title nb-headline-feed__lead-title" data-nb-entity="itemTitle">
                        <?php if ($lead_item['url'] !== ''): ?>
                        <a href="<?= $lead_item['url'] ?>"><?= $lead_item['title'] ?></a>
                        <?php else: ?>
                        <?= $lead_item['title'] ?>
                        <?php endif; ?>
                    </h3>
                    <?php endif; ?>
                    <?php if ($show_excerpt && $lead_item['excerpt'] !== ''): ?>
                    <div class="nb-content-feed__excerpt nb-headline-feed__lead-excerpt" data-nb-entity="itemText"><?= $lead_item['excerpt'] ?></div>
                    <?php endif; ?>
                    <?php $card_link_label = nb_headline_feed_card_link_label($lead_item); ?>
                    <?php if ($card_link_label !== ''): ?>
                    <a class="nb-content-feed__item-link" href="<?= $lead_item['url'] ?>" data-nb-entity="itemLink"><?= $card_link_label ?></a>
                    <?php endif; ?>
                    <?php if (($show_date && $lead_item['date'] !== '') || ($show_views && $lead_item['views'] !== '') || ($show_comments && $lead_item['comments'] !== '')): ?>
                    <div class="nb-content-feed__meta" data-nb-entity="meta">
                        <?php if ($show_date && $lead_item['date'] !== ''): ?><span><?= $lead_item['date'] ?></span><?php endif; ?>
                        <?php if ($show_views && $lead_item['views'] !== ''): ?><span><?= $lead_item['views'] ?> просмотров</span><?php endif; ?>
                        <?php if ($show_comments && $lead_item['comments'] !== ''): ?><span><?= $lead_item['comments'] ?> комментариев</span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>

            <?php if ($split_top_items): ?>
            <div class="nb-headline-feed__cluster nb-headline-feed__cluster--top">
                <?php foreach ($split_top_items as $item): ?>
                <article class="nb-content-feed__card nb-headline-feed__minor-card nb-card" data-nb-entity="itemSurface">
                    <?php if ($show_image && $item['image'] !== ''): ?>
                    <a class="nb-content-feed__media" href="<?= $item['url'] !== '' ? $item['url'] : '#' ?>"<?= $item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                        <img class="nb-content-feed__image" src="<?= $item['image'] ?>" alt="<?= $item['imageAlt'] ?>">
                    </a>
                    <?php endif; ?>
                    <div class="nb-content-feed__body">
                        <?php if ($show_category && $item['category'] !== ''): ?>
                        <div class="nb-content-feed__category" data-nb-entity="meta"><?= $item['category'] ?></div>
                        <?php endif; ?>
                        <?php if ($item['title'] !== ''): ?>
                        <h3 class="nb-content-feed__card-title" data-nb-entity="itemTitle">
                            <?php if ($item['url'] !== ''): ?>
                            <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                            <?php else: ?>
                            <?= $item['title'] ?>
                            <?php endif; ?>
                        </h3>
                        <?php endif; ?>
                        <?php if ($show_excerpt && $item['excerpt'] !== ''): ?>
                        <div class="nb-content-feed__excerpt" data-nb-entity="itemText"><?= $item['excerpt'] ?></div>
                        <?php endif; ?>
                        <?php $card_link_label = nb_headline_feed_card_link_label($item); ?>
                        <?php if ($card_link_label !== ''): ?>
                        <a class="nb-content-feed__item-link" href="<?= $item['url'] ?>" data-nb-entity="itemLink"><?= $card_link_label ?></a>
                        <?php endif; ?>
                        <?php if (($show_date && $item['date'] !== '') || ($show_views && $item['views'] !== '') || ($show_comments && $item['comments'] !== '')): ?>
                        <div class="nb-content-feed__meta" data-nb-entity="meta">
                            <?php if ($show_date && $item['date'] !== ''): ?><span><?= $item['date'] ?></span><?php endif; ?>
                            <?php if ($show_views && $item['views'] !== ''): ?><span><?= $item['views'] ?> просмотров</span><?php endif; ?>
                            <?php if ($show_comments && $item['comments'] !== ''): ?><span><?= $item['comments'] ?> комментариев</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($split_bottom_items): ?>
            <div class="nb-headline-feed__cluster nb-headline-feed__cluster--bottom">
                <?php foreach ($split_bottom_items as $item): ?>
                <article class="nb-content-feed__card nb-headline-feed__minor-card nb-card" data-nb-entity="itemSurface">
                    <?php if ($show_image && $item['image'] !== ''): ?>
                    <a class="nb-content-feed__media" href="<?= $item['url'] !== '' ? $item['url'] : '#' ?>"<?= $item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                        <img class="nb-content-feed__image" src="<?= $item['image'] ?>" alt="<?= $item['imageAlt'] ?>">
                    </a>
                    <?php endif; ?>
                    <div class="nb-content-feed__body">
                        <?php if ($show_category && $item['category'] !== ''): ?>
                        <div class="nb-content-feed__category" data-nb-entity="meta"><?= $item['category'] ?></div>
                        <?php endif; ?>
                        <?php if ($item['title'] !== ''): ?>
                        <h3 class="nb-content-feed__card-title" data-nb-entity="itemTitle">
                            <?php if ($item['url'] !== ''): ?>
                            <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                            <?php else: ?>
                            <?= $item['title'] ?>
                            <?php endif; ?>
                        </h3>
                        <?php endif; ?>
                        <?php if ($show_excerpt && $item['excerpt'] !== ''): ?>
                        <div class="nb-content-feed__excerpt" data-nb-entity="itemText"><?= $item['excerpt'] ?></div>
                        <?php endif; ?>
                        <?php $card_link_label = nb_headline_feed_card_link_label($item); ?>
                        <?php if ($card_link_label !== ''): ?>
                        <a class="nb-content-feed__item-link" href="<?= $item['url'] ?>" data-nb-entity="itemLink"><?= $card_link_label ?></a>
                        <?php endif; ?>
                        <?php if (($show_date && $item['date'] !== '') || ($show_views && $item['views'] !== '') || ($show_comments && $item['comments'] !== '')): ?>
                        <div class="nb-content-feed__meta" data-nb-entity="meta">
                            <?php if ($show_date && $item['date'] !== ''): ?><span><?= $item['date'] ?></span><?php endif; ?>
                            <?php if ($show_views && $item['views'] !== ''): ?><span><?= $item['views'] ?> просмотров</span><?php endif; ?>
                            <?php if ($show_comments && $item['comments'] !== ''): ?><span><?= $item['comments'] ?> комментариев</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($split_feature_item): ?>
            <article class="nb-content-feed__card nb-headline-feed__feature nb-card" data-nb-entity="itemSurface">
                <?php if ($show_image && $split_feature_item['image'] !== ''): ?>
                <a class="nb-content-feed__media nb-headline-feed__feature-media" href="<?= $split_feature_item['url'] !== '' ? $split_feature_item['url'] : '#' ?>"<?= $split_feature_item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                    <img class="nb-content-feed__image" src="<?= $split_feature_item['image'] ?>" alt="<?= $split_feature_item['imageAlt'] ?>">
                </a>
                <?php endif; ?>
                <div class="nb-content-feed__body nb-headline-feed__feature-body">
                    <?php if ($show_category && $split_feature_item['category'] !== ''): ?>
                    <div class="nb-content-feed__category" data-nb-entity="meta"><?= $split_feature_item['category'] ?></div>
                    <?php endif; ?>
                    <?php if ($split_feature_item['title'] !== ''): ?>
                    <h3 class="nb-content-feed__card-title nb-headline-feed__feature-title" data-nb-entity="itemTitle">
                        <?php if ($split_feature_item['url'] !== ''): ?>
                        <a href="<?= $split_feature_item['url'] ?>"><?= $split_feature_item['title'] ?></a>
                        <?php else: ?>
                        <?= $split_feature_item['title'] ?>
                        <?php endif; ?>
                    </h3>
                    <?php endif; ?>
                    <?php $card_link_label = nb_headline_feed_card_link_label($split_feature_item); ?>
                    <?php if ($card_link_label !== ''): ?>
                    <a class="nb-content-feed__item-link" href="<?= $split_feature_item['url'] ?>" data-nb-entity="itemLink"><?= $card_link_label ?></a>
                    <?php endif; ?>
                    <?php if (($show_date && $split_feature_item['date'] !== '') || ($show_views && $split_feature_item['views'] !== '') || ($show_comments && $split_feature_item['comments'] !== '')): ?>
                    <div class="nb-content-feed__meta" data-nb-entity="meta">
                        <?php if ($show_date && $split_feature_item['date'] !== ''): ?><span><?= $split_feature_item['date'] ?></span><?php endif; ?>
                        <?php if ($show_views && $split_feature_item['views'] !== ''): ?><span><?= $split_feature_item['views'] ?> просмотров</span><?php endif; ?>
                        <?php if ($show_comments && $split_feature_item['comments'] !== ''): ?><span><?= $split_feature_item['comments'] ?> комментариев</span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endif; ?>
            <?php else: ?>
            <article class="nb-content-feed__card nb-headline-feed__lead nb-card" data-nb-entity="itemSurface">
                <?php if ($show_image && $lead_item['image'] !== ''): ?>
                <a class="nb-content-feed__media nb-headline-feed__lead-media" href="<?= $lead_item['url'] !== '' ? $lead_item['url'] : '#' ?>"<?= $lead_item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                    <img class="nb-content-feed__image" src="<?= $lead_item['image'] ?>" alt="<?= $lead_item['imageAlt'] ?>">
                </a>
                <?php endif; ?>
                <div class="nb-content-feed__body nb-headline-feed__lead-body">
                    <?php if ($show_category && $lead_item['category'] !== ''): ?>
                    <div class="nb-content-feed__category" data-nb-entity="meta"><?= $lead_item['category'] ?></div>
                    <?php endif; ?>
                    <?php if ($lead_item['title'] !== ''): ?>
                    <h3 class="nb-content-feed__card-title nb-headline-feed__lead-title" data-nb-entity="itemTitle">
                        <?php if ($lead_item['url'] !== ''): ?>
                        <a href="<?= $lead_item['url'] ?>"><?= $lead_item['title'] ?></a>
                        <?php else: ?>
                        <?= $lead_item['title'] ?>
                        <?php endif; ?>
                    </h3>
                    <?php endif; ?>
                    <?php if ($show_excerpt && $lead_item['excerpt'] !== ''): ?>
                    <div class="nb-content-feed__excerpt nb-headline-feed__lead-excerpt" data-nb-entity="itemText"><?= $lead_item['excerpt'] ?></div>
                    <?php endif; ?>
                    <?php $card_link_label = nb_headline_feed_card_link_label($lead_item); ?>
                    <?php if ($card_link_label !== ''): ?>
                    <a class="nb-content-feed__item-link" href="<?= $lead_item['url'] ?>" data-nb-entity="itemLink"><?= $card_link_label ?></a>
                    <?php endif; ?>
                    <?php if (($show_date && $lead_item['date'] !== '') || ($show_views && $lead_item['views'] !== '') || ($show_comments && $lead_item['comments'] !== '')): ?>
                    <div class="nb-content-feed__meta" data-nb-entity="meta">
                        <?php if ($show_date && $lead_item['date'] !== ''): ?><span><?= $lead_item['date'] ?></span><?php endif; ?>
                        <?php if ($show_views && $lead_item['views'] !== ''): ?><span><?= $lead_item['views'] ?> просмотров</span><?php endif; ?>
                        <?php if ($show_comments && $lead_item['comments'] !== ''): ?><span><?= $lead_item['comments'] ?> комментариев</span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>

            <?php if ($rail_items): ?>
            <div class="nb-headline-feed__rail">
                <?php foreach ($rail_items as $item): ?>
                <article class="nb-content-feed__card nb-headline-feed__rail-card nb-card" data-nb-entity="itemSurface">
                    <?php if ($show_image && $item['image'] !== ''): ?>
                    <a class="nb-content-feed__media" href="<?= $item['url'] !== '' ? $item['url'] : '#' ?>"<?= $item['url'] === '' ? ' aria-disabled="true"' : '' ?> data-nb-entity="media">
                        <img class="nb-content-feed__image" src="<?= $item['image'] ?>" alt="<?= $item['imageAlt'] ?>">
                    </a>
                    <?php endif; ?>
                    <div class="nb-content-feed__body">
                        <?php if ($show_category && $item['category'] !== ''): ?>
                        <div class="nb-content-feed__category" data-nb-entity="meta"><?= $item['category'] ?></div>
                        <?php endif; ?>
                        <?php if ($item['title'] !== ''): ?>
                        <h3 class="nb-content-feed__card-title" data-nb-entity="itemTitle">
                            <?php if ($item['url'] !== ''): ?>
                            <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                            <?php else: ?>
                            <?= $item['title'] ?>
                            <?php endif; ?>
                        </h3>
                        <?php endif; ?>
                        <?php if ($show_excerpt && $item['excerpt'] !== ''): ?>
                        <div class="nb-content-feed__excerpt" data-nb-entity="itemText"><?= $item['excerpt'] ?></div>
                        <?php endif; ?>
                        <?php $card_link_label = nb_headline_feed_card_link_label($item); ?>
                        <?php if ($card_link_label !== ''): ?>
                        <a class="nb-content-feed__item-link" href="<?= $item['url'] ?>" data-nb-entity="itemLink"><?= $card_link_label ?></a>
                        <?php endif; ?>
                        <?php if (($show_date && $item['date'] !== '') || ($show_views && $item['views'] !== '') || ($show_comments && $item['comments'] !== '')): ?>
                        <div class="nb-content-feed__meta" data-nb-entity="meta">
                            <?php if ($show_date && $item['date'] !== ''): ?><span><?= $item['date'] ?></span><?php endif; ?>
                            <?php if ($show_views && $item['views'] !== ''): ?><span><?= $item['views'] ?> просмотров</span><?php endif; ?>
                            <?php if ($show_comments && $item['comments'] !== ''): ?><span><?= $item['comments'] ?> комментариев</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>