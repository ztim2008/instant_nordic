<?php

class actionInstylerCss extends cmsAction{

    public function run(){

        header("Content-type: text/css");

        $selectors = $this->model->getSelectors();

        $uri = $this->request->get('uri', '');

        if (!$selectors) { $this->halt(); }

        foreach($selectors as $selector){

            if (!$this->isSelectorApplicable($selector, $uri) || !$selector['is_enabled']){
                continue;
            }

            $css = $this->buildSelectorCSS($selector);

            echo $css . "\n";

        }

        $this->halt();

    }

}
