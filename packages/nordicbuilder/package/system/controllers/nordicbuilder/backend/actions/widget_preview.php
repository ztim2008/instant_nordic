<?php

class actionNordicbuilderWidgetPreview extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) {
            return cmsCore::error404();
        }

        $widget_id = (int) $this->request->get('widget_id', 0);
        if (!$widget_id) {
            return $this->cms_template->renderJSON(['error' => true, 'message' => 'Widget not found']);
        }

        $bridge_model = cmsCore::getModel('landingbuilder');
        $bridge_options = (array) cmsController::loadOptions('landingbuilder');

        $template_name = (string) $this->request->get('template', '');
        $templates = cmsCore::getTemplates();
        if (!$template_name || !in_array($template_name, $templates, true)) {
            $template_name = !empty($bridge_options['preview_template']) ? $bridge_options['preview_template'] : cmsConfig::get('template');
        }

        $template = cmsTemplate::getInstance();
        $original_template_name = method_exists($template, 'getName') ? $template->getName() : '';

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
            if ($template_name && $original_template_name && $template_name !== $original_template_name) {
                $template->setBaseTemplate($template_name);
            }

            if ((($widget['controller'] ?? '') === '' || ($widget['controller'] ?? '') === 'core') && ($widget['name'] ?? '') === 'menu' && empty($options['menu'])) {
                return $this->cms_template->renderJSON([
                    'error'    => false,
                    'widget'   => $widget,
                    'html'     => '<div class="text-muted small">Для предпросмотра виджета меню выберите меню в его настройках справа.</div>',
                    'template' => $template_name
                ]);
            }

            try {
                $widget_object = cmsCore::getWidgetObject($widget_data);
            } catch (Throwable $exception) {
                return $this->cms_template->renderJSON([
                    'error'   => true,
                    'message' => 'Не удалось инициализировать виджет для предпросмотра.'
                ]);
            }

            $result = false;

            try {
                $result = call_user_func_array([$widget_object, 'run'], []);
            } catch (Throwable $exception) {
                return $this->cms_template->renderJSON([
                    'error'   => true,
                    'message' => 'Ошибка выполнения виджета: ' . $exception->getMessage()
                ]);
            }

            if ($result === false || !is_array($result)) {
                return $this->cms_template->renderJSON([
                    'error'   => false,
                    'widget'  => $widget,
                    'html'    => '',
                    'template'=> $template_name
                ]);
            }

            $tpl_path = cmsCore::getWidgetPath($widget_object->name, $widget_object->controller);
            $tpl_file = cmsTemplate::getInstance()->getTemplateFileName($tpl_path . '/' . $widget_object->getTemplate(), true);

            if (!$tpl_file) {
                return $this->cms_template->renderJSON([
                    'error'   => true,
                    'message' => 'Шаблон виджета не найден.'
                ]);
            }

            $device_type = cmsRequest::getDeviceType();
            extract($result);

            ob_start();
            include($tpl_file);
            $html = ob_get_clean();

            return $this->cms_template->renderJSON([
                'error'    => false,
                'widget'   => $widget,
                'html'     => $html,
                'template' => $template_name
            ]);
        } finally {
            if ($original_template_name && $template->getName() !== $original_template_name) {
                $template->setBaseTemplate($original_template_name);
            }
        }
    }
}
