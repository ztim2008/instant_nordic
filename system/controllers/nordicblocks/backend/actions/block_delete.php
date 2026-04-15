<?php

class actionNordicblocksBlockDelete extends cmsAction {

    public function run() {
        header('Content-Type: application/json; charset=utf-8');

        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $id   = isset($data['id']) ? (int) $data['id'] : 0;

        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'bad_id']);
            exit;
        }

        $block = $this->model->getBlockById($id);
        if (!$block) {
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            exit;
        }

        $this->model->deleteBlock($id);
        echo json_encode(['ok' => true]);
        exit;
    }
}
