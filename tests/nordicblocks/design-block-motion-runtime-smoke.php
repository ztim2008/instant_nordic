<?php

declare(strict_types=1);

$repo_root = dirname(__DIR__, 2);

$_SERVER['DOCUMENT_ROOT'] = $repo_root;

require_once $repo_root . '/bootstrap.php';

$source = $argv[1] ?? 'live';
$source = $source === 'package' ? 'package' : 'live';

$base = $source === 'package'
    ? $repo_root . '/packages/nordicblocks/package/system/controllers/nordicblocks'
    : $repo_root . '/system/controllers/nordicblocks';

require_once $base . '/libs/DesignBlockContractNormalizer.php';
require_once $base . '/libs/DesignBlockCssBuilder.php';
require_once $base . '/libs/DesignBlockElementRenderer.php';

if ($source === 'live') {
    require_once $base . '/libs/DesignBlockRenderPayloadBuilder.php';
}

function fail(string $message): void {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function assertSameValue($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        fail($message . ' Expected: ' . var_export($expected, true) . '; actual: ' . var_export($actual, true));
    }
}

function assertContainsText(string $needle, string $haystack, string $message): void {
    if (strpos($haystack, $needle) === false) {
        fail($message . ' Missing fragment: ' . $needle);
    }
}

$block = [
    'title' => 'Motion Smoke',
    'props' => [
        'meta' => [
            'label' => 'Motion Smoke',
            'status' => 'active',
        ],
        'content' => [
            'section' => [
                'name' => 'Motion Smoke',
                'tag' => 'section',
                'elements' => [
                    [
                        'id' => 'object_1',
                        'type' => 'object',
                        'desktop' => [
                            'box' => ['x' => 20, 'y' => 30, 'w' => 120, 'h' => 90],
                            'props' => [
                                'backgroundColor' => '#0f172a',
                                'hoverBackgroundMode' => 'gradient',
                                'hoverGradientFrom' => '#111827',
                                'hoverGradientTo' => '#2563eb',
                                'hoverBorderColor' => '#38bdf8',
                                'hoverScalePct' => 105,
                                'hoverLift' => 6,
                                'transitionDuration' => 360,
                                'motionTrigger' => 'entry',
                                'motionPreset' => 'slide-left',
                                'motionDuration' => 700,
                                'motionDelay' => 120,
                                'motionEasing' => 'snappy',
                                'motionAmount' => 24,
                            ],
                        ],
                        'tablet' => [
                            'box' => ['x' => 20, 'y' => 30, 'w' => 120, 'h' => 90],
                            'props' => [
                                'motionTrigger' => 'scroll',
                                'motionPreset' => 'fade-down',
                            ],
                        ],
                        'mobile' => [
                            'box' => ['x' => 20, 'y' => 30, 'w' => 120, 'h' => 90],
                            'props' => [
                                'motionTrigger' => 'none',
                                'motionPreset' => 'fade-up',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'design' => [
            'section' => [
                'background' => [
                    'mode' => 'solid',
                    'color' => '#f8fafc',
                ],
            ],
        ],
        'layout' => [
            'stage' => [
                'desktop' => ['contentWidth' => 1110, 'minHeight' => 680],
                'tablet' => ['contentWidth' => 672, 'minHeight' => 560],
                'mobile' => ['contentWidth' => 342, 'minHeight' => 440],
            ],
        ],
        'data' => ['source' => ['type' => 'manual']],
        'entities' => ['section' => ['kind' => 'surface', 'styleSlot' => 'section'], 'byElementId' => []],
        'runtime' => ['editor' => ['mode' => 'design_freeform', 'version' => 1], 'ssr' => ['version' => 1]],
    ],
];

$contract = NordicblocksDesignBlockContractNormalizer::normalize($block);
$props = $contract['content']['section']['elements'][0]['desktop']['props'] ?? [];

assertSameValue('gradient', $props['hoverBackgroundMode'] ?? null, 'Normalizer must keep hover background mode.');
assertSameValue('entry', $props['motionTrigger'] ?? null, 'Normalizer must keep motion trigger.');
assertSameValue('slide-left', $props['motionPreset'] ?? null, 'Normalizer must keep motion preset.');
assertSameValue(700, $props['motionDuration'] ?? null, 'Normalizer must keep motion duration.');
assertSameValue(360, $props['transitionDuration'] ?? null, 'Normalizer must keep transition duration.');

$payload = $source === 'live'
    ? NordicblocksDesignBlockRenderPayloadBuilder::build($contract, [
        'blockId' => 999,
        'blockUid' => 'motion_smoke',
    ])
    : [
        'sectionId' => 'nb-design-motion-smoke',
        'section' => [
            'name' => (string) ($contract['content']['section']['name'] ?? 'Motion Smoke'),
            'tag' => (string) ($contract['content']['section']['tag'] ?? 'section'),
            'background' => is_array($contract['design']['section']['background'] ?? null) ? $contract['design']['section']['background'] : [],
        ],
        'stage' => is_array($contract['layout']['stage'] ?? null) ? $contract['layout']['stage'] : [],
        'elements' => is_array($contract['content']['section']['elements'] ?? null) ? $contract['content']['section']['elements'] : [],
        'flatElements' => is_array($contract['content']['section']['elements'] ?? null) ? $contract['content']['section']['elements'] : [],
    ];

if ($source !== 'live') {
    $payload['css'] = NordicblocksDesignBlockCssBuilder::build($payload);
}

$css = (string) ($payload['css']['all'] ?? '');

assertContainsText('--nb-design-transition-duration:360ms;', $css, 'CSS must include hover transition duration variable.');
assertContainsText('--nb-design-motion-duration:700ms;', $css, 'CSS must include motion duration variable.');
assertContainsText('--nb-design-motion-delay:120ms;', $css, 'CSS must include motion delay variable.');
assertContainsText('--nb-design-motion-from:translate3d(24px,0,0);', $css, 'CSS must include slide-left transform.');
assertContainsText('--nb-design-hover-transform:translate3d(0,-6px,0) scale(1.05);', $css, 'CSS must include hover transform.');
assertContainsText('background:linear-gradient(135deg,#111827 0%,#2563eb 100%)', $css, 'CSS must include hover gradient for object.');

$html = '';

if ($source === 'live') {
    $render_file = $repo_root . '/system/controllers/nordicblocks/blocks/design_block/render.php';
    $block_contract = $contract;
    $block = ['id' => 999, 'title' => 'Motion Smoke', 'status' => 'active'];
    $block_uid = 'motion_smoke';

    ob_start();
    require $render_file;
    $html = (string) ob_get_clean();

    assertContainsText('data-motion="1"', $html, 'Rendered markup must expose motion flag.');
    assertContainsText('data-motion-trigger-desktop="entry"', $html, 'Rendered markup must expose desktop trigger.');
    assertContainsText('data-motion-preset-tablet="fade-down"', $html, 'Rendered markup must expose tablet preset.');
    assertContainsText('IntersectionObserver', $html, 'Render path must inject runtime motion script when motion elements exist.');
} else {
    $html = NordicblocksDesignBlockElementRenderer::render($payload);

    assertContainsText('data-motion="1"', $html, 'Package renderer must expose motion flag.');
    assertContainsText('data-motion-trigger-desktop="entry"', $html, 'Package renderer must expose desktop trigger.');
    assertContainsText('data-motion-preset-tablet="fade-down"', $html, 'Package renderer must expose tablet preset.');
}

fwrite(STDOUT, 'OK: design-block motion runtime smoke (' . $source . ')' . PHP_EOL);