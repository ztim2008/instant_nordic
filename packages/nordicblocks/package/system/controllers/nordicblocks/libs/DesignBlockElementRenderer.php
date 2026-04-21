<?php

class NordicblocksDesignBlockElementRenderer {

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

    public static function render(array $payload) {
        return self::renderElements((array) ($payload['elements'] ?? []));
    }

    private static function renderElements(array $elements) {
        $html = '';

        foreach ($elements as $element) {
            if (!is_array($element) || empty($element['id'])) {
                continue;
            }

            $html .= self::renderElement($element);
        }

        return $html;
    }

    private static function renderElement(array $element) {
        $id = (string) ($element['id'] ?? '');
        $type = self::normalizeType((string) ($element['type'] ?? 'text'));
        $props = is_array($element['desktop']['props'] ?? null) ? $element['desktop']['props'] : [];
        $classes = 'nb-design-el nb-design-el--' . self::attr($type);
        $attrs = ' class="' . $classes . '" data-el-id="' . self::attr($id) . '" data-nb-entity="element:' . self::attr($id) . '"';

        if ($type === 'text') {
            $tag = self::selectTag((string) ($props['tag'] ?? 'div'));
            return '<' . $tag . $attrs . '>' . nl2br(self::escape((string) ($props['text'] ?? ''))) . '</' . $tag . '>';
        }

        if ($type === 'button') {
            $url = trim((string) ($props['url'] ?? '#'));
            $target = !empty($props['targetBlank']) ? ' target="_blank" rel="noopener noreferrer"' : '';
            $icon_markup = self::renderIconMarkup((string) ($props['iconClass'] ?? ''));
            $icon_position = (($props['iconPosition'] ?? 'start') === 'end') ? 'end' : 'start';
            $label = '<span class="nb-design-button__label">' . nl2br(self::escape((string) ($props['text'] ?? 'Подробнее'))) . '</span>';
            $icon = $icon_markup !== '' ? '<span class="nb-design-button__icon" aria-hidden="true">' . $icon_markup . '</span>' : '';
            $content = $icon !== '' && $icon_position === 'end' ? $label . $icon : $icon . $label;

            return '<div' . $attrs . '><a class="nb-design-button__link" href="' . self::attr($url !== '' ? $url : '#') . '"' . $target . '>' . $content . '</a></div>';
        }

        if ($type === 'photo' || $type === 'svg') {
            $src = trim((string) ($props['src'] ?? ''));
            $alt = (string) ($props['alt'] ?? '');
            $style = ' style="' . self::attr(self::buildImageStyle($props)) . '"';
            return '<div' . $attrs . '>' . ($src !== '' ? '<img src="' . self::attr($src) . '" alt="' . self::attr($alt) . '" loading="lazy"' . $style . '>' : '') . '</div>';
        }

        if ($type === 'video') {
            $src = trim((string) ($props['src'] ?? ''));
            $poster = trim((string) ($props['poster'] ?? ''));
            $controls = !empty($props['controls']) ? ' controls' : '';
            $autoplay = !empty($props['autoplay']) ? ' autoplay' : '';
            $muted = !empty($props['muted']) ? ' muted' : '';
            $loop = !empty($props['loop']) ? ' loop' : '';
            return '<div' . $attrs . '>' . ($src !== '' ? '<video src="' . self::attr($src) . '" poster="' . self::attr($poster) . '" playsinline' . $controls . $autoplay . $muted . $loop . '></video>' : '') . '</div>';
        }

        if ($type === 'icon') {
            return '<div' . $attrs . '>' . self::renderIconMarkup((string) ($props['iconClass'] ?? 'fas fa-star')) . '</div>';
        }

        if ($type === 'container') {
            return '<div' . $attrs . '>' . self::renderElements((array) ($element['children'] ?? [])) . '</div>';
        }

        return '<div' . $attrs . '></div>';
    }

    private static function selectTag($tag) {
        return in_array($tag, ['div', 'p', 'span', 'h1', 'h2', 'h3', 'h4'], true) ? $tag : 'div';
    }

    private static function sanitizeIconClass($value) {
        return trim(preg_replace('/[^a-z0-9_\-: ]/i', '', (string) $value));
    }

    private static function parseIconToken($value) {
        if (!preg_match('/^([a-z0-9_\-]+):([a-z0-9_\-]+)(?::.*)?$/i', trim((string) $value), $matches)) {
            return null;
        }

        return [
            'file' => $matches[1],
            'name' => $matches[2],
        ];
    }

    private static function renderIconMarkup($value) {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $icon_token = self::parseIconToken($value);
        if ($icon_token && function_exists('html_svg_icon')) {
            $svg = html_svg_icon($icon_token['file'], $icon_token['name'], 16, false);

            if (is_string($svg) && $svg !== '') {
                return $svg;
            }
        }

        $icon_class = self::sanitizeIconClass($value);
        return $icon_class !== '' ? '<i class="' . self::attr($icon_class) . '"></i>' : '';
    }

    private static function buildImageStyle(array $props) {
        $style = 'width:100%;height:100%;display:block;object-fit:' . (string) ($props['objectFit'] ?? 'cover') . ';object-position:' . self::buildImageObjectPosition($props) . ';';
        $filter = self::buildImageFilter($props);

        if ($filter !== '') {
            $style .= 'filter:' . $filter . ';';
        }

        return $style;
    }

    private static function buildImageObjectPosition(array $props) {
        if (array_key_exists('objectPositionX', $props) || array_key_exists('objectPositionY', $props)) {
            $x = max(0, min(100, (float) ($props['objectPositionX'] ?? 50)));
            $y = max(0, min(100, (float) ($props['objectPositionY'] ?? 50)));

            return $x . '% ' . $y . '%';
        }

        return (string) ($props['objectPosition'] ?? 'center center');
    }

    private static function buildImageFilter(array $props) {
        $parts = [];

        if ((float) ($props['filterBrightness'] ?? 100) !== 100.0) {
            $parts[] = 'brightness(' . (float) $props['filterBrightness'] . '%)';
        }
        if ((float) ($props['filterContrast'] ?? 100) !== 100.0) {
            $parts[] = 'contrast(' . (float) $props['filterContrast'] . '%)';
        }
        if ((float) ($props['filterSaturate'] ?? 100) !== 100.0) {
            $parts[] = 'saturate(' . (float) $props['filterSaturate'] . '%)';
        }
        if ((float) ($props['filterGrayscale'] ?? 0) > 0.0) {
            $parts[] = 'grayscale(' . (float) $props['filterGrayscale'] . '%)';
        }

        return implode(' ', $parts);
    }

    private static function escape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private static function attr($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}