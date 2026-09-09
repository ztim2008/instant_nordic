<?php
/**
 * AJAX: сохранить CSS overlay сайта (только админ).
 */
class actionNordicaiOverlaySave extends cmsAction {

    public function run() {

        // См. generate.php: empty($this->cms_user->is_admin) ломается на magic __get.
        $user = cmsUser::getInstance();
        if (empty($user->id) || !$user->is_admin) {
            return $this->cms_template->renderJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $css = trim((string) $this->request->get('css', ''));
        $tokens_json = trim((string) $this->request->get('tokens', ''));

        if ($css === '' && $tokens_json === '') {
            return $this->cms_template->renderJSON(['ok' => false, 'error' => 'empty_overlay']);
        }

        if (preg_match('/@import|expression\s*\(|javascript:/i', $css)) {
            return $this->cms_template->renderJSON(['ok' => false, 'error' => 'unsafe_css']);
        }

        // Если пришли tokens — соберём :root поверх css
        $tokens = json_decode($tokens_json, true);
        if (is_array($tokens) && $tokens) {
            cmsCore::includeFile('system/controllers/nordicai/libs/PatchService.php');
            $from_tokens = nordicaiPatchService::patchToCss(['tokens' => $tokens, 'css' => '']);
            $css = trim($from_tokens . "\n\n" . $css);
        }

        $options = cmsController::loadOptions('nordicai');
        if (!is_array($options)) {
            $options = [];
        }
        $options['site_overlay_css'] = $css;
        $options['site_overlay_updated'] = date('c');
        cmsController::saveOptions('nordicai', $options);

        return $this->cms_template->renderJSON([
            'ok'  => true,
            'css' => $css,
        ]);
    }

}
