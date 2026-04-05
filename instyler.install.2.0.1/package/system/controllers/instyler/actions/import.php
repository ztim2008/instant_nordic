<?php

class actionInstylerImport extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $config = cmsConfig::getInstance();

        $uploader = new cmsUploader();

        $result = $uploader->upload('file');

        if (!$result['success']){
            if(!empty($result['path'])){
                $uploader->remove($result['path']);
            }
            cmsTemplate::getInstance()->renderJSON(array(
                'success' => false,
                'error' => $result['error']
            ));
            $this->halt();
        }

        $json = file_get_contents($result['path']);

        $this->import($json);
        
        cmsTemplate::getInstance()->renderJSON(array(
            'success' => false,
            'error' => LANG_INSTYLER_IMPORT_ERROR_JSON,
        ));

    }

    private function import($json){

        $selectorScheme = array(
            'title',
            'path',
            'styles',
            'scope',
            'mask_pos',
            'mask_neg',
            'is_enabled',
            'is_important',
            'custom',
            'width'
        );

        $stylesScheme = array('base', 'hover', 'active');

        $dump = json_decode($json, true);

        if (!is_array($dump) || !count($dump)){
            cmsTemplate::getInstance()->renderJSON(array(
                'success' => false,
                'error' => LANG_INSTYLER_IMPORT_ERROR_JSON,
            ));
        }

        $selectorsAdded = 0;

        foreach ($dump as $selector){

            $fieldsFound = 0;

            foreach($selector as $field => $value){
                if (!in_array($field, $selectorScheme)){
                    unset($selector[$field]);
                    return;
                }
                $fieldsFound++;
            }

            if ($fieldsFound < count($selectorScheme)){
                continue;
            }

            $is_styles_valid = true;

            foreach($stylesScheme as $state){

                if (!isset($selector['styles'][$state])){
                    $is_styles_valid = false;
                    break;
                }

                if (!is_array($selector['styles'][$state])){
                    $selector['styles'][$state] = array();
                }

                foreach($selector['styles'][$state] as $style=>$value){

                    if (!preg_match('/^([a-z0-9\-]+)$/i', $style)){
                        $is_styles_valid = false;
                        break;
                    }

                }

                if (!$is_styles_valid) { break; }

            }

            if (!$is_styles_valid) { continue; }

            if ($this->model->addSelector($selector)){
                $selectorsAdded++;
            }

        }

        if ($selectorsAdded == 0){
            cmsTemplate::getInstance()->renderJSON(array(
                'success' => false,
                'error' => LANG_INSTYLER_IMPORT_ERROR_JSON,
            ));
        }

        cmsTemplate::getInstance()->renderJSON(array(
            'success' => true
        ));

    }

}
