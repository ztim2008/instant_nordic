<?php

class actionInstylerMove extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $from = $this->request->get('from');
        $to = $this->request->get('to');

        $this->model->moveSelector($from, $to);

        $this->halt();

    }

}
