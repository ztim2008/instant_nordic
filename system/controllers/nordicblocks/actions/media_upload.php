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

        $file = $_FILES['file'];

        if (!empty($file['error'])) {
            echo json_encode(['ok' => false, 'error' => 'upload_error'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if (!$tmp || !is_uploaded_file($tmp)) {
            echo json_encode(['ok' => false, 'error' => 'bad_file'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $image_info = @getimagesize($tmp);
        $mime       = $image_info['mime'] ?? '';

        $allowed_mimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
        ];

        if (!$mime || !isset($allowed_mimes[$mime])) {
            echo json_encode(['ok' => false, 'error' => 'unsupported_type'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $upload_path = rtrim((string) cmsConfig::get('upload_path'), '/\\');
        if (!$upload_path) {
            echo json_encode(['ok' => false, 'error' => 'upload_path_missing'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $subdir   = 'nordicblocks/' . date('Y/m');
        $dest_dir = $upload_path . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subdir);

        if (!is_dir($dest_dir) && !@mkdir($dest_dir, 0775, true)) {
            echo json_encode(['ok' => false, 'error' => 'mkdir_failed'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $orig_name = (string) ($file['name'] ?? 'image');
        $base_name = pathinfo($orig_name, PATHINFO_FILENAME);
        $slug      = preg_replace('/[^a-z0-9_-]+/i', '-', $base_name);
        $slug      = trim((string) $slug, '-_');
        if ($slug === '') {
            $slug = 'image';
        }

        $ext      = $allowed_mimes[$mime];
        $filename = $slug . '-' . substr(md5(uniqid('', true)), 0, 10) . '.' . $ext;
        $dest_abs = $dest_dir . DIRECTORY_SEPARATOR . $filename;

        if (!@move_uploaded_file($tmp, $dest_abs)) {
            echo json_encode(['ok' => false, 'error' => 'move_failed'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        @chmod($dest_abs, 0644);

        $url = $this->buildPublicUrl($dest_abs, $upload_path . DIRECTORY_SEPARATOR);

        echo json_encode([
            'ok'  => true,
            'url' => $url,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function buildPublicUrl($absolute_path, $upload_path) {
        $absolute_path = str_replace('\\', '/', (string) $absolute_path);
        $upload_path   = str_replace('\\', '/', (string) $upload_path);
        $root_path     = str_replace('\\', '/', rtrim((string) cmsConfig::get('root_path'), '/\\'));

        if ($root_path && strpos($absolute_path, $root_path . '/') === 0) {
            $relative = substr($absolute_path, strlen($root_path));
            return '/' . ltrim($relative, '/');
        }

        if (strpos($absolute_path, $upload_path) === 0) {
            $relative    = ltrim(substr($absolute_path, strlen($upload_path)), '/');
            $upload_host = rtrim((string) cmsConfig::get('upload_host'), '/');
            if ($upload_host) {
                return $upload_host . '/' . $relative;
            }
            return '/upload/' . $relative;
        }

        return '';
    }
}
