<?php

class widgetOnebuilderServices2 extends cmsWidget { 
    
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

        $title             = $this->getOption('title');
        $title_preset      = $this->getOption('title_preset');
        $typebutton        = $this->getOption('typebutton');
        $scrolllink        = $this->getOption('scrolllink');
        $titlebutton       = $this->getOption('titlebutton');
        $link              = $this->getOption('link');
        $linkopt           = $this->getOption('linkopt');
        $bpreset           = $this->getOption('bpreset');

        $icon              = $this->getOption('icon');
        $title_s           = $this->getOption('title_s');
        $desc              = $this->getOption('desc');
        $p_preset          = $this->getOption('p_preset');
        $title_preset2     = $this->getOption('title_preset2');

        $title_array       = explode("\n", $title_s);
        $desc_array        = explode("**", $desc);

        return array(
            
            'id'                => $id,
            'ptop'              => $ptop,
            'pbottom'           => $pbottom,
            'bgtype'            => $bgtype,
            'bgcolor'           => $bgcolor,
            'bgimage'           => $bgimage,
            'bgfixed'           => $bgfixed,

            'title'             => $title,
            'title_preset'      => $title_preset,
            'typebutton'        => $typebutton,
            'scrolllink'        => $scrolllink,
            'titlebutton'       => $titlebutton,
            'link'              => $link,
            'linkopt'           => $linkopt,
            'bpreset'           => $bpreset,

            'icon'              => $icon,
            'title_array'       => $title_array,
            'title_s'           => $title_s,
            'desc'              => $desc,
            'desc_array'        => $desc_array,
            'p_preset'          => $p_preset,
            'title_preset2'     => $title_preset2,

            );    
    } 
}