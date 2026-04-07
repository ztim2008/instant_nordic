<?php

class widgetOnebuilderTestimonials1 extends cmsWidget { 
    
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

        $name              = $this->getOption('name');
        $name_array        = $this->getOption('name_array');
        $company           = $this->getOption('company');
        $company_array     = $this->getOption('company_array');
        $rev               = $this->getOption('rev');
        $rev_array         = $this->getOption('rev_array');
        $photo             = $this->getOption('photo');
        $p_preset          = $this->getOption('p_preset');

        //$name_array    = explode("\n", $name);
        //$company_array = explode("\n", $company);
        //$rev_array     = explode("\n", $rev);

        //$name_array    = preg_split('/\s*,\s*/', $name, -1, PREG_SPLIT_NO_EMPTY);
        //$company_array = preg_split('/\s*,\s*/', $company, -1, PREG_SPLIT_NO_EMPTY);
        //$rev_array     = preg_split('/\s*,\s*/', $rev, -1, PREG_SPLIT_NO_EMPTY);

        $name_array    = explode("\n", $name);
        $company_array = explode("\n", $company);
        $rev_array     = explode("***", $rev);

        return array(
            
            'id'                => $id,
            'ptop'              => $ptop,
            'pbottom'           => $pbottom,
            'bgtype'            => $bgtype,
            'bgcolor'           => $bgcolor,
            'bgimage'           => $bgimage,
            'bgfixed'           => $bgfixed,

            'name'              => $name,
            'name_array'        => $name_array,
            'company'           => $company,
            'company_array'     => $company_array,
            'rev'               => $rev,
            'rev_array'         => $rev_array,
            'photo'             => $photo,
            'p_preset'          => $p_preset,


            );    
    } 
}