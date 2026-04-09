<?php

class actionNordicbuilderCreateBinding extends cmsAction {

	public function run() {

		if (!$this->request->isAjax()) {
			return cmsCore::error404();
		}

		$csrf_token = (string) $this->request->get('csrf_token', '');
		if (!cmsForm::validateCSRFToken($csrf_token)) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => LANG_FORM_ERRORS
			]);
		}

		$model = cmsCore::getModel('nordicbuilder');
		if (!$model) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Nordicbuilder model is unavailable.'
			]);
		}

		$binding_key = trim((string) $this->request->get('binding_key', ''));
		$page_key = trim((string) $this->request->get('page_key', ''));
		$title = trim((string) $this->request->get('title', ''));
		$priority = (int) $this->request->get('priority', 0);
		$priority = max(-100000, min(100000, $priority));

		if ($binding_key === '' || $page_key === '') {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'binding_key и page_key обязательны.'
			]);
		}

		$url_masks = $this->parseLinesToList((string) $this->request->get('url_masks', ''));
		$exclude_masks = $this->parseLinesToList((string) $this->request->get('exclude_masks', ''));

		$route_params_raw = trim((string) $this->request->get('route_params_json', ''));
		$route_params = [];
		if ($route_params_raw !== '') {
			$decoded = json_decode($route_params_raw, true);
			if (!is_array($decoded)) {
				return $this->cms_template->renderJSON([
					'error'   => true,
					'message' => 'route_params должен быть валидным JSON-объектом.'
				]);
			}
			$route_params = $decoded;
		}

		$document = [
			'schema_version' => '1.0',
			'key'           => $binding_key,
			'title'         => $title,
			'page_key'      => $page_key,
			'priority'      => $priority,
			'matching'      => [
				'url_masks'      => $url_masks,
				'exclude_masks'  => $exclude_masks,
				'route_params'   => $route_params,
				'require_https'  => false
			],
			'fallback'      => [
				'strategy' => 'theme',
				'on_missing_page' => 'theme',
				'on_missing_adapter' => 'theme',
				'on_missing_preset' => 'binding_default'
			],
			'rules'         => [
				'allow_structural_overlay' => true,
				'allow_dynamic_blocks' => true,
				'allow_custom_css' => false
			]
		];

		$user_id = (int) ($this->cms_user->id ?? 0);
		$result = $model->saveBindingOptions($binding_key, $document, $user_id);
		if (empty($result['is_valid'])) {
			$errors = (array) ($result['errors'] ?? []);
			$message = $errors ? implode("\n", array_map('strval', $errors)) : 'Не удалось сохранить правило.';
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => $message
			]);
		}

		return $this->cms_template->renderJSON([
			'error' => false,
			'binding_key' => $binding_key,
			'page_key' => $page_key
		]);
	}

	protected function parseLinesToList($raw) {
		$lines = preg_split('/\r\n|\r|\n/', (string) $raw);
		$items = [];

		foreach ($lines as $line) {
			$line = trim((string) $line);
			if ($line === '') {
				continue;
			}
			$items[] = $line;
		}

		return array_values(array_unique($items));
	}
}
