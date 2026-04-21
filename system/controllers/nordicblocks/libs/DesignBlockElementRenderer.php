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
            $icon_class = self::sanitizeIconClass((string) ($props['iconClass'] ?? ''));
            $icon_position = (($props['iconPosition'] ?? 'start') === 'end') ? 'end' : 'start';
            $label = '<span class="nb-design-button__label">' . nl2br(self::escape((string) ($props['text'] ?? 'Подробнее'))) . '</span>';
            $icon = $icon_class !== '' ? '<span class="nb-design-button__icon" aria-hidden="true"><i class="' . self::attr($icon_class) . '"></i></span>' : '';
            $content = $icon !== '' && $icon_position === 'end' ? $label . $icon : $icon . $label;

            return '<div' . $attrs . '><a class="nb-design-button__link" href="' . self::attr($url !== '' ? $url : '#') . '"' . $target . '>' . $content . '</a></div>';
        }

        if ($type === 'photo' || $type === 'svg') {
            $src = trim((string) ($props['src'] ?? ''));
            $alt = (string) ($props['alt'] ?? '');
            $fit = (string) ($props['objectFit'] ?? 'cover');
            $position = (string) ($props['objectPosition'] ?? 'center center');
            $style = ' style="width:100%;height:100%;display:block;object-fit:' . self::attr($fit) . ';object-position:' . self::attr($position) . '"';
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
            $icon_class = preg_replace('/[^a-z0-9_\-: ]/i', '', (string) ($props['iconClass'] ?? 'fas fa-star'));
            return '<div' . $attrs . '><i class="' . self::attr($icon_class) . '"></i></div>';
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

    private static function escape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private static function attr($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}