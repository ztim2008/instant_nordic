<?php

class widgetOnebuilderTestimonials2 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');

        $id                    = $this->getOption('id');
        $ptop                  = $this->getOption('ptop');
        $pbottom               = $this->getOption('pbottom');
        $bgtype                = $this->getOption('bgtype');
        $bgcolor               = $this->getOption('bgcolor');
        $bgimage               = $this->getOption('bgimage');
        $bgfixed               = $this->getOption('bgfixed');

        $name                  = $this->getOption('name');
        $name_array            = $this->getOption('name_array');
        $rev                   = $this->getOption('rev');
        $rev_array             = $this->getOption('rev_array');
        $photo                 = $this->getOption('photo');
        $title                 = $this->getOption('title');
        $title_preset          = $this->getOption('title_preset');
        $title_preset2         = $this->getOption('title_preset2');
        $p_preset              = $this->getOption('p_preset');

        $name_array    = explode("\n", $name);
        $rev_array     = explode("***", $rev);

        return array(
            
            'id'                     => $id,
            'ptop'                   => $ptop,
            'pbottom'                => $pbottom,
            'bgtype'                 => $bgtype,
            'bgcolor'                => $bgcolor,
            'bgimage'                => $bgimage,
            'bgfixed'                => $bgfixed,

            'name'                   => $name,
            'name_array'             => $name_array,
            'rev'                    => $rev,
            'rev_array'              => $rev_array,
            'photo'                  => $photo,
            'title'                  => $title,
            'title_preset'           => $title_preset,
            'p_preset'               => $p_preset,
            'title_preset2'          => $title_preset2,


            );    
    } 
}