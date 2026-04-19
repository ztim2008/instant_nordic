#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require_once $rootDir . '/scripts/nordicblocks-scaffold-lib.php';
NordicblocksScaffoldStage1::bootstrap($rootDir);

$args = NordicblocksScaffoldStage1::parseCliArgs($argv);

if (!empty($args['help'])) {
    echo "NordicBlocks validate block stage 3\n";
    echo "Usage:\n";
    echo "  /opt/php84/bin/php scripts/nordicblocks-validate-block.php --block=<slug> [--json]\n";
    echo "  /opt/php84/bin/php scripts/nordicblocks-validate-block.php --slug=<slug> --title=\"Title\" --family=<family> --profile=<profile> [--json]\n";
    echo "  /opt/php84/bin/php scripts/nordicblocks-validate-block.php --spec=/abs/path/spec.json [--json]\n";
    echo "\n";
    echo "For existing blocks the validator distinguishes managed scaffold blocks from legacy debt.\n";
    echo "Stage 3 managed blocks are expected to pass through dynamic runtime integration without manual central registry patching.\n";
    exit(0);
}

try {
    if (!empty($args['block'])) {
        $report = NordicblocksScaffoldStage1::validateExistingBlock((string) $args['block'], $rootDir);
    } else {
        $report = NordicblocksScaffoldStage1::validateBlueprint(
            NordicblocksScaffoldStage1::buildBlueprint($args, $rootDir),
            $rootDir
        );
    }

    if (!empty($args['json'])) {
        echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        echo NordicblocksScaffoldStage1::formatReport($report);
    }

    exit(in_array($report['status'], ['FAIL', 'FAILED_DS_GUARD'], true) ? 1 : 0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}