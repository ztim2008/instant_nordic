<?php

class formWidgetOnebuilderServices3Options extends cmsForm{
    
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
                'title' => 'Услуги',
                'childs' => array(
                    new fieldText('options:name', array(
                        'title' => 'Названия услуг<span class="badge bg-danger">Каждая услуга с новой строки</span>',
                        )),
                    new fieldText('options:icon', array(
                        'title' => 'Иконки услуг<span class="badge bg-danger">Каждая иконка с новой строки</span>',
                        'hint' => '<mark>Укажите иконку в формате: <strong>ti-save</strong>. Имена всех иконок доступны <a href = "https://themify.me/themify-icons" target = "_blank">Здесь</a></mark>'
                        )),
                    new fieldText('options:text', array(
                        'title' => 'Описание услуг<span class="badge bg-danger">Каждое описание через ***</span>',
                        'hint' => '<mark>Описание 1 *** Описание 2 *** Описание 3</mark>'
                        )),
                    new fieldList('options:title_preset2', array(
                        'title' => 'Пресет для названий услуг',
                        'items' => array(
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6'
                            )
                        )),
                    new fieldList('options:p_preset2', array(
                        'title' => 'Пресет для текста услуг',
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