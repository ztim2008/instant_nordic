<?php

class nordicstyl extends cmsFrontend {

    protected const RULE_DEVICES = ['base', 'mobile', 'tablet', 'desktop'];
    protected const RULE_STATES = ['default', 'hover', 'active', 'focus', 'focus-visible', 'visited', 'before', 'after'];

    public function before($action_name) {
        parent::before($action_name);
        setlocale(LC_NUMERIC, 'en_US.UTF-8');
    }

    public function buildRuleCSS(array $rule): string {

        $selector = trim((string)($rule['path'] ?? ''));
        if ($selector === '') {
            return '';
        }

        $stylesRaw = $rule['styles'] ?? '';
        $customRaw = $rule['custom'] ?? '';

        $styles = is_array($stylesRaw) ? $stylesRaw : cmsModel::yamlToArray((string)$stylesRaw);
        $custom = is_array($customRaw) ? $customRaw : cmsModel::yamlToArray((string)$customRaw);

        $important = !empty($rule['is_important']);

        $stylesByDevice = $this->normalizeStyleBranches(is_array($styles) ? $styles : []);
        $customByDevice = $this->normalizeCustomBranches(is_array($custom) ? $custom : []);

        $devices = $this->collectOrderedKeys(array_merge(array_keys($stylesByDevice), array_keys($customByDevice)), self::RULE_DEVICES, 'base');

        if (!$devices) {
            $devices = ['base'];
        }

        $css = '';

        foreach ($devices as $device) {

            $deviceStyles = $stylesByDevice[$device] ?? [];
            $deviceCustom = $customByDevice[$device] ?? [];

            $states = $this->collectOrderedKeys(array_merge(array_keys($deviceStyles), array_keys($deviceCustom)), self::RULE_STATES, 'default');

            if (!$states) {
                $states = ['default'];
            }

            $deviceCss = '';

            foreach ($states as $state) {

                $stateStyles = isset($deviceStyles[$state]) && is_array($deviceStyles[$state]) ? $deviceStyles[$state] : [];
                $stateCustom = isset($deviceCustom[$state]) ? (string)$deviceCustom[$state] : '';

                if (!$stateStyles && trim($stateCustom) === '') {
                    continue;
                }

                $stateCss = $this->buildStateCSS($selector, $state, $stateStyles, $stateCustom, $important);

                if ($stateCss !== '') {
                    $deviceCss .= $stateCss;
                }
            }

            if ($deviceCss !== '') {
                $css .= $this->wrapDeviceCSS($device, $deviceCss);
            }
        }

        return $css;
    }

    protected function normalizeStyleBranches(array $styles): array {

        if (!$styles) {
            return [];
        }

        if ($this->isDeviceAwarePayload($styles)) {
            $normalized = [];

            foreach ($styles as $device => $states) {
                if (!is_array($states)) {
                    continue;
                }

                foreach ($states as $state => $declarations) {
                    if (!is_array($declarations)) {
                        continue;
                    }

                    $normalized[$device][$state] = $declarations;
                }
            }

            return $normalized;
        }

        return ['base' => array_filter($styles, 'is_array')];
    }

    protected function normalizeCustomBranches(array $custom): array {

        if (!$custom) {
            return [];
        }

        if ($this->isDeviceAwarePayload($custom)) {
            $normalized = [];

            foreach ($custom as $device => $states) {
                if (!is_array($states)) {
                    continue;
                }

                foreach ($states as $state => $css) {
                    if (is_array($css)) {
                        continue;
                    }

                    $normalized[$device][$state] = (string)$css;
                }
            }

            return $normalized;
        }

        $normalized = [];
        foreach ($custom as $state => $css) {
            if (is_array($css)) {
                continue;
            }
            $normalized['base'][$state] = (string)$css;
        }

        return $normalized;
    }

    protected function isDeviceAwarePayload(array $payload): bool {

        if (!$payload) {
            return false;
        }

        $hasKnownDevice = false;
        $hasKnownState = false;

        foreach (array_keys($payload) as $key) {
            $key = (string)$key;
            if (in_array($key, self::RULE_DEVICES, true)) {
                $hasKnownDevice = true;
            }
            if (in_array($key, self::RULE_STATES, true)) {
                $hasKnownState = true;
            }
        }

        return $hasKnownDevice && !$hasKnownState;
    }

    protected function collectOrderedKeys(array $keys, array $preferredOrder, string $fallback): array {

        $keys = array_values(array_unique(array_filter(array_map('strval', $keys), function($key) {
            return $key !== '';
        })));

        if (!$keys) {
            return [$fallback];
        }

        $ordered = [];

        foreach ($preferredOrder as $preferredKey) {
            if (in_array($preferredKey, $keys, true)) {
                $ordered[] = $preferredKey;
            }
        }

        foreach ($keys as $key) {
            if (!in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }

        return $ordered;
    }

    protected function buildStateCSS(string $selector, string $state, array $stateStyles, string $stateCustom, bool $important): string {

        $resolvedSelector = $this->buildSelectorForState($selector, $state);
        if ($resolvedSelector === '') {
            return '';
        }

        $css = $resolvedSelector . '{';

        foreach ($stateStyles as $prop => $value) {
            $prop = trim((string)$prop);
            $value = trim((string)$value);
            if ($prop === '' || $value === '') {
                continue;
            }
            $css .= $prop . ':' . $value . ($important ? ' !important' : '') . ';';
        }

        $stateCustom = trim($stateCustom);
        if ($stateCustom !== '') {
            $customLine = str_replace(["\r", "\n"], ' ', $stateCustom);
            if ($important) {
                $customLine = str_replace(';', ' !important;', $customLine);
            }
            $css .= trim($customLine);
        }

        $css .= "}\n";

        return $css;
    }

    protected function buildSelectorForState(string $selector, string $state): string {

        $state = trim($state);

        if ($state === '' || $state === 'default' || $state === 'normal') {
            return $selector;
        }

        $pseudoStates = [
            'hover' => ':hover',
            'active' => ':active',
            'focus' => ':focus',
            'focus-visible' => ':focus-visible',
            'visited' => ':visited',
            'before' => '::before',
            'after' => '::after'
        ];

        if (!isset($pseudoStates[$state])) {
            return $selector;
        }

        return $selector . $pseudoStates[$state];
    }

    protected function wrapDeviceCSS(string $device, string $css): string {

        $css = trim($css);
        if ($css === '') {
            return '';
        }

        $mediaMap = [
            'mobile' => '@media (max-width: 767.98px)',
            'tablet' => '@media (min-width: 768px) and (max-width: 991.98px)',
            'desktop' => '@media (min-width: 992px)'
        ];

        if (!isset($mediaMap[$device])) {
            return $css . "\n";
        }

        return $mediaMap[$device] . "{\n" . $css . "}\n";
    }
}
