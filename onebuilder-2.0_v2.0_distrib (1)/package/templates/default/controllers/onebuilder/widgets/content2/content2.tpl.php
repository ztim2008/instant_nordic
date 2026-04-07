<?php if ($items){ ?>
<div id = "<?= $id; ?>" class="bg-wrap-c-light" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
        <div class="row padding-lg-110t margin-lg-70b padding-md-70t margin-md-60b padding-sm-45t margin-sm-40b">
            <div class="col-md-12">
                <div class="onebuilder-heading t-center onebuilder-heading--classic onebuilder-heading--b-c2">
                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">

        <?php foreach($items as $item) { ?>
            <div class="col-md-4 margin-md-50b" style = "margin-bottom: 40px;">
                <div class="onebuilder-services onebuilder-services--busns-event">
                    <div class="onebuilder-services__image">
                    <?php $url = href_to($ctype['name'], $item['slug']) . '.html'; $image = html_image_src($item[$image_field], $image_preset, true); ?>
                        <img src="<?php echo $image; ?>" class="js-bg">
                    </div>
                    <div class="onebuilder-services__content">
                        <div class="onebuilder-services__caption">
                            <h5 class="onebuilder-services__title t-bold"><?php html($item['title']); ?></h5>
                        </div>
                        <a href="<?php echo $url; ?>" class="onebuilder-services__link ">+ <?= $view_item; ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>

        </div>
    </div>
</div>
<?php } ?>