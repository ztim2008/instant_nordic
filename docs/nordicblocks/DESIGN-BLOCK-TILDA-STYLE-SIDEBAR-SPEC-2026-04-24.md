# Design Block Tilda-Style Sidebar Spec

Дата: 2026-04-24
Статус: working reference
Область: `design_block` editor sidebar, inspector IA, top-to-bottom settings flow

## Цель документа

Этот документ фиксирует желаемую структуру правой панели настроек `design_block` по мотивам Tilda Zero Block, но адаптированную под NordicBlocks и текущий контракт компонента.

Документ не означает буквальное копирование интерфейса Tilda.

Его задача:

1. убрать лишний шум из текущего inspector flow;
2. привести панель к понятному top-to-bottom порядку;
3. зафиксировать базу для следующего editor pass;
4. дать канонический reference для `text`, `image`, `object` и artboard settings.

## Связанные документы

1. `docs/nordicblocks/DESIGN-BLOCK-ROADMAP-AND-EXECUTION-PLAN-2026-04-23.md`
2. `docs/nordicblocks/DESIGN-BLOCK-SIDEBAR-ZERO-BLOCK-PLAN-V1.md`
3. `docs/nordicblocks/DESIGN-BLOCK-EDITOR-SHELL-V1.md`

## Главный принцип

Правая панель должна ощущаться не как набор длинных HTML-форм, а как компактный рабочий inspector с понятной иерархией сверху вниз.

Правильная логика чтения панели:

1. быстрые действия;
2. позиция и размер;
3. content-specific настройки выбранного элемента;
4. decoration/effects/interaction;
5. advanced и системные настройки;
6. общие states, breakpoints и actions внизу.

## Статусы

Ниже статусная карта по состоянию на 2026-04-24.

### 🟢 Сделано

1. зафиксирован отдельный reference по Tilda-style sidebar для `design_block`;
2. определён top-to-bottom принцип без лишнего шума;
3. выделены базовые группы настроек для `text`, `image`, `object` и artboard;
4. зафиксирована позиция, что адаптируем UX-паттерн, а не копируем Tilda буквально.

### 🟡 В работе

1. сопоставление этого reference с текущими возможностями `design_block editor`;
2. разбор, какие блоки inspector уже поддерживаются контрактом, а какие ещё только UI-желание;
3. сбор практических замечаний из реальной сборки design blocks;
4. уточнение text/group workflow по результатам живого использования editor.

### 🔵 Запланировано

1. привести порядок секций sidebar к каноническому top-to-bottom flow;
2. вынести быстрые действия в верхний quick-actions row;
3. стабилизировать блок `Position` как первую секцию для всех элементов;
4. уплотнить и упростить typography/fill/spacing/decorations без визуального мусора;
5. добавить states и breakpoints как нижний системный слой, а не как шум в середине inspector;
6. довести artboard panel до отдельного чистого режима по клику на пустое место;
7. внедрять блоки по фазам, начиная с MVP, а не всё сразу.

## Noise Reduction Policy

Чтобы панель стала взрослой и быстрой, держим жёсткие правила:

1. не показывать второстепенные advanced controls раньше position/content/style;
2. не дублировать одни и те же действия в нескольких местах sidebar;
3. не смешивать artboard controls и element controls в одном потоке;
4. не ставить breakpoints, states и animation выше базовой geometry/content настройки;
5. не тащить в pass 1 всё, что теоретически умеет Zero Block.

Дополнительное правило по итогам практики:

6. не дублировать в sidebar те действия, которые уже живут в context menu, если это не критичный always-visible control.

## Каноническая структура панели

Панель открывается при выделении элемента и идёт сверху вниз.

### 1. Верхний ряд — быстрые действия

Сверху должны жить быстрые операции над выделением:

1. Align Left / Center / Right;
2. Align Top / Middle / Bottom;
3. Bring to Front / Send to Back;
4. Lock / Unlock;
5. Hide / Show;
6. Group / Ungroup.

Это именно action row, а не основная форма.

## Базовый блок для всех элементов

### 2. Position — координаты и размеры

Это должен быть первый и обязательный блок inspector для всех element types.

Состав блока:

1. X;
2. Y;
3. Width;
4. Height;
5. Container: `Grid` или `Window`;
6. Axis: horizontal/vertical anchor;
7. Units: `px` или `%`.

Принципы:

1. container и units не должны быть спрятаны слишком глубоко;
2. для мышечного workflow этот блок должен быть самым быстрым;
3. anchor/axis нужны как системный слой geometry, а не как декоративная настройка.

## Элемент Text

### 3. Typography

Порядок настроек сверху вниз:

1. Font Family;
2. Font Weight;
3. Font Size;
4. Line Height;
5. Letter Spacing;
6. Text Transform;
7. Text Align;
8. Color.

Замечание по адаптации:

1. в pass 1 достаточно solid color;
2. gradient text не обязателен как стартовая часть MVP;
3. typography block должен быть очень плотным и без визуального растяжения;
4. font weight должен переключаться по реальным поддерживаемым значениям толщины, а не по всем числам подряд.

### Content policy for text

По итогам практической работы с editor для текста принимается отдельное правило:

1. если текст редактируется прямо на холсте, sidebar не должен дублировать этот же content block без явной пользы;
2. для text element sidebar в первую очередь должен показывать `Position`, `Typography`, `Spacing`, `Decoration`, а не повтор содержимого;
3. редактирование текста на холсте считается primary path, а не secondary feature.

### 4. Spacing

Для text/button-like элементов нужен отдельный padding block:

1. Top;
2. Right;
3. Bottom;
4. Left;
5. linked padding mode, если это уместно для типа.

### 5. Decoration

Для текста и похожих элементов:

1. Background;
2. Opacity;
3. Border style/width/color;
4. Radius;
5. Shadow.

### 6. Effects

Этот блок нельзя поднимать выше typography и decoration.

Поддерживаемый reference-перечень:

1. Blur;
2. Brightness;
3. Contrast;
4. Grayscale;
5. Hue Rotate;
6. Invert;
7. Saturate;
8. Sepia.

### 7. Interaction

Минимальный состав:

1. URL;
2. Target;
3. hover effects для color/background/border/scale/opacity.

### 8. Advanced

Этот блок должен быть внизу текстового inspector:

1. CSS Class;
2. ID;
3. Alt Text или semantics-эквивалент, если применимо;
4. Tag;
5. Z-index;
6. Overflow.

### Text canvas handling

Отдельный UX-требование по длинным текстам:

1. при большом количестве текста пользователь не должен терять удобный доступ к верхней кромке объекта;
2. resize/selection handles должны оставаться доступными без утомительного скролла вниз-вверх;
3. длинный текст не должен ломать основной authoring loop на холсте.

## Элемент Image

### 1. Image Source

Первым после Position должен идти источник изображения:

1. upload/select file;
2. original size reset;
3. edit image action, если редактор есть;
4. alt text;
5. lazy load.

### 2. Fit & Position

Основной блок для image placement:

1. Fit: `cover`, `contain`, `fill`;
2. Position presets;
3. manual X/Y offset.

### 3. Shape & Border

1. Radius;
2. Border style;
3. Border width;
4. Border color.

### 4. Shadow & Effects

1. Shadow;
2. тот же базовый набор filters/effects, что и у text.

### 5. Interaction

1. zoomable mode;
2. URL;
3. Target;
4. hover effects для opacity/scale/filter.

## Элемент Object / Shape

### 1. Shape Type

1. Rectangle;
2. Circle;
3. Line;
4. custom SVG/vector как later-stage path.

### 2. Fill

1. Solid;
2. Linear gradient;
3. Radial gradient;
4. Opacity;
5. background image как later-stage extension.

### 3. Border

1. Border Style;
2. Width;
3. Color;
4. Radius.

### 4. Shadow & Effects

1. Shadow;
2. базовые filters;
3. background blur как later-stage advanced feature.

### 5. Interaction & Advanced

1. URL;
2. Target;
3. hover effects для fill/border/shadow;
4. CSS Class / ID / Tag;
5. Z-index / Overflow.

## Общие блоки для всех элементов

Эти секции должны быть внизу панели как системный слой, а не в центре inspector.

### States

1. `Normal`;
2. `Hover`;
3. `Active`;
4. `Focus`.

Правило:

1. pass 1 не обязан открывать весь state system сразу;
2. если states нет в контракте как полноценного supported path, UI не должен притворяться, что feature finished.

### Breakpoints

Reference-идея:

1. desktop;
2. tablet-like breakpoints;
3. mobile breakpoints;
4. reset to desktop для локальных override.

Правило адаптации:

1. NordicBlocks должен опираться на свою breakpoint-модель, а не механически копировать zero-block список ширин.

### Animation

1. Type;
2. Duration;
3. Delay;
4. Easing;
5. Trigger.

Правило адаптации:

1. animation не поднимать выше geometry/content/style;
2. sequence и motion должны оставаться отдельным controlled-expansion этапом.

### Group Settings

1. group type;
2. auto layout;
3. inner padding.

### Actions

1. Copy Styles;
2. Paste Styles;
3. Reset;
4. Delete.

Правило:

1. если `Delete`, `Duplicate`, `Bring forward/back` уже доступны в контекстном меню, их повтор в sidebar должен быть либо убран, либо явно оправдан как часть quick-actions row.

## Grouping behavior policy

По итогам практической работы grouping должен вести себя так:

1. после группировки группа двигается как один объект;
2. single click выбирает группу целиком;
3. вход внутрь группы делается отдельным действием: через double click, layers или explicit enter-group mode;
4. дублирование группы сохраняет все дочерние элементы;
5. смешанная группа `text + object` не должна становиться неподвижной.

Текущее состояние по замечаниям:

1. выделение внутри группы конфликтует с ожиданием `group as one object`;
2. duplicate group ведёт себя неполно;
3. mixed groups требуют отдельного bugfix pass до дальнейшего расширения group UX.

## Implementation Batch 01

Этот sidebar reference напрямую конвертируется в первый рабочий пакет.

### 🟡 Batch 01 in progress definition

Batch 01 должен закрыть три вещи:

1. убрать дублирование действий в правой панели;
2. подтвердить text-first canvas workflow;
3. вернуть grouping поведение к модели `group as one object`.

### Batch 01 status board

1. 🔵 Запланировано: Batch 01.1 Quick-actions cleanup
2. 🔵 Запланировано: Batch 01.2 Text-first inspector cleanup
3. 🔵 Запланировано: Batch 01.3 Text handling stabilization
4. 🔵 Запланировано: Batch 01.4 Group-as-one-object behavior
5. 🔵 Запланировано: Batch 01.5 Final validation

Правило статусов:

1. 🔵 Запланировано = задача описана, но код не начат;
2. 🟡 В работе = задача уже открыта в коде и текущий фокус на ней;
3. 🟢 Готово = код сделан и проверка после шага пройдена.

### Batch 01 scope

#### A. Quick-actions cleanup

Статус:

1. 🔵 Запланировано

1. `Delete`, `Duplicate`, `Bring forward/back` не должны жить и в context menu, и в теле sidebar без явной причины;
2. если действия остаются в sidebar, они должны быть собраны в компактный верхний action row;
3. дубли внутри основных form sections надо убрать.

#### B. Text-first inspector cleanup

Статус:

1. 🔵 Запланировано

1. text element не должен показывать шумный content block, если текст редактируется прямо на холсте;
2. первой секцией должен быть `Position`;
3. затем `Typography`, `Spacing`, `Decoration`;
4. content repetition в sidebar должна быть убрана или сведена к вспомогательному режиму.

#### C. Text handling stabilization

Статус:

1. 🔵 Запланировано

1. длинный текст не должен уводить workflow вниз без быстрого возврата к верхней кромке;
2. resize/selection affordances должны оставаться доступными;
3. font weight должен опираться на реальные supported values.

#### D. Group-as-one-object behavior

Статус:

1. 🔵 Запланировано

1. single click выбирает группу;
2. вход внутрь группы делается отдельно;
3. duplicate группы сохраняет всех children;
4. mixed group `text + object` остаётся movable.

### Definition of done for Batch 01

1. inspector для текста стал заметно короче и чище;
2. действия не дублируются между context menu и sidebar;
3. длинный текст больше не ломает базовую манипуляцию на холсте;
4. группы ведут себя как единый объект в базовом selection/move/duplicate flow.

После закрытия batch статусы должны выглядеть так:

1. 🟢 Готово: Batch 01.1 Quick-actions cleanup
2. 🟢 Готово: Batch 01.2 Text-first inspector cleanup
3. 🟢 Готово: Batch 01.3 Text handling stabilization
4. 🟢 Готово: Batch 01.4 Group-as-one-object behavior
5. 🟢 Готово: Batch 01.5 Final validation
2. duplicate group сейчас ведёт себя неполно;
3. mixed groups требуют отдельного bugfix pass до дальнейшего расширения group UX.

## Artboard Panel

По клику на пустое место должна открываться отдельная artboard panel.

### Настройки контейнера

1. Grid Container Height;
2. Window Container Height или эквивалент stage/window semantics;
3. Background Color;
4. Background Image;
5. Background Position / Repeat / Size;
6. Filter Overlay;
7. Position;
8. Z-index;
9. Overflow.

### Настройки сетки

1. Columns;
2. Gutter;
3. Margin;
4. Show Grid;
5. Show Rulers;
6. Show Guides.

## MVP-порядок внедрения

Ниже не wishlist, а рекомендуемый порядок внедрения.

### 🟢 MVP Pass 1

1. Quick actions row;
2. Position block для всех элементов;
3. Typography/Fill basics для `text`;
4. basic image source + fit controls;
5. basic object fill/border;
6. clean artboard panel;
7. только `Normal` state без агрессивного расширения states/animation.

### 🟡 Pass 2

1. Spacing / Decoration;
2. Shadow blocks;
3. Hover interaction basics;
4. better grouped controls;
5. compact advanced section.

### 🔵 Pass 3

1. states inheritance UI;
2. richer breakpoints UI;
3. filters/effects expansion;
4. group settings;
5. animation block;
6. import/export styling ideas, если это подтвердится как полезный product path.

## Что не копировать буквально из Tilda

1. не переносить слепо всю вложенность UI, если контракт NordicBlocks ещё её не поддерживает;
2. не рисовать фальшивые controls для features, которых нет в runtime/save path;
3. не раздувать pass 1 до состояния полного Zero Block clone;
4. не смешивать reference-UI и реальную roadmap-приоритизацию.

## Практический вывод

Для NordicBlocks сейчас ценнее всего не полный клон Zero Block, а чистый и строгий inspector без шума.

Поэтому этот reference надо использовать так:

1. как порядок сверху вниз;
2. как vocabulary для будущего inspector;
3. как фильтр против хаотичного роста sidebar;
4. как основу для следующих editor passes.