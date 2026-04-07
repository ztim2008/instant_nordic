<style>

.onebuilder-tab__box--overlay-1::before {
    background-color: rgba(<?= $left; ?>);
}

</style>
<div id = "<?= $id; ?>" class="onebuilder-tab js-tab onebuilder-tab--business">
    <div class="onebuilder-tab__content">
        <div class="onebuilder-tab__box js-tab-box active onebuilder-tab__box--overlay-1">
            <img class="onebuilder-tab__box-bg js-bg" src="/upload/<?php echo html_image_src($bg, $size_preset='original', $is_add_host=false, $is_relative=true) ?>">
            <div class="onebuilder-tab__box-inner">
                <div class="container">
                    <div class="row margin-lg-10t">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="onebuilder-heading onebuilder-heading--business onebuilder-heading--divider-t3 onebuilder-heading--t-white">
                                        <h1 class="onebuilder-heading__title"><?= $title; ?></h1>
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-lg-15t">
                                <div class="col-md-12">
                                    <div class="one-cta__links">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>