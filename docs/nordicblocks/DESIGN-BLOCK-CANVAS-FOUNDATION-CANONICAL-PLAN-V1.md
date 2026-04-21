# Design Block Canvas Foundation — Canonical Plan V1

Дата: 2026-04-21
Статус документа: canonical working plan
Область: `design_block`

## 1. Зачем этот документ

Этот документ фиксирует новый базовый вектор работ по `design_block`.

Главный принцип:

1. сначала холст, его контейнеры, брейкпоинты, сетка и координатная модель;
2. потом базовые production-элементы;
3. потом команды, контекстное меню, слои и sidebar;
4. только после этого расширение палитры и сложные element types.

Это сознательно переупорядочивает приоритеты: больше не расширяем editor в ширину раньше, чем у него появится жёсткий canvas foundation.

---

## 2. Статусы

- ⚪ `В разработке` — ещё не начато, решение зафиксировано только на уровне плана.
- 🟡 `В работе` — архитектурный контур уже начат или частично подтверждён, но ещё не считается production-ready.
- 🟢 `Готово` — уже реализовано и подтверждено кодом и smoke-проверками в текущем репозитории.

---

## 3. Главная цель

Собрать `design_block` как взрослый визуальный редактор свободной секции, где:

1. холст является каноническим источником layout-правил;
2. брейкпоинты живут как часть artboard contract, а не как случайный runtime state;
3. grid overlay и content container имеют точную геометрию;
4. базовые элементы (`text`, `button`, `object`, `photo`) являются production-элементами, а не demo placeholders;
5. все действия над элементами идут через единый command layer;
6. layers panel, context menu и sidebar являются разными проекциями одной scene model;
7. отрицательные координаты и вынос объекта за холст считаются нормальной частью дизайн-системы, а не багом.

---

## 4. Финальный Canvas Contract

### 4.1 Канонический desktop baseline

Это baseline, который принимается как первая взрослая геометрическая модель холста.

- 🟢 `Готово как продуктовая цель`

Параметры desktop baseline:

1. `windowWidth = 1440`
2. `contentWidth = 1320`
3. `columns = 12`
4. `gutter = 24`
5. `columnWidth = 88`
6. `outerMargin = 60` с каждой стороны
7. `overflowMode = auto`

Проверка математики:

$$
12 \times 88 + 11 \times 24 = 1056 + 264 = 1320
$$

$$
\frac{1440 - 1320}{2} = 60
$$

### 4.2 Три пространства холста

- ⚪ `В разработке`

На холсте должны существовать три явных пространства:

1. `Window Container` — полная ширина экрана или viewport текущего брейкпоинта.
2. `Content Container` — рабочая область контента и колонок.
3. `Bleed Area` — допустимая зона выноса объекта за content container, но ещё внутри логики artboard.

Это не декоративные подсказки. Это канонические зоны, от которых зависят:

1. grid overlay;
2. стартовая вставка root-объектов;
3. отрицательные координаты;
4. snap candidates;
5. расчет guides;
6. ограничения drag and resize;
7. SSR/runtime parity.

### 4.3 Координатная модель

- 🟡 `В работе`

Канонические правила координат:

1. `x = 0` означает левую границу `Content Container`, а не левую границу окна.
2. `y = 0` означает верхнюю границу artboard content flow.
3. `x > 0` двигает объект вправо внутри content area.
4. `x < 0` выводит объект в левый bleed.
5. `x + width > contentWidth` выводит объект в правый bleed.
6. `y < 0` допускается только если это явно разрешено artboard rules для конкретного режима.
7. `y + height > artboardHeight` не считается ошибкой само по себе, если artboard autosize или explicit overflow разрешает этот кейс.

### 4.4 Отрицательные значения и вынос за холст

- 🟡 `В работе`

Это обязательная часть contract.

Нужно считать не только положительный layout, но и controlled overflow.

Канонические поля:

1. `layout.stage.<breakpoint>.windowWidth`
2. `layout.stage.<breakpoint>.contentWidth`
3. `layout.stage.<breakpoint>.columns`
4. `layout.stage.<breakpoint>.gutter`
5. `layout.stage.<breakpoint>.outerMargin`
6. `layout.stage.<breakpoint>.bleedLeft`
7. `layout.stage.<breakpoint>.bleedRight`
8. `layout.stage.<breakpoint>.minHeight`
9. `layout.stage.<breakpoint>.gridOverlay.color`
10. `layout.stage.<breakpoint>.gridOverlay.opacity`
11. `layout.stage.<breakpoint>.overflowMode`

Для desktop baseline по умолчанию:

1. `bleedLeft = outerMargin = 60`
2. `bleedRight = outerMargin = 60`

Логика видимости объекта:

$$
visibleLeft = -bleedLeft
$$

$$
visibleRight = contentWidth + bleedRight
$$

Объект с координатами `x` и `width` считается допустимым, если:

$$
x \ge -bleedLeft
$$

и

$$
x + width \le contentWidth + bleedRight
$$

Если объект выходит за эти границы, editor должен:

1. либо не дать увести его дальше жёсткого permissible range;
2. либо явно показать overflow-warning state, если такой режим будет разрешён отдельно.

### 4.5 Формулы колонной сетки

- ⚪ `В разработке`

Нужны канонические вычисления:

$$
contentWidth = columns \times columnWidth + (columns - 1) \times gutter
$$

$$
columnWidth = \frac{contentWidth - (columns - 1) \times gutter}{columns}
$$

$$
outerMargin = \frac{windowWidth - contentWidth}{2}
$$

Overlay grid должен рисоваться не через приблизительный CSS, а через stage metrics, которые одинаково доступны editor shell и runtime.

### 4.6 Брейкпоинты

- ⚪ `В разработке`

Продуктово правильная модель должна быть рассчитана минимум на пять экранов:

1. `desktop`
2. `tablet_landscape`
3. `tablet_portrait`
4. `mobile_landscape`
5. `mobile_portrait`

Разрешено временно запускать UI только на трёх переключателях, но contract сразу должен уметь жить на пяти.

Для каждого брейкпоинта нужны:

1. свои stage metrics;
2. наследование от предыдущего брейкпоинта;
3. явная индикация overridden values;
4. единая serialization model без расползания по shell-state.

---

## 5. Канонический состав системы

### 5.1 Холст

- 🟡 `В работе`

Что уже есть:

1. world/viewport geometry core;
2. camera and zoom;
3. grid-related stage contract;
4. базовая overlay grid;
5. negative `x` и bleed в техническом контуре;
6. save/reload подтверждение базового stage contract.

Что считается готовым только после следующего большого прохода:

1. пустой клик по холсту открывает artboard settings;
2. grid overlay управляется как часть canvas settings;
3. baseline metrics читаются из одного canonical stage contract;
4. overlay визуально и математически совпадает с принятым макетом.

### 5.2 Базовые production-элементы

- ⚪ `В разработке`

На первом взрослым проходе оставляем только:

1. `text`
2. `button`
3. `object`
4. `photo`

Правило:

1. `object` — это один базовый shape element, который через свойства может быть прямоугольником, линией или кругом;
2. `photo` — это production image element, а не placeholder image box;
3. `container`, `icon`, `divider`, `video`, `svg` временно убираются из главной пользовательской палитры до стабилизации базы.

### 5.3 Команды

- ⚪ `В разработке`

Нужен единый command registry. Все действия должны вызываться одними и теми же командами из разных UI-точек.

Базовый набор команд:

1. `delete`
2. `duplicate`
3. `group`
4. `ungroup`
5. `bringForward`
6. `sendBackward`
7. `bringToFront`
8. `sendToBack`
9. `lock`
10. `unlock`
11. `hide`
12. `show`
13. `copy`
14. `paste`

### 5.4 Контекстное меню

- ⚪ `В разработке`

При правом клике по элементу обязано открываться context menu.

Минимальный состав:

1. удалить;
2. дублировать;
3. объединить;
4. разгруппировать;
5. поднять;
6. опустить;
7. заблокировать;
8. скрыть.

Важно:

1. контекстное меню не должно жить отдельной логикой;
2. оно обязано вызывать тот же command layer, что и toolbar, layers и hotkeys.

### 5.5 Layers

- 🟡 `В работе`

Что уже подтверждено:

1. tree по `parentId` уже есть;
2. breadcrumbs уже есть;
3. insertion context уже подсвечивается;
4. root context уже работает.

Что ещё обязательно:

1. lock and hide controls;
2. context menu в layers;
3. reorder через layers;
4. collapse and expand для глубоких деревьев;
5. multi-select и group actions без рассинхрона со сценой.

### 5.6 Sidebar

- ⚪ `В разработке`

Sidebar обязан иметь три канонических режима:

1. `Canvas`
2. `Element`
3. `Multi-select`

#### Режим Canvas

1. брейкпоинт;
2. artboard width and height;
3. content width;
4. columns and gutter;
5. outer margin;
6. grid color;
7. grid opacity;
8. show or hide grid;
9. background;
10. bleed policy.

#### Режим Element

1. `Контент`
2. `Макет`
3. `Стиль`

#### Режим Multi-select

1. align;
2. distribute;
3. group;
4. bulk layer actions.

---

## 6. Большой план работ

### Этап 0. Freeze архитектуры

- 🟡 `В работе`

Цель:

1. зафиксировать, что `design_block` переприоритизирован вокруг canvas foundation;
2. прекратить хаотичное расширение editor path до окончания базового прохода.

Что входит:

1. принять этот документ как canonical plan;
2. зафиксировать desktop baseline `1440 / 1110 / 12 / 30 / 65`;
3. зафиксировать four-element palette первой волны;
4. зафиксировать обязательную поддержку отрицательных координат и bleed.

Checkpoint:

1. отдельный checkpoint перед первым большим кодовым проходом по canvas contract.

Риск:

1. если этого не сделать, дальше снова появятся локальные UX-улучшения без канонической геометрии.

Smoke:

1. документ согласован;
2. базовые числа холста не спорят между собой;
3. negative-x и bleed явно описаны в contract, а не остаются скрытым знанием.

### Этап 1. Canvas Foundation

- ⚪ `В разработке`

Цель:

1. сделать холст каноническим product-layer, а не техническим runtime sandbox.

Что сделать:

1. пересобрать stage contract под `windowWidth / contentWidth / columns / gutter / outerMargin / bleedLeft / bleedRight`;
2. построить overlay grid из stage metrics;
3. дать пустому клику по холсту открывать `Canvas` mode sidebar;
4. привести visual overlay к baseline макету;
5. ввести явную origin marker модель;
6. привести drag and resize bounds к новому contract.

Checkpoint:

1. после первого зелёного canvas-only smoke до перехода к элементам.

Риски:

1. поломать существующий save and reload path;
2. рассинхронизировать editor geometry и runtime layout;
3. ошибиться в math для bleed and outer margins.

Smoke:

1. desktop baseline визуально совпадает с макетом;
2. grid color и opacity работают;
3. при reload stage metrics не деградируют;
4. negative-x внутри bleed живёт предсказуемо.

### Этап 2. Breakpoint System

- ⚪ `В разработке`

Цель:

1. превратить брейкпоинты из переключателя UI в полноценную stage inheritance model.

Что сделать:

1. описать пять canonical breakpoints;
2. поддержать inheritance from previous breakpoint;
3. показать overridden values в sidebar;
4. зафиксировать, какие значения shared, а какие breakpoint-specific;
5. стабилизировать round-trip serialization.

Checkpoint:

1. после первого зелёного smoke по desktop and one smaller breakpoint.

Риски:

1. потеря значений при save and reload;
2. превращение breakpoint logic в хаотичный набор fallback-ов;
3. drift между shell state и serialized contract.

Smoke:

1. desktop and smaller breakpoint сохраняются и возвращаются без потерь;
2. inherited values читаются отдельно от overridden;
3. grid overlay меняется вместе с breakpoint metrics.

### Этап 3. Базовые production-элементы

- ⚪ `В разработке`

Цель:

1. сделать четыре настоящих элемента и убрать временный шум.

Что сделать:

1. временно убрать из пользовательской palette всё кроме `text`, `button`, `object`, `photo`;
2. пересобрать default props этих четырёх элементов;
3. довести их contract до production-level settings;
4. проверить real save/reload для каждого;
5. проверить их поведение при negative-x и bleed.

Checkpoint:

1. после зелёного save/reload smoke по всем четырём типам.

Риски:

1. оставить элементы визуально demo-level;
2. скрыто сломать editor shell для вторичных типов;
3. потерять sync между layers, selection и sidebar.

Smoke:

1. текст имеет авто-высоту и честную типографику;
2. кнопка имеет реальные size and padding settings;
3. object может быть rectangle, line, circle;
4. photo имеет source, fit, radius, link and alt.

### Этап 4. Command Layer и Context Menu

- ⚪ `В разработке`

Цель:

1. перестать плодить action-обработчики по месту и собрать единый command layer.

Что сделать:

1. описать command registry;
2. перевести toolbar actions на эти команды;
3. перевести layers actions на те же команды;
4. добавить context menu по правому клику;
5. подключить горячие клавиши к тем же командам.

Checkpoint:

1. после unified command smoke без изменения canvas contract.

Риски:

1. рассинхрон delete/duplicate/group между разными UI точками;
2. утечки block-specific hacks;
3. нестабильный right-click flow в editor DOM.

Smoke:

1. delete работает одинаково из toolbar, layers и context menu;
2. duplicate даёт одинаковый результат из всех точек;
3. group and ungroup не теряют children;
4. context menu не ломает обычный selection loop.

### Этап 5. Layers V2

- 🟡 `В работе`

Цель:

1. довести layers до уровня второго полноценного источника управления сценой.

Что уже есть:

1. tree structure;
2. breadcrumbs;
3. insertion context;
4. root mode.

Что сделать дальше:

1. collapse and expand;
2. lock and hide;
3. context menu;
4. reorder;
5. multi-select operations.

Checkpoint:

1. после зелёного nested-authoring smoke с длиннее чем одним parent level.

Риски:

1. глубоко вложенные сцены станут нечитаемыми;
2. reorder будет расходиться с actual z-order;
3. layers selection начнёт спорить с canvas selection.

Smoke:

1. nested tree можно читать без открытия DOM inspector;
2. reorder отражается на canvas;
3. lock and hide переживают save and reload.

### Этап 6. Sidebar IA V2

- ⚪ `В разработке`

Цель:

1. сделать sidebar продуктовым, а не debug-панелью.

Что сделать:

1. разделить `Canvas`, `Element`, `Multi-select`;
2. убрать случайные технические поля из primary UX;
3. показать geometry, style and content в продуктовой последовательности;
4. добавить visual distinction overridden breakpoint values;
5. держать frequent controls на поверхности.

Checkpoint:

1. после первого полного authoring smoke без подсказок по коду.

Риски:

1. sidebar останется смесью contract debug и UX-панели;
2. часто используемые поля утонут в структуре;
3. холст и element settings смешаются в одном режиме.

Smoke:

1. пустой клик по холсту открывает canvas settings;
2. клик по элементу открывает его settings;
3. мультивыбор открывает collective actions;
4. пользователь может собрать базовую секцию без чтения кода или JSON.

### Этап 7. Runtime Parity по новой базе

- ⚪ `В разработке`

Цель:

1. подтвердить, что новая canvas foundation не живёт только в editor shell.

Что сделать:

1. повторить widget/live smoke на блоках, собранных уже по новой contract модели;
2. проверить desktop;
3. проверить один tablet or mobile breakpoint;
4. проверить negative-x and bleed case в public runtime;
5. проверить четыре базовых элемента в runtime.

Checkpoint:

1. финальный checkpoint после зелёного public smoke по новой базе.

Риски:

1. editor geometry и SSR layout снова разойдутся;
2. bleed будет работать только в backend shell;
3. object bounds и overflow будут по-разному резаться в editor и public.

Smoke:

1. desktop layout совпадает с editor;
2. smaller breakpoint не ломает object placement;
3. negative-x case не теряется в runtime;
4. editor-only overlays не попадают на public page.

---

## 7. Что временно запрещено

Пока не закрыты этапы 1-6, запрещено расширять scope следующими вещами:

1. новые element families beyond first four;
2. animation system;
3. advanced media logic;
4. giant block templates поверх нестабильного canvas;
5. отдельные block-specific shell ветки;
6. selector-driven cosmetic overlay вместо contract-first geometry.

---

## 8. Что уже реально готово в репозитории

- 🟢 `Готово`

1. world and viewport geometry base уже вынесены и покрыты тестами;
2. базовый interaction core уже существует;
3. save and reload для design_block уже подтверждены;
4. базовая live parity уже один раз подтверждена;
5. root insertion mode уже подтверждён;
6. nested layers tree уже подтверждён;
7. negative `x` и bleed уже существуют как технический слой, но ещё не оформлены как product-level canvas contract.

---

## 9. Что сейчас в работе

- 🟡 `В работе`

1. превращение `design_block` из технического canvas prototype в product-level canvas editor;
2. стабилизация insertion context и layers tree;
3. переход от текущего stage/grid contract к канонической artboard model;
4. выравнивание negative-x и bleed как официальной дизайн-возможности.

---

## 10. Что станет признаком взрослой версии

- ⚪ `В разработке`

`Design_block` можно считать взрослым только когда одновременно верны все пункты:

1. grid overlay математически совпадает с contract;
2. брейкпоинты работают через наследование и override model;
3. четыре базовых элемента являются production-элементами;
4. отрицательные координаты и bleed поддерживаются осознанно и предсказуемо;
5. context menu и command layer едины;
6. layers и canvas не спорят между собой;
7. sidebar имеет отдельный canvas mode;
8. runtime parity подтверждён для новой базы.

---

## 11. Рекомендуемый ближайший шаг

- 🟡 `В работе`

Следующий реальный шаг после этого документа:

1. сделать checkpoint;
2. заморозить palette до `text/button/object/photo`;
3. начать кодовый проход с `Canvas Foundation`, а не с элементов;
4. привести stage metrics и overlay grid к baseline `1440 / 1110 / 12 / 30 / 65`;
5. только потом трогать sidebar, commands и context menu.