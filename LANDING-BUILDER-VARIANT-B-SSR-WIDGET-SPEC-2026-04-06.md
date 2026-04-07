# Variant B: SSR Runtime через один виджет (без takeover-магии)

Дата: 2026-04-06

## Decision

Мы фиксируем **Variant B** как целевую архитектуру runtime для чистого `nordicbuilder`:

- Landing/Builder-страница — это **обычная страница InstantCMS**.
- Builder-контент на live выводится **одним виджетом** (пример: `nordicbuilder.render`) в зоне `content_body`.
- Preview/live используют **один и тот же Renderer** и один и тот же контракт документа страницы.
- Тема (`templates/nordic/main.tpl.php`) **не содержит условий** вида “если builder/takeover — рендерить иначе”.

Критерии:
- SEO: live отдаёт SSR HTML + корректный `<head>`.
- Speed: live читает готовый опубликованный render (или кэш), без тяжёлых JS-бандлов “на всякий случай”.
- Parity: preview == publish == live (разница только в источнике данных: draft/published).

## Canon: Zone ownership (жёсткое правило)

В Variant B действует правило владения зонами: **builder управляет только `content_body`**, а все остальные зоны принадлежат теме/InstantCMS и не переопределяются builder‑ом.

- Builder: только `content_body` (один виджет `nordicbuilder.render`).
- Theme/InstantCMS: header/footer/sidebars и любые shell‑слоты.
- Запрещено: takeover/hybrid runtime, подавление сторонних зон, инклюд preview‑шаблона внутрь body.

## Canon: как живет тело сайта (shell/body)

`<body>` и shell‑структура страницы рендерятся темой как обычно. Builder влияет только на содержимое `content_body`, где виджет `nordicbuilder.render` вставляет опубликованный SSR HTML и выставляет SEO meta через `cms_template`.

## Canon: root namespace wrapper (изоляция стилей)

Весь HTML, который `nordicbuilder.render` вставляет в `content_body`, должен иметь один корневой wrapper с namespace‑классом (пример: `.nb-runtime`). Это обязательное правило для отсутствия конфликтов между стилями темы/виджетов InstantCMS и стилями landing‑блоков.

- Wrapper пример:

  `<div class="nb-runtime" data-nb-page-key="{page_key}" data-nb-render-hash="{hash}"> ... </div>`

- CSS правило:
  - все стили component library/блоков начинаются с `.nb-runtime`;
  - запрещены глобальные селекторы без namespace (например `h1 {}`, `.container {}`, `body {}`) в стилях builder‑контента.

---

## Page Key strategy (URL-based)

**Ключ страницы** (`page_key`) — это нормализованный URI текущего запроса (без query string).

- Базовое значение: `page_key = $core->uri` (InstantCMS), приведённый к строке.
- Нормализация:
  - удалить ведущие `/`;
  - пустой URI (главная) -> `homepage`;
  - разрешённые символы: `[a-z0-9_:/.\-]`, остальные заменяются на `-` (как `sanitizeDocumentKey()`).

Пример:
- `/` -> `homepage`
- `/pages/about.html` -> `pages/about.html`
- `/catalog/item-123?ref=ad` -> `catalog/item-123`

**Почему так:**
- не требуется ручная настройка виджета для каждой страницы;
- ключ стабилен, переносим, прозрачен для диагностики;
- минимизирует риск расхождений preview/live.

---

## Canonical contract: PageDocument

Каноническая структура документа страницы — `nordicbuilder.page` (см. контракт `page-document`).

### Минимально необходимое (runtime-critical)

```
{
  "kind": "nordicbuilder.page",
  "schema_version": "1.0",
  "key": "homepage",
  "title": "...",
  "page_type": "standalone",
  "editor_mode": "canvas",
  "meta": {
    "seo": {
      "title": "...",
      "description": "...",
      "canonical": "https://site/...",
      "robots": "index,follow",
      "og": {
        "title": "...",
        "description": "...",
        "image": "https://.../image.jpg"
      }
    }
  },
  "theme": { "template_preset": "..." },
  "layout": { "layout_mode": "sections", "content_slot": "content_body" },
  "shell_slots": ["content_body"],
  "zones": [
    {
      "zone_key": "content_body",
      "title": "Основное содержимое",
      "sections": [
        {
          "uid": "section-hero",
          "title": "...",
          "layout": "1col",
          "section_type": "hero",
          "style_preset": "hero",
          "background_tone": "base",
          "container_preset": "standard",
          "spacing_preset": "xl",
          "visibility": {"desktop": true, "tablet": true, "mobile": true},
          "settings": {"css_class": ""},
          "columns": [
            {
              "uid": "section-hero-column-1",
              "title": "...",
              "visibility": {"desktop": true, "tablet": true, "mobile": true},
              "width": {"desktop": "auto", "tablet": "auto", "mobile": "auto"},
              "settings": {"align": "stretch", "css_class": ""},
              "nodes": [
                {
                  "uid": "node-1",
                  "type": "block",
                  "label": "...",
                  "source_key": "core.hero-heading",
                  "device_visibility": {"desktop": true, "tablet": true, "mobile": true},
                  "options": {
                    "eyebrow": "...",
                    "title": "...",
                    "text": "..."
                  }
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

### Важные инварианты

- `key` = `page_key` из URL-strategy.
- `zones[].zone_key` соответствует shell-зоне (для Variant B обычно достаточно `content_body`).
- Узлы рендерятся по `source_key` через manifest/registry (например `core.hero-heading`).
- `options` строго типизированы и валидируются через manifest props.

---

## Published snapshot и render cache

### Хранилище (логическое)

- Draft хранится как JSON (`PageDocumentDraft`).
- Publish создаёт неизменяемую версию (`PagePublishVersion`) и готовый SSR результат (`PageRender`).

### Что хранит PageRender

- `html` — готовая разметка для вставки в body.
- `meta_json` — вычисленные мета-значения.
- `assets_json` — список CSS/JS для блоков (если используется точечная загрузка).
- `render_hash` — hash от нормализованного документа + token set.

### Правило live

- Live-виджет читает **только опубликованный** `PageRender` по `page_key`.
- Если публикации нет:
  - гость: ничего не выводим или выводим стандартный контент страницы;
  - админ: показываем короткую подсказку (без красных ошибок).

---

## Renderer: один код для preview/publish/live

Renderer pipeline:

1) validate(PageDocument, schemas/manifests)
2) normalize(defaults, tokens)
3) render HTML
4) collect meta
5) collect assets

- Preview: рендерим draft, не пишем в publish cache.
- Publish: рендерим и пишем `PageRender`.
- Live: читаем `PageRender` и отдаём.

Главная гарантия: **никаких отдельных веток шаблонов для preview/live**.

---

## SEO: как виджет заполняет <head>

InstantCMS поддерживает добавление тегов в head через `cms_template->addHead()` и установку SEO полей через `cms_template->setPageTitle()/setPageDescription()/setMeta()`.

Правило:

- После загрузки `PageRender.meta_json` виджет:
  - выставляет title/description;
  - добавляет canonical и robots через `addHead(<link...>)` / `addHead(<meta...>)`;
  - опционально добавляет OG-теги.

Fallback (если нужно): при publish синхронизировать title/description в сущность страницы InstantCMS, но canonical/robots/og всё равно можно держать в `addHead`.

---

## Assets policy (быстро и безопасно)

MVP:
- один общий CSS bundle темы + minimal CSS для блоков;
- JS подключается только там, где реально нужен (progressive enhancement).

Опционально позже:
- `assets_json` на уровне PageRender для точечного подключения блоковых ресурсов.

Запрещено:
- подключать много сторонних библиотек «всем страницам» как в OneBuilder `start`.

---

## Vertical Slice: минимальные блоки

Чтобы закрыть MVP-loop (1 page → 2 sections → ≤5 blocks → save/load → preview → publish → live), достаточно следующих `source_key`:

- `core.hero-heading` (eyebrow/title/text)
- `core.hero-actions` (title/text + labels)
- `core.cards-grid` (title/text + items_text → список)
- `core.feature-list` (title + items_text → список)
- `custom.raw-block` (title/text) — временный универсальный блок

Все эти блоки уже фигурируют в текущем starter документе `buildVerticalSliceStarterDocument()` и согласованы с моделью `zones/sections/columns/nodes`.

---

## Не-цели (чтобы не расползалось)

- Нет takeover/hybrid overlay режимов в runtime.
- Нет “start widget”, который глобально инжектит десятки JS/CSS библиотек.
- Нет отдельного page-mode на фронте: режимы — исключительно editor concern.
