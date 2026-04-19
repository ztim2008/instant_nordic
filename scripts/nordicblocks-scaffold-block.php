#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require_once $rootDir . '/scripts/nordicblocks-scaffold-lib.php';
NordicblocksScaffoldStage1::bootstrap($rootDir);

$args = NordicblocksScaffoldStage1::parseCliArgs($argv);

if (!empty($args['help'])) {
    echo "NordicBlocks scaffold block stage 1\n";
    echo "Usage:\n";
    echo "  /opt/php84/bin/php scripts/nordicblocks-scaffold-block.php --slug=<slug> --title=\"Title\" --family=<family> --profile=<profile> [--json]\n";
    echo "  /opt/php84/bin/php scripts/nordicblocks-scaffold-block.php --spec=/abs/path/spec.json [--json]\n";
    echo "\n";
    echo "Stage 1 supports dry-run only and does not write files.\n";
    exit(0);
}

if (!empty($args['apply'])) {
    fwrite(STDERR, "Stage 1 scaffold supports dry-run only. Apply mode will be added in the next stage.\n");
    exit(2);
}

try {
    $report = NordicblocksScaffoldStage1::validateBlueprint(
        NordicblocksScaffoldStage1::buildBlueprint($args, $rootDir),
        $rootDir
    );

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
    }

    exit(in_array($report['status'], ['FAIL', 'FAILED_DS_GUARD'], true) ? 1 : 0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}