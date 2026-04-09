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
			'set_status_url'     => href_to($this->controller->root_url, 'set_page_status')
		]);
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