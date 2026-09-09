<?php
/**
 * @property \modelNordicai $model
 */
class backendNordicai extends cmsBackend {

    public $useDefaultOptionsAction = true;

    protected $useOptions = true;

    public function getBackendMenu() {
        return [
            [
                'title'   => LANG_NORDICAI_CP_AGENT,
                'url'     => href_to($this->root_url, 'agent'),
                'options' => ['icon' => 'magic']
            ],
            [
                'title'   => LANG_NORDICAI_CP_OPTIONS_AGENT,
                'url'     => href_to($this->root_url, 'options'),
                'options' => ['icon' => 'key']
            ],
        ];
    }

    public function actionIndex() {
        $this->redirectToAction('agent');
    }

}
