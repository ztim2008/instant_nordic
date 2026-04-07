<?php

class formWidgetOnebuilderAbout1Options extends cmsForm{
    
    public function init() {
        return array( 

            array(
                'type' => 'fieldset',
                'title' => 'Секция',
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
                    'hint' => '<mark>Введите заголовок</mark>',
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
                new fieldText('options:about', array(
                    'title' => 'Описание под заголовком'
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
                new fieldImage('options:image1', array(
                    'title' => 'Фото первой услуги',
                    'hint' => '<mark>Старайтесь, чтобы загружаемые изображения имели одинаковую высоту</mark>',
                    'options' => array('sizes' => array('big'))
                    )),
                new fieldString('options:title1', array(
                    'title' => 'Заголовок',
                    'hint' => '<mark>Введите заголовок</mark>'
                    )),
                new fieldList('options:title_preset2', array(
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
                new fieldString('options:icon1', array(
                    'title' => 'Иконка заголовка',
                    'hint' => '<mark>Укажите иконку в формате: <strong>ti-save</strong>. Имена всех иконок доступны <a href = "https://themify.me/themify-icons" target = "_blank">Здесь</a></mark>',
                    'default' => 'ti-crown'
                    )),
                new fieldText('options:text1', array(
                    'title' => 'Описание под заголовком',
                    )),
                new fieldList('options:p2_preset', array(
                    'title' => 'Пресет текста',
                    'items' => array(
                        'onebuilder-p1' => 'Пресет 1',
                        'onebuilder-p2' => 'Пресет 2',
                        'onebuilder-p3' => 'Пресет 3'
                    ),
                    'default' => 'onebuilder-p1'
                    )),
                new fieldImage('options:image2', array(
                    'title' => 'Фото второй услуги',
                    'hint' => '<mark>Старайтесь, чтобы загружаемые изображения имели одинаковую высоту</mark>',
                    'options' => array('sizes' => array('big'))
                    )),
                new fieldString('options:title2', array(
                    'title' => 'Заголовок',
                    'hint' => '<mark>Введите заголовок</mark>',
                    )),
                new fieldList('options:title_preset3', array(
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
                new fieldString('options:icon2', array(
                    'title' => 'Иконка заголовка',
                    'hint' => '<mark>Укажите иконку в формате: <strong>ti-save</strong>. Имена всех иконок доступны <a href = "https://themify.me/themify-icons" target = "_blank">Здесь</a></mark>',
                    'default' => 'ti-pencil-alt'
                    )),
                new fieldText('options:text2', array(
                    'title' => 'Описание под заголовком'
                    )),
                new fieldList('options:p3_preset', array(
                    'title' => 'Пресет текста',
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

