<?php

class widgetOnebuilderBanner3 extends cmsWidget { 
    
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
        $is_overlay        = $this->getOption('is_overlay');
        $o_color           = $this->getOption('o_color');
        $o_opacity         = $this->getOption('o_opacity');

        $icon              = $this->getOption('icon');
        $text              = $this->getOption('text');
        $p_preset          = $this->getOption('p_preset');
        $title             = $this->getOption('title');
        $title_preset      = $this->getOption('title_preset');
        $typebutton        = $this->getOption('typebutton');
        $scrolllink        = $this->getOption('scrolllink');
        $titlebutton       = $this->getOption('titlebutton');
        $link              = $this->getOption('link');
        $linkopt           = $this->getOption('linkopt');
        $bpreset           = $this->getOption('bpreset');

        return array(
            
            'id'                => $id,
            'ptop'              => $ptop,
            'pbottom'           => $pbottom,
            'bgtype'            => $bgtype,
            'bgcolor'           => $bgcolor,
            'bgimage'           => $bgimage,
            'bgfixed'           => $bgfixed,
            'is_overlay'        => $is_overlay,
            'o_color'           => $o_color,
            'o_opacity'         => $o_opacity,

            'icon'              => $icon,
            'text'              => $text,
            'p_preset'          => $p_preset,
            'title'             => $title,
            'title_preset'      => $title_preset,
            'typebutton'        => $typebutton,
            'scrolllink'        => $scrolllink,
            'titlebutton'       => $titlebutton,
            'link'              => $link,
            'linkopt'           => $linkopt,
            'bpreset'           => $bpreset,

            );    
    } 
}