<?php
$shell_scheme_file = cmsConfig::get('root_path') . 'templates/nordic/shell_scheme.php';
$shell_scheme = is_readable($shell_scheme_file) ? include $shell_scheme_file : [];
$reserved_positions = [];

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

$rows = $filter_rows($rows);

include cmsConfig::get('root_path') . 'templates/modern/layout_childs/main_scheme.tpl.php';