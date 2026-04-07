<div id = "<?= $id; ?>" class="container-fluid" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
    <div class="row margin-lg-120t margin-lg-100b margin-md-75t margin-md-20b margin-sm-10b margin-sm-45t">
        <div class="col-md-4 col-lg-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="onebuilder-heading onebuilder-heading--classic onebuilder-heading--b-c2">
                    <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7 col-lg-8 offset-lg-1 padding-sm-45t">
            <div class="row">
                <div class="col-md-12">
                    <div class="onebuilder-heading onebuilder-heading--classic onebuilder-heading--b-c2">
                        <p class="<?= $p_preset; ?>"><?= $desc; ?></p>
                    </div>
                </div>
            </div>
            <div class="row margin-lg-75t margin-sm-40t">

            <?php foreach ( $perc_array as $other => $percs ) { ?>
                <div class="col-md-4 margin-md-50b margin-sm-30b" style = "margin-bottom: 20px;">
                    <div class="onebuilder-progress onebuilder-progress--simple t-center">
                        <div class="onebuilder-progress__chart-holder">
                            <svg class="onebuilder-progress__chart" viewbox="0 0 33.83098862 33.83098862" xmlns="http://www.w3.org/2000/svg">
                                <circle class="onebuilder-progress__chart-bg" fill="none" cx="16.91549431" cy="16.91549431" r="15.91549431" />
                                <circle class="onebuilder-progress__chart-circle js-chart-circle" stroke-dasharray="<?= $percs; ?>,100" stroke-linecap="round" fill="none" cx="16.91549431" cy="16.91549431" r="15.91549431" />
                            </svg>
                            <h3 class="onebuilder-progress__chart-number"><?= $percs; ?> <?= $attr; ?></h3>
                        </div>
                        <p class="<?= $p_preset2; ?>" style = "margin-top: 10px;"><?= $name_array[$other]; ?></p>
                    </div>
                </div>
            <?php } ?>

            </div>
        </div>
    </div>
    </div>
</div>