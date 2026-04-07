<style>
.services2 {
    margin-top: 75px;
}
.onebuilder-content-block--business .onebuilder-content-block__img {
    margin: 0 0 15px;
    background-position: left;
    background-size: contain;
}
</style>
<div id = "<?= $id; ?>" class="business-services-wrap" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
        <div class="row margin-lg-90t margin-lg-105b margin-md-70t margin-md-60b margin-sm-40t margin-sm-45b">
            <div class="col-md-5">
                <div class="row">
                    <div class="col-md-12">
                        <div class="onebuilder-heading onebuilder-heading--classic onebuilder-heading--b-c2">
                        <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                        </div>
                    </div>
                </div>
                <div class="row margin-lg-50t margin-md-30t">
                    <div class="col-md-12">
                        <div class="onebuilder-btn-container">
                            <?php
                            switch ($typebutton) {
                            case 'scrollto':
                                echo '<a href="'.$scrolllink.'" class="'.$bpreset.'">'.$titlebutton.'</a>';
                            break;
                            case 'linkto':
                                echo '<a href="'.$link.'" target = "_'.$linkopt.'" class="'.$bpreset.'">'.$titlebutton.'</a>';
                            break;
                            case 'none':
                                echo '';
                            break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 col-lg-6 offset-lg-1">
                <div class="row margin-lg-20t margin-md-0t margin-sm-45t">
                    <?php foreach ( $icon as $other => $icons ) { ?>
                    <div class="col-md-6">
                        <div class="onebuilder-content-block onebuilder-content-block--business">
                            <div class="onebuilder-content-block__img">
                                <img class="js-bg" src="/upload/<?php echo html_image_src($icons, $size_preset='original', $is_add_host=false, $is_relative=true) ?>">
                            </div>
                            <div class="onebuilder-content-block__descr transition-none">
                                <<?= $title_preset2; ?> class="onebuilder-<?= $title_preset2; ?>"><?= $title_array[$other]; ?></<?= $title_preset; ?>>
                                <div class="onebuilder-content-block__info">
                                    <p class="<?= $p_preset; ?>" style = "margin-top: 15px;"><?= $desc_array[$other]; ?></p>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>