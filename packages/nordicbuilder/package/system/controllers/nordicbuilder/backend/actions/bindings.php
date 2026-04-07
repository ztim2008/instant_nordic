<?php

class actionNordicbuilderBindings extends cmsAction {

	public function run() {
		$model = cmsCore::getModel('nordicbuilder');
		if (!$model) {
			return cmsCore::error('Nordicbuilder model is unavailable.');
		}

		$errors = [];
		$messages = [];

		$selected_key = (string) $this->request->get('key', '');
		$selected = $selected_key !== '' ? $model->getBindingOptionsByKey($selected_key) : false;
		$selected_doc = $selected && !empty($selected['document']) && is_array($selected['document']) ? $selected['document'] : [];

		if ($this->request->has('seed_examples')) {
			$csrf_token = (string) $this->request->get('csrf_token', '');
			if (!cmsForm::validateCSRFToken($csrf_token)) {
				cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
				return $this->redirectBack();
			}

			$user_id = (int) ($this->cms_user->id ?? 0);
			$examples = $this->buildExampleBindingOptions();
			$created = 0;

			foreach ($examples as $example) {
				$binding_key = (string) ($example['binding_key'] ?? '');
				if ($binding_key === '') {
					continue;
				}

				$existing = $model->getBindingOptionsByKey($binding_key);
				if ($existing) {
					continue;
				}

				$document = (array) ($example['document'] ?? []);
				$result = $model->saveBindingOptions($binding_key, $document, $user_id);
				if (!empty($result['is_valid'])) {
					$created++;
				}
			}

			cmsUser::addSessionMessage('Примеры правил добавлены: ' . $created, 'success');
			return $this->redirectToAction('bindings');
		}

		if ($this->request->has('delete')) {
			$csrf_token = (string) $this->request->get('csrf_token', '');
			if (!cmsForm::validateCSRFToken($csrf_token)) {
				cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
				return $this->redirectBack();
			}

			$delete_key = (string) $this->request->get('binding_key', '');
			if ($delete_key !== '') {
				$ok = $model->deleteBindingOptionsByKey($delete_key);
				cmsUser::addSessionMessage($ok ? 'Правило удалено.' : 'Не удалось удалить правило.', $ok ? 'success' : 'error');
			}

			return $this->redirectToAction('bindings');
		}

		if ($this->request->has('submit')) {
			$csrf_token = (string) $this->request->get('csrf_token', '');
			if (!cmsForm::validateCSRFToken($csrf_token)) {
				cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
				return $this->redirectBack();
			}

			$binding_key = trim((string) $this->request->get('binding_key', ''));
			$title = trim((string) $this->request->get('title', ''));
			$page_key = trim((string) $this->request->get('page_key', ''));

			$url_masks = $this->parseLinesToList((string) $this->request->get('url_masks', ''));
			$exclude_masks = $this->parseLinesToList((string) $this->request->get('exclude_masks', ''));

			$route_params_raw = trim((string) $this->request->get('route_params_json', ''));
			$route_params = [];
			if ($route_params_raw !== '') {
				$decoded = json_decode($route_params_raw, true);
				if (!is_array($decoded)) {
					$errors['route_params_json'] = 'route_params должен быть валидным JSON-объектом.';
				} else {
					$route_params = $decoded;
				}
			}

			$require_https = (bool) $this->request->get('require_https', false);
			$fallback_strategy = (string) $this->request->get('fallback_strategy', 'theme');
			$allowed_strategies = ['theme', 'binding_default', 'page_default', 'disabled'];
			if (!in_array($fallback_strategy, $allowed_strategies, true)) {
				$fallback_strategy = 'theme';
			}

			$rules = [
				'allow_structural_overlay' => (bool) $this->request->get('allow_structural_overlay', true),
				'allow_dynamic_blocks'     => (bool) $this->request->get('allow_dynamic_blocks', true),
				'allow_custom_css'         => (bool) $this->request->get('allow_custom_css', false)
			];

			if ($binding_key === '') {
				$errors['binding_key'] = 'binding_key обязателен.';
			}

			if ($page_key === '') {
				$errors['page_key'] = 'page_key обязателен.';
			}

			if (!$errors) {
				$document = [
					'schema_version' => '1.0',
					'key'           => $binding_key,
					'title'         => $title,
					'page_key'      => $page_key,
					'matching'      => [
						'url_masks'      => $url_masks,
						'exclude_masks'  => $exclude_masks,
						'route_params'   => $route_params,
						'require_https'  => $require_https
					],
					'fallback'      => [
						'strategy' => $fallback_strategy,
						'on_missing_page' => 'theme',
						'on_missing_adapter' => 'theme',
						'on_missing_preset' => 'binding_default'
					],
					'rules'         => $rules
				];

				$result = $model->saveBindingOptions($binding_key, $document, (int) ($this->cms_user->id ?? 0));

				if (!empty($result['is_valid'])) {
					cmsUser::addSessionMessage(LANG_CP_SAVE_SUCCESS, 'success');
					return $this->redirectToAction('bindings', ['key' => $binding_key]);
				}

				$errors = (array) ($result['errors'] ?? ['storage' => 'Не удалось сохранить правило.']);
				cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
			}

			$selected_key = $binding_key;
			$selected_doc = [
				'key' => $binding_key,
				'title' => $title,
				'page_key' => $page_key,
				'matching' => [
					'url_masks' => $url_masks,
					'exclude_masks' => $exclude_masks,
					'route_params' => $route_params,
					'require_https' => $require_https
				],
				'fallback' => ['strategy' => $fallback_strategy],
				'rules' => $rules,
				'schema_version' => '1.0'
			];
		}

		$items = $model->getBindingOptionsIndex(200);

		$base_url = href_to_abs('admin', 'controllers', ['edit', $this->controller->root_url, 'bindings']);
		foreach ($items as &$item) {
			$key = (string) ($item['binding_key'] ?? '');
			$item['edit_url'] = $base_url . '?key=' . urlencode($key);
		}
		unset($item);

		$examples = $this->buildExampleBindingOptions();

		return $this->cms_template->render('backend/bindings', [
			'menu' => $this->controller->getBackendMenu(),
			'items' => $items,
			'selected_key' => $selected_key,
			'doc' => $selected_doc,
			'errors' => $errors,
			'examples' => $examples,
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

	protected function buildExampleBindingOptions() {
		$base_rules = [
			'allow_structural_overlay' => true,
			'allow_dynamic_blocks' => true,
			'allow_custom_css' => false
		];

		$base_fallback = [
			'strategy' => 'theme',
			'on_missing_page' => 'theme',
			'on_missing_adapter' => 'theme',
			'on_missing_preset' => 'binding_default'
		];

		return [
			[
				'binding_key' => 'page.homepage',
				'document' => [
					'schema_version' => '1.0',
					'key' => 'page.homepage',
					'title' => 'Главная страница',
					'page_key' => 'homepage',
					'matching' => [
						'url_masks' => [],
						'exclude_masks' => [],
						'route_params' => [
							'ctrl' => '',
							'action' => 'index'
						],
						'require_https' => false
					],
					'fallback' => $base_fallback,
					'rules' => $base_rules
				]
			],
			[
				'binding_key' => 'page.content_list',
				'document' => [
					'schema_version' => '1.0',
					'key' => 'page.content_list',
					'title' => 'Список контента (content/index)',
					'page_key' => 'content-list',
					'matching' => [
						'url_masks' => [],
						'exclude_masks' => [],
						'route_params' => [
							'ctrl' => 'content',
							'action' => 'index'
						],
						'require_https' => false
					],
					'fallback' => $base_fallback,
					'rules' => $base_rules
				]
			],
			[
				'binding_key' => 'page.content_item',
				'document' => [
					'schema_version' => '1.0',
					'key' => 'page.content_item',
					'title' => 'Запись контента (content/item)',
					'page_key' => 'content-item',
					'matching' => [
						'url_masks' => [],
						'exclude_masks' => [],
						'route_params' => [
							'ctrl' => 'content',
							'action' => 'item'
						],
						'require_https' => false
					],
					'fallback' => $base_fallback,
					'rules' => $base_rules
				]
			],
			[
				'binding_key' => 'overlay.content_category.board',
				'document' => [
					'schema_version' => '1.0',
					'key' => 'overlay.content_category.board',
					'title' => 'Категории объявлений (board)',
					'page_key' => 'ads-category',
					'matching' => [
						'url_masks' => [],
						'exclude_masks' => [],
						'route_params' => [
							'overlay' => 'content_category',
							'ctype' => 'board'
						],
						'require_https' => false
					],
					'fallback' => $base_fallback,
					'rules' => $base_rules
				]
			],
			[
				'binding_key' => 'overlay.user_profile.default',
				'document' => [
					'schema_version' => '1.0',
					'key' => 'overlay.user_profile.default',
					'title' => 'Профиль пользователя (обложка)',
					'page_key' => 'profile-cover',
					'matching' => [
						'url_masks' => [],
						'exclude_masks' => [],
						'route_params' => [
							'overlay' => 'user_profile'
						],
						'require_https' => false
					],
					'fallback' => $base_fallback,
					'rules' => $base_rules
				]
			]
		];
	}
}
