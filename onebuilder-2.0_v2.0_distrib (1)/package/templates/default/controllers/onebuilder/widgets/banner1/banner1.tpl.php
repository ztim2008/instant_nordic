<div id = "<?= $id; ?>" class="bg-wrap" style = "background-attachment: <?= $bg_att; ?>;">
        <img src="/upload/<?php echo html_image_src($bg, $size_preset='original', $is_add_host=false, $is_relative=true) ?>" class="js-bg">
        <div class="container">
            <div class="row padding-lg-145t padding-lg-135b padding-md-60t padding-md-60b  margin-sm-30t margin-sm-10b">
                <div class="col-md-12">
                    <div class="one-cta one-cta--classic t-center one-cta--t-white">
                        <div class="one-cta__text">
                            <p class="<?= $p_preset; ?>"><?= $sub; ?></p>
                            <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                        </div>
                        <div class="one-cta__links" style = "margin-top: 50px;">
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