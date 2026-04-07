<?php $this->addCSS('templates/default/controllers/onebuilder/widgets/services3/css/services3.css'); ?>
<section id = "<?= $id; ?>" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
        <div class="row">
        <?php foreach ( $name_array as $other => $names ) { ?>
            <div class="col-md-4">
                <div class="content-box-1">
                    <div class="content-box-1-icon-wrapper">
                        <div class="content-box-1-circle">
                            <div class="content-box-1-circle-bullet"></div>
                        </div>
                        <div class="content-box-1-circle">
                            <div class="content-box-1-circle-bullet"></div>
                        </div>
                        <span class="content-box-1-icon">
                            <i class="<?= $icon_array[$other]; ?>"></i>
                        </span>
                    </div>
                    <div class="content-box-1-content-wrapper">
                    <<?= $title_preset2; ?> class="onebuilder-<?= $title_preset2; ?>"><?= $names; ?></<?= $title_preset2; ?>>
                    <p class="<?= $p_preset2; ?>" style = "margin-top: 15px;"><?= $text_array[$other]; ?></p>
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
    </div>
</section>
