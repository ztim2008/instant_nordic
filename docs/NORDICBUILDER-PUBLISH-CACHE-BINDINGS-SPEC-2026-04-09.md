# NordicBuilder: Publish + Cache + Bindings (SEO-first) — 2026-04-09

## Цель

Сделать коммерческий `nordicbuilder` (ZIP-пакет), который:

- **не ломает** существующие URL/типы контента InstantCMS;
- даёт **быстрый и SEO-дружелюбный** фронтенд (SSR HTML);
- обеспечивает **parity preview/live** (один контракт и один runtime путь);
- поддерживает **блоки с данными** из Instant (новости, меню, галереи, списки контента).

## Канон (Plan A / Variant B)

1) InstantCMS владеет маршрутом и контекстом страницы (controller/action, ctype, item, category, user).

2) NordicBuilder владеет:

- PageDocument (сборка из секций/блоков),
- Design Tokens (CSS vars),
- Bindings (правила применения макета),
- SSR publish и кеш SSR HTML.

3) Builder управляет только **`content_body`**:

- на странице стоит один runtime-виджет `nordicbuilder_render`,
- он вставляет SSR HTML в `.nb-runtime` и применяет vars.

Никаких takeover/hybrid как основы.

## Главная проблема, которую решаем

У пользователей уже есть свои URL и типы контента. Значит нельзя “публиковать один HTML на страницу page_key” для всех случаев.

Для SEO и скорости нужен SSR HTML **по контексту**:

- отдельный HTML для записи (item);
- отдельный HTML для категории (category) и страницы пагинации;
- отдельный HTML для набора фильтров/датасета (если используется);
- отдельный HTML для standalone page (homepage, landing).

## Термины

- **Binding** — правило, какое builder-оформление применять к какому контексту InstantCMS.
- **Runtime Context** — нормализованный набор параметров запроса (что за страница, какой тип, какая запись/категория и т.п.).
- **Render Cache Entry** — опубликованный SSR HTML + meta + style vars для конкретного binding+context.

## Invariants (обязательные свойства)

1) Единственный live output: `nordicbuilder_render` → published SSR HTML.
2) SSR HTML всегда обёрнут в `.nb-runtime` (namespace для CSS).
3) Кеш строится **только для публичного гостевого режима** (без персонализации).
4) Любое изменение контента должно приводить к инвалидации релевантных кешей.
5) Если кеш не найден — система должна уметь:
   - безопасно отрендерить SSR “на лету” (и положить в кеш),
   - или показать корректный fallback (без белого экрана).

6) Решение по устройствам (принято): **один SSR HTML на все устройства**.
  - адаптивность обеспечивается CSS внутри `.nb-runtime`;
  - разные `device_type` не создают разные кеш-ключи;
  - скрытие/перестройка для mobile делается стилями, а не отдельным HTML.

## Runtime Context (канонический формат)

Минимальный формат (пример):

- `route.controller` — например `content`
- `route.action` — например `view`, `category`
- `ctype.name` — например `news`
- `item.id` — например `123` (для view)
- `category.id` — например `55` (для category)
- `page` — номер страницы пагинации (для списков)
- `dataset` — ключ датасета (если применимо)
- `lang` — язык/префикс (если мультиязычность)
- `device` — `desktop|tablet|mobile` (опционально; если SSR реально различается)

Важно: **не использовать сырую строку URL как истину**. URL маски допускаются только как экспертная прослойка в bindings, а нормальный матчинг должен опираться на route-поля.

## Cache Key (как различаем кеши)

Кеш должен быть уникален на сочетание:

- `binding_key` (какой макет выбран)
- `context_key` (какой контент/страница)
- `device` (не используется в MVP, так как принят один HTML)
- `theme_signature` (если глобальные токены влияют на HTML/meta)

Рекомендуемый формат `context_key`:

- Standalone: `page:{page_key}`
- Content item: `content:view:{ctype}:{item_id}`
- Category list: `content:category:{ctype}:{category_id}:p{page}`
- Dataset list: `content:dataset:{ctype}:{dataset_key}:p{page}`

`theme_signature` — hash от набора активных глобальных токенов (чтобы при смене preset можно было инвалидировать кеш целиком).

## Publish-модель (SEO-first)

### 1) Публикуем два слоя

A) **Published PageDocument** (истина макета)

- что пользователь собрал в конструкторе.

B) **Published SSR HTML Cache** (ускорение)

- готовый HTML под конкретный контекст.

### 2) Где хранить кеш

Чтобы поддержать per-item/per-category и не ломать текущую таблицу, предпочтительнее:

- оставить `nordicbuilder_page_renders` как кеш “standalone page_key”,
- добавить новую таблицу (примерное имя): `nordicbuilder_runtime_renders` с полями:
  - `binding_key`
  - `context_key`
  - `device_type`
  - `theme_signature`
  - `meta_json`
  - `html`
  - `content_hash`
  - `published_at`, `updated_at`
  - индекс/unique на (`binding_key`,`context_key`,`device_type`,`theme_signature`).

Это безопаснее для коммерческого ZIP (не ломаешь установленные версии и простой режим).

## Runtime flow (как это работает на запросе)

1) InstantCMS обрабатывает URL и формирует контекст (ctype/item/category).
2) NordicBuilder resolver выбирает `binding_key` по context.
3) Виджет `nordicbuilder_render`:

- строит `context_key`;
- пытается взять SSR cache entry;
- если найдено — выводит HTML + выставляет meta + style vars.

4) Если кеша нет:

- SSR renderer строит HTML из PageDocument + adapters (данные из Instant),
- сохраняет entry (lazy fill),
- отдаёт результат.

## Invalidation (как не держать устаревший HTML)

### P0: события контента

InstantCMS имеет события в `content` (пример: `content_after_add`, `content_after_update`, `content_after_delete`, и per-ctype варианты).

Нужно добавить hooks в `nordicbuilder`, которые при изменениях:

- инвалидируют item cache: `content:view:{ctype}:{item_id}`
- инвалидируют category cache для категории записи (минимум: `content:category:{ctype}:{category_id}:p1`)
- при необходимости инвалидируют главные списки/датасеты.

### P1: TTL + ручной rebuild

Для источников, где нет стабильных событий (например меню/виджеты сторонних компонентов):

- использовать TTL (например 1–12 часов на списки);
- дать кнопку “Пересобрать кеш” в админке (по binding / по ctype / всё).

### P2: расширенная инвалидация

- отдельные adapter-level dependency keys (например `menu:{name}`),
- инвалидация по этим dependency keys.

## Что делать с «Нативным контентом Instant»

Для страниц типа `content/view` и `content/category` в builder-макете должен быть системный блок “Нативный контент”, который:

- вставляет стандартный вывод InstantCMS (запись/категория),
- и остаётся совместимым с любыми темами.

Это позволяет строить “обвязку” вокруг контента, не ломая привычную модель CMS.

## P0 roadmap (1–2 недели)

### Неделя 1: инфраструктура

1) Формализовать Runtime Context и `context_key`.
2) Реализовать cache storage для per-context (новая таблица).
3) Реализовать lazy fill: нет кеша → SSR render → сохранить → отдать.
4) Ограничить кеширование гостевым режимом (admin/logged-in bypass).

### Неделя 2: инвалидация и коммерческая надёжность

5) Включить P0 invalidation на событиях `content_after_update/delete`.
6) Добавить ручной rebuild (по binding и глобально).
7) Добавить theme_signature и массовую инвалидацию при смене глобальных preset.

## Открытые вопросы (их нужно закрыть до реализации)

1) Какие контексты считаем обязательными для MVP: item view, category list, homepage?
2) Какой минимальный TTL для списков допустим в коммерческом продукте?
