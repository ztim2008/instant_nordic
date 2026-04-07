<?php

class widgetOnebuilderTable1 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id                = $this->getOption('id');
        $title             = $this->getOption('title');
        $title_preset      = $this->getOption('title_preset');
        $ptop              = $this->getOption('ptop');
        $pbottom           = $this->getOption('pbottom');
        $bgtype            = $this->getOption('bgtype');
        $bgcolor           = $this->getOption('bgcolor');
        $bgimage           = $this->getOption('bgimage');
        $bgfixed           = $this->getOption('bgfixed');

        $htable            = $this->getOption('htable');
        $ptable            = $this->getOption('ptable');
        $otable            = $this->getOption('otable');
        $htable_array      = $this->getOption('htable_array');
        $ptable_array      = $this->getOption('ptable_array');
        $otable_array      = $this->getOption('otable_array');

        $htable_array      = explode("\n", $htable);
        $ptable_array      = explode("\n", $ptable);
        $otable_array      = explode("***", $otable);

        $line_style        = $this->getOption('line_style');

        return array(
            
            'id'                => $id,
            'title'             => $title,
            'title_preset'      => $title_preset,
            'ptop'              => $ptop,
            'pbottom'           => $pbottom,
            'bgtype'            => $bgtype,
            'bgcolor'           => $bgcolor,
            'bgimage'           => $bgimage,
            'bgfixed'           => $bgfixed,

            'htable'            => $htable,
            'ptable'            => $ptable,
            'otable'            => $otable,
            'htable_array'      => $htable_array,
            'ptable_array'      => $ptable_array,
            'otable_array'      => $otable_array,

            'line_style'        => $line_style

            );    
    } 
}