<?php

class actionNordicbuilderRemoveDemo extends cmsAction {

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

		$model = cmsCore::getModel('nordicbuilder');
		if (!$model || !method_exists($model, 'removeQuickDemo')) {
			return $this->cms_template->renderJSON([
				'error' => true,
				'message' => 'Demo remove service is unavailable.'
			]);
		}

		$result = $model->removeQuickDemo((int) ($this->cms_user->id ?? 0));
		$is_valid = !empty($result['is_valid']);
		$errors = (array) ($result['errors'] ?? []);

		return $this->cms_template->renderJSON([
			'error' => !$is_valid,
			'mode' => 'quick',
			'message' => $is_valid ? 'Quick Demo удален.' : ('Quick Demo удален с ошибками: ' . implode('; ', array_map('strval', $errors))),
			'result' => $result
		]);
	}
}
