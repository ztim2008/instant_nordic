# Техспека: canvas editor, responsive-секции и системные widgets

## Статусы

- ⚪ Не начато
- 🟡 Запланировано
- 🔵 В работе
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Навигация

- Главный трекер: [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
- Продуктовый blueprint: [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md)
- Предыдущий документ: [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md)
- Следующий документ: [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)

## 1. Цель документа

Этот документ фиксирует, каким должен быть реальный visual canvas в Нордик.

Конкретный продуктовый UX экрана, sidebar и MVP-пакета секций дополнительно зафиксирован в [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md).

Главный вопрос здесь не в том, как хранить данные, а в том, какой UX и какая внутренняя модель нужны, чтобы:

1. собирать страницу из нескольких динамических секций;
2. делать 1, 2 и 3 колонки drag-and-drop способом;
3. переключать desktop, tablet и mobile режимы;
4. добавлять на холст не только builder blocks, но и стандартные widgets InstantCMS.

## 2. Базовый принцип

### 🟢 Готово: принцип зафиксирован

Все должно быть визуально на холсте.

Пользователь не должен прыгать между:

- отдельной страницей виджетов;
- скрытыми layout positions;
- Bootstrap-классами;
- техническими row/col-формами InstantCMS.

Конструктор должен показывать на холсте:

- секции;
- колонки;
- блоки;
- системные widgets;
- состояние страницы на разных устройствах.

## 3. Что уже умеет InstantCMS и что из этого нужно забрать

### 🟢 Готово: текущий принцип разобран

Текущая layout-система InstantCMS устроена так:

1. `layout_rows`
2. `layout_cols`
3. widget position внутри колонки
4. `widgets_bind_pages`
5. рендер виджета через `cmsWidget` и template layer

Практический pipeline сейчас такой:

`row -> col -> position -> widget instance -> widget template render`

Что уже полезно для Нордик:

1. У рядов и колонок есть готовая responsive-логика.
2. Есть width classes по breakpoint'ам.
3. Есть order по breakpoint'ам.
4. Есть container/wrapper semantics.
5. Есть стандартный механизм widget options forms.
6. Есть стандартный runtime рендер widgets.

Что нельзя брать как конечный UX:

1. показ row/col и Bootstrap-классов пользователю;
2. работу через голые `pos_8`, `pos_9` и другие служебные позиции;
3. разделение, где blocks живут в одном мире, а widgets в другом.

Вывод:

InstantCMS grid нужно использовать как внутренний compatibility layer, а не как финальный пользовательский интерфейс.

### 🟢 Готово: продуктовое уточнение

Для Нордик это означает следующее:

1. widgets scheme шаблона не считается главным builder UX;
2. основной экран должен быть section-based canvas;
3. sidebar должен работать через presets и tokens;
4. grid semantics остаются внутренним runtime-слоем.

## 4. Каноническая модель canvas

### 🔵 В работе: каноническая структура зафиксирована

Для Нордик каноническая visual-модель должна быть такой:

`page -> zone -> section -> columns -> nodes`

Где:

1. `page` это документ страницы.
2. `zone` это область страницы или adapter-зона.
3. `section` это визуальная секция страницы.
4. `columns` это раскладка внутри секции.
5. `nodes` это элементы внутри колонок.

### 4.1. Node types

На canvas должны существовать минимум два типа узлов:

1. `block`
2. `system_widget`

Дополнительно позже можно добавить:

3. `adapter_slot`
4. `dynamic_collection`
5. `global_partial`

### 4.2. Почему нужен именно `node`, а не только `block`

Потому что иначе стандартные widgets InstantCMS опять выпадут из visual editor.

Если в модели будет только block instance, то придется:

- дублировать стандартные widgets как псевдо-блоки;
- ломать существующий widget runtime;
- плодить отдельную, несовместимую архитектуру.

Правильнее сделать общую canvas-сущность `node`, где блок и системный widget это просто два режима одного визуального элемента.

## 5. Секции и колонки

### 🟢 Готово: требования зафиксированы

Canvas не должен ограничиваться одной секцией и одним вертикальным стеком.

Пользователь должен уметь:

1. добавлять несколько секций подряд;
2. менять порядок секций drag-and-drop;
3. дублировать секции;
4. удалять секции;
5. выбирать layout секции: 1 колонка, 2 колонки, 3 колонки;
6. перетаскивать node между колонками;
7. менять порядок node внутри колонки.

### 5.1. Layout presets первой версии

Для MVP достаточно зафиксировать такие пресеты секций:

1. `1col`
2. `2col_equal`
3. `2col_sidebar_left`
4. `2col_sidebar_right`
5. `3col_equal`
6. `3col_featured_center`

На уровне UX пользователь должен видеть не `col-lg-4`, а понятные шаблоны колонок.

### 5.2. Ограничение первой версии

В первой версии не нужен freeform absolute-position canvas.

Нужен контролируемый layout editor:

- секции;
- колонки;
- reorder;
- responsive overrides.

Это реалистичнее и лучше сочетается с текущим InstantCMS runtime.

## 6. Responsive-режимы

### 🟢 Готово: базовая модель зафиксирована

У canvas должны быть device toggles.

Минимально:

1. `desktop`
2. `tablet`
3. `mobile`

Пользовательский UX должен как минимум иметь быстрые переключатели:

1. Компьютер
2. Планшет
3. Телефон

### 6.1. Что должно переключаться по устройствам

Для каждого режима должны отдельно поддерживаться:

1. ширины колонок;
2. порядок колонок;
3. visibility секции;
4. visibility node;
5. отступы и gap, если блок это поддерживает;
6. поведение некоторых widgets, если у них есть device-specific options.

### 6.2. Внутреннее маппирование

Внутри runtime это может маппиться на текущую bootstrap-логику InstantCMS:

- `desktop` -> `lg/xl`
- `tablet` -> `md`
- `mobile` -> `sm/default`

Но в UI пользователь не должен видеть эти обозначения как основной язык интерфейса.

## 7. Стандартные widgets InstantCMS на холсте

### 🟢 Готово: стратегическое решение зафиксировано

Стандартные widgets InstantCMS должны добавляться на canvas как first-class элементы.

Не как отдельная админка.

Не как скрытая интеграция.

Не как технический импорт без визуального представления.

### 7.1. Как это должно выглядеть в UX

В левой панели библиотеки должны быть минимум две вкладки:

1. `Блоки Нордик`
2. `Системные widgets`

Пользователь перетаскивает на холст:

- либо block;
- либо widget.

На холсте оба варианта должны вести себя одинаково по базовой механике:

- выделение;
- drag-and-drop;
- duplicate;
- delete;
- visibility settings;
- device switching preview.

### 7.2. Как это должно работать технически

Для `system_widget` node нужно использовать существующую модель InstantCMS:

1. registry доступных widgets;
2. `options.form.php` конкретного widget;
3. runtime class на базе `cmsWidget`;
4. существующий template/render pipeline.

То есть Нордик не должен переписывать widgets в свои blocks.

Он должен дать им visual wrapper и bridge-слой.

### 7.3. Widget bridge layer

Нужен отдельный bridge между canvas и стандартными widgets:

1. canvas node хранит `widget_ref`;
2. inspector умеет открыть widget options form;
3. preview рендерится через существующий widget runtime;
4. binding страницы и route context прокидываются в preview так, чтобы widget вел себя максимально близко к реальному frontend.

## 8. Что именно нужно скрыть от пользователя

### 🟢 Готово

Пользователь не должен видеть:

1. `layout_rows`
2. `layout_cols`
3. `widgets_bind_pages`
4. `pos_8`, `pos_9` и подобные position names
5. `col-lg-6`, `order-md-2` и другие Bootstrap-классы

Но система может использовать эти механизмы внутри compatibility-слоя.

## 9. MVP для canvas

### 🟡 Запланировано

Для первой рабочей версии достаточно такого состава:

1. несколько секций на странице;
2. 1/2/3-column layout presets;
3. drag-and-drop между колонками;
4. desktop/tablet/mobile toggles;
5. builder blocks на холсте;
6. стандартные widgets на холсте;
7. inspector для props и widget options;
8. реальный preview, близкий к runtime output.

Что можно отложить:

1. абсолютное позиционирование элементов;
2. сложную вложенность секций глубже одного уровня;
3. визуальный grid designer со свободным числом колонок;
4. collaborative edit.

## 10. Итоговый вывод

### 🟢 Готово

Нордик должен строиться не как новая форма над старой сеткой widgets, а как полноценный visual canvas.

При этом правильный инженерный путь такой:

1. взять текущую grid/widget архитектуру InstantCMS как внутренний reference и runtime-совместимый слой;
2. поверх нее построить собственную visual model `section -> columns -> nodes`;
3. поддержать в `nodes` и blocks, и стандартные widgets;
4. сделать desktop/tablet/mobile переключатели частью самого canvas, а не отдельной внешней настройкой.