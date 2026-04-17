<?php

class actionNordicblocksBlockPreview extends cmsAction {

    public function run($type = '') {
        $type = preg_replace('/[^a-z0-9_\-]/i', '', (string) $type);
        if ($type === '') {
            return cmsCore::error404();
        }

        $block_dir = dirname(__DIR__) . '/blocks/' . $type;
        if (!is_dir($block_dir)) {
            return cmsCore::error404();
        }

        $mime_types = [
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
        ];

        foreach ($mime_types as $ext => $mime_type) {
            $preview_file = $block_dir . '/preview.' . $ext;
            if (!file_exists($preview_file)) {
                continue;
            }

            header('Content-Type: ' . $mime_type);
            header('Content-Length: ' . (string) filesize($preview_file));
            header('Cache-Control: public, max-age=3600');
            readfile($preview_file);
            exit;
        }

        return cmsCore::error404();
    }
}