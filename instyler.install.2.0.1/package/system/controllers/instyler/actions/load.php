<?php

class actionInstylerLoad extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $uri = $this->request->get('uri', '');

        $config = cmsConfig::getInstance();

        $selectors = array();

        $total = $this->model->getSelectorsCount();

        if ($total){

            $selectors = $this->model->getSelectors();

            foreach($selectors as $id => $selector){
                $selectors[$id]['styles'] = cmsModel::yamlToArray($selector['styles']);
                $selectors[$id]['custom'] = cmsModel::yamlToArray($selector['custom']);
                $selectors[$id]['is_important'] = (bool)$selector['is_important'];
                $selectors[$id]['is_enabled'] = (bool)$selector['is_enabled'];
            }

        }

        cmsTemplate::getInstance()->renderJSON(array(
            'selectors'  => $selectors,
            'total' => $total
        ));

    }

}
