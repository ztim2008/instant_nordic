<?php

class formWidgetOnebuilderProgress1Options extends cmsForm{
    
    public function init() {
        return array( 

            array(
                'type' => 'fieldset',
                'title' => 'Общее',
                'childs' => array(
                    new fieldString('options:id', array(
                        'title' => 'Уникальный идентификатор блока <span class="badge bg-danger">!</span>',
                        'hint' => '<mark>Например my-firts-block</mark>',
                        'rules' => array(array('required'))
                        )),
                    new fieldNumber('options:ptop', array(
                        'title' => 'Внутренний отступ в секции сверху',
                        'units' => 'px'
                        )),
                    new fieldNumber('options:pbottom', array(
                        'title' => 'Внутренний отступ в секции снизу',
                        'units' => 'px'
                        )),
                    new fieldList('options:bgtype', array(
                        'title' => 'Тип фона в секции',
                        'items' => array(
                            '' => '',
                            'background-color' => 'Сплошной цвет',
                            'background-image' => 'Изображение'
                            )
                        )),
                    new fieldColor('options:bgcolor', array(
                        'title' => 'Цвет фона',
                        'visible_depend' => array('options:bgtype' => array('show' => array('background-color')))
                        )),
                    new fieldImage('options:bgimage', array(
                        'title' => 'Фоновое изображение',
                        'options' => array('sizes' => array('original')),
                        'visible_depend' => array('options:bgtype' => array('show' => array('background-image')))
                        )),
                    new fieldList('options:bgfixed', array(
                        'title' => 'Прикрепление фона',
                        'items' => array(
                            '' => '',
                            'fixed' => 'Зафиксировать',
                            'inherit' => 'Прокручивать вместе с содержимым'
                            ),
                        'visible_depend' => array('options:bgtype' => array('show' => array('background-image')))
                        )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Левая колонка',
                'childs' => array(
                    new fieldString('options:title', array(
                        'title' => 'Заголовок <span class="badge bg-danger">!</span>',
                        'rules' => array(array('required'))
                        )),
                    new fieldList('options:title_preset', array(
                        'title' => 'Пресет заголовка',
                        'items' => array(
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6'
                            )
                        )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Правая колонка',
                'childs' => array(
                    new fieldText('options:desc', array(
                        'title' => 'Описание',
                        'hint' => '<mark>Если не нужно выводить, оставьте поле пустым</mark>'
                        )),
                    new fieldList('options:p_preset', array(
                        'title' => 'Пресет текста',
                        'items' => array(
                            'onebuilder-p1' => 'Пресет 1',
                            'onebuilder-p2' => 'Пресет 2',
                            'onebuilder-p3' => 'Пресет 3'
                        ),
                        'default' => 'onebuilder-p1'
                        )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Прогресс-бар',
                'childs' => array(
                    new fieldText('options:perc', array(
                        'title' => 'Проценты заполненности прогресс-баров <span class="badge bg-danger">каждый с новой строки</span>',
                        'hint' => '<mark>Укажите столько значений, сколько нужно вывести прогресс-баров. Кажддое значение с новой строки. Указывается в числовом значении от 0 до 100</mark>'
                        )),
                    new fieldText('options:name', array(
                        'title' => 'Заголовки для прогресс-баров <span class="badge bg-danger">каждый с новой строки</span>',
                        'hint' => '<mark>Укажите столько значений, сколько вы указали выше строк</mark>'
                        )),
                    new fieldString('options:attr', array(
                        'title' => 'Название внутри прогресс-бара',
                        'hint' => '<mark>Внутри прогресс бара можно указать какое-то значение. Например, <strong>%</strong>. Или <strong>шт</strong> </mark>'
                        )),
                    new fieldList('options:p_preset2', array(
                        'title' => 'Пресет текста для заголовков',
                        'items' => array(
                            'onebuilder-p1' => 'Пресет 1',
                            'onebuilder-p2' => 'Пресет 2',
                            'onebuilder-p3' => 'Пресет 3'
                        ),
                        'default' => 'onebuilder-p1'
                        )),
                    )
                )
                );
            }
            
        }