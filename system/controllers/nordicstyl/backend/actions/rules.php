<?php

class actionNordicstylRules extends cmsAction {

    public function run() {

        $baseUrl = href_to_abs('admin', 'controllers', ['edit', $this->controller->name, 'rules']);
        $buildUrl = function (array $params = []) use ($baseUrl) {
            if (!$params) {
                return $baseUrl;
            }
            return $baseUrl . '?' . http_build_query($params);
        };

        $editId = (int)$this->request->get('edit', 0);
        $current = $editId ? $this->model->getRule($editId) : null;

        if (!$editId && !$current) {
            $prefillPath = trim((string)$this->request->get('path', ''));
            $prefillTitle = trim((string)$this->request->get('title', ''));
            if ($prefillPath !== '' || $prefillTitle !== '') {
                $current = [
                    'id' => 0,
                    'title' => $prefillTitle,
                    'path' => $prefillPath,
                    'ordering' => 0,
                    'is_enabled' => 1,
                    'is_important' => 0,
                    'styles' => '',
                    'custom' => ''
                ];
            }
        }

        if ($this->request->has('delete')) {
            $csrf = (string)$this->request->get('csrf_token', '');
            if (!cmsForm::validateCSRFToken($csrf)) {
                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
                return $this->redirectBack();
            }

            $id = (int)$this->request->get('id', 0);
            if ($id) {
                $ok = $this->model->deleteRule($id);
                cmsUser::addSessionMessage($ok ? 'Правило удалено.' : 'Не удалось удалить правило.', $ok ? 'success' : 'error');
            }

            return $this->redirect($baseUrl);
        }

        if ($this->request->has('toggle')) {
            $csrf = (string)$this->request->get('csrf_token', '');
            if (!cmsForm::validateCSRFToken($csrf)) {
                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
                return $this->redirectBack();
            }

            $id = (int)$this->request->get('id', 0);
            if ($id) {
                $rule = $this->model->getRule($id);
                if ($rule) {
                    $newEnabled = empty($rule['is_enabled']) ? 1 : 0;
                    $ok = $this->model->updateRule($id, ['is_enabled' => $newEnabled]);
                    cmsUser::addSessionMessage($ok ? 'Статус обновлён.' : 'Не удалось обновить статус.', $ok ? 'success' : 'error');
                }
            }

            return $this->redirect($baseUrl);
        }

        if ($this->request->has('submit')) {
            $csrf = (string)$this->request->get('csrf_token', '');
            if (!cmsForm::validateCSRFToken($csrf)) {
                cmsUser::addSessionMessage(LANG_FORM_ERRORS, 'error');
                return $this->redirectBack();
            }

            $id = (int)$this->request->get('id', 0);
            $title = trim((string)$this->request->get('title', ''));
            $path = trim((string)$this->request->get('path', ''));
            $ordering = (int)$this->request->get('ordering', 0);
            $ordering = max(0, min(1000000, $ordering));

            $isEnabled = (int)!empty($this->request->get('is_enabled', 0));
            $isImportant = (int)!empty($this->request->get('is_important', 0));

            $stylesYaml = trim((string)$this->request->get('styles_yaml', ''));
            $customYaml = trim((string)$this->request->get('custom_yaml', ''));

            $errors = [];
            if ($path === '') {
                $errors['path'] = 'Укажите CSS селектор.';
            }

            if ($stylesYaml !== '') {
                $parsed = cmsModel::yamlToArray($stylesYaml);
                if (!is_array($parsed)) {
                    $errors['styles_yaml'] = 'styles должен быть валидным YAML (массив состояний).';
                }
            }

            if ($customYaml !== '') {
                $parsed = cmsModel::yamlToArray($customYaml);
                if (!is_array($parsed)) {
                    $errors['custom_yaml'] = 'custom должен быть валидным YAML (массив состояний).';
                }
            }

            $data = [
                'title'        => $title,
                'path'         => $path,
                'ordering'     => $ordering,
                'is_enabled'   => $isEnabled,
                'is_important' => $isImportant,
                'styles'       => $stylesYaml !== '' ? $stylesYaml : '',
                'custom'       => $customYaml !== '' ? $customYaml : ''
            ];

            if ($errors) {
                cmsUser::addSessionMessage('Исправьте ошибки формы.', 'error');
                return $this->cms_template->render('backend/rules', [
                    'menu'       => $this->controller->getBackendMenu(),
                    'base_url'   => $baseUrl,
                    'build_url'  => $buildUrl,
                    'rules'      => $this->model->getRules(false),
                    'csrf_token' => cmsForm::getCSRFToken(),
                    'errors'     => $errors,
                    'current'    => array_merge((array)$current, ['id' => $id] + $data)
                ]);
            }

            $ok = $id ? $this->model->updateRule($id, $data) : $this->model->addRule($data);
            cmsUser::addSessionMessage($ok ? 'Сохранено.' : 'Не удалось сохранить.', $ok ? 'success' : 'error');

            return $this->redirect($baseUrl);
        }

        $tokensRuleId = 0;
        $allRules = $this->model->getRules(false);
        foreach ($allRules as $r) {
            if (isset($r['path']) && trim((string)$r['path']) === ':root') {
                $tokensRuleId = (int)($r['id'] ?? 0);
                break;
            }
        }

        $tokensUrl = $tokensRuleId
            ? $buildUrl(['edit' => $tokensRuleId])
            : $buildUrl(['path' => ':root', 'title' => 'Токены']);

        return $this->cms_template->render('backend/rules', [
            'menu'       => $this->controller->getBackendMenu(),
            'base_url'   => $baseUrl,
            'build_url'  => $buildUrl,
            'rules'      => $allRules,
            'tokens_url' => $tokensUrl,
            'csrf_token' => cmsForm::getCSRFToken(),
            'errors'     => [],
            'current'    => $current
        ]);
    }
}
