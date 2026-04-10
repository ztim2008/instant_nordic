<?php

class actionNordicstylBuilderReset extends cmsAction {

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

        $templateName = trim((string)$this->request->get('template', ''));
        $sourceTemplate = trim((string)$this->request->get('source_template', ''));

        if ($templateName === '' || $sourceTemplate === '') {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Не хватает данных для возврата к default-схеме.'
            ]);
        }

        $model = cmsCore::getModel('nordicstyl', '_', false);
        if (!$model || !method_exists($model, 'resetTemplateToDefault')) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Model nordicstyl недоступна для reset-to-default.'
            ]);
        }

        $result = $model->resetTemplateToDefault($templateName, $sourceTemplate);

        return $this->cms_template->renderJSON([
            'error' => empty($result['ok']),
            'message' => (string)($result['message'] ?? 'Не удалось вернуть шаблон к default-схеме.'),
            'rows' => (int)($result['rows'] ?? 0),
            'columns' => (int)($result['columns'] ?? 0),
            'widgets' => (int)($result['widgets'] ?? 0)
        ]);
    }
}