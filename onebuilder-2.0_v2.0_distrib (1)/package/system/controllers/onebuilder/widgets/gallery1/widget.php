<?php

class widgetOnebuilderGallery1 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id                = $this->getOption('id');

        $title             = $this->getOption('title');
        $title_preset      = $this->getOption('title_preset');
        $desc              = $this->getOption('desc');
        $p_preset          = $this->getOption('p_preset');
        $photo             = $this->getOption('photo');

        return array(
            
            'id'                => $id,

            'title'             => $title,
            'title_preset'      => $title_preset,
            'desc'              => $desc,
            'p_preset'          => $p_preset,
            'photo'             => $photo,

            );    
    } 
}