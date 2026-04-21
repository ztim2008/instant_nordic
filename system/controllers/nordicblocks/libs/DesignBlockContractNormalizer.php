<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockTypography.php';

class NordicblocksDesignBlockContractNormalizer {

    private static $allowed_element_types = ['text', 'photo', 'button', 'object', 'icon', 'container', 'video', 'divider', 'svg', 'image', 'shape'];
    private static $allowed_background_modes = ['theme', 'solid', 'gradient', 'image'];

    public static function supportsType($type) {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $type)) === 'design_block';
    }

    public static function normalize(array $block) {
        $payload  = is_array($block['props'] ?? null) ? $block['props'] : [];
        $contract = self::isContractPayload($payload) ? $payload : [];

        $section_name = self::string($contract['content']['section']['name'] ?? ($payload['section_name'] ?? ($block['title'] ?? 'Design Block')), 'Design Block', 160);
        $elements_raw = $contract['content']['section']['elements'] ?? ($payload['elements'] ?? []);
        $elements     = self::normalizeElements(is_array($elements_raw) ? $elements_raw : []);

        if (!$elements) {
            $elements = self::buildDefaultElements();
        }

        return [
            'meta' => [
                'contractVersion' => 1,
                'blockType'       => 'design_block',
                'schemaVersion'   => 1,
                'label'           => self::string($contract['meta']['label'] ?? ($block['title'] ?? 'Design Block'), 'Design Block', 255),
                'status'          => self::string($contract['meta']['status'] ?? ($block['status'] ?? 'active'), 'active', 32),
            ],
            'content' => [
                'section' => [
                    'name'     => $section_name,
                    'tag'      => self::select($contract['content']['section']['tag'] ?? ($payload['section_tag'] ?? 'section'), ['section', 'div'], 'section'),
                    'elements' => $elements,
                ],
            ],
            'design' => [
                'section' => [
                    'theme'      => self::string($contract['design']['section']['theme'] ?? 'custom', 'custom', 32),
                    'background' => self::normalizeBackground($contract['design']['section']['background'] ?? $payload),
                ],
            ],
            'layout' => [
                'stage' => self::normalizeStage($contract['layout']['stage'] ?? $payload),
            ],
            'data' => [
                'source' => [
                    'type' => 'manual',
                ],
            ],
            'entities' => [
                'section'     => ['kind' => 'surface', 'styleSlot' => 'section'],
                'byElementId' => self::buildElementEntityMap($elements),
            ],
            'runtime' => [
                'editor' => [
                    'mode'    => 'design_freeform',
                    'version' => 1,
                ],
                'ssr' => [
                    'version' => 1,
                ],
            ],
        ];
    }

    public static function denormalizeProps(array $contract) {
        return [
            'section_name'      => (string) ($contract['content']['section']['name'] ?? ''),
            'section_tag'       => (string) ($contract['content']['section']['tag'] ?? 'section'),
            'background_mode'   => (string) ($contract['design']['section']['background']['mode'] ?? 'solid'),
            'background_color'  => (string) ($contract['design']['section']['background']['color'] ?? ''),
            'desktop_width'     => (int) ($contract['layout']['stage']['desktop']['width'] ?? 1200),
            'desktop_min_height'=> (int) ($contract['layout']['stage']['desktop']['minHeight'] ?? 640),
            'desktop_columns'   => (int) ($contract['layout']['stage']['desktop']['grid']['columns'] ?? 12),
            'desktop_gutter'    => (int) ($contract['layout']['stage']['desktop']['grid']['gutter'] ?? 20),
            'desktop_bleed_x'   => (int) ($contract['layout']['stage']['desktop']['grid']['bleedX'] ?? 160),
            'tablet_width'      => (int) ($contract['layout']['stage']['tablet']['width'] ?? 768),
            'tablet_min_height' => (int) ($contract['layout']['stage']['tablet']['minHeight'] ?? 540),
            'tablet_columns'    => (int) ($contract['layout']['stage']['tablet']['grid']['columns'] ?? 8),
            'tablet_gutter'     => (int) ($contract['layout']['stage']['tablet']['grid']['gutter'] ?? 16),
            'tablet_bleed_x'    => (int) ($contract['layout']['stage']['tablet']['grid']['bleedX'] ?? 96),
            'mobile_width'      => (int) ($contract['layout']['stage']['mobile']['width'] ?? 390),
            'mobile_min_height' => (int) ($contract['layout']['stage']['mobile']['minHeight'] ?? 420),
            'mobile_columns'    => (int) ($contract['layout']['stage']['mobile']['grid']['columns'] ?? 4),
            'mobile_gutter'     => (int) ($contract['layout']['stage']['mobile']['grid']['gutter'] ?? 12),
            'mobile_bleed_x'    => (int) ($contract['layout']['stage']['mobile']['grid']['bleedX'] ?? 32),
            'elements'          => is_array($contract['content']['section']['elements'] ?? null) ? $contract['content']['section']['elements'] : [],
        ];
    }

    private static function isContractPayload(array $payload) {
        foreach (['meta', 'content', 'design', 'layout', 'data', 'entities', 'runtime'] as $key) {
            if (!isset($payload[$key]) || !is_array($payload[$key])) {
                return false;
            }
        }

        return true;
    }

    private static function normalizeBackground($raw) {
        $raw = is_array($raw) ? $raw : [];

        return [
            'mode'           => self::select($raw['mode'] ?? ($raw['background_mode'] ?? 'solid'), self::$allowed_background_modes, 'solid'),
            'color'          => self::string($raw['color'] ?? ($raw['background_color'] ?? '#f5f7fb'), '#f5f7fb', 255),
            'gradientFrom'   => self::string($raw['gradientFrom'] ?? ($raw['background_gradient_from'] ?? '#f8fafc'), '#f8fafc', 255),
            'gradientTo'     => self::string($raw['gradientTo'] ?? ($raw['background_gradient_to'] ?? '#e2e8f0'), '#e2e8f0', 255),
            'gradientAngle'  => self::number($raw['gradientAngle'] ?? ($raw['background_gradient_angle'] ?? 135), 0, 360, 135),
            'image'          => self::string($raw['image'] ?? ($raw['background_image'] ?? ''), '', 1024),
            'imagePosition'  => self::string($raw['imagePosition'] ?? ($raw['background_image_position'] ?? 'center center'), 'center center', 64),
            'imageSize'      => self::string($raw['imageSize'] ?? ($raw['background_image_size'] ?? 'cover'), 'cover', 64),
            'imageRepeat'    => self::string($raw['imageRepeat'] ?? ($raw['background_image_repeat'] ?? 'no-repeat'), 'no-repeat', 64),
            'overlayColor'   => self::string($raw['overlayColor'] ?? ($raw['background_overlay_color'] ?? 'rgba(15,23,42,.18)'), 'rgba(15,23,42,.18)', 255),
            'overlayOpacity' => self::number($raw['overlayOpacity'] ?? ($raw['background_overlay_opacity'] ?? 18), 0, 100, 18),
        ];
    }

    private static function normalizeStage($raw) {
        $raw = is_array($raw) ? $raw : [];

        return [
            'desktop' => self::normalizeStageBranch($raw['desktop'] ?? $raw, 1200, 640, 24, 12, 20, 160),
            'tablet'  => self::normalizeStageBranch($raw['tablet'] ?? $raw, 768, 540, 20, 8, 16, 96),
            'mobile'  => self::normalizeStageBranch($raw['mobile'] ?? $raw, 390, 420, 16, 4, 12, 32),
        ];
    }

    private static function normalizeStageBranch($raw, $default_width, $default_min_height, $default_padding, $default_columns, $default_gutter, $default_bleed_x) {
        $raw = is_array($raw) ? $raw : [];
        $grid = is_array($raw['grid'] ?? null) ? $raw['grid'] : $raw;

        return [
            'width'      => self::number($raw['width'] ?? ($raw['stage_width'] ?? $default_width), 240, 1920, $default_width),
            'minHeight'  => self::number($raw['minHeight'] ?? ($raw['stage_min_height'] ?? $default_min_height), 160, 1800, $default_min_height),
            'paddingX'   => self::number($raw['paddingX'] ?? ($raw['stage_padding_x'] ?? $default_padding), 0, 160, $default_padding),
            'paddingY'   => self::number($raw['paddingY'] ?? ($raw['stage_padding_y'] ?? $default_padding), 0, 160, $default_padding),
            'grid'       => [
                'columns' => self::number($grid['columns'] ?? ($grid['columnsCount'] ?? $default_columns), 1, 24, $default_columns),
                'gutter'  => self::number($grid['gutter'] ?? ($grid['columnGap'] ?? $default_gutter), 0, 80, $default_gutter),
                'bleedX'  => self::number($grid['bleedX'] ?? ($grid['bleed'] ?? $default_bleed_x), 0, 480, $default_bleed_x),
            ],
        ];
    }

    private static function normalizeElements(array $elements) {
        $normalized = [];
        $seen_ids   = [];

        foreach ($elements as $index => $element) {
            if (!is_array($element)) {
                continue;
            }

            $normalized_element = self::normalizeElement($element, $index);
            if (!$normalized_element) {
                continue;
            }

            if (isset($seen_ids[$normalized_element['id']])) {
                $normalized_element['id'] .= '-' . ($index + 1);
            }

            $seen_ids[$normalized_element['id']] = true;
            $normalized[] = $normalized_element;
        }

        return $normalized;
    }

    private static function normalizeElement(array $raw, $index) {
        $type = self::normalizeElementType($raw['type'] ?? 'text');
        $id   = self::id($raw['id'] ?? ($type . '-' . ($index + 1)), $type . '-' . ($index + 1));
        $desktop = self::normalizeElementBranch($type, $raw['desktop'] ?? $raw, []);
        $tablet  = self::normalizeElementBranch($type, $raw['tablet'] ?? [], $desktop);
        $mobile  = self::normalizeElementBranch($type, $raw['mobile'] ?? [], $tablet);

        return [
            'id'       => $id,
            'type'     => $type,
            'name'     => self::string($raw['name'] ?? ucfirst(str_replace('_', ' ', $type)), ucfirst(str_replace('_', ' ', $type)), 120),
            'role'     => self::id($raw['role'] ?? '', ''),
            'parentId' => self::id($raw['parentId'] ?? '', ''),
            'desktop'  => $desktop,
            'tablet'   => $tablet,
            'mobile'   => $mobile,
        ];
    }

    private static function normalizeElementBranch($type, $raw, array $fallback) {
        $raw         = is_array($raw) ? $raw : [];
        $fallback_box   = is_array($fallback['box'] ?? null) ? $fallback['box'] : [];
        $fallback_props = is_array($fallback['props'] ?? null) ? $fallback['props'] : self::defaultPropsForType($type);
        $box_source     = isset($raw['box']) && is_array($raw['box']) ? $raw['box'] : $raw;
        $props_source   = isset($raw['props']) && is_array($raw['props']) ? $raw['props'] : $raw;

        return [
            'box' => [
                'x'      => self::number($box_source['x'] ?? ($fallback_box['x'] ?? 0), -4000, 4000, $fallback_box['x'] ?? 0),
                'y'      => self::number($box_source['y'] ?? ($fallback_box['y'] ?? 0), -4000, 4000, $fallback_box['y'] ?? 0),
                'w'      => self::number($box_source['w'] ?? ($box_source['width'] ?? ($fallback_box['w'] ?? 320)), 1, 4000, $fallback_box['w'] ?? 320),
                'h'      => self::number($box_source['h'] ?? ($box_source['height'] ?? ($fallback_box['h'] ?? 80)), 1, 4000, $fallback_box['h'] ?? 80),
                'zIndex' => self::number($box_source['zIndex'] ?? ($fallback_box['zIndex'] ?? 1), -50, 500, $fallback_box['zIndex'] ?? 1),
                'visible'=> self::bool($box_source['visible'] ?? ($fallback_box['visible'] ?? true), true),
            ],
            'props' => array_replace($fallback_props, self::normalizeTypeProps($type, $props_source)),
        ];
    }

    private static function normalizeTypeProps($type, array $raw) {
        $common = [
            'opacityPct'   => self::number($raw['opacityPct'] ?? 100, 0, 100, 100),
            'rotate'       => self::number($raw['rotate'] ?? 0, -360, 360, 0),
            'backgroundColor' => self::string($raw['backgroundColor'] ?? '', '', 255),
            'backgroundCss'   => self::string($raw['backgroundCss'] ?? '', '', 255),
            'borderRadius' => self::number($raw['borderRadius'] ?? 0, 0, 999, 0),
            'borderWidth'  => self::number($raw['borderWidth'] ?? 0, 0, 32, 0),
            'borderColor'  => self::string($raw['borderColor'] ?? '', '', 255),
            'borderStyle'  => self::select($raw['borderStyle'] ?? 'solid', ['solid', 'dashed', 'dotted'], 'solid'),
            'boxShadow'    => self::string($raw['boxShadow'] ?? '', '', 255),
            'blur'         => self::number($raw['blur'] ?? 0, 0, 80, 0),
            'backdropBlur' => self::number($raw['backdropBlur'] ?? 0, 0, 80, 0),
        ];

        if ($type === 'text') {
            return $common + [
                'text'          => self::string($raw['text'] ?? 'Новый текст', 'Новый текст', 5000),
                'tag'           => self::select($raw['tag'] ?? 'div', ['div', 'p', 'span', 'h1', 'h2', 'h3', 'h4'], 'div'),
                'color'         => self::string($raw['color'] ?? '#0f172a', '#0f172a', 255),
                'fontFamily'    => self::select($raw['fontFamily'] ?? 'montserrat', NordicblocksDesignBlockTypography::getFontFamilyValues(), 'montserrat'),
                'fontSize'      => self::number($raw['fontSize'] ?? 48, 10, 240, 48),
                'fontWeight'    => self::number($raw['fontWeight'] ?? 800, 100, 900, 800),
                'lineHeight'    => self::number($raw['lineHeight'] ?? 120, 60, 240, 120),
                'letterSpacing' => self::number($raw['letterSpacing'] ?? 0, -20, 40, 0),
                'textAlign'     => self::select($raw['textAlign'] ?? 'left', ['left', 'center', 'right'], 'left'),
                'textTransform' => self::select($raw['textTransform'] ?? 'none', ['none', 'uppercase', 'lowercase'], 'none'),
            ];
        }

        if ($type === 'button') {
            return $common + [
                'text'           => self::string($raw['text'] ?? 'Подробнее', 'Подробнее', 255),
                'url'            => self::string($raw['url'] ?? '#', '#', 1024),
                'targetBlank'    => self::bool($raw['targetBlank'] ?? false, false),
                'color'          => self::string($raw['color'] ?? '#ffffff', '#ffffff', 255),
                'fontFamily'     => self::select($raw['fontFamily'] ?? 'montserrat', NordicblocksDesignBlockTypography::getFontFamilyValues(), 'montserrat'),
                'fontSize'       => self::number($raw['fontSize'] ?? 16, 10, 96, 16),
                'fontWeight'     => self::number($raw['fontWeight'] ?? 700, 100, 900, 700),
                'lineHeight'     => self::number($raw['lineHeight'] ?? 120, 60, 240, 120),
                'letterSpacing'  => self::number($raw['letterSpacing'] ?? 0, -20, 40, 0),
                'textTransform'  => self::select($raw['textTransform'] ?? 'none', ['none', 'uppercase', 'lowercase'], 'none'),
                'justifyContent' => self::select($raw['justifyContent'] ?? 'center', ['flex-start', 'center', 'flex-end'], 'center'),
                'paddingTop'     => self::number($raw['paddingTop'] ?? 16, 0, 240, 16),
                'paddingRight'   => self::number($raw['paddingRight'] ?? 28, 0, 240, 28),
                'paddingBottom'  => self::number($raw['paddingBottom'] ?? 16, 0, 240, 16),
                'paddingLeft'    => self::number($raw['paddingLeft'] ?? 28, 0, 240, 28),
                'gap'            => self::number($raw['gap'] ?? 10, 0, 120, 10),
                'iconClass'      => self::string($raw['iconClass'] ?? '', '', 255),
                'iconPosition'   => self::select($raw['iconPosition'] ?? 'start', ['start', 'end'], 'start'),
                'hoverColor'     => self::string($raw['hoverColor'] ?? '', '', 255),
                'hoverBackgroundColor' => self::string($raw['hoverBackgroundColor'] ?? '', '', 255),
                'hoverBorderColor' => self::string($raw['hoverBorderColor'] ?? '', '', 255),
            ];
        }

        if ($type === 'photo' || $type === 'image' || $type === 'svg') {
            return $common + [
                'src'            => self::string($raw['src'] ?? '', '', 1024),
                'alt'            => self::string($raw['alt'] ?? '', '', 255),
                'objectFit'      => self::select($raw['objectFit'] ?? 'cover', ['cover', 'contain', 'fill'], 'cover'),
                'objectPosition' => self::select($raw['objectPosition'] ?? 'center center', ['center center', 'left top', 'right top', 'left bottom', 'right bottom'], 'center center'),
            ];
        }

        if ($type === 'video') {
            return $common + [
                'src'       => self::string($raw['src'] ?? '', '', 1024),
                'poster'    => self::string($raw['poster'] ?? '', '', 1024),
                'autoplay'  => self::bool($raw['autoplay'] ?? false, false),
                'muted'     => self::bool($raw['muted'] ?? true, true),
                'controls'  => self::bool($raw['controls'] ?? true, true),
                'loop'      => self::bool($raw['loop'] ?? false, false),
                'objectFit' => self::select($raw['objectFit'] ?? 'cover', ['cover', 'contain', 'fill'], 'cover'),
            ];
        }

        if ($type === 'object' || $type === 'shape') {
            return $common + [
                'shape' => self::select($raw['shape'] ?? 'rect', ['rect', 'pill', 'circle', 'line'], 'rect'),
                'fill'  => self::string($raw['fill'] ?? ($raw['backgroundColor'] ?? '#dbeafe'), '#dbeafe', 255),
            ];
        }

        if ($type === 'icon') {
            return $common + [
                'iconClass' => self::string($raw['iconClass'] ?? 'fas fa-star', 'fas fa-star', 255),
                'color'     => self::string($raw['color'] ?? '#0f172a', '#0f172a', 255),
                'size'      => self::number($raw['size'] ?? 24, 10, 240, 24),
            ];
        }

        if ($type === 'divider') {
            return $common + [
                'color'       => self::string($raw['color'] ?? '#cbd5e1', '#cbd5e1', 255),
                'orientation' => self::select($raw['orientation'] ?? 'horizontal', ['horizontal', 'vertical'], 'horizontal'),
            ];
        }

        if ($type === 'container') {
            return $common + [
                'layoutMode'    => self::select($raw['layoutMode'] ?? 'absolute', ['absolute', 'flex'], 'absolute'),
                'direction'     => self::select($raw['direction'] ?? 'column', ['row', 'column'], 'column'),
                'justifyContent'=> self::select($raw['justifyContent'] ?? 'flex-start', ['flex-start', 'center', 'flex-end', 'space-between'], 'flex-start'),
                'alignItems'    => self::select($raw['alignItems'] ?? 'stretch', ['stretch', 'flex-start', 'center', 'flex-end'], 'stretch'),
                'gap'           => self::number($raw['gap'] ?? 16, 0, 200, 16),
                'paddingTop'    => self::number($raw['paddingTop'] ?? 0, 0, 240, 0),
                'paddingRight'  => self::number($raw['paddingRight'] ?? 0, 0, 240, 0),
                'paddingBottom' => self::number($raw['paddingBottom'] ?? 0, 0, 240, 0),
                'paddingLeft'   => self::number($raw['paddingLeft'] ?? 0, 0, 240, 0),
            ];
        }

        return $common;
    }

    private static function defaultPropsForType($type) {
        return self::normalizeTypeProps($type, []);
    }

    private static function buildElementEntityMap(array $elements) {
        $entities = [];

        foreach ($elements as $element) {
            $id   = (string) ($element['id'] ?? '');
            $type = (string) ($element['type'] ?? '');
            if ($id === '' || $type === '') {
                continue;
            }

            $entities[$id] = [
                'kind' => in_array($type, ['text', 'button', 'icon'], true) ? 'text' : 'surface',
                'role' => (string) ($element['role'] ?? ''),
                'type' => $type,
            ];
        }

        return $entities;
    }

    private static function buildDefaultElements() {
        return [
            [
                'id'   => 'shape-accent',
                'type' => 'object',
                'name' => 'Accent Shape',
                'role' => 'accent',
                'parentId' => '',
                'desktop' => [
                    'box' => ['x' => 820, 'y' => 72, 'w' => 280, 'h' => 280, 'zIndex' => 1, 'visible' => true],
                    'props' => self::normalizeTypeProps('object', ['shape' => 'circle', 'fill' => 'linear-gradient(135deg,#f97316 0%,#fb7185 100%)', 'blur' => 0]),
                ],
                'tablet' => [
                    'box' => ['x' => 500, 'y' => 70, 'w' => 220, 'h' => 220, 'zIndex' => 1, 'visible' => true],
                    'props' => self::normalizeTypeProps('object', ['shape' => 'circle', 'fill' => 'linear-gradient(135deg,#f97316 0%,#fb7185 100%)']),
                ],
                'mobile' => [
                    'box' => ['x' => 210, 'y' => 54, 'w' => 120, 'h' => 120, 'zIndex' => 1, 'visible' => true],
                    'props' => self::normalizeTypeProps('object', ['shape' => 'circle', 'fill' => 'linear-gradient(135deg,#f97316 0%,#fb7185 100%)']),
                ],
            ],
            [
                'id'   => 'headline',
                'type' => 'text',
                'name' => 'Headline',
                'role' => 'headline',
                'parentId' => '',
                'desktop' => [
                    'box' => ['x' => 72, 'y' => 86, 'w' => 660, 'h' => 180, 'zIndex' => 3, 'visible' => true],
                    'props' => self::normalizeTypeProps('text', ['text' => 'Свободный design block для NordicBlocks', 'tag' => 'h1', 'fontSize' => 58, 'fontWeight' => 800, 'lineHeight' => 105, 'color' => '#0f172a']),
                ],
                'tablet' => [
                    'box' => ['x' => 40, 'y' => 64, 'w' => 480, 'h' => 160, 'zIndex' => 3, 'visible' => true],
                    'props' => self::normalizeTypeProps('text', ['text' => 'Свободный design block для NordicBlocks', 'tag' => 'h1', 'fontSize' => 42, 'fontWeight' => 800, 'lineHeight' => 108, 'color' => '#0f172a']),
                ],
                'mobile' => [
                    'box' => ['x' => 24, 'y' => 36, 'w' => 280, 'h' => 140, 'zIndex' => 3, 'visible' => true],
                    'props' => self::normalizeTypeProps('text', ['text' => 'Свободный design block для NordicBlocks', 'tag' => 'h1', 'fontSize' => 28, 'fontWeight' => 800, 'lineHeight' => 112, 'color' => '#0f172a']),
                ],
            ],
            [
                'id'   => 'description',
                'type' => 'text',
                'name' => 'Description',
                'role' => 'description',
                'parentId' => '',
                'desktop' => [
                    'box' => ['x' => 76, 'y' => 292, 'w' => 520, 'h' => 84, 'zIndex' => 3, 'visible' => true],
                    'props' => self::normalizeTypeProps('text', ['text' => 'Stage 1-3 уже активируют contract-first storage, SSR render и backend preview shell. Графический freeform editor может доехать следующим шагом поверх этого каркаса.', 'fontSize' => 18, 'fontWeight' => 400, 'lineHeight' => 145, 'color' => '#334155']),
                ],
                'tablet' => [
                    'box' => ['x' => 42, 'y' => 246, 'w' => 420, 'h' => 96, 'zIndex' => 3, 'visible' => true],
                    'props' => self::normalizeTypeProps('text', ['text' => 'Stage 1-3 уже активируют contract-first storage, SSR render и backend preview shell.', 'fontSize' => 16, 'fontWeight' => 400, 'lineHeight' => 145, 'color' => '#334155']),
                ],
                'mobile' => [
                    'box' => ['x' => 24, 'y' => 170, 'w' => 300, 'h' => 120, 'zIndex' => 3, 'visible' => true],
                    'props' => self::normalizeTypeProps('text', ['text' => 'Stage 1-3 уже активируют contract-first storage, SSR render и backend preview shell.', 'fontSize' => 15, 'fontWeight' => 400, 'lineHeight' => 145, 'color' => '#334155']),
                ],
            ],
            [
                'id'   => 'cta',
                'type' => 'button',
                'name' => 'Primary CTA',
                'role' => 'primary_cta',
                'parentId' => '',
                'desktop' => [
                    'box' => ['x' => 76, 'y' => 412, 'w' => 220, 'h' => 56, 'zIndex' => 4, 'visible' => true],
                    'props' => self::normalizeTypeProps('button', ['text' => 'Открыть JSON shell', 'url' => '#', 'backgroundColor' => '#0f172a', 'color' => '#ffffff', 'borderRadius' => 999, 'fontSize' => 16]),
                ],
                'tablet' => [
                    'box' => ['x' => 42, 'y' => 368, 'w' => 220, 'h' => 52, 'zIndex' => 4, 'visible' => true],
                    'props' => self::normalizeTypeProps('button', ['text' => 'Открыть JSON shell', 'url' => '#', 'backgroundColor' => '#0f172a', 'color' => '#ffffff', 'borderRadius' => 999, 'fontSize' => 15]),
                ],
                'mobile' => [
                    'box' => ['x' => 24, 'y' => 316, 'w' => 182, 'h' => 48, 'zIndex' => 4, 'visible' => true],
                    'props' => self::normalizeTypeProps('button', ['text' => 'JSON shell', 'url' => '#', 'backgroundColor' => '#0f172a', 'color' => '#ffffff', 'borderRadius' => 999, 'fontSize' => 14]),
                ],
            ],
        ];
    }

    private static function normalizeElementType($type) {
        $type = self::select($type, self::$allowed_element_types, 'text');

        if ($type === 'image') {
            return 'photo';
        }

        if ($type === 'shape') {
            return 'object';
        }

        return $type;
    }

    private static function id($value, $default = '') {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9_\-]/', '-', $value);
        $value = trim((string) preg_replace('/-+/', '-', $value), '-');
        return $value !== '' ? $value : $default;
    }

    private static function string($value, $default = '', $max_length = 255) {
        $value = trim((string) $value);
        if ($value === '') {
            $value = $default;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $max_length, 'UTF-8');
        }

        return substr($value, 0, $max_length);
    }

    private static function select($value, array $allowed, $default) {
        $value = trim((string) $value);
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private static function number($value, $min, $max, $default) {
        if (!is_numeric($value)) {
            return $default;
        }

        $value = (float) $value;
        if ($value < $min) {
            $value = $min;
        }
        if ($value > $max) {
            $value = $max;
        }

        if (floor($value) === $value) {
            return (int) $value;
        }

        return round($value, 3);
    }

    private static function bool($value, $default) {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}