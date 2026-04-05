<?php

class actionInstylerDeleteImage extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $id = $this->request->get('id');

        if (!is_numeric($id)){
            cmsCore::error404();
        }

        $image = $this->model->getImage($id);

        if (!$image){
            cmsCore::error404();
        }

        $path = cmsConfig::get('upload_path') . $image['path'];
        
        @unlink($path);

        $success = !file_exists($path);
        
        if ($success) { $this->model->deleteImage($id); }

        cmsTemplate::getInstance()->renderJSON(array(
            'success' => $success
        ));

    }

}
