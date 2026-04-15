<?php
$shell_scheme_file = cmsConfig::get('root_path') . 'templates/nordic/shell_scheme.php';
$shell_scheme = is_readable($shell_scheme_file) ? include $shell_scheme_file : [];
$reserved_positions = [];

// В modern-skin (dev) мы рендерим стандартную layout-схему (top/header/main/footer)
// и не должны вырезать reserved positions, иначе пропадают ключевые области modern.
$__nordic_disable_reserved_filter = !empty($nordic_disable_reserved_filter);

if (!empty($shell_scheme['reserved_positions']) && is_array($shell_scheme['reserved_positions'])) {
    $reserved_positions = array_fill_keys($shell_scheme['reserved_positions'], true);
}

$collect_row_positions = function(array $rows) use (&$collect_row_positions) {
    $positions = [];

    foreach ($rows as $row) {
        foreach (($row['cols'] ?? []) as $col) {
            $positions = array_merge($positions, $col['positions'] ?? []);

            if (!empty($col['rows']['before'])) {
                $positions = array_merge($positions, $collect_row_positions($col['rows']['before']));
            }

            if (!empty($col['rows']['after'])) {
                $positions = array_merge($positions, $collect_row_positions($col['rows']['after']));
            }
        }
    }

    return array_values(array_unique(array_filter($positions)));
};

$filter_rows = function(array $rows) use (&$filter_rows, $collect_row_positions, $reserved_positions) {
    $filtered_rows = [];

    foreach ($rows as $row_id => $row) {
        $filtered_cols = [];

        foreach (($row['cols'] ?? []) as $col_id => $col) {
            if (!empty($col['rows']['before'])) {
                $col['rows']['before'] = $filter_rows($col['rows']['before']);
            }

            if (!empty($col['rows']['after'])) {
                $col['rows']['after'] = $filter_rows($col['rows']['after']);
            }

            $direct_positions = [];

            foreach (($col['positions'] ?? []) as $position) {
                if ($position !== '' && !isset($reserved_positions[$position])) {
                    $direct_positions[] = $position;
                }
            }

            if (!empty($col['name']) && !isset($reserved_positions[$col['name']])) {
                $direct_positions[] = $col['name'];
            }

            $nested_positions = [];

            if (!empty($col['rows']['before'])) {
                $nested_positions = array_merge($nested_positions, $collect_row_positions($col['rows']['before']));
            }

            if (!empty($col['rows']['after'])) {
                $nested_positions = array_merge($nested_positions, $collect_row_positions($col['rows']['after']));
            }

            $col['positions'] = array_values(array_unique(array_merge($direct_positions, $nested_positions)));

            if (!$col['positions']) {
                continue;
            }

            if (!empty($col['name']) && isset($reserved_positions[$col['name']])) {
                $col['name'] = '';
            }

            $filtered_cols[$col_id] = $col;
        }

        if (!$filtered_cols) {
            continue;
        }

        $row['cols'] = $filtered_cols;
        $row['positions'] = $collect_row_positions([$row]);

        if (!$row['positions']) {
            continue;
        }

        $filtered_rows[$row_id] = $row;
    }

    return $filtered_rows;
};

if (!$__nordic_disable_reserved_filter) {
    $rows = $filter_rows($rows);
}

$resolve_shell_zone = function(array $positions) {
    $position_lookup = array_fill_keys(array_filter($positions), true);

    foreach (['header_primary', 'header_secondary'] as $position) {
        if (isset($position_lookup[$position])) {
            return 'header';
        }
    }

    foreach (['content_body', 'content_sidebar_left', 'content_sidebar_right'] as $position) {
        if (isset($position_lookup[$position])) {
            return 'shell.content-body';
        }
    }

    foreach (['footer_primary', 'footer_secondary'] as $position) {
        if (isset($position_lookup[$position])) {
            return 'footer';
        }
    }

    return '';
};

$get_shell_zone_meta = function($zone) {
    if ($zone === 'header') {
        return [
            'tag' => 'header',
            'class' => 'nordic-shell-zone nordic-shell-zone--header',
            'id' => 'shell-header',
            'label' => 'Шапка сайта'
        ];
    }

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
        $row_zone = $resolve_shell_zone($row['positions'] ?? []);
        $prev_row_zone = ($row_index > 0) ? $resolve_shell_zone($render_rows[$row_index - 1]['positions'] ?? []) : '';
        $next_row_zone = isset($render_rows[$row_index + 1]) ? $resolve_shell_zone($render_rows[$row_index + 1]['positions'] ?? []) : '';
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
        $row_class = $row['tag'] ? ['row'] : [];
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
        <?php if (!empty($col['options']['add_js_files'])) { ?>
            <?php $this->addTplJSName($col['options']['add_js_files']); ?>
        <?php } ?>
        <?php if ($col['type'] === 'custom') { ?>
            <?php if (!empty($col['rows']['before'])) { ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['before'], 'nordic_disable_reserved_filter' => $__nordic_disable_reserved_filter]); ?>
            <?php } ?>
            <?php if ($col_has_widgets) { ?>
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
            <?php if (!empty($col['rows']['after'])) { ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['after'], 'nordic_disable_reserved_filter' => $__nordic_disable_reserved_filter]); ?>
            <?php } ?>
            <?php continue; ?>
        <?php } ?>
        <?php if (!empty($col['options']['cut_before'])) { ?>
            <div class="w-100"></div>
        <?php } ?>
        <?php
            // Нормализация: колонки созданные через SQL могут не иметь всех ключей
            $col_opts = $col['options'] ?? [];
            $col_class = [];
            if (!empty($col_opts['col_class']))     { $col_class[] = $col_opts['col_class']; }
            if (!empty($col_opts['default_col_class'])) { $col_class[] = $col_opts['default_col_class']; }
            if (!empty($col_opts['md_col_class']))  { $col_class[] = $col_opts['md_col_class']; }
            if (!empty($col_opts['lg_col_class']))  { $col_class[] = $col_opts['lg_col_class']; }
            if (!empty($col_opts['xl_col_class']))  { $col_class[] = $col_opts['xl_col_class']; }
            if (!empty($col_opts['default_order'])) { $col_class[] = 'order-'    . $col_opts['default_order']; }
            if (!empty($col_opts['sm_order']))      { $col_class[] = 'order-sm-' . $col_opts['sm_order']; }
            if (!empty($col_opts['md_order']))      { $col_class[] = 'order-md-' . $col_opts['md_order']; }
            if (!empty($col_opts['lg_order']))      { $col_class[] = 'order-lg-' . $col_opts['lg_order']; }
            if (!empty($col_opts['xl_order']))      { $col_class[] = 'order-xl-' . $col_opts['xl_order']; }
            if (!empty($col['class']))              { $col_class[] = $col['class']; }
        ?>
        <<?php echo $col['tag']; ?> class="<?php html(implode(' ', $col_class)); ?>" data-nordic-id="<?php html($col_nordic_id); ?>" data-nordic-role="layout-column" data-nordic-label="<?php html($col_nordic_label); ?>">
            <?php if (!empty($col['rows']['before'])) { ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['before'], 'nordic_disable_reserved_filter' => $__nordic_disable_reserved_filter]); ?>
            <?php } ?>
            <?php if ($col_has_widgets) { ?>
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
            <?php if (!empty($col['rows']['after'])) { ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['after'], 'nordic_disable_reserved_filter' => $__nordic_disable_reserved_filter]); ?>
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