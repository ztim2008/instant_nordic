# NordicBlocks Universal Visual Inspector Implementation Plan

Дата: 2026-04-19

## 1. Цель плана

Этот план переводит product decision в рабочий порядок внедрения.

Он исходит из жёсткого правила:

1. universal visual inspector является primary editor path;
2. contract-first controls являются нормой;
3. visual CSS overlay остаётся только дополнительным инструментом;
4. отдельные block-specific inspector решения не расширяются.

## 2. Scope первой фазы

Первая фаза должна закрыть единый visual editing loop для pilot block types:

1. `hero`;
2. `faq`;
3. `content_feed` или `headline_feed`;
4. `swiss_grid` или `catalog_browser`.

Минимальный entity set для pilot:

1. `section`;
2. `title`;
3. `subtitle`;
4. `body`;
5. `media` / `mediaSurface`;
6. `primaryButton`;
7. `itemSurface`;
8. `itemTitle`;
9. `itemText`;
10. `meta`.

## 3. Delivery rule

Каждый день должен завершаться проверяемым продуктовым результатом, а не только кодом.

## 4. План по дням

### Day 0. Freeze decision and guardrails

Задача:

1. зафиксировать universal inspector как primary path;
2. перевести overlay в secondary role;
3. запретить новые block-specific inspector additions по умолчанию.

Изменения в docs:

1. этот документ;
2. go/no-go blueprint;
3. worklog;
4. при необходимости ссылка из existing unified inspector docs.

Результат дня:

1. команда больше не спорит о направлении;
2. любые новые задачи проверяются against blueprint.

### Day 1. Universal canvas selection layer

Задача:

1. вынести и стабилизировать общий canvas entity picking flow;
2. сделать его block-agnostic для любого SSR markup с `data-nb-entity`.

Основные файлы:

1. `system/controllers/nordicblocks/backend/actions/block_canvas.php`
2. `packages/nordicblocks/package/system/controllers/nordicblocks/backend/actions/block_canvas.php`
3. `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php`
4. `packages/nordicblocks/package/templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php`

Что делаем:

1. нормализуем hover outline и active highlight;
2. стабилизируем click-to-select entity;
3. добавляем единый canvas event contract;
4. синхронизируем canvas selected entity с shell без hero-specific glue.

Критерий готовности:

1. на pilot blocks клик по title/body/button/media выбирает сущность одинаковым способом.

### Day 2. Inspector state and focus routing

Задача:

1. сделать selectedEntity canonical частью inspector runtime;
2. чтобы UI открывал правильные панельные группы после canvas click.

Основные файлы:

1. `system/controllers/nordicblocks/libs/InspectorStateBuilder.php`
2. `system/controllers/nordicblocks/backend/actions/block_editor_state.php`
3. `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php`
4. package mirror equivalents.

Что делаем:

1. убираем локальную привязку к отдельным блокам;
2. приклеиваем selected entity, active tab, panel focus и entity labels к общему state;
3. добавляем predictable empty state, если сущность не имеет editable controls.

Критерий готовности:

1. после клика на canvas inspector переводит фокус в ожидаемую панель без ручного поиска пользователя.

### Day 3. Registry/capability cleanup

Задача:

1. дожать shared registry до реального universal режима;
2. убрать оставшиеся implicit block-specific assumptions.

Основные файлы:

1. `system/controllers/nordicblocks/libs/InspectorDefinitionRegistry.php`
2. `system/controllers/nordicblocks/libs/InspectorRegistryBuilder.php`
3. block manifests первой волны;
4. package mirror equivalents.

Что делаем:

1. выравниваем canonical entity labels;
2. проверяем capability to panel coverage;
3. запрещаем panel keys без общего registry entry;
4. убеждаемся, что блоки расширяют registry, а не обходят его.

Критерий готовности:

1. один и тот же entity key ведёт к одному и тому же типу controls на разных блоках.

### Day 4. Russian unified inspector UI

Задача:

1. вычистить пользовательский inspector UX до единого русского интерфейса;
2. убрать остатки технических и англоязычных названий из main flow.

Основные файлы:

1. `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php`
2. shared renderer partials, которые рисуют panels/chips/accordion/control labels;
3. package mirror equivalents.

Что делаем:

1. единые русские названия вкладок, сущностей и действий;
2. понятные пустые состояния;
3. единый copywriting style для inspector;
4. явные labels для entities с override only where needed.

Критерий готовности:

1. inspector можно показать пользователю без объяснения англоязычных служебных терминов.

### Day 5. Pilot block rollout

Задача:

1. подключить pilot block types к одному visual loop;
2. выровнять markup gaps по `data-nb-entity`.

Основные файлы:

1. `system/controllers/nordicblocks/blocks/hero/render.php`
2. `system/controllers/nordicblocks/blocks/faq/render.php`
3. `system/controllers/nordicblocks/blocks/content_feed/render.php`
4. `system/controllers/nordicblocks/blocks/headline_feed/render.php`
5. `system/controllers/nordicblocks/blocks/swiss_grid/render.php`
6. `system/controllers/nordicblocks/blocks/catalog_browser/render.php`
7. package mirror equivalents.

Что делаем:

1. добавляем или выравниваем missing entity markers;
2. проверяем, что сущности реально соответствуют редактируемым узлам;
3. фиксируем mismatches между markup и registry.

Критерий готовности:

1. 4 pilot block types проходят реальный click-to-edit smoke без специальных обходов.

Статус 2026-04-19:

1. выполнено для `hero`, `faq`, `content_feed`, `swiss_grid` и `catalog_browser`;
2. реальный rollout gap оказался не в registry, а в SSR markup: `catalog_browser` требовал canonical `data-nb-entity` для toolbar/filter/card/modal/empty-state сущностей, а `swiss_grid` — явный `meta` coverage для auxiliary meta.

### Day 6. Contract-first controls pass

Задача:

1. закрыть базовые edit flows через существующие controls;
2. отделить реальные gaps от ложных ожиданий overlay.

Основные файлы:

1. shared control renderers;
2. relevant manifest/schema definitions for pilot blocks;
3. `system/controllers/nordicblocks/libs/InspectorDefinitionRegistry.php`;
4. package mirror equivalents.

Что делаем:

1. title/body/button/media/item controls доводим как primary UX;
2. documentируем unresolved gaps;
3. overlay оставляем только там, где реально нет contract control.

Критерий готовности:

1. минимум 80% pilot editing flows не требуют raw CSS или специальных hacks.

Статус 2026-04-19:

1. старт Day 6 подтверждён новым checkpoint `snapshot/20260419-211558`;
2. первый подтверждённый contract gap закрыт на pilot `faq`: shared `typography-text-panel` уже умел `eyebrow`, но manifest не включал `eyebrowTypography`, из-за чего для реальной сущности `eyebrow` в design flow не было contract-first панели.
3. второй подтверждённый gap закрыт на pilot `content_feed` и `swiss_grid`: shared shell и contract vocabulary уже знали `itemLink`, но normalizer/shared `itemTypography` не доводили CTA карточки до полноценной typography flow; теперь `itemLink` проходит через contract, shared design renderer и SSR runtime CSS variables.
4. live smoke сразу обнаружил residual bug в `faq`: после включения `eyebrowTypography` generic collection profile не содержал `eyebrow` defaults и design panel падала; shared profile доведён, после чего `faq`/`content_feed`/`swiss_grid` подтвердили реальный inspector flow на canvas + design panels.
5. следующий routing gap закрыт на pilot `catalog_browser`: card CTA уже использовала existing shared `buttonsStyle` runtime path, но manifest не связывал `cardPrimaryAction` с group `buttons`; после manifest routing selection по `CTA карточки` открывает shared panel `Действия` без отдельного renderer.
6. следующий contract-first routing gap закрыт на `catalog_browser` filters/meta flow: toolbar и filter entities уже использовали shared `meta` typography tokens в runtime, но manifest не связывал `toolbar/searchField/categoryFilter/priceFilter/sortControl/activeFilters/cardBadge` с group `meta`; после manifest routing selection по этим сущностям открывает shared panel `Мета и фильтры` без нового control renderer.
7. следующий уже не routing-only, а полноценный contract/runtime gap закрыт на `catalog_browser` toolbar-controls flow: shared registry/manifest/shell/normalizer/SSR/CSS теперь честно разделяют `toolbarSurface`, `toolbarControlsSurface` и `cardPriceTypography`, а live smoke блока `#124` подтвердил, что `Панель фильтров` открывает отдельную surface panel, `Поиск` и `Сортировка` одновременно получают shared `Мета и фильтры` + `Поля фильтров`, текущая цена карточки открывает отдельную panel `Текущая цена`, а зачёркнутая старая цена остаётся в `meta` path.

### Day 7. Formal go / no-go review

Задача:

1. провести жёсткую приёмку против blueprint;
2. решить, идём дальше или закрываем направление.

Что проверяем:

1. есть ли один inspector UX;
2. работает ли universal canvas selection;
3. нужен ли новый block-specific UI хоть для одного pilot block;
4. сколько pilot flows реально закрыты contract-first;
5. осталось ли ощущение «один редактор», а не набор исключений.

Результат:

1. `GO`: universal inspector становится default roadmap;
2. `NO-GO`: инициативу останавливаем и не размазываем дальше ресурс.

Статус 2026-04-19:

1. verdict: `GO`.
2. критерий universal canvas picking подтверждён manual live smoke по pilot blocks `#121 hero`, `#122 faq`, `#123 content_feed`, `#124 catalog_browser`, `#125 swiss_grid`: canvas selection, inspector focus и reload-safe flow остаются рабочими после Day 6 contract-first pass.
3. критерий одного inspector UI подтверждён: rollout продолжает жить в едином shell `editor_hero_v2`, а не в новых private block templates; новые flows были доведены через shared registry, manifest, normalizer, renderers и SSR runtime.
4. критерий entity-first selection подтверждён на pilot coverage шире минимального порога: помимо `title/subtitle/body/media/button` живые editor flows теперь закрывают `eyebrow`, `itemLink`, `meta`, `toolbar`, `toolbarControls`, `cardPrimaryAction`, `cardPrice`, `mediaModal`, `emptyState`.
5. критерий contract-first coverage подтверждён выше go-порога: базовые pilot flows не требуют raw CSS и не зависят от selector builder; overlay остаётся secondary layer, а не главным UX path.
6. блокирующие no-go сигналы не проявились: второй и третий pilot blocks не потребовали нового shell, entity picking не деградировал в selector ranking, а `catalog_browser` gaps закрывались через shared vocabulary вместо block-specific инспектора.
7. отдельный public/widget smoke для последнего `catalog_browser` toolbar/control/price patch завершён: блок `#124` был временно размещён штатным `block_place` в `pos_38`, публичная главная отрендерила toolbar/input/current price/old price без PHP warnings, после чего временный widget bind был удалён и frontend возвращён в baseline.
8. остающиеся риски не блокируют `GO`: часть legacy/overlay gaps сознательно остаётся вне Phase 1, а package mirror для live CSS runtime asset в текущей структуре отсутствует как отдельный дистрибутивный файл.
9. решение Phase 1: universal visual inspector принимается как default путь для новых NordicBlocks block types; rollback baseline для этой приёмки остаётся `snapshot/20260419-211558` + DB backup `backups/db/builders-20260419-211557.sql.gz`.

## 5. Объём кода

Ожидаемый объём первой фазы:

1. примерно 700-1200 строк целевых изменений для core runtime и shell;
2. плюс markup alignment по pilot blocks;
3. плюс package mirror sync;
4. плюс docs/worklog.

Это не giant rewrite. Главная сложность в выравнивании contracts, а не в количестве строк.

## 6. Обязательные checkpoints

Checkpoint нужен перед переходом к каждому рискованному подэтапу, который затрагивает editor runtime и массовые render-файлы.

Минимум:

1. перед Day 1;
2. перед Day 5 rollout на pilot blocks;
3. перед formal go/no-go review, если между Day 5 и Day 7 были дополнительные runtime правки.

Если затрагивается SQL или persistence слой, отдельно обязателен DB backup.

## 7. Что не входит в первую фазу

Чтобы не расползтись, в первую фазу сознательно не входят:

1. raw CSS editor уровня so-css;
2. selector discovery и specificity engine;
3. массовый rollout на все block types;
4. item-level deep editing для всех repeaters без pilot proof;
5. полное удаление overlay layer.

## 8. Следующий шаг после успешного GO

Только после подтверждённого GO допустимо идти дальше:

1. расширять pilot coverage;
2. делать item-level inspector для repeaters глубже;
3. добавлять selector-safe overlay как fallback panel для ограниченных сущностей;
4. формально запрещать новые block-specific inspector paths в code review.