# Главный план: Нордик для InstantCMS 2

## Что это за файл

Это главный трекер проекта.

Если нужно быстро понять, что делать дальше, в каком порядке идти и что уже готово по конструктору, начинать нужно с этого документа.

## Продуктовая рамка

- Публичное имя продукта: `Нордик`
- Язык интерфейса MVP: русский
- Целевая платформа: InstantCMS 2
- Рабочее техническое имя компонента: `landingbuilder`
- Рабочее техническое имя frontend template: `nordic`
- Development base в этом репозитории: `instantcms-mcp-main`
- Итоговый продукт: component + template + design system + installer + update mechanism

## Легенда статусов

- ⚪ Не начато
- 🟡 Запланировано
- 🔵 В работе
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Порядок чтения документов

1. [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
2. [LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md](LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md)
3. [LANDING-BUILDER-ROADMAP-2026-04-03.md](LANDING-BUILDER-ROADMAP-2026-04-03.md)
4. [LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md](LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md)
5. [LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md](LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md)
6. [LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md)
7. [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md)
8. [LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md](LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md)
9. [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
10. [LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md](LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md)
11. [LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md](LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md)

## Актуальное архитектурное решение 2026-04-04

После анализа подхода `inthemer` и собственного целевого UX зафиксировано следующее:

1. Overlay-путь поверх существующих шаблонов остается важным как режим миграции, но не считается единственной архитектурой продукта.
2. Целевой продукт Нордик строится как связка отдельного компонента `landingbuilder` и отдельного frontend template `nordic`.
3. Внутри продукта должен существовать отдельный слой global theme settings и design system tokens.
4. `modern` остается reference и временным migration path, но не основной shell целевого продукта.
5. Следующий крупный этап после стабилизации overlay-режима это skeleton шаблона `nordic` и управляемых theme slots.

## Главный трек работ

### 0. Project foundation

Статус: 🟢 Готово

- Что входит:
  - git-репозиторий
  - rollback scripts
  - docs/process foundation
  - инструкции для агента
- Артефакты:
  - [README.md](README.md)
  - [docs/PROJECT-OVERVIEW.md](docs/PROJECT-OVERVIEW.md)
  - [docs/DEVELOPMENT-WORKFLOW.md](docs/DEVELOPMENT-WORKFLOW.md)
  - [docs/ROLLBACK-AND-RECOVERY.md](docs/ROLLBACK-AND-RECOVERY.md)
  - [AGENTS.md](AGENTS.md)

### 1. Product definition and architecture

Статус: 🔵 В работе

- Что входит:
  - продуктовая рамка `Нордик`
  - границы scope
  - решение по dev-базе `instantcms-mcp-main`
  - решение по hybrid-модели `landingbuilder` + `nordic`
  - решение по install/update продукту
- Артефакты:
  - [LANDING-BUILDER-ROADMAP-2026-04-03.md](LANDING-BUILDER-ROADMAP-2026-04-03.md)
- Готово:
  - зафиксирована dev-база `instantcms-mcp-main`
  - зафиксирован отдельный installable component
  - зафиксирован отдельный frontend template `nordic`
  - зафиксирован подход core + packs
  - зафиксирован русский продуктовый фокус
- Следующий результат:
  - закрепить технические имена component/template в коде skeleton

### 2. Packaging and update architecture

Статус: 🟡 Запланировано

- Что входит:
  - install package discipline
  - update package discipline
  - versioning strategy
  - release composition for digital product
- Связанные документы:
  - [LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md](LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md)

### 3. Backend component skeleton

Статус: ⚪ Не начато

- Что входит:
  - `backend.php`
  - `frontend.php`
  - `manifest.php`
  - backend menu
  - options form
- Связанные документы:
  - [LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md](LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md)

### 4. Frontend template skeleton `nordic`

Статус: 🔵 В работе

- Что входит:
  - отдельный template package
  - manifest шаблона
  - базовый shell template
  - регистрация как selectable default template в InstantCMS settings
  - опора на `modern` как reference или base

- Готово:
  - добавлен runtime scaffold шаблона `templates/nordic`
  - добавлен пакет-зеркало `packages/nordic`
  - добавлен стартовый shell со слотами `site_top`, `header_primary`, `header_secondary`, `hero`, `before_content`, `after_content`, `footer_primary`, `footer_secondary`
  - добавлен дефолтный theme config `theme_nordic.yml`

- Следующий результат:
  - встроить явный `content_body` contract поверх shell slots и связать его с runtime `landingbuilder`

- Связанные документы:
  - [LANDING-BUILDER-ROADMAP-2026-04-03.md](LANDING-BUILDER-ROADMAP-2026-04-03.md)
  - [LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md](LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md)

### 5. SQL data model and install layer

Статус: ⚪ Не начато

- Что входит:
  - `install.sql`
  - таблицы pages, page_versions, bindings, presets, registry
  - install/update discipline
- Связанные документы:
  - [LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md)

### 6. JSON contracts and validation layer

Статус: ⚪ Не начато

- Что входит:
  - page schema contract
  - block manifest contract
  - adapter manifest contract
  - preset token contract
  - binding options contract
- Связанные документы:
  - [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)

### 7. Registry and packs pipeline

Статус: ⚪ Не начато

- Что входит:
  - registry refresh
  - pack scan
  - upsert manifests
  - diagnostics broken/incompatible
- Связанные документы:
  - [LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md](LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md)

### 8. Page adapters and bindings

Статус: ⚪ Не начато

- Что входит:
  - standalone landing adapter
  - content item/list/category adapters
  - profile/home bindings
  - styling overlay and structural overlay
  - participation modes: full_takeover, hybrid_overlay, zone_injection, data_only
- Связанные документы:
  - [LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md](LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md)
  - [LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md](LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md)
  - [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md)

### 9. Runtime rendering and version resolution

Статус: ⚪ Не начато

- Что входит:
  - resolve binding
  - resolve adapter
  - resolve preset inheritance
  - resolve published page version
  - render or fallback
- Связанные документы:
  - [LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md](LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md)

### 10. Editor and canvas UI

Статус: ⚪ Не начато

- Что входит:
  - canvas
  - section -> columns -> nodes model
  - drag-and-drop
  - multi-section pages
  - 1/2/3-column presets
  - block library sidebar
  - standard widgets sidebar tab
  - props editing
  - desktop/tablet/mobile toggles
  - live preview
- Примечание:
  - этот этап нельзя начинать до завершения skeleton, SQL и contracts
- Связанные документы:
  - [LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md](LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md)
  - [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)

### 11. Core blocks library

Статус: ⚪ Не начато

- Что входит:
  - hero blocks
  - CTA blocks
  - features blocks
  - content-driven sections
  - token-aware render templates
  - dynamic collection blocks for ctype-driven cards and lists

### 12. Data source resolver and collections

Статус: 🟡 Запланировано

- Что входит:
  - universal block data source model
  - ctype item/list/category sources
  - query collections
  - reusable source presets
  - empty-state and fallback behavior
- Связанные документы:
  - [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md)
  - [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)

### 13. Tests, docs and release discipline

Статус: 🟡 Запланировано

- Что входит:
  - ручные проверки
  - smoke сценарии
  - worklog discipline
  - release tags
  - docs sync

## Трекер паков и плагинов

### Core component `landingbuilder`

Статус: ⚪ Не начато

### Frontend template `nordic`

Статус: ⚪ Не начато

### Base blocks pack `nordic-core-blocks`

Статус: ⚪ Не начато

### Дополнительные тематические packs

Статус: 🟡 Запланировано

- примеры:
  - `nordic-marketing-pack`
  - `nordic-music-pack`
  - `nordic-premium-pack-01`

### Dev tooling `instantcms-mcp-main`

Статус: 🟢 Готово как основная dev-база

- роль:
  - scaffolding
  - introspection
  - reference для InstantCMS tooling
  - основа для технического проектирования в этом репозитории

## Правило перевода этапа в 🟢

Этап можно считать готовым только если:

1. Есть код или документируемый результат.
2. Есть краткая проверка результата.
3. Обновлены связанные документы.
4. Запись добавлена в [docs/WORKLOG.md](docs/WORKLOG.md).

## Текущий следующий шаг

Следующий практический шаг по коду:

1. создать skeleton компонента `landingbuilder`;
2. создать skeleton шаблона `nordic`;
3. добавить backend/frontend/manifest/install scaffold и packaging foundation;
4. после этого перейти к SQL-модели и registry foundation.
