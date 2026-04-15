<?php

class actionNordicblocksMediaList extends cmsAction {

    public function run() {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->cms_user->is_admin) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }

        $upload_path = rtrim((string) cmsConfig::get('upload_path'), '/\\') . DIRECTORY_SEPARATOR;
        if (!is_dir($upload_path)) {
            echo json_encode(['ok' => true, 'files' => []], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
        $files       = [];
        $limit       = 200;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($upload_path, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file_info) {
                if (!$file_info->isFile()) {
                    continue;
                }

                $ext = strtolower((string) pathinfo($file_info->getFilename(), PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_ext, true)) {
                    continue;
                }

                $path = $file_info->getPathname();
                $url  = $this->buildPublicUrl($path, $upload_path);
                if (!$url) {
                    continue;
                }

                $files[] = [
                    'url'   => $url,
                    'mtime' => (int) $file_info->getMTime(),
                ];
            }
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'scan_failed'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        usort($files, function ($a, $b) {
            return $b['mtime'] <=> $a['mtime'];
        });

        if (count($files) > $limit) {
            $files = array_slice($files, 0, $limit);
        }

        foreach ($files as &$file) {
            unset($file['mtime']);
        }
        unset($file);

        echo json_encode(['ok' => true, 'files' => $files], JSON_UNESCAPED_UNICODE);
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

        return null;
    }
}
