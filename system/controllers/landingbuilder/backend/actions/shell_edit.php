<?php

class actionLandingbuilderShellEdit extends cmsAction {

	public function run($variant_key = null) {

		$variant = $this->model->getShellVariantByKey($variant_key);
		if (!$variant) {
			return cmsCore::error404();
		}

		$catalog = $this->model->getShellVariantChoiceCatalog();
		$form = $this->getForm('shell_variant', [$catalog]);

		if ($this->request->has('submit')) {
			$data = $form->parse($this->request, true);
			$errors = $form->validate($this, $data);

			if (!$errors) {
				$variant = $this->model->saveShellVariant($variant['key'], $data);

				cmsUser::addSessionMessage(LANG_CP_SAVE_SUCCESS, 'success');

				return $this->redirectToAction('shell_edit', [$variant['key']]);
			}

			$variant = $this->model->prepareShellVariant($variant['key'], $data);

			cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
		}

		return $this->cms_template->render('backend/shell_variant', [
			'menu'         => $this->controller->getBackendMenu(),
			'variant'      => $variant,
			'preview_rows' => $this->model->getShellPreviewRows($variant),
			'catalog'      => $catalog,
			'form'         => $form,
			'errors'       => $errors ?? false
		]);
	}
}