<?php

class widgetOnebuilderCounter1 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id                  = $this->getOption('id');
        $ptop                = $this->getOption('ptop');
        $pbottom             = $this->getOption('pbottom');
        $bgtype              = $this->getOption('bgtype');
        $bgcolor             = $this->getOption('bgcolor');
        $bgimage             = $this->getOption('bgimage');
        $bgfixed             = $this->getOption('bgfixed');

        $title               = $this->getOption('title');
        $title_preset        = $this->getOption('title_preset');
        $desc                = $this->getOption('desc');
        $p_preset            = $this->getOption('p_preset');

        $counter             = $this->getOption('counter');
        $title_preset2       = $this->getOption('title_preset2');
        $counter_array       = $this->getOption('counter_array');
        $icon                = $this->getOption('icon');
        $icon_color          = $this->getOption('icon_color');
        $icon_array          = $this->getOption('icon_array');
        $ctitle              = $this->getOption('ctitle');
        $p_preset2           = $this->getOption('p_preset2');
        $ctitle_array        = $this->getOption('ctitle_array');

        $counter_array    = explode("\n", $counter);
        $icon_array    = explode("\n", $icon);
        $ctitle_array    = explode("\n", $ctitle);

        return array(
            
            'id'                     => $id,
            'ptop'                   => $ptop,
            'pbottom'                => $pbottom,
            'bgtype'                 => $bgtype,
            'bgcolor'                => $bgcolor,
            'bgimage'                => $bgimage,
            'bgfixed'                => $bgfixed,

            'title'                  => $title,
            'title_preset'           => $title_preset,
            'desc'                   => $desc,
            'p_preset'               => $p_preset,
            
            'counter'                => $counter,
            'title_preset2'          => $title_preset2,
            'counter_array'          => $counter_array,
            'icon'                   => $icon,
            'icon_color'             => $icon_color,
            'icon_array'             => $icon_array,
            'ctitle'                 => $ctitle,
            'p_preset2'              => $p_preset2,
            'ctitle_array'           => $ctitle_array,

            );    
    } 
}