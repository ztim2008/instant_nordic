<?php

require_once __DIR__ . '/overlay_integration_trait.php';

class onLandingbuilderProcessRenderContentCategoryView extends cmsAction {

	use landingbuilderOverlayIntegrationTrait;

	public function run($_data) {

		list($tpl_file, $data, $request) = $_data;

		if (!$request || $request->isInternal()) {
			return $_data;
		}

		$ctype = is_array($data['ctype'] ?? null) ? $data['ctype'] : [];
		$category = is_array($data['category'] ?? null) ? $data['category'] : [];

		$integration = $this->model->getContentCategoryOverlay($ctype, $category, cmsUser::isAdmin());
		if (!$integration) {
			return $_data;
		}

		$this->applyOverlayIntegration($integration, [
			[
				'zone'    => 'before_content',
				'block'   => 'before_content_items_list_html',
				'prepend' => true
			],
			[
				'zone'  => 'after_content',
				'block' => 'after_content_items_list_html'
			]
		]);

		return $_data;
	}
}