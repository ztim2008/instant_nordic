<?php

class actionNordicbuilderVersionRestore extends cmsAction {

	public function run() {

		try {

			if (!$this->request->isAjax()) {
				return cmsCore::error404();
			}

			$version_id = $this->request->get('version_id', 0);
			if (!$version_id) {
				return $this->cms_template->renderJSON(['error' => true, 'message' => 'Version id is required']);
			}

			$bridge_model = cmsCore::getModel('landingbuilder');
			$page = $bridge_model->restorePageVersion($version_id, $this->cms_user->id);
			if (!$page) {
				return $this->cms_template->renderJSON(['error' => true, 'message' => 'Restore failed']);
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
				'schema'       => $page['schema'],
				'widget_nodes' => $page['widget_nodes'],
				'versions'     => $bridge_model->getPageVersionsByKey($page['key'])
			]);
		} catch (Throwable $exception) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Не удалось восстановить версию. ' . $exception->getMessage()
			]);
		}
	}
}