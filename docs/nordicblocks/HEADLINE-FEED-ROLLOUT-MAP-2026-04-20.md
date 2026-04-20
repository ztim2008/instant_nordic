# Headline Feed Rollout Map — 2026-04-20

## Цель

Этот документ фиксирует discovery-only карту для `headline_feed` перед любыми кодовыми правками второй волны `Universal Visual Inspector`.

Задача карты: заранее отделить уже готовый shared contour от реальных truthful gaps, чтобы implementation не превратился в новый block-specific путь.

## Почему выбран именно headline_feed

`headline_feed` подходит под критерии [docs/nordicblocks/START-2026-04-20.md](docs/nordicblocks/START-2026-04-20.md):

1. даёт новый сценарий `lead + rail/grid`, которого не было в pilot blocks;
2. уже живёт в manifest-first architecture и не требует отдельного inspector shell;
3. объём gaps локализован и выглядит реалистичным для contract-first прохода в рамках одного рабочего дня.

`category_cards` пока отложен, потому что по смыслу ближе к уже покрытому `content_feed` и слабее расширяет доказанный universal path.

## Что уже готово в shared contour

### 1. Block contract и manifest

У `headline_feed` уже есть готовые manifest/schema/runtime файлы:

1. `system/controllers/nordicblocks/blocks/headline_feed/manifest.php`
2. `system/controllers/nordicblocks/blocks/headline_feed/schema.json`
3. `system/controllers/nordicblocks/blocks/headline_feed/render.php`
4. package mirror тем же составом

Manifest уже объявляет canonical entities:

1. `section`
2. `title`
3. `subtitle`
4. `primaryButton`
5. `items`
6. `itemSurface`
7. `media`
8. `itemTitle`
9. `itemText`
10. `itemLink`
11. `meta`

### 2. Shared editor shell уже знает headline_feed

В shared shell уже есть отдельный UI profile для `headline_feed`:

1. theme options;
2. layout presets `split / stack / cover`;
3. title/subtitle/meta defaults;
4. media defaults;
5. item surface defaults;
6. layout control `headline-feed`.

То есть блок уже не требует нового shell или block-specific tabs.

### 3. Shared renderers и normalizer уже подключены

На discovery подтверждено:

1. `BlockContractNormalizer` уже поддерживает `headline_feed`;
2. shared design renderer уже умеет route для `itemTypography` и `itemLink`;
3. shared layout renderer уже знает `headline_feed` preset control;
4. runtime CSS уже содержит shared feed CTA selectors через `.nb-content-feed__item-link` и `--nb-feed-item-link-*` variables.

Иными словами, стек уже наполовину готов: это не новый block family с нуля.

## Подтверждённые truthful gaps

### 1. Manifest объявляет itemLink, но SSR markup не поднимает эту entity

Это главный уже подтверждённый gap.

Сейчас в `headline_feed` SSR есть markers для:

1. `section`
2. `title`
3. `subtitle`
4. `primaryButton`
5. `items`
6. `itemSurface`
7. `media`
8. `itemTitle`
9. `itemText`
10. `meta`

Но отдельного узла с `data-nb-entity="itemLink"` в markup нет.

Следствие:

1. canvas не сможет честно выбрать `itemLink` как отдельную canonical entity;
2. manifest и SSR на этом месте расходятся;
3. выбор `CTA карточки` в inspector пока будет phantom/alias path, а не truthful DOM-backed path.

### 2. UI profile headline_feed не даёт link defaults для itemTypography

Shared design renderer уже умеет рендерить CTA typography для `itemLink`, но только если block profile даёт `itemTypography.link` defaults.

У `headline_feed` сейчас есть:

1. `itemTypography.title`
2. `itemTypography.text`

Но нет:

1. `itemTypography.link`

Следствие:

1. даже после появления честного canvas marker для `itemLink` shared panel не сможет показать полноценные CTA typography controls как у `content_feed`/`swiss_grid`;
2. rollout останется неполным без очень маленького, но обязательного profile pass.

### 3. Render не эмитит itemLink runtime variables

Shared CSS уже знает `--nb-feed-item-link-color`, `--nb-feed-item-link-size`, `--nb-feed-item-link-weight`, `--nb-feed-item-link-line-height`, `--nb-feed-item-link-letter-spacing`.

Но в `system/controllers/nordicblocks/blocks/headline_feed/render.php` сейчас нет эмиссии этих CSS variables.

Следствие:

1. даже при появлении inspector controls и DOM marker runtime roundtrip для CTA останется незавершённым;
2. это типичный shared-contract gap уровня `render -> CSS vars`, а не повод делать block-specific editor logic.

### 4. У items нет отдельного linkLabel

В schema/repeater у карточек есть `url`, но нет отдельного поля `linkLabel`.

Это создаёт product/contract question до implementation:

1. либо `itemLink` должен стать derived CTA с фиксированным/системным label;
2. либо `itemLink` не должен существовать как отдельная canonical entity в `headline_feed`, если по продуктовой логике ссылка живёт только в заголовке.

Этот вопрос нужно решить явно до кода. Его нельзя замаскировать псевдо-marker'ом на `<a>` внутри `itemTitle`, если продукт ожидает самостоятельную сущность `CTA карточки`.

### 5. Нет готовых live instances для smoke

В текущей базе нет готовых block instances ни для `headline_feed`, ни для `category_cards`.

Следствие:

1. editor smoke после implementation нельзя будет сделать через уже существующий `block_edit/<id>`;
2. понадобится временно создать block instance;
3. если дойдём до public/widget smoke, нужно будет использовать тот же безопасный cleanup discipline, что и для `catalog_browser`.

## Что shared system уже позволяет переиспользовать без нового shell

Если implementation пойдёт дальше, повторно использовать нужно именно shared parts:

1. existing collection profile flow в `editor_hero_v2.tpl.php`;
2. existing shared `itemTypography` renderer;
3. existing shared feed CTA CSS selectors;
4. existing contract normalization path для `headline_feed`;
5. existing package mirror parity discipline.

Запрещённый путь:

1. отдельный `headline_feed` inspector template;
2. новый private editor sidebar;
3. selector-builder вместо truthful entity routing.

## Предварительный implementation order

Если идти дальше в код, порядок должен быть таким:

1. сделать checkpoint перед изменениями, потому что дальше уже меняются editor flow, render и runtime contract;
2. принять явное решение по семантике `itemLink`: это отдельный CTA node или entity должна быть снята из manifest;
3. довести SSR markup до truthful state для принятого решения;
4. довести `headline_feed` UI profile до честного `itemTypography.link` support;
5. довести render до эмиссии `--nb-feed-item-link-*` variables, если `itemLink` остаётся в contract;
6. проверить live/package parity;
7. создать временный block instance для editor smoke;
8. только после editor smoke решать, нужен ли отдельный public/widget smoke.

## Итог discovery

`headline_feed` подтверждён как подходящий второй-wave кандидат.

Причина простая:

1. блок уже сидит на shared foundation;
2. найденные gaps конкретны и локальны;
3. ни один из найденных gaps не требует нового private inspector path.

Это означает, что следующий шаг должен быть не новым исследованием, а controlled implementation с checkpoint перед стартом.