<?php

class formWidgetOnebuilderAbout3Options extends cmsForm{
    
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
                    new fieldImage('options:img', array(
                        'title' => 'Изображение в левой колонке',
                        'options' => array('sizes' => array('original'))
                        )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Правая колонка',
                'childs' => array(
                    new fieldString('options:title', array(
                        'title' => 'Заголовок'
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
                    new fieldText('options:desc', array(
                        'title' => 'Описание'
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
                    new fieldString('options:video', array(
                        'title' => 'Ссылка на видео, которое откроется в модальном окне при клике на кнопку'
                        )),
                    new fieldString('options:btitle', array(
                        'title' => 'Заголовок кнопки'
                        )),
                    new fieldList('options:bpreset', array(
                        'title' => 'Пресет кнопки',
                        'default' => 'onebuilder-button1 obbtn1',
                        'hint' => '<mark>Выберите, какой пресет кнопки выводить в этой секции. По умолчанию выводится родная кнопка OneBuilder.</mark>',
                        'items' => array(
                            'onebuilder-button1 obbtn1' => 'Пресет 1',
                            'onebuilder-button2 obbtn2' => 'Пресет 2',
                            'onebuilder-button3 obbtn3' => 'Пресет 3',
                            'onebuilder-button4 obbtn4' => 'Пресет 4',
                            'onebuilder-button5 obbtn5' => 'Пресет 5'
                        )
                        )),
                    )
                )
                );
            }
            
        }