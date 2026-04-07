<?php

class widgetOnebuilderAbout4 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id            = $this->getOption('id');
        $ptop          = $this->getOption('ptop');
        $pbottom       = $this->getOption('pbottom');
        $bgtype        = $this->getOption('bgtype');
        $bgcolor       = $this->getOption('bgcolor');
        $bgimage       = $this->getOption('bgimage');
        $bgfixed       = $this->getOption('bgfixed');

        $img           = $this->getOption('img');
        $title         = $this->getOption('title');
        $title_preset  = $this->getOption('title_preset');
        $bg_color      = $this->getOption('bg_color');
        $desc          = $this->getOption('desc');
        $p_preset      = $this->getOption('p_preset');
        $typebutton    = $this->getOption('typebutton');
        $scrolllink    = $this->getOption('scrolllink');
        $titlebutton   = $this->getOption('titlebutton');
        $link          = $this->getOption('link');
        $linkopt       = $this->getOption('linkopt');
        $bpreset       = $this->getOption('bpreset');


        return array(
            
            'id'                => $id,
            'ptop'              => $ptop,
            'pbottom'           => $pbottom,
            'bgtype'            => $bgtype,
            'bgcolor'           => $bgcolor,
            'bgimage'           => $bgimage,
            'bgfixed'           => $bgfixed,

            'img'               => $img,
            'title'             => $title,
            'title_preset'      => $title_preset,
            'bg_color'          => $bg_color,
            'desc'              => $desc,
            'p_preset'          => $p_preset,
            'typebutton'        => $typebutton,
            'scrolllink'        => $scrolllink,
            'titlebutton'       => $titlebutton,
            'link'              => $link,
            'linkopt'           => $linkopt,
            'bpreset'           => $bpreset,

            );    
    } 
}