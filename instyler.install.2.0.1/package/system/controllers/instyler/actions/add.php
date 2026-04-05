<?php

class actionInstylerAdd extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $uri = trim($this->request->get('uri'), '/');
        $selector = json_decode($this->request->get('selector'), true);

        $id = $this->model->addSelector($selector);

        $scope_type = $this->getSelectorScopeType($selector, $uri);

        cmsTemplate::getInstance()->renderJSON(array(
            'success' => is_numeric($id),
            'id' => $id,
            'scope_type' => $scope_type
        ));

    }

}
