<?php

class actionNordicstylBuilderRestore extends cmsAction {

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
        $targetUri = trim((string)$this->request->get('uri', '/'));
        $sourceTemplate = trim((string)$this->request->get('source_template', ''));
        $revisionId = (int)$this->request->get('revision_id', 0);

        $model = cmsCore::getModel('nordicstyl', '_', false);
        if (!$model || !method_exists($model, 'restoreLayoutRevision')) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Model nordicstyl недоступна для восстановления ревизии.'
            ]);
        }

        $result = $model->restoreLayoutRevision(
            $templateName,
            $targetUri,
            $revisionId,
            $sourceTemplate,
            (int)cmsUser::get('id', 0)
        );

        return $this->cms_template->renderJSON([
            'error' => empty($result['ok']),
            'message' => (string)($result['message'] ?? 'Не удалось восстановить ревизию.'),
            'rows' => (int)($result['rows'] ?? 0),
            'columns' => (int)($result['columns'] ?? 0),
            'widgets' => (int)($result['widgets'] ?? 0)
        ]);
    }
}