<?php

if (!class_exists('NordicShellMigration')) {

    class NordicShellMigration {

        public const DEFAULT_TEMPLATE   = 'nordic';
        public const DEFAULT_SCHEME_KEY = 'nordic_shell_v1';

        public static function buildPlan(cmsDatabase $db, cmsConfig $config, string $template = self::DEFAULT_TEMPLATE): array {

            $shell_scheme = self::loadShellScheme($config, $template);
            $prefix       = $config->db_prefix;

            $canonical_positions = [];

            foreach ($shell_scheme['slot_positions'] as $slot => $positions) {
                $canonical_positions[$slot] = true;

                foreach ($positions as $position) {
                    $canonical_positions[$position] = true;
                }
            }

            $rows = self::fetchAll(
                $db,
                "SELECT id, parent_id, title, tag, template, ordering, nested_position, class, options\n" .
                "FROM {$prefix}layout_rows\n" .
                "WHERE template = '" . $db->escape($template) . "'\n" .
                "ORDER BY ordering, id"
            );

            $row_ids = [];

            foreach ($rows as $row) {
                $row_ids[] = (int) $row['id'];
            }

            $cols                     = [];
            $legacy_position_priority = [];

            if ($row_ids) {
                $cols = self::fetchAll(
                    $db,
                    "SELECT lc.id, lc.row_id, lc.title, lc.name, lc.type, lc.ordering, lc.tag, lc.class, lc.wrapper, lc.options,\n" .
                    "       lr.ordering AS row_ordering\n" .
                    "FROM {$prefix}layout_cols lc\n" .
                    "INNER JOIN {$prefix}layout_rows lr ON lr.id = lc.row_id\n" .
                    "WHERE lc.row_id IN (" . implode(', ', $row_ids) . ")\n" .
                    "ORDER BY lr.ordering, lc.ordering, lc.id"
                );

                foreach ($cols as $col) {
                    $legacy_position_priority[$col['name']] = [
                        'row_ordering' => (int) $col['row_ordering'],
                        'col_ordering' => (int) $col['ordering']
                    ];
                }
            }

            $binds = self::fetchAll(
                $db,
                "SELECT id, bind_id, page_id, template, is_enabled, position, ordering\n" .
                "FROM {$prefix}widgets_bind_pages\n" .
                "WHERE template = '" . $db->escape($template) . "'\n" .
                "ORDER BY page_id, position, ordering, id"
            );

            $position_stats = [];

            foreach ($binds as $bind) {
                $position = $bind['position'];
                $position_stats[$position] = ($position_stats[$position] ?? 0) + 1;
            }

            ksort($position_stats);

            $planned_rows = [];
            $planned_cols = [];

            foreach ($shell_scheme['layout_rows'] as $row_index => $row) {
                $planned_rows[] = [
                    'title'           => (string) ($row['title'] ?? ('Nordic Row ' . ($row_index + 1))),
                    'tag'             => !empty($row['tag']) ? (string) $row['tag'] : 'div',
                    'template'        => $template,
                    'ordering'        => $row_index + 1,
                    'nested_position' => null,
                    'parent_id'       => 0,
                    'class'           => (string) ($row['class'] ?? ''),
                    'options'         => ''
                ];

                foreach (($row['cols'] ?? []) as $col_index => $col) {
                    $planned_cols[] = [
                        'row_plan_index' => $row_index,
                        'title'          => (string) ($col['title'] ?? ('Col ' . ($col_index + 1))),
                        'name'           => (string) ($col['name'] ?? ''),
                        'type'           => !empty($col['type']) ? (string) $col['type'] : 'typical',
                        'ordering'       => $col_index + 1,
                        'tag'            => !empty($col['tag']) ? (string) $col['tag'] : 'div',
                        'class'          => (string) ($col['class'] ?? ''),
                        'wrapper'        => (string) ($col['wrapper'] ?? ''),
                        'options'        => ''
                    ];
                }
            }

            $bind_updates       = [];
            $unmapped_positions = [];

            foreach ($binds as $bind) {
                $old_position = (string) $bind['position'];

                if (isset($shell_scheme['legacy_bind_map'][$old_position])) {
                    $new_position = $shell_scheme['legacy_bind_map'][$old_position];
                } elseif (isset($canonical_positions[$old_position])) {
                    $new_position = $old_position;
                } else {
                    $unmapped_positions[$old_position] = true;
                    $new_position                      = $old_position;
                }

                $priority = $legacy_position_priority[$old_position] ?? ['row_ordering' => 999, 'col_ordering' => 999];

                $bind_updates[] = [
                    'id'            => (int) $bind['id'],
                    'bind_id'       => (int) $bind['bind_id'],
                    'page_id'       => (int) $bind['page_id'],
                    'is_enabled'    => (int) $bind['is_enabled'],
                    'old_position'  => $old_position,
                    'new_position'  => $new_position,
                    'old_ordering'  => (int) $bind['ordering'],
                    'row_ordering'  => $priority['row_ordering'],
                    'col_ordering'  => $priority['col_ordering']
                ];
            }

            usort($bind_updates, function(array $left, array $right) {
                $left_group = [$left['page_id'], $left['new_position'], $left['row_ordering'], $left['col_ordering'], $left['old_ordering'], $left['id']];
                $right_group = [$right['page_id'], $right['new_position'], $right['row_ordering'], $right['col_ordering'], $right['old_ordering'], $right['id']];

                return $left_group <=> $right_group;
            });

            $group_ordering    = [];
            $changed_bind_count = 0;

            foreach ($bind_updates as $index => $bind_update) {
                $group_key = $bind_update['page_id'] . '|' . $bind_update['new_position'];

                $group_ordering[$group_key] = ($group_ordering[$group_key] ?? 0) + 1;
                $bind_updates[$index]['new_ordering'] = $group_ordering[$group_key];

                if (
                    $bind_updates[$index]['old_position'] !== $bind_updates[$index]['new_position'] ||
                    $bind_updates[$index]['old_ordering'] !== $bind_updates[$index]['new_ordering']
                ) {
                    $changed_bind_count++;
                }
            }

            return [
                'template'            => $template,
                'shell_scheme_key'    => (string) ($shell_scheme['key'] ?? self::DEFAULT_SCHEME_KEY),
                'current_counts'      => [
                    'rows'  => count($rows),
                    'cols'  => count($cols),
                    'binds' => count($binds)
                ],
                'planned_counts'      => [
                    'rows_to_recreate' => count($planned_rows),
                    'cols_to_recreate' => count($planned_cols),
                    'binds_to_touch'   => $changed_bind_count
                ],
                'position_stats'      => $position_stats,
                'unmapped_positions'  => array_values(array_keys($unmapped_positions)),
                'bind_migration_preview' => array_slice(array_map(function(array $item) {
                    return [
                        'id'      => $item['id'],
                        'page_id' => $item['page_id'],
                        'from'    => $item['old_position'] . '#' . $item['old_ordering'],
                        'to'      => $item['new_position'] . '#' . $item['new_ordering']
                    ];
                }, $bind_updates), 0, 30),
                'planned_rows'        => $planned_rows,
                'planned_cols'        => $planned_cols,
                'bind_updates'        => $bind_updates,
                'existing_row_ids'    => $row_ids
            ];
        }

        public static function apply(cmsDatabase $db, cmsConfig $config, cmsCache $cache, string $template = self::DEFAULT_TEMPLATE): array {

            $plan   = self::buildPlan($db, $config, $template);
            $prefix = $config->db_prefix;

            if ($plan['unmapped_positions']) {
                throw new RuntimeException('Unmapped positions found: ' . implode(', ', $plan['unmapped_positions']));
            }

            $db->autocommitOff();
            $db->beginTransaction();

            try {
                if ($plan['existing_row_ids']) {
                    self::runQuery(
                        $db,
                        "DELETE FROM {$prefix}layout_cols WHERE row_id IN (" . implode(', ', $plan['existing_row_ids']) . ")"
                    );
                }

                self::runQuery(
                    $db,
                    "DELETE FROM {$prefix}layout_rows WHERE template = '" . $db->escape($template) . "'"
                );

                $new_row_ids = [];

                foreach ($plan['planned_rows'] as $row_plan_index => $row) {
                    self::runQuery(
                        $db,
                        "INSERT INTO {$prefix}layout_rows (parent_id, title, tag, template, ordering, nested_position, class, options) VALUES (" .
                        (int) $row['parent_id'] . ', ' .
                        self::sqlValue($db, $row['title']) . ', ' .
                        self::sqlValue($db, $row['tag']) . ', ' .
                        self::sqlValue($db, $row['template']) . ', ' .
                        (int) $row['ordering'] . ', ' .
                        self::sqlValue($db, $row['nested_position']) . ', ' .
                        self::sqlValue($db, $row['class']) . ', ' .
                        self::sqlValue($db, $row['options']) . ')'
                    );

                    $new_row_ids[$row_plan_index] = (int) $db->lastId();
                }

                foreach ($plan['planned_cols'] as $col) {
                    self::runQuery(
                        $db,
                        "INSERT INTO {$prefix}layout_cols (row_id, title, name, type, ordering, tag, class, wrapper, options) VALUES (" .
                        (int) $new_row_ids[$col['row_plan_index']] . ', ' .
                        self::sqlValue($db, $col['title']) . ', ' .
                        self::sqlValue($db, $col['name']) . ', ' .
                        self::sqlValue($db, $col['type']) . ', ' .
                        (int) $col['ordering'] . ', ' .
                        self::sqlValue($db, $col['tag']) . ', ' .
                        self::sqlValue($db, $col['class']) . ', ' .
                        self::sqlValue($db, $col['wrapper']) . ', ' .
                        self::sqlValue($db, $col['options']) . ')'
                    );
                }

                foreach ($plan['bind_updates'] as $bind_update) {
                    self::runQuery(
                        $db,
                        "UPDATE {$prefix}widgets_bind_pages SET position = " . self::sqlValue($db, $bind_update['new_position']) .
                        ", ordering = " . (int) $bind_update['new_ordering'] .
                        " WHERE id = " . (int) $bind_update['id']
                    );
                }

                $db->commit();
                $db->autocommitOn();
            } catch (Throwable $exception) {
                $db->rollback();
                $db->autocommitOn();

                throw $exception;
            }

            $cache->clean('layout.rows');
            $cache->clean('widgets.bind_pages');
            $cache->clean('widgets.bind');
            $cache->clean('widgets.pages');

            return self::createReport($plan, 'apply', 'applied');
        }

        public static function createReport(array $plan, string $mode = 'dry-run', string $status = ''): array {

            $report = [
                'mode'                  => $mode,
                'template'              => $plan['template'],
                'shell_scheme_key'      => $plan['shell_scheme_key'],
                'current_counts'        => $plan['current_counts'],
                'planned_counts'        => $plan['planned_counts'],
                'position_stats'        => $plan['position_stats'],
                'unmapped_positions'    => $plan['unmapped_positions'],
                'bind_migration_preview' => $plan['bind_migration_preview']
            ];

            if ($status !== '') {
                $report['status'] = $status;
            }

            return $report;
        }

        public static function formatCliReport(array $report): array {

            $lines = [
                'mode\t' . $report['mode'],
                'template\t' . $report['template'],
                'shell_scheme_key\t' . $report['shell_scheme_key'],
                'current_rows\t' . $report['current_counts']['rows'],
                'current_cols\t' . $report['current_counts']['cols'],
                'current_binds\t' . $report['current_counts']['binds'],
                'planned_rows_to_recreate\t' . $report['planned_counts']['rows_to_recreate'],
                'planned_cols_to_recreate\t' . $report['planned_counts']['cols_to_recreate'],
                'planned_binds_to_touch\t' . $report['planned_counts']['binds_to_touch'],
                'unmapped_positions\t' . ($report['unmapped_positions'] ? implode(',', $report['unmapped_positions']) : 'none')
            ];

            if (!empty($report['status'])) {
                $lines[] = 'status\t' . $report['status'];
            }

            $lines[] = 'position_stats:';

            foreach ($report['position_stats'] as $position => $count) {
                $lines[] = ['  -', $position, $count];
            }

            $lines[] = 'bind_migration_preview:';

            foreach ($report['bind_migration_preview'] as $preview) {
                $lines[] = ['  -', $preview['id'], $preview['page_id'], $preview['from'], $preview['to']];
            }

            return $lines;
        }

        private static function loadShellScheme(cmsConfig $config, string $template): array {

            $shell_scheme_file = $config->root_path . 'templates/' . $template . '/shell_scheme.php';

            if (!is_readable($shell_scheme_file)) {
                throw new RuntimeException('Shell scheme not found: ' . $shell_scheme_file);
            }

            $shell_scheme = include $shell_scheme_file;

            if (!is_array($shell_scheme) || empty($shell_scheme['layout_rows']) || empty($shell_scheme['legacy_bind_map'])) {
                throw new RuntimeException('Invalid shell scheme structure');
            }

            return $shell_scheme;
        }

        private static function fetchAll(cmsDatabase $db, string $sql): array {

            $result = $db->query($sql);

            if (!$result) {
                throw new RuntimeException('Query failed: ' . $sql);
            }

            $rows = [];

            while ($row = $db->fetchAssoc($result)) {
                $rows[] = $row;
            }

            $db->freeResult($result);

            return $rows;
        }

        private static function runQuery(cmsDatabase $db, string $sql): void {

            if ($db->query($sql) === false) {
                throw new RuntimeException('Query failed: ' . $sql);
            }
        }

        private static function sqlValue(cmsDatabase $db, $value): string {

            if ($value === null) {
                return 'NULL';
            }

            return "'" . $db->escape((string) $value) . "'";
        }
    }
}
