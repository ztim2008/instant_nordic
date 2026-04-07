<?php

class widgetOnebuilderServices3 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id                = $this->getOption('id');
        $ptop              = $this->getOption('ptop');
        $pbottom           = $this->getOption('pbottom');
        $bgtype            = $this->getOption('bgtype');
        $bgcolor           = $this->getOption('bgcolor');
        $bgimage           = $this->getOption('bgimage');
        $bgfixed           = $this->getOption('bgfixed');

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
            'ptop'              => $ptop,
            'pbottom'           => $pbottom,
            'bgtype'            => $bgtype,
            'bgcolor'           => $bgcolor,
            'bgimage'           => $bgimage,
            'bgfixed'           => $bgfixed,

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