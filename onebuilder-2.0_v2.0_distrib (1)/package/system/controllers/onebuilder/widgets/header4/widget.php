<?php

class widgetOnebuilderHeader4 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id            = $this->getOption('id');
        $bg            = $this->getOption('bg');

        return array(
            
            'id'             => $id,
            'bg'             => $bg,

            );    
    } 
}