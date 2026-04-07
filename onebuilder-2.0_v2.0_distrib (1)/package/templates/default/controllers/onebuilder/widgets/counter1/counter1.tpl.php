<div id = "<?= $id; ?>" class="about-counter-wrapp" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2 margin-lg-55b margin-md-35b">
                <div class="onebuilder-heading t-center">
                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                    <p class="<?= $p_preset; ?>"><?= $desc; ?></p>
                </div>
            </div>
        </div>
        <div class="row margin-lg-30t margin-sm-0t">

        <?php foreach ( $icon_array as $other => $icons ) { ?>
            <div class="col-sm-4 margin-sm-30b" style = "margin-bottom: 30px;">
                <div class="onebuilder-counter onebuilder-counter--classic">
                    <i class="onebuilder-counter__icon icon <?= $icon_array[$other]; ?>" style = "color: <?= $icon_color; ?>;"></i>
                    <<?= $title_preset2; ?> class="onebuilder-<?= $title_preset2; ?> js-counter"><?= $counter_array[$other]; ?></<?= $title_preset2; ?>>
                    <p class="<?= $p_preset2; ?>"><?= $ctitle_array[$other]; ?></h5>
                </div>
            </div>
        <?php } ?>

        </div>
    </div>
</div>