<link href="<?php echo (empty($font1)) ? 'https://fonts.googleapis.com/css2?family=PT+Sans:wght@700' : $font1; ?>&display=swap" rel="stylesheet">
<link href="<?php echo (empty($font2)) ? 'https://fonts.googleapis.com/css2?family=Roboto' : $font2; ?>&display=swap" rel="stylesheet">
<?php if(!empty($font3)) echo '<link href="'.$font3.'&display=swap" rel="stylesheet">'; else echo ""; ?>
<?php if(!empty($font4)) echo '<link href="'.$font4.'&display=swap" rel="stylesheet">'; else echo ""; ?> 
<?php if(!empty($font5)) echo '<link href="'.$font5.'&display=swap" rel="stylesheet">'; else echo ""; ?> 

<style>
.onebuilder-button1 { 
    font-family: <?php echo (empty($button1_font)) ? 'pt sans' : $button1_font; ?>;
    background-color: <?php echo (empty($button1_bg)) ? '#1540CE' : $button1_bg; ?>;
    color: <?php echo (empty($button1_color)) ? '#fff' : $button1_color; ?> !important;
    padding: <?php echo (empty($button1_paddingtb)) ? '20' : $button1_paddingtb; ?>px 
             <?php echo (empty($button1_paddinglr)) ? '40' : $button1_paddinglr; ?>px;
    border-radius: <?php echo (empty($button1_border)) ? '7' : $button1_border; ?>px;
    font-size: <?php echo (empty($button1_fsize)) ? '16' : $button1_fsize; ?>px;
    text-transform: <?php echo (empty($button1_ftran)) ? 'none' : $button1_ftran; ?>;
    font-style: <?php echo (empty($button1_fstyle)) ? 'normal' : $button1_fstyle; ?>;
    border: <?php echo (empty($button1_fbordt)) ? '2' : $button1_fbordt; ?>px
            <?php echo (empty($button1_fbord)) ? 'solid' : $button1_fbord; ?> 
            <?php echo (empty($button1_bcolor)) ? '#235d9b' : $button1_bcolor; ?>;
}

.obbtn1:hover { background-color: <?php echo (empty($button1_bgh)) ? '#3865d0' : $button1_bgh; ?>;
    color: <?php echo (empty($button1_colorh)) ? '#fff' : $button1_colorh; ?> !important;
}

.onebuilder-button2 { 
    font-family: <?php echo (empty($button2_font)) ? 'pt sans' : $button2_font; ?>;
    background-color: <?php echo (empty($button2_bg)) ? '#fff0' : $button2_bg; ?>;
    color: <?php echo (empty($button2_color)) ? '#0400db' : $button2_color; ?> !important;
    padding: <?php echo (empty($button2_paddingtb)) ? '20' : $button2_paddingtb; ?>px 
             <?php echo (empty($button2_paddinglr)) ? '40' : $button2_paddinglr; ?>px;
    border-radius: <?php echo (empty($button2_border)) ? '0' : $button2_border; ?>px;
    font-size: <?php echo (empty($button2_fsize)) ? '16' : $button2_fsize; ?>px;
    text-transform: <?php echo (empty($button2_ftran)) ? 'uppercase' : $button2_ftran; ?>;
    font-style: <?php echo (empty($button2_fstyle)) ? 'normal' : $button2_fstyle; ?>;
    border: <?php echo (empty($button2_fbordt)) ? '0' : $button2_fbordt; ?>px
            <?php echo (empty($button2_fbord)) ? 'solid' : $button2_fbord; ?> 
            <?php echo (empty($button2_bcolor)) ? '#fff' : $button2_bcolor; ?>;
}

.obbtn2:hover { background-color: <?php echo (empty($button2_bgh)) ? '#fff0' : $button2_bgh; ?>;
    color: <?php echo (empty($button2_colorh)) ? '#000' : $button2_colorh; ?> !important;
}

.onebuilder-button3 { 
    <?php if(!empty($button3_font)) echo 'font-family:'.$button3_font.';'; else echo ""; ?>
    <?php if(!empty($button3_bg)) echo 'background-color:'.$button3_bg.';'; else echo ""; ?>
    <?php if(!empty($button3_color)) echo 'color:'.$button3_color.';'; else echo ""; ?>
    <?php if(!empty($button3_paddingtb)) echo 'padding:'.$button3_paddingtb.'px '.$button3_paddinglr.'px;'; else echo ""; ?>
    <?php if(!empty($button3_border)) echo 'border-radius:'.$button3_border.'px;'; else echo ""; ?>
    <?php if(!empty($button3_fsize)) echo 'font-size:'.$button3_fsize.'px;'; else echo ""; ?>
    <?php if(!empty($button3_ftran)) echo 'text-transform:'.$button3_ftran.';'; else echo ""; ?>
    <?php if(!empty($button3_fstyle)) echo 'font-style:'.$button3_fstyle.';>'; else echo ""; ?>
    <?php if(!empty($button3_fbordt)) echo 'border:'.$button3_fbordt.'px '.$button3_fbord.' '.$button3_bcolor.';'; else echo ""; ?>
}

.obbtn3:hover { 
    <?php if(!empty($button3_bgh)) echo 'background-color:'.$button3_bgh.';'; else echo ""; ?>
    <?php if(!empty($button3_colorh)) echo 'color:'.$button3_colorh.'!important;'; else echo ""; ?> 
}

.onebuilder-button4 { 
    <?php if(!empty($button4_font)) echo 'font-family:'.$button4_font.';'; else echo ""; ?>
    <?php if(!empty($button4_bg)) echo 'background-color:'.$button4_bg.';'; else echo ""; ?>
    <?php if(!empty($button4_color)) echo 'color:'.$button4_color.';'; else echo ""; ?>
    <?php if(!empty($button4_paddingtb)) echo 'padding:'.$button4_paddingtb.'px '.$button4_paddinglr.'px;'; else echo ""; ?>
    <?php if(!empty($button4_border)) echo 'border-radius:'.$button4_border.'px;'; else echo ""; ?>
    <?php if(!empty($button4_fsize)) echo 'font-size:'.$button4_fsize.'px;'; else echo ""; ?>
    <?php if(!empty($button4_ftran)) echo 'text-transform:'.$button4_ftran.';'; else echo ""; ?>
    <?php if(!empty($button4_fstyle)) echo 'font-style:'.$button4_fstyle.';'; else echo ""; ?>
    <?php if(!empty($button4_fbordt)) echo 'border:'.$button4_fbordt.'px '.$button4_fbord.' '.$button4_bcolor.';'; else echo ""; ?>
}

.obbtn4:hover { 
    <?php if(!empty($button4_bgh)) echo 'background-color:'.$button4_bgh.';'; else echo ""; ?>
    <?php if(!empty($button4_colorh)) echo 'color:'.$button4_colorh.'!important;'; else echo ""; ?> 
}

.onebuilder-button5 { 
    <?php if(!empty($button5_font)) echo 'font-family:'.$button5_font.';'; else echo ""; ?>
    <?php if(!empty($button5_bg)) echo 'background-color:'.$button5_bg.';'; else echo ""; ?>
    <?php if(!empty($button5_color)) echo 'color:'.$button5_color.';'; else echo ""; ?>
    <?php if(!empty($button5_paddingtb)) echo 'padding:'.$button5_paddingtb.'px '.$button5_paddinglr.'px;'; else echo ""; ?>
    <?php if(!empty($button5_border)) echo 'border-radius:'.$button5_border.'px;'; else echo ""; ?>
    <?php if(!empty($button5_fsize)) echo 'font-size:'.$button5_fsize.'px;'; else echo ""; ?>
    <?php if(!empty($button5_ftran)) echo 'text-transform:'.$button5_ftran.';'; else echo ""; ?>
    <?php if(!empty($button5_fstyle)) echo 'font-style:'.$button5_fstyle.';'; else echo ""; ?>
    <?php if(!empty($button5_fbordt)) echo 'border:'.$button5_fbordt.'px '.$button5_fbord.' '.$button5_bcolor.';'; else echo ""; ?>
}

.obbtn5:hover { 
    <?php if(!empty($button5_bgh)) echo 'background-color:'.$button5_bgh.';'; else echo ""; ?>
    <?php if(!empty($button5_colorh)) echo 'color:'.$button5_colorh.'!important;'; else echo ""; ?> 
}

.onebuilder-h1 { 
    font-family: <?php echo (empty($h1_font)) ? 'pt sans' : $h1_font; ?>;
    color: <?php echo (empty($h1_color)) ? '#000' : $h1_color; ?>;
    font-size: <?php echo (empty($h1_fsize)) ? '44' : $h1_fsize; ?>px;
    text-transform: <?php echo (empty($h1_ftran)) ? 'uppercase' : $h1_ftran; ?>;
    text-decoration: <?php echo (empty($h1_decor)) ? 'none' : $h1_decor; ?> !important;
    text-align: <?php echo (empty($h1_align)) ? 'left' : $h1_align; ?> !important;
}

.onebuilder-h2 { 
    font-family: <?php echo (empty($h2_font)) ? 'pt sans' : $h2_font; ?>;
    color: <?php echo (empty($h2_color)) ? '#000' : $h2_color; ?>;
    font-size: <?php echo (empty($h2_fsize)) ? '36' : $h2_fsize; ?>px;
    text-transform: <?php echo (empty($h2_ftran)) ? 'uppercase' : $h2_ftran; ?>;
    text-decoration: <?php echo (empty($h2_decor)) ? 'none' : $h2_decor; ?> !important;
    text-align: <?php echo (empty($h2_align)) ? 'left' : $h2_align; ?> !important;
}

.onebuilder-h3 { 
    <?php if(!empty($h3_font)) echo 'font-family:'.$h3_font.';'; else echo ""; ?>
    <?php if(!empty($h3_color)) echo 'color:'.$h3_color.';'; else echo ""; ?>
    <?php if(!empty($h3_fsize)) echo 'font-size:'.$h3_fsize.'px;'; else echo ""; ?>
    <?php if(!empty($h3_ftran)) echo 'text-transform:'.$h3_ftran.';'; else echo ""; ?>
    <?php if(!empty($h3_decor)) echo 'text-decoration:'.$h3_decor.' !important;'; else echo ""; ?>
    <?php if(!empty($h3_align)) echo 'text-align:'.$h3_align.' !important;'; else echo ""; ?>
}

.onebuilder-h4 { 
    <?php if(!empty($h4_font)) echo 'font-family:'.$h4_font.';'; else echo ""; ?>
    <?php if(!empty($h4_color)) echo 'color:'.$h4_color.';'; else echo ""; ?>
    <?php if(!empty($h4_fsize)) echo 'font-size:'.$h4_fsize.'px;'; else echo ""; ?>
    <?php if(!empty($h4_ftran)) echo 'text-transform:'.$h4_ftran.';'; else echo ""; ?>
    <?php if(!empty($h4_decor)) echo 'text-decoration:'.$h4_decor.' !important;'; else echo ""; ?>
    <?php if(!empty($h4_align)) echo 'text-align:'.$h4_align.' !important;'; else echo ""; ?>
}

.onebuilder-h5 { 
    <?php if(!empty($h5_font)) echo 'font-family:'.$h5_font.';'; else echo ""; ?>
    <?php if(!empty($h5_color)) echo 'color:'.$h5_color.';'; else echo ""; ?>
    <?php if(!empty($h5_fsize)) echo 'font-size:'.$h5_fsize.'px;'; else echo ""; ?>
    <?php if(!empty($h5_ftran)) echo 'text-transform:'.$h5_ftran.';'; else echo ""; ?>
    <?php if(!empty($h5_decor)) echo 'text-decoration:'.$h5_decor.' !important;'; else echo ""; ?>
    <?php if(!empty($h5_align)) echo 'text-align:'.$h5_align.' !important;'; else echo ""; ?>
}

.onebuilder-p1 { 
    font-family: <?php echo (empty($p1_font)) ? 'roboto' : $p1_font; ?>;
    color: <?php echo (empty($p1_color)) ? '#000' : $p1_color; ?>;
    font-size: <?php echo (empty($p1_fsize)) ? '18' : $p1_fsize; ?>px;
    text-transform: <?php echo (empty($p1_ftran)) ? 'none' : $p1_ftran; ?>;
    line-height: <?php echo (empty($p1_fline)) ? '21' : $p1_fline; ?>px; 
}

.onebuilder-p2 { 
font-family: <?php echo (empty($p2_font)) ? 'pt sans' : $p2_font; ?>;
color: <?php echo (empty($p2_color)) ? '#000' : $p2_color; ?>;
font-size: <?php echo (empty($p2_fsize)) ? '18' : $p2_fsize; ?>px;
text-transform: <?php echo (empty($p2_ftran)) ? 'none' : $p2_ftran; ?>;
line-height: <?php echo (empty($p2_fline)) ? '21' : $p2_fline; ?>px;
}

.onebuilder-p3 { 
font-family: <?php echo (empty($p3_font)) ? 'roboto' : $p3_font; ?>;
color: <?php echo (empty($p3_color)) ? '#fff' : $p3_color; ?>;
font-size: <?php echo (empty($p3_fsize)) ? '18' : $p3_fsize; ?>px;
text-transform: <?php echo (empty($p3_ftran)) ? 'none' : $p3_ftran; ?>;
line-height: <?php echo (empty($p3_fline)) ? '21' : $p3_fline; ?>px;
}

</style>



<?php ob_start(); ?>
<script>
$('a[href*="#"]')
  .not('[href="#"]')
  .not('[href="#0"]')
  .on('click', function(event) {
    if (
      location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') 
      && 
      location.hostname == this.hostname
    ) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      if (target.length) {
        event.preventDefault();
        $('html, body').animate({
          scrollTop: target.offset().top
        }, <?php if(!empty($go_top)) echo ''.$go_top.'000'; else echo "1000"; ?>, function() { // Получаем значение прокрутки из настроек виджета
          var $target = $(target);
          $target.trigger('focus');
          if ($target.is(":focus")) { 
            return false;
          } else {
            $target.attr('tabindex','-1'); 
            $target.trigger('focus'); 
          };
        });
      }
    }
  });
</script>
<?php $this->addBottom(ob_get_clean()); ?>

<?php $this->addCSS('templates/default/controllers/onebuilder/widgets/start/css/sections.css'); ?>
<?php $this->addCSS('templates/default/controllers/onebuilder/widgets/start/css/lightgallery.min.css'); ?>
<?php $this->addCSS('templates/default/controllers/onebuilder/widgets/start/css/lity.min.css'); ?>
<?php $this->addCSS('templates/default/controllers/onebuilder/widgets/start/css/main.css'); ?>
<?php $this->addCSS('templates/default/controllers/onebuilder/widgets/start/css/mediaelementplayer.min.css'); ?>
<?php $this->addCSS('templates/default/controllers/onebuilder/widgets/start/css/swiper.min.css'); ?>
<?php $this->addCSS('templates/default/controllers/onebuilder/widgets/start/css/jquery.range.css'); ?>

<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/imagesloaded.pkgd.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/isotope.pkgd.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/lity.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/mediaelement.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/mediaelement-and-player.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/swiper.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/typed.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/jquery.magnific-popup.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/anm.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/parallax.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/lightgallery.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/script.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/spectragram.min.js'); ?>
<?php $this->addJS('templates/default/controllers/onebuilder/widgets/start/js/jquery.range-min.js'); ?>