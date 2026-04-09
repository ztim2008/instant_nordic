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
	],
	'pro.flex-composer' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'pro.flex-composer',
		'title'          => 'PRO: Гибкий компоновщик',
		'category'       => 'content',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['overlay', 'canvas']
		],
		'render'         => [
			'runtime_component' => 'pro-flex-composer',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок'],
			['key' => 'text', 'type' => 'string', 'title' => 'Текст'],
			['key' => 'layout_mode', 'type' => 'string', 'title' => 'Структура блока'],
			['key' => 'columns_ratio', 'type' => 'string', 'title' => 'Пропорция колонок'],
			['key' => 'content_align', 'type' => 'string', 'title' => 'Выравнивание'],
			['key' => 'button_label', 'type' => 'string', 'title' => 'Основная кнопка'],
			['key' => 'secondary_label', 'type' => 'string', 'title' => 'Вторичная кнопка'],
			['key' => 'features_text', 'type' => 'string', 'title' => 'Список акцентов'],
			['key' => 'image_url', 'type' => 'string', 'title' => 'Картинка'],
			['key' => 'background_mode', 'type' => 'string', 'title' => 'Фон секции'],
			['key' => 'bg_color_start', 'type' => 'string', 'title' => 'Цвет фона 1'],
			['key' => 'bg_color_end', 'type' => 'string', 'title' => 'Цвет фона 2'],
			['key' => 'accent_color', 'type' => 'string', 'title' => 'Акцент'],
			['key' => 'surface_mode', 'type' => 'string', 'title' => 'Подложка'],
			['key' => 'padding_y', 'type' => 'number', 'title' => 'Padding по Y'],
			['key' => 'gap', 'type' => 'number', 'title' => 'Gap'],
			['key' => 'radius', 'type' => 'number', 'title' => 'Радиус']
		],
		'defaults'       => [
			'title'           => 'Гибкий блок с управлением структурой и визуалом',
			'text'            => 'Меняйте раскладку, цветовую систему, подложку, акценты и поведение медиа без правки шаблона.',
			'layout_mode'     => 'split-left',
			'columns_ratio'   => '6-6',
			'content_align'   => 'left',
			'button_label'    => 'Оставить заявку',
			'secondary_label' => 'Смотреть кейсы',
			'features_text'   => "SEO-ready\nБыстрый runtime\nГибкая композиция",
			'show_media'      => 1,
			'image_url'       => '',
			'background_mode' => 'gradient',
			'bg_color_start'  => '#0f172a',
			'bg_color_end'    => '#1d4ed8',
			'accent_color'    => '#22c55e',
			'surface_mode'    => 'glass',
			'padding_y'       => 56,
			'gap'             => 28,
			'radius'          => 22
		],
		'meta'           => [
			'default_label'     => 'Гибкий компоновщик',
			'summary'           => 'Универсальная PRO-секция с полной настройкой структуры, цветов и медиа.',
			'migration_aliases' => ['pro компоновщик', 'гибкий hero', 'flex composer', 'adaptive hero']
		]
	],
	'pro.metrics-grid-pro' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'pro.metrics-grid-pro',
		'title'          => 'PRO: Метрики и карточки',
		'category'       => 'content',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['overlay', 'canvas']
		],
		'render'         => [
			'runtime_component' => 'pro-metrics-grid',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок'],
			['key' => 'text', 'type' => 'string', 'title' => 'Пояснение'],
			['key' => 'items_text', 'type' => 'string', 'title' => 'Показатели'],
			['key' => 'data_source_mode', 'type' => 'string', 'title' => 'Источник данных'],
			['key' => 'data_ctype_name', 'type' => 'string', 'title' => 'Тип контента'],
			['key' => 'data_limit', 'type' => 'number', 'title' => 'Лимит'],
			['key' => 'data_sort', 'type' => 'string', 'title' => 'Сортировка'],
			['key' => 'data_value_field', 'type' => 'string', 'title' => 'Поле значения'],
			['key' => 'data_label_field', 'type' => 'string', 'title' => 'Поле подписи'],
			['key' => 'data_note_field', 'type' => 'string', 'title' => 'Поле заметки'],
			['key' => 'columns', 'type' => 'string', 'title' => 'Колонки'],
			['key' => 'card_style', 'type' => 'string', 'title' => 'Стиль карточек'],
			['key' => 'section_bg', 'type' => 'string', 'title' => 'Фон секции'],
			['key' => 'card_bg', 'type' => 'string', 'title' => 'Фон карточки'],
			['key' => 'value_color', 'type' => 'string', 'title' => 'Цвет значения'],
			['key' => 'label_color', 'type' => 'string', 'title' => 'Цвет подписи'],
			['key' => 'note_color', 'type' => 'string', 'title' => 'Цвет заметки'],
			['key' => 'accent_color', 'type' => 'string', 'title' => 'Акцент'],
			['key' => 'border_color', 'type' => 'string', 'title' => 'Граница'],
			['key' => 'radius', 'type' => 'number', 'title' => 'Радиус'],
			['key' => 'gap', 'type' => 'number', 'title' => 'Gap']
		],
		'defaults'       => [
			'title'       => 'Результаты в цифрах',
			'text'        => 'Коротко и наглядно покажите эффективность, сроки и качество работы.',
			'items_text'  => "1200|Лидов в месяц|Среднее за квартал\n4.9|Рейтинг|На основании 840 отзывов\n18 мин|Ответ менеджера|Средний SLA",
			'data_source_mode' => 'manual',
			'data_ctype_name'  => '',
			'data_limit'       => 6,
			'data_sort'        => 'date_desc',
			'data_value_field' => 'id',
			'data_label_field' => 'title',
			'data_note_field'  => 'date_pub',
			'columns'     => '3',
			'card_style'  => 'soft',
			'section_bg'  => '#f8fafc',
			'card_bg'     => '#ffffff',
			'value_color' => '#0f172a',
			'label_color' => '#334155',
			'note_color'  => '#64748b',
			'accent_color'=> '#2563eb',
			'border_color'=> '#dbeafe',
			'radius'      => 16,
			'padding_y'   => 44,
			'gap'         => 18
		],
		'meta'           => [
			'default_label'     => 'Метрики PRO',
			'summary'           => 'Гибкая сетка метрик и доверительных цифр с настраиваемыми карточками.',
			'migration_aliases' => ['kpi', 'метрики', 'цифры', 'достижения']
		]
	],
	'pro.faq-adaptive-pro' => [
		'kind'           => 'nordicbuilder.block',
		'schema_version' => '1.0',
		'key'            => 'pro.faq-adaptive-pro',
		'title'          => 'PRO: FAQ адаптивный',
		'category'       => 'content',
		'supports'       => [
			'canvas_node_kinds' => ['block'],
			'page_types'        => ['standalone', 'system_overlay', 'ctype_overlay'],
			'editor_modes'      => ['overlay', 'canvas']
		],
		'render'         => [
			'runtime_component' => 'pro-faq-adaptive',
			'surface_modes'     => ['runtime', 'overlay']
		],
		'props_schema'   => [
			['key' => 'title', 'type' => 'string', 'title' => 'Заголовок'],
			['key' => 'text', 'type' => 'string', 'title' => 'Подзаголовок'],
			['key' => 'items_text', 'type' => 'string', 'title' => 'Вопросы и ответы'],
			['key' => 'data_source_mode', 'type' => 'string', 'title' => 'Источник данных'],
			['key' => 'data_ctype_name', 'type' => 'string', 'title' => 'Тип контента'],
			['key' => 'data_limit', 'type' => 'number', 'title' => 'Лимит'],
			['key' => 'data_sort', 'type' => 'string', 'title' => 'Сортировка'],
			['key' => 'data_question_field', 'type' => 'string', 'title' => 'Поле вопроса'],
			['key' => 'data_answer_field', 'type' => 'string', 'title' => 'Поле ответа'],
			['key' => 'layout_mode', 'type' => 'string', 'title' => 'Раскладка'],
			['key' => 'open_first', 'type' => 'boolean', 'title' => 'Открывать первый'],
			['key' => 'section_bg', 'type' => 'string', 'title' => 'Фон секции'],
			['key' => 'question_bg', 'type' => 'string', 'title' => 'Фон вопроса'],
			['key' => 'question_color', 'type' => 'string', 'title' => 'Цвет вопроса'],
			['key' => 'answer_color', 'type' => 'string', 'title' => 'Цвет ответа'],
			['key' => 'border_color', 'type' => 'string', 'title' => 'Граница'],
			['key' => 'accent_color', 'type' => 'string', 'title' => 'Акцент'],
			['key' => 'radius', 'type' => 'number', 'title' => 'Радиус'],
			['key' => 'gap', 'type' => 'number', 'title' => 'Gap']
		],
		'defaults'       => [
			'title'         => 'Частые вопросы',
			'text'          => 'Собрали ответы на ключевые вопросы до старта проекта.',
			'items_text'    => "Сколько длится запуск?|Обычно 5-10 рабочих дней.\nЕсть ли поддержка?|Да, сопровождение включено.\nМожно ли интегрировать CRM?|Да, подключаем любую популярную CRM.",
			'data_source_mode'   => 'manual',
			'data_ctype_name'    => '',
			'data_limit'         => 6,
			'data_sort'          => 'date_desc',
			'data_question_field'=> 'title',
			'data_answer_field'  => 'teaser',
			'layout_mode'   => 'single',
			'open_first'    => 1,
			'section_bg'    => '#ffffff',
			'question_bg'   => '#f8fafc',
			'question_color'=> '#0f172a',
			'answer_color'  => '#334155',
			'border_color'  => '#e2e8f0',
			'accent_color'  => '#2563eb',
			'radius'        => 14,
			'padding_y'     => 40,
			'gap'           => 12
		],
		'meta'           => [
			'default_label'     => 'FAQ PRO',
			'summary'           => 'FAQ-секция с адаптивной раскладкой, аккордеоном и цветовым контролем.',
			'migration_aliases' => ['faq pro', 'частые вопросы', 'аккордеон', 'faq адаптивный']
		]
	]
];