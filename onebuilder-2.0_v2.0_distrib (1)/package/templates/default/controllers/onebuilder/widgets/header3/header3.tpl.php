<div id = <?= $id; ?> class="onebuilder-titlebar one-ban--main onebuilder-titlebar--political onebuilder-banner--political">
    <div class="onebuilder-titlebar__main bg-color" style = "<?= $css_section; ?>">
        <img class="js-bg" src="/upload/<?php echo html_image_src($bg, $size_preset='original', $is_add_host=false, $is_relative=true) ?>">
        <div class="onebuilder-titlebar__overlay c-2 onebuilder-titlebar__overlay--4"></div>
        <div class="onebuilder-titlebar__content w-1200">
            <div class="onebuilder-titlebar__text">
                <h1 class="onebuilder-titlebar__title  t-white t-semibold t-center t-uppercase" style = "<?= $css_title; ?>"><?= $title; ?></h1>
                <h4 class="onebuilder-titlebar__subtitle t-white font-style-italic padding-lg-10b t-regular t-center" style = "<?= $css_desc; ?>"><?= $desc; ?></h4>
                <div class="t-center margin-lg-45t">
                        <?php
                            switch ($typebutton) {
                            case 'scrollto':
                                echo '<a href="'.$scrolllink.'" class="onebuilder-btn onebuilder-btn--alter onebuilder-btn--shadow" style = "'.$css_button.'">'.$titlebutton.'</a>';
                            break;
                            case 'linkto':
                                echo '<a href="'.$link.'" target = "_'.$linkopt.'" class="onebuilder-btn onebuilder-btn--alter onebuilder-btn--shadow" style = "'.$css_button.'">'.$titlebutton.'</a>';
                            break;
                            case 'none':
                                echo '';
                            break;
                            }
                        ?>
                        <?php
                            switch ($typebutton2) {
                            case 'scrollto':
                                echo '<a href="'.$scrolllink2.'" class="onebuilder-btn onebuilder-btn--underline  onebuilder-btn--light" style = "'.$css_button2.'">'.$titlebutton2.'</a>';
                            break;
                            case 'linkto':
                                echo '<a href="'.$link2.'" target = "_'.$linkopt2.'" class="onebuilder-btn onebuilder-btn--underline  onebuilder-btn--light" style = "'.$css_button2.'">'.$titlebutton2.'</a>';
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