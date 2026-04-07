<div id = "<?= $id; ?>" class="onebuilder-titlebar onebuilder-titlebar--height-600 onebuilder-titlebar--political" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
<?php if ($is_overlay == true) { echo '<div class = "overlay" style = "position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: '.$o_color.'; opacity: .'.$o_opacity.';"></div>'; } ?>
<div class="onebuilder-titlebar__main bg-color">
    <div class="onebuilder-titlebar__overlay c-2 onebuilder-titlebar__overlay--4"></div>
    <div class="onebuilder-titlebar__content w-1200">
        <div class="onebuilder-titlebar__text">
            <div class="onebuilder-single-img t-center margin-lg-35b">
                <img src="/upload/<?php echo html_image_src($icon, $size_preset='small', $is_add_host=false, $is_relative=true) ?>">
            </div>
            <p class="<?= $p_preset; ?>" style = "text-align: center;"><?= $text; ?></p>
            <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
            <div class="t-center margin-lg-45t">
                <?php
                switch ($typebutton) {
                case 'scrollto':
                    echo '<a href="'.$scrolllink.'" class="'.$bpreset.'">'.$titlebutton.'</a>';
                break;
                case 'linkto':
                    echo '<a href="'.$link.'" target = "_'.$linkopt.'" class="'.$bpreset.'">'.$titlebutton.'</a>';
                break;
                case 'none':
                    echo '';
                break;
                }
                ?>
            </div>
        </div>
    </div>
</div>
</div>