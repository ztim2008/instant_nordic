<?php

class formNordicbuilderTokens extends cmsForm {

	public function init($catalog) {

		return [
			[
				'type' => 'fieldset',
				'title' => 'Токены будущих блоков',
				'childs' => [
					new fieldList('button_preset', [
						'title' => 'Кнопки блоков',
						'default' => 'soft_accent',
						'items' => $catalog['button_preset']
					]),
					new fieldList('card_preset', [
						'title' => 'Карточки блоков',
						'default' => 'quiet',
						'items' => $catalog['card_preset']
					]),
					new fieldList('surface_preset', [
						'title' => 'Поверхности блоков',
						'default' => 'neutral',
						'items' => $catalog['surface_preset']
					]),
					new fieldList('radius_preset', [
						'title' => 'Скругления блоков',
						'default' => 'none',
						'items' => $catalog['radius_preset']
					]),
					new fieldList('density_preset', [
						'title' => 'Плотность блоков',
						'default' => 'balanced',
						'items' => $catalog['density_preset']
					]),
					new fieldList('contrast_preset', [
						'title' => 'Контраст блоков',
						'default' => 'balanced',
						'items' => $catalog['contrast_preset']
					])
				]
			]
		];
	}
}
