# NordicBlocks Visual CSS MVP Implementation Plan

Дата: 2026-04-19

## 1. Назначение документа

Этот документ переводит архитектурную идею Visual CSS overlay в прикладной план внедрения.

Его задача:

1. зафиксировать, какие backend actions действительно нужны в MVP;
2. перечислить новые JS-модули и их физические точки в коде;
3. назвать точные файлы, которые надо менять в первом проходе;
4. определить checkpoint и rollback-точку перед стартом;
5. определить SQL-минимум без лишней переделки editor/runtime.

## 2. Решение по старту

Стартовать нужно в два шага, а не одной большой переделкой.

### MVP-A

Цель: доказать подход на одном блоке без новой persistence-схемы.

Scope:

1. целевой блок: `hero_panels_wide`;
2. live preview CSS внутри текущего `editor_hero_v2`;
3. overlay применяется только в iframe canvas;
4. overlay draft живёт в JS state редактора;
5. сохранение block contract остаётся как есть;
6. SQL не нужен.

### MVP-B

Цель: добавить хранение и повторное открытие CSS overlay.

Scope:

1. отдельное backend state/save API для CSS overlay;
2. одна таблица current-state как минимальный persistence слой;
3. revisions можно отложить на следующий шаг, но лучше предусмотреть отдельной таблицей.

### MVP-C

Цель: разделить saved draft и published runtime, добавить selector-safe presets и явный publish UX.

Scope:

1. `block_css_save` обновляет только draft document;
2. `block_css_publish` отдельно продвигает draft в runtime layer;
3. editor canvas стартует с draft CSS, public runtime/page/widget стартуют только с published CSS;
4. presets хранятся как target-key maps по разрешённым `allowedTargets`, без произвольных selector injections;
5. editor показывает diff draft vs published на уровне `targetCss`, а не сырых compiled selectors.

### MVP-D

Цель: добавить историю saved draft revisions и безопасный restore выбранной revision обратно в draft layer.

Scope:

1. revisions хранятся отдельно от current/published row в `cms_nordicblocks_block_css_revision`;
2. каждая успешная операция `block_css_save` пишет snapshot нормализованного draft document с номером version;
3. `block_css_restore` не трогает runtime напрямую, а восстанавливает выбранную revision как новый draft с новым version;
4. editor показывает список последних revisions и позволяет вернуть их в draft перед отдельным publish.

## 3. Backend Actions

## 3.1 Что делаем в MVP-A

В первом проходе новые backend actions не обязательны.

Причина:

1. текущий `block_editor_state` уже отдаёт editor payload;
2. текущий `block_canvas` уже умеет двусторонний `postMessage` bridge;
3. самый дешёвый MVP делается добавлением live CSS transport, а не новым save API.

### Изменяемые существующие backend actions в MVP-A

| Action | Файл | Что меняется |
| --- | --- | --- |
| `block_edit` | `system/controllers/nordicblocks/backend/actions/block_edit.php` | Прокидывает флаги CSS overlay в shell и, при переходе к MVP-B, URLs для CSS state/save |
| `block_editor_state` | `system/controllers/nordicblocks/backend/actions/block_editor_state.php` | Отдаёт `cssOverlay` metadata: block type, scope selector, allowed entity targets, стартовый preset target map |
| `block_canvas` | `system/controllers/nordicblocks/backend/actions/block_canvas.php` | Добавляет style-tag overlay и обработку `postMessage` типов `css:set` и `css:clear` |

## 3.2 Какие новые backend actions заводим в MVP-B

Если после MVP-A preview признан удачным, следующий минимальный набор actions такой.

| Новый action | Файл | Метод | Назначение |
| --- | --- | --- | --- |
| `block_css_state` | `system/controllers/nordicblocks/backend/actions/block_css_state.php` | GET | Возвращает текущий сохранённый CSS overlay для блока, version и target metadata |
| `block_css_save` | `system/controllers/nordicblocks/backend/actions/block_css_save.php` | POST JSON | Сохраняет текущий CSS overlay и возвращает нормализованный сохранённый документ |
| `block_css_publish` | `system/controllers/nordicblocks/backend/actions/block_css_publish.php` | POST JSON | Продвигает saved draft в published runtime layer |
| `block_css_revisions` | `system/controllers/nordicblocks/backend/actions/block_css_revisions.php` | GET | Возвращает список последних saved draft revisions для блока |
| `block_css_restore` | `system/controllers/nordicblocks/backend/actions/block_css_restore.php` | POST JSON | Возвращает выбранную revision обратно в draft layer с новым version |

### Минимальный payload для `block_css_state`

```json
{
  "ok": true,
  "block": {
    "id": 118,
    "type": "hero_panels_wide"
  },
  "cssOverlay": {
    "enabled": true,
    "scopeSelector": "[data-nb-block-root=\"hero_panels_wide\"]",
    "allowedTargets": ["title", "body", "accentSurface", "bodySurface"],
    "presets": [
      {
        "key": "editorial-punch",
        "label": "Editorial Punch",
        "description": "Быстрый старт для плотной журнальной типографики.",
        "targetCss": {
          "title": "font-size: clamp(3.4rem, 5.4vw, 5.2rem);\nletter-spacing: -0.05em;"
        }
      }
    ],
    "targetCss": {
      "title": "font-size: clamp(3rem, 5vw, 4.5rem);\nletter-spacing: -0.04em;"
    },
    "cssText": "[data-nb-block-root=\"hero_panels_wide\"] [data-nb-entity=\"title\"] {font-size: clamp(3rem, 5vw, 4.5rem);\nletter-spacing: -0.04em;}",
    "version": 0
  }
}
```

### Минимальный payload для `block_css_save`

```json
{
  "targetCss": {
    "title": "font-size: clamp(2.8rem, 5vw, 4.5rem);"
  },
  "version": 3
}
```

### Минимальный payload для `block_css_publish`

```json
{
  "version": 3
}
```

### Implementation note for MVP-B

В production-safe реализации persisted storage должен хранить не «готовый CSS для всего документа», а canonical target-document по разрешённым target keys.

Причина:

1. один и тот же `hero_panels_wide` может встречаться несколько раз на странице;
2. runtime scope должен компилироваться server-side под конкретный instance root, например `#block-<uid>`;
3. иначе одно сохранённое fine-tune правило потечёт на все блоки того же type.

Практически это означает:

1. в БД хранится сериализованный target map;
2. editor получает target map + compiled preview CSS под editor scope;
3. runtime/page/widget/canvas получают compiled CSS уже под instance-specific selector.

### Implementation note for MVP-C

В MVP-C runtime больше не должен читать тот же current document, который редактируется в shell.

Иначе explicit publish UX фиктивен: любой saved draft сразу утечёт в public runtime.

Поэтому production-safe схема такая:

1. одна row хранит draft document в `css_text` + `version`;
2. та же row хранит published document в `published_css_text` + `published_version`;
3. `block_canvas` использует draft document;
4. public `view` и widget runtime используют только published document;
5. SQL migration должна backfill-нуть существующие rows: `published_* := draft_*`, иначе после rollout runtime внезапно опустеет.

## 4. JS Modules

В текущем репозитории editor shell собран через tpl partials, а не через отдельный frontend bundle. Поэтому в MVP разумно добавлять JS-модули в том же стиле.

| Модуль | Физический файл | Назначение |
| --- | --- | --- |
| `nbhCssOverlayState` | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_css_overlay.tpl.php` | Хранит draft CSS overlay, dirty-state и target metadata |
| `nbhCssOverlayTransport` | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_css_overlay.tpl.php` | Отправляет CSS в iframe через `postMessage`, умеет `set` и `clear` |
| `nbhBuildCssOverlayRenderers` | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_renderers_css.tpl.php` | Рендерит UI fine-tune панели для `title`, `body`, `accentSurface`, `bodySurface` |
| `nbCanvasCssOverlayBridge` | `system/controllers/nordicblocks/backend/actions/block_canvas.php` | Принимает overlay CSS в iframe и вставляет его в отдельный `<style>` |
| `nbhCssOverlayPublishFlow` | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_css_overlay.tpl.php` | Управляет published state, publish button, preset apply и diff summary |
| `nbhCssOverlayRevisionFlow` | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_css_overlay.tpl.php` | Загружает revisions list и выполняет restore выбранной revision обратно в draft |

### Почему не отдельный frontend bundle на MVP

Потому что это увеличит объём внедрения без пользы для proof-of-concept.

На MVP важнее:

1. не ломать существующий `editor_hero_v2`;
2. не заводить лишнюю сборку;
3. встроиться в существующие control renderers и текущий canvas bridge.

## 5. Какие именно файлы менять в MVP

Ниже перечислены конкретные файлы первого прохода.

## 5.1 MVP-A: live code

| Статус | Файл | Что меняем |
| --- | --- | --- |
| modify | `system/controllers/nordicblocks/backend/actions/block_edit.php` | Прокидываем `cssOverlayEnabled`, а позже и URLs для CSS state/save |
| modify | `system/controllers/nordicblocks/backend/actions/block_editor_state.php` | Добавляем `cssOverlay` metadata в JSON payload |
| modify | `system/controllers/nordicblocks/backend/actions/block_canvas.php` | Вставляем отдельный overlay style-tag и поддержку `css:set` / `css:clear` |
| modify | `system/controllers/nordicblocks/libs/InspectorDefinitionRegistry.php` | Регистрируем design-panel key для fine-tune CSS именно под hero panels MVP |
| modify | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php` | Подключаем новый CSS overlay module, state и live transport |
| modify | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_dispatch.tpl.php` | Подключаем новый renderer partial |
| add | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_css_overlay.tpl.php` | Новый JS partial для state/transport overlay |
| add | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_renderers_css.tpl.php` | Новый renderer partial для fine-tune controls |
| modify | `system/controllers/nordicblocks/blocks/hero_panels_wide/render.php` | Добавляем стабильный block root scope marker, например `data-nb-block-root="hero_panels_wide"` |

## 5.2 MVP-A: package mirrors

После live-правок нужно синхронно править package mirror файлы.

| Статус | Файл |
| --- | --- |
| modify | `packages/nordicblocks/package/system/controllers/nordicblocks/backend/actions/block_edit.php` |
| modify | `packages/nordicblocks/package/system/controllers/nordicblocks/backend/actions/block_editor_state.php` |
| modify | `packages/nordicblocks/package/system/controllers/nordicblocks/backend/actions/block_canvas.php` |
| modify | `packages/nordicblocks/package/system/controllers/nordicblocks/libs/InspectorDefinitionRegistry.php` |
| modify | `packages/nordicblocks/package/system/controllers/nordicblocks/blocks/hero_panels_wide/render.php` |
| modify | `packages/nordicblocks/package/templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php` |
| modify | `packages/nordicblocks/package/templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_dispatch.tpl.php` |
| add | `packages/nordicblocks/package/templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_css_overlay.tpl.php` |
| add | `packages/nordicblocks/package/templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_renderers_css.tpl.php` |

## 5.3 MVP-B: persistence layer

Эти файлы не нужны для старта MVP-A, но понадобятся сразу после подтверждения preview flow.

| Статус | Файл | Что меняем |
| --- | --- | --- |
| add | `system/controllers/nordicblocks/backend/actions/block_css_state.php` | Отдельный CSS state endpoint |
| add | `system/controllers/nordicblocks/backend/actions/block_css_save.php` | Отдельный CSS save endpoint |
| add | `system/controllers/nordicblocks/backend/actions/block_css_revisions.php` | Список ревизий, если не откладываем их |
| modify | `system/controllers/nordicblocks/backend/actions/block_edit.php` | Даём shell URLs новых CSS endpoints |
| modify | `system/controllers/nordicblocks/model.php` | CRUD для current CSS overlay и ревизий |
| modify | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_css_overlay.tpl.php` | Загрузка persisted CSS и explicit save/revert |

## 5.5 MVP-C: draft/publish layer

| Статус | Файл | Что меняем |
| --- | --- | --- |
| add | `system/controllers/nordicblocks/backend/actions/block_css_publish.php` | Отдельный publish action для runtime layer |
| modify | `system/controllers/nordicblocks/model.php` | Разделяем draft и published document в одной row, добавляем presets и diff metadata |
| modify | `system/controllers/nordicblocks/backend/actions/block_canvas.php` | Canvas берёт именно draft overlay |
| modify | `system/controllers/nordicblocks/actions/view.php` | Public runtime остаётся только на published overlay |
| modify | `system/widgets/nordicblocks_page/widget.php` | Widget runtime остаётся только на published overlay |
| modify | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2.tpl.php` | Shell получает publish URL |
| modify | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_css_overlay.tpl.php` | publish state, preset apply, target diff |
| modify | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_renderers_css.tpl.php` | UI для presets, draft/published diff и publish button |

## 5.6 MVP-D: revisions history

| Статус | Файл | Что меняем |
| --- | --- | --- |
| add | `system/controllers/nordicblocks/backend/actions/block_css_revisions.php` | Отдаёт список последних draft revisions |
| add | `system/controllers/nordicblocks/backend/actions/block_css_restore.php` | Восстанавливает revision обратно в draft layer |
| modify | `system/controllers/nordicblocks/model.php` | Пишет revision snapshot на успешный save draft и умеет list/restore |
| modify | `system/controllers/nordicblocks/backend/actions/block_edit.php` | Передаёт revisions/restore URLs в editor shell |
| modify | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_css_overlay.tpl.php` | transport для revisions list + restore |
| modify | `templates/admincoreui/controllers/nordicblocks/backend/editor_hero_v2_control_renderers_css.tpl.php` | UI для истории revisions |

## 5.4 Какие файлы в MVP специально не трогаем

| Файл | Почему не трогаем |
| --- | --- |
| `system/controllers/nordicblocks/libs/BlockContractNormalizer.php` | Это compatibility bridge, а не preview engine |
| `system/controllers/nordicblocks/assets/blocks.css` | Общий runtime CSS не нужен для proof-of-concept overlay |
| `system/controllers/nordicblocks/backend/actions/editor_save.php` | Page-level save flow не относится к block-level CSS MVP |

## 6. Checkpoint перед стартом

Перед началом MVP нужен именно repo checkpoint со встроенным DB backup, а не ручная надежда на текущий dirty worktree.

Рекомендованная команда:

```bash
./scripts/pre-change-checkpoint.sh "nb visual css mvp-a start"
```

Что должно получиться:

1. новый snapshot tag вида `snapshot/YYYYMMDD-HHMMSS`;
2. DB backup в `backups/db/builders-YYYYMMDD-HHMMSS.sql.gz`;
3. rollback-ориентир в worklog на этот snapshot.

Почему этого достаточно:

1. `pre-change-checkpoint.sh` уже вызывает DB backup в режиме `required`;
2. задача затрагивает backend actions, editor shell и, начиная с MVP-B, SQL;
3. это соответствует действующему rollback flow репозитория.

## 7. SQL-минимум перед стартом

## 7.1 Для MVP-A

SQL не нужен.

Это принципиальное решение, а не упрощение ради вида.

Причина:

1. MVP-A доказывает только live preview transport;
2. draft CSS живёт в editor JS state;
3. если transport не взлетит, новая таблица только загрязнит production схему.

## 7.2 Минимум для MVP-B

Если persistence нужен сразу после MVP-A, минимально достаточно одной current-state таблицы.

```sql
CREATE TABLE cms_nordicblocks_block_css (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    block_id INT UNSIGNED NOT NULL,
    block_type VARCHAR(64) NOT NULL,
    scope_type VARCHAR(32) NOT NULL DEFAULT 'block',
    css_text MEDIUMTEXT NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    updated_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_block_id (block_id),
    KEY idx_block_type (block_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

В фактической реализации `css_text` может использоваться как serialized canonical target-document, а не как final CSS string. Final CSS должен компилироваться backend-ом на чтении под нужный scope.

Этого достаточно, чтобы:

1. хранить один актуальный CSS document на блок;

## 7.3 Минимум для MVP-C

Нужно расширить ту же таблицу published-слоем, а не заводить второй table только ради первой publish-итерации.

Минимум колонок:

```sql
ALTER TABLE cms_nordicblocks_block_css
    ADD COLUMN published_css_text MEDIUMTEXT NULL AFTER css_text,
    ADD COLUMN published_version INT UNSIGNED NOT NULL DEFAULT 0 AFTER version,
    ADD COLUMN published_by INT UNSIGNED DEFAULT NULL AFTER updated_by,
    ADD COLUMN published_at DATETIME DEFAULT NULL AFTER updated_at;
```

И обязательный backfill после rollout:

```sql
UPDATE cms_nordicblocks_block_css
SET
    published_css_text = css_text,
    published_version  = CASE WHEN TRIM(COALESCE(css_text, '')) <> '' THEN GREATEST(version, 1) ELSE published_version END,
    published_by       = COALESCE(published_by, updated_by),
    published_at       = COALESCE(published_at, updated_at)
WHERE COALESCE(published_version, 0) = 0
  AND TRIM(COALESCE(css_text, '')) <> '';
```
2. повторно открывать редактор и видеть те же fine-tune правки;
3. не трогать block contract table и не смешивать contract с freeform CSS.

## 7.3 SQL для ревизий

Если ревизии нужны сразу, добавляется вторая таблица.

```sql
CREATE TABLE cms_nordicblocks_block_css_revision (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    block_css_id BIGINT UNSIGNED NOT NULL,
    version INT UNSIGNED NOT NULL,
    css_text MEDIUMTEXT NOT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_block_css_id (block_css_id),
    KEY idx_block_css_version (block_css_id, version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

На production start это уже не минимум, а рекомендуемое расширение.

Для текущего rollout MVP-D это уже обязательная миграция, потому что editor UI и backend actions опираются на наличие history table.

## 8. Порядок выполнения

Рабочий порядок должен быть таким.

1. создать checkpoint через `scripts/pre-change-checkpoint.sh`;
2. сделать MVP-A без SQL и без новых save actions;
3. проверить manual live smoke на `hero_panels_wide` в editor: `title`, `body`, `accentSurface`, `bodySurface`;
4. если preview transport стабилен, перейти к MVP-B и только тогда добавлять SQL + `block_css_state`/`block_css_save`;
5. после live-кода синхронно обновить package mirror;
6. отдельной финальной проверкой убедиться, что обычный contract save/reload не сломан.

## 9. Критерий готовности MVP-A

MVP-A считается успешным, если одновременно выполняются все условия.

1. изменение fine-tune control обновляет iframe без autosave и без reload src;
2. scope ограничен только текущим block root;
3. target mapping не позволяет писать произвольный CSS по всему preview document;
4. существующий entity selection через `data-nb-entity` остаётся рабочим;
5. если overlay выключен или очищен, canvas возвращается к чистому SSR output без артефактов.