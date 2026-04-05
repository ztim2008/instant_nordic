<?php

require_once __DIR__ . '/overlay_integration_trait.php';

class onLandingbuilderProcessRenderUsersProfileView extends cmsAction {

	use landingbuilderOverlayIntegrationTrait;

	public function run($_data) {

		list($tpl_file, $data, $request) = $_data;

		if (!$request || $request->isInternal()) {
			return $_data;
		}

		$profile = is_array($data['profile'] ?? null) ? $data['profile'] : [];

		$integration = $this->model->getUserProfileOverlay($profile, cmsUser::isAdmin());
		if (!$integration) {
			return $_data;
		}

		$this->applyOverlayIntegration($integration, [
			[
				'zone'  => 'hero',
				'block' => 'users_profile_view_top'
			],
			[
				'zone'  => 'after_content',
				'block' => 'users_profile_view_bottom'
			]
		]);

		return $_data;
	}
}