<?php

class actionNordicblocksMediaList extends cmsAction {

    public function run() {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->cms_user->is_admin) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }

        try {
            $files = $this->model->getMediaLibraryItems(200);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'scan_failed'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['ok' => true, 'files' => $files], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
