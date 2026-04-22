<?php

class actionNordicblocksBlockDelete extends cmsAction {

    public function run() {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->request->isMethod('POST')) {
            echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
            exit;
        }

        if (!$this->cms_user->is_admin) {
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }

        $csrf_token = (string) $this->request->get('csrf_token', '');
        if (!cmsForm::validateCSRFToken($csrf_token)) {
            echo json_encode(['ok' => false, 'error' => 'invalid_csrf']);
            exit;
        }

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

        $delete_result = $this->model->deleteBlock($id);

        echo json_encode([
            'ok'                    => true,
            'removed_widget_binds'  => (int) ($delete_result['removed_widget_binds'] ?? 0)
        ]);
        exit;
    }
}
