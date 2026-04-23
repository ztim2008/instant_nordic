# NordicBlocks Internal Block Catalog Card — Manifest Fields

Дата: 2026-04-23

## 1. Статус документа

Этот документ раскладывает структуру карточки блока в будущем internal catalog по manifest-полям.

Важно:

1. это не финальный schema contract;
2. это не backend implementation;
3. это продуктово-архитектурный mapping, чтобы команда заранее понимала состав catalog card.

## 2. Зачем нужен manifest mapping

Catalog card не должна собираться из случайных ad-hoc полей.

Если карточка заранее опирается на понятный manifest-level shape,
дальше проще масштабировать:

1. curated registry;
2. preview grid;
3. install state;
4. future filtering and compatibility.

## 3. Базовый принцип

У каждой installable catalog entry должна быть product-facing карточка,
которая собирается из небольшой и предсказуемой группы manifest fields.

В MVP лучше держать manifest компактным,
чем пытаться сразу описать все возможные будущие метаданные.

## 4. Рекомендуемая структура card manifest v1

### 4.1 Identity

Обязательные поля:

1. `slug`
2. `title`
3. `family`
4. `category`

Назначение:

1. `slug` — технический устойчивый идентификатор entry;
2. `title` — главное имя на карточке;
3. `family` — семейство блока на уровне платформы;
4. `category` — пользовательская browse-категория в каталоге.

### 4.2 Product role

Обязательные или strongly recommended поля:

1. `subtitle`
2. `summary`
3. `tags`

Назначение:

1. `subtitle` — короткая роль блока на карточке;
2. `summary` — 1–2 строки о назначении;
3. `tags` — быстрые словесные маркеры для будущего browse/filter UX.

### 4.3 Preview

Обязательные поля:

1. `previewImage`

Опциональные поля:

1. `previewImageAlt`
2. `previewMode`
3. `demoUrl`

Назначение:

1. `previewImage` — основной visual asset карточки;
2. `previewImageAlt` — доступность и fallback description;
3. `previewMode` — указание на характер preview, если позже появятся вариации;
4. `demoUrl` — будущий переход в demo/live preview, если команда это включит.

### 4.4 Distribution

Обязательные поля:

1. `availability`
2. `distributionModel`

Рекомендуемые поля:

1. `version`
2. `requiresVersion`

Назначение:

1. `availability` — например `free`, `coming_soon`, `unavailable`;
2. `distributionModel` — в MVP фиксируется как `local_install`;
3. `version` — локальная версия curated entry;
4. `requiresVersion` — совместимость с версией компонента на будущее.

### 4.5 Editor path

Обязательные поля:

1. `editorMode`
2. `installTarget`

Назначение:

1. `editorMode` — должен явно указывать, что entry открывается через managed editor path;
2. `installTarget` — во что локально превращается запись после установки.

### 4.6 Curation metadata

Рекомендуемые поля:

1. `status`
2. `featured`
3. `sortOrder`

Назначение:

1. `status` — внутренний curated lifecycle state;
2. `featured` — приоритетное положение на первом экране;
3. `sortOrder` — стабильный ручной порядок карточек.

## 5. Минимальный card manifest для MVP

Если команда захочет стартовать совсем узко,
минимальный обязательный набор для первой карточки такой:

1. `slug`
2. `title`
3. `family`
4. `category`
5. `subtitle`
6. `summary`
7. `previewImage`
8. `availability`
9. `distributionModel`
10. `editorMode`
11. `installTarget`

## 6. Mapping manifest -> UI card

Предлагаемый mapping:

1. hero image карточки <- `previewImage`
2. title карточки <- `title`
3. secondary line <- `subtitle`
4. short description <- `summary`
5. badge family <- `family`
6. badge category <- `category`
7. badge availability <- `availability`
8. install CTA logic <- `installTarget` + install state runtime layer

## 7. Жёсткие правила для MVP

Для первой волны фиксируются ограничения:

1. не тащить в manifest сложную pricing-модель;
2. не строить contract вокруг marketplace-author metadata;
3. не делать preview зависимым от удалённого runtime;
4. editor path должен быть явным и предсказуемым;
5. поля карточки должны быть пригодны для curated manual maintenance.

## 8. Пример shape без жёсткой привязки к коду

Продуктово карточку можно мыслить как такой набор:

1. кто это — `slug`, `title`;
2. к какой family относится — `family`, `category`;
3. зачем нужен — `subtitle`, `summary`, `tags`;
4. как выглядит — `previewImage`;
5. можно ли поставить — `availability`, `distributionModel`;
6. куда попадёт после установки — `editorMode`, `installTarget`.

## 9. Decision summary

На текущий момент фиксируется:

1. карточка каталога должна иметь manifest-driven shape;
2. MVP держится на компактном наборе полей, а не на расширенном marketplace schema;
3. install/edit semantics должны быть видны уже на уровне manifest metadata.

## 10. Связанные документы

1. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-BROWSER-MINI-SPEC.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-BROWSER-MINI-SPEC.md)
2. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md)
3. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md)
4. [docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md](docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md)
