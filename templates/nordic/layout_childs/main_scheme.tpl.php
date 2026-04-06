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
?>
<?php foreach ($rows as $row) { ?>
    <?php if (!$this->hasWidgetsOn($row['positions'])) {
        continue;
    } ?>
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
        <<?php echo $row['tag']; ?> class="<?php html(implode(' ', $row_class)); ?>">
    <?php } ?>
    <?php foreach ($row['cols'] as $col) { ?>
        <?php if (!$this->hasWidgetsOn($col['positions'])) {
            continue;
        } ?>
        <?php if (!empty($col['options']['add_js_files'])) { ?>
            <?php $this->addTplJSName($col['options']['add_js_files']); ?>
        <?php } ?>
        <?php if ($col['type'] === 'custom') { ?>
            <?php if (!empty($col['rows']['before'])) { ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['before'], 'nordic_disable_reserved_filter' => $__nordic_disable_reserved_filter]); ?>
            <?php } ?>
            <?php if ($this->hasWidgetsOn($col['name'])) { ?>
                <?php $this->widgetsInHtml($col['name'], $col['wrapper']); ?>
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
        <<?php echo $col['tag']; ?> class="<?php html(implode(' ', $col_class)); ?>">
            <?php if (!empty($col['rows']['before'])) { ?>
                <?php $this->renderLayoutChild('scheme', ['rows' => $col['rows']['before'], 'nordic_disable_reserved_filter' => $__nordic_disable_reserved_filter]); ?>
            <?php } ?>
            <?php if ($this->hasWidgetsOn($col['name'])) { ?>
                <?php $this->widgets($col['name']); ?>
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
<?php } ?>