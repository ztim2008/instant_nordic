<?php

class onInstylerMenuInstyler extends cmsAction {

    public function run($item){

        $action = $item['action'];

        if ($action == 'open'){

            $url = href_to('instyler') . '?url=' . href_to($this->cms_core->uri);

            return array(
                'url' => $url,
                'items' => false
            );

        }

    }

}
