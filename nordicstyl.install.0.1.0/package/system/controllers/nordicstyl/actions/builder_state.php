<?php

class actionNordicstylBuilderState extends cmsAction {

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
        $payload = trim((string)$this->request->get('layout_state', ''));

        if ($templateName === '' || $payload === '') {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Не хватает данных для сохранения builder state.'
            ]);
        }

        $layoutState = json_decode($payload, true);
        if (!is_array($layoutState)) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'layout_state должен быть валидным JSON.'
            ]);
        }

        $model = cmsCore::getModel('nordicstyl', '_', false);
        if (!$model || !method_exists($model, 'saveLayoutState')) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Model nordicstyl недоступна для сохранения builder state.'
            ]);
        }

        $saved = $model->saveLayoutState($templateName, $targetUri, $layoutState);

        return $this->cms_template->renderJSON([
            'error' => !$saved,
            'message' => $saved ? 'Builder state сохранен.' : 'Не удалось сохранить builder state.'
        ]);
    }
}