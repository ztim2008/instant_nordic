<div id = <?= $id; ?> class="bg-wrap-c-active" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
        <div class="row margin-lg-115t margin-md-65t margin-lg-25b">
            <div class="col-md-12">
                <div class="onebuilder-heading t-center">
                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                    <p class="<?= $p_preset; ?>"><?= $desc; ?></p>
                </div>
            </div>
        </div>
        <div class="row margin-lg-15t margin-lg-65b margin-lg-45b margin-sm-50b">
            <div class="col-lg-10 offset-md-1">
                <div class="onebuilder-clients onebuilder-clients--3-in-row">

                    <?php foreach($logo as $logos) {echo '<div class="onebuilder-clients__holder">
                        <a class="onebuilder-clients__link">
                            <img class="onebuilder-clients__img" src="/upload/'.html_image_src($logos, $size_preset='original', $is_add_host=false, $is_relative=true).'">
                        </a>
                    </div>'; } ?>

                </div>
            </div>
        </div>
    </div>