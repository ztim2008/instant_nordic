# NordicBlocks Design Block Mode V1

Дата: 2026-04-20

## 1. Зачем нужен отдельный документ

Раздел в `NORDICBLOCKS-DEV.md` уже фиксирует product-идею design block, но для реальной разработки этого мало.

Нужен отдельный документ, который заранее удерживает 4 вещи:

1. design block не превращает NordicBlocks во второй page-builder;
2. design block не ломает нативный widget placement InstantCMS;
3. freeform editor, storage и runtime описываются как отдельная ветка внутри NordicBlocks, а не как отдельный продукт рядом;
4. перенос donor-идеи из `nordic-builder.ru` идёт не 1:1, а через NordicBlocks-native контракт.

Итоговое правило:

`design_block` — это отдельный block type внутри NordicBlocks для свободной авторской секции, но не отдельный builder всего сайта.

Связанные рабочие документы:

1. `docs/nordicblocks/DESIGN-BLOCK-MODE-V1.md` — product-рамка и границы режима.
2. `docs/nordicblocks/DESIGN-BLOCK-TECH-SPEC-V1.md` — storage, renderer, palette, backend flow.
3. `docs/nordicblocks/DESIGN-BLOCK-RUNTIME-CONTRACT-V1.md` — PHP normalizer и render payload.
4. `docs/nordicblocks/DESIGN-BLOCK-EDITOR-SHELL-V1.md` — admin template и JS shell contract.
5. `docs/nordicblocks/DESIGN-BLOCK-IMPLEMENTATION-PLAN-V1.md` — порядок внедрения по этапам.

---

## 2. Продуктовая позиция

### 2.1 Что такое design block

`design_block` нужен для сценариев, где schema-driven блоков уже недостаточно:

1. сложный hero с нестандартной композицией;
2. авторская промо-секция;
3. art-directed рекламный экран;
4. свободная секция с несколькими слоями, картинками, shape-элементами и точной композицией.

Это именно свободная секция в рамках одного блока.

### 2.2 Чем он не является

`design_block` не является:

1. takeover-page-builder поверх InstantCMS;
2. отдельной системой страниц;
3. второй схемой размещения;
4. React-only runtime для live-страницы;
5. заменой обычных `hero`, `faq`, `news`, `cta` и других managed block types.

### 2.3 Каноническая продуктовая формула

NordicBlocks остаётся библиотекой секций с placement через стандартные виджеты InstantCMS.

`design_block` добавляет в эту библиотеку отдельный premium/freeform режим, но не меняет главный продуктовый workflow:

1. создать блок;
2. настроить его в редакторе;
3. сохранить;
4. разместить тем же `nordicblocks_block` в схеме InstantCMS.

---

## 3. Почему donor нельзя переносить 1:1

Исследование donor `design-block-02` из `nordic-builder.ru` показало:

1. block живёт внутри page-scoped project store, а не как самостоятельная reusable block-сущность;
2. editor сильно завязан на React page-builder shell;
3. export логика ближе к HTML/CSS document output, чем к SSR widget runtime;
4. storage и editing построены вокруг страницы, а не вокруг standalone InstantCMS block placement.

Для NordicBlocks такой перенос опасен, потому что сломает главный продуктовый принцип:

в InstantCMS размещение должно оставаться нативным через структуру, схемы и виджеты.

Значит, design block берёт из donor не архитектуру целиком, а целевой уровень свободы редактора и модель element-driven секции.

---

## 4. Жёсткие продуктовые ограничения

### 4.1 Что нельзя допустить

1. Нельзя делать отдельный placement-путь для design block.
2. Нельзя делать отдельную публичную rendering-систему, оторванную от NordicBlocks runtime.
3. Нельзя хранить design block как page-scoped сущность вместо обычного блока.
4. Нельзя превращать visual editor в место для бизнес-логики InstantCMS.
5. Нельзя допускать расхождение preview и live.
6. Нельзя пускать freeform mode как новую основу всего продукта.

### 4.2 Что обязано остаться общим с NordicBlocks

1. единая таблица блоков и общий block lifecycle;
2. тот же `nordicblocks_block` widget;
3. единый cache/invalidation контур;
4. единый подход к preview/live hydration;
5. общая design foundation и общая vocabulary-система сущностей там, где это возможно.

---

## 5. Место design block в архитектуре NordicBlocks

### 5.1 Базовая модель

Внутри NordicBlocks появляется ещё один block type:

1. `hero`
2. `faq`
3. `news_*`
4. `design_block`

У него своя editor-ветка, но общий runtime-контур платформы.

### 5.2 Отдельный editor mode

Для `design_block` нужен отдельный editor page/mode, потому что обычный schema-first inspector shell для него недостаточен.

Минимальный состав design block editor:

1. canvas;
2. element tree;
3. selection model;
4. drag/resize/position controls;
5. правая панель свойств выбранного элемента;
6. breakpoint switcher `desktop / tablet / mobile`.

Критично:

это отдельный editor mode внутри NordicBlocks backend, а не отдельное приложение с собственной публикационной логикой.

### 5.3 Связь с общим backend flow

Общий backend flow должен остаться таким:

1. блок типа `design_block` виден в общем списке блоков NordicBlocks;
2. при открытии блок попадает в отдельный design editor route;
3. после save блок остаётся обычным block record;
4. дальше его выбирают тем же виджетом в структуре InstantCMS.

---

## 6. Канонический storage-контур

### 6.1 Верхнеуровневое правило

Физически `design_block` может храниться в том же `props_json`, но логически он обязан жить как Block Contract v3.

### 6.2 Минимальная форма контракта

```json
{
  "meta": {
    "contractVersion": 3,
    "blockType": "design_block",
    "schemaVersion": 1,
    "label": "Design Block",
    "status": "active"
  },
  "content": {
    "elements": []
  },
  "design": {
    "theme": "light",
    "background": {}
  },
  "layout": {
    "desktop": {},
    "tablet": {},
    "mobile": {}
  },
  "data": {
    "source": {
      "type": "manual"
    },
    "bindings": {}
  },
  "entities": {},
  "runtime": {
    "renderMode": "ssr",
    "editorMode": "freeform"
  }
}
```

### 6.3 Что важно в этой форме

1. `content.elements[]` заменяет schema-field модель как основной слой содержимого;
2. `design` и `layout` остаются отдельными слоями, а не растворяются внутри элементов без правил;
3. `data` зарезервирован под будущие bindings и не смешивается с geometry;
4. `runtime` фиксирует, что это freeform block editor mode, но всё ещё SSR block runtime.

---

## 7. Element model v1

### 7.1 Базовый принцип

Design block должен быть element-driven, но не бесконтрольным.

На первой итерации нужен ограниченный donor-compatible набор элементов:

1. `text`
2. `image`
3. `button`
4. `shape`
5. `icon`
6. `container`
7. `video`
8. `divider`
9. `svg`

Если добавить слишком много element types сразу, редактор быстро расползётся раньше, чем появится зрелый runtime.

Отдельный `heading` type в v1 не нужен: заголовки и подзаголовки делаем как `text`-элементы с semantic role.

Отдельный `group` type в v1 тоже не нужен: grouping остаётся editor behavior через multi-selection и `groupId`, а не отдельным production element type.

### 7.2 Минимальная форма одного элемента

```json
{
  "id": "el_hero_title",
  "type": "text",
  "name": "Главный заголовок",
  "visible": true,
  "content": {
    "text": "Сильная секция без takeover-builder"
  },
  "style": {
    "color": "#111111",
    "fontSize": {
      "desktop": 64,
      "tablet": 48,
      "mobile": 34
    }
  },
  "layout": {
    "desktop": {
      "x": 120,
      "y": 96,
      "w": 620,
      "h": 96
    },
    "tablet": {},
    "mobile": {}
  },
  "binding": null
}
```

### 7.3 Что обязательно для element model

1. стабильный `id`;
2. тип элемента;
3. content-слой;
4. style-слой;
5. responsive layout-слой;
6. `visible`;
7. будущая точка для binding.

---

## 8. Responsive и layout-модель

### 8.1 Breakpoints

Для design block v1 фиксируем 3 breakpoint-состояния:

1. `desktop`
2. `tablet`
3. `mobile`

### 8.2 Правило хранения

Desktop является базой.

Tablet и mobile хранятся как переопределения поверх desktop, а не как три полностью независимых документа.

### 8.3 Координатная модель

На первой итерации допустима смешанная модель:

1. абсолютное позиционирование внутри секции;
2. контейнерные группы для упрощения композиций;
3. ограниченный набор constraints, чтобы mobile не разваливался полностью.

Важно не пытаться сразу повторить весь Tilda/Webflow-уровень constraints engine.

---

## 9. Editor UX v1

### 9.1 Что должен уметь editor на первой итерации

1. создавать блок `design_block`;
2. открывать отдельный freeform editor;
3. добавлять элементы из ограниченной палитры;
4. выбирать элемент на canvas и в tree;
5. двигать и ресайзить элемент;
6. переключать breakpoint;
7. редактировать контент и базовые style/layout свойства;
8. сохранять контракт без отдельного publish-режима.

### 9.2 Что сознательно не входит в первую итерацию

1. полноценная анимационная timeline;
2. сложные relations между элементами;
3. on-canvas data mapping;
4. page-wide multi-section editing;
5. arbitrary custom JS внутри блока.

### 9.3 Роль правой панели

Правая панель должна работать не как общий schema form, а как contextual properties panel выбранного элемента или секции.

То есть:

1. выбрана секция — показываются свойства секции;
2. выбран text/heading — показываются text/style/layout controls;
3. выбрана image — показываются media/style/layout controls;
4. выбран group/container — показываются group layout controls.

---

## 10. Preview, live и renderer

### 10.1 Каноническое правило

Design block не должен иметь отдельный JS-only live runtime.

Preview и live обязаны собираться из одного и того же block contract.

### 10.2 Практический вывод

Для `design_block` нужен не donor-style export HTML/CSS как отдельная внешняя ветка, а NordicBlocks-native section renderer:

1. читает contract;
2. нормализует responsive layout;
3. собирает section HTML;
4. генерирует предсказуемый CSS слой или CSS variables;
5. отдаёт тот же результат в preview iframe и в live widget runtime.

### 10.3 SSR-позиция

Даже если editor сам по себе будет JS-heavy, live output должен оставаться SSR-совместимым и SEO-safe на уровне секции.

---

## 11. Связь с data adapters

### 11.1 Не в первой итерации

Первая рабочая версия `design_block` не обязана сразу быть deeply data-driven.

Сначала нужно стабилизировать:

1. storage contract;
2. editor UX;
3. preview/live parity;
4. section renderer.

### 11.2 Но binding-слой должен быть предусмотрен

Контракт должен заранее оставлять место под будущие binding-сценарии:

1. text element -> `title`, `subtitle`, `price`, `date`;
2. image element -> `cover_image` и image-like поля;
3. button -> `record_url` или ручной fallback;
4. collection/group элементы -> будущий `content_list` слой.

Итог:

v1 design block может быть manual-first, но не должен быть архитектурным тупиком для adapter layer.

---

## 12. Интеграция со штатными InstantCMS picker-механиками

Даже в freeform editor сохраняется правило NordicBlocks:

1. изображения выбираются через штатную InstantCMS image modal;
2. иконки выбираются через штатную InstantCMS icon modal;
3. contract хранит нормализованное значение и editor hint, но не DOM-детали модалки;
4. design block не получает отдельный самодельный media manager как основу.

Это важно для совместимости и для удержания продукта в границах InstantCMS.

---

## 13. Рекомендуемый порядок внедрения

### 13.1 Что должно быть готово до design block

Перед активной реализацией design block должны быть уже стабилизированы:

1. Block Contract v3;
2. Adapter Contract v1;
3. unified inspector foundation для обычных blocks;
4. preview/live hydration pipeline;
5. cache/invalidation контур для обычного runtime.

### 13.2 Порядок design block rollout

1. Документ и storage contract.
2. Минимальный renderer для section/elements.
3. Отдельный backend editor mode.
4. Manual-first element palette v1.
5. Preview/live parity smoke.
6. Только потом — первый binding layer.

---

## 14. Definition of Done для первого design block stage

Первая стадия design block считается принятой, если одновременно выполнены условия:

1. `design_block` существует как отдельный block type внутри NordicBlocks;
2. block хранится как самостоятельная block-сущность, а не как page fragment;
3. block размещается тем же `nordicblocks_block` widget;
4. editor использует отдельный freeform mode;
5. preview и live читают один и тот же contract и один renderer;
6. images/icons используют штатные InstantCMS picker-механики;
7. первая итерация остаётся manual-first и не тащит page-builder архитектуру в ядро NordicBlocks.

---

## 15. Практическая рекомендация на текущий момент

Design block стоит считать не задачей "сделать сейчас любой ценой", а следующей крупной product-веткой после стабилизации основной block system.

Но документ нужно держать уже сейчас, потому что он:

1. не даёт случайно свернуть NordicBlocks в takeover-builder;
2. удерживает общие архитектурные правила заранее;
3. помогает проектировать editor/runtime/storage под будущий freeform режим без хаоса;
4. даёт понятную рамку для дальнейшего tech spec и implementation plan.