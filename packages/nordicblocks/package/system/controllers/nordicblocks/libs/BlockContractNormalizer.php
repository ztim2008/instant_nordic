<?php

class NordicblocksBlockContractNormalizer {

    public static function isContractPayload($payload) {
        if (!is_array($payload)) {
            return false;
        }

        if (isset($payload['meta']) && is_array($payload['meta']) && !empty($payload['meta']['contractVersion'])) {
            return true;
        }

        $required_roots = ['meta', 'content', 'design', 'layout', 'data', 'entities', 'runtime'];
        $matched_roots = 0;

        foreach ($required_roots as $key) {
            if (!array_key_exists($key, $payload) || !is_array($payload[$key])) {
                return false;
            }

            $matched_roots++;
        }

        return $matched_roots === count($required_roots);
    }

    public static function normalize(array $block) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
        $payload = (array) ($block['props'] ?? []);

        if (self::isContractPayload($payload)) {
            return self::normalizeStoredContract($block, $payload);
        }

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
                'animation' => [
                    'name'  => self::normalizeSelect($props['block_animation'] ?? 'none', ['none', 'fade-up', 'fade-in', 'zoom-in'], 'none'),
                    'delay' => self::normalizeNumber($props['block_animation_delay'] ?? 0, 0, 1500, 0),
                ],
                'featureFlags' => [
                    'useAdapter'             => false,
                    'useResponsiveOverrides' => true,
                ],
            ],
        ];

        return $contract;
    }

    private static function normalizeStoredContract(array $block, array $contract) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? ($contract['meta']['blockType'] ?? ''))));

        if ($type === 'hero') {
            return self::normalizeHero([
                'type'   => 'hero',
                'title'  => (string) ($block['title'] ?? ($contract['meta']['label'] ?? 'Hero')),
                'status' => (string) ($block['status'] ?? ($contract['meta']['status'] ?? 'active')),
                'props'  => self::denormalizeProps('hero', $contract),
            ]);
        }

        return $contract + self::normalizeFallback($block, $type);
    }

    public static function denormalizeProps($type, array $contract) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $type));
        if ($type !== 'hero') {
            return [];
        }

        return [
            'layout'                 => (string) ($contract['layout']['desktop']['mode'] ?? 'centered'),
            'theme'                  => (string) ($contract['design']['section']['theme'] ?? 'light'),
            'eyebrow'                => (string) ($contract['content']['eyebrow'] ?? ''),
            'heading'                => (string) ($contract['content']['title'] ?? ''),
            'subheading'             => (string) ($contract['content']['subtitle'] ?? ''),
            'btn_primary_label'      => (string) ($contract['content']['primaryButton']['label'] ?? ''),
            'btn_primary_url'        => (string) ($contract['content']['primaryButton']['url'] ?? '#'),
            'btn_secondary_label'    => (string) ($contract['content']['secondaryButton']['label'] ?? ''),
            'btn_secondary_url'      => (string) ($contract['content']['secondaryButton']['url'] ?? '#'),
            'image'                  => (string) ($contract['content']['media']['image'] ?? ''),
            'image_alt'              => (string) ($contract['content']['media']['alt'] ?? ''),
            'heading_tag'            => (string) ($contract['design']['entities']['title']['tag'] ?? 'h1'),
            'heading_weight'         => (string) ($contract['design']['entities']['title']['weight'] ?? '900'),
            'title_size_desktop'     => (string) ($contract['design']['entities']['title']['desktop']['fontSize'] ?? 64),
            'title_size_mobile'      => (string) ($contract['design']['entities']['title']['mobile']['fontSize'] ?? 40),
            'subtitle_size_desktop'  => (string) ($contract['design']['entities']['subtitle']['desktop']['fontSize'] ?? 20),
            'subtitle_size_mobile'   => (string) ($contract['design']['entities']['subtitle']['mobile']['fontSize'] ?? 18),
            'content_width'          => (string) ($contract['layout']['desktop']['contentWidth'] ?? 640),
            'padding_top_desktop'    => (string) ($contract['layout']['desktop']['paddingTop'] ?? 96),
            'padding_bottom_desktop' => (string) ($contract['layout']['desktop']['paddingBottom'] ?? 96),
            'padding_top_mobile'     => (string) ($contract['layout']['mobile']['paddingTop'] ?? 56),
            'padding_bottom_mobile'  => (string) ($contract['layout']['mobile']['paddingBottom'] ?? 56),
            'min_height_desktop'     => (string) ($contract['layout']['desktop']['minHeight'] ?? 0),
            'min_height_mobile'      => (string) ($contract['layout']['mobile']['minHeight'] ?? 0),
            'btn_primary_style'      => (string) ($contract['design']['entities']['primaryButton']['style'] ?? 'primary'),
            'btn_secondary_style'    => (string) ($contract['design']['entities']['secondaryButton']['style'] ?? 'outline'),
            'block_animation'        => (string) ($contract['runtime']['animation']['name'] ?? 'none'),
            'block_animation_delay'  => (string) ($contract['runtime']['animation']['delay'] ?? 0),
        ];
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