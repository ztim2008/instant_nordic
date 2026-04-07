<div id = <?= $id; ?> class="bg-wrap onebuilder-banner--height-520" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>
                            ">
<div class="container d-flex h-100 justify-content-center align-items-center">
    <div class="row">
        <div class="col-md-8 offset-md-2 padding-lg-35t padding-md-0t">
            <div class="one-cta one-cta--business one-cta--t-white t-center">
                <div class="one-cta__text">
                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                </div>
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