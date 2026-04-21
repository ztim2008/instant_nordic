# Design Block Sidebar Zero-Block Plan V1

Дата: 2026-04-21
Статус: canonical sidebar plan
Область: design_block sidebar, inspector, element settings

## 1. Product goal

Собрать sidebar и inspector, которые ощущаются как рабочая панель Zero Block, а не как набор HTML-форм.

Главные признаки целевого UX:

1. добавление элементов идёт через крупную кнопку плюс и выпадающее меню;
2. панель настроек компактная и визуально плотная;
3. цвет задаётся через color picker, а не ручным вводом hex по умолчанию;
4. числовые значения можно менять мышью по оси X без ручного набора;
5. сверху вниз сохраняется понятная иерархия: add, selection actions, content, layout, style;
6. настройки максимально полные для наших поддерживаемых типов: text, button, object, photo.

## 2. Что берём у Zero Block как канон

По референсу Tilda Zero Block:

1. элемент добавляется из кнопки плюс в левом верхнем углу;
2. панель настроек открывается как компактный inspector;
3. верхняя зона настроек содержит быстрые действия и выравнивание;
4. координаты и размеры идут выше остальных вторичных параметров;
5. мышь используется не только на canvas, но и в самих контролах inspector.

## 3. Ограничения адаптации под NordicBlocks

Мы не копируем интерфейс буквально. Адаптируем под текущий состав блоков и контракт.

Правила адаптации:

1. не ломать текущий contract stage/runtime/element;
2. не вводить лишние типы элементов в палитру pass 1;
3. не смешивать editor-only UX controls с runtime semantics;
4. сначала улучшать top-level inspector flow, потом глубокие advanced settings.

## 4. Target structure

### 4.1 Sidebar top

Сверху должны жить:

1. крупная кнопка Добавить с выпадающим списком;
2. контекст текущей вставки;
3. breadcrumbs выбора;
4. основные действия над выделением.

### 4.2 Artboard section

Для пустого выбора или root context:

1. настройки блока;
2. высота artboard;
3. overflow;
4. поведение объектов вне блока;
5. фон и сетка;
6. рабочие editor toggles.

### 4.3 Element inspector

Для одиночного выбранного элемента порядок должен быть таким:

1. quick actions;
2. content;
3. layout;
4. style.

Каждая секция должна быть визуально компактной и ориентированной на мышь.

## 5. Controls policy

### 5.1 Add control

Добавление элементов:

1. одна большая кнопка плюс;
2. popover/dropdown со списком типов;
3. короткое описание каждого типа;
4. закрытие по клику вне меню.

### 5.2 Color controls

Для цветовых полей:

1. основной control это swatch + native color input;
2. рядом остаётся текстовое значение для точного контроля, но не как основной путь;
3. fallback для не-hex значений остаётся безопасным.

### 5.3 Numeric controls

Для числовых полей:

1. текстовое число остаётся редактируемым;
2. появляется scrub zone для drag left/right;
3. шаги различаются по типу поля:
   x/y/size/z-index/spacing: 1
   opacity/line-height: 1
   border radius и padding: 1
4. Shift ускоряет шаг;
5. scrub не должен ломать обычный фокус в input.

## 6. Pass plan

### Pass 1

1. большой Add плюс с dropdown;
2. color picker для section, stage и element color fields;
3. numeric scrub для number inputs;
4. обновлённый top-order sidebar blocks.

### Pass 2

1. compact quick align row для element inspector;
2. более tilda-like grouped controls;
3. richer photo/object/button settings layout;
4. collapsible inspector sections.

### Pass 3

1. layers UX pass;
2. drag reorder;
3. lock/visibility icons ближе к product UI;
4. multi-select group actions refinement.

## 7. Smoke checklist

После каждого заметного sidebar pass проверять:

1. добавление text/button/object/photo через плюс;
2. color change мышью через picker;
3. x/y/w/h изменение scrub-движением мыши;
4. ручной ввод чисел всё ещё работает;
5. dropdown закрывается по outside click;
6. desktop и mobile breakpoint inspector не ломаются.

## 8. Rollback discipline

Перед большими sidebar/refactor passes использовать checkpoint.

Для этого прохода актуальный rollback:

1. snapshot/20260421-151837
2. backups/db/builders-20260421-151836.sql.gz