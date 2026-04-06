<?php

class actionNordicbuilderWidgetsCatalog extends cmsAction {

	public function run() {

		if (!$this->request->isAjax()) {
			return cmsCore::error404();
		}

		try {
			$bridge_model = cmsCore::getModel('landingbuilder');
			$widgets = $bridge_model->getAvailableSystemWidgets();
		} catch (Throwable $exception) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Не удалось загрузить список виджетов: ' . $exception->getMessage()
			]);
		}

		return $this->cms_template->renderJSON([
			'error'   => false,
			'widgets' => $widgets
		]);
	}
}