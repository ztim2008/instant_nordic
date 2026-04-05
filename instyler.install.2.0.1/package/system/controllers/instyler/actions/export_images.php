<?php

class actionInstylerExportImages extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()){ cmsCore::error404(); }

        $selectors = array();

        $total = $this->model->getSelectorsCount();

		$images = array();
		
		$config = cmsConfig::getInstance();
		
        if ($total){

            $selectors = $this->model->getSelectors();

            foreach($selectors as $id => $selector){

				$selector['styles'] = cmsModel::yamlToArray($selector['styles']);
				
				if (!$selector['styles']) { continue; }
				
				foreach($selector['styles'] as $state=>$styles){
					
					if (empty($styles)) { continue; } 
					if (empty($styles['background-image'])) { continue; } 
					
					$url = str_replace(array('url(', ')', '"', '"'), '', $styles['background-image']);
					
					if ($url == 'none') { continue; }
					
					if (mb_substr($url, 0, 4) == 'http'){ continue; }
					if (mb_substr($url, 0, mb_strlen($config->upload_root)) != $config->upload_root){ continue; }
					
					$path = $config->upload_path . trim(mb_substr($url, mb_strlen($config->upload_root)-1), '/');
					
					$images[] = array('path'=>$path, 'url'=>trim($url, '/'));
					
				}

            }

        }

		$zip_filename = $config->upload_path . 'instyler-' . time() . '.zip';
		
		$zip = new ZipArchive;
		
		if ($zip->open($zip_filename, ZipArchive::CREATE) !== true) {
			$this->halt(LANG_INSTYLER_EXPORT_IMAGES_ZIP_ERROR);
		}
		
		foreach($images as $image){
			$zip->addFile($image['path'], $image['url']);
		}
		
		$zip->close();
		
		$filename = 'instyler-images.zip';
		
        header("Content-Type: application/force-download");
        header("Content-Length: " . filesize($zip_filename));
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        readfile($zip_filename);

        $this->halt();

    }

}
