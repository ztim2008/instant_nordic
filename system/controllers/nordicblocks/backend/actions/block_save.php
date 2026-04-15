<?php

class actionNordicblocksBlockSave extends cmsAction {

    public function run($block_id = 0) {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->request->isMethod('POST')) {
            echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
            exit;
        }

        $block_id = (int) $block_id;
        $raw      = file_get_contents('php://input');
        $data     = json_decode($raw, true);

        if (!$block_id || !is_array($data)) {
            echo json_encode(['ok' => false, 'error' => 'bad_request']);
            exit;
        }

        $block = $this->model->getBlockById($block_id);
        if (!$block) {
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            exit;
        }

        $title = isset($data['title']) ? trim((string) $data['title']) : (string) $block['title'];
        if ($title === '') {
            $title = (string) $block['title'];
        }
        $title = $this->limitString($title, 255);

        $incoming_props = isset($data['props']) && is_array($data['props']) ? $data['props'] : [];
        $schema_fields  = $this->loadSchemaFields((string) ($block['type'] ?? ''));

        if ($schema_fields) {
            $clean_props = [];
            foreach ($schema_fields as $field) {
                $key = $field['key'];
                if (array_key_exists($key, $incoming_props)) {
                    $value = $incoming_props[$key];
                } elseif (isset($block['props']) && is_array($block['props']) && array_key_exists($key, $block['props'])) {
                    $value = $block['props'][$key];
                } else {
                    $value = $field['default'];
                }
                $clean_props[$key] = $this->sanitizeFieldValue($field, $value);
            }
        } else {
            $clean_props = $this->sanitizeFallbackProps($incoming_props, (array) ($block['props'] ?? []));
        }

        $this->model->saveBlock($block_id, $title, $clean_props);

        echo json_encode(['ok' => true]);
        exit;
    }

    private function loadSchemaFields($type) {
        $type = preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim((string) $type)));
        if ($type === '') {
            return [];
        }

        $schema_file = cmsConfig::get('root_path') . 'system/controllers/nordicblocks/blocks/' . $type . '/schema.json';
        if (!file_exists($schema_file)) {
            return [];
        }

        $schema = json_decode((string) file_get_contents($schema_file), true);
        if (!is_array($schema) || empty($schema['fields']) || !is_array($schema['fields'])) {
            return [];
        }

        $fields = [];
        foreach ($schema['fields'] as $field) {
            $key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) ($field['key'] ?? '')));
            if ($key === '') {
                continue;
            }
            $fields[] = [
                'key'     => $key,
                'type'    => (string) ($field['type'] ?? 'text'),
                'default' => $field['default'] ?? '',
                'options' => is_array($field['options'] ?? null) ? $field['options'] : [],
            ];
        }

        return $fields;
    }

    private function sanitizeFallbackProps(array $incoming_props, array $stored_props) {
        $source     = $incoming_props ?: $stored_props;
        $clean_props = [];

        foreach ($source as $k => $v) {
            $clean_key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $k));
            if ($clean_key === '') {
                continue;
            }

            if (is_scalar($v) || $v === null) {
                $clean_props[$clean_key] = $this->limitString((string) $v, 10000);
            }
        }

        return $clean_props;
    }

    private function sanitizeFieldValue(array $field, $value) {
        $type    = strtolower((string) ($field['type'] ?? 'text'));
        $default = (string) ($field['default'] ?? '');

        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_numeric($value)) {
            $value = (string) $value;
        } elseif (!is_string($value)) {
            $value = $default;
        }

        $value = trim($value);

        if ($type === 'select') {
            $allowed = [];
            foreach ($field['options'] as $opt) {
                if (!is_array($opt)) {
                    continue;
                }
                $allowed[] = (string) ($opt['value'] ?? '');
            }
            if ($allowed && !in_array($value, $allowed, true)) {
                $value = $default;
            }
            return $this->limitString($value, 255);
        }

        if ($type === 'color') {
            if (!preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $value)) {
                $value = $default;
            }
            return $this->limitString($value, 16);
        }

        if ($type === 'icon') {
            $value = preg_replace('/[^a-z0-9\-\s]/i', '', $value);
            return $this->limitString($value, 120);
        }

        if ($type === 'image') {
            // Allow relative upload paths and absolute URLs.
            if ($value !== '' && !preg_match('#^(\/|https?:\/\/)#i', $value)) {
                $value = $default;
            }
            return $this->limitString($value, 2048);
        }

        if ($type === 'url') {
            return $this->limitString($value, 2048);
        }

        if ($type === 'textarea' || $type === 'richtext') {
            return $this->limitString($value, 10000);
        }

        return $this->limitString($value, 1000);
    }

    private function limitString($value, $max) {
        $value = (string) $value;
        if ($max <= 0) {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $max);
        }

        return substr($value, 0, $max);
    }
}
