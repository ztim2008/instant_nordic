<div id = "<? $id; ?>" class="container">
    <div class="row margin-lg-115t margin-md-65t margin-lg-45b">
        <div class="col-md-8 offset-md-2">
            <div class="onebuilder-heading t-center">
            <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                <p class="<?= $p_preset; ?>"><?= $desc; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="swiper swiper--websites" style = "padding-bottom: 100px;">
    <div class="swiper-container" data-delay="4000" data-speed="1000" data-loop="true" data-centeredSlides="true" data-spaceBetween="50" data-slidesPerView="auto" data-add-slides="3" data-lg-slides="3" data-md-slides="2" data-sm-slides="1" data-xs-slides="1">
        
            <div class="swiper-wrapper ">
                <?php
                foreach ($photo as $photos){ ?>

                <div class="swiper-slide ">
                    <img src="/upload/<?php echo html_image_src($photos, $size_preset='original', $is_add_host=false, $is_relative=true) ?>">
                </div>

                <?php } ?>
            </div>
            
        
    </div>
</div>
