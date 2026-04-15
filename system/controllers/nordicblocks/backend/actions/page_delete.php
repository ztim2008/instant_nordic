<?php

/**
 * Удаление страницы NordicBlocks.
 * POST JSON: { "id": 5 }
 */
class actionNordicblocksPageDelete extends cmsAction {

    public function run() {
        if (!$this->cms_user->is_admin) {
            $this->jsonResponse(['ok' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        $raw = file_get_contents('php://input');
        $data = $raw ? json_decode($raw, true) : null;
        $id = isset($data['id']) ? (int) $data['id'] : 0;

        if (!$id) {
            $this->jsonResponse(['ok' => false, 'error' => 'Invalid ID']);
            return;
        }

        $page = $this->model->getPageById($id);
        if (!$page) {
            $this->jsonResponse(['ok' => false, 'error' => 'Not found']);
            return;
        }

        $this->model->deletePage($id);

        $this->jsonResponse(['ok' => true]);
    }
}
