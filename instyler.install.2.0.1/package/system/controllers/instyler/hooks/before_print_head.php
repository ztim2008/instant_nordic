<?php

class onInstylerBeforePrintHead extends cmsAction {

	public function run($data){

        if ($this->cms_core->controller == 'admin') { return $data; }
        if ($this->cms_core->controller == $this->name) { return $data; }

        if (!empty($_REQUEST['instyler_inject'])){
            cmsTemplate::getInstance()->addControllerCSS('inject', 'instyler');
            return $data;
        }

        $uri = $this->cms_core->uri;

        $template = cmsTemplate::getInstance();

        $template->addCSS(href_to_abs('instyler', 'css') . '?uri='. $uri);

        return $data;

    }

}
