<?php

class modelLandingbuilder extends cmsModel {

	const PAGE_TABLE = 'landingbuilder_pages';
	const VERSION_TABLE = 'landingbuilder_page_versions';
	const PAGE_WIDGET_TABLE = 'landingbuilder_page_widgets';

	public function hasInstalledSchema() {
		return $this->db->isTableExists(self::PAGE_TABLE);
	}

	public function getPagesForAdmin() {

		if (!$this->hasInstalledSchema()) {
			return $this->getDefaultPages();
		}

		$pages = $this->orderBy('updated_at', 'desc')->get(self::PAGE_TABLE, function ($item) {
			return $this->normalizePage($item);
		});

		return $pages ? array_values($pages) : $this->getDefaultPages();
	}

	public function getShellVariantsForAdmin() {

		$defaults = $this->getDefaultShellVariants();
		$stored_variants = $this->getStoredShellVariants();
		$variants = [];

		foreach ($defaults as $variant_key => $default_variant) {
			$variants[] = $this->normalizeShellVariant($variant_key, array_merge($default_variant, $stored_variants[$variant_key] ?? []));
		}

		foreach ($stored_variants as $variant_key => $stored_variant) {
			if (isset($defaults[$variant_key])) {
				continue;
			}

			$variants[] = $this->normalizeShellVariant($variant_key, $stored_variant);
		}

		return $variants;
	}

	public function getShellVariantByKey($variant_key) {

		$variant_key = $this->sanitizeShellVariantKey($variant_key);
		if ($variant_key === '') {
			return false;
		}

		$variants = $this->getShellVariantsForAdmin();

		foreach ($variants as $variant) {
			if ($variant['key'] === $variant_key) {
				return $variant;
			}
		}

		return false;
	}

	public function prepareShellVariant($variant_key, array $data = []) {

		$current_variant = $this->getShellVariantByKey($variant_key);
		if (!$current_variant) {
			return false;
		}

		return $this->normalizeShellVariant($variant_key, array_merge($current_variant, $data));
	}

	public function saveShellVariant($variant_key, array $data) {

		$variant_key = $this->sanitizeShellVariantKey($variant_key);
		if ($variant_key === '') {
			return false;
		}

		$current_variant = $this->getShellVariantByKey($variant_key);
		if (!$current_variant) {
			return false;
		}

		$variant = $this->normalizeShellVariant($variant_key, array_merge($current_variant, $data));
		$stored_variants = $this->getStoredShellVariants();
		$stored_variants[$variant_key] = $this->extractShellVariantStorageData($variant);

		$options = (array) cmsController::loadOptions('landingbuilder');
		$options['shell_variants'] = $stored_variants;

		cmsController::saveOptions('landingbuilder', $options);

		return $this->getShellVariantByKey($variant_key);
	}

	public function getShellVariantChoiceCatalog() {
		return [
			'header_variant' => [
				'classic'          => 'Классический header',
				'split_navigation' => 'Header с отдельной полосой навигации',
				'centered_brand'   => 'Центрированный бренд и вторичная навигация',
				'compact'          => 'Компактный header'
			],
			'footer_variant' => [
				'minimal'   => 'Минимальный footer',
				'columns_3' => 'Footer в 3 колонки',
				'columns_4' => 'Footer в 4 колонки',
				'editorial' => 'Редакционный footer'
			],
			'menu_placement' => [
				'header_primary'   => 'В основном header',
				'header_secondary' => 'Во вторичном header',
				'site_top'         => 'В верхней служебной зоне'
			],
			'body_layout' => [
				'no_sidebars'  => 'Без сайдбаров',
				'left_sidebar' => 'Левый сайдбар',
				'right_sidebar'=> 'Правый сайдбар',
				'two_sidebars' => 'Два сайдбара'
			],
			'homepage_shell_mode' => [
				'inherit'   => 'Наследовать общий сценарий',
				'page_hero' => 'Hero управляется страницей',
				'shell_hero'=> 'Hero управляется shell',
				'mixed'     => 'Shell hero и page sections вместе'
			],
			'sticky_header' => [
				'off'   => 'Без прилипания',
				'on'    => 'Всегда липкий',
				'smart' => 'Липкий только при прокрутке вверх'
			],
			'mobile_menu_mode' => [
				'drawer'        => 'Выезжающая панель',
				'bottom_sheet'  => 'Нижняя панель',
				'inline_compact'=> 'Компактное встроенное меню'
			]
		];
	}

	public function getShellPreviewRows(array $variant) {

		$active_slots = array_flip($variant['active_slots']);
		$scheme = $this->getNordicShellScheme();
		$rows = [];

		foreach (($scheme['layout_rows'] ?? []) as $row) {
			$preview_cols = [];

			foreach (($row['cols'] ?? []) as $col) {
				$slot_name = (string) ($col['name'] ?? '');
				$preview_cols[] = [
					'name'      => $slot_name,
					'title'     => (string) ($col['title'] ?? $slot_name),
					'is_active' => isset($active_slots[$slot_name])
				];
			}

			$rows[] = [
				'title' => (string) ($row['title'] ?? ''),
				'cols'  => $preview_cols
			];
		}

		return $rows;
	}

	public function getPageByKey($page_key) {

		$legacy_page = $this->getLegacyPageByKey($page_key);
		$bridge_page = $this->getNordicbuilderBridgePage($page_key, $legacy_page ?: []);

		if ($bridge_page) {
			$bridge_page['widget_nodes'] = $this->extractSystemWidgetNodes($bridge_page['schema']);
			return $bridge_page;
		}

		return $legacy_page;
	}

	public function getPageForMigration($page_key) {
		return $this->getLegacyPageByKey($page_key);
	}

	protected function getLegacyPageByKey($page_key) {

		if (!$this->hasInstalledSchema()) {
			return $this->getDefaultPageByKey($page_key);
		}

		$page = $this->getItemByField(self::PAGE_TABLE, 'name', $page_key, function ($item) {
			return $this->normalizePage($item);
		});

		if (!$page) {
			return $this->getDefaultPageByKey($page_key);
		}

		$page['widget_nodes'] = $this->getPageWidgetNodes($page['id'], $page['schema']);

		return $page;
	}

	public function getContentCategoryOverlay(array $ctype, array $category = [], $is_admin = false) {

		$page_key = $this->resolveContentCategoryPageKey($ctype, $category);

		return $this->getOverlayIntegrationByPageKey($page_key, $is_admin);
	}

	public function getUserProfileOverlay(array $profile = [], $is_admin = false) {

		if (empty($profile['id'])) {
			return false;
		}

		return $this->getOverlayIntegrationByPageKey('profile-cover', $is_admin);
	}

	public function createPage(array $data, $user_id = 0) {

		if (!$this->hasInstalledSchema()) {
			return false;
		}

		$key = $this->sanitizePageKey($data['key'] ?? '');
		$title = trim((string) ($data['title'] ?? ''));

		if (!$key || !$title) {
			return false;
		}

		$exists = $this->getItemByField(self::PAGE_TABLE, 'name', $key);
		if ($exists) {
			return false;
		}

		$page_mode = !empty($data['mode']) ? $data['mode'] : 'full_takeover';
		$status = !empty($data['status']) ? $data['status'] : 'draft';
		$template = !empty($data['template']) ? $data['template'] : 'nordic';
		$schema = isset($data['schema']) && is_array($data['schema']) ? $data['schema'] : ['sections' => []];
		$now = date('Y-m-d H:i:s');

		$page_id = $this->insert(self::PAGE_TABLE, [
			'name'        => $key,
			'title'       => $title,
			'status'      => $status,
			'page_mode'   => $page_mode,
			'template'    => $template,
			'schema_json' => $this->encodeJson($this->normalizeSchema($schema, $key)),
			'created_at'  => $now,
			'updated_at'  => $now
		]);

		if (!$page_id) {
			return false;
		}

		return $this->savePageSchema($key, $schema, $user_id, 'Первичная версия');
	}

	public function getAvailableSystemWidgets() {

		if (!$this->db->isTableExists('widgets')) {
			return [];
		}

		$widgets = $this->orderByList([
			['by' => 'controller', 'to' => 'asc'],
			['by' => 'name', 'to' => 'asc']
		])->get('widgets', function ($item) {
			if (!empty($item['image_hint_path'])) {
				$item['image_hint_path'] = cmsConfig::get('upload_host') . '/' . $item['image_hint_path'];
			}
			return $item;
		});

		if (!$widgets) {
			return [];
		}

		$grouped = [];

		foreach ($widgets as $widget) {
			$group_key = $widget['controller'] ? $widget['controller'] : 'core';
			$grouped[$group_key][] = $widget;
		}

		return $grouped;
	}

	public function getSystemWidgetById($widget_id) {

		if (!$widget_id || !$this->db->isTableExists('widgets')) {
			return false;
		}

		return $this->getItemById('widgets', $widget_id);
	}

	public function savePageSchema($page_key, array $schema, $user_id = 0, $version_note = '') {

		$bridge_model = $this->getNordicbuilderBridgeModel();
		$legacy_page = $this->getLegacyPageByKey($page_key);
		$bridge_page = $bridge_model ? $bridge_model->getPageDocumentByKey($page_key) : false;

		if ($bridge_model && $bridge_page) {
			$fallback_page = $legacy_page ?: $this->getDefaultPageByKey($page_key);
			$result = $bridge_model->saveBridgePageSchema($page_key, $this->normalizeSchema($schema, $page_key), $fallback_page ?: [], $user_id, (string) (($fallback_page['status'] ?? 'draft')));

			if (!empty($result['is_valid'])) {
				return $this->getPageByKey($page_key);
			}

			return false;
		}

		if (!$this->hasInstalledSchema() || !$this->db->isTableExists(self::VERSION_TABLE) || !$this->db->isTableExists(self::PAGE_WIDGET_TABLE)) {
			return false;
		}

		$page = $this->getItemByField(self::PAGE_TABLE, 'name', $page_key);
		if (!$page) {
			return false;
		}

		$schema = $this->normalizeSchema($schema, $page_key);
		$schema_json = $this->encodeJson($schema);
		$now = date('Y-m-d H:i:s');

		$version_id = $this->insert(self::VERSION_TABLE, [
			'page_id'       => $page['id'],
			'version_note'  => trim($version_note),
			'schema_json'   => $schema_json,
			'created_by'    => (int) $user_id,
			'created_at'    => $now
		]);

		if (!$version_id) {
			return false;
		}

		$updated = $this->update(self::PAGE_TABLE, $page['id'], [
			'schema_json'         => $schema_json,
			'current_version_id'  => $version_id,
			'updated_at'          => $now
		]);

		if (!$updated) {
			return false;
		}

		$this->syncPageWidgetNodes($page['id'], $schema, $now);

		return $this->getPageByKey($page_key);
	}

	public function getPageVersionsByKey($page_key, $limit = 20) {

		if (!$this->hasInstalledSchema() || !$this->db->isTableExists(self::VERSION_TABLE)) {
			return [];
		}

		$page = $this->getItemByField(self::PAGE_TABLE, 'name', $page_key);
		if (!$page) {
			return [];
		}

		return $this->getPageVersions($page['id'], $limit);
	}

	public function getPageVersions($page_id, $limit = 20) {

		if (!$page_id || !$this->db->isTableExists(self::VERSION_TABLE)) {
			return [];
		}

		$this->filterEqual('page_id', $page_id);
		$this->orderBy('id', 'desc');

		if ($limit > 0) {
			$this->limit($limit);
		}

		$versions = $this->get(self::VERSION_TABLE, function ($item) {
			$item['is_current'] = false;
			return $item;
		});

		return $versions ? array_values($versions) : [];
	}

	public function restorePageVersion($version_id, $user_id = 0) {

		if (!$version_id || !$this->hasInstalledSchema() || !$this->db->isTableExists(self::VERSION_TABLE)) {
			return false;
		}

		$version = $this->getItemById(self::VERSION_TABLE, $version_id);
		if (!$version) {
			return false;
		}

		$page = $this->getItemById(self::PAGE_TABLE, $version['page_id']);
		if (!$page) {
			return false;
		}

		$schema = json_decode($version['schema_json'], true);
		if (!is_array($schema)) {
			return false;
		}

		return $this->savePageSchema($page['name'], $schema, $user_id, 'Восстановление версии #' . $version_id);
	}

	public function getCanvasScreen(array $page, array $options = []) {

		$device_keys = ['desktop', 'mobile'];

		if (!empty($options['enable_tablet_mode'])) {
			$device_keys = ['desktop', 'tablet', 'mobile'];
		}

		$left_tabs = $this->getCanvasLibraryTabs($options);

		$theme_defaults = $this->getCanvasThemeDefaults($options);
		$adapter = $this->getAdapterDefinition($this->resolveAdapterKey($page));
		$shell = $this->buildRuntimeShell($page, $adapter);
		$zones = $this->buildRuntimeZones($page, $adapter, $shell);
		$slot_map = $this->buildRuntimeSlotMap($shell, $zones, $adapter);

		return [
			'devices'             => $this->getCanvasDevices($device_keys),
			'device_keys'         => $device_keys,
			'left_tabs'           => $left_tabs,
			'section_presets'     => $this->getCanvasSectionPresets(),
			'theme_defaults'      => $theme_defaults,
			'theme_option_catalog'=> $this->getCanvasThemeOptionCatalog(),
			'default_section_layout' => !empty($options['default_section_layout']) ? (string) $options['default_section_layout'] : '1col',
			'screen_map'          => [
				'topbar' => ['page_summary', 'page_theme', 'device_preview', 'page_versions'],
				'library' => array_column($left_tabs, 'key'),
				'sidebar' => ['page', 'selection', 'widget_options', 'versions'],
				'canvas' => ['content_body']
			],
			'page_shell'          => $this->getCanvasPageShellScreen($page, $shell, $slot_map),
			'sections'            => $page['schema']['sections'],
			'widget_nodes'        => $page['widget_nodes'],
			'versions'            => !empty($page['key']) ? $this->getPageVersionsByKey($page['key']) : [],
			'schema_installed'    => $this->hasInstalledSchema() || (($page['storage_backend'] ?? '') === 'nordicbuilder')
		];
	}

	protected function getNordicbuilderBridgeModel() {
		static $bridge_model = null;
		static $resolved = false;

		if ($resolved) {
			return $bridge_model;
		}

		$resolved = true;
		$bridge_model = cmsCore::getModel('nordicbuilder');

		if (!$bridge_model || !method_exists($bridge_model, 'getBridgePageForLandingbuilder')) {
			$bridge_model = false;
		}

		return $bridge_model;
	}

	protected function getNordicbuilderBridgePage($page_key, array $fallback_page = []) {
		$bridge_model = $this->getNordicbuilderBridgeModel();

		if (!$bridge_model) {
			return false;
		}

		return $bridge_model->getBridgePageForLandingbuilder($page_key, $fallback_page);
	}

	protected function getCanvasPageShellScreen(array $page, array $shell, array $slot_map) {

		$variant_options = [[
			'value'       => '',
			'title'       => 'Авто по правилам',
			'description' => 'Resolver сам выберет shell variant по типу страницы, adapter и page mode.'
		]];
		$variant_catalog = [];
		$slot_titles = $this->getNordicShellSlotTitleMap();
		$active_slots = [];

		foreach ($this->getShellVariantsForAdmin() as $variant) {
			$variant_options[] = [
				'value'       => $variant['key'],
				'title'       => $variant['title'],
				'description' => $variant['description']
			];

			$variant_catalog[$variant['key']] = [
				'key'          => $variant['key'],
				'title'        => $variant['title'],
				'description'  => $variant['description'],
				'scope'        => $variant['scope'],
				'body_layout'  => $variant['body_layout'],
				'active_slots' => $variant['active_slots']
			];
		}

		foreach (($shell['available_slots'] ?? $shell['active_slots'] ?? []) as $slot_key) {
			$slot = $slot_map[$slot_key] ?? ['render_mode' => 'widgets', 'section_count' => 0];
			$active_slots[] = [
				'key'           => $slot_key,
				'title'         => $slot_titles[$slot_key] ?? $slot_key,
				'render_mode'   => $slot['render_mode'],
				'section_count' => (int) ($slot['section_count'] ?? 0)
			];
		}

		return [
			'variant_options'         => $variant_options,
			'variant_catalog'         => $variant_catalog,
			'current_override'        => (string) ($page['schema']['layout']['shell_variant'] ?? ''),
			'auto_variant_key'        => $shell['variant_key'] ?? 'site-default',
			'auto_assignment_source'  => $shell['assignment_source'] ?? 'default',
			'assignment_source_titles'=> $this->getShellAssignmentSourceTitles(),
			'slot_titles'             => $slot_titles,
			'content_slot_options'    => $this->getCanvasPageContentSlotOptions($shell, $slot_map),
			'effective_variant'       => [
				'key'               => $shell['variant_key'] ?? 'site-default',
				'title'             => $shell['variant_title'] ?? 'Базовый shell',
				'assignment_source' => $shell['assignment_source'] ?? 'default',
				'body_layout'       => $shell['body_layout'] ?? 'no_sidebars',
				'content_slot'      => $shell['content_slot'] ?? 'content_body',
				'active_slots'      => $active_slots
			]
		];
	}

	protected function getCanvasPageContentSlotOptions(array $shell, array $slot_map) {

		$allowed_slot_keys = ['hero', 'before_content', 'content_body', 'content_sidebar_left', 'content_sidebar_right', 'after_content'];
		$slot_titles = $this->getNordicShellSlotTitleMap();
		$options = [];

		foreach (($shell['available_slots'] ?? $shell['active_slots'] ?? []) as $slot_key) {
			if (!in_array($slot_key, $allowed_slot_keys, true)) {
				continue;
			}

			$slot = $slot_map[$slot_key] ?? [];
			$options[] = [
				'value' => $slot_key,
				'title' => $slot_titles[$slot_key] ?? $slot_key,
				'hint'  => !empty($slot['render_mode']) && $slot['render_mode'] !== 'widgets'
					? 'В этом slot уже есть builder/native слой: ' . $slot['render_mode']
					: 'Основной slot для builder-содержимого страницы.'
			];
		}

		if (!$options) {
			$options[] = [
				'value' => 'content_body',
				'title' => $slot_titles['content_body'] ?? 'content_body'
			];
		}

		return $options;
	}

	protected function getCanvasDevices(array $device_keys) {

		$definitions = [
			'desktop' => [
				'key'            => 'desktop',
				'title'          => 'Компьютер',
				'viewport_width' => 1280,
				'canvas_width'   => '100%'
			],
			'tablet' => [
				'key'            => 'tablet',
				'title'          => 'Планшет',
				'viewport_width' => 820,
				'canvas_width'   => '820px'
			],
			'mobile' => [
				'key'            => 'mobile',
				'title'          => 'Телефон',
				'viewport_width' => 430,
				'canvas_width'   => '430px'
			]
		];

		$devices = [];

		foreach ($device_keys as $device_key) {
			if (isset($definitions[$device_key])) {
				$devices[] = $definitions[$device_key];
			}
		}

		return $devices;
	}

	protected function getCanvasLibraryTabs(array $options = []) {

		$tabs = [
			[
				'key'   => 'sections',
				'title' => 'Секции'
			],
			[
				'key'   => 'blocks',
				'title' => 'Блоки Нордик'
			]
		];

		if (!empty($options['enable_system_widgets'])) {
			$tabs[] = [
				'key'   => 'widgets',
				'title' => 'Системные виджеты'
			];
		}

		return $tabs;
	}

	public function getThemeOptionCatalog() {
		return [
			'global_style_preset' => [
				['value' => 'nordic_balanced', 'title' => 'Сбалансированный Нордик', 'description' => 'Универсальный пресет для сайта компании, сервиса и смешанных страниц.'],
				['value' => 'nordic_contrast', 'title' => 'Контрастный Нордик', 'description' => 'Более плотный и контрастный стиль для акцентного бренда и первого экрана.'],
				['value' => 'nordic_editorial', 'title' => 'Редакционный Нордик', 'description' => 'Спокойный пресет с более журнальным характером и мягким ритмом.'],
				['value' => 'nordic_catalog', 'title' => 'Каталоговый Нордик', 'description' => 'Пресет для каталогов, списков и рабочих страниц с карточками.']
			],
			'color_preset' => [
				['value' => 'nordic_day', 'title' => 'Дневная палитра', 'description' => 'Светлая нейтральная база с мягким акцентом.'],
				['value' => 'slate_contrast', 'title' => 'Сланцевый контраст', 'description' => 'Холодная контрастная палитра для более строгого интерфейса.'],
				['value' => 'forest_accent', 'title' => 'Лесной акцент', 'description' => 'Спокойная природная палитра с зеленым акцентом.']
			],
			'typography_preset' => [
				['value' => 'editorial', 'title' => 'Редакционная', 'description' => 'Более журнальный характер текста и заголовков.'],
				['value' => 'neutral', 'title' => 'Нейтральная', 'description' => 'Универсальная пара для большинства сайтов.'],
				['value' => 'compact', 'title' => 'Компактная', 'description' => 'Более плотный набор для каталожных и утилитарных страниц.']
			],
			'container_preset' => [
				['value' => 'text', 'title' => 'Узкий текстовый', 'description' => 'Для long-form текста, help и editorial-страниц.'],
				['value' => 'standard', 'title' => 'Стандартный', 'description' => 'Основной рабочий контейнер сайта.'],
				['value' => 'wide', 'title' => 'Широкий', 'description' => 'Для hero, списков карточек и более просторных экранов.'],
				['value' => 'full', 'title' => 'Во всю ширину', 'description' => 'Для максимально широкого контента и промо-блоков.']
			],
			'button_preset' => [
				['value' => 'soft_accent', 'title' => 'Мягкий акцент', 'description' => 'Спокойные кнопки с мягкой цветовой подложкой.'],
				['value' => 'solid_brand', 'title' => 'Плотный брендовый', 'description' => 'Более насыщенные CTA для продающих сценариев.'],
				['value' => 'ghost', 'title' => 'Прозрачный', 'description' => 'Легкие вторичные кнопки и аккуратные действия.']
			],
			'card_preset' => [
				['value' => 'quiet', 'title' => 'Спокойные', 'description' => 'Базовые карточки без лишнего визуального шума.'],
				['value' => 'raised', 'title' => 'Поднятые', 'description' => 'Карточки с более заметной глубиной и тенью.'],
				['value' => 'outline', 'title' => 'С обводкой', 'description' => 'Легкая геометрия с акцентом на границу.']
			],
			'section_spacing' => [
				['value' => 'compact', 'title' => 'Компактный', 'description' => 'Плотный вертикальный ритм для каталожных страниц.'],
				['value' => 'comfortable', 'title' => 'Комфортный', 'description' => 'Сбалансированный ритм по умолчанию.'],
				['value' => 'airy', 'title' => 'Воздушный', 'description' => 'Больше воздуха между секциями и блоками.']
			]
		];
	}

	public function getThemeFormCatalog() {

		$form_catalog = [];

		foreach ($this->getThemeOptionCatalog() as $option_key => $items) {
			$form_catalog[$option_key] = [];

			foreach ($items as $item) {
				$form_catalog[$option_key][$item['value']] = $item['title'];
			}
		}

		return $form_catalog;
	}

	public function getThemeOptionTitle($option_key, $value, array $catalog = []) {

		$catalog = $catalog ?: $this->getThemeOptionCatalog();
		$value = (string) $value;

		foreach (($catalog[$option_key] ?? []) as $item) {
			if ((string) ($item['value'] ?? '') === $value) {
				return (string) ($item['title'] ?? $value);
			}
		}

		return $value;
	}

	public function getSiteThemeDefaults(array $options = []) {

		$options = $options ?: (array) cmsController::loadOptions('landingbuilder');

		return $this->normalizeThemeState([
			'global_style_preset' => !empty($options['default_global_style_preset']) ? (string) $options['default_global_style_preset'] : '',
			'color_preset'        => !empty($options['default_color_preset']) ? (string) $options['default_color_preset'] : '',
			'typography_preset'   => !empty($options['default_typography_preset']) ? (string) $options['default_typography_preset'] : '',
			'container_preset'    => !empty($options['default_container_preset']) ? (string) $options['default_container_preset'] : '',
			'button_preset'       => !empty($options['default_button_preset']) ? (string) $options['default_button_preset'] : '',
			'card_preset'         => !empty($options['default_card_preset']) ? (string) $options['default_card_preset'] : '',
			'section_spacing'     => !empty($options['default_section_spacing']) ? (string) $options['default_section_spacing'] : ''
		]);
	}

	public function saveSiteThemeSettings(array $settings) {

		$theme = $this->normalizeThemeState($settings);
		$options = (array) cmsController::loadOptions('landingbuilder');

		$options['default_global_style_preset'] = $theme['global_style_preset'];
		$options['default_color_preset'] = $theme['color_preset'];
		$options['default_typography_preset'] = $theme['typography_preset'];
		$options['default_container_preset'] = $theme['container_preset'];
		$options['default_button_preset'] = $theme['button_preset'];
		$options['default_card_preset'] = $theme['card_preset'];
		$options['default_section_spacing'] = $theme['section_spacing'];

		cmsController::saveOptions('landingbuilder', $options);

		return $theme;
	}

	public function getDesignSystemScreen(array $theme = [], array $catalog = []) {

		$catalog = $catalog ?: $this->getThemeOptionCatalog();
		$theme = $theme ? $this->normalizeThemeState($theme) : $this->getSiteThemeDefaults();

		return [
			'theme'   => $theme,
			'catalog' => $catalog,
			'summary' => [
				['label' => 'Стартовый пресет', 'value' => $this->getThemeOptionTitle('global_style_preset', $theme['global_style_preset'], $catalog)],
				['label' => 'Палитра', 'value' => $this->getThemeOptionTitle('color_preset', $theme['color_preset'], $catalog)],
				['label' => 'Типографика', 'value' => $this->getThemeOptionTitle('typography_preset', $theme['typography_preset'], $catalog)],
				['label' => 'Контейнеры', 'value' => $this->getThemeOptionTitle('container_preset', $theme['container_preset'], $catalog)],
				['label' => 'Кнопки', 'value' => $this->getThemeOptionTitle('button_preset', $theme['button_preset'], $catalog)],
				['label' => 'Карточки', 'value' => $this->getThemeOptionTitle('card_preset', $theme['card_preset'], $catalog)],
				['label' => 'Ритм секций', 'value' => $this->getThemeOptionTitle('section_spacing', $theme['section_spacing'], $catalog)]
			]
		];
	}

	protected function getCanvasThemeOptionCatalog() {
		return $this->getThemeOptionCatalog();
	}

	protected function getCanvasThemeDefaults(array $options = []) {
		return $this->getSiteThemeDefaults($options);
	}

	protected function getCanvasSectionPresets() {
		return [
			[
				'key' => 'hero_simple',
				'title' => 'Первый экран с кнопкой',
				'description' => 'Крупный первый экран с заголовком, текстом и кнопкой.',
				'layout' => '1col',
				'section_type' => 'hero',
				'style_preset' => 'hero',
				'background_tone' => 'brand-soft',
				'container_preset' => 'standard',
				'spacing_preset' => 'xl',
				'columns' => [
					[
						'title' => 'Основной контент',
						'nodes' => [
							['type' => 'block', 'label' => 'Главный заголовок', 'source_key' => 'core.hero-heading'],
							['type' => 'block', 'label' => 'Кнопки первого экрана', 'source_key' => 'core.hero-actions']
						]
					]
				]
			],
			[
				'key' => 'hero_media_left',
				'title' => 'Первый экран с медиа слева',
				'description' => 'Медиа слева, контент справа. Подходит для продукта и категории.',
				'layout' => '2col_equal',
				'section_type' => 'hero',
				'style_preset' => 'hero-split',
				'background_tone' => 'base',
				'container_preset' => 'wide',
				'spacing_preset' => 'xl',
				'columns' => [
					[
						'title' => 'Медиа',
						'nodes' => [
							['type' => 'block', 'label' => 'Карточка медиа', 'source_key' => 'core.cards-grid']
						]
					],
					[
						'title' => 'Контент',
						'nodes' => [
							['type' => 'block', 'label' => 'Главный заголовок', 'source_key' => 'core.hero-heading'],
							['type' => 'block', 'label' => 'Список преимуществ', 'source_key' => 'core.feature-list']
						]
					]
				]
			],
			[
				'key' => 'hero_media_right',
				'title' => 'Первый экран с медиа справа',
				'description' => 'Контент слева, медиа справа.',
				'layout' => '2col_equal',
				'section_type' => 'hero',
				'style_preset' => 'hero-split',
				'background_tone' => 'base',
				'container_preset' => 'wide',
				'spacing_preset' => 'xl',
				'columns' => [
					[
						'title' => 'Контент',
						'nodes' => [
							['type' => 'block', 'label' => 'Главный заголовок', 'source_key' => 'core.hero-heading'],
							['type' => 'block', 'label' => 'Кнопки первого экрана', 'source_key' => 'core.hero-actions']
						]
					],
					[
						'title' => 'Медиа',
						'nodes' => [
							['type' => 'block', 'label' => 'Карточка медиа', 'source_key' => 'core.cards-grid']
						]
					]
				]
			],
			[
				'key' => 'benefits_3_cards',
				'title' => 'Три карточки преимуществ',
				'description' => 'Три карточки преимуществ.',
				'layout' => '3col_equal',
				'section_type' => 'benefits',
				'style_preset' => 'cards',
				'background_tone' => 'base',
				'container_preset' => 'standard',
				'spacing_preset' => 'lg',
				'columns' => [
					['title' => 'Преимущество 1', 'nodes' => [['type' => 'block', 'label' => 'Карточка 1', 'source_key' => 'core.cards-grid']]],
					['title' => 'Преимущество 2', 'nodes' => [['type' => 'block', 'label' => 'Карточка 2', 'source_key' => 'core.cards-grid']]],
					['title' => 'Преимущество 3', 'nodes' => [['type' => 'block', 'label' => 'Карточка 3', 'source_key' => 'core.cards-grid']]]
				]
			],
			[
				'key' => 'features_2_columns',
				'title' => 'Две колонки с особенностями',
				'description' => 'Список особенностей в двух колонках.',
				'layout' => '2col_equal',
				'section_type' => 'features',
				'style_preset' => 'feature-list',
				'background_tone' => 'base',
				'container_preset' => 'standard',
				'spacing_preset' => 'lg',
				'columns' => [
					['title' => 'Колонка 1', 'nodes' => [['type' => 'block', 'label' => 'Список преимуществ', 'source_key' => 'core.feature-list']]],
					['title' => 'Колонка 2', 'nodes' => [['type' => 'block', 'label' => 'Сетка карточек', 'source_key' => 'core.cards-grid']]]
				]
			],
			[
				'key' => 'logo_cloud',
				'title' => 'Облако логотипов',
				'description' => 'Логотипы клиентов или партнёров.',
				'layout' => '1col',
				'section_type' => 'logos',
				'style_preset' => 'logos',
				'background_tone' => 'muted',
				'container_preset' => 'wide',
				'spacing_preset' => 'md',
				'columns' => [
					['title' => 'Логотипы', 'nodes' => [['type' => 'block', 'label' => 'Сетка карточек', 'source_key' => 'core.cards-grid']]]
				]
			],
			[
				'key' => 'stats_row',
				'title' => 'Ряд со статистикой',
				'description' => 'Короткий блок со статистикой.',
				'layout' => '3col_equal',
				'section_type' => 'stats',
				'style_preset' => 'stats',
				'background_tone' => 'contrast',
				'container_preset' => 'standard',
				'spacing_preset' => 'md',
				'columns' => [
					['title' => 'Стат 1', 'nodes' => [['type' => 'block', 'label' => 'Показатель 1', 'source_key' => 'profile.quick-stats']]],
					['title' => 'Стат 2', 'nodes' => [['type' => 'block', 'label' => 'Показатель 2', 'source_key' => 'profile.quick-stats']]],
					['title' => 'Стат 3', 'nodes' => [['type' => 'block', 'label' => 'Показатель 3', 'source_key' => 'profile.quick-stats']]]
				]
			],
			[
				'key' => 'testimonials',
				'title' => 'Отзывы',
				'description' => 'Отзывы в карточках.',
				'layout' => '3col_equal',
				'section_type' => 'testimonials',
				'style_preset' => 'cards',
				'background_tone' => 'base',
				'container_preset' => 'standard',
				'spacing_preset' => 'lg',
				'columns' => [
					['title' => 'Отзыв 1', 'nodes' => [['type' => 'block', 'label' => 'Карточка 1', 'source_key' => 'core.cards-grid']]],
					['title' => 'Отзыв 2', 'nodes' => [['type' => 'block', 'label' => 'Карточка 2', 'source_key' => 'core.cards-grid']]],
					['title' => 'Отзыв 3', 'nodes' => [['type' => 'block', 'label' => 'Карточка 3', 'source_key' => 'core.cards-grid']]]
				]
			],
			[
				'key' => 'faq_accordion',
				'title' => 'FAQ-аккордеон',
				'description' => 'Вопросы и ответы.',
				'layout' => '1col',
				'section_type' => 'faq',
				'style_preset' => 'faq',
				'background_tone' => 'base',
				'container_preset' => 'text',
				'spacing_preset' => 'lg',
				'columns' => [
					['title' => 'FAQ', 'nodes' => [['type' => 'block', 'label' => 'Список преимуществ', 'source_key' => 'core.feature-list']]]
				]
			],
			[
				'key' => 'cta_banner',
				'title' => 'Баннер с действием',
				'description' => 'Финальный блок с призывом к действию.',
				'layout' => '2col_equal',
				'section_type' => 'cta',
				'style_preset' => 'cta',
				'background_tone' => 'brand-strong',
				'container_preset' => 'wide',
				'spacing_preset' => 'lg',
				'columns' => [
					['title' => 'Текст', 'nodes' => [['type' => 'block', 'label' => 'Главный заголовок', 'source_key' => 'core.hero-heading']]],
					['title' => 'Действие', 'nodes' => [['type' => 'block', 'label' => 'Кнопки первого экрана', 'source_key' => 'core.hero-actions']]]
				]
			],
			[
				'key' => 'contacts_map',
				'title' => 'Контакты и карта',
				'description' => 'Контакты рядом с картой или формой.',
				'layout' => '2col_sidebar_right',
				'section_type' => 'contacts',
				'style_preset' => 'contacts',
				'background_tone' => 'muted',
				'container_preset' => 'wide',
				'spacing_preset' => 'lg',
				'columns' => [
					['title' => 'Контакты', 'nodes' => [['type' => 'block', 'label' => 'Список преимуществ', 'source_key' => 'core.feature-list']]],
					['title' => 'Карта / форма', 'nodes' => []]
				]
			],
			[
				'key' => 'rich_text',
				'title' => 'Текстовая секция',
				'description' => 'Текстовый блок для описания, условий или статьи.',
				'layout' => '1col',
				'section_type' => 'content',
				'style_preset' => 'content',
				'background_tone' => 'base',
				'container_preset' => 'text',
				'spacing_preset' => 'lg',
				'columns' => [
					['title' => 'Текст', 'nodes' => [['type' => 'block', 'label' => 'Главный заголовок', 'source_key' => 'core.hero-heading']]]
				]
			],
			[
				'key' => 'catalog_header',
				'title' => 'Шапка каталога',
				'description' => 'Верх страницы категории с шапкой и фильтрами.',
				'layout' => '2col_sidebar_right',
				'section_type' => 'catalog',
				'style_preset' => 'catalog',
				'background_tone' => 'base',
				'container_preset' => 'wide',
				'spacing_preset' => 'md',
				'columns' => [
					['title' => 'Контент', 'nodes' => [['type' => 'block', 'label' => 'Шапка категории', 'source_key' => 'ads.category-header']]],
					['title' => 'Фильтры', 'nodes' => [['type' => 'block', 'label' => 'Панель фильтров', 'source_key' => 'ads.filter-bar']]]
				]
			],
			[
				'key' => 'profile_cover',
				'title' => 'Обложка профиля',
				'description' => 'Обложка и краткая статистика профиля.',
				'layout' => '2col_equal',
				'section_type' => 'profile',
				'style_preset' => 'profile',
				'background_tone' => 'contrast',
				'container_preset' => 'wide',
				'spacing_preset' => 'lg',
				'columns' => [
					['title' => 'Обложка', 'nodes' => [['type' => 'block', 'label' => 'Обложка профиля', 'source_key' => 'profile.cover-hero']]],
					['title' => 'Статистика', 'nodes' => [['type' => 'block', 'label' => 'Статистика профиля', 'source_key' => 'profile.quick-stats']]]
				]
			]
		];
	}

	public function getRuntimePage(array $page) {

		$page['adapter_key'] = $this->resolveAdapterKey($page);
		$page['page_type'] = $this->resolvePageType($page);
		$page['schema'] = $this->enrichSchemaForRuntime($page);

		$adapter = $this->getAdapterDefinition($page['adapter_key']);
		$shell = $this->buildRuntimeShell($page, $adapter);
		$page = $this->applyHomepageShellModeToPage($page, $shell);

		$zones = $this->buildRuntimeZones($page, $adapter, $shell);

		return [
			'adapter'     => $adapter,
			'shell'       => $shell,
			'page_type'   => $page['page_type'],
			'zones'       => $zones,
			'slot_map'    => $this->buildRuntimeSlotMap($shell, $zones, $adapter),
			'widget_map'  => $this->getPageWidgetMap($page)
		];
	}

	public function getAdapterDefinitions() {

		return [
			'standalone_landing' => [
				'key'                  => 'standalone_landing',
				'title'                => 'Самостоятельная landing-страница',
				'description'          => 'Полностью своя страница, собранная из секций конструктора.',
				'shell'                => 'nordic',
				'default_zone'         => 'content_body',
				'native_content_label' => '',
				'zones'                => [
					[
						'key'         => 'hero',
						'slot_key'    => 'hero',
						'title'       => 'Hero страницы',
						'kind'        => 'builder',
						'description' => 'Первый экран страницы, если им управляет builder или homepage shell mode.'
					],
					[
						'key'         => 'before_content',
						'slot_key'    => 'before_content',
						'title'       => 'Перед основным содержимым',
						'kind'        => 'builder',
						'description' => 'Промежуточная зона между shell-верхом и основным содержимым страницы.'
					],
					[
						'key'         => 'content_body',
						'slot_key'    => 'content_body',
						'title'       => 'Основное содержимое страницы',
						'kind'        => 'builder',
						'description' => 'В этой зоне конструктор управляет основным содержимым внутри shell шаблона nordic.'
					],
					[
						'key'         => 'after_content',
						'slot_key'    => 'after_content',
						'title'       => 'После основного содержимого',
						'kind'        => 'builder',
						'description' => 'Зона для нижних CTA, FAQ и завершающих секций страницы.'
					]
				]
			],
			'content_category_generic' => [
				'key'                  => 'content_category_generic',
				'title'                => 'Страница категории контента',
				'description'          => 'Builder добавляет секции вокруг системной страницы категории InstantCMS.',
				'shell'                => 'overlay',
				'default_zone'         => 'before_content',
				'native_content_label' => 'Здесь продолжает работать системная страница категории InstantCMS.',
				'zones'                => [
					[
						'key'         => 'before_content',
						'slot_key'    => 'before_content',
						'title'       => 'Над основным списком',
						'kind'        => 'builder',
						'description' => 'Подходит для шапки категории, фильтров и промо-блоков.'
					],
					[
						'key'         => 'content_body',
						'slot_key'    => 'content_body',
						'title'       => 'Системное содержимое страницы',
						'kind'        => 'native',
						'description' => 'Эта зона занимает slot content_body и остается под управлением стандартной страницы InstantCMS.'
					],
					[
						'key'         => 'content_sidebar_right',
						'slot_key'    => 'content_sidebar_right',
						'title'       => 'Боковая колонка',
						'kind'        => 'builder',
						'description' => 'Сюда удобно выводить дополнительные виджеты и короткие блоки рядом с основным content_body.'
					],
					[
						'key'         => 'after_content',
						'slot_key'    => 'after_content',
						'title'       => 'Под основным списком',
						'kind'        => 'builder',
						'description' => 'Нижняя зона для CTA, подборок и связанных блоков.'
					]
				]
			],
			'user_profile' => [
				'key'                  => 'user_profile',
				'title'                => 'Профиль пользователя',
				'description'          => 'Builder встраивает дополнительные секции вокруг стандартного профиля пользователя.',
				'shell'                => 'overlay',
				'default_zone'         => 'hero',
				'native_content_label' => 'Здесь остается стандартный профиль пользователя InstantCMS.',
				'zones'                => [
					[
						'key'         => 'hero',
						'slot_key'    => 'hero',
						'title'       => 'Верхняя зона профиля',
						'kind'        => 'builder',
						'description' => 'Подходит для обложки, приветственного блока или важного акцента.'
					],
					[
						'key'         => 'content_body',
						'slot_key'    => 'content_body',
						'title'       => 'Системное содержимое профиля',
						'kind'        => 'native',
						'description' => 'Эта зона занимает slot content_body и остается под управлением штатного профиля InstantCMS.'
					],
					[
						'key'         => 'after_content',
						'slot_key'    => 'after_content',
						'title'       => 'Под профилем',
						'kind'        => 'builder',
						'description' => 'Зона для дополнительных карточек, CTA и связанных блоков.'
					]
				]
			]
		];
	}

	protected function normalizePage(array $item) {

		$item['key'] = $item['name'];
		$item['mode'] = $item['page_mode'];
		$item['schema'] = $this->decodeSchema(isset($item['schema_json']) ? $item['schema_json'] : '', $item['name']);
		$item['adapter_key'] = $this->resolveAdapterKey($item);
		$item['page_type'] = $this->resolvePageType($item);
		$item['widget_nodes'] = [];

		return $item;
	}

	protected function getAdapterDefinition($adapter_key) {

		$definitions = $this->getAdapterDefinitions();

		if (!isset($definitions[$adapter_key])) {
			return $definitions['standalone_landing'];
		}

		return $definitions[$adapter_key];
	}

	protected function resolveContentCategoryPageKey(array $ctype, array $category = []) {

		if (empty($category['id'])) {
			return '';
		}

		$ctype_name = (string) ($ctype['name'] ?? '');

		$page_key_map = [
			'board' => 'ads-category',
			'ads'   => 'ads-category'
		];

		return $page_key_map[$ctype_name] ?? '';
	}

	protected function getOverlayIntegrationByPageKey($page_key, $is_admin = false) {

		$page_key = (string) $page_key;
		if (!$page_key) {
			return false;
		}

		$page = $this->getPageByKey($page_key);
		if (!$page || !$this->canRenderOverlayPage($page, $is_admin)) {
			return false;
		}

		$runtime = $this->getRuntimePage($page);

		return [
			'page_key' => $page_key,
			'page'     => $page,
			'runtime'  => $runtime,
			'zones'    => $this->indexRuntimeZones($runtime['zones'] ?? [])
		];
	}

	protected function canRenderOverlayPage(array $page, $is_admin = false) {

		$status = $page['status'] ?? 'draft';

		if ($status === 'published') {
			return true;
		}

		return (bool) $is_admin;
	}

	protected function indexRuntimeZones(array $zones) {

		$indexed = [];

		foreach ($zones as $zone) {
			if (empty($zone['key'])) {
				continue;
			}

			$indexed[$zone['key']] = $zone;
		}

		return $indexed;
	}

	protected function resolveAdapterKey(array $page) {

		if (!empty($page['schema']['adapter_key']) && isset($this->getAdapterDefinitions()[$page['schema']['adapter_key']])) {
			return $page['schema']['adapter_key'];
		}

		$page_key = $page['key'] ?? ($page['name'] ?? '');
		$page_mode = $page['mode'] ?? ($page['page_mode'] ?? 'full_takeover');

		if ($page_key === 'profile-cover' || $page_mode === 'zone_injection') {
			return 'user_profile';
		}

		if ($page_key === 'ads-category' || $page_mode === 'hybrid_overlay') {
			return 'content_category_generic';
		}

		return 'standalone_landing';
	}

	protected function resolvePageType(array $page) {

		$page_mode = $page['mode'] ?? ($page['page_mode'] ?? 'full_takeover');

		if ($page_mode === 'full_takeover') {
			return 'standalone';
		}

		return 'system_overlay';
	}

	protected function enrichSchemaForRuntime(array $page) {

		$schema = $page['schema'];
		$widget_map = $this->getPageWidgetMap($page);
		$default_zone_key = $this->getDefaultZoneKey($page);

		foreach ($schema['sections'] as $section_index => $section) {
			$zone_key = !empty($section['zone_key'])
				? $section['zone_key']
				: (!empty($section['settings']['zone_key']) ? $section['settings']['zone_key'] : $default_zone_key);

			$schema['sections'][$section_index]['zone_key'] = $this->normalizeRuntimeZoneKey($zone_key);
			$schema['sections'][$section_index]['slot_key'] = $schema['sections'][$section_index]['zone_key'];

			foreach ($schema['sections'][$section_index]['columns'] as $column_index => $column) {
				foreach ($schema['sections'][$section_index]['columns'][$column_index]['nodes'] as $node_index => $node) {

					if (($node['type'] ?? '') === 'block' && empty($node['source_key'])) {
						$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['source_key'] = $node['label'];
					}

					if (($node['type'] ?? '') !== 'system_widget') {
						continue;
					}

					$widget_data = $widget_map[$node['uid']] ?? [];

					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['widget_id'] = isset($node['widget_id'])
						? (int) $node['widget_id']
						: (int) ($widget_data['widget_id'] ?? 0);
					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['widget_name'] = !empty($node['widget_name'])
						? $node['widget_name']
						: ($widget_data['widget_name'] ?? '');
					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['widget_controller'] = !empty($node['widget_controller'])
						? $node['widget_controller']
						: ($widget_data['widget_controller'] ?? '');
					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['options'] = !empty($node['options']) && is_array($node['options'])
						? $node['options']
						: ($widget_data['options'] ?? []);

					if (!empty($widget_data['widget_title']) && empty($schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['label'])) {
						$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['label'] = $widget_data['widget_title'];
					}
				}
			}
		}

		return $schema;
	}

	protected function buildRuntimeZones(array $page, array $adapter, array $shell = []) {

		$zones = [];

		foreach ($adapter['zones'] as $zone) {
			$zone['key'] = $this->normalizeRuntimeZoneKey($zone['key']);
			$zone['slot_key'] = $this->normalizeRuntimeZoneKey($zone['slot_key'] ?? $zone['key']);

			if (($zone['kind'] ?? 'builder') === 'builder' && !$this->isRuntimeBuilderSlotEnabled($shell, $zone['slot_key'])) {
				continue;
			}

			$zone['sections'] = [];
			$zones[$zone['key']] = $zone;
		}

		foreach ($page['schema']['sections'] as $section) {
			$zone_key = !empty($section['zone_key']) ? $section['zone_key'] : $adapter['default_zone'];
			$zone_key = $this->normalizeRuntimeZoneKey($zone_key);

			if (!$this->isRuntimeBuilderSlotEnabled($shell, $zone_key)) {
				continue;
			}

			if (!isset($zones[$zone_key])) {
				$zones[$zone_key] = [
					'key'         => $zone_key,
					'slot_key'    => $zone_key,
					'title'       => $zone_key,
					'kind'        => 'builder',
					'description' => '',
					'sections'    => []
				];
			}

			$zones[$zone_key]['sections'][] = $section;
		}

		return array_values($zones);
	}

	protected function buildRuntimeShell(array $page, array $adapter) {

		$layout = isset($page['schema']['layout']) && is_array($page['schema']['layout']) ? $page['schema']['layout'] : [];
		$slot_positions = $this->getNordicShellSlotPositions();
		$shell_variant = $this->resolveRuntimeShellVariant($page, $adapter);
		$active_slots = $this->normalizeShellSlots($shell_variant['active_slots']);
		$builder_slots = $active_slots;
		$homepage_shell_mode = $shell_variant['homepage_shell_mode'];

		if (($page['key'] ?? $page['name'] ?? '') === 'homepage') {
			if ($homepage_shell_mode === 'page_hero') {
				$active_slots = array_values(array_diff($active_slots, ['hero']));
				$builder_slots = $this->normalizeShellSlots(array_merge($active_slots, ['hero']));
			} elseif ($homepage_shell_mode === 'mixed') {
				$active_slots = $this->normalizeShellSlots(array_merge($active_slots, ['hero']));
				$builder_slots = $active_slots;
			}
		}
		$content_slot = $this->normalizeRuntimeZoneKey($layout['content_slot'] ?? 'content_body');

		if (!in_array($content_slot, $builder_slots, true)) {
			$content_slot = 'content_body';
		}

		$slot_state = [];

		foreach ($this->getDefaultShellSlots() as $slot_name) {
			$slot_state[$slot_name] = in_array($slot_name, $active_slots, true);
		}

		return [
			'template'     => !empty($page['template']) ? $page['template'] : 'nordic',
			'name'         => $adapter['shell'] ?? 'nordic',
			'scheme'       => !empty($layout['scheme']) ? (string) $layout['scheme'] : $this->getNordicShellSchemeKey(),
			'content_slot' => $content_slot,
			'slots'        => $builder_slots,
			'active_slots' => $active_slots,
			'builder_slots'=> $builder_slots,
			'available_slots' => $builder_slots,
			'slot_state'   => $slot_state,
			'slot_positions' => $slot_positions,
			'variant_key'  => $shell_variant['key'],
			'variant_title'=> $shell_variant['title'],
			'variant_scope'=> $shell_variant['scope'],
			'assignment_source' => $shell_variant['assignment_source'],
			'body_layout'  => $shell_variant['body_layout'],
			'chrome'       => [
				'header_variant'      => $shell_variant['header_variant'],
				'footer_variant'      => $shell_variant['footer_variant'],
				'menu_placement'      => $shell_variant['menu_placement'],
				'sticky_header'       => $shell_variant['sticky_header'],
				'mobile_menu_mode'    => $shell_variant['mobile_menu_mode'],
				'homepage_shell_mode' => $shell_variant['homepage_shell_mode']
			],
			'body_classes' => $this->getRuntimeShellBodyClasses($shell_variant)
		];
	}

	protected function buildRuntimeSlotMap(array $shell, array $zones, array $adapter) {

		$slot_titles = $this->getNordicShellSlotTitleMap();
		$zones_by_slot = $this->indexRuntimeZonesBySlot($zones);
		$slot_map = [];

		foreach ($this->getDefaultShellSlots() as $slot_key) {
			$zone = $zones_by_slot[$slot_key] ?? null;
			$render_mode = 'widgets';

			if ($zone) {
				$render_mode = ($zone['kind'] ?? 'builder') === 'native' ? 'native' : 'builder';
			}

			$slot_map[$slot_key] = [
				'key'            => $slot_key,
				'title'          => $slot_titles[$slot_key] ?? $slot_key,
				'active'         => $this->isRuntimeBuilderSlotEnabled($shell, $slot_key),
				'shell_active'   => $this->isRuntimeShellSlotEnabled($shell, $slot_key),
				'is_content_slot'=> ($shell['content_slot'] ?? 'content_body') === $slot_key,
				'position_keys'  => $shell['slot_positions'][$slot_key] ?? [$slot_key],
				'render_mode'    => $render_mode,
				'zone_key'       => $zone['key'] ?? null,
				'zone_kind'      => $zone['kind'] ?? null,
				'section_count'  => !empty($zone['sections']) ? count($zone['sections']) : 0,
				'native_label'   => !empty($zone) && ($zone['kind'] ?? '') === 'native' ? ($adapter['native_content_label'] ?? '') : ''
			];
		}

		return $slot_map;
	}

	protected function resolveRuntimeShellVariant(array $page, array $adapter) {

		$variant_key = $this->resolveRuntimeShellVariantKey($page, $adapter);
		$variant = $this->getShellVariantByKey($variant_key);

		if (!$variant) {
			$variant = $this->normalizeShellVariant('site-default', $this->getDefaultShellVariants()['site-default'] ?? []);
		}

		$variant['assignment_source'] = $this->resolveRuntimeShellAssignmentSource($page, $adapter, $variant['key']);

		return $variant;
	}

	protected function resolveRuntimeShellVariantKey(array $page, array $adapter) {

		$layout = isset($page['schema']['layout']) && is_array($page['schema']['layout']) ? $page['schema']['layout'] : [];
		$explicit_variant = $this->sanitizeShellVariantKey($layout['shell_variant'] ?? ($page['schema']['shell_variant'] ?? ''));

		if ($explicit_variant !== '' && $this->getShellVariantByKey($explicit_variant)) {
			return $explicit_variant;
		}

		$page_key = (string) ($page['key'] ?? $page['name'] ?? '');
		if ($page_key === 'homepage') {
			return 'homepage';
		}

		$adapter_key = (string) ($adapter['key'] ?? '');
		if ($adapter_key === 'content_category_generic') {
			return 'category-pages';
		}

		if ($adapter_key === 'user_profile') {
			return 'profile-pages';
		}

		if (($page['page_mode'] ?? $page['mode'] ?? '') === 'full_takeover') {
			return 'landing-pages';
		}

		return 'site-default';
	}

	protected function resolveRuntimeShellAssignmentSource(array $page, array $adapter, $variant_key) {

		$layout = isset($page['schema']['layout']) && is_array($page['schema']['layout']) ? $page['schema']['layout'] : [];
		$explicit_variant = $this->sanitizeShellVariantKey($layout['shell_variant'] ?? ($page['schema']['shell_variant'] ?? ''));

		if ($explicit_variant !== '' && $explicit_variant === $variant_key) {
			return 'page-layout';
		}

		$page_key = (string) ($page['key'] ?? $page['name'] ?? '');
		if ($page_key === 'homepage') {
			return 'page-key';
		}

		$adapter_key = (string) ($adapter['key'] ?? '');
		if (in_array($adapter_key, ['content_category_generic', 'user_profile'], true)) {
			return 'adapter';
		}

		if (($page['page_mode'] ?? $page['mode'] ?? '') === 'full_takeover') {
			return 'page-mode';
		}

		return 'default';
	}

	protected function getShellAssignmentSourceTitles() {
		return [
			'page-layout' => 'Переопределение страницы',
			'page-key'    => 'Системный ключ страницы',
			'adapter'     => 'Adapter страницы',
			'page-mode'   => 'Режим участия страницы',
			'default'     => 'Базовое правило'
		];
	}

	protected function isRuntimeShellSlotEnabled(array $shell, $slot_key) {

		$slot_key = $this->normalizeRuntimeZoneKey($slot_key);

		if (!$shell || empty($shell['active_slots']) || !is_array($shell['active_slots'])) {
			return true;
		}

		return in_array($slot_key, $shell['active_slots'], true);
	}

	protected function isRuntimeBuilderSlotEnabled(array $shell, $slot_key) {

		$slot_key = $this->normalizeRuntimeZoneKey($slot_key);

		if (!$shell) {
			return true;
		}

		if (!empty($shell['builder_slots']) && is_array($shell['builder_slots'])) {
			return in_array($slot_key, $shell['builder_slots'], true);
		}

		return $this->isRuntimeShellSlotEnabled($shell, $slot_key);
	}

	protected function applyHomepageShellModeToPage(array $page, array $shell) {

		if (($page['key'] ?? $page['name'] ?? '') !== 'homepage') {
			return $page;
		}

		$mode = (string) ($shell['chrome']['homepage_shell_mode'] ?? 'inherit');
		if (!in_array($mode, ['page_hero', 'mixed'], true)) {
			return $page;
		}

		if (empty($page['schema']['sections']) || !is_array($page['schema']['sections'])) {
			return $page;
		}

		$first_section = $page['schema']['sections'][0] ?? null;
		if (!is_array($first_section)) {
			return $page;
		}

		$current_zone = $this->normalizeRuntimeZoneKey($first_section['zone_key'] ?? ($first_section['settings']['zone_key'] ?? ''));
		if ($current_zone !== '' && $current_zone !== 'content_body') {
			return $page;
		}

		$page['schema']['sections'][0]['zone_key'] = 'hero';
		$page['schema']['sections'][0]['slot_key'] = 'hero';

		if (empty($page['schema']['sections'][0]['settings']) || !is_array($page['schema']['sections'][0]['settings'])) {
			$page['schema']['sections'][0]['settings'] = [];
		}

		$page['schema']['sections'][0]['settings']['zone_key'] = 'hero';

		return $page;
	}

	protected function getRuntimeShellBodyClasses(array $variant) {
		return [
			'lb-shell-variant-' . $variant['key'],
			'lb-shell-scope-' . $variant['scope'],
			'lb-shell-layout-' . $variant['body_layout'],
			'lb-header-' . $variant['header_variant'],
			'lb-footer-' . $variant['footer_variant'],
			'lb-menu-placement-' . $variant['menu_placement'],
			'lb-homepage-shell-' . $variant['homepage_shell_mode'],
			'lb-mobile-menu-' . $variant['mobile_menu_mode'],
			'lb-sticky-header-' . $variant['sticky_header']
		];
	}

	protected function indexRuntimeZonesBySlot(array $zones) {

		$indexed = [];

		foreach ($zones as $zone) {
			$slot_key = $this->normalizeRuntimeZoneKey($zone['slot_key'] ?? ($zone['key'] ?? ''));

			if (!$slot_key || isset($indexed[$slot_key])) {
				continue;
			}

			$indexed[$slot_key] = $zone;
		}

		return $indexed;
	}

	protected function getDefaultZoneKey(array $page) {

		$adapter_key = $this->resolveAdapterKey($page);
		$adapter = $this->getAdapterDefinition($adapter_key);

		if ($adapter_key === 'standalone_landing' && !empty($page['schema']['layout']['content_slot'])) {
			return $this->normalizeRuntimeZoneKey($page['schema']['layout']['content_slot']);
		}

		return $this->normalizeRuntimeZoneKey($adapter['default_zone']);
	}

	protected function getPageWidgetMap(array $page) {

		$widget_map = [];

		foreach ($page['widget_nodes'] as $widget_node) {
			if (empty($widget_node['node_uid'])) {
				continue;
			}

			$widget_map[$widget_node['node_uid']] = $widget_node;
		}

		return $widget_map;
	}

	protected function getDefaultPages() {
		return [
			$this->buildDefaultPage('homepage', 'Главная страница', 'full_takeover', 'draft'),
			$this->buildDefaultPage('ads-category', 'Категория Объявлений', 'hybrid_overlay', 'prototype'),
			$this->buildDefaultPage('profile-cover', 'Профиль пользователя', 'zone_injection', 'idea')
		];
	}

	protected function getDefaultPageByKey($page_key) {

		foreach ($this->getDefaultPages() as $page) {
			if ($page['key'] === $page_key) {
				return $page;
			}
		}

		return false;
	}

	protected function buildDefaultPage($key, $title, $mode, $status) {
		return [
			'id'                => 0,
			'key'               => $key,
			'name'              => $key,
			'title'             => $title,
			'status'            => $status,
			'mode'              => $mode,
			'page_mode'         => $mode,
			'template'          => 'nordic',
			'updated_at'        => date('Y-m-d H:i:s'),
			'schema'            => $this->getDefaultSchema($key),
			'widget_nodes'      => $this->extractSystemWidgetNodes($this->getDefaultSchema($key))
		];
	}

	protected function getDefaultSchema($page_key) {

		$schemas = [
			'homepage' => [
				'sections' => [
					[
						'uid'     => 'section-hero',
						'title'   => 'Первый экран',
						'layout'  => '2col_equal',
						'columns' => [
							[
								'uid'   => 'column-hero-main',
								'title' => 'Колонка 1',
								'nodes' => [
									['uid' => 'node-hero-heading', 'type' => 'block', 'label' => 'core.hero-heading'],
									['uid' => 'node-hero-actions', 'type' => 'block', 'label' => 'core.hero-actions']
								]
							],
							[
								'uid'   => 'column-hero-side',
								'title' => 'Колонка 2',
								'nodes' => [
									['uid' => 'node-widget-auth-register', 'type' => 'system_widget', 'label' => 'auth.register']
								]
							]
						]
					],
					[
						'uid'     => 'section-grid',
						'title'   => 'Сетка контента',
						'layout'  => '3col_equal',
						'columns' => [
							[
								'uid'   => 'column-grid-1',
								'title' => 'Колонка 1',
								'nodes' => [
									['uid' => 'node-cards-grid', 'type' => 'block', 'label' => 'core.cards-grid']
								]
							],
							[
								'uid'   => 'column-grid-2',
								'title' => 'Колонка 2',
								'nodes' => [
									['uid' => 'node-feature-list', 'type' => 'block', 'label' => 'core.feature-list']
								]
							],
							[
								'uid'   => 'column-grid-3',
								'title' => 'Колонка 3',
								'nodes' => [
									['uid' => 'node-widget-content-list', 'type' => 'system_widget', 'label' => 'content.list']
								]
							]
						]
					]
				]
			],
			'ads-category' => [
				'sections' => [
					[
						'uid'     => 'section-ads-header',
						'title'   => 'Шапка категории',
						'layout'  => '2col_sidebar_right',
						'columns' => [
							[
								'uid'   => 'column-ads-main',
								'title' => 'Основная колонка',
								'nodes' => [
									['uid' => 'node-ads-breadcrumbs', 'type' => 'block', 'label' => 'ads.category-header'],
									['uid' => 'node-ads-filters', 'type' => 'block', 'label' => 'ads.filter-bar']
								]
							],
							[
								'uid'   => 'column-ads-side',
								'title' => 'Сайдбар',
								'nodes' => [
									['uid' => 'node-widget-tags-cloud', 'type' => 'system_widget', 'label' => 'tags.cloud']
								]
							]
						]
					]
				]
			],
			'profile-cover' => [
				'sections' => [
					[
						'uid'     => 'section-profile-cover',
						'title'   => 'Обложка профиля',
						'layout'  => '1col',
						'columns' => [
							[
								'uid'   => 'column-profile-cover',
								'title' => 'Колонка 1',
								'nodes' => [
									['uid' => 'node-profile-cover', 'type' => 'block', 'label' => 'profile.cover-hero'],
									['uid' => 'node-profile-stats', 'type' => 'block', 'label' => 'profile.quick-stats']
								]
							]
						]
					]
				]
			]
		];

		return $schemas[$page_key] ?? ['sections' => []];
	}

	protected function decodeSchema($schema_json, $page_key) {

		if ($schema_json) {
			$schema = json_decode($schema_json, true);
			if (is_array($schema)) {
				return $this->normalizeSchema($schema, $page_key);
			}
		}

		return $this->normalizeSchema($this->getDefaultSchema($page_key), $page_key);
	}

	protected function normalizeSchema(array $schema, $page_key) {

		$schema['schema_version'] = !empty($schema['schema_version']) ? (string) $schema['schema_version'] : '1.0';
		$schema['theme'] = isset($schema['theme']) && is_array($schema['theme']) ? array_merge($this->getDefaultThemeState(), $schema['theme']) : $this->getDefaultThemeState();
		$schema['layout'] = isset($schema['layout']) && is_array($schema['layout']) ? array_merge([
			'template'     => 'nordic',
			'scheme'       => $this->getNordicShellSchemeKey(),
			'width_mode'   => 'contained',
			'header_mode'  => 'theme',
			'footer_mode'  => 'theme',
			'shell_variant'=> '',
			'content_slot' => 'content_body'
		], $schema['layout']) : [
			'template'     => 'nordic',
			'scheme'       => $this->getNordicShellSchemeKey(),
			'width_mode'   => 'contained',
			'header_mode'  => 'theme',
			'footer_mode'  => 'theme',
			'shell_variant'=> '',
			'content_slot' => 'content_body'
		];
		$schema['layout']['scheme'] = !empty($schema['layout']['scheme']) ? (string) $schema['layout']['scheme'] : $this->getNordicShellSchemeKey();
		$schema['layout']['shell_variant'] = $this->sanitizeShellVariantKey($schema['layout']['shell_variant']);
		$schema['layout']['content_slot'] = $this->normalizeRuntimeZoneKey($schema['layout']['content_slot']);
		$schema['shell_slots'] = $this->normalizeShellSlots(isset($schema['shell_slots']) && is_array($schema['shell_slots']) ? $schema['shell_slots'] : []);

		$schema['sections'] = isset($schema['sections']) && is_array($schema['sections']) ? array_values($schema['sections']) : [];

		foreach ($schema['sections'] as $section_index => $section) {
			$schema['sections'][$section_index]['uid'] = !empty($section['uid']) ? $section['uid'] : 'section-' . ($section_index + 1);
			$schema['sections'][$section_index]['title'] = !empty($section['title']) ? $section['title'] : 'Секция ' . ($section_index + 1);
			$schema['sections'][$section_index]['layout'] = !empty($section['layout']) ? $section['layout'] : '1col';
			$schema['sections'][$section_index]['section_type'] = !empty($section['section_type']) ? $section['section_type'] : 'content';
			$schema['sections'][$section_index]['style_preset'] = !empty($section['style_preset']) ? $section['style_preset'] : (!empty($section['settings']['style_preset']) ? $section['settings']['style_preset'] : 'content');
			$schema['sections'][$section_index]['container_preset'] = !empty($section['container_preset']) ? $section['container_preset'] : (!empty($section['settings']['container_preset']) ? $section['settings']['container_preset'] : 'standard');
			$schema['sections'][$section_index]['spacing_preset'] = !empty($section['spacing_preset']) ? $section['spacing_preset'] : (!empty($section['settings']['spacing_preset']) ? $section['settings']['spacing_preset'] : 'md');
			$schema['sections'][$section_index]['background_tone'] = !empty($section['background_tone']) ? $section['background_tone'] : (!empty($section['settings']['background_tone']) ? $section['settings']['background_tone'] : 'base');
			$schema['sections'][$section_index]['visibility'] = isset($section['visibility']) && is_array($section['visibility']) ? array_merge($this->getDefaultVisibility(), $section['visibility']) : $this->getDefaultVisibility();
			$schema['sections'][$section_index]['settings'] = isset($section['settings']) && is_array($section['settings']) ? array_merge([
				'style_preset'     => 'content',
				'container_preset' => 'standard',
				'spacing_preset'   => 'md',
				'background_tone'  => 'base',
				'gap_preset'       => 'md',
				'background_class' => '',
				'padding'          => 'md',
				'css_class'        => ''
			], $section['settings']) : [
				'style_preset'     => 'content',
				'container_preset' => 'standard',
				'spacing_preset'   => 'md',
				'background_tone'  => 'base',
				'gap_preset'       => 'md',
				'background_class' => '',
				'padding'          => 'md',
				'css_class'        => ''
			];
			$schema['sections'][$section_index]['settings']['style_preset'] = $schema['sections'][$section_index]['style_preset'];
			$schema['sections'][$section_index]['settings']['container_preset'] = $schema['sections'][$section_index]['container_preset'];
			$schema['sections'][$section_index]['settings']['spacing_preset'] = $schema['sections'][$section_index]['spacing_preset'];
			$schema['sections'][$section_index]['settings']['background_tone'] = $schema['sections'][$section_index]['background_tone'];
			$schema['sections'][$section_index]['columns'] = isset($section['columns']) && is_array($section['columns']) ? array_values($section['columns']) : [];

			foreach ($schema['sections'][$section_index]['columns'] as $column_index => $column) {
				$schema['sections'][$section_index]['columns'][$column_index]['uid'] = !empty($column['uid']) ? $column['uid'] : $schema['sections'][$section_index]['uid'] . '-column-' . ($column_index + 1);
				$schema['sections'][$section_index]['columns'][$column_index]['title'] = !empty($column['title']) ? $column['title'] : 'Колонка ' . ($column_index + 1);
				$schema['sections'][$section_index]['columns'][$column_index]['visibility'] = isset($column['visibility']) && is_array($column['visibility']) ? array_merge($this->getDefaultVisibility(), $column['visibility']) : $this->getDefaultVisibility();
				$schema['sections'][$section_index]['columns'][$column_index]['width'] = isset($column['width']) && is_array($column['width']) ? array_merge($this->getDefaultColumnWidths(), $column['width']) : $this->getDefaultColumnWidths();
				$schema['sections'][$section_index]['columns'][$column_index]['settings'] = isset($column['settings']) && is_array($column['settings']) ? array_merge([
					'align'     => 'stretch',
					'css_class' => ''
				], $column['settings']) : [
					'align'     => 'stretch',
					'css_class' => ''
				];
				$schema['sections'][$section_index]['columns'][$column_index]['nodes'] = isset($column['nodes']) && is_array($column['nodes']) ? array_values($column['nodes']) : [];

				foreach ($schema['sections'][$section_index]['columns'][$column_index]['nodes'] as $node_index => $node) {
					if (empty($node['uid'])) {
						$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['uid'] = $schema['sections'][$section_index]['columns'][$column_index]['uid'] . '-node-' . ($node_index + 1);
					}
					if (empty($node['type'])) {
						$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['type'] = 'block';
					}
					if (empty($node['label'])) {
						$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['label'] = $page_key . '.node.' . ($node_index + 1);
					}
					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['class_name'] = isset($node['class_name']) ? $node['class_name'] : '';
					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['notes'] = isset($node['notes']) ? $node['notes'] : '';
					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['source_key'] = isset($node['source_key']) ? $node['source_key'] : '';
					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['style_variant'] = isset($node['style_variant']) ? $node['style_variant'] : 'default';
					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['device_visibility'] = isset($node['device_visibility']) && is_array($node['device_visibility']) ? array_merge($this->getDefaultVisibility(), $node['device_visibility']) : $this->getDefaultVisibility();
					$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]['options'] = isset($node['options']) && is_array($node['options']) ? $node['options'] : [];
				}
			}
		}

		return $schema;
	}

	protected function getDefaultThemeState() {
		return $this->getSiteThemeDefaults();
	}

	protected function getBaseThemeState() {
		return [
			'global_style_preset' => 'nordic_balanced',
			'color_preset'        => 'nordic_day',
			'typography_preset'   => 'editorial',
			'container_preset'    => 'standard',
			'button_preset'       => 'soft_accent',
			'card_preset'         => 'quiet',
			'section_spacing'     => 'comfortable'
		];
	}

	protected function normalizeThemeState(array $theme = []) {

		$defaults = $this->getBaseThemeState();
		$form_catalog = $this->getThemeFormCatalog();
		$normalized = [];

		foreach ($defaults as $key => $default_value) {
			$value = isset($theme[$key]) ? (string) $theme[$key] : $default_value;
			$normalized[$key] = array_key_exists($value, $form_catalog[$key] ?? []) ? $value : $default_value;
		}

		return $normalized;
	}

	protected function getDefaultShellVariants() {
		return [
			'site-default' => [
				'title'               => 'Базовый shell сайта',
				'description'         => 'Главный каркас сайта по умолчанию для большинства страниц.',
				'target_label'        => 'Все страницы по умолчанию',
				'scope'               => 'site',
				'is_system'           => 1,
				'header_variant'      => 'split_navigation',
				'footer_variant'      => 'columns_4',
				'menu_placement'      => 'header_primary',
				'show_site_top'       => 0,
				'show_hero'           => 0,
				'show_before_content' => 0,
				'show_after_content'  => 1,
				'body_layout'         => 'right_sidebar',
				'homepage_shell_mode' => 'inherit',
				'sticky_header'       => 'smart',
				'mobile_menu_mode'    => 'drawer'
			],
			'homepage' => [
				'title'               => 'Главная страница',
				'description'         => 'Отдельный shell-сценарий для главной страницы и её верхних зон.',
				'target_label'        => 'Маршрут / и стартовые страницы',
				'scope'               => 'homepage',
				'is_system'           => 1,
				'header_variant'      => 'centered_brand',
				'footer_variant'      => 'columns_4',
				'menu_placement'      => 'header_secondary',
				'show_site_top'       => 1,
				'show_hero'           => 1,
				'show_before_content' => 1,
				'show_after_content'  => 1,
				'body_layout'         => 'no_sidebars',
				'homepage_shell_mode' => 'shell_hero',
				'sticky_header'       => 'on',
				'mobile_menu_mode'    => 'drawer'
			],
			'content-pages' => [
				'title'               => 'Материалы и статьи',
				'description'         => 'Контентный shell для страниц со статьями и длинным текстом.',
				'target_label'        => 'Статьи, help-страницы, editorial content',
				'scope'               => 'content',
				'is_system'           => 1,
				'header_variant'      => 'classic',
				'footer_variant'      => 'editorial',
				'menu_placement'      => 'header_primary',
				'show_site_top'       => 0,
				'show_hero'           => 0,
				'show_before_content' => 0,
				'show_after_content'  => 1,
				'body_layout'         => 'left_sidebar',
				'homepage_shell_mode' => 'inherit',
				'sticky_header'       => 'smart',
				'mobile_menu_mode'    => 'inline_compact'
			],
			'category-pages' => [
				'title'               => 'Категории и каталоги',
				'description'         => 'Shell для каталогов, категорий и listing-страниц с фильтрами.',
				'target_label'        => 'Категории, каталоги, listing routes',
				'scope'               => 'category',
				'is_system'           => 1,
				'header_variant'      => 'split_navigation',
				'footer_variant'      => 'columns_3',
				'menu_placement'      => 'header_primary',
				'show_site_top'       => 1,
				'show_hero'           => 0,
				'show_before_content' => 1,
				'show_after_content'  => 0,
				'body_layout'         => 'right_sidebar',
				'homepage_shell_mode' => 'inherit',
				'sticky_header'       => 'smart',
				'mobile_menu_mode'    => 'drawer'
			],
			'profile-pages' => [
				'title'               => 'Профили',
				'description'         => 'Shell для profile view, cover-зон и быстрых пользовательских действий.',
				'target_label'        => 'Профили пользователей и компаний',
				'scope'               => 'profile',
				'is_system'           => 1,
				'header_variant'      => 'compact',
				'footer_variant'      => 'minimal',
				'menu_placement'      => 'site_top',
				'show_site_top'       => 1,
				'show_hero'           => 1,
				'show_before_content' => 0,
				'show_after_content'  => 0,
				'body_layout'         => 'no_sidebars',
				'homepage_shell_mode' => 'inherit',
				'sticky_header'       => 'on',
				'mobile_menu_mode'    => 'bottom_sheet'
			],
			'landing-pages' => [
				'title'               => 'Лендинги',
				'description'         => 'Упрощенный shell для страниц, которые полностью собираются в page builder.',
				'target_label'        => 'Landing pages и промо-страницы',
				'scope'               => 'landing',
				'is_system'           => 1,
				'header_variant'      => 'compact',
				'footer_variant'      => 'minimal',
				'menu_placement'      => 'header_primary',
				'show_site_top'       => 0,
				'show_hero'           => 0,
				'show_before_content' => 0,
				'show_after_content'  => 0,
				'body_layout'         => 'no_sidebars',
				'homepage_shell_mode' => 'page_hero',
				'sticky_header'       => 'off',
				'mobile_menu_mode'    => 'inline_compact'
			]
		];
	}

	protected function getStoredShellVariants() {

		$options = (array) cmsController::loadOptions('landingbuilder');
		$variants = $options['shell_variants'] ?? [];

		return is_array($variants) ? $variants : [];
	}

	protected function normalizeShellVariant($variant_key, array $data = []) {

		$catalog = $this->getShellVariantChoiceCatalog();
		$variant_key = $this->sanitizeShellVariantKey($variant_key);
		$variant = array_merge([
			'key'                 => $variant_key,
			'title'               => $variant_key !== '' ? $variant_key : 'shell-variant',
			'description'         => '',
			'target_label'        => '',
			'scope'               => 'custom',
			'is_system'           => 0,
			'header_variant'      => 'classic',
			'footer_variant'      => 'columns_4',
			'menu_placement'      => 'header_primary',
			'show_site_top'       => 0,
			'show_hero'           => 0,
			'show_before_content' => 0,
			'show_after_content'  => 0,
			'body_layout'         => 'no_sidebars',
			'homepage_shell_mode' => 'inherit',
			'sticky_header'       => 'off',
			'mobile_menu_mode'    => 'drawer'
		], $data);

		$variant['key'] = $variant_key;
		$variant['title'] = trim((string) $variant['title']);
		$variant['description'] = trim((string) $variant['description']);
		$variant['target_label'] = trim((string) $variant['target_label']);
		$variant['scope'] = trim((string) $variant['scope']);
		$variant['is_system'] = !empty($variant['is_system']) ? 1 : 0;
		$variant['show_site_top'] = !empty($variant['show_site_top']) ? 1 : 0;
		$variant['show_hero'] = !empty($variant['show_hero']) ? 1 : 0;
		$variant['show_before_content'] = !empty($variant['show_before_content']) ? 1 : 0;
		$variant['show_after_content'] = !empty($variant['show_after_content']) ? 1 : 0;
		$variant['header_variant'] = $this->normalizeShellChoice($variant['header_variant'], $catalog['header_variant'], 'classic');
		$variant['footer_variant'] = $this->normalizeShellChoice($variant['footer_variant'], $catalog['footer_variant'], 'columns_4');
		$variant['menu_placement'] = $this->normalizeShellChoice($variant['menu_placement'], $catalog['menu_placement'], 'header_primary');
		$variant['body_layout'] = $this->normalizeShellChoice($variant['body_layout'], $catalog['body_layout'], 'no_sidebars');
		$variant['homepage_shell_mode'] = $this->normalizeShellChoice($variant['homepage_shell_mode'], $catalog['homepage_shell_mode'], 'inherit');
		$variant['sticky_header'] = $this->normalizeShellChoice($variant['sticky_header'], $catalog['sticky_header'], 'off');
		$variant['mobile_menu_mode'] = $this->normalizeShellChoice($variant['mobile_menu_mode'], $catalog['mobile_menu_mode'], 'drawer');
		$variant['active_slots'] = $this->getShellVariantActiveSlots($variant);
		$variant['inactive_slots'] = array_values(array_diff($this->getDefaultShellSlots(), $variant['active_slots']));
		$variant['slot_titles'] = $this->getNordicShellSlotTitleMap();

		return $variant;
	}

	protected function extractShellVariantStorageData(array $variant) {
		return [
			'title'               => $variant['title'],
			'description'         => $variant['description'],
			'target_label'        => $variant['target_label'],
			'scope'               => $variant['scope'],
			'is_system'           => $variant['is_system'],
			'header_variant'      => $variant['header_variant'],
			'footer_variant'      => $variant['footer_variant'],
			'menu_placement'      => $variant['menu_placement'],
			'show_site_top'       => $variant['show_site_top'],
			'show_hero'           => $variant['show_hero'],
			'show_before_content' => $variant['show_before_content'],
			'show_after_content'  => $variant['show_after_content'],
			'body_layout'         => $variant['body_layout'],
			'homepage_shell_mode' => $variant['homepage_shell_mode'],
			'sticky_header'       => $variant['sticky_header'],
			'mobile_menu_mode'    => $variant['mobile_menu_mode']
		];
	}

	protected function getShellVariantActiveSlots(array $variant) {

		$slots = ['header_primary', 'content_body'];

		if (in_array($variant['header_variant'], ['split_navigation', 'centered_brand'], true)) {
			$slots[] = 'header_secondary';
		}

		if (!empty($variant['show_site_top'])) {
			$slots[] = 'site_top';
		}

		if ($variant['menu_placement'] === 'site_top') {
			$slots[] = 'site_top';
		}

		if ($variant['menu_placement'] === 'header_secondary') {
			$slots[] = 'header_secondary';
		}

		if (!empty($variant['show_hero'])) {
			$slots[] = 'hero';
		}

		if (!empty($variant['show_before_content'])) {
			$slots[] = 'before_content';
		}

		if (!empty($variant['show_after_content'])) {
			$slots[] = 'after_content';
		}

		if (in_array($variant['body_layout'], ['left_sidebar', 'two_sidebars'], true)) {
			$slots[] = 'content_sidebar_left';
		}

		if (in_array($variant['body_layout'], ['right_sidebar', 'two_sidebars'], true)) {
			$slots[] = 'content_sidebar_right';
		}

		if ($variant['footer_variant'] === 'minimal') {
			$slots[] = 'footer_secondary';
		} else {
			$slots[] = 'footer_primary';
			$slots[] = 'footer_secondary';
		}

		return $this->normalizeShellSlots($slots);
	}

	protected function getNordicShellSlotTitleMap() {

		$scheme = $this->getNordicShellScheme();
		$slot_titles = [];

		foreach (($scheme['layout_rows'] ?? []) as $row) {
			foreach (($row['cols'] ?? []) as $col) {
				if (empty($col['name'])) {
					continue;
				}

				$slot_titles[$col['name']] = (string) ($col['title'] ?? $col['name']);
			}
		}

		return $slot_titles;
	}

	protected function normalizeShellChoice($value, array $items, $default) {
		$value = trim((string) $value);
		return array_key_exists($value, $items) ? $value : $default;
	}

	protected function sanitizeShellVariantKey($variant_key) {
		$variant_key = mb_strtolower(trim((string) $variant_key));
		$variant_key = preg_replace('/[^a-z0-9_-]+/u', '-', $variant_key);
		return trim($variant_key, '-');
	}

	protected function getDefaultVisibility() {
		return [
			'desktop' => true,
			'tablet'  => true,
			'mobile'  => true
		];
	}

	protected function getDefaultColumnWidths() {
		return [
			'desktop' => 'auto',
			'tablet'  => 'auto',
			'mobile'  => 'auto'
		];
	}

	protected function getFallbackShellSlots() {
		return [
			'site_top',
			'header_primary',
			'header_secondary',
			'hero',
			'before_content',
			'content_body',
			'content_sidebar_left',
			'content_sidebar_right',
			'after_content',
			'footer_primary',
			'footer_secondary'
		];
	}

	protected function getNordicShellScheme() {

		static $scheme = null;

		if ($scheme !== null) {
			return $scheme;
		}

		$file = cmsConfig::get('root_path') . 'templates/nordic/shell_scheme.php';

		if (is_readable($file)) {
			$loaded = include $file;
			if (is_array($loaded)) {
				$scheme = $loaded;
				return $scheme;
			}
		}

		$scheme = [
			'key' => 'nordic_shell_v1',
			'slot_positions' => [],
			'reserved_positions' => array_merge($this->getFallbackShellSlots(), ['top', 'header', 'footer'])
		];

		foreach ($this->getFallbackShellSlots() as $slot) {
			$scheme['slot_positions'][$slot] = [$slot];
		}

		return $scheme;
	}

	protected function getNordicShellSchemeKey() {

		$scheme = $this->getNordicShellScheme();

		return !empty($scheme['key']) ? (string) $scheme['key'] : 'nordic_shell_v1';
	}

	protected function getNordicShellSlotPositions() {

		$scheme = $this->getNordicShellScheme();

		if (!empty($scheme['slot_positions']) && is_array($scheme['slot_positions'])) {
			return $scheme['slot_positions'];
		}

		$positions = [];

		foreach ($this->getFallbackShellSlots() as $slot) {
			$positions[$slot] = [$slot];
		}

		return $positions;
	}

	protected function getDefaultShellSlots() {
		return array_keys($this->getNordicShellSlotPositions());
	}

	protected function normalizeShellSlots(array $slots) {

		if (!$slots) {
			return $this->getDefaultShellSlots();
		}

		$normalized = [];

		foreach ($slots as $slot) {
			$slot = $this->normalizeRuntimeZoneKey($slot);
			if (!$slot || in_array($slot, $normalized, true)) {
				continue;
			}
			$normalized[] = $slot;
		}

		return $normalized ?: $this->getDefaultShellSlots();
	}

	protected function normalizeRuntimeZoneKey($zone_key) {

		$zone_key = trim((string) $zone_key);
		if ($zone_key === '') {
			return 'content_body';
		}

		$legacy_map = [
			'main'           => 'content_body',
			'native_content' => 'content_body',
			'sidebar'        => 'content_sidebar_right'
		];

		return $legacy_map[$zone_key] ?? $zone_key;
	}

	protected function getPageWidgetNodes($page_id, array $schema) {

		if (!$page_id || !$this->db->isTableExists(self::PAGE_WIDGET_TABLE)) {
			return $this->extractSystemWidgetNodes($schema);
		}

		$nodes = $this->filterEqual('page_id', $page_id)->orderBy('id', 'asc')->get(self::PAGE_WIDGET_TABLE, function ($item) {
			$item['options'] = $item['options'] ? json_decode($item['options'], true) : [];
			$item['device_visibility'] = $item['device_visibility'] ? json_decode($item['device_visibility'], true) : [];
			return $item;
		});

		return $nodes ? array_values($nodes) : $this->extractSystemWidgetNodes($schema);
	}

	protected function extractSystemWidgetNodes(array $schema) {

		$result = [];

		foreach ($schema['sections'] as $section) {
			foreach ($section['columns'] as $column) {
				foreach ($column['nodes'] as $node) {
					if (($node['type'] ?? '') !== 'system_widget') {
						continue;
					}

					$result[] = [
						'node_uid'           => $node['uid'],
						'widget_id'          => isset($node['widget_id']) ? (int) $node['widget_id'] : 0,
						'widget_name'        => isset($node['widget_name']) ? $node['widget_name'] : '',
						'widget_controller'  => isset($node['widget_controller']) ? $node['widget_controller'] : '',
						'widget_title'       => isset($node['label']) ? $node['label'] : '',
						'options'            => isset($node['options']) && is_array($node['options']) ? $node['options'] : [],
						'device_visibility'  => isset($node['device_visibility']) && is_array($node['device_visibility']) ? $node['device_visibility'] : []
					];
				}
			}
		}

		return $result;
	}

	protected function syncPageWidgetNodes($page_id, array $schema, $timestamp) {

		if (!$this->db->isTableExists(self::PAGE_WIDGET_TABLE)) {
			return true;
		}

		$nodes = $this->extractSystemWidgetNodes($schema);
		$active_uids = [];

		foreach ($nodes as $node) {

			$active_uids[] = $node['node_uid'];

			$widget = !empty($node['widget_id']) ? $this->getSystemWidgetById((int) $node['widget_id']) : false;
			$widget_name = $widget ? (string) ($widget['name'] ?? '') : (string) ($node['widget_name'] ?? '');
			$widget_controller = $widget ? ($widget['controller'] ?? null) : ($node['widget_controller'] ?? null);
			$widget_title = $widget ? (string) ($widget['title'] ?? '') : (string) ($node['widget_title'] ?? ($node['label'] ?? ''));

			if ($widget_controller === '') {
				$widget_controller = null;
			}

			$row = [
				'page_id'            => $page_id,
				'node_uid'           => $node['node_uid'],
				'widget_id'          => !empty($node['widget_id']) ? (int) $node['widget_id'] : 0,
				'widget_name'        => $widget_name,
				'widget_controller'  => $widget_controller,
				'widget_title'       => $widget_title,
				'options'            => $this->encodeJson($node['options']),
				'device_visibility'  => $this->encodeJson($node['device_visibility']),
				'updated_at'         => $timestamp
			];

			$existing = $this->getItemByField(self::PAGE_WIDGET_TABLE, 'node_uid', $node['node_uid']);

			if ($existing && (int) $existing['page_id'] === (int) $page_id) {
				$this->update(self::PAGE_WIDGET_TABLE, $existing['id'], $row);
			} else {
				$row['created_at'] = $timestamp;
				$this->insert(self::PAGE_WIDGET_TABLE, $row);
			}
		}

		$this->filterEqual('page_id', $page_id);

		if ($active_uids) {
			$this->filterNotIn('node_uid', $active_uids);
		}

		$this->deleteFiltered(self::PAGE_WIDGET_TABLE);

		return true;
	}

	protected function encodeJson(array $data) {
		return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	protected function sanitizePageKey($key) {
		$key = mb_strtolower(trim((string) $key));
		$key = preg_replace('/[^a-z0-9_-]+/u', '-', $key);
		return trim($key, '-');
	}
}