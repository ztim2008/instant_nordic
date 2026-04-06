<?php
/**
 * Основной макет шаблона
 * https://docs.instantcms.ru/dev/templates/layouts
 */
/** @var cmsTemplate $this */

// SAFETY STOP: по умолчанию отключаем takeover главной через landingbuilder,
// чтобы фронт был предсказуемым и совпадал для admin/guest.
$lb_front_integration_enabled = false;

$lb_takeover_active = false;
$lb_takeover_html = '';

try {
    $is_homepage = empty($core->uri);
    if ($lb_front_integration_enabled && $is_homepage) {
        $lb_model = cmsCore::getModel('landingbuilder');
        if ($lb_model && method_exists($lb_model, 'getPageByKey') && method_exists($lb_model, 'getRuntimePage')) {
            $takeover_page = $lb_model->getPageByKey('homepage');
            if ($takeover_page) {
                $is_preview = ($takeover_page['status'] ?? 'draft') !== 'published';
                if (!$is_preview || cmsUser::isAdmin()) {
                    $theme_lib = cmsConfig::get('root_path') . 'templates/default/controllers/landingbuilder/runtime_theme.php';
                    $renderer_lib = cmsConfig::get('root_path') . 'templates/default/controllers/landingbuilder/runtime_renderer.php';
                    $styles_lib = cmsConfig::get('root_path') . 'system/controllers/landingbuilder/helpers/runtime_styles.php';
                    if (is_readable($theme_lib)) {
                        require_once $theme_lib;
                    }
                    if (is_readable($renderer_lib)) {
                        require_once $renderer_lib;
                    }
                    if (is_readable($styles_lib)) {
                        require_once $styles_lib;
                    }

                    $takeover_runtime = $lb_model->getRuntimePage($takeover_page);
                    $takeover_runtime['device_type'] = cmsRequest::getDeviceType();
                    $device_type = $takeover_runtime['device_type'] ?? 'desktop';
                    $zones = is_array($takeover_runtime['zones'] ?? null) ? $takeover_runtime['zones'] : [];
                    $slot_map = is_array($takeover_runtime['slot_map'] ?? null) ? $takeover_runtime['slot_map'] : [];

                    if (function_exists('landingbuilder_get_runtime_theme_context') && function_exists('landingbuilder_render_css_vars') && function_exists('landingbuilder_render_runtime_zone_sections')) {
                        $theme_context = landingbuilder_get_runtime_theme_context($takeover_page);
                        $page_theme = $theme_context['theme'] ?? [];
                        $page_theme_style = landingbuilder_render_css_vars($theme_context['vars'] ?? []);

                        ob_start();
                        ?>
                        <div class="lb-runtime-embed" style="<?php html($page_theme_style); ?>" data-global-style-preset="<?php html($page_theme['global_style_preset'] ?? ''); ?>" data-color-preset="<?php html($page_theme['color_preset'] ?? ''); ?>" data-typography-preset="<?php html($page_theme['typography_preset'] ?? ''); ?>" data-container-preset="<?php html($page_theme['container_preset'] ?? ''); ?>">
                            <?php foreach ($zones as $zone) { ?>
                                <?php if (($zone['kind'] ?? 'builder') !== 'builder') { continue; } ?>
                                <?php if (empty($zone['sections']) || !is_array($zone['sections'])) { continue; } ?>
                                <?php echo landingbuilder_render_runtime_zone_sections($zone, [
                                    'device_type'   => $device_type,
                                    'theme_context' => $theme_context,
                                    'slot_map'      => $slot_map,
                                    'surface'       => 'site'
                                ]); ?>
                            <?php } ?>
                        </div>
                        <?php
                        $lb_takeover_html = (string) ob_get_clean();
                        $lb_takeover_active = trim($lb_takeover_html) !== '';

                        if ($lb_takeover_active && function_exists('landingbuilder_get_runtime_site_styles_css')) {
                            $css = landingbuilder_get_runtime_site_styles_css();
                            if ($css !== '') {
                                $this->addHead('<style>' . $css . '</style>');
                            }
                        }
                    }
                }
            }
        }
    }
} catch (Throwable $exception) {
    $lb_takeover_active = false;
    $lb_takeover_html = '';
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
    <?php if(!empty($this->options['head_html_top'])) { ?>
        <?php echo $this->options['head_html_top']."\n"; ?>
    <?php } ?>
<?php
        $this->addMainTplCSSName(['theme']);
        if(!empty($this->options['font_type']) && $this->options['font_type'] === 'gfont') {
            $this->addHead('<link rel="dns-prefetch" href="https://fonts.googleapis.com">');
            $this->addHead('<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>');
            $this->addHead('<link rel="dns-prefetch" href="https://fonts.gstatic.com">');
            $this->addHead('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
            $this->addCSS('https://fonts.googleapis.com/css?family='.$this->options['gfont'].':400,400i,700,700i&display=swap&subset=cyrillic-ext', false);
        }
        $this->addMainTplJSName('jquery', true);
        $this->addMainTplJSName(['vendors/popper.js/js/popper.min', 'vendors/bootstrap/bootstrap.min']);
        $this->onDemandTplJSName(['vendors/photoswipe/photoswipe.min']);
        $this->onDemandTplCSSName(['photoswipe']);
        $this->addMainTplJSName(['core', 'modal']);
?>
        <?php $this->head(true, !empty($this->options['js_print_head']), true); ?>
    <?php if(!empty($this->options['favicon_head_html'])) { ?>
        <?php echo $this->options['favicon_head_html']."\n"; ?>
    <?php } ?>
    <?php if(!empty($this->options['favicon']['path'])) { ?>
        <link rel="icon" href="<?php echo $config->upload_root . $this->options['favicon']['path']; ?>" type="<?php echo pathinfo($this->options['favicon']['path'], PATHINFO_EXTENSION) === 'svg' ? 'image/svg+xml' : 'image/x-icon'; ?>">
    <?php } else { ?>
        <link rel="icon" href="<?php echo $this->getTemplateFilePath('images/favicons/favicon.ico'); ?>" type="image/x-icon">
    <?php } ?>
    </head>
    <body id="<?php echo $device_type; ?>_device_type" data-device="<?php echo $device_type; ?>" class="d-flex flex-column min-vh-100<?php if(!empty($body_classes)) { ?> <?php html(implode(' ', $body_classes)); ?><?php } ?> <?php html($this->options['body_classes'] ?? ''); ?>">
		<?php if ($lb_takeover_active) { ?>
			<?php echo $lb_takeover_html; ?>
		<?php } else { ?>
			<?php $this->renderLayoutChild('scheme', ['rows' => $rows]); ?>
		<?php } ?>
        <?php if (!empty($this->options['show_top_btn'])){ ?>
            <a class="btn btn-secondary btn-lg" href="#<?php echo $device_type; ?>_device_type" id="scroll-top">
                <?php html_svg_icon('solid', 'chevron-up'); ?>
            </a>
        <?php } ?>
        <?php if (!empty($this->options['show_cookiealert'])){ ?>
            <div class="alert text-center py-3 border-0 rounded-0 m-0 position-fixed fixed-bottom icms-cookiealert" id="icms-cookiealert">
                <div class="container">
                    <?php echo $this->options['cookiealert_text']; ?>
                    <button type="button" class="ml-2 btn btn-primary btn-sm acceptcookies">
                        <?php echo LANG_MODERN_THEME_COOKIEALERT_AGREE; ?>
                    </button>
                </div>
            </div>
        <?php } ?>
        <?php if ($config->debug && cmsUser::isAdmin()){ ?>
            <?php $this->renderAsset('ui/debug', ['core' => $core]); ?>
        <?php } ?>
        <script nonce="<?php echo $this->nonce; ?>"><?php echo $this->getLangJS('LANG_LOADING', 'LANG_ALL', 'LANG_COLLAPSE', 'LANG_EXPAND'); ?></script>
        <?php if(empty($this->options['js_print_head'])) { ?>
            <?php $this->printJavascriptTags(); ?>
        <?php } ?>
        <?php $this->bottom(); ?>
        <?php $this->onDemandPrint(); ?>
    </body>
</html>
