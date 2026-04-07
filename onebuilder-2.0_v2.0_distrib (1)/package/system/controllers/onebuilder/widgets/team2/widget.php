<?php

class widgetOnebuilderTeam2 extends cmsWidget { 
    
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
        $photo             = $this->getOption('photo');
        $name              = $this->getOption('name');
        $name_array        = $this->getOption('name_array');
        $dlg               = $this->getOption('dlg');
        $dlg_array         = $this->getOption('dlg_array');
        $p_preset          = $this->getOption('p_preset');
        $title_preset2     = $this->getOption('title_preset2');

        $name_array        = explode("\n", $name);
        $dlg_array         = explode("\n", $dlg);
        

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
            'photo'             => $photo,
            'name'              => $name,
            'name_array'        => $name_array,
            'dlg'               => $dlg,
            'dlg_array'         => $dlg_array,
            'p_preset'          => $p_preset,
            'title_preset2'     => $title_preset2,

            );    
    } 
}