<?php

class actionNordicbuilderVersions extends cmsAction {

	public function run() {

		if (!$this->request->isAjax()) {
			return cmsCore::error404();
		}

		$page_key = $this->request->get('page_key', '');
		if (!$page_key) {
			return $this->cms_template->renderJSON(['error' => true, 'message' => 'Page key is required']);
		}

		$bridge_model = cmsCore::getModel('landingbuilder');

		return $this->cms_template->renderJSON([
			'error'    => false,
			'versions' => $bridge_model->getPageVersionsByKey($page_key)
		]);
	}
}