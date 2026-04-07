<?php

class widgetOnebuilderHeader2 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id            = $this->getOption('id');
        $bg            = $this->getOption('bg');
        $bgr           = $this->getOption('bgr');

        $title         = $this->getOption('title');
        $desc          = $this->getOption('desc');
        $typebutton    = $this->getOption('typebutton');
        $scrolllink    = $this->getOption('scrolllink');
        $titlebutton   = $this->getOption('titlebutton');
        $link          = $this->getOption('link');
        $linkopt       = $this->getOption('linkopt');

        return array(
            
            'id'            => $id,
            'bg'            => $bg,
            'bgr'           => $bgr,

            'title'         => $title,
            'desc'          => $desc,
            'typebutton'    => $typebutton,
            'scrolllink'    => $scrolllink,
            'titlebutton'   => $titlebutton,
            'link'          => $link,
            'linkopt'       => $linkopt

            );    
    } 
}