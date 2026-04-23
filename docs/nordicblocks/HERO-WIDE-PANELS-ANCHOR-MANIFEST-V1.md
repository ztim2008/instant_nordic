# NordicBlocks Hero: Wide Panels — Anchor Manifest V1

Дата: 2026-04-23

## 1. Статус документа

Этот документ фиксирует рекомендуемый manifest shape для первого anchor block каталога:

`Hero: Wide Panels`

Важно:

1. это не production schema contract;
2. это не готовый JSON-файл;
3. это продуктово-архитектурный эталон того, как должен выглядеть первый curated catalog entry.

## 2. Зачем нужен отдельный anchor manifest

Общий документ про card fields задаёт рамку,
но для первого pilot block нужен более конкретный эталон.

Он нужен, чтобы заранее проверить:

1. хватает ли manifest-level metadata для понятной карточки;
2. нет ли скрытых ad-hoc полей;
3. достаточно ли этого shape для install -> open editor сценария.

## 3. Identity shape

Для `Hero: Wide Panels` фиксируется такой identity-level набор:

1. `slug`: `hero_panels_wide`
2. `title`: `Hero: Wide Panels`
3. `family`: `hero`
4. `category`: `Hero`

Причина выбора `slug`:

он уже логически совпадает с существующим scaffold-managed вектором внутри hero family,
что делает pilot более приземлённым и менее выдуманным.

## 4. Product role shape

Рекомендуемый product-facing набор:

1. `subtitle`: `Широкий hero с акцентом на CTA и быстрый старт лендинга`
2. `summary`: `Готовая стартовая секция для сервисов, малого бизнеса и offer-first страниц. После установки редактируется через design block editor.`
3. `tags`: `hero`, `cta`, `business`, `landing`, `panels`

## 5. Preview shape

Для карточки anchor block рекомендуется:

1. `previewImage`: stable preview asset для card grid;
2. `previewImageAlt`: `Превью блока Hero: Wide Panels`;
3. `previewMode`: `static_cover`.

Опционально на будущее:

1. `demoUrl`, если позже появится отдельный demo-step.

## 6. Distribution shape

Для pilot entry фиксируется:

1. `availability`: `free`
2. `distributionModel`: `local_install`
3. `version`: `1.0.0-pilot`

Опционально на будущее:

1. `requiresVersion`

## 7. Editor path shape

Критический смысловой слой для anchor block:

1. `editorMode`: `design_block_managed`
2. `installTarget`: `managed_block_entry`

Это нужно, чтобы уже на manifest-уровне было видно:

1. блок не ведёт в отдельный private inspector;
2. после установки он должен открываться по знакомому editor path;
3. ownership после install переходит в обычный managed workflow.

## 8. Curation metadata

Для первого anchor block рекомендуется:

1. `status`: `pilot_ready`
2. `featured`: `true`
3. `sortOrder`: `10`

Причина:

первый anchor block должен визуально и продуктово стоять первым в grid.

## 9. Canonical entry shape

Без привязки к конкретному коду anchor entry можно мыслить так:

1. кто это:
   `hero_panels_wide` / `Hero: Wide Panels`
2. где живёт:
   family `hero`, category `Hero`
3. зачем нужен:
   понятный CTA-first hero для массового сценария
4. как выглядит:
   стабильный preview card image
5. как распространяется:
   free + local_install
6. как открывается:
   design_block_managed

## 10. Почему именно этот shape подходит для первого пилота

Этот anchor manifest достаточно мал,
чтобы не перегрузить MVP,
но уже достаточно полон,
чтобы поддержать:

1. понятную карточку каталога;
2. явный install state;
3. предсказуемый переход в editor;
4. ручное curated maintenance.

## 11. Decision summary

На текущий момент фиксируется:

1. первый anchor entry строится вокруг `slug = hero_panels_wide`;
2. его editor semantics должны быть явно managed/design-block-first;
3. manifest должен быть компактным, но достаточно богатым для card UX и install semantics.

## 12. Связанные документы

1. [docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md](docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md)
2. [docs/nordicblocks/HERO-FAMILY-V1.md](docs/nordicblocks/HERO-FAMILY-V1.md)
3. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md)
4. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md)
5. [docs/nordicblocks/FIRST-CATALOG-INSTALL-LOOP-ACCEPTANCE-CRITERIA.md](docs/nordicblocks/FIRST-CATALOG-INSTALL-LOOP-ACCEPTANCE-CRITERIA.md)
