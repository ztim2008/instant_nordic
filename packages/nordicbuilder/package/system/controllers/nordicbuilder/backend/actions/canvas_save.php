<?php

class actionNordicbuilderCanvasSave extends cmsAction {

	public function run() {

		try {

			if (!$this->request->isAjax()) {
				return cmsCore::error404();
			}

			$page_key = $this->request->get('page_key', '');
			if (!$page_key) {
				return $this->cms_template->renderJSON(['error' => true, 'message' => 'Page key is required']);
			}

			$schema_payload = $this->request->get('schema', '');
			$schema = is_array($schema_payload) ? $schema_payload : json_decode((string) $schema_payload, true);

			if (!is_array($schema)) {
				return $this->cms_template->renderJSON(['error' => true, 'message' => 'Schema payload is invalid']);
			}

			$bridge_model = cmsCore::getModel('landingbuilder');
			$version_note = $this->request->get('version_note', '');

			$page = $bridge_model->savePageSchema($page_key, $schema, $this->cms_user->id, $version_note);
			if (!$page) {
				return $this->cms_template->renderJSON([
					'error'   => true,
					'message' => 'Canvas save failed. Проверь builder storage layer nordicbuilder.'
				]);
			}

			return $this->cms_template->renderJSON([
				'error'        => false,
				'page'         => [
					'id'         => $page['id'],
					'key'        => $page['key'],
					'title'      => $page['title'],
					'status'     => $page['status'],
					'mode'       => $page['mode'],
					'updated_at' => $page['updated_at']
				],
				'widget_nodes' => $page['widget_nodes']
			]);
		} catch (Throwable $exception) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Не удалось сохранить страницу. ' . $exception->getMessage()
			]);
		}
	}
}