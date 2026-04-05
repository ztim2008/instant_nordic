#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$root = dirname(__DIR__);

require $root . '/bootstrap.php';

$helper_file = $root . '/templates/nordic/install_helpers/shell_migration.php';

if (!is_readable($helper_file)) {
    fwrite(STDERR, "Shell migration helper not found: {$helper_file}\n");
    exit(1);
}

require_once $helper_file;

$apply = in_array('--apply', $argv, true);
$json = in_array('--json', $argv, true);
$template = 'nordic';

foreach ($argv as $arg) {
    if (strpos($arg, '--template=') === 0) {
        $template = substr($arg, strlen('--template='));
    }
}

$config = cmsConfig::getInstance();
$db = cmsDatabase::getInstance();
$cache = cmsCache::getInstance();

function cli_print($payload, bool $json): void {
    if ($json) {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
        return;
    }

    foreach ($payload as $line) {
        if (is_array($line)) {
            echo implode("\t", $line) . PHP_EOL;
            continue;
        }

        echo $line . PHP_EOL;
    }
}

try {
    $plan = NordicShellMigration::buildPlan($db, $config, $template);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration planning failed: ' . $exception->getMessage() . PHP_EOL);
    exit(2);
}

$report = NordicShellMigration::createReport($plan, $apply ? 'apply' : 'dry-run');

if (!$apply) {
    if ($json) {
        cli_print($report, true);
        exit(0);
    }

    cli_print(NordicShellMigration::formatCliReport($report), false);
    exit(0);
}

try {
    $apply_report = NordicShellMigration::apply($db, $config, $cache, $template);
    cli_print($json ? $apply_report : NordicShellMigration::formatCliReport($apply_report), $json);
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration failed: ' . $exception->getMessage() . PHP_EOL);
    exit(3);
}
