<?php

class actionNordicblocksMediaUpload extends cmsAction {

    public function run() {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->cms_user->is_admin) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }

        if (!$this->request->isMethod('POST') || empty($_FILES['file'])) {
            echo json_encode(['ok' => false, 'error' => 'bad_request'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $preset_name = trim((string) $this->request->get('preset', 'original'));
        if ($preset_name === '') {
            $preset_name = 'original';
        }

        $this->cms_uploader
            ->enableRemoteUpload()
            ->setAllowedMime([
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/svg+xml'
            ]);

        $result = $this->cms_uploader->upload('file');
        if (empty($result['success'])) {
            echo json_encode([
                'ok'    => false,
                'error' => !empty($result['error']) ? $result['error'] : 'upload_failed'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $uploaded_name = pathinfo((string) ($result['name'] ?? 'image'), PATHINFO_FILENAME);
        $media         = $this->model->normalizeImageFieldValue([
            'original_path' => (string) ($result['url'] ?? ''),
            'preset'        => $preset_name,
            'alt'           => str_replace(['-', '_'], ' ', $uploaded_name),
        ]);

        if (!$media) {
            if (!empty($result['path'])) {
                files_delete_file($result['path'], 2);
            }
            echo json_encode(['ok' => false, 'error' => 'media_contract_failed'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'ok'     => true,
            'url'    => $media['display'],
            'preset' => $media['preset'],
            'media'  => $media,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
