<?php

class actionNordicstylBuilderStyleRule extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) {
            return cmsCore::error404();
        }

        if (!cmsUser::isLogged() || !cmsUser::isAllowed('admin', 'manage_nordicstyl_picker')) {
            return cmsCore::error404();
        }

        $csrfToken = (string)$this->request->get('csrf_token', '');
        if (!cmsForm::validateCSRFToken($csrfToken)) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Некорректный CSRF token.'
            ]);
        }

        $mode = trim((string)$this->request->get('mode', 'load'));
        if ($mode === 'save') {
            return $this->saveRule();
        }

        return $this->loadRule();
    }

    protected function loadRule() {

        $selector = trim((string)$this->request->get('selector', ''));
        $ruleId = (int)$this->request->get('rule_id', 0);
        $rule = null;

        if ($ruleId > 0) {
            $rule = $this->model->getRule($ruleId);
        }

        if (!$rule && $selector !== '' && method_exists($this->model, 'findRuleByPath')) {
            $rule = $this->model->findRuleByPath($selector);
        }

        return $this->cms_template->renderJSON([
            'error' => false,
            'rule' => $rule ? $this->normalizeRule($rule) : null
        ]);
    }

    protected function saveRule() {

        $selector = trim((string)$this->request->get('selector', ''));
        $title = trim((string)$this->request->get('title', ''));
        $ruleId = (int)$this->request->get('rule_id', 0);
        $stylesPayload = trim((string)$this->request->get('styles', ''));
        $customPayload = trim((string)$this->request->get('custom', ''));

        if ($selector === '') {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Не указан selector для style rule.'
            ]);
        }

        $styles = json_decode($stylesPayload, true);
        $custom = json_decode($customPayload, true);

        if (!is_array($styles) || !is_array($custom)) {
            return $this->cms_template->renderJSON([
                'error' => true,
                'message' => 'Некорректный style payload.'
            ]);
        }

        $title = $title !== '' ? $title : ('Inspector · ' . $selector);
        $stylesYaml = cmsModel::arrayToYaml($styles);
        $customYaml = cmsModel::arrayToYaml($custom);

        $existingRule = $ruleId > 0 ? $this->model->getRule($ruleId) : null;
        if (!$existingRule && method_exists($this->model, 'findRuleByPath')) {
            $existingRule = $this->model->findRuleByPath($selector);
        }

        $data = [
            'title' => $title,
            'path' => $selector,
            'is_enabled' => 1,
            'is_important' => (int)($existingRule['is_important'] ?? 0),
            'styles' => is_string($stylesYaml) ? $stylesYaml : '',
            'custom' => is_string($customYaml) ? $customYaml : ''
        ];

        if ($existingRule) {
            $data['ordering'] = (int)($existingRule['ordering'] ?? 0);
            $ok = $this->model->updateRule((int)$existingRule['id'], $data);
            $savedRule = $ok ? $this->model->getRule((int)$existingRule['id']) : null;
        } else {
            $data['ordering'] = $this->model->getMaxRuleOrdering() + 1;
            $newId = $this->model->addRule($data);
            $ok = (int)$newId > 0;
            $savedRule = $ok ? $this->model->getRule((int)$newId) : null;
        }

        return $this->cms_template->renderJSON([
            'error' => !$ok,
            'message' => $ok ? 'Style rule сохранено.' : 'Не удалось сохранить style rule.',
            'rule' => $savedRule ? $this->normalizeRule($savedRule) : null
        ]);
    }

    protected function normalizeRule(array $rule): array {

        return [
            'id' => (int)($rule['id'] ?? 0),
            'title' => trim((string)($rule['title'] ?? '')),
            'path' => trim((string)($rule['path'] ?? '')),
            'is_enabled' => !empty($rule['is_enabled']),
            'is_important' => !empty($rule['is_important']),
            'styles' => $this->decodeYamlArray((string)($rule['styles'] ?? '')),
            'custom' => $this->decodeYamlArray((string)($rule['custom'] ?? '')),
        ];
    }

    protected function decodeYamlArray(string $yaml): array {

        $yaml = trim($yaml);
        if ($yaml === '') {
            return [];
        }

        $decoded = cmsModel::yamlToArray($yaml);

        return is_array($decoded) ? $decoded : [];
    }
}