<?php

class NordicblocksBlockContractNormalizer {

    private static $allowed_source_types = ['manual', 'content_item', 'content_list'];
    private static $allowed_list_sorts = ['date_pub_desc', 'date_pub_asc', 'title_asc', 'title_desc', 'hits_desc', 'hits_asc', 'comments_desc', 'comments_asc'];

    private static function isCardCollectionType($type) {
        return in_array($type, ['content_feed', 'category_cards'], true);
    }

    public static function supportsContractType($type) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $type));
        return in_array($type, ['hero', 'faq', 'content_feed', 'category_cards'], true);
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

        if ($type === 'content_feed') {
            return self::normalizeContentFeed($block);
        }

        if ($type === 'category_cards') {
            return self::normalizeCategoryCards($block);
        }

        return self::normalizeFallback($block, $type);
    }

    private static function normalizeHero(array $block, array $stored_contract = []) {
        $props = (array) ($block['props'] ?? []);
        $stored_media_surface = is_array($stored_contract['design']['entities']['mediaSurface'] ?? null)
            ? $stored_contract['design']['entities']['mediaSurface']
            : [];

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
                    'eyebrow' => [
                        'desktop' => [
                            'fontSize' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_size_desktop'], 14), 10, 120, 14),
                            'marginBottom' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_margin_bottom_desktop'], 16), 0, 240, 16),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['eyebrow_weight_desktop', 'eyebrow_weight'], '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['eyebrow_color_desktop', 'eyebrow_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_line_height_percent_desktop', 'eyebrow_line_height_percent'], 140), 80, 240, 140),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_letter_spacing_desktop', 'eyebrow_letter_spacing'], 1), -40, 80, 1),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_size_mobile'], 13), 10, 120, 13),
                            'marginBottom' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_margin_bottom_mobile'], 14), 0, 240, 14),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['eyebrow_weight_mobile', 'eyebrow_weight'], '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['eyebrow_color_mobile', 'eyebrow_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_line_height_percent_mobile', 'eyebrow_line_height_percent'], 140), 80, 240, 140),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_letter_spacing_mobile', 'eyebrow_letter_spacing'], 1), -40, 80, 1),
                        ],
                        'textTransform' => self::normalizeSelect((string) self::coalesceProp($props, ['eyebrow_text_transform'], 'uppercase'), ['uppercase', 'none'], 'uppercase'),
                    ],
                    'title' => [
                        'visible' => self::normalizeBoolean($props['title_visible'] ?? '1', true),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['title_size_desktop'] ?? 64, 12, 240, 64),
                            'marginBottom' => self::normalizeNumber($props['title_margin_bottom_desktop'] ?? 16, 0, 240, 16),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['title_weight_desktop', 'heading_weight'], '900'), ['400', '500', '600', '700', '800', '900'], '900'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['title_color_desktop', 'title_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['title_line_height_percent_desktop', 'title_line_height_percent'], 110), 80, 220, 110),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['title_letter_spacing_desktop', 'title_letter_spacing'], 0), -40, 80, 0),
                            'maxWidth' => self::normalizeNumber(self::coalesceProp($props, ['title_max_width_desktop', 'title_max_width'], 600), 240, 1440, 600),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['title_size_mobile'] ?? 40, 12, 240, 40),
                            'marginBottom' => self::normalizeNumber($props['title_margin_bottom_mobile'] ?? 14, 0, 240, 14),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['title_weight_mobile', 'heading_weight'], '900'), ['400', '500', '600', '700', '800', '900'], '900'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['title_color_mobile', 'title_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['title_line_height_percent_mobile', 'title_line_height_percent'], 110), 80, 220, 110),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['title_letter_spacing_mobile', 'title_letter_spacing'], 0), -40, 80, 0),
                            'maxWidth' => self::normalizeNumber(self::coalesceProp($props, ['title_max_width_mobile', 'title_max_width'], 600), 240, 1440, 600),
                        ],
                        'tag'    => self::normalizeSelect($props['heading_tag'] ?? 'h1', ['div', 'h1', 'h2', 'h3'], 'h1'),
                    ],
                    'subtitle' => [
                        'visible' => self::normalizeBoolean($props['subtitle_visible'] ?? '1', true),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_desktop'] ?? 20, 10, 120, 20),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_desktop'] ?? 24, 0, 240, 24),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['subtitle_weight_desktop', 'subtitle_weight'], '400'), ['400', '500', '600', '700', '800', '900'], '400'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['subtitle_color_desktop', 'subtitle_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['subtitle_line_height_percent_desktop', 'subtitle_line_height_percent'], 165), 80, 240, 165),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['subtitle_letter_spacing_desktop', 'subtitle_letter_spacing'], 0), -40, 80, 0),
                            'maxWidth' => self::normalizeNumber(self::coalesceProp($props, ['subtitle_max_width_desktop', 'subtitle_max_width'], 720), 240, 1440, 720),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_mobile'] ?? 18, 10, 120, 18),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_mobile'] ?? 20, 0, 240, 20),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['subtitle_weight_mobile', 'subtitle_weight'], '400'), ['400', '500', '600', '700', '800', '900'], '400'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['subtitle_color_mobile', 'subtitle_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['subtitle_line_height_percent_mobile', 'subtitle_line_height_percent'], 165), 80, 240, 165),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['subtitle_letter_spacing_mobile', 'subtitle_letter_spacing'], 0), -40, 80, 0),
                            'maxWidth' => self::normalizeNumber(self::coalesceProp($props, ['subtitle_max_width_mobile', 'subtitle_max_width'], 720), 240, 1440, 720),
                        ],
                    ],
                    'meta' => [
                        'desktop' => [
                            'fontSize' => self::normalizeNumber(self::coalesceProp($props, ['meta_size_desktop'], 14), 10, 120, 14),
                            'marginBottom' => self::normalizeNumber(self::coalesceProp($props, ['meta_margin_bottom_desktop'], 24), 0, 240, 24),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['meta_weight_desktop', 'meta_weight'], '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['meta_color_desktop', 'meta_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['meta_line_height_percent_desktop', 'meta_line_height_percent'], 140), 80, 240, 140),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['meta_letter_spacing_desktop', 'meta_letter_spacing'], 0), -40, 80, 0),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber(self::coalesceProp($props, ['meta_size_mobile'], 13), 10, 120, 13),
                            'marginBottom' => self::normalizeNumber(self::coalesceProp($props, ['meta_margin_bottom_mobile'], 20), 0, 240, 20),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['meta_weight_mobile', 'meta_weight'], '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['meta_color_mobile', 'meta_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['meta_line_height_percent_mobile', 'meta_line_height_percent'], 140), 80, 240, 140),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['meta_letter_spacing_mobile', 'meta_letter_spacing'], 0), -40, 80, 0),
                        ],
                    ],
                    'buttonsText' => [
                        'desktop' => [
                            'fontSize' => self::normalizeNumber(self::coalesceProp($props, ['button_text_size_desktop'], 16), 10, 120, 16),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['button_text_weight_desktop', 'button_text_weight'], '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['button_text_color_desktop', 'button_text_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['button_text_line_height_percent_desktop', 'button_text_line_height_percent'], 120), 80, 220, 120),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['button_text_letter_spacing_desktop', 'button_text_letter_spacing'], 0), -40, 80, 0),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber(self::coalesceProp($props, ['button_text_size_mobile'], 15), 10, 120, 15),
                            'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['button_text_weight_mobile', 'button_text_weight'], '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                            'color' => self::normalizeFlatString(self::coalesceProp($props, ['button_text_color_mobile', 'button_text_color'], '')),
                            'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['button_text_line_height_percent_mobile', 'button_text_line_height_percent'], 120), 80, 220, 120),
                            'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['button_text_letter_spacing_mobile', 'button_text_letter_spacing'], 0), -40, 80, 0),
                        ],
                    ],
                    'media' => [
                        'aspectRatio' => self::normalizeSelect((string) self::coalesceProp($props, ['media_aspect_ratio'], '16:10'), ['auto', '16:10', '16:9', '4:3', '1:1', '3:4'], '16:10'),
                        'objectFit' => self::normalizeSelect((string) self::coalesceProp($props, ['media_object_fit'], 'cover'), ['cover', 'contain'], 'cover'),
                        'radius' => self::normalizeNumber(self::coalesceProp($props, ['media_radius'], 28), 0, 80, 28),
                    ],
                    'primaryButton' => [
                        'style' => self::normalizeSelect($props['btn_primary_style'] ?? 'primary', ['primary', 'outline', 'ghost'], 'primary'),
                    ],
                    'secondaryButton' => [
                        'style' => self::normalizeSelect($props['btn_secondary_style'] ?? 'outline', ['primary', 'outline', 'ghost'], 'outline'),
                    ],
                    'mediaSurface' => [
                        'backgroundMode' => self::normalizeSelect((string) self::coalesceProp($props, ['media_surface_background_mode'], $stored_media_surface['backgroundMode'] ?? (self::coalesceProp($props, ['media_surface_background_color'], $stored_media_surface['backgroundColor'] ?? '') !== '' ? 'solid' : 'transparent')), ['transparent', 'solid'], 'transparent'),
                        'backgroundColor' => self::normalizeFlatString(self::coalesceProp($props, ['media_surface_background_color'], $stored_media_surface['backgroundColor'] ?? '')),
                        'padding' => self::normalizeNumber(self::coalesceProp($props, ['media_surface_padding'], 0), 0, 80, 0),
                        'radius' => self::normalizeNumber(self::coalesceProp($props, ['media_surface_radius'], 28), 0, 100, 28),
                        'borderWidth' => self::normalizeNumber(self::coalesceProp($props, ['media_surface_border_width'], 0), 0, 20, 0),
                        'borderColor' => self::normalizeFlatString(self::coalesceProp($props, ['media_surface_border_color'], '')),
                        'shadow' => self::normalizeSelect((string) self::coalesceProp($props, ['media_surface_shadow'], 'lg'), ['none', 'sm', 'md', 'lg'], 'lg'),
                    ],
                ],
            ],
            'layout' => [
                'desktop' => [
                    'mode'        => $layout,
                    'contentWidth'=> self::normalizeNumber($props['content_width'] ?? 640, 280, 1440, 640),
                    'paddingTop'  => self::normalizeNumber($props['padding_top_desktop'] ?? 96, 0, 300, 96),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_desktop'] ?? 96, 0, 300, 96),
                    'minHeight'   => self::normalizeNumber($props['min_height_desktop'] ?? 0, 0, 1200, 0),
                    'contentGap'  => self::normalizeNumber(self::coalesceProp($props, ['content_gap_desktop'], 40), 0, 240, 40),
                    'actionsGap'  => self::normalizeNumber(self::coalesceProp($props, ['actions_gap_desktop'], 12), 0, 120, 12),
                ],
                'mobile' => [
                    'paddingTop'  => self::normalizeNumber($props['padding_top_mobile'] ?? 56, 0, 300, 56),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_mobile'] ?? 56, 0, 300, 56),
                    'minHeight'   => self::normalizeNumber($props['min_height_mobile'] ?? 0, 0, 1200, 0),
                    'contentGap'  => self::normalizeNumber(self::coalesceProp($props, ['content_gap_mobile'], 24), 0, 240, 24),
                    'actionsGap'  => self::normalizeNumber(self::coalesceProp($props, ['actions_gap_mobile'], 10), 0, 120, 10),
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

    private static function normalizeContentFeed(array $block, array $stored_contract = []) {
        $props = (array) ($block['props'] ?? []);
        $theme = self::normalizeSelect($props['theme'] ?? 'light', ['light', 'alt', 'dark'], 'light');
        $align = self::normalizeSelect($props['align'] ?? 'left', ['left', 'center'], 'left');
        $background = self::normalizeBackgroundConfig($props);
        $data = self::normalizeDataLayer('content_feed', (array) ($stored_contract['data'] ?? []));
        $use_adapter = self::isAdapterEnabled($data);

        $contract = [
            'meta' => [
                'contractVersion' => 3,
                'blockType'       => 'content_feed',
                'schemaVersion'   => 1,
                'label'           => (string) ($block['title'] ?? 'Лента новостей'),
                'status'          => (string) ($block['status'] ?? 'active'),
            ],
            'content' => [
                'title' => (string) ($props['heading'] ?? 'Последние новости'),
                'subtitle' => (string) ($props['intro'] ?? 'Короткая лента материалов, которую можно наполнить вручную или подключить к данным InstantCMS.'),
                'primaryButton' => [
                    'label' => (string) ($props['more_link_label'] ?? 'Все материалы'),
                    'url'   => (string) ($props['more_link_url'] ?? '/news'),
                ],
                'items' => self::normalizeContentFeedItems($props['items'] ?? []),
            ],
            'design' => [
                'section' => [
                    'theme' => $theme,
                    'background' => $background,
                ],
                'entities' => [
                    'title' => [
                        'visible' => self::normalizeBoolean($props['title_visible'] ?? '1', true),
                        'color'   => self::normalizeFlatString($props['title_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['title_line_height_percent'] ?? 110, 80, 220, 110),
                        'letterSpacing' => self::normalizeNumber($props['title_letter_spacing'] ?? 0, -40, 80, 0),
                        'maxWidth' => self::normalizeNumber($props['title_max_width'] ?? 760, 240, 1440, 760),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['title_size_desktop'] ?? 42, 12, 160, 42),
                            'marginBottom' => self::normalizeNumber($props['title_margin_bottom_desktop'] ?? 0, 0, 240, 0),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['title_size_mobile'] ?? 30, 12, 160, 30),
                            'marginBottom' => self::normalizeNumber($props['title_margin_bottom_mobile'] ?? 0, 0, 240, 0),
                        ],
                        'weight' => self::normalizeNumber($props['heading_weight'] ?? 800, 100, 900, 800),
                        'tag'    => self::normalizeSelect($props['heading_tag'] ?? 'h2', ['div', 'h1', 'h2', 'h3'], 'h2'),
                    ],
                    'subtitle' => [
                        'visible' => self::normalizeBoolean($props['subtitle_visible'] ?? '1', true),
                        'color'   => self::normalizeFlatString($props['subtitle_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['subtitle_line_height_percent'] ?? 160, 80, 240, 160),
                        'letterSpacing' => self::normalizeNumber($props['subtitle_letter_spacing'] ?? 0, -40, 80, 0),
                        'maxWidth' => self::normalizeNumber($props['subtitle_max_width'] ?? 680, 240, 1440, 680),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_desktop'] ?? 18, 10, 80, 18),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_desktop'] ?? 0, 0, 240, 0),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_mobile'] ?? 16, 10, 80, 16),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_mobile'] ?? 0, 0, 240, 0),
                        ],
                    ],
                    'meta' => [
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['meta_size_desktop'] ?? 14, 10, 120, 14),
                            'marginBottom' => 0,
                            'weight' => self::normalizeSelect((string) ($props['meta_weight_desktop'] ?? $props['meta_weight'] ?? '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                            'color' => self::normalizeFlatString($props['meta_color_desktop'] ?? $props['meta_color'] ?? ''),
                            'lineHeightPercent' => self::normalizeNumber($props['meta_line_height_percent_desktop'] ?? $props['meta_line_height_percent'] ?? 140, 80, 240, 140),
                            'letterSpacing' => self::normalizeNumber($props['meta_letter_spacing_desktop'] ?? $props['meta_letter_spacing'] ?? 0, -40, 80, 0),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['meta_size_mobile'] ?? 13, 10, 120, 13),
                            'marginBottom' => 0,
                            'weight' => self::normalizeSelect((string) ($props['meta_weight_mobile'] ?? $props['meta_weight'] ?? '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                            'color' => self::normalizeFlatString($props['meta_color_mobile'] ?? $props['meta_color'] ?? ''),
                            'lineHeightPercent' => self::normalizeNumber($props['meta_line_height_percent_mobile'] ?? $props['meta_line_height_percent'] ?? 140, 80, 240, 140),
                            'letterSpacing' => self::normalizeNumber($props['meta_letter_spacing_mobile'] ?? $props['meta_letter_spacing'] ?? 0, -40, 80, 0),
                        ],
                    ],
                    'media' => [
                        'aspectRatio' => self::normalizeSelect((string) ($props['media_aspect_ratio'] ?? '16:10'), ['auto', '16:10', '16:9', '4:3', '1:1', '3:4'], '16:10'),
                        'objectFit' => self::normalizeSelect((string) ($props['media_object_fit'] ?? 'cover'), ['cover', 'contain'], 'cover'),
                        'radius' => self::normalizeNumber($props['media_radius'] ?? 24, 0, 80, 24),
                    ],
                    'itemSurface' => [
                        'variant' => self::normalizeSelect($props['item_surface_variant'] ?? 'card', ['card', 'plain'], 'card'),
                        'radius' => self::normalizeNumber($props['item_surface_radius'] ?? 28, 0, 100, 28),
                        'borderWidth' => self::normalizeNumber($props['item_surface_border_width'] ?? 1, 0, 20, 1),
                        'borderColor' => self::normalizeFlatString($props['item_surface_border_color'] ?? '#e2e8f0'),
                        'shadow' => self::normalizeSelect((string) ($props['item_surface_shadow'] ?? 'md'), ['none', 'sm', 'md', 'lg'], 'md'),
                    ],
                    'itemTitle' => [
                        'color' => self::normalizeFlatString($props['item_title_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['item_title_line_height_percent'] ?? 130, 80, 220, 130),
                        'letterSpacing' => self::normalizeNumber($props['item_title_letter_spacing'] ?? 0, -40, 80, 0),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['item_title_size_desktop'] ?? 24, 10, 80, 24),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['item_title_size_mobile'] ?? 20, 10, 80, 20),
                        ],
                        'weight' => self::normalizeNumber($props['item_title_weight'] ?? 800, 100, 900, 800),
                    ],
                    'itemText' => [
                        'color' => self::normalizeFlatString($props['item_text_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['item_text_line_height_percent'] ?? 165, 80, 260, 165),
                        'letterSpacing' => self::normalizeNumber($props['item_text_letter_spacing'] ?? 0, -40, 80, 0),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['item_text_size_desktop'] ?? 16, 10, 80, 16),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['item_text_size_mobile'] ?? 15, 10, 80, 15),
                        ],
                    ],
                ],
            ],
            'layout' => [
                'desktop' => [
                    'align' => $align,
                    'contentWidth'=> self::normalizeNumber($props['content_width'] ?? 1160, 320, 1600, 1160),
                    'paddingTop'  => self::normalizeNumber($props['padding_top_desktop'] ?? 88, 0, 300, 88),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_desktop'] ?? 88, 0, 300, 88),
                    'columns' => self::normalizeNumber($props['columns_desktop'] ?? 3, 1, 4, 3),
                    'cardGap' => self::normalizeNumber($props['card_gap_desktop'] ?? 24, 0, 120, 24),
                    'headerGap' => self::normalizeNumber($props['header_gap_desktop'] ?? 28, 0, 160, 28),
                ],
                'mobile' => [
                    'paddingTop'  => self::normalizeNumber($props['padding_top_mobile'] ?? 56, 0, 300, 56),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_mobile'] ?? 56, 0, 300, 56),
                    'columns' => self::normalizeNumber($props['columns_mobile'] ?? 1, 1, 2, 1),
                    'cardGap' => self::normalizeNumber($props['card_gap_mobile'] ?? 16, 0, 120, 16),
                    'headerGap' => self::normalizeNumber($props['header_gap_mobile'] ?? 20, 0, 160, 20),
                ],
            ],
            'data' => $data,
            'entities' => [
                'title' => ['kind' => 'text', 'styleSlot' => 'title'],
                'subtitle' => ['kind' => 'text', 'styleSlot' => 'subtitle'],
                'primaryButton' => ['kind' => 'button', 'styleSlot' => 'primaryButton'],
                'items' => ['kind' => 'repeater', 'styleSlot' => 'items'],
                'itemSurface' => ['kind' => 'surface', 'styleSlot' => 'itemSurface'],
                'itemTitle' => ['kind' => 'text', 'styleSlot' => 'itemTitle'],
                'itemText' => ['kind' => 'text', 'styleSlot' => 'itemText'],
                'media' => ['kind' => 'media', 'styleSlot' => 'media'],
                'meta' => ['kind' => 'text', 'styleSlot' => 'meta'],
            ],
            'runtime' => [
                'renderMode' => 'ssr',
                'cacheScope' => 'page',
                'animation' => [
                    'name'  => self::normalizeSelect($props['block_animation'] ?? 'none', ['none', 'fade-up', 'fade-in', 'zoom-in'], 'none'),
                    'delay' => self::normalizeNumber($props['block_animation_delay'] ?? 0, 0, 1500, 0),
                ],
                'visibility' => [
                    'moreLink' => self::normalizeBoolean($props['show_more_link'] ?? '1', true),
                    'image' => self::normalizeBoolean($props['show_image'] ?? '1', true),
                    'category' => self::normalizeBoolean($props['show_category'] ?? '1', true),
                    'excerpt' => self::normalizeBoolean($props['show_excerpt'] ?? '1', true),
                    'date' => self::normalizeBoolean($props['show_date'] ?? '1', true),
                    'views' => self::normalizeBoolean($props['show_views'] ?? '1', true),
                    'comments' => self::normalizeBoolean($props['show_comments'] ?? '1', true),
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

    private static function normalizeCategoryCards(array $block, array $stored_contract = []) {
        $block['props'] = array_merge([
            'eyebrow' => 'Раздел',
            'heading' => 'Рубрика недели',
            'intro' => 'Компактная секция раздела для главной: четыре карточки на desktop, две на mobile и тот же manual/data режим без отдельного runtime.',
            'more_link_label' => 'Открыть раздел',
            'more_link_url' => '/news',
            'content_width' => 1280,
            'padding_top_desktop' => 72,
            'padding_bottom_desktop' => 72,
            'padding_top_mobile' => 48,
            'padding_bottom_mobile' => 48,
            'columns_desktop' => 4,
            'columns_mobile' => 2,
            'card_gap_desktop' => 20,
            'card_gap_mobile' => 14,
            'header_gap_desktop' => 18,
            'header_gap_mobile' => 14,
            'title_size_desktop' => 34,
            'title_size_mobile' => 26,
            'subtitle_size_desktop' => 16,
            'subtitle_size_mobile' => 15,
            'subtitle_max_width' => 860,
            'media_aspect_ratio' => '4:3',
            'media_radius' => 20,
            'item_surface_radius' => 20,
            'item_surface_border_width' => 1,
            'item_surface_border_color' => '#dbe4ef',
            'item_surface_shadow' => 'sm',
            'item_title_size_desktop' => 20,
            'item_title_size_mobile' => 18,
            'item_title_weight' => 800,
            'item_text_size_desktop' => 15,
            'item_text_size_mobile' => 14,
            'meta_size_desktop' => 13,
            'meta_size_mobile' => 12,
            'eyebrow_size_desktop' => 13,
            'eyebrow_size_mobile' => 12,
            'eyebrow_margin_bottom_desktop' => 10,
            'eyebrow_margin_bottom_mobile' => 8,
            'eyebrow_weight_desktop' => '700',
            'eyebrow_weight_mobile' => '700',
            'eyebrow_color_desktop' => '#0f766e',
            'eyebrow_color_mobile' => '#0f766e',
            'eyebrow_line_height_percent_desktop' => 140,
            'eyebrow_line_height_percent_mobile' => 140,
            'eyebrow_letter_spacing_desktop' => 1,
            'eyebrow_letter_spacing_mobile' => 1,
            'eyebrow_text_transform' => 'uppercase',
        ], (array) ($block['props'] ?? []));

        $props = (array) $block['props'];
        $contract = self::normalizeContentFeed([
            'type'   => 'content_feed',
            'title'  => (string) ($block['title'] ?? 'Рубрика с карточками'),
            'status' => (string) ($block['status'] ?? 'active'),
            'props'  => $props,
        ], $stored_contract);

        $contract['meta']['blockType'] = 'category_cards';
        $contract['meta']['label'] = (string) ($block['title'] ?? 'Рубрика с карточками');
        $contract['content']['eyebrow'] = (string) ($props['eyebrow'] ?? 'Раздел');
        $contract['design']['entities']['eyebrow'] = self::mergeContractArrays(
            [
                'desktop' => [
                    'fontSize' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_size_desktop'], 13), 10, 120, 13),
                    'marginBottom' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_margin_bottom_desktop'], 10), 0, 240, 10),
                    'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['eyebrow_weight_desktop', 'eyebrow_weight'], '700'), ['400', '500', '600', '700', '800', '900'], '700'),
                    'color' => self::normalizeFlatString(self::coalesceProp($props, ['eyebrow_color_desktop', 'eyebrow_color'], '')),
                    'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_line_height_percent_desktop', 'eyebrow_line_height_percent'], 140), 80, 240, 140),
                    'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_letter_spacing_desktop', 'eyebrow_letter_spacing'], 1), -40, 80, 1),
                ],
                'mobile' => [
                    'fontSize' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_size_mobile'], 12), 10, 120, 12),
                    'marginBottom' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_margin_bottom_mobile'], 8), 0, 240, 8),
                    'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['eyebrow_weight_mobile', 'eyebrow_weight'], '700'), ['400', '500', '600', '700', '800', '900'], '700'),
                    'color' => self::normalizeFlatString(self::coalesceProp($props, ['eyebrow_color_mobile', 'eyebrow_color'], '')),
                    'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_line_height_percent_mobile', 'eyebrow_line_height_percent'], 140), 80, 240, 140),
                    'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_letter_spacing_mobile', 'eyebrow_letter_spacing'], 1), -40, 80, 1),
                ],
                'textTransform' => self::normalizeSelect((string) self::coalesceProp($props, ['eyebrow_text_transform'], 'uppercase'), ['uppercase', 'none'], 'uppercase'),
            ],
            is_array($stored_contract['design']['entities']['eyebrow'] ?? null) ? $stored_contract['design']['entities']['eyebrow'] : []
        );
        $contract['entities'] = array_merge([
            'eyebrow' => ['kind' => 'text', 'styleSlot' => 'eyebrow'],
        ], (array) ($contract['entities'] ?? []));

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

        if ($type === 'content_feed') {
            return self::normalizeContentFeed([
                'type'   => 'content_feed',
                'title'  => (string) ($block['title'] ?? ($contract['meta']['label'] ?? 'Лента новостей')),
                'status' => (string) ($block['status'] ?? ($contract['meta']['status'] ?? 'active')),
                'props'  => self::denormalizeProps('content_feed', $contract),
            ], $contract);
        }

        if ($type === 'category_cards') {
            return self::normalizeCategoryCards([
                'type'   => 'category_cards',
                'title'  => (string) ($block['title'] ?? ($contract['meta']['label'] ?? 'Рубрика с карточками')),
                'status' => (string) ($block['status'] ?? ($contract['meta']['status'] ?? 'active')),
                'props'  => self::denormalizeProps('category_cards', $contract),
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
                'heading_weight'         => (string) ($contract['design']['entities']['title']['desktop']['weight'] ?? $contract['design']['entities']['title']['mobile']['weight'] ?? '900'),
                'eyebrow_size_desktop'   => (string) ($contract['design']['entities']['eyebrow']['desktop']['fontSize'] ?? 14),
                'eyebrow_size_mobile'    => (string) ($contract['design']['entities']['eyebrow']['mobile']['fontSize'] ?? 13),
                'eyebrow_margin_bottom_desktop' => (string) ($contract['design']['entities']['eyebrow']['desktop']['marginBottom'] ?? 16),
                'eyebrow_margin_bottom_mobile' => (string) ($contract['design']['entities']['eyebrow']['mobile']['marginBottom'] ?? 14),
                'eyebrow_weight_desktop' => (string) ($contract['design']['entities']['eyebrow']['desktop']['weight'] ?? '600'),
                'eyebrow_weight_mobile'  => (string) ($contract['design']['entities']['eyebrow']['mobile']['weight'] ?? '600'),
                'eyebrow_color_desktop'  => (string) ($contract['design']['entities']['eyebrow']['desktop']['color'] ?? ''),
                'eyebrow_color_mobile'   => (string) ($contract['design']['entities']['eyebrow']['mobile']['color'] ?? ''),
                'eyebrow_line_height_percent_desktop' => (string) ($contract['design']['entities']['eyebrow']['desktop']['lineHeightPercent'] ?? 140),
                'eyebrow_line_height_percent_mobile' => (string) ($contract['design']['entities']['eyebrow']['mobile']['lineHeightPercent'] ?? 140),
                'eyebrow_letter_spacing_desktop' => (string) ($contract['design']['entities']['eyebrow']['desktop']['letterSpacing'] ?? 1),
                'eyebrow_letter_spacing_mobile' => (string) ($contract['design']['entities']['eyebrow']['mobile']['letterSpacing'] ?? 1),
                'eyebrow_text_transform' => (string) ($contract['design']['entities']['eyebrow']['textTransform'] ?? 'uppercase'),
                'title_size_desktop'     => (string) ($contract['design']['entities']['title']['desktop']['fontSize'] ?? 64),
                'title_size_mobile'      => (string) ($contract['design']['entities']['title']['mobile']['fontSize'] ?? 40),
                'title_margin_bottom_desktop' => (string) ($contract['design']['entities']['title']['desktop']['marginBottom'] ?? 16),
                'title_margin_bottom_mobile' => (string) ($contract['design']['entities']['title']['mobile']['marginBottom'] ?? 14),
                'title_weight_desktop'   => (string) ($contract['design']['entities']['title']['desktop']['weight'] ?? $contract['design']['entities']['title']['weight'] ?? '900'),
                'title_weight_mobile'    => (string) ($contract['design']['entities']['title']['mobile']['weight'] ?? $contract['design']['entities']['title']['weight'] ?? '900'),
                'title_color_desktop'    => (string) ($contract['design']['entities']['title']['desktop']['color'] ?? $contract['design']['entities']['title']['color'] ?? ''),
                'title_color_mobile'     => (string) ($contract['design']['entities']['title']['mobile']['color'] ?? $contract['design']['entities']['title']['color'] ?? ''),
                'title_line_height_percent_desktop' => (string) ($contract['design']['entities']['title']['desktop']['lineHeightPercent'] ?? $contract['design']['entities']['title']['lineHeightPercent'] ?? 110),
                'title_line_height_percent_mobile' => (string) ($contract['design']['entities']['title']['mobile']['lineHeightPercent'] ?? $contract['design']['entities']['title']['lineHeightPercent'] ?? 110),
                'title_letter_spacing_desktop' => (string) ($contract['design']['entities']['title']['desktop']['letterSpacing'] ?? $contract['design']['entities']['title']['letterSpacing'] ?? 0),
                'title_letter_spacing_mobile' => (string) ($contract['design']['entities']['title']['mobile']['letterSpacing'] ?? $contract['design']['entities']['title']['letterSpacing'] ?? 0),
                'title_max_width_desktop' => (string) ($contract['design']['entities']['title']['desktop']['maxWidth'] ?? $contract['design']['entities']['title']['maxWidth'] ?? 600),
                'title_max_width_mobile' => (string) ($contract['design']['entities']['title']['mobile']['maxWidth'] ?? $contract['design']['entities']['title']['maxWidth'] ?? 600),
                'subtitle_size_desktop'  => (string) ($contract['design']['entities']['subtitle']['desktop']['fontSize'] ?? 20),
                'subtitle_size_mobile'   => (string) ($contract['design']['entities']['subtitle']['mobile']['fontSize'] ?? 18),
                'subtitle_margin_bottom_desktop' => (string) ($contract['design']['entities']['subtitle']['desktop']['marginBottom'] ?? 24),
                'subtitle_margin_bottom_mobile' => (string) ($contract['design']['entities']['subtitle']['mobile']['marginBottom'] ?? 20),
                'subtitle_weight_desktop' => (string) ($contract['design']['entities']['subtitle']['desktop']['weight'] ?? '400'),
                'subtitle_weight_mobile'  => (string) ($contract['design']['entities']['subtitle']['mobile']['weight'] ?? '400'),
                'subtitle_color_desktop'  => (string) ($contract['design']['entities']['subtitle']['desktop']['color'] ?? $contract['design']['entities']['subtitle']['color'] ?? ''),
                'subtitle_color_mobile'   => (string) ($contract['design']['entities']['subtitle']['mobile']['color'] ?? $contract['design']['entities']['subtitle']['color'] ?? ''),
                'subtitle_line_height_percent_desktop' => (string) ($contract['design']['entities']['subtitle']['desktop']['lineHeightPercent'] ?? $contract['design']['entities']['subtitle']['lineHeightPercent'] ?? 165),
                'subtitle_line_height_percent_mobile' => (string) ($contract['design']['entities']['subtitle']['mobile']['lineHeightPercent'] ?? $contract['design']['entities']['subtitle']['lineHeightPercent'] ?? 165),
                'subtitle_letter_spacing_desktop' => (string) ($contract['design']['entities']['subtitle']['desktop']['letterSpacing'] ?? $contract['design']['entities']['subtitle']['letterSpacing'] ?? 0),
                'subtitle_letter_spacing_mobile' => (string) ($contract['design']['entities']['subtitle']['mobile']['letterSpacing'] ?? $contract['design']['entities']['subtitle']['letterSpacing'] ?? 0),
                'subtitle_max_width_desktop' => (string) ($contract['design']['entities']['subtitle']['desktop']['maxWidth'] ?? $contract['design']['entities']['subtitle']['maxWidth'] ?? 720),
                'subtitle_max_width_mobile' => (string) ($contract['design']['entities']['subtitle']['mobile']['maxWidth'] ?? $contract['design']['entities']['subtitle']['maxWidth'] ?? 720),
                'meta_size_desktop'      => (string) ($contract['design']['entities']['meta']['desktop']['fontSize'] ?? 14),
                'meta_size_mobile'       => (string) ($contract['design']['entities']['meta']['mobile']['fontSize'] ?? 13),
                'meta_margin_bottom_desktop' => (string) ($contract['design']['entities']['meta']['desktop']['marginBottom'] ?? 24),
                'meta_margin_bottom_mobile' => (string) ($contract['design']['entities']['meta']['mobile']['marginBottom'] ?? 20),
                'meta_weight_desktop'    => (string) ($contract['design']['entities']['meta']['desktop']['weight'] ?? $contract['design']['entities']['meta']['weight'] ?? '600'),
                'meta_weight_mobile'     => (string) ($contract['design']['entities']['meta']['mobile']['weight'] ?? $contract['design']['entities']['meta']['weight'] ?? '600'),
                'meta_color_desktop'     => (string) ($contract['design']['entities']['meta']['desktop']['color'] ?? $contract['design']['entities']['meta']['color'] ?? ''),
                'meta_color_mobile'      => (string) ($contract['design']['entities']['meta']['mobile']['color'] ?? $contract['design']['entities']['meta']['color'] ?? ''),
                'meta_line_height_percent_desktop' => (string) ($contract['design']['entities']['meta']['desktop']['lineHeightPercent'] ?? $contract['design']['entities']['meta']['lineHeightPercent'] ?? 140),
                'meta_line_height_percent_mobile' => (string) ($contract['design']['entities']['meta']['mobile']['lineHeightPercent'] ?? $contract['design']['entities']['meta']['lineHeightPercent'] ?? 140),
                'meta_letter_spacing_desktop' => (string) ($contract['design']['entities']['meta']['desktop']['letterSpacing'] ?? $contract['design']['entities']['meta']['letterSpacing'] ?? 0),
                'meta_letter_spacing_mobile' => (string) ($contract['design']['entities']['meta']['mobile']['letterSpacing'] ?? $contract['design']['entities']['meta']['letterSpacing'] ?? 0),
                'button_text_size_desktop' => (string) ($contract['design']['entities']['buttonsText']['desktop']['fontSize'] ?? 16),
                'button_text_size_mobile' => (string) ($contract['design']['entities']['buttonsText']['mobile']['fontSize'] ?? 15),
                'button_text_weight_desktop' => (string) ($contract['design']['entities']['buttonsText']['desktop']['weight'] ?? '600'),
                'button_text_weight_mobile' => (string) ($contract['design']['entities']['buttonsText']['mobile']['weight'] ?? '600'),
                'button_text_color_desktop' => (string) ($contract['design']['entities']['buttonsText']['desktop']['color'] ?? $contract['design']['entities']['buttonsText']['color'] ?? ''),
                'button_text_color_mobile' => (string) ($contract['design']['entities']['buttonsText']['mobile']['color'] ?? $contract['design']['entities']['buttonsText']['color'] ?? ''),
                'button_text_line_height_percent_desktop' => (string) ($contract['design']['entities']['buttonsText']['desktop']['lineHeightPercent'] ?? $contract['design']['entities']['buttonsText']['lineHeightPercent'] ?? 120),
                'button_text_line_height_percent_mobile' => (string) ($contract['design']['entities']['buttonsText']['mobile']['lineHeightPercent'] ?? $contract['design']['entities']['buttonsText']['lineHeightPercent'] ?? 120),
                'button_text_letter_spacing_desktop' => (string) ($contract['design']['entities']['buttonsText']['desktop']['letterSpacing'] ?? $contract['design']['entities']['buttonsText']['letterSpacing'] ?? 0),
                'button_text_letter_spacing_mobile' => (string) ($contract['design']['entities']['buttonsText']['mobile']['letterSpacing'] ?? $contract['design']['entities']['buttonsText']['letterSpacing'] ?? 0),
                'media_aspect_ratio'      => (string) ($contract['design']['entities']['media']['aspectRatio'] ?? '16:10'),
                'media_object_fit'        => (string) ($contract['design']['entities']['media']['objectFit'] ?? 'cover'),
                'media_radius'            => (string) ($contract['design']['entities']['media']['radius'] ?? 28),
                'media_surface_background_mode' => (string) ($contract['design']['entities']['mediaSurface']['backgroundMode'] ?? ((string) ($contract['design']['entities']['mediaSurface']['backgroundColor'] ?? '') !== '' ? 'solid' : 'transparent')),
                'media_surface_background_color' => (string) ($contract['design']['entities']['mediaSurface']['backgroundColor'] ?? ''),
                'media_surface_padding'   => (string) ($contract['design']['entities']['mediaSurface']['padding'] ?? 0),
                'media_surface_radius'    => (string) ($contract['design']['entities']['mediaSurface']['radius'] ?? 28),
                'media_surface_border_width' => (string) ($contract['design']['entities']['mediaSurface']['borderWidth'] ?? 0),
                'media_surface_border_color' => (string) ($contract['design']['entities']['mediaSurface']['borderColor'] ?? ''),
                'media_surface_shadow'    => (string) ($contract['design']['entities']['mediaSurface']['shadow'] ?? 'lg'),
                'content_width'          => (string) ($contract['layout']['desktop']['contentWidth'] ?? 640),
                'padding_top_desktop'    => (string) ($contract['layout']['desktop']['paddingTop'] ?? 96),
                'padding_bottom_desktop' => (string) ($contract['layout']['desktop']['paddingBottom'] ?? 96),
                'padding_top_mobile'     => (string) ($contract['layout']['mobile']['paddingTop'] ?? 56),
                'padding_bottom_mobile'  => (string) ($contract['layout']['mobile']['paddingBottom'] ?? 56),
                'min_height_desktop'     => (string) ($contract['layout']['desktop']['minHeight'] ?? 0),
                'min_height_mobile'      => (string) ($contract['layout']['mobile']['minHeight'] ?? 0),
                'content_gap_desktop'    => (string) ($contract['layout']['desktop']['contentGap'] ?? 40),
                'content_gap_mobile'     => (string) ($contract['layout']['mobile']['contentGap'] ?? 24),
                'actions_gap_desktop'    => (string) ($contract['layout']['desktop']['actionsGap'] ?? 12),
                'actions_gap_mobile'     => (string) ($contract['layout']['mobile']['actionsGap'] ?? 10),
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

        if ($type === 'content_feed') {
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
                'heading'                 => (string) ($contract['content']['title'] ?? ''),
                'intro'                   => (string) ($contract['content']['subtitle'] ?? ''),
                'more_link_label'         => (string) ($contract['content']['primaryButton']['label'] ?? 'Все материалы'),
                'more_link_url'           => (string) ($contract['content']['primaryButton']['url'] ?? '/news'),
                'items'                   => is_array($contract['content']['items'] ?? null) ? $contract['content']['items'] : [],
                'title_visible'           => !empty($contract['design']['entities']['title']['visible']) ? '1' : '0',
                'subtitle_visible'        => !empty($contract['design']['entities']['subtitle']['visible']) ? '1' : '0',
                'heading_tag'             => (string) ($contract['design']['entities']['title']['tag'] ?? 'h2'),
                'heading_weight'          => (string) ($contract['design']['entities']['title']['weight'] ?? '800'),
                'title_size_desktop'      => (string) ($contract['design']['entities']['title']['desktop']['fontSize'] ?? 42),
                'title_size_mobile'       => (string) ($contract['design']['entities']['title']['mobile']['fontSize'] ?? 30),
                'title_margin_bottom_desktop' => (string) ($contract['design']['entities']['title']['desktop']['marginBottom'] ?? 0),
                'title_margin_bottom_mobile' => (string) ($contract['design']['entities']['title']['mobile']['marginBottom'] ?? 0),
                'title_color'             => (string) ($contract['design']['entities']['title']['color'] ?? ''),
                'title_line_height_percent' => (string) ($contract['design']['entities']['title']['lineHeightPercent'] ?? 110),
                'title_letter_spacing'    => (string) ($contract['design']['entities']['title']['letterSpacing'] ?? 0),
                'title_max_width'         => (string) ($contract['design']['entities']['title']['maxWidth'] ?? 760),
                'subtitle_size_desktop'   => (string) ($contract['design']['entities']['subtitle']['desktop']['fontSize'] ?? 18),
                'subtitle_size_mobile'    => (string) ($contract['design']['entities']['subtitle']['mobile']['fontSize'] ?? 16),
                'subtitle_margin_bottom_desktop' => (string) ($contract['design']['entities']['subtitle']['desktop']['marginBottom'] ?? 0),
                'subtitle_margin_bottom_mobile' => (string) ($contract['design']['entities']['subtitle']['mobile']['marginBottom'] ?? 0),
                'subtitle_color'          => (string) ($contract['design']['entities']['subtitle']['color'] ?? ''),
                'subtitle_line_height_percent' => (string) ($contract['design']['entities']['subtitle']['lineHeightPercent'] ?? 160),
                'subtitle_letter_spacing' => (string) ($contract['design']['entities']['subtitle']['letterSpacing'] ?? 0),
                'subtitle_max_width'      => (string) ($contract['design']['entities']['subtitle']['maxWidth'] ?? 680),
                'meta_size_desktop'       => (string) ($contract['design']['entities']['meta']['desktop']['fontSize'] ?? 14),
                'meta_size_mobile'        => (string) ($contract['design']['entities']['meta']['mobile']['fontSize'] ?? 13),
                'meta_weight_desktop'     => (string) ($contract['design']['entities']['meta']['desktop']['weight'] ?? $contract['design']['entities']['meta']['weight'] ?? '600'),
                'meta_weight_mobile'      => (string) ($contract['design']['entities']['meta']['mobile']['weight'] ?? $contract['design']['entities']['meta']['weight'] ?? '600'),
                'meta_color_desktop'      => (string) ($contract['design']['entities']['meta']['desktop']['color'] ?? $contract['design']['entities']['meta']['color'] ?? ''),
                'meta_color_mobile'       => (string) ($contract['design']['entities']['meta']['mobile']['color'] ?? $contract['design']['entities']['meta']['color'] ?? ''),
                'meta_line_height_percent' => (string) ($contract['design']['entities']['meta']['desktop']['lineHeightPercent'] ?? $contract['design']['entities']['meta']['lineHeightPercent'] ?? 140),
                'meta_letter_spacing'     => (string) ($contract['design']['entities']['meta']['desktop']['letterSpacing'] ?? $contract['design']['entities']['meta']['letterSpacing'] ?? 0),
                'media_aspect_ratio'      => (string) ($contract['design']['entities']['media']['aspectRatio'] ?? '16:10'),
                'media_object_fit'        => (string) ($contract['design']['entities']['media']['objectFit'] ?? 'cover'),
                'media_radius'            => (string) ($contract['design']['entities']['media']['radius'] ?? 24),
                'item_surface_variant'    => (string) ($contract['design']['entities']['itemSurface']['variant'] ?? 'card'),
                'item_surface_radius'     => (string) ($contract['design']['entities']['itemSurface']['radius'] ?? 28),
                'item_surface_border_width' => (string) ($contract['design']['entities']['itemSurface']['borderWidth'] ?? 1),
                'item_surface_border_color' => (string) ($contract['design']['entities']['itemSurface']['borderColor'] ?? '#e2e8f0'),
                'item_surface_shadow'     => (string) ($contract['design']['entities']['itemSurface']['shadow'] ?? 'md'),
                'item_title_size_desktop' => (string) ($contract['design']['entities']['itemTitle']['desktop']['fontSize'] ?? 24),
                'item_title_size_mobile'  => (string) ($contract['design']['entities']['itemTitle']['mobile']['fontSize'] ?? 20),
                'item_title_weight'       => (string) ($contract['design']['entities']['itemTitle']['weight'] ?? '800'),
                'item_title_color'        => (string) ($contract['design']['entities']['itemTitle']['color'] ?? ''),
                'item_title_line_height_percent' => (string) ($contract['design']['entities']['itemTitle']['lineHeightPercent'] ?? 130),
                'item_title_letter_spacing' => (string) ($contract['design']['entities']['itemTitle']['letterSpacing'] ?? 0),
                'item_text_size_desktop'  => (string) ($contract['design']['entities']['itemText']['desktop']['fontSize'] ?? 16),
                'item_text_size_mobile'   => (string) ($contract['design']['entities']['itemText']['mobile']['fontSize'] ?? 15),
                'item_text_color'         => (string) ($contract['design']['entities']['itemText']['color'] ?? ''),
                'item_text_line_height_percent' => (string) ($contract['design']['entities']['itemText']['lineHeightPercent'] ?? 165),
                'item_text_letter_spacing' => (string) ($contract['design']['entities']['itemText']['letterSpacing'] ?? 0),
                'content_width'           => (string) ($contract['layout']['desktop']['contentWidth'] ?? 1160),
                'padding_top_desktop'     => (string) ($contract['layout']['desktop']['paddingTop'] ?? 88),
                'padding_bottom_desktop'  => (string) ($contract['layout']['desktop']['paddingBottom'] ?? 88),
                'padding_top_mobile'      => (string) ($contract['layout']['mobile']['paddingTop'] ?? 56),
                'padding_bottom_mobile'   => (string) ($contract['layout']['mobile']['paddingBottom'] ?? 56),
                'align'                   => (string) ($contract['layout']['desktop']['align'] ?? 'left'),
                'columns_desktop'         => (string) ($contract['layout']['desktop']['columns'] ?? 3),
                'columns_mobile'          => (string) ($contract['layout']['mobile']['columns'] ?? 1),
                'card_gap_desktop'        => (string) ($contract['layout']['desktop']['cardGap'] ?? 24),
                'card_gap_mobile'         => (string) ($contract['layout']['mobile']['cardGap'] ?? 16),
                'header_gap_desktop'      => (string) ($contract['layout']['desktop']['headerGap'] ?? 28),
                'header_gap_mobile'       => (string) ($contract['layout']['mobile']['headerGap'] ?? 20),
                'show_more_link'          => !empty($contract['runtime']['visibility']['moreLink']) ? '1' : '0',
                'show_image'              => !empty($contract['runtime']['visibility']['image']) ? '1' : '0',
                'show_category'           => !empty($contract['runtime']['visibility']['category']) ? '1' : '0',
                'show_excerpt'            => !empty($contract['runtime']['visibility']['excerpt']) ? '1' : '0',
                'show_date'               => !empty($contract['runtime']['visibility']['date']) ? '1' : '0',
                'show_views'              => !empty($contract['runtime']['visibility']['views']) ? '1' : '0',
                'show_comments'           => !empty($contract['runtime']['visibility']['comments']) ? '1' : '0',
                'block_animation'         => (string) ($contract['runtime']['animation']['name'] ?? 'none'),
                'block_animation_delay'   => (string) ($contract['runtime']['animation']['delay'] ?? 0),
            ];
        }

        if ($type === 'category_cards') {
            return array_merge(self::denormalizeProps('content_feed', $contract), [
                'eyebrow'                 => (string) ($contract['content']['eyebrow'] ?? ''),
                'eyebrow_size_desktop'    => (string) ($contract['design']['entities']['eyebrow']['desktop']['fontSize'] ?? 13),
                'eyebrow_size_mobile'     => (string) ($contract['design']['entities']['eyebrow']['mobile']['fontSize'] ?? 12),
                'eyebrow_margin_bottom_desktop' => (string) ($contract['design']['entities']['eyebrow']['desktop']['marginBottom'] ?? 10),
                'eyebrow_margin_bottom_mobile' => (string) ($contract['design']['entities']['eyebrow']['mobile']['marginBottom'] ?? 8),
                'eyebrow_weight_desktop'  => (string) ($contract['design']['entities']['eyebrow']['desktop']['weight'] ?? '700'),
                'eyebrow_weight_mobile'   => (string) ($contract['design']['entities']['eyebrow']['mobile']['weight'] ?? '700'),
                'eyebrow_color_desktop'   => (string) ($contract['design']['entities']['eyebrow']['desktop']['color'] ?? ''),
                'eyebrow_color_mobile'    => (string) ($contract['design']['entities']['eyebrow']['mobile']['color'] ?? ''),
                'eyebrow_line_height_percent_desktop' => (string) ($contract['design']['entities']['eyebrow']['desktop']['lineHeightPercent'] ?? 140),
                'eyebrow_line_height_percent_mobile' => (string) ($contract['design']['entities']['eyebrow']['mobile']['lineHeightPercent'] ?? 140),
                'eyebrow_letter_spacing_desktop' => (string) ($contract['design']['entities']['eyebrow']['desktop']['letterSpacing'] ?? 1),
                'eyebrow_letter_spacing_mobile' => (string) ($contract['design']['entities']['eyebrow']['mobile']['letterSpacing'] ?? 1),
                'eyebrow_text_transform'  => (string) ($contract['design']['entities']['eyebrow']['textTransform'] ?? 'uppercase'),
            ]);
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

        if (!in_array($type, ['faq', 'content_feed', 'category_cards'], true)) {
            $normalized['listSource'] = self::normalizeListSource([]);
        } else {
            $normalized['listSource'] = self::normalizeListSource((array) ($data['listSource'] ?? []), self::isCardCollectionType($type) ? 'content_feed' : $type);
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

    private static function normalizeListSource(array $config, $type = 'faq') {
        $map = is_array($config['map'] ?? null) ? $config['map'] : [];

        if (self::isCardCollectionType($type)) {
            return [
                'type'          => self::normalizeSelect((string) ($config['type'] ?? 'manual'), ['manual', 'content_list'], 'manual'),
                'ctype'         => preg_replace('/[^a-z0-9_\-\{\}]/i', '', (string) ($config['ctype'] ?? '')),
                'limit'         => self::normalizeNumber($config['limit'] ?? 3, 1, 24, 3),
                'sort'          => self::normalizeSelect((string) ($config['sort'] ?? 'date_pub_desc'), self::$allowed_list_sorts, 'date_pub_desc'),
                'map'           => [
                    'title' => self::normalizeFieldReference($map['title'] ?? 'title'),
                    'excerpt' => self::normalizeFieldReference($map['excerpt'] ?? 'teaser'),
                    'image' => self::normalizeFieldReference($map['image'] ?? 'record_image_url'),
                    'imageAlt' => self::normalizeFieldReference($map['imageAlt'] ?? 'title'),
                    'category' => self::normalizeFieldReference($map['category'] ?? 'category.title'),
                    'date' => self::normalizeFieldReference($map['date'] ?? 'date_pub'),
                    'views' => self::normalizeFieldReference($map['views'] ?? 'hits_count'),
                    'comments' => self::normalizeFieldReference($map['comments'] ?? 'comments_count'),
                    'url' => self::normalizeFieldReference($map['url'] ?? 'record_url'),
                ],
                'emptyBehavior' => self::normalizeSelect((string) ($config['emptyBehavior'] ?? 'fallback'), ['fallback', 'empty'], 'fallback'),
            ];
        }

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

    private static function coalesceProp(array $props, array $keys, $fallback = null) {
        foreach ($keys as $key) {
            if ($key === '' || !array_key_exists($key, $props)) {
                continue;
            }

            return $props[$key];
        }

        return $fallback;
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

    private static function normalizeContentFeedItems($value) {
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

            $title = trim((string) ($item['title'] ?? ''));
            $excerpt = trim((string) ($item['excerpt'] ?? ($item['text'] ?? '')));
            $category = trim((string) ($item['category'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));
            $date = trim((string) ($item['date'] ?? ''));
            $views = trim((string) ($item['views'] ?? ''));
            $comments = trim((string) ($item['comments'] ?? ''));
            $image = self::normalizeImagePayload($item['image'] ?? '');
            $image_alt = trim((string) ($item['imageAlt'] ?? ($item['alt'] ?? ($image['alt'] ?? ''))));

            if ($title === '' && $excerpt === '' && $category === '' && $url === '' && empty($image['original']) && empty($image['display'])) {
                continue;
            }

            $items[] = self::buildContentFeedItemPayload([
                'category' => $category,
                'title' => $title,
                'excerpt' => $excerpt,
                'url' => $url,
                'image' => (string) ($image['original'] ?? $image['display'] ?? ''),
                'imageAlt' => $image_alt,
                'date' => $date,
                'views' => $views,
                'comments' => $comments,
            ]);
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

    private static function buildContentFeedItemPayload(array $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $excerpt = trim((string) ($item['excerpt'] ?? ($item['text'] ?? '')));

        return [
            'category' => trim((string) ($item['category'] ?? '')),
            'title' => $title,
            'excerpt' => $excerpt,
            'text' => $excerpt,
            'url' => trim((string) ($item['url'] ?? '')),
            'image' => trim((string) ($item['image'] ?? '')),
            'imageAlt' => trim((string) ($item['imageAlt'] ?? ($item['alt'] ?? ''))),
            'alt' => trim((string) ($item['imageAlt'] ?? ($item['alt'] ?? ''))),
            'date' => trim((string) ($item['date'] ?? '')),
            'views' => trim((string) ($item['views'] ?? '')),
            'comments' => trim((string) ($item['comments'] ?? '')),
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