<style>

body .swiper--business .swiper-pagination-bullet {
    border: 0px solid #fff; }

body .swiper--business .swiper-pagination-bullet {
    width: 14px;
    height: 14px;
    margin: 0 6px !important;
    background-color: #ed0000;
    opacity: none; }

</style>

<div id = "<?= $id; ?>" class="large-container-wrap" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="row margin-lg-110t margin-md-70t margin-sm-45t">
        <div class="col-md-12">
            <div class="onebuilder-heading t-center onebuilder-heading--classic onebuilder-heading--b-c2">
            <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
            </div>
        </div>
    </div>
    <div class="row margin-lg-40t margin-lg-85b margin-md-45b margin-sm-0t margin-sm-20b">
        <div class="col-md-12">
            <div class="swiper swiper--business">
                <div class="swiper-container" data-speed="500" data-spaceBetween="50" data-slidesPerView="responsive" data-add-slides="5" data-lg-slides="4" data-md-slides="3" data-sm-slides="2" data-xs-slides="1">
                    <div class="swiper-wrapper">

                    <?php foreach ( $photo as $testimonial => $photos ) { ?>
                        <div class="swiper-slide">
                            <div class="tm tm--business">
                                <div class="tm__author">
                                    <div class="tm__avatar">
                                    <img src="/upload/<?php echo html_image_src($photos, $size_preset='original', $is_add_host=false, $is_relative=true) ?>" class="js-bg">
                                    </div>
                                    <div class="tm__info">
                                        <<?= $title_preset2; ?> class="onebuilder-<?= $title_preset2; ?>"><?= $name_array[$testimonial]; ?></<?= $title_preset; ?>>
                                    </div>
                                </div>
                                <div class="tm__content">
                                    <p class="<?= $p_preset; ?>"><?= $rev_array[$testimonial]; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
    </div>
</div>