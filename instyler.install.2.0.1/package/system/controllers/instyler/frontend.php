<?php

class instyler extends cmsFrontend {

    const SCOPE_GLOBAL = 1;
    const SCOPE_HOME = 2;
    const SCOPE_CUSTOM = 3;

    const SCOPE_TYPE_ACTIVE = 1;
    const SCOPE_TYPE_GLOBAL = 2;
    const SCOPE_TYPE_INACTIVE = 3;

    public function before($action_name) {
        parent::before($action_name);
        setlocale(LC_NUMERIC, 'en_US.UTF-8');
    }

    public function buildSelectorCSS($selector){

        if (empty($selector['styles'])){ return ''; }

        $styles = is_array($selector['styles']) ? $selector['styles'] : cmsModel::yamlToArray($selector['styles']);
        $custom = is_array($selector['custom']) ? $selector['custom'] : cmsModel::yamlToArray($selector['custom']);

        if (!empty($selector['width'])){
            $media = '@media screen and (max-width:'.$selector['width'].'px){ $ }';
        }

        $css = '';

        foreach($styles as $state=>$styles){

            $is_styles = !empty($styles);
            $is_custom = !empty($custom[$state]);
            
            if (!$is_styles && !$is_custom) { continue; }

            $css .= $selector['path'];

            if ($state == 'hover') { $css .= ':hover'; }
            if ($state == 'active') { $css .= ':active'; }

            $css .= '{';

            if ($is_styles){
                foreach($styles as $style=>$value){
                    $css .= $style . ':' . $value . ($selector['is_important'] ? ' !important' : '') . ';';
                }
            }
            
            if ($is_custom){
                $custom_css = $custom[$state];
                if ($selector['is_important']){
                    $custom_css = str_replace(';', ' !important;', $custom_css);
                }
                $css .= trim(join('', explode("\n", $custom_css)));
            }

            $css .= '} ';

        }

        if ($selector['width']){
            $css = str_replace('$', $css, $media);
        }

        return $css;

    }

    public function getDevices(){

        return array(
            'desktop' => array(
                'title' => LANG_INSTYLER_DEVICE_DESKTOP,
                'icon' => 'desktop',
                'width' => 0,
            ),
            
            'tablet-h' => array(
                'title' => LANG_INSTYLER_DEVICE_TABLET_H,
                'icon' => 'tablet',
                'width' => 1024
            ),
            'tablet' => array(
                'title' => LANG_INSTYLER_DEVICE_TABLET,
                'icon' => 'tablet',
                'width' => 600
            ),
            'phone-h' => array(
                'title' => LANG_INSTYLER_DEVICE_PHONE_H,
                'icon' => 'mobile',
                'width' => 480
            ),
            'phone' => array(
                'title' => LANG_INSTYLER_DEVICE_PHONE,
                'icon' => 'mobile',
                'width' => 320
            ),
            
        );

    }

    public function isSelectorApplicable($selector, $uri){

        if ($selector['scope'] == instyler::SCOPE_GLOBAL){
            return true;
        }

        $uri = trim(trim($uri, '/'));

        if ($selector['scope'] == instyler::SCOPE_HOME){
            return $uri == '';
        }

        if (empty($selector['mask_pos'])) {
            $selector['mask_pos'] = '*';
        }

        $is_mask_match = false;
        $is_stop_match = false;

        if ($selector['mask_pos']) {
            $masks_pos = explode("\n", $selector['mask_pos']);
            foreach($masks_pos as $mask){
                $regular = string_mask_to_regular(trim($mask, '/'));
                $regular = "/^{$regular}$/iu";
                $is_mask_match = $is_mask_match || preg_match($regular, $uri);
            }
        }

        if (!empty($selector['mask_neg'])) {
            $masks_neg = explode("\n", $selector['mask_neg']);
            foreach($masks_neg as $mask){
                $regular = string_mask_to_regular(trim($mask, '/'));
                $regular = "/^{$regular}$/iu";
                $is_stop_match = $is_stop_match || preg_match($regular, $uri);
            }
        }

        return ($is_mask_match && !$is_stop_match);

    }

    public function getSelectorScopeType($selector, $uri){

        if ($selector['scope'] == instyler::SCOPE_GLOBAL) {
            return instyler::SCOPE_TYPE_GLOBAL;
        }

        if ($this->isSelectorApplicable($selector, $uri)){
            return instyler::SCOPE_TYPE_ACTIVE;
        }

        return instyler::SCOPE_TYPE_INACTIVE;

    }

}
