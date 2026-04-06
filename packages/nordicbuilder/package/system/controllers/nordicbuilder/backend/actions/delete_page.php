<?php

class actionNordicbuilderDeletePage extends cmsAction {

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

		$page_key = (string) $this->request->get('key', '');
		$page_key = trim($page_key);
		if ($page_key === '') {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Не задан key.'
			]);
		}

		$bridge_model = cmsCore::getModel('landingbuilder');
		if (!$bridge_model || !method_exists($bridge_model, 'deletePageByKey')) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Удаление страницы не поддерживается в текущей версии.'
			]);
		}

		$deleted = (bool) $bridge_model->deletePageByKey($page_key);
		if (!$deleted) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Не удалось удалить страницу. Возможно, ключ не найден.'
			]);
		}

		$model = cmsCore::getModel('nordicbuilder');
		$bindings_deleted = false;
		$doc_deleted = false;

		if ($model) {
			if (method_exists($model, 'deleteBindingOptionsByPageKey')) {
				$bindings_deleted = (bool) $model->deleteBindingOptionsByPageKey($page_key);
			}
			if (method_exists($model, 'deletePageDocumentByKey')) {
				$doc_deleted = (bool) $model->deletePageDocumentByKey($page_key);
			}
		}

		return $this->cms_template->renderJSON([
			'error' => false,
			'page'  => [
				'key' => $page_key
			],
			'cleanup' => [
				'bindings_deleted' => $bindings_deleted,
				'document_deleted' => $doc_deleted
			]
		]);
	}
}
