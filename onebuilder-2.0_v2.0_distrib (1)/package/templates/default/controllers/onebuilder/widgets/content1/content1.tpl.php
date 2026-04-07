<style>
.related-posts .post .content-top-wrapper .image-wrapper img {
    display: block !important;
    width: 100%;
    height: 300px;
    position: relative;
}
</style>

<?php if ($items){ ?>
<style>
body .swiper-pagination-bullet-active {
    background-color: <?= $dot_color; ?>;
}
</style>
<div id = <?= $id; ?> class="home-blog" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
        <div class="row padding-md-70t padding-lg-115t padding-lg-10b">
            <div class="col-md-8 offset-md-2">
                <div class="onebuilder-heading t-center">
                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                    <p class="onebuilder-heading__desc"><?= $desc; ?></p>
                </div>
            </div>
        </div>
        <div class="row padding-lg-105b padding-md-75b">
            <div class="related-posts alt">
                <div class="container">
                
                    <div class="swiper swiper--recent-posts">
                        <div class="swiper-container" data-speed="500" data-spaceBetween="30" data-slidesPerView="responsive" data-add-slides="3" data-lg-slides="3" data-md-slides="3" data-sm-slides="2" data-xs-slides="1">
                        <?php if ($dot_pos == 'top') { echo
                            '<div class="swiper-pagination"></div>'
                        ; } ?>
                            <div class="swiper-wrapper">

                            <?php foreach($items as $item) { ?>
                                <div class="swiper-slide">
                                    <article class="post format-image">
                                        <div class="content-top-wrapper">
                                            <div class="image-wrapper">
                                            <?php $url = href_to($ctype['name'], $item['slug']) . '.html'; $image = html_image_src($item[$image_field], $image_preset, true); ?>
                                            <a href="<?php echo $url; ?>">
                                            <?php echo (empty($image)) ? '<img src="/upload/onebuilder/no-image.jpg">' : '<img src="'.$image.'">'; ?>
                                            </a>
                                            </div>
                                        </div>
                                        <div class="content-wrapper">
                                            <h1 class="post-title"><a href="<?php echo $url; ?>"><?php html($item['title']); ?></a></h1>
                                            <p><?php echo $item[$teaser_field]; ?></p>
                                            <a href="<?php echo $url; ?>" class="onebuilder-btn onebuilder-btn--underline"><?= $view_item; ?></a>
                                        </div>
                                    </article>
                                </div>
                            <?php } ?>


                            </div>
                        </div>
                        <?php if ($dot_pos == 'bottom') { echo
                            '<div class="swiper-pagination"></div>'
                        ; } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>