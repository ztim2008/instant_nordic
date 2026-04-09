<?php

class actionNordicbuilderSetGlobalSectionsSource extends cmsAction {

	public function run() {

		if (!$this->request->isAjax() || !cmsUser::isAdmin()) {
			return cmsCore::error404();
		}

		$csrf_token = (string) $this->request->get('csrf_token', '');
		if (!cmsForm::validateCSRFToken($csrf_token)) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Некорректный CSRF token.'
			]);
		}

		$page_key = trim((string) $this->request->get('global_sections_source_page_key', ''));
		if ($page_key === 'none') {
			$page_key = '';
		}
		$page_key = $this->sanitizePageKey($page_key);

		$bridge_model = cmsCore::getModel('landingbuilder');
		if (!$bridge_model) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Landingbuilder model is unavailable.'
			]);
		}

		$source_page = null;
		if ($page_key !== '') {
			$source_page = $bridge_model->getPageByKey($page_key);
			if (!$source_page) {
				return $this->cms_template->renderJSON([
					'error'   => true,
					'message' => 'Страница-источник не найдена. Выберите страницу из списка.'
				]);
			}
		}

		$options = (array) cmsController::loadOptions('landingbuilder');
		$options['global_sections_source_page_key'] = $page_key;
		cmsController::saveOptions('landingbuilder', $options);

		return $this->cms_template->renderJSON([
			'error' => false,
			'global_sections_source_page_key' => $page_key,
			'global_sections_source_title' => $source_page ? (string) ($source_page['title'] ?? $page_key) : '',
			'mode' => $page_key !== '' ? 'explicit' : 'auto'
		]);
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
}
