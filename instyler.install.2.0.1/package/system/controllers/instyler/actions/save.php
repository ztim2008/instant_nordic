<?php

class actionInstylerSave extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $selector = json_decode($this->request->get('selector'), true);

        $id = $selector['id'];

        if (!is_numeric($id)){
            cmsCore::error404();
        }

        if (!$this->model->getSelector($id)){
            cmsCore::error404();
        }

        $this->model->updateSelector($id, $selector);

        cmsTemplate::getInstance()->renderJSON(array(
            'success' => true,
        ));

    }

}
