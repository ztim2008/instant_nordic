<?php

class formLandingbuilderDesign extends cmsForm {

	public function init($catalog) {

		return [
			[
				'type' => 'fieldset',
				'title' => 'Шаблон сайта и палитра',
				'childs' => [
					new fieldList('template_preset', [
						'title' => 'Шаблон сайта',
						'default' => 'nordic_classic',
						'items' => $catalog['template_preset']
					]),
					new fieldList('global_style_preset', [
						'title' => 'Базовый пресет сайта',
						'default' => 'nordic_balanced',
						'items' => $catalog['global_style_preset']
					]),
					new fieldList('color_preset', [
						'title' => 'Палитра сайта',
						'default' => 'nordic_day',
						'items' => $catalog['color_preset']
					])
				]
			],
			[
				'type' => 'fieldset',
				'title' => 'Типографика и контейнеры',
				'childs' => [
					new fieldList('typography_preset', [
						'title' => 'Шрифтовая пара',
						'default' => 'editorial',
						'items' => $catalog['typography_preset']
					]),
					new fieldList('container_preset', [
						'title' => 'Контейнеры сайта',
						'default' => 'standard',
						'items' => $catalog['container_preset']
					]),
					new fieldList('section_spacing', [
						'title' => 'Ритм между секциями',
						'default' => 'comfortable',
						'items' => $catalog['section_spacing']
					])
				]
			],
			[
				'type' => 'fieldset',
				'title' => 'Компоненты',
				'childs' => [
					new fieldList('button_preset', [
						'title' => 'Кнопки сайта',
						'default' => 'soft_accent',
						'items' => $catalog['button_preset']
					]),
					new fieldList('card_preset', [
						'title' => 'Карточки сайта',
						'default' => 'quiet',
						'items' => $catalog['card_preset']
					])
				]
			]
		];
	}
}