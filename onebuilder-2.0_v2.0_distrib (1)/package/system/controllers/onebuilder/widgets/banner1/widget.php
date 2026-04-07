<?php

class widgetOnebuilderBanner1 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id                = $this->getOption('id');

        $bg                = $this->getOption('bg');
        $bg_att            = $this->getOption('bg_att');
        $title             = $this->getOption('title');
        $title_preset      = $this->getOption('title_preset');
        $sub               = $this->getOption('sub');
        $p_preset          = $this->getOption('p_preset');
        $typebutton        = $this->getOption('typebutton');
        $scrolllink        = $this->getOption('scrolllink');
        $titlebutton       = $this->getOption('titlebutton');
        $link              = $this->getOption('link');
        $linkopt           = $this->getOption('linkopt');
        $bpreset           = $this->getOption('bpreset');

        return array(
            
            'id'                => $id,

            'bg'                => $bg,
            'bg_att'            => $bg_att,
            'title'             => $title,
            'title_preset'      => $title_preset,
            'sub'               => $sub,
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