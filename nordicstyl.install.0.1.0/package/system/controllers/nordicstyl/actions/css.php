<?php

class actionNordicstylCss extends cmsAction {

    public function run() {

        header('Content-type: text/css; charset=UTF-8');

        $uri = (string)$this->request->get('uri', '');
        unset($uri);

        $rules = $this->model->getRules(true);
        if (!$rules) {
            $this->halt();
        }

        foreach ($rules as $rule) {
            if (empty($rule['is_enabled'])) {
                continue;
            }
            echo $this->buildRuleCSS($rule);
        }

        $this->halt();
    }
}
