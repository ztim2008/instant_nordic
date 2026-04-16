<?php

class NordicblocksBlockContractNormalizer {

    public static function normalize(array $block) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));

        if ($type === 'hero') {
            return self::normalizeHero($block);
        }

        return self::normalizeFallback($block, $type);
    }

    private static function normalizeHero(array $block) {
        $props = (array) ($block['props'] ?? []);

        $layout = self::normalizeSelect($props['layout'] ?? 'centered', ['centered', 'left', 'split'], 'centered');
        $theme  = self::normalizeSelect($props['theme'] ?? 'light', ['light', 'dark', 'accent'], 'light');

        $image = self::normalizeImagePayload($props['image'] ?? '');

        $contract = [
            'meta' => [
                'contractVersion' => 3,
                'blockType'       => 'hero',
                'schemaVersion'   => 1,
                'label'           => (string) ($block['title'] ?? 'Hero'),
                'status'          => (string) ($block['status'] ?? 'active'),
            ],
            'content' => [
                'eyebrow' => (string) ($props['eyebrow'] ?? ''),
                'title'   => (string) ($props['heading'] ?? 'Создавайте сайты быстро'),
                'subtitle'=> (string) ($props['subheading'] ?? 'Визуальный конструктор лендингов для InstantCMS'),
                'primaryButton' => [
                    'label' => (string) ($props['btn_primary_label'] ?? 'Начать бесплатно'),
                    'url'   => (string) ($props['btn_primary_url'] ?? '#'),
                ],
                'secondaryButton' => [
                    'label' => (string) ($props['btn_secondary_label'] ?? ''),
                    'url'   => (string) ($props['btn_secondary_url'] ?? '#'),
                ],
                'media' => [
                    'image' => (string) ($image['original'] ?? $image['display'] ?? ''),
                    'alt'   => (string) ($image['alt'] ?? ($props['image_alt'] ?? '')),
                ],
            ],
            'design' => [
                'section' => [
                    'theme' => $theme,
                ],
                'entities' => [
                    'eyebrow' => [],
                    'title' => [
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['title_size_desktop'] ?? 64, 12, 240, 64),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['title_size_mobile'] ?? 40, 12, 240, 40),
                        ],
                        'weight' => self::normalizeNumber($props['heading_weight'] ?? 900, 100, 900, 900),
                        'tag'    => self::normalizeSelect($props['heading_tag'] ?? 'h1', ['div', 'h1', 'h2', 'h3'], 'h1'),
                    ],
                    'subtitle' => [
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_desktop'] ?? 20, 10, 120, 20),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_mobile'] ?? 18, 10, 120, 18),
                        ],
                    ],
                    'primaryButton' => [
                        'style' => self::normalizeSelect($props['btn_primary_style'] ?? 'primary', ['primary', 'outline', 'ghost'], 'primary'),
                    ],
                    'secondaryButton' => [
                        'style' => self::normalizeSelect($props['btn_secondary_style'] ?? 'outline', ['primary', 'outline', 'ghost'], 'outline'),
                    ],
                    'mediaSurface' => [],
                ],
            ],
            'layout' => [
                'desktop' => [
                    'mode'        => $layout,
                    'contentWidth'=> self::normalizeNumber($props['content_width'] ?? 640, 280, 1440, 640),
                    'paddingTop'  => self::normalizeNumber($props['padding_top_desktop'] ?? 96, 0, 300, 96),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_desktop'] ?? 96, 0, 300, 96),
                    'minHeight'   => self::normalizeNumber($props['min_height_desktop'] ?? 0, 0, 1200, 0),
                ],
                'mobile' => [
                    'paddingTop'  => self::normalizeNumber($props['padding_top_mobile'] ?? 56, 0, 300, 56),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_mobile'] ?? 56, 0, 300, 56),
                    'minHeight'   => self::normalizeNumber($props['min_height_mobile'] ?? 0, 0, 1200, 0),
                ],
            ],
            'data' => [
                'source'    => 'manual',
                'bindings'  => [],
                'fallbacks' => [],
            ],
            'entities' => [
                'eyebrow' => ['kind' => 'text', 'styleSlot' => 'eyebrow'],
                'title' => ['kind' => 'text', 'styleSlot' => 'title'],
                'subtitle' => ['kind' => 'text', 'styleSlot' => 'subtitle'],
                'primaryButton' => ['kind' => 'button', 'styleSlot' => 'primaryButton'],
                'secondaryButton' => ['kind' => 'button', 'styleSlot' => 'secondaryButton'],
                'media' => ['kind' => 'media', 'styleSlot' => 'media'],
                'mediaSurface' => ['kind' => 'surface', 'styleSlot' => 'mediaSurface'],
            ],
            'runtime' => [
                'renderMode' => 'ssr',
                'cacheScope' => 'page',
                'featureFlags' => [
                    'useAdapter'             => false,
                    'useResponsiveOverrides' => true,
                ],
            ],
        ];

        return $contract;
    }

    private static function normalizeFallback(array $block, $type) {
        return [
            'meta' => [
                'contractVersion' => 3,
                'blockType'       => $type ?: 'unknown',
                'schemaVersion'   => 1,
                'label'           => (string) ($block['title'] ?? $type),
            ],
            'content'  => [],
            'design'   => ['section' => [], 'entities' => []],
            'layout'   => ['desktop' => [], 'mobile' => []],
            'data'     => ['source' => 'manual', 'bindings' => [], 'fallbacks' => []],
            'entities' => [],
            'runtime'  => ['renderMode' => 'ssr', 'cacheScope' => 'page', 'featureFlags' => []],
        ];
    }

    private static function normalizeImagePayload($value) {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (!is_array($value)) {
            $value = [
                'display'  => (string) $value,
                'original' => (string) $value,
                'alt'      => '',
            ];
        }

        return $value;
    }

    private static function normalizeNumber($value, $min, $max, $fallback) {
        if (!is_numeric($value)) {
            $value = $fallback;
        }

        $value = 0 + $value;
        if ($value < $min) {
            $value = $min;
        }
        if ($value > $max) {
            $value = $max;
        }

        return (int) round($value);
    }

    private static function normalizeSelect($value, array $allowed, $fallback) {
        $value = (string) $value;
        return in_array($value, $allowed, true) ? $value : $fallback;
    }
}