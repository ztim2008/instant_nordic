<?php

class formWidgetNordicbuilderRenderOptions extends cmsForm {

    public function init() {

        return [
            [
                'type'   => 'fieldset',
                'title'  => LANG_OPTIONS,
                'childs' => [
                    new fieldString('options:page_key', [
                        'title' => 'Ключ страницы (опционально)',
                        'hint'  => 'Если оставить пустым, ключ берется из текущего URL (главная страница = homepage).'
                    ])
                ]
            ]
        ];
    }
}
