<?php

if (!function_exists('landingbuilder_sanitize_theme_token')) {

	function landingbuilder_sanitize_theme_token($value) {
		$token = strtolower((string) $value);
		$token = preg_replace('/[^a-z0-9_-]+/', '-', $token);
		return trim($token, '-');
	}

	function landingbuilder_get_base_theme_defaults() {
		return [
			'global_style_preset' => 'nordic_balanced',
			'color_preset'        => 'nordic_day',
			'typography_preset'   => 'editorial',
			'container_preset'    => 'standard',
			'button_preset'       => 'soft_accent',
			'card_preset'         => 'quiet',
			'section_spacing'     => 'comfortable',
			'radius_preset'       => 'none',
			'density_preset'      => 'balanced',
			'contrast_preset'     => 'balanced'
		];
	}

	function landingbuilder_get_site_theme_defaults() {

		$options = (array) cmsController::loadOptions('landingbuilder');
		$defaults = landingbuilder_get_base_theme_defaults();
		$mapping = [
			'global_style_preset' => 'default_global_style_preset',
			'color_preset'        => 'default_color_preset',
			'typography_preset'   => 'default_typography_preset',
			'container_preset'    => 'default_container_preset',
			'button_preset'       => 'default_button_preset',
			'card_preset'         => 'default_card_preset',
			'section_spacing'     => 'default_section_spacing',
			'radius_preset'       => 'default_radius_preset',
			'density_preset'      => 'default_density_preset',
			'contrast_preset'     => 'default_contrast_preset'
		];

		foreach ($mapping as $theme_key => $option_key) {
			if (!empty($options[$option_key])) {
				$defaults[$theme_key] = (string) $options[$option_key];
			}
		}

		return $defaults;
	}

	function landingbuilder_normalize_theme_state(array $theme, array $base_theme = []) {

		$normalized = $base_theme ?: landingbuilder_get_site_theme_defaults();

		foreach (landingbuilder_get_base_theme_defaults() as $key => $default_value) {
			if (isset($theme[$key]) && $theme[$key] !== '') {
				$normalized[$key] = (string) $theme[$key];
			}

			if (empty($normalized[$key])) {
				$normalized[$key] = $default_value;
			}
		}

		return $normalized;
	}

	function landingbuilder_render_css_vars(array $vars) {
		$parts = [];

		foreach ($vars as $name => $value) {
			if ($value === '' || $value === null) {
				continue;
			}

			$parts[] = $name . ':' . $value;
		}

		return implode(';', $parts);
	}

	function landingbuilder_get_theme_runtime_catalog() {
		return [
			'defaults' => landingbuilder_get_base_theme_defaults(),
			'global_style_presets' => [
				'nordic_balanced' => [
					'--lb-radius-md'      => '18px',
					'--lb-radius-lg'      => '24px',
					'--lb-hero-background'=> 'linear-gradient(135deg, #f4f6f8 0%, #ffffff 60%, #eef3f8 100%)',
					'--lb-shadow-lg'      => '0 18px 48px rgba(19, 41, 61, 0.10)'
				],
				'nordic_contrast' => [
					'--lb-radius-md'      => '20px',
					'--lb-radius-lg'      => '28px',
					'--lb-hero-background'=> 'linear-gradient(135deg, #142634 0%, #284559 100%)',
					'--lb-shadow-lg'      => '0 22px 56px rgba(8, 17, 25, 0.18)'
				],
				'nordic_editorial' => [
					'--lb-radius-md'      => '12px',
					'--lb-radius-lg'      => '18px',
					'--lb-hero-background'=> 'linear-gradient(135deg, #faf6ef 0%, #ffffff 68%, #f3ede5 100%)',
					'--lb-shadow-lg'      => '0 16px 40px rgba(61, 43, 30, 0.10)'
				],
				'nordic_catalog' => [
					'--lb-radius-md'      => '14px',
					'--lb-radius-lg'      => '20px',
					'--lb-hero-background'=> 'linear-gradient(135deg, #f1f4f7 0%, #ffffff 45%, #edf3f8 100%)',
					'--lb-shadow-lg'      => '0 18px 44px rgba(19, 41, 61, 0.08)'
				]
			],
			'color_presets' => [
				'nordic_day' => [
					'--lb-page-background'   => '#f4f7fa',
					'--lb-surface-color'     => '#ffffff',
					'--lb-surface-muted'     => '#eef3f8',
					'--lb-surface-soft'      => '#f8fbfd',
					'--lb-text-color'        => '#173042',
					'--lb-text-muted'        => '#5b7282',
					'--lb-heading-color'     => '#142c3d',
					'--lb-border-color'      => '#dce4ea',
					'--lb-accent-color'      => '#2f7aa1',
					'--lb-accent-soft'       => '#e8f3f8',
					'--lb-accent-contrast'   => '#ffffff',
					'--lb-contrast-surface'  => '#173042',
					'--lb-contrast-text'     => '#f7fbff',
					'--lb-zone-pill-background' => '#ffffff',
					'--lb-zone-pill-color'   => '#335168'
				],
				'slate_contrast' => [
					'--lb-page-background'   => '#e9eef3',
					'--lb-surface-color'     => '#ffffff',
					'--lb-surface-muted'     => '#dfe7ee',
					'--lb-surface-soft'      => '#f4f7fa',
					'--lb-text-color'        => '#152532',
					'--lb-text-muted'        => '#526678',
					'--lb-heading-color'     => '#101e29',
					'--lb-border-color'      => '#cad5df',
					'--lb-accent-color'      => '#355a74',
					'--lb-accent-soft'       => '#dce8f1',
					'--lb-accent-contrast'   => '#ffffff',
					'--lb-contrast-surface'  => '#15232f',
					'--lb-contrast-text'     => '#f6fbff',
					'--lb-zone-pill-background' => '#f6f9fb',
					'--lb-zone-pill-color'   => '#274357'
				],
				'forest_accent' => [
					'--lb-page-background'   => '#f2f7f3',
					'--lb-surface-color'     => '#ffffff',
					'--lb-surface-muted'     => '#e6f0e9',
					'--lb-surface-soft'      => '#f7fbf8',
					'--lb-text-color'        => '#183127',
					'--lb-text-muted'        => '#567163',
					'--lb-heading-color'     => '#14291f',
					'--lb-border-color'      => '#d3dfd7',
					'--lb-accent-color'      => '#356b4b',
					'--lb-accent-soft'       => '#e7f2ea',
					'--lb-accent-contrast'   => '#ffffff',
					'--lb-contrast-surface'  => '#183127',
					'--lb-contrast-text'     => '#f5fbf7',
					'--lb-zone-pill-background' => '#ffffff',
					'--lb-zone-pill-color'   => '#29563d'
				]
			],
			'typography_presets' => [
				'editorial' => [
					'--lb-font-body'    => 'Georgia, "Times New Roman", serif',
					'--lb-font-heading' => 'Georgia, "Times New Roman", serif',
					'--lb-hero-title-size' => '42px'
				],
				'neutral' => [
					'--lb-font-body'    => '"Segoe UI", Tahoma, sans-serif',
					'--lb-font-heading' => '"Segoe UI", Tahoma, sans-serif',
					'--lb-hero-title-size' => '40px'
				],
				'compact' => [
					'--lb-font-body'    => 'Arial, sans-serif',
					'--lb-font-heading' => '"Arial Narrow", Arial, sans-serif',
					'--lb-hero-title-size' => '36px'
				]
			],
			'container_presets' => [
				'text'     => '760px',
				'standard' => '1120px',
				'wide'     => '1320px',
				'full'     => '100%'
			],
			'section_spacing' => [
				'compact'     => '20px',
				'comfortable' => '32px',
				'airy'        => '48px'
			],
			'radius_presets' => [
				'none' => [
					'--lb-radius-sm' => '0px',
					'--lb-radius-md' => '0px',
					'--lb-radius-lg' => '0px'
				],
				'soft' => [
					'--lb-radius-sm' => '8px',
					'--lb-radius-md' => '12px',
					'--lb-radius-lg' => '16px'
				],
				'rounded' => [
					'--lb-radius-sm' => '14px',
					'--lb-radius-md' => '22px',
					'--lb-radius-lg' => '30px'
				]
			],
			'density_presets' => [
				'compact' => [
					'--lb-content-gap'         => '12px',
					'--lb-control-height'      => '38px',
					'--lb-card-padding'        => '16px',
					'--lb-button-padding-y'    => '10px',
					'--lb-button-padding-x'    => '18px',
					'--lb-topbar-padding-y'    => '10px',
					'--lb-topbar-padding-x'    => '16px'
				],
				'balanced' => [
					'--lb-content-gap'         => '18px',
					'--lb-control-height'      => '42px',
					'--lb-card-padding'        => '20px',
					'--lb-button-padding-y'    => '12px',
					'--lb-button-padding-x'    => '22px',
					'--lb-topbar-padding-y'    => '12px',
					'--lb-topbar-padding-x'    => '18px'
				],
				'relaxed' => [
					'--lb-content-gap'         => '24px',
					'--lb-control-height'      => '46px',
					'--lb-card-padding'        => '26px',
					'--lb-button-padding-y'    => '14px',
					'--lb-button-padding-x'    => '26px',
					'--lb-topbar-padding-y'    => '14px',
					'--lb-topbar-padding-x'    => '22px'
				]
			],
			'contrast_presets' => [
				'soft' => [
					'--lb-border-contrast' => 'rgba(23, 48, 66, 0.12)',
					'--lb-shadow-sm'       => '0 6px 16px rgba(18, 36, 52, 0.04)',
					'--lb-shadow-md'       => '0 10px 24px rgba(18, 36, 52, 0.06)',
					'--lb-shadow-lg'       => '0 14px 34px rgba(19, 41, 61, 0.08)'
				],
				'balanced' => [
					'--lb-border-contrast' => 'rgba(23, 48, 66, 0.18)',
					'--lb-shadow-sm'       => '0 8px 20px rgba(18, 36, 52, 0.05)',
					'--lb-shadow-md'       => '0 12px 32px rgba(18, 36, 52, 0.08)',
					'--lb-shadow-lg'       => '0 18px 48px rgba(19, 41, 61, 0.10)'
				],
				'strong' => [
					'--lb-border-contrast' => 'rgba(23, 48, 66, 0.26)',
					'--lb-shadow-sm'       => '0 10px 24px rgba(14, 29, 42, 0.08)',
					'--lb-shadow-md'       => '0 16px 36px rgba(14, 29, 42, 0.12)',
					'--lb-shadow-lg'       => '0 24px 56px rgba(10, 21, 31, 0.16)'
				]
			],
			'button_presets' => [
				'soft_accent' => [
					'--lb-button-background' => 'var(--lb-accent-soft)',
					'--lb-button-color'      => 'var(--lb-accent-color)',
					'--lb-button-border'     => 'transparent'
				],
				'solid_brand' => [
					'--lb-button-background' => 'var(--lb-accent-color)',
					'--lb-button-color'      => 'var(--lb-accent-contrast)',
					'--lb-button-border'     => 'var(--lb-accent-color)'
				],
				'ghost' => [
					'--lb-button-background' => 'transparent',
					'--lb-button-color'      => 'var(--lb-text-color)',
					'--lb-button-border'     => 'var(--lb-border-color)'
				]
			],
			'card_presets' => [
				'quiet' => [
					'--lb-card-background' => '#ffffff',
					'--lb-card-border'     => 'var(--lb-border-color)',
					'--lb-card-shadow'     => '0 8px 20px rgba(18, 36, 52, 0.05)'
				],
				'raised' => [
					'--lb-card-background' => '#ffffff',
					'--lb-card-border'     => 'rgba(0, 0, 0, 0.04)',
					'--lb-card-shadow'     => '0 16px 34px rgba(18, 36, 52, 0.12)'
				],
				'outline' => [
					'--lb-card-background' => '#ffffff',
					'--lb-card-border'     => 'var(--lb-accent-color)',
					'--lb-card-shadow'     => 'none'
				]
			],
			'base_vars' => [
				'--lb-page-background'      => '#f4f7fa',
				'--lb-surface-color'        => '#ffffff',
				'--lb-surface-muted'        => '#eef3f8',
				'--lb-surface-soft'         => '#f8fbfd',
				'--lb-text-color'           => '#173042',
				'--lb-text-muted'           => '#5b7282',
				'--lb-heading-color'        => '#142c3d',
				'--lb-border-color'         => '#dce4ea',
				'--lb-accent-color'         => '#2f7aa1',
				'--lb-accent-soft'          => '#e8f3f8',
				'--lb-accent-contrast'      => '#ffffff',
				'--lb-contrast-surface'     => '#173042',
				'--lb-contrast-text'        => '#f7fbff',
				'--lb-zone-pill-background' => '#ffffff',
				'--lb-zone-pill-color'      => '#335168',
				'--lb-radius-sm'            => '12px',
				'--lb-radius-md'            => '18px',
				'--lb-radius-lg'            => '24px',
				'--lb-shadow-sm'            => '0 8px 20px rgba(18, 36, 52, 0.05)',
				'--lb-shadow-md'            => '0 12px 32px rgba(18, 36, 52, 0.08)',
				'--lb-shadow-lg'            => '0 18px 48px rgba(19, 41, 61, 0.10)',
				'--lb-hero-background'      => 'linear-gradient(135deg, #f4f6f8 0%, #ffffff 60%, #eef3f8 100%)',
				'--lb-font-body'            => '"Segoe UI", Tahoma, sans-serif',
				'--lb-font-heading'         => '"Segoe UI", Tahoma, sans-serif',
				'--lb-hero-title-size'      => '40px',
				'--lb-button-background'    => 'var(--lb-accent-soft)',
				'--lb-button-color'         => 'var(--lb-accent-color)',
				'--lb-button-border'        => 'transparent',
				'--lb-card-background'      => '#ffffff',
				'--lb-card-border'          => 'var(--lb-border-color)',
				'--lb-card-shadow'          => '0 8px 20px rgba(18, 36, 52, 0.05)',
				'--lb-content-gap'          => '18px',
				'--lb-control-height'       => '42px',
				'--lb-card-padding'         => '20px',
				'--lb-button-padding-y'     => '12px',
				'--lb-button-padding-x'     => '22px',
				'--lb-topbar-padding-y'     => '12px',
				'--lb-topbar-padding-x'     => '18px',
				'--lb-border-contrast'      => 'rgba(23, 48, 66, 0.18)'
			]
		];
	}

	function landingbuilder_get_runtime_theme_context_from_theme(array $theme, array $site_theme = []) {
		$catalog = landingbuilder_get_theme_runtime_catalog();
		$theme = landingbuilder_normalize_theme_state($theme, $site_theme ?: landingbuilder_get_site_theme_defaults());

		$vars = array_merge($catalog['base_vars'], [
			'--lb-page-max-width'       => $catalog['container_presets'][$theme['container_preset']] ?? '1120px',
			'--lb-section-gap'          => $catalog['section_spacing'][$theme['section_spacing']] ?? '32px'
		], $catalog['global_style_presets'][$theme['global_style_preset']] ?? [], $catalog['color_presets'][$theme['color_preset']] ?? [], $catalog['typography_presets'][$theme['typography_preset']] ?? [], $catalog['radius_presets'][$theme['radius_preset']] ?? [], $catalog['density_presets'][$theme['density_preset']] ?? [], $catalog['contrast_presets'][$theme['contrast_preset']] ?? [], $catalog['button_presets'][$theme['button_preset']] ?? [], $catalog['card_presets'][$theme['card_preset']] ?? []);

		return [
			'theme'            => $theme,
			'vars'             => $vars,
			'container_presets'=> $catalog['container_presets'],
			'section_spacing'  => $catalog['section_spacing']
		];
	}

	function landingbuilder_get_runtime_theme_context(array $page) {
		$page_theme = isset($page['schema']['theme']) && is_array($page['schema']['theme']) ? $page['schema']['theme'] : [];
		return landingbuilder_get_runtime_theme_context_from_theme($page_theme);
	}

	function landingbuilder_get_section_value(array $section, $key, $default = '') {
		if (isset($section[$key]) && $section[$key] !== '') {
			return $section[$key];
		}

		if (!empty($section['settings']) && is_array($section['settings']) && isset($section['settings'][$key]) && $section['settings'][$key] !== '') {
			return $section['settings'][$key];
		}

		return $default;
	}

	function landingbuilder_get_section_presentation(array $section, array $theme_context) {
		$theme = $theme_context['theme'];
		$style_preset = landingbuilder_get_section_value($section, 'style_preset', 'content');
		$background_tone = landingbuilder_get_section_value($section, 'background_tone', 'base');
		$container_preset = landingbuilder_get_section_value($section, 'container_preset', $theme['container_preset'] ?? 'standard');
		$spacing_preset = landingbuilder_get_section_value($section, 'spacing_preset', 'md');

		$classes = [
			'lb-section--style-' . landingbuilder_sanitize_theme_token($style_preset),
			'lb-section--tone-' . landingbuilder_sanitize_theme_token($background_tone),
			'lb-section--container-' . landingbuilder_sanitize_theme_token($container_preset),
			'lb-section--spacing-' . landingbuilder_sanitize_theme_token($spacing_preset)
		];

		if (!empty($section['settings']['css_class'])) {
			$classes[] = $section['settings']['css_class'];
		}

		return [
			'style_preset'     => $style_preset,
			'background_tone'  => $background_tone,
			'container_preset' => $container_preset,
			'spacing_preset'   => $spacing_preset,
			'class'            => implode(' ', array_filter($classes))
		];
	}
}