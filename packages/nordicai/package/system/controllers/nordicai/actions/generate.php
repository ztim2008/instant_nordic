<?php
/**
 * AJAX: генерация patch с живого сайта (только админ).
 *
 * @property \modelNordicai $model
 */
class actionNordicaiGenerate extends cmsAction {

    public function run() {

        // Важно: не использовать empty($this->cms_user->is_admin) —
        // через magic __get экшена empty() ложно даёт true даже для админа.
        $user = cmsUser::getInstance();
        if (empty($user->id) || !$user->is_admin) {
            return $this->cms_template->renderJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $prompt   = trim((string) $this->request->get('prompt', ''));
        $selector = trim((string) $this->request->get('selector', ''));
        $context  = trim((string) $this->request->get('context', ''));

        if ($prompt === '') {
            return $this->cms_template->renderJSON(['ok' => false, 'error' => 'empty_prompt']);
        }

        cmsCore::includeFile('system/controllers/nordicai/libs/PatchService.php');
        $result = nordicaiPatchService::generate($this->options ?: [], $prompt, $selector, $context);

        try {
            if (!empty($this->model)) {
                $this->model->addRun([
                    'user_id'  => (int) $user->id,
                    'prompt'   => mb_substr($prompt, 0, 2000),
                    'selector' => mb_substr($selector, 0, 255),
                    'response' => !empty($result['patch']) ? mb_substr(json_encode($result['patch'], JSON_UNESCAPED_UNICODE), 0, 65000) : mb_substr((string) ($result['raw_content'] ?? ''), 0, 65000),
                    'error'    => empty($result['ok']) ? mb_substr((string) ($result['error'] ?? 'error'), 0, 500) : null,
                    'is_ok'    => !empty($result['ok']) ? 1 : 0,
                    'date_pub' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (Exception $e) {
        }

        return $this->cms_template->renderJSON($result);
    }

}
