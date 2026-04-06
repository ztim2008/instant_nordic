<?php

class actionNordicbuilderWidgetOptions extends cmsAction {

	public function run() {

		if (!$this->request->isAjax()) {
			return cmsCore::error404();
		}

		$widget_id = $this->request->get('widget_id', 0);
		if (!$widget_id) {
			return $this->cms_template->renderJSON(['error' => true, 'message' => 'Widget not found']);
		}

		$bridge_model = cmsCore::getModel('landingbuilder');
		$bridge_options = (array) cmsController::loadOptions('landingbuilder');

		$template_name = $this->request->get('template', '');
		$templates = cmsCore::getTemplates();
		if (!$template_name || !in_array($template_name, $templates, true)) {
			$template_name = !empty($bridge_options['preview_template']) ? $bridge_options['preview_template'] : cmsConfig::get('template');
		}

		$widget = $bridge_model->getSystemWidgetById($widget_id);
		if (!$widget) {
			return $this->cms_template->renderJSON(['error' => true, 'message' => 'Widget not found']);
		}

		$options = $this->request->get('options', []);
		if (!is_array($options)) {
			$decoded = json_decode((string) $options, true);
			$options = is_array($decoded) ? $decoded : [];
		}

		$widget_data = [
			'id'         => $widget['id'],
			'name'       => $widget['name'],
			'controller' => $widget['controller'],
			'options'    => $options
		];

		try {
			$widget_object = cmsCore::getWidgetObject($widget_data);
			$admin = cmsCore::getController('admin', new cmsRequest([], cmsRequest::CTX_INTERNAL));
			$form = $admin->getWidgetOptionsForm(
				$widget['name'],
				$widget['controller'],
				$options,
				$template_name,
				$widget_object->isAllowCacheableOption()
			);
		} catch (Throwable $exception) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Ошибка загрузки настроек виджета: ' . $exception->getMessage()
			]);
		}

		$widget_event_name = 'widget_' . ($widget['controller'] ? $widget['controller'] . '_' : '') . $widget['name'] . '_form';

		try {
			list($form, $widget_data, $widget_object, $template_name) = cmsEventsManager::hook(
				['widget_form', $widget_event_name],
				[$form, $widget_data, $widget_object, $template_name],
				null,
				$this->request
			);
		} catch (Throwable $exception) {
			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Ошибка применения расширений формы виджета: ' . $exception->getMessage()
			]);
		}

		try {
			ob_start();

			$this->cms_template->renderForm($form, $options, [
				'only_fields'   => true,
				'form_tpl_file' => 'form_fields',
				'form_id'       => 'nordicbuilder-widget-options-' . $widget_id
			]);
			$html = ob_get_clean();
		} catch (Throwable $exception) {
			if (ob_get_level()) {
				ob_end_clean();
			}

			return $this->cms_template->renderJSON([
				'error'   => true,
				'message' => 'Ошибка рендера формы настроек виджета: ' . $exception->getMessage()
			]);
		}

		return $this->cms_template->renderJSON([
			'error'    => false,
			'widget'   => $widget,
			'html'     => $html,
			'template' => $template_name
		]);
	}
}