<?php

class onNordicaiBeforePrintHead extends cmsAction {

    public function run($template) {

        // Защита: после «битых» хуков в цепочке иногда приходит не template
        if (!is_object($template) || !method_exists($template, 'addHead')) {
            return $template;
        }

        // Не мешаем админке InstantCMS
        $uri_controller = (string) ($this->cms_core->uri_controller ?? '');
        if ($uri_controller === 'admin') {
            return $template;
        }

        $options = is_array($this->options ?? null) ? $this->options : [];
        $user = cmsUser::getInstance();
        $is_admin = !empty($user->id) && !empty($user->is_admin);

        // Диагностический маркер в HTML (видно в исходнике страницы)
        $template->addHead('<!-- nordicai-hook:ok admin=' . ($is_admin ? '1' : '0') . ' uid=' . (int) ($user->id ?? 0) . ' -->');

        // Опубликованный overlay — для всех
        $overlay_css = trim((string) ($options['site_overlay_css'] ?? ''));
        if ($overlay_css !== '') {
            $safe = str_replace('</style', '<\/style', $overlay_css);
            $template->addHead('<style id="nordicai-site-overlay">' . $safe . '</style>');
        }

        // Инспектор — только админу на фронте
        if (!$is_admin) {
            return $template;
        }

        $template->addControllerCSS('inspector', 'nordicai', false);
        $template->addControllerJS('inspector', 'nordicai', '', false);

        $config = [
            'generateUrl'     => href_to('nordicai', 'generate'),
            'overlaySaveUrl'  => href_to('nordicai', 'overlay_save'),
            'overlayClearUrl' => href_to('nordicai', 'overlay_clear'),
            'optionsUrl'      => href_to('admin', 'nordicai', 'options'),
            'agentUrl'        => href_to('admin', 'nordicai', 'agent'),
            'hasApiKey'       => !empty($options['deepseek_api_key']),
            'hasOverlay'      => $overlay_css !== '',
            'i18n' => [
                'title'    => 'Nordic AI Inspector',
                'pick'     => 'Выбрать элемент',
                'stop'     => 'Стоп',
                'prompt'   => 'Промпт',
                'generate' => 'Сгенерировать',
                'apply'    => 'Применить preview',
                'save'     => 'Сохранить на сайт',
                'clear'    => 'Сбросить overlay',
                'selected' => 'Выбрано',
                'noKey'    => 'Нет API ключа DeepSeek',
                'hint'     => 'Кликните элемент на странице',
            ],
        ];

        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $template->addHead('<script>window.NORDICAI_INSPECTOR=' . $json . ';</script>');

        return $template;
    }

}
