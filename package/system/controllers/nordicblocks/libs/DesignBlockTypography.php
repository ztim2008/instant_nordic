<?php

class NordicblocksDesignBlockTypography {

    private static function catalog() {
        static $catalog = null;

        if ($catalog !== null) {
            return $catalog;
        }

        $catalog = [
            'system-ui' => [
                'label' => 'System UI',
                'family' => 'system-ui',
                'stack' => 'system-ui,-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif',
                'source' => 'system',
                'weights' => [400, 500, 700, 800],
                'subsets' => [],
            ],
            'montserrat' => [
                'label' => 'Montserrat',
                'family' => 'Montserrat',
                'stack' => '\'Montserrat\',sans-serif',
                'source' => 'local',
                'folder' => 'montserrat',
                'weights' => [400, 500, 700, 800],
                'subsets' => ['cyrillic-ext', 'cyrillic', 'latin-ext', 'latin'],
            ],
            'unbounded' => [
                'label' => 'Unbounded',
                'family' => 'Unbounded',
                'stack' => '\'Unbounded\',sans-serif',
                'source' => 'local',
                'folder' => 'unbounded',
                'weights' => [400, 500, 700, 800],
                'subsets' => ['cyrillic-ext', 'cyrillic', 'latin-ext', 'latin'],
            ],
            'play' => [
                'label' => 'Play',
                'family' => 'Play',
                'stack' => '\'Play\',sans-serif',
                'source' => 'local',
                'folder' => 'play',
                'weights' => [400, 700],
                'subsets' => ['cyrillic-ext', 'cyrillic', 'latin-ext', 'latin'],
            ],
            'philosopher' => [
                'label' => 'Philosopher',
                'family' => 'Philosopher',
                'stack' => '\'Philosopher\',serif',
                'source' => 'local',
                'folder' => 'philosopher',
                'weights' => [400, 700],
                'subsets' => ['cyrillic-ext', 'cyrillic', 'latin-ext', 'latin'],
            ],
            'playfair-display-sc' => [
                'label' => 'Playfair Display SC',
                'family' => 'Playfair Display SC',
                'stack' => '\'Playfair Display SC\',serif',
                'source' => 'local',
                'folder' => 'playfair-display-sc',
                'weights' => [400, 700, 900],
                'subsets' => ['cyrillic', 'latin-ext', 'latin'],
            ],
            'russo-one' => [
                'label' => 'Russo One',
                'family' => 'Russo One',
                'stack' => '\'Russo One\',sans-serif',
                'source' => 'local',
                'folder' => 'russo-one',
                'weights' => [400],
                'subsets' => ['cyrillic', 'latin-ext', 'latin'],
            ],
        ];

        return $catalog;
    }

    public static function getFontFamilyValues() {
        return array_keys(self::catalog());
    }

    public static function getFontFamilies() {
        $items = [];

        foreach (self::catalog() as $value => $entry) {
            $items[] = [
                'value' => $value,
                'label' => (string) ($entry['label'] ?? $value),
                'stack' => (string) ($entry['stack'] ?? 'system-ui,sans-serif'),
                'source' => (string) ($entry['source'] ?? 'system'),
            ];
        }

        return $items;
    }

    public static function normalizeFontFamily($value, $fallback = 'montserrat') {
        $value = trim((string) $value);
        $catalog = self::catalog();

        if ($value !== '' && isset($catalog[$value])) {
            return $value;
        }

        return isset($catalog[$fallback]) ? $fallback : 'system-ui';
    }

    public static function resolveCssStack($value) {
        $normalized = self::normalizeFontFamily($value);
        $catalog = self::catalog();

        return (string) ($catalog[$normalized]['stack'] ?? 'system-ui,sans-serif');
    }

    public static function buildCatalogFontFaceCss() {
        return self::buildFontFaceCss(self::getFontFamilyValues());
    }

    public static function buildFontFaceCss(array $families) {
        $catalog = self::catalog();
        $css = '';
        $seen = [];

        foreach ($families as $family_key) {
            $normalized = self::normalizeFontFamily($family_key);

            if (isset($seen[$normalized]) || !isset($catalog[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $entry = $catalog[$normalized];

            if (($entry['source'] ?? 'system') !== 'local') {
                continue;
            }

            $folder = (string) ($entry['folder'] ?? $normalized);
            $family = (string) ($entry['family'] ?? $entry['label'] ?? $normalized);
            foreach ((array) ($entry['weights'] ?? []) as $weight) {
                foreach ((array) ($entry['subsets'] ?? []) as $subset) {
                    $base = '/upload/nordicblocks/fonts/' . rawurlencode($folder) . '/' . rawurlencode($folder . '-' . $subset . '-' . $weight . '-normal');
                    $css .= '@font-face{font-family:\'' . self::escapeCssString($family) . '\';font-style:normal;font-weight:' . (int) $weight . ';font-display:swap;src:url("' . $base . '.woff2") format("woff2"),url("' . $base . '.woff") format("woff");}';
                }
            }
        }

        return $css;
    }

    private static function escapeCssString($value) {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $value);
    }
}