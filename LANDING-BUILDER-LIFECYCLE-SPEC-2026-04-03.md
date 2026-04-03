# Техспека: lifecycle manifests, presets, page versions и bindings

## Статусы

- 🔴 Запланировано
- 🔵 В работе
- 🟢 Готово

## 1. Цель документа

Этот документ фиксирует lifecycle ключевых документов и runtime-сущностей конструктора.

Он отвечает на 4 вопроса:

1. Как регистрируются `block manifests` и `adapter manifests`.
2. Как мерджится inheritance у `preset tokens`.
3. Как версионируется `page schema`.
4. Как в runtime разрешаются `bindings`.

Это уже не просто описание форматов данных, а описание движения этих данных через систему.

## 2. На что опирается lifecycle

### 🟢 Готово: зависимые документы зафиксированы

Этот документ опирается на:

1. `LANDING-BUILDER-ROADMAP-2026-04-03.md`
2. `LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md`
3. `LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md`
4. `LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md`
5. `LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md`

Также он опирается на выводы из `instantcms-mcp-main`:

- у InstantCMS уже есть package/install pipeline;
- у InstantCMS уже есть реестр компонентов и options;
- у InstantCMS уже есть page matching-модель на примере `cms_widgets_pages`;
- schema-first validation лучше закладывать сразу.

## 3. Общая модель lifecycle

### 🟢 Готово: единая цепочка зафиксирована

У конструктора должен быть такой жизненный цикл данных:

1. Установка core component или external pack.
2. Загрузка package manifest.
3. Поиск и валидация block/adapters manifests.
4. Регистрация manifests в builder registry.
5. Доступ manifests в editor UI.
6. Создание или обновление page schema.
7. Выбор preset и bindings.
8. Runtime resolve binding для текущего запроса.
9. Resolve adapter.
10. Resolve preset inheritance.
11. Resolve page version.
12. Render или fallback.

## 4. Lifecycle регистрации block manifests и adapter manifests

### 🔵 В работе: регистрационный pipeline зафиксирован

## 4.1. Источники manifests

Manifest может приходить из двух источников:

1. Core builder package.
2. External installable pack.

В первой версии правильная модель такая:

- package система доставляет файлы в проект;
- builder runtime после этого сканирует известные точки manifests;
- после сканирования registry обновляется через upsert.

## 4.2. Логические стадии регистрации

### Stage 1. Package installed

После установки пакета InstantCMS файлы пакета уже лежат в системе.

На этом шаге builder ещё не считает registry актуальным автоматически, пока не прошёл scan/import cycle.

### Stage 2. Registry scan started

Builder запускает registry refresh:

1. читает meta установленного pack;
2. определяет, где лежат manifests;
3. находит block manifests;
4. находит adapter manifests;
5. собирает raw manifest payloads.

### Stage 3. Validation

Для каждого manifest выполняется:

1. проверка `kind`;
2. проверка `schema_version`;
3. проверка `key`;
4. проверка required fields;
5. проверка внутренних enum и контрактов.

Если validation не проходит:

- manifest не попадает в active registry;
- pack может получить статус `broken` или `incompatible`;
- ошибка логируется в backend diagnostics.

### Stage 4. Registry upsert

Если validation проходит:

1. pack upsert в `landingbuilder_registry_packs`;
2. manifests blocks upsert в `landingbuilder_registry_blocks`;
3. manifests adapters upsert в `landingbuilder_registry_adapters`.

Upsert должен работать по стабильным `pack_key`, `block_key`, `adapter_key`.

### Stage 5. Deactivation of missing manifests

Если при refresh какой-то ранее установленный manifest больше не найден:

- не удалять его физически сразу;
- помечать как `is_enabled = 0` или `status = disabled/broken`;
- сохранять возможность диагностики существующих страниц, которые на него ссылались.

Это критично, иначе старые pages начнут ломаться без следа.

## 4.3. Правило стабильных ключей

Ключи должны жить дольше, чем конкретная версия кода.

Примеры:

- `core.hero-heading`
- `premium.music-feature-grid`
- `content_item_generic`

Нельзя делать ключи, завязанные на временные id или путь конкретного проекта.

## 4.4. Когда запускать refresh

Минимально нужны такие точки запуска:

1. после установки pack;
2. после обновления pack;
3. вручную из backend-раздела `Block Packs`;
4. по диагностической команде администратора.

В первой версии не нужно делать постоянный авто-watch файловой системы.

## 5. Lifecycle merge preset inheritance

### 🔵 В работе: merge chain и приоритеты зафиксированы

Preset inheritance должен быть предсказуемым и детерминированным.

## 5.1. Источники токенов

Финальный effective preset для страницы может собираться из нескольких уровней:

1. Core base preset.
2. Parent preset chain через `inherits`.
3. Binding-level preset.
4. Page-level `preset_key`.
5. Page-level `tokens.overrides`.
6. Zone-level overrides.

## 5.2. Рекомендуемый порядок merge

Порядок должен быть таким:

1. resolve chain `inherits` от базового предка к потомку;
2. применить токены текущего preset;
3. если binding задаёт explicit preset override, применить его после page default только если binding имеет больший приоритет для runtime context;
4. применить `page.tokens.overrides`;
5. применить `zone_overrides` на уровне конкретной зоны.

Ключевой принцип:

- чем ближе слой к конкретному runtime context, тем позже он мерджится.

## 5.3. Merge semantics

Для MVP рекомендуются такие правила:

1. object + object = deep merge;
2. scalar + scalar = replace;
3. array + array = replace целиком, а не deep merge;
4. `null` не использовать как silent-delete без отдельной договорённости;
5. cycle в `inherits` должен быть ошибкой валидации.

## 5.4. Resolve algorithm

Логически алгоритм должен быть таким:

1. загрузить root preset;
2. рекурсивно пройти `inherits`;
3. собрать ordered chain;
4. проверить циклы;
5. последовательно применить merge;
6. вернуть effective token tree.

## 5.5. Что делать при ошибках inheritance

Если один из parent presets не найден:

- не падать фатально на всём сайте;
- пометить effective preset как degraded;
- применить fallback к ближайшему валидному слою;
- отдать сигнал в backend diagnostics.

## 6. Lifecycle versioning page schema

### 🔵 В работе: модель редакторских версий зафиксирована

Page schema не должна жить как один перезаписываемый JSON blob.

Правильная модель уже зафиксирована через `pages` + `page_versions`.

## 6.1. Основные состояния версии

Для первой версии:

1. `draft`
2. `autosave`
3. `published`
4. `snapshot`

## 6.2. Базовый lifecycle страницы

### Создание

1. создаётся запись в `landingbuilder_pages`;
2. создаётся первая версия `version_no = 1`, `version_type = draft`;
3. `current_version_id` указывает на неё.

### Редактирование

1. пользователь меняет page schema в editor;
2. создаётся новая draft или autosave version;
3. `current_version_id` двигается на последнюю draft-compatible версию;
4. `published_version_id` остаётся прежней.

### Публикация

1. текущая draft-версия либо переводится в published snapshot, либо клонируется в published;
2. `published_version_id` обновляется;
3. `pages.status = published`;
4. фиксируется `published_by` и `date_published`.

### Откат

1. старая published или snapshot version выбирается как base;
2. на её основе создаётся новая draft version;
3. прямой destructive rollback без новой версии не рекомендуется.

## 6.3. Версионирование schema format

Нужно разделять две вещи:

1. номер версии документа страницы внутри одной страницы: `version_no`;
2. версию формата JSON: `schema_version`.

Это принципиально разные уровни.

Пример:

- page version `18`
- schema format `1.0`

## 6.4. Миграция page schema между версиями формата

При изменении `schema_version` runtime/editor не должны молча доверять старому JSON.

Нужен pipeline:

1. detect old `schema_version`;
2. найти migration chain;
3. преобразовать JSON в актуальную схему;
4. провалидировать результат;
5. сохранить как новую draft или system snapshot version.

В первой версии можно ограничиться только forward migrations.

## 6.5. Как работать с deprecated blocks

Если page schema ссылается на deprecated block:

- runtime не должен сразу ломать страницу;
- блок можно продолжать рендерить, если renderer доступен;
- editor должен предупреждать, что блок deprecated;
- при возможности миграция должна предлагать replacement.

## 7. Lifecycle runtime resolve bindings

### 🔵 В работе: runtime resolution path зафиксирован

Bindings должны разрешаться не по одному флагу, а по полному runtime context.

## 7.1. Входные данные runtime resolver

Resolver должен уметь учитывать:

1. controller;
2. action;
3. route;
4. matched ctype;
5. page type;
6. user context при необходимости;
7. enable flags;
8. priority.

## 7.2. Рекомендуемый алгоритм resolve

### Step 1. Собрать context

На основе запроса собрать `RuntimePageContext`:

- controller;
- action;
- uri;
- ctype name;
- item/list/category/profile/homepage scope;
- route params.

### Step 2. Найти candidate bindings

Из `landingbuilder_bindings` выбрать bindings, которые:

1. `is_enabled = 1`;
2. подходят по `binding_type`;
3. подходят по controller/action/ctype/route;
4. не отфильтрованы exclude rules.

### Step 3. Отсортировать candidates

Приоритет сортировки рекомендуется такой:

1. более специфичный binding;
2. больший `priority`;
3. system exact binding выше generic binding;
4. ctype-specific binding выше общего content binding.

### Step 4. Выбрать winning binding

После сортировки выбирается один effective binding.

В первой версии не рекомендуется смешивать несколько bindings в один runtime page compose, кроме специальных future scenarios.

### Step 5. Resolve adapter

По `binding.adapter_key` или page default adapter:

1. загрузить adapter manifest;
2. убедиться, что adapter enabled;
3. проверить, что adapter поддерживает нужный scope;
4. проверить zones и mode.

### Step 6. Resolve page

Если binding указывает на builder page:

1. загрузить page entity;
2. взять `published_version_id` для frontend runtime;
3. для preview может использоваться `current_version_id`.

### Step 7. Resolve preset

1. взять preset из binding, если он explicit;
2. иначе использовать page preset;
3. иначе использовать adapter/page-type default preset;
4. собрать effective tokens через inheritance merge.

### Step 8. Render or fallback

Если всё валидно:

- выполнить render pipeline.

Если что-то невалидно:

- применить fallback из binding options.

## 7.3. Fallback policy

При ошибках разрешения нужно поддержать минимум такие сценарии:

1. adapter не найден;
2. page не найдена;
3. page version не найдена;
4. preset не найден;
5. page schema невалидна;
6. binding устарел.

Рекомендуемое поведение:

- fallback в theme/native rendering;
- запись в diagnostics;
- отсутствие фатального падения frontend там, где это возможно.

## 8. Минимальные runtime services

### 🔴 Запланировано

Для будущей реализации стоит сразу мыслить систему такими логическими сервисами:

1. `PackRegistryRefresher`
2. `BlockManifestValidator`
3. `AdapterManifestValidator`
4. `PresetResolver`
5. `PageVersionManager`
6. `BindingResolver`
7. `PageSchemaMigrator`
8. `BuilderRuntimeRenderer`

Это ещё не классы реализации, а архитектурные роли.

## 9. Что уже можно считать зафиксированным

### 🟢 Готово

1. Package install и builder registry refresh это разные стадии lifecycle.
2. Block/adapters manifests должны попадать в registry через validate + upsert pipeline.
3. Missing manifests после refresh не удаляются бесследно, а деактивируются или помечаются как broken.
4. Preset inheritance должен собираться через детерминированный ordered merge.
5. Page schema versioning должен разделять `version_no` и `schema_version`.
6. Runtime bindings должны разрешаться по полному request context, а не по одному флагу.
7. При ошибках предпочтителен fallback в theme/native rendering, а не жёсткое падение страницы.

## 10. Следующий логичный шаг

### 🔴 Запланировано

Следующий документ должен описать execution plan первой реализации на clean InstantCMS instance:

1. структура новой ветки;
2. минимальный skeleton компонента;
3. install.sql;
4. первый registry refresh flow;
5. первый draft/publish flow.