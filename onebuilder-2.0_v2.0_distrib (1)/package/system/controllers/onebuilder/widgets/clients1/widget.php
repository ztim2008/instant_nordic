<?php

class widgetOnebuilderClients1 extends cmsWidget { 
    
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
        $desc              = $this->getOption('desc');
        $p_preset          = $this->getOption('p_preset');
        $logo              = $this->getOption('logo');

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
            'desc'              => $desc,
            'p_preset'          => $p_preset,
            'logo'              => $logo,

            );    
    } 
}