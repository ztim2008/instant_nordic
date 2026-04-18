<?php

class NordicblocksBlockContractNormalizer {

    private static $allowed_source_types = ['manual', 'content_item', 'content_list'];
    private static $allowed_list_sorts = ['date_pub_desc', 'date_pub_asc', 'title_asc', 'title_desc', 'hits_desc', 'hits_asc', 'comments_desc', 'comments_asc'];

    public static function supportsContractType($type) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $type));
        return in_array($type, ['hero', 'faq'], true);
    }

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

        if ($type === 'faq') {
            return self::normalizeFaq($block);
        }

        return self::normalizeFallback($block, $type);
    }

    private static function normalizeHero(array $block, array $stored_contract = []) {
        $props = (array) ($block['props'] ?? []);

        $layout = self::normalizeSelect($props['layout'] ?? 'centered', ['centered', 'left', 'split'], 'centered');
        $theme  = self::normalizeSelect($props['theme'] ?? 'light', ['light', 'dark', 'accent'], 'light');
        $background = self::normalizeBackgroundConfig($props);
        $data   = self::normalizeDataLayer('hero', (array) ($stored_contract['data'] ?? []));
        $use_adapter = self::isAdapterEnabled($data);

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
                'meta' => [
                    'category' => '',
                    'author'   => '',
                    'date'     => '',
                    'views'    => '',
                    'comments' => '',
                ],
            ],
            'design' => [
                'section' => [
                    'theme' => $theme,
                    'background' => $background,
                ],
                'entities' => [
                    'eyebrow' => [],
                    'title' => [
                        'visible' => self::normalizeBoolean($props['title_visible'] ?? '1', true),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['title_size_desktop'] ?? 64, 12, 240, 64),
                            'marginBottom' => self::normalizeNumber($props['title_margin_bottom_desktop'] ?? 16, 0, 240, 16),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['title_size_mobile'] ?? 40, 12, 240, 40),
                            'marginBottom' => self::normalizeNumber($props['title_margin_bottom_mobile'] ?? 14, 0, 240, 14),
                        ],
                        'weight' => self::normalizeNumber($props['heading_weight'] ?? 900, 100, 900, 900),
                        'tag'    => self::normalizeSelect($props['heading_tag'] ?? 'h1', ['div', 'h1', 'h2', 'h3'], 'h1'),
                    ],
                    'subtitle' => [
                        'visible' => self::normalizeBoolean($props['subtitle_visible'] ?? '1', true),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_desktop'] ?? 20, 10, 120, 20),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_desktop'] ?? 24, 0, 240, 24),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_mobile'] ?? 18, 10, 120, 18),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_mobile'] ?? 20, 0, 240, 20),
                        ],
                    ],
                    'meta' => [
                        'desktop' => [
                            'fontSize' => 14,
                            'marginBottom' => 24,
                        ],
                        'mobile' => [
                            'fontSize' => 13,
                            'marginBottom' => 20,
                        ],
                        'weight' => 600,
                        'color' => '',
                        'lineHeightPercent' => 140,
                        'letterSpacing' => 0,
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
            'data' => $data,
            'entities' => [
                'eyebrow' => ['kind' => 'text', 'styleSlot' => 'eyebrow'],
                'title' => ['kind' => 'text', 'styleSlot' => 'title'],
                'subtitle' => ['kind' => 'text', 'styleSlot' => 'subtitle'],
                'meta' => ['kind' => 'text', 'styleSlot' => 'meta'],
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
                        'useAdapter'             => $use_adapter,
                    'useResponsiveOverrides' => true,
                ],
            ],
        ];

        return self::mergeStoredContract($contract, $stored_contract);
    }

    private static function normalizeFaq(array $block, array $stored_contract = []) {
        $props = (array) ($block['props'] ?? []);
        $theme = self::normalizeSelect($props['theme'] ?? 'light', ['light', 'alt', 'dark'], 'light');
        $align = self::normalizeSelect($props['align'] ?? 'center', ['left', 'center'], 'center');
        $background = self::normalizeBackgroundConfig($props);
        $data  = self::normalizeDataLayer('faq', (array) ($stored_contract['data'] ?? []));
        $use_adapter = self::isAdapterEnabled($data);

        $contract = [
            'meta' => [
                'contractVersion' => 3,
                'blockType'       => 'faq',
                'schemaVersion'   => 1,
                'label'           => (string) ($block['title'] ?? 'FAQ'),
                'status'          => (string) ($block['status'] ?? 'active'),
            ],
            'content' => [
                'eyebrow' => (string) ($props['eyebrow'] ?? 'FAQ'),
                'title'   => (string) ($props['heading'] ?? 'Частые вопросы'),
                'subtitle'=> (string) ($props['intro'] ?? 'Коротко ответьте на самые частые вопросы, чтобы снять возражения до заявки.'),
                'items'   => self::normalizeFaqItems($props['items'] ?? []),
            ],
            'design' => [
                'section' => [
                    'theme' => $theme,
                    'background' => $background,
                ],
                'entities' => [
                    'eyebrow' => [],
                    'title' => [
                        'visible' => self::normalizeBoolean($props['title_visible'] ?? '1', true),
                        'color'   => self::normalizeFlatString($props['title_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['title_line_height_percent'] ?? 110, 80, 220, 110),
                        'letterSpacing' => self::normalizeNumber($props['title_letter_spacing'] ?? 0, -40, 80, 0),
                        'maxWidth' => self::normalizeNumber($props['title_max_width'] ?? 600, 240, 1440, 600),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['title_size_desktop'] ?? 48, 12, 160, 48),
                            'marginBottom' => self::normalizeNumber($props['title_margin_bottom_desktop'] ?? 0, 0, 240, 0),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['title_size_mobile'] ?? 32, 12, 160, 32),
                            'marginBottom' => self::normalizeNumber($props['title_margin_bottom_mobile'] ?? 0, 0, 240, 0),
                        ],
                        'weight' => self::normalizeNumber($props['heading_weight'] ?? 800, 100, 900, 800),
                        'tag'    => self::normalizeSelect($props['heading_tag'] ?? 'h2', ['div', 'h1', 'h2', 'h3'], 'h2'),
                    ],
                    'subtitle' => [
                        'visible' => self::normalizeBoolean($props['subtitle_visible'] ?? '1', true),
                        'color'   => self::normalizeFlatString($props['subtitle_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['subtitle_line_height_percent'] ?? 165, 80, 240, 165),
                        'letterSpacing' => self::normalizeNumber($props['subtitle_letter_spacing'] ?? 0, -40, 80, 0),
                        'maxWidth' => self::normalizeNumber($props['subtitle_max_width'] ?? 720, 240, 1440, 720),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_desktop'] ?? 18, 10, 80, 18),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_desktop'] ?? 32, 0, 240, 32),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_mobile'] ?? 16, 10, 80, 16),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_mobile'] ?? 24, 0, 240, 24),
                        ],
                    ],
                    'itemTitle' => [
                        'color' => self::normalizeFlatString($props['item_title_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['item_title_line_height_percent'] ?? 135, 80, 220, 135),
                        'letterSpacing' => self::normalizeNumber($props['item_title_letter_spacing'] ?? 0, -40, 80, 0),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['item_title_size_desktop'] ?? 18, 10, 80, 18),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['item_title_size_mobile'] ?? 17, 10, 80, 17),
                        ],
                        'weight' => self::normalizeNumber($props['item_title_weight'] ?? 700, 100, 900, 700),
                    ],
                    'itemText' => [
                        'color' => self::normalizeFlatString($props['item_text_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['item_text_line_height_percent'] ?? 170, 80, 260, 170),
                        'letterSpacing' => self::normalizeNumber($props['item_text_letter_spacing'] ?? 0, -40, 80, 0),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['item_text_size_desktop'] ?? 16, 10, 80, 16),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['item_text_size_mobile'] ?? 15, 10, 80, 15),
                        ],
                    ],
                    'itemSurface' => [
                        'variant' => self::normalizeSelect($props['item_surface_variant'] ?? 'card', ['card', 'plain'], 'card'),
                    ],
                ],
            ],
            'layout' => [
                'desktop' => [
                    'align'       => $align,
                    'contentWidth'=> self::normalizeNumber($props['content_width'] ?? 760, 320, 1440, 760),
                    'paddingTop'  => self::normalizeNumber($props['padding_top_desktop'] ?? 88, 0, 300, 88),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_desktop'] ?? 88, 0, 300, 88),
                ],
                'mobile' => [
                    'paddingTop'  => self::normalizeNumber($props['padding_top_mobile'] ?? 56, 0, 300, 56),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_mobile'] ?? 56, 0, 300, 56),
                ],
            ],
            'data' => $data,
            'entities' => [
                'eyebrow'    => ['kind' => 'text', 'styleSlot' => 'eyebrow'],
                'title'      => ['kind' => 'text', 'styleSlot' => 'title'],
                'subtitle'   => ['kind' => 'text', 'styleSlot' => 'subtitle'],
                'items'      => ['kind' => 'repeater', 'styleSlot' => 'items'],
                'itemSurface'=> ['kind' => 'surface', 'styleSlot' => 'itemSurface'],
                'itemTitle'  => ['kind' => 'text', 'styleSlot' => 'itemTitle'],
                'itemText'   => ['kind' => 'text', 'styleSlot' => 'itemText'],
            ],
            'runtime' => [
                'renderMode' => 'ssr',
                'cacheScope' => 'page',
                'animation' => [
                    'name'  => self::normalizeSelect($props['block_animation'] ?? 'none', ['none', 'fade-up', 'fade-in', 'zoom-in'], 'none'),
                    'delay' => self::normalizeNumber($props['block_animation_delay'] ?? 0, 0, 1500, 0),
                ],
                'disclosure' => [
                    'openFirst' => self::normalizeBoolean($props['open_first'] ?? '1', true),
                ],
                'featureFlags' => [
                    'useAdapter'             => $use_adapter,
                    'useResponsiveOverrides' => true,
                    'useRepeater'            => true,
                ],
            ],
        ];

        return self::mergeStoredContract($contract, $stored_contract);
    }

    private static function normalizeStoredContract(array $block, array $contract) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? ($contract['meta']['blockType'] ?? ''))));

        if ($type === 'hero') {
            return self::normalizeHero([
                'type'   => 'hero',
                'title'  => (string) ($block['title'] ?? ($contract['meta']['label'] ?? 'Hero')),
                'status' => (string) ($block['status'] ?? ($contract['meta']['status'] ?? 'active')),
                'props'  => self::denormalizeProps('hero', $contract),
            ], $contract);
        }

        if ($type === 'faq') {
            return self::normalizeFaq([
                'type'   => 'faq',
                'title'  => (string) ($block['title'] ?? ($contract['meta']['label'] ?? 'FAQ')),
                'status' => (string) ($block['status'] ?? ($contract['meta']['status'] ?? 'active')),
                'props'  => self::denormalizeProps('faq', $contract),
            ], $contract);
        }

        return self::mergeStoredContract(self::normalizeFallback($block, $type), $contract);
    }

    public static function denormalizeProps($type, array $contract) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $type));

        if ($type === 'hero') {
            return [
                'layout'                 => (string) ($contract['layout']['desktop']['mode'] ?? 'centered'),
                'theme'                  => (string) ($contract['design']['section']['theme'] ?? 'light'),
                'background_mode'        => (string) ($contract['design']['section']['background']['mode'] ?? 'theme'),
                'background_color'       => (string) ($contract['design']['section']['background']['color'] ?? ''),
                'background_gradient_from' => (string) ($contract['design']['section']['background']['gradientFrom'] ?? ''),
                'background_gradient_to' => (string) ($contract['design']['section']['background']['gradientTo'] ?? ''),
                'background_gradient_angle' => (string) ($contract['design']['section']['background']['gradientAngle'] ?? 135),
                'background_image'       => (string) ($contract['design']['section']['background']['image'] ?? ''),
                'background_image_position' => (string) ($contract['design']['section']['background']['imagePosition'] ?? 'center center'),
                'background_image_size'  => (string) ($contract['design']['section']['background']['imageSize'] ?? 'cover'),
                'background_image_repeat' => (string) ($contract['design']['section']['background']['imageRepeat'] ?? 'no-repeat'),
                'background_overlay_color' => (string) ($contract['design']['section']['background']['overlayColor'] ?? '#0f172a'),
                'background_overlay_opacity' => (string) ($contract['design']['section']['background']['overlayOpacity'] ?? 45),
                'eyebrow'                => (string) ($contract['content']['eyebrow'] ?? ''),
                'heading'                => (string) ($contract['content']['title'] ?? ''),
                'subheading'             => (string) ($contract['content']['subtitle'] ?? ''),
                'title_visible'          => !empty($contract['design']['entities']['title']['visible']) ? '1' : '0',
                'subtitle_visible'       => !empty($contract['design']['entities']['subtitle']['visible']) ? '1' : '0',
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
                'title_margin_bottom_desktop' => (string) ($contract['design']['entities']['title']['desktop']['marginBottom'] ?? 16),
                'title_margin_bottom_mobile' => (string) ($contract['design']['entities']['title']['mobile']['marginBottom'] ?? 14),
                'subtitle_size_desktop'  => (string) ($contract['design']['entities']['subtitle']['desktop']['fontSize'] ?? 20),
                'subtitle_size_mobile'   => (string) ($contract['design']['entities']['subtitle']['mobile']['fontSize'] ?? 18),
                'subtitle_margin_bottom_desktop' => (string) ($contract['design']['entities']['subtitle']['desktop']['marginBottom'] ?? 24),
                'subtitle_margin_bottom_mobile' => (string) ($contract['design']['entities']['subtitle']['mobile']['marginBottom'] ?? 20),
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

        if ($type === 'faq') {
            return [
                'theme'                   => (string) ($contract['design']['section']['theme'] ?? 'light'),
                'background_mode'         => (string) ($contract['design']['section']['background']['mode'] ?? 'theme'),
                'background_color'        => (string) ($contract['design']['section']['background']['color'] ?? ''),
                'background_gradient_from'=> (string) ($contract['design']['section']['background']['gradientFrom'] ?? ''),
                'background_gradient_to'  => (string) ($contract['design']['section']['background']['gradientTo'] ?? ''),
                'background_gradient_angle' => (string) ($contract['design']['section']['background']['gradientAngle'] ?? 135),
                'background_image'        => (string) ($contract['design']['section']['background']['image'] ?? ''),
                'background_image_position' => (string) ($contract['design']['section']['background']['imagePosition'] ?? 'center center'),
                'background_image_size'   => (string) ($contract['design']['section']['background']['imageSize'] ?? 'cover'),
                'background_image_repeat' => (string) ($contract['design']['section']['background']['imageRepeat'] ?? 'no-repeat'),
                'background_overlay_color'=> (string) ($contract['design']['section']['background']['overlayColor'] ?? '#0f172a'),
                'background_overlay_opacity' => (string) ($contract['design']['section']['background']['overlayOpacity'] ?? 45),
                'eyebrow'                 => (string) ($contract['content']['eyebrow'] ?? ''),
                'heading'                 => (string) ($contract['content']['title'] ?? ''),
                'intro'                   => (string) ($contract['content']['subtitle'] ?? ''),
                'items'                   => is_array($contract['content']['items'] ?? null) ? $contract['content']['items'] : [],
                'open_first'              => !empty($contract['runtime']['disclosure']['openFirst']) ? '1' : '0',
                'title_visible'           => !empty($contract['design']['entities']['title']['visible']) ? '1' : '0',
                'subtitle_visible'        => !empty($contract['design']['entities']['subtitle']['visible']) ? '1' : '0',
                'heading_tag'             => (string) ($contract['design']['entities']['title']['tag'] ?? 'h2'),
                'heading_weight'          => (string) ($contract['design']['entities']['title']['weight'] ?? '800'),
                'title_size_desktop'      => (string) ($contract['design']['entities']['title']['desktop']['fontSize'] ?? 48),
                'title_size_mobile'       => (string) ($contract['design']['entities']['title']['mobile']['fontSize'] ?? 32),
                'title_margin_bottom_desktop' => (string) ($contract['design']['entities']['title']['desktop']['marginBottom'] ?? 0),
                'title_margin_bottom_mobile' => (string) ($contract['design']['entities']['title']['mobile']['marginBottom'] ?? 0),
                'title_color'             => (string) ($contract['design']['entities']['title']['color'] ?? ''),
                'title_line_height_percent' => (string) ($contract['design']['entities']['title']['lineHeightPercent'] ?? 110),
                'title_letter_spacing'    => (string) ($contract['design']['entities']['title']['letterSpacing'] ?? 0),
                'title_max_width'         => (string) ($contract['design']['entities']['title']['maxWidth'] ?? 600),
                'subtitle_size_desktop'   => (string) ($contract['design']['entities']['subtitle']['desktop']['fontSize'] ?? 18),
                'subtitle_size_mobile'    => (string) ($contract['design']['entities']['subtitle']['mobile']['fontSize'] ?? 16),
                'subtitle_margin_bottom_desktop' => (string) ($contract['design']['entities']['subtitle']['desktop']['marginBottom'] ?? 32),
                'subtitle_margin_bottom_mobile' => (string) ($contract['design']['entities']['subtitle']['mobile']['marginBottom'] ?? 24),
                'subtitle_color'          => (string) ($contract['design']['entities']['subtitle']['color'] ?? ''),
                'subtitle_line_height_percent' => (string) ($contract['design']['entities']['subtitle']['lineHeightPercent'] ?? 165),
                'subtitle_letter_spacing' => (string) ($contract['design']['entities']['subtitle']['letterSpacing'] ?? 0),
                'subtitle_max_width'      => (string) ($contract['design']['entities']['subtitle']['maxWidth'] ?? 720),
                'item_title_size_desktop' => (string) ($contract['design']['entities']['itemTitle']['desktop']['fontSize'] ?? 18),
                'item_title_size_mobile'  => (string) ($contract['design']['entities']['itemTitle']['mobile']['fontSize'] ?? 17),
                'item_title_weight'       => (string) ($contract['design']['entities']['itemTitle']['weight'] ?? '700'),
                'item_title_color'        => (string) ($contract['design']['entities']['itemTitle']['color'] ?? ''),
                'item_title_line_height_percent' => (string) ($contract['design']['entities']['itemTitle']['lineHeightPercent'] ?? 135),
                'item_title_letter_spacing' => (string) ($contract['design']['entities']['itemTitle']['letterSpacing'] ?? 0),
                'item_text_size_desktop'  => (string) ($contract['design']['entities']['itemText']['desktop']['fontSize'] ?? 16),
                'item_text_size_mobile'   => (string) ($contract['design']['entities']['itemText']['mobile']['fontSize'] ?? 15),
                'item_text_color'         => (string) ($contract['design']['entities']['itemText']['color'] ?? ''),
                'item_text_line_height_percent' => (string) ($contract['design']['entities']['itemText']['lineHeightPercent'] ?? 170),
                'item_text_letter_spacing' => (string) ($contract['design']['entities']['itemText']['letterSpacing'] ?? 0),
                'item_surface_variant'    => (string) ($contract['design']['entities']['itemSurface']['variant'] ?? 'card'),
                'content_width'           => (string) ($contract['layout']['desktop']['contentWidth'] ?? 760),
                'padding_top_desktop'     => (string) ($contract['layout']['desktop']['paddingTop'] ?? 88),
                'padding_bottom_desktop'  => (string) ($contract['layout']['desktop']['paddingBottom'] ?? 88),
                'padding_top_mobile'      => (string) ($contract['layout']['mobile']['paddingTop'] ?? 56),
                'padding_bottom_mobile'   => (string) ($contract['layout']['mobile']['paddingBottom'] ?? 56),
                'align'                   => (string) ($contract['layout']['desktop']['align'] ?? 'center'),
                'block_animation'         => (string) ($contract['runtime']['animation']['name'] ?? 'none'),
                'block_animation_delay'   => (string) ($contract['runtime']['animation']['delay'] ?? 0),
            ];
        }

        return [];
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
            'data'     => ['source' => ['type' => 'manual'], 'bindings' => [], 'fallbacks' => [], 'meta' => []],
            'entities' => [],
            'runtime'  => ['renderMode' => 'ssr', 'cacheScope' => 'page', 'featureFlags' => []],
        ];
    }

    private static function normalizeDataLayer($type, array $data) {
        $normalized = [
            'source'    => self::normalizeSourceConfig($data['source'] ?? 'manual'),
            'bindings'  => self::normalizeBindings($type, is_array($data['bindings'] ?? null) ? $data['bindings'] : []),
            'fallbacks' => is_array($data['fallbacks'] ?? null) ? $data['fallbacks'] : [],
            'meta'      => is_array($data['meta'] ?? null) ? $data['meta'] : [],
            'listSource'=> self::normalizeListSource((array) ($data['listSource'] ?? [])),
        ];

        if ($type !== 'faq') {
            $normalized['listSource'] = self::normalizeListSource([]);
        }

        return $normalized;
    }

    private static function mergeStoredContract(array $normalized, array $stored) {
        if (!$stored) {
            return $normalized;
        }

        return self::mergeContractArrays($stored, $normalized);
    }

    private static function mergeContractArrays($base, $overlay) {
        if (!is_array($base) || !is_array($overlay)) {
            return $overlay;
        }

        if (self::isListArray($base) || self::isListArray($overlay)) {
            return $overlay;
        }

        $merged = $base;

        foreach ($overlay as $key => $value) {
            if (array_key_exists($key, $merged)) {
                $merged[$key] = self::mergeContractArrays($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    private static function isListArray(array $value) {
        if ($value === []) {
            return false;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    private static function normalizeSourceConfig($source) {
        if (is_string($source)) {
            $source = ['type' => $source];
        }

        if (!is_array($source)) {
            $source = [];
        }

        $type = self::normalizeSelect((string) ($source['type'] ?? 'manual'), self::$allowed_source_types, 'manual');
        $normalized = ['type' => $type];

        if (!empty($source['ctype'])) {
            $normalized['ctype'] = preg_replace('/[^a-z0-9_\-\{\}]/i', '', (string) $source['ctype']);
        }

        if (!empty($source['resolver']) && is_array($source['resolver'])) {
            $resolver = [];
            foreach ($source['resolver'] as $key => $value) {
                $clean_key = preg_replace('/[^a-z0-9_\-]/i', '', (string) $key);
                if ($clean_key === '') {
                    continue;
                }

                if (is_scalar($value) || $value === null) {
                    $resolver[$clean_key] = is_string($value) ? trim($value) : $value;
                }
            }

            if ($resolver) {
                $normalized['resolver'] = $resolver;
            }
        }

        if ($type === 'content_item') {
            $resolver = is_array($normalized['resolver'] ?? null) ? $normalized['resolver'] : [];
            $mode = self::normalizeSelect((string) ($resolver['mode'] ?? 'current'), ['current', 'by_id', 'latest'], 'current');
            $normalized['resolver'] = ['mode' => $mode];

            if ($mode === 'by_id') {
                $item_id = (int) ($resolver['id'] ?? $resolver['itemId'] ?? $resolver['item_id'] ?? 0);
                if ($item_id > 0) {
                    $normalized['resolver']['id'] = $item_id;
                }
            }
        }

        return $normalized;
    }

    private static function normalizeBindings($type, array $bindings) {
        if ($type !== 'hero') {
            return $bindings;
        }

        $defaults = self::getHeroBindingDefaults();
        $normalized = [];

        foreach ($defaults as $key => $config) {
            $binding = [];
            if (isset($bindings[$key]) && is_array($bindings[$key])) {
                $binding = $bindings[$key];
            } elseif ($key === 'primaryButtonUrl' && isset($bindings['primaryButton']['url']) && is_array($bindings['primaryButton']['url'])) {
                $binding = $bindings['primaryButton']['url'];
            }

            $normalized[$key] = self::normalizeBindingConfig($binding, $config);
        }

        return $normalized;
    }

    private static function normalizeBindingConfig(array $binding, array $defaults) {
        $formatters = ['plain_text', 'image_url', 'record_url', 'date_human', 'number'];

        return [
            'mode'          => self::normalizeSelect((string) ($binding['mode'] ?? $defaults['mode']), ['manual', 'bound', 'mixed'], $defaults['mode']),
            'field'         => self::normalizeFieldReference($binding['field'] ?? ''),
            'formatter'     => self::normalizeSelect((string) ($binding['formatter'] ?? $defaults['formatter']), $formatters, $defaults['formatter']),
            'emptyBehavior' => self::normalizeSelect((string) ($binding['emptyBehavior'] ?? $defaults['emptyBehavior']), ['fallback', 'hide', 'empty'], $defaults['emptyBehavior']),
        ];
    }

    private static function getHeroBindingDefaults() {
        return [
            'eyebrow' => ['mode' => 'mixed', 'formatter' => 'plain_text', 'emptyBehavior' => 'fallback'],
            'title' => ['mode' => 'bound', 'formatter' => 'plain_text', 'emptyBehavior' => 'fallback'],
            'subtitle' => ['mode' => 'mixed', 'formatter' => 'plain_text', 'emptyBehavior' => 'fallback'],
            'image' => ['mode' => 'mixed', 'formatter' => 'image_url', 'emptyBehavior' => 'fallback'],
            'imageAlt' => ['mode' => 'mixed', 'formatter' => 'plain_text', 'emptyBehavior' => 'fallback'],
            'category' => ['mode' => 'bound', 'formatter' => 'plain_text', 'emptyBehavior' => 'hide'],
            'author' => ['mode' => 'bound', 'formatter' => 'plain_text', 'emptyBehavior' => 'hide'],
            'date' => ['mode' => 'bound', 'formatter' => 'date_human', 'emptyBehavior' => 'hide'],
            'views' => ['mode' => 'bound', 'formatter' => 'number', 'emptyBehavior' => 'hide'],
            'comments' => ['mode' => 'bound', 'formatter' => 'number', 'emptyBehavior' => 'hide'],
            'primaryButtonUrl' => ['mode' => 'mixed', 'formatter' => 'record_url', 'emptyBehavior' => 'fallback'],
        ];
    }

    private static function normalizeListSource(array $config) {
        $map = is_array($config['map'] ?? null) ? $config['map'] : [];

        return [
            'type'          => self::normalizeSelect((string) ($config['type'] ?? 'manual'), ['manual', 'content_list'], 'manual'),
            'ctype'         => preg_replace('/[^a-z0-9_\-\{\}]/i', '', (string) ($config['ctype'] ?? '')),
            'limit'         => self::normalizeNumber($config['limit'] ?? 3, 1, 24, 3),
            'sort'          => self::normalizeSelect((string) ($config['sort'] ?? 'date_pub_desc'), self::$allowed_list_sorts, 'date_pub_desc'),
            'map'           => [
                'question' => self::normalizeFieldReference($map['question'] ?? 'title'),
                'answer'   => self::normalizeFieldReference($map['answer'] ?? ''),
            ],
            'emptyBehavior' => self::normalizeSelect((string) ($config['emptyBehavior'] ?? 'fallback'), ['fallback', 'empty'], 'fallback'),
        ];
    }

    private static function normalizeFieldReference($value) {
        return preg_replace('/[^a-z0-9_\.\-]/i', '', trim((string) $value));
    }

    private static function isAdapterEnabled(array $data) {
        $source_type = (string) ($data['source']['type'] ?? 'manual');
        if ($source_type !== 'manual') {
            return true;
        }

        $list_source_type = (string) ($data['listSource']['type'] ?? 'manual');
        return $list_source_type === 'content_list' && !empty($data['listSource']['ctype']);
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

    private static function normalizeFaqItems($value) {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (!is_array($value)) {
            $value = [];
        }

        $items = [];
        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['title'] ?? ($item['question'] ?? '')));
            $answer   = trim((string) ($item['text'] ?? ($item['answer'] ?? '')));

            if ($question === '' && $answer === '') {
                continue;
            }

            $items[] = self::buildFaqItemPayload($question, $answer);
        }

        return $items;
    }

    private static function buildFaqItemPayload($title, $text) {
        $title = trim((string) $title);
        $text  = trim((string) $text);

        return [
            'title'    => $title,
            'text'     => $text,
            'question' => $title,
            'answer'   => $text,
        ];
    }

    private static function normalizeBoolean($value, $fallback) {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $fallback;
    }

    private static function normalizeBackgroundConfig(array $props) {
        return [
            'mode' => self::normalizeSelect($props['background_mode'] ?? 'theme', ['theme', 'color', 'gradient', 'image'], 'theme'),
            'color' => self::normalizeFlatString($props['background_color'] ?? ''),
            'gradientFrom' => self::normalizeFlatString($props['background_gradient_from'] ?? ''),
            'gradientTo' => self::normalizeFlatString($props['background_gradient_to'] ?? ''),
            'gradientAngle' => self::normalizeNumber($props['background_gradient_angle'] ?? 135, 0, 360, 135),
            'image' => self::normalizeFlatString($props['background_image'] ?? ''),
            'imagePosition' => self::normalizeSelect($props['background_image_position'] ?? 'center center', ['center center', 'top center', 'bottom center', 'center left', 'center right', 'top left', 'top right', 'bottom left', 'bottom right'], 'center center'),
            'imageSize' => self::normalizeSelect($props['background_image_size'] ?? 'cover', ['cover', 'contain', 'auto'], 'cover'),
            'imageRepeat' => self::normalizeSelect($props['background_image_repeat'] ?? 'no-repeat', ['no-repeat', 'repeat', 'repeat-x', 'repeat-y'], 'no-repeat'),
            'overlayColor' => self::normalizeFlatString($props['background_overlay_color'] ?? '#0f172a'),
            'overlayOpacity' => self::normalizeNumber($props['background_overlay_opacity'] ?? 45, 0, 100, 45),
        ];
    }

    private static function normalizeFlatString($value, $max_length = 2048) {
        $value = trim((string) $value);
        if ($max_length > 0 && strlen($value) > $max_length) {
            $value = substr($value, 0, $max_length);
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