<div id = "<?= $id; ?>" class="about-counter-political" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container padding-lg-75t padding-lg-60b padding-md-50t padding-md-50b padding-sm-40t padding-sm-40b">
        <div class="row">
            

            <?php foreach ( $perc_array as $other => $percs ) { ?>
                <div class="col-md-3 margin-sm-20b">
                <div class="onebuilder-counter onebuilder-counter--political">
                    <div class="onebuilder-counter__img">
                        <img class="js-bg" src="/upload/onebuilder/counter.png">
                    </div>
                    <h6 class="onebuilder-counter__number js-counter"><?= $percs; ?></h6>
                    <p class="<?= $p_preset; ?>"><?= $name_array[$other]; ?></p>
                </div>
                </div>
            <?php } ?>

            
        </div>
    </div>
</div>