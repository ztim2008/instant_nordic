<?php

class actionInstylerScope extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $uri = trim($this->request->get('uri'), '/');

        if ($this->request->has('all')){

            $selectors = $this->model->getSelectors();

            $scope_types = array();

			if ($selectors){
				foreach($selectors as $selector){
					$scope_types[$selector['id']] = $this->getSelectorScopeType($selector, $uri);
				}
			}

            cmsTemplate::getInstance()->renderJSON(array(
                'success' => true,
                'scope_types' => $scope_types
            ));

            $this->halt();

        }

        $selector = json_decode($this->request->get('selector'), true);

        $scope_type = $this->getSelectorScopeType($selector, $uri);

        cmsTemplate::getInstance()->renderJSON(array(
            'success' => true,
            'scope_type' => $scope_type
        ));

    }

}
