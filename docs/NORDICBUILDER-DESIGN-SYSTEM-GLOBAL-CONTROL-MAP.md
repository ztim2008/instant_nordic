# Nordic Builder: Карта глобального дизайн-контроля

## Цель

Этот документ фиксирует практический scope: на какие элементы и параметры сайта дизайнер может влиять глобально через дизайн-систему, без правок ядра InstantCMS.

## 1) Что уже управляется глобально (через экран Глобальный стиль)

Источник: form/model каталоги `landingbuilder`.

1. Шаблон сайта (`template_preset`)
2. Стартовый style preset (`global_style_preset`)
3. Палитра (`color_preset`)
4. Типографика (`typography_preset`)
5. Контейнеры (`container_preset`)
6. Кнопки (`button_preset`)
7. Карточки (`card_preset`)
8. Ритм секций (`section_spacing`)
9. Радиусы (`radius_preset`)
10. Плотность интерфейса (`density_preset`)
11. Контраст (`contrast_preset`)

## 2) Доступные пресеты (фактический каталог)

1. `template_preset`:
- `nordic_classic`
- `nordic_editorial`
- `nordic_catalog`
- `nordic_warm_market`
- `nordic_compact`
- `nm_landing`

2. `global_style_preset`:
- `nordic_balanced`
- `nordic_contrast`
- `nordic_editorial`
- `nordic_catalog`

3. `color_preset`:
- `nordic_day`
- `slate_contrast`
- `forest_accent`

4. `typography_preset`:
- `editorial`
- `neutral`
- `compact`

5. `container_preset`:
- `text`
- `standard`
- `wide`
- `full`

6. `button_preset`:
- `soft_accent`
- `solid_brand`
- `ghost`

7. `card_preset`:
- `quiet`
- `raised`
- `outline`

8. `section_spacing`:
- `compact`
- `comfortable`
- `airy`

9. `radius_preset`:
- `none`
- `soft`
- `rounded`

10. `density_preset`:
- `compact`
- `balanced`
- `relaxed`

11. `contrast_preset`:
- `soft`
- `balanced`
- `strong`

## 3) Runtime токены (полный список CSS variables)

Эти переменные уже участвуют в runtime-генерации темы и являются реальным рычагом глобального визуального управления:

1. `--lb-page-background`
2. `--lb-surface-color`
3. `--lb-surface-muted`
4. `--lb-surface-soft`
5. `--lb-text-color`
6. `--lb-text-muted`
7. `--lb-heading-color`
8. `--lb-border-color`
9. `--lb-border-contrast`
10. `--lb-accent-color`
11. `--lb-accent-soft`
12. `--lb-accent-contrast`
13. `--lb-contrast-surface`
14. `--lb-contrast-text`
15. `--lb-zone-pill-background`
16. `--lb-zone-pill-color`
17. `--lb-radius-sm`
18. `--lb-radius-md`
19. `--lb-radius-lg`
20. `--lb-shadow-sm`
21. `--lb-shadow-md`
22. `--lb-shadow-lg`
23. `--lb-hero-background`
24. `--lb-font-body`
25. `--lb-font-heading`
26. `--lb-hero-title-size`
27. `--lb-button-background`
28. `--lb-button-color`
29. `--lb-button-border`
30. `--lb-card-background`
31. `--lb-card-border`
32. `--lb-card-shadow`
33. `--lb-content-gap`
34. `--lb-control-height`
35. `--lb-card-padding`
36. `--lb-button-padding-y`
37. `--lb-button-padding-x`
38. `--lb-topbar-padding-y`
39. `--lb-topbar-padding-x`
40. `--lb-page-max-width`
41. `--lb-section-gap`

## 4) Какие элементы InstantCMS уже покрыты глобальным стилем

Ниже перечислены системные элементы, которые уже получают глобальное оформление через Nordic theme layer и runtime токены:

1. Типографика:
- `h1`..`h6`
- базовый текст `body`
- ссылки `a`
- служебный/малый текст
- выделение текста `::selection`

2. Навигация и меню:
- `navbar`
- `nav-pills`
- `nav-tabs`
- `dropdown-menu`
- `page-link`
- `pagination`

3. Кнопки и маркеры:
- `btn` / `btn-primary` / `btn-outline-primary`
- `badge` / `badge-primary` / `badge-light`

4. Формы и controls:
- `form-control`
- `custom-select`
- `custom-file-label`
- focus-состояния полей

5. Контейнеры и поверхности:
- `card`
- `modal-content`
- `table`
- `list-group-item`
- `info_bar`
- `icms-comment-html`

6. Контентные паттерны:
- заголовки и текст страницы контента (`content-item`, `content-list`, `content-category`)
- `blockquote`
- карточки в листингах
- мета-информация (`item-meta`)
- теги (`content-item-tags`)

7. Сайдбары и shell:
- заголовки виджетов в sidebar
- `site_top/header/hero/footer` поверхности
- content frame, content grid, отступы shell

## 5) Что важно для продуктовой реализации

1. Дизайнер управляет системой через понятные controls (цвет, шрифт, радиус, тень, плотность), а не через raw CSS.
2. Все глобальные настройки должны применяться единообразно в canvas, preview и live.
3. Любое расширение control-scope должно идти через токены/контракты, без правок ядра InstantCMS.

## 6) Очередь расширения (следующие шаги)

Это список логичных улучшений экрана Глобальных стилей, которые усилят контроль дизайнера без вмешательства в движок:

1. Отдельные настройки шрифтовой шкалы H1/H2/H3/Body/Small (а не только preset).
2. Глобальные настройки состояний кнопок (`hover`, `active`, `disabled`) как явные controls.
3. Глобальные токены для полей форм: фон, бордер, radius, focus-ring, высота.
4. Глобальные токены для таблиц: плотность строк, бордеры, шапка.
5. Глобальные токены для навигации: высота navbar, плотность пунктов, активный индикатор.
6. Глобальные токены для sidebar widgets: заголовок, фон, отступы, разделители.
7. Глобальные токены для alerts/status colors (info/success/warn/error).

## 7) Нефункциональное правило

Канонический путь: надстройка над InstantCMS.

- Не править core для задач дизайна.
- Не создавать критическую зависимость от patch-логики шаблона.
- Все изменения проводить через runtime tokens, presets, contracts и адаптеры.

## 8) Взрослый план развития (журнальный курс)

### Почему это важно

1. Журнальные сайты держатся на типографике, ритме и цветовой иерархии, а не на случайных локальных правках.
2. Без глобального управления токенами невозможно стабильно выдерживать единый стиль на всем сайте.
3. Update-safe надстройка позволяет развивать визуальный слой без риска поломать ядро InstantCMS и без ручного ремонта после апдейтов.

### Этап 1. Token Foundation v1 (база)

Цель: закрыть 100% базовых глобальных рычагов цвета, типографики, контейнеров, радиусов и теней.

Состав:

1. Утвердить канонический token registry (`color`, `type`, `spacing`, `surface`, `states`).
2. Зафиксировать fallback-policy для каждого токена.
3. Проверить единый pipeline применения токенов: `canvas -> preview -> live`.

Критерий готовности:

1. Все базовые токены управляются из одной модели и не требуют ручного CSS для типового сценария.

### Этап 2. Global Style UI v2 (дизайнерский контроль)

Цель: дать дизайнеру понятный UI-контроль без технических ключей.

Состав:

1. Вынести controls для типографической шкалы `H1/H2/H3/Body/Small`.
2. Вынести controls для состояний кнопок (`normal/hover/active/disabled`).
3. Вынести controls для форм (`field bg/border/radius/focus`).
4. Добавить controls для таблиц и навигации (`density`, `active`, `divider`).

Критерий готовности:

1. Дизайнер управляет глобальным стилем без raw CSS и без dev-панели.

### Этап 3. Journal Presets Pack (редакционные наборы)

Цель: сделать first-class линейку пресетов для журнальных сценариев.

Состав:

1. Линейка `Editorial Light`, `Editorial Contrast`, `Business Magazine`, `Market Review`, `Compact News`.
2. Для каждого пресета: цветовая матрица, типографика, ритм, карточки, навигация, sidebar tone.
3. Поддержка асимметричных композиций (`3/9`, `4/8`, `5/7`, `7/5`, `8/4`, `9/3`) как управляемых page/section presets.

Критерий готовности:

1. Новый сайт можно стартовать выбором пресета и получить готовый журнальный baseline без ручной сборки темы.

### Этап 4. Instant Elements Coverage (полное покрытие системных элементов)

Цель: расширить покрытие глобального стиля для всех ключевых системных элементов InstantCMS.

Состав:

1. Полный mapping системных элементов (`navbar`, `forms`, `tables`, `alerts`, `widgets`, `comments`, `pagination`, `tags`).
2. Для каждой группы определить контролируемые токены и состояние в UI.
3. Добавить regression-checklist на смену пресетов и device modes.

Критерий готовности:

1. Нет "визуальных островов", которые выпадают из дизайн-системы при смене глобальных настроек.

### Этап 5. Governance и стабильность на обновлениях

Цель: закрепить долгоживущую модель сопровождения.

Состав:

1. Версионирование token-contract и preset-contract.
2. Backward-compatible миграции пресетов.
3. Обязательный smoke-проход после обновлений InstantCMS/шаблона.

Критерий готовности:

1. После обновления движка/шаблона глобальный стиль не требует аварийных ручных правок.

## 9) Definition of Done для "глобального дизайна"

Функция считается готовой только если выполнены все пункты:

1. Настройка доступна в понятном UI и не требует правки кода.
2. Настройка влияет одинаково на canvas, preview и live.
3. Настройка не требует изменений core InstantCMS.
4. Настройка покрыта smoke-checklist и не ломает существующие пресеты.

## 10) Execution-план на 2 недели (по коду, без воды)

### Легенда статусов

- 🟡 Запланировано
- 🔵 В работе
- 🟢 Сделано

### Порядок старта (что делаем первым в коде)

1. P0-01 Типографическая шкала как глобальные токены.
2. P0-02 Состояния кнопок через токены (`normal/hover/active/disabled`).
3. P0-03 Токены форм и focus-режима.

### Неделя 1

#### День 1 (P0) — контракт токенов и карта применения

- Статус: 🔵 В работе
- Задачи:
1. Утвердить финальный token-contract для `type`, `buttons`, `forms`, `nav`, `tables`.
2. Добавить недостающие ключи в runtime-генератор токенов.
3. Зафиксировать mapping "токен -> элемент" в документации.
- Файлы:
1. `templates/default/controllers/landingbuilder/runtime_theme.php`
2. `system/controllers/landingbuilder/model.php`
3. `docs/NORDICBUILDER-DESIGN-SYSTEM-GLOBAL-CONTROL-MAP.md`

#### День 2 (P0) — типографика H1/H2/H3/Body/Small

- Статус: 🟡 Запланировано
- Задачи:
1. Добавить глобальные controls типографической шкалы в экран Глобальный стиль.
2. Сохранение/нормализация новых значений в site options.
3. Применение новых токенов в базовой типографике темы.
- Файлы:
1. `system/controllers/landingbuilder/backend/forms/form_design.php`
2. `system/controllers/landingbuilder/model.php`
3. `templates/default/controllers/landingbuilder/runtime_theme.php`
4. `templates/admincoreui/controllers/landingbuilder/backend/design.tpl.php`
5. `templates/nordic/scss/theme/_base.scss`

#### День 3 (P0) — состояния кнопок

- Статус: 🟡 Запланировано
- Задачи:
1. Ввести отдельные токены для `button hover/active/disabled`.
2. Подключить их в глобальные стили `.btn-primary` и `.btn-outline-primary`.
3. Расширить превью в экране Глобальный стиль.
- Файлы:
1. `templates/default/controllers/landingbuilder/runtime_theme.php`
2. `templates/nordic/scss/theme/_components.scss`
3. `templates/admincoreui/controllers/landingbuilder/backend/design.tpl.php`

#### День 4 (P0) — формы и focus-ring

- Статус: 🟡 Запланировано
- Задачи:
1. Добавить токены для фона поля, границы, скругления, focus-ring, высоты control.
2. Применить токены к `form-control`, `custom-select`, `custom-file-label`.
3. Добавить controls в Глобальный стиль.
- Файлы:
1. `templates/default/controllers/landingbuilder/runtime_theme.php`
2. `templates/nordic/scss/theme/_components.scss`
3. `system/controllers/landingbuilder/backend/forms/form_design.php`
4. `system/controllers/landingbuilder/model.php`

#### День 5 (P1) — таблицы и навигация

- Статус: 🟡 Запланировано
- Задачи:
1. Добавить токены плотности таблиц (`thead`, `row`, `divider`).
2. Добавить токены навигации (`navbar height`, `item gap`, `active indicator`).
3. Применить токены к `table`, `pagination`, `nav-tabs`, `nav-pills`, `navbar`.
- Файлы:
1. `templates/default/controllers/landingbuilder/runtime_theme.php`
2. `templates/nordic/scss/theme/_components.scss`
3. `templates/nordic/scss/theme/_shell.scss`

#### День 6 (P1) — синхронизация mirrors

- Статус: 🟡 Запланировано
- Задачи:
1. Синхронизировать изменения в package mirrors.
2. Проверить parity source/mirror по ключевым файлам.
- Файлы:
1. `packages/landingbuilder/package/system/controllers/landingbuilder/model.php`
2. `packages/landingbuilder/package/system/controllers/landingbuilder/backend/forms/form_design.php`
3. `packages/landingbuilder/package/templates/default/controllers/landingbuilder/runtime_theme.php`
4. `packages/nordic/package/templates/nordic/scss/theme/_base.scss`
5. `packages/nordic/package/templates/nordic/scss/theme/_components.scss`
6. `packages/nordic/package/templates/nordic/scss/theme/_shell.scss`

#### День 7 (P0) — smoke и фиксация

- Статус: 🟡 Запланировано
- Задачи:
1. Пройти smoke `canvas -> preview -> live` на `/`, `/board`, `/users/1`.
2. Зафиксировать результаты и риски.
- Файлы:
1. `docs/WORKLOG.md`
2. `docs/worklogs/`

### Неделя 2

#### День 8 (P0) — Journal Presets Pack v1

- Статус: 🟡 Запланировано
- Задачи:
1. Добавить пресеты: `Editorial Light`, `Editorial Contrast`, `Business Magazine`, `Market Review`, `Compact News`.
2. Для каждого пресета описать defaults цветов, типографики, ритма и карточек.
- Файлы:
1. `system/controllers/landingbuilder/model.php`
2. `templates/default/controllers/landingbuilder/runtime_theme.php`

#### День 9 (P0) — журнальные layout-presets

- Статус: 🟡 Запланировано
- Задачи:
1. Ввести first-class пресеты композиций: `3/9`, `4/8`, `5/7`, `7/5`, `8/4`, `9/3`.
2. Подключить пресеты в page/section controls.
- Файлы:
1. `templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php`
2. `system/controllers/landingbuilder/model.php`
3. `templates/nordic/main.tpl.php`

#### День 10 (P1) — sidebar/widgets token coverage

- Статус: 🟡 Запланировано
- Задачи:
1. Добавить токены заголовков виджетов, разделителей, фонов sidebar-панелей.
2. Привести sidebar к полному соответствию выбранному глобальному пресету.
- Файлы:
1. `templates/default/controllers/landingbuilder/runtime_theme.php`
2. `templates/nordic/scss/theme/_components.scss`
3. `templates/nordic/scss/theme/_shell.scss`

#### День 11 (P1) — alerts/status colors

- Статус: 🟡 Запланировано
- Задачи:
1. Добавить глобальные токены для `info/success/warn/error`.
2. Применить их в системных alert/status элементах.
- Файлы:
1. `templates/default/controllers/landingbuilder/runtime_theme.php`
2. `templates/nordic/scss/theme/_components.scss`
3. `templates/admincoreui/controllers/landingbuilder/backend/design.tpl.php`

#### День 12 (P1) — coverage audit

- Статус: 🟡 Запланировано
- Задачи:
1. Пройти аудит покрытия системных элементов и отметить пробелы.
2. Обновить карту контроля по факту покрытия.
- Файлы:
1. `docs/NORDICBUILDER-DESIGN-SYSTEM-GLOBAL-CONTROL-MAP.md`
2. `docs/WORKLOG.md`

#### День 13 (P0) — governance

- Статус: 🟡 Запланировано
- Задачи:
1. Зафиксировать версионирование token-contract и preset-contract.
2. Описать правила backward-compatible миграций.
- Файлы:
1. `docs/NORDICBUILDER-DESIGN-SYSTEM-GLOBAL-CONTROL-MAP.md`
2. `LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md`

#### День 14 (P0) — релизная фиксация этапа

- Статус: 🟡 Запланировано
- Задачи:
1. Финальный smoke по матрице preset/device/page-type.
2. Закрывающая запись в WORKLOG + checkpoint.
- Файлы:
1. `docs/WORKLOG.md`
2. `docs/worklogs/`

## 11) Операционный формат трекинга

1. Каждую задачу из плана вести в одном из трех статусов: 🟡/🔵/🟢.
2. После каждого дня обновлять статус прямо в этом файле.
3. После завершения дня делать короткую запись в `docs/WORKLOG.md`.
