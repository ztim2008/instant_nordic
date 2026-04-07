<?php

class formWidgetOnebuilderServices1Options extends cmsForm{
    
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
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Секция',
                'childs' => array(
                    new fieldString('options:title', array(
                        'title' => 'Заголовок секции',
                        'hint' => '<mark>Если заголовок выводить не требуется, оставьте поле пустым</mark>',
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
                    new fieldHtml('options:desc', array(
                        'title' => 'Описание секции',
                        'hint' => '<mark>Если описание выводить не требуется, оставьте поле пустым</mark>',
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
                    new fieldColor('options:icolor', array(
                        'title' => 'Цвет иконок',
                        'default' => '#004fe6',
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