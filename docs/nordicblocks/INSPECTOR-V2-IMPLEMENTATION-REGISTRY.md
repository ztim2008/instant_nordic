# NordicBlocks Inspector V2 Implementation Registry

Дата: 2026-04-16

## 1. Назначение документа

Этот документ не про общую идею, а про implementation-ready слой для следующего этапа.

Его задача:

1. зафиксировать canonical entity registry;
2. зафиксировать capability matrix;
3. зафиксировать inspector panel registry;
4. определить правила видимости секций;
5. дать эталонный профиль для hero-блока;
6. дать основу, на которой можно начинать прототип нового Inspector Shell v2.

Этот документ используется как bridge между архитектурным планом и кодом.

## 2. Граница ответственности

### 2.1 Что считается входом для Inspector Shell

Inspector Shell получает от backend уже нормализованный payload:

```json
{
  "contract": {},
  "registry": {
    "entities": {},
    "capabilities": {},
    "panels": {},
    "tabs": []
  },
  "ui": {
    "selectedEntity": null,
    "selectedRepeaterPath": null,
    "activeTab": "content",
    "activeBreakpoint": "desktop"
  }
}
```

### 2.2 Что backend обязан сделать до frontend

До передачи в shell backend обязан:

1. прочитать block contract;
2. нормализовать legacy flat props;
3. определить активные entities;
4. определить capability flags для блока;
5. собрать registry payload для этого блока;
6. не тащить в frontend случайные сырые schema-ключи без нормализации.

### 2.3 Что frontend shell делает сам

Frontend shell делает:

1. рендер вкладок;
2. рендер панелей;
3. visibility filtering;
4. selection state;
5. repeater item state;
6. responsive tab state;
7. dirty-state и autosave hooks.

## 3. Канонический набор вкладок

Вкладки фиксируются один раз и не зависят от блока:

1. `content`
2. `design`
3. `layout`
4. `data`

Registry tabs:

```json
[
  { "key": "content", "label": "Контент", "order": 10 },
  { "key": "design",  "label": "Дизайн",  "order": 20 },
  { "key": "layout",  "label": "Макет",   "order": 30 },
  { "key": "data",    "label": "Данные",  "order": 40 }
]
```

## 4. Canonical Entity Registry

### 4.1 Общая структура записи

Каждая сущность описывается по одному формату:

```json
{
  "key": "title",
  "label": "Заголовок",
  "kind": "text",
  "level": "block",
  "styleSlot": "title",
  "dataSlot": "title",
  "contentPath": "content.title",
  "designPath": "design.entities.title",
  "layoutPath": null,
  "supports": {
    "content": true,
    "design": true,
    "layout": false,
    "data": true,
    "responsiveTypography": true,
    "responsiveSpacing": false,
    "states": false
  }
}
```

### 4.2 Правила registry

1. `key` всегда канонический и общий для всех блоков.
2. `kind` определяет базовый тип панели.
3. `level` бывает `block` или `item`.
4. `styleSlot` используется design runtime и canvas highlight logic.
5. `dataSlot` используется в binding layer.
6. `contentPath` и `designPath` нужны для generic inspector field binding.
7. `supports` управляет допустимыми control groups.

### 4.3 Канонический реестр v1

```json
{
  "eyebrow": {
    "key": "eyebrow",
    "label": "Надзаголовок",
    "kind": "text",
    "level": "block",
    "styleSlot": "eyebrow",
    "dataSlot": "badge",
    "contentPath": "content.eyebrow",
    "designPath": "design.entities.eyebrow",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": true,
      "states": false
    }
  },
  "title": {
    "key": "title",
    "label": "Заголовок",
    "kind": "text",
    "level": "block",
    "styleSlot": "title",
    "dataSlot": "title",
    "contentPath": "content.title",
    "designPath": "design.entities.title",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": true,
      "states": false
    }
  },
  "subtitle": {
    "key": "subtitle",
    "label": "Подзаголовок",
    "kind": "text",
    "level": "block",
    "styleSlot": "subtitle",
    "dataSlot": "subtitle",
    "contentPath": "content.subtitle",
    "designPath": "design.entities.subtitle",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": true,
      "states": false
    }
  },
  "body": {
    "key": "body",
    "label": "Основной текст",
    "kind": "text",
    "level": "block",
    "styleSlot": "body",
    "dataSlot": "body",
    "contentPath": "content.body",
    "designPath": "design.entities.body",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": true,
      "states": false
    }
  },
  "primaryButton": {
    "key": "primaryButton",
    "label": "Основная кнопка",
    "kind": "button",
    "level": "block",
    "styleSlot": "primaryButton",
    "dataSlot": "primaryButton",
    "contentPath": "content.primaryButton",
    "designPath": "design.entities.primaryButton",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": false,
      "states": true
    }
  },
  "secondaryButton": {
    "key": "secondaryButton",
    "label": "Вторичная кнопка",
    "kind": "button",
    "level": "block",
    "styleSlot": "secondaryButton",
    "dataSlot": "secondaryButton",
    "contentPath": "content.secondaryButton",
    "designPath": "design.entities.secondaryButton",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": false,
      "states": true
    }
  },
  "media": {
    "key": "media",
    "label": "Медиа",
    "kind": "media",
    "level": "block",
    "styleSlot": "media",
    "dataSlot": "image",
    "contentPath": "content.media",
    "designPath": "design.entities.media",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": false,
      "states": false
    }
  },
  "mediaSurface": {
    "key": "mediaSurface",
    "label": "Поверхность медиа",
    "kind": "surface",
    "level": "block",
    "styleSlot": "mediaSurface",
    "dataSlot": null,
    "contentPath": null,
    "designPath": "design.entities.mediaSurface",
    "supports": {
      "content": false,
      "design": true,
      "layout": false,
      "data": false,
      "responsiveTypography": false,
      "states": false
    }
  },
  "itemSurface": {
    "key": "itemSurface",
    "label": "Поверхность элемента",
    "kind": "surface",
    "level": "item",
    "styleSlot": "itemSurface",
    "dataSlot": null,
    "contentPath": null,
    "designPath": "design.entities.itemSurface",
    "supports": {
      "content": false,
      "design": true,
      "layout": false,
      "data": false,
      "responsiveTypography": false,
      "states": false
    }
  },
  "itemTitle": {
    "key": "itemTitle",
    "label": "Заголовок элемента",
    "kind": "text",
    "level": "item",
    "styleSlot": "itemTitle",
    "dataSlot": "items[].title",
    "contentPath": "content.items[].title",
    "designPath": "design.entities.itemTitle",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": true,
      "states": false
    }
  },
  "itemText": {
    "key": "itemText",
    "label": "Текст элемента",
    "kind": "text",
    "level": "item",
    "styleSlot": "itemText",
    "dataSlot": "items[].text",
    "contentPath": "content.items[].text",
    "designPath": "design.entities.itemText",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": true,
      "states": false
    }
  },
  "itemBadge": {
    "key": "itemBadge",
    "label": "Плашка элемента",
    "kind": "text",
    "level": "item",
    "styleSlot": "itemBadge",
    "dataSlot": "items[].badge",
    "contentPath": "content.items[].badge",
    "designPath": "design.entities.itemBadge",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": true,
      "responsiveTypography": true,
      "states": false
    }
  },
  "itemIcon": {
    "key": "itemIcon",
    "label": "Иконка элемента",
    "kind": "icon",
    "level": "item",
    "styleSlot": "itemIcon",
    "dataSlot": "items[].icon",
    "contentPath": "content.items[].icon",
    "designPath": "design.entities.itemIcon",
    "supports": {
      "content": true,
      "design": true,
      "layout": false,
      "data": false,
      "responsiveTypography": false,
      "states": false
    }
  }
}
```

## 5. Capability Registry

### 5.1 Общая структура capability

Каждая capability описывается отдельно и используется как coarse-grained feature flag для UI.

```json
{
  "key": "titleTypography",
  "label": "Типографика заголовка",
  "tab": "design",
  "requiresEntities": ["title"],
  "panelKeys": ["titleTypography"],
  "default": false
}
```

### 5.2 Канонический список capability v1

```json
{
  "sectionBackground": {
    "key": "sectionBackground",
    "label": "Фон секции",
    "tab": "design",
    "default": true
  },
  "sectionContainer": {
    "key": "sectionContainer",
    "label": "Контейнер секции",
    "tab": "design",
    "default": true
  },
  "titleContent": {
    "key": "titleContent",
    "label": "Контент заголовка",
    "tab": "content",
    "requiresEntities": ["title"],
    "default": false
  },
  "subtitleContent": {
    "key": "subtitleContent",
    "label": "Контент подзаголовка",
    "tab": "content",
    "requiresEntities": ["subtitle"],
    "default": false
  },
  "bodyContent": {
    "key": "bodyContent",
    "label": "Контент body",
    "tab": "content",
    "requiresEntities": ["body"],
    "default": false
  },
  "buttonsContent": {
    "key": "buttonsContent",
    "label": "Контент кнопок",
    "tab": "content",
    "requiresAnyEntities": ["primaryButton", "secondaryButton"],
    "default": false
  },
  "mediaContent": {
    "key": "mediaContent",
    "label": "Контент медиа",
    "tab": "content",
    "requiresEntities": ["media"],
    "default": false
  },
  "repeaterContent": {
    "key": "repeaterContent",
    "label": "Контент repeaters",
    "tab": "content",
    "requiresEntities": ["items"],
    "default": false
  },
  "titleTypography": {
    "key": "titleTypography",
    "label": "Типографика заголовка",
    "tab": "design",
    "requiresEntities": ["title"],
    "default": false
  },
  "subtitleTypography": {
    "key": "subtitleTypography",
    "label": "Типографика подзаголовка",
    "tab": "design",
    "requiresEntities": ["subtitle"],
    "default": false
  },
  "bodyTypography": {
    "key": "bodyTypography",
    "label": "Типографика body",
    "tab": "design",
    "requiresEntities": ["body"],
    "default": false
  },
  "buttonsStyle": {
    "key": "buttonsStyle",
    "label": "Стиль кнопок",
    "tab": "design",
    "requiresAnyEntities": ["primaryButton", "secondaryButton"],
    "default": false
  },
  "mediaSurface": {
    "key": "mediaSurface",
    "label": "Поверхность медиа",
    "tab": "design",
    "requiresEntities": ["mediaSurface"],
    "default": false
  },
  "itemSurface": {
    "key": "itemSurface",
    "label": "Поверхность элементов",
    "tab": "design",
    "requiresEntities": ["itemSurface"],
    "default": false
  },
  "itemTypography": {
    "key": "itemTypography",
    "label": "Типографика элементов",
    "tab": "design",
    "requiresAnyEntities": ["itemTitle", "itemText", "itemBadge"],
    "default": false
  },
  "spacingLayout": {
    "key": "spacingLayout",
    "label": "Spacing",
    "tab": "layout",
    "default": true
  },
  "alignmentLayout": {
    "key": "alignmentLayout",
    "label": "Alignment",
    "tab": "layout",
    "default": true
  },
  "responsiveTypography": {
    "key": "responsiveTypography",
    "label": "Responsive typography",
    "tab": "layout",
    "default": false
  },
  "responsiveSpacing": {
    "key": "responsiveSpacing",
    "label": "Responsive spacing",
    "tab": "layout",
    "default": false
  },
  "dataBindings": {
    "key": "dataBindings",
    "label": "Привязки данных",
    "tab": "data",
    "default": false
  },
  "repeaterBindings": {
    "key": "repeaterBindings",
    "label": "Привязки repeater",
    "tab": "data",
    "requiresEntities": ["items"],
    "default": false
  }
}
```

## 6. Capability Matrix

### 6.1 Формат матрицы

Матрица нужна, чтобы быстро понять, какие панели доступны конкретному типу блока.

```json
{
  "hero": {
    "entities": [],
    "capabilities": {}
  }
}
```

### 6.2 Hero Reference Matrix

Это эталон для следующего прототипа Inspector Shell v2.

```json
{
  "hero": {
    "entities": [
      "eyebrow",
      "title",
      "subtitle",
      "primaryButton",
      "secondaryButton",
      "media",
      "mediaSurface"
    ],
    "capabilities": {
      "sectionBackground": true,
      "sectionContainer": true,
      "titleContent": true,
      "subtitleContent": true,
      "bodyContent": false,
      "buttonsContent": true,
      "mediaContent": true,
      "repeaterContent": false,
      "titleTypography": true,
      "subtitleTypography": true,
      "bodyTypography": false,
      "buttonsStyle": true,
      "mediaSurface": true,
      "itemSurface": false,
      "itemTypography": false,
      "spacingLayout": true,
      "alignmentLayout": true,
      "responsiveTypography": true,
      "responsiveSpacing": true,
      "dataBindings": true,
      "repeaterBindings": false
    }
  }
}
```

### 6.3 FAQ Reference Matrix

Нужна как вторая проверка registry-логики.

```json
{
  "faq": {
    "entities": [
      "eyebrow",
      "title",
      "subtitle",
      "items",
      "itemSurface",
      "itemTitle",
      "itemText"
    ],
    "capabilities": {
      "sectionBackground": true,
      "sectionContainer": true,
      "titleContent": true,
      "subtitleContent": true,
      "bodyContent": false,
      "buttonsContent": false,
      "mediaContent": false,
      "repeaterContent": true,
      "titleTypography": true,
      "subtitleTypography": true,
      "bodyTypography": false,
      "buttonsStyle": false,
      "mediaSurface": false,
      "itemSurface": true,
      "itemTypography": true,
      "spacingLayout": true,
      "alignmentLayout": true,
      "responsiveTypography": true,
      "responsiveSpacing": true,
      "dataBindings": true,
      "repeaterBindings": true
    }
  }
}
```

## 7. Panel Registry

### 7.1 Общая структура панели

```json
{
  "key": "titleTypography",
  "label": "Заголовок",
  "tab": "design",
  "section": "typography",
  "group": "title",
  "order": 120,
  "requiresCapabilities": ["titleTypography"],
  "requiresEntities": ["title"],
  "entityScope": "title",
  "controlPreset": "typographyText",
  "breakpointAware": true,
  "repeatable": false
}
```

### 7.2 Поля panel registry

1. `key` — уникальный id панели.
2. `tab` — вкладка.
3. `section` — крупный раздел внутри вкладки.
4. `group` — логическая группа.
5. `order` — порядок.
6. `requiresCapabilities` — hard gate.
7. `requiresEntities` или `requiresAnyEntities` — entity gate.
8. `entityScope` — к какой сущности относится панель.
9. `controlPreset` — какой пресет UI-контролов использовать.
10. `breakpointAware` — есть ли desktop/mobile режим.
11. `repeatable` — нужно ли уметь работать по item index.

### 7.3 Control presets

На первом этапе панели не должны описывать каждый input по одному. Они должны ссылаться на переиспользуемые preset-наборы.

Первые presets:

1. `textContent`
2. `buttonContent`
3. `mediaContent`
4. `repeaterItems`
5. `typographyText`
6. `buttonStyle`
7. `surfaceStyle`
8. `sectionBackground`
9. `sectionContainer`
10. `spacingLayout`
11. `alignmentLayout`
12. `dataBindingSingle`
13. `dataBindingRepeater`

### 7.4 Канонический panel registry v1

```json
[
  {
    "key": "textEyebrowContent",
    "label": "Надзаголовок",
    "tab": "content",
    "section": "text",
    "group": "eyebrow",
    "order": 110,
    "requiresCapabilities": ["titleContent"],
    "requiresEntities": ["eyebrow"],
    "entityScope": "eyebrow",
    "controlPreset": "textContent",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "textTitleContent",
    "label": "Заголовок",
    "tab": "content",
    "section": "text",
    "group": "title",
    "order": 120,
    "requiresCapabilities": ["titleContent"],
    "requiresEntities": ["title"],
    "entityScope": "title",
    "controlPreset": "textContent",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "textSubtitleContent",
    "label": "Подзаголовок",
    "tab": "content",
    "section": "text",
    "group": "subtitle",
    "order": 130,
    "requiresCapabilities": ["subtitleContent"],
    "requiresEntities": ["subtitle"],
    "entityScope": "subtitle",
    "controlPreset": "textContent",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "buttonsContent",
    "label": "Кнопки",
    "tab": "content",
    "section": "actions",
    "group": "buttons",
    "order": 210,
    "requiresCapabilities": ["buttonsContent"],
    "requiresAnyEntities": ["primaryButton", "secondaryButton"],
    "entityScope": "buttons",
    "controlPreset": "buttonContent",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "mediaContent",
    "label": "Медиа",
    "tab": "content",
    "section": "media",
    "group": "media",
    "order": 310,
    "requiresCapabilities": ["mediaContent"],
    "requiresEntities": ["media"],
    "entityScope": "media",
    "controlPreset": "mediaContent",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "repeaterItems",
    "label": "Элементы",
    "tab": "content",
    "section": "repeaters",
    "group": "items",
    "order": 410,
    "requiresCapabilities": ["repeaterContent"],
    "requiresEntities": ["items"],
    "entityScope": "items",
    "controlPreset": "repeaterItems",
    "breakpointAware": false,
    "repeatable": true
  },
  {
    "key": "sectionBackground",
    "label": "Фон секции",
    "tab": "design",
    "section": "section",
    "group": "background",
    "order": 110,
    "requiresCapabilities": ["sectionBackground"],
    "entityScope": "section",
    "controlPreset": "sectionBackground",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "sectionContainer",
    "label": "Контейнер",
    "tab": "design",
    "section": "section",
    "group": "container",
    "order": 120,
    "requiresCapabilities": ["sectionContainer"],
    "entityScope": "section",
    "controlPreset": "sectionContainer",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "titleTypography",
    "label": "Заголовок",
    "tab": "design",
    "section": "typography",
    "group": "title",
    "order": 210,
    "requiresCapabilities": ["titleTypography"],
    "requiresEntities": ["title"],
    "entityScope": "title",
    "controlPreset": "typographyText",
    "breakpointAware": true,
    "repeatable": false
  },
  {
    "key": "subtitleTypography",
    "label": "Подзаголовок",
    "tab": "design",
    "section": "typography",
    "group": "subtitle",
    "order": 220,
    "requiresCapabilities": ["subtitleTypography"],
    "requiresEntities": ["subtitle"],
    "entityScope": "subtitle",
    "controlPreset": "typographyText",
    "breakpointAware": true,
    "repeatable": false
  },
  {
    "key": "bodyTypography",
    "label": "Body",
    "tab": "design",
    "section": "typography",
    "group": "body",
    "order": 230,
    "requiresCapabilities": ["bodyTypography"],
    "requiresEntities": ["body"],
    "entityScope": "body",
    "controlPreset": "typographyText",
    "breakpointAware": true,
    "repeatable": false
  },
  {
    "key": "buttonsStyle",
    "label": "Кнопки",
    "tab": "design",
    "section": "actions",
    "group": "buttons",
    "order": 310,
    "requiresCapabilities": ["buttonsStyle"],
    "requiresAnyEntities": ["primaryButton", "secondaryButton"],
    "entityScope": "buttons",
    "controlPreset": "buttonStyle",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "mediaSurface",
    "label": "Поверхность медиа",
    "tab": "design",
    "section": "surfaces",
    "group": "mediaSurface",
    "order": 410,
    "requiresCapabilities": ["mediaSurface"],
    "requiresEntities": ["mediaSurface"],
    "entityScope": "mediaSurface",
    "controlPreset": "surfaceStyle",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "itemSurface",
    "label": "Поверхность элементов",
    "tab": "design",
    "section": "surfaces",
    "group": "itemSurface",
    "order": 420,
    "requiresCapabilities": ["itemSurface"],
    "requiresEntities": ["itemSurface"],
    "entityScope": "itemSurface",
    "controlPreset": "surfaceStyle",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "itemTypography",
    "label": "Типографика элементов",
    "tab": "design",
    "section": "typography",
    "group": "items",
    "order": 430,
    "requiresCapabilities": ["itemTypography"],
    "requiresAnyEntities": ["itemTitle", "itemText", "itemBadge"],
    "entityScope": "items",
    "controlPreset": "typographyText",
    "breakpointAware": true,
    "repeatable": false
  },
  {
    "key": "spacingLayout",
    "label": "Отступы",
    "tab": "layout",
    "section": "spacing",
    "group": "spacing",
    "order": 110,
    "requiresCapabilities": ["spacingLayout"],
    "entityScope": "section",
    "controlPreset": "spacingLayout",
    "breakpointAware": true,
    "repeatable": false
  },
  {
    "key": "alignmentLayout",
    "label": "Выравнивание",
    "tab": "layout",
    "section": "alignment",
    "group": "alignment",
    "order": 120,
    "requiresCapabilities": ["alignmentLayout"],
    "entityScope": "section",
    "controlPreset": "alignmentLayout",
    "breakpointAware": true,
    "repeatable": false
  },
  {
    "key": "dataBindings",
    "label": "Привязки",
    "tab": "data",
    "section": "bindings",
    "group": "single",
    "order": 110,
    "requiresCapabilities": ["dataBindings"],
    "entityScope": "block",
    "controlPreset": "dataBindingSingle",
    "breakpointAware": false,
    "repeatable": false
  },
  {
    "key": "repeaterBindings",
    "label": "Привязки списка",
    "tab": "data",
    "section": "bindings",
    "group": "repeaters",
    "order": 120,
    "requiresCapabilities": ["repeaterBindings"],
    "requiresEntities": ["items"],
    "entityScope": "items",
    "controlPreset": "dataBindingRepeater",
    "breakpointAware": false,
    "repeatable": true
  }
]
```

## 8. Visibility Engine

### 8.1 Каноническое правило видимости панели

Панель видна, только если одновременно выполняются условия:

1. активна вкладка панели;
2. все `requiresCapabilities` имеют значение `true`;
3. все `requiresEntities` присутствуют у блока;
4. хотя бы одна из `requiresAnyEntities` присутствует, если она задана;
5. если панель repeatable и выбран item mode, панель знает active item path;
6. если панель breakpointAware, shell знает активный breakpoint.

Псевдокод:

```ts
function isPanelVisible(panel, blockState) {
  if (!isTabEnabled(panel.tab, blockState)) return false
  if (!hasAllCapabilities(panel.requiresCapabilities, blockState.capabilities)) return false
  if (!hasAllEntities(panel.requiresEntities, blockState.entities)) return false
  if (!hasAnyEntities(panel.requiresAnyEntities, blockState.entities)) return false
  if (panel.repeatable && panel.entityScope === 'items' && !blockState.ui.selectedRepeaterPath && blockState.ui.selectionMode === 'item') return false
  return true
}
```

### 8.2 Selection-aware panels

Когда пользователь кликает по canvas-элементу:

1. shell получает `selectedEntity`;
2. если entity имеет `preferredPanelKeys`, эти панели поднимаются наверх;
3. вкладка переключается автоматически только при первом выборе, дальше не прыгает без запроса пользователя.

## 9. Hero Prototype Scope

Следующий прототип Inspector Shell v2 должен покрывать только hero reference-block.

### 9.1 Что обязательно должно работать

1. Вкладки `Контент / Дизайн / Макет / Данные`.
2. Entity selection для `eyebrow`, `title`, `subtitle`, `primaryButton`, `secondaryButton`, `media`.
3. Автоматическое скрытие всех item/repeater секций.
4. Типографика заголовка и подзаголовка.
5. Контент кнопок и стиль кнопок.
6. Медиа-панель.
7. Section background и container panel.
8. Responsive desktop/mobile toggle для typography и spacing.

### 9.2 Что не входит в первый hero prototype

1. Drag-drop секций на canvas.
2. Rich text.
3. Nested repeaters.
4. Full adapter UI для content list.
5. Visual CSS freeform editor.

## 10. Hero Prototype Block Shape

Нормализованный hero contract для прототипа должен иметь минимум:

```json
{
  "meta": {
    "contractVersion": 3,
    "blockType": "hero",
    "schemaVersion": 1,
    "label": "Hero"
  },
  "content": {
    "eyebrow": "Новый продукт",
    "title": "Главный оффер",
    "subtitle": "Короткое пояснение",
    "primaryButton": {
      "label": "Оставить заявку",
      "url": "/contacts"
    },
    "secondaryButton": {
      "label": "Подробнее",
      "url": "/about"
    },
    "media": {
      "image": "",
      "alt": ""
    }
  },
  "design": {
    "section": {},
    "entities": {
      "eyebrow": {},
      "title": {},
      "subtitle": {},
      "primaryButton": {},
      "secondaryButton": {},
      "mediaSurface": {}
    }
  },
  "layout": {
    "desktop": {},
    "mobile": {}
  },
  "data": {
    "bindings": {}
  },
  "entities": {
    "eyebrow": { "kind": "text", "styleSlot": "eyebrow" },
    "title": { "kind": "text", "styleSlot": "title" },
    "subtitle": { "kind": "text", "styleSlot": "subtitle" },
    "primaryButton": { "kind": "button", "styleSlot": "primaryButton" },
    "secondaryButton": { "kind": "button", "styleSlot": "secondaryButton" },
    "media": { "kind": "media", "styleSlot": "media" },
    "mediaSurface": { "kind": "surface", "styleSlot": "mediaSurface" }
  },
  "capabilities": {
    "sectionBackground": true,
    "sectionContainer": true,
    "titleContent": true,
    "subtitleContent": true,
    "buttonsContent": true,
    "mediaContent": true,
    "titleTypography": true,
    "subtitleTypography": true,
    "buttonsStyle": true,
    "mediaSurface": true,
    "spacingLayout": true,
    "alignmentLayout": true,
    "responsiveTypography": true,
    "responsiveSpacing": true,
    "dataBindings": true
  }
}
```

## 11. Backend Output Contract для Shell

Для старта прототипа backend должен отдавать не HTML-фрагменты настроек, а структурированный JSON.

Минимальная форма:

```json
{
  "ok": true,
  "block": {
    "id": 42,
    "type": "hero",
    "title": "Hero — Главная"
  },
  "contract": {},
  "registry": {
    "tabs": [],
    "entities": {},
    "capabilities": {},
    "panels": []
  },
  "ui": {
    "selectedEntity": null,
    "selectedRepeaterPath": null,
    "activeTab": "content",
    "activeBreakpoint": "desktop"
  }
}
```

## 12. Что должно быть в коде следующим шагом

### 12.1 Backend

Нужны отдельные слои:

1. `BlockContractNormalizer`
2. `BlockEntityResolver`
3. `BlockCapabilityResolver`
4. `InspectorRegistryBuilder`

### 12.2 Frontend

Нужны отдельные слои:

1. `InspectorShellApp`
2. `useInspectorStore`
3. `PanelRenderer`
4. `CanvasSelectionBridge`
5. `ControlPresetRenderer`

## 13. Рекомендуемое файловое разбиение для следующего шага

Это не обязательные имена, но рекомендованный ориентир.

### PHP

1. `system/controllers/nordicblocks/libs/BlockContractNormalizer.php`
2. `system/controllers/nordicblocks/libs/BlockEntityResolver.php`
3. `system/controllers/nordicblocks/libs/BlockCapabilityResolver.php`
4. `system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php`
5. `system/controllers/nordicblocks/backend/actions/block_editor_state.php`

### Frontend shell

1. `frontend/nordicblocks-inspector-shell/src/main.ts`
2. `frontend/nordicblocks-inspector-shell/src/store/inspector.ts`
3. `frontend/nordicblocks-inspector-shell/src/components/InspectorTabs.vue`
4. `frontend/nordicblocks-inspector-shell/src/components/InspectorPanelRenderer.vue`
5. `frontend/nordicblocks-inspector-shell/src/components/presets/*.vue`

## 14. Acceptance Criteria для документа

Этот registry layer считается утвержденным, если:

1. у каждого нового блока есть canonical entities;
2. capability matrix определяет крупные возможности блока;
3. panel registry управляет показом секций без ручной верстки под каждый блок;
4. hero-block можно поднять на этом слое без добавления block-specific inspector шаблона;
5. faq-block можно поднять на этом же слое как вторая проверка, уже с repeaters.

## 15. Следующий шаг после этого документа

Следующий практический шаг уже должен быть кодовым:

1. поднять backend state endpoint для hero;
2. собрать Inspector Shell v2 только для hero;
3. завести selection bridge по `data-nb-entity`;
4. проверить, что shell реально скрывает неактуальные панели;
5. только потом переводить faq и features.

Это и будет первая настоящая проверка, что архитектура живет не только в документах, а в коде.