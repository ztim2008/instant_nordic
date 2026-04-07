#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$configPath = $root . '/system/config/config.php';

if (!is_file($configPath)) {
    fwrite(STDERR, "Config file not found: {$configPath}\n");
    exit(1);
}

$config = include $configPath;
if (!is_array($config)) {
    fwrite(STDERR, "Invalid config format in {$configPath}\n");
    exit(1);
}

$required = ['db_host', 'db_user', 'db_pass', 'db_base', 'db_prefix'];
foreach ($required as $key) {
    if (!array_key_exists($key, $config)) {
        fwrite(STDERR, "Missing config key: {$key}\n");
        exit(1);
    }
}

$mode = 'dry-run';
foreach ($argv as $arg) {
    if ($arg === '--apply') {
        $mode = 'apply';
    }
}

$mysqli = new mysqli((string) $config['db_host'], (string) $config['db_user'], (string) $config['db_pass'], (string) $config['db_base']);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "DB connect failed: {$mysqli->connect_error}\n");
    exit(1);
}

$charset = !empty($config['db_charset']) ? (string) $config['db_charset'] : 'utf8mb4';
$mysqli->set_charset($charset);

$prefix = (string) $config['db_prefix'];
$tRows = $prefix . 'layout_rows';
$tCols = $prefix . 'layout_cols';
$tBinds = $prefix . 'widgets_bind_pages';

function fetchAllAssoc(mysqli $db, string $sql): array {
    $res = $db->query($sql);
    if (!$res) {
        throw new RuntimeException('SQL error: ' . $db->error . "\nQuery: {$sql}");
    }
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $res->free();
    return $rows;
}

function sqlValue(mysqli $db, $value): string {
    if ($value === null) {
        return 'NULL';
    }
    return "'" . $db->real_escape_string((string) $value) . "'";
}

function buildRollbackSql(mysqli $db, string $tRows, string $tCols, string $tBinds, array $rows, array $cols, array $binds): string {
    $lines = [];
    $lines[] = '-- Nordic layout rollback snapshot';
    $lines[] = '-- Generated at ' . date('c');
    $lines[] = 'START TRANSACTION;';
    $lines[] = 'SET FOREIGN_KEY_CHECKS=0;';
    $lines[] = "DELETE c FROM {$tCols} c INNER JOIN {$tRows} r ON r.id = c.row_id WHERE r.template = 'nordic';";
    $lines[] = "DELETE FROM {$tRows} WHERE template = 'nordic';";
    $lines[] = "DELETE FROM {$tBinds} WHERE template = 'nordic';";

    if ($rows) {
        foreach ($rows as $row) {
            $lines[] = "INSERT INTO {$tRows} (id, parent_id, title, tag, template, ordering, nested_position, class, options) VALUES (" .
                (int) $row['id'] . ', ' .
                ($row['parent_id'] === null ? 'NULL' : (int) $row['parent_id']) . ', ' .
                sqlValue($db, $row['title']) . ', ' .
                sqlValue($db, $row['tag']) . ', ' .
                "'nordic', " .
                (int) $row['ordering'] . ', ' .
                sqlValue($db, $row['nested_position']) . ', ' .
                sqlValue($db, $row['class']) . ', ' .
                sqlValue($db, $row['options']) .
                ');';
        }
    }

    if ($cols) {
        foreach ($cols as $col) {
            $lines[] = "INSERT INTO {$tCols} (id, row_id, title, name, type, ordering, tag, class, wrapper, options) VALUES (" .
                (int) $col['id'] . ', ' .
                (int) $col['row_id'] . ', ' .
                sqlValue($db, $col['title']) . ', ' .
                sqlValue($db, $col['name']) . ', ' .
                sqlValue($db, $col['type']) . ', ' .
                (int) $col['ordering'] . ', ' .
                sqlValue($db, $col['tag']) . ', ' .
                sqlValue($db, $col['class']) . ', ' .
                sqlValue($db, $col['wrapper']) . ', ' .
                sqlValue($db, $col['options']) .
                ');';
        }
    }

    if ($binds) {
        foreach ($binds as $bind) {
            $lines[] = "INSERT INTO {$tBinds} (id, bind_id, template, is_enabled, page_id, position, ordering) VALUES (" .
                (int) $bind['id'] . ', ' .
                (int) $bind['bind_id'] . ', ' .
                "'nordic', " .
                (int) $bind['is_enabled'] . ', ' .
                (int) $bind['page_id'] . ', ' .
                sqlValue($db, $bind['position']) . ', ' .
                (int) $bind['ordering'] .
                ');';
        }
    }

    $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
    $lines[] = 'COMMIT;';
    $lines[] = '';

    return implode("\n", $lines);
}

try {
    $modernRows = fetchAllAssoc($mysqli, "SELECT id, parent_id, title, tag, ordering, nested_position, class, options FROM {$tRows} WHERE template='modern' ORDER BY ordering, id");
    $modernCols = fetchAllAssoc($mysqli, "SELECT c.id, c.row_id, c.title, c.name, c.type, c.ordering, c.tag, c.class, c.wrapper, c.options FROM {$tCols} c INNER JOIN {$tRows} r ON r.id = c.row_id WHERE r.template='modern' ORDER BY r.ordering, c.ordering, c.id");
    $modernBinds = fetchAllAssoc($mysqli, "SELECT id, bind_id, is_enabled, page_id, position, ordering FROM {$tBinds} WHERE template='modern' ORDER BY id");

    $nordicRows = fetchAllAssoc($mysqli, "SELECT id, parent_id, title, tag, ordering, nested_position, class, options FROM {$tRows} WHERE template='nordic' ORDER BY ordering, id");
    $nordicRowIds = array_map(static function(array $r): int { return (int) $r['id']; }, $nordicRows);

    $nordicCols = [];
    if ($nordicRowIds) {
        $ids = implode(',', array_map('intval', $nordicRowIds));
        $nordicCols = fetchAllAssoc($mysqli, "SELECT id, row_id, title, name, type, ordering, tag, class, wrapper, options FROM {$tCols} WHERE row_id IN ({$ids}) ORDER BY row_id, ordering, id");
    }

    $nordicBinds = fetchAllAssoc($mysqli, "SELECT id, bind_id, is_enabled, page_id, position, ordering FROM {$tBinds} WHERE template='nordic' ORDER BY id");

    if (!$modernRows || !$modernCols || !$modernBinds) {
        throw new RuntimeException('Modern template data is incomplete in DB. Aborting sync.');
    }

    $backupDir = $root . '/backups/db';
    if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
        throw new RuntimeException('Cannot create backup dir: ' . $backupDir);
    }

    $stamp = date('Ymd-His');
    $rollbackFile = $backupDir . '/nordic-layout-rollback-' . $stamp . '.sql';
    $reportFile = $backupDir . '/nordic-layout-sync-report-' . $stamp . '.json';

    $rollbackSql = buildRollbackSql($mysqli, $tRows, $tCols, $tBinds, $nordicRows, $nordicCols, $nordicBinds);
    file_put_contents($rollbackFile, $rollbackSql);

    $report = [
        'mode' => $mode,
        'generated_at' => date('c'),
        'db' => $config['db_base'],
        'prefix' => $prefix,
        'before' => [
            'nordic_rows' => count($nordicRows),
            'nordic_cols' => count($nordicCols),
            'nordic_binds' => count($nordicBinds)
        ],
        'source_modern' => [
            'modern_rows' => count($modernRows),
            'modern_cols' => count($modernCols),
            'modern_binds' => count($modernBinds)
        ],
        'rollback_file' => $rollbackFile
    ];

    if ($mode !== 'apply') {
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Dry-run completed.\n";
        echo "Rollback file prepared: {$rollbackFile}\n";
        echo "Report: {$reportFile}\n";
        echo 'Nordic snapshot: rows=' . count($nordicRows) . ', cols=' . count($nordicCols) . ', binds=' . count($nordicBinds) . "\n";
        echo 'Modern source: rows=' . count($modernRows) . ', cols=' . count($modernCols) . ', binds=' . count($modernBinds) . "\n";
        exit(0);
    }

    $mysqli->begin_transaction();

    if (!$mysqli->query("DELETE c FROM {$tCols} c INNER JOIN {$tRows} r ON r.id = c.row_id WHERE r.template='nordic'")) {
        throw new RuntimeException('Delete nordic cols failed: ' . $mysqli->error);
    }
    if (!$mysqli->query("DELETE FROM {$tRows} WHERE template='nordic'")) {
        throw new RuntimeException('Delete nordic rows failed: ' . $mysqli->error);
    }
    if (!$mysqli->query("DELETE FROM {$tBinds} WHERE template='nordic'")) {
        throw new RuntimeException('Delete nordic binds failed: ' . $mysqli->error);
    }

    $rowIdMap = [];
    $rowParentColOld = [];

    foreach ($modernRows as $row) {
        $sql = "INSERT INTO {$tRows} (parent_id, title, tag, template, ordering, nested_position, class, options) VALUES (" .
            'NULL, ' .
            sqlValue($mysqli, $row['title']) . ', ' .
            sqlValue($mysqli, $row['tag']) . ', ' .
            "'nordic', " .
            (int) $row['ordering'] . ', ' .
            sqlValue($mysqli, $row['nested_position']) . ', ' .
            sqlValue($mysqli, $row['class']) . ', ' .
            sqlValue($mysqli, $row['options']) .
            ')';

        if (!$mysqli->query($sql)) {
            throw new RuntimeException('Insert nordic row failed: ' . $mysqli->error);
        }

        $oldRowId = (int) $row['id'];
        $rowIdMap[$oldRowId] = (int) $mysqli->insert_id;
        $rowParentColOld[$oldRowId] = $row['parent_id'] === null ? null : (int) $row['parent_id'];
    }

    $colIdMap = [];
    foreach ($modernCols as $col) {
        $oldRowId = (int) $col['row_id'];
        if (!isset($rowIdMap[$oldRowId])) {
            throw new RuntimeException('Missing row map for old row id ' . $oldRowId);
        }

        $sql = "INSERT INTO {$tCols} (row_id, title, name, type, ordering, tag, class, wrapper, options) VALUES (" .
            (int) $rowIdMap[$oldRowId] . ', ' .
            sqlValue($mysqli, $col['title']) . ', ' .
            sqlValue($mysqli, $col['name']) . ', ' .
            sqlValue($mysqli, $col['type']) . ', ' .
            (int) $col['ordering'] . ', ' .
            sqlValue($mysqli, $col['tag']) . ', ' .
            sqlValue($mysqli, $col['class']) . ', ' .
            sqlValue($mysqli, $col['wrapper']) . ', ' .
            sqlValue($mysqli, $col['options']) .
            ')';

        if (!$mysqli->query($sql)) {
            throw new RuntimeException('Insert nordic col failed: ' . $mysqli->error);
        }

        $colIdMap[(int) $col['id']] = (int) $mysqli->insert_id;
    }

    foreach ($rowIdMap as $oldRowId => $newRowId) {
        $oldParentCol = $rowParentColOld[$oldRowId];
        if ($oldParentCol === null) {
            continue;
        }

        if (!isset($colIdMap[$oldParentCol])) {
            throw new RuntimeException('Missing col map for old parent col id ' . $oldParentCol);
        }

        $newParentCol = (int) $colIdMap[$oldParentCol];
        if (!$mysqli->query("UPDATE {$tRows} SET parent_id={$newParentCol} WHERE id=" . (int) $newRowId)) {
            throw new RuntimeException('Update nested row parent failed: ' . $mysqli->error);
        }
    }

    foreach ($modernBinds as $bind) {
        $sql = "INSERT INTO {$tBinds} (bind_id, template, is_enabled, page_id, position, ordering) VALUES (" .
            (int) $bind['bind_id'] . ", 'nordic', " .
            (int) $bind['is_enabled'] . ', ' .
            (int) $bind['page_id'] . ', ' .
            sqlValue($mysqli, $bind['position']) . ', ' .
            (int) $bind['ordering'] .
            ')';

        if (!$mysqli->query($sql)) {
            throw new RuntimeException('Insert nordic bind failed: ' . $mysqli->error);
        }
    }

    $mysqli->commit();

    $afterRows = fetchAllAssoc($mysqli, "SELECT id FROM {$tRows} WHERE template='nordic'");
    $afterRowIds = array_map(static function(array $r): int { return (int) $r['id']; }, $afterRows);
    $afterCols = [];
    if ($afterRowIds) {
        $ids = implode(',', array_map('intval', $afterRowIds));
        $afterCols = fetchAllAssoc($mysqli, "SELECT id FROM {$tCols} WHERE row_id IN ({$ids})");
    }
    $afterBinds = fetchAllAssoc($mysqli, "SELECT id FROM {$tBinds} WHERE template='nordic'");

    $report['after'] = [
        'nordic_rows' => count($afterRows),
        'nordic_cols' => count($afterCols),
        'nordic_binds' => count($afterBinds)
    ];
    $report['status'] = 'applied';

    file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "Applied successfully.\n";
    echo "Rollback SQL: {$rollbackFile}\n";
    echo "Report: {$reportFile}\n";
    echo 'After sync: rows=' . count($afterRows) . ', cols=' . count($afterCols) . ', binds=' . count($afterBinds) . "\n";
} catch (Throwable $e) {
    if ($mysqli->errno) {
        $mysqli->rollback();
    }
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}

$mysqli->close();
