<?php

class NordicblocksDesignBlockCssBuilder {

    public static function build(array $payload) {
        $section_id = (string) ($payload['sectionId'] ?? 'nb-design-block');
        $base = self::baseCss($section_id);
        $desktop = '';
        $tablet = '';
        $mobile = '';

        self::collectElementCss((array) ($payload['elements'] ?? []), $section_id, '', false, $desktop, $tablet, $mobile);

        $all = $base . $desktop;
        if ($tablet !== '') {
            $all .= '@media (max-width: 991px){#' . $section_id . '{--nb-design-stage-width:var(--nb-design-stage-width-tablet);--nb-design-stage-min-height:var(--nb-design-stage-min-height-tablet);--nb-design-stage-padding-x:var(--nb-design-stage-padding-x-tablet);--nb-design-stage-padding-y:var(--nb-design-stage-padding-y-tablet);}' . $tablet . '}';
        }
        if ($mobile !== '') {
            $all .= '@media (max-width: 640px){#' . $section_id . '{--nb-design-stage-width:var(--nb-design-stage-width-mobile);--nb-design-stage-min-height:var(--nb-design-stage-min-height-mobile);--nb-design-stage-padding-x:var(--nb-design-stage-padding-x-mobile);--nb-design-stage-padding-y:var(--nb-design-stage-padding-y-mobile);}' . $mobile . '}';
        }

        return ['all' => $all];
    }

    public static function buildSectionInlineStyle(array $payload) {
        $stage = (array) ($payload['stage'] ?? []);
        $background = (array) ($payload['section']['background'] ?? []);

        $style = [
            '--nb-design-stage-width:' . (int) ($stage['desktop']['width'] ?? 1200) . 'px',
            '--nb-design-stage-min-height:' . (int) ($stage['desktop']['minHeight'] ?? 640) . 'px',
            '--nb-design-stage-padding-x:' . (int) ($stage['desktop']['paddingX'] ?? 24) . 'px',
            '--nb-design-stage-padding-y:' . (int) ($stage['desktop']['paddingY'] ?? 24) . 'px',
            '--nb-design-stage-width-tablet:' . (int) ($stage['tablet']['width'] ?? 768) . 'px',
            '--nb-design-stage-min-height-tablet:' . (int) ($stage['tablet']['minHeight'] ?? 540) . 'px',
            '--nb-design-stage-padding-x-tablet:' . (int) ($stage['tablet']['paddingX'] ?? 20) . 'px',
            '--nb-design-stage-padding-y-tablet:' . (int) ($stage['tablet']['paddingY'] ?? 20) . 'px',
            '--nb-design-stage-width-mobile:' . (int) ($stage['mobile']['width'] ?? 390) . 'px',
            '--nb-design-stage-min-height-mobile:' . (int) ($stage['mobile']['minHeight'] ?? 420) . 'px',
            '--nb-design-stage-padding-x-mobile:' . (int) ($stage['mobile']['paddingX'] ?? 16) . 'px',
            '--nb-design-stage-padding-y-mobile:' . (int) ($stage['mobile']['paddingY'] ?? 16) . 'px',
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
        return '#' . $section_id . '{position:relative;overflow:hidden;padding:clamp(1.25rem,4vw,2.5rem);border-radius:28px}#' . $section_id . ' .nb-design-block__stage{position:relative;width:min(100%,var(--nb-design-stage-width));min-height:var(--nb-design-stage-min-height);padding:var(--nb-design-stage-padding-y) var(--nb-design-stage-padding-x);margin:0 auto;overflow:hidden}#' . $section_id . ' .nb-design-el{box-sizing:border-box;transform-origin:center center}#' . $section_id . ' .nb-design-el--button>.nb-design-button__link{display:flex;align-items:center;justify-content:inherit;width:100%;height:100%;color:inherit;text-decoration:none}#' . $section_id . ' .nb-design-el--image img,#' . $section_id . ' .nb-design-el--svg img,#' . $section_id . ' .nb-design-el--video video{width:100%;height:100%;display:block}';
    }

    private static function collectElementCss(array $elements, $section_id, $parent_type, $flow_child, &$desktop, &$tablet, &$mobile) {
        foreach ($elements as $element) {
            if (!is_array($element) || empty($element['id'])) {
                continue;
            }

            $selector = '#' . $section_id . ' [data-el-id="' . self::attr((string) $element['id']) . '"]';
            $type = (string) ($element['type'] ?? 'text');
            $desktop .= $selector . '{' . self::buildElementCss($type, (array) ($element['desktop'] ?? []), $flow_child) . '}';
            $tablet  .= $selector . '{' . self::buildElementCss($type, (array) ($element['tablet'] ?? []), $flow_child) . '}';
            $mobile  .= $selector . '{' . self::buildElementCss($type, (array) ($element['mobile'] ?? []), $flow_child) . '}';

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

        if (!empty($props['borderRadius']) || $type === 'shape' && (($props['shape'] ?? '') === 'circle')) {
            $radius = $type === 'shape' && (($props['shape'] ?? '') === 'circle') ? 9999 : (int) ($props['borderRadius'] ?? 0);
            $css .= 'border-radius:' . $radius . 'px;';
        }
        if (!empty($props['borderWidth'])) {
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
            $css .= 'color:' . self::css((string) ($props['color'] ?? '#0f172a')) . ';font-size:' . (float) ($props['fontSize'] ?? 16) . 'px;font-weight:' . (int) ($props['fontWeight'] ?? 400) . ';line-height:' . ((float) ($props['lineHeight'] ?? 140) / 100) . ';letter-spacing:' . (float) ($props['letterSpacing'] ?? 0) . 'px;text-align:' . self::css((string) ($props['textAlign'] ?? 'left')) . ';text-transform:' . self::css((string) ($props['textTransform'] ?? 'none')) . ';white-space:pre-wrap;';
        } elseif ($type === 'button') {
            $css .= 'display:flex;align-items:center;justify-content:' . self::css((string) ($props['justifyContent'] ?? 'center')) . ';color:' . self::css((string) ($props['color'] ?? '#ffffff')) . ';font-size:' . (float) ($props['fontSize'] ?? 16) . 'px;font-weight:' . (int) ($props['fontWeight'] ?? 700) . ';';
        } elseif ($type === 'image' || $type === 'svg') {
            $css .= 'overflow:hidden;';
        } elseif ($type === 'video') {
            $css .= 'overflow:hidden;background:#020617;';
        } elseif ($type === 'shape') {
            $css .= 'background:' . self::css((string) ($props['fill'] ?? '#dbeafe')) . ';';
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