# NordicBlocks Design Block Editor Shell V1

Дата: 2026-04-20

## 1. Цель документа

Этот документ фиксирует backend editor shell для `design_block` в InstantCMS-репозитории NordicBlocks.

Он описывает:

1. admin template layout;
2. state bootstrap из PHP в JS;
3. JS store contract;
4. live DOM-canvas в той же странице редактора;
5. sidebar structure donor-уровня.

Обновление на 2026-04-20:

1. основной UX редактора для `design_block` больше не опирается на iframe preview как на главный режим работы;
2. канонический shell рендерит холст прямо в DOM admin-страницы;
3. drag/drop, inline-редактирование текста, направляющие и 12-колоночная сетка работают без отдельного frame-layer;
4. `block_design_canvas` остаётся допустимым вспомогательным SSR endpoint для smoke/parity, но не считается главным UX-контуром editor shell.

Главное правило:

freeform editor является отдельным режимом редактирования блока, но не отдельным приложением с собственным lifecycle вне NordicBlocks backend.

Каноническая integration boundary дополнительно зафиксирована в `docs/nordicblocks/DESIGN-BLOCK-INSTANTCMS-INTEGRATION-RULES-V1.md`.

---

## 2. Точка входа

### 2.1 Канонический route

Пользовательский URL открытия блока не меняется:

`GET /admin/controllers/edit/nordicblocks/block_edit/{block_id}`

### 2.2 Dispatch rule

`backend/actions/block_edit.php` обязан:

1. загрузить block record;
2. проверить admin access;
3. если `block.type !== design_block`, отдать текущий inspector shell;
4. если `block.type === design_block`, отдать отдельный template `editor_design_block.tpl.php`;
5. не пытаться строить для `design_block` shared inspector state.

Это сохраняет единый вход в редактор и не плодит второй пользовательский маршрут для открытия блока.

---

## 3. Backend endpoints для editor shell

### 3.1 Канонический набор действий

В v1 фиксируем 4 backend endpoint-а:

1. `GET block_edit/{id}`
2. `GET block_design_state/{id}`
3. `GET block_design_canvas/{id}`
4. `POST block_save/{id}`

### 3.2 Назначение endpoint-ов

1. `block_edit`
  - отрисовывает outer shell template.
2. `block_design_state`
  - возвращает JSON bootstrap/state для JS shell.
  - явно маркирует dedicated editor engine.
3. `block_design_canvas`
  - возвращает standalone SSR preview для smoke/parity-проверок и вспомогательных сценариев.
4. `block_save`
  - сохраняет title и full contract.

### 3.3 Почему без отдельного `block_design_save`

Отдельный save endpoint не запрещён, но по умолчанию не нужен.

Лучший путь:

1. сохранить один общий write path `block_save`;
2. для `design_block` внутри него сделать dedicated contract branch.

Это уменьшает дублирование безопасности и CRUD-логики.

---

## 4. PHP template contract

### 4.1 Канонический template

```text
templates/admincoreui/controllers/nordicblocks/backend/editor_design_block.tpl.php
```

### 4.2 Layout regions

Template состоит из 3 зон:

1. topbar;
2. canvas area;
3. right sidebar.

Схема:

```text
┌ Topbar: back / title / breakpoints / save / place ┐
├──────────────────────────┬─────────────────────────┤
│ Canvas в том же DOM      │ Sidebar                │
│                          │ 1. Дизайн-блок         │
│                          │ 2. Холст               │
│                          │ 3. Секция              │
│                          │ 4. Слои                │
│                          │ 5. Свойства элемента   │
└──────────────────────────┴─────────────────────────┘
```

### 4.3 Topbar contract

Topbar обязан содержать:

1. back button в список блоков;
2. editable block title;
3. block type badge `design_block`;
4. breakpoint switcher `desktop / tablet / mobile`;
5. save button;
6. place button в стандартные виджеты InstantCMS;
7. optional dirty/saving status.

### 4.4 Canvas area contract

Canvas area содержит:

1. локально отрисованный DOM stage без iframe;
2. overlay selection helpers, drag handles, guide lines и 12-колоночную сетку;
3. background work area;
4. adaptive width switch according to active breakpoint;
5. inline contenteditable text editing для текстовых и button-like элементов.

### 4.5 Sidebar contract

Правая панель фиксирована по структуре donor-style:

1. `Дизайн-блок`
2. `Холст`
3. `Секция`
4. `Слои`
5. `Свойства элемента`

Для v1 не делаем generic tabbed inspector как у schema-block editors. Здесь нужен contextual freeform sidebar.

---

## 5. State bootstrap contract

### 5.1 Источник bootstrap

JS shell не должен собирать стартовое состояние из случайных inline фрагментов.

Канонический источник истины:

`GET /admin/controllers/edit/nordicblocks/block_design_state/{id}`

### 5.2 Минимальный JSON response

```json
{
  "ok": true,
  "block": {
    "id": 42,
    "type": "design_block",
    "title": "Design Block — Hero 01",
    "status": "active"
  },
  "contract": {},
  "editor": {
    "saveUrl": ".../block_save/42",
    "canvasUrl": ".../block_design_canvas/42",
    "placeUrl": ".../admin/widgets?...",
    "backUrl": ".../admin/controllers/edit/nordicblocks/blocks",
    "csrfToken": "..."
  },
  "palette": {
    "items": []
  },
  "pickers": {
    "image": "instantcms_image_modal",
    "icon": "instantcms_icon_modal",
    "file": "instantcms_file_modal"
  },
  "ui": {
    "activeBreakpoint": "desktop",
    "selectedElementId": null,
    "sidebarSection": "section"
  }
}
```

### 5.3 Что грузим из template сразу

Template может отдать только минимальный bootstrap:

1. `block_id`
2. `state_url`

Полный contract лучше грузить через `block_design_state`, чтобы shell можно было обновлять reload-safe способом без засорения PHP template.

---

## 6. JS shell state contract

### 6.1 Два слоя состояния

JS shell должен разделять:

1. persisted document state;
2. transient UI/editor state.

### 6.2 Persisted state

`documentState` обязан содержать:

1. `block`
2. `contract`
3. `version`
4. `lastSavedAt`

### 6.3 UI state

`uiState` должен содержать:

1. `activeBreakpoint`
2. `selectedElementId`
3. `selectedElementIds`
4. `selectedSidebarCard`
5. `layersCollapsedIds`
6. `addMenuOpen`
7. `isDirty`
8. `isSaving`
9. `canvasHeight`
10. `canvasReady`

### 6.4 Transient interaction state

`interactionState` не сохраняется на сервер и не входит в contract:

1. `dragElementId`
2. `resizeHandle`
3. `guideState`
4. `dropHint`
5. `hoverElementId`
6. `clipboardSelection`

### 6.5 Store rule

Freeform shell может использовать нативный JS module store или отдельный state object.

Главное:

1. persisted contract и UI state должны быть разведены;
2. transient interaction state не должен попадать в save payload.

---

## 7. Sidebar cards v1

### 7.1 Карточка `Дизайн-блок`

Содержит:

1. breakpoint switcher;
2. optional block meta summary;
3. future-ready preset info.

### 7.2 Карточка `Холст`

Содержит breakpoint-aware controls:

1. `showColumnsGrid`
2. `columnsGridColor`
3. `columnsGridOpacity`
4. `layout.stage.desktop.grid.columns`
5. `layout.stage.tablet.grid.columns`
6. `layout.stage.mobile.grid.columns`
7. `layout.stage.*.grid.gutter`
8. `layout.stage.*.grid.bleedX`
9. `snapToGrid`
10. `gridSize`
11. `snapThreshold`
12. `showGuides`

Семантика панели:

1. shell показывает одновременно grid container и window container текущего breakpoint;
2. `x=0` всегда равен левой границе grid container;
3. отрицательный `x` не считается ошибкой и визуально уводит элемент в bleed;
4. переключение breakpoint меняет grid contract, а не масштабирует старую сцену.

### 7.3 Карточка `Секция`

Содержит минимум:

1. `backgroundColor`
2. `minHeightDesktop`
3. `minHeightTablet`
4. `minHeightMobile`

### 7.4 Карточка `Слои`

Содержит:

1. tree of elements;
2. nested container rendering;
3. z-index actions;
4. add element entry point;
5. selection support;
6. collapsed/expanded state for containers.

### 7.5 Карточка `Свойства элемента`

При single selection показывает группы:

1. `Header`
2. `Geometry`
3. `Constraints`
4. `Appearance by type`
5. `Styles / Effects`
6. `Advanced`

При multi-selection показывает bulk layout actions.

При empty selection показывает empty state.

---

## 8. Canvas <-> shell bridge

### 8.1 Основа

Связь между shell и iframe должна идти через `postMessage`, как уже сделано в current NordicBlocks preview bridge.

### 8.2 Message sources

Фиксируем источники:

1. shell -> iframe: `source = nordicblocks-editor`
2. iframe -> shell: `source = nordicblocks-canvas`

### 8.3 Сообщения shell -> iframe

Минимальный набор для freeform mode:

1. `design:select-element`
2. `design:clear-selection`
3. `design:request-metrics`
4. `design:apply-preview-contract`
5. `design:reload-preview`

### 8.4 Сообщения iframe -> shell

Минимальный набор:

1. `design:canvas-ready`
2. `design:canvas-selection`
3. `design:canvas-metrics`
4. `design:element-double-click`

### 8.5 Selection contract

`design:canvas-selection` должен возвращать:

1. `elementId`
2. `elementType`
3. `selectionKind = single | multi | empty`
4. optional `entityRole`

Именно shell решает, какую sidebar card открыть по этому событию.

---

## 9. Save cycle

### 9.1 Dirty rule

Любое изменение contract-bearing state:

1. помечает shell как dirty;
2. обновляет preview;
3. не сохраняет transient UI state в payload.

### 9.2 Save payload

`POST block_save/{id}` должен отправлять:

```json
{
  "title": "Design Block — Hero 01",
  "contract": {},
  "csrf_token": "..."
}
```

### 9.3 Save UX

Обязательно поддержать:

1. click save;
2. `Ctrl+S` / `Cmd+S`;
3. visible saving state;
4. clear success/error feedback.

Автосохранение допустимо позже, но не обязательно в первой реализации.

---

## 10. Picker integrations

### 10.1 Жёсткое правило

Freeform shell не делает собственный media/icon manager.

### 10.2 Что поддерживаем

1. `instantcms_image_modal`
2. `instantcms_icon_modal`
3. `instantcms_file_modal`

### 10.3 Contract rule

В JS shell хранится только hint уровня picker type.

Нельзя тащить в contract:

1. CSS selectors модалок;
2. donor React callback names;
3. DOM traversal details.

---

## 11. Recommended files

### 11.1 PHP

1. `system/controllers/nordicblocks/backend/actions/block_edit.php`
2. `system/controllers/nordicblocks/backend/actions/block_design_state.php`
3. `system/controllers/nordicblocks/backend/actions/block_design_canvas.php`
4. `templates/admincoreui/controllers/nordicblocks/backend/editor_design_block.tpl.php`

### 11.2 JS/CSS shell assets

1. `templates/admincoreui/controllers/nordicblocks/backend/js/design-block-editor.js`
2. `templates/admincoreui/controllers/nordicblocks/backend/css/design-block-editor.css`

Если repo prefers inline shell script initially, это допустимо только как переходный шаг. Целевой путь — вынесенный asset.

---

## 12. Definition of Done

Editor shell v1 считается зафиксированным, если одновременно выполнены условия:

1. `block_edit` умеет dispatch-ить `design_block` в отдельный template;
2. существует formal state endpoint `block_design_state`;
3. PHP template имеет зафиксированные regions topbar/canvas/sidebar;
4. JS state разделён на document/ui/transient слои;
5. bridge между shell и iframe формализован;
6. sidebar повторяет donor-style карточки и группы, но живёт в NordicBlocks backend flow.