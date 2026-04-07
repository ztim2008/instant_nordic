<?php

class formWidgetOnebuilderTeam1Options extends cmsForm{
    
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
                'title' => 'Левая колонка',
                'childs' => array(
                    new fieldString('options:title', array(
                        'title' => 'Заголовок в левой колонке',
                        'hint' => '<mark>Если заголовок выводить не нужно, оставьте поле пустым</mark>'
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
                    new fieldHtml('options:content', array(
                        'title' => 'Описание в левой колонке',
                        'hint' => '<mark>Если описание выводить не нужно, оставьте поле пустым</mark>'
                        )),
                    new fieldList('options:typebutton', array(
                        'title' => 'Тип действия на кнопке',
                        'hint' => '<mark>Выберите, какое действие необходимо сделат прик клике на кнопку</mark>',
                        'items' => array(
                            'scrollto' => 'Скролл к секции',
                            'linkto' => 'Открыть ссылку',
                            'none' => 'Не выводить кнопку')
                        )),
                    new fieldString('options:scrolllink', array(
                        'title' => 'Куда скроллить?',
                        'hint' => '<mark>Укажите идентификатор блока, к которому нужно сделать прокрутку. Первый символ в ссылке - обязательно <strong>#</strong>. Например, <strong>#block-id</strong></mark>',
                        'visible_depend' => array('options:typebutton' => array('show' => array('scrollto')))
                        )),
                    new fieldString('options:titlebutton', array(
                        'title' => 'Заголовок кнопки',
                        'hint' => '<mark>Укажите заголовок кнопки</mark>',
                        'visible_depend' => array('options:typebutton' => array('show' => array('scrollto', 'linkto', 'modal')))
                        )),
                    new fieldString('options:link', array(
                        'title' => 'Ссылка',
                        'hint' => '<mark>Укажите ссылку. Для внешних ссылок указывайте полный адрес. Для внутренних - <strong>pages/about.html</strong></mark>',
                        'visible_depend' => array('options:typebutton' => array('show' => array('linkto')))
                        )),
                    new fieldList('options:linkopt', array(
                        'title' => 'Открыть ссылку',
                        'hint' => '<mark>Выберите, каким образом открыть ссылку</mark>',
                        'items' => array(
                            'self' => 'В текущем окне',
                            'blank' => 'В новом окне'),
                        'visible_depend' => array('options:typebutton' => array('show' => array('linkto')))
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
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Правая колонка',
                'childs' => array(
                    new fieldList('options:num_column', array(
                        'title' => 'Сколько сотрудников выводить в  правой колонке? <span class="badge bg-danger">!</span>',
                        'default' => 'none',
                        'rules' => array(array('required')),
                        'items' => array(
                            'none' => '',
                            'three' => '3',
                            'six' => '6'
                        ),
                        )),
                    new fieldImage('options:user1', array(
                        'title' => 'Фото первого сотрудника',
                        'options' => array('sizes' => array('big')),
                        'hint' => '<mark>Старайтесь загружать квадратное фото с равнцми пропорциями</mark>',
                        'visible_depend' => array('options:num_column' => array('show' => array('three', 'six')))
                        )),
                        new fieldString('options:name1', array(
                            'title' => 'Имя сотрудника',
                            'hint' => '<mark>Например, Вася Иванов</mark>',
                            'visible_depend' => array('options:num_column' => array('show' => array('three', 'six')))
                            )),
                            new fieldString('options:work1', array(
                                'title' => 'Должность сотрудника',
                                'hint' => '<mark>Если должность не нужно выводить, оставьте поле пустым</mark>',
                                'visible_depend' => array('options:num_column' => array('show' => array('three', 'six')))
                                )),
                    new fieldImage('options:user2', array(
                        'title' => 'Фото второго сотрудника',
                        'options' => array('sizes' => array('big')),
                        'hint' => '<mark>Старайтесь загружать квадратное фото с равнцми пропорциями</mark>',
                        'visible_depend' => array('options:num_column' => array('show' => array('three', 'six')))
                        )),
                        new fieldString('options:name2', array(
                            'title' => 'Имя сотрудника',
                            'hint' => '<mark>Например, Вася Иванов</mark>',
                            'visible_depend' => array('options:num_column' => array('show' => array('three', 'six')))
                            )),
                            new fieldString('options:work2', array(
                                'title' => 'Должность сотрудника',
                                'hint' => '<mark>Если должность не нужно выводить, оставьте поле пустым</mark>',
                                'visible_depend' => array('options:num_column' => array('show' => array('three', 'six')))
                                )),
                    new fieldImage('options:user3', array(
                        'title' => 'Фото первого сотрудника',
                        'options' => array('sizes' => array('big')),
                        'hint' => '<mark>Старайтесь загружать квадратное фото с равнцми пропорциями</mark>',
                        'visible_depend' => array('options:num_column' => array('show' => array('three', 'six')))
                        )),
                        new fieldString('options:name3', array(
                            'title' => 'Имя сотрудника',
                            'hint' => '<mark>Например, Вася Иванов</mark>',
                            'visible_depend' => array('options:num_column' => array('show' => array('three', 'six')))
                            )),
                            new fieldString('options:work3', array(
                                'title' => 'Должность сотрудника',
                                'hint' => '<mark>Если должность не нужно выводить, оставьте поле пустым</mark>',
                                'visible_depend' => array('options:num_column' => array('show' => array('three', 'six')))
                                )),
                    new fieldImage('options:user4', array(
                        'title' => 'Фото четвертого сотрудника',
                        'options' => array('sizes' => array('big')),
                        'hint' => '<mark>Старайтесь загружать квадратное фото с равнцми пропорциями</mark>',
                        'visible_depend' => array('options:num_column' => array('show' => array('six')))
                        )),
                        new fieldString('options:name4', array(
                            'title' => 'Имя сотрудника',
                            'hint' => '<mark>Например, Вася Иванов</mark>',
                            'visible_depend' => array('options:num_column' => array('show' => array('six')))
                            )),
                            new fieldString('options:work4', array(
                                'title' => 'Должность сотрудника',
                                'hint' => '<mark>Если должность не нужно выводить, оставьте поле пустым</mark>',
                                'visible_depend' => array('options:num_column' => array('show' => array('six')))
                                )),
                    new fieldImage('options:user5', array(
                        'title' => 'Фото пятого сотрудника',
                        'options' => array('sizes' => array('big')),
                        'hint' => '<mark>Старайтесь загружать квадратное фото с равнцми пропорциями</mark>',
                        'visible_depend' => array('options:num_column' => array('show' => array('six')))
                        )),
                        new fieldString('options:name5', array(
                            'title' => 'Имя сотрудника',
                            'hint' => '<mark>Например, Вася Иванов</mark>',
                            'visible_depend' => array('options:num_column' => array('show' => array('six')))
                            )),
                            new fieldString('options:work5', array(
                                'title' => 'Должность сотрудника',
                                'hint' => '<mark>Если должность не нужно выводить, оставьте поле пустым</mark>',
                                'visible_depend' => array('options:num_column' => array('show' => array('six')))
                                )),
                    new fieldImage('options:user6', array(
                        'title' => 'Фото шестого сотрудника',
                        'options' => array('sizes' => array('big')),
                        'hint' => '<mark>Старайтесь загружать квадратное фото с равнцми пропорциями</mark>',
                        'visible_depend' => array('options:num_column' => array('show' => array('six')))
                        )),
                        new fieldString('options:name6', array(
                            'title' => 'Имя сотрудника',
                            'hint' => '<mark>Например, Вася Иванов</mark>',
                            'visible_depend' => array('options:num_column' => array('show' => array('six')))
                            )),
                            new fieldString('options:work6', array(
                                'title' => 'Должность сотрудника',
                                'hint' => '<mark>Если должность не нужно выводить, оставьте поле пустым</mark>',
                                'visible_depend' => array('options:num_column' => array('show' => array('six')))
                                )),
                    )
                ),
                );
            }
            
        }