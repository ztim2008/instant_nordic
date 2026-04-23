<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/ManagedScaffoldRegistry.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php';

class NordicblocksBlockContractNormalizer {

    private static $allowed_source_types = ['manual', 'content_item', 'content_list'];
    private static $allowed_list_sorts = ['date_pub_desc', 'date_pub_asc', 'title_asc', 'title_desc', 'hits_desc', 'hits_asc', 'comments_desc', 'comments_asc'];

    private static function isCardCollectionType($type) {
        return in_array($type, ['content_feed', 'category_cards', 'headline_feed', 'swiss_grid', 'catalog_browser'], true)
            || NordicblocksManagedScaffoldRegistry::usesCardCollectionMapping($type);
    }

    public static function supportsContractType($type) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $type));
        return in_array($type, ['hero', 'faq', 'content_feed', 'category_cards', 'headline_feed', 'swiss_grid', 'catalog_browser', 'design_block'], true)
            || NordicblocksManagedScaffoldRegistry::isManagedType($type);
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

        if (NordicblocksDesignBlockContractNormalizer::supportsType($type)) {
            return NordicblocksDesignBlockContractNormalizer::normalize($block);
        }

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

        if ($type === 'headline_feed') {
            return self::normalizeHeadlineFeed($block);
        }

        if ($type === 'swiss_grid') {
            return self::normalizeSwissGrid($block);
        }

        if ($type === 'catalog_browser') {
            return self::normalizeCatalogBrowser($block);
        }

        if (NordicblocksManagedScaffoldRegistry::isManagedType($type)) {
            return self::normalizeManagedScaffold($block);
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
                'tertiaryButton' => [
                    'label' => (string) ($props['btn_tertiary_label'] ?? ''),
                    'url'   => (string) ($props['btn_tertiary_url'] ?? '#'),
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
                    'tertiaryButton' => [
                        'style' => self::normalizeSelect($props['btn_tertiary_style'] ?? 'ghost', ['primary', 'outline', 'ghost'], 'ghost'),
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
                'preset' => self::normalizeSelect((string) ($props['layout_preset'] ?? 'classic'), ['classic', 'split-left', 'split-right', 'edge-left', 'edge-right', 'strip'], 'classic'),
                'desktop' => [
                    'mode'        => $layout,
                    'containerMode' => self::normalizeSelect((string) ($props['container_mode'] ?? 'contained'), ['contained', 'fluid'], 'contained'),
                    'mediaPosition' => self::normalizeSelect((string) ($props['media_position_desktop'] ?? 'start'), ['start', 'end'], 'start'),
                    'contentWidth'=> self::normalizeNumber($props['content_width'] ?? 640, 280, 1440, 640),
                    'paddingTop'  => self::normalizeNumber($props['padding_top_desktop'] ?? 96, 0, 300, 96),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_desktop'] ?? 96, 0, 300, 96),
                    'minHeight'   => self::normalizeNumber($props['min_height_desktop'] ?? 0, 0, 1200, 0),
                    'contentGap'  => self::normalizeNumber(self::coalesceProp($props, ['content_gap_desktop'], 40), 0, 240, 40),
                    'actionsGap'  => self::normalizeNumber(self::coalesceProp($props, ['actions_gap_desktop'], 12), 0, 120, 12),
                ],
                'mobile' => [
                    'mediaPosition' => self::normalizeSelect((string) ($props['media_position_mobile'] ?? 'top'), ['top', 'bottom'], 'top'),
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
                'tertiaryButton' => ['kind' => 'button', 'styleSlot' => 'tertiaryButton'],
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
        $layout_preset = self::normalizeSelect((string) self::coalesceProp($props, ['layout_preset'], 'default'), ['default', 'swiss'], 'default');
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
                        'maxWidth' => self::normalizeNumber($props['title_max_width'] ?? 700, 240, 1440, 700),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['title_size_desktop'] ?? 36, 12, 160, 36),
                            'marginBottom' => self::normalizeNumber($props['title_margin_bottom_desktop'] ?? 0, 0, 240, 0),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['title_size_mobile'] ?? 27, 12, 160, 27),
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
                        'maxWidth' => self::normalizeNumber($props['subtitle_max_width'] ?? 620, 240, 1440, 620),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_desktop'] ?? 16, 10, 80, 16),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_desktop'] ?? 0, 0, 240, 0),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['subtitle_size_mobile'] ?? 15, 10, 80, 15),
                            'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_mobile'] ?? 0, 0, 240, 0),
                        ],
                    ],
                    'meta' => [
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['meta_size_desktop'] ?? 13, 10, 120, 13),
                            'marginBottom' => 0,
                            'weight' => self::normalizeSelect((string) ($props['meta_weight_desktop'] ?? $props['meta_weight'] ?? '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                            'color' => self::normalizeFlatString($props['meta_color_desktop'] ?? $props['meta_color'] ?? ''),
                            'lineHeightPercent' => self::normalizeNumber($props['meta_line_height_percent_desktop'] ?? $props['meta_line_height_percent'] ?? 140, 80, 240, 140),
                            'letterSpacing' => self::normalizeNumber($props['meta_letter_spacing_desktop'] ?? $props['meta_letter_spacing'] ?? 0, -40, 80, 0),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['meta_size_mobile'] ?? 12, 10, 120, 12),
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
                        'radius' => self::normalizeNumber($props['media_radius'] ?? 20, 0, 80, 20),
                    ],
                    'itemSurface' => [
                        'variant' => self::normalizeSelect($props['item_surface_variant'] ?? 'card', ['card', 'plain'], 'card'),
                        'radius' => self::normalizeNumber($props['item_surface_radius'] ?? 22, 0, 100, 22),
                        'borderWidth' => self::normalizeNumber($props['item_surface_border_width'] ?? 1, 0, 20, 1),
                        'borderColor' => self::normalizeFlatString($props['item_surface_border_color'] ?? '#e2e8f0'),
                        'shadow' => self::normalizeSelect((string) ($props['item_surface_shadow'] ?? 'md'), ['none', 'sm', 'md', 'lg'], 'md'),
                    ],
                    'itemTitle' => [
                        'color' => self::normalizeFlatString($props['item_title_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['item_title_line_height_percent'] ?? 130, 80, 220, 130),
                        'letterSpacing' => self::normalizeNumber($props['item_title_letter_spacing'] ?? 0, -40, 80, 0),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['item_title_size_desktop'] ?? 20, 10, 80, 20),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['item_title_size_mobile'] ?? 18, 10, 80, 18),
                        ],
                        'weight' => self::normalizeNumber($props['item_title_weight'] ?? 800, 100, 900, 800),
                    ],
                    'itemText' => [
                        'color' => self::normalizeFlatString($props['item_text_color'] ?? ''),
                        'lineHeightPercent' => self::normalizeNumber($props['item_text_line_height_percent'] ?? 165, 80, 260, 165),
                        'letterSpacing' => self::normalizeNumber($props['item_text_letter_spacing'] ?? 0, -40, 80, 0),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['item_text_size_desktop'] ?? 15, 10, 80, 15),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['item_text_size_mobile'] ?? 14, 10, 80, 14),
                        ],
                    ],
                    'itemLink' => [
                        'color' => self::normalizeFlatString($props['item_link_color_desktop'] ?? $props['item_link_color'] ?? ''),
                        'weight' => self::normalizeSelect((string) ($props['item_link_weight_desktop'] ?? $props['item_link_weight'] ?? '700'), ['400', '500', '600', '700', '800', '900'], '700'),
                        'lineHeightPercent' => self::normalizeNumber($props['item_link_line_height_percent_desktop'] ?? $props['item_link_line_height_percent'] ?? 120, 80, 220, 120),
                        'letterSpacing' => self::normalizeNumber($props['item_link_letter_spacing_desktop'] ?? $props['item_link_letter_spacing'] ?? 1, -40, 80, 1),
                        'desktop' => [
                            'fontSize' => self::normalizeNumber($props['item_link_size_desktop'] ?? 13, 10, 80, 13),
                            'weight' => self::normalizeSelect((string) ($props['item_link_weight_desktop'] ?? $props['item_link_weight'] ?? '700'), ['400', '500', '600', '700', '800', '900'], '700'),
                            'color' => self::normalizeFlatString($props['item_link_color_desktop'] ?? $props['item_link_color'] ?? ''),
                            'lineHeightPercent' => self::normalizeNumber($props['item_link_line_height_percent_desktop'] ?? $props['item_link_line_height_percent'] ?? 120, 80, 220, 120),
                            'letterSpacing' => self::normalizeNumber($props['item_link_letter_spacing_desktop'] ?? $props['item_link_letter_spacing'] ?? 1, -40, 80, 1),
                        ],
                        'mobile' => [
                            'fontSize' => self::normalizeNumber($props['item_link_size_mobile'] ?? 12, 10, 80, 12),
                            'weight' => self::normalizeSelect((string) ($props['item_link_weight_mobile'] ?? $props['item_link_weight'] ?? ($props['item_link_weight_desktop'] ?? '700')), ['400', '500', '600', '700', '800', '900'], '700'),
                            'color' => self::normalizeFlatString($props['item_link_color_mobile'] ?? $props['item_link_color'] ?? ($props['item_link_color_desktop'] ?? '')),
                            'lineHeightPercent' => self::normalizeNumber($props['item_link_line_height_percent_mobile'] ?? $props['item_link_line_height_percent'] ?? ($props['item_link_line_height_percent_desktop'] ?? 120), 80, 220, 120),
                            'letterSpacing' => self::normalizeNumber($props['item_link_letter_spacing_mobile'] ?? $props['item_link_letter_spacing'] ?? ($props['item_link_letter_spacing_desktop'] ?? 1), -40, 80, 1),
                        ],
                    ],
                ],
            ],
            'layout' => [
                'preset' => $layout_preset,
                'desktop' => [
                    'align' => $align,
                    'contentWidth'=> self::normalizeNumber($props['content_width'] ?? 1080, 320, 1600, 1080),
                    'paddingTop'  => self::normalizeNumber($props['padding_top_desktop'] ?? 64, 0, 300, 64),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_desktop'] ?? 64, 0, 300, 64),
                    'columns' => self::normalizeNumber($props['columns_desktop'] ?? 3, 1, 4, 3),
                    'cardGap' => self::normalizeNumber($props['card_gap_desktop'] ?? 18, 0, 120, 18),
                    'headerGap' => self::normalizeNumber($props['header_gap_desktop'] ?? 18, 0, 160, 18),
                ],
                'mobile' => [
                    'paddingTop'  => self::normalizeNumber($props['padding_top_mobile'] ?? 44, 0, 300, 44),
                    'paddingBottom'=> self::normalizeNumber($props['padding_bottom_mobile'] ?? 44, 0, 300, 44),
                    'columns' => self::normalizeNumber($props['columns_mobile'] ?? 1, 1, 2, 1),
                    'cardGap' => self::normalizeNumber($props['card_gap_mobile'] ?? 14, 0, 120, 14),
                    'headerGap' => self::normalizeNumber($props['header_gap_mobile'] ?? 14, 0, 160, 14),
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
                'itemLink' => ['kind' => 'text', 'styleSlot' => 'itemLink'],
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
            'content_width' => 1180,
            'padding_top_desktop' => 56,
            'padding_bottom_desktop' => 56,
            'padding_top_mobile' => 40,
            'padding_bottom_mobile' => 40,
            'columns_desktop' => 4,
            'columns_mobile' => 2,
            'card_gap_desktop' => 16,
            'card_gap_mobile' => 12,
            'header_gap_desktop' => 14,
            'header_gap_mobile' => 12,
            'title_size_desktop' => 30,
            'title_size_mobile' => 24,
            'subtitle_size_desktop' => 15,
            'subtitle_size_mobile' => 14,
            'subtitle_max_width' => 720,
            'media_aspect_ratio' => '4:3',
            'media_radius' => 18,
            'item_surface_radius' => 18,
            'item_surface_border_width' => 1,
            'item_surface_border_color' => '#dbe4ef',
            'item_surface_shadow' => 'sm',
            'item_title_size_desktop' => 18,
            'item_title_size_mobile' => 16,
            'item_title_weight' => 800,
            'item_text_size_desktop' => 14,
            'item_text_size_mobile' => 13,
            'meta_size_desktop' => 12,
            'meta_size_mobile' => 11,
            'eyebrow_size_desktop' => 13,
            'eyebrow_size_mobile' => 12,
            'eyebrow_margin_bottom_desktop' => 8,
            'eyebrow_margin_bottom_mobile' => 6,
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
                    'marginBottom' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_margin_bottom_desktop'], 8), 0, 240, 8),
                    'weight' => self::normalizeSelect((string) self::coalesceProp($props, ['eyebrow_weight_desktop', 'eyebrow_weight'], '700'), ['400', '500', '600', '700', '800', '900'], '700'),
                    'color' => self::normalizeFlatString(self::coalesceProp($props, ['eyebrow_color_desktop', 'eyebrow_color'], '')),
                    'lineHeightPercent' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_line_height_percent_desktop', 'eyebrow_line_height_percent'], 140), 80, 240, 140),
                    'letterSpacing' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_letter_spacing_desktop', 'eyebrow_letter_spacing'], 1), -40, 80, 1),
                ],
                'mobile' => [
                    'fontSize' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_size_mobile'], 12), 10, 120, 12),
                    'marginBottom' => self::normalizeNumber(self::coalesceProp($props, ['eyebrow_margin_bottom_mobile'], 6), 0, 240, 6),
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

    private static function normalizeHeadlineFeed(array $block, array $stored_contract = []) {
        $block['props'] = array_merge([
            'heading' => 'Главная статья и лента',
            'intro' => 'Один акцентный материал и продолжение ленты в том же блоке: удобно для главной, спецтемы и редакционных подборок.',
            'more_link_label' => 'Смотреть все материалы',
            'more_link_url' => '/news',
            'layout_preset' => 'split',
            'content_width' => 1140,
            'padding_top_desktop' => 64,
            'padding_bottom_desktop' => 64,
            'padding_top_mobile' => 44,
            'padding_bottom_mobile' => 44,
            'columns_desktop' => 3,
            'columns_mobile' => 1,
            'card_gap_desktop' => 18,
            'card_gap_mobile' => 14,
            'header_gap_desktop' => 18,
            'header_gap_mobile' => 14,
            'title_size_desktop' => 32,
            'title_size_mobile' => 24,
            'subtitle_size_desktop' => 16,
            'subtitle_size_mobile' => 14,
            'subtitle_max_width' => 640,
            'media_aspect_ratio' => '4:3',
            'media_radius' => 22,
            'item_surface_radius' => 22,
            'item_surface_border_width' => 1,
            'item_surface_border_color' => '#dbe4ef',
            'item_surface_shadow' => 'md',
            'item_title_size_desktop' => 17,
            'item_title_size_mobile' => 16,
            'item_title_weight' => 800,
            'item_text_size_desktop' => 14,
            'item_text_size_mobile' => 13,
            'meta_size_desktop' => 12,
            'meta_size_mobile' => 11,
        ], (array) ($block['props'] ?? []));

        $props = (array) $block['props'];
        $contract = self::normalizeContentFeed([
            'type'   => 'content_feed',
            'title'  => (string) ($block['title'] ?? 'Главная статья и лента'),
            'status' => (string) ($block['status'] ?? 'active'),
            'props'  => $props,
        ], $stored_contract);

        $contract['meta']['blockType'] = 'headline_feed';
        $contract['meta']['label'] = (string) ($block['title'] ?? 'Главная статья и лента');
        $contract['layout']['preset'] = self::normalizeSelect((string) self::coalesceProp($props, ['layout_preset'], 'split'), ['split', 'stack', 'cover'], 'split');

        return self::mergeStoredContract($contract, $stored_contract);
    }

    private static function normalizeSwissGrid(array $block, array $stored_contract = []) {
        $block['props'] = array_merge([
            'heading' => 'Swiss Style Grid',
            'intro' => 'Минимализм. Порядок. Типографика.',
            'theme' => 'light',
            'align' => 'left',
            'content_width' => 1400,
            'padding_top_desktop' => 0,
            'padding_bottom_desktop' => 0,
            'padding_top_mobile' => 0,
            'padding_bottom_mobile' => 0,
            'columns_desktop' => 3,
            'columns_mobile' => 1,
            'card_gap_desktop' => 0,
            'card_gap_mobile' => 0,
            'header_gap_desktop' => 0,
            'header_gap_mobile' => 0,
            'title_size_desktop' => 48,
            'title_size_mobile' => 32,
            'heading_weight' => 700,
            'title_max_width' => 1400,
            'subtitle_size_desktop' => 16,
            'subtitle_size_mobile' => 12,
            'subtitle_weight_desktop' => '500',
            'subtitle_weight_mobile' => '500',
            'subtitle_max_width' => 1400,
            'meta_size_desktop' => 11,
            'meta_size_mobile' => 11,
            'meta_weight_desktop' => '700',
            'meta_weight_mobile' => '700',
            'item_title_size_desktop' => 20,
            'item_title_size_mobile' => 17,
            'item_title_weight' => 700,
            'item_text_size_desktop' => 14,
            'item_text_size_mobile' => 13,
            'media_aspect_ratio' => '4:3',
            'media_object_fit' => 'cover',
            'media_radius' => 0,
            'item_surface_variant' => 'card',
            'item_surface_radius' => 0,
            'item_surface_border_width' => 1,
            'item_surface_border_color' => '#eaeaea',
            'item_surface_shadow' => 'none',
            'show_more_link' => '0',
            'show_image' => '1',
            'show_category' => '1',
            'show_excerpt' => '1',
            'show_date' => '0',
            'show_views' => '0',
            'show_comments' => '0',
        ], (array) ($block['props'] ?? []));

        $props = (array) $block['props'];
        $contract = self::normalizeContentFeed([
            'type'   => 'content_feed',
            'title'  => (string) ($block['title'] ?? 'Swiss Grid'),
            'status' => (string) ($block['status'] ?? 'active'),
            'props'  => $props,
        ], $stored_contract);

        $contract['meta']['blockType'] = 'swiss_grid';
        $contract['meta']['label'] = (string) ($block['title'] ?? 'Swiss Grid');
        $contract['runtime']['visibility']['moreLink'] = false;
        $contract['design']['entities']['itemLink'] = [
            'color' => '',
            'lineHeightPercent' => 120,
            'letterSpacing' => 1,
            'desktop' => [
                'fontSize' => 12,
                'weight' => '700',
            ],
            'mobile' => [
                'fontSize' => 12,
                'weight' => '700',
            ],
        ];

        return self::mergeStoredContract($contract, $stored_contract);
    }

    private static function normalizeCatalogBrowser(array $block, array $stored_contract = []) {
        $block['props'] = array_merge([
            'heading' => 'Каталог',
            'intro' => 'Управляемый каталог услуг, работ или товаров без checkout, но с сильной карточечной подачей.',
            'section_link_label' => 'Открыть все',
            'section_link_url' => '/catalog',
            'theme' => 'light',
            'align' => 'left',
            'content_width' => 1180,
            'padding_top_desktop' => 64,
            'padding_bottom_desktop' => 64,
            'padding_top_mobile' => 44,
            'padding_bottom_mobile' => 44,
            'columns_desktop' => 3,
            'columns_mobile' => 1,
            'card_gap_desktop' => 18,
            'card_gap_mobile' => 14,
            'header_gap_desktop' => 18,
            'header_gap_mobile' => 14,
            'collection_mode' => 'all',
            'items_per_page' => 6,
            'show_results_count' => '1',
            'search_in_title' => '1',
            'search_in_excerpt' => '1',
            'search_in_category' => '1',
            'search_in_badge' => '1',
            'search_in_tags' => '1',
            'search_in_price' => '0',
            'search_in_availability' => '1',
            'title_size_desktop' => 34,
            'title_size_mobile' => 26,
            'subtitle_size_desktop' => 16,
            'subtitle_size_mobile' => 14,
            'subtitle_max_width' => 720,
            'media_aspect_ratio' => '4:3',
            'media_radius' => 20,
            'item_surface_radius' => 22,
            'item_surface_border_width' => 1,
            'item_surface_border_color' => '#dbe4ef',
            'item_surface_shadow' => 'md',
            'item_title_size_desktop' => 20,
            'item_title_size_mobile' => 18,
            'item_title_weight' => 800,
            'item_text_size_desktop' => 14,
            'item_text_size_mobile' => 13,
            'meta_size_desktop' => 12,
            'meta_size_mobile' => 11,
            'show_more_link' => '1',
            'show_search' => '1',
            'show_category_filter' => '1',
            'show_price_filter' => '1',
            'show_sort' => '1',
            'show_active_filters' => '1',
            'show_image' => '1',
            'show_category' => '1',
            'show_badge' => '1',
            'show_price' => '1',
            'show_old_price' => '1',
            'show_excerpt' => '1',
            'show_cta' => '1',
        ], (array) ($block['props'] ?? []));

        $props = (array) $block['props'];
        $props['more_link_label'] = (string) ($props['section_link_label'] ?? ($props['more_link_label'] ?? 'Открыть все'));
        $props['more_link_url'] = (string) ($props['section_link_url'] ?? ($props['more_link_url'] ?? '/catalog'));
        $catalog_media_radius = self::normalizeNumber($props['media_radius'] ?? 20, 0, 80, 20);
        $catalog_item_surface_radius = self::normalizeNumber($props['item_surface_radius'] ?? 22, 0, 100, 22);
        $catalog_item_surface_border_width = self::normalizeNumber($props['item_surface_border_width'] ?? 1, 0, 20, 1);
        $catalog_item_surface_border_color = strtolower(self::normalizeFlatString($props['item_surface_border_color'] ?? '#dbe4ef'));
        $catalog_item_surface_shadow = self::normalizeSelect((string) ($props['item_surface_shadow'] ?? 'md'), ['none', 'sm', 'md', 'lg'], 'md');
        $catalog_media_inherit_global = array_key_exists('media_inherit_global', $props)
            ? self::normalizeBoolean($props['media_inherit_global'], true)
            : ($catalog_media_radius === 20);
        $catalog_item_surface_inherit_global = array_key_exists('item_surface_inherit_global', $props)
            ? self::normalizeBoolean($props['item_surface_inherit_global'], true)
            : (($catalog_item_surface_radius === 22 || $catalog_item_surface_radius === 1)
                && $catalog_item_surface_border_width === 1
                && $catalog_item_surface_border_color === '#dbe4ef'
                && $catalog_item_surface_shadow === 'md');
        $catalog_toolbar_background_mode = self::normalizeSelect((string) ($props['toolbar_background_mode'] ?? 'solid'), ['transparent', 'solid'], 'solid');
        $catalog_toolbar_background_color = self::normalizeFlatString($props['toolbar_background_color'] ?? '');
        $catalog_toolbar_padding = self::normalizeNumber($props['toolbar_padding'] ?? 16, 0, 120, 16);
        $catalog_toolbar_radius = self::normalizeNumber($props['toolbar_radius'] ?? 22, 0, 120, 22);
        $catalog_toolbar_border_width = self::normalizeNumber($props['toolbar_border_width'] ?? 1, 0, 20, 1);
        $catalog_toolbar_border_color = self::normalizeFlatString($props['toolbar_border_color'] ?? '');
        $catalog_toolbar_shadow = self::normalizeSelect((string) ($props['toolbar_shadow'] ?? 'sm'), ['none', 'sm', 'md', 'lg'], 'sm');
        $catalog_toolbar_controls_background_mode = self::normalizeSelect((string) ($props['toolbar_controls_background_mode'] ?? 'solid'), ['transparent', 'solid'], 'solid');
        $catalog_toolbar_controls_background_color = self::normalizeFlatString($props['toolbar_controls_background_color'] ?? '');
        $catalog_toolbar_controls_radius = self::normalizeNumber($props['toolbar_controls_radius'] ?? 16, 0, 80, 16);
        $catalog_toolbar_controls_border_width = self::normalizeNumber($props['toolbar_controls_border_width'] ?? 1, 0, 20, 1);
        $catalog_toolbar_controls_border_color = self::normalizeFlatString($props['toolbar_controls_border_color'] ?? '');
        $catalog_toolbar_controls_shadow = self::normalizeSelect((string) ($props['toolbar_controls_shadow'] ?? 'none'), ['none', 'sm', 'md', 'lg'], 'none');

        $contract = self::normalizeContentFeed([
            'type'   => 'content_feed',
            'title'  => (string) ($block['title'] ?? 'Каталог'),
            'status' => (string) ($block['status'] ?? 'active'),
            'props'  => $props,
        ], $stored_contract);

        $contract['meta']['blockType'] = 'catalog_browser';
        $contract['meta']['label'] = (string) ($block['title'] ?? 'Каталог');
        $contract['layout']['desktop']['columns'] = self::normalizeNumber($props['columns_desktop'] ?? 3, 1, 6, 3);
        $contract['layout']['mobile']['columns'] = self::normalizeNumber($props['columns_mobile'] ?? 1, 1, 2, 1);
        $contract['entities'] = array_merge((array) ($contract['entities'] ?? []), [
            'toolbar' => ['kind' => 'group', 'styleSlot' => 'toolbar'],
            'toolbarControls' => ['kind' => 'surface', 'styleSlot' => 'toolbarControls'],
            'searchField' => ['kind' => 'text', 'styleSlot' => 'searchField'],
            'categoryFilter' => ['kind' => 'text', 'styleSlot' => 'categoryFilter'],
            'priceFilter' => ['kind' => 'text', 'styleSlot' => 'priceFilter'],
            'sortControl' => ['kind' => 'text', 'styleSlot' => 'sortControl'],
            'activeFilters' => ['kind' => 'text', 'styleSlot' => 'activeFilters'],
            'cardBadge' => ['kind' => 'text', 'styleSlot' => 'cardBadge'],
            'cardPrice' => ['kind' => 'text', 'styleSlot' => 'cardPrice'],
            'cardPrimaryAction' => ['kind' => 'text', 'styleSlot' => 'cardPrimaryAction'],
            'mediaModal' => ['kind' => 'surface', 'styleSlot' => 'mediaModal'],
            'emptyState' => ['kind' => 'text', 'styleSlot' => 'emptyState'],
        ]);
        $contract['runtime']['visibility'] = array_merge((array) ($contract['runtime']['visibility'] ?? []), [
            'search' => self::normalizeBoolean($props['show_search'] ?? '1', true),
            'categoryFilter' => self::normalizeBoolean($props['show_category_filter'] ?? '1', true),
            'priceFilter' => self::normalizeBoolean($props['show_price_filter'] ?? '1', true),
            'sort' => self::normalizeBoolean($props['show_sort'] ?? '1', true),
            'activeFilters' => self::normalizeBoolean($props['show_active_filters'] ?? '1', true),
            'badge' => self::normalizeBoolean($props['show_badge'] ?? '1', true),
            'price' => self::normalizeBoolean($props['show_price'] ?? '1', true),
            'oldPrice' => self::normalizeBoolean($props['show_old_price'] ?? '1', true),
            'cta' => self::normalizeBoolean($props['show_cta'] ?? '1', true),
        ]);
        $contract['runtime']['catalog'] = array_merge((array) ($contract['runtime']['catalog'] ?? []), [
            'collectionMode' => self::normalizeSelect((string) ($props['collection_mode'] ?? 'all'), ['all', 'load_more', 'pagination'], 'all'),
            'itemsPerPage' => self::normalizeNumber($props['items_per_page'] ?? 6, 1, 48, 6),
            'showResultsCount' => self::normalizeBoolean($props['show_results_count'] ?? '1', true),
            'searchFields' => [
                'title' => self::normalizeBoolean($props['search_in_title'] ?? '1', true),
                'excerpt' => self::normalizeBoolean($props['search_in_excerpt'] ?? '1', true),
                'category' => self::normalizeBoolean($props['search_in_category'] ?? '1', true),
                'badge' => self::normalizeBoolean($props['search_in_badge'] ?? '1', true),
                'tags' => self::normalizeBoolean($props['search_in_tags'] ?? '1', true),
                'price' => self::normalizeBoolean($props['search_in_price'] ?? '0', false),
                'availability' => self::normalizeBoolean($props['search_in_availability'] ?? '1', true),
            ],
        ]);
        $contract['design']['entities']['toolbar'] = self::mergeContractArrays([
            'backgroundMode' => $catalog_toolbar_background_mode,
            'backgroundColor' => $catalog_toolbar_background_color,
            'padding' => $catalog_toolbar_padding,
            'radius' => $catalog_toolbar_radius,
            'borderWidth' => $catalog_toolbar_border_width,
            'borderColor' => $catalog_toolbar_border_color,
            'shadow' => $catalog_toolbar_shadow,
        ], is_array($stored_contract['design']['entities']['toolbar'] ?? null) ? $stored_contract['design']['entities']['toolbar'] : []);
        $contract['design']['entities']['toolbarControls'] = self::mergeContractArrays([
            'backgroundMode' => $catalog_toolbar_controls_background_mode,
            'backgroundColor' => $catalog_toolbar_controls_background_color,
            'radius' => $catalog_toolbar_controls_radius,
            'borderWidth' => $catalog_toolbar_controls_border_width,
            'borderColor' => $catalog_toolbar_controls_border_color,
            'shadow' => $catalog_toolbar_controls_shadow,
        ], is_array($stored_contract['design']['entities']['toolbarControls'] ?? null) ? $stored_contract['design']['entities']['toolbarControls'] : []);
        $contract['design']['entities']['cardPrice'] = self::mergeContractArrays([
            'desktop' => [
                'fontSize' => self::normalizeNumber($props['card_price_size_desktop'] ?? 19, 10, 120, 19),
                'weight' => self::normalizeSelect((string) ($props['card_price_weight_desktop'] ?? $props['card_price_weight'] ?? '800'), ['400', '500', '600', '700', '800', '900'], '800'),
                'color' => self::normalizeFlatString($props['card_price_color_desktop'] ?? $props['card_price_color'] ?? ''),
                'lineHeightPercent' => self::normalizeNumber($props['card_price_line_height_percent_desktop'] ?? $props['card_price_line_height_percent'] ?? 120, 80, 220, 120),
                'letterSpacing' => self::normalizeNumber($props['card_price_letter_spacing_desktop'] ?? $props['card_price_letter_spacing'] ?? 0, -40, 80, 0),
            ],
            'mobile' => [
                'fontSize' => self::normalizeNumber($props['card_price_size_mobile'] ?? 17, 10, 120, 17),
                'weight' => self::normalizeSelect((string) ($props['card_price_weight_mobile'] ?? $props['card_price_weight'] ?? '800'), ['400', '500', '600', '700', '800', '900'], '800'),
                'color' => self::normalizeFlatString($props['card_price_color_mobile'] ?? $props['card_price_color'] ?? ''),
                'lineHeightPercent' => self::normalizeNumber($props['card_price_line_height_percent_mobile'] ?? $props['card_price_line_height_percent'] ?? 120, 80, 220, 120),
                'letterSpacing' => self::normalizeNumber($props['card_price_letter_spacing_mobile'] ?? $props['card_price_letter_spacing'] ?? 0, -40, 80, 0),
            ],
        ], is_array($stored_contract['design']['entities']['cardPrice'] ?? null) ? $stored_contract['design']['entities']['cardPrice'] : []);
        $contract['design']['entities']['media']['inheritGlobalStyle'] = $catalog_media_inherit_global;
        $contract['design']['entities']['itemSurface']['inheritGlobalStyle'] = $catalog_item_surface_inherit_global;

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

        if ($type === 'headline_feed') {
            return self::normalizeHeadlineFeed([
                'type'   => 'headline_feed',
                'title'  => (string) ($block['title'] ?? ($contract['meta']['label'] ?? 'Главная статья и лента')),
                'status' => (string) ($block['status'] ?? ($contract['meta']['status'] ?? 'active')),
                'props'  => self::denormalizeProps('headline_feed', $contract),
            ], $contract);
        }

        if ($type === 'swiss_grid') {
            return self::normalizeSwissGrid([
                'type'   => 'swiss_grid',
                'title'  => (string) ($block['title'] ?? ($contract['meta']['label'] ?? 'Swiss Grid')),
                'status' => (string) ($block['status'] ?? ($contract['meta']['status'] ?? 'active')),
                'props'  => self::denormalizeProps('swiss_grid', $contract),
            ], $contract);
        }

        if ($type === 'catalog_browser') {
            return self::normalizeCatalogBrowser([
                'type'   => 'catalog_browser',
                'title'  => (string) ($block['title'] ?? ($contract['meta']['label'] ?? 'Каталог')),
                'status' => (string) ($block['status'] ?? ($contract['meta']['status'] ?? 'active')),
                'props'  => self::denormalizeProps('catalog_browser', $contract),
            ], $contract);
        }

        if (NordicblocksManagedScaffoldRegistry::isManagedType($type)) {
            return self::normalizeManagedScaffold([
                'type'   => $type,
                'title'  => (string) ($block['title'] ?? ($contract['meta']['label'] ?? $type)),
                'status' => (string) ($block['status'] ?? ($contract['meta']['status'] ?? 'active')),
                'props'  => self::denormalizeManagedScaffold($type, $contract),
            ], $contract);
        }

        return self::mergeStoredContract(self::normalizeFallback($block, $type), $contract);
    }

    public static function denormalizeProps($type, array $contract) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $type));

        if (NordicblocksDesignBlockContractNormalizer::supportsType($type)) {
            return NordicblocksDesignBlockContractNormalizer::denormalizeProps($contract);
        }

        if ($type === 'hero') {
            return [
                'layout'                 => (string) ($contract['layout']['desktop']['mode'] ?? 'centered'),
                'layout_preset'          => (string) ($contract['layout']['preset'] ?? 'classic'),
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
                'btn_tertiary_label'     => (string) ($contract['content']['tertiaryButton']['label'] ?? ''),
                'btn_tertiary_url'       => (string) ($contract['content']['tertiaryButton']['url'] ?? '#'),
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
                'container_mode'        => (string) ($contract['layout']['desktop']['containerMode'] ?? 'contained'),
                'media_position_desktop' => (string) ($contract['layout']['desktop']['mediaPosition'] ?? 'start'),
                'media_position_mobile' => (string) ($contract['layout']['mobile']['mediaPosition'] ?? 'top'),
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
                'btn_tertiary_style'     => (string) ($contract['design']['entities']['tertiaryButton']['style'] ?? 'ghost'),
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
                'layout_preset'           => (string) ($contract['layout']['preset'] ?? 'default'),
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
                'item_link_size_desktop' => (string) ($contract['design']['entities']['itemLink']['desktop']['fontSize'] ?? 13),
                'item_link_size_mobile'  => (string) ($contract['design']['entities']['itemLink']['mobile']['fontSize'] ?? 12),
                'item_link_weight_desktop' => (string) ($contract['design']['entities']['itemLink']['desktop']['weight'] ?? $contract['design']['entities']['itemLink']['weight'] ?? '700'),
                'item_link_weight_mobile' => (string) ($contract['design']['entities']['itemLink']['mobile']['weight'] ?? $contract['design']['entities']['itemLink']['weight'] ?? $contract['design']['entities']['itemLink']['desktop']['weight'] ?? '700'),
                'item_link_color_desktop' => (string) ($contract['design']['entities']['itemLink']['desktop']['color'] ?? $contract['design']['entities']['itemLink']['color'] ?? ''),
                'item_link_color_mobile' => (string) ($contract['design']['entities']['itemLink']['mobile']['color'] ?? $contract['design']['entities']['itemLink']['color'] ?? $contract['design']['entities']['itemLink']['desktop']['color'] ?? ''),
                'item_link_line_height_percent_desktop' => (string) ($contract['design']['entities']['itemLink']['desktop']['lineHeightPercent'] ?? $contract['design']['entities']['itemLink']['lineHeightPercent'] ?? 120),
                'item_link_line_height_percent_mobile' => (string) ($contract['design']['entities']['itemLink']['mobile']['lineHeightPercent'] ?? $contract['design']['entities']['itemLink']['lineHeightPercent'] ?? $contract['design']['entities']['itemLink']['desktop']['lineHeightPercent'] ?? 120),
                'item_link_letter_spacing_desktop' => (string) ($contract['design']['entities']['itemLink']['desktop']['letterSpacing'] ?? $contract['design']['entities']['itemLink']['letterSpacing'] ?? 1),
                'item_link_letter_spacing_mobile' => (string) ($contract['design']['entities']['itemLink']['mobile']['letterSpacing'] ?? $contract['design']['entities']['itemLink']['letterSpacing'] ?? $contract['design']['entities']['itemLink']['desktop']['letterSpacing'] ?? 1),
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

        if ($type === 'headline_feed') {
            return array_merge(self::denormalizeProps('content_feed', $contract), [
                'layout_preset' => (string) ($contract['layout']['preset'] ?? 'split'),
            ]);
        }

        if ($type === 'swiss_grid') {
            return array_merge(self::denormalizeProps('content_feed', $contract), []);
        }

        if ($type === 'catalog_browser') {
            $catalog_runtime = isset($contract['runtime']['catalog']) && is_array($contract['runtime']['catalog'])
                ? $contract['runtime']['catalog']
                : [];
            $catalog_search_fields = isset($catalog_runtime['searchFields']) && is_array($catalog_runtime['searchFields'])
                ? $catalog_runtime['searchFields']
                : [];
            $catalog_media_entity = isset($contract['design']['entities']['media']) && is_array($contract['design']['entities']['media'])
                ? $contract['design']['entities']['media']
                : [];
            $catalog_toolbar_entity = isset($contract['design']['entities']['toolbar']) && is_array($contract['design']['entities']['toolbar'])
                ? $contract['design']['entities']['toolbar']
                : [];
            $catalog_toolbar_controls_entity = isset($contract['design']['entities']['toolbarControls']) && is_array($contract['design']['entities']['toolbarControls'])
                ? $contract['design']['entities']['toolbarControls']
                : [];
            $catalog_item_surface_entity = isset($contract['design']['entities']['itemSurface']) && is_array($contract['design']['entities']['itemSurface'])
                ? $contract['design']['entities']['itemSurface']
                : [];
            $catalog_card_price_entity = isset($contract['design']['entities']['cardPrice']) && is_array($contract['design']['entities']['cardPrice'])
                ? $contract['design']['entities']['cardPrice']
                : [];
            $catalog_media_inherit_global = array_key_exists('inheritGlobalStyle', $catalog_media_entity)
                ? (!empty($catalog_media_entity['inheritGlobalStyle']) ? '1' : '0')
                : (((int) ($catalog_media_entity['radius'] ?? 20)) === 20 ? '1' : '0');
            $catalog_item_surface_radius = (int) ($catalog_item_surface_entity['radius'] ?? 22);
            $catalog_item_surface_border_width = (int) ($catalog_item_surface_entity['borderWidth'] ?? 1);
            $catalog_item_surface_border_color = strtolower((string) ($catalog_item_surface_entity['borderColor'] ?? '#dbe4ef'));
            $catalog_item_surface_shadow = (string) ($catalog_item_surface_entity['shadow'] ?? 'md');
            $catalog_item_surface_inherit_global = array_key_exists('inheritGlobalStyle', $catalog_item_surface_entity)
                ? (!empty($catalog_item_surface_entity['inheritGlobalStyle']) ? '1' : '0')
                : ((($catalog_item_surface_radius === 22 || $catalog_item_surface_radius === 1)
                    && $catalog_item_surface_border_width === 1
                    && $catalog_item_surface_border_color === '#dbe4ef'
                    && $catalog_item_surface_shadow === 'md') ? '1' : '0');
            return array_merge(self::denormalizeProps('content_feed', $contract), [
                'section_link_label' => (string) ($contract['content']['primaryButton']['label'] ?? 'Открыть все'),
                'section_link_url' => (string) ($contract['content']['primaryButton']['url'] ?? '/catalog'),
                'media_inherit_global' => $catalog_media_inherit_global,
                'item_surface_inherit_global' => $catalog_item_surface_inherit_global,
                'toolbar_background_mode' => (string) ($catalog_toolbar_entity['backgroundMode'] ?? 'solid'),
                'toolbar_background_color' => (string) ($catalog_toolbar_entity['backgroundColor'] ?? ''),
                'toolbar_padding' => (string) ($catalog_toolbar_entity['padding'] ?? 16),
                'toolbar_radius' => (string) ($catalog_toolbar_entity['radius'] ?? 22),
                'toolbar_border_width' => (string) ($catalog_toolbar_entity['borderWidth'] ?? 1),
                'toolbar_border_color' => (string) ($catalog_toolbar_entity['borderColor'] ?? ''),
                'toolbar_shadow' => (string) ($catalog_toolbar_entity['shadow'] ?? 'sm'),
                'toolbar_controls_background_mode' => (string) ($catalog_toolbar_controls_entity['backgroundMode'] ?? 'solid'),
                'toolbar_controls_background_color' => (string) ($catalog_toolbar_controls_entity['backgroundColor'] ?? ''),
                'toolbar_controls_radius' => (string) ($catalog_toolbar_controls_entity['radius'] ?? 16),
                'toolbar_controls_border_width' => (string) ($catalog_toolbar_controls_entity['borderWidth'] ?? 1),
                'toolbar_controls_border_color' => (string) ($catalog_toolbar_controls_entity['borderColor'] ?? ''),
                'toolbar_controls_shadow' => (string) ($catalog_toolbar_controls_entity['shadow'] ?? 'none'),
                'card_price_size_desktop' => (string) ($catalog_card_price_entity['desktop']['fontSize'] ?? 19),
                'card_price_size_mobile' => (string) ($catalog_card_price_entity['mobile']['fontSize'] ?? 17),
                'card_price_weight_desktop' => (string) ($catalog_card_price_entity['desktop']['weight'] ?? $catalog_card_price_entity['weight'] ?? '800'),
                'card_price_weight_mobile' => (string) ($catalog_card_price_entity['mobile']['weight'] ?? $catalog_card_price_entity['weight'] ?? '800'),
                'card_price_color_desktop' => (string) ($catalog_card_price_entity['desktop']['color'] ?? $catalog_card_price_entity['color'] ?? ''),
                'card_price_color_mobile' => (string) ($catalog_card_price_entity['mobile']['color'] ?? $catalog_card_price_entity['color'] ?? ''),
                'card_price_line_height_percent_desktop' => (string) ($catalog_card_price_entity['desktop']['lineHeightPercent'] ?? $catalog_card_price_entity['lineHeightPercent'] ?? 120),
                'card_price_line_height_percent_mobile' => (string) ($catalog_card_price_entity['mobile']['lineHeightPercent'] ?? $catalog_card_price_entity['lineHeightPercent'] ?? 120),
                'card_price_letter_spacing_desktop' => (string) ($catalog_card_price_entity['desktop']['letterSpacing'] ?? $catalog_card_price_entity['letterSpacing'] ?? 0),
                'card_price_letter_spacing_mobile' => (string) ($catalog_card_price_entity['mobile']['letterSpacing'] ?? $catalog_card_price_entity['letterSpacing'] ?? 0),
                'show_search' => !empty($contract['runtime']['visibility']['search']) ? '1' : '0',
                'show_category_filter' => !empty($contract['runtime']['visibility']['categoryFilter']) ? '1' : '0',
                'show_price_filter' => !empty($contract['runtime']['visibility']['priceFilter']) ? '1' : '0',
                'show_sort' => !empty($contract['runtime']['visibility']['sort']) ? '1' : '0',
                'show_active_filters' => !empty($contract['runtime']['visibility']['activeFilters']) ? '1' : '0',
                'show_badge' => !empty($contract['runtime']['visibility']['badge']) ? '1' : '0',
                'show_price' => !empty($contract['runtime']['visibility']['price']) ? '1' : '0',
                'show_old_price' => !empty($contract['runtime']['visibility']['oldPrice']) ? '1' : '0',
                'show_cta' => !empty($contract['runtime']['visibility']['cta']) ? '1' : '0',
                'collection_mode' => (string) ($catalog_runtime['collectionMode'] ?? 'all'),
                'items_per_page' => (string) ($catalog_runtime['itemsPerPage'] ?? 6),
                'show_results_count' => array_key_exists('showResultsCount', $catalog_runtime) ? (!empty($catalog_runtime['showResultsCount']) ? '1' : '0') : '1',
                'search_in_title' => array_key_exists('title', $catalog_search_fields) ? (!empty($catalog_search_fields['title']) ? '1' : '0') : '1',
                'search_in_excerpt' => array_key_exists('excerpt', $catalog_search_fields) ? (!empty($catalog_search_fields['excerpt']) ? '1' : '0') : '1',
                'search_in_category' => array_key_exists('category', $catalog_search_fields) ? (!empty($catalog_search_fields['category']) ? '1' : '0') : '1',
                'search_in_badge' => array_key_exists('badge', $catalog_search_fields) ? (!empty($catalog_search_fields['badge']) ? '1' : '0') : '1',
                'search_in_tags' => array_key_exists('tags', $catalog_search_fields) ? (!empty($catalog_search_fields['tags']) ? '1' : '0') : '1',
                'search_in_price' => array_key_exists('price', $catalog_search_fields) ? (!empty($catalog_search_fields['price']) ? '1' : '0') : '0',
                'search_in_availability' => array_key_exists('availability', $catalog_search_fields) ? (!empty($catalog_search_fields['availability']) ? '1' : '0') : '1',
            ]);
        }

        if (NordicblocksManagedScaffoldRegistry::isManagedType($type)) {
            return self::denormalizeManagedScaffold($type, $contract);
        }

        return [];
    }

    private static function normalizeManagedScaffold(array $block, array $stored_contract = []) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
        $props = (array) ($block['props'] ?? []);
        $defaults = NordicblocksManagedScaffoldRegistry::getSchemaDefaults($type);
        $profile = NordicblocksManagedScaffoldRegistry::getProfile($type);
        $generator = NordicblocksManagedScaffoldRegistry::getGeneratorMeta($type);
        $entity_keys = NordicblocksManagedScaffoldRegistry::getEntityKeys($type);
        $theme = self::normalizeSelect((string) ($props['theme'] ?? ($defaults['theme'] ?? 'light')), ['light', 'dark', 'accent', 'alt'], 'light');
        $background = self::normalizeBackgroundConfig($props);
        $data = self::normalizeDataLayer($type, (array) ($stored_contract['data'] ?? []));

        $contract = [
            'meta' => [
                'contractVersion' => 3,
                'blockType' => $type,
                'schemaVersion' => 1,
                'label' => (string) ($block['title'] ?? ($defaults['heading'] ?? $type)),
                'status' => (string) ($block['status'] ?? 'active'),
                'generator' => $generator,
            ],
            'content' => [
                'eyebrow' => NordicblocksManagedScaffoldRegistry::hasEntity($type, 'eyebrow') ? (string) ($props['eyebrow'] ?? ($defaults['eyebrow'] ?? '')) : '',
                'title' => (string) ($props['heading'] ?? ($defaults['heading'] ?? ($block['title'] ?? ''))),
                'subtitle' => (string) ($props['subheading'] ?? ($props['intro'] ?? ($defaults['subheading'] ?? ''))),
                'body' => NordicblocksManagedScaffoldRegistry::hasEntity($type, 'body') ? (string) ($props['body'] ?? ($defaults['body'] ?? '')) : '',
                'primaryButton' => [
                    'label' => (string) ($props['btn_primary_label'] ?? ($props['section_link_label'] ?? ($defaults['btn_primary_label'] ?? ($defaults['section_link_label'] ?? '')))),
                    'url' => (string) ($props['btn_primary_url'] ?? ($props['section_link_url'] ?? ($defaults['btn_primary_url'] ?? ($defaults['section_link_url'] ?? '#')))),
                ],
                'secondaryButton' => [
                    'label' => (string) ($props['btn_secondary_label'] ?? ''),
                    'url' => (string) ($props['btn_secondary_url'] ?? '#'),
                ],
                'tertiaryButton' => [
                    'label' => (string) ($props['btn_tertiary_label'] ?? ''),
                    'url' => (string) ($props['btn_tertiary_url'] ?? '#'),
                ],
                'media' => [
                    'image' => (string) ($props['image'] ?? ''),
                    'alt' => (string) ($props['image_alt'] ?? ''),
                ],
                'meta' => [
                    'category' => '',
                    'author' => '',
                    'date' => '',
                    'views' => '',
                    'comments' => '',
                ],
                'slides' => NordicblocksManagedScaffoldRegistry::hasEntity($type, 'slide')
                    ? self::normalizeSliderSlides($props['slides'] ?? ($defaults['slides'] ?? []))
                    : [],
                'items' => NordicblocksManagedScaffoldRegistry::hasEntity($type, 'items')
                    ? (NordicblocksManagedScaffoldRegistry::usesFaqMapping($type)
                        ? self::normalizeFaqItems($props['items'] ?? ($defaults['items'] ?? []))
                        : self::normalizeContentFeedItems($props['items'] ?? ($defaults['items'] ?? [])))
                    : [],
            ],
            'design' => [
                'section' => [
                    'theme' => $theme,
                    'background' => $background,
                ],
                'entities' => self::buildManagedDesignEntities($type, $props),
            ],
            'layout' => self::buildManagedLayout($type, $props, $defaults),
            'data' => $data,
            'entities' => self::buildManagedEntityMeta($entity_keys),
            'runtime' => [
                'renderMode' => 'ssr',
                'cacheScope' => 'page',
                'visibility' => [
                    'image' => self::normalizeBoolean($props['show_image'] ?? '1', true),
                    'category' => self::normalizeBoolean($props['show_category'] ?? '1', true),
                    'excerpt' => self::normalizeBoolean($props['show_excerpt'] ?? '1', true),
                    'date' => self::normalizeBoolean($props['show_date'] ?? '1', true),
                    'views' => self::normalizeBoolean($props['show_views'] ?? '1', true),
                    'comments' => self::normalizeBoolean($props['show_comments'] ?? '1', true),
                    'moreLink' => self::normalizeBoolean($props['show_more_link'] ?? '1', true),
                    'itemLink' => self::normalizeBoolean($props['show_item_link'] ?? '1', true),
                    'navigation' => self::normalizeBoolean($props['show_navigation'] ?? '1', true),
                    'pagination' => self::normalizeBoolean($props['show_pagination'] ?? '1', true),
                    'progress' => self::normalizeBoolean($props['show_progress'] ?? '0', false),
                    'slideMedia' => self::normalizeBoolean($props['show_media'] ?? '1', true),
                    'slideEyebrow' => self::normalizeBoolean($props['show_eyebrow'] ?? '1', true),
                    'slideText' => self::normalizeBoolean($props['show_text'] ?? '1', true),
                    'slideMeta' => self::normalizeBoolean($props['show_meta'] ?? '1', true),
                    'slidePrimaryAction' => self::normalizeBoolean($props['show_primary_cta'] ?? '1', true),
                    'slideSecondaryAction' => self::normalizeBoolean($props['show_secondary_cta'] ?? '1', true),
                ],
                'slider' => NordicblocksManagedScaffoldRegistry::hasEntity($type, 'slide') ? [
                    'swipe' => self::normalizeBoolean($props['swipe'] ?? '1', true),
                    'autoplay' => self::normalizeBoolean($props['autoplay'] ?? '0', false),
                    'loop' => self::normalizeBoolean($props['loop'] ?? '0', false),
                    'autoplayDelay' => self::normalizeNumber($props['autoplay_delay'] ?? 4500, 1000, 20000, 4500),
                    'transitionMs' => self::normalizeNumber($props['transition_ms'] ?? 450, 100, 5000, 450),
                ] : [],
                'collectionMode' => self::normalizeSelect((string) ($props['collection_mode'] ?? ($defaults['collection_mode'] ?? 'all')), ['all', 'load_more', 'pagination'], 'all'),
                'itemsPerPage' => self::normalizeNumber($props['items_per_page'] ?? ($defaults['items_per_page'] ?? 6), 1, 48, 6),
                'initialItemsCount' => self::normalizeNumber($props['items_initial'] ?? ($defaults['items_initial'] ?? ($defaults['items_per_page'] ?? 6)), 1, 48, 6),
                'loadMoreLabel' => trim((string) ($props['load_more_label'] ?? ($defaults['load_more_label'] ?? 'Показать ещё'))),
                'showBottomNavigation' => self::normalizeBoolean($props['show_bottom_navigation'] ?? ($defaults['show_bottom_navigation'] ?? '1'), true),
                'animation' => [
                    'name' => self::normalizeSelect((string) ($props['block_animation'] ?? 'none'), ['none', 'fade-up', 'fade-in', 'zoom-in'], 'none'),
                    'delay' => self::normalizeNumber($props['block_animation_delay'] ?? 0, 0, 1500, 0),
                ],
                'featureFlags' => [
                    'managedScaffold' => true,
                    'scaffoldProfile' => $profile,
                    'sourceModeProfile' => NordicblocksManagedScaffoldRegistry::getSourceModeProfile($type),
                ],
            ],
        ];

        return self::mergeStoredContract($contract, $stored_contract);
    }

    private static function denormalizeManagedScaffold($type, array $contract) {
        $defaults = NordicblocksManagedScaffoldRegistry::getSchemaDefaults($type);
        $result = $defaults;

        $result['theme'] = (string) ($contract['design']['section']['theme'] ?? ($defaults['theme'] ?? 'light'));
        $result['heading'] = (string) ($contract['content']['title'] ?? ($defaults['heading'] ?? ''));
        $result['subheading'] = (string) ($contract['content']['subtitle'] ?? ($defaults['subheading'] ?? ''));
        $result['title_visible'] = !empty($contract['design']['entities']['title']['visible']) ? '1' : '0';
        $result['subtitle_visible'] = !empty($contract['design']['entities']['subtitle']['visible']) ? '1' : '0';
        $result['show_image'] = !array_key_exists('image', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['image']) ? '1' : '0';
        $result['show_category'] = !array_key_exists('category', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['category']) ? '1' : '0';
        $result['show_excerpt'] = !array_key_exists('excerpt', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['excerpt']) ? '1' : '0';
        $result['show_date'] = !array_key_exists('date', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['date']) ? '1' : '0';
        $result['show_views'] = !array_key_exists('views', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['views']) ? '1' : '0';
        $result['show_comments'] = !array_key_exists('comments', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['comments']) ? '1' : '0';
        $result['show_more_link'] = !array_key_exists('moreLink', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['moreLink']) ? '1' : '0';
        $result['padding_top_desktop'] = (string) ($contract['layout']['desktop']['paddingTop'] ?? ($defaults['padding_top_desktop'] ?? 72));
        $result['padding_bottom_desktop'] = (string) ($contract['layout']['desktop']['paddingBottom'] ?? ($defaults['padding_bottom_desktop'] ?? 72));
        $result['padding_top_mobile'] = (string) ($contract['layout']['mobile']['paddingTop'] ?? ($defaults['padding_top_mobile'] ?? 48));
        $result['padding_bottom_mobile'] = (string) ($contract['layout']['mobile']['paddingBottom'] ?? ($defaults['padding_bottom_mobile'] ?? 48));
        $result['content_width'] = (string) ($contract['layout']['desktop']['contentWidth'] ?? ($defaults['content_width'] ?? 960));
        $result['heading_tag'] = (string) ($contract['design']['entities']['title']['tag'] ?? ($defaults['heading_tag'] ?? 'h2'));
        $result['title_size_desktop'] = (string) ($contract['design']['entities']['title']['desktop']['fontSize'] ?? ($defaults['title_size_desktop'] ?? 42));
        $result['title_size_mobile'] = (string) ($contract['design']['entities']['title']['mobile']['fontSize'] ?? ($defaults['title_size_mobile'] ?? 30));
        $result['title_margin_bottom_desktop'] = (string) ($contract['design']['entities']['title']['desktop']['marginBottom'] ?? ($defaults['title_margin_bottom_desktop'] ?? 12));
        $result['title_margin_bottom_mobile'] = (string) ($contract['design']['entities']['title']['mobile']['marginBottom'] ?? ($defaults['title_margin_bottom_mobile'] ?? 10));
        $result['title_weight_desktop'] = (string) ($contract['design']['entities']['title']['desktop']['weight'] ?? ($contract['design']['entities']['title']['weight'] ?? ($defaults['title_weight_desktop'] ?? $defaults['heading_weight'] ?? '800')));
        $result['title_weight_mobile'] = (string) ($contract['design']['entities']['title']['mobile']['weight'] ?? ($contract['design']['entities']['title']['weight'] ?? ($defaults['title_weight_mobile'] ?? $defaults['heading_weight'] ?? '800')));
        $result['title_color_desktop'] = (string) ($contract['design']['entities']['title']['desktop']['color'] ?? ($contract['design']['entities']['title']['color'] ?? ($defaults['title_color_desktop'] ?? '')));
        $result['title_color_mobile'] = (string) ($contract['design']['entities']['title']['mobile']['color'] ?? ($contract['design']['entities']['title']['color'] ?? ($defaults['title_color_mobile'] ?? '')));
        $result['title_line_height_percent_desktop'] = (string) ($contract['design']['entities']['title']['desktop']['lineHeightPercent'] ?? ($defaults['title_line_height_percent_desktop'] ?? 110));
        $result['title_line_height_percent_mobile'] = (string) ($contract['design']['entities']['title']['mobile']['lineHeightPercent'] ?? ($defaults['title_line_height_percent_mobile'] ?? 110));
        $result['title_letter_spacing_desktop'] = (string) ($contract['design']['entities']['title']['desktop']['letterSpacing'] ?? ($defaults['title_letter_spacing_desktop'] ?? 0));
        $result['title_letter_spacing_mobile'] = (string) ($contract['design']['entities']['title']['mobile']['letterSpacing'] ?? ($defaults['title_letter_spacing_mobile'] ?? 0));
        $result['title_max_width_desktop'] = (string) ($contract['design']['entities']['title']['desktop']['maxWidth'] ?? ($defaults['title_max_width_desktop'] ?? 760));
        $result['title_max_width_mobile'] = (string) ($contract['design']['entities']['title']['mobile']['maxWidth'] ?? ($defaults['title_max_width_mobile'] ?? 760));
        $result['subtitle_size_desktop'] = (string) ($contract['design']['entities']['subtitle']['desktop']['fontSize'] ?? ($defaults['subtitle_size_desktop'] ?? 18));
        $result['subtitle_size_mobile'] = (string) ($contract['design']['entities']['subtitle']['mobile']['fontSize'] ?? ($defaults['subtitle_size_mobile'] ?? 16));
        $result['subtitle_margin_bottom_desktop'] = (string) ($contract['design']['entities']['subtitle']['desktop']['marginBottom'] ?? ($defaults['subtitle_margin_bottom_desktop'] ?? 18));
        $result['subtitle_margin_bottom_mobile'] = (string) ($contract['design']['entities']['subtitle']['mobile']['marginBottom'] ?? ($defaults['subtitle_margin_bottom_mobile'] ?? 14));
        $result['subtitle_weight_desktop'] = (string) ($contract['design']['entities']['subtitle']['desktop']['weight'] ?? ($contract['design']['entities']['subtitle']['weight'] ?? ($defaults['subtitle_weight_desktop'] ?? '400')));
        $result['subtitle_weight_mobile'] = (string) ($contract['design']['entities']['subtitle']['mobile']['weight'] ?? ($contract['design']['entities']['subtitle']['weight'] ?? ($defaults['subtitle_weight_mobile'] ?? '400')));
        $result['subtitle_color_desktop'] = (string) ($contract['design']['entities']['subtitle']['desktop']['color'] ?? ($contract['design']['entities']['subtitle']['color'] ?? ($defaults['subtitle_color_desktop'] ?? '')));
        $result['subtitle_color_mobile'] = (string) ($contract['design']['entities']['subtitle']['mobile']['color'] ?? ($contract['design']['entities']['subtitle']['color'] ?? ($defaults['subtitle_color_mobile'] ?? '')));
        $result['subtitle_line_height_percent_desktop'] = (string) ($contract['design']['entities']['subtitle']['desktop']['lineHeightPercent'] ?? ($defaults['subtitle_line_height_percent_desktop'] ?? 160));
        $result['subtitle_line_height_percent_mobile'] = (string) ($contract['design']['entities']['subtitle']['mobile']['lineHeightPercent'] ?? ($defaults['subtitle_line_height_percent_mobile'] ?? 160));
        $result['subtitle_letter_spacing_desktop'] = (string) ($contract['design']['entities']['subtitle']['desktop']['letterSpacing'] ?? ($defaults['subtitle_letter_spacing_desktop'] ?? 0));
        $result['subtitle_letter_spacing_mobile'] = (string) ($contract['design']['entities']['subtitle']['mobile']['letterSpacing'] ?? ($defaults['subtitle_letter_spacing_mobile'] ?? 0));
        $result['subtitle_max_width_desktop'] = (string) ($contract['design']['entities']['subtitle']['desktop']['maxWidth'] ?? ($defaults['subtitle_max_width_desktop'] ?? 760));
        $result['subtitle_max_width_mobile'] = (string) ($contract['design']['entities']['subtitle']['mobile']['maxWidth'] ?? ($defaults['subtitle_max_width_mobile'] ?? 760));

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'eyebrow')) {
            $result['eyebrow'] = (string) ($contract['content']['eyebrow'] ?? ($defaults['eyebrow'] ?? ''));
            $result['eyebrow_size_desktop'] = (string) ($contract['design']['entities']['eyebrow']['desktop']['fontSize'] ?? ($defaults['eyebrow_size_desktop'] ?? 14));
            $result['eyebrow_size_mobile'] = (string) ($contract['design']['entities']['eyebrow']['mobile']['fontSize'] ?? ($defaults['eyebrow_size_mobile'] ?? 13));
            $result['eyebrow_margin_bottom_desktop'] = (string) ($contract['design']['entities']['eyebrow']['desktop']['marginBottom'] ?? ($defaults['eyebrow_margin_bottom_desktop'] ?? 12));
            $result['eyebrow_margin_bottom_mobile'] = (string) ($contract['design']['entities']['eyebrow']['mobile']['marginBottom'] ?? ($defaults['eyebrow_margin_bottom_mobile'] ?? 10));
            $result['eyebrow_weight_desktop'] = (string) ($contract['design']['entities']['eyebrow']['desktop']['weight'] ?? ($contract['design']['entities']['eyebrow']['weight'] ?? ($defaults['eyebrow_weight_desktop'] ?? '700')));
            $result['eyebrow_weight_mobile'] = (string) ($contract['design']['entities']['eyebrow']['mobile']['weight'] ?? ($contract['design']['entities']['eyebrow']['weight'] ?? ($defaults['eyebrow_weight_mobile'] ?? '700')));
            $result['eyebrow_color_desktop'] = (string) ($contract['design']['entities']['eyebrow']['desktop']['color'] ?? ($contract['design']['entities']['eyebrow']['color'] ?? ($defaults['eyebrow_color_desktop'] ?? '')));
            $result['eyebrow_color_mobile'] = (string) ($contract['design']['entities']['eyebrow']['mobile']['color'] ?? ($contract['design']['entities']['eyebrow']['color'] ?? ($defaults['eyebrow_color_mobile'] ?? '')));
            $result['eyebrow_line_height_percent_desktop'] = (string) ($contract['design']['entities']['eyebrow']['desktop']['lineHeightPercent'] ?? ($defaults['eyebrow_line_height_percent_desktop'] ?? 140));
            $result['eyebrow_line_height_percent_mobile'] = (string) ($contract['design']['entities']['eyebrow']['mobile']['lineHeightPercent'] ?? ($defaults['eyebrow_line_height_percent_mobile'] ?? 140));
            $result['eyebrow_letter_spacing_desktop'] = (string) ($contract['design']['entities']['eyebrow']['desktop']['letterSpacing'] ?? ($defaults['eyebrow_letter_spacing_desktop'] ?? 1));
            $result['eyebrow_letter_spacing_mobile'] = (string) ($contract['design']['entities']['eyebrow']['mobile']['letterSpacing'] ?? ($defaults['eyebrow_letter_spacing_mobile'] ?? 1));
            $result['eyebrow_text_transform'] = (string) ($contract['design']['entities']['eyebrow']['textTransform'] ?? ($defaults['eyebrow_text_transform'] ?? 'uppercase'));
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'body')) {
            $result['body'] = (string) ($contract['content']['body'] ?? ($defaults['body'] ?? ''));
            $result['body_size_desktop'] = (string) ($contract['design']['entities']['body']['desktop']['fontSize'] ?? ($defaults['body_size_desktop'] ?? 16));
            $result['body_size_mobile'] = (string) ($contract['design']['entities']['body']['mobile']['fontSize'] ?? ($defaults['body_size_mobile'] ?? 15));
            $result['body_weight_desktop'] = (string) ($contract['design']['entities']['body']['desktop']['weight'] ?? ($contract['design']['entities']['body']['weight'] ?? ($defaults['body_weight_desktop'] ?? '400')));
            $result['body_weight_mobile'] = (string) ($contract['design']['entities']['body']['mobile']['weight'] ?? ($contract['design']['entities']['body']['weight'] ?? ($defaults['body_weight_mobile'] ?? '400')));
            $result['body_color_desktop'] = (string) ($contract['design']['entities']['body']['desktop']['color'] ?? ($contract['design']['entities']['body']['color'] ?? ($defaults['body_color_desktop'] ?? '')));
            $result['body_color_mobile'] = (string) ($contract['design']['entities']['body']['mobile']['color'] ?? ($contract['design']['entities']['body']['color'] ?? ($defaults['body_color_mobile'] ?? '')));
            $result['body_line_height_percent_desktop'] = (string) ($contract['design']['entities']['body']['desktop']['lineHeightPercent'] ?? ($defaults['body_line_height_percent_desktop'] ?? 165));
            $result['body_line_height_percent_mobile'] = (string) ($contract['design']['entities']['body']['mobile']['lineHeightPercent'] ?? ($defaults['body_line_height_percent_mobile'] ?? 165));
            $result['body_letter_spacing_desktop'] = (string) ($contract['design']['entities']['body']['desktop']['letterSpacing'] ?? ($defaults['body_letter_spacing_desktop'] ?? 0));
            $result['body_letter_spacing_mobile'] = (string) ($contract['design']['entities']['body']['mobile']['letterSpacing'] ?? ($defaults['body_letter_spacing_mobile'] ?? 0));
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'primaryButton')) {
            $result['btn_primary_label'] = (string) ($contract['content']['primaryButton']['label'] ?? ($defaults['btn_primary_label'] ?? ''));
            $result['btn_primary_url'] = (string) ($contract['content']['primaryButton']['url'] ?? ($defaults['btn_primary_url'] ?? '#'));
            $result['section_link_label'] = (string) ($contract['content']['primaryButton']['label'] ?? ($defaults['section_link_label'] ?? ''));
            $result['section_link_url'] = (string) ($contract['content']['primaryButton']['url'] ?? ($defaults['section_link_url'] ?? '#'));
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'media')) {
            $result['image'] = (string) ($contract['content']['media']['image'] ?? '');
            $result['image_alt'] = (string) ($contract['content']['media']['alt'] ?? '');
            $result['media_aspect_ratio'] = (string) ($contract['design']['entities']['media']['aspectRatio'] ?? '16:10');
            $result['media_object_fit'] = (string) ($contract['design']['entities']['media']['objectFit'] ?? 'cover');
            $result['media_radius'] = (string) ($contract['design']['entities']['media']['radius'] ?? 24);
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'accentSurface')) {
            $result['accent_surface_background_mode'] = (string) ($contract['design']['entities']['accentSurface']['backgroundMode'] ?? (((string) ($contract['design']['entities']['accentSurface']['backgroundColor'] ?? '')) !== '' ? 'solid' : 'transparent'));
            $result['accent_surface_background_color'] = (string) ($contract['design']['entities']['accentSurface']['backgroundColor'] ?? '');
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'bodySurface')) {
            $result['body_surface_background_mode'] = (string) ($contract['design']['entities']['bodySurface']['backgroundMode'] ?? (((string) ($contract['design']['entities']['bodySurface']['backgroundColor'] ?? '')) !== '' ? 'solid' : 'transparent'));
            $result['body_surface_background_color'] = (string) ($contract['design']['entities']['bodySurface']['backgroundColor'] ?? '');
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'items')) {
            $result['items'] = is_array($contract['content']['items'] ?? null) ? $contract['content']['items'] : [];
            $result['layout_preset'] = (string) ($contract['layout']['preset'] ?? ($defaults['layout_preset'] ?? 'default'));
            $result['columns_desktop'] = (string) ($contract['layout']['desktop']['columns'] ?? ($defaults['columns_desktop'] ?? 3));
            $result['columns_mobile'] = (string) ($contract['layout']['mobile']['columns'] ?? ($defaults['columns_mobile'] ?? 1));
            $result['card_gap_desktop'] = (string) ($contract['layout']['desktop']['cardGap'] ?? ($defaults['card_gap_desktop'] ?? 24));
            $result['card_gap_mobile'] = (string) ($contract['layout']['mobile']['cardGap'] ?? ($defaults['card_gap_mobile'] ?? 16));
            $result['header_gap_desktop'] = (string) ($contract['layout']['desktop']['headerGap'] ?? ($defaults['header_gap_desktop'] ?? 24));
            $result['header_gap_mobile'] = (string) ($contract['layout']['mobile']['headerGap'] ?? ($defaults['header_gap_mobile'] ?? 18));
            $result['collection_mode'] = (string) ($contract['runtime']['collectionMode'] ?? ($defaults['collection_mode'] ?? 'all'));
            $result['items_per_page'] = (string) ($contract['runtime']['itemsPerPage'] ?? ($defaults['items_per_page'] ?? 6));
            $result['items_initial'] = (string) ($contract['runtime']['initialItemsCount'] ?? ($defaults['items_initial'] ?? ($defaults['items_per_page'] ?? 6)));
            $result['load_more_label'] = (string) ($contract['runtime']['loadMoreLabel'] ?? ($defaults['load_more_label'] ?? 'Показать ещё'));
            $result['show_bottom_navigation'] = !array_key_exists('showBottomNavigation', (array) ($contract['runtime'] ?? [])) || !empty($contract['runtime']['showBottomNavigation']) ? '1' : '0';
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'meta')) {
            $result['meta_size_desktop'] = (string) ($contract['design']['entities']['meta']['desktop']['fontSize'] ?? ($defaults['meta_size_desktop'] ?? 11));
            $result['meta_size_mobile'] = (string) ($contract['design']['entities']['meta']['mobile']['fontSize'] ?? ($defaults['meta_size_mobile'] ?? 11));
            $result['meta_weight_desktop'] = (string) ($contract['design']['entities']['meta']['desktop']['weight'] ?? ($contract['design']['entities']['meta']['weight'] ?? ($defaults['meta_weight_desktop'] ?? '600')));
            $result['meta_weight_mobile'] = (string) ($contract['design']['entities']['meta']['mobile']['weight'] ?? ($contract['design']['entities']['meta']['weight'] ?? ($defaults['meta_weight_mobile'] ?? '600')));
            $result['meta_color_desktop'] = (string) ($contract['design']['entities']['meta']['desktop']['color'] ?? ($contract['design']['entities']['meta']['color'] ?? ($defaults['meta_color_desktop'] ?? '')));
            $result['meta_color_mobile'] = (string) ($contract['design']['entities']['meta']['mobile']['color'] ?? ($contract['design']['entities']['meta']['color'] ?? ($defaults['meta_color_mobile'] ?? '')));
            $result['meta_line_height_percent_desktop'] = (string) ($contract['design']['entities']['meta']['desktop']['lineHeightPercent'] ?? ($contract['design']['entities']['meta']['lineHeightPercent'] ?? ($defaults['meta_line_height_percent_desktop'] ?? $defaults['meta_line_height_percent'] ?? 130)));
            $result['meta_line_height_percent_mobile'] = (string) ($contract['design']['entities']['meta']['mobile']['lineHeightPercent'] ?? ($contract['design']['entities']['meta']['lineHeightPercent'] ?? ($defaults['meta_line_height_percent_mobile'] ?? $defaults['meta_line_height_percent'] ?? 130)));
            $result['meta_letter_spacing_desktop'] = (string) ($contract['design']['entities']['meta']['desktop']['letterSpacing'] ?? ($contract['design']['entities']['meta']['letterSpacing'] ?? ($defaults['meta_letter_spacing_desktop'] ?? $defaults['meta_letter_spacing'] ?? 1)));
            $result['meta_letter_spacing_mobile'] = (string) ($contract['design']['entities']['meta']['mobile']['letterSpacing'] ?? ($contract['design']['entities']['meta']['letterSpacing'] ?? ($defaults['meta_letter_spacing_mobile'] ?? $defaults['meta_letter_spacing'] ?? 1)));
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'itemSurface')) {
            $result['item_surface_variant'] = (string) ($contract['design']['entities']['itemSurface']['variant'] ?? ($defaults['item_surface_variant'] ?? 'card'));
            $result['item_surface_radius'] = (string) ($contract['design']['entities']['itemSurface']['radius'] ?? ($defaults['item_surface_radius'] ?? 24));
            $result['item_surface_border_width'] = (string) ($contract['design']['entities']['itemSurface']['borderWidth'] ?? ($defaults['item_surface_border_width'] ?? 1));
            $result['item_surface_border_color'] = (string) ($contract['design']['entities']['itemSurface']['borderColor'] ?? ($defaults['item_surface_border_color'] ?? '#e2e8f0'));
            $result['item_surface_shadow'] = (string) ($contract['design']['entities']['itemSurface']['shadow'] ?? ($defaults['item_surface_shadow'] ?? 'md'));
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'itemTitle')) {
            $result['item_title_size_desktop'] = (string) ($contract['design']['entities']['itemTitle']['desktop']['fontSize'] ?? ($defaults['item_title_size_desktop'] ?? 22));
            $result['item_title_size_mobile'] = (string) ($contract['design']['entities']['itemTitle']['mobile']['fontSize'] ?? ($defaults['item_title_size_mobile'] ?? 18));
            $result['item_title_weight_desktop'] = (string) ($contract['design']['entities']['itemTitle']['desktop']['weight'] ?? ($contract['design']['entities']['itemTitle']['weight'] ?? ($defaults['item_title_weight_desktop'] ?? $defaults['item_title_weight'] ?? '700')));
            $result['item_title_weight_mobile'] = (string) ($contract['design']['entities']['itemTitle']['mobile']['weight'] ?? ($contract['design']['entities']['itemTitle']['weight'] ?? ($defaults['item_title_weight_mobile'] ?? $defaults['item_title_weight'] ?? '700')));
            $result['item_title_color_desktop'] = (string) ($contract['design']['entities']['itemTitle']['desktop']['color'] ?? ($contract['design']['entities']['itemTitle']['color'] ?? ($defaults['item_title_color_desktop'] ?? $defaults['item_title_color'] ?? '')));
            $result['item_title_color_mobile'] = (string) ($contract['design']['entities']['itemTitle']['mobile']['color'] ?? ($contract['design']['entities']['itemTitle']['color'] ?? ($defaults['item_title_color_mobile'] ?? $defaults['item_title_color'] ?? '')));
            $result['item_title_line_height_percent_desktop'] = (string) ($contract['design']['entities']['itemTitle']['desktop']['lineHeightPercent'] ?? ($contract['design']['entities']['itemTitle']['lineHeightPercent'] ?? ($defaults['item_title_line_height_percent_desktop'] ?? $defaults['item_title_line_height_percent'] ?? 125)));
            $result['item_title_line_height_percent_mobile'] = (string) ($contract['design']['entities']['itemTitle']['mobile']['lineHeightPercent'] ?? ($contract['design']['entities']['itemTitle']['lineHeightPercent'] ?? ($defaults['item_title_line_height_percent_mobile'] ?? $defaults['item_title_line_height_percent'] ?? 125)));
            $result['item_title_letter_spacing_desktop'] = (string) ($contract['design']['entities']['itemTitle']['desktop']['letterSpacing'] ?? ($contract['design']['entities']['itemTitle']['letterSpacing'] ?? ($defaults['item_title_letter_spacing_desktop'] ?? $defaults['item_title_letter_spacing'] ?? 0)));
            $result['item_title_letter_spacing_mobile'] = (string) ($contract['design']['entities']['itemTitle']['mobile']['letterSpacing'] ?? ($contract['design']['entities']['itemTitle']['letterSpacing'] ?? ($defaults['item_title_letter_spacing_mobile'] ?? $defaults['item_title_letter_spacing'] ?? 0)));
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'itemText')) {
            $result['item_text_size_desktop'] = (string) ($contract['design']['entities']['itemText']['desktop']['fontSize'] ?? ($defaults['item_text_size_desktop'] ?? 15));
            $result['item_text_size_mobile'] = (string) ($contract['design']['entities']['itemText']['mobile']['fontSize'] ?? ($defaults['item_text_size_mobile'] ?? 14));
            $result['item_text_weight_desktop'] = (string) ($contract['design']['entities']['itemText']['desktop']['weight'] ?? ($contract['design']['entities']['itemText']['weight'] ?? ($defaults['item_text_weight_desktop'] ?? $defaults['item_text_weight'] ?? '400')));
            $result['item_text_weight_mobile'] = (string) ($contract['design']['entities']['itemText']['mobile']['weight'] ?? ($contract['design']['entities']['itemText']['weight'] ?? ($defaults['item_text_weight_mobile'] ?? $defaults['item_text_weight'] ?? '400')));
            $result['item_text_color_desktop'] = (string) ($contract['design']['entities']['itemText']['desktop']['color'] ?? ($contract['design']['entities']['itemText']['color'] ?? ($defaults['item_text_color_desktop'] ?? $defaults['item_text_color'] ?? '')));
            $result['item_text_color_mobile'] = (string) ($contract['design']['entities']['itemText']['mobile']['color'] ?? ($contract['design']['entities']['itemText']['color'] ?? ($defaults['item_text_color_mobile'] ?? $defaults['item_text_color'] ?? '')));
            $result['item_text_line_height_percent_desktop'] = (string) ($contract['design']['entities']['itemText']['desktop']['lineHeightPercent'] ?? ($contract['design']['entities']['itemText']['lineHeightPercent'] ?? ($defaults['item_text_line_height_percent_desktop'] ?? $defaults['item_text_line_height_percent'] ?? 160)));
            $result['item_text_line_height_percent_mobile'] = (string) ($contract['design']['entities']['itemText']['mobile']['lineHeightPercent'] ?? ($contract['design']['entities']['itemText']['lineHeightPercent'] ?? ($defaults['item_text_line_height_percent_mobile'] ?? $defaults['item_text_line_height_percent'] ?? 160)));
            $result['item_text_letter_spacing_desktop'] = (string) ($contract['design']['entities']['itemText']['desktop']['letterSpacing'] ?? ($contract['design']['entities']['itemText']['letterSpacing'] ?? ($defaults['item_text_letter_spacing_desktop'] ?? $defaults['item_text_letter_spacing'] ?? 0)));
            $result['item_text_letter_spacing_mobile'] = (string) ($contract['design']['entities']['itemText']['mobile']['letterSpacing'] ?? ($contract['design']['entities']['itemText']['letterSpacing'] ?? ($defaults['item_text_letter_spacing_mobile'] ?? $defaults['item_text_letter_spacing'] ?? 0)));
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'itemLink')) {
            $result['item_link_size_desktop'] = (string) ($contract['design']['entities']['itemLink']['desktop']['fontSize'] ?? ($defaults['item_link_size_desktop'] ?? 13));
            $result['item_link_size_mobile'] = (string) ($contract['design']['entities']['itemLink']['mobile']['fontSize'] ?? ($defaults['item_link_size_mobile'] ?? 12));
            $result['item_link_weight_desktop'] = (string) ($contract['design']['entities']['itemLink']['desktop']['weight'] ?? ($contract['design']['entities']['itemLink']['weight'] ?? ($defaults['item_link_weight_desktop'] ?? $defaults['item_link_weight'] ?? '700')));
            $result['item_link_weight_mobile'] = (string) ($contract['design']['entities']['itemLink']['mobile']['weight'] ?? ($contract['design']['entities']['itemLink']['weight'] ?? ($defaults['item_link_weight_mobile'] ?? $defaults['item_link_weight'] ?? '700')));
            $result['item_link_color_desktop'] = (string) ($contract['design']['entities']['itemLink']['desktop']['color'] ?? ($contract['design']['entities']['itemLink']['color'] ?? ($defaults['item_link_color_desktop'] ?? $defaults['item_link_color'] ?? '')));
            $result['item_link_color_mobile'] = (string) ($contract['design']['entities']['itemLink']['mobile']['color'] ?? ($contract['design']['entities']['itemLink']['color'] ?? ($defaults['item_link_color_mobile'] ?? $defaults['item_link_color'] ?? '')));
            $result['item_link_line_height_percent_desktop'] = (string) ($contract['design']['entities']['itemLink']['desktop']['lineHeightPercent'] ?? ($contract['design']['entities']['itemLink']['lineHeightPercent'] ?? ($defaults['item_link_line_height_percent_desktop'] ?? $defaults['item_link_line_height_percent'] ?? 120)));
            $result['item_link_line_height_percent_mobile'] = (string) ($contract['design']['entities']['itemLink']['mobile']['lineHeightPercent'] ?? ($contract['design']['entities']['itemLink']['lineHeightPercent'] ?? ($defaults['item_link_line_height_percent_mobile'] ?? $defaults['item_link_line_height_percent'] ?? 120)));
            $result['item_link_letter_spacing_desktop'] = (string) ($contract['design']['entities']['itemLink']['desktop']['letterSpacing'] ?? ($contract['design']['entities']['itemLink']['letterSpacing'] ?? ($defaults['item_link_letter_spacing_desktop'] ?? $defaults['item_link_letter_spacing'] ?? 1)));
            $result['item_link_letter_spacing_mobile'] = (string) ($contract['design']['entities']['itemLink']['mobile']['letterSpacing'] ?? ($contract['design']['entities']['itemLink']['letterSpacing'] ?? ($defaults['item_link_letter_spacing_mobile'] ?? $defaults['item_link_letter_spacing'] ?? 1)));
            $result['show_item_link'] = !array_key_exists('itemLink', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['itemLink']) ? '1' : '0';
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'slide')) {
            $result['slides'] = is_array($contract['content']['slides'] ?? null) ? $contract['content']['slides'] : [];
            $result['slides_per_view_desktop'] = (string) ($contract['layout']['desktop']['slidesPerView'] ?? ($defaults['slides_per_view_desktop'] ?? 3));
            $result['slides_per_view_mobile'] = (string) ($contract['layout']['mobile']['slidesPerView'] ?? ($defaults['slides_per_view_mobile'] ?? 1));
            $result['slide_gap_desktop'] = (string) ($contract['layout']['desktop']['slideGap'] ?? ($defaults['slide_gap_desktop'] ?? 24));
            $result['slide_gap_mobile'] = (string) ($contract['layout']['mobile']['slideGap'] ?? ($defaults['slide_gap_mobile'] ?? 16));
            $result['header_gap_desktop'] = (string) ($contract['layout']['desktop']['headerGap'] ?? ($defaults['header_gap_desktop'] ?? 28));
            $result['header_gap_mobile'] = (string) ($contract['layout']['mobile']['headerGap'] ?? ($defaults['header_gap_mobile'] ?? 18));
            $result['min_height_desktop'] = (string) ($contract['layout']['desktop']['minHeight'] ?? ($defaults['min_height_desktop'] ?? 0));
            $result['min_height_mobile'] = (string) ($contract['layout']['mobile']['minHeight'] ?? ($defaults['min_height_mobile'] ?? 0));
            $result['navigation_position_desktop'] = (string) ($contract['layout']['desktop']['navigationPosition'] ?? 'overlay');
            $result['navigation_position_mobile'] = (string) ($contract['layout']['mobile']['navigationPosition'] ?? 'hidden');
            $result['pagination_position_desktop'] = (string) ($contract['layout']['desktop']['paginationPosition'] ?? 'below');
            $result['pagination_position_mobile'] = (string) ($contract['layout']['mobile']['paginationPosition'] ?? 'below');
            $result['progress_position_desktop'] = (string) ($contract['layout']['desktop']['progressPosition'] ?? 'below');
            $result['progress_position_mobile'] = (string) ($contract['layout']['mobile']['progressPosition'] ?? 'below');
            $result['show_navigation'] = !array_key_exists('navigation', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['navigation']) ? '1' : '0';
            $result['show_pagination'] = !array_key_exists('pagination', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['pagination']) ? '1' : '0';
            $result['show_progress'] = !empty($contract['runtime']['visibility']['progress']) ? '1' : '0';
            $result['show_media'] = !array_key_exists('slideMedia', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['slideMedia']) ? '1' : '0';
            $result['show_eyebrow'] = !array_key_exists('slideEyebrow', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['slideEyebrow']) ? '1' : '0';
            $result['show_text'] = !array_key_exists('slideText', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['slideText']) ? '1' : '0';
            $result['show_meta'] = !array_key_exists('slideMeta', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['slideMeta']) ? '1' : '0';
            $result['show_primary_cta'] = !array_key_exists('slidePrimaryAction', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['slidePrimaryAction']) ? '1' : '0';
            $result['show_secondary_cta'] = !array_key_exists('slideSecondaryAction', (array) ($contract['runtime']['visibility'] ?? [])) || !empty($contract['runtime']['visibility']['slideSecondaryAction']) ? '1' : '0';
            $result['swipe'] = !array_key_exists('swipe', (array) ($contract['runtime']['slider'] ?? [])) || !empty($contract['runtime']['slider']['swipe']) ? '1' : '0';
            $result['autoplay'] = !empty($contract['runtime']['slider']['autoplay']) ? '1' : '0';
            $result['loop'] = !empty($contract['runtime']['slider']['loop']) ? '1' : '0';
            $result['autoplay_delay'] = (string) ($contract['runtime']['slider']['autoplayDelay'] ?? 4500);
            $result['transition_ms'] = (string) ($contract['runtime']['slider']['transitionMs'] ?? 450);
            $result['slide_surface_background_mode'] = (string) ($contract['design']['entities']['slideSurface']['backgroundMode'] ?? 'solid');
            $result['slide_surface_background_color'] = (string) ($contract['design']['entities']['slideSurface']['backgroundColor'] ?? '#ffffff');
            $result['slide_surface_padding'] = (string) ($contract['design']['entities']['slideSurface']['padding'] ?? 0);
            $result['slide_surface_radius'] = (string) ($contract['design']['entities']['slideSurface']['radius'] ?? 28);
            $result['slide_surface_border_width'] = (string) ($contract['design']['entities']['slideSurface']['borderWidth'] ?? 1);
            $result['slide_surface_border_color'] = (string) ($contract['design']['entities']['slideSurface']['borderColor'] ?? '#dbe4ef');
            $result['slide_surface_shadow'] = (string) ($contract['design']['entities']['slideSurface']['shadow'] ?? 'sm');
            $result['navigation_size'] = (string) ($contract['design']['entities']['navigation']['size'] ?? 46);
            $result['navigation_radius'] = (string) ($contract['design']['entities']['navigation']['radius'] ?? 999);
            $result['navigation_background_color'] = (string) ($contract['design']['entities']['navigation']['backgroundColor'] ?? '#0f172a');
            $result['navigation_text_color'] = (string) ($contract['design']['entities']['navigation']['textColor'] ?? '#ffffff');
            $result['navigation_border_color'] = (string) ($contract['design']['entities']['navigation']['borderColor'] ?? '#0f172a');
            $result['navigation_shadow'] = (string) ($contract['design']['entities']['navigation']['shadow'] ?? 'md');
            $result['pagination_dot_size'] = (string) ($contract['design']['entities']['pagination']['dotSize'] ?? 10);
            $result['pagination_gap'] = (string) ($contract['design']['entities']['pagination']['gap'] ?? 8);
            $result['pagination_color'] = (string) ($contract['design']['entities']['pagination']['color'] ?? '#cbd5e1');
            $result['pagination_active_color'] = (string) ($contract['design']['entities']['pagination']['activeColor'] ?? '#0f172a');
            $result['progress_height'] = (string) ($contract['design']['entities']['progress']['height'] ?? 4);
            $result['progress_radius'] = (string) ($contract['design']['entities']['progress']['radius'] ?? 999);
            $result['progress_track_color'] = (string) ($contract['design']['entities']['progress']['trackColor'] ?? '#e2e8f0');
            $result['progress_fill_color'] = (string) ($contract['design']['entities']['progress']['fillColor'] ?? '#0f172a');
        }

        return $result;
    }

    private static function buildManagedDesignEntities($type, array $props) {
        $entities = [];

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'eyebrow')) {
            $entities['eyebrow'] = [
                'desktop' => [
                    'fontSize' => self::normalizeNumber($props['eyebrow_size_desktop'] ?? 14, 10, 120, 14),
                    'marginBottom' => self::normalizeNumber($props['eyebrow_margin_bottom_desktop'] ?? 12, 0, 160, 12),
                    'weight' => self::normalizeSelect((string) ($props['eyebrow_weight_desktop'] ?? $props['eyebrow_weight'] ?? '700'), ['400', '500', '600', '700', '800', '900'], '700'),
                    'color' => self::normalizeFlatString($props['eyebrow_color_desktop'] ?? $props['eyebrow_color'] ?? ''),
                    'lineHeightPercent' => self::normalizeNumber($props['eyebrow_line_height_percent_desktop'] ?? 140, 80, 240, 140),
                    'letterSpacing' => self::normalizeNumber($props['eyebrow_letter_spacing_desktop'] ?? 1, -10, 40, 1),
                ],
                'mobile' => [
                    'fontSize' => self::normalizeNumber($props['eyebrow_size_mobile'] ?? 13, 10, 120, 13),
                    'marginBottom' => self::normalizeNumber($props['eyebrow_margin_bottom_mobile'] ?? 10, 0, 160, 10),
                    'weight' => self::normalizeSelect((string) ($props['eyebrow_weight_mobile'] ?? $props['eyebrow_weight'] ?? '700'), ['400', '500', '600', '700', '800', '900'], '700'),
                    'color' => self::normalizeFlatString($props['eyebrow_color_mobile'] ?? $props['eyebrow_color'] ?? ''),
                    'lineHeightPercent' => self::normalizeNumber($props['eyebrow_line_height_percent_mobile'] ?? 140, 80, 240, 140),
                    'letterSpacing' => self::normalizeNumber($props['eyebrow_letter_spacing_mobile'] ?? 1, -10, 40, 1),
                ],
                'textTransform' => (string) ($props['eyebrow_text_transform'] ?? 'uppercase'),
            ];
        }

        $entities['title'] = [
            'visible' => self::normalizeBoolean($props['title_visible'] ?? '1', true),
            'desktop' => [
                'fontSize' => self::normalizeNumber($props['title_size_desktop'] ?? 42, 18, 180, 42),
                'marginBottom' => self::normalizeNumber($props['title_margin_bottom_desktop'] ?? 12, 0, 200, 12),
                'weight' => self::normalizeSelect((string) ($props['title_weight_desktop'] ?? $props['heading_weight'] ?? '800'), ['400', '500', '600', '700', '800', '900'], '800'),
                'color' => self::normalizeFlatString($props['title_color_desktop'] ?? $props['title_color'] ?? ''),
                'lineHeightPercent' => self::normalizeNumber($props['title_line_height_percent_desktop'] ?? 110, 70, 240, 110),
                'letterSpacing' => self::normalizeNumber($props['title_letter_spacing_desktop'] ?? 0, -20, 40, 0),
                'maxWidth' => self::normalizeNumber($props['title_max_width_desktop'] ?? 760, 0, 2000, 760),
            ],
            'mobile' => [
                'fontSize' => self::normalizeNumber($props['title_size_mobile'] ?? 30, 16, 140, 30),
                'marginBottom' => self::normalizeNumber($props['title_margin_bottom_mobile'] ?? 10, 0, 200, 10),
                'weight' => self::normalizeSelect((string) ($props['title_weight_mobile'] ?? $props['heading_weight'] ?? '800'), ['400', '500', '600', '700', '800', '900'], '800'),
                'color' => self::normalizeFlatString($props['title_color_mobile'] ?? $props['title_color'] ?? ''),
                'lineHeightPercent' => self::normalizeNumber($props['title_line_height_percent_mobile'] ?? 110, 70, 240, 110),
                'letterSpacing' => self::normalizeNumber($props['title_letter_spacing_mobile'] ?? 0, -20, 40, 0),
                'maxWidth' => self::normalizeNumber($props['title_max_width_mobile'] ?? 760, 0, 2000, 760),
            ],
            'tag' => (string) ($props['heading_tag'] ?? 'h2'),
        ];

        $entities['subtitle'] = [
            'visible' => self::normalizeBoolean($props['subtitle_visible'] ?? '1', true),
            'desktop' => [
                'fontSize' => self::normalizeNumber($props['subtitle_size_desktop'] ?? 18, 12, 80, 18),
                'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_desktop'] ?? 18, 0, 200, 18),
                'weight' => self::normalizeSelect((string) ($props['subtitle_weight_desktop'] ?? $props['subtitle_weight'] ?? '400'), ['400', '500', '600', '700', '800', '900'], '400'),
                'color' => self::normalizeFlatString($props['subtitle_color_desktop'] ?? $props['subtitle_color'] ?? ''),
                'lineHeightPercent' => self::normalizeNumber($props['subtitle_line_height_percent_desktop'] ?? 160, 70, 260, 160),
                'letterSpacing' => self::normalizeNumber($props['subtitle_letter_spacing_desktop'] ?? 0, -20, 40, 0),
                'maxWidth' => self::normalizeNumber($props['subtitle_max_width_desktop'] ?? 760, 0, 2000, 760),
            ],
            'mobile' => [
                'fontSize' => self::normalizeNumber($props['subtitle_size_mobile'] ?? 16, 12, 64, 16),
                'marginBottom' => self::normalizeNumber($props['subtitle_margin_bottom_mobile'] ?? 14, 0, 200, 14),
                'weight' => self::normalizeSelect((string) ($props['subtitle_weight_mobile'] ?? $props['subtitle_weight'] ?? '400'), ['400', '500', '600', '700', '800', '900'], '400'),
                'color' => self::normalizeFlatString($props['subtitle_color_mobile'] ?? $props['subtitle_color'] ?? ''),
                'lineHeightPercent' => self::normalizeNumber($props['subtitle_line_height_percent_mobile'] ?? 160, 70, 260, 160),
                'letterSpacing' => self::normalizeNumber($props['subtitle_letter_spacing_mobile'] ?? 0, -20, 40, 0),
                'maxWidth' => self::normalizeNumber($props['subtitle_max_width_mobile'] ?? 760, 0, 2000, 760),
            ],
        ];

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'body')) {
            $entities['body'] = [
                'desktop' => [
                    'fontSize' => self::normalizeNumber($props['body_size_desktop'] ?? 16, 12, 56, 16),
                    'weight' => self::normalizeSelect((string) ($props['body_weight_desktop'] ?? $props['body_weight'] ?? '400'), ['400', '500', '600', '700', '800', '900'], '400'),
                    'color' => self::normalizeFlatString($props['body_color_desktop'] ?? $props['body_color'] ?? ''),
                    'lineHeightPercent' => self::normalizeNumber($props['body_line_height_percent_desktop'] ?? 165, 70, 260, 165),
                    'letterSpacing' => self::normalizeNumber($props['body_letter_spacing_desktop'] ?? 0, -20, 40, 0),
                ],
                'mobile' => [
                    'fontSize' => self::normalizeNumber($props['body_size_mobile'] ?? 15, 12, 48, 15),
                    'weight' => self::normalizeSelect((string) ($props['body_weight_mobile'] ?? $props['body_weight'] ?? '400'), ['400', '500', '600', '700', '800', '900'], '400'),
                    'color' => self::normalizeFlatString($props['body_color_mobile'] ?? $props['body_color'] ?? ''),
                    'lineHeightPercent' => self::normalizeNumber($props['body_line_height_percent_mobile'] ?? 165, 70, 260, 165),
                    'letterSpacing' => self::normalizeNumber($props['body_letter_spacing_mobile'] ?? 0, -20, 40, 0),
                ],
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'meta')) {
            $entities['meta'] = [
                'desktop' => [
                    'fontSize' => self::normalizeNumber($props['meta_size_desktop'] ?? 13, 10, 120, 13),
                    'weight' => self::normalizeSelect((string) ($props['meta_weight_desktop'] ?? $props['meta_weight'] ?? '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                    'color' => self::normalizeFlatString($props['meta_color_desktop'] ?? $props['meta_color'] ?? ''),
                    'lineHeightPercent' => self::normalizeNumber($props['meta_line_height_percent_desktop'] ?? 140, 70, 240, 140),
                    'letterSpacing' => self::normalizeNumber($props['meta_letter_spacing_desktop'] ?? 0, -20, 40, 0),
                ],
                'mobile' => [
                    'fontSize' => self::normalizeNumber($props['meta_size_mobile'] ?? 12, 10, 120, 12),
                    'weight' => self::normalizeSelect((string) ($props['meta_weight_mobile'] ?? $props['meta_weight'] ?? '600'), ['400', '500', '600', '700', '800', '900'], '600'),
                    'color' => self::normalizeFlatString($props['meta_color_mobile'] ?? $props['meta_color'] ?? ''),
                    'lineHeightPercent' => self::normalizeNumber($props['meta_line_height_percent_mobile'] ?? 140, 70, 240, 140),
                    'letterSpacing' => self::normalizeNumber($props['meta_letter_spacing_mobile'] ?? 0, -20, 40, 0),
                ],
            ];
        }

        foreach (['primaryButton', 'secondaryButton', 'tertiaryButton'] as $button_key) {
            if (NordicblocksManagedScaffoldRegistry::hasEntity($type, $button_key)) {
                $entities[$button_key] = ['style' => $button_key === 'primaryButton' ? 'primary' : 'outline'];
            }
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'media')) {
            $entities['media'] = [
                'aspectRatio' => (string) ($props['media_aspect_ratio'] ?? '16:10'),
                'objectFit' => (string) ($props['media_object_fit'] ?? 'cover'),
                'radius' => self::normalizeNumber($props['media_radius'] ?? 24, 0, 80, 24),
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'mediaSurface')) {
            $entities['mediaSurface'] = [
                'backgroundMode' => 'transparent',
                'backgroundColor' => '',
                'padding' => 0,
                'radius' => self::normalizeNumber($props['media_radius'] ?? 24, 0, 80, 24),
                'borderWidth' => 0,
                'borderColor' => '',
                'shadow' => 'none',
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'accentSurface')) {
            $accent_background_color = self::normalizeFlatString($props['accent_surface_background_color'] ?? '');
            $entities['accentSurface'] = [
                'backgroundMode' => self::normalizeSelect((string) ($props['accent_surface_background_mode'] ?? ($accent_background_color !== '' ? 'solid' : 'transparent')), ['transparent', 'solid'], 'solid'),
                'backgroundColor' => $accent_background_color,
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'bodySurface')) {
            $body_background_color = self::normalizeFlatString($props['body_surface_background_color'] ?? '');
            $entities['bodySurface'] = [
                'backgroundMode' => self::normalizeSelect((string) ($props['body_surface_background_mode'] ?? ($body_background_color !== '' ? 'solid' : 'transparent')), ['transparent', 'solid'], 'solid'),
                'backgroundColor' => $body_background_color,
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'itemSurface')) {
            $entities['itemSurface'] = [
                'variant' => self::normalizeSelect((string) ($props['item_surface_variant'] ?? 'card'), ['card', 'plain'], 'card'),
                'radius' => self::normalizeNumber($props['item_surface_radius'] ?? 24, 0, 80, 24),
                'borderWidth' => self::normalizeNumber($props['item_surface_border_width'] ?? 1, 0, 20, 1),
                'borderColor' => self::normalizeFlatString($props['item_surface_border_color'] ?? '#e2e8f0'),
                'shadow' => self::normalizeSelect((string) ($props['item_surface_shadow'] ?? 'md'), ['none', 'sm', 'md', 'lg'], 'md'),
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'itemTitle')) {
            $entities['itemTitle'] = [
                'desktop' => [
                    'fontSize' => self::normalizeNumber($props['item_title_size_desktop'] ?? 22, 10, 80, 22),
                    'weight' => self::normalizeSelect((string) ($props['item_title_weight_desktop'] ?? $props['item_title_weight'] ?? '700'), ['400', '500', '600', '700', '800', '900'], '700'),
                    'color' => self::normalizeFlatString($props['item_title_color_desktop'] ?? $props['item_title_color'] ?? ''),
                    'lineHeightPercent' => self::normalizeNumber($props['item_title_line_height_percent_desktop'] ?? $props['item_title_line_height_percent'] ?? 125, 70, 240, 125),
                    'letterSpacing' => self::normalizeNumber($props['item_title_letter_spacing_desktop'] ?? $props['item_title_letter_spacing'] ?? 0, -20, 40, 0),
                ],
                'mobile' => [
                    'fontSize' => self::normalizeNumber($props['item_title_size_mobile'] ?? 18, 10, 80, 18),
                    'weight' => self::normalizeSelect((string) ($props['item_title_weight_mobile'] ?? $props['item_title_weight'] ?? ($props['item_title_weight_desktop'] ?? '700')), ['400', '500', '600', '700', '800', '900'], '700'),
                    'color' => self::normalizeFlatString($props['item_title_color_mobile'] ?? $props['item_title_color'] ?? ($props['item_title_color_desktop'] ?? '')),
                    'lineHeightPercent' => self::normalizeNumber($props['item_title_line_height_percent_mobile'] ?? $props['item_title_line_height_percent'] ?? ($props['item_title_line_height_percent_desktop'] ?? 125), 70, 240, 125),
                    'letterSpacing' => self::normalizeNumber($props['item_title_letter_spacing_mobile'] ?? $props['item_title_letter_spacing'] ?? ($props['item_title_letter_spacing_desktop'] ?? 0), -20, 40, 0),
                ],
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'itemText')) {
            $entities['itemText'] = [
                'desktop' => [
                    'fontSize' => self::normalizeNumber($props['item_text_size_desktop'] ?? 15, 10, 80, 15),
                    'weight' => self::normalizeSelect((string) ($props['item_text_weight_desktop'] ?? $props['item_text_weight'] ?? '400'), ['400', '500', '600', '700', '800', '900'], '400'),
                    'color' => self::normalizeFlatString($props['item_text_color_desktop'] ?? $props['item_text_color'] ?? ''),
                    'lineHeightPercent' => self::normalizeNumber($props['item_text_line_height_percent_desktop'] ?? $props['item_text_line_height_percent'] ?? 160, 70, 260, 160),
                    'letterSpacing' => self::normalizeNumber($props['item_text_letter_spacing_desktop'] ?? $props['item_text_letter_spacing'] ?? 0, -20, 40, 0),
                ],
                'mobile' => [
                    'fontSize' => self::normalizeNumber($props['item_text_size_mobile'] ?? 14, 10, 80, 14),
                    'weight' => self::normalizeSelect((string) ($props['item_text_weight_mobile'] ?? $props['item_text_weight'] ?? ($props['item_text_weight_desktop'] ?? '400')), ['400', '500', '600', '700', '800', '900'], '400'),
                    'color' => self::normalizeFlatString($props['item_text_color_mobile'] ?? $props['item_text_color'] ?? ($props['item_text_color_desktop'] ?? '')),
                    'lineHeightPercent' => self::normalizeNumber($props['item_text_line_height_percent_mobile'] ?? $props['item_text_line_height_percent'] ?? ($props['item_text_line_height_percent_desktop'] ?? 160), 70, 260, 160),
                    'letterSpacing' => self::normalizeNumber($props['item_text_letter_spacing_mobile'] ?? $props['item_text_letter_spacing'] ?? ($props['item_text_letter_spacing_desktop'] ?? 0), -20, 40, 0),
                ],
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'itemLink')) {
            $entities['itemLink'] = [
                'desktop' => [
                    'fontSize' => self::normalizeNumber($props['item_link_size_desktop'] ?? 13, 10, 80, 13),
                    'weight' => self::normalizeSelect((string) ($props['item_link_weight_desktop'] ?? $props['item_link_weight'] ?? '700'), ['400', '500', '600', '700', '800', '900'], '700'),
                    'color' => self::normalizeFlatString($props['item_link_color_desktop'] ?? $props['item_link_color'] ?? ''),
                    'lineHeightPercent' => self::normalizeNumber($props['item_link_line_height_percent_desktop'] ?? $props['item_link_line_height_percent'] ?? 120, 70, 220, 120),
                    'letterSpacing' => self::normalizeNumber($props['item_link_letter_spacing_desktop'] ?? $props['item_link_letter_spacing'] ?? 1, -20, 40, 1),
                ],
                'mobile' => [
                    'fontSize' => self::normalizeNumber($props['item_link_size_mobile'] ?? 12, 10, 80, 12),
                    'weight' => self::normalizeSelect((string) ($props['item_link_weight_mobile'] ?? $props['item_link_weight'] ?? ($props['item_link_weight_desktop'] ?? '700')), ['400', '500', '600', '700', '800', '900'], '700'),
                    'color' => self::normalizeFlatString($props['item_link_color_mobile'] ?? $props['item_link_color'] ?? ($props['item_link_color_desktop'] ?? '')),
                    'lineHeightPercent' => self::normalizeNumber($props['item_link_line_height_percent_mobile'] ?? $props['item_link_line_height_percent'] ?? ($props['item_link_line_height_percent_desktop'] ?? 120), 70, 220, 120),
                    'letterSpacing' => self::normalizeNumber($props['item_link_letter_spacing_mobile'] ?? $props['item_link_letter_spacing'] ?? ($props['item_link_letter_spacing_desktop'] ?? 1), -20, 40, 1),
                ],
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'slideSurface')) {
            $entities['slideSurface'] = [
                'backgroundMode' => self::normalizeSelect((string) ($props['slide_surface_background_mode'] ?? 'solid'), ['transparent', 'solid'], 'solid'),
                'backgroundColor' => self::normalizeFlatString($props['slide_surface_background_color'] ?? '#ffffff'),
                'padding' => self::normalizeNumber($props['slide_surface_padding'] ?? 0, 0, 80, 0),
                'radius' => self::normalizeNumber($props['slide_surface_radius'] ?? 28, 0, 80, 28),
                'borderWidth' => self::normalizeNumber($props['slide_surface_border_width'] ?? 1, 0, 20, 1),
                'borderColor' => self::normalizeFlatString($props['slide_surface_border_color'] ?? '#dbe4ef'),
                'shadow' => self::normalizeSelect((string) ($props['slide_surface_shadow'] ?? 'sm'), ['none', 'sm', 'md', 'lg'], 'sm'),
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'slideMedia')) {
            $entities['slideMedia'] = [
                'aspectRatio' => (string) ($props['media_aspect_ratio'] ?? '4:3'),
                'objectFit' => (string) ($props['media_object_fit'] ?? 'cover'),
                'radius' => self::normalizeNumber($props['media_radius'] ?? 24, 0, 80, 24),
            ];
        }

        foreach (['slideEyebrow', 'slideTitle', 'slideText', 'slideMeta', 'slidePrimaryAction', 'slideSecondaryAction'] as $entity_key) {
            if (!NordicblocksManagedScaffoldRegistry::hasEntity($type, $entity_key)) {
                continue;
            }

            $defaults_map = [
                'slideEyebrow' => [13, 12, '700', '#0f766e', 140, 1],
                'slideTitle' => [24, 19, '800', '', 120, 0],
                'slideText' => [16, 14, '400', '', 160, 0],
                'slideMeta' => [12, 11, '600', '', 135, 1],
                'slidePrimaryAction' => [14, 13, '700', '', 120, 0],
                'slideSecondaryAction' => [14, 13, '600', '', 120, 0],
            ];
            list($desktop_size, $mobile_size, $weight, $color, $line_height, $letter_spacing) = $defaults_map[$entity_key];
            $prefix = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $entity_key));

            $entities[$entity_key] = [
                'desktop' => [
                    'fontSize' => self::normalizeNumber($props[$prefix . '_size_desktop'] ?? $desktop_size, 10, 120, $desktop_size),
                    'weight' => self::normalizeSelect((string) ($props[$prefix . '_weight_desktop'] ?? $props[$prefix . '_weight'] ?? $weight), ['400', '500', '600', '700', '800', '900'], $weight),
                    'color' => self::normalizeFlatString($props[$prefix . '_color_desktop'] ?? $props[$prefix . '_color'] ?? $color),
                    'lineHeightPercent' => self::normalizeNumber($props[$prefix . '_line_height_percent_desktop'] ?? $line_height, 70, 260, $line_height),
                    'letterSpacing' => self::normalizeNumber($props[$prefix . '_letter_spacing_desktop'] ?? $letter_spacing, -20, 40, $letter_spacing),
                ],
                'mobile' => [
                    'fontSize' => self::normalizeNumber($props[$prefix . '_size_mobile'] ?? $mobile_size, 10, 120, $mobile_size),
                    'weight' => self::normalizeSelect((string) ($props[$prefix . '_weight_mobile'] ?? $props[$prefix . '_weight'] ?? $weight), ['400', '500', '600', '700', '800', '900'], $weight),
                    'color' => self::normalizeFlatString($props[$prefix . '_color_mobile'] ?? $props[$prefix . '_color'] ?? $color),
                    'lineHeightPercent' => self::normalizeNumber($props[$prefix . '_line_height_percent_mobile'] ?? $line_height, 70, 260, $line_height),
                    'letterSpacing' => self::normalizeNumber($props[$prefix . '_letter_spacing_mobile'] ?? $letter_spacing, -20, 40, $letter_spacing),
                ],
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'navigation')) {
            $entities['navigation'] = [
                'size' => self::normalizeNumber($props['navigation_size'] ?? 46, 24, 96, 46),
                'radius' => self::normalizeNumber($props['navigation_radius'] ?? 999, 0, 999, 999),
                'backgroundColor' => self::normalizeFlatString($props['navigation_background_color'] ?? '#0f172a'),
                'textColor' => self::normalizeFlatString($props['navigation_text_color'] ?? '#ffffff'),
                'borderColor' => self::normalizeFlatString($props['navigation_border_color'] ?? '#0f172a'),
                'shadow' => self::normalizeSelect((string) ($props['navigation_shadow'] ?? 'md'), ['none', 'sm', 'md', 'lg'], 'md'),
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'pagination')) {
            $entities['pagination'] = [
                'dotSize' => self::normalizeNumber($props['pagination_dot_size'] ?? 10, 4, 32, 10),
                'gap' => self::normalizeNumber($props['pagination_gap'] ?? 8, 0, 40, 8),
                'color' => self::normalizeFlatString($props['pagination_color'] ?? '#cbd5e1'),
                'activeColor' => self::normalizeFlatString($props['pagination_active_color'] ?? '#0f172a'),
            ];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'progress')) {
            $entities['progress'] = [
                'height' => self::normalizeNumber($props['progress_height'] ?? 4, 2, 24, 4),
                'radius' => self::normalizeNumber($props['progress_radius'] ?? 999, 0, 999, 999),
                'trackColor' => self::normalizeFlatString($props['progress_track_color'] ?? '#e2e8f0'),
                'fillColor' => self::normalizeFlatString($props['progress_fill_color'] ?? '#0f172a'),
            ];
        }

        return $entities;
    }

    private static function buildManagedLayout($type, array $props, array $defaults) {
        $layout = [
            'desktop' => [
                'contentWidth' => self::normalizeNumber($props['content_width'] ?? ($defaults['content_width'] ?? 960), 240, 1600, 960),
                'paddingTop' => self::normalizeNumber($props['padding_top_desktop'] ?? ($defaults['padding_top_desktop'] ?? 72), 0, 300, 72),
                'paddingBottom' => self::normalizeNumber($props['padding_bottom_desktop'] ?? ($defaults['padding_bottom_desktop'] ?? 72), 0, 300, 72),
                'align' => self::normalizeSelect((string) ($props['align'] ?? 'left'), ['left', 'center', 'right'], 'left'),
            ],
            'mobile' => [
                'paddingTop' => self::normalizeNumber($props['padding_top_mobile'] ?? ($defaults['padding_top_mobile'] ?? 48), 0, 300, 48),
                'paddingBottom' => self::normalizeNumber($props['padding_bottom_mobile'] ?? ($defaults['padding_bottom_mobile'] ?? 48), 0, 300, 48),
                'align' => self::normalizeSelect((string) ($props['align'] ?? 'left'), ['left', 'center', 'right'], 'left'),
            ],
        ];

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'items')) {
            $layout['preset'] = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($props['layout_preset'] ?? ($defaults['layout_preset'] ?? 'default'))));
            $layout['desktop']['columns'] = self::normalizeNumber($props['columns_desktop'] ?? ($defaults['columns_desktop'] ?? 3), 1, 6, 3);
            $layout['mobile']['columns'] = self::normalizeNumber($props['columns_mobile'] ?? ($defaults['columns_mobile'] ?? 1), 1, 3, 1);
            $layout['desktop']['cardGap'] = self::normalizeNumber($props['card_gap_desktop'] ?? ($defaults['card_gap_desktop'] ?? 24), 0, 160, 24);
            $layout['mobile']['cardGap'] = self::normalizeNumber($props['card_gap_mobile'] ?? ($defaults['card_gap_mobile'] ?? 16), 0, 160, 16);
            $layout['desktop']['headerGap'] = self::normalizeNumber($props['header_gap_desktop'] ?? ($defaults['header_gap_desktop'] ?? 24), 0, 160, 24);
            $layout['mobile']['headerGap'] = self::normalizeNumber($props['header_gap_mobile'] ?? ($defaults['header_gap_mobile'] ?? 18), 0, 160, 18);
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'slide')) {
            $layout['desktop']['slidesPerView'] = self::normalizeNumber($props['slides_per_view_desktop'] ?? ($defaults['slides_per_view_desktop'] ?? 3), 1, 6, 3);
            $layout['mobile']['slidesPerView'] = self::normalizeNumber($props['slides_per_view_mobile'] ?? ($defaults['slides_per_view_mobile'] ?? 1), 1, 3, 1);
            $layout['desktop']['slideGap'] = self::normalizeNumber($props['slide_gap_desktop'] ?? ($defaults['slide_gap_desktop'] ?? 24), 0, 160, 24);
            $layout['mobile']['slideGap'] = self::normalizeNumber($props['slide_gap_mobile'] ?? ($defaults['slide_gap_mobile'] ?? 16), 0, 160, 16);
            $layout['desktop']['headerGap'] = self::normalizeNumber($props['header_gap_desktop'] ?? ($defaults['header_gap_desktop'] ?? 28), 0, 160, 28);
            $layout['mobile']['headerGap'] = self::normalizeNumber($props['header_gap_mobile'] ?? ($defaults['header_gap_mobile'] ?? 18), 0, 160, 18);
            $layout['desktop']['minHeight'] = self::normalizeNumber($props['min_height_desktop'] ?? ($defaults['min_height_desktop'] ?? 0), 0, 1200, 0);
            $layout['mobile']['minHeight'] = self::normalizeNumber($props['min_height_mobile'] ?? ($defaults['min_height_mobile'] ?? 0), 0, 1200, 0);
            $layout['desktop']['navigationPosition'] = self::normalizeSelect((string) ($props['navigation_position_desktop'] ?? 'overlay'), ['overlay', 'below', 'hidden'], 'overlay');
            $layout['mobile']['navigationPosition'] = self::normalizeSelect((string) ($props['navigation_position_mobile'] ?? ($props['navigation_position_desktop'] ?? 'hidden')), ['overlay', 'below', 'hidden'], 'hidden');
            $layout['desktop']['paginationPosition'] = self::normalizeSelect((string) ($props['pagination_position_desktop'] ?? 'below'), ['overlay', 'below', 'hidden'], 'below');
            $layout['mobile']['paginationPosition'] = self::normalizeSelect((string) ($props['pagination_position_mobile'] ?? 'below'), ['overlay', 'below', 'hidden'], 'below');
            $layout['desktop']['progressPosition'] = self::normalizeSelect((string) ($props['progress_position_desktop'] ?? 'below'), ['overlay', 'below', 'hidden'], 'below');
            $layout['mobile']['progressPosition'] = self::normalizeSelect((string) ($props['progress_position_mobile'] ?? 'below'), ['overlay', 'below', 'hidden'], 'below');
        }

        return $layout;
    }

    private static function buildManagedEntityMeta(array $entity_keys) {
        $meta = [];

        foreach ($entity_keys as $entity_key) {
            $meta[$entity_key] = [
                'kind' => self::managedEntityKind($entity_key),
                'styleSlot' => $entity_key,
            ];
        }

        return $meta;
    }

    private static function managedEntityKind($entity_key) {
        $map = [
            'eyebrow' => 'text',
            'title' => 'text',
            'subtitle' => 'text',
            'meta' => 'text',
            'body' => 'text',
            'primaryButton' => 'button',
            'secondaryButton' => 'button',
            'tertiaryButton' => 'button',
            'media' => 'media',
            'mediaSurface' => 'surface',
            'accentSurface' => 'surface',
            'bodySurface' => 'surface',
            'items' => 'repeater',
            'slide' => 'repeater',
            'itemSurface' => 'surface',
            'itemTitle' => 'text',
            'itemText' => 'text',
            'itemLink' => 'text',
            'slideSurface' => 'surface',
            'slideMedia' => 'media',
            'slideEyebrow' => 'text',
            'slideTitle' => 'text',
            'slideText' => 'text',
            'slideMeta' => 'text',
            'slidePrimaryAction' => 'button',
            'slideSecondaryAction' => 'button',
            'navigation' => 'surface',
            'prevButton' => 'button',
            'nextButton' => 'button',
            'pagination' => 'surface',
            'progress' => 'surface',
        ];

        return (string) ($map[$entity_key] ?? 'text');
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

        $supports_list = in_array($type, ['faq', 'content_feed', 'category_cards', 'headline_feed', 'swiss_grid', 'catalog_browser'], true)
            || NordicblocksManagedScaffoldRegistry::supportsContentList($type);

        if (!$supports_list) {
            $normalized['listSource'] = self::normalizeListSource([]);
        } else {
            $list_profile_type = self::isCardCollectionType($type)
                ? 'content_feed'
                : (NordicblocksManagedScaffoldRegistry::usesSliderCollectionMapping($type)
                    ? 'cards_slider'
                    : (NordicblocksManagedScaffoldRegistry::usesFaqMapping($type) ? 'faq' : $type));
            $normalized['listSource'] = self::normalizeListSource((array) ($data['listSource'] ?? []), $list_profile_type);
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
        $defaults = self::getSingleItemBindingDefaults($type);
        if (!$defaults) {
            return $bindings;
        }
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
            'body' => ['mode' => 'mixed', 'formatter' => 'plain_text', 'emptyBehavior' => 'fallback'],
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

    private static function getSingleItemBindingDefaults($type) {
        if ($type === 'hero') {
            return self::getHeroBindingDefaults();
        }

        if (!NordicblocksManagedScaffoldRegistry::supportsContentItem($type)) {
            return [];
        }

        $defaults = [];
        foreach (['eyebrow', 'title', 'subtitle', 'body'] as $key) {
            if (NordicblocksManagedScaffoldRegistry::hasEntity($type, $key)) {
                $defaults[$key] = [
                    'mode' => $key === 'title' ? 'bound' : 'mixed',
                    'formatter' => 'plain_text',
                    'emptyBehavior' => 'fallback',
                ];
            }
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'media')) {
            $defaults['image'] = ['mode' => 'mixed', 'formatter' => 'image_url', 'emptyBehavior' => 'fallback'];
            $defaults['imageAlt'] = ['mode' => 'mixed', 'formatter' => 'plain_text', 'emptyBehavior' => 'fallback'];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'meta')) {
            $defaults['category'] = ['mode' => 'bound', 'formatter' => 'plain_text', 'emptyBehavior' => 'hide'];
            $defaults['author'] = ['mode' => 'bound', 'formatter' => 'plain_text', 'emptyBehavior' => 'hide'];
            $defaults['date'] = ['mode' => 'bound', 'formatter' => 'date_human', 'emptyBehavior' => 'hide'];
            $defaults['views'] = ['mode' => 'bound', 'formatter' => 'number', 'emptyBehavior' => 'hide'];
            $defaults['comments'] = ['mode' => 'bound', 'formatter' => 'number', 'emptyBehavior' => 'hide'];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'primaryButton')) {
            $defaults['primaryButtonUrl'] = ['mode' => 'mixed', 'formatter' => 'record_url', 'emptyBehavior' => 'fallback'];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'secondaryButton')) {
            $defaults['secondaryButtonUrl'] = ['mode' => 'mixed', 'formatter' => 'record_url', 'emptyBehavior' => 'fallback'];
        }

        if (NordicblocksManagedScaffoldRegistry::hasEntity($type, 'tertiaryButton')) {
            $defaults['tertiaryButtonUrl'] = ['mode' => 'mixed', 'formatter' => 'record_url', 'emptyBehavior' => 'fallback'];
        }

        return $defaults;
    }

    private static function normalizeListSource(array $config, $type = 'faq') {
        $map = is_array($config['map'] ?? null) ? $config['map'] : [];

        if ($type === 'cards_slider') {
            return [
                'type' => self::normalizeSelect((string) ($config['type'] ?? 'manual'), ['manual', 'content_list'], 'manual'),
                'ctype' => preg_replace('/[^a-z0-9_\-\{\}]/i', '', (string) ($config['ctype'] ?? '')),
                'limit' => self::normalizeNumber($config['limit'] ?? 6, 1, 24, 6),
                'sort' => self::normalizeSelect((string) ($config['sort'] ?? 'date_pub_desc'), self::$allowed_list_sorts, 'date_pub_desc'),
                'map' => [
                    'eyebrow' => self::normalizeFieldReference($map['eyebrow'] ?? 'category.title'),
                    'title' => self::normalizeFieldReference($map['title'] ?? 'title'),
                    'text' => self::normalizeFieldReference($map['text'] ?? 'teaser'),
                    'image' => self::normalizeFieldReference($map['image'] ?? 'record_image_url'),
                    'imageAlt' => self::normalizeFieldReference($map['imageAlt'] ?? 'title'),
                    'metaLabel' => self::normalizeFieldReference($map['metaLabel'] ?? 'category.title'),
                    'date' => self::normalizeFieldReference($map['date'] ?? 'date_pub'),
                    'primaryCtaLabel' => self::normalizeFieldReference($map['primaryCtaLabel'] ?? ''),
                    'primaryCtaUrl' => self::normalizeFieldReference($map['primaryCtaUrl'] ?? 'record_url'),
                    'secondaryCtaLabel' => self::normalizeFieldReference($map['secondaryCtaLabel'] ?? ''),
                    'secondaryCtaUrl' => self::normalizeFieldReference($map['secondaryCtaUrl'] ?? ''),
                    'recordUrl' => self::normalizeFieldReference($map['recordUrl'] ?? 'record_url'),
                ],
                'emptyBehavior' => self::normalizeSelect((string) ($config['emptyBehavior'] ?? 'fallback'), ['fallback', 'empty'], 'fallback'),
            ];
        }

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
                    'categoryUrl' => self::normalizeFieldReference($map['categoryUrl'] ?? 'category.url'),
                    'price' => self::normalizeFieldReference($map['price'] ?? 'price'),
                    'priceOld' => self::normalizeFieldReference($map['priceOld'] ?? 'price_old'),
                    'currency' => self::normalizeFieldReference($map['currency'] ?? 'currency'),
                    'badge' => self::normalizeFieldReference($map['badge'] ?? 'badge'),
                    'tags' => self::normalizeFieldReference($map['tags'] ?? 'tags'),
                    'date' => self::normalizeFieldReference($map['date'] ?? 'date_pub'),
                    'views' => self::normalizeFieldReference($map['views'] ?? 'hits_count'),
                    'comments' => self::normalizeFieldReference($map['comments'] ?? 'comments_count'),
                    'url' => self::normalizeFieldReference($map['url'] ?? 'record_url'),
                    'ctaLabel' => self::normalizeFieldReference($map['ctaLabel'] ?? 'cta_label'),
                    'ctaUrl' => self::normalizeFieldReference($map['ctaUrl'] ?? 'cta_url'),
                    'availability' => self::normalizeFieldReference($map['availability'] ?? 'availability'),
                    'gallery' => self::normalizeFieldReference($map['gallery'] ?? 'gallery'),
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

            $item_id = trim((string) ($item['id'] ?? ($item['itemId'] ?? ($item['item_id'] ?? ''))));
            $title = trim((string) ($item['title'] ?? ''));
            $excerpt = trim((string) ($item['excerpt'] ?? ($item['text'] ?? '')));
            $category = trim((string) ($item['category'] ?? ''));
            $category_url = trim((string) ($item['categoryUrl'] ?? ($item['category_url'] ?? '')));
            $url = trim((string) ($item['url'] ?? ''));
            $link_label = trim((string) ($item['linkLabel'] ?? ($item['link_label'] ?? '')));
            $date = trim((string) ($item['date'] ?? ''));
            $views = trim((string) ($item['views'] ?? ''));
            $comments = trim((string) ($item['comments'] ?? ''));
            $price = trim((string) ($item['price'] ?? ''));
            $price_old = trim((string) ($item['priceOld'] ?? ($item['price_old'] ?? '')));
            $currency = trim((string) ($item['currency'] ?? ''));
            $badge = trim((string) ($item['badge'] ?? ''));
            $tags = $item['tags'] ?? [];
            $cta_label = trim((string) ($item['ctaLabel'] ?? ($item['cta_label'] ?? '')));
            $cta_url = trim((string) ($item['ctaUrl'] ?? ($item['cta_url'] ?? '')));
            $availability = trim((string) ($item['availability'] ?? ''));
            $gallery = $item['gallery'] ?? [];
            $image = self::normalizeImagePayload($item['image'] ?? '');
            $image_alt = trim((string) ($item['imageAlt'] ?? ($item['alt'] ?? ($image['alt'] ?? ''))));

            if ($title === '' && $excerpt === '' && $category === '' && $url === '' && empty($image['original']) && empty($image['display'])) {
                continue;
            }

            $items[] = self::buildContentFeedItemPayload([
                'id' => $item_id,
                'category' => $category,
                'categoryUrl' => $category_url,
                'title' => $title,
                'excerpt' => $excerpt,
                'linkLabel' => $link_label,
                'url' => $url,
                'image' => (string) ($image['original'] ?? $image['display'] ?? ''),
                'imageAlt' => $image_alt,
                'date' => $date,
                'views' => $views,
                'comments' => $comments,
                'price' => $price,
                'priceOld' => $price_old,
                'currency' => $currency,
                'badge' => $badge,
                'tags' => is_array($tags) ? $tags : preg_split('/\s*,\s*/', trim((string) $tags), -1, PREG_SPLIT_NO_EMPTY),
                'ctaLabel' => $cta_label,
                'ctaUrl' => $cta_url,
                'availability' => $availability,
                'gallery' => is_array($gallery) ? $gallery : [],
            ]);
        }

        return $items;
    }

    private static function normalizeSliderSlides($value) {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (!is_array($value)) {
            $value = [];
        }

        $slides = [];
        foreach ($value as $slide) {
            if (!is_array($slide)) {
                continue;
            }

            $title = trim((string) ($slide['title'] ?? ''));
            $text = trim((string) ($slide['text'] ?? ''));
            $eyebrow = trim((string) ($slide['eyebrow'] ?? ''));
            $meta_label = trim((string) ($slide['metaLabel'] ?? ($slide['meta_label'] ?? '')));
            $date = trim((string) ($slide['date'] ?? ''));
            $record_url = trim((string) ($slide['recordUrl'] ?? ($slide['record_url'] ?? ($slide['url'] ?? ''))));
            $primary_cta_label = trim((string) ($slide['primaryCtaLabel'] ?? ($slide['primary_cta_label'] ?? (($slide['primaryAction']['label'] ?? '')))));
            $primary_cta_url = trim((string) ($slide['primaryCtaUrl'] ?? ($slide['primary_cta_url'] ?? (($slide['primaryAction']['url'] ?? '')))));
            $secondary_cta_label = trim((string) ($slide['secondaryCtaLabel'] ?? ($slide['secondary_cta_label'] ?? (($slide['secondaryAction']['label'] ?? '')))));
            $secondary_cta_url = trim((string) ($slide['secondaryCtaUrl'] ?? ($slide['secondary_cta_url'] ?? (($slide['secondaryAction']['url'] ?? '')))));
            $image = self::normalizeImagePayload($slide['image'] ?? '');
            $image_alt = trim((string) ($slide['imageAlt'] ?? ($slide['image_alt'] ?? ($slide['alt'] ?? ($image['alt'] ?? '')))));

            if ($title === '' && $text === '' && $eyebrow === '' && $record_url === '' && empty($image['original']) && empty($image['display'])) {
                continue;
            }

            $slides[] = self::buildSliderSlidePayload([
                'eyebrow' => $eyebrow,
                'title' => $title,
                'text' => $text,
                'image' => (string) ($image['original'] ?? $image['display'] ?? ''),
                'imageAlt' => $image_alt,
                'date' => $date,
                'metaLabel' => $meta_label,
                'recordUrl' => $record_url,
                'primaryCtaLabel' => $primary_cta_label,
                'primaryCtaUrl' => $primary_cta_url,
                'secondaryCtaLabel' => $secondary_cta_label,
                'secondaryCtaUrl' => $secondary_cta_url,
            ]);
        }

        return $slides;
    }

    private static function buildSliderSlidePayload(array $slide) {
        $image = self::normalizeImagePayload($slide['image'] ?? '');
        $image_alt = trim((string) ($slide['imageAlt'] ?? ($slide['image_alt'] ?? ($image['alt'] ?? ''))));
        $record_url = trim((string) ($slide['recordUrl'] ?? ($slide['record_url'] ?? ($slide['url'] ?? ''))));
        $primary_cta_label = trim((string) ($slide['primaryCtaLabel'] ?? ($slide['primary_cta_label'] ?? '')));
        $primary_cta_url = trim((string) ($slide['primaryCtaUrl'] ?? ($slide['primary_cta_url'] ?? '')));
        $secondary_cta_label = trim((string) ($slide['secondaryCtaLabel'] ?? ($slide['secondary_cta_label'] ?? '')));
        $secondary_cta_url = trim((string) ($slide['secondaryCtaUrl'] ?? ($slide['secondary_cta_url'] ?? '')));

        return [
            'eyebrow' => trim((string) ($slide['eyebrow'] ?? '')),
            'title' => trim((string) ($slide['title'] ?? '')),
            'text' => trim((string) ($slide['text'] ?? '')),
            'image' => (string) ($image['original'] ?? $image['display'] ?? ''),
            'imageAlt' => $image_alt,
            'image_alt' => $image_alt,
            'date' => trim((string) ($slide['date'] ?? '')),
            'metaLabel' => trim((string) ($slide['metaLabel'] ?? ($slide['meta_label'] ?? ''))),
            'meta_label' => trim((string) ($slide['metaLabel'] ?? ($slide['meta_label'] ?? ''))),
            'recordUrl' => $record_url,
            'record_url' => $record_url,
            'url' => $record_url,
            'primaryAction' => [
                'label' => $primary_cta_label,
                'url' => $primary_cta_url,
            ],
            'secondaryAction' => [
                'label' => $secondary_cta_label,
                'url' => $secondary_cta_url,
            ],
            'primaryCtaLabel' => $primary_cta_label,
            'primary_cta_label' => $primary_cta_label,
            'primaryCtaUrl' => $primary_cta_url,
            'primary_cta_url' => $primary_cta_url,
            'secondaryCtaLabel' => $secondary_cta_label,
            'secondary_cta_label' => $secondary_cta_label,
            'secondaryCtaUrl' => $secondary_cta_url,
            'secondary_cta_url' => $secondary_cta_url,
        ];
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
        $item_id = trim((string) ($item['id'] ?? ($item['itemId'] ?? ($item['item_id'] ?? ''))));
        $title = trim((string) ($item['title'] ?? ''));
        $excerpt = trim((string) ($item['excerpt'] ?? ($item['text'] ?? '')));
        $category_url = trim((string) ($item['categoryUrl'] ?? ($item['category_url'] ?? '')));

        return [
            'id' => $item_id,
            'itemId' => $item_id,
            'item_id' => $item_id,
            'category' => trim((string) ($item['category'] ?? '')),
            'categoryUrl' => $category_url,
            'category_url' => $category_url,
            'title' => $title,
            'excerpt' => $excerpt,
            'text' => $excerpt,
            'linkLabel' => trim((string) ($item['linkLabel'] ?? ($item['link_label'] ?? ''))),
            'link_label' => trim((string) ($item['linkLabel'] ?? ($item['link_label'] ?? ''))),
            'url' => trim((string) ($item['url'] ?? '')),
            'image' => trim((string) ($item['image'] ?? '')),
            'imageAlt' => trim((string) ($item['imageAlt'] ?? ($item['alt'] ?? ''))),
            'alt' => trim((string) ($item['imageAlt'] ?? ($item['alt'] ?? ''))),
            'date' => trim((string) ($item['date'] ?? '')),
            'views' => trim((string) ($item['views'] ?? '')),
            'comments' => trim((string) ($item['comments'] ?? '')),
            'price' => trim((string) ($item['price'] ?? '')),
            'priceOld' => trim((string) ($item['priceOld'] ?? ($item['price_old'] ?? ''))),
            'price_old' => trim((string) ($item['priceOld'] ?? ($item['price_old'] ?? ''))),
            'currency' => trim((string) ($item['currency'] ?? '')),
            'badge' => trim((string) ($item['badge'] ?? '')),
            'tags' => is_array($item['tags'] ?? null) ? array_values($item['tags']) : preg_split('/\s*,\s*/', trim((string) ($item['tags'] ?? '')), -1, PREG_SPLIT_NO_EMPTY),
            'ctaLabel' => trim((string) ($item['ctaLabel'] ?? ($item['cta_label'] ?? ''))),
            'cta_label' => trim((string) ($item['ctaLabel'] ?? ($item['cta_label'] ?? ''))),
            'ctaUrl' => trim((string) ($item['ctaUrl'] ?? ($item['cta_url'] ?? ''))),
            'cta_url' => trim((string) ($item['ctaUrl'] ?? ($item['cta_url'] ?? ''))),
            'availability' => trim((string) ($item['availability'] ?? '')),
            'gallery' => is_array($item['gallery'] ?? null) ? array_values($item['gallery']) : [],
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