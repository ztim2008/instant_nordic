# NordicBlocks Design Block Tech Spec V1

Дата: 2026-04-20

## 1. Назначение документа

Этот документ фиксирует implementation-ready контур для `design_block` внутри NordicBlocks.

Здесь описываются 4 вещи:

1. storage contract для freeform-блока;
2. renderer contract для preview/live SSR;
3. element palette v1 и точные группы свойств sidebar;
4. backend editor flow и маршруты для freeform mode в текущем InstantCMS-репозитории.

Документ опирается на donor `design-block-02` из `nordic-builder.ru`, но не копирует его 1:1.

Главное правило этого spec:

`design_block` должен дать donor-уровень свободы внутри одной секции, но остаться обычным NordicBlocks block record с placement через стандартный widget InstantCMS.

Связанные документы:

1. `docs/nordicblocks/DESIGN-BLOCK-RUNTIME-CONTRACT-V1.md`
2. `docs/nordicblocks/DESIGN-BLOCK-EDITOR-SHELL-V1.md`
3. `docs/nordicblocks/DESIGN-BLOCK-IMPLEMENTATION-PLAN-V1.md`

---

## 2. Donor baseline, который реально переносим

Из donor `design-block-02` берём не page-builder архитектуру, а конкретные рабочие идеи:

1. element-driven scene contract;
2. отдельный freeform editor mode;
3. element tree и selection model;
4. responsive patches `desktop / tablet / mobile`;
5. набор типов элементов и их sidebar-свойства;
6. CSS-first export-модель для predictable render output.

Подтверждённый donor baseline:

1. block id: `design-block-02`;
2. category: `design-blocks`;
3. current donor palette: `text`, `image`, `button`, `shape`, `icon`, `container`, `video`, `divider`, `svg`;
4. правая панель разделена на `Дизайн-блок`, `Холст`, `Секция`, `Слои`, `Свойства элемента`;
5. `Свойства элемента` внутри donor состоят из групп `header`, `geometry`, `constraints`, `appearance by type`, `styles/effects`, `advanced`.

Что осознанно не переносим 1:1:

1. page-scoped project store `project.pages[].blocks[]`;
2. публикацию как document export вместо InstantCMS block runtime;
3. приватный runtime, отдельный от NordicBlocks widget render;
4. editor shell как отдельный продукт рядом с NordicBlocks backend.

---

## 3. Канонический storage contract v1

### 3.1 Верхнеуровневое правило

Физически `design_block` хранится в текущем `cms_nordicblocks_blocks.props_json`, но логически это уже полный Block Contract v3 с freeform-веткой.

### 3.2 Root shape

```json
{
  "meta": {},
  "content": {},
  "design": {},
  "layout": {},
  "data": {},
  "entities": {},
  "runtime": {}
}
```

### 3.3 Минимальный design block contract

```json
{
  "meta": {
    "contractVersion": 3,
    "blockType": "design_block",
    "schemaVersion": 1,
    "rendererVersion": 1,
    "label": "Design Block",
    "status": "active"
  },
  "content": {
    "section": {
      "name": "Design Block",
      "elements": []
    }
  },
  "design": {
    "section": {
      "background": {
        "mode": "solid",
        "color": "#ffffff"
      }
    }
  },
  "layout": {
    "stage": {
      "desktop": {
        "width": 1200,
        "minHeight": 520,
        "paddingX": 24,
        "paddingY": 24,
        "grid": {
          "columns": 12,
          "gutter": 20,
          "bleedX": 160
        }
      },
      "tablet": {
        "width": 768,
        "minHeight": 520,
        "paddingX": 20,
        "paddingY": 20,
        "grid": {
          "columns": 8,
          "gutter": 16,
          "bleedX": 96
        }
      },
      "mobile": {
        "width": 390,
        "minHeight": 520,
        "paddingX": 16,
        "paddingY": 16,
        "grid": {
          "columns": 4,
          "gutter": 12,
          "bleedX": 32
        }
      }
    }
  },
  "data": {
    "source": {
      "type": "manual"
    },
    "bindings": {},
    "listSource": null,
    "meta": {
      "bindingVersion": 1
    }
  },
  "entities": {
    "byElementId": {}
  },
  "runtime": {
    "renderMode": "ssr",
    "editorMode": "freeform",
    "renderer": "design_block_v1",
    "cssProfile": "absolute_stage",
    "breakpoints": ["desktop", "tablet", "mobile"],
    "editor": {
      "snapToGrid": true,
      "gridSize": 8,
      "snapThreshold": 6,
      "showGuides": true,
      "showColumnsGrid": false,
      "columnsGridColor": "#0f172a",
      "columnsGridOpacity": 8
    }
  }
}
```

### 3.4 Что куда складываем

1. `meta` хранит версию контракта, тип блока и версию renderer-а.
2. `content.section.elements[]` хранит саму сцену и элементы.
3. `design.section.background` хранит видимые свойства секции, которые влияют на live.
4. `layout.stage.*` хранит размеры сцен для breakpoints и grid contract (`columns`, `gutter`, `bleedX`).
5. `data` заранее резервируется под будущие bindings.
6. `entities.byElementId` даёт semantic map для элементов, если позже появятся bindable roles.
7. `runtime.editor` хранит editor-only настройки, которые не должны ломать live renderer.

### 3.5 Что запрещено хранить в contract

1. текущий selection state;
2. drag state;
3. transient resize handles;
4. DOM selectors;
5. JS callback names;
6. raw SQL/query logic;
7. page-level navigation state редактора.

Итоговое правило:

contract хранит только то, что нужно для повторяемого render/editor восстановления, а не временное состояние интерфейса.

---

## 4. Element contract v1

### 4.1 Каноническая форма элемента

```json
{
  "id": "el_hero_title",
  "type": "text",
  "name": "Заголовок",
  "zIndex": 3,
  "hidden": false,
  "locked": false,
  "groupId": null,
  "parentId": null,
  "constraints": {
    "horizontal": "left",
    "vertical": "top"
  },
  "desktop": {
    "box": { "x": 64, "y": 64, "w": 720, "h": 140 },
    "props": {},
    "sizing": {}
  },
  "tablet": {
    "box": {},
    "props": {},
    "sizing": {}
  },
  "mobile": {
    "box": {},
    "props": {},
    "sizing": {}
  }
}
```

### 4.2 Обязательные поля элемента

1. `id` — стабильный id элемента.
2. `type` — один из разрешённых palette types.
3. `name` — человекочитаемое имя для layers panel.
4. `zIndex` — порядок слоёв.
5. `hidden` — скрытие из render без удаления.
6. `locked` — блокировка редактирования.
7. `parentId` — опциональный контейнер-родитель.
8. `constraints` — якоря как в donor/Figma.
9. `desktop` — базовое состояние.
10. `tablet` и `mobile` — patch-слои поверх desktop.

### 4.3 Breakpoint-модель

Для v1 используем donor-compatible правило:

1. `desktop` — базовый источник истины;
2. `tablet` — patch поверх desktop;
3. `mobile` — patch поверх desktop и tablet;
4. если patch пустой, live и editor используют desktop/base state.

### 4.4 Geometry и sizing

У каждого элемента есть 2 связанных слоя:

1. `box` — `x`, `y`, `w`, `h`;
2. `sizing` — optional unit-aware sizing для `w` и `h` через `px` или `%`.

Это сохраняет donor-модель, где geometry редактируется в sidebar как `X / Y / W / H`, а ширина и высота при необходимости могут жить в процентах.

### 4.5 Parent/container model

Если элемент вложен в `container`, то:

1. `parentId` указывает на id контейнера;
2. локальные `x / y` считаются от content-area контейнера;
3. padding контейнера влияет на доступную внутреннюю область;
4. если контейнер работает в `flex`, layout задаётся раскладкой контейнера, а не свободными координатами ребёнка.

---

## 5. Element palette v1

### 5.1 Канонический palette v1

Palette v1 должен совпадать с donor baseline и не расширяться раньше времени:

1. `text`
2. `image`
3. `button`
4. `shape`
5. `icon`
6. `container`
7. `video`
8. `divider`
9. `svg`

Критичное уточнение:

отдельный `heading` type в v1 не заводим. Заголовки и подзаголовки в первой итерации живут как `text`-элементы с semantic role через `entities.byElementId`.

### 5.2 Общие sidebar-группы для любого элемента

Как и в donor, правая панель выбранного элемента должна иметь стабильный каркас:

1. `Header` — имя элемента, selection/meta controls.
2. `Geometry` — `X`, `Y`, `W`, `H`, unit `px/%`, parent container.
3. `Constraints` — горизонтальные и вертикальные якоря.
4. `Appearance by type` — поля конкретного типа элемента.
5. `Styles / Effects` — rotation, blend, border, shadow, filter, animation.
6. `Advanced` — hidden, locked, move to front.

### 5.3 Общие свойства всех элементов

Для всех palette types в contract поддерживаются общие style/runtime-поля donor-уровня:

1. `rotate`
2. `mixBlendMode`
3. `effects.opacityPct`
4. `effects.filter.blurPx`
5. `effects.filter.brightnessPct`
6. `effects.filter.contrastPct`
7. `effects.filter.saturatePct`
8. `effects.textShadow` — только для text-like элементов
9. `boxShadow`
10. `borderWidth`
11. `borderColor`
12. `borderStyle`
13. `anim.kind`
14. `anim.durationMs`
15. `anim.delayMs`
16. `anim.easing`
17. `anim.iterations`
18. `anim.fillMode`
19. `anim.direction`
20. `anim.trigger`

### 5.4 Свойства `text`

`text` должен повторять donor sidebar:

1. `text`
2. `fontFamily`
3. `color`
4. `fontSize`
5. `fontWeight`
6. `lineHeight`
7. `letterSpacing`
8. `textAlign`
9. `textTransform`
10. `fontStyle`
11. `textDecoration`

### 5.5 Свойства `image`

1. `src`
2. `alt`
3. `objectFit`
4. `borderRadius`

Фото выбирается через штатную InstantCMS image modal. В contract хранится только итоговый путь/asset ref.

### 5.6 Свойства `svg`

1. `src`
2. `alt`
3. `objectFit`
4. `borderRadius`

SVG выбирается через file picker, но live renderer обязан обрабатывать его как обычный asset source без donor-only логики.

### 5.7 Свойства `video`

1. `src`
2. `poster`
3. `controls`
4. `autoplay`
5. `loop`
6. `muted`
7. `playsInline`
8. `objectFit`
9. `borderRadius`

### 5.8 Свойства `button`

Кнопка должна получить donor-compatible набор, а не урезанный CTA-object:

1. `text`
2. `link`
3. `textColor`
4. `backgroundColor`
5. `backgroundCss`
6. `borderRadius`
7. `fontSize`
8. `fontWeight`
9. `paddingX`
10. `paddingY`
11. `hoverTextColor`
12. `hoverBackgroundColor`
13. `hoverBackgroundCss`
14. `activeTextColor`
15. `activeBackgroundColor`
16. `activeBackgroundCss`
17. `disabled`
18. `disabledTextColor`
19. `disabledBackgroundColor`

### 5.9 Свойства `shape`

1. `shape` = `rect | circle`
2. `fill`
3. `fillCss`
4. `borderRadius`
5. `opacity`
6. `blur`
7. `glass`
8. `backdropBlur`

### 5.10 Свойства `icon`

1. `iconClass`
2. `color`
3. `size`

Иконки выбираются только через штатный InstantCMS icon picker. В contract не хранится donor-specific DOM UI.

### 5.11 Свойства `divider`

1. `orientation`
2. `thickness`
3. `color`
4. `borderRadius`

### 5.12 Свойства `container`

`container` повторяет donor как локальный frame/layout node:

1. `backgroundColor`
2. `borderRadius`
3. `paddingTop`
4. `paddingRight`
5. `paddingBottom`
6. `paddingLeft`
7. `clipContent`
8. `layoutMode` = `absolute | flex`
9. `direction`
10. `justifyContent`
11. `alignItems`
12. `gap`
13. `wrap`

### 5.13 Что не входит в palette v1

1. отдельный `group` element type;
2. произвольный custom HTML node;
3. collection/repeater node;
4. arbitrary code widget;
5. freeform form/query element.

Группировка в v1 допускается как editor behavior через multi-selection и `groupId`, но не как отдельный production element type.

---

## 6. Sidebar architecture v1

### 6.1 Верхний уровень правой панели

Как и в donor, freeform sidebar должен состоять из фиксированных карточек:

1. `Дизайн-блок` — breakpoint switcher `desktop / tablet / mobile`;
2. `Холст` — grid, snap, guides, bleed, columns overlay;
3. `Секция` — section background и min-height;
4. `Слои` — tree, z-order, nested containers, add element entry point;
5. `Свойства элемента` — текущий selection inspector.

### 6.2 Карточка `Холст`

В v1 фиксируем breakpoint-aware набор:

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

Ключевое правило v1:

1. `width` описывает ширину grid container, а не полный window container;
2. `grid.bleedX` расширяет window container влево и вправо относительно grid container;
3. world x=0 совпадает с левой границей grid container;
4. отрицательный `x` допустим и означает уход элемента в bleed-область.

### 6.3 Карточка `Секция`

На первой итерации обязательны:

1. фон секции;
2. минимальная высота desktop;
3. минимальная высота tablet;
4. минимальная высота mobile.

Даже если donor currently хранит только desktop/mobile, для NordicBlocks v1 лучше сразу нормализовать stage-height на три breakpoint-состояния.

---

## 7. Renderer contract v1

### 7.1 Главный принцип

Freeform editor может быть JS-heavy, но live output обязан оставаться SSR-совместимым выводом через стандартный NordicBlocks runtime.

### 7.2 Точка вывода

Для live и preview используется один и тот же block type renderer:

1. file: `system/controllers/nordicblocks/blocks/design_block/render.php`
2. widget entry: `system/widgets/nordicblocks_block/widget.php`
3. preview entry: backend `block_design_canvas` или unified `block_canvas`, который вызывает тот же renderer.

### 7.3 Renderer input

Renderer получает не raw `props_json`, а уже нормализованный contract:

1. `meta`
2. `content.section.elements`
3. `design.section.background`
4. `layout.stage.*`
5. `entities.byElementId`
6. `runtime.renderer`

### 7.4 Renderer output contract

Как и остальные NordicBlocks block types, design block renderer обязан вернуть:

1. `html`
2. `inline_css`

Каноническая HTML-структура v1:

```html
<section class="nb-design-block nb-design-block--v1" id="nb-design-block-42">
  <div class="nb-design-block__stage">
    <div class="nb-design-el nb-design-el--text" data-el-id="el_hero_title"></div>
  </div>
</section>
```

### 7.5 CSS generation model

Для v1 фиксируем donor-style, но NordicBlocks-native подход:

1. один DOM tree;
2. desktop styles как base;
3. tablet/mobile patches как media query overrides;
4. эффекты и animation-only поля генерируются в CSS, а не в runtime JS;
5. renderer не тащит editor chrome в публичный output.

### 7.6 Что renderer обязан поддержать в v1

1. absolute positioning for stage children;
2. nested containers;
3. donor-compatible constraints model;
4. box sizing in `px` и `%`;
5. type-specific markup for text/image/button/shape/icon/container/video/divider/svg;
6. CSS-only filters, shadows, borders, blend modes, rotation and animation.

### 7.7 Что renderer не должен делать

1. не читать данные из базы напрямую по content type;
2. не знать про editor selection;
3. не выполнять donor export workflow отдельно от NordicBlocks;
4. не решать widget placement;
5. не зависеть от отдельного React runtime на публичной странице.

### 7.8 Preview/live parity rule

Preview iframe и live widget обязаны использовать один и тот же pipeline:

1. read contract;
2. normalize contract;
3. build design block render payload;
4. render HTML;
5. build CSS.

Любая ветка preview-only styling считается нарушением канонического контура.

---

## 8. Backend editor flow v1

### 8.1 Точка входа

Общий backend URL открытия блока должен остаться прежним:

`GET /admin/controllers/edit/nordicblocks/block_edit/{block_id}`

Правило диспетчеризации:

1. обычные block types продолжают открываться в existing inspector shell;
2. если `block.type === design_block`, `block_edit` открывает отдельный freeform editor template.

### 8.2 Канонические backend actions

Для v1 фиксируем такой набор actions:

1. `backend/actions/block_edit.php`
  - единая точка входа;
  - dispatch по `block.type`.
2. `backend/actions/block_design_state.php`
  - `GET` contract/state для freeform editor.
3. `backend/actions/block_design_canvas.php`
  - `GET` standalone preview iframe через тот же renderer.
4. `backend/actions/block_save.php`
  - общий `POST` save для contract-based blocks;
  - для `design_block` принимает полный `contract`.

При желании можно добавить alias `block_design_save.php`, но канонически лучше не плодить второй write endpoint без необходимости.

### 8.3 Рекомендуемые URL

1. `GET /admin/controllers/edit/nordicblocks/block_edit/{id}`
2. `GET /admin/controllers/edit/nordicblocks/block_design_state/{id}`
3. `GET /admin/controllers/edit/nordicblocks/block_design_canvas/{id}`
4. `POST /admin/controllers/edit/nordicblocks/block_save/{id}`

### 8.4 Editor page composition

Freeform editor template должен жить отдельно от current hero/faq shell:

1. `templates/admincoreui/controllers/nordicblocks/backend/editor_design_block.tpl.php`
2. topbar: back, block title, breakpoint, save, place action;
3. main area: canvas/stage;
4. right column: donor-style cards `Дизайн-блок`, `Холст`, `Секция`, `Слои`, `Свойства элемента`.

### 8.5 State load contract

`block_design_state` должен вернуть минимум:

```json
{
  "ok": true,
  "block": {
    "id": 42,
    "type": "design_block",
    "title": "Design block — Hero 01",
    "status": "active"
  },
  "contract": {},
  "editor": {
    "saveUrl": ".../block_save/42",
    "canvasUrl": ".../block_design_canvas/42",
    "placeUrl": "...admin/widgets?...",
    "csrfToken": "..."
  },
  "pickers": {
    "image": "instantcms_image_modal",
    "icon": "instantcms_icon_modal"
  }
}
```

### 8.6 Save contract

`block_save` для `design_block` принимает:

1. `title`
2. `contract`
3. `csrf_token`

Сервер обязан:

1. проверить admin-session;
2. проверить CSRF;
3. проверить `block.type === design_block`;
4. нормализовать freeform contract;
5. сохранить его через contract-first путь, а не через schema-fields sanitizer.

### 8.7 Canvas contract

`block_design_canvas` должен:

1. читать тот же contract, что live;
2. вызывать тот же renderer;
3. возвращать standalone HTML для iframe preview;
4. не добавлять editor-only CSS в саму секцию.

---

## 9. Маршруты и файлы текущего репозитория

### 9.1 Файлы, которые должны появиться или расшириться

1. `system/controllers/nordicblocks/backend/actions/block_design_state.php`
2. `system/controllers/nordicblocks/backend/actions/block_design_canvas.php`
3. `system/controllers/nordicblocks/backend/actions/block_edit.php`
4. `system/controllers/nordicblocks/backend/actions/block_save.php`
5. `system/controllers/nordicblocks/blocks/design_block/render.php`
6. `templates/admincoreui/controllers/nordicblocks/backend/editor_design_block.tpl.php`
7. `system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`
8. `system/controllers/nordicblocks/libs/DesignBlockRenderer.php`

### 9.2 Почему так

Это сохраняет текущую архитектуру NordicBlocks:

1. backend actions живут рядом с обычными block actions;
2. renderer живёт внутри `blocks/{type}`;
3. save остаётся в том же CRUD-контуре;
4. widget placement не меняется вообще.

---

## 10. Ограничения первой реализации

В v1 осознанно не делаем:

1. отдельный page editor из нескольких design sections;
2. произвольный HTML embed как элемент;
3. data binding прямо on-canvas;
4. donor-style template marketplace для freeform секций;
5. complex motion system beyond current CSS animation model;
6. multi-root nested containers без ограничений глубины.

---

## 11. Definition of Done для tech spec v1

Этот tech spec считается достаточным для начала реализации, если зафиксированы условия:

1. storage contract описан формально и не конфликтует с Block Contract v3;
2. renderer contract явно отделяет editor data от live SSR output;
3. element palette v1 совпадает с donor-набором типов и их sidebar-свойств;
4. backend flow не плодит второй placement-контур;
5. preview/live опираются на один renderer;
6. freeform mode остаётся отдельной веткой внутри NordicBlocks, а не новым продуктом поверх InstantCMS.