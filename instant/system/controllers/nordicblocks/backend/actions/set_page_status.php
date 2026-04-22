<?php

class actionNordicblocksSetPageStatus extends cmsAction {

    public function run() {
        if (!$this->cms_user->is_admin) {
            $this->jsonResponse(['ok' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        $raw    = file_get_contents('php://input');
        $data   = $raw ? (array) json_decode($raw, true) : [];
        $id     = (int) ($data['id']     ?? $this->request->get('id',     0));
        $status = (string) ($data['status'] ?? $this->request->get('status', 'draft'));

        if (!$id) {
            $this->jsonResponse(['ok' => false, 'error' => 'No id']);
            return;
        }

        $this->model->setPageStatus($id, $status);
        $this->jsonResponse(['ok' => true]);
    }

    private function jsonResponse(array $data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
