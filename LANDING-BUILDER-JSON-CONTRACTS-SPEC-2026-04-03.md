# Техспека: JSON contracts конструктора

## Статусы

- ⚪ Не начато
- 🟡 Запланировано
- 🔵 В работе
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Навигация

- Главный трекер: [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
- Предыдущий документ: [LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md)
- Уточняющий документ: [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md)
- Уточняющий документ: [LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md](LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md)
- Следующий документ: [LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md](LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md)

## 1. Цель документа

Этот документ фиксирует JSON contracts первой версии конструктора.

Он покрывает 5 ключевых сущностей:

1. `page schema JSON`
2. `block manifest JSON`
3. `adapter manifest JSON`
4. `preset token JSON`
5. `binding options JSON`

Задача документа не в том, чтобы описать PHP-код или SQL, а в том, чтобы зафиксировать стабильные форматы данных, которые потом можно будет:

- хранить в БД;
- валидировать;
- мигрировать между версиями;
- использовать в editor UI;
- использовать в runtime renderer.

## 2. На что опирается формат контрактов

### 🟢 Готово: базовые принципы зафиксированы

Формат опирается на 4 источника:

1. `LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md`
2. `LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md`
3. `LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md`
4. подход `instantcms-mcp-main/src/data/schemas.ts`

Из `instantcms-mcp-main` здесь особенно важен стиль описания schemas:

- явные required fields;
- строгие enum там, где scope конечен;
- extensible JSON-поля там, где система должна расти;
- отдельная версия schema для будущих миграций.

## 3. Общие правила для всех JSON contracts

### 🟢 Готово: общий каркас зафиксирован

Каждый JSON contract должен подчиняться одним и тем же правилам.

### 3.1. Обязательные общие поля

Для всех основных контрактов рекомендуется использовать общий паттерн:

```json
{
  "kind": "...",
  "schema_version": "1.0",
  "key": "...",
  "title": "...",
  "meta": {}
}
```

### 3.2. Значение полей

- `kind` определяет тип документа.
- `schema_version` определяет версию самого JSON-формата.
- `key` это стабильный системный ключ.
- `title` это человекочитаемое имя.
- `meta` это служебный расширяемый блок.

### 3.3. Общие правила совместимости

1. Новые optional поля можно добавлять в рамках той же major-версии.
2. Удаление или переименование required поля требует повышения major-версии.
3. Изменение enum semantics без обратной совместимости тоже требует новой major-версии.
4. Runtime должен явно проверять `schema_version` перед рендером или редактированием.

### 3.4. Общие ограничения

1. Не хранить в JSON абсолютные пути файловой системы.
2. Не хранить в JSON PHP callback names как единственный источник истины без stable ref.
3. Не смешивать UI state редактора и runtime state страницы в одном неструктурированном blob.
4. Не использовать поля вида `data1`, `data2`, `misc`.

## 4. Contract: `page schema JSON`

### 🔵 В работе: основной документ страницы зафиксирован

Этот контракт хранится в `landingbuilder_page_versions.schema_json`.

Он описывает одну конкретную версию страницы.

### 4.1. Назначение

Хранить:

- структуру секций;
- список блоков;
- props блоков;
- data bindings блоков;
- page-level token overrides;
- zone placement;
- runtime hints для renderer.

### 4.2. Рекомендуемая структура

```json
{
  "kind": "landingbuilder.page",
  "schema_version": "1.0",
  "key": "landing/summer-sale",
  "title": "Summer Sale",
  "page_type": "standalone",
  "editor_mode": "canvas",
  "adapter_key": "standalone_landing",
  "preset_key": "summer_dark",
  "meta": {
    "description": "Landing page for summer campaign",
    "preview_image": null,
    "tags": ["campaign", "summer"],
    "runtime": {
      "body_class": "lb-page-summer-sale"
    }
  },
  "seo": {
    "title": "Summer Sale",
    "description": "Музыкальная подборка и акции",
    "keywords": ["music", "sale", "summer"],
    "h1": "Summer Sale"
  },
  "layout": {
    "template": "nordic",
    "width_mode": "contained",
    "header_mode": "theme",
    "footer_mode": "theme",
    "content_slot": "content_body"
  },
  "shell_slots": [
    "site_top",
    "header_primary",
    "header_secondary",
    "hero",
    "before_content",
    "content_body",
    "content_sidebar_left",
    "content_sidebar_right",
    "after_content",
    "footer_primary",
    "footer_secondary"
  ],
  "tokens": {
    "preset_inherit": true,
    "overrides": {}
  },
  "zones": [
    {
      "zone_key": "content_body",
      "title": "Main canvas",
      "sections": [
        {
          "id": "sec_hero_001",
          "kind": "section",
          "section_type": "hero",
          "variant": "split-cover",
          "props": {},
          "blocks": [
            {
              "id": "blk_heading_001",
              "block_key": "core.hero-heading",
              "props": {
                "headline": "Музыка, которая цепляет",
                "subheadline": "Подборки, новинки, премьеры"
              },
              "data": {
                "mode": "manual"
              },
              "visibility": {
                "desktop": true,
                "tablet": true,
                "mobile": true
              }
            }
          ]
        }
      ]
    }
  ],
  "globals": {
    "actions": [],
    "assets": {
      "css": [],
      "js": []
    }
  }
}
```

### 4.3. Required fields

Обязательные поля верхнего уровня:

1. `kind`
2. `schema_version`
3. `key`
4. `title`
5. `page_type`
6. `editor_mode`
7. `meta`
8. `zones`

### 4.4. Enum значения

Для первой версии:

- `kind`: только `landingbuilder.page`
- `page_type`: `standalone`, `system_overlay`, `ctype_overlay`
- `editor_mode`: `canvas`, `overlay`
- `layout.template`: для текущей взрослой версии `nordic`
- `layout.width_mode`: `contained`, `wide`, `full`
- `layout.header_mode`: `theme`, `hidden`, `custom`
- `layout.footer_mode`: `theme`, `hidden`, `custom`
- `layout.content_slot`: для shell Нордик базовое значение `content_body`

### 4.4.1. Shell slots и zone keys для Нордик

Для шаблона `nordic` page schema должна уметь явно описывать, в какой shell slot попадает системное или builder-содержимое.

Минимальный канонический набор `shell_slots`:

1. `site_top`
2. `header_primary`
3. `header_secondary`
4. `hero`
5. `before_content`
6. `content_body`
7. `content_sidebar_left`
8. `content_sidebar_right`
9. `after_content`
10. `footer_primary`
11. `footer_secondary`

Практическое правило первой версии:

1. для `standalone_landing` основной builder zone по умолчанию это `content_body`;
2. для overlay-страниц нативное системное содержимое тоже маппится в `content_body`;
3. legacy-ключи `main`, `native_content` и `sidebar` считаются миграционными и в runtime нормализуются в `content_body` или `content_sidebar_right`.

### 4.5. Структура `zones`

Каждая zone должна содержать:

1. `zone_key`
2. `title`
3. `sections`

Каждая section должна содержать:

1. `id`
2. `kind`
3. `section_type`
4. `props`
5. `blocks`

Каждый block instance должен содержать:

1. `id`
2. `block_key`
3. `props`
4. `data`

### 4.5.1. Каноническая canvas-структура для секций и колонок

Для первой взрослой версии page schema должна поддерживать не только плоский список `blocks`, но и каноническую модель:

`section -> columns -> nodes`

Это нужно для:

1. 2- и 3-колоночных секций;
2. drag-and-drop между колонками;
3. responsive order и widths;
4. стандартных widgets на холсте.

Рекомендуемая структура section:

```json
{
  "id": "sec_features_001",
  "kind": "section",
  "section_type": "content-grid",
  "props": {},
  "layout": {
    "preset": "3col_equal"
  },
  "columns": [
    {
      "id": "col_1",
      "width": {
        "desktop": 4,
        "tablet": 6,
        "mobile": 12
      },
      "nodes": []
    }
  ]
}
```

Поле `blocks` можно сохранить как короткую форму только для простых single-column секций или для миграций ранних версий.

Дополнительно section рекомендуется хранить `zone_key`, совпадающий с shell slot или runtime zone:

```json
{
  "id": "sec_body_001",
  "kind": "section",
  "zone_key": "content_body",
  "section_type": "content-grid",
  "props": {},
  "layout": {
    "preset": "3col_equal"
  },
  "columns": []
}
```

### 4.5.2. Contract: `node instance`

Каждый canvas node должен содержать минимум:

1. `id`
2. `node_kind`
3. `visibility`

Допустимые `node_kind` для MVP:

1. `block`
2. `system_widget`

Пример block node:

```json
{
  "id": "node_blk_001",
  "node_kind": "block",
  "block_key": "core.hero-heading",
  "props": {},
  "data": {
    "mode": "manual"
  },
  "visibility": {
    "desktop": true,
    "tablet": true,
    "mobile": true
  }
}
```

Пример system widget node:

```json
{
  "id": "node_wd_001",
  "node_kind": "system_widget",
  "widget_ref": {
    "controller": "content",
    "name": "list"
  },
  "widget_options": {},
  "visibility": {
    "desktop": true,
    "tablet": true,
    "mobile": true
  }
}
```

### 4.6. Структура `data`

Для каждого block instance:

```json
{
  "mode": "manual",
  "source": null,
  "mapping": {}
}
```

Допустимые `mode`:

- `manual`
- `dynamic`
- `hybrid`

Рекомендуемые `source.type` для первой взрослой версии:

- `context.item`
- `context.list`
- `context.category`
- `context.profile`
- `ctype.list`
- `ctype.item`
- `ctype.related`
- `query.collection`

### 4.7. Что не хранить внутри page schema

1. Draft UI-состояние боковой панели.
2. Координаты мыши и временный drag state.
3. Кеш рендера HTML.
4. Информацию о конкретном SQL table name.
5. Bootstrap-классы как пользовательскую source of truth для колонок.

## 5. Contract: `block manifest JSON`

### 🔵 В работе: контракт реестра блоков зафиксирован

Этот контракт описывает тип блока, а не конкретный block instance на странице.

Он нужен для registry, editor library и runtime validation.

### 5.1. Рекомендуемая структура

```json
{
  "kind": "landingbuilder.block",
  "schema_version": "1.0",
  "key": "core.hero-heading",
  "title": "Hero Heading",
  "category": "Hero",
  "icon": "heading",
  "pack": {
    "key": "core",
    "version": "1.0.0"
  },
  "supports": {
    "modes": ["manual", "dynamic"],
    "page_types": ["standalone", "system_overlay", "ctype_overlay"],
    "zones": ["content_body", "hero", "before_content", "after_content", "content_sidebar_right"],
    "repeatable": true,
    "canvas_node_kinds": ["block"]
  },
  "render": {
    "type": "template_ref",
    "ref": "blocks/core/hero-heading"
  },
  "preview": {
    "type": "template_ref",
    "ref": "blocks/core/hero-heading-preview"
  },
  "props_schema": {
    "type": "object",
    "required": ["headline"],
    "properties": {
      "headline": {
        "type": "string",
        "maxLength": 180
      },
      "subheadline": {
        "type": "string",
        "maxLength": 400
      },
      "align": {
        "type": "string",
        "enum": ["left", "center", "right"]
      }
    }
  },
  "defaults": {
    "headline": "Заголовок",
    "subheadline": "Подзаголовок",
    "align": "left"
  },
  "data_contract": {
    "sources": ["manual", "context.item", "ctype.list", "query.collection"],
    "mapping": {
      "headline": ["item.title", "manual.headline"],
      "subheadline": ["item.description", "manual.subheadline"]
    },
    "collection": {
      "supported": true,
      "item_alias": "item"
    }
  },
  "meta": {
    "description": "Hero block with heading and subheading",
    "tags": ["hero", "heading", "content"],
    "deprecated": false
  }
}
```

### 5.2. Required fields

1. `kind`
2. `schema_version`
3. `key`
4. `title`
5. `category`
6. `supports`
7. `render`
8. `props_schema`
9. `defaults`
10. `meta`

### 5.3. Required rules

1. `key` должен быть globally unique в registry.
2. `defaults` должны проходить валидацию против `props_schema`.
3. Если `supports.modes` содержит `dynamic`, то `data_contract` обязателен.
4. Если `meta.deprecated = true`, блок не должен исчезать из runtime, только из library по умолчанию.
5. Если блок работает с коллекциями, это должно быть явно отражено в `data_contract.collection`.
6. Для builder block manifest `supports.canvas_node_kinds` должен включать `block`.

### 5.4. Почему manifest нужен отдельно от instance props

Потому что manifest отвечает за контракт типа блока:

- что он умеет;
- что он рендерит;
- какие у него props;
- какие data sources ему подходят.

А instance на странице отвечает только за конкретное наполнение.

## 6. Contract: `adapter manifest JSON`

### 🔵 В работе: контракт adapter layer зафиксирован

Этот контракт описывает page adapter, через который builder подключается к существующей странице или самостоятельной landing page.

### 6.1. Рекомендуемая структура

```json
{
  "kind": "landingbuilder.adapter",
  "schema_version": "1.0",
  "key": "content_item_generic",
  "title": "Generic content item adapter",
  "target_scope": "content_item",
  "pack": {
    "key": "core",
    "version": "1.0.0"
  },
  "supports": {
    "styling": true,
    "structural": true,
    "page_types": ["ctype_overlay"],
    "content_types": ["*"]
  },
  "matching": {
    "controller": "content",
    "actions": ["view"],
    "requires_ctype": true
  },
  "zones": [
    {
      "zone_key": "page_shell",
      "title": "Page shell",
      "mode": "styling",
      "required": true
    },
    {
      "zone_key": "before_content",
      "title": "Before content",
      "mode": "structural",
      "required": false
    },
    {
      "zone_key": "after_content",
      "title": "After content",
      "mode": "structural",
      "required": false
    }
  ],
  "runtime": {
    "handler_ref": "adapters/content/item/generic",
    "context_resolver": "content_item_context_v1"
  },
  "style_contract": {
    "token_groups": ["colors", "spacing", "typography", "cards", "buttons"]
  },
  "meta": {
    "description": "Adapter for standard InstantCMS content item pages",
    "deprecated": false
  }
}
```

### 6.2. Required fields

1. `kind`
2. `schema_version`
3. `key`
4. `title`
5. `target_scope`
6. `supports`
7. `matching`
8. `zones`
9. `runtime`
10. `meta`

### 6.3. Enum значения для `target_scope`

- `standalone`
- `homepage`
- `content_item`
- `content_list`
- `content_category`
- `user_profile`
- `system_page`

### 6.4. Правила

1. Если `supports.structural = true`, должен быть хотя бы один zone с `mode = structural`.
2. Если adapter работает только как styling overlay, `zones` всё равно обязательны, но они могут быть только styling-зонами.
3. `matching` не должен содержать runtime PHP-код, только declarative rules.
4. `runtime.handler_ref` должен быть stable logical ref, а не filesystem path.

## 7. Contract: `preset token JSON`

### 🔵 В работе: дизайн-контракт пресета зафиксирован

Этот контракт хранится в `landingbuilder_presets.tokens_json`.

Он описывает reusable token layer для страниц, zones и overlays.

### 7.1. Рекомендуемая структура

```json
{
  "kind": "landingbuilder.preset",
  "schema_version": "1.0",
  "key": "music_dark",
  "title": "Music Dark",
  "scope": "content_item",
  "inherits": ["core/base-dark"],
  "tokens": {
    "colors": {
      "page.bg": "#0d1016",
      "page.surface": "#161b24",
      "text.primary": "#f4f7fb",
      "text.muted": "#98a2b3",
      "accent.primary": "#ff7a18"
    },
    "typography": {
      "font.family.base": "Manrope",
      "font.family.heading": "Space Grotesk",
      "font.size.base": "16px",
      "font.weight.heading": 700
    },
    "spacing": {
      "section.py": "72px",
      "section.gap": "24px",
      "card.padding": "24px"
    },
    "radius": {
      "card": "24px",
      "button": "999px"
    },
    "shadow": {
      "card": "0 20px 60px rgba(0,0,0,0.22)"
    },
    "buttons": {
      "primary.bg": "#ff7a18",
      "primary.text": "#ffffff"
    }
  },
  "zone_overrides": {
    "page_shell": {
      "colors.page.bg": "#0a0d12"
    }
  },
  "meta": {
    "description": "Dark preset for music-oriented pages",
    "tags": ["dark", "music"],
    "deprecated": false
  }
}
```

### 7.2. Required fields

1. `kind`
2. `schema_version`
3. `key`
4. `title`
5. `scope`
6. `tokens`
7. `meta`

### 7.3. Scope enum

- `global`
- `landing`
- `homepage`
- `content_item`
- `content_list`
- `content_category`
- `user_profile`
- `ctype`

### 7.4. Правила для tokens

1. Token keys должны быть namespaced, например `page.bg`, `text.primary`, `button.primary.bg`.
2. Значения должны быть сериализуемы и пригодны для runtime token resolver.
3. `inherits` может быть пустым, но если он есть, merge order должен быть слева направо.
4. `zone_overrides` не должны заменять весь preset, только patch поверх него.

### 7.5. Что не делать

1. Не хранить в preset готовый HTML.
2. Не хранить в preset page structure.
3. Не класть туда бизнес-данные блоков.

## 8. Contract: `binding options JSON`

### 🔵 В работе: runtime options binding зафиксированы

Этот контракт хранится внутри `landingbuilder_bindings.options`.

Он не описывает весь binding целиком, потому что часть binding уже хранится в колонках SQL.

Он описывает только binding-specific runtime детали.

### 8.1. Рекомендуемая структура

```json
{
  "schema_version": "1.0",
  "matching": {
    "url_masks": ["news/*"],
    "exclude_masks": ["news/admin/*"],
    "route_params": {},
    "require_https": false
  },
  "zones": {
    "enabled": ["page_shell", "before_content", "after_content"],
    "readonly": ["content_fields_zone"],
    "hidden": []
  },
  "fallback": {
    "strategy": "theme",
    "on_missing_page": "theme",
    "on_missing_adapter": "theme",
    "on_missing_preset": "binding_default"
  },
  "preview": {
    "allow_preview": true,
    "requires_auth": true
  },
  "rules": {
    "allow_structural_overlay": true,
    "allow_dynamic_blocks": true,
    "allow_custom_css": false
  },
  "meta": {
    "note": "Homepage binding for composed front page"
  }
}
```

### 8.2. Required fields

1. `schema_version`
2. `matching`
3. `fallback`
4. `rules`

### 8.3. Enum значения для `fallback.strategy`

- `theme`
- `binding_default`
- `page_default`
- `disabled`

### 8.4. Правила

1. `binding options` не должны дублировать `binding_type`, `page_id`, `preset_id` и `adapter_key`, потому что это уже хранится в SQL колонках.
2. `matching.url_masks` и `exclude_masks` должны быть декларативными, без regex по умолчанию для MVP.
3. `zones.enabled` не может содержать zone, которой нет в adapter manifest.
4. Если `allow_structural_overlay = false`, binding не должен разрешать structural zones даже если adapter это умеет.

## 9. Рекомендуемая стратегия валидации

### 🟢 Готово: подход зафиксирован

Так как `instantcms-mcp-main` уже использует schema-first validation стиль, для builder лучше сразу закладывать похожую модель.

Рекомендуемая группа валидаторов:

1. `validatePageSchema(data)`
2. `validateBlockManifest(data)`
3. `validateAdapterManifest(data)`
4. `validatePresetTokens(data)`
5. `validateBindingOptions(data)`

### 9.1. Что проверять строго

Строго проверять:

1. required fields;
2. enum values;
3. типы полей;
4. уникальность `key` внутри registry;
5. совместимость `defaults` и `props_schema`;
6. совместимость `binding options` и `adapter zones`.

### 9.2. Что проверять семантически

Отдельной логикой проверять:

1. существует ли `adapter_key`;
2. существует ли `preset_key`;
3. разрешён ли block для указанного page type;
4. разрешён ли dynamic source для этого блока;
5. не использует ли page schema deprecated block без fallback.

## 10. Минимальный набор logical validator names

### 🔴 Запланировано

Для будущей реализации имеет смысл держать такие logical validator names:

1. `LandingBuilderPageSchema`
2. `LandingBuilderBlockManifest`
3. `LandingBuilderAdapterManifest`
4. `LandingBuilderPresetSchema`
5. `LandingBuilderBindingOptions`

Это ещё не PHP-классы и не TS-типы, а просто согласованный vocabulary для кода и документации.

## 11. Что уже можно считать зафиксированным

### 🟢 Готово

1. Все основные JSON documents должны иметь `kind`, `schema_version`, `key`, `title`, `meta`, кроме `binding options`, где `key/title` живут в SQL слое.
2. `page schema JSON` описывает page document версии и хранит sections, blocks, props и data bindings.
3. `block manifest JSON` описывает тип блока, а не его instance.
4. `adapter manifest JSON` описывает page adapter declaratively, без вшитого runtime PHP-кода.
5. `preset token JSON` хранит design tokens и zone overrides, но не HTML и не page structure.
6. `binding options JSON` хранит только runtime options binding-уровня, а не дублирует SQL колонки.
7. Все контракты должны быть versioned и пригодны для отдельной валидации.

## 12. Следующий логичный шаг

### 🔴 Запланировано

Следующий документ должен описать уже не контракты как форматы, а lifecycle этих документов:

1. как page schema создаётся и версионируется;
2. как block packs регистрируют manifests;
3. как adapters попадают в registry;
4. как presets наследуются и мерджатся;
5. как bindings разрешаются в runtime.

Статус:

- выполнено отдельно в `LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md`.