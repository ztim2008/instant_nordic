<?php

class actionNordicblocksCreatePage extends cmsAction {

    public function run() {
        $errors = [];

        if ($this->request->has('submit')) {
            $title = trim((string) $this->request->get('title', ''));
            $key   = trim((string) $this->request->get('key',   ''));

            // Валидация
            if ($title === '') {
                $errors['title'] = 'Укажите название страницы';
            }
            if ($key === '') {
                $errors['key'] = 'Укажите URL-ключ страницы';
            } elseif (!preg_match('/^[a-z0-9\-_]+$/', $key)) {
                $errors['key'] = 'Только латиница, цифры, дефис и подчёркивание';
            } elseif ($this->model->pageKeyExists($key)) {
                $errors['key'] = 'Страница с таким ключом уже существует';
            }

            if (!$errors) {
                $id = $this->model->createPage($key, $title);
                cmsUser::addSessionMessage('Страница создана', 'success');
                return $this->redirect(
                    href_to('admin', 'controllers', ['edit', $this->controller->root_url, 'editor', $id])
                );
            }
        }

        return $this->cms_template->render('backend/create_page', [
            'menu'   => $this->controller->getBackendMenu(),
            'errors' => $errors,
        ]);
    }
}
