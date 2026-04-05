<?php

class actionInstylerToggle extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $id = $this->request->get('id');

        if (!is_numeric($id)){
            cmsCore::error404();
        }

        if (!$this->model->getSelector($id)){
            cmsCore::error404();
        }

        $is_enabled = (int)$this->request->get('is_enabled');

        $this->model->updateSelector($id, array(
            'is_enabled' => $is_enabled
        ));

        cmsTemplate::getInstance()->renderJSON(array(
            'success' => true,
        ));

    }

}
