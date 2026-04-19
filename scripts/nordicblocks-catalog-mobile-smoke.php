#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);
require $rootDir . '/bootstrap.php';

function nb_mobile_smoke_fail(string $message, int $code = 1): void {
    fwrite(STDERR, $message . "\n");
    exit($code);
}

function nb_mobile_smoke_assert(bool $condition, string $message): void {
    if (!$condition) {
        nb_mobile_smoke_fail($message);
    }
}

function nb_mobile_smoke_parse_args(array $argv): array {
    $options = [
        'url' => '',
        'block-id' => '',
        'expect-mobile-columns' => null,
        'expect-desktop-columns' => null,
        'expect-collection-mode' => '',
        'expect-items-per-page' => null,
        'json' => false,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--json') {
            $options['json'] = true;
            continue;
        }

        if (strpos($arg, '--') !== 0) {
            continue;
        }

        $pair = explode('=', substr($arg, 2), 2);
        $key = $pair[0] ?? '';
        $value = $pair[1] ?? '';

        if (!array_key_exists($key, $options)) {
            continue;
        }

        if (in_array($key, ['expect-mobile-columns', 'expect-desktop-columns', 'expect-items-per-page'], true)) {
            $options[$key] = $value !== '' ? (int) $value : null;
            continue;
        }

        $options[$key] = $value;
    }

    return $options;
}

function nb_mobile_smoke_fetch(string $url, string $userAgent): string {
    if (function_exists('curl_init')) {
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_HTTPHEADER => ['Accept: text/html,application/xhtml+xml'],
        ]);

        $html = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if (is_string($html) && $html !== '' && $status >= 200 && $status < 400) {
            return $html;
        }

        nb_mobile_smoke_fail('Не удалось получить HTML по URL ' . $url . ($error !== '' ? ': ' . $error : ''));
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: {$userAgent}\r\nAccept: text/html,application/xhtml+xml\r\n",
            'timeout' => 20,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $html = @file_get_contents($url, false, $context);
    if (!is_string($html) || $html === '') {
        nb_mobile_smoke_fail('Не удалось получить HTML по URL ' . $url);
    }

    return $html;
}

function nb_mobile_smoke_extract(string $html, string $blockId = ''): array {
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $query = "//section[@data-nb-block='catalog_browser']";
    if ($blockId !== '') {
        $query .= "[@id='" . htmlspecialchars($blockId, ENT_QUOTES, 'UTF-8') . "']";
    }

    $nodes = $xpath->query($query);
    nb_mobile_smoke_assert($nodes instanceof DOMNodeList && $nodes->length > 0, 'catalog_browser не найден в SSR HTML');

    $node = $nodes->item(0);
    nb_mobile_smoke_assert($node instanceof DOMElement, 'Не удалось прочитать catalog_browser node');

    $style = (string) $node->getAttribute('style');

    return [
        'id' => (string) $node->getAttribute('id'),
        'renderVersion' => (string) $node->getAttribute('data-nb-render-version'),
        'gridDesktop' => (int) $node->getAttribute('data-nb-grid-desktop'),
        'gridMobile' => (int) $node->getAttribute('data-nb-grid-mobile'),
        'collectionMode' => (string) $node->getAttribute('data-nb-collection-mode'),
        'itemsPerPage' => (int) $node->getAttribute('data-nb-items-per-page'),
        'styleHasDesktopGridVar' => strpos($style, '--nb-catalog-columns:') !== false,
        'styleHasMobileGridVar' => strpos($style, '--nb-catalog-columns-mobile:') !== false,
        'sectionsFound' => $nodes->length,
    ];
}

$model = cmsCore::getModel('nordicblocks');
if (!$model || !method_exists($model, 'getRenderCacheVersion')) {
    nb_mobile_smoke_fail('Не удалось поднять modelNordicblocks для mobile smoke');
}

$options = nb_mobile_smoke_parse_args($argv);
$config = cmsConfig::getInstance();
$url = trim((string) ($options['url'] ?: rtrim((string) $config->host, '/') . '/'));
$expectedRenderVersion = (string) $model->getRenderCacheVersion('catalog_browser');

$desktopUa = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36';
$mobileUa = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3 Mobile/15E148 Safari/604.1';

$desktopHtml = nb_mobile_smoke_fetch($url, $desktopUa);
$mobileHtml = nb_mobile_smoke_fetch($url, $mobileUa);

$desktop = nb_mobile_smoke_extract($desktopHtml, (string) $options['block-id']);
$mobile = nb_mobile_smoke_extract($mobileHtml, (string) $options['block-id']);

nb_mobile_smoke_assert($desktop['renderVersion'] !== '', 'В desktop SSR отсутствует data-nb-render-version');
nb_mobile_smoke_assert($mobile['renderVersion'] !== '', 'В mobile SSR отсутствует data-nb-render-version');
nb_mobile_smoke_assert($desktop['renderVersion'] === $expectedRenderVersion, 'Desktop SSR render version не совпал с model->getRenderCacheVersion');
nb_mobile_smoke_assert($mobile['renderVersion'] === $expectedRenderVersion, 'Mobile SSR render version не совпал с model->getRenderCacheVersion');
nb_mobile_smoke_assert($desktop['gridDesktop'] > 0, 'В desktop SSR отсутствует data-nb-grid-desktop');
nb_mobile_smoke_assert($desktop['gridMobile'] > 0, 'В desktop SSR отсутствует data-nb-grid-mobile');
nb_mobile_smoke_assert($desktop['styleHasDesktopGridVar'], 'В desktop SSR отсутствует CSS var --nb-catalog-columns');
nb_mobile_smoke_assert($desktop['styleHasMobileGridVar'], 'В desktop SSR отсутствует CSS var --nb-catalog-columns-mobile');
nb_mobile_smoke_assert($desktop['renderVersion'] === $mobile['renderVersion'], 'Desktop/Mobile SSR отдали разные render version');
nb_mobile_smoke_assert($desktop['gridDesktop'] === $mobile['gridDesktop'], 'Desktop/Mobile SSR отдали разные desktop grid markers');
nb_mobile_smoke_assert($desktop['gridMobile'] === $mobile['gridMobile'], 'Desktop/Mobile SSR отдали разные mobile grid markers');
nb_mobile_smoke_assert($desktop['collectionMode'] === $mobile['collectionMode'], 'Desktop/Mobile SSR отдали разные collection mode markers');

if ($options['expect-mobile-columns'] !== null) {
    nb_mobile_smoke_assert($desktop['gridMobile'] === (int) $options['expect-mobile-columns'], 'Mobile grid marker не совпал с ожидаемым значением');
}

if ($options['expect-desktop-columns'] !== null) {
    nb_mobile_smoke_assert($desktop['gridDesktop'] === (int) $options['expect-desktop-columns'], 'Desktop grid marker не совпал с ожидаемым значением');
}

if (!empty($options['expect-collection-mode'])) {
    nb_mobile_smoke_assert($desktop['collectionMode'] === (string) $options['expect-collection-mode'], 'Collection mode marker не совпал с ожидаемым значением');
}

if ($options['expect-items-per-page'] !== null) {
    nb_mobile_smoke_assert($desktop['itemsPerPage'] === (int) $options['expect-items-per-page'], 'Items per page marker не совпал с ожидаемым значением');
}

$payload = [
    'ok' => true,
    'url' => $url,
    'expectedRenderVersion' => $expectedRenderVersion,
    'desktop' => $desktop,
    'mobile' => $mobile,
];

if (!empty($options['json'])) {
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
    exit(0);
}

echo "Catalog mobile smoke: OK\n";
echo ' - url: ' . $url . "\n";
echo ' - block id: ' . ($desktop['id'] !== '' ? $desktop['id'] : '(first catalog_browser)') . "\n";
echo ' - sections found: ' . (int) $desktop['sectionsFound'] . "\n";
echo ' - render version: ' . $expectedRenderVersion . "\n";
echo ' - grid desktop/mobile: ' . (int) $desktop['gridDesktop'] . '/' . (int) $desktop['gridMobile'] . "\n";
echo ' - collection mode: ' . $desktop['collectionMode'] . ', items per page: ' . (int) $desktop['itemsPerPage'] . "\n";
echo " - UA parity: desktop/mobile markers match\n";

exit(0);