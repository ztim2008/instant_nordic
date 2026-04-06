<?php

require_once __DIR__ . '/../../../default/controllers/landingbuilder/runtime_theme.php';
require_once __DIR__ . '/../../../default/controllers/landingbuilder/runtime_renderer.php';

$styles_lib = cmsConfig::get('root_path') . 'system/controllers/landingbuilder/helpers/runtime_styles.php';
if (is_readable($styles_lib)) {
	require_once $styles_lib;
}

$adapter = $runtime['adapter'];
$zones = $runtime['zones'];
$slot_map = $runtime['slot_map'] ?? [];
$device_type = $runtime['device_type'] ?? 'desktop';
$theme_context = landingbuilder_get_runtime_theme_context($page);
$page_theme = $theme_context['theme'];
$page_theme_style = landingbuilder_render_css_vars($theme_context['vars']);

?>

<?php if (function_exists('landingbuilder_get_runtime_site_styles_css')) { ?>
	<style><?php echo landingbuilder_get_runtime_site_styles_css(); ?></style>
<?php } ?>

<div class="lb-runtime-embed" style="<?php html($page_theme_style); ?>" data-global-style-preset="<?php html($page_theme['global_style_preset']); ?>" data-color-preset="<?php html($page_theme['color_preset']); ?>" data-typography-preset="<?php html($page_theme['typography_preset']); ?>" data-container-preset="<?php html($page_theme['container_preset']); ?>">
	<?php foreach ($zones as $zone) { ?>
		<?php if (($zone['kind'] ?? 'builder') !== 'builder') { continue; } ?>
		<?php if (empty($zone['sections'])) { continue; } ?>
		<?php echo landingbuilder_render_runtime_zone_sections($zone, [
			'device_type'   => $device_type,
			'theme_context' => $theme_context,
			'slot_map'      => $slot_map,
			'surface'       => 'site'
		]); ?>
	<?php } ?>
</div>
