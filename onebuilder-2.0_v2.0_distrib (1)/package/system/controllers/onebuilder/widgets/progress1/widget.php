<?php

class widgetOnebuilderProgress1 extends cmsWidget { 
    
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
        $p_preset2         = $this->getOption('p_preset2');
        $perc              = $this->getOption('perc');
        $perc_array        = $this->getOption('perc_array');
        $name              = $this->getOption('name');
        $name_array        = $this->getOption('name_array');
        $attr              = $this->getOption('attr');

        $perc_array        = explode("\n", $perc);
        $name_array        = explode("\n", $name);

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
            'p_preset2'         => $p_preset2,
            'perc'              => $perc,
            'perc_array'        => $perc_array,
            'name'              => $name,
            'name_array'        => $name_array,
            'attr'              => $attr,

            );    
    } 
}