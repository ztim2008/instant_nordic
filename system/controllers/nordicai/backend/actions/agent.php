<?php
/**
 * Площадка агента: промпт → JSON patch (tokens / scoped CSS).
 *
 * @property \modelNordicai $model
 */
class actionNordicaiAgent extends cmsAction {

    public function run() {

        $options = $this->controller->options ?: [];
        $has_key = !empty($options['deepseek_api_key']);

        $recent = [];
        try {
            $recent = $this->model->getRecentRuns(10);
        } catch (Exception $e) {
            $recent = [];
        }

        return $this->cms_template->render('backend/agent', [
            'options'      => $options,
            'has_api_key'  => $has_key,
            'generate_url' => href_to($this->root_url, 'agent_generate'),
            'options_url'  => href_to($this->root_url, 'options'),
            'recent'       => $recent,
            'known_tokens' => $this->getKnownTokens(),
        ]);
    }

    private function getKnownTokens() {
        return [
            '--nordic-bg',
            '--nordic-surface',
            '--nordic-ink',
            '--nordic-muted',
            '--nordic-accent',
            '--nordic-accent-strong',
            '--nordic-line',
            '--nordic-radius-md',
            '--nordic-radius-sm',
            '--nordic-shadow-card',
            '--nordic-font-ui',
            '--nordic-font-body',
            '--primary',
        ];
    }

}
