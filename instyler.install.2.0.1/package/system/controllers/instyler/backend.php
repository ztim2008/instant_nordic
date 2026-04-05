<?php

class backendInstyler extends cmsBackend{

    public function actionIndex(){

        return cmsTemplate::getInstance()->render('backend/index', array());

    }

}
