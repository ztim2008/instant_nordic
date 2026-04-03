# Техспека: page adapters и editable zones для конструктора

## Статусы

- ⚪ Не начато
- 🟡 Запланировано
- 🔵 В работе
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Навигация

- Главный трекер: [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
- Предыдущий документ: [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
- Следующий документ: [LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md](LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md)

## 1. Цель документа

Этот документ фиксирует следующую важную часть архитектуры конструктора лендингов:

1. какие page adapters нужны в первой версии;
2. какие editable zones должны быть у страниц `news` / profile / category;
3. как выглядит opt-in модель для пользовательских типов контента;
4. какие style tokens пользователь может редактировать из sidebar.

Документ отвечает на отдельный важный вопрос пользователей:

- можно ли править на канве готовые страницы сайта;
- можно ли стилизовать существующие страницы;
- смогут ли пользовательские типы контента участвовать в конструкторе.

## 2. Основной принцип

### 🟢 Готово: принцип зафиксирован

Конструктор не должен работать как свободный DOM-редактор всей страницы.

Он должен работать через adapters.

То есть для каждой поддерживаемой страницы система знает:

- какой это page type;
- какие зоны у неё доступны для редактирования;
- где доступны только стили;
- где доступны структурные секции;
- какие данные можно привязывать динамически.

Это защищает систему от хаоса и делает UX предсказуемым.

## 3. Два режима работы поверх готовых страниц

### 🟢 Готово: подход зафиксирован

Для существующих страниц сайта нужно сразу поддерживать два разных режима.

### 3.1. Styling Overlay

Это режим, в котором пользователь не ломает структуру страницы, а меняет оформление уже существующих областей:

- шрифты;
- размеры;
- отступы;
- цвета текста;
- цвета карточек;
- цвета кнопок;
- фон зон;
- радиусы;
- тени;
- визуальные акценты.

Этот режим должен быть доступен раньше всего, потому что он безопаснее и охватывает больше страниц.

### 3.2. Structural Overlay

Это режим, в котором пользователь может не только менять стиль, но и управлять секциями вокруг основной структуры страницы:

- hero above content;
- before content section;
- after content section;
- sidebar blocks;
- CTA секции;
- related sections;
- промо-блоки и data-driven секции.

Этот режим нельзя делать “на весь DOM”.

Он должен работать через заранее описанные editable zones.

## 3.3. Общие режимы участия страницы в builder

### 🟢 Готово: режимы участия зафиксированы

Для продукта в целом нужны четыре режима:

1. `full_takeover`
2. `hybrid_overlay`
3. `zone_injection`
4. `data_only`

Для MVP рабочими считаются первые три.

`data_only` остается следующим этапом развития.

## 4. Page adapters первой версии

### 🔵 В работе: состав первой волны адаптеров определён

В первой версии рекомендуются следующие adapters.

### 4.1. `standalone_landing`

Статус: 🔴 Запланировано.

Назначение:

- полностью самостоятельная landing-страница, собранная внутри конструктора.

Роль:

- базовый и самый свободный режим конструктора;
- не зависит от существующего шаблона страницы материала или профиля.

Support level:

- styling overlay: да;
- structural overlay: да;
- manual content: да;
- dynamic content: да.

### 4.2. `content_item_generic`

Статус: 🔴 Запланировано.

Назначение:

- адаптер для стандартной страницы материала любого content type, который рендерится через общий content pipeline InstantCMS.

Это ключевой адаптер для:

- news;
- music;
- пользовательских типов контента, которые построены на стандартной content-системе.

Support level:

- styling overlay: да;
- structural overlay: да, но через ограниченные zones;
- manual content: частично;
- dynamic content: да.

Почему он важен:

- именно этот адаптер даёт ответ на вопрос “можно ли править страницу новости поверх готового сайта”.

### 4.3. `content_category_generic`

Статус: 🔴 Запланировано.

Назначение:

- адаптер для страницы категории и списка материалов типа контента.

Support level:

- styling overlay: да;
- structural overlay: да, но по ограниченным зонам;
- manual content: частично;
- dynamic content: да.

Этот адаптер особенно полезен для:

- категорий новостей;
- категорий музыки;
- каталогов пользовательских типов контента.

### 4.4. `content_list_generic`

Статус: 🔴 Запланировано.

Назначение:

- адаптер для общей страницы списка материалов без привязки к конкретной категории.

Support level:

- styling overlay: да;
- structural overlay: ограниченно;
- dynamic content: да.

### 4.5. `user_profile`

Статус: 🔴 Запланировано.

Назначение:

- адаптер для страницы профиля пользователя.

Support level:

- styling overlay: да;
- structural overlay: да, но осторожно и только по заранее разрешённым зонам;
- dynamic content: ограниченно, через данные профиля и пользовательские блоки.

Это отдельный adapter, а не generic content adapter, потому что профиль рендерится через отдельный контроллер пользователей.

### 4.6. `homepage_composed`

Статус: 🔴 Запланировано.

Назначение:

- адаптер для главной страницы проекта.

Support level:

- styling overlay: да;
- structural overlay: да;
- dynamic content: да.

Этот адаптер нужен отдельно, потому что главная часто собирается как смешанная страница с несколькими системами вывода.

## 5. Приоритет adapters для MVP

### 🟢 Готово: порядок внедрения зафиксирован

Рекомендуемый порядок реализации:

1. `standalone_landing`
2. `content_item_generic`
3. `content_category_generic`
4. `homepage_composed`
5. `user_profile`
6. `content_list_generic`

Почему так:

- это сначала даёт максимальную пользу для лендингов и страниц материалов;
- потом открывает поддержку категорий и главной;
- профиль оставляет в scope первой версии, но не делает его блокером архитектуры.

## 6. Editable zones для `news` / content item

### 🔴 Запланировано

Основа рендера:

- общий item template идёт через стандартный content item pipeline;
- ключевой шаблон по умолчанию: `site/templates/modern/content/default_item.tpl.php`.

Для первой версии адаптер `content_item_generic` должен открывать такие зоны.

### 6.1. Зоны только для styling overlay

1. `page_shell`
   - общий фон страницы;
   - max width;
   - фон контейнера;
   - вертикальный ритм.

2. `item_title_zone`
   - h1;
   - subtitle/parent title;
   - цвет заголовка;
   - spacing after title.

3. `tabs_zone`
   - nav tabs item-menu;
   - шрифты;
   - active/inactive colors;
   - border and radius.

4. `content_fields_zone`
   - поля материала;
   - field label;
   - field value;
   - fieldset heading;
   - columns gap;
   - table-like property groups.

5. `tags_zone`
   - кнопки тегов;
   - radius;
   - outline/fill colors;
   - spacing.

6. `info_bar_zone`
   - badges;
   - counters;
   - icons;
   - surface styles.

### 6.2. Зоны для structural overlay

1. `hero_before_item`
   - дополнительная секция до основного материала.

2. `before_item_body`
   - секции между заголовком и контентом.

3. `after_item_body`
   - CTA, related blocks, forms, subscription.

4. `sidebar`
   - если конкретный layout страницы поддерживает боковую колонку.

5. `footer_cta`
   - отдельная финальная секция под материалом.

### 6.3. Что не давать редактировать напрямую

1. Произвольную HTML-структуру поля.
2. Внутренний markup `field html`.
3. Любые случайные DOM-узлы без adapter contract.

## 7. Editable zones для `category` / страницы категории

### 🔴 Запланировано

Основа рендера:

- текущая логика страницы категории находится в `site/templates/modern/controllers/content/category_view.tpl.php`.

Для первой версии адаптер `content_category_generic` должен открывать такие зоны.

### 7.1. Зоны только для styling overlay

1. `category_header`
   - h1 страницы;
   - rss icon;
   - toolbar buttons;
   - spacing.

2. `datasets_zone`
   - datasets/filter panel;
   - chips/buttons;
   - active states.

3. `category_description_zone`
   - typography;
   - text width;
   - spacing below.

4. `subcategories_zone`
   - сетка карточек подкатегорий;
   - radius;
   - overlay;
   - badge styles;
   - title/meta styles.

5. `content_list_zone`
   - список материалов;
   - cards;
   - gaps;
   - list style tokens;
   - pagination styling.

### 7.2. Зоны для structural overlay

1. `hero_before_category`
2. `between_header_and_list`
3. `between_subcategories_and_list`
4. `after_list`
5. `sidebar`

### 7.3. Что не давать редактировать напрямую

1. Системную логику datasets.
2. Сырые list templates без адаптера.
3. Произвольную структуру пагинации.

## 8. Editable zones для `profile`

### 🔴 Запланировано

Основа рендера:

- `site/templates/modern/controllers/users/profile_view.tpl.php`
- `site/templates/modern/controllers/users/profile_header.tpl.php`

Профиль лучше поддерживать через отдельный adapter `user_profile`.

### 8.1. Зоны только для styling overlay

1. `profile_header`
   - avatar shell;
   - h1 nickname;
   - status line;
   - karma/rating cards;
   - tabs area.

2. `profile_left_sidebar`
   - avatar block;
   - friends list card;
   - counters list;
   - sidebar surfaces and spacing.

3. `profile_main_info`
   - system fields typography;
   - custom fieldsets;
   - titles;
   - value rows;
   - section gaps.

### 8.2. Зоны для structural overlay

Здесь важно опираться на уже существующие block points внутри шаблона:

1. `after_profile_avatar`
2. `users_profile_view_blocks`
3. `users_profile_information_blocks`
4. `users_profile_view_bottom`

Дополнительно допускаются wrapper zones:

5. `before_profile_header`
6. `after_profile_header`

### 8.3. Что не давать редактировать напрямую

1. Внутренние интерактивные элементы статуса пользователя.
2. Системную логику karma/rating.
3. Низкоуровневый DOM профиля.

## 9. Opt-in для пользовательских типов контента

### 🔵 В работе: принцип зафиксирован, детали нужно формализовать

Да, пользовательские типы контента должны участвовать в конструкторе.

Но это должно быть не магически “для всего всегда”, а через opt-in.

### 9.1. Как это должно работать

Для каждого ctype должен быть отдельный набор опций участия в конструкторе.

Дополнительно для opt-in ctype нужно уметь выбирать не только adapter, но и participation mode по умолчанию.

Предлагаемый набор:

1. `builder_enabled`
2. `builder_support_list`
3. `builder_support_item`
4. `builder_support_category`
5. `builder_adapter`
6. `builder_style_preset`

### 9.2. Базовое поведение

1. Если тип контента использует стандартный content pipeline InstantCMS, он совместим по умолчанию с generic adapters.
2. Если у типа контента есть кастомный item/list template, он всё равно может участвовать в конструкторе, но может потребоваться отдельный adapter profile.
3. Если тип контента реализован нестандартно и не идёт через обычный content pipeline, участие возможно только через отдельный adapter.

### 9.3. Рекомендуемый opt-in UI

В настройках типа контента:

1. чекбокс `Участвует в конструкторе`;
2. чекбокс `Поддержка страницы материала`;
3. чекбокс `Поддержка списка`;
4. чекбокс `Поддержка категории`;
5. select `Adapter`;
6. select `Стилевой пресет по умолчанию`.

### 9.4. Что должен уметь конструктор после opt-in

1. Использовать ctype как data source для блоков.
2. Подключать ctype list/item/category pages к builder-слою.
3. Применять style tokens для данного ctype.
4. Разрешать для этого ctype collection blocks и query-driven blocks.
4. Подключать дополнительные structural zones, если это разрешено adapter'ом.

### 9.5. Критерий готовности

- новый пользовательский ctype можно подключить к конструктору без изменений ядра.

## 10. Style tokens для sidebar

### 🔵 В работе: список безопасных токенов определён

Sidebar должен редактировать не CSS-классы, а tokens.

Рекомендуется разделить их по группам.

### 10.1. Typography tokens

1. `font.family.body`
2. `font.family.heading`
3. `font.size.body`
4. `font.size.small`
5. `font.size.h1`
6. `font.size.h2`
7. `font.size.h3`
8. `font.weight.body`
9. `font.weight.heading`
10. `line.height.body`
11. `line.height.heading`
12. `letter.spacing.heading`

### 10.2. Spacing tokens

1. `space.page.top`
2. `space.page.bottom`
3. `space.section.y`
4. `space.section.x`
5. `space.block.gap`
6. `space.card.padding`
7. `space.zone.margin.bottom`

### 10.3. Color tokens

1. `color.page.background`
2. `color.surface.background`
3. `color.surface.alt`
4. `color.text.primary`
5. `color.text.secondary`
6. `color.border`
7. `color.accent`
8. `color.accent.contrast`
9. `color.link`
10. `color.link.hover`

### 10.4. Button tokens

1. `button.primary.background`
2. `button.primary.text`
3. `button.primary.border`
4. `button.primary.hover.background`
5. `button.secondary.background`
6. `button.secondary.text`
7. `button.radius`
8. `button.padding.x`
9. `button.padding.y`
10. `button.shadow`

### 10.5. Card and block tokens

1. `card.background`
2. `card.border.color`
3. `card.border.width`
4. `card.radius`
5. `card.shadow`
6. `card.hover.shadow`
7. `card.overlay.color`

### 10.6. Media tokens

1. `media.radius`
2. `media.overlay`
3. `media.aspect.ratio`
4. `media.object.fit`

### 10.7. Layout tokens

1. `layout.container.max_width`
2. `layout.sidebar.width`
3. `layout.columns.gap`
4. `layout.sticky.sidebar`

### 10.8. Effects tokens

1. `effect.border.radius.scale`
2. `effect.shadow.scale`
3. `effect.hover.lift`
4. `effect.transition.speed`
5. `effect.section.divider`

### 10.9. Visibility tokens

1. `visibility.desktop`
2. `visibility.tablet`
3. `visibility.mobile`

## 11. Как это должно работать на практике

### 🔴 Запланировано

Для пользователя процесс должен выглядеть так:

1. Он открывает страницу новости, категории или профиля в режиме canvas.
2. Система определяет page adapter.
3. Adapter отдает список editable zones.
4. Пользователь выбирает зону.
5. В sidebar он видит только релевантные tokens и допустимые structural actions.
6. Изменения применяются как overlay layer, не ломая системный шаблон.

## 12. Ограничения первой версии

### 🟢 Готово: ограничения зафиксированы

В первой версии не рекомендуется:

1. Давать свободное редактирование произвольного DOM любых системных страниц.
2. Поддерживать все страницы сайта без adapter contracts.
3. Делать глубокий structural overlay для сложных системных страниц без отдельного адаптера.
4. Считать любой пользовательский ctype автоматически готовым к builder-режиму без opt-in.

## 13. Следующий шаг после этой техспеки

### 🔴 Запланировано

Следующий документ должен уже формализовать:

1. data model для page adapters;
2. JSON schema editable zones;
3. opt-in поля для ctype settings;
4. contract для style token scopes;
5. папки и классы нового компонента конструктора.