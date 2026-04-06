<?php

class formLandingbuilderShellVariant extends cmsForm {

	public function init($catalog) {

		return [
			[
				'type' => 'fieldset',
				'title' => 'Идентичность варианта',
				'childs' => [
					new fieldString('title', [
						'title' => 'Название варианта',
						'rules' => [
							['required'],
							['max_length', 128]
						]
					]),
					new fieldText('description', [
						'title' => 'Краткое описание',
						'rules' => [
							['max_length', 500]
						]
					]),
					new fieldString('target_label', [
						'title' => 'Для каких страниц',
						'rules' => [
							['max_length', 128]
						]
					])
				]
			],
			[
				'type' => 'fieldset',
				'title' => 'Header и меню',
				'childs' => [
					new fieldList('header_variant', [
						'title' => 'Вариант header',
						'default' => 'classic',
						'items' => $catalog['header_variant']
					]),
					new fieldList('menu_placement', [
						'title' => 'Размещение главного меню',
						'default' => 'header_primary',
						'items' => $catalog['menu_placement']
					]),
					new fieldList('sticky_header', [
						'title' => 'Поведение header при прокрутке',
						'default' => 'off',
						'items' => $catalog['sticky_header']
					]),
					new fieldList('mobile_menu_mode', [
						'title' => 'Мобильное меню',
						'default' => 'drawer',
						'items' => $catalog['mobile_menu_mode']
					]),
					new fieldCheckbox('show_site_top', [
						'title' => 'Показывать верхнюю служебную зону',
						'default' => 0
					])
				]
			],
			[
				'type' => 'fieldset',
				'title' => 'Контентный каркас',
				'childs' => [
					new fieldCheckbox('show_hero', [
						'title' => 'Включать отдельную hero-зону',
						'default' => 0
					]),
					new fieldCheckbox('show_before_content', [
						'title' => 'Показывать зону перед контентом',
						'default' => 0
					]),
					new fieldCheckbox('show_after_content', [
						'title' => 'Показывать зону после контента',
						'default' => 0
					]),
					new fieldList('body_layout', [
						'title' => 'Схема контентной области',
						'default' => 'no_sidebars',
						'items' => $catalog['body_layout']
					]),
					new fieldList('homepage_shell_mode', [
						'title' => 'Режим главной страницы',
						'default' => 'inherit',
						'items' => $catalog['homepage_shell_mode']
					])
				]
			],
			[
				'type' => 'fieldset',
				'title' => 'Footer',
				'childs' => [
					new fieldList('footer_variant', [
						'title' => 'Вариант footer',
						'default' => 'columns_4',
						'items' => $catalog['footer_variant']
					])
				]
			]
		];
	}
}