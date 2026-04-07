<section class="padding-lg-40b padding-sm-10b" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
        <div class="row padding-lg-125t padding-md-75t padding-sm-50t margin-lg-60b margin-md-0b">
            <div class="col-md-12 margin-lg-70b margin-md-50b margin-sm-30b">
                <div class="onebuilder-heading t-center onebuilder-heading">
                    <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                </div>
            </div>
            <div class="col-sm-12 margin-md-30b">
                <div class="swiper swiper--saas ">
                    <div class="swiper-container " data-speed="500" data-spaceBetween="30" data-slidesPerView="responsive" data-add-slides="4" data-lg-slides="4" data-md-slides="4" data-sm-slides="3" data-xs-slides="1">

                    
                        <div class="swiper-wrapper ">
                        <?php foreach ( $photo as $team => $photos ) { ?>
                            <div class="swiper-slide ">
                                <div class="onebuilder-member onebuilder-member--saas t-center">
                                    <div class="onebuilder-member__img-holder">
                                        <img class="onebuilder-member__img" src="/upload/<?php echo html_image_src($photos, $size_preset='original', $is_add_host=false, $is_relative=true) ?>">
                                    </div>
                                    <div class="onebuilder-member__text">
                                        <<?= $title_preset2; ?> class="onebuilder-<?= $title_preset2; ?>" style = "text-align: center !important;"><?= $name_array[$team]; ?></<?= $title_preset2; ?>>
                                        <p class="<?= $p_preset; ?>"><?= $dlg_array[$team]; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>