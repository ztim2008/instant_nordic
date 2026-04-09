<?php

class nordicstyl extends cmsFrontend {

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

        $states = [];
        if (is_array($styles)) {
            $states = array_keys($styles);
        }
        if (is_array($custom)) {
            $states = array_unique(array_merge($states, array_keys($custom)));
        }
        if (!$states) {
            $states = ['default'];
        }

        $css = '';

        foreach ($states as $state) {

            $stateStyles = (is_array($styles) && isset($styles[$state]) && is_array($styles[$state])) ? $styles[$state] : [];
            $stateCustom = (is_array($custom) && isset($custom[$state])) ? (string)$custom[$state] : '';

            if (!$stateStyles && trim($stateCustom) === '') {
                continue;
            }

            $css .= $selector;
            if ($state === 'hover') {
                $css .= ':hover';
            } elseif ($state === 'active') {
                $css .= ':active';
            }

            $css .= '{';

            foreach ($stateStyles as $prop => $value) {
                $prop = trim((string)$prop);
                $value = trim((string)$value);
                if ($prop === '' || $value === '') {
                    continue;
                }
                $css .= $prop . ':' . $value . ($important ? ' !important' : '') . ';';
            }

            if (trim($stateCustom) !== '') {
                $customLine = str_replace(["\r", "\n"], '', $stateCustom);
                if ($important) {
                    $customLine = str_replace(';', ' !important;', $customLine);
                }
                $css .= trim($customLine);
            }

            $css .= '}\n';
        }

        return $css;
    }
}
