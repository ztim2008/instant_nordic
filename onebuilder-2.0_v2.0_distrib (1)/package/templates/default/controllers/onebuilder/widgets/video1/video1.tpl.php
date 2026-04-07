<div class="section-video" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="one-video one-video--political one-video--play-center">
                    <div class="one-video__img">
                        <img class="js-bg" src="/upload/<?php echo html_image_src($cover, $size_preset='original', $is_add_host=false, $is_relative=true) ?>">
                    </div>
                    <a href="<?= $video; ?>" class="one-video__link js-mp-video">
                        <i class="ti-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>