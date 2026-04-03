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

	public function getPageByKey($page_key) {

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

		$devices = ['desktop', 'mobile'];

		if (!empty($options['enable_tablet_mode'])) {
			$devices = ['desktop', 'tablet', 'mobile'];
		}

		$left_tabs = ['Блоки Нордик'];

		if (!empty($options['enable_system_widgets'])) {
			$left_tabs[] = 'Системные widgets';
		}

		return [
			'devices'             => $devices,
			'left_tabs'           => $left_tabs,
			'sections'            => $page['schema']['sections'],
			'widget_nodes'        => $page['widget_nodes'],
			'versions'            => !empty($page['key']) ? $this->getPageVersionsByKey($page['key']) : [],
			'schema_installed'    => $this->hasInstalledSchema()
		];
	}

	protected function normalizePage(array $item) {

		$item['key'] = $item['name'];
		$item['mode'] = $item['page_mode'];
		$item['schema'] = $this->decodeSchema(isset($item['schema_json']) ? $item['schema_json'] : '', $item['name']);
		$item['widget_nodes'] = [];

		return $item;
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
						'title'   => 'Hero / Intro',
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
						'title'   => 'Контентная сетка',
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

		$schema['sections'] = isset($schema['sections']) && is_array($schema['sections']) ? array_values($schema['sections']) : [];

		foreach ($schema['sections'] as $section_index => $section) {
			$schema['sections'][$section_index]['uid'] = !empty($section['uid']) ? $section['uid'] : 'section-' . ($section_index + 1);
			$schema['sections'][$section_index]['title'] = !empty($section['title']) ? $section['title'] : 'Секция ' . ($section_index + 1);
			$schema['sections'][$section_index]['layout'] = !empty($section['layout']) ? $section['layout'] : '1col';
			$schema['sections'][$section_index]['columns'] = isset($section['columns']) && is_array($section['columns']) ? array_values($section['columns']) : [];

			foreach ($schema['sections'][$section_index]['columns'] as $column_index => $column) {
				$schema['sections'][$section_index]['columns'][$column_index]['uid'] = !empty($column['uid']) ? $column['uid'] : $schema['sections'][$section_index]['uid'] . '-column-' . ($column_index + 1);
				$schema['sections'][$section_index]['columns'][$column_index]['title'] = !empty($column['title']) ? $column['title'] : 'Колонка ' . ($column_index + 1);
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
				}
			}
		}

		return $schema;
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

			$row = [
				'page_id'            => $page_id,
				'node_uid'           => $node['node_uid'],
				'widget_id'          => !empty($node['widget_id']) ? (int) $node['widget_id'] : 0,
				'widget_name'        => $widget ? $widget['name'] : $node['widget_name'],
				'widget_controller'  => $widget ? $widget['controller'] : $node['widget_controller'],
				'widget_title'       => $widget ? $widget['title'] : $node['widget_title'],
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