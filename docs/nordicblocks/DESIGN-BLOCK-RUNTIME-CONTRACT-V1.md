# NordicBlocks Design Block Runtime Contract V1

Дата: 2026-04-20

## 1. Цель документа

Этот документ фиксирует PHP-контур для `design_block` внутри NordicBlocks.

Он отвечает на 2 вопроса:

1. как `design_block` нормализуется в канонический contract на сервере;
2. какой render payload должен получить `render.php`, чтобы preview и live шли по одному SSR-контуру.

Главное правило:

`design_block` не должен тянуть отдельный frontend runtime на публичную страницу. Его live output обязан собираться сервером через тот же NordicBlocks render path, что и остальные block types.

---

## 2. Место в текущем runtime NordicBlocks

### 2.1 Канонический pipeline

Для `design_block` pipeline должен быть таким:

1. block record читается из `cms_nordicblocks_blocks`;
2. `props_json` подаётся в общий contract dispatcher;
3. dispatcher передаёт `design_block` в dedicated normalizer;
4. normalizer возвращает Block Contract v3 freeform-ветки;
5. при preview/live вызывается dedicated payload builder;
6. `blocks/design_block/render.php` рендерит уже готовый payload;
7. widget/runtime получает `html + inline_css`.

### 2.2 Встраивание в текущие классы

В текущем репозитории уже есть:

1. `NordicblocksBlockContractNormalizer`
2. `NordicblocksBlockPayloadHydrator`

Для `design_block` вводим такой паттерн:

1. `NordicblocksBlockContractNormalizer` остаётся root dispatcher;
2. внутри него `design_block` передаётся в `NordicblocksDesignBlockContractNormalizer`;
3. для render-ветки вводится `NordicblocksDesignBlockRenderPayloadBuilder`;
4. `NordicblocksBlockPayloadHydrator` остаётся общим для adapter-driven block types и не смешивается с freeform geometry-логикой.

Причина:

если засунуть freeform normalizer целиком внутрь общего `BlockContractNormalizer`, он быстро превратится в перегруженный комбайн.

---

## 3. PHP normalizer contract

### 3.1 Канонический класс

```php
class NordicblocksDesignBlockContractNormalizer {
    public static function supportsType(string $type): bool;
    public static function normalize(array $block): array;
}
```

### 3.2 Ответственность normalizer-а

`NordicblocksDesignBlockContractNormalizer` обязан:

1. принять block record из модели;
2. распознать, пришёл уже contract-first payload или legacy/freeform-черновик;
3. нормализовать root keys `meta/content/design/layout/data/entities/runtime`;
4. нормализовать stage settings;
5. нормализовать массив элементов;
6. allowlist-очистить все style/effects/animation поля;
7. выбросить editor-transient значения;
8. вернуть предсказуемый server-side contract.

### 3.3 Что normalizer не делает

1. не выбирает данные InstantCMS по content type;
2. не строит HTML;
3. не вычисляет live CSS строки;
4. не знает про DOM редактора;
5. не хранит current selection или drag state.

### 3.4 Root dispatcher rule

В `NordicblocksBlockContractNormalizer` добавляется ветка:

```php
if ($type === 'design_block') {
    return NordicblocksDesignBlockContractNormalizer::normalize($block);
}
```

`supportsContractType()` тоже должен знать `design_block` как поддерживаемый contract-first тип.

---

## 4. Root contract shape для normalizer output

### 4.1 Канонический output

Normalizer всегда возвращает shape:

```php
[
    'meta' => [],
    'content' => [],
    'design' => [],
    'layout' => [],
    'data' => [],
    'entities' => [],
    'runtime' => [],
]
```

### 4.2 Meta layer

`meta` должен содержать минимум:

1. `contractVersion = 3`
2. `blockType = design_block`
3. `schemaVersion = 1`
4. `rendererVersion = 1`
5. `label`
6. `status`

### 4.3 Content layer

`content` для design block хранит:

1. `section.name`
2. `section.elements[]`

Допускается future-ready `content.fallbackText` или `content.meta`, но не в первой итерации.

### 4.4 Design layer

В v1 туда входит минимум:

1. `section.background.mode`
2. `section.background.color`
3. future-ready `gradient`, `image`, `overlay`

### 4.5 Layout layer

В v1 туда входит:

1. `stage.desktop`
2. `stage.tablet`
3. `stage.mobile`

Каждый stage содержит:

1. `width`
2. `minHeight`
3. `bleedX`

### 4.6 Data layer

В v1 `data` для design block по умолчанию manual-first:

1. `source.type = manual`
2. `bindings = []`
3. `listSource = null`

Но слой обязан существовать сразу, чтобы `design_block` не стал архитектурным тупиком для future adapter integration.

### 4.7 Entities layer

`entities.byElementId` хранит semantic role map, например:

```php
[
    'el_hero_title' => [
        'kind' => 'text',
        'role' => 'title',
    ],
]
```

Это позволяет позже подключать bindable roles без переизобретения контракта.

### 4.8 Runtime layer

`runtime` должен содержать:

1. `renderMode = ssr`
2. `editorMode = freeform`
3. `renderer = design_block_v1`
4. `cssProfile = absolute_stage`
5. `breakpoints = ['desktop', 'tablet', 'mobile']`
6. `editor.*` — grid/snap/guides settings

---

## 5. Element normalizer contract

### 5.1 Канонический helper-слой

Внутри `NordicblocksDesignBlockContractNormalizer` нужны private helpers:

1. `normalizeMeta()`
2. `normalizeSectionDesign()`
3. `normalizeStageLayout()`
4. `normalizeElements()`
5. `normalizeElement()`
6. `normalizeElementDesktopState()`
7. `normalizeElementPatch()`
8. `normalizeElementPropsByType()`
9. `normalizeCommonStyleProps()`
10. `normalizeAnimationProps()`

### 5.2 Канонический element output

Каждый элемент после normalizer-а обязан иметь:

1. `id`
2. `type`
3. `name`
4. `zIndex`
5. `hidden`
6. `locked`
7. `groupId`
8. `parentId`
9. `constraints`
10. `desktop.box`
11. `desktop.props`
12. `desktop.sizing`
13. optional `tablet`
14. optional `mobile`

### 5.3 Type allowlist

Допустимые типы только такие:

1. `text`
2. `image`
3. `button`
4. `shape`
5. `icon`
6. `container`
7. `video`
8. `divider`
9. `svg`

Неизвестный тип не должен доходить до renderer-а. Он должен:

1. либо быть отброшен;
2. либо быть заменён на safe fallback `text` с пустым содержимым.

Для v1 предпочтительнее отбрасывать неизвестный type с серверным warning, чем silently портить render.

### 5.4 Type-specific prop normalizers

Для каждого типа нужен dedicated allowlist:

1. `normalizeTextProps()`
2. `normalizeImageProps()`
3. `normalizeButtonProps()`
4. `normalizeShapeProps()`
5. `normalizeIconProps()`
6. `normalizeContainerProps()`
7. `normalizeVideoProps()`
8. `normalizeDividerProps()`
9. `normalizeSvgProps()`

### 5.5 Common style normalizer

Во все type-specific props может быть встроен общий style layer:

1. `rotate`
2. `mixBlendMode`
3. `boxShadow`
4. `borderWidth`
5. `borderColor`
6. `borderStyle`
7. `effects.opacityPct`
8. `effects.filter.*`
9. `effects.textShadow`
10. `anim.*`

Все значения проходят strict allowlist, как это уже сделано в donor editor logic.

---

## 6. Legacy / transitional input support

### 6.1 Что считаем transitional input

Для `design_block` допустимы два входных режима:

1. уже готовый contract-first payload;
2. raw freeform payload переходной фазы, близкий к donor `data.ts` shape.

### 6.2 Transitional input example

```php
[
    'backgroundColor' => '#ffffff',
    'minHeightDesktop' => 520,
    'minHeightMobile' => 520,
    'elements' => [...],
]
```

Normalizer обязан поднять это в full Block Contract v3.

### 6.3 Что не поддерживаем

1. page-scoped donor project blob;
2. несколько секций в одном design block payload;
3. произвольный runtime state из React store;
4. HTML export blobs вместо scene contract.

---

## 7. Render payload builder

### 7.1 Канонический класс

```php
class NordicblocksDesignBlockRenderPayloadBuilder {
    public static function build(array $contract, array $context = []): array;
}
```

### 7.2 Задача payload builder-а

Он получает уже нормализованный contract и возвращает render-ready payload для `render.php`.

Builder обязан:

1. вычислить effective stage settings;
2. разложить элементы по z-order;
3. собрать effective desktop/tablet/mobile props для каждого элемента;
4. подготовить markup model для type-specific renderer helpers;
5. собрать section-level CSS variables;
6. собрать CSS rules по breakpoints;
7. вернуть predictable payload без editor-only state.

### 7.3 Что builder не делает

1. не читает block record из базы;
2. не валидирует CSRF;
3. не знает про admin template;
4. не подмешивает JS editor runtime.

---

## 8. Render payload shape v1

### 8.1 Канонический payload

```php
[
    'blockId' => 42,
    'blockType' => 'design_block',
    'section' => [
        'id' => 'nb-design-block-42',
        'classes' => ['nb-design-block', 'nb-design-block--v1'],
        'attrs' => [],
    ],
    'stage' => [
        'desktop' => [],
        'tablet' => [],
        'mobile' => [],
    ],
    'elements' => [
        [
            'id' => 'el_hero_title',
            'type' => 'text',
            'zIndex' => 3,
            'hidden' => false,
            'htmlTag' => 'div',
            'content' => [],
            'attrs' => [],
            'classes' => [],
            'styleVars' => [],
        ],
    ],
    'css' => [
        'sectionVars' => [],
        'desktop' => '',
        'tablet' => '',
        'mobile' => '',
    ],
]
```

### 8.2 Section payload

В `section` живёт:

1. id блока;
2. классы секции;
3. section attrs;
4. background variables;
5. aria-safe defaults.

### 8.3 Stage payload

`stage` должен содержать per-breakpoint config:

1. `width`
2. `minHeight`
3. `bleedX`
4. computed stage class / selectors

### 8.4 Element payload

Каждый элемент в payload должен содержать:

1. `id`
2. `type`
3. `zIndex`
4. `hidden`
5. `htmlTag`
6. `content`
7. `attrs`
8. `classes`
9. `styleVars`
10. `responsiveCss`
11. `children` — только для container nodes

Это позволяет `render.php` остаться thin-view слоем, а не вторым normalizer-ом.

---

## 9. Type-specific render payload rules

### 9.1 `text`

Builder должен отдавать:

1. plain text value;
2. typography vars;
3. text transform/style/decoration;
4. alignment vars.

### 9.2 `image` / `svg`

Builder должен отдавать:

1. normalized asset src;
2. alt;
3. object-fit;
4. radius;
5. type-safe markup hint: `img`.

### 9.3 `video`

Builder должен отдавать:

1. src;
2. poster;
3. playback flags;
4. object-fit;
5. radius;
6. safe boolean attrs.

### 9.4 `button`

Builder должен отдавать:

1. text;
2. normalized link href/target;
3. button state palette;
4. gradient CSS vars if enabled;
5. disabled state.

### 9.5 `shape`

Builder должен отдавать:

1. shape kind;
2. fill / gradient fill;
3. opacity;
4. blur;
5. glass/backdrop blur;
6. radius.

### 9.6 `icon`

Builder должен отдавать:

1. icon ref/class;
2. color;
3. size;
4. icon family hint if needed.

### 9.7 `divider`

Builder должен отдавать:

1. orientation;
2. thickness;
3. color;
4. radius.

### 9.8 `container`

Builder должен отдавать:

1. background;
2. radius;
3. padding;
4. clip mode;
5. layout mode `absolute|flex`;
6. flex settings if enabled;
7. nested child payloads.

---

## 10. Render helpers and files

### 10.1 Recommended classes

Для v1 фиксируем такую раскладку:

1. `NordicblocksDesignBlockContractNormalizer`
2. `NordicblocksDesignBlockRenderPayloadBuilder`
3. `NordicblocksDesignBlockCssBuilder`
4. `NordicblocksDesignBlockElementRenderer`

### 10.2 Recommended file layout

1. `system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php`
2. `system/controllers/nordicblocks/libs/DesignBlockRenderPayloadBuilder.php`
3. `system/controllers/nordicblocks/libs/DesignBlockCssBuilder.php`
4. `system/controllers/nordicblocks/libs/DesignBlockElementRenderer.php`
5. `system/controllers/nordicblocks/blocks/design_block/render.php`

### 10.3 `render.php` responsibility

`blocks/design_block/render.php` должен:

1. получить уже готовый payload;
2. пройти по element tree;
3. собрать HTML;
4. вернуть `html + inline_css` через обычный NordicBlocks contract.

`render.php` не должен заниматься нормализацией JSON или вычислением geometry.

---

## 11. Preview/live parity rule

И preview, и live обязаны работать так:

1. `normalize -> build payload -> render`.

Запрещённые схемы:

1. preview uses donor-like client export, live uses PHP;
2. preview uses raw contract, live uses normalized contract;
3. preview имеет особые CSS hacks, которых нет в live.

---

## 12. Definition of Done

Runtime contract v1 считается принятым, если одновременно выполнены условия:

1. `design_block` проходит через dedicated PHP normalizer;
2. normalizer возвращает полный Block Contract v3 shape;
3. render payload имеет формально описанную стабильную структуру;
4. `render.php` остаётся thin-view слоем;
5. preview и live используют один и тот же payload builder;
6. freeform geometry и style rules не растворяются в editor-only JS logic.