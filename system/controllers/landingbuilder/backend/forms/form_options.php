<?php

class formLandingbuilderOptions extends cmsForm {

    public function init() {

        return [
            [
                'type' => 'fieldset',
                'title' => 'Редактор холста',
                'childs' => [
                    new fieldCheckbox('enable_system_widgets', [
                        'title' => 'Разрешить системные виджеты на холсте',
                        'default' => 1
                    ]),
                    new fieldCheckbox('enable_tablet_mode', [
                        'title' => 'Включить отдельный режим планшета',
                        'default' => 1
                    ]),
                    new fieldCheckbox('enable_device_toggles', [
                        'title' => 'Показывать переключатели устройств в редакторе',
                        'default' => 1
                    ]),
                    new fieldList('default_section_layout', [
                        'title' => 'Схема секции по умолчанию',
                        'default' => '1col',
                        'items' => [
                            '1col' => '1 колонка',
                            '2col_equal' => '2 равные колонки',
                            '2col_sidebar_right' => '2 колонки с правым сайдбаром',
                            '3col_equal' => '3 равные колонки'
                        ]
                    ]),
                    new fieldList('preview_template', [
                        'title' => 'Базовый шаблон предпросмотра',
                        'default' => 'nordic',
                        'items' => [
                            'nordic' => 'nordic',
                            'modern' => 'modern'
                        ]
                    ])
                ]
            ]
        ];
    }
}