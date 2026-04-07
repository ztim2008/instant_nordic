<style>

i.iconservices1 {
    display: inline;
    line-height: unset;
    vertical-align: middle;
    color: <?= $icolor; ?>;
    font-size: 25px;
    margin-right: 25px;
}

</style>
<div id = "<? $id; ?>" class="container">
    <div class="row margin-lg-115t margin-md-65t margin-lg-45b">
        <div class="col-md-8 offset-md-2">
            <div class="onebuilder-heading t-center">
            <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                <p class="<?= $p_preset; ?>"><?= $desc; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row margin-lg-70t margin-lg-70b margin-md-45t margin-md-45b">
             
        <?php foreach ( $name_array as $other => $names ) { ?>
            <div class="col-md-4">
            <div class="onebuilder-content-block onebuilder-content-block--feature t-left" style = "margin-bottom: 40px;">
                <div class="onebuilder-content-block__descr transition-none">
                    <div class="onebuilder-content-block__title-holder">
                    <i class="one-cb__icon iconservices1 <?= $icon_array[$other]; ?>"></i>
                        <<?= $title_preset2; ?> class="onebuilder-<?= $title_preset2; ?>"><?= $names; ?></<?= $title_preset2; ?>>
                    </div>
                    <div class="onebuilder-content-block__info">
                        <p class="<?= $p_preset2; ?>" style = "margin-top: 15px;"><?= $text_array[$other]; ?></p>
                    </div>
                </div>
            </div>
            </div>
        <?php } ?>

        
    </div>
</div>