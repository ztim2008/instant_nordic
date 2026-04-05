<?php

class actionInstylerUpload extends cmsAction{

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

        if (!$uploader->isImage($result['path'])){
            $result['success'] = false;
            $result['error']   = LANG_UPLOAD_ERR_MIME;
            cmsTemplate::getInstance()->renderJSON(array(
                'success' => false,
                'error' => $result['error']
            ));
            $this->halt();
        }

        $image = array(
            'title' => $result['name'],
            'path' => $result['url'],
        );

        $image['id'] = $this->model->addImage($image);

        $image['url'] = $config->upload_root . $image['path'];

        unset($result['path']);

        cmsTemplate::getInstance()->renderJSON(array(
            'success' => true,
            'image' => $image
        ));

    }

}
