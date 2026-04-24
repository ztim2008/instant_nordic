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
        $content_width = max(1, (int) ($branch['contentWidth'] ?? ($branch['width'] ?? (($columns * $column_width) + (max(0, $columns - 1) * $gutter)))));
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
        return '#' . $section_id . '{position:relative;overflow:hidden;padding:0;border-radius:0}#' . $section_id . ' .nb-design-block__stage{position:relative;width:min(100%,var(--nb-design-stage-width));min-height:var(--nb-design-stage-min-height);padding:var(--nb-design-stage-padding-y) var(--nb-design-stage-padding-x);margin:0 auto;overflow:visible}#' . $section_id . ' .nb-design-el{box-sizing:border-box;transform-origin:center center;--nb-design-base-opacity:1;--nb-design-rotate:rotate(0deg);--nb-design-hover-transform:translate3d(0,0,0) scale(1);--nb-design-motion-transform:translate3d(0,0,0);--nb-design-motion-opacity:1;--nb-design-transition-duration:220ms;--nb-design-transition-easing:cubic-bezier(0.22,1,0.36,1);--nb-design-motion-duration:var(--nb-design-transition-duration);--nb-design-motion-delay:0ms;--nb-design-motion-runtime-delay:0ms;--nb-design-motion-easing:var(--nb-design-transition-easing);opacity:calc(var(--nb-design-base-opacity) * var(--nb-design-motion-opacity));transform:var(--nb-design-hover-transform) var(--nb-design-motion-transform) var(--nb-design-rotate);transition:opacity var(--nb-design-motion-duration) var(--nb-design-motion-easing) calc(var(--nb-design-motion-delay) + var(--nb-design-motion-runtime-delay)),transform var(--nb-design-motion-duration) var(--nb-design-motion-easing) calc(var(--nb-design-motion-delay) + var(--nb-design-motion-runtime-delay)),background-color var(--nb-design-transition-duration) var(--nb-design-transition-easing),background-image var(--nb-design-transition-duration) var(--nb-design-transition-easing),color var(--nb-design-transition-duration) var(--nb-design-transition-easing),border-color var(--nb-design-transition-duration) var(--nb-design-transition-easing),box-shadow var(--nb-design-transition-duration) var(--nb-design-transition-easing),filter var(--nb-design-transition-duration) var(--nb-design-transition-easing)}#' . $section_id . '.nb-design-block--motion-ready .nb-design-el[data-motion-active-trigger="entry"],#' . $section_id . '.nb-design-block--motion-ready .nb-design-el[data-motion-active-trigger="scroll"]{--nb-design-motion-opacity:0;--nb-design-motion-transform:var(--nb-design-motion-from,translate3d(0,0,0));will-change:transform,opacity}#' . $section_id . '.nb-design-block--motion-ready .nb-design-el.is-motion-active{--nb-design-motion-opacity:1;--nb-design-motion-transform:translate3d(0,0,0);will-change:auto}#' . $section_id . ' .nb-design-el--button>.nb-design-button__link{display:flex;align-items:center;justify-content:inherit;gap:inherit;width:100%;height:100%;padding:inherit;color:inherit;text-decoration:none;box-sizing:border-box;line-height:inherit;letter-spacing:inherit;text-transform:inherit;transition:background-color .18s ease,color .18s ease,border-color .18s ease}#' . $section_id . ' .nb-design-button__icon{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;color:var(--nb-design-button-icon-color,currentColor)}#' . $section_id . ' .nb-design-button__icon .icms-svg-icon{display:block;width:1.1em;height:1.1em;fill:currentColor}#' . $section_id . ' .nb-design-button__icon i{font-size:1em;line-height:1}#' . $section_id . ' .nb-design-el--image img,#' . $section_id . ' .nb-design-el--photo img,#' . $section_id . ' .nb-design-el--svg img,#' . $section_id . ' .nb-design-el--video video,#' . $section_id . ' .nb-design-el--embed .nb-design-embed__frame{width:100%;height:100%;display:block;border:0}#' . $section_id . ' .nb-design-el--embed .nb-design-embed__surface{width:100%;height:100%;background:transparent}#' . $section_id . ' .nb-design-el--embed .nb-design-embed__placeholder{display:flex;align-items:center;justify-content:center;width:100%;height:100%;padding:1rem;border:1px dashed rgba(148,163,184,.7);background:rgba(255,255,255,.56);color:#475569;font-size:.875rem;text-align:center}';
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
        $css .= '--nb-design-base-opacity:' . (max(0, min(100, (int) ($props['opacityPct'] ?? 100))) / 100) . ';';
        $css .= '--nb-design-rotate:rotate(' . (float) ($props['rotate'] ?? 0) . 'deg);';
        $css .= '--nb-design-transition-duration:' . (int) ($props['transitionDuration'] ?? 220) . 'ms;';

        if (($props['motionTrigger'] ?? 'none') !== 'none') {
            $css .= '--nb-design-motion-duration:' . (int) ($props['motionDuration'] ?? 650) . 'ms;';
            $css .= '--nb-design-motion-delay:' . (int) ($props['motionDelay'] ?? 0) . 'ms;';
            $css .= '--nb-design-motion-easing:' . self::resolveMotionEasingCss((string) ($props['motionEasing'] ?? 'smooth')) . ';';
            $css .= '--nb-design-motion-from:' . self::buildMotionTransformCss($props) . ';';
        }

        $background_color = (string) ($props['backgroundColor'] ?? '');
        if (($type === 'photo' || $type === 'svg') && !empty($props['src']) && strtolower(trim($background_color)) === '#e2e8f0') {
            $background_color = '';
        }

        if (!empty($props['backgroundCss'])) {
            $css .= 'background:' . self::css((string) $props['backgroundCss']) . ';';
        } elseif ($background_color !== '') {
            $css .= 'background:' . self::css($background_color) . ';';
        }

        if (!empty($props['borderRadius']) || $type === 'object' && in_array(($props['shape'] ?? ''), ['circle', 'pill'], true)) {
            $radius = $type === 'object' && in_array(($props['shape'] ?? ''), ['circle', 'pill'], true) ? 9999 : (int) ($props['borderRadius'] ?? 0);
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
        $backdrop_filter = self::buildBackdropFilterCss($props);
        if ($backdrop_filter !== '') {
            $css .= '-webkit-backdrop-filter:' . $backdrop_filter . ';';
            $css .= 'backdrop-filter:' . $backdrop_filter . ';';
        }

        if ($type === 'text') {
            $css .= 'color:' . self::css((string) ($props['color'] ?? '#0f172a')) . ';font-family:' . NordicblocksDesignBlockTypography::resolveCssStack((string) ($props['fontFamily'] ?? 'montserrat')) . ';font-size:' . (float) ($props['fontSize'] ?? 16) . 'px;font-weight:' . (int) ($props['fontWeight'] ?? 400) . ';line-height:' . ((float) ($props['lineHeight'] ?? 140) / 100) . ';letter-spacing:' . (float) ($props['letterSpacing'] ?? 0) . 'px;text-align:' . self::css((string) ($props['textAlign'] ?? 'left')) . ';text-transform:' . self::css((string) ($props['textTransform'] ?? 'none')) . ';white-space:pre-wrap;';
        } elseif ($type === 'button') {
            $button_shadow = self::buildButtonShadowCss($props, false);
            $css .= 'display:flex;align-items:center;justify-content:' . self::css((string) ($props['justifyContent'] ?? 'center')) . ';gap:' . (int) ($props['gap'] ?? 10) . 'px;padding:' . (int) ($props['paddingTop'] ?? 16) . 'px ' . (int) ($props['paddingRight'] ?? 28) . 'px ' . (int) ($props['paddingBottom'] ?? 16) . 'px ' . (int) ($props['paddingLeft'] ?? 28) . 'px;color:' . self::css((string) ($props['color'] ?? '#ffffff')) . ';--nb-design-button-icon-color:' . self::css((string) ($props['iconColor'] ?? ($props['color'] ?? '#ffffff'))) . ';font-family:' . NordicblocksDesignBlockTypography::resolveCssStack((string) ($props['fontFamily'] ?? 'montserrat')) . ';font-size:' . (float) ($props['fontSize'] ?? 16) . 'px;font-weight:' . (int) ($props['fontWeight'] ?? 700) . ';line-height:' . ((float) ($props['lineHeight'] ?? 120) / 100) . ';letter-spacing:' . (float) ($props['letterSpacing'] ?? 0) . 'px;text-transform:' . self::css((string) ($props['textTransform'] ?? 'none')) . ';background:' . self::buildButtonBackgroundCss($props, false) . ';transition:background ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1),color ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1),border-color ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1),transform ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1),box-shadow ' . (int) ($props['transitionDuration'] ?? 220) . 'ms cubic-bezier(0.22,1,0.36,1);';
            if ($button_shadow !== '') {
                $css .= 'box-shadow:' . self::css($button_shadow) . ';';
            }
        } elseif ($type === 'photo' || $type === 'svg') {
            $css .= 'overflow:hidden;';
        } elseif ($type === 'video') {
            $css .= 'overflow:hidden;background:#020617;';
        } elseif ($type === 'embed') {
            $css .= 'overflow:hidden;background:' . self::css((string) ($props['backgroundColor'] ?? '#ffffff')) . ';';
        } elseif ($type === 'object') {
            $object_shadow = self::buildObjectShadowCss($props);
            if ($object_shadow !== '') {
                $css .= 'box-shadow:' . self::css($object_shadow) . ';';
            }
            if (($props['shape'] ?? '') === 'line') {
                $css .= 'background:transparent;height:0;top:' . max(0, (int) round($height / 2)) . 'px;border-top:' . max(1, (int) ($props['borderWidth'] ?? 2)) . 'px solid ' . self::buildObjectStrokeColor($props) . ';';
            } else {
                $css .= 'background:' . self::buildObjectFillCss($props) . ';';
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
        if (!in_array($type, ['text', 'button', 'object', 'photo', 'svg'], true)) {
            return '';
        }

        $css = '';
        $hoverBackground = $type === 'button'
            ? self::buildButtonBackgroundCss($props, true)
            : self::buildGenericHoverBackgroundCss($props);

        if ($hoverBackground !== '') {
            $css .= 'background:' . $hoverBackground . ';';
        }

        if (!empty($props['hoverColor']) && in_array($type, ['text', 'button', 'icon'], true)) {
            $css .= 'color:' . self::css((string) $props['hoverColor']) . ';';
        }
        if (!empty($props['hoverBorderColor'])) {
            $css .= 'border-color:' . self::css((string) $props['hoverBorderColor']) . ';';
        }
        $hover_shadow = self::buildHoverShadowCss($props, $type);
        if ($hover_shadow !== '') {
            $css .= 'box-shadow:' . self::css($hover_shadow) . ';';
        }
        if ($type === 'button' && (!empty($props['hoverIconColor']) || !empty($props['hoverColor']))) {
            $css .= '--nb-design-button-icon-color:' . self::css((string) ($props['hoverIconColor'] ?? ($props['hoverColor'] ?? ''))) . ';';
        }
        if ((float) ($props['hoverLift'] ?? 0) > 0 || (float) ($props['hoverScalePct'] ?? 100) !== 100.0) {
            $css .= '--nb-design-hover-transform:' . self::buildHoverTransform($props) . ';';
        }

        if ($css === '') {
            return '';
        }

        return $selector . ':hover,' . $selector . ':focus-within{' . $css . '}';
    }

    private static function buildGenericHoverBackgroundCss(array $props) {
        $mode = (string) ($props['hoverBackgroundMode'] ?? 'inherit');

        if ($mode === 'gradient') {
            $angle = (float) ($props['gradientAngle'] ?? 135);
            $from = (string) ($props['hoverGradientFrom'] ?? ($props['gradientFrom'] ?? ($props['backgroundColor'] ?? '#0f172a')));
            $to = (string) ($props['hoverGradientTo'] ?? ($props['gradientTo'] ?? ($props['backgroundColor'] ?? '#1d4ed8')));
            return 'linear-gradient(' . $angle . 'deg,' . self::css($from) . ' 0%,' . self::css($to) . ' 100%)';
        }

        if ($mode === 'solid' && !empty($props['hoverBackgroundColor'])) {
            return self::css((string) $props['hoverBackgroundColor']);
        }

        return '';
    }

    private static function buildHoverTransform(array $props) {
        $parts = [];
        $lift = (float) ($props['hoverLift'] ?? 0);
        $scale = max(0.9, min(1.2, ((float) ($props['hoverScalePct'] ?? 100)) / 100));

        if ($lift > 0) {
            $parts[] = 'translate3d(0,-' . $lift . 'px,0)';
        }

        if ($scale !== 1.0) {
            $parts[] = 'scale(' . $scale . ')';
        }

        return $parts ? implode(' ', $parts) : 'none';
    }

    private static function buildHoverShadowCss(array $props, $type) {
        if (!empty($props['hoverShadow'])) {
            return (string) $props['hoverShadow'];
        }

        if (
            empty($props['hoverShadowColor'])
            && (float) ($props['hoverShadowBlur'] ?? 0) <= 0
            && (float) ($props['hoverShadowSpread'] ?? 0) === 0.0
            && (float) ($props['hoverShadowX'] ?? 0) === 0.0
            && (float) ($props['hoverShadowY'] ?? 0) === 0.0
        ) {
            return $type === 'button' ? self::buildButtonShadowCss($props, true) : '';
        }

        return self::buildShadowCss(
            (float) ($props['hoverShadowX'] ?? 0),
            (float) ($props['hoverShadowY'] ?? 0),
            (float) ($props['hoverShadowBlur'] ?? 0),
            (float) ($props['hoverShadowSpread'] ?? 0),
            (string) ($props['hoverShadowColor'] ?? 'rgba(15,23,42,.24)'),
            !empty($props['hoverShadowInset'])
        );
    }

    private static function buildMotionTransformCss(array $props) {
        $preset = (string) ($props['motionPreset'] ?? 'fade-up');
        $amount = max(0, (float) ($props['motionAmount'] ?? 32));

        if ($preset === 'fade-down') {
            return 'translate3d(0,-' . $amount . 'px,0)';
        }
        if ($preset === 'slide-left') {
            return 'translate3d(' . $amount . 'px,0,0)';
        }
        if ($preset === 'slide-right') {
            return 'translate3d(-' . $amount . 'px,0,0)';
        }
        if ($preset === 'zoom-in') {
            return 'scale(' . max(0.72, 1 - min(0.28, $amount / 200)) . ')';
        }
        if ($preset === 'soft-pop') {
            return 'translate3d(0,' . round($amount * 0.4, 2) . 'px,0) scale(0.96)';
        }

        return 'translate3d(0,' . $amount . 'px,0)';
    }

    private static function resolveMotionEasingCss($easing) {
        if ($easing === 'soft') {
            return 'cubic-bezier(0.16,1,0.3,1)';
        }
        if ($easing === 'snappy') {
            return 'cubic-bezier(0.2,0.8,0.2,1)';
        }
        if ($easing === 'linear') {
            return 'linear';
        }

        return 'cubic-bezier(0.22,1,0.36,1)';
    }

    private static function buildShadowCss($x, $y, $blur, $spread, $color, $inset) {
        return ($inset ? 'inset ' : '')
            . $x . 'px '
            . $y . 'px '
            . $blur . 'px '
            . $spread . 'px '
            . self::css((string) $color);
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

    private static function buildButtonShadowCss(array $props, $hover = false) {
        $prefix = $hover ? 'hoverShadow' : 'shadow';
        $raw = trim((string) ($hover ? ($props['hoverShadow'] ?? '') : ($props['boxShadow'] ?? '')));
        $x = (float) ($props[$prefix . 'X'] ?? 0);
        $y = (float) ($props[$prefix . 'Y'] ?? 0);
        $blur = max(0, (float) ($props[$prefix . 'Blur'] ?? 0));
        $spread = (float) ($props[$prefix . 'Spread'] ?? 0);
        $color = trim((string) ($props[$prefix . 'Color'] ?? ''));
        $inset = !empty($props[$prefix . 'Inset']);

        if ($inset || $x !== 0.0 || $y !== 0.0 || $blur !== 0.0 || $spread !== 0.0 || $color !== '') {
            if ($color === '') {
                $color = $hover ? (string) ($props['shadowColor'] ?? 'rgba(15,23,42,0.18)') : 'rgba(15,23,42,0.18)';
            }

            return ($inset ? 'inset ' : '') . $x . 'px ' . $y . 'px ' . $blur . 'px ' . $spread . 'px ' . self::css($color);
        }

        return $raw;
    }

    private static function buildButtonHoverTransform(array $props) {
        $lift = max(0, (float) ($props['hoverLift'] ?? 0));
        $scale = max(0.9, min(1.2, ((float) ($props['hoverScalePct'] ?? 100) / 100)));

        return 'translateY(' . (-$lift) . 'px) scale(' . round($scale, 3) . ')';
    }

    private static function buildBackdropFilterCss(array $props) {
        $parts = [];
        $blur = (float) ($props['backdropBlur'] ?? 0);
        $saturate = (float) ($props['backdropSaturate'] ?? 100);
        $brightness = (float) ($props['backdropBrightness'] ?? 100);

        if ($blur > 0.0) {
            $parts[] = 'blur(' . $blur . 'px)';
        }
        if ($saturate !== 100.0) {
            $parts[] = 'saturate(' . $saturate . '%)';
        }
        if ($brightness !== 100.0) {
            $parts[] = 'brightness(' . $brightness . '%)';
        }

        return implode(' ', $parts);
    }

    private static function resolveObjectFillAlpha(array $props) {
        $raw_alpha = max(0, min(100, (float) ($props['fillOpacityPct'] ?? 100))) / 100;
        $has_backdrop_effect = (float) ($props['backdropBlur'] ?? 0) > 0
            || (float) ($props['backdropSaturate'] ?? 100) !== 100.0
            || (float) ($props['backdropBrightness'] ?? 100) !== 100.0;

        if (!$has_backdrop_effect || $raw_alpha <= 0.0 || $raw_alpha >= 0.999) {
            return $raw_alpha;
        }

        return min(1, round(0.12 + (0.88 * pow($raw_alpha, 0.72)), 3));
    }

    private static function buildObjectFillCss(array $props) {
        $mode = (string) ($props['backgroundMode'] ?? 'solid');
        $alpha = self::resolveObjectFillAlpha($props);
        $color = self::withAlpha((string) ($props['backgroundColor'] ?? ($props['fill'] ?? '#dbeafe')), $alpha);

        if ($mode === 'gradient') {
            $angle = (float) ($props['gradientAngle'] ?? 135);
            $from = self::withAlpha((string) ($props['gradientFrom'] ?? ($props['backgroundColor'] ?? ($props['fill'] ?? '#dbeafe'))), $alpha);
            $to = self::withAlpha((string) ($props['gradientTo'] ?? '#60a5fa'), $alpha);

            return 'linear-gradient(' . $angle . 'deg,' . self::css($from) . ',' . self::css($to) . ')';
        }

        return self::css($color);
    }

    private static function buildObjectStrokeColor(array $props) {
        if (!empty($props['borderColor'])) {
            return self::css((string) $props['borderColor']);
        }

        if (($props['backgroundMode'] ?? 'solid') === 'gradient') {
            return self::css((string) ($props['gradientFrom'] ?? ($props['backgroundColor'] ?? ($props['fill'] ?? '#dbeafe'))));
        }

        return self::css((string) ($props['backgroundColor'] ?? ($props['fill'] ?? '#dbeafe')));
    }

    private static function buildObjectShadowCss(array $props) {
        $raw = trim((string) ($props['boxShadow'] ?? ''));
        $x = (float) ($props['shadowX'] ?? 0);
        $y = (float) ($props['shadowY'] ?? 0);
        $blur = max(0, (float) ($props['shadowBlur'] ?? 0));
        $spread = (float) ($props['shadowSpread'] ?? 0);
        $color = trim((string) ($props['shadowColor'] ?? ''));
        $inset = !empty($props['shadowInset']);

        if ($inset || $x !== 0.0 || $y !== 0.0 || $blur !== 0.0 || $spread !== 0.0 || $color !== '') {
            if ($color === '') {
                $color = 'rgba(15,23,42,0.18)';
            }

            return ($inset ? 'inset ' : '') . $x . 'px ' . $y . 'px ' . $blur . 'px ' . $spread . 'px ' . self::css($color);
        }

        return $raw;
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