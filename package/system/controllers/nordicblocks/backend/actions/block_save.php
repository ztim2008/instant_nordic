<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/BlockContractNormalizer.php';

class actionNordicblocksBlockSave extends cmsAction {

    public function run($block_id = 0) {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->request->isMethod('POST')) {
            echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
            exit;
        }

        if (!$this->cms_user->is_admin) {
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            exit;
        }

        $raw      = file_get_contents('php://input');
        $data     = json_decode($raw, true);

        $csrf_token = (string) $this->request->get('csrf_token', '');
        if ($csrf_token === '' && is_array($data)) {
            $csrf_token = (string) ($data['csrf_token'] ?? '');
        }

        if (!cmsForm::validateCSRFToken($csrf_token)) {
            echo json_encode(['ok' => false, 'error' => 'invalid_csrf']);
            exit;
        }

        $block_id = (int) $block_id;

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

        if (NordicblocksBlockContractNormalizer::supportsContractType((string) ($block['type'] ?? '')) && isset($data['contract']) && is_array($data['contract'])) {
            $contract = NordicblocksBlockContractNormalizer::normalize([
                'id'     => (int) $block['id'],
                'type'   => (string) $block['type'],
                'title'  => $title,
                'status' => (string) ($block['status'] ?? 'active'),
                'props'  => $data['contract'],
            ]);

            $this->model->saveBlockContract($block_id, $title, $contract);

            echo json_encode(['ok' => true, 'contract' => $contract], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

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
        $definition = $this->model->getBlockDefinition($type);
        if (!$definition || empty($definition['schema']['fields'])) {
            return [];
        }

        return $this->loadSchemaFieldDefinitions($definition['schema']['fields']);
    }

    private function loadSchemaFieldDefinitions(array $schema_fields) {
        $fields = [];

        foreach ($schema_fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            $normalized = $this->buildSchemaFieldDefinition($field);
            if ($normalized) {
                $fields[] = $normalized;
            }
        }

        return $fields;
    }

    private function buildSchemaFieldDefinition(array $field) {
        $key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) ($field['key'] ?? '')));
        if ($key === '') {
            return null;
        }

        $normalized = [
            'key'       => $key,
            'type'      => (string) ($field['type'] ?? 'text'),
            'default'   => $field['default'] ?? '',
            'options'   => is_array($field['options'] ?? null) ? $field['options'] : [],
            'min'       => $field['min'] ?? null,
            'max'       => $field['max'] ?? null,
            'max_items' => $field['max_items'] ?? null,
            'fields'    => [],
        ];

        if (strtolower($normalized['type']) === 'repeater' && !empty($field['fields']) && is_array($field['fields'])) {
            $normalized['fields'] = $this->loadSchemaFieldDefinitions($field['fields']);
        }

        return $normalized;
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
        $raw_default = $field['default'] ?? '';

        if ($type === 'repeater') {
            return $this->sanitizeRepeaterValue($field, $value);
        }

        $default = (string) ($field['default'] ?? '');

        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_numeric($value)) {
            $value = (string) $value;
        } elseif (!is_string($value)) {
            $value = $default;
        }

        $value = trim($value);

        if ($type === 'boolean') {
            $truthy = ['1', 'true', 'yes', 'on'];
            if ($value === '') {
                return $raw_default ? '1' : '0';
            }
            return in_array(strtolower($value), $truthy, true) ? '1' : '0';
        }

        if ($type === 'number') {
            $number = is_numeric($value) ? (0 + $value) : (is_numeric($raw_default) ? (0 + $raw_default) : 0);

            if (is_numeric($field['min'] ?? null) && $number < (0 + $field['min'])) {
                $number = 0 + $field['min'];
            }
            if (is_numeric($field['max'] ?? null) && $number > (0 + $field['max'])) {
                $number = 0 + $field['max'];
            }

            if ((float) $number === (float) ((int) $number)) {
                return (string) ((int) $number);
            }

            return rtrim(rtrim(sprintf('%.4F', $number), '0'), '.');
        }

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
            $value = strtolower($value);
            $value = preg_replace('/[^a-z0-9:_\-\s]/', '', $value);
            return $this->limitString($value, 120);
        }

        if ($type === 'image') {
            $media = $this->model->normalizeImageFieldValue($value);
            if ($media !== '') {
                return $media;
            }

            $fallback = $default;
            if ($fallback !== '' && !preg_match('#^(\/|https?:\/\/)#i', $fallback)) {
                $fallback = '';
            }

            return $fallback ? $this->model->normalizeImageFieldValue($fallback) : '';
        }

        if ($type === 'url') {
            return $this->limitString($value, 2048);
        }

        if ($type === 'textarea' || $type === 'richtext') {
            return $this->limitString($value, 10000);
        }

        return $this->limitString($value, 1000);
    }

    private function sanitizeRepeaterValue(array $field, $value) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (!is_array($value)) {
            $value = is_array($field['default'] ?? null) ? $field['default'] : [];
        }

        $child_fields = is_array($field['fields'] ?? null) ? $field['fields'] : [];
        $clean_items  = [];
        $max_items    = is_numeric($field['max_items'] ?? null) ? max(0, (int) $field['max_items']) : 100;

        foreach ($value as $item) {
            if ($max_items > 0 && count($clean_items) >= $max_items) {
                break;
            }

            if (!is_array($item)) {
                continue;
            }

            $clean_item = [];
            foreach ($child_fields as $child_field) {
                $child_key = (string) ($child_field['key'] ?? '');
                if ($child_key === '') {
                    continue;
                }

                $child_value = array_key_exists($child_key, $item)
                    ? $item[$child_key]
                    : ($child_field['default'] ?? '');

                $clean_item[$child_key] = $this->sanitizeFieldValue($child_field, $child_value);
            }

            $clean_items[] = $clean_item;
        }

        return $clean_items;
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
