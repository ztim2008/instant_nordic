<?php

class formWidgetOnebuilderHeader4Options extends cmsForm{
    
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
                    new fieldImage('options:bg', array(
                        'title' => 'Фон секции <span class="badge bg-danger">!</span>',
                        'options' => array('sizes' => array('original')),
                        'rules' => array(array('required'))
                        )),
                    )
                ),
                );
            }
            
        }