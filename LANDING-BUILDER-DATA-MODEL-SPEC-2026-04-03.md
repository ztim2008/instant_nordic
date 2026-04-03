# Техспека: data model и таблицы конструктора

## Статусы

- ⚪ Не начато
- 🟡 Запланировано
- 🔵 В работе
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Навигация

- Главный трекер: [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
- Предыдущий документ: [LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md](LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md)
- Следующий документ: [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)

## 1. Цель документа

Этот документ фиксирует data model первой взрослой версии конструктора лендингов для InstantCMS.

Он отвечает на 5 вопросов:

1. Какие сущности должны быть у конструктора.
2. Какие таблицы нужны реально, а какие не нужны.
3. Что хранить в собственных таблицах компонента, а что опирать на уже существующие таблицы InstantCMS.
4. Как хранить страницы, bindings, presets и registry.
5. На какой install/migration-подход ориентироваться до старта реализации.

## 2. На что опирается эта модель

### 🟢 Готово: опора на текущие документы и instantcms-mcp-main зафиксирована

Модель не придумывается с нуля в вакууме. Она опирается на уже изученные части проекта.

### 2.1. На локальные документы проекта

- `LANDING-BUILDER-ROADMAP-2026-04-03.md`
- `LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md`
- `LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md`

### 2.2. На instantcms-mcp-main

Ключевые выводы из `instantcms-mcp-main`:

1. Компонент живёт как обычный InstantCMS component с `backend.php`, `frontend.php`, `manifest` и package/install логикой.
2. Список компонентов и их options уже живёт в `cms_controllers`.
3. Локальные настройки content types уже живут в `cms_content_types.options`.
4. Права доступа уже живут в `cms_perms_rules` и `cms_perms_users`.
5. InstantCMS уже использует отдельную таблицу page matching для widgets через `cms_widgets_pages`.
6. Для рендера и внедрения на страницу уже есть подходящие точки интеграции вроде `before_render_page`, `render_page` и `frontpage_action_index`.

Именно поэтому builder не должен дублировать базовые механизмы CMS, а должен расширять их собственными таблицами только там, где ядро уже ничего не хранит.

## 3. Базовый принцип модели

### 🟢 Готово: принцип зафиксирован

У конструктора должны быть 4 главные агрегатные зоны данных:

1. `pages`
2. `bindings`
3. `presets`
4. `registry`

Но это не означает, что каждая визуальная мелочь должна иметь отдельную SQL-таблицу.

Текущая рекомендация такая:

- страница конструктора хранится как metadata + versioned JSON schema;
- bindings хранятся отдельно как orchestration layer;
- presets хранятся отдельно как reusable style/data layer;
- registry хранится отдельно для blocks, adapters и packs;
- глобальные options компонента не выносятся в отдельную таблицу, а живут там, где это уже делает InstantCMS;
- opt-in по content type не выносится в отдельную таблицу, а живёт рядом с самим ctype.

## 4. Что использовать из ядра InstantCMS, а не дублировать

### 🟢 Готово: границы между core и builder определены

### 4.1. Использовать существующие таблицы ядра

#### `cms_controllers`

Использовать для:

- регистрации компонента `landingbuilder`;
- хранения глобальных options компонента через стандартный механизм options action;
- хранения флага включённости компонента и его версии.

Вывод:

- отдельная таблица `landingbuilder_options` не нужна.

#### `cms_content_types`

Использовать для:

- opt-in участия ctype в конструкторе;
- хранения локальных builder-настроек конкретного ctype внутри `options`.

Пример логического блока внутри `cms_content_types.options`:

```php
[
    'landingbuilder' => [
        'enabled' => true,
        'supports' => ['item', 'list', 'category'],
        'adapter' => 'content_item_generic',
        'preset' => 'music_dark',
        'allow_structural_overlay' => true
    ]
]
```

Вывод:

- отдельная таблица `landingbuilder_content_type_settings` для MVP не нужна.

#### `cms_perms_rules` и `cms_perms_users`

Использовать для:

- прав `edit_pages`;
- `publish_pages`;
- `manage_bindings`;
- `manage_presets`;
- `manage_registry`;
- `manage_packs`.

Вывод:

- отдельная ACL-таблица builder не нужна.

#### `cms_users`

Использовать для:

- ссылок `created_by`;
- `updated_by`;
- `published_by`;
- `locked_by`, если позже понадобится collaborative edit.

#### `cms_widgets_pages`

Эту таблицу не нужно переиспользовать напрямую, но её нужно считать архитектурным ориентиром.

Почему:

- InstantCMS уже решает через неё задачу page matching по controller, name и URL mask;
- builder bindings решают похожую задачу, но шире: не только для widget pages, а для overlays и page ownership.

Вывод:

- делать собственную таблицу bindings правильно;
- но её semantics должны быть совместимы по духу с моделью `cms_widgets_pages`.

### 4.2. Не дублировать package/install слой ядра

Из `instantcms-mcp-main` также видно, что у InstantCMS уже есть manifest/package/install pipeline.

Следовательно:

- установка core builder идёт через обычный component package;
- установка external block packs идёт через installable package;
- runtime registry packs всё равно хранится у builder отдельно, но это не заменяет package manifest системы.

## 5. Какие собственные таблицы нужны компоненту

### 🟢 Готово: MVP-структура зафиксирована

Для первой версии рекомендуются 7 кастомных таблиц:

1. `{#}landingbuilder_pages`
2. `{#}landingbuilder_page_versions`
3. `{#}landingbuilder_bindings`
4. `{#}landingbuilder_presets`
5. `{#}landingbuilder_registry_blocks`
6. `{#}landingbuilder_registry_adapters`
7. `{#}landingbuilder_registry_packs`

Где `{#}` означает стандартный префикс таблиц InstantCMS в `install.sql`.

На живой БД это будут физические таблицы вида:

- `cms_landingbuilder_pages`
- `cms_landingbuilder_page_versions`
- и так далее.

## 6. Почему не нужны отдельные tables для sections и blocks страницы

### 🟢 Готово: решение зафиксировано

На этом этапе не рекомендуется делать такие таблицы как:

- `landingbuilder_sections`
- `landingbuilder_page_blocks`
- `landingbuilder_block_props`

Причина простая:

- конструктор должен жить как schema-driven editor;
- структура страницы будет часто меняться;
- блоки будут расширяться внешними packs;
- жёсткая реляционная декомпозиция начнёт мешать эволюции DSL и migrations.

Правильнее хранить:

- metadata страницы в SQL;
- снимок схемы страницы в JSON версии.

Именно поэтому table `pages` неразрывно связана с `page_versions`.

## 7. Сущность `pages`

### 🔵 В работе: структура агрегата зафиксирована

`pages` описывает логическую страницу конструктора, а не её конкретную ревизию.

Это root entity.

### 7.1. Назначение

Хранить:

- identity страницы;
- её тип;
- статус;
- дефолтный adapter/preset;
- ссылки на текущую и опубликованную версию;
- редакторские метаданные.

### 7.2. Таблица `{#}landingbuilder_pages`

| Поле | Тип | Назначение |
| --- | --- | --- |
| `id` | `BIGINT UNSIGNED` | PK |
| `uuid` | `CHAR(36)` | стабильный публичный идентификатор |
| `page_key` | `VARCHAR(191)` | уникальный системный ключ страницы |
| `title` | `VARCHAR(255)` | backend title |
| `slug` | `VARCHAR(191)` | slug для standalone pages |
| `page_type` | `ENUM('standalone','system_overlay','ctype_overlay')` | тип страницы |
| `editor_mode` | `ENUM('canvas','overlay')` | режим редактора |
| `status` | `ENUM('draft','published','archived')` | текущий статус |
| `default_adapter_key` | `VARCHAR(100)` | adapter по умолчанию |
| `default_preset_id` | `BIGINT UNSIGNED NULL` | preset по умолчанию |
| `current_version_id` | `BIGINT UNSIGNED NULL` | черновая текущая версия |
| `published_version_id` | `BIGINT UNSIGNED NULL` | опубликованная версия |
| `preview_hash` | `VARCHAR(64) NULL` | short-lived preview marker |
| `is_system` | `TINYINT(1)` | системная предустановленная страница |
| `is_enabled` | `TINYINT(1)` | доступна для использования |
| `created_by` | `INT UNSIGNED` | FK логически на `cms_users.id` |
| `updated_by` | `INT UNSIGNED NULL` | кто обновил |
| `published_by` | `INT UNSIGNED NULL` | кто опубликовал |
| `date_created` | `DATETIME` | дата создания |
| `date_updated` | `DATETIME NULL` | дата обновления |
| `date_published` | `DATETIME NULL` | дата публикации |
| `options` | `LONGTEXT NULL` | доп. метаданные страницы |

### 7.3. Индексы

Обязательные:

1. `PRIMARY (id)`
2. `UNIQUE KEY uq_uuid (uuid)`
3. `UNIQUE KEY uq_page_key (page_key)`
4. `KEY idx_page_type_status (page_type, status)`
5. `KEY idx_slug (slug)`
6. `KEY idx_default_preset (default_preset_id)`

### 7.4. Как использовать `page_key`

`page_key` должен быть главным стабильным ключом на уровне системы.

Примеры:

- `landing/summer-sale`
- `system/homepage`
- `ctype/news/item/default`
- `ctype/music/category/default`

Это удобнее, чем пытаться связывать всё только через integer ids.

## 8. Сущность `page_versions`

### 🟢 Готово: companion table признана обязательной

Без таблицы версий нормальный builder не получится.

Если хранить только одну JSON-схему прямо в `pages`, сразу теряются:

- история изменений;
- publish workflow;
- сравнение draft vs published;
- rollback;
- будущий autosave.

### 8.1. Таблица `{#}landingbuilder_page_versions`

| Поле | Тип | Назначение |
| --- | --- | --- |
| `id` | `BIGINT UNSIGNED` | PK |
| `page_id` | `BIGINT UNSIGNED` | ссылка на страницу |
| `version_no` | `INT UNSIGNED` | номер версии внутри страницы |
| `version_type` | `ENUM('draft','published','autosave','snapshot')` | тип версии |
| `schema_version` | `VARCHAR(32)` | версия DSL схемы |
| `schema_json` | `LONGTEXT` | полная JSON-схема страницы |
| `schema_hash` | `CHAR(64)` | hash схемы для сравнения |
| `based_on_version_id` | `BIGINT UNSIGNED NULL` | от какой версии ответвились |
| `change_summary` | `VARCHAR(255) NULL` | краткое описание изменений |
| `created_by` | `INT UNSIGNED` | автор версии |
| `date_created` | `DATETIME` | дата создания версии |
| `meta` | `LONGTEXT NULL` | доп. данные preview/editor |

### 8.2. Индексы

1. `PRIMARY (id)`
2. `UNIQUE KEY uq_page_version (page_id, version_no)`
3. `KEY idx_page_type (page_id, version_type)`
4. `KEY idx_schema_hash (schema_hash)`

### 8.3. Что хранится внутри `schema_json`

Внутри версии хранится весь page document:

- sections;
- block tree;
- block props;
- token overrides на уровне страницы;
- dynamic data bindings блоков;
- zone placement;
- canvas metadata.

### 8.4. Почему это лучше для block packs

Когда блоки расширяются отдельными пакетами, schema-driven хранение лучше переносит:

- новые block types;
- новые props;
- новые preset references;
- миграции между версиями DSL.

## 9. Сущность `bindings`

### 🔵 В работе: orchestration table зафиксирована

`bindings` отвечает не за хранение самой страницы, а за вопрос:

- где именно на сайте включается builder;
- в каком режиме;
- через какой adapter;
- с каким preset;
- на какую page entity это указывает.

### 9.1. Таблица `{#}landingbuilder_bindings`

| Поле | Тип | Назначение |
| --- | --- | --- |
| `id` | `BIGINT UNSIGNED` | PK |
| `binding_key` | `VARCHAR(191)` | уникальный ключ binding |
| `binding_type` | `ENUM('homepage','route','content_item','content_list','content_category','user_profile','system_page')` | тип привязки |
| `target_controller` | `VARCHAR(32) NULL` | controller InstantCMS |
| `target_action` | `VARCHAR(64) NULL` | action/page name |
| `target_ctype_name` | `VARCHAR(32) NULL` | системное имя ctype |
| `target_route` | `VARCHAR(255) NULL` | route или url mask |
| `target_entity` | `VARCHAR(100) NULL` | уточняющий scope key |
| `page_id` | `BIGINT UNSIGNED NULL` | standalone/builder page target |
| `adapter_key` | `VARCHAR(100)` | adapter для binding |
| `mode` | `ENUM('styling','structural','hybrid')` | режим подключения |
| `preset_id` | `BIGINT UNSIGNED NULL` | preset, если переопределяет дефолт |
| `priority` | `INT` | приоритет матчинга |
| `is_enabled` | `TINYINT(1)` | активен ли binding |
| `is_system` | `TINYINT(1)` | системный binding |
| `options` | `LONGTEXT NULL` | url masks, zones, rules, fallback |
| `created_by` | `INT UNSIGNED` | кто создал |
| `updated_by` | `INT UNSIGNED NULL` | кто обновил |
| `date_created` | `DATETIME` | дата создания |
| `date_updated` | `DATETIME NULL` | дата обновления |

### 9.2. Индексы

1. `PRIMARY (id)`
2. `UNIQUE KEY uq_binding_key (binding_key)`
3. `KEY idx_binding_type_enabled (binding_type, is_enabled, priority)`
4. `KEY idx_target_controller_action (target_controller, target_action)`
5. `KEY idx_target_ctype (target_ctype_name, binding_type)`
6. `KEY idx_page (page_id)`
7. `KEY idx_preset (preset_id)`

### 9.3. Почему binding хранится отдельно от ctype settings

Потому что это два разных уровня.

`cms_content_types.options` отвечает на вопрос:

- имеет ли данный тип контента право участвовать в builder.

А `landingbuilder_bindings` отвечает на вопрос:

- как именно builder подцеплён в runtime.

Это разделение нужно сохранить.

### 9.4. Примеры `binding_key`

- `system:homepage`
- `route:promo/summer`
- `ctype:news:item`
- `ctype:music:category`
- `users:profile:view`

### 9.5. Что хранить в `options`

В `options` bindings должны жить только binding-specific настройки:

- editable zones policy;
- fallback strategy;
- route mask;
- include/exclude rules;
- page context flags.

Туда не нужно класть полную схему страницы.

## 10. Сущность `presets`

### 🔵 В работе: reusable style layer зафиксирован

`presets` нужны не только для цвета кнопок.

Это reusable слой для:

- style tokens;
- default spacing;
- typography policy;
- zone style defaults;
- иногда default block behavior для конкретного scope.

### 10.1. Таблица `{#}landingbuilder_presets`

| Поле | Тип | Назначение |
| --- | --- | --- |
| `id` | `BIGINT UNSIGNED` | PK |
| `preset_key` | `VARCHAR(191)` | уникальный системный ключ |
| `title` | `VARCHAR(255)` | имя пресета |
| `scope` | `ENUM('global','landing','homepage','content_item','content_list','content_category','user_profile','ctype')` | зона применения |
| `source_type` | `ENUM('core','pack','custom')` | источник пресета |
| `source_pack_key` | `VARCHAR(100) NULL` | pack-источник |
| `schema_version` | `VARCHAR(32)` | версия token schema |
| `tokens_json` | `LONGTEXT` | набор design tokens |
| `custom_css` | `LONGTEXT NULL` | дополнительный CSS слой |
| `preview_meta` | `LONGTEXT NULL` | backend preview/meta |
| `is_system` | `TINYINT(1)` | системный пресет |
| `is_enabled` | `TINYINT(1)` | включён ли |
| `sort_order` | `INT` | порядок в UI |
| `created_by` | `INT UNSIGNED NULL` | автор |
| `updated_by` | `INT UNSIGNED NULL` | редактор |
| `date_created` | `DATETIME` | дата создания |
| `date_updated` | `DATETIME NULL` | дата обновления |

### 10.2. Индексы

1. `PRIMARY (id)`
2. `UNIQUE KEY uq_preset_key (preset_key)`
3. `KEY idx_scope_enabled (scope, is_enabled)`
4. `KEY idx_source_pack (source_pack_key)`

### 10.3. Что хранить в `tokens_json`

Примеры доменов:

- colors;
- typography;
- spacing;
- radius;
- shadow;
- buttons;
- cards;
- section backgrounds;
- overlay zone defaults.

### 10.4. Почему presets не надо смешивать с theme options

Потому что:

- theme options описывают тему сайта целиком;
- presets описывают слой builder и overlays;
- один и тот же theme runtime должен уметь использовать много builder presets.

## 11. Сущность `registry`

### 🔵 В работе: registry делится на blocks, adapters и packs

Registry нельзя хранить одной безразмерной таблицей.

Нужны минимум 3 таблицы.

## 11.1. Таблица `{#}landingbuilder_registry_blocks`

| Поле | Тип | Назначение |
| --- | --- | --- |
| `id` | `BIGINT UNSIGNED` | PK |
| `block_key` | `VARCHAR(191)` | уникальный ключ блока |
| `title` | `VARCHAR(255)` | отображаемое название |
| `category` | `VARCHAR(100)` | категория библиотеки |
| `pack_key` | `VARCHAR(100) NULL` | владелец-пакет |
| `schema_version` | `VARCHAR(32)` | версия схемы props |
| `icon` | `VARCHAR(100) NULL` | icon slug |
| `props_schema_json` | `LONGTEXT` | schema props блока |
| `defaults_json` | `LONGTEXT` | default props |
| `data_contract_json` | `LONGTEXT NULL` | поддерживаемые data modes |
| `render_ref` | `VARCHAR(255)` | путь или logical ref renderer |
| `preview_ref` | `VARCHAR(255) NULL` | preview renderer |
| `is_enabled` | `TINYINT(1)` | активен ли блок |
| `is_system` | `TINYINT(1)` | core block |
| `sort_order` | `INT` | порядок в library |
| `manifest_json` | `LONGTEXT NULL` | сырой manifest блока |
| `date_installed` | `DATETIME` | когда появился |
| `date_updated` | `DATETIME NULL` | когда обновился |

### 11.2. Индексы для blocks

1. `PRIMARY (id)`
2. `UNIQUE KEY uq_block_key (block_key)`
3. `KEY idx_category_enabled (category, is_enabled)`
4. `KEY idx_pack_key (pack_key)`

## 11.3. Таблица `{#}landingbuilder_registry_adapters`

| Поле | Тип | Назначение |
| --- | --- | --- |
| `id` | `BIGINT UNSIGNED` | PK |
| `adapter_key` | `VARCHAR(191)` | уникальный ключ adapter |
| `title` | `VARCHAR(255)` | название |
| `target_scope` | `ENUM('standalone','homepage','content_item','content_list','content_category','user_profile','system_page')` | целевой тип страницы |
| `supports_styling` | `TINYINT(1)` | умеет styling overlay |
| `supports_structural` | `TINYINT(1)` | умеет structural overlay |
| `zones_schema_json` | `LONGTEXT` | описание editable zones |
| `matching_rules_json` | `LONGTEXT NULL` | правила определения target |
| `handler_ref` | `VARCHAR(255)` | logical ref обработчика |
| `pack_key` | `VARCHAR(100) NULL` | источник |
| `is_enabled` | `TINYINT(1)` | включён ли |
| `is_system` | `TINYINT(1)` | core adapter |
| `manifest_json` | `LONGTEXT NULL` | сырой manifest |
| `date_installed` | `DATETIME` | дата установки |
| `date_updated` | `DATETIME NULL` | дата обновления |

### 11.4. Индексы для adapters

1. `PRIMARY (id)`
2. `UNIQUE KEY uq_adapter_key (adapter_key)`
3. `KEY idx_target_scope_enabled (target_scope, is_enabled)`
4. `KEY idx_pack_key (pack_key)`

## 11.5. Таблица `{#}landingbuilder_registry_packs`

| Поле | Тип | Назначение |
| --- | --- | --- |
| `id` | `BIGINT UNSIGNED` | PK |
| `pack_key` | `VARCHAR(191)` | уникальный ключ пакета |
| `title` | `VARCHAR(255)` | имя пакета |
| `pack_type` | `ENUM('core','blocks','adapters','mixed')` | тип пакета |
| `version` | `VARCHAR(32)` | версия пакета |
| `core_version_min` | `VARCHAR(32) NULL` | минимальная версия ядра |
| `core_version_max` | `VARCHAR(32) NULL` | максимальная версия ядра |
| `install_source` | `ENUM('package','manual','dev')` | источник установки |
| `status` | `ENUM('active','disabled','incompatible','broken')` | runtime статус |
| `manifest_json` | `LONGTEXT` | manifest и meta |
| `is_enabled` | `TINYINT(1)` | доступен ли |
| `date_installed` | `DATETIME` | дата установки |
| `date_updated` | `DATETIME NULL` | дата обновления |

### 11.6. Индексы для packs

1. `PRIMARY (id)`
2. `UNIQUE KEY uq_pack_key (pack_key)`
3. `KEY idx_pack_type_status (pack_type, status)`

### 11.7. Почему registry нужен даже при наличии manifest package системы

Потому что package система отвечает за установку файлов, а builder registry отвечает за runtime-вопросы:

- какие блоки реально доступны редактору;
- какие adapters разрешены;
- какой pack совместим с текущим core;
- какие сущности включены или выключены в UI.

## 12. Связи между сущностями

### 🟢 Готово: основной graph зафиксирован

Базовые связи такие:

1. `pages 1 -> N page_versions`
2. `pages 1 -> N bindings` или `bindings N -> 1 pages`
3. `presets 1 -> N pages`
4. `presets 1 -> N bindings`
5. `registry_packs 1 -> N registry_blocks`
6. `registry_packs 1 -> N registry_adapters`

Плюс внешние логические связи:

1. `pages.created_by -> cms_users.id`
2. `bindings.target_ctype_name -> cms_content_types.name`
3. `landingbuilder component options -> cms_controllers.options`
4. `builder permissions -> cms_perms_rules / cms_perms_users`

## 13. Что не входит в MVP-модель данных

### 🟢 Готово: ограничения первой версии зафиксированы

В первую версию не стоит включать отдельные таблицы для:

1. analytics;
2. collaborative cursors;
3. per-block comments;
4. page scheduling queue;
5. A/B experiments;
6. asset registry внутри builder.

Это можно будет добавить позже без ломки основной модели.

## 14. Рекомендуемый install/migration подход

### 🟢 Готово: подготовительная стратегия зафиксирована

До начала разработки рекомендуется сразу принять следующие правила.

### 14.1. Сначала чистый InstantCMS

Для этого проекта действительно лучше поднять отдельный чистый InstantCMS, а не сразу писать всё поверх текущего сильно кастомизированного сайта.

Причины:

1. builder затрагивает component install, routes, rendering hooks, content types и backend options одновременно;
2. на текущем проекте уже есть много кастомных шаблонов и overrides;
3. на чистой инсталляции проще отделить архитектурные ошибки от наследия проекта.

### 14.2. Отдельная ветка или отдельный worktree

Рекомендуемый режим работы:

1. текущий проект оставить как reference instance;
2. для builder-core завести отдельную ветку;
3. в идеале завести отдельный чистый workspace или worktree под новую инсталляцию InstantCMS.

Практический вывод:

- сегодня правильно ограничиться подготовкой и фиксацией модели;
- старт кода лучше делать уже на clean install instance.

### 14.3. Формат install.sql

В `install.sql` компонента использовать таблицы в формате:

- `{#}landingbuilder_pages`
- `{#}landingbuilder_page_versions`
- `{#}landingbuilder_bindings`
- `{#}landingbuilder_presets`
- `{#}landingbuilder_registry_blocks`
- `{#}landingbuilder_registry_adapters`
- `{#}landingbuilder_registry_packs`

### 14.4. Формат миграций

Так как schema JSON и manifests будут развиваться, нужно сразу закладывать:

1. `schema_version` в page versions;
2. `schema_version` в presets;
3. `schema_version` в registry blocks;
4. version compatibility в packs.

Без этого block packs быстро начнут ломаться на обновлениях ядра.

## 15. Что уже можно считать окончательно согласованным

### 🟢 Готово

1. Глобальные options компонента не требуют своей таблицы и живут через `cms_controllers.options`.
2. Настройки участия content type живут в `cms_content_types.options`, а не в отдельной builder-таблице.
3. Права доступа builder живут через стандартные `cms_perms_*` таблицы.
4. Собственные таблицы builder нужны только для pages, versions, bindings, presets и registry.
5. Страница хранится как metadata + versioned JSON document.
6. Registry нужно разделять минимум на blocks, adapters и packs.
7. Первый код лучше запускать на отдельной чистой инсталляции InstantCMS и в отдельной ветке.

## 16. Следующий логичный шаг

### 🔴 Запланировано

Следующая спека должна описать JSON contracts:

1. `page schema JSON`
2. `block manifest JSON`
3. `adapter manifest JSON`
4. `preset token JSON`
5. `binding options JSON`

Статус:

- выполнено отдельно в `LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md`.