<?php

class actionNordicbuilderInstallDemo extends cmsAction {

	public function run() {

		if (!$this->request->isAjax() || !cmsUser::isAdmin()) {
			return cmsCore::error404();
		}

		$csrf_token = (string) $this->request->get('csrf_token', '');
		if (!cmsForm::validateCSRFToken($csrf_token)) {
			return $this->cms_template->renderJSON([
				'error' => true,
				'message' => 'Некорректный CSRF token.'
			]);
		}

		$mode = trim((string) $this->request->get('mode', 'quick'));
		if ($mode === '') {
			$mode = 'quick';
		}

		if ($mode !== 'quick') {
			return $this->cms_template->renderJSON([
				'error' => true,
				'message' => 'На этапе 1 поддерживается только режим Quick Demo.',
				'mode' => $mode
			]);
		}

		$model = cmsCore::getModel('nordicbuilder');
		if (!$model || !method_exists($model, 'installQuickDemo')) {
			return $this->cms_template->renderJSON([
				'error' => true,
				'message' => 'Demo installer service is unavailable.'
			]);
		}

		$result = $model->installQuickDemo((int) ($this->cms_user->id ?? 0));
		$is_valid = !empty($result['is_valid']);
		$errors = (array) ($result['errors'] ?? []);

		return $this->cms_template->renderJSON([
			'error' => !$is_valid,
			'mode' => 'quick',
			'message' => $is_valid ? 'Quick Demo установлен.' : ('Quick Demo установлен с ошибками: ' . implode('; ', array_map('strval', $errors))),
			'result' => $result
		]);
	}
}
