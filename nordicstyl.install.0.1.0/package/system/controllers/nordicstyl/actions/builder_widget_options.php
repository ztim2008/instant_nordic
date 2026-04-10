<?php

class actionNordicstylBuilderWidgetOptions extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) {
            return cmsCore::error404();
        }

        if (!cmsUser::isLogged() || !cmsUser::isAllowed('admin', 'manage_nordicstyl_picker')) {
            return cmsCore::error404();
        }

        $csrfToken = (string)$this->request->get('csrf_token', '');
        if (!cmsForm::validateCSRFToken($csrfToken)) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Некорректный CSRF token.'
            ]);
        }

        $widgetId = (int)$this->request->get('widget_id', 0);
        if ($widgetId < 1) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Widget not found'
            ]);
        }

        $backendWidgetsModel = cmsCore::getModel('backend_widgets', '_', false);
        if (!$backendWidgetsModel || !method_exists($backendWidgetsModel, 'getWidget')) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Backend widgets model недоступна.'
            ]);
        }

        $templateName = trim((string)$this->request->get('template', ''));
        $templates = cmsCore::getTemplates();
        if ($templateName === '' || !in_array($templateName, $templates, true)) {
            $templateName = cmsConfig::get('template');
        }

        $widget = $backendWidgetsModel->getWidget($widgetId);
        if (!$widget) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Widget not found'
            ]);
        }

        $bindConfig = $this->request->get('options', []);
        if (!is_array($bindConfig)) {
            $decoded = json_decode((string)$bindConfig, true);
            $bindConfig = is_array($decoded) ? $decoded : [];
        }

        $widgetData = [
            'id' => $widget['id'],
            'name' => $widget['name'],
            'controller' => $widget['controller'],
            'options' => $bindConfig
        ];

        try {
            $widgetObject = cmsCore::getWidgetObject($widgetData);
            $admin = cmsCore::getController('admin', new cmsRequest([], cmsRequest::CTX_INTERNAL));
            $form = $admin->getWidgetOptionsForm(
                $widget['name'],
                $widget['controller'],
                $bindConfig,
                $templateName,
                $widgetObject->isAllowCacheableOption()
            );
        } catch (Throwable $exception) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Ошибка загрузки настроек виджета: ' . $exception->getMessage()
            ]);
        }

        $widgetEventName = 'widget_' . ($widget['controller'] ? $widget['controller'] . '_' : '') . $widget['name'] . '_form';

        try {
            list($form, $widgetData, $widgetObject, $templateName) = cmsEventsManager::hook(
                ['widget_form', $widgetEventName],
                [$form, $widgetData, $widgetObject, $templateName],
                null,
                $this->request
            );
        } catch (Throwable $exception) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Ошибка применения расширений формы виджета: ' . $exception->getMessage()
            ]);
        }

        try {
            ob_start();
            $this->cms_template->renderForm($form, $bindConfig, [
                'only_fields' => true,
                'form_tpl_file' => 'form_fields',
                'form_id' => 'nordicstyl-widget-options-' . $widgetId
            ]);
            $html = ob_get_clean();
        } catch (Throwable $exception) {
            if (ob_get_level()) {
                ob_end_clean();
            }

            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Ошибка рендера формы настроек виджета: ' . $exception->getMessage()
            ]);
        }

        return $this->cms_template->renderJSON([
            'error' => false,
            'widget' => $widget,
            'html' => $html,
            'template' => $templateName
        ]);
    }
}