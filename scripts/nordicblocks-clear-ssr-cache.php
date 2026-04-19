#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require $rootDir . '/bootstrap.php';

function nb_cache_cli_fail(string $message, int $code = 1): void {
    fwrite(STDERR, $message . "\n");
    exit($code);
}

function nb_cache_cli_has_flag(array $argv, string $flag): bool {
    return in_array($flag, $argv, true);
}

$model = cmsCore::getModel('nordicblocks');
if (!$model || !method_exists($model, 'getCacheStats') || !method_exists($model, 'clearAllCache')) {
    nb_cache_cli_fail('Не удалось поднять modelNordicblocks для очистки SSR cache');
}

$before = $model->getCacheStats();
$dryRun = nb_cache_cli_has_flag($argv, '--dry-run');
$json = nb_cache_cli_has_flag($argv, '--json');

if (!$dryRun) {
    $model->clearAllCache();
}

$after = $model->getCacheStats();
$payload = [
    'ok' => true,
    'dryRun' => $dryRun,
    'before' => $before,
    'after' => $after,
    'designVersion' => (string) $model->getDesignCacheVersion(),
    'rendererVersion' => (string) $model->getRenderCacheVersion('catalog_browser'),
    'flushedAt' => date('c'),
];

if ($json) {
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
    exit(0);
}

echo $dryRun ? "SSR cache dry-run: OK\n" : "SSR cache flush: OK\n";
echo ' - before total: ' . (int) ($before['total'] ?? 0) . ', active: ' . (int) ($before['active'] ?? 0) . "\n";
echo ' - before page/block/runtime: ' . (int) ($before['page'] ?? 0) . '/' . (int) ($before['block'] ?? 0) . '/' . (int) ($before['runtime'] ?? 0) . "\n";
echo ' - after total: ' . (int) ($after['total'] ?? 0) . ', active: ' . (int) ($after['active'] ?? 0) . "\n";
echo ' - design version: ' . (string) $payload['designVersion'] . "\n";
echo ' - catalog_browser renderer version: ' . (string) $payload['rendererVersion'] . "\n";

exit(0);