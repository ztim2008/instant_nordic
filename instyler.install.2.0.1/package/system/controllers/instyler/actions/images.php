<?php

class actionInstylerImages extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $config = cmsConfig::getInstance();

        $perpage = 15;
        $page = $this->request->get('page', 1);

        $this->model->orderBy('id', 'desc');

        $total = $this->model->getImagesCount();

        $this->model->limitPage($page, $perpage);

        $images = $this->model->getImages();

        foreach($images as $id => $image){
            unset($images[$id]['path']);
            $images[$id]['url'] = $config->upload_root . $image['path'];
            $images[$id]['is_pattern'] = (bool)$image['is_pattern'];
        }

        cmsTemplate::getInstance()->renderJSON(array(
            'images' => $images ? $images : array(),
            'total' => $total,
            'perpage' => $perpage
        ));

    }

}
