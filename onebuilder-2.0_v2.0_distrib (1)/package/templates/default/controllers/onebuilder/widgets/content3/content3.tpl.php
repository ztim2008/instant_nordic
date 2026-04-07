<style>

.new .content-wrapper {
    padding: 20px 35px 5px;
    margin-bottom: 0;
}

.new {
    background-color: #fff;
    position: relative;
    min-height: 335px;
    margin-bottom: 0;
    -webkit-transition: .2s all;
    transition: .2s all;
    height: auto;
    -webkit-box-shadow: 0 0 35.7px 2.3px rgba(0, 0, 0, .09);
    box-shadow: 0 0 35.7px 2.3px rgba(0, 0, 0, .09);
}

.new .content-top-wrapper .image-wrapper img {
    display: block;
    width: 100%;
    height: 300px;
}

</style>

<?php
$no_image = '/upload/onebuilder/no-image.jpg';
?>

<div id = "<?= $id; ?>" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="large-container-wrap">
        <div class="row padding-lg-110t padding-md-70t padding-sm-45t">
            <div class="col-md-12">
                <div class="onebuilder-heading t-center onebuilder-heading--political">
                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>" style = "text-align: center !important;"><?= $title; ?></<?= $title_preset; ?>>
                    <p class="<?= $p_preset; ?>"><?= $desc; ?></p>
                </div>
            </div>
        </div>
        <div class="row padding-lg-30t padding-md-0t padding-lg-105b padding-md-75b padding-sm-45b">
            <div class="related-posts alt news-posts">
                <div class="swiper swiper--recent-posts">
                    <div class="swiper-container" data-speed="500" data-spaceBetween="30" data-slidesPerView="responsive" data-add-slides="4" data-lg-slides="4" data-md-slides="3" data-sm-slides="2" data-xs-slides="1">
                    <?php if ($dot_pos == 'top') { echo '<div class="swiper-pagination"></div>' ; } ?>
                        <div class="swiper-wrapper">
                            <?php foreach($items as $item) { ?>
                            <div class="swiper-slide">
                            <a href = "#"><article class="new format-image border-radius-5">
                                    <div class="content-top-wrapper">
                                        <div class="image-wrapper">
                                        <?php $url = href_to($ctype['name'], $item['slug']) . '.html'; $image = html_image_src($item[$image_field], $image_preset, true); ?>
                                        <a href="<?php echo $url; ?>">
                                        <?php echo (empty($image)) ? '<img src="/upload/onebuilder/no-image.jpg" class="border-radius-5t">' : '<img src="'.$image.'" class="border-radius-5t">'; ?>
                                        </a>
                                        </div>
                                    </div>
                                    <div class="content-wrapper">
                                        <h4 class="new-title" style = "font-family: inherit; font-weight: bold;"><a href="<?php echo $url; ?>"><?php html($item['title']); ?></a></h4>
                                        <p><?php echo $item[$teaser_field]; ?></p>
                                    </div>
                                </article></a>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if ($dot_pos == 'bottom') { echo '<div class="swiper-pagination"></div>'; } ?>
                </div>
            </div>
        </div>
    </div>
</div>