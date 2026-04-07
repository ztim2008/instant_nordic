<?php

class widgetOnebuilderProgress2 extends cmsWidget { 
    
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

        $perc              = $this->getOption('perc');
        $perc_array        = $this->getOption('perc_array');
        $name              = $this->getOption('name');
        $p_preset          = $this->getOption('p_preset');
        $name_array        = $this->getOption('name_array');

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

            'perc'              => $perc,
            'perc_array'        => $perc_array,
            'name'              => $name,
            'p_preset'          => $p_preset,
            'name_array'        => $name_array

            );    
    } 
}