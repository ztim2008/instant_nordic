<?php
/**
 * AJAX playground generate (admin backend).
 *
 * @property \modelNordicai $model
 */
class actionNordicaiAgentGenerate extends cmsAction {

    public function run() {

        if (!$this->request->isAjax() && !$this->request->has('prompt')) {
            return cmsCore::error404();
        }

        $prompt   = trim((string) $this->request->get('prompt', ''));
        $selector = trim((string) $this->request->get('selector', ''));
        $context  = trim((string) $this->request->get('context', ''));

        if ($prompt === '') {
            return $this->cms_template->renderJSON([
                'ok'    => false,
                'error' => LANG_NORDICAI_ERR_EMPTY_PROMPT,
            ]);
        }

        cmsCore::includeFile('system/controllers/nordicai/libs/PatchService.php');
        $result = nordicaiPatchService::generate($this->controller->options ?: [], $prompt, $selector, $context);

        $error_map = [
            'api_key_missing' => LANG_NORDICAI_ERR_NO_KEY,
            'bad_json'        => LANG_NORDICAI_ERR_BAD_JSON,
            'empty_patch'     => LANG_NORDICAI_ERR_EMPTY_PATCH,
        ];
        if (empty($result['ok']) && !empty($result['error']) && isset($error_map[$result['error']])) {
            $result['error'] = $error_map[$result['error']];
        }

        try {
            $this->model->addRun([
                'user_id'  => (int) ($this->cms_user->id ?? 0),
                'prompt'   => mb_substr($prompt, 0, 2000),
                'selector' => mb_substr($selector, 0, 255),
                'response' => !empty($result['patch']) ? mb_substr(json_encode($result['patch'], JSON_UNESCAPED_UNICODE), 0, 65000) : mb_substr((string) ($result['raw_content'] ?? ''), 0, 65000),
                'error'    => empty($result['ok']) ? mb_substr((string) ($result['error'] ?? 'error'), 0, 500) : null,
                'is_ok'    => !empty($result['ok']) ? 1 : 0,
                'date_pub' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
        }

        return $this->cms_template->renderJSON($result);
    }

}
