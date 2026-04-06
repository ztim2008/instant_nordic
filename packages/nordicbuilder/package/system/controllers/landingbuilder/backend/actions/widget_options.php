<?php

class actionLandingbuilderWidgetOptions extends cmsAction {

    public function run() {

        if (!$this->request->isAjax() || !cmsUser::isAdmin()) {
            return cmsCore::error404();
        }

        $widget_id = $this->request->get('widget_id', 0);
        if (!$widget_id) {
            return $this->cms_template->renderJSON(['error' => true, 'message' => 'Widget not found']);
        }

        $template_name = $this->request->get('template', '');
        $templates = cmsCore::getTemplates();
        if (!$template_name || !in_array($template_name, $templates, true)) {
            $template_name = !empty($this->options['preview_template']) ? $this->options['preview_template'] : cmsConfig::get('template');
        }

        $widget = $this->model->getSystemWidgetById($widget_id);
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

        $widget_object = cmsCore::getWidgetObject($widget_data);
        $admin = cmsCore::getController('admin', new cmsRequest([], cmsRequest::CTX_INTERNAL));
        $form = $admin->getWidgetOptionsForm(
            $widget['name'],
            $widget['controller'],
            $options,
            $template_name,
            $widget_object->isAllowCacheableOption()
        );

        $widget_event_name = 'widget_' . ($widget['controller'] ? $widget['controller'] . '_' : '') . $widget['name'] . '_form';

        list($form, $widget_data, $widget_object, $template_name) = cmsEventsManager::hook(
            ['widget_form', $widget_event_name],
            [$form, $widget_data, $widget_object, $template_name],
            null,
            $this->request
        );

        ob_start();

        $this->cms_template->renderForm($form, $options, [
            'only_fields'   => true,
            'form_tpl_file' => 'form_fields',
            'form_id'       => 'landingbuilder-widget-options-' . $widget_id
        ]);

        return $this->cms_template->renderJSON([
            'error'       => false,
            'widget'      => $widget,
            'html'        => ob_get_clean(),
            'template'    => $template_name
        ]);
    }
}