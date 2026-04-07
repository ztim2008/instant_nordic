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

$rollbackFile = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--file=') === 0) {
        $rollbackFile = substr($arg, 7);
    }
}

if (!$rollbackFile) {
    fwrite(STDERR, "Usage: php scripts/nordic-sync-layout-rollback.php --file=/absolute/path/to/rollback.sql\n");
    exit(1);
}

if (!is_file($rollbackFile)) {
    fwrite(STDERR, "Rollback SQL file not found: {$rollbackFile}\n");
    exit(1);
}

$sql = file_get_contents($rollbackFile);
if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, "Rollback SQL file is empty or unreadable: {$rollbackFile}\n");
    exit(1);
}

$mysqli = new mysqli((string) $config['db_host'], (string) $config['db_user'], (string) $config['db_pass'], (string) $config['db_base']);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "DB connect failed: {$mysqli->connect_error}\n");
    exit(1);
}

$charset = !empty($config['db_charset']) ? (string) $config['db_charset'] : 'utf8mb4';
$mysqli->set_charset($charset);

if (!$mysqli->multi_query($sql)) {
    fwrite(STDERR, "Rollback execution failed: {$mysqli->error}\n");
    $mysqli->close();
    exit(1);
}

while ($mysqli->more_results()) {
    if (!$mysqli->next_result()) {
        fwrite(STDERR, "Rollback execution failed: {$mysqli->error}\n");
        $mysqli->close();
        exit(1);
    }
}

$mysqli->close();

echo "Rollback applied successfully from {$rollbackFile}\n";
