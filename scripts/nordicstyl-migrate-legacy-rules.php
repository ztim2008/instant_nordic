#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require $rootDir . '/bootstrap.php';

$apply = in_array('--apply', $argv, true);

$plan = [
    1 => [
        'type' => 'update',
        'from' => 'body > section.container:nth-of-type(1) > main.row > article.col-lg.order-2.mb-3 > div.icms-body-toolbox:nth-of-type(1) > h1',
        'to'   => 'node:content-category-title',
        'style_updates' => [
            ['device' => 'base', 'state' => 'default', 'prop' => 'color', 'value' => '#777777']
        ],
        'note' => 'category page title moved to stable node'
    ],
    2 => [
        'type' => 'update',
        'from' => '#desktop_device_type',
        'to'   => 'node:root',
        'note' => 'page root already has stable node'
    ],
    3 => [
        'type' => 'update',
        'from' => 'body > section.container:nth-of-type(1) > main.row > article.col-lg.order-2.mb-3 > div.content_datasets.mobile-menu-wrapper.my-3:nth-of-type(2) > ul.nav.nav-pills.pills-menu',
        'to'   => 'node:content-category-datasets-pills',
        'note' => 'category datasets panel now has stable node'
    ],
    11 => [
        'type' => 'update',
        'from' => '#widget-919 > div.icms-widget__content_list.mb-n3.mb-md-n4:nth-of-type(1) > div.col-md-6.col-lg-12.mb-3:nth-of-type(1)',
        'to'   => 'node:content-list-widget-953-item-0',
        'note' => 'homepage featured widget first slot moved to stable widget-slot node'
    ],
    12 => [
        'type' => 'delete',
        'from' => '#widget-links-953 > a.btn.btn-outline-info.btn-sm:nth-of-type(1)',
        'note' => 'legacy rule is empty and no longer affects output'
    ],
    15 => [
        'type' => 'update',
        'from' => '#icms-widget__tabbed_953_955_957_tabs a[data-id="953"][href="#widget-953"]',
        'to'   => 'node:widget-953-tab-link',
        'note' => 'tab link already has stable node'
    ]
];

$model = cmsCore::getModel('nordicstyl');
$results = [];

function getRuleStylesArray(array $rule): array {

    $stylesRaw = $rule['styles'] ?? '';
    $styles = is_array($stylesRaw) ? $stylesRaw : cmsModel::yamlToArray((string)$stylesRaw);

    return is_array($styles) ? $styles : [];
}

function hasPendingStyleUpdates(array $rule, array $styleUpdates): bool {

    if (!$styleUpdates) {
        return false;
    }

    $styles = getRuleStylesArray($rule);

    foreach ($styleUpdates as $update) {
        $device = (string)($update['device'] ?? 'base');
        $state = (string)($update['state'] ?? 'default');
        $prop = (string)($update['prop'] ?? '');
        $value = (string)($update['value'] ?? '');

        if ($prop === '') {
            continue;
        }

        if ((string)($styles[$device][$state][$prop] ?? '') !== $value) {
            return true;
        }
    }

    return false;
}

function buildUpdatedStylesYaml(array $rule, array $styleUpdates): string {

    $styles = getRuleStylesArray($rule);

    foreach ($styleUpdates as $update) {
        $device = (string)($update['device'] ?? 'base');
        $state = (string)($update['state'] ?? 'default');
        $prop = (string)($update['prop'] ?? '');
        $value = (string)($update['value'] ?? '');

        if ($prop === '') {
            continue;
        }

        if (!isset($styles[$device]) || !is_array($styles[$device])) {
            $styles[$device] = [];
        }

        if (!isset($styles[$device][$state]) || !is_array($styles[$device][$state])) {
            $styles[$device][$state] = [];
        }

        $styles[$device][$state][$prop] = $value;
    }

    return cmsModel::arrayToYaml($styles);
}

foreach ($plan as $ruleId => $action) {
    $rule = $model->getRule((int)$ruleId);

    if (!$rule) {
        $results[] = ['id' => $ruleId, 'status' => 'missing', 'message' => 'rule not found'];
        continue;
    }

    $currentPath = trim((string)($rule['path'] ?? ''));
    $expectedPath = (string)$action['from'];
    $styleUpdates = isset($action['style_updates']) && is_array($action['style_updates']) ? $action['style_updates'] : [];
    $needsStyleUpdate = hasPendingStyleUpdates($rule, $styleUpdates);

    if ($action['type'] === 'update' && $currentPath === (string)$action['to'] && !$needsStyleUpdate) {
        $results[] = ['id' => $ruleId, 'status' => 'already-migrated', 'message' => (string)$action['to']];
        continue;
    }

    if ($action['type'] === 'delete' && $currentPath === '') {
        $results[] = ['id' => $ruleId, 'status' => 'already-removed', 'message' => 'empty path'];
        continue;
    }

    if ($currentPath !== $expectedPath && $currentPath !== (string)$action['to']) {
        $results[] = ['id' => $ruleId, 'status' => 'skipped', 'message' => 'unexpected current path: ' . $currentPath];
        continue;
    }

    if (!$apply) {
        $suffix = $needsStyleUpdate ? ' | styles normalized' : '';
        $results[] = ['id' => $ruleId, 'status' => 'planned', 'message' => $action['type'] === 'delete' ? 'delete' : ((string)$action['to'] . $suffix)];
        continue;
    }

    if ($action['type'] === 'delete') {
        $ok = $model->deleteRule((int)$ruleId);
        $results[] = ['id' => $ruleId, 'status' => $ok ? 'deleted' : 'failed', 'message' => (string)$action['note']];
        continue;
    }

    $payload = ['path' => (string)$action['to']];
    if ($needsStyleUpdate) {
        $payload['styles'] = buildUpdatedStylesYaml($rule, $styleUpdates);
    }

    $ok = $model->updateRule((int)$ruleId, $payload);
    $results[] = ['id' => $ruleId, 'status' => $ok ? 'updated' : 'failed', 'message' => (string)$action['to'] . ' | ' . (string)$action['note']];
}

foreach ($results as $result) {
    echo sprintf(
        "%d | %s | %s\n",
        (int)$result['id'],
        (string)$result['status'],
        (string)$result['message']
    );
}

$legacyLeft = [];
foreach ($model->getRules(false) as $rule) {
    $path = trim((string)($rule['path'] ?? ''));
    if ($path !== '' && strncmp($path, 'node:', 5) !== 0 && $path !== ':root') {
        $legacyLeft[] = (int)($rule['id'] ?? 0) . ':' . $path;
    }
}

echo 'legacy-left=' . count($legacyLeft) . "\n";
if ($legacyLeft) {
    foreach ($legacyLeft as $line) {
        echo $line . "\n";
    }
}