<div id = "<?= $id; ?>" class="testimonials__wrapp" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
        <div class="row padding-lg-85t padding-lg-85b padding-md-65b">
            <div class="col-md-12">
                <div class="swiper">
                    <div class="swiper-container" data-speed="500" data-spaceBetween="30" data-slidesPerView="responsive" data-add-slides="3" data-lg-slides="3" data-md-slides="2" data-sm-slides="2" data-xs-slides="1">
                    <div class="swiper-wrapper ">

                        <?php
                        foreach ( $photo as $testimonial => $photos ) { ?>
                            <div class="swiper-slide ">
                            <div class="tm tm--default">
                                <div class="tm__author">
                                <div class="tm__avatar">
                                    <img src="/upload/<?php echo html_image_src($photos, $size_preset='original', $is_add_host=false, $is_relative=true) ?>" class="js-bg">
                                    </div>
                                    <div class="tm__info">
                                        <h6 class="tm__name"><?= $name_array[$testimonial]; ?></h6>
                                        <p class="tm__position"><?= $company_array[$testimonial]; ?></p>
                                    </div>
                                </div>
                                <div class="tm__content">
                                    <p class="<?= $p_preset; ?>"><?= $rev_array[$testimonial]; ?></p>
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
</div>