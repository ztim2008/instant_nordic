<?php

class actionNordicbuilderCreatePage extends cmsAction {

	public function run() {

		if (!$this->request->isAjax()) {
			return cmsCore::error404();
		}

		$csrf_token = (string) $this->request->get('csrf_token', '');
		if (!cmsForm::validateCSRFToken($csrf_token)) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Некорректный CSRF token.'
			]);
		}

		$bridge_model = cmsCore::getModel('landingbuilder');
		$page = $bridge_model->createPage([
			'key'      => $this->request->get('key', ''),
			'title'    => $this->request->get('title', ''),
			'mode'     => $this->request->get('mode', 'instant_content_body'),
			'status'   => $this->request->get('status', 'draft'),
			'template' => $this->request->get('template', 'nordic'),
			'adapter_key' => $this->request->get('adapter_key', ''),
			'disable_starter_seed' => (bool) $this->request->get('disable_starter_seed', false),
			'inherit_global_sections' => (bool) $this->request->get('inherit_global_sections', false),
			'use_as_global_sections_source' => (bool) $this->request->get('use_as_global_sections_source', false)
		], $this->cms_user->id);

		if (!$page) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Не удалось создать страницу. Проверь уникальность ключа и установленный SQL-пакет.'
			]);
		}

		return $this->cms_template->renderJSON([
			'error' => false,
			'page'  => [
				'id'         => $page['id'],
				'key'        => $page['key'],
				'title'      => $page['title'],
				'status'     => $page['status'],
				'mode'       => $page['mode'],
				'updated_at' => $page['updated_at'],
				'canvas_url' => href_to_abs($this->controller->root_url, 'canvas', $page['key'])
			]
		]);
	}
}