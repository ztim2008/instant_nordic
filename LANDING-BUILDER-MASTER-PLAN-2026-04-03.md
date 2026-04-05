# Главный план: Нордик для InstantCMS 2

> Статус файла: reference-уровень.
>
> Текущий рабочий execution tracker теперь ведется в [LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md](LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md).

## Что это за файл

Это главный трекер проекта.

Если нужно быстро понять, что делать дальше, в каком порядке идти и что уже готово по конструктору, начинать нужно с этого документа.

## Продуктовая рамка

- Публичное имя продукта: `Нордик`
- Язык интерфейса MVP: русский
- Целевая платформа: InstantCMS 2
- Текущий переходный компонент в репозитории: `landingbuilder`
- Канонический целевой builder component: отдельный visual-builder компонент `nordicbuilder`
- Рабочее техническое имя frontend template: `nordic`
- Development base в этом репозитории: `instantcms-mcp-main`
- Итоговый продукт: backend foundation + builder component + `nordic` template runtime + design system + component library + widget/data adapter + installer/update mechanism

## Легенда статусов

- ⚪ Не начато
- 🟡 Запланировано
- 🔵 В работе
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Порядок чтения документов

1. [LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md](LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md)
2. [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
3. [LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md](LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md)
4. [LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md](LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md)
5. [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md)
6. [LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md](LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md)
7. [LANDING-BUILDER-ROADMAP-2026-04-03.md](LANDING-BUILDER-ROADMAP-2026-04-03.md)
8. [LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md](LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md)
9. [LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md](LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md)
10. [LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md)
11. [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md)
12. [LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md](LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md)
13. [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
14. [LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md](LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md)
15. [LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md](LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md)

## Актуальное архитектурное решение 2026-04-05

После анализа подхода `inthemer` и собственного целевого UX зафиксировано следующее:

1. Overlay-путь поверх существующих шаблонов остается важным как режим миграции, но не считается единственной архитектурой продукта.
2. Текущий `landingbuilder` считается переходным bridge-слоем, а не окончательной продуктовой границей.
3. Целевой продукт Нордик строится как отдельный visual-builder слой над InstantCMS 2 и отдельный frontend template `nordic`.
4. Внутри продукта должен существовать отдельный design system token-layer с secondary screen `Глобальные стили`.
5. `modern` остается reference и временным migration path, но не основной shell целевого продукта.
6. Основной пользовательский UX дальше строится как guided visual builder: `страница -> секции -> блоки -> элементы`, а не как Bootstrap/grid editor.
7. Component library и widget/data adapter считаются обязательными слоями взрослой архитектуры, а не опциональным довеском.
8. Канонический MVP теперь идет в три этапа: foundation layer, visual editor core, system integration layer.
9. Следующий осознанный кодовый выбор должен проверяться не по инерции текущего `landingbuilder`, а по тому, приближает ли он boundary нового builder-компонента.

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
  - решение по bridge-модели `landingbuilder` -> `nordicbuilder` + `nordic`
  - решение по install/update продукту
  - продуктовый blueprint visual builder и design system defaults UX
  - каноническая карта продукта и границы между backend, runtime, builder, component library и adapter-layer
- Артефакты:
  - [LANDING-BUILDER-ROADMAP-2026-04-03.md](LANDING-BUILDER-ROADMAP-2026-04-03.md)
  - [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md)
  - [LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md](LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md)
  - [LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md](LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md)
- Готово:
  - зафиксирована dev-база `instantcms-mcp-main`
  - зафиксирован отдельный installable component
  - зафиксирован отдельный frontend template `nordic`
  - зафиксирован подход core + packs
  - зафиксирован русский продуктовый фокус
- Следующий результат:
  - зафиксировать stage 1 foundation для `nordicbuilder`: schema contract, design token model, component library core и bridge-стратегию переиспользования текущего кода

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
  - `content_body` закреплен как канонический runtime slot для standalone pages и native system content
  - page contracts расширены под `shell_slots`, `layout.content_slot` и legacy migration keys
  - выполнен временный smoke-test с переключением сайта на `nordic` и проверкой живых страниц
  - добавлена каноничная shell scheme `nordic_shell_v1` с отдельным source of truth в `templates/nordic/shell_scheme.php`
  - добавлен runtime contract `layout.scheme` и canonical slot positions для `nordic`
  - добавлен отдельный `templates/nordic/layout_childs/main_scheme.tpl.php`, который не дублирует shell positions в `content_body`
  - зафиксирована code-level map для перевода copied `modern` bind positions в shell slots `nordic`
  - собран и прогнан dry-run/apply migration script для `nordic` rows, cols и bind positions
  - copied `nordic` rows и bind positions в БД переведены на canonical shell scheme `nordic_shell_v1`
  - повторный smoke-test на live `nordic` после apply прошел
  - migration logic вынесена в общий helper `templates/nordic/install_helpers/shell_migration.php` и пакет-зеркало
  - пакет `packages/nordic` получил root installer hook `packages/nordic/install.php` с auto-apply `nordic_shell_v1` после install/update pipeline
  - для админки добавлен `templates/nordic/scheme.php`, который показывает shell map и не отключает dynamic layout editor

- Следующий результат:
  - прогнать ручной UI smoke-test страницы `/admin/widgets?template_name=nordic` и проверить итоговый UX на живых bind-операциях
  - при необходимости добавить package-level update manifest, если будем собирать отдельный update-архив для `nordic`

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
