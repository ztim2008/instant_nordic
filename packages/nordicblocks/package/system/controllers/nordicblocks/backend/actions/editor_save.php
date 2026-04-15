<?php

/**
 * AJAX‑сохранение массива блоков страницы.
 * POST JSON body: { "blocks": [...] }
 */
class actionNordicblocksEditorSave extends cmsAction {

    public function run($page_id = 0) {
        $page_id = (int) $page_id;

        if (!$this->cms_user->is_admin) {
            $this->jsonResponse(['ok' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        $page = $this->model->getPageById($page_id);
        if (!$page) {
            $this->jsonResponse(['ok' => false, 'error' => 'Page not found'], 404);
            return;
        }

        $raw = file_get_contents('php://input');
        if (!$raw) {
            $this->jsonResponse(['ok' => false, 'error' => 'Empty request']);
            return;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['blocks']) || !is_array($data['blocks'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'Invalid payload']);
            return;
        }

        // Санитизируем каждый блок: только разрешённые типы полей
        $blocks = $this->sanitizeBlocks($data['blocks']);

        $this->model->savePage($page_id, $blocks);

        $this->jsonResponse(['ok' => true]);
    }

    private function sanitizeBlocks(array $blocks) {
        $clean = [];
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }
            $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) ($block['type'] ?? '')));
            if ($type === '') {
                continue;
            }
            $uid   = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($block['uid'] ?? uniqid('b', true)));
            $props = isset($block['props']) && is_array($block['props'])
                ? $this->sanitizeProps($block['props'])
                : [];

            $clean[] = [
                'type'  => $type,
                'uid'   => $uid,
                'props' => $props,
            ];
        }
        return $clean;
    }

    private function sanitizeProps(array $props) {
        $clean = [];
        foreach ($props as $key => $value) {
            $key = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $key);
            if ($key === '') {
                continue;
            }
            // Принимаем только скалярные значения и одномерные списки
            if (is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
                $clean[$key] = $value;
            } elseif (is_array($value)) {
                // Для repeater‑полей (список объектов)
                $clean[$key] = $value;
            }
        }
        return $clean;
    }

    private function jsonResponse(array $data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
