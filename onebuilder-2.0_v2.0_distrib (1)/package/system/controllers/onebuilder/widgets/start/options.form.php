<?php

class formWidgetOnebuilderStartOptions extends cmsForm{
    
    public function init() {
        return array( 
            array(
                'type' => 'fieldset',
                'title' => 'Шрифты',
                'childs' => array(
                    new fieldString('options:font1', array(
                        'title' => 'Первый шрифт из Google Fonts'
                        )),
                    new fieldString('options:font2', array(
                        'title' => 'Второй шрифт из Google Fonts'
                        )),
                    new fieldCheckbox('options:on_font3', array(
                        'title' => 'Подключить третий шрифт'
                        )),
                    new fieldString('options:font3', array(
                        'title' => 'Третий шрифт из Google Fonts',
                        'visible_depend' => array('options:on_font3' => array('show' => array('1')))
                        )),
                    new fieldCheckbox('options:on_font4', array(
                        'title' => 'Подключить четвертый шрифт'
                        )),
                    new fieldString('options:font4', array(
                        'title' => 'Четвертый шрифт из Google Fonts',
                        'visible_depend' => array('options:on_font4' => array('show' => array('1')))
                        )),
                    new fieldCheckbox('options:on_font5', array(
                        'title' => 'Подключить пятый шрифт'
                        )),
                    new fieldString('options:font5', array(
                        'title' => 'Пятый шрифт из Google Fonts',
                        'visible_depend' => array('options:on_font5' => array('show' => array('1')))
                        )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Типографика',
                'childs' => array(
                    new fieldList('options:zag_list', array(
                        'title' => 'Список доступных заголовков',
                        'hint' => '<mark>Выберите нужный заголовок, чтобы переопределить стили</mark>',
                        'default' => 'none',
                        'items' => array(
                            '' => '',
                            'onebuilder-h1' => 'Заголовок H1',
                            'onebuilder-h2' => 'Заголовок H2',
                            'onebuilder-h3' => 'Заголовок H3',
                            'onebuilder-h4' => 'Заголовок H4',
                            'onebuilder-h5' => 'Заголовок H5'
                        ),
                        )),
                    new fieldString('options:h1_font', array(
                        'title' => 'Шрифт заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение. Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h1')))
                        )),
                    new fieldColor('options:h1_color', array(
                        'title' => 'Цвет заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h1')))
                        )),
                    new fieldNumber('options:h1_fsize', array(
                        'title' => 'Размер шрифта',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h1')))
                        )),
                    new fieldList('options:h1_ftran', array(
                        'title' => 'Трансформация заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h1')))
                        )),
                    new fieldList('options:h1_decor', array(
                        'title' => 'Оформление заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Без декоративного оформления',
                            'underline' => 'Подчеркивание',
                            'overline' => 'Линия над текстом',
                            'line-through' => 'Зачеркивание'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h1')))
                        )),
                    new fieldList('options:h1_align', array(
                        'title' => 'Выравнивание заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'left',
                        'items' => array(
                            'left' => 'По левому краю',
                            'center' => 'По центру',
                            'right' => 'По правому краю'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h1')))
                        )),

                        /// <H2>
                        
                    new fieldString('options:h2_font', array(
                        'title' => 'Шрифт заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение. Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h2')))
                        )),
                    new fieldColor('options:h2_color', array(
                        'title' => 'Цвет заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h2')))
                        )),
                    new fieldNumber('options:h2_fsize', array(
                        'title' => 'Размер шрифта',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h2')))
                        )),
                    new fieldList('options:h2_ftran', array(
                        'title' => 'Трансформация заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h2')))
                        )),
                    new fieldList('options:h2_decor', array(
                        'title' => 'Оформление заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Без декоративного оформления',
                            'underline' => 'Подчеркивание',
                            'overline' => 'Линия над текстом',
                            'line-through' => 'Зачеркивание'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h2')))
                        )),
                    new fieldList('options:h2_align', array(
                        'title' => 'Выравнивание заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'left',
                        'items' => array(
                            'left' => 'По левому краю',
                            'center' => 'По центру',
                            'right' => 'По правому краю'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h2')))
                        )),

                        /// <H3>
                    
                    new fieldString('options:h3_font', array(
                        'title' => 'Шрифт заголовка',
                        'hint' => '<mark>Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h3')))
                        )),
                    new fieldColor('options:h3_color', array(
                        'title' => 'Цвет заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h3')))
                        )),
                    new fieldNumber('options:h3_fsize', array(
                        'title' => 'Размер шрифта',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h3')))
                        )),
                    new fieldList('options:h3_ftran', array(
                        'title' => 'Трансформация заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h3')))
                        )),
                    new fieldList('options:h3_decor', array(
                        'title' => 'Оформление заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Без декоративного оформления',
                            'underline' => 'Подчеркивание',
                            'overline' => 'Линия над текстом',
                            'line-through' => 'Зачеркивание'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h3')))
                        )),
                    new fieldList('options:h3_align', array(
                        'title' => 'Выравнивание заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'left',
                        'items' => array(
                            'left' => 'По левому краю',
                            'center' => 'По центру',
                            'right' => 'По правому краю'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h3')))
                        )),

                            /// <H4>
                    
                    new fieldString('options:h4_font', array(
                        'title' => 'Шрифт заголовка',
                        'hint' => '<mark>Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h4')))
                        )),
                    new fieldColor('options:h4_color', array(
                        'title' => 'Цвет заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h4')))
                        )),
                    new fieldNumber('options:h4_fsize', array(
                        'title' => 'Размер шрифта',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h4')))
                        )),
                    new fieldList('options:h4_ftran', array(
                        'title' => 'Трансформация заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h4')))
                        )),
                    new fieldList('options:h4_decor', array(
                        'title' => 'Оформление заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Без декоративного оформления',
                            'underline' => 'Подчеркивание',
                            'overline' => 'Линия над текстом',
                            'line-through' => 'Зачеркивание'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h4')))
                        )),
                    new fieldList('options:h4_align', array(
                        'title' => 'Выравнивание заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'left',
                        'items' => array(
                            'left' => 'По левому краю',
                            'center' => 'По центру',
                            'right' => 'По правому краю'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h4')))
                        )),

                         /// <H5>
                    
                    new fieldString('options:h5_font', array(
                        'title' => 'Шрифт заголовка',
                        'hint' => '<mark>Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h5')))
                        )),
                    new fieldColor('options:h5_color', array(
                        'title' => 'Цвет заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h5')))
                        )),
                    new fieldNumber('options:h5_fsize', array(
                        'title' => 'Размер шрифта',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h5')))
                        )),
                    new fieldList('options:h5_ftran', array(
                        'title' => 'Трансформация заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h5')))
                        )),
                    new fieldList('options:h5_decor', array(
                        'title' => 'Оформление заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Без декоративного оформления',
                            'underline' => 'Подчеркивание',
                            'overline' => 'Линия над текстом',
                            'line-through' => 'Зачеркивание'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h5')))
                        )),
                    new fieldList('options:h5_align', array(
                        'title' => 'Выравнивание заголовка',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'left',
                        'items' => array(
                            'left' => 'По левому краю',
                            'center' => 'По центру',
                            'right' => 'По правому краю'
                        ),
                        'visible_depend' => array('options:zag_list' => array('show' => array('onebuilder-h5')))
                        )),

                    new fieldList('options:txt_list', array(
                        'title' => 'Пресеты текста',
                        'hint' => '<mark>Выберите нужный пресет, чтобы переопределить стили</mark>',
                        'default' => 'none',
                        'items' => array(
                            '' => '',
                            'onebuilder-p1' => 'Пресет 1',
                            'onebuilder-p2' => 'Пресет 2',
                            'onebuilder-p3' => 'Пресет 3'
                        ),
                        )),

                    /// P1

                    new fieldString('options:p1_font', array(
                        'title' => 'Шрифт',
                        'hint' => '<mark>Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p1')))
                        )),
                    new fieldColor('options:p1_color', array(
                        'title' => 'Цвет текста',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p1')))
                        )),
                    new fieldNumber('options:p1_fsize', array(
                        'title' => 'Размер шрифта',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p1')))
                        )),
                    new fieldList('options:p1_ftran', array(
                        'title' => 'Трансформация текста',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p1')))
                        )),
                    new fieldNumber('options:p1_fline', array(
                        'title' => 'Высота строки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p1')))
                        )),

                    /// P2

                    new fieldString('options:p2_font', array(
                        'title' => 'Шрифт',
                        'hint' => '<mark>Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p2')))
                        )),
                    new fieldColor('options:p2_color', array(
                        'title' => 'Цвет текста',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p2')))
                        )),
                    new fieldNumber('options:p2_fsize', array(
                        'title' => 'Размер шрифта',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p2')))
                        )),
                    new fieldList('options:p2_ftran', array(
                        'title' => 'Трансформация текста',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p2')))
                        )),
                    new fieldNumber('options:p2_fline', array(
                        'title' => 'Высота строки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p2')))
                        )),

                    /// P2

                new fieldString('options:p3_font', array(
                    'title' => 'Шрифт',
                    'hint' => '<mark>Если Вы указали свои шрифты, в первой вкладке настроек, 
                    то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                    В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                    'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p3')))
                    )),
                new fieldColor('options:p3_color', array(
                    'title' => 'Цвет текста',
                    'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                    'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p3')))
                    )),
                new fieldNumber('options:p3_fsize', array(
                    'title' => 'Размер шрифта',
                    'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                    'units' => 'px',
                    'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p3')))
                    )),
                new fieldList('options:p3_ftran', array(
                    'title' => 'Трансформация текста',
                    'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                    'default' => 'none',
                    'items' => array(
                        'none' => 'Не менять регистр',
                        'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                        'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                        'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                    ),
                    'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p3')))
                    )),
                new fieldNumber('options:p3_fline', array(
                    'title' => 'Высота строки',
                    'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                    'units' => 'px',
                    'visible_depend' => array('options:txt_list' => array('show' => array('onebuilder-p3')))
                    )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Кнопки',
                'childs' => array(
                    new fieldList('options:button_list', array(
                        'title' => 'Список доступных кнопок',
                        'hint' => '<mark>Выберите нужную кнопку, чтобы переопределить ее стили</mark>',
                        'default' => 'none',
                        'items' => array(
                            '' => '',
                            'onebuilder-button1' => 'Кнопка 1',
                            'onebuilder-button2' => 'Кнопка 2',
                            'onebuilder-button3' => 'Кнопка 3',
                            'onebuilder-button4' => 'Кнопка 4',
                            'onebuilder-button5' => 'Кнопка 5'
                        ),
                        )),

                    new fieldString('options:button1_font', array(
                        'title' => 'Шрифт',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение. Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldColor('options:button1_bg', array(
                        'title' => 'Фон кнопки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldColor('options:button1_bgh', array(
                        'title' => 'Фон кнопки при наведении курсора мыши',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldColor('options:button1_color', array(
                        'title' => 'Цвет текста на кнопке',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldColor('options:button1_colorh', array(
                        'title' => 'Цвет текста на кнопке при наведении',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldNumber('options:button1_paddingtb', array(
                        'title' => 'Внутренний отступ сверху и снизу',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldNumber('options:button1_paddinglr', array(
                        'title' => 'Внутренний отступ слева и справа',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldNumber('options:button1_border', array(
                        'title' => 'Радиус углов кнопки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldNumber('options:button1_fsize', array(
                        'title' => 'Размер шрифта',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldList('options:button1_ftran', array(
                        'title' => 'Трансформация текста',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldList('options:button1_fstyle', array(
                        'title' => 'Стиль текста',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'normal',
                        'items' => array(
                            'normal' => 'Обычный стиль',
                            'italic' => 'Курсивное начертание',
                            'oblique' => 'Наклонное начертание'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldList('options:button1_fbord', array(
                        'title' => 'Стиль рамки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            '' => '',
                            'none' => 'Не выводить рамку',
                            'dotted' => 'Dotted',
                            'dashed' => 'Dashed',
                            'solid' => 'Solid',
                            'double' => 'Double',
                            'groove' => 'Groove',
                            'ridge' => 'Rigge',
                            'inset' => 'Inset',
                            'outset' => 'Outset'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button1')))
                        )),
                    new fieldNumber('options:button1_fbordt', array(
                        'title' => 'Толщина рамки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button1_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),
                    new fieldColor('options:button1_bcolor', array(
                        'title' => 'Цвет рамки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button1_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),

                    /// Вторая кнопка


                    new fieldString('options:button2_font', array(
                        'title' => 'Шрифт',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение. Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldColor('options:button2_bg', array(
                        'title' => 'Фон кнопки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldColor('options:button2_bgh', array(
                        'title' => 'Фон кнопки при наведении курсора мыши',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldColor('options:button2_color', array(
                        'title' => 'Цвет текста на кнопке',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldColor('options:button2_colorh', array(
                        'title' => 'Цвет текста на кнопке при наведении',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldNumber('options:button2_paddingtb', array(
                        'title' => 'Внутренний отступ сверху и снизу',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldNumber('options:button2_paddinglr', array(
                        'title' => 'Внутренний отступ слева и справа',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldNumber('options:button2_border', array(
                        'title' => 'Радиус углов кнопки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldNumber('options:button2_fsize', array(
                        'title' => 'Размер шрифта',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldList('options:button2_ftran', array(
                        'title' => 'Трансформация текста',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldList('options:button2_fstyle', array(
                        'title' => 'Стиль текста',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'default' => 'normal',
                        'items' => array(
                            'normal' => 'Обычный стиль',
                            'italic' => 'Курсивное начертание',
                            'oblique' => 'Наклонное начертание'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldList('options:button2_fbord', array(
                        'title' => 'Стиль рамки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'items' => array(
                            '' => '',
                            'none' => 'Не выводить рамку',
                            'dotted' => 'Dotted',
                            'dashed' => 'Dashed',
                            'solid' => 'Solid',
                            'double' => 'Double',
                            'groove' => 'Groove',
                            'ridge' => 'Rigge',
                            'inset' => 'Inset',
                            'outset' => 'Outset'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button2')))
                        )),
                    new fieldNumber('options:button2_fbordt', array(
                        'title' => 'Толщина рамки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'units' => 'px',
                        'visible_depend' => array('options:button2_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),
                    new fieldColor('options:button2_bcolor', array(
                        'title' => 'Цвет рамки',
                        'hint' => '<mark>Если поле не будет заполнено - выведется дефолтное значение</mark>',
                        'visible_depend' => array('options:button2_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),

                        /// Третья кнопка


                    new fieldString('options:button3_font', array(
                        'title' => 'Шрифт',
                        'hint' => '<mark>Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldColor('options:button3_bg', array(
                        'title' => 'Фон кнопки',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldColor('options:button3_bgh', array(
                        'title' => 'Фон кнопки при наведении курсора мыши',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldColor('options:button3_color', array(
                        'title' => 'Цвет текста на кнопке',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldColor('options:button3_colorh', array(
                        'title' => 'Цвет текста на кнопке при наведении',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldNumber('options:button3_paddingtb', array(
                        'title' => 'Внутренний отступ сверху и снизу',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldNumber('options:button3_paddinglr', array(
                        'title' => 'Внутренний отступ слева и справа',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldNumber('options:button3_border', array(
                        'title' => 'Радиус углов кнопки',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldNumber('options:button3_fsize', array(
                        'title' => 'Размер шрифта',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldList('options:button3_ftran', array(
                        'title' => 'Трансформация текста',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldList('options:button3_fstyle', array(
                        'title' => 'Стиль текста',
                        'default' => 'normal',
                        'items' => array(
                            'normal' => 'Обычный стиль',
                            'italic' => 'Курсивное начертание',
                            'oblique' => 'Наклонное начертание'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldList('options:button3_fbord', array(
                        'title' => 'Стиль рамки',
                        'items' => array(
                            '' => '',
                            'none' => 'Не выводить рамку',
                            'dotted' => 'Dotted',
                            'dashed' => 'Dashed',
                            'solid' => 'Solid',
                            'double' => 'Double',
                            'groove' => 'Groove',
                            'ridge' => 'Rigge',
                            'inset' => 'Inset',
                            'outset' => 'Outset'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button3')))
                        )),
                    new fieldNumber('options:button3_fbordt', array(
                        'title' => 'Толщина рамки',
                        'units' => 'px',
                        'visible_depend' => array('options:button3_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),
                    new fieldColor('options:button3_bcolor', array(
                        'title' => 'Цвет рамки',
                        'visible_depend' => array('options:button3_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),

                        /// Четвертая кнопка


                    new fieldString('options:button4_font', array(
                        'title' => 'Шрифт',
                        'hint' => '<mark>Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldColor('options:button4_bg', array(
                        'title' => 'Фон кнопки',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldColor('options:button4_bgh', array(
                        'title' => 'Фон кнопки при наведении курсора мыши',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldColor('options:button4_color', array(
                        'title' => 'Цвет текста на кнопке',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldColor('options:button4_colorh', array(
                        'title' => 'Цвет текста на кнопке при наведении',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldNumber('options:button4_paddingtb', array(
                        'title' => 'Внутренний отступ сверху и снизу',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldNumber('options:button4_paddinglr', array(
                        'title' => 'Внутренний отступ слева и справа',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldNumber('options:button4_border', array(
                        'title' => 'Радиус углов кнопки',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldNumber('options:button4_fsize', array(
                        'title' => 'Размер шрифта',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldList('options:button4_ftran', array(
                        'title' => 'Трансформация текста',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldList('options:button4_fstyle', array(
                        'title' => 'Стиль текста',
                        'default' => 'normal',
                        'items' => array(
                            'normal' => 'Обычный стиль',
                            'italic' => 'Курсивное начертание',
                            'oblique' => 'Наклонное начертание'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldList('options:button4_fbord', array(
                        'title' => 'Стиль рамки',
                        'items' => array(
                            '' => '',
                            'none' => 'Не выводить рамку',
                            'dotted' => 'Dotted',
                            'dashed' => 'Dashed',
                            'solid' => 'Solid',
                            'double' => 'Double',
                            'groove' => 'Groove',
                            'ridge' => 'Rigge',
                            'inset' => 'Inset',
                            'outset' => 'Outset'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button4')))
                        )),
                    new fieldNumber('options:button4_fbordt', array(
                        'title' => 'Толщина рамки',
                        'units' => 'px',
                        'visible_depend' => array('options:button4_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),
                    new fieldColor('options:button4_bcolor', array(
                        'title' => 'Цвет рамки',
                        'visible_depend' => array('options:button4_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),

                    /// Пятая кнопка


                    new fieldString('options:button5_font', array(
                        'title' => 'Шрифт',
                        'hint' => '<mark>Если Вы указали свои шрифты, в первой вкладке настроек, 
                        то укажите в этом поле название шрифта. Например, вы указали шрифт <strong>https://fonts.googleapis.com/css2?family=Oswald.</strong>
                        В таком случае, вам нужно указать название <strong>Oswald.</strong></mark>',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldColor('options:button5_bg', array(
                        'title' => 'Фон кнопки',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldColor('options:button5_bgh', array(
                        'title' => 'Фон кнопки при наведении курсора мыши',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldColor('options:button5_color', array(
                        'title' => 'Цвет текста на кнопке',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldColor('options:button5_colorh', array(
                        'title' => 'Цвет текста на кнопке при наведении',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldNumber('options:button5_paddingtb', array(
                        'title' => 'Внутренний отступ сверху и снизу',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldNumber('options:button5_paddinglr', array(
                        'title' => 'Внутренний отступ слева и справа',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldNumber('options:button5_border', array(
                        'title' => 'Радиус углов кнопки',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldNumber('options:button5_fsize', array(
                        'title' => 'Размер шрифта',
                        'units' => 'px',
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldList('options:button5_ftran', array(
                        'title' => 'Трансформация текста',
                        'default' => 'none',
                        'items' => array(
                            'none' => 'Не менять регистр',
                            'capitalize' => 'Первый символ каждого слова в предложении будет заглавным',
                            'lowercase' => 'Все символы текста будут строчными (нижний регистр)',
                            'uppercase' => 'Все символы текста будут прописными (верхний регистр)'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldList('options:button5_fstyle', array(
                        'title' => 'Стиль текста',
                        'default' => 'normal',
                        'items' => array(
                            'normal' => 'Обычный стиль',
                            'italic' => 'Курсивное начертание',
                            'oblique' => 'Наклонное начертание'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldList('options:button5_fbord', array(
                        'title' => 'Стиль рамки',
                        'items' => array(
                            '' => '',
                            'none' => 'Не выводить рамку',
                            'dotted' => 'Dotted',
                            'dashed' => 'Dashed',
                            'solid' => 'Solid',
                            'double' => 'Double',
                            'groove' => 'Groove',
                            'ridge' => 'Rigge',
                            'inset' => 'Inset',
                            'outset' => 'Outset'
                        ),
                        'visible_depend' => array('options:button_list' => array('show' => array('onebuilder-button5')))
                        )),
                    new fieldNumber('options:button5_fbordt', array(
                        'title' => 'Толщина рамки',
                        'units' => 'px',
                        'visible_depend' => array('options:button5_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),
                    new fieldColor('options:button5_bcolor', array(
                        'title' => 'Цвет рамки',
                        'visible_depend' => array('options:button5_fbord' => array('show' => array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')))
                        )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'Разное',
                'childs' => array(
                    new fieldNumber('options:go_top', array(
                        'title' => 'Скорость прокрутки при скроллинге к секциям',
                        'hint' => 'В вашем проекте Вы можете использовать прокрутку к секции при клике на кнопки. Здесь можно задать скорость прокрутки',
                        'units' => 'сек'
                        )),
                    )
                ),
            array(
                'type' => 'fieldset',
                'title' => 'HELPER',
                'childs' => array(
                    new cmsFormField('font_info', array(
                        'title' => '<p style = "font-size: 15px; color: #fff; border: 1px solid #c6c6c6; padding: 15px; background-color: #005cbf; border-radius: 4px; font-weight: bold; margin-bottom: 0px !important;">Подключение шрифтов и их использование</p>',
                        'hint' => '<p style = "color: #000 !important; font-size: 14px;">OneBuilder позволяет подключить к проекту до 5 шрифтов из каталога Google Fonts. Чтобы подключить шрифт, укажите в поле его адрес. 
                        Адрес должен соответсовать такому виду: <strong>https://fonts.googleapis.com/css2?family=Oswald</strong>. Чтобы в дальнейшем использовать этот 
                        шрифт, скажем, в пресетах какой-то кнопки, достаточно в её настройках указать название шрифта <strong>Oswald</strong>.<br>
                        <img style = "border: 1px solid #000; margin-top: 10px;" src = "/upload/onebuilder/helper/1.jpg"><br>
                        Вы можете подключить к своему проекту до 5 шрифтов одновременно и использовать их повсеместно - в заголовках, текстовых блоках, кнопках и т.д.</p>',
                        'html' => ''
                    )),
                    new cmsFormField('h_info', array(
                        'title' => '<p style = "font-size: 15px; color: #fff; border: 1px solid #c6c6c6; padding: 15px; background-color: #005cbf; border-radius: 4px; font-weight: bold; margin-bottom: 0px !important;">Настройка типографики</p>',
                        'hint' => '<p style = "color: #000 !important; font-size: 14px;">Пока нет информации</p>',
                        'html' => ''
                    )),
                    new cmsFormField('button_info', array(
                        'title' => '<p style = "font-size: 15px; color: #fff; border: 1px solid #c6c6c6; padding: 15px; background-color: #005cbf; font-weight: bold; border-radius: 4px; margin-bottom: 0px !important;">Пресеты кнопок и их настройка</p>',
                        'hint' => '<p style = "color: #000 !important; font-size: 14px;">В OneBuilder можно настроить 5 пресетов для кнопок. По умолчанию первые два пресета 
                        уже настроены, однако Вы можете переопределить их стили. Для этого в выпадающем списке выберите нужный пресет и заполните настройки кнопки.<br>
                        <img style = "border: 1px solid #000; margin-top: 10px;" src = "/upload/onebuilder/helper/2.jpg"><br>
                        После настройки пресета, вы можете испольховать кнопки в виджетах OneBuilder. На данный момент в кнопках опущены некторые настройки, такие как использование теней
                        и градиентного фона, эти настройки появятся чуть позднее</p>',
                        'html' => ''
                    )),
                    )
                )
                );
            }
            
        }