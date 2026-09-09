<?php
/**
 * AJAX: сбросить site overlay (только админ).
 */
class actionNordicaiOverlayClear extends cmsAction {

    public function run() {

        // См. generate.php: empty($this->cms_user->is_admin) ломается на magic __get.
        $user = cmsUser::getInstance();
        if (empty($user->id) || !$user->is_admin) {
            return $this->cms_template->renderJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $options = cmsController::loadOptions('nordicai');
        if (!is_array($options)) {
            $options = [];
        }
        $options['site_overlay_css'] = '';
        $options['site_overlay_updated'] = date('c');
        cmsController::saveOptions('nordicai', $options);

        return $this->cms_template->renderJSON(['ok' => true]);
    }

}
