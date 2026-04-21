<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockTypography.php';

class NordicblocksDesignBlockCssBuilder {

    private static function normalizeType($type) {
        $type = (string) $type;

        if ($type === 'image') {
            return 'photo';
        }

        if ($type === 'shape') {
            return 'object';
        }

        return $type;
    }

    private static function resolveStageBranch(array $stage, $breakpoint, array $fallback) {
        $branch = isset($stage[$breakpoint]) && is_array($stage[$breakpoint]) ? $stage[$breakpoint] : [];
        $grid = isset($branch['grid']) && is_array($branch['grid']) ? $branch['grid'] : [];
        $grid_overlay = isset($branch['gridOverlay']) && is_array($branch['gridOverlay']) ? $branch['gridOverlay'] : [];
        $columns = max(1, (int) ($branch['columns'] ?? ($grid['columns'] ?? $fallback['columns'])));
        $gutter = max(0, (int) ($branch['gutter'] ?? ($grid['gutter'] ?? $fallback['gutter'])));
        $column_width = max(1, (float) ($branch['columnWidth'] ?? $fallback['columnWidth']));
        $content_width = max(1, (int) ($branch['contentWidth'] ?? ($columns * $column_width) + (max(0, $columns - 1) * $gutter)));
        $outer_margin = array_key_exists('outerMargin', $branch)
            ? max(0, (int) $branch['outerMargin'])
            : max(0, ((int) ($branch['windowWidth'] ?? ($content_width + ($fallback['outerMargin'] * 2))) - $content_width) / 2);

        return [
            'contentWidth' => $content_width,
            'minHeight' => max(1, (int) ($branch['minHeight'] ?? $fallback['minHeight'])),
            'outerMargin' => $outer_margin,
            'columns' => $columns,
            'gutter' => $gutter,
            'gridOverlay' => [
                'color' => (string) ($grid_overlay['color'] ?? '#0f172a'),
                'opacity' => max(0, min(100, (int) ($grid_overlay['opacity'] ?? 8))),
            ],
        ];
    }

    public static function build(array $payload) {
        $section_id = (string) ($payload['sectionId'] ?? 'nb-design-block');
        $base = self::baseCss($section_id);
        $desktop = '';
        $tablet = '';
        $mobile = '';
        $font_face_css = NordicblocksDesignBlockTypography::buildFontFaceCss(self::collectUsedFontFamilies((array) ($payload['elements'] ?? [])));

        self::collectElementCss((array) ($payload['elements'] ?? []), $section_id, '', false, $desktop, $tablet, $mobile);

        $all = $font_face_css . $base . $desktop;
        if ($tablet !== '') {
            $all .= '@media (max-width: 991px){#' . $section_id . '{--nb-design-stage-width:var(--nb-design-stage-width-tablet);--nb-design-stage-min-height:var(--nb-design-stage-min-height-tablet);--nb-design-stage-padding-x:var(--nb-design-stage-padding-x-tablet);--nb-design-stage-padding-y:var(--nb-design-stage-padding-y-tablet);}' . $tablet . '}';
        }
        if ($mobile !== '') {
            $all .= '@media (max-width: 640px){#' . $section_id . '{--nb-design-stage-width:var(--nb-design-stage-width-mobile);--nb-design-stage-min-height:var(--nb-design-stage-min-height-mobile);--nb-design-stage-padding-x:var(--nb-design-stage-padding-x-mobile);--nb-design-stage-padding-y:var(--nb-design-stage-padding-y-mobile);}' . $mobile . '}';
        }

        return ['all' => $all];
    }

    private static function collectUsedFontFamilies(array $elements, array $used = []) {
        foreach ($elements as $element) {
            if (!is_array($element)) {
                continue;
            }

            foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
                $props = (array) (($element[$breakpoint]['props'] ?? []));
                if (!empty($props['fontFamily'])) {
                    $used[] = (string) $props['fontFamily'];
                }
            }

            $used = self::collectUsedFontFamilies((array) ($element['children'] ?? []), $used);
        }

        return array_values(array_unique($used));
    }

    public static function buildSectionInlineStyle(array $payload) {
        $stage = (array) ($payload['stage'] ?? []);
        $background = (array) ($payload['section']['background'] ?? []);
        $desktop = self::resolveStageBranch($stage, 'desktop', ['contentWidth' => 1110, 'outerMargin' => 165, 'columns' => 12, 'gutter' => 30, 'columnWidth' => 65, 'minHeight' => 680]);
        $tablet = self::resolveStageBranch($stage, 'tablet', ['contentWidth' => 672, 'outerMargin' => 48, 'columns' => 8, 'gutter' => 16, 'columnWidth' => 70, 'minHeight' => 560]);
        $mobile = self::resolveStageBranch($stage, 'mobile', ['contentWidth' => 342, 'outerMargin' => 24, 'columns' => 4, 'gutter' => 12, 'columnWidth' => 76.5, 'minHeight' => 440]);

        $style = [
            '--nb-design-stage-width:' . (int) $desktop['contentWidth'] . 'px',
            '--nb-design-stage-min-height:' . (int) $desktop['minHeight'] . 'px',
            '--nb-design-stage-padding-x:0px',
            '--nb-design-stage-padding-y:0px',
            '--nb-design-stage-width-tablet:' . (int) $tablet['contentWidth'] . 'px',
            '--nb-design-stage-min-height-tablet:' . (int) $tablet['minHeight'] . 'px',
            '--nb-design-stage-padding-x-tablet:0px',
            '--nb-design-stage-padding-y-tablet:0px',
            '--nb-design-stage-width-mobile:' . (int) $mobile['contentWidth'] . 'px',
            '--nb-design-stage-min-height-mobile:' . (int) $mobile['minHeight'] . 'px',
            '--nb-design-stage-padding-x-mobile:0px',
            '--nb-design-stage-padding-y-mobile:0px',
        ];

        $mode = (string) ($background['mode'] ?? 'solid');
        if ($mode === 'gradient') {
            $style[] = 'background-image:linear-gradient(' . (int) ($background['gradientAngle'] ?? 135) . 'deg,' . self::css((string) ($background['gradientFrom'] ?? '#f8fafc')) . ' 0%,' . self::css((string) ($background['gradientTo'] ?? '#e2e8f0')) . ' 100%)';
        } elseif ($mode === 'image' && !empty($background['image'])) {
            $overlay_alpha = max(0, min(100, (int) ($background['overlayOpacity'] ?? 18))) / 100;
            $overlay = self::withAlpha((string) ($background['overlayColor'] ?? '#0f172a'), $overlay_alpha);
            $style[] = 'background-image:linear-gradient(' . $overlay . ',' . $overlay . '),url(' . self::url((string) $background['image']) . ')';
            $style[] = 'background-position:' . self::css((string) ($background['imagePosition'] ?? 'center center'));
            $style[] = 'background-size:' . self::css((string) ($background['imageSize'] ?? 'cover'));
            $style[] = 'background-repeat:' . self::css((string) ($background['imageRepeat'] ?? 'no-repeat'));
        } else {
            $style[] = 'background:' . self::css((string) ($background['color'] ?? '#f5f7fb'));
        }

        return implode(';', $style) . ';';
    }

    private static function baseCss($section_id) {
        return '#' . $section_id . '{position:relative;overflow:hidden;padding:clamp(1.25rem,4vw,2.5rem);border-radius:28px}#' . $section_id . ' .nb-design-block__stage{position:relative;width:min(100%,var(--nb-design-stage-width));min-height:var(--nb-design-stage-min-height);padding:var(--nb-design-stage-padding-y) var(--nb-design-stage-padding-x);margin:0 auto;overflow:visible}#' . $section_id . ' .nb-design-el{box-sizing:border-box;transform-origin:center center}#' . $section_id . ' .nb-design-el--button>.nb-design-button__link{display:flex;align-items:center;justify-content:inherit;gap:inherit;width:100%;height:100%;padding:inherit;color:inherit;text-decoration:none;box-sizing:border-box;line-height:inherit;letter-spacing:inherit;text-transform:inherit;transition:background-color .18s ease,color .18s ease,border-color .18s ease}#' . $section_id . ' .nb-design-button__icon{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto}#' . $section_id . ' .nb-design-el--image img,#' . $section_id . ' .nb-design-el--photo img,#' . $section_id . ' .nb-design-el--svg img,#' . $section_id . ' .nb-design-el--video video{width:100%;height:100%;display:block}';
    }

    private static function collectElementCss(array $elements, $section_id, $parent_type, $flow_child, &$desktop, &$tablet, &$mobile) {
        foreach ($elements as $element) {
            if (!is_array($element) || empty($element['id'])) {
                continue;
            }

            $selector = '#' . $section_id . ' [data-el-id="' . self::attr((string) $element['id']) . '"]';
            $type = self::normalizeType((string) ($element['type'] ?? 'text'));
            $desktop .= $selector . '{' . self::buildElementCss($type, (array) ($element['desktop'] ?? []), $flow_child) . '}';
            $tablet  .= $selector . '{' . self::buildElementCss($type, (array) ($element['tablet'] ?? []), $flow_child) . '}';
            $mobile  .= $selector . '{' . self::buildElementCss($type, (array) ($element['mobile'] ?? []), $flow_child) . '}';
            $desktop .= self::buildElementHoverCss($selector, $type, (array) (($element['desktop']['props'] ?? [])));
            $tablet  .= self::buildElementHoverCss($selector, $type, (array) (($element['tablet']['props'] ?? [])));
            $mobile  .= self::buildElementHoverCss($selector, $type, (array) (($element['mobile']['props'] ?? [])));

            $props = (array) (($element['desktop']['props'] ?? []));
            $is_flex_parent = $type === 'container' && (($props['layoutMode'] ?? 'absolute') === 'flex');

            self::collectElementCss((array) ($element['children'] ?? []), $section_id, $type, $is_flex_parent, $desktop, $tablet, $mobile);
        }
    }

    private static function buildElementCss($type, array $branch, $flow_child) {
        $box = (array) ($branch['box'] ?? []);
        $props = (array) ($branch['props'] ?? []);
        $visible = !empty($box['visible']);
        $width = max(1, (int) ($box['w'] ?? 1));
        $height = max(1, (int) ($box['h'] ?? 1));

        $css = $flow_child
            ? 'position:relative;left:auto;top:auto;'
            : 'position:absolute;left:' . (int) ($box['x'] ?? 0) . 'px;top:' . (int) ($box['y'] ?? 0) . 'px;';

        $css .= 'z-index:' . (int) ($box['zIndex'] ?? 1) . ';';
        if (!$visible) {
            $css .= 'display:none;';
        }
        $css .= 'width:' . $width . 'px;';
        $css .= in_array($type, ['text'], true) ? 'min-height:' . $height . 'px;' : 'height:' . $height . 'px;';
        $css .= 'opacity:' . (max(0, min(100, (int) ($props['opacityPct'] ?? 100))) / 100) . ';';
        $css .= 'transform:rotate(' . (float) ($props['rotate'] ?? 0) . 'deg);';

        if (!empty($props['backgroundCss'])) {
            $css .= 'background:' . self::css((string) $props['backgroundCss']) . ';';
        } elseif (!empty($props['backgroundColor'])) {
            $css .= 'background:' . self::css((string) $props['backgroundColor']) . ';';
        }

        if (!empty($props['borderRadius']) || $type === 'object' && (($props['shape'] ?? '') === 'circle')) {
            $radius = $type === 'object' && (($props['shape'] ?? '') === 'circle') ? 9999 : (int) ($props['borderRadius'] ?? 0);
            $css .= 'border-radius:' . $radius . 'px;';
        }
        if (!empty($props['borderWidth']) && !($type === 'object' && (($props['shape'] ?? '') === 'line'))) {
            $css .= 'border:' . (int) $props['borderWidth'] . 'px ' . self::css((string) ($props['borderStyle'] ?? 'solid')) . ' ' . self::css((string) ($props['borderColor'] ?? '#cbd5e1')) . ';';
        }
        if (!empty($props['boxShadow'])) {
            $css .= 'box-shadow:' . self::css((string) $props['boxShadow']) . ';';
        }
        if (!empty($props['blur'])) {
            $css .= 'filter:blur(' . (int) $props['blur'] . 'px);';
        }
        if (!empty($props['backdropBlur'])) {
            $css .= 'backdrop-filter:blur(' . (int) $props['backdropBlur'] . 'px);';
        }

        if ($type === 'text') {
            $css .= 'color:' . self::css((string) ($props['color'] ?? '#0f172a')) . ';font-family:' . NordicblocksDesignBlockTypography::resolveCssStack((string) ($props['fontFamily'] ?? 'montserrat')) . ';font-size:' . (float) ($props['fontSize'] ?? 16) . 'px;font-weight:' . (int) ($props['fontWeight'] ?? 400) . ';line-height:' . ((float) ($props['lineHeight'] ?? 140) / 100) . ';letter-spacing:' . (float) ($props['letterSpacing'] ?? 0) . 'px;text-align:' . self::css((string) ($props['textAlign'] ?? 'left')) . ';text-transform:' . self::css((string) ($props['textTransform'] ?? 'none')) . ';white-space:pre-wrap;';
        } elseif ($type === 'button') {
            $css .= 'display:flex;align-items:center;justify-content:' . self::css((string) ($props['justifyContent'] ?? 'center')) . ';gap:' . (int) ($props['gap'] ?? 10) . 'px;padding:' . (int) ($props['paddingTop'] ?? 16) . 'px ' . (int) ($props['paddingRight'] ?? 28) . 'px ' . (int) ($props['paddingBottom'] ?? 16) . 'px ' . (int) ($props['paddingLeft'] ?? 28) . 'px;color:' . self::css((string) ($props['color'] ?? '#ffffff')) . ';font-family:' . NordicblocksDesignBlockTypography::resolveCssStack((string) ($props['fontFamily'] ?? 'montserrat')) . ';font-size:' . (float) ($props['fontSize'] ?? 16) . 'px;font-weight:' . (int) ($props['fontWeight'] ?? 700) . ';line-height:' . ((float) ($props['lineHeight'] ?? 120) / 100) . ';letter-spacing:' . (float) ($props['letterSpacing'] ?? 0) . 'px;text-transform:' . self::css((string) ($props['textTransform'] ?? 'none')) . ';background:' . self::buildButtonBackgroundCss($props, false) . ';transition:background ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1),color ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1),border-color ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1),transform ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1),box-shadow ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1);';
        } elseif ($type === 'photo' || $type === 'svg') {
            $css .= 'overflow:hidden;';
        } elseif ($type === 'video') {
            $css .= 'overflow:hidden;background:#020617;';
        } elseif ($type === 'object') {
            if (($props['shape'] ?? '') === 'line') {
                $css .= 'background:transparent;height:0;top:' . max(0, (int) round($height / 2)) . 'px;border-top:' . max(1, (int) ($props['borderWidth'] ?? 2)) . 'px solid ' . self::css((string) ($props['borderColor'] ?? $props['backgroundColor'] ?? $props['fill'] ?? '#dbeafe')) . ';';
            } else {
                $css .= 'background:' . self::css((string) ($props['fill'] ?? '#dbeafe')) . ';';
            }
        } elseif ($type === 'icon') {
            $css .= 'display:flex;align-items:center;justify-content:center;color:' . self::css((string) ($props['color'] ?? '#0f172a')) . ';font-size:' . (float) ($props['size'] ?? 24) . 'px;';
        } elseif ($type === 'divider') {
            $css .= 'background:' . self::css((string) ($props['color'] ?? '#cbd5e1')) . ';';
        } elseif ($type === 'container') {
            $css .= 'overflow:hidden;';
            if (($props['layoutMode'] ?? 'absolute') === 'flex') {
                $css .= 'display:flex;flex-direction:' . self::css((string) ($props['direction'] ?? 'column')) . ';justify-content:' . self::css((string) ($props['justifyContent'] ?? 'flex-start')) . ';align-items:' . self::css((string) ($props['alignItems'] ?? 'stretch')) . ';gap:' . (int) ($props['gap'] ?? 16) . 'px;padding:' . (int) ($props['paddingTop'] ?? 0) . 'px ' . (int) ($props['paddingRight'] ?? 0) . 'px ' . (int) ($props['paddingBottom'] ?? 0) . 'px ' . (int) ($props['paddingLeft'] ?? 0) . 'px;';
            }
        }

        return $css;
    }

    private static function buildElementHoverCss($selector, $type, array $props) {
        if ($type !== 'button') {
            return '';
        }

        $css = '';
        $hoverBackground = self::buildButtonBackgroundCss($props, true);

        if ($hoverBackground !== '') {
            $css .= 'background:' . $hoverBackground . ';';
        }

        if (!empty($props['hoverColor'])) {
            $css .= 'color:' . self::css((string) $props['hoverColor']) . ';';
        }
        if (!empty($props['hoverBorderColor'])) {
            $css .= 'border-color:' . self::css((string) $props['hoverBorderColor']) . ';';
        }
        if (!empty($props['hoverShadow'])) {
            $css .= 'box-shadow:' . self::css((string) $props['hoverShadow']) . ';';
        }
        if ((float) ($props['hoverLift'] ?? 0) > 0 || (float) ($props['hoverScalePct'] ?? 100) !== 100.0) {
            $css .= 'transform:' . self::buildButtonHoverTransform($props) . ';';
        }

        if ($css === '') {
            return '';
        }

        return $selector . ':hover,' . $selector . ':focus-within{' . $css . '}';
    }

    private static function buildButtonBackgroundCss(array $props, $hover = false) {
        $mode = $hover
            ? (string) ($props['hoverBackgroundMode'] ?? 'inherit')
            : (string) ($props['backgroundMode'] ?? 'solid');

        if ($hover && $mode === 'inherit') {
            return self::buildButtonBackgroundCss($props, false);
        }

        if ($mode === 'gradient') {
            $angle = (float) ($props['gradientAngle'] ?? 135);
            $from = $hover
                ? (string) ($props['hoverGradientFrom'] ?? ($props['gradientFrom'] ?? ($props['backgroundColor'] ?? '#0f172a')))
                : (string) ($props['gradientFrom'] ?? ($props['backgroundColor'] ?? '#0f172a'));
            $to = $hover
                ? (string) ($props['hoverGradientTo'] ?? ($props['gradientTo'] ?? ($props['backgroundColor'] ?? '#1d4ed8')))
                : (string) ($props['gradientTo'] ?? ($props['backgroundColor'] ?? '#1d4ed8'));

            return 'linear-gradient(' . $angle . 'deg,' . self::css($from) . ',' . self::css($to) . ')';
        }

        return self::css((string) ($hover ? ($props['hoverBackgroundColor'] ?? ($props['backgroundColor'] ?? '#0f172a')) : ($props['backgroundColor'] ?? '#0f172a')));
    }

    private static function buildButtonHoverTransform(array $props) {
        $lift = max(0, (float) ($props['hoverLift'] ?? 0));
        $scale = max(0.9, min(1.2, ((float) ($props['hoverScalePct'] ?? 100) / 100)));

        return 'translateY(' . (-$lift) . 'px) scale(' . round($scale, 3) . ')';
    }

    private static function css($value) {
        return trim(str_replace(['<', '>', '"', "\n", "\r"], '', (string) $value));
    }

    private static function url($value) {
        return str_replace(['"', ')', '('], '', trim((string) $value));
    }

    private static function attr($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private static function withAlpha($color, $alpha) {
        $color = trim((string) $color);
        if (strpos($color, 'rgba(') === 0 || strpos($color, 'hsla(') === 0) {
            return $color;
        }

        if (preg_match('/^#([0-9a-f]{6})$/i', $color, $matches)) {
            $hex = $matches[1];
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . round($alpha, 3) . ')';
        }

        return $color;
    }
}