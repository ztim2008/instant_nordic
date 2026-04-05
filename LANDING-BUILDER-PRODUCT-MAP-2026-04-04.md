# Каноническая карта продукта Нордик

## Зачем этот документ

Этот документ фиксирует взрослую карту продукта после visual-first pivot и отдельной архитектурной переоценки.

Он нужен, чтобы больше не смешивать в одну сущность:

1. backend InstantCMS;
2. runtime шаблона;
3. design system;
4. visual builder;
5. библиотеку компонентов;
6. слой системных widgets и data adapters.

Главный практический вывод:

Нордик это не экран `Дизайн сайта` и не canvas сам по себе.

Нордик это отдельный продуктовый слой над InstantCMS 2.

## 1. Короткая формула продукта

Нордик = `InstantCMS 2 backend + nordic template runtime + design system / global defaults + visual builder workspace + component library + widget/data adapter layer`.

Это и есть новая каноническая формула продукта.

### 1.1. Что это означает на практике

1. InstantCMS 2 остается CMS-основанием и не превращается в builder.
2. `nordic` отвечает за публичный runtime, shell и итоговый frontend.
3. `Глобальные стили` это UI над design system, но не главный ежедневный маршрут.
4. Главный ежедневный продуктовый экран это visual builder workspace.
5. Пользователь собирает страницу из контролируемых sections и blocks, а не из raw rows и cols.
6. Системные widgets и данные InstantCMS подключаются через отдельный adapter-слой.

## 2. Канонические слои продукта

### 2.1. `InstantCMS 2 Backend Layer`

Этот слой отвечает за:

1. routing;
2. users и permissions;
3. content types;
4. categories;
5. стандартные widgets;
6. SEO и системные страницы.

Это фундамент продукта, но не builder-экран.

### 2.2. `Nordic Template Runtime`

Этот слой отвечает за:

1. публичный shell сайта;
2. header и footer;
3. slot-зоны;
4. homepage shell modes;
5. runtime rendering builder pages;
6. применение design tokens на frontend.

`Shell Builder` является advanced-screen над этим слоем.

Он не должен считаться главным daily-flow экраном.

### 2.3. `Design System / Global Defaults`

Этот слой отвечает за:

1. глобальную палитру;
2. типографику;
3. spacing scale;
4. container widths;
5. surfaces и tones;
6. defaults для buttons и cards;
7. shell surface defaults.

Важно:

design system это слой продукта.

Экран `Глобальные стили` это только вторичный UI над этим слоем.

### 2.4. `Visual Builder Workspace`

Это главный ежедневный экран продукта.

Он отвечает за:

1. page-like canvas;
2. выбор страницы, секции, блока и элемента прямо по preview;
3. live inspector;
4. responsive modes;
5. save, preview и publish loop.

Именно здесь пользователь должен проводить большую часть времени.

### 2.5. `Component Library`

Этот слой отвечает за:

1. section presets;
2. block presets;
3. reusable patterns;
4. page templates;
5. packs и шаблонные наборы.

Именно этот слой делает builder guided, а не freeform.

### 2.6. `Widget/Data Adapter Layer`

Этот слой отвечает за:

1. перевод системных widgets InstantCMS в canvas nodes;
2. data-driven blocks;
3. route-aware content adapters;
4. единый runtime/data contract между editor, preview и frontend.

Этот слой не должен диктовать технический UX пользователю.

## 3. Каноническая модель сборки страницы

Нордик теперь считается guided builder, а не grid-builder.

Каноническая иерархия такая:

1. `Страница`;
2. `Секции`;
3. `Блоки`;
4. `Элементы`.

Что важно:

1. пользователь не строит страницу из bootstrap-сетки;
2. пользователь не должен видеть raw CSS-классы;
3. пользователь не должен мыслить `layout_rows`, `layout_cols`, `pos_*` и служебными ids;
4. визуальный ритм и default styling должны приходить из design system и runtime, а не из ручного низкоуровневого редактирования.

## 4. Роли экранов в продукте

### 4.1. Daily Flow

Ежедневный маршрут должен быть только таким:

1. открыть страницу;
2. увидеть живой canvas;
3. выбрать нужную область кликом;
4. изменить контент, layout или стиль справа;
5. увидеть тот же результат сразу;
6. сохранить и опубликовать.

### 4.2. Secondary Flow

Редкий вторичный маршрут такой:

1. открыть `Глобальные стили`;
2. поменять site-wide defaults;
3. вернуться в canvas и увидеть те же изменения.

### 4.3. Advanced Flow

Редкий advanced-маршрут такой:

1. открыть `Shell Builder`;
2. поменять header, footer, hero zone или slot composition;
3. проверить runtime effect;
4. вернуться в canvas при необходимости.

## 5. Каноническая карта экранов bridge-фазы

Пока взрослый продукт еще собирается внутри текущего репозитория, каноническая карта экранов выглядит так:

1. `Страницы` как точка входа в visual builder workspace;
2. `Глобальные стили` как secondary screen для site-wide defaults;
3. `Shell Builder` как advanced runtime/shell screen;
4. `Блоки` и `Block Packs` как ранняя форма component library;
5. `Bindings` и `Adapters` как экран adapter-layer;
6. `Опции` и `Права доступа` как служебные экраны компонента.

## 6. Что означает текущий `landingbuilder`

Текущий `landingbuilder` больше не считается канонической финальной формой продукта.

Теперь его роль такая:

1. переходный bridge-слой;
2. место, где уже есть schema persistence;
3. место, где уже есть canvas loop;
4. место, где уже есть preview/runtime contract;
5. место, где уже есть package/install discipline.

Что из этого следует:

1. текущий код нужно переиспользовать выборочно;
2. form-first экраны нельзя продолжать развивать как финальный UX;
3. новые решения нужно оценивать по тому, приближают ли они boundary отдельного builder-компонента.

## 7. Что теперь считается ошибочным направлением

Ошибочным дальше считается:

1. воспринимать builder как экран настроек шаблона;
2. ставить `Дизайн сайта` в центр продукта;
3. расширять продукт через raw field forms вместо live inspector;
4. делать главной сущностью rows и cols вместо sections и blocks;
5. смешивать shell-настройки, site defaults и page editing в одном пользовательском маршруте.

## 8. Что считается правильным направлением

Правильным дальше считается:

1. держать canvas главным daily-flow экраном;
2. держать `Глобальные стили` вторичным экраном defaults;
3. держать `Shell Builder` отдельным expert-screen;
4. строить section/block library как основу guided editing;
5. проектировать adapter-layer отдельно от пользовательского визуального UX;
6. синхронизировать canvas, preview и live runtime через единый JSON contract.

## 9. Канонический вывод

Считать зафиксированным:

1. Нордик это отдельный visual-builder продукт над InstantCMS 2.
2. Canvas страницы это только один, хотя и главный, экран продукта.
3. Design system это слой продукта, а не просто отдельная форма настроек.
4. `Shell Builder`, `Глобальные стили`, `Visual Builder Workspace`, `Component Library` и `Widget/Data Adapter Layer` не должны смешиваться в один пользовательский сценарий.
5. Текущий `landingbuilder` остается переходным мостом до момента, когда boundary нового builder-компонента будет формально зафиксирован.
