<div id = "<?= $id; ?>" class="bg-wrap-c-light" style = "<?php echo (empty($ptop)) ? 'padding-top: 20px;' : 'padding-top:'.$ptop.'px;'; ?>
                            <?php echo (empty($pbottom)) ? 'padding-top: 20px;' : 'padding-bottom:'.$pbottom.'px;'; ?>
                            <?php if($bgtype == 'background-color') {echo 'background-color: '.$bgcolor.';'; } ?>
                            <?php if($bgtype == 'background-image') {echo 'background-image: url(/upload/'.html_image_src($bgimage, $size_preset='original', $is_add_host=false, $is_relative=true).');
                                background-attachment: '.$bgfixed.';'; } ?>
                            ">
    <div class="container">
        <div class="row">
            <div class="col-md-6 about-img margin-lg-55t">
                <div class="onebuilder-single-img h-100 ">
                    <img src="/upload/<?php echo html_image_src($photo, $size_preset='original', $is_add_host=false, $is_relative=true) ?>" class="h-100 w-100">
                </div>
            </div>
            <div class="col-md-6">
                <div class="row margin-lg-120t margin-lg-50b margin-md-60t margin-sm-0t padding-sm-45t margin-sm-25b">
                    <div class="col-md-12 col-lg-11 offset-lg-1">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="onebuilder-heading onebuilder-heading--classic onebuilder-heading--b-c2">
                                <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                                    <p class="<?= $p_preset; ?>"><?= $desc; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row margin-lg-30t margin-lg-30b">
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
                </div>
            </div>
        </div>
    </div>
</div>