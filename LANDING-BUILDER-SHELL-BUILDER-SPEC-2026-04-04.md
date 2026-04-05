# Техспека: Shell Builder Nordic

## Зачем этот документ

Этот документ фиксирует отдельный слой продукта, которого пока нет в UI, но без которого Нордик не станет полноценным theme builder.

Речь идет не о page canvas, а о builder для самого shell сайта.

## 1. Что такое Shell Builder

`Shell Builder` это отдельный продуктовый экран Нордик, который управляет постоянным каркасом сайта.

Он должен отвечать за:

1. header;
2. footer;
3. menu zones;
4. global slots;
5. homepage shell layout;
6. mapping system content в slots шаблона `nordic`.

## 2. Что входит в shell Nordic

Канонический shell Nordic уже зафиксирован в [templates/nordic/shell_scheme.php](templates/nordic/shell_scheme.php).

Базовый состав слотов:

1. `site_top`;
2. `header_primary`;
3. `header_secondary`;
4. `hero`;
5. `before_content`;
6. `content_body`;
7. `content_sidebar_left`;
8. `content_sidebar_right`;
9. `after_content`;
10. `footer_primary`;
11. `footer_secondary`.

## 3. Что должен уметь Shell Builder

### 3.1. Header Builder

Для MVP он должен уметь:

1. включать и отключать `site_top`;
2. выбирать layout variant для `header_primary`;
3. выбирать layout variant для `header_secondary`;
4. определять, где живет основное меню;
5. определять, где живут logo, actions, search, user links;
6. выбирать sticky behavior header.

### 3.2. Footer Builder

Для MVP он должен уметь:

1. выбирать layout variant для `footer_primary`;
2. выбирать layout variant для `footer_secondary`;
3. задавать количество колонок footer-зоны;
4. задавать, какие slot areas отданы под меню, контакты, copyright и widgets.

### 3.3. Menu Layer

Для MVP нужно поддержать не редактирование самих пунктов меню InstantCMS, а shell-level размещение menu widgets.

То есть Shell Builder должен отвечать на вопросы:

1. в каком slot показывается главное меню;
2. в каком slot показывается вторичное меню;
3. где живут служебные links;
4. где живет mobile menu trigger.

### 3.4. Global Slot Composition

Shell Builder должен управлять не только header и footer, но и глобальной slot-композицией страницы:

1. использовать ли `hero` как отдельный shell slot;
2. показывать ли `before_content` и `after_content`;
3. включать ли левый и правый sidebar;
4. какой slot считается каноническим body slot для системного контента.

## 4. Особый сценарий: homepage layout

Главная страница в Нордик должна считаться особым shell-сценарием, а не просто одной обычной page schema.

Для homepage нужно отдельно уметь задавать:

1. использовать ли глобальный hero slot;
2. идет ли hero до page sections или заменяется page-level first screen;
3. живут ли promo zones в shell или в page builder;
4. где и как встраивается системный контент homepage, если он нужен;
5. какой shell variant использует именно главная.

## 5. Чего Shell Builder делать не должен

Он не должен:

1. редактировать текст, карточки и layout каждой секции страницы;
2. подменять page builder;
3. становиться редактором CSS-классов;
4. заставлять пользователя работать с raw `layout_rows`, `layout_cols`, `pos_*` и legacy positions.

## 6. Режимы работы Shell Builder

Нужно различать три уровня shell-конфигурации:

1. global site shell;
2. shell variant для группы страниц;
3. page-level shell override.

### 6.1. Global site shell

Это базовый shell сайта, который применяется по умолчанию.

### 6.2. Shell variant

Это отдельный вариант shell для:

1. homepage;
2. content pages;
3. category pages;
4. profile pages;
5. landing pages.

### 6.3. Page-level override

Это редкое исключение, когда конкретная builder page переопределяет shell behavior локально.

Для MVP это можно ограничить минимумом.

## 7. Каноническая модель экрана Shell Builder

Экран должен быть отдельным от page canvas.

### 7.1. Основные зоны экрана

1. верхняя панель shell-level действий;
2. список shell variants слева;
3. central shell preview;
4. inspector справа.

### 7.2. Что должно быть в левой панели

1. `Базовый shell сайта`;
2. `Главная`;
3. `Материалы`;
4. `Категории`;
5. `Профили`;
6. `Лендинги`;
7. будущие custom shell variants.

### 7.3. Что должно быть в inspector

1. variant name;
2. header variant;
3. footer variant;
4. hero slot mode;
5. body layout mode;
6. left/right sidebar mode;
7. menu placement;
8. mobile shell behavior;
9. sticky header behavior;
10. shell-specific token hooks.

## 8. Что нужно хранить в данных

Для shell builder нужна отдельная contract-модель, а не только `page.schema.sections`.

Минимальные сущности:

1. `shell_variant`;
2. `header_variant`;
3. `footer_variant`;
4. `slot_layout`;
5. `menu_placement`;
6. `body_layout_mode`;
7. `hero_mode`;
8. `shell_token_overrides`.

## 9. MVP состав Shell Builder

Чтобы не сорваться в слишком большой scope, MVP Shell Builder должен быть контролируемым.

Для первой версии достаточно:

1. variant selector для header;
2. variant selector для footer;
3. menu placement selector;
4. toggles для `hero`, `before_content`, `after_content`;
5. body layout selector: `без сайдбаров`, `с левым`, `с правым`, `с двумя`;
6. homepage shell mode selector;
7. preview shell map без raw CMS-терминов.

### 9.1. Что не делать в MVP

Не нужно в первой версии делать:

1. freeform drag-and-drop header builder;
2. визуальный конструктор каждой micro-zone;
3. произвольный grid editor shell slots;
4. ручной редактор HTML/CSS в shell UI по умолчанию.

## 10. Связь с Design System / Global Defaults

Shell Builder и слой `design system / global defaults` должны быть связанными, но не сливаться в один редактор.

Shell Builder отвечает за структуру.

`Глобальные стили` остаются только secondary UI над token-layer, который отвечает за site-wide defaults.

Примеры:

1. `header_variant = split_navigation` это задача Shell Builder;
2. `header_background = dark glass` это задача global defaults или shell token override;
3. `footer_columns = 4` это задача Shell Builder;
4. `footer_typography = compact contrast` это задача global defaults.

## 11. Связь с Visual Builder Workspace

Visual Builder Workspace не должен конкурировать с Shell Builder.

Канонический порядок такой:

1. Shell Builder задает рамку страницы;
2. `design system / global defaults` задает глобальный visual language;
3. Visual Builder Workspace наполняет разрешенные зоны страницами, секциями, блоками и элементами.

## 12. Связь с participation modes

Participation modes не заменяют shell builder.

Они определяют только то, как builder content подключается к конкретной странице:

1. `full_takeover` использует shell максимально полно;
2. `hybrid_overlay` использует shell частично и сохраняет native content;
3. `zone_injection` работает только по разрешенным зонам.

Shell Builder находится уровнем выше этих режимов.

## 13. Как это должно появиться в backend-компоненте

В backend меню Нордик должен появиться отдельный экран:

1. `Shell Builder`.

Он не должен прятаться внутри `Страницы` или `Опции`.

## 14. Критерии качества MVP Shell Builder

MVP можно считать состоявшимся, если одновременно выполняется всё ниже:

1. пользователь понимает, что редактирует не страницу, а каркас сайта;
2. header и footer можно переключать по вариантам без правки шаблонов вручную;
3. menu placement управляется из продукта, а не из хаотичных bind positions;
4. homepage имеет отдельный shell mode;
5. shell preview не показывает raw InstantCMS-технику;
6. page canvas продолжает жить отдельно и не ломается от shell-level решений.

## 15. Канонический следующий выбор после этой спеки

После этой спеки следующим практическим шагом в новой взрослой модели считаются четыре направления:

1. foundation layer: contracts, tokens и boundary компонента `nordicbuilder`;
2. MVP `Shell Builder` как UI для header, footer, menu placement и homepage shell mode;
3. secondary UI `Глобальные стили` как оболочка над global defaults;
4. `Visual Builder Workspace` как главный ежедневный экран.

Дальше шлифовать только page canvas без привязки к одному из этих четырех направлений считать bridge-работой, а не финальным продуктовым курсом.