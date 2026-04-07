<div id = "<?= $id; ?>" class="bg-wrap-c-active" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
        <div class="container">
            <div class="row padding-lg-55t padding-lg-50b padding-lg-70t padding-lg-70b">
                <div class="col-lg-8 offset-lg-2">
                    <div class="one-cta one-cta--modern one-cta--t-white">
                        <div class="one-cta__text">
                        <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                        </div>
                        <div class="one-cta__links">
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
    </div>

                            