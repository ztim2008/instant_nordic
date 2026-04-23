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
        $attrs .= self::buildMotionAttrs($element);

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

        if ($type === 'embed') {
            return self::renderEmbedElement($attrs, $props);
        }

        if ($type === 'icon') {
            return '<div' . $attrs . '>' . self::renderIconMarkup((string) ($props['iconClass'] ?? 'fas fa-star')) . '</div>';
        }

        if ($type === 'container' || $type === 'group') {
            return '<div' . $attrs . '>' . self::renderElements((array) ($element['children'] ?? [])) . '</div>';
        }

        return '<div' . $attrs . '></div>';
    }

    private static function buildMotionAttrs(array $element) {
        $attrs = '';
        $has_motion = false;
        $has_sequence = false;
        $desktop_trigger = 'none';
        $desktop_preset = 'fade-up';

        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $props = is_array($element[$breakpoint]['props'] ?? null) ? $element[$breakpoint]['props'] : [];
            $motion_trigger = (string) ($props['motionTrigger'] ?? 'none');
            $motion_preset = (string) ($props['motionPreset'] ?? 'fade-up');
            $sequence_mode = (string) ($props['sequenceMode'] ?? 'none');
            $sequence_id = trim((string) ($props['sequenceId'] ?? ''));
            $sequence_step = (int) round((float) ($props['sequenceStep'] ?? 0));
            $sequence_gap = (int) round((float) ($props['sequenceGap'] ?? 80));
            $sequence_trigger = (string) ($props['sequenceTrigger'] ?? 'inherit');
            $sequence_replay = (string) ($props['sequenceReplay'] ?? 'once');

            if ($breakpoint === 'desktop') {
                $desktop_trigger = $motion_trigger;
                $desktop_preset = $motion_preset;
            }

            if (in_array($motion_trigger, ['entry', 'scroll'], true)) {
                $has_motion = true;
            }

            if ($sequence_mode === 'orchestrated' && $sequence_id !== '') {
                $has_sequence = true;
            }

            $attrs .= ' data-motion-trigger-' . $breakpoint . '="' . self::attr($motion_trigger) . '"';
            $attrs .= ' data-motion-preset-' . $breakpoint . '="' . self::attr($motion_preset) . '"';
            $attrs .= ' data-sequence-mode-' . $breakpoint . '="' . self::attr($sequence_mode) . '"';
            $attrs .= ' data-sequence-id-' . $breakpoint . '="' . self::attr($sequence_id) . '"';
            $attrs .= ' data-sequence-step-' . $breakpoint . '="' . self::attr((string) $sequence_step) . '"';
            $attrs .= ' data-sequence-gap-' . $breakpoint . '="' . self::attr((string) $sequence_gap) . '"';
            $attrs .= ' data-sequence-trigger-' . $breakpoint . '="' . self::attr($sequence_trigger) . '"';
            $attrs .= ' data-sequence-replay-' . $breakpoint . '="' . self::attr($sequence_replay) . '"';
        }

        if (!$has_motion) {
            return $attrs;
        }

        $attrs .= ' data-motion="1"';

        if ($has_sequence) {
            $attrs .= ' data-sequence="1"';
        }

        if (in_array($desktop_trigger, ['entry', 'scroll'], true)) {
            $attrs .= ' data-motion-active-trigger="' . self::attr($desktop_trigger) . '"';
            $attrs .= ' data-motion-active-preset="' . self::attr($desktop_preset) . '"';
        }

        return $attrs;
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

    private static function renderEmbedElement($attrs, array $props) {
        $frame_markup = self::buildEmbedFrameMarkup($props);

        if ($frame_markup === '') {
            return '<div' . $attrs . '><div class="nb-design-embed__placeholder">Добавьте HTML код или URL iframe в свойствах элемента</div></div>';
        }

        return '<div' . $attrs . '><div class="nb-design-embed__surface">' . $frame_markup . '</div></div>';
    }

    private static function buildEmbedFrameMarkup(array $props) {
        $title = trim((string) ($props['title'] ?? 'Встраиваемый блок'));
        $title = $title !== '' ? $title : 'Встраиваемый блок';
        $loading = !empty($props['lazy']) ? 'lazy' : 'eager';
        $sandbox = self::buildEmbedSandbox((string) ($props['sandboxProfile'] ?? 'strict'));
        $allow = self::buildEmbedAllow($props);
        $referrer_policy = trim((string) ($props['referrerPolicy'] ?? 'strict-origin-when-cross-origin'));
        $source_mode = self::resolveEmbedSourceMode($props);
        $provider = self::resolveEmbedProvider($props);
        $frame_attrs = ' class="nb-design-embed__frame" title="' . self::attr($title) . '" loading="' . self::attr($loading) . '" referrerpolicy="' . self::attr($referrer_policy) . '"';

        if ($sandbox !== '') {
            $frame_attrs .= ' sandbox="' . self::attr($sandbox) . '"';
        }

        if ($allow !== '') {
            $frame_attrs .= ' allow="' . self::attr($allow) . '"';
        }

        if (!empty($props['allowFullscreen'])) {
            $frame_attrs .= ' allowfullscreen';
        }

        if (!empty($props['hideScrollbars'])) {
            $frame_attrs .= ' scrolling="no"';
        }

        if ($source_mode === 'url') {
            $url = self::normalizeEmbedUrl((string) ($props['url'] ?? ''), $provider);

            if (!preg_match('~^https?://~i', $url)) {
                return '';
            }

            return '<iframe' . $frame_attrs . ' src="' . self::attr($url) . '"></iframe>';
        }

        $code = trim((string) ($props['code'] ?? ''));
        if ($code === '') {
            return '';
        }

        return '<iframe' . $frame_attrs . ' srcdoc="' . self::attr(self::buildEmbedSrcdoc($code, $title, !empty($props['hideScrollbars']))) . '"></iframe>';
    }

    private static function resolveEmbedSourceMode(array $props) {
        $source_mode = (string) ($props['sourceMode'] ?? 'html');

        if ($source_mode === 'url' && trim((string) ($props['url'] ?? '')) !== '') {
            return 'url';
        }

        if (trim((string) ($props['code'] ?? '')) !== '') {
            return 'html';
        }

        return $source_mode === 'url' ? 'url' : 'html';
    }

    private static function buildEmbedSandbox($profile) {
        $profiles = [
            'strict' => 'allow-scripts allow-popups',
            'forms' => 'allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox',
            'media' => 'allow-scripts allow-popups allow-presentation',
            'trusted' => 'allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox allow-presentation allow-downloads',
        ];

        return $profiles[$profile] ?? $profiles['strict'];
    }

    private static function buildEmbedAllow(array $props) {
        $allow = ['autoplay', 'clipboard-write', 'encrypted-media', 'picture-in-picture'];
        $profile = (string) ($props['sandboxProfile'] ?? 'strict');

        if (in_array($profile, ['media', 'trusted'], true) || !empty($props['allowFullscreen'])) {
            $allow[] = 'fullscreen';
        }

        if (in_array($profile, ['forms', 'trusted'], true)) {
            $allow[] = 'payment';
        }

        return implode('; ', array_values(array_unique($allow)));
    }

    private static function resolveEmbedProvider(array $props) {
        $provider = trim((string) ($props['provider'] ?? 'generic'));

        if (in_array($provider, ['rutube', 'vk_video', 'kinescope'], true)) {
            return $provider;
        }

        $source = trim((string) ($props['url'] ?? ''));
        if ($source === '') {
            $source = trim((string) ($props['code'] ?? ''));
        }

        if (preg_match('~rutube\.ru~i', $source)) {
            return 'rutube';
        }

        if (preg_match('~(?:vkvideo\.ru|vk\.com/video_ext\.php)~i', $source)) {
            return 'vk_video';
        }

        if (preg_match('~kinescope\.io~i', $source)) {
            return 'kinescope';
        }

        return 'generic';
    }

    private static function normalizeEmbedUrl($url, $provider) {
        $url = trim((string) $url);

        if ($url === '') {
            return '';
        }

        if ($provider === 'rutube') {
            if (preg_match('~rutube\.ru/(?:play/embed|video)/([a-z0-9_-]+)~i', $url, $matches)) {
                return 'https://rutube.ru/play/embed/' . $matches[1];
            }
        }

        if ($provider === 'kinescope') {
            if (preg_match('~kinescope\.io/(?:embed/)?([a-z0-9]+)~i', $url, $matches)) {
                return 'https://kinescope.io/embed/' . $matches[1];
            }
        }

        if ($provider === 'vk_video') {
            if (preg_match('~(?:vkvideo\.ru|vk\.com)/video_ext\.php\?([^\s"\']+)~i', $url, $matches)) {
                return 'https://vkvideo.ru/video_ext.php?' . $matches[1];
            }
        }

        return $url;
    }

    private static function buildEmbedSrcdoc($code, $title, $hide_scrollbars = false) {
        $body_overflow = $hide_scrollbars ? 'hidden' : 'auto';
        $scrollbar_css = $hide_scrollbars ? 'body{-ms-overflow-style:none;scrollbar-width:none;}body::-webkit-scrollbar{display:none;}' : '';

        return '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'
            . self::escape($title)
            . '</title><style>html,body{margin:0;padding:0;background:transparent;min-height:100%;}body{font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;overflow:' . $body_overflow . ';}' . $scrollbar_css . 'iframe{max-width:100%;}img,video{max-width:100%;height:auto;display:block;}</style></head><body>'
            . $code
            . '</body></html>';
    }

    private static function escape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private static function attr($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}