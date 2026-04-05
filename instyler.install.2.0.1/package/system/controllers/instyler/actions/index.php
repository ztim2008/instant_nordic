<?php

class actionInstylerIndex extends cmsAction{

    public function run(){

        $user = cmsUser::getInstance();
		$config = cmsConfig::getInstance();

        if (!$user->is_admin) {
            cmsUser::goLogin();
        }

        $template = cmsTemplate::getInstance();

        $url = $this->request->get('url', '/');

		$premade_list_file = $config->root_path . 'templates/'.$template->name.'/instyler.json';

        $devices = $this->getDevices();

		$is_premade_list = file_exists($premade_list_file);

        $html = $template->render('index', array(
            'url' => $url,
            'devices' => $devices,
			'is_premade_list' => $is_premade_list,
        ));

        echo $html; $this->halt();

    }

}
