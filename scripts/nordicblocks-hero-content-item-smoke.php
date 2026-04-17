<?php

require dirname(__DIR__) . '/bootstrap.php';

function smoke_fail($message, $code = 1) {
    fwrite(STDERR, "ERROR: {$message}\n");
    exit($code);
}

function smoke_assert($condition, $message) {
    if (!$condition) {
        smoke_fail($message);
    }
}

$model = cmsCore::getModel('nordicblocks');
$content_model = cmsCore::getModel('content');

if (!$model || !$content_model) {
    smoke_fail('Не удалось поднять модели nordicblocks/content');
}

$sample_ctype = '';
$sample_item = [];

foreach ((array) $content_model->getContentTypes() as $ctype) {
    $ctype_name = trim((string) ($ctype['name'] ?? ''));
    if ($ctype_name === '') {
        continue;
    }

    $probe_model = cmsCore::getModel('content');
    $probe_model->orderBy('date_pub', 'desc');
    $probe_model->limit(1);
    $items = $probe_model->getContentItems($ctype_name);
    if (!is_array($items) || !$items) {
        continue;
    }

    $candidate = reset($items);
    if (!is_array($candidate) || empty($candidate['id']) || trim((string) ($candidate['title'] ?? '')) === '') {
        continue;
    }

    $sample_ctype = $ctype_name;
    $sample_item = $candidate;
    break;
}

if ($sample_ctype === '' || !$sample_item) {
    smoke_fail('Не найден content type с хотя бы одной опубликованной записью для hero content_item smoke');
}

$block_id = (int) $model->createBlock('hero', 'Smoke Hero content_item');
if ($block_id <= 0) {
    smoke_fail('Не удалось создать временный hero-блок');
}

try {
    $block = $model->getBlockById($block_id);
    smoke_assert(is_array($block) && !empty($block['contract']), 'Не удалось получить contract временного hero-блока');

    $contract = (array) $block['contract'];
    $contract['content']['title'] = 'Manual title fallback';
    $contract['content']['subtitle'] = 'Manual subtitle fallback';
    $contract['content']['primaryButton']['url'] = '/fallback';
    $contract['data']['source'] = [
        'type' => 'content_item',
        'ctype' => $sample_ctype,
        'resolver' => [
            'mode' => 'by_id',
            'id'   => (int) $sample_item['id'],
        ],
    ];
    $contract['data']['bindings']['title']['field'] = 'title';
    $contract['data']['bindings']['subtitle']['field'] = 'user.nickname';
    $contract['data']['bindings']['date']['field'] = 'date_pub';
    $contract['data']['bindings']['views']['field'] = 'hits_count';
    $contract['data']['bindings']['comments']['field'] = 'comments_count';
    $contract['data']['bindings']['primaryButtonUrl']['field'] = 'record_url';
    $contract['data']['bindings']['image']['field'] = 'record_image_url';

    $model->saveBlockContract($block_id, 'Smoke Hero content_item', $contract);

    $hydrated_block = $model->hydrateBlockForRender($model->getBlockById($block_id), ['mode' => 'smoke']);
    $hydrated = (array) ($hydrated_block['contract'] ?? []);

    $hydrated_title = trim((string) ($hydrated['content']['title'] ?? ''));
    $expected_title = trim((string) ($sample_item['title'] ?? ''));
    smoke_assert($hydrated_title === $expected_title, 'Hydration не подменил hero title значением записи');

    $button_url = trim((string) ($hydrated['content']['primaryButton']['url'] ?? ''));
    smoke_assert($button_url !== '', 'Hydration не собрал primaryButton.url для hero content_item');

    $adapter = (array) ($hydrated['runtime']['adapter'] ?? []);
    smoke_assert(($adapter['source'] ?? '') === 'content_item', 'Runtime adapter source должен быть content_item');
    smoke_assert((int) ($adapter['recordId'] ?? 0) === (int) $sample_item['id'], 'Runtime adapter recordId не совпал с выбранной записью');

    $props = (array) ($hydrated_block['props'] ?? []);
    $block_contract = $hydrated;
    $block_type = 'hero';
    $block_uid = 'smoke_hero_content_item';
    $render_file = cmsConfig::get('root_path') . 'system/controllers/nordicblocks/blocks/hero/render.php';

    ob_start();
    include $render_file;
    $html = (string) ob_get_clean();

    smoke_assert($html !== '', 'Hero render вернул пустой HTML');
    smoke_assert(mb_strpos($html, htmlspecialchars($expected_title, ENT_QUOTES, 'UTF-8')) !== false, 'Hero render не содержит hydrated title');

    fwrite(STDOUT, "OK: hero content_item smoke passed for {$sample_ctype}#" . (int) $sample_item['id'] . "\n");
} finally {
    $model->deleteBlock($block_id);
}