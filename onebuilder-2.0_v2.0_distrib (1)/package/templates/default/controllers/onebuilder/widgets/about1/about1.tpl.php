<style>

.one-headingone-heading--main .one-heading__desc {
    margin-top: 38px;
    margin-bottom: 50px;
    max-width: 100%;
    html, body{
  min-height: 100%;
}

</style>

<section id = "<?= $id; ?>" class="container-fluid" 
                    style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
        <div class="container">
            <div class="row margin-lg-140t margin-sm-50t margin-md-80t padding-lg-120b padding-md-80b padding-sm-50b">
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-12 margin-lg-30b">
                            <div class="one-headingone-heading--main ">
                                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                                <p class="<?= $p_preset; ?>"><?= $about; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12 margin-md-30b">
                            <div class="onebuilder-btn-container">
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
                <div class="col-md-4 margin-md-30b">
                    <div class="one-cb one-cb--main main-home-cb ">
                        <div class="one-cb__img">
                            <img class="js-bg" src="/upload/<?php echo html_image_src($image1, $size_preset='big', $is_add_host=false, $is_relative=true) ?>" style="display: none;">
                        </div>
                        <div class="one-cb__caption">
                            <i class="one-cb__icon icon <?= $icon1; ?>"></i>
                            <<?= $title_preset2; ?> class="onebuilder-<?= $title_preset2; ?>"><?= $title1; ?></<?= $title_preset2; ?>>
                        </div>
                        <p class="<?= $p2_preset; ?>"><?= $text1; ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="one-cb one-cb--main main-home-cb ">
                        <div class="one-cb__img">
                            <img class="js-bg" src="/upload/<?php echo html_image_src($image2, $size_preset='big', $is_add_host=false, $is_relative=true) ?>" style="display: none;">
                        </div>
                        <div class="one-cb__caption">
                            <i class="one-cb__icon icon <?= $icon2; ?>"></i>
                            <<?= $title_preset3; ?> class="onebuilder-<?= $title_preset3; ?>"><?= $title2; ?></<?= $title_preset3; ?>>
                        </div>
                        <p class="<?= $p3_preset; ?>"><?= $text2; ?></p>
                    </div>
                </div>
            </div>
        </div> 
</section>

