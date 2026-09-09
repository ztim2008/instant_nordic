<?php

/**
 * Общая логика design-patch для AI-агента.
 */
class nordicaiPatchService {

    public static function clientFromOptions(array $options) {
        cmsCore::includeFile('system/controllers/nordicai/libs/DeepSeekClient.php');
        return new nordicaiDeepSeekClient($options);
    }

    public static function buildSystemPrompt(array $options) {

        $allow_css = !empty($options['allow_scoped_css']);
        $strict    = !isset($options['strict_tokens_only']) || !empty($options['strict_tokens_only']);

        $lines = [
            'Ты — design-patch агент для InstantCMS (шаблон Modern / Nordic).',
            'Верни ТОЛЬКО JSON-объект без markdown и без пояснений.',
            'Формат:',
            '{',
            '  "tokens": { "--token-name": "value" },',
            '  "css": "scoped css only",',
            '  "notes": ["short note"]',
            '}',
            'Правила:',
            '1) Нельзя менять PHP, tpl, JS, HTML структуру.',
            '2) Нельзя предлагать правки ядра InstantCMS.',
            '3) Не используй !important без крайней необходимости.',
            '4) Не трогай layout-классы Bootstrap (.row/.col-*/.container) как display/float/position.',
            '5) Предпочитай CSS variables (tokens), а не hardcoded hex на каждый элемент.',
            '6) css должен быть scoped (селектор из user context или :root для tokens).',
            '7) Сохраняй бренд Nordic: --nordic-accent это красный (#b42318 семейство), не синий Bootstrap.',
        ];

        if ($strict) {
            $lines[] = '8) STRICT: сначала заполняй tokens; css только если tokens недостаточно.';
        }
        if (!$allow_css) {
            $lines[] = '9) css должен быть пустой строкой. Разрешены только tokens.';
        }

        $lines[] = 'Известные tokens: --nordic-bg, --nordic-surface, --nordic-ink, --nordic-muted, --nordic-accent, --nordic-accent-strong, --nordic-line, --nordic-radius-md, --nordic-radius-sm, --nordic-shadow-card, --nordic-font-ui, --nordic-font-body, --primary.';

        return implode("\n", $lines);
    }

    public static function buildUserPrompt($prompt, $selector, $context) {
        $parts = ['Задача: ' . $prompt];
        if ($selector !== '') {
            $parts[] = 'Целевой селектор: ' . $selector;
        }
        if ($context !== '') {
            $parts[] = 'Контекст DOM/CSS: ' . mb_substr($context, 0, 4000);
        }
        return implode("\n", $parts);
    }

    public static function parsePatch($content, array $options) {

        $json = $content;
        if (preg_match('/\{[\s\S]*\}/', $content, $m)) {
            $json = $m[0];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return ['ok' => false, 'error' => 'bad_json'];
        }

        $tokens = [];
        if (!empty($data['tokens']) && is_array($data['tokens'])) {
            foreach ($data['tokens'] as $k => $v) {
                $k = trim((string) $k);
                $v = trim((string) $v);
                if ($k === '' || $v === '') {
                    continue;
                }
                if ($k[0] !== '-') {
                    $k = '--' . ltrim($k, '-');
                }
                if (!preg_match('/^--[a-zA-Z0-9_-]+$/', $k)) {
                    continue;
                }
                if (preg_match('/expression|javascript:|@import/i', $v)) {
                    continue;
                }
                $tokens[$k] = $v;
            }
        }

        $css = '';
        $warnings = [];
        if (!empty($options['allow_scoped_css'])) {
            $css = trim((string) ($data['css'] ?? ''));
            if ($css !== '' && preg_match('/@import|expression\s*\(|javascript:/i', $css)) {
                $warnings[] = 'unsafe_css_stripped';
                $css = '';
            }
        }

        if (!$tokens && $css === '') {
            return ['ok' => false, 'error' => 'empty_patch'];
        }

        return [
            'ok'       => true,
            'warnings' => $warnings,
            'patch'    => [
                'tokens' => $tokens,
                'css'    => $css,
                'notes'  => array_values(array_filter((array) ($data['notes'] ?? []))),
            ],
        ];
    }

    public static function patchToCss(array $patch) {

        $chunks = [];
        $tokens = $patch['tokens'] ?? [];
        if ($tokens) {
            $lines = [];
            foreach ($tokens as $k => $v) {
                $lines[] = '  ' . $k . ': ' . $v . ';';
            }
            $chunks[] = ":root {\n" . implode("\n", $lines) . "\n}";
        }

        $css = trim((string) ($patch['css'] ?? ''));
        if ($css !== '') {
            $chunks[] = $css;
        }

        return implode("\n\n", $chunks);
    }

    public static function generate(array $options, $prompt, $selector = '', $context = '') {

        $client = self::clientFromOptions($options);
        if (!$client->isConfigured()) {
            return ['ok' => false, 'error' => 'api_key_missing'];
        }

        $result = $client->chat([
            ['role' => 'system', 'content' => self::buildSystemPrompt($options)],
            ['role' => 'user', 'content' => self::buildUserPrompt($prompt, $selector, $context)],
        ]);

        if (empty($result['ok'])) {
            return ['ok' => false, 'error' => $result['error'] ?? 'deepseek_error', 'raw' => $result['raw'] ?? null];
        }

        $parsed = self::parsePatch((string) $result['content'], $options);
        if (empty($parsed['ok'])) {
            return [
                'ok'          => false,
                'error'       => $parsed['error'] ?? 'parse_error',
                'raw_content' => $result['content'],
            ];
        }

        return [
            'ok'          => true,
            'patch'       => $parsed['patch'],
            'css_text'    => self::patchToCss($parsed['patch']),
            'raw_content' => $result['content'],
            'warnings'    => $parsed['warnings'] ?? [],
        ];
    }

}
