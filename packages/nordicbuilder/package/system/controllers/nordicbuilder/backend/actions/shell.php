<?php

class actionNordicbuilderShell extends cmsAction {

	public function run() {

		$lb_model = cmsCore::getModel('landingbuilder');

		$variants = $lb_model->getShellVariantsForAdmin();

		foreach ($variants as &$variant) {
			$variant['edit_url'] = href_to($this->controller->root_url, 'shell_edit', [$variant['key']]);
		}

		return $this->cms_template->render('backend/shell', [
			'menu'     => $this->controller->getBackendMenu(),
			'variants' => $variants,
			'catalog'  => $lb_model->getShellVariantChoiceCatalog()
		]);
	}
}
