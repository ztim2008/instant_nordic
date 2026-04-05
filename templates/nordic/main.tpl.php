<?php
/**
 * Основной макет шаблона nordic.
 * Первая версия сохраняет совместимость с dynamic layout modern,
 * но добавляет собственный shell и именованные slots.
 */
/** @var cmsTemplate $this */

$shell_scheme_file = cmsConfig::get('root_path') . 'templates/nordic/shell_scheme.php';
$shell_scheme = is_readable($shell_scheme_file) ? include $shell_scheme_file : [];
$slot_positions = !empty($shell_scheme['slot_positions']) && is_array($shell_scheme['slot_positions']) ? $shell_scheme['slot_positions'] : [];
$shell_runtime = !empty($landingbuilder_shell_runtime) && is_array($landingbuilder_shell_runtime) ? $landingbuilder_shell_runtime : [];
$shell_chrome = !empty($shell_runtime['chrome']) && is_array($shell_runtime['chrome']) ? $shell_runtime['chrome'] : [];
$active_shell_slots = !empty($shell_runtime['active_slots']) && is_array($shell_runtime['active_slots']) ? array_fill_keys($shell_runtime['active_slots'], true) : [];
$menu_placement = !empty($shell_chrome['menu_placement']) ? (string) $shell_chrome['menu_placement'] : 'header_primary';

if (!in_array($menu_placement, ['header_primary', 'header_secondary', 'site_top'], true)) {
    $menu_placement = 'header_primary';
}

if (!empty($shell_runtime['body_classes']) && is_array($shell_runtime['body_classes'])) {
    $body_classes = array_values(array_unique(array_merge($body_classes ?? [], $shell_runtime['body_classes'])));
}

$getSlotPositions = function($slot, array $fallback = []) use ($slot_positions) {
    return !empty($slot_positions[$slot]) ? $slot_positions[$slot] : $fallback;
};

$isSlotEnabled = function($slot) use ($active_shell_slots) {
    if (!$active_shell_slots) {
        return true;
    }

    return isset($active_shell_slots[$slot]);
};

$renderPositionGroup = function($positions, $groupClass, $wrapper = 'wrapper_plain', $itemBaseClass = 'nordic-shell__slot') {
    $has_content = false;

    foreach ($positions as $position) {
        if (!$this->hasWidgetsOn($position)) {
            continue;
        }

        if (!$has_content) {
            echo '<div class="' . html($groupClass, false) . '">';
            $has_content = true;
        }

        echo '<div class="' . html($itemBaseClass, false) . ' ' . html($itemBaseClass . '--' . $position, false) . '">';
        $this->widgets($position, false, $wrapper);
        echo '</div>';
    }

    if ($has_content) {
        echo '</div>';
    }

    return $has_content;
};

$renderSlot = function($positions, $slotClass, $wrapper = 'wrapper_plain') use ($renderPositionGroup) {
    ob_start();
    $has_content = $renderPositionGroup($positions, 'nordic-shell__slot-panel', $wrapper, 'nordic-shell__slot');
    $content = ob_get_clean();

    if (!$has_content) {
        return;
    }

    echo '<section class="' . html($slotClass, false) . '">';
    echo $content;
    echo '</section>';
};

$slot_fallbacks = [
    'site_top' => ['site_top', 'top'],
    'header_primary' => ['header_primary'],
    'header_secondary' => ['header_secondary'],
    'hero' => ['hero'],
    'before_content' => ['before_content'],
    'content_body' => ['content_body'],
    'content_sidebar_left' => ['content_sidebar_left'],
    'content_sidebar_right' => ['content_sidebar_right'],
    'after_content' => ['after_content'],
    'footer_primary' => ['footer_primary', 'footer'],
    'footer_secondary' => ['footer_secondary']
];

$slot_fallbacks[$menu_placement][] = 'header';

$resolveSlotPositions = function($slot) use ($getSlotPositions, $slot_fallbacks) {
    $fallback = $slot_fallbacks[$slot] ?? [$slot];
    return $getSlotPositions($slot, array_values(array_unique($fallback)));
};

$left_sidebar_positions = $resolveSlotPositions('content_sidebar_left');
$right_sidebar_positions = $resolveSlotPositions('content_sidebar_right');
$content_body_positions = $resolveSlotPositions('content_body');

$has_left_content_sidebar = $isSlotEnabled('content_sidebar_left') && $this->hasWidgetsOn($left_sidebar_positions);
$has_right_content_sidebar = $isSlotEnabled('content_sidebar_right') && $this->hasWidgetsOn($right_sidebar_positions);
$has_content_body_widgets = $this->hasWidgetsOn($content_body_positions);

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
    <body id="<?php echo $device_type; ?>_device_type" data-device="<?php echo $device_type; ?>"<?php if (!empty($shell_runtime['variant_key'])) { ?> data-shell-variant="<?php html($shell_runtime['variant_key']); ?>"<?php } ?><?php if (!empty($shell_runtime['body_layout'])) { ?> data-shell-layout="<?php html($shell_runtime['body_layout']); ?>"<?php } ?><?php if (!empty($shell_chrome['header_variant'])) { ?> data-shell-header="<?php html($shell_chrome['header_variant']); ?>"<?php } ?><?php if (!empty($shell_chrome['footer_variant'])) { ?> data-shell-footer="<?php html($shell_chrome['footer_variant']); ?>"<?php } ?><?php if (!empty($shell_chrome['menu_placement'])) { ?> data-shell-menu="<?php html($shell_chrome['menu_placement']); ?>"<?php } ?><?php if (!empty($shell_chrome['mobile_menu_mode'])) { ?> data-shell-mobile-menu="<?php html($shell_chrome['mobile_menu_mode']); ?>"<?php } ?><?php if (!empty($shell_chrome['homepage_shell_mode'])) { ?> data-shell-homepage-mode="<?php html($shell_chrome['homepage_shell_mode']); ?>"<?php } ?> class="d-flex flex-column min-vh-100 nordic-template<?php if (!empty($body_classes)) { ?> <?php html(implode(' ', $body_classes)); ?><?php } ?> <?php html($this->options['body_classes'] ?? ''); ?>">
        <a class="nordic-skip-link" href="#nordic-content-frame">Перейти к содержимому</a>
        <div class="nordic-shell nordic-shell--header-<?php html($shell_chrome['header_variant'] ?? 'classic'); ?> nordic-shell--footer-<?php html($shell_chrome['footer_variant'] ?? 'columns_4'); ?> nordic-shell--menu-<?php html($menu_placement); ?> nordic-shell--mobile-menu-<?php html($shell_chrome['mobile_menu_mode'] ?? 'drawer'); ?> nordic-shell--homepage-mode-<?php html($shell_chrome['homepage_shell_mode'] ?? 'inherit'); ?>">

            <?php if (!$config->is_site_on) { ?>
                <div class="nordic-shell__notice">
                    <?php if (cmsUser::isAdmin()) { ?>
                        <?php printf(ERR_SITE_OFFLINE_FULL, href_to('admin', 'settings', 'siteon')); ?>
                    <?php } else { ?>
                        <?php echo ERR_SITE_OFFLINE; ?>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($isSlotEnabled('site_top')) { $renderSlot($resolveSlotPositions('site_top'), 'nordic-shell__site-top'); } ?>

            <header class="nordic-shell__header">
                <?php if ($isSlotEnabled('header_primary')) { $renderSlot($resolveSlotPositions('header_primary'), 'nordic-shell__header-primary'); } ?>
                <?php if ($isSlotEnabled('header_secondary')) { $renderSlot($resolveSlotPositions('header_secondary'), 'nordic-shell__header-secondary'); } ?>
            </header>

            <?php if ($isSlotEnabled('hero')) { $renderSlot($resolveSlotPositions('hero'), 'nordic-shell__hero'); } ?>
            <?php if ($isSlotEnabled('before_content')) { $renderSlot($resolveSlotPositions('before_content'), 'nordic-shell__before-content'); } ?>

            <main class="nordic-shell__main">
                <div class="nordic-shell__content-frame" id="nordic-content-frame" data-slot="content_body">
                    <div class="<?php html($content_grid_class); ?>">
                        <?php if ($has_left_content_sidebar) { ?>
                            <aside class="nordic-shell__content-sidebar nordic-shell__content-sidebar--left" data-slot="content_sidebar_left">
                                <?php $renderPositionGroup($left_sidebar_positions, 'nordic-shell__content-sidebar-group', 'wrapper_plain', 'nordic-shell__content-sidebar-widget'); ?>
                            </aside>
                        <?php } ?>
                        <div class="nordic-shell__content-body-slot" data-slot="content_body">
                            <?php if ($has_content_body_widgets) { ?>
                                <?php $renderPositionGroup($content_body_positions, 'nordic-shell__content-body-widgets', 'wrapper_plain', 'nordic-shell__content-widget'); ?>
                            <?php } ?>
                            <div class="nordic-shell__content-body-runtime">
                                <?php $this->renderLayoutChild('scheme', ['rows' => $rows]); ?>
                            </div>
                        </div>
                        <?php if ($has_right_content_sidebar) { ?>
                            <aside class="nordic-shell__content-sidebar nordic-shell__content-sidebar--right" data-slot="content_sidebar_right">
                                <?php $renderPositionGroup($right_sidebar_positions, 'nordic-shell__content-sidebar-group', 'wrapper_plain', 'nordic-shell__content-sidebar-widget'); ?>
                            </aside>
                        <?php } ?>
                    </div>
                </div>
            </main>

            <?php if ($isSlotEnabled('after_content')) { $renderSlot($resolveSlotPositions('after_content'), 'nordic-shell__after-content'); } ?>

            <footer class="nordic-shell__footer">
                <?php if ($isSlotEnabled('footer_primary')) { $renderSlot($resolveSlotPositions('footer_primary'), 'nordic-shell__footer-primary'); } ?>
                <?php if ($isSlotEnabled('footer_secondary')) { $renderSlot($resolveSlotPositions('footer_secondary'), 'nordic-shell__footer-secondary'); } ?>
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