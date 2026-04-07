<style>
.onebuilder-video-link__label {
    font-size: 14px;
    font-weight: 500;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1.4px;
    background: #E2506D;
    padding: 20px;
}
</style>
<div id = "<?= $id; ?>" class="container-fluid" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="row row--flex row--v-center">
        <div class="col-sm-12 col-md-6 col-xs-12 sm-t-center margin-xs-20b">
            <img src="/upload/<?php echo html_image_src($img, $size_preset='original', $is_add_host=false, $is_relative=true) ?>" alt="" class="w-100">
        </div>
        <div class="col-lg-5 offset-lg-1 col-md-6 col-sm-12 col-xs-12">
            <div>
                <div class="onebuilder-heading t-left sm-t-center onebuilder-heading">
                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                </div>
            </div>
            <div class="margin-lg-25t">
                <div class="onebuilder-heading t-left sm-t-center onebuilder-heading">
                    <p class="<?= $p_preset; ?>"><?= $desc; ?></p>
                </div>
            </div>
            <div class="margin-lg-45t margin-lg-40t margin-lg-30b sm-t-center">
                <a href="<?= $video; ?>" class="onebuilder-video-link js-mfp-video">
                    <span class="<?= $bpreset; ?>"><?= $btitle; ?></span>
                </a>
            </div>
        </div>
    </div>
</div>