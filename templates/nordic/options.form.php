<?php

require_once dirname(__FILE__) . '/../modern/options.form.php';

/**
 * Дизайн-система шаблона Nordic
 * Управление токенами оформления: цвет, типографика, форма, тени, шапка/футер
 */
class formNordicTemplateOptions extends formModernTemplateOptions {

    public function init() {

        // Шрифты — человекочитаемые пары [CSS-стек => Название]
        $font_body_options = [
            '"PT Serif", Georgia, serif'                         => 'PT Serif (с засечками)',
            '"Lora", Georgia, serif'                             => 'Lora (с засечками)',
            '"Roboto", Arial, sans-serif'                        => 'Roboto (без засечек)',
            '"Inter", Arial, sans-serif'                         => 'Inter (без засечек)',
            '"Open Sans", Arial, sans-serif'                     => 'Open Sans (без засечек)',
            '"Montserrat", Arial, sans-serif'                    => 'Montserrat (без засечек)',
            '"Nunito", Arial, sans-serif'                        => 'Nunito (мягкий)',
            '-apple-system, BlinkMacSystemFont, sans-serif'      => 'Системный шрифт',
        ];

        $font_ui_options = [
            '"Roboto Condensed", "Arial Narrow", sans-serif'     => 'Roboto Condensed (компактный)',
            '"Roboto", Arial, sans-serif'                        => 'Roboto (нейтральный)',
            '"Inter", Arial, sans-serif'                         => 'Inter (современный)',
            '"Montserrat", Arial, sans-serif'                    => 'Montserrat (акцентный)',
            '"PT Sans", Arial, sans-serif'                       => 'PT Sans',
            '"Open Sans", Arial, sans-serif'                     => 'Open Sans',
        ];

        // Масштаб радиуса
        $radius_options = [
            'none' => 'Без скруглений',
            'sm'   => 'Небольшое',
            'md'   => 'Среднее (по умолчанию)',
            'lg'   => 'Большое',
            'pill' => 'Таблетка (pill)',
        ];

        // Масштаб теней
        $shadow_options = [
            'none' => 'Без теней',
            'sm'   => 'Лёгкие',
            'md'   => 'Средние (по умолчанию)',
            'lg'   => 'Выразительные',
        ];

        // Стиль кнопок
        $btn_style_options = [
            'filled'  => 'Заливка (filled)',
            'outline' => 'Обводка (outline)',
            'ghost'   => 'Без фона (ghost)',
        ];

        $fields = [

            // ── Вкладка: Цвета ────────────────────────────────────────────
            'nordic_colors' => [
                'type'   => 'fieldset',
                'title'  => 'Цвета',
                'childs' => [

                    new fieldColor('scss:nordic-color-accent', [
                        'title'   => 'Акцентный цвет',
                        'hint'    => 'Кнопки, ссылки, активные элементы',
                        'default' => '#b42318',
                    ]),

                    new fieldColor('scss:nordic-color-bg', [
                        'title'   => 'Фон страницы',
                        'default' => '#eceff3',
                    ]),

                    new fieldColor('scss:nordic-color-surface', [
                        'title'   => 'Поверхность (карточки, виджеты)',
                        'default' => '#ffffff',
                    ]),

                    new fieldColor('scss:nordic-color-ink', [
                        'title'   => 'Цвет текста',
                        'default' => '#10141b',
                    ]),

                    new fieldColor('scss:nordic-color-muted', [
                        'title'   => 'Приглушённый текст',
                        'hint'    => 'Подписи, метки, второстепенный текст',
                        'default' => '#5f6b7a',
                    ]),

                    new fieldColor('scss:nordic-color-panel', [
                        'title'   => 'Тёмная панель',
                        'hint'    => 'Тёмный контрастный фон (например, в тёмных блоках)',
                        'default' => '#121826',
                    ]),

                ]
            ],

            // ── Вкладка: Шапка и футер ────────────────────────────────────
            'nordic_header_footer' => [
                'type'   => 'fieldset',
                'title'  => 'Шапка и футер',
                'childs' => [

                    new fieldColor('scss:nordic-color-header-bg', [
                        'title'   => 'Фон шапки',
                        'default' => '#121826',
                    ]),

                    new fieldColor('scss:nordic-color-header-fg', [
                        'title'   => 'Цвет текста в шапке',
                        'default' => '#ffffff',
                    ]),

                    new fieldColor('scss:nordic-color-footer-bg', [
                        'title'   => 'Фон футера',
                        'default' => '#1d2638',
                    ]),

                    new fieldColor('scss:nordic-color-footer-fg', [
                        'title'   => 'Цвет текста в футере',
                        'default' => '#9aa5b4',
                    ]),

                ]
            ],

            // ── Вкладка: Типографика ──────────────────────────────────────
            'nordic_typography' => [
                'type'   => 'fieldset',
                'title'  => 'Типографика',
                'childs' => [

                    new fieldList('scss:nordic-font-body', [
                        'title'   => 'Шрифт текста',
                        'hint'    => 'Основной текст, абзацы, материалы',
                        'default' => '"PT Serif", Georgia, serif',
                        'items'   => $font_body_options,
                    ]),

                    new fieldList('scss:nordic-font-ui', [
                        'title'   => 'Шрифт интерфейса',
                        'hint'    => 'Заголовки, кнопки, навигация, подписи',
                        'default' => '"Roboto Condensed", "Arial Narrow", sans-serif',
                        'items'   => $font_ui_options,
                    ]),

                    new fieldList('scss:nordic-font-size-base', [
                        'title'   => 'Размер базового текста',
                        'default' => '16px',
                        'items'   => [
                            '14px' => '14px (компактный)',
                            '15px' => '15px',
                            '16px' => '16px (по умолчанию)',
                            '17px' => '17px',
                            '18px' => '18px (увеличенный)',
                        ],
                    ]),

                    new fieldList('scss:nordic-line-height', [
                        'title'   => 'Межстрочный интервал',
                        'default' => '1.7',
                        'items'   => [
                            '1.4' => '1.4 (плотный)',
                            '1.5' => '1.5',
                            '1.6' => '1.6',
                            '1.7' => '1.7 (по умолчанию)',
                            '1.8' => '1.8 (просторный)',
                        ],
                    ]),

                ]
            ],

            // ── Вкладка: Форма элементов ──────────────────────────────────
            'nordic_shape' => [
                'type'   => 'fieldset',
                'title'  => 'Форма (скругления)',
                'childs' => [

                    new fieldList('scss:nordic-radius-scale', [
                        'title'   => 'Скругление углов',
                        'hint'    => 'Применяется ко всем элементам: карточки, кнопки, инпуты, виджеты',
                        'default' => 'md',
                        'items'   => $radius_options,
                    ]),

                ]
            ],

            // ── Вкладка: Тени ─────────────────────────────────────────────
            'nordic_shadows' => [
                'type'   => 'fieldset',
                'title'  => 'Тени',
                'childs' => [

                    new fieldList('scss:nordic-shadow-scale', [
                        'title'   => 'Интенсивность теней',
                        'hint'    => 'Применяется к карточкам, виджетам и панелям',
                        'default' => 'md',
                        'items'   => $shadow_options,
                    ]),

                ]
            ],

            // ── Вкладка: Кнопки ───────────────────────────────────────────
            'nordic_buttons' => [
                'type'   => 'fieldset',
                'title'  => 'Кнопки',
                'childs' => [

                    new fieldList('scss:nordic-btn-style', [
                        'title'   => 'Стиль кнопок',
                        'hint'    => 'Применяется ко всем primary-кнопкам на сайте',
                        'default' => 'filled',
                        'items'   => $btn_style_options,
                    ]),

                ]
            ],

        ];

        // Добавляем вкладки дизайн-системы в начало формы
        foreach ($fields as $key => $fieldset) {
            $this->addField($key, $fieldset);
        }

        // Добавляем стандартные вкладки Modern (лого, favicon, Bootstrap-переменные и т.д.)
        parent::init();
    }

}
