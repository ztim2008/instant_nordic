<div id = "<?= $id; ?>" class="onebuilder-pricing--home-construction" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
        <div class="container margin-lg-90b margin-md-50b margin-sm-20b">
            <div class="row padding-lg-125t padding-md-70t padding-sm-40t">
                <div class="col-lg-12">
                    <div class="onebuilder-heading t-center onebuilder-heading--construction">
                    <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>" style = "text-align: center !important;"><?= $title; ?></<?= $title_preset; ?>>
                    </div>
                </div>
            </div>
            <div class="row">
            <?php foreach ( $icon as $other => $icons ) { ?>
                <div class="col-xl-3 col-md-6">
                    <div class="onebuilder-pricing onebuilder-pricing--default">
                        <div class="onebuilder-pricing__content">
                            <h5 class="onebuilder-pricing__title t-bold"><?= $htable_array[$other]; ?></h5>
                            <img class="onebuilder-pricing__ico-img margin-lg-30b" src="/upload/<?php echo html_image_src($icons, $size_preset='original', $is_add_host=false, $is_relative=true) ?>">
                            <div class="onebuilder-pricing__description">
                                <ul>
                                    <?= $otable_array[$other]; ?>
                                </ul>
                            </div>
                            <div class="onebuilder-pricing__options">
                            </div>
                            <hr class="d-lg-block">
                        </div>
                        <div class="onebuilder-pricing__cost">
                            <div class="onebuilder-pricing__cost-value"><?= $ptable_array[$other]; ?></div>
                        </div>
                        <div class="onebuilder-pricing__footer">
                            <span class="onebuilder-btn onebuilder-pricing__btn"></span>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
    </div>