<section class="onebuilder-banner onebuilder-banner--full-height d-flex onebuilder-banner--saas d-flex" style="background-image: url('/upload/<?php echo html_image_src($bg, $size_preset='original', $is_add_host=false, $is_relative=true) ?>')">
    <div class="container padding-lg-90t padding-sm-15t">
        <div class="row">
            <div class="col-md-6 padding-lg-80t padding-md-50t padding-sm-5t">
                <div>
                    <div class="onebuilder-heading t-left md-t-center onebuilder-heading--t-white">
                        <h1 class="onebuilder-heading__title t-light"><?= $title; ?></h1>
                    </div>
                </div>
                <div class="margin-lg-20t">
                    <div class="onebuilder-heading t-left md-t-center onebuilder-heading--t-white">
                        <h5 class="onebuilder-heading__title f-18 t-light "><?= $desc; ?></h5>
                    </div>
                </div>
                <div class="margin-lg-55t margin-md-40t">
                    <div class="onebuilder-btn-container t-left md-t-center">
                        <?php
                            switch ($typebutton) {
                            case 'scrollto':
                                echo '<a href="'.$scrolllink.'" class="onebuilder-btn onebuilder-btn--alter onebuilder-btn--shadow">'.$titlebutton.'</a>';
                            break;
                            case 'linkto':
                                echo '<a href="'.$link.'" target = "_'.$linkopt.'" class="onebuilder-btn onebuilder-btn--alter onebuilder-btn--shadow">'.$titlebutton.'</a>';
                            break;
                            case 'none':
                                echo '';
                            break;
                            }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 margin-md-50t margin-sm-0t">
                <img src="/upload/<?php echo html_image_src($bgr, $size_preset='original', $is_add_host=false, $is_relative=true) ?>" class="onebuilder-banner__image onebuilder-banner__image--absolute">
            </div>
        </div>
    </div>
</section>