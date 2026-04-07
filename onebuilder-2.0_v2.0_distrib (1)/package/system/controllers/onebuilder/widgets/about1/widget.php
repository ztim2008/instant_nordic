<?php

class widgetOnebuilderAbout1 extends cmsWidget { 
    
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

        $title         = $this->getOption('title');
        $title_preset  = $this->getOption('title_preset');
        $about         = $this->getOption('about');
        $p_preset      = $this->getOption('p_preset');
        $typebutton    = $this->getOption('typebutton');
        $scrolllink    = $this->getOption('scrolllink');
        $titlebutton   = $this->getOption('titlebutton');
        $link          = $this->getOption('link');
        $linkopt       = $this->getOption('linkopt');
        $bpreset       = $this->getOption('bpreset');

        $image1        = $this->getOption('image1');
        $title1        = $this->getOption('title1');
        $title_preset2 = $this->getOption('title_preset2');
        $icon1         = $this->getOption('icon1');
        $text1         = $this->getOption('text1');
        $image2        = $this->getOption('image2');
        $title2        = $this->getOption('title2');
        $title_preset3 = $this->getOption('title_preset3');
        $icon2         = $this->getOption('icon2');
        $text2         = $this->getOption('text2');
        $p2_preset     = $this->getOption('p2_preset');
        $p3_preset     = $this->getOption('p3_preset');

        return array(

            'id'            => $id,
            'ptop'          => $ptop,
            'pbottom'       => $pbottom,
            'bgtype'        => $bgtype,
            'bgcolor'       => $bgcolor,
            'bgimage'       => $bgimage,
            'bgfixed'       => $bgfixed,

            'title'         => $title,
            'title_preset'  => $title_preset,
            'about'         => $about,
            'p_preset'      => $p_preset,
            'typebutton'    => $typebutton,
            'scrolllink'    => $scrolllink,
            'titlebutton'   => $titlebutton,
            'link'          => $link,
            'linkopt'       => $linkopt,
            'bpreset'       => $bpreset,

            'image1'        => $image1,
            'title1'        => $title1,
            'title_preset2' => $title_preset2,
            'icon1'         => $icon1,
            'text1'         => $text1,
            'image2'        => $image2,
            'title2'        => $title2,
            'title_preset3' => $title_preset3,
            'icon2'         => $icon2,
            'text2'         => $text2,
            'p2_preset'     => $p2_preset,
            'p3_preset'     => $p3_preset

            );    
    } 
}