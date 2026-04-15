<?php
/**
 * Ряды и колонки к макету шаблона
 * https://docs.instantcms.ru/dev/templates/layouts
 */
?>
<?php
$resolve_shell_zone = function(array $row) {
    $positions = array_fill_keys(array_filter($row['positions'] ?? []), true);
    $row_tag = strtolower((string) ($row['tag'] ?? ''));

    foreach (['content_body', 'content_sidebar_left', 'content_sidebar_right', 'main', 'native_content', 'sidebar', 'pos_8', 'pos_9', 'pos_34'] as $position) {
        if (isset($positions[$position])) {
            return 'shell.content-body';
        }
    }

    if ($row_tag === 'main') {
        return 'shell.content-body';
    }

    foreach (['footer_primary', 'footer_secondary', 'footer', 'pos_11', 'pos_32', 'pos_38', 'pos_39', 'pos_40'] as $position) {
        if (isset($positions[$position])) {
            return 'footer';
        }
    }

    return '';
};

$get_shell_zone_meta = function($zone) {
    if ($zone === 'shell.content-body') {
        return [
            'tag' => 'div',
            'class' => 'nordic-shell-zone nordic-shell-zone--content-frame',
            'id' => 'shell-content-body',
            'label' => 'Основная контентная зона'
        ];
    }

    if ($zone === 'footer') {
        return [
            'tag' => 'footer',
            'class' => 'nordic-shell-zone nordic-shell-zone--footer',
            'id' => 'shell-footer',
            'label' => 'Подвал сайта'
        ];
    }

    return null;
};

$render_rows = [];

foreach ($rows as $row) {
    if (empty($row['_nordicstyl_force_render']) && !$this->hasWidgetsOn($row['positions'])) {
        continue;
    }

    $render_rows[] = $row;
}
?>
<?php foreach ($render_rows as $row_index => $row) { ?>
    <?php
        $row_positions_hash = substr(sha1(json_encode($row['positions'] ?? [])), 0, 10);
        $row_nordic_id = !empty($row['_nordic_id']) ? (string)$row['_nordic_id'] : (!empty($row['id']) ? ('row-' . (int)$row['id']) : ('row-' . $row_positions_hash));
        $row_nordic_label = !empty($row['_nordic_label']) ? (string)$row['_nordic_label'] : (!empty($row['title']) ? (string)$row['title'] : 'Ряд макета');
        $row_zone = $resolve_shell_zone($row);
        $prev_row_zone = ($row_index > 0) ? $resolve_shell_zone($render_rows[$row_index - 1]) : '';
        $next_row_zone = isset($render_rows[$row_index + 1]) ? $resolve_shell_zone($render_rows[$row_index + 1]) : '';
    ?>
    <?php if ($row_zone !== '' && $row_zone !== $prev_row_zone) {
        $shell_zone_meta = $get_shell_zone_meta($row_zone);
    ?>
        <<?php echo $shell_zone_meta['tag']; ?> class="<?php html($shell_zone_meta['class']); ?>" data-nordic-id="<?php html($shell_zone_meta['id']); ?>" data-nordic-role="<?php html($row_zone); ?>" data-nordic-label="<?php html($shell_zone_meta['label']); ?>">
    <?php } ?>
    <?php if (!empty($row['options']['parrent_tag'])) { ?>
        <<?php echo $row['options']['parrent_tag']; ?><?php if ($row['options']['parrent_tag_class']) { ?> class="<?php html($row['options']['parrent_tag_class']); ?>"<?php } ?>>
    <?php } ?>
    <?php if (!empty($row['options']['container'])) { ?>
        <<?php echo $row['options']['container_tag']; ?> class="<?php html($row['options']['container']); ?><?php if ($row['options']['container_tag_class']) { ?> <?php html($row['options']['container_tag_class']); ?><?php } ?>">
    <?php } ?>
    <?php
        // Собираем класс ряда
        $row_class =  $row['tag'] ? ['row'] : [];
        if (!empty($row['options']['no_gutters'])) {
            $row_class[] = 'no-gutters';
        }
        if (!empty($row['options']['vertical_align'])) {
            $row_class[] = $row['options']['vertical_align'];
        }
        if (!empty($row['options']['horizontal_align'])) {
            $row_class[] = $row['options']['horizontal_align'];
        }
        if ($row['class']) {
            $row_class[] = $row['class'];
        }
    ?>
    <?php if ($row['tag'] && $row_class) { ?>
        <<?php echo $row['tag']; ?> class="<?php html(implode(' ', $row_class)); ?>" data-nordic-id="<?php html($row_nordic_id); ?>" data-nordic-role="layout-row" data-nordic-label="<?php html($row_nordic_label); ?>">
    <?php } ?>
    <?php foreach ($row['cols'] as $col) { ?>
        <?php if (empty($col['_nordicstyl_force_render']) && !$this->hasWidgetsOn($col['positions'])) {
            continue;
        } ?>
        <?php
            $col_name = trim((string)($col['name'] ?? ''));
            $col_positions_hash = substr(sha1(json_encode($col['positions'] ?? [])), 0, 10);
            $col_nordic_suffix = !empty($col['_nordic_id']) ? (string)$col['_nordic_id'] : (!empty($col['id']) ? ('id-' . (int)$col['id']) : ($col_name !== '' ? preg_replace('/[^a-z0-9\-_]+/i', '-', $col_name) : $col_positions_hash));
            $col_nordic_id = 'col-' . trim((string)$col_nordic_suffix, '-');
            $col_nordic_label = !empty($col['_nordic_label']) ? (string)$col['_nordic_label'] : ($col_name !== '' ? ('Колонка: ' . $col_name) : 'Колонка макета');
            $col_has_widgets = $this->hasWidgetsOn($col['name']);
            $draft_preset_key = trim((string)($col['_nordic_preset_key'] ?? ''));
            $draft_content = is_array($col['_nordic_content'] ?? null) ? $col['_nordic_content'] : [];
            $draft_items = array_values(array_filter(array_map('trim', is_array($draft_content['items'] ?? null) ? $draft_content['items'] : [])));
            $draft_item_index = max(0, (int)($col['_nordic_column_index'] ?? 0));
            $draft_eyebrow = trim((string)($draft_content['eyebrow'] ?? ''));
            $draft_heading = trim((string)($draft_content['heading'] ?? '')) ?: $col_nordic_label;
            $draft_text = trim((string)($draft_content['text'] ?? ''));
            $draft_button_text = trim((string)($draft_content['button_text'] ?? ''));
            $draft_button_url = trim((string)($draft_content['button_url'] ?? ''));
            $draft_html = trim((string)($draft_content['html'] ?? ''));
            $draft_card_heading = ($draft_preset_key === 'features' || $draft_preset_key === 'cards') && !empty($draft_items[$draft_item_index])
                ? $draft_items[$draft_item_index]
                : $draft_heading;
        ?>
        <?php if(!empty($col['options']['add_js_files'])) { ?>
            <?php $this->addTplJSName($col['options']['add_js_files']); ?>
        <?php } ?>
        <?php if($col['type'] === 'custom') { ?>
            <?php if(!empty($col['rows']['before'])){ ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['before']]); ?>
            <?php } ?>
            <?php if($col_has_widgets){ ?>
                <?php $this->widgetsInHtml($col['name'], $col['wrapper']); ?>
            <?php } elseif (!empty($col['_nordicstyl_force_render'])) { ?>
                <div class="ns-draft-slot-placeholder" data-nordic-placeholder="1" data-nordic-preset="<?php html($draft_preset_key ?: 'draft'); ?>">
                    <span class="ns-draft-slot-placeholder__kicker"><?php html($draft_eyebrow !== '' ? $draft_eyebrow : 'Draft section'); ?></span>
                    <strong class="ns-draft-slot-placeholder__title"><?php html($draft_card_heading); ?></strong>
                    <?php if ($draft_text !== '') { ?>
                        <p class="ns-draft-slot-placeholder__text"><?php html($draft_text); ?></p>
                    <?php } ?>
                    <?php if (($draft_preset_key === 'features' || $draft_preset_key === 'cards') && $draft_heading !== '' && $draft_card_heading !== $draft_heading) { ?>
                        <p class="ns-draft-slot-placeholder__text"><?php html($draft_heading); ?></p>
                    <?php } ?>
                    <?php if ($draft_preset_key === 'faq' && $draft_items) { ?>
                        <div class="ns-draft-slot-placeholder__list">
                            <?php foreach ($draft_items as $draft_item) { ?>
                                <span class="ns-draft-slot-placeholder__item"><?php html($draft_item); ?></span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <?php if (($draft_preset_key === 'features' || $draft_preset_key === 'cards') && $draft_items) { ?>
                        <div class="ns-draft-slot-placeholder__list">
                            <span class="ns-draft-slot-placeholder__item"><?php html($draft_card_heading); ?></span>
                        </div>
                    <?php } ?>
                    <?php if ($draft_button_text !== '') { ?>
                        <span class="ns-draft-slot-placeholder__button"><?php html($draft_button_text); ?><?php if ($draft_button_url !== '') { ?> → <?php html($draft_button_url); ?><?php } ?></span>
                    <?php } ?>
                    <?php if ($draft_preset_key === 'html' && $draft_html !== '') { ?>
                        <code class="ns-draft-slot-placeholder__code"><?php html($draft_html); ?></code>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if(!empty($col['rows']['after'])){ ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['after']]); ?>
            <?php } ?>
            <?php continue; ?>
        <?php } ?>
        <?php if(!empty($col['options']['cut_before'])){ ?>
            <div class="w-100"></div>
        <?php } ?>
        <?php
            // Собираем класс колонки
            $col_class = [];
            if ($col['options']['col_class']) {
                $col_class[] = $col['options']['col_class'];
            }
            if ($col['options']['default_col_class']) {
                $col_class[] = $col['options']['default_col_class'];
            }
            if ($col['options']['md_col_class']) {
                $col_class[] = $col['options']['md_col_class'];
            }
            if ($col['options']['lg_col_class']) {
                $col_class[] = $col['options']['lg_col_class'];
            }
            if ($col['options']['xl_col_class']) {
                $col_class[] = $col['options']['xl_col_class'];
            }
            if ($col['options']['default_order']) {
                $col_class[] = 'order-'.$col['options']['default_order'];
            }
            if ($col['options']['sm_order']) {
                $col_class[] = 'order-sm-'.$col['options']['sm_order'];
            }
            if ($col['options']['md_order']) {
                $col_class[] = 'order-md-'.$col['options']['md_order'];
            }
            if ($col['options']['lg_order']) {
                $col_class[] = 'order-lg-'.$col['options']['lg_order'];
            }
            if ($col['options']['xl_order']) {
                $col_class[] = 'order-xl-'.$col['options']['xl_order'];
            }
            if ($col['class']) {
                $col_class[] = $col['class'];
            }
        ?>
        <<?php echo $col['tag']; ?> class="<?php html(implode(' ', $col_class)); ?>" data-nordic-id="<?php html($col_nordic_id); ?>" data-nordic-role="layout-column" data-nordic-label="<?php html($col_nordic_label); ?>">
            <?php if(!empty($col['rows']['before'])){ ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['before']]); ?>
            <?php } ?>
            <?php if($col_has_widgets){ ?>
                <?php $this->widgets($col['name']); ?>
            <?php } elseif (!empty($col['_nordicstyl_force_render'])) { ?>
                <div class="ns-draft-slot-placeholder" data-nordic-placeholder="1" data-nordic-preset="<?php html($draft_preset_key ?: 'draft'); ?>">
                    <span class="ns-draft-slot-placeholder__kicker"><?php html($draft_eyebrow !== '' ? $draft_eyebrow : 'Draft section'); ?></span>
                    <strong class="ns-draft-slot-placeholder__title"><?php html($draft_card_heading); ?></strong>
                    <?php if ($draft_text !== '') { ?>
                        <p class="ns-draft-slot-placeholder__text"><?php html($draft_text); ?></p>
                    <?php } ?>
                    <?php if (($draft_preset_key === 'features' || $draft_preset_key === 'cards') && $draft_heading !== '' && $draft_card_heading !== $draft_heading) { ?>
                        <p class="ns-draft-slot-placeholder__text"><?php html($draft_heading); ?></p>
                    <?php } ?>
                    <?php if ($draft_preset_key === 'faq' && $draft_items) { ?>
                        <div class="ns-draft-slot-placeholder__list">
                            <?php foreach ($draft_items as $draft_item) { ?>
                                <span class="ns-draft-slot-placeholder__item"><?php html($draft_item); ?></span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <?php if (($draft_preset_key === 'features' || $draft_preset_key === 'cards') && $draft_items) { ?>
                        <div class="ns-draft-slot-placeholder__list">
                            <span class="ns-draft-slot-placeholder__item"><?php html($draft_card_heading); ?></span>
                        </div>
                    <?php } ?>
                    <?php if ($draft_button_text !== '') { ?>
                        <span class="ns-draft-slot-placeholder__button"><?php html($draft_button_text); ?><?php if ($draft_button_url !== '') { ?> → <?php html($draft_button_url); ?><?php } ?></span>
                    <?php } ?>
                    <?php if ($draft_preset_key === 'html' && $draft_html !== '') { ?>
                        <code class="ns-draft-slot-placeholder__code"><?php html($draft_html); ?></code>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if(!empty($col['rows']['after'])){ ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['after']]); ?>
            <?php } ?>
        </<?php echo $col['tag']; ?>>
    <?php } ?>
    <?php if ($row['tag'] && $row_class) { ?>
        </<?php echo $row['tag']; ?>>
    <?php } ?>
    <?php if (!empty($row['options']['container'])) { ?>
        </<?php echo $row['options']['container_tag']; ?>>
    <?php } ?>
    <?php if (!empty($row['options']['parrent_tag'])) { ?>
        </<?php echo $row['options']['parrent_tag']; ?>>
    <?php } ?>
    <?php if ($row_zone !== '' && $row_zone !== $next_row_zone) { ?>
        </<?php echo $shell_zone_meta['tag']; ?>>
    <?php } ?>
<?php } ?>