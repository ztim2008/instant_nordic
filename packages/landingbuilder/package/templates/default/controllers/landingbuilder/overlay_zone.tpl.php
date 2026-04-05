<?php

require_once __DIR__ . '/runtime_theme.php';
require_once __DIR__ . '/runtime_renderer.php';

$device_type = cmsRequest::getDeviceType();
$theme_context = landingbuilder_get_runtime_theme_context($page ?? []);
$page_theme = $theme_context['theme'];
$page_theme_style = landingbuilder_render_css_vars($theme_context['vars']);

$zone_sections_html = landingbuilder_render_runtime_zone_sections($zone, [
	'device_type'   => $device_type,
	'theme_context' => $theme_context,
	'surface'       => 'overlay'
]);

if (trim($zone_sections_html) === '') {
	return;
}
?>
<div class="lb-overlay-zone lb-overlay-zone--<?php html($zone['key'] ?? 'builder'); ?>" style="<?php html($page_theme_style); ?>" data-global-style-preset="<?php html($page_theme['global_style_preset']); ?>" data-color-preset="<?php html($page_theme['color_preset']); ?>" data-typography-preset="<?php html($page_theme['typography_preset']); ?>" data-container-preset="<?php html($page_theme['container_preset']); ?>">
	<div class="lb-overlay-zone__head">
		<div>
			<div class="lb-overlay-zone__kicker">Живая зона конструктора</div>
			<h2 class="lb-overlay-zone__title"><?php html($zone['title'] ?? 'Зона конструктора'); ?></h2>
			<?php if (!empty($zone['description'])) { ?>
				<p class="lb-overlay-zone__desc"><?php html($zone['description']); ?></p>
			<?php } ?>
		</div>
		<div class="lb-overlay-pill">Страница: <?php html($page['title'] ?? $page['key'] ?? 'Нордик'); ?></div>
	</div>

	<?php echo $zone_sections_html; ?>
</div>