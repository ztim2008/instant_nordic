<?php

return [
	'core.text' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'core.text',
		'title'          => 'Текст',
		'category'       => 'content',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['canvas', 'overlay']
		],
		'render'         => [
			'runtime_component' => 'text',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок'],
			['key' => 'text', 'type' => 'string', 'title' => 'Текст']
		],
		'defaults'       => [
			'title' => 'Заголовок секции',
			'text'  => 'Здесь будет ваш текст. Секции помогают собрать страницу простыми смысловыми блоками.'
		],
		'meta'           => [
			'default_label'     => 'Текст',
			'summary'           => 'Базовый текстовый блок: заголовок + абзац(ы).',
			'migration_aliases' => ['текст', 'описание', 'paragraph', 'text']
		]
	],
	'core.raw-html' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'core.raw-html',
		'title'          => 'HTML',
		'category'       => 'content',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['canvas', 'overlay']
		],
		'render'         => [
			'runtime_component' => 'raw-html',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'html', 'type' => 'string', 'title' => 'HTML']
		],
		'defaults'       => [
			'html' => '<p>HTML-блок. Вставьте сюда разметку.</p>'
		],
		'meta'           => [
			'default_label'     => 'HTML',
			'summary'           => 'Вставка произвольной HTML-разметки. Используйте аккуратно.',
			'migration_aliases' => ['html', 'raw html', 'вставка html']
		]
	],
	'core.hero' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'core.hero',
		'title'          => 'Первый экран (Hero)',
		'category'       => 'hero',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['canvas', 'overlay']
		],
		'render'         => [
			'runtime_component' => 'hero',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'eyebrow', 'type' => 'string', 'title' => 'Надзаголовок'],
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок'],
			['key' => 'text', 'type' => 'string', 'title' => 'Подзаголовок'],
			['key' => 'button_label', 'type' => 'string', 'title' => 'Текст кнопки'],
			['key' => 'button_url', 'type' => 'string', 'title' => 'Ссылка кнопки'],
			['key' => 'image_url', 'type' => 'string', 'title' => 'Картинка (URL)']
		],
		'defaults'       => [
			'eyebrow'      => 'Нордик Builder',
			'title'        => 'Сильный заголовок первого экрана',
			'text'         => 'Короткое пояснение, которое помогает понять предложение с первого взгляда.',
			'button_label' => 'Начать',
			'button_url'   => '',
			'image_url'    => ''
		],
		'meta'           => [
			'default_label'     => 'Первый экран',
			'summary'           => 'Hero-блок: градиентный фон, сильный заголовок и одна кнопка.',
			'migration_aliases' => ['hero', 'hero block', 'первый экран', 'главный экран']
		]
	],
	'custom.raw-block' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'custom.raw-block',
		'title'          => 'Пользовательский блок',
		'category'       => 'custom',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['canvas', 'overlay']
		],
		'render'         => [
			'runtime_component' => 'custom-raw-block',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок'],
			['key' => 'text', 'type' => 'string', 'title' => 'Текст']
		],
		'defaults'       => [
			'title' => '',
			'text'  => ''
		],
		'meta'           => [
			'default_label'     => 'Пользовательский блок',
			'summary'           => 'Резервный semantic-контракт для нестандартных блоков вне базового каталога.',
			'migration_aliases' => []
		]
	],
	'core.hero-heading' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'core.hero-heading',
		'title'          => 'Главный экран с заголовком',
		'category'       => 'hero',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['canvas', 'overlay']
		],
		'render'         => [
			'runtime_component' => 'hero-heading',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'eyebrow', 'type' => 'string', 'title' => 'Надзаголовок'],
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок'],
			['key' => 'text', 'type' => 'string', 'title' => 'Подзаголовок']
		],
		'defaults'       => [
			'eyebrow' => 'Нордик Builder',
			'title'   => 'Сильный заголовок первого экрана',
			'text'    => 'Короткое пояснение, которое помогает понять предложение с первого взгляда.'
		],
		'meta'           => [
			'default_label'     => 'Главный экран',
			'summary'           => 'Первый экран с сильным обещанием, подводкой и верхним акцентом.',
			'migration_aliases' => ['главный заголовок', 'первый экран', 'hero heading', 'hero title']
		]
	],
	'core.hero-actions' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'core.hero-actions',
		'title'          => 'Главный экран с кнопками',
		'category'       => 'hero',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['canvas', 'overlay']
		],
		'render'         => [
			'runtime_component' => 'hero-actions',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок блока'],
			['key' => 'text', 'type' => 'string', 'title' => 'Пояснение'],
			['key' => 'primary_label', 'type' => 'string', 'title' => 'Основная кнопка'],
			['key' => 'secondary_label', 'type' => 'string', 'title' => 'Дополнительная кнопка']
		],
		'defaults'       => [
			'title'           => 'Готовы перейти к следующему шагу?',
			'text'            => 'Добавьте короткое пояснение, что произойдет после нажатия.',
			'primary_label'   => 'Оставить заявку',
			'secondary_label' => 'Узнать подробнее'
		],
		'meta'           => [
			'default_label'     => 'Призыв к действию',
			'summary'           => 'CTA-блок с пояснением и одной или двумя кнопками.',
			'migration_aliases' => ['кнопки первого экрана', 'cta', 'призыв к действию', 'главное действие']
		]
	],
	'core.cards-grid' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'core.cards-grid',
		'title'          => 'Сетка карточек',
		'category'       => 'content',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['canvas', 'overlay']
		],
		'render'         => [
			'runtime_component' => 'cards-grid',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок блока'],
			['key' => 'text', 'type' => 'string', 'title' => 'Пояснение'],
			['key' => 'items_text', 'type' => 'string', 'title' => 'Карточки по строкам']
		],
		'defaults'       => [
			'title'      => 'Сетка карточек',
			'text'       => 'Соберите здесь ключевые карточки страницы: услуги, тарифы или преимущества.',
			'items_text' => "Ключевое предложение\nВторой смысловой акцент\nТретий важный блок"
		],
		'meta'           => [
			'default_label'     => 'Карточки',
			'summary'           => 'Набор карточек для преимуществ, тарифов, услуг или подборок.',
			'migration_aliases' => ['карточка', 'карточки', 'сетка карточек', 'тарифы', 'отзывы', 'логотипы', 'медиа']
		]
	],
	'core.feature-list' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'core.feature-list',
		'title'          => 'Список преимуществ',
		'category'       => 'content',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['canvas', 'overlay']
		],
		'render'         => [
			'runtime_component' => 'feature-list',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок блока'],
			['key' => 'items_text', 'type' => 'string', 'title' => 'Пункты по строкам']
		],
		'defaults'       => [
			'title'      => 'Почему это работает',
			'items_text' => "Короткий сильный тезис\nВторой аргумент доверия\nПонятный следующий шаг"
		],
		'meta'           => [
			'default_label'     => 'Преимущества',
			'summary'           => 'Короткий список тезисов, причин выбрать продукт или формат работы.',
			'migration_aliases' => ['список преимуществ', 'преимущества', 'faq', 'контакты', 'особенности']
		]
	],
	'ads.category-header' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'ads.category-header',
		'title'          => 'Шапка категории объявлений',
		'category'       => 'overlay',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['overlay', 'canvas']
		],
		'render'         => [
			'runtime_component' => 'ads-category-header',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'eyebrow', 'type' => 'string', 'title' => 'Надзаголовок'],
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок'],
			['key' => 'text', 'type' => 'string', 'title' => 'Пояснение']
		],
		'defaults'       => [
			'eyebrow' => 'Категория',
			'title'   => 'Шапка категории объявлений',
			'text'    => 'Добавьте вводный контекст перед системным списком и фильтрами.'
		],
		'meta'           => [
			'default_label'     => 'Шапка категории',
			'summary'           => 'Контекстный верхний блок для категории, фильтров и вводного текста.',
			'migration_aliases' => ['шапка категории', 'категория объявлений', 'header категории']
		]
	],
	'ads.filter-bar' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'ads.filter-bar',
		'title'          => 'Панель фильтров',
		'category'       => 'overlay',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['overlay', 'canvas']
		],
		'render'         => [
			'runtime_component' => 'ads-filter-bar',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок блока'],
			['key' => 'items_text', 'type' => 'string', 'title' => 'Фильтры по строкам']
		],
		'defaults'       => [
			'title'      => 'Быстрые уточнения',
			'items_text' => "Новые\nС доставкой\nПроверенные продавцы"
		],
		'meta'           => [
			'default_label'     => 'Фильтры',
			'summary'           => 'Лента быстрых фильтров, уточнений или подсказок для списка.',
			'migration_aliases' => ['фильтры', 'панель фильтров', 'панель отбора', 'быстрые уточнения']
		]
	],
	'profile.cover-hero' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'profile.cover-hero',
		'title'          => 'Обложка профиля',
		'category'       => 'profile',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['system_overlay', 'ctype_overlay', 'standalone'],
			'editor_modes'      => ['overlay', 'canvas']
		],
		'render'         => [
			'runtime_component' => 'profile-cover-hero',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'eyebrow', 'type' => 'string', 'title' => 'Надзаголовок'],
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок'],
			['key' => 'text', 'type' => 'string', 'title' => 'Пояснение']
		],
		'defaults'       => [
			'eyebrow' => 'Профиль',
			'title'   => 'Имя профиля или компании',
			'text'    => 'Добавьте краткое описание, специализацию или ключевое позиционирование.'
		],
		'meta'           => [
			'default_label'     => 'Обложка профиля',
			'summary'           => 'Крупный верхний блок профиля с именем, подводкой и визуальным акцентом.',
			'migration_aliases' => ['обложка профиля', 'профиль', 'cover profile']
		]
	],
	'profile.quick-stats' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'profile.quick-stats',
		'title'          => 'Короткая статистика профиля',
		'category'       => 'profile',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['system_overlay', 'ctype_overlay', 'standalone'],
			'editor_modes'      => ['overlay', 'canvas']
		],
		'render'         => [
			'runtime_component' => 'profile-quick-stats',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок блока'],
			['key' => 'items_text', 'type' => 'string', 'title' => 'Показатели по строкам']
		],
		'defaults'       => [
			'title'      => 'Ключевые показатели',
			'items_text' => "120|завершенных заказов\n4.9|средний рейтинг\n7 лет|на рынке"
		],
		'meta'           => [
			'default_label'     => 'Статистика',
			'summary'           => 'Набор коротких показателей профиля в компактной сетке.',
			'migration_aliases' => ['статистика профиля', 'показатель', 'стат', 'рейтинг']
		]
	]
];