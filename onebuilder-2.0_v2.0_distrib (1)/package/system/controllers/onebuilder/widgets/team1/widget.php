<?php

class widgetOnebuilderTeam1 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id                = $this->getOption('id');

        $title             = $this->getOption('title');
        $title_preset      = $this->getOption('title_preset');
        $content           = $this->getOption('content');
        $typebutton        = $this->getOption('typebutton');
        $scrolllink        = $this->getOption('scrolllink');
        $titlebutton       = $this->getOption('titlebutton');
        $link              = $this->getOption('link');
        $linkopt           = $this->getOption('linkopt');
        $bpreset           = $this->getOption('bpreset');

        $num_column        = $this->getOption('num_column');
        $user1             = $this->getOption('user1');
        $user2             = $this->getOption('user2');
        $user3             = $this->getOption('user3');
        $user4             = $this->getOption('user4');
        $user5             = $this->getOption('user5');
        $user6             = $this->getOption('user6');

        $name1             = $this->getOption('name1');
        $name2             = $this->getOption('name2');
        $name3             = $this->getOption('name3');
        $name4             = $this->getOption('name4');
        $name5             = $this->getOption('name5');
        $name6             = $this->getOption('name6');

        $work1             = $this->getOption('work1');
        $work2             = $this->getOption('work2');
        $work3             = $this->getOption('work3');
        $work4             = $this->getOption('work4');
        $work5             = $this->getOption('work5');
        $work6             = $this->getOption('work6');
        

        return array(
            
            'id'                => $id,

            'title'             => $title,
            'title_preset'      => $title_preset,
            'content'           => $content,
            'typebutton'        => $typebutton,
            'scrolllink'        => $scrolllink,
            'titlebutton'       => $titlebutton,
            'link'              => $link,
            'linkopt'           => $linkopt,
            'bpreset'           => $bpreset,

            'num_column'        => $num_column,
            'user1'             => $user1,
            'user2'             => $user2,
            'user3'             => $user3,
            'user4'             => $user4,
            'user5'             => $user5,
            'user6'             => $user6,

            'name1'             => $name1,
            'name2'             => $name2,
            'name3'             => $name3,
            'name4'             => $name4,
            'name5'             => $name5,
            'name6'             => $name6,

            'work1'             => $work1,
            'work2'             => $work2,
            'work3'             => $work3,
            'work4'             => $work4,
            'work5'             => $work5,
            'work6'             => $work6,

            );    
    } 
}