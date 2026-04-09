<?php

class onNordicstylBeforePrintHead extends cmsAction {

    public function run($data) {

        if ($this->cms_core->controller === 'admin') {
            return $data;
        }

        if ($this->cms_core->controller === $this->name) {
            return $data;
        }

        $template = cmsTemplate::getInstance();

        if (!empty($_REQUEST['nordicstyl_inject'])) {
            $template->addControllerCSS('inject', 'nordicstyl');
            return $data;
        }

        $uri = (string)$this->cms_core->uri;
        $template->addCSS(href_to_abs('nordicstyl', 'css') . '?uri=' . $uri);

        return $data;
    }
}
