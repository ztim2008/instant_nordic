<?php

class actionInstylerExport extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $selectors = array();

        $total = $this->model->getSelectorsCount();

        $exportedSelectors = array();

        if ($total){

            $selectors = $this->model->getSelectors();

            foreach($selectors as $id => $selector){

                unset($selector['id']);
                unset($selector['ordering']);

                $selector['styles'] = cmsModel::yamlToArray($selector['styles']);
                $selector['is_important'] = (bool)$selector['is_important'];
                $selector['is_enabled'] = (bool)$selector['is_enabled'];

                $exportedSelectors[] = $selector;

            }

        }

        $filename = 'instyler-export.json';

        $is_pretty = true;

        if (!defined('JSON_PRETTY_PRINT')){
            define('JSON_PRETTY_PRINT', 1);
            $is_pretty = false;
        }

        $json = $is_pretty ? json_encode($exportedSelectors, JSON_PRETTY_PRINT) : json_encode($exportedSelectors);

        $file = cmsConfig::get('upload_path') . $filename;

        header("Content-Type: application/force-download");
        header("Content-Length: " . strlen($json));
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo $json;

        $this->halt();

    }

}
