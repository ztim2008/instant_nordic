<?php

class actionInstylerDelete extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $id = $this->request->get('id');

        if (!is_numeric($id)){
            cmsCore::error404();
        }

        if (!$this->model->getSelector($id)){
            cmsCore::error404();
        }

        $this->model->deleteSelector($id);

        cmsTemplate::getInstance()->renderJSON(array(
            'success' => true
        ));

    }

}
