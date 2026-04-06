<?php

class actionLandingbuilderDesign extends cmsAction {

	public function run() {

		$catalog = $this->model->getThemeOptionCatalog();
		$form_catalog = $this->model->getThemeFormCatalog();
		$theme = $this->model->getSiteThemeDefaults($this->options);
		$form = $this->getForm('design', [$form_catalog]);

		if ($this->request->has('submit')) {
			$data = $form->parse($this->request, true);
			$errors = $form->validate($this, $data);

			if (!$errors) {
				$theme = $this->model->saveSiteThemeSettings($data);
				cmsUser::addSessionMessage(LANG_CP_SAVE_SUCCESS, 'success');
				return $this->redirectToAction('design');
			}

			$theme = $this->model->getSiteThemeDefaults(array_merge((array) $this->options, [
				'default_template_preset' => $data['template_preset'] ?? '',
				'default_global_style_preset' => $data['global_style_preset'] ?? '',
				'default_color_preset' => $data['color_preset'] ?? '',
				'default_typography_preset' => $data['typography_preset'] ?? '',
				'default_container_preset' => $data['container_preset'] ?? '',
				'default_button_preset' => $data['button_preset'] ?? '',
				'default_card_preset' => $data['card_preset'] ?? '',
				'default_section_spacing' => $data['section_spacing'] ?? ''
			]));

			cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
		}

		return $this->cms_template->render('backend/design', [
			'menu'   => $this->controller->getBackendMenu(),
			'theme'  => $theme,
			'screen' => $this->model->getDesignSystemScreen($theme, $catalog),
			'form'   => $form,
			'errors' => $errors ?? false
		]);
	}
}