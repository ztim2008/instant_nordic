<?php

class actionNordicbuilderPages extends cmsAction {

	public function run() {
		$bridge_model = cmsCore::getModel('landingbuilder');
		$pages = $bridge_model->getPagesForAdmin();

		foreach ($pages as &$page) {
			$page['canvas_url'] = href_to_abs($this->controller->root_url, 'canvas', $page['key']);
			$page['view_url'] = href_to('nordicbuilder', 'view', [$page['key']]);
		}

		return $this->cms_template->render('backend/pages', [
			'menu'               => $this->controller->getBackendMenu(),
			'pages'              => $pages,
			'is_schema_installed'=> $bridge_model->hasInstalledSchema(),
			'create_page_url'    => href_to($this->controller->root_url, 'create_page'),
			'create_binding_url' => href_to($this->controller->root_url, 'create_binding'),
			'delete_page_url'    => href_to($this->controller->root_url, 'delete_page'),
			'set_status_url'     => href_to($this->controller->root_url, 'set_page_status')
		]);
	}
}