# NordicBlocks — Slider Inspector Vocabulary V1

Дата: 2026-04-20

## 1. Назначение документа

Этот документ фиксирует, какой shared vocabulary должен появиться в inspector до старта кода первого slider block.

Его задача:

1. не дать `cards_slider` утащить block-private исключения в shared shell;
2. заранее зафиксировать canonical entities и control groups slider family;
3. дать опорный мост между family spec и будущей implementation работой.

## 2. Почему этот слой нужен до scaffold

Без зафиксированного slider vocabulary первый block type почти неизбежно начнёт расти через ручные if-ветки в shell.

Это противоречит целям V2, потому что:

1. slider family должна войти в shared system, а не рядом с ней;
2. navigation/pagination/track mechanics повторятся во всех трёх slider blocks;
3. один раз правильно введённый vocabulary дешевле, чем потом чинить 3 разных исключения.

## 3. Canonical slider entities

Для slider family в shared inspector должны быть доступны следующие entity keys:

1. `viewport`
2. `track`
3. `slide`
4. `navigation`
5. `prevButton`
6. `nextButton`
7. `pagination`
8. `progress`
9. `slideSurface`
10. `slideMedia`
11. `slideEyebrow`
12. `slideTitle`
13. `slideText`
14. `slideMeta`
15. `slidePrimaryAction`
16. `slideSecondaryAction`

Правило:

1. эти entity keys считаются shared vocabulary;
2. конкретный slider block может использовать только часть из них;
3. но не должен придумывать альтернативные названия для тех же сущностей.

## 4. Требуемые control groups

До старта `cards_slider` shared inspector должен понимать такие control groups.

### 4.1 Content controls

1. `slider-slides` — repeater ручных slides;
2. `slider-header` — content controls секции;
3. `slider-actions` — section actions;
4. `slider-visibility` — visibility toggles для navigation/pagination/media/meta/text/actions.

### 4.2 Design controls

1. `slider-surface-design`
2. `slider-media-design`
3. `slider-navigation-design`
4. `slider-pagination-design`
5. `slider-progress-design`
6. `slider-item-typography`

### 4.3 Layout controls

1. `slider-layout`
2. `slider-motion`
3. `slider-navigation-layout`

### 4.4 Data controls

1. `slider-data-source`
2. `slider-data-query`
3. `slider-data-visibility`

## 5. Требуемые capability flags

Для slider family нужны явные capability flags, чтобы shell не гадал по block type name.

Минимальный набор:

1. `hasSlides`
2. `hasSliderNavigation`
3. `hasSliderPagination`
4. `hasSliderProgress`
5. `hasMobileSwipe`
6. `hasAutoplay`
7. `hasLoop`
8. `hasContentListSource`

## 6. Требуемые family profile defaults

Для slider family shared shell должен уметь принимать не только block kind, но и family profile со следующими defaults:

1. full-width policy как default;
2. desktop/mobile slides per view defaults;
3. desktop/mobile gap defaults;
4. navigation style defaults;
5. pagination style defaults;
6. motion defaults;
7. slide typography defaults.

## 7. Selection and canvas rules

До старта кода нужно зафиксировать правила canvas selection для slider blocks:

1. кликабельные DOM nodes должны соответствовать canonical slider entities;
2. navigation и pagination не должны сваливаться в generic `meta` или `controls`;
3. slide CTA nodes должны быть отдельными truthful entities, а не скрываться внутри заголовка;
4. shell должен фильтровать доступные entity chips по фактическим canvas markers, как и в остальных families.

## 8. Что не нужно делать на этом этапе

До первого slider block не нужно:

1. делать отдельный private shell для slider family;
2. вводить selector-based pseudo-entities вместо truthful DOM markers;
3. строить отдельный animation builder;
4. открывать block-specific tab system.

## 9. Готовность vocabulary к открытию первого блока

Shared slider vocabulary считается достаточно готовым для открытия `cards_slider`, когда:

1. canonical entity keys для slider family зафиксированы;
2. control groups для content/design/layout/data определены;
3. capability flags согласованы;
4. family profile defaults описаны;
5. canvas selection rules зафиксированы до scaffold.