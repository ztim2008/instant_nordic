<style>
.onebuilder-line {
    <?= $line_style; ?>
}
</style>
<div class = "home-event-wrap-tickets" id = "pricing" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
        <div class="row padding-sm-50t padding-md-70t padding-lg-115t">
            <div class="col-md-8 offset-md-2">
                <div class="onebuilder-heading t-center onebuilder-heading--divider-t1">
                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>" style = "text-align: center !important;"><?= $title; ?></<?= $title_preset; ?>>
                </div>
            </div>
        </div>
        <div class="row margin-sm-50t margin-lg-70t padding-md-70b padding-lg-165b">
        <?php foreach ( $htable_array as $other => $htables ) { ?>
            <div class="col-md-4 margin-sm-50b">
                <div class="onebuilder-pricing onebuilder-pricing--home-event">
                    <div class="onebuilder-pricing__content">
                        <div class="onebuilder-pricing__header">
                            <h4 class="onebuilder-pricing__title t-semibold"><?= $htables; ?></h4>
                        </div>
                        <h1 class="onebuilder-pricing__cost-value"><?= $ptable_array[$other]; ?></h1>
                        <ul class="onebuilder-pricing__details">
                            <li class="onebuilder-pricing__detail">
                                <?= $otable_array[$other]; ?>
                            </li>
                        </ul>
                    </div>
                </div>  
            </div>
        <?php } ?>
        </div>
    </div>
</div>