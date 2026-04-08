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

	public function migrateLegacyPageModesToInstantContentBody($user_id = 0) {

		static $is_running = false;

		$result = [
			'checked' => 0,
			'updated' => 0
		];

		if ($is_running || !$this->hasInstalledSchema()) {
			return $result;
		}

		$is_running = true;
		$rows = $this->get(self::PAGE_TABLE);

		if (!$rows) {
			$is_running = false;
			return $result;
		}

		foreach ($rows as $row) {
			$result['checked']++;

			$page_id = (int) ($row['id'] ?? 0);
			$page_key = (string) ($row['name'] ?? '');
			if (!$page_id || $page_key === '') {
				continue;
			}

			$current_mode = (string) ($row['page_mode'] ?? '');
			$schema = $this->decodeSchema((string) ($row['schema_json'] ?? ''), $page_key);

			if (!isset($schema['layout']) || !is_array($schema['layout'])) {
				$schema['layout'] = [];
			}

			$needs_schema_update = (($schema['layout']['page_mode'] ?? '') !== 'instant_content_body');

			if (empty($schema['layout']['content_slot'])) {
				$schema['layout']['content_slot'] = 'content_body';
				$needs_schema_update = true;
			}

			if ($needs_schema_update) {
				$schema['layout']['page_mode'] = 'instant_content_body';
			}

			$needs_mode_update = ($current_mode !== 'instant_content_body');

			if (!$needs_schema_update && !$needs_mode_update) {
				continue;
			}

			if ($needs_schema_update) {
				$saved = $this->savePageSchema($page_key, $schema, $user_id, 'Системная миграция в instant_content_body');
				if (!$saved) {
					continue;
				}
			}

			if ($needs_mode_update) {
				$updated = $this->update(self::PAGE_TABLE, $page_id, [
					'page_mode'  => 'instant_content_body',
					'updated_at' => date('Y-m-d H:i:s')
				]);

				if (!$updated) {
					continue;
				}
			}

			$result['updated']++;
		}

		$is_running = false;

		return $result;
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

		$resolved_page_key = $this->resolveOverlayPageKeyFromBindings('user_profile', [
			'overlay'  => 'user_profile',
			'user_id'  => (string) ($profile['id'] ?? ''),
			'group_id' => (string) ($profile['group_id'] ?? ''),
		], 'profile-cover');

		return $this->getOverlayIntegrationByPageKey($resolved_page_key, $is_admin);
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

		$page_mode = !empty($data['mode']) ? $data['mode'] : 'instant_content_body';
		$status = !empty($data['status']) ? $data['status'] : 'draft';
		$template = !empty($data['template']) ? $data['template'] : 'nordic';
		$schema = isset($data['schema']) && is_array($data['schema']) ? $data['schema'] : [];
		$adapter_key = trim((string) ($data['adapter_key'] ?? ''));
		$adapter_definitions = $this->getAdapterDefinitions();
		if (empty($schema['sections']) && empty($schema['zones'])) {
			$schema = $this->getStarterSchemaForNewPage($key, $title, $template, $page_mode);
		}
		if ($adapter_key !== '' && isset($adapter_definitions[$adapter_key])) {
			$schema['adapter_key'] = $adapter_key;
		}
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

	public function deletePageByKey($page_key) {

		if (!$this->hasInstalledSchema()) {
			return false;
		}

		$page_key = $this->sanitizePageKey($page_key);
		if (!$page_key) {
			return false;
		}

		if (!$this->db->isTableExists(self::PAGE_TABLE)) {
			return false;
		}

		$page = $this->getItemByField(self::PAGE_TABLE, 'name', $page_key);
		if (!$page) {
			return false;
		}

		$page_id = (int) ($page['id'] ?? 0);
		if (!$page_id) {
			return false;
		}

		if ($this->db->isTableExists(self::PAGE_WIDGET_TABLE)) {
			$this->filterEqual('page_id', $page_id);
			$this->deleteFiltered(self::PAGE_WIDGET_TABLE);
		}

		if ($this->db->isTableExists(self::VERSION_TABLE)) {
			$this->filterEqual('page_id', $page_id);
			$this->deleteFiltered(self::VERSION_TABLE);
		}

		return (bool) $this->delete(self::PAGE_TABLE, $page_id);
	}

	public function setPageStatusByKey($page_key, $status, $user_id = 0) {

		if (!$this->hasInstalledSchema()) {
			return false;
		}

		$page_key = $this->sanitizePageKey($page_key);
		if (!$page_key) {
			return false;
		}

		$status = (string) $status;
		$allowed_statuses = ['draft', 'prototype', 'idea', 'published'];
		if (!in_array($status, $allowed_statuses, true)) {
			return false;
		}

		$page = $this->getItemByField(self::PAGE_TABLE, 'name', $page_key);
		if (!$page) {
			return false;
		}

		$now = date('Y-m-d H:i:s');
		$updated = $this->update(self::PAGE_TABLE, $page['id'], [
			'status'     => $status,
			'updated_at' => $now
		]);

		if (!$updated) {
			return false;
		}

		return $this->getItemByField(self::PAGE_TABLE, 'name', $page_key, function ($item) {
			return $this->normalizePage($item);
		});
	}

	protected function getStarterSchemaForNewPage($page_key, $title = '', $template = 'nordic', $page_mode = 'instant_content_body') {

		$schema = ['sections' => []];
		$bridge_model = $this->getNordicbuilderBridgeModel();

		if ($bridge_model && method_exists($bridge_model, 'buildEmptyLandingbuilderSchema')) {
			$starter_schema = $bridge_model->buildEmptyLandingbuilderSchema($page_key, $title, $schema);

			if (is_array($starter_schema)) {
				$schema = $starter_schema;
			}
		} elseif ($bridge_model && method_exists($bridge_model, 'buildStarterLandingbuilderSchema')) {
			$starter_schema = $bridge_model->buildStarterLandingbuilderSchema($page_key, $title, $schema);

			if (is_array($starter_schema)) {
				$schema = $starter_schema;
			}
		}

		if (!isset($schema['layout']) || !is_array($schema['layout'])) {
			$schema['layout'] = [];
		}

		$schema['layout']['template'] = $template ?: 'nordic';
		if (empty($schema['layout']['page_mode'])) {
			$schema['layout']['page_mode'] = $page_mode ?: 'instant_content_body';
		}

		return $schema;
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
		$schema = $this->normalizeSchema($schema, $page_key);

		if (!isset($schema['layout']) || !is_array($schema['layout'])) {
			$schema['layout'] = [];
		}

		$schema['layout']['page_mode'] = 'instant_content_body';
		if (empty($schema['layout']['content_slot'])) {
			$schema['layout']['content_slot'] = 'content_body';
		}

		if ($bridge_model && $bridge_page) {
			$fallback_page = $legacy_page ?: $this->getDefaultPageByKey($page_key);
			$fallback_page['mode'] = 'instant_content_body';
			$fallback_page['page_mode'] = 'instant_content_body';
			$fallback_page['page_type'] = 'system_overlay';
			if (!empty($schema['layout']['template'])) {
				$fallback_page['template'] = (string) $schema['layout']['template'];
			}

			$allowed_statuses = ['draft', 'prototype', 'idea', 'published'];
			$status_candidates = [
				$bridge_page['status'] ?? null,
				$legacy_page['status'] ?? null,
				$fallback_page['status'] ?? null
			];
			$effective_status = 'draft';

			foreach ($status_candidates as $status_candidate) {
				$status_candidate = is_string($status_candidate) ? trim($status_candidate) : '';
				if (!in_array($status_candidate, $allowed_statuses, true)) {
					continue;
				}

				if ($status_candidate === 'published') {
					$effective_status = 'published';
					break;
				}

				if ($effective_status === 'draft') {
					$effective_status = $status_candidate;
				}
			}

			$result = $bridge_model->saveBridgePageSchema($page_key, $schema, $fallback_page ?: [], $user_id, $effective_status);

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

		$schema_json = $this->encodeJson($schema);
		$now = date('Y-m-d H:i:s');
		$template_name = !empty($schema['layout']['template']) ? (string) $schema['layout']['template'] : (!empty($page['template']) ? (string) $page['template'] : 'nordic');

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
			'template'            => $template_name,
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
			'block_catalog'       => array_values($this->getCanvasBlockCatalog()),
			'section_presets'     => $this->getCanvasSectionPresets(),
			'theme_defaults'      => $theme_defaults,
			'theme_option_catalog'=> $this->getCanvasThemeOptionCatalog(),
			'template_preset_catalog' => array_values($this->getTemplatePresetCatalog()),
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
			'section_zone_options'    => $this->getCanvasSectionZoneOptions($shell, $slot_map),
			'default_section_zone'    => $this->getDefaultZoneKey($page),
			'effective_variant'       => [
				'key'               => $shell['variant_key'] ?? 'site-default',
				'title'             => $shell['variant_title'] ?? 'Базовый shell',
				'assignment_source' => $shell['assignment_source'] ?? 'default',
				'body_layout'       => $shell['body_layout'] ?? 'no_sidebars',
				'body_columns'      => $shell['body_columns'] ?? [],
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

	protected function getCanvasSectionZoneOptions(array $shell, array $slot_map) {

		$allowed_slot_keys = ['hero', 'before_content', 'content_body', 'content_sidebar_left', 'content_sidebar_right', 'after_content'];
		$slot_titles = $this->getNordicShellSlotTitleMap();
		$options = [];

		foreach (($shell['available_slots'] ?? $shell['active_slots'] ?? []) as $slot_key) {
			if (!in_array($slot_key, $allowed_slot_keys, true)) {
				continue;
			}

			$slot = $slot_map[$slot_key] ?? [];
			if (($slot['render_mode'] ?? 'widgets') === 'native') {
				continue;
			}

			$options[] = [
				'value' => $slot_key,
				'title' => $slot_titles[$slot_key] ?? $slot_key,
				'hint'  => 'Секции конструктора будут рендериться в этой зоне shell.'
			];
		}

		if (!$options) {
			$options[] = [
				'value' => 'before_content',
				'title' => $slot_titles['before_content'] ?? 'before_content',
				'hint'  => 'Fallback зона для секций, если shell еще не активировал отдельные builder slots.'
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
		$template_preset_items = [];

		foreach ($this->getTemplatePresetCatalog() as $preset) {
			$template_preset_items[] = [
				'value'       => $preset['key'],
				'title'       => $preset['title'],
				'description' => $preset['description']
			];
		}

		return [
			'template_preset' => $template_preset_items,
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
			],
			'radius_preset' => [
				['value' => 'none', 'title' => 'Без скруглений', 'description' => 'Строгая геометрия с острыми углами.'],
				['value' => 'soft', 'title' => 'Мягкие углы', 'description' => 'Небольшое скругление для спокойного корпоративного стиля.'],
				['value' => 'rounded', 'title' => 'Выраженное скругление', 'description' => 'Более дружелюбный визуальный характер интерфейса.']
			],
			'density_preset' => [
				['value' => 'compact', 'title' => 'Плотная', 'description' => 'Меньше отступов и более собранная подача контента.'],
				['value' => 'balanced', 'title' => 'Сбалансированная', 'description' => 'Основной ритм по умолчанию для большинства страниц.'],
				['value' => 'relaxed', 'title' => 'Свободная', 'description' => 'Больше воздуха и увеличенные интервалы между блоками.']
			],
			'contrast_preset' => [
				['value' => 'soft', 'title' => 'Мягкий контраст', 'description' => 'Более деликатные границы и спокойные переходы.'],
				['value' => 'balanced', 'title' => 'Сбалансированный', 'description' => 'Нейтральный контраст для универсального сценария.'],
				['value' => 'strong', 'title' => 'Высокий контраст', 'description' => 'Более четкая визуальная иерархия и усиленные границы.']
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
			'template_preset'     => !empty($options['default_template_preset']) ? (string) $options['default_template_preset'] : '',
			'global_style_preset' => !empty($options['default_global_style_preset']) ? (string) $options['default_global_style_preset'] : '',
			'color_preset'        => !empty($options['default_color_preset']) ? (string) $options['default_color_preset'] : '',
			'typography_preset'   => !empty($options['default_typography_preset']) ? (string) $options['default_typography_preset'] : '',
			'container_preset'    => !empty($options['default_container_preset']) ? (string) $options['default_container_preset'] : '',
			'button_preset'       => !empty($options['default_button_preset']) ? (string) $options['default_button_preset'] : '',
			'card_preset'         => !empty($options['default_card_preset']) ? (string) $options['default_card_preset'] : '',
			'section_spacing'     => !empty($options['default_section_spacing']) ? (string) $options['default_section_spacing'] : '',
			'radius_preset'       => !empty($options['default_radius_preset']) ? (string) $options['default_radius_preset'] : '',
			'density_preset'      => !empty($options['default_density_preset']) ? (string) $options['default_density_preset'] : '',
			'contrast_preset'     => !empty($options['default_contrast_preset']) ? (string) $options['default_contrast_preset'] : ''
		]);
	}

	public function saveSiteThemeSettings(array $settings) {

		$theme = $this->normalizeThemeState($settings);
		$options = (array) cmsController::loadOptions('landingbuilder');

		$options['default_template_preset'] = $theme['template_preset'];
		$options['default_global_style_preset'] = $theme['global_style_preset'];
		$options['default_color_preset'] = $theme['color_preset'];
		$options['default_typography_preset'] = $theme['typography_preset'];
		$options['default_container_preset'] = $theme['container_preset'];
		$options['default_button_preset'] = $theme['button_preset'];
		$options['default_card_preset'] = $theme['card_preset'];
		$options['default_section_spacing'] = $theme['section_spacing'];
		$options['default_radius_preset'] = $theme['radius_preset'];
		$options['default_density_preset'] = $theme['density_preset'];
		$options['default_contrast_preset'] = $theme['contrast_preset'];

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
				['label' => 'Шаблон сайта', 'value' => $this->getThemeOptionTitle('template_preset', $theme['template_preset'], $catalog)],
				['label' => 'Стартовый пресет', 'value' => $this->getThemeOptionTitle('global_style_preset', $theme['global_style_preset'], $catalog)],
				['label' => 'Палитра', 'value' => $this->getThemeOptionTitle('color_preset', $theme['color_preset'], $catalog)],
				['label' => 'Типографика', 'value' => $this->getThemeOptionTitle('typography_preset', $theme['typography_preset'], $catalog)],
				['label' => 'Контейнеры', 'value' => $this->getThemeOptionTitle('container_preset', $theme['container_preset'], $catalog)],
				['label' => 'Кнопки', 'value' => $this->getThemeOptionTitle('button_preset', $theme['button_preset'], $catalog)],
				['label' => 'Карточки', 'value' => $this->getThemeOptionTitle('card_preset', $theme['card_preset'], $catalog)],
				['label' => 'Ритм секций', 'value' => $this->getThemeOptionTitle('section_spacing', $theme['section_spacing'], $catalog)],
				['label' => 'Скругления', 'value' => $this->getThemeOptionTitle('radius_preset', $theme['radius_preset'], $catalog)],
				['label' => 'Плотность', 'value' => $this->getThemeOptionTitle('density_preset', $theme['density_preset'], $catalog)],
				['label' => 'Контраст', 'value' => $this->getThemeOptionTitle('contrast_preset', $theme['contrast_preset'], $catalog)]
			]
		];
	}

	protected function getCanvasThemeOptionCatalog() {
		return $this->getThemeOptionCatalog();
	}

	public function getTemplatePresetCatalog() {
		return [
			'nordic_classic' => [
				'key'              => 'nordic_classic',
				'title'            => 'Классический Нордик',
				'description'      => 'Привычный шаблон сайта как в старом Нордике: универсальный shell, спокойный ритм и базовая навигация.',
				'preview_template' => 'nordic',
				'route_variants'   => [
					'site'     => 'site-default',
					'homepage' => 'homepage',
					'category' => 'category-pages',
					'profile'  => 'profile-pages',
					'landing'  => 'landing-pages'
				],
				'theme_defaults'   => [
					'global_style_preset' => 'nordic_balanced',
					'color_preset'        => 'nordic_day',
					'typography_preset'   => 'editorial',
					'container_preset'    => 'standard',
					'button_preset'       => 'soft_accent',
					'card_preset'         => 'quiet',
					'section_spacing'     => 'comfortable'
				]
			],
			'nordic_editorial' => [
				'key'              => 'nordic_editorial',
				'title'            => 'Nordic Editorial',
				'description'      => 'Редакционный шаблон для статей, help-страниц и спокойного контентного сценария с более журнальным ритмом.',
				'preview_template' => 'nordic',
				'route_variants'   => [
					'site'     => 'content-pages',
					'homepage' => 'homepage',
					'category' => 'category-pages',
					'profile'  => 'profile-pages',
					'landing'  => 'landing-pages'
				],
				'theme_defaults'   => [
					'global_style_preset' => 'nordic_editorial',
					'color_preset'        => 'nordic_day',
					'typography_preset'   => 'editorial',
					'container_preset'    => 'text',
					'button_preset'       => 'ghost',
					'card_preset'         => 'outline',
					'section_spacing'     => 'airy'
				]
			],
			'nordic_catalog' => [
				'key'              => 'nordic_catalog',
				'title'            => 'Nordic Catalog',
				'description'      => 'Каталоговый шаблон для listing-страниц, витрин и рабочих экранов с карточками и фильтрами.',
				'preview_template' => 'nordic',
				'route_variants'   => [
					'site'     => 'category-pages',
					'homepage' => 'homepage',
					'category' => 'category-pages',
					'profile'  => 'profile-pages',
					'landing'  => 'landing-pages'
				],
				'theme_defaults'   => [
					'global_style_preset' => 'nordic_catalog',
					'color_preset'        => 'nordic_day',
					'typography_preset'   => 'compact',
					'container_preset'    => 'wide',
					'button_preset'       => 'solid_brand',
					'card_preset'         => 'raised',
					'section_spacing'     => 'compact'
				]
			],
			'nordic_warm_market' => [
				'key'              => 'nordic_warm_market',
				'title'            => 'Nordic Warm Market',
				'description'      => 'Более мягкий рыночный шаблон для витрин, сервисов и продающих страниц без резкого контраста.',
				'preview_template' => 'nordic',
				'route_variants'   => [
					'site'     => 'site-default',
					'homepage' => 'homepage',
					'category' => 'category-pages',
					'profile'  => 'profile-pages',
					'landing'  => 'landing-pages'
				],
				'theme_defaults'   => [
					'global_style_preset' => 'nordic_balanced',
					'color_preset'        => 'forest_accent',
					'typography_preset'   => 'neutral',
					'container_preset'    => 'wide',
					'button_preset'       => 'soft_accent',
					'card_preset'         => 'raised',
					'section_spacing'     => 'comfortable'
				]
			],
			'nordic_compact' => [
				'key'              => 'nordic_compact',
				'title'            => 'Nordic Compact',
				'description'      => 'Компактный шаблон для плотных рабочих страниц, где важны скорость просмотра и утилитарная подача.',
				'preview_template' => 'nordic',
				'route_variants'   => [
					'site'     => 'category-pages',
					'homepage' => 'homepage',
					'category' => 'category-pages',
					'profile'  => 'profile-pages',
					'landing'  => 'landing-pages'
				],
				'theme_defaults'   => [
					'global_style_preset' => 'nordic_catalog',
					'color_preset'        => 'slate_contrast',
					'typography_preset'   => 'compact',
					'container_preset'    => 'standard',
					'button_preset'       => 'ghost',
					'card_preset'         => 'outline',
					'section_spacing'     => 'compact'
				]
			],
			'nm_landing' => [
				'key'              => 'nm_landing',
				'title'            => 'NM: лендинговый шаблон',
				'description'      => 'Первый управляемый preset по вашему NM-наброску: компактный header, акцентный первый экран и более собранный продающий корпус.',
				'preview_template' => 'nordic',
				'route_variants'   => [
					'site'     => 'nm-site',
					'homepage' => 'nm-homepage',
					'category' => 'category-pages',
					'profile'  => 'profile-pages',
					'landing'  => 'nm-landing'
				],
				'theme_defaults'   => [
					'global_style_preset' => 'nordic_contrast',
					'color_preset'        => 'slate_contrast',
					'typography_preset'   => 'neutral',
					'container_preset'    => 'wide',
					'button_preset'       => 'solid_brand',
					'card_preset'         => 'raised',
					'section_spacing'     => 'comfortable'
				]
			]
		];
	}

	protected function getTemplatePresetByKey($key) {
		$key = trim((string) $key);
		$catalog = $this->getTemplatePresetCatalog();

		if (isset($catalog[$key])) {
			return $catalog[$key];
		}

		return $catalog['nordic_classic'];
	}

	protected function resolveTemplatePresetVariantKey($template_preset, array $page, array $adapter) {
		$preset = $this->getTemplatePresetByKey($template_preset);
		$route_variants = isset($preset['route_variants']) && is_array($preset['route_variants']) ? $preset['route_variants'] : [];

		$page_key = (string) ($page['key'] ?? $page['name'] ?? '');
		if ($page_key === 'homepage' && !empty($route_variants['homepage'])) {
			return (string) $route_variants['homepage'];
		}

		$adapter_key = (string) ($adapter['key'] ?? '');
		if ($adapter_key === 'content_category_generic' && !empty($route_variants['category'])) {
			return (string) $route_variants['category'];
		}

		if ($adapter_key === 'user_profile' && !empty($route_variants['profile'])) {
			return (string) $route_variants['profile'];
		}

		if ($adapter_key === 'standalone_landing' && !empty($route_variants['landing'])) {
			return (string) $route_variants['landing'];
		}

		return !empty($route_variants['site']) ? (string) $route_variants['site'] : '';
	}

	protected function getCanvasThemeDefaults(array $options = []) {
		return $this->getSiteThemeDefaults($options);
	}

	protected function getCanvasBlockCatalog() {
		$catalog = $this->getBlockCatalog();
		return array_values(is_array($catalog) ? $catalog : []);
	}

	protected function getBlockCatalog() {
		$menu_choices = [['value' => '', 'title' => 'Выберите меню…']];
		try {
			$menus = cmsCore::getModel('menu')->getMenus();
			if ($menus) {
				foreach ($menus as $menu) {
					$menu_choices[] = [
						'value' => (string) ($menu['name'] ?? ''),
						'title' => (string) ($menu['title'] ?? ($menu['name'] ?? ''))
					];
				}
			}
		} catch (Throwable $exception) {
			// noop
		}

		$menu_template_choices = [];
		try {
			$templates = cmsTemplate::getInstance()->getAvailableTemplatesFiles('assets/ui', 'menu*.tpl.php');
			if ($templates) {
				foreach ($templates as $key => $title) {
					$menu_template_choices[] = [
						'value' => (string) $key,
						'title' => (string) $title
					];
				}
			}
		} catch (Throwable $exception) {
			// noop
		}
		if (!$menu_template_choices) {
			$menu_template_choices = [
				['value' => 'menu', 'title' => 'menu']
			];
		}

		return [
			'core.hero' => [
				'key'           => 'core.hero',
				'title'         => 'Первый экран (Hero)',
				'default_label' => 'Первый экран',
				'description'   => 'Крупный hero-блок с градиентным фоном, заголовком, подзаголовком, кнопкой и необязательной картинкой.',
				'summary'       => 'Основной блок для первого экрана страницы.',
				'fields'        => [
					['key' => 'eyebrow', 'title' => 'Надзаголовок', 'type' => 'text', 'placeholder' => 'Например: Нордик Builder'],
					['key' => 'title', 'title' => 'Заголовок', 'type' => 'text', 'placeholder' => 'Сильный заголовок первого экрана'],
					['key' => 'text', 'title' => 'Подзаголовок', 'type' => 'textarea', 'placeholder' => 'Коротко объясните пользу страницы.'],
					['key' => 'button_label', 'title' => 'Текст кнопки', 'type' => 'text', 'placeholder' => 'Например: Оставить заявку'],
					['key' => 'button_url', 'title' => 'Ссылка кнопки', 'type' => 'text', 'placeholder' => 'https://... или /contact'],
					['key' => 'image_url', 'title' => 'Картинка (URL)', 'type' => 'text', 'placeholder' => 'https://.../hero.jpg']
				],
				'defaults'      => [
					'eyebrow'      => 'Нордик Builder',
					'title'        => 'Сильный заголовок первого экрана',
					'text'         => 'Короткое пояснение, которое помогает понять предложение с первого взгляда.',
					'button_label' => 'Начать',
					'button_url'   => '',
					'image_url'    => ''
				]
			],
			'core.navigation' => [
				'key'           => 'core.navigation',
				'title'         => 'Навигация',
				'default_label' => 'Навигация',
				'description'   => 'Выводит выбранное меню InstantCMS с нужным стилем и подсветкой активного пункта.',
				'summary'       => 'Используйте для шапки/подвала и системной навигации.',
				'fields'        => [
					[
						'key' => 'menu',
						'title' => 'Меню',
						'type' => 'select',
						'options' => $menu_choices,
						'hint' => 'Выберите одно из меню из раздела «Меню» в админке.'
					],
					[
						'key' => 'template',
						'title' => 'Шаблон меню',
						'type' => 'select',
						'options' => $menu_template_choices,
						'hint' => 'Шаблон вывода берется из assets/ui (menu*.tpl.php), учитывая наследование шаблона сайта.'
					],
					['key' => 'class', 'title' => 'CSS класс', 'type' => 'text', 'placeholder' => 'menu nav', 'hint' => 'Дополнительные классы для контейнера меню.'],
					[
						'key' => 'navbar_color_scheme',
						'title' => 'Цветовая схема',
						'type' => 'select',
						'options' => [
							['value' => '', 'title' => 'По умолчанию'],
							['value' => 'navbar-light', 'title' => 'Светлое меню'],
							['value' => 'navbar-dark', 'title' => 'Тёмное меню']
						]
					],
					[
						'key' => 'menu_nav_style',
						'title' => 'Расположение меню',
						'type' => 'select',
						'options' => [
							['value' => '', 'title' => 'Горизонтальное, по левому краю'],
							['value' => 'justify-content-between', 'title' => 'Горизонтальное, по краям'],
							['value' => 'justify-content-center', 'title' => 'Горизонтальное, по центру'],
							['value' => 'justify-content-end', 'title' => 'Горизонтальное, по правому краю'],
							['value' => 'flex-column', 'title' => 'Вертикальное']
						]
					],
					[
						'key' => 'menu_nav_style_add',
						'title' => 'Расположение на других разрешениях',
						'type' => 'select',
						'options' => [
							['value' => '', 'title' => '—'],
							['value' => 'flex-sm-row justify-content-sm-start', 'title' => 'Горизонтальное ≥576px'],
							['value' => 'flex-md-row justify-content-md-start', 'title' => 'Горизонтальное ≥768px'],
							['value' => 'flex-lg-row justify-content-lg-start', 'title' => 'Горизонтальное ≥992px'],
							['value' => 'flex-xl-row justify-content-xl-start', 'title' => 'Горизонтальное ≥1200px'],
							['value' => 'flex-sm-column', 'title' => 'Вертикальное ≥576px'],
							['value' => 'flex-md-column', 'title' => 'Вертикальное ≥768px'],
							['value' => 'flex-lg-column', 'title' => 'Вертикальное ≥992px'],
							['value' => 'flex-xl-column', 'title' => 'Вертикальное ≥1200px']
						]
					],
					[
						'key' => 'is_detect',
						'title' => 'Выделять активный пункт',
						'type' => 'checkbox'
					],
					[
						'key' => 'is_detect_strict',
						'title' => 'Строгое выделение активного пункта',
						'type' => 'checkbox',
						'hint' => 'Если выключено — может подсветить несколько пунктов по URL-совпадению.'
					],
					[
						'key' => 'max_items',
						'title' => 'Максимальное количество пунктов',
						'type' => 'number',
						'hint' => 'Остальные пункты будут помещены в пункт «Еще…». 0 — без ограничений.'
					]
				],
				'defaults'      => [
					'menu' => '',
					'template' => 'menu',
					'class' => 'menu nav',
					'navbar_color_scheme' => '',
					'menu_nav_style' => '',
					'menu_nav_style_add' => '',
					'is_detect' => 1,
					'is_detect_strict' => 0,
					'max_items' => 0
				]
			],
			'ads.category-header' => [
				'key'           => 'ads.category-header',
				'title'         => 'Шапка категории объявлений',
				'default_label' => 'Шапка категории',
				'description'   => 'Контекстный верхний блок для категории, фильтров и вводного текста.',
				'summary'       => 'Используйте для усиления overlay-страницы категории.',
				'fields'        => [
					['key' => 'eyebrow', 'title' => 'Надзаголовок', 'type' => 'text', 'placeholder' => 'Например: Категория'],
					['key' => 'title', 'title' => 'Заголовок', 'type' => 'text', 'placeholder' => 'Заголовок категории'],
					['key' => 'text', 'title' => 'Пояснение', 'type' => 'textarea', 'placeholder' => 'Помогите пользователю быстрее понять контекст категории.']
				],
				'defaults'      => [
					'eyebrow' => 'Категория',
					'title'   => 'Шапка категории объявлений',
					'text'    => 'Добавьте вводный контекст перед системным списком и фильтрами.'
				]
			],
			'ads.filter-bar' => [
				'key'           => 'ads.filter-bar',
				'title'         => 'Панель фильтров',
				'default_label' => 'Фильтры',
				'description'   => 'Лента быстрых фильтров, уточнений или подсказок для списка.',
				'summary'       => 'Каждая строка станет отдельным фильтром или смысловым чипом.',
				'fields'        => [
					['key' => 'title', 'title' => 'Заголовок блока', 'type' => 'text', 'placeholder' => 'Быстрые уточнения'],
					['key' => 'items_text', 'title' => 'Фильтры по строкам', 'type' => 'textarea', 'placeholder' => "Новые\nС доставкой\nПроверенные продавцы"]
				],
				'defaults'      => [
					'title'      => 'Быстрые уточнения',
					'items_text' => "Новые\nС доставкой\nПроверенные продавцы"
				]
			],
			'profile.cover-hero' => [
				'key'           => 'profile.cover-hero',
				'title'         => 'Обложка профиля',
				'default_label' => 'Обложка профиля',
				'description'   => 'Крупный верхний блок профиля с именем, подводкой и визуальным акцентом.',
				'summary'       => 'Подходит для верхней части страницы пользователя или компании.',
				'fields'        => [
					['key' => 'eyebrow', 'title' => 'Надзаголовок', 'type' => 'text', 'placeholder' => 'Например: Профиль'],
					['key' => 'title', 'title' => 'Заголовок', 'type' => 'text', 'placeholder' => 'Имя профиля или компании'],
					['key' => 'text', 'title' => 'Пояснение', 'type' => 'textarea', 'placeholder' => 'Короткое описание профиля.']
				],
				'defaults'      => [
					'eyebrow' => 'Профиль',
					'title'   => 'Имя профиля или компании',
					'text'    => 'Добавьте краткое описание, специализацию или ключевое позиционирование.'
				]
			],
			'profile.quick-stats' => [
				'key'           => 'profile.quick-stats',
				'title'         => 'Короткая статистика профиля',
				'default_label' => 'Статистика',
				'description'   => 'Набор коротких показателей профиля в компактной сетке.',
				'summary'       => 'Каждая строка в формате «значение|подпись» станет отдельной карточкой.',
				'fields'        => [
					['key' => 'title', 'title' => 'Заголовок блока', 'type' => 'text', 'placeholder' => 'Ключевые показатели'],
					['key' => 'items_text', 'title' => 'Показатели по строкам', 'type' => 'textarea', 'placeholder' => "120|завершенных заказов\n4.9|средний рейтинг\n7 лет|на рынке"]
				],
				'defaults'      => [
					'title'      => 'Ключевые показатели',
					'items_text' => "120|завершенных заказов\n4.9|средний рейтинг\n7 лет|на рынке"
				]
			]
		];
	}

	protected function mergeBlockDefinitionOptions(array $definition, $options) {

		$defaults = isset($definition['defaults']) && is_array($definition['defaults']) ? $definition['defaults'] : [];
		$options = is_array($options) ? $options : [];

		return array_merge($defaults, $options);
	}

	protected function getBlockDefinition($source_key) {

		$source_key = (string) $source_key;
		$catalog = $this->getBlockCatalog();

		if (isset($catalog[$source_key])) {
			return $catalog[$source_key];
		}

		return [
			'key'           => $source_key,
			'title'         => $source_key ?: 'Пользовательский блок',
			'default_label' => $source_key ?: 'Пользовательский блок',
			'description'   => 'Пользовательский блок без зарегистрированного semantic-пресета.',
			'summary'       => 'Для этого блока пока нет описанного semantic-контракта.',
			'fields'        => [],
			'defaults'      => []
		];
	}

	protected function enrichRuntimeBlockNode(array $node) {

		$source_key = trim((string) ($node['source_key'] ?? ($node['label'] ?? '')));
		$definition = $this->getBlockDefinition($source_key);

		$node['source_key'] = $source_key;
		$node['options'] = $this->mergeBlockDefinitionOptions($definition, $node['options'] ?? []);

		if (empty($node['label']) || $node['label'] === $source_key) {
			$node['label'] = $definition['default_label'] ?: $definition['title'];
		}

		$node['block_meta'] = [
			'key'           => $definition['key'],
			'title'         => $definition['title'],
			'default_label' => $definition['default_label'],
			'description'   => $definition['description'],
			'summary'       => $definition['summary'],
			'fields'        => $definition['fields']
		];

		return $node;
	}

	protected function getCanvasSectionPresets() {
		return [
			[
				'key' => 'hero_simple',
				'title' => 'Первый экран (Hero)',
				'description' => 'Крупный первый экран с градиентным фоном, кнопкой и картинкой (опционально).',
				'layout' => '1col',
				'section_type' => 'hero',
				'style_preset' => 'hero',
				'background_tone' => 'brand-soft',
				'container_preset' => 'wide',
				'spacing_preset' => 'xl',
				'columns' => [
					[
						'title' => 'Hero',
						'nodes' => [
							['type' => 'block', 'label' => 'Первый экран', 'source_key' => 'core.hero']
						]
					]
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
			'internal_content_generic' => [
				'key'                  => 'internal_content_generic',
				'title'                => 'Внутренняя страница (native body)',
				'description'          => 'Builder добавляет секции вокруг нативного содержимого внутренней страницы InstantCMS любого типа.',
				'shell'                => 'overlay',
				'default_zone'         => 'before_content',
				'native_content_label' => 'Здесь продолжает работать системное содержимое текущей внутренней страницы InstantCMS.',
				'zones'                => [
					[
						'key'         => 'before_content',
						'slot_key'    => 'before_content',
						'title'       => 'Над системным содержимым',
						'kind'        => 'builder',
						'description' => 'Подходит для заголовка, промо-блоков и навигации перед нативным контентом.'
					],
					[
						'key'         => 'content_body',
						'slot_key'    => 'content_body',
						'title'       => 'Системное содержимое страницы',
						'kind'        => 'native',
						'description' => 'Эта зона занимает slot content_body и остается под управлением стандартного рендера InstantCMS.'
					],
					[
						'key'         => 'after_content',
						'slot_key'    => 'after_content',
						'title'       => 'Под системным содержимым',
						'kind'        => 'builder',
						'description' => 'Нижняя зона для CTA, связанных материалов и дополнительных секций.'
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

		$default_page_key = $page_key_map[$ctype_name] ?? '';

		return $this->resolveOverlayPageKeyFromBindings('content_category', [
			'overlay'      => 'content_category',
			'ctype'        => $ctype_name,
			'category_id'  => (string) ($category['id'] ?? ''),
			'category_key' => (string) ($category['slug'] ?? $category['slug_key'] ?? ''),
		], $default_page_key);
	}

	public function resolveFullTakeoverPageKeyFromBindings(array $route_params, $fallback_page_key = '') {
		return $this->resolvePageKeyFromBindingsByPrefix('page.', $route_params, $fallback_page_key);
	}

	protected function resolveOverlayPageKeyFromBindings($overlay_kind, array $route_params, $fallback_page_key) {
		$fallback_page_key = (string) $fallback_page_key;
		$overlay_kind = trim((string) $overlay_kind);

		if ($overlay_kind === '') {
			return $fallback_page_key;
		}

		return $this->resolvePageKeyFromBindingsByPrefix('overlay.' . $overlay_kind, $route_params, $fallback_page_key);
	}

	protected function resolvePageKeyFromBindingsByPrefix($binding_prefix, array $route_params, $fallback_page_key = '') {
		$fallback_page_key = (string) $fallback_page_key;
		$binding_prefix = trim((string) $binding_prefix);

		if ($binding_prefix === '') {
			return $fallback_page_key;
		}

		$bridge_model = $this->getNordicbuilderBridgeModel();
		if (!$bridge_model || !method_exists($bridge_model, 'getBindingOptionsCandidatesByPrefix')) {
			return $fallback_page_key;
		}

		$core = cmsCore::getInstance();
		$uri = trim((string) ($core->uri ?? ''), '/');
		$is_secure = !empty($core->request) && method_exists($core->request, 'isSecure') ? (bool) $core->request->isSecure() : false;

		$candidates = $bridge_model->getBindingOptionsCandidatesByPrefix($binding_prefix, 50);
		if (!$candidates) {
			return $fallback_page_key;
		}

		$best_key = '';
		$best_score = -1;

		foreach ($candidates as $candidate) {
			$document = isset($candidate['document']) && is_array($candidate['document']) ? $candidate['document'] : [];
			$page_key = (string) (($candidate['page_key'] ?? '') ?: ($document['page_key'] ?? ''));
			if ($page_key === '') {
				continue;
			}

			if (!$this->matchesBindingOptionsDocument($document, $uri, $route_params, $is_secure)) {
				continue;
			}

			$score = $this->scoreBindingOptionsDocument($document);
			if ($score > $best_score) {
				$best_score = $score;
				$best_key = $page_key;
			}
		}

		if ($best_key !== '') {
			if ($fallback_page_key === '' || $best_key !== $fallback_page_key) {
				$page = $this->getPageByKey($best_key);
				if (!$page) {
					return $fallback_page_key;
				}
			}
		}

		return $best_key !== '' ? $best_key : $fallback_page_key;
	}

	protected function matchesBindingOptionsDocument(array $document, $uri, array $route_params, $is_secure) {
		$matching = isset($document['matching']) && is_array($document['matching']) ? $document['matching'] : [];

		if (!empty($matching['require_https']) && !$is_secure) {
			return false;
		}

		$uri = trim((string) $uri, '/');
		$url_masks = isset($matching['url_masks']) && is_array($matching['url_masks']) ? $matching['url_masks'] : [];
		$exclude_masks = isset($matching['exclude_masks']) && is_array($matching['exclude_masks']) ? $matching['exclude_masks'] : [];

		if ($exclude_masks) {
			foreach ($exclude_masks as $mask) {
				if ($this->matchesSimpleUrlMask($mask, $uri)) {
					return false;
				}
			}
		}

		if ($url_masks) {
			$ok = false;
			foreach ($url_masks as $mask) {
				if ($this->matchesSimpleUrlMask($mask, $uri)) {
					$ok = true;
					break;
				}
			}
			if (!$ok) {
				return false;
			}
		}

		$required_params = isset($matching['route_params']) && is_array($matching['route_params']) ? $matching['route_params'] : [];
		foreach ($required_params as $key => $expected) {
			if (!array_key_exists($key, $route_params)) {
				if ($this->allowsMissingBindingRouteParam($expected)) {
					continue;
				}

				return false;
			}

			$actual = $route_params[$key];
			if (is_array($expected)) {
				$expected_strings = array_map('strval', $expected);
				if (!in_array((string) $actual, $expected_strings, true)) {
					return false;
				}
				continue;
			}

			if (!$this->matchesBindingRouteParamValue($actual, $expected)) {
				return false;
			}
		}

		return true;
	}

	protected function allowsMissingBindingRouteParam($expected) {

		if (is_array($expected)) {
			return false;
		}

		$expected = trim((string) $expected);

		return $expected !== '' && strpos($expected, '!') === 0;
	}

	protected function matchesBindingRouteParamValue($actual, $expected) {
		$expected = (string) $expected;

		if ($expected !== '' && strpos($expected, '!') === 0) {
			$negative_expected = substr($expected, 1);
			return (string) $actual !== $negative_expected;
		}

		return (string) $actual === $expected;
	}

	protected function scoreBindingOptionsDocument(array $document) {
		$matching = isset($document['matching']) && is_array($document['matching']) ? $document['matching'] : [];
		$route_params = isset($matching['route_params']) && is_array($matching['route_params']) ? $matching['route_params'] : [];
		$url_masks = isset($matching['url_masks']) && is_array($matching['url_masks']) ? $matching['url_masks'] : [];

		return (count($route_params) * 100) + (count($url_masks) * 10);
	}

	protected function matchesSimpleUrlMask($mask, $uri) {
		$mask = trim((string) $mask);
		if ($mask === '') {
			return false;
		}

		$mask = trim($mask, '/');
		$uri = trim((string) $uri, '/');

		$pattern = preg_quote($mask, '#');
		$pattern = str_replace('\\*', '.*', $pattern);

		return (bool) preg_match('#^' . $pattern . '$#u', $uri);
	}

	protected function getOverlayIntegrationByPageKey($page_key, $is_admin = false) {

		$page_key = (string) $page_key;
		if (!$page_key) {
			return false;
		}

		// Do not render overlay for synthetic default pages.
		// Overlay should appear only when a real page is stored
		// in landingbuilder table or in nordicbuilder bridge documents.
		if (!$this->hasStoredOverlayPageByKey($page_key)) {
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
		return true;
	}

	protected function hasStoredOverlayPageByKey($page_key) {

		$page_key = (string) $page_key;
		if ($page_key === '') {
			return false;
		}

		if ($this->hasInstalledSchema()) {
			$legacy_page = $this->getItemByField(self::PAGE_TABLE, 'name', $page_key);
			if ($legacy_page) {
				return true;
			}
		}

		$bridge_model = $this->getNordicbuilderBridgeModel();
		if ($bridge_model && method_exists($bridge_model, 'getPageDocumentByKey')) {
			$stored_document = $bridge_model->getPageDocumentByKey($page_key);
			if ($stored_document) {
				return true;
			}
		}

		return false;
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

		if ($page_key === 'profile-cover') {
			return 'user_profile';
		}

		if ($page_key === 'ads-category') {
			return 'content_category_generic';
		}

		$binding_adapter_key = $this->resolveAdapterKeyFromBindingOptions($page_key);
		if ($binding_adapter_key !== '') {
			return $binding_adapter_key;
		}

		return 'standalone_landing';
	}

	protected function resolveAdapterKeyFromBindingOptions($page_key) {

		$page_key = (string) $page_key;
		if ($page_key === '') {
			return '';
		}

		$bridge_model = $this->getNordicbuilderBridgeModel();
		if (!$bridge_model || !method_exists($bridge_model, 'getBindingOptionsCandidatesByPrefix')) {
			return '';
		}

		$prefixes = ['overlay.user_profile', 'overlay.content_category', 'page.'];

		foreach ($prefixes as $prefix) {
			$candidates = $bridge_model->getBindingOptionsCandidatesByPrefix($prefix, 200);
			if (!$candidates) {
				continue;
			}

			foreach ($candidates as $candidate) {
				$document = isset($candidate['document']) && is_array($candidate['document']) ? $candidate['document'] : [];
				$candidate_page_key = (string) (($candidate['page_key'] ?? '') ?: ($document['page_key'] ?? ''));
				if ($candidate_page_key === '' || $candidate_page_key !== $page_key) {
					continue;
				}

				$binding_key = (string) (($candidate['binding_key'] ?? '') ?: ($document['key'] ?? ''));
				if (strpos($binding_key, 'overlay.user_profile') === 0) {
					return 'user_profile';
				}
				if (strpos($binding_key, 'overlay.content_category') === 0) {
					return 'content_category_generic';
				}
				if (strpos($binding_key, 'page.all_internal') === 0) {
					return 'internal_content_generic';
				}

				$matching = isset($document['matching']) && is_array($document['matching']) ? $document['matching'] : [];
				$route_params = isset($matching['route_params']) && is_array($matching['route_params']) ? $matching['route_params'] : [];
				$page_type_rule = trim((string) ($route_params['page_type'] ?? ''));

				if ($page_type_rule === '!homepage') {
					return 'internal_content_generic';
				}
			}
		}

		return '';
	}

	protected function resolvePageType(array $page) {

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

						if (($node['type'] ?? '') === 'block') {
							$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index] = $this->enrichRuntimeBlockNode(
								$schema['sections'][$section_index]['columns'][$column_index]['nodes'][$node_index]
							);
							continue;
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
			$explicit_zone_key = !empty($section['settings']['zone_key']) ? (string) $section['settings']['zone_key'] : '';
			$legacy_zone_key = !empty($section['zone_key']) ? (string) $section['zone_key'] : '';
			$default_zone_key = $this->normalizeRuntimeZoneKey($adapter['default_zone'] ?? 'before_content');
			$has_explicit_zone = $explicit_zone_key !== '' || ($legacy_zone_key !== '' && $this->normalizeRuntimeZoneKey($legacy_zone_key) !== $default_zone_key);

			if ($explicit_zone_key !== '') {
				$zone_key = $explicit_zone_key;
			} elseif ($legacy_zone_key !== '' && $this->normalizeRuntimeZoneKey($legacy_zone_key) !== $default_zone_key) {
				$zone_key = $legacy_zone_key;
			} else {
				$zone_key = $this->resolveImplicitSectionZoneKey($adapter, $shell);
			}
			$zone_key = $this->normalizeRuntimeZoneKey($zone_key);

			// For adapters with native content_body, sections must not capture that slot.
			if (
				$zone_key === 'content_body' &&
				$this->hasNativeRuntimeZone($adapter, 'content_body')
			) {
				$zone_key = $this->resolveImplicitSectionZoneKey($adapter, $shell);
			}

			if (!$this->isRuntimeBuilderSlotEnabled($shell, $zone_key)) {
				if (!$has_explicit_zone) {
					continue;
				}
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

	protected function resolveImplicitSectionZoneKey(array $adapter, array $shell = []) {

		$default_zone_key = $this->normalizeRuntimeZoneKey($adapter['default_zone'] ?? 'before_content');
		if (
			$default_zone_key !== '' &&
			!$this->hasNativeRuntimeZone($adapter, $default_zone_key) &&
			$this->isRuntimeBuilderSlotEnabled($shell, $default_zone_key)
		) {
			return $default_zone_key;
		}

		foreach (($adapter['zones'] ?? []) as $zone) {
			if (($zone['kind'] ?? 'builder') !== 'builder') {
				continue;
			}

			$candidate_zone_key = $this->normalizeRuntimeZoneKey($zone['slot_key'] ?? ($zone['key'] ?? ''));
			if ($candidate_zone_key === '') {
				continue;
			}

			if (!$this->isRuntimeBuilderSlotEnabled($shell, $candidate_zone_key)) {
				continue;
			}

			return $candidate_zone_key;
		}

		return $default_zone_key;
	}

	protected function hasNativeRuntimeZone(array $adapter, $zone_key) {

		$zone_key = $this->normalizeRuntimeZoneKey($zone_key);

		foreach (($adapter['zones'] ?? []) as $zone) {
			$current_zone_key = $this->normalizeRuntimeZoneKey($zone['key'] ?? '');
			if ($current_zone_key !== $zone_key) {
				continue;
			}

			return ($zone['kind'] ?? 'builder') === 'native';
		}

		return false;
	}

	protected function buildRuntimeShell(array $page, array $adapter) {

		$layout = isset($page['schema']['layout']) && is_array($page['schema']['layout']) ? $page['schema']['layout'] : [];
		$slot_positions = $this->getNordicShellSlotPositions();
		$shell_variant = $this->resolveRuntimeShellVariant($page, $adapter);
		$raw_body_columns_mode = trim((string) ($layout['body_columns_mode'] ?? ''));
		$body_columns_mode = $this->normalizeBodyColumnsMode($raw_body_columns_mode);
		if ($body_columns_mode === '') {
			$body_columns_mode = $this->mapBodyLayoutToColumnsMode($shell_variant['body_layout'] ?? 'no_sidebars');
		}
		$body_left_span = $this->normalizeBodyColumnSpan($layout['body_left_span'] ?? 3, 3);
		$body_right_span = $this->normalizeBodyColumnSpan($layout['body_right_span'] ?? 3, 3);

		$body_columns = $this->buildRuntimeBodyColumnsState($body_columns_mode, $body_left_span, $body_right_span);
		$body_layout = $this->mapBodyColumnsModeToBodyLayout($body_columns['mode']);

		$active_slots = $this->normalizeShellSlots($shell_variant['active_slots']);
		$active_slots = array_values(array_diff($active_slots, ['content_sidebar_left', 'content_sidebar_right']));

		if (!empty($body_columns['has_left'])) {
			$active_slots[] = 'content_sidebar_left';
		}
		if (!empty($body_columns['has_right'])) {
			$active_slots[] = 'content_sidebar_right';
		}

		$active_slots = $this->normalizeShellSlots($active_slots);
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
			'body_layout'  => $body_layout,
			'body_columns' => $body_columns,
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

		$template_preset = (string) ($page['schema']['theme']['template_preset'] ?? '');
		$template_variant = $this->resolveTemplatePresetVariantKey($template_preset, $page, $adapter);
		if ($template_variant !== '' && $this->getShellVariantByKey($template_variant)) {
			return $template_variant;
		}

		$page_key = (string) ($page['key'] ?? $page['name'] ?? '');
		if ($page_key === 'homepage') {
			return 'homepage';
		}

		$adapter_key = (string) ($adapter['key'] ?? '');
		if ($adapter_key === 'content_category_generic') {
			return 'category-pages';
		}

		if ($adapter_key === 'internal_content_generic') {
			return 'site-default';
		}

		if ($adapter_key === 'user_profile') {
			return 'profile-pages';
		}

		if ($adapter_key === 'standalone_landing') {
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

		$template_preset = (string) ($page['schema']['theme']['template_preset'] ?? '');
		$template_variant = $this->resolveTemplatePresetVariantKey($template_preset, $page, $adapter);
		if ($template_variant !== '' && $template_variant === $variant_key) {
			return 'template-preset';
		}

		$page_key = (string) ($page['key'] ?? $page['name'] ?? '');
		if ($page_key === 'homepage') {
			return 'page-key';
		}

		$adapter_key = (string) ($adapter['key'] ?? '');
		if (in_array($adapter_key, ['content_category_generic', 'internal_content_generic', 'user_profile', 'standalone_landing'], true)) {
			return 'adapter';
		}

		return 'default';
	}

	protected function getShellAssignmentSourceTitles() {
		return [
			'page-layout' => 'Переопределение страницы',
			'template-preset' => 'Шаблон страницы или сайта',
			'page-key'    => 'Системный ключ страницы',
			'adapter'     => 'Adapter страницы',
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
			$this->buildDefaultPage('homepage', 'Главная страница', 'instant_content_body', 'draft'),
			$this->buildDefaultPage('ads-category', 'Категория Объявлений', 'instant_content_body', 'prototype'),
			$this->buildDefaultPage('profile-cover', 'Профиль пользователя', 'instant_content_body', 'idea')
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
			'content_slot' => 'content_body',
			'body_columns_mode' => '',
			'body_left_span' => 3,
			'body_right_span' => 3
		], $schema['layout']) : [
			'template'     => 'nordic',
			'scheme'       => $this->getNordicShellSchemeKey(),
			'width_mode'   => 'contained',
			'header_mode'  => 'theme',
			'footer_mode'  => 'theme',
			'shell_variant'=> '',
			'content_slot' => 'content_body',
			'body_columns_mode' => '',
			'body_left_span' => 3,
			'body_right_span' => 3
		];
		$template_preset = (string) ($schema['theme']['template_preset'] ?? 'nordic_classic');
		$template_config = $this->getTemplatePresetByKey($template_preset);
		$schema['layout']['template'] = !empty($schema['layout']['template']) ? (string) $schema['layout']['template'] : (string) ($template_config['preview_template'] ?? 'nordic');
		$schema['layout']['scheme'] = !empty($schema['layout']['scheme']) ? (string) $schema['layout']['scheme'] : $this->getNordicShellSchemeKey();
		$schema['layout']['shell_variant'] = $this->sanitizeShellVariantKey($schema['layout']['shell_variant']);
		$schema['layout']['content_slot'] = $this->normalizeRuntimeZoneKey($schema['layout']['content_slot']);
		$schema['layout']['body_columns_mode'] = $this->normalizeBodyColumnsMode($schema['layout']['body_columns_mode'] ?? '');
		$schema['layout']['body_left_span'] = $this->normalizeBodyColumnSpan($schema['layout']['body_left_span'] ?? 3, 3);
		$schema['layout']['body_right_span'] = $this->normalizeBodyColumnSpan($schema['layout']['body_right_span'] ?? 3, 3);
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
				'stack_tablet'     => false,
				'stack_phone'      => true,
				'width_inherit'    => true,
				'autoscale_base_blocks' => false,
				'gap_preset'       => 'md',
				'background_class' => '',
				'padding'          => 'md',
				'css_class'        => ''
			], $section['settings']) : [
				'style_preset'     => 'content',
				'container_preset' => 'standard',
				'spacing_preset'   => 'md',
				'background_tone'  => 'base',
				'stack_tablet'     => false,
				'stack_phone'      => true,
				'width_inherit'    => true,
				'autoscale_base_blocks' => false,
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
			'template_preset'     => 'nordic_classic',
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

	protected function normalizeThemeState(array $theme = []) {

		$form_catalog = $this->getThemeFormCatalog();
		$template_preset = isset($theme['template_preset']) ? (string) $theme['template_preset'] : 'nordic_classic';
		if (!array_key_exists($template_preset, $form_catalog['template_preset'] ?? [])) {
			$template_preset = 'nordic_classic';
		}

		$defaults = array_merge($this->getBaseThemeState(), $this->getTemplatePresetByKey($template_preset)['theme_defaults'] ?? []);
		$defaults['template_preset'] = $template_preset;
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
			],
			'nm-site' => [
				'title'               => 'NM: основной шаблон сайта',
				'description'         => 'Собранный продающий shell по NM-сценарию: компактный header, акцент на основном контенте и минимальный footer.',
				'target_label'        => 'Основные страницы сайта в шаблоне NM',
				'scope'               => 'site-template',
				'is_system'           => 1,
				'header_variant'      => 'compact',
				'footer_variant'      => 'minimal',
				'menu_placement'      => 'header_primary',
				'show_site_top'       => 0,
				'show_hero'           => 0,
				'show_before_content' => 0,
				'show_after_content'  => 1,
				'body_layout'         => 'no_sidebars',
				'homepage_shell_mode' => 'inherit',
				'sticky_header'       => 'smart',
				'mobile_menu_mode'    => 'drawer'
			],
			'nm-homepage' => [
				'title'               => 'NM: главная страница',
				'description'         => 'Главная в стиле NM: builder управляет первым экраном, shell не перегружает страницу боковыми зонами.',
				'target_label'        => 'Маршрут / и стартовые страницы в шаблоне NM',
				'scope'               => 'homepage',
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
				'sticky_header'       => 'on',
				'mobile_menu_mode'    => 'drawer'
			],
			'nm-landing' => [
				'title'               => 'NM: лендинги',
				'description'         => 'Лендинговый shell по NM-наброску: компактный chrome и чистый корпус под page builder.',
				'target_label'        => 'Landing pages и промо-страницы в шаблоне NM',
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

	protected function normalizeBodyColumnsMode($mode) {

		$mode = trim((string) $mode);
		if ($mode === '') {
			return '';
		}
		$allowed = ['1', '2-left', '2-right', '3'];

		return in_array($mode, $allowed, true) ? $mode : '1';
	}

	protected function normalizeBodyColumnSpan($span, $default = 3) {

		$default = (int) $default;
		$value = is_numeric($span) ? (int) $span : $default;

		if ($value < 2) {
			$value = 2;
		}
		if ($value > 5) {
			$value = 5;
		}

		return $value;
	}

	protected function mapBodyLayoutToColumnsMode($body_layout) {

		$body_layout = trim((string) $body_layout);

		if ($body_layout === 'left_sidebar') {
			return '2-left';
		}
		if ($body_layout === 'right_sidebar') {
			return '2-right';
		}
		if ($body_layout === 'two_sidebars') {
			return '3';
		}

		return '1';
	}

	protected function mapBodyColumnsModeToBodyLayout($mode) {

		$mode = $this->normalizeBodyColumnsMode($mode);

		if ($mode === '2-left') {
			return 'left_sidebar';
		}
		if ($mode === '2-right') {
			return 'right_sidebar';
		}
		if ($mode === '3') {
			return 'two_sidebars';
		}

		return 'no_sidebars';
	}

	protected function buildRuntimeBodyColumnsState($mode, $left_span, $right_span) {

		$mode = $this->normalizeBodyColumnsMode($mode);
		$left_span = $this->normalizeBodyColumnSpan($left_span, 3);
		$right_span = $this->normalizeBodyColumnSpan($right_span, 3);

		$has_left = in_array($mode, ['2-left', '3'], true);
		$has_right = in_array($mode, ['2-right', '3'], true);

		if (!$has_left) {
			$left_span = 0;
		}
		if (!$has_right) {
			$right_span = 0;
		}

		if ($has_left && $has_right && ($left_span + $right_span) > 8) {
			$right_span = 8 - $left_span;
			if ($right_span < 2) {
				$right_span = 2;
				$left_span = 6;
			}
		}

		$body_span = 12 - $left_span - $right_span;
		if ($body_span < 4) {
			$body_span = 4;
		}

		return [
			'mode' => $mode,
			'has_left' => $has_left,
			'has_right' => $has_right,
			'left_span' => $left_span,
			'right_span' => $right_span,
			'body_span' => $body_span
		];
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