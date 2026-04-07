<?php

class widgetOnebuilderServices1 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id                = $this->getOption('id');

        $title             = $this->getOption('title');
        $title_preset      = $this->getOption('title_preset');
        $desc              = $this->getOption('desc');
        $p_preset          = $this->getOption('p_preset');
        $icolor            = $this->getOption('icolor');

        $name              = $this->getOption('name');
        $icon              = $this->getOption('icon');
        $text              = $this->getOption('text');
        $p_preset2         = $this->getOption('p_preset2');
        $title_preset2     = $this->getOption('title_preset2');


        $name_array    = explode("\n", $name);
        $icon_array    = explode("\n", $icon);
        $text_array    = explode("***", $text);

        return array(
            
            'id'                => $id,

            'title'             => $title,
            'title_preset'      => $title_preset,
            'desc'              => $desc,
            'p_preset'          => $p_preset,
            'icolor'            => $icolor,

            'name'              => $name,
            'name_array'        => $name_array,
            'icon'              => $icon,
            'icon_array'        => $icon_array,
            'text'              => $text,
            'p_preset2'         => $p_preset2,
            'text_array'        => $text_array,
            'title_preset2'     => $title_preset2,

            );    
    } 
}