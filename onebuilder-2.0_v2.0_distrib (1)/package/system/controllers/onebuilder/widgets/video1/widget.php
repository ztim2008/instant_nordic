<?php

class widgetOnebuilderVideo1 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');

        $id                = $this->getOption('id');
        $ptop              = $this->getOption('ptop');
        $pbottom           = $this->getOption('pbottom');

        $cover             = $this->getOption('cover');
        $video             = $this->getOption('video');

        return array(
            
            'id'                => $id,
            'ptop'              => $ptop,
            'pbottom'           => $pbottom,

            'cover'             => $cover,
            'video'             => $video

            );    
    } 
}