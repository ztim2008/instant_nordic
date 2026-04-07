<?php

class widgetOnebuilderHeader3 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id            = $this->getOption('id');
        $bg            = $this->getOption('bg');

        $title         = $this->getOption('title');
        $desc          = $this->getOption('desc');
        $typebutton    = $this->getOption('typebutton');
        $scrolllink    = $this->getOption('scrolllink');
        $titlebutton   = $this->getOption('titlebutton');
        $link          = $this->getOption('link');
        $linkopt       = $this->getOption('linkopt');
        $typebutton2   = $this->getOption('typebutton2');
        $scrolllink2   = $this->getOption('scrolllink2');
        $titlebutton2  = $this->getOption('titlebutton2');
        $link2         = $this->getOption('link2');
        $linkopt2      = $this->getOption('linkopt2');

        $css_section   = $this->getOption('css_section');
        $css_title     = $this->getOption('css_title');
        $css_desc      = $this->getOption('css_desc');
        $css_button    = $this->getOption('css_button');
        $css_button2   = $this->getOption('css_button2');

        return array(
            
            'id'             => $id,
            'bg'             => $bg,

            'title'          => $title,
            'desc'           => $desc,
            'typebutton'     => $typebutton,
            'scrolllink'     => $scrolllink,
            'titlebutton'    => $titlebutton,
            'link'           => $link,
            'linkopt'        => $linkopt,
            'typebutton2'    => $typebutton2,
            'scrolllink2'    => $scrolllink2,
            'titlebutton2'   => $titlebutton2,
            'link2'          => $link2,
            'linkopt2'       => $linkopt2,

            'css_section'    => $css_section,
            'css_title'      => $css_title,
            'css_desc'       => $css_desc,
            'css_button'     => $css_button,
            'css_button2'    => $css_button2

            );    
    } 
}