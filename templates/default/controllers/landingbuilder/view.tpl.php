<?php

require_once __DIR__ . '/runtime_theme.php';
require_once __DIR__ . '/runtime_renderer.php';

$adapter = $runtime['adapter'];
$zones = $runtime['zones'];
$slot_map = $runtime['slot_map'] ?? [];
$device_type = $runtime['device_type'] ?? 'desktop';
$is_preview = !empty($runtime['is_preview']);
$theme_context = landingbuilder_get_runtime_theme_context($page);
$page_theme = $theme_context['theme'];
$page_theme_style = landingbuilder_render_css_vars($theme_context['vars']);

$page_mode_titles = [
	'full_takeover'  => 'Полностью своя страница',
	'hybrid_overlay' => 'Поверх существующей страницы',
	'zone_injection' => 'Встраивание в зону страницы',
	'data_only'      => 'Только данные для блоков'
];

ob_start();
?>
<style>
	.lb-runtime-page {
		max-width: var(--lb-page-max-width, 1240px);
		margin: 0 auto;
		padding: 32px 20px 56px;
		background: var(--lb-page-background, #f4f7fa);
		color: var(--lb-text-color, #173042);
		font-family: var(--lb-font-body, "Segoe UI", Tahoma, sans-serif);
	}
	.lb-runtime-page h1,
	.lb-runtime-page h2,
	.lb-runtime-page h3 {
		font-family: var(--lb-font-heading, "Segoe UI", Tahoma, sans-serif);
	}
	.lb-runtime-hero {
		padding: 28px;
		border-radius: var(--lb-radius-lg, 24px);
		background: var(--lb-hero-background, linear-gradient(135deg, #f4f6f8 0%, #ffffff 60%, #eef3f8 100%));
		border: 1px solid var(--lb-border-color, #dbe3ea);
		box-shadow: var(--lb-shadow-lg, 0 18px 48px rgba(19, 41, 61, 0.08));
		margin-bottom: 28px;
	}
	.lb-runtime-kicker {
		font-size: 12px;
		letter-spacing: 0.12em;
		text-transform: uppercase;
		color: var(--lb-text-muted, #5e7c92);
		margin-bottom: 12px;
	}
	.lb-runtime-hero h1 {
		margin: 0 0 10px;
		font-size: var(--lb-hero-title-size, 40px);
		line-height: 1.1;
		color: var(--lb-heading-color, #163040);
	}
	.lb-runtime-meta {
		display: flex;
		gap: 10px;
		flex-wrap: wrap;
		margin-top: 18px;
	}
	.lb-runtime-pill {
		display: inline-flex;
		align-items: center;
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--lb-zone-pill-background, #ffffff);
		border: 1px solid var(--lb-border-color, #d7e0e7);
		font-size: 13px;
		color: var(--lb-zone-pill-color, #335168);
	}
	.lb-preview-note {
		margin-top: 18px;
		padding: 12px 14px;
		border-radius: 14px;
		background: #fff7df;
		border: 1px solid #f1d690;
		color: #6c5618;
	}
	.lb-runtime-layout {
		display: grid;
		gap: 20px;
	}
	.lb-runtime-zone {
		background: var(--lb-surface-color, #fff);
		border-radius: var(--lb-radius-lg, 22px);
		border: 1px solid var(--lb-border-color, #dce4ea);
		padding: 22px;
		box-shadow: var(--lb-shadow-md, 0 12px 32px rgba(18, 36, 52, 0.06));
	}
	.lb-runtime-zone-head {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 20px;
		margin-bottom: 18px;
	}
	.lb-runtime-zone-head h2 {
		margin: 4px 0 0;
		font-size: 22px;
		color: var(--lb-heading-color, #1c3344);
	}
	.lb-runtime-zone-head p,
	.lb-native-placeholder p {
		margin: 0;
		max-width: 520px;
		color: var(--lb-text-muted, #5b7282);
	}
	.lb-zone-kind,
	.lb-section-kicker,
	.lb-node-label,
	.lb-column-title,
	.lb-native-title {
		font-size: 12px;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: #648197;
	}
	.lb-native-placeholder,
	.lb-empty-zone {
		padding: 18px;
		border-radius: 16px;
		border: 1px dashed var(--lb-border-color, #b8c8d4);
		background: var(--lb-surface-soft, #f7fafc);
	}
	.lb-section {
		padding: 18px;
		border-radius: var(--lb-radius-md, 18px);
		background: var(--lb-surface-color, #fdfefe);
		border: 1px solid var(--lb-border-color, #dfe8ee);
		margin-bottom: var(--lb-section-gap, 18px);
	}
	.lb-section-inner {
		width: 100%;
	}
	.lb-section:last-child {
		margin-bottom: 0;
	}
	.lb-section-head {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 18px;
		margin-bottom: 16px;
	}
	.lb-section-head h2 {
		margin: 4px 0 0;
		font-size: 24px;
		color: var(--lb-heading-color, #142c3d);
	}
	.lb-zone-pill {
		padding: 8px 12px;
		border-radius: 999px;
		background: var(--lb-zone-pill-background, #edf5fb);
		color: var(--lb-zone-pill-color, #2a5979);
		font-size: 13px;
	}
	.lb-columns {
		display: grid;
		gap: 16px;
	}
	.lb-grid-1 {
		grid-template-columns: minmax(0, 1fr);
	}
	.lb-grid-2,
	.lb-grid-sidebar-left,
	.lb-grid-sidebar-right {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
	.lb-grid-sidebar-left {
		grid-template-columns: minmax(220px, 0.8fr) minmax(0, 1.6fr);
	}
	.lb-grid-sidebar-right {
		grid-template-columns: minmax(0, 1.6fr) minmax(220px, 0.8fr);
	}
	.lb-grid-3 {
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}
	.lb-column {
		padding: 16px;
		border-radius: 16px;
		background: var(--lb-surface-soft, #f7fafc);
		border: 1px solid var(--lb-border-color, #dbe6ee);
	}
	.lb-node-card {
		margin-top: 12px;
		padding: 16px;
		border-radius: 16px;
		background: var(--lb-card-background, #ffffff);
		border: 1px solid var(--lb-card-border, #dce5ec);
		box-shadow: var(--lb-card-shadow, 0 8px 20px rgba(22, 38, 52, 0.05));
	}
	.lb-node-card--block {
		border-left: 4px solid var(--lb-accent-color, #3f8fb5);
	}
	.lb-node-card--widget {
		border-left: 4px solid #2a6752;
	}
	.lb-node-content h2,
	.lb-node-content h3 {
		margin: 8px 0 10px;
		color: var(--lb-heading-color, #173042);
	}
	.lb-node-content p,
	.lb-node-content li {
		color: var(--lb-text-muted, #566f80);
	}
	.lb-node-meta,
	.lb-node-note {
		margin-top: 10px;
		font-size: 13px;
		color: var(--lb-text-muted, #6b7f8d);
	}
	.lb-section--container-text .lb-section-inner {
		max-width: 760px;
		margin: 0 auto;
	}
	.lb-section--container-standard .lb-section-inner {
		max-width: 1120px;
		margin: 0 auto;
	}
	.lb-section--container-wide .lb-section-inner {
		max-width: 1320px;
		margin: 0 auto;
	}
	.lb-section--spacing-sm {
		padding-top: 14px;
		padding-bottom: 14px;
	}
	.lb-section--spacing-md {
		padding-top: 18px;
		padding-bottom: 18px;
	}
	.lb-section--spacing-lg {
		padding-top: 28px;
		padding-bottom: 28px;
	}
	.lb-section--spacing-xl {
		padding-top: 40px;
		padding-bottom: 40px;
	}
	.lb-section--tone-base {
		background: var(--lb-surface-color, #ffffff);
	}
	.lb-section--tone-brand-soft {
		background: var(--lb-accent-soft, #e8f3f8);
	}
	.lb-section--tone-muted {
		background: var(--lb-surface-muted, #eef3f8);
	}
	.lb-section--tone-brand-strong {
		background: var(--lb-accent-color, #2f7aa1);
		color: var(--lb-accent-contrast, #ffffff);
		border-color: transparent;
	}
	.lb-section--tone-contrast,
	.lb-section--tone-inverse {
		background: var(--lb-contrast-surface, #173042);
		color: var(--lb-contrast-text, #f7fbff);
		border-color: transparent;
	}
	.lb-section--tone-brand-strong .lb-section-head h2,
	.lb-section--tone-contrast .lb-section-head h2,
	.lb-section--tone-inverse .lb-section-head h2,
	.lb-section--tone-brand-strong .lb-node-content h2,
	.lb-section--tone-brand-strong .lb-node-content h3,
	.lb-section--tone-contrast .lb-node-content h2,
	.lb-section--tone-contrast .lb-node-content h3,
	.lb-section--tone-inverse .lb-node-content h2,
	.lb-section--tone-inverse .lb-node-content h3 {
		color: inherit;
	}
	.lb-section--tone-brand-strong .lb-zone-pill,
	.lb-section--tone-contrast .lb-zone-pill,
	.lb-section--tone-inverse .lb-zone-pill {
		background: rgba(255, 255, 255, 0.12);
		color: inherit;
	}
	.lb-section--tone-brand-strong .lb-column,
	.lb-section--tone-contrast .lb-column,
	.lb-section--tone-inverse .lb-column {
		background: rgba(255, 255, 255, 0.08);
		border-color: rgba(255, 255, 255, 0.12);
	}
	.lb-section--tone-brand-strong .lb-node-card,
	.lb-section--tone-contrast .lb-node-card,
	.lb-section--tone-inverse .lb-node-card {
		background: rgba(255, 255, 255, 0.12);
		border-color: rgba(255, 255, 255, 0.18);
		box-shadow: none;
	}
	.lb-section--tone-brand-strong .lb-node-content p,
	.lb-section--tone-brand-strong .lb-node-content li,
	.lb-section--tone-brand-strong .lb-node-meta,
	.lb-section--tone-brand-strong .lb-node-note,
	.lb-section--tone-contrast .lb-node-content p,
	.lb-section--tone-contrast .lb-node-content li,
	.lb-section--tone-contrast .lb-node-meta,
	.lb-section--tone-contrast .lb-node-note,
	.lb-section--tone-inverse .lb-node-content p,
	.lb-section--tone-inverse .lb-node-content li,
	.lb-section--tone-inverse .lb-node-meta,
	.lb-section--tone-inverse .lb-node-note {
		color: rgba(255, 255, 255, 0.82);
	}
	.lb-section--style-hero .lb-section-head h2,
	.lb-section--style-hero-split .lb-section-head h2 {
		font-size: clamp(32px, 4vw, 48px);
	}
	.lb-section--style-cards .lb-column {
		background: transparent;
		border-style: dashed;
	}
	@media (max-width: 900px) {
		.lb-runtime-page {
			padding: 24px 14px 48px;
		}
		.lb-runtime-hero h1 {
			font-size: 30px;
		}
		.lb-grid-2,
		.lb-grid-sidebar-left,
		.lb-grid-sidebar-right,
		.lb-grid-3 {
			grid-template-columns: minmax(0, 1fr);
		}
		.lb-runtime-zone-head,
		.lb-section-head {
			flex-direction: column;
		}
	}
</style>

<div class="lb-runtime-page" style="<?php html($page_theme_style); ?>" data-global-style-preset="<?php html($page_theme['global_style_preset']); ?>" data-color-preset="<?php html($page_theme['color_preset']); ?>" data-typography-preset="<?php html($page_theme['typography_preset']); ?>" data-container-preset="<?php html($page_theme['container_preset']); ?>">
	<header class="lb-runtime-hero">
		<div class="lb-runtime-kicker">Живой runtime Нордик</div>
		<h1><?php html($page['title']); ?></h1>
		<p><?php html($adapter['description']); ?></p>
		<div class="lb-runtime-meta">
			<div class="lb-runtime-pill">Режим: <?php html($page_mode_titles[$page['mode']] ?? $page['mode']); ?></div>
			<div class="lb-runtime-pill">Адаптер: <?php html($adapter['title']); ?></div>
			<div class="lb-runtime-pill">Ключ страницы: <?php html($page['key']); ?></div>
			<div class="lb-runtime-pill">Устройство: <?php html($device_type); ?></div>
		</div>
		<?php if ($is_preview) { ?>
			<div class="lb-preview-note">Открыт preview-режим. Страница еще не опубликована, поэтому runtime доступен только администратору.</div>
		<?php } ?>
	</header>

	<div class="lb-runtime-layout">
		<?php foreach ($zones as $zone) { ?>
			<?php if (($zone['kind'] ?? 'builder') === 'native') {
				$zone['native_label'] = $adapter['native_content_label'];
			} ?>
			<?php echo landingbuilder_render_runtime_zone($zone, [
				'device_type'   => $device_type,
				'theme_context' => $theme_context,
				'slot_map'      => $slot_map,
				'surface'       => 'runtime'
			]); ?>
		<?php } ?>
	</div>
</div>

<?php echo ob_get_clean(); ?>