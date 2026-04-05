<?php

class actionInstylerPremade extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

		$config = cmsConfig::getInstance();
		$template = cmsTemplate::getInstance();
		
		$premade_list_file = $config->root_path . 'templates/'.$template->name.'/instyler.json';
		
		$is_premade_list = file_exists($premade_list_file);
		
		if (!$is_premade_list){
			cmsCore::error404();
		}
		
		header('Content-type: application/json; charset=utf-8');
		
		readfile($premade_list_file);
		
		$this->halt();

    }

}
