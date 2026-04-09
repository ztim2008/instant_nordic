<?php

class formNordicbuilderInstantGlobal extends cmsForm {

	public function init($catalog) {

		return [
			[
				'type' => 'fieldset',
				'title' => 'InstantCMS: глобальные настройки сайта',
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
					]),
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
			]
		];
	}
}
