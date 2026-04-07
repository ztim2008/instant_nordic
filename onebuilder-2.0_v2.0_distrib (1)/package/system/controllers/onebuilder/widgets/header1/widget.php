<?php

class widgetOnebuilderHeader1 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id            = $this->getOption('id');

        $bg            = $this->getOption('bg');
        $left          = $this->getOption('left');
        $title         = $this->getOption('title');
        $typebutton    = $this->getOption('typebutton');
        $scrolllink    = $this->getOption('scrolllink');
        $titlebutton   = $this->getOption('titlebutton');
        $link          = $this->getOption('link');
        $linkopt       = $this->getOption('linkopt');
        $bpreset       = $this->getOption('bpreset');

        return array(
            
            'id'            => $id,

            'bg'            => $bg,
            'left'          => $left,
            'title'         => $title,
            'typebutton'    => $typebutton,
            'scrolllink'    => $scrolllink,
            'titlebutton'   => $titlebutton,
            'link'          => $link,
            'linkopt'       => $linkopt,
            'bpreset'       => $bpreset,

            );    
    } 
}