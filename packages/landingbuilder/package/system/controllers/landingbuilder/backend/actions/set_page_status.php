<?php

class actionLandingbuilderSetPageStatus extends cmsAction {

    public function run() {

        if (!$this->request->isAjax() || !cmsUser::isAdmin()) {
            return cmsCore::error404();
        }

        $csrf_token = (string) $this->request->get('csrf_token', '');
        if (!cmsForm::validateCSRFToken($csrf_token)) {
            return $this->cms_template->renderJSON([
                'error'   => true,
                'message' => 'Некорректный CSRF token.'
            ]);
        }

        $page_key = (string) $this->request->get('key', '');
        $status = (string) $this->request->get('status', 'draft');

        $page = $this->model->setPageStatusByKey($page_key, $status, $this->cms_user->id);

        if (!$page) {
            return $this->cms_template->renderJSON([
                'error'   => true,
                'message' => 'Не удалось обновить статус страницы. Проверьте ключ страницы и корректность статуса.'
            ]);
        }

        return $this->cms_template->renderJSON([
            'error' => false,
            'page'  => [
                'id'         => $page['id'],
                'key'        => $page['key'],
                'title'      => $page['title'],
                'status'     => $page['status'],
                'mode'       => $page['mode'],
                'updated_at' => $page['updated_at']
            ]
        ]);
    }
}
