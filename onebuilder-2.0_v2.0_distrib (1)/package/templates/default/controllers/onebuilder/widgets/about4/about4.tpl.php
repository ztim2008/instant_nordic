<style>
blockquote.q-bg {
    background-color: <?= $bg_color; ?>;
}

.onebuilder-btn-container .onebuilder-btn:last-child {
    margin-right: 0;
}
.onebuilder-blockquote--political blockquote .onebuilder-btn {
    width: 100%;
    text-align: right;
    color: #fff;
    display: inline-block;
    padding: 0;
    background: 0 0;
    border: none;
}
</style>
<div id = <?= $id; ?> class="onebuilder-blockquote--political margin-lg-115t margin-lg-120b margin-md-80t margin-md-80b margin-sm-50t margin-sm-50b" 
                            style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                            background-attachment: '.$bgfixed.';'; } ?>">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="onebuilder-single-img">
                    <img src="/upload/<?php echo html_image_src($img, $size_preset='original', $is_add_host=false, $is_relative=true) ?>" style = "<?= $css_image; ?>">
                </div>
                <blockquote class="q-bg t-left">
                    <div class="about_blockquote">
                        <div class="row">
                            <div class="col-lg-12 padding-sm-20b">
                                <div class="onebuilder-heading onebuilder-heading--political">
                                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 padding-lg-35t padding-lg-45b padding-sm-15t padding-sm-20b">
                                    <p class="<?= $p_preset; ?>"><?= $desc; ?></p>
                            </div>
                        </div>
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
                </blockquote>
            </div>
        </div>
    </div>
</div>