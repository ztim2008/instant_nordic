#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require_once $rootDir . '/scripts/nordicblocks-scaffold-lib.php';
NordicblocksScaffoldStage1::bootstrap($rootDir);

$args = NordicblocksScaffoldStage1::parseCliArgs($argv);

if (!empty($args['help'])) {
    echo "NordicBlocks scaffold block stage 3\n";
    echo "Usage:\n";
    echo "  /opt/php84/bin/php scripts/nordicblocks-scaffold-block.php --slug=<slug> --title=\"Title\" --family=<family> --profile=<profile> [--json]\n";
    echo "  /opt/php84/bin/php scripts/nordicblocks-scaffold-block.php --spec=/abs/path/spec.json [--json]\n";
    echo "  /opt/php84/bin/php scripts/nordicblocks-scaffold-block.php --slug=<slug> --title=\"Title\" --family=<family> --profile=<profile> --apply --checkpoint=<snapshot/tag> [--json]\n";
    echo "\n";
    echo "Without --apply the script runs in dry-run mode only.\n";
    echo "Apply mode writes live/package block files and syncs checklist + family doc markers.\n";
    exit(0);
}

try {
    $report = NordicblocksScaffoldStage1::validateBlueprint(
        NordicblocksScaffoldStage1::buildBlueprint($args, $rootDir),
        $rootDir
    );

    if (!empty($args['apply']) && !in_array($report['status'], ['FAIL', 'FAILED_DS_GUARD'], true)) {
        $report['applied'] = NordicblocksScaffoldStage1::applyBlueprint($report, (string) ($args['checkpoint'] ?? ''), $rootDir);
    }

    if (!empty($args['json'])) {
        echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        echo NordicblocksScaffoldStage1::formatReport($report);
        if (!empty($report['plan'])) {
            echo "\nPlanned create files:\n";
            foreach ((array) ($report['plan']['create'] ?? []) as $path) {
                echo " - " . $path . "\n";
            }
            echo "\nPlanned patch points:\n";
            foreach ((array) ($report['plan']['patch'] ?? []) as $path) {
                echo " - " . $path . "\n";
            }
        }
        if (!empty($report['applied']['writtenFiles'])) {
            echo "\nWritten files:\n";
            foreach ((array) $report['applied']['writtenFiles'] as $path) {
                echo " - " . $path . "\n";
            }
            echo "\nCheckpoint: " . (string) ($report['applied']['checkpoint'] ?? '') . "\n";
        }
    }

    exit(in_array($report['status'], ['FAIL', 'FAILED_DS_GUARD'], true) ? 1 : 0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}