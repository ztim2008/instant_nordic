<?php

class actionNordicbuilderPages extends cmsAction {

	public function run() {
		$bridge_model = cmsCore::getModel('landingbuilder');
		$pages = $bridge_model->getPagesForAdmin();
		$preview_routes = $this->resolvePreviewRoutesByPageKey();
		$content_types = [];

		$content_model = cmsCore::getModel('content');
		if ($content_model && method_exists($content_model, 'getContentTypes')) {
			foreach ((array) $content_model->getContentTypes() as $ctype) {
				$name = trim((string) ($ctype['name'] ?? ''));
				if ($name === '') {
					continue;
				}

				$title = trim((string) ($ctype['title'] ?? ''));
				if ($title === '' && !empty($ctype['labels']['one'])) {
					$title = trim((string) $ctype['labels']['one']);
				}

				$content_types[] = [
					'name'  => $name,
					'title' => $title !== '' ? $title : $name,
				];
			}
		}

		foreach ($pages as &$page) {
			$page['canvas_url'] = href_to_abs($this->controller->root_url, 'canvas', $page['key']);
			$page['view_url'] = href_to('nordicbuilder', 'view', [$page['key']]);
			$page_key = (string) ($page['key'] ?? '');
			if ($page_key !== '' && !empty($preview_routes[$page_key])) {
				$page['view_url'] = (string) $preview_routes[$page_key];
			}
		}
		unset($page);

		$global_source_screen = $this->buildGlobalSectionsSourceScreen($pages, $bridge_model);

		return $this->cms_template->render('backend/pages', [
			'menu'               => $this->controller->getBackendMenu(),
			'pages'              => $pages,
			'content_types'      => $content_types,
			'is_schema_installed'=> $bridge_model->hasInstalledSchema(),
			'bindings_url'       => href_to('admin', 'controllers', ['edit', $this->controller->root_url, 'bindings']),
			'create_page_url'    => href_to($this->controller->root_url, 'create_page'),
			'publish_page_url'   => href_to($this->controller->root_url, 'publish_page'),
			'create_binding_url' => href_to($this->controller->root_url, 'create_binding'),
			'delete_page_url'    => href_to($this->controller->root_url, 'delete_page'),
			'set_status_url'     => href_to($this->controller->root_url, 'set_page_status'),
			'global_sections_source_url' => href_to($this->controller->root_url, 'set_global_sections_source'),
			'global_sections_source_options' => $global_source_screen['options'],
			'explicit_global_sections_source_page_key' => $global_source_screen['explicit_page_key'],
			'effective_global_sections_source_page_key' => $global_source_screen['effective_page_key']
		]);
	}

	protected function buildGlobalSectionsSourceScreen(array $pages, $bridge_model) {
		$options = (array) cmsController::loadOptions('landingbuilder');
		$explicit_page_key = trim((string) ($options['global_sections_source_page_key'] ?? ''));
		$explicit_page_key = $this->sanitizePageKey($explicit_page_key);

		$source_options = [];
		$flag_source_keys = [];

		foreach ($pages as $page) {
			$page_key = trim((string) ($page['key'] ?? ''));
			if ($page_key === '') {
				continue;
			}

			$layout = isset($page['schema']['layout']) && is_array($page['schema']['layout'])
				? $page['schema']['layout']
				: [];
			$is_flag_source = !empty($layout['use_as_global_sections_source']);

			if ($is_flag_source) {
				$flag_source_keys[] = $page_key;
			}

			$source_options[] = [
				'key' => $page_key,
				'title' => (string) ($page['title'] ?? $page_key),
				'is_flag_source' => $is_flag_source
			];
		}

		$effective_page_key = '';
		$known_keys = array_column($source_options, 'key');

		if ($explicit_page_key !== '' && in_array($explicit_page_key, $known_keys, true)) {
			$effective_page_key = $explicit_page_key;
		} elseif ($flag_source_keys) {
			sort($flag_source_keys, SORT_STRING);
			$effective_page_key = (string) $flag_source_keys[0];
		}

		return [
			'options' => $source_options,
			'explicit_page_key' => $explicit_page_key,
			'effective_page_key' => $effective_page_key
		];
	}

	protected function sanitizePageKey($value) {
		$value = trim((string) $value);
		if ($value === '') {
			return '';
		}

		$value = strtolower($value);
		$value = preg_replace('/[^a-z0-9\-_]/', '', $value) ?: '';

		return trim($value);
	}

	protected function resolvePreviewRoutesByPageKey() {
		$routes = [];

		$nordicbuilder_model = cmsCore::getModel('nordicbuilder');
		if (!$nordicbuilder_model || !method_exists($nordicbuilder_model, 'getBindingOptionsIndex') || !method_exists($nordicbuilder_model, 'getBindingOptionsByKey')) {
			return $routes;
		}

		$items = (array) $nordicbuilder_model->getBindingOptionsIndex(300);
		foreach ($items as $item) {
			$page_key = trim((string) ($item['page_key'] ?? ''));
			$binding_key = trim((string) ($item['binding_key'] ?? ''));

			if ($page_key === '' || $binding_key === '' || isset($routes[$page_key])) {
				continue;
			}

			$binding = $nordicbuilder_model->getBindingOptionsByKey($binding_key);
			$document = isset($binding['document']) && is_array($binding['document']) ? $binding['document'] : [];
			$preview_url = $this->resolvePreviewUrlFromBindingDocument($document);

			if ($preview_url !== '') {
				$routes[$page_key] = $preview_url;
			}
		}

		return $routes;
	}

	protected function resolvePreviewUrlFromBindingDocument(array $document) {
		$matching = isset($document['matching']) && is_array($document['matching']) ? $document['matching'] : [];
		$route_params = isset($matching['route_params']) && is_array($matching['route_params']) ? $matching['route_params'] : [];
		$overlay = trim((string) ($route_params['overlay'] ?? ''));

		if ($overlay === 'content_category') {
			$ctype = $route_params['ctype'] ?? '';
			if (is_array($ctype)) {
				$ctype = (string) reset($ctype);
			}
			$ctype = trim((string) $ctype);
			if ($ctype !== '') {
				return href_to($ctype);
			}

			return href_to('board');
		}

		if ($overlay === 'user_profile') {
			return href_to('users', 1);
		}

		$ctrl = trim((string) ($route_params['ctrl'] ?? ''));
		$action = trim((string) ($route_params['action'] ?? ''));
		$page_type = trim((string) ($route_params['page_type'] ?? ''));

		if ($page_type === 'homepage' || ($ctrl === '' && $action === 'index')) {
			return href_to_home();
		}

		$url_masks = isset($matching['url_masks']) && is_array($matching['url_masks']) ? $matching['url_masks'] : [];
		foreach ($url_masks as $mask) {
			$path = trim((string) $mask);
			if ($path === '') {
				continue;
			}

			$path = str_replace('*', '', $path);
			$path = trim($path, '/');

			if ($path !== '') {
				return '/' . $path;
			}
		}

		return '';
	}
}