<?php

class widgetOnebuilderStart extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');

		$font1               = $this->getOption('font1');
        $font2               = $this->getOption('font2');
        $on_font3            = $this->getOption('on_font3');
        $font3               = $this->getOption('font3');
        $on_font4            = $this->getOption('on_font4');
        $font4               = $this->getOption('font4');
        $on_font5            = $this->getOption('on_font5');
        $font5               = $this->getOption('font5');

        $zag_list            = $this->getOption('zag_list');

        $h1_font             = $this->getOption('h1_font');
        $h1_color            = $this->getOption('h1_color');
        $h1_fsize            = $this->getOption('h1_fsize');
        $h1_ftran            = $this->getOption('h1_ftran');
        $h1_decor            = $this->getOption('h1_decor');
        $h1_align            = $this->getOption('h1_align');

        $h2_font             = $this->getOption('h2_font');
        $h2_color            = $this->getOption('h2_color');
        $h2_fsize            = $this->getOption('h2_fsize');
        $h2_ftran            = $this->getOption('h2_ftran');
        $h2_decor            = $this->getOption('h2_decor');
        $h2_align            = $this->getOption('h2_align');

        $h3_font             = $this->getOption('h3_font');
        $h3_color            = $this->getOption('h3_color');
        $h3_fsize            = $this->getOption('h3_fsize');
        $h3_ftran            = $this->getOption('h3_ftran');
        $h3_decor            = $this->getOption('h3_decor');
        $h3_align            = $this->getOption('h3_align');

        $h4_font             = $this->getOption('h4_font');
        $h4_color            = $this->getOption('h4_color');
        $h4_fsize            = $this->getOption('h4_fsize');
        $h4_ftran            = $this->getOption('h4_ftran');
        $h4_decor            = $this->getOption('h4_decor');
        $h4_align            = $this->getOption('h4_align');

        $h5_font             = $this->getOption('h5_font');
        $h5_color            = $this->getOption('h5_color');
        $h5_fsize            = $this->getOption('h5_fsize');
        $h5_ftran            = $this->getOption('h5_ftran');
        $h5_decor            = $this->getOption('h5_decor');
        $h5_align            = $this->getOption('h5_align');

        $txt_list            = $this->getOption('txt_list');

        $p1_font             = $this->getOption('p1_font');
        $p1_color            = $this->getOption('p1_color');
        $p1_fsize            = $this->getOption('p1_fsize');
        $p1_ftran            = $this->getOption('p1_ftran');
        $p1_fline            = $this->getOption('p1_fline');

        $p2_font             = $this->getOption('p2_font');
        $p2_color            = $this->getOption('p2_color');
        $p2_fsize            = $this->getOption('p2_fsize');
        $p2_ftran            = $this->getOption('p2_ftran');
        $p2_fline            = $this->getOption('p2_fline');

        $p3_font             = $this->getOption('p3_font');
        $p3_color            = $this->getOption('p3_color');
        $p3_fsize            = $this->getOption('p3_fsize');
        $p3_ftran            = $this->getOption('p3_ftran');
        $p3_fline            = $this->getOption('p3_fline');

        $button_list         = $this->getOption('button_list');

        $button1_font        = $this->getOption('button1_font');
        $button1_bg          = $this->getOption('button1_bg');
        $button1_bgh         = $this->getOption('button1_bgh');
        $button1_color       = $this->getOption('button1_color');
        $button1_colorh      = $this->getOption('button1_colorh');
        $button1_paddingtb   = $this->getOption('button1_paddingtb');
        $button1_paddinglr   = $this->getOption('button1_paddinglr');
        $button1_border      = $this->getOption('button1_border');
        $button1_fsize       = $this->getOption('button1_fsize');
        $button1_ftran       = $this->getOption('button1_ftran');
        $button1_fstyle      = $this->getOption('button1_fstyle');
        $button1_fbord       = $this->getOption('button1_fbord');
        $button1_fbordt      = $this->getOption('button1_fbordt');
        $button1_bcolor      = $this->getOption('button1_bcolor');

        $button2_font        = $this->getOption('button2_font');
        $button2_bg          = $this->getOption('button2_bg');
        $button2_bgh         = $this->getOption('button2_bgh');
        $button2_color       = $this->getOption('button2_color');
        $button2_colorh      = $this->getOption('button2_colorh');
        $button2_paddingtb   = $this->getOption('button2_paddingtb');
        $button2_paddinglr   = $this->getOption('button2_paddinglr');
        $button2_border      = $this->getOption('button2_border');
        $button2_fsize       = $this->getOption('button2_fsize');
        $button2_ftran       = $this->getOption('button2_ftran');
        $button2_fstyle      = $this->getOption('button2_fstyle');
        $button2_fbord       = $this->getOption('button2_fbord');
        $button2_fbordt      = $this->getOption('button2_fbordt');
        $button2_bcolor      = $this->getOption('button2_bcolor');

        $button3_font        = $this->getOption('button3_font');
        $button3_bg          = $this->getOption('button3_bg');
        $button3_bgh         = $this->getOption('button3_bgh');
        $button3_color       = $this->getOption('button3_color');
        $button3_colorh      = $this->getOption('button3_colorh');
        $button3_paddingtb   = $this->getOption('button3_paddingtb');
        $button3_paddinglr   = $this->getOption('button3_paddinglr');
        $button3_border      = $this->getOption('button3_border');
        $button3_fsize       = $this->getOption('button3_fsize');
        $button3_ftran       = $this->getOption('button3_ftran');
        $button3_fstyle      = $this->getOption('button3_fstyle');
        $button3_fbord       = $this->getOption('button3_fbord');
        $button3_fbordt      = $this->getOption('button3_fbordt');
        $button3_bcolor      = $this->getOption('button3_bcolor');

        $button4_font        = $this->getOption('button4_font');
        $button4_bg          = $this->getOption('button4_bg');
        $button4_bgh         = $this->getOption('button4_bgh');
        $button4_color       = $this->getOption('button4_color');
        $button4_colorh      = $this->getOption('button4_colorh');
        $button4_paddingtb   = $this->getOption('button4_paddingtb');
        $button4_paddinglr   = $this->getOption('button4_paddinglr');
        $button4_border      = $this->getOption('button4_border');
        $button4_fsize       = $this->getOption('button4_fsize');
        $button4_ftran       = $this->getOption('button4_ftran');
        $button4_fstyle      = $this->getOption('button4_fstyle');
        $button4_fbord       = $this->getOption('button4_fbord');
        $button4_fbordt      = $this->getOption('button4_fbordt');
        $button4_bcolor      = $this->getOption('button4_bcolor');

        $button5_font        = $this->getOption('button5_font');
        $button5_bg          = $this->getOption('button5_bg');
        $button5_bgh         = $this->getOption('button5_bgh');
        $button5_color       = $this->getOption('button5_color');
        $button5_colorh      = $this->getOption('button5_colorh');
        $button5_paddingtb   = $this->getOption('button5_paddingtb');
        $button5_paddinglr   = $this->getOption('button5_paddinglr');
        $button5_border      = $this->getOption('button5_border');
        $button5_fsize       = $this->getOption('button5_fsize');
        $button5_ftran       = $this->getOption('button5_ftran');
        $button5_fstyle      = $this->getOption('button5_fstyle');
        $button5_fbord       = $this->getOption('button5_fbord');
        $button5_fbordt      = $this->getOption('button5_fbordt');
        $button5_bcolor      = $this->getOption('button5_bcolor');

        // Other

        $go_top              = $this->getOption('go_top');

        return array(

            'font1'               => $font1,
            'font2'               => $font2,
            'on_font3'            => $on_font3,
            'font3'               => $font3,
            'on_font4'            => $on_font4,
            'font4'               => $font4,
            'on_font5'            => $on_font5,
            'font5'               => $font5,

            'zag_list'            => $zag_list,

            'h1_font'             => $h1_font,
            'h1_color'            => $h1_color,
            'h1_fsize'            => $h1_fsize,
            'h1_ftran'            => $h1_ftran,
            'h1_decor'            => $h1_decor,
            'h1_align'            => $h1_align,

            'h2_font'             => $h2_font,
            'h2_color'            => $h2_color,
            'h2_fsize'            => $h2_fsize,
            'h2_ftran'            => $h2_ftran,
            'h2_decor'            => $h2_decor,
            'h2_align'            => $h2_align,

            'h3_font'             => $h3_font,
            'h3_color'            => $h3_color,
            'h3_fsize'            => $h3_fsize,
            'h3_ftran'            => $h3_ftran,
            'h3_decor'            => $h3_decor,
            'h3_align'            => $h3_align,

            'h4_font'             => $h4_font,
            'h4_color'            => $h4_color,
            'h4_fsize'            => $h4_fsize,
            'h4_ftran'            => $h4_ftran,
            'h4_decor'            => $h4_decor,
            'h4_align'            => $h4_align,

            'h5_font'             => $h5_font,
            'h5_color'            => $h5_color,
            'h5_fsize'            => $h5_fsize,
            'h5_ftran'            => $h5_ftran,
            'h5_decor'            => $h5_decor,
            'h5_align'            => $h5_align,

            'txt_list'            => $txt_list,

            'p1_font'             => $p1_font,
            'p1_color'            => $p1_color,
            'p1_fsize'            => $p1_fsize,
            'p1_ftran'            => $p1_ftran,
            'p1_fline'            => $p1_fline,

            'p2_font'             => $p2_font,
            'p2_color'            => $p2_color,
            'p2_fsize'            => $p2_fsize,
            'p2_ftran'            => $p2_ftran,
            'p2_fline'            => $p2_fline,

            'p3_font'             => $p3_font,
            'p3_color'            => $p3_color,
            'p3_fsize'            => $p3_fsize,
            'p3_ftran'            => $p3_ftran,
            'p3_fline'            => $p3_fline,

            'button_list'         => $button_list,

            'button1_font'        => $button1_font,
            'button1_bg'          => $button1_bg,
            'button1_bgh'         => $button1_bgh,
            'button1_color'       => $button1_color,
            'button1_colorh'      => $button1_colorh,
            'button1_paddingtb'   => $button1_paddingtb,
            'button1_paddinglr'   => $button1_paddinglr,
            'button1_border'      => $button1_border,
            'button1_fsize'       => $button1_fsize,
            'button1_ftran'       => $button1_ftran,
            'button1_fstyle'      => $button1_fstyle,
            'button1_fbord'       => $button1_fbord,
            'button1_fbordt'      => $button1_fbordt,
            'button1_bcolor'      => $button1_bcolor,

            'button2_font'        => $button2_font,
            'button2_bg'          => $button2_bg,
            'button2_bgh'         => $button2_bgh,
            'button2_color'       => $button2_color,
            'button2_colorh'      => $button2_colorh,
            'button2_paddingtb'   => $button2_paddingtb,
            'button2_paddinglr'   => $button2_paddinglr,
            'button2_border'      => $button2_border,
            'button2_fsize'       => $button2_fsize,
            'button2_ftran'       => $button2_ftran,
            'button2_fstyle'      => $button2_fstyle,
            'button2_fbord'       => $button2_fbord,
            'button2_fbordt'      => $button2_fbordt,
            'button2_bcolor'      => $button2_bcolor,

            'button3_font'        => $button3_font,
            'button3_bg'          => $button3_bg,
            'button3_bgh'         => $button3_bgh,
            'button3_color'       => $button3_color,
            'button3_colorh'      => $button3_colorh,
            'button3_paddingtb'   => $button3_paddingtb,
            'button3_paddinglr'   => $button3_paddinglr,
            'button3_border'      => $button3_border,
            'button3_fsize'       => $button3_fsize,
            'button3_ftran'       => $button3_ftran,
            'button3_fstyle'      => $button3_fstyle,
            'button3_fbord'       => $button3_fbord,
            'button3_fbordt'      => $button3_fbordt,
            'button3_bcolor'      => $button3_bcolor,

            'button4_font'        => $button4_font,
            'button4_bg'          => $button4_bg,
            'button4_bgh'         => $button4_bgh,
            'button4_color'       => $button4_color,
            'button4_colorh'      => $button4_colorh,
            'button4_paddingtb'   => $button4_paddingtb,
            'button4_paddinglr'   => $button4_paddinglr,
            'button4_border'      => $button4_border,
            'button4_fsize'       => $button4_fsize,
            'button4_ftran'       => $button4_ftran,
            'button4_fstyle'      => $button4_fstyle,
            'button4_fbord'       => $button4_fbord,
            'button4_fbordt'      => $button4_fbordt,
            'button4_bcolor'      => $button4_bcolor,

            'button5_font'        => $button5_font,
            'button5_bg'          => $button5_bg,
            'button5_bgh'         => $button5_bgh,
            'button5_color'       => $button5_color,
            'button5_colorh'      => $button5_colorh,
            'button5_paddingtb'   => $button5_paddingtb,
            'button5_paddinglr'   => $button5_paddinglr,
            'button5_border'      => $button5_border,
            'button5_fsize'       => $button5_fsize,
            'button5_ftran'       => $button5_ftran,
            'button5_fstyle'      => $button5_fstyle,
            'button5_fbord'       => $button5_fbord,
            'button5_fbordt'      => $button5_fbordt,
            'button5_bcolor'      => $button5_bcolor,

            'go_top'              => $go_top,

            );    
    } 
}