<?php
/**
 * Основной макет шаблона nordic.
 * Первая версия сохраняет совместимость с dynamic layout modern,
 * но добавляет собственный shell и именованные slots.
 */
/** @var cmsTemplate $this */

$renderSlot = function($positions, $slotClass, $wrapper = 'wrapper_plain') {
    $has_content = false;

    foreach ($positions as $position) {
        if (!$this->hasWidgetsOn($position)) {
            continue;
        }

        if (!$has_content) {
            echo '<section class="' . html($slotClass, false) . '">';
            echo '<div class="nordic-shell__slot-panel">';
            $has_content = true;
        }

        echo '<div class="nordic-shell__slot nordic-shell__slot--' . html($position, false) . '">';
        $this->widgets($position, false, $wrapper);
        echo '</div>';
    }

    if ($has_content) {
        echo '</div>';
        echo '</section>';
    }
};

$has_left_content_sidebar = $this->hasWidgetsOn('content_sidebar_left');
$has_right_content_sidebar = $this->hasWidgetsOn('content_sidebar_right');
$has_content_body_widgets = $this->hasWidgetsOn('content_body');

$content_grid_class = 'nordic-shell__content-grid';
if ($has_left_content_sidebar) {
    $content_grid_class .= ' nordic-shell__content-grid--with-left';
}
if ($has_right_content_sidebar) {
    $content_grid_class .= ' nordic-shell__content-grid--with-right';
}
?>
<!DOCTYPE html>
<html <?php echo html_attr_str(($this->layout_params['attr'] ?? []), false); ?>>
    <head>
        <title><?php $this->title(); ?></title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="<?php echo cmsForm::getCSRFToken(); ?>">
<?php if (!$config->disable_copyright) { ?>
        <meta name="generator" content="InstantCMS">
<?php } ?>
    <?php if (!empty($this->options['head_html_top'])) { ?>
        <?php echo $this->options['head_html_top'] . "\n"; ?>
    <?php } ?>
<?php
        $this->addMainTplCSSName(['theme']);
        if (!empty($this->options['font_type']) && $this->options['font_type'] === 'gfont') {
            $this->addHead('<link rel="dns-prefetch" href="https://fonts.googleapis.com">');
            $this->addHead('<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>');
            $this->addHead('<link rel="dns-prefetch" href="https://fonts.gstatic.com">');
            $this->addHead('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
            $this->addCSS('https://fonts.googleapis.com/css?family=' . $this->options['gfont'] . ':400,400i,700,700i&display=swap&subset=cyrillic-ext', false);
        }
        $this->addMainTplJSName('jquery', true);
        $this->addMainTplJSName(['vendors/popper.js/js/popper.min', 'vendors/bootstrap/bootstrap.min']);
        $this->onDemandTplJSName(['vendors/photoswipe/photoswipe.min']);
        $this->onDemandTplCSSName(['photoswipe']);
        $this->addMainTplJSName(['core', 'modal']);
?>
        <?php $this->head(true, !empty($this->options['js_print_head']), true); ?>
    <?php if (!empty($this->options['favicon_head_html'])) { ?>
        <?php echo $this->options['favicon_head_html'] . "\n"; ?>
    <?php } ?>
    <?php if (!empty($this->options['favicon']['path'])) { ?>
        <link rel="icon" href="<?php echo $config->upload_root . $this->options['favicon']['path']; ?>" type="<?php echo pathinfo($this->options['favicon']['path'], PATHINFO_EXTENSION) === 'svg' ? 'image/svg+xml' : 'image/x-icon'; ?>">
    <?php } else { ?>
        <link rel="icon" href="<?php echo $config->root; ?>templates/modern/images/favicons/favicon.ico" type="image/x-icon">
    <?php } ?>
    </head>
    <body id="<?php echo $device_type; ?>_device_type" data-device="<?php echo $device_type; ?>" class="d-flex flex-column min-vh-100 nordic-template<?php if (!empty($body_classes)) { ?> <?php html(implode(' ', $body_classes)); ?><?php } ?> <?php html($this->options['body_classes'] ?? ''); ?>">
        <a class="nordic-skip-link" href="#nordic-content-frame">Перейти к содержимому</a>
        <div class="nordic-shell">

            <?php if (!$config->is_site_on) { ?>
                <div class="nordic-shell__notice">
                    <?php if (cmsUser::isAdmin()) { ?>
                        <?php printf(ERR_SITE_OFFLINE_FULL, href_to('admin', 'settings', 'siteon')); ?>
                    <?php } else { ?>
                        <?php echo ERR_SITE_OFFLINE; ?>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php $renderSlot(['site_top', 'top'], 'nordic-shell__site-top'); ?>

            <header class="nordic-shell__header">
                <?php $renderSlot(['header_primary', 'header'], 'nordic-shell__header-primary'); ?>
                <?php $renderSlot(['header_secondary'], 'nordic-shell__header-secondary'); ?>
            </header>

            <?php $renderSlot(['hero'], 'nordic-shell__hero'); ?>
            <?php $renderSlot(['before_content'], 'nordic-shell__before-content'); ?>

            <main class="nordic-shell__main">
                <div class="nordic-shell__content-frame" id="nordic-content-frame" data-slot="content_body">
                    <div class="<?php html($content_grid_class); ?>">
                        <?php if ($has_left_content_sidebar) { ?>
                            <aside class="nordic-shell__content-sidebar nordic-shell__content-sidebar--left" data-slot="content_sidebar_left">
                                <?php $this->widgets('content_sidebar_left', false, 'wrapper_plain'); ?>
                            </aside>
                        <?php } ?>
                        <div class="nordic-shell__content-body-slot" data-slot="content_body">
                            <?php if ($has_content_body_widgets) { ?>
                                <div class="nordic-shell__content-body-widgets">
                                    <?php $this->widgets('content_body', false, 'wrapper_plain'); ?>
                                </div>
                            <?php } ?>
                            <div class="nordic-shell__content-body-runtime">
                                <?php $this->renderLayoutChild('scheme', ['rows' => $rows]); ?>
                            </div>
                        </div>
                        <?php if ($has_right_content_sidebar) { ?>
                            <aside class="nordic-shell__content-sidebar nordic-shell__content-sidebar--right" data-slot="content_sidebar_right">
                                <?php $this->widgets('content_sidebar_right', false, 'wrapper_plain'); ?>
                            </aside>
                        <?php } ?>
                    </div>
                </div>
            </main>

            <?php $renderSlot(['after_content'], 'nordic-shell__after-content'); ?>

            <footer class="nordic-shell__footer">
                <?php $renderSlot(['footer_primary', 'footer'], 'nordic-shell__footer-primary'); ?>
                <?php $renderSlot(['footer_secondary'], 'nordic-shell__footer-secondary'); ?>
            </footer>
        </div>

        <?php if (!empty($this->options['show_top_btn'])) { ?>
            <a class="btn btn-secondary btn-lg" href="#<?php echo $device_type; ?>_device_type" id="scroll-top">
                <?php html_svg_icon('solid', 'chevron-up'); ?>
            </a>
        <?php } ?>
        <?php if (!empty($this->options['show_cookiealert'])) { ?>
            <div class="alert text-center py-3 border-0 rounded-0 m-0 position-fixed fixed-bottom icms-cookiealert" id="icms-cookiealert">
                <div class="container">
                    <?php echo $this->options['cookiealert_text']; ?>
                    <button type="button" class="ml-2 btn btn-primary btn-sm acceptcookies">
                        <?php echo LANG_MODERN_THEME_COOKIEALERT_AGREE; ?>
                    </button>
                </div>
            </div>
        <?php } ?>
        <?php if ($config->debug && cmsUser::isAdmin()) { ?>
            <?php $this->renderAsset('ui/debug', ['core' => $core]); ?>
        <?php } ?>
        <script nonce="<?php echo $this->nonce; ?>"><?php echo $this->getLangJS('LANG_LOADING', 'LANG_ALL', 'LANG_COLLAPSE', 'LANG_EXPAND'); ?></script>
        <?php if (empty($this->options['js_print_head'])) { ?>
            <?php $this->printJavascriptTags(); ?>
        <?php } ?>
        <?php $this->bottom(); ?>
        <?php $this->onDemandPrint(); ?>
    </body>
</html>