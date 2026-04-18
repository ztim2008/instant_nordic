# NordicBlocks — Документ разработки

> Версия: 2.3 | Дата: 2026-04-17 | Автор: проектная документация

---

## 1. Концепция

### Короткая статья-концепция компонента

NordicBlocks задуман не как второй page-builder поверх InstantCMS, а как аккуратное расширение самого InstantCMS. Это продукт для тех, кому нужны красивые современные секции сайта, но без слома привычной логики системы, без takeover-редактора и без отдельного сложного конструктора страниц.

Смысл компонента простой: в InstantCMS уже есть сильная механика размещения через Структуру, схемы и виджеты. NordicBlocks не заменяет этот путь, а усиливает его. Пользователь не собирает страницу с нуля на пустом холсте, а берёт готовый тип секции, настраивает его под задачу и добавляет в стандартную сетку InstantCMS как обычный виджет.

Логика работы компонента как продукта для InstantCMS должна восприниматься так:

1. В админке создаётся блок как самостоятельная сущность с названием, типом и настройками.
2. Блок редактируется в своём редакторе с живым preview и inspector-панелью.
3. После сохранения блок не публикуется сам по себе, а становится доступным для вставки в стандартную схему сайта.
4. В структуре InstantCMS пользователь добавляет обычный виджет NordicBlocks и выбирает в нём нужный блок.
5. На странице блок рендерится как нормальная SSR-секция и живёт в том месте сетки, куда его поставили.

В этом и есть главная продуктовая ценность NordicBlocks для InstantCMS:

1. не ломается привычный workflow контент-менеджера;
2. placement остаётся нативным для InstantCMS;
3. preview и live могут идти через один и тот же render-runtime;
4. компонент легче продавать как понятный addon, а не как отдельную сложную платформу внутри CMS.

То есть NordicBlocks как продукт для InstantCMS — это библиотека сильных секций с удобным редактором, но с нативным размещением через стандартные механизмы самой CMS. Пользователь получает современный визуальный уровень блоков, а разработчик и владелец проекта сохраняют предсказуемую архитектуру, SEO-совместимый SSR-вывод и нормальную поддержку в рамках экосистемы InstantCMS.

### Актуальный foundation-слой global design

С 2026-04-16 глобальная дизайн-система NordicBlocks переведена на canonical contract version 2.

Отдельный канонический документ:

- `docs/nordicblocks/GLOBAL-DESIGN-FOUNDATION-V2.md`

Если задача касается глобальных токенов, кнопок, радиусов, карточек, hover-механики или foundation для новых блоков, сначала опираться именно на этот документ.

**NordicBlocks — это набор независимых блоков-виджетов.**

Не конструктор страниц. Страницы строятся стандартным способом — через **Структуру (схему) InstantCMS**. NordicBlocks добавляет красиво оформленные секции (hero, features, CTA и т.д.) которые вставляются в эту схему как обычные виджеты.

```
Пользователь создаёт блок → настраивает в редакторе → добавляет виджет в схему InstantCMS
```

---

## 2. Поток работы

```
Компоненты → NordicBlocks → Список блоков
  ↓
[+ Создать блок]  →  выбрать тип (Hero, Features, CTA, ...)
  ↓
[Редактор]  →  iframe-превью + инспектор полей справа
  ↓
[Сохранить]  →  блок хранится в cms_nordicblocks_blocks (ID: 42)
  ↓
Структура → Схема → Добавить виджет "NordicBlocks: Блок"
  → опция «Блок» = выбрать из списка → «Hero — Главная страница»
  ↓
Виджет рендерит HTML+CSS → появляется на странице
```

### UX-принцип (зафиксировано)

Пользователь не собирает страницу с нуля в отдельном билдере.

1. Выбирает готовый тип блока в модальном окне (Hero, Features, CTA и т.д.)
2. Настраивает блок под себя в редакторе (iframe-превью + инспектор)
3. Вставляет блок как стандартный виджет в сетку InstantCMS (Структура → Схема)

Это сохраняет привычный workflow InstantCMS и снижает порог входа для контент-менеджера.

### Первая волна продукта (зафиксировано с 2026-04-17)

На этапе сборки unified inspector первой волны активный продуктовый поток NordicBlocks ограничен только двумя типами блоков:

1. `hero`
2. `faq`

Все остальные legacy-типы не считаются частью активной первой волны и выводятся из пользовательского backend-потока до стабилизации одного канонического editor shell.

С 2026-04-17 эти два типа считаются ещё и опорными manifest-референсами для редактора:

1. `hero` и `faq` больше не просто «разрешённые блоки», а первые реальные носители декларативного inspector manifest рядом со `schema` и `render`.
2. Активный editor flow должен собирать registry именно от этих manifest-описаний, а не от разрастающегося набора ручных `if block_type === ...`.
3. Добавление следующих разновидностей допустимо только после стабилизации этого manifest-first рельса.

## 2.1 Опора на instantcms-mcp-main (фиксируем как dev-гайд)

Подсказки из `/instantcms-mcp-main` считаем опорными при разработке NordicBlocks, чтобы не расходиться со стандартами InstantCMS.

Что это дает в реальной разработке:

1. Виджетный контракт и структура файлов
  - Используем подход из `src/tools/widget-tool.ts`: отдельные `widget.php`, `options/form`, cache key.
  - Это помогает сразу проектировать `nordicblocks_block` как «родной» виджет, а не кастомный обходной рендер.
2. Кэш и инвалидация
  - Используем логику из `src/tools/cache-tool.ts`: инвалидация после save/delete/publish-like событий.
  - Это снижает риск рассинхрона между редактором и live.
3. Layout/сетка
  - Ориентируемся на `src/tools/layout-tool.ts`: блок встраивается в существующие rows/cols/positions схемы.
  - Это подтверждает, что текущая идея «блок как виджет в стандартную сетку» архитектурно корректна.
4. Маршруты и действия
  - Фиксируем явные action endpoint-ы (без дублирующих названий), по аналогии с `routes-map`/controller actions.
5. DB и миграции
  - Таблицы и индексы проверяем через подходы `db-tool`/`migration-tool` перед внедрением SQL в компонент.

Итог: использование этих подсказок ускоряет разработку и уменьшает вероятность архитектурных ошибок.

## 2.2 Контракт вывода блока (без нового виджета)

Важно: отдельный новый виджет для размещения не создаём.

### Обновление 2026-04-16: Inspector Shell v2

Зафиксировано для текущих contract-first блоков `hero` и `faq`:

Подробный итог дня и список проверок вынесен в `docs/nordicblocks/WORKLOG-2026-04-16.md`.

1. Правая панель Inspector Shell v2 теперь скроллится корректно по высоте окна и не уходит за нижнюю границу браузера.
2. Для заголовка и подзаголовка добавлены реальные настройки показа/скрытия и отступа снизу отдельно для desktop и mobile.
3. Фон секции поддерживает режимы `theme`, сплошной цвет, градиент и фото с затемняющим overlay.
4. Эти настройки заведены в SSR-рендер preview/live, чтобы не было расхождения между редактором и боевым выводом.

Дополнение 2026-04-17:

1. Shell v2 закреплён по высоте окна, а правая панель получила собственную вертикальную прокрутку без скрытия нижних полей.
2. Пользовательские подписи inspector-а ведём на русском языке: названия секций, режимов, кнопок и подсказок не оставляем на английском.
3. Англоязычные значения допустимы только как внутренние технические ключи контракта и runtime, но не как текст интерфейса.

Используем один боевой виджет `nordicblocks_block` и один шаблон рендера блока `blocks/{type}/render.php` в трёх сценариях:

1. Редактор (iframe preview)
  - URL: `GET /admin/controllers/edit/nordicblocks/block_canvas/{block_id}`
  - Цель: быстрый визуальный preview в админке
  - Источник HTML: тот же `render.php` выбранного типа блока

2. Публикация через виджет в структуре InstantCMS
  - Виджет: `system/widgets/nordicblocks_block/widget.php`
  - Опция: `block_id`
  - Цель: боевой вывод блока на странице

3. Legacy page-view режим NordicBlocks
  - URL: `GET /nordicblocks/{page_key}`
  - Цель: обратная совместимость старого сценария страниц
  - Источник HTML: тот же `render.php` блоков

Единое правило: где бы блок ни показывался, рендер всегда идёт через `blocks/{type}/render.php`.

---

## 3. Схема данных

### Таблица `cms_nordicblocks_blocks` ← **НОВАЯ**

```sql
CREATE TABLE `cms_nordicblocks_blocks` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       VARCHAR(64)  NOT NULL,
    `title`      VARCHAR(255) NOT NULL DEFAULT '',
    `props_json` MEDIUMTEXT,
    `status`     VARCHAR(16)  NOT NULL DEFAULT 'active',
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Поля:**
| Поле | Описание |
|------|----------|
| `id` | Уникальный ID блока |
| `type` | Тип блока: `hero`, `features`, `text_section`, `cta` |
| `title` | Название для ориентации в админке: «Hero — Главная» |
| `props_json` | JSON с настройками `{ "heading": "...", "image": "..." }` |
| `status` | `active` / `disabled` |

### Существующие таблицы (вторичные)

| Таблица | Роль |
|---------|------|
| `cms_nordicblocks_design` | Дизайн-токены (цвета, шрифты) — остаётся |
| `cms_nordicblocks_pages` | Старая концепция «страниц» — не используется в новом подходе |
| `cms_nordicblocks_cache` | Кеш рендера блоков — остаётся |

---

## 4. Типы блоков

Каждый тип — папка в `system/controllers/nordicblocks/blocks/`:

```
blocks/
  hero/
    schema.json    — описание полей инспектора
    render.php     — SSR-рендер блока
  features/
  text_section/
  cta/
```

### Формат schema.json

```json
{
  "title": "Hero — Заголовок секции",
  "category": "hero",
  "version": "1.0",
  "fields": [
    { "key": "heading",   "label": "Заголовок",   "type": "text",     "default": "..." },
    { "key": "image",     "label": "Изображение", "type": "image",    "default": "" },
    { "key": "btn_url",   "label": "Ссылка",      "type": "url",      "default": "#" },
    { "key": "icon",      "label": "Иконка",      "type": "icon",     "default": "star" },
    { "key": "items",     "label": "Элементы",    "type": "repeater", "default": [],
      "fields": [
        { "key": "title", "label": "Заголовок", "type": "text", "default": "..." },
        { "key": "text",  "label": "Текст",      "type": "textarea", "default": "..." }
      ] },
    { "key": "theme",     "label": "Тема",        "type": "select",   "default": "light",
      "options": [{ "value": "light", "label": "Светлая" }] }
  ]
}
```

### Типы полей инспектора

| Тип | Виджет в инспекторе | Статус |
|-----|---------------------|--------|
| `text` | `<input type="text">` | ✅ |
| `textarea` | `<textarea>` | ✅ |
| `select` | `<select>` + `<option>` | ✅ |
| `color` | `<input type="color">` | ✅ |
| `image` | кнопка «Выбрать фото» → стандартный file picker InstantCMS | ⏳ |
| `url` | `<input type="text">` с кнопкой «Страница CMS» | ⏳ |
| `icon` | кнопка «Иконка» → сетка Font Awesome 5 Free | ⏳ |
| `repeater` | список карточек с вложенными полями, хранится как массив в `props_json` | ✅ |
| `richtext` | TinyMCE / встроенный WYS | 🔜 |

Для `repeater` правило простое: повторяющиеся элементы (`items`, `questions`, `steps`) не размазываем по `item1_*`, `item2_*`, а храним одним массивом внутри `props_json`.

---

## 5. Редактор блока

Файл: `templates/admincoreui/controllers/nordicblocks/backend/editor.tpl.php`

```
┌────────────────────────────────────────────────────────┐
│ [← Блоки]  «Hero — Главная»  [Desktop / Mobile]  [Сохранить] │
├───────────────────────────────────┬────────────────────┤
│                                   │  ИНСПЕКТОР         │
│         iframe: /admin/controllers/│  ─────────────     │
│         edit/nordicblocks/         │  Заголовок [___]   │
│         block_canvas/{block_id}    │                    │
│                                   │  Тема     [v  ]   │
│         (живой SSR-превью)        │  Изображение [📷]  │
│                                   │  Ссылка  [___] [🔗]│
│                                   │  Иконка  [⭐ ]    │
└───────────────────────────────────┴────────────────────┘
```

**Поведение:**
- Изменение любого поля → debounce 800ms → `POST /admin/controllers/edit/nordicblocks/block_save/{block_id}` → `reloadCanvas()`
- `Ctrl+S` → моментальное сохранение
- Кнопка Desktop/Mobile → iframe меняет max-width до 390px
- Сохранённые данные: `{ type, title, props_json }`

**Контракт сохранения (block_save):**
- Вход: `block_id`, `title`, `props_json`, `csrf_token`
- Сервер всегда валидирует `props_json` по `schema.json` выбранного типа блока
- Ошибка валидации возвращается как JSON с полем/причиной, без silent-save

---

## 6. Canvas endpoint (превью редактора)

`GET /admin/controllers/edit/nordicblocks/block_canvas/{block_id}`

Файл: `system/controllers/nordicblocks/backend/actions/block_canvas.php`

- Только для admin (иначе 403)
- Возвращает standalone HTML: `<style>tokens CSS + blocks CSS</style>` + rendered block HTML
- Используется как `src` iframe в редакторе

Примечание: старый frontend endpoint `/nordicblocks/canvas/*` не является каноничным для редактора.

---

## 7. Виджет `nordicblocks_block`

Файл: `system/widgets/nordicblocks_block/widget.php`

**Опции виджета:**
| Опция | Тип | Описание |
|-------|-----|----------|
| `block_id` | select | Выбор блока из cms_nordicblocks_blocks |

**Что делает виджет:**
1. Читает `block_id` из options
2. Загружает запись из `cms_nordicblocks_blocks`
3. Проверяет `status = active` (если `disabled` — возвращает пустой HTML)
4. Подключает `blocks/{type}/render.php`
5. Рендерит HTML
6. Возвращает `['html' => $html, 'inline_css' => $css]` (стандартный контракт InstantCMS)

**Кэширование виджета (обязательно):**
- Cache key: `nordicblocks:block:{block_id}:{updated_at}`
- Инвалидация при `block_save`, `block_delete`, смене `status`, обновлении токенов дизайна
- Если один и тот же блок вставлен несколько раз на странице — желательно request-level memoization, чтобы не делать повторный DB fetch в рамках одного запроса

Блок рендерится в том месте схемы, куда пользователь добавил виджет — заголовок страницы, до контента, сайдбар и т.д.

---

## 8. Backend-интерфейс (список блоков)

Маршрут: `Компоненты → NordicBlocks` — показывает список блоков (не страниц)

```
┌──────────┬────────────┬──────────┬────────────────────────┐
│ Тип      │ Название   │ Статус   │ Действия               │
├──────────┼────────────┼──────────┼────────────────────────┤
│ hero     │ Hero Главн │ active   │ [Редактировать] [✕]    │
│ features │ 3 пункта   │ active   │ [Редактировать] [✕]    │
│ cta      │ Подписка   │ disabled │ [Редактировать] [✕]    │
└──────────┴────────────┴──────────┴────────────────────────┘
[+ Создать блок]
```

**Backend actions (нужны/планируются):**

| Файл | Что делает |
|------|------------|
| `backend/actions/blocks.php` | Список всех блоков → `blocks.tpl.php` |
| `backend/actions/block_create.php` | POST: создать запись → redirect в редактор |
| `backend/actions/block_edit.php` | GET: открыть редактор по `block_id` |
| `backend/actions/block_save.php` | POST `/nordicblocks/block_save` (AJAX): сохранить `props_json`, `title` |
| `backend/actions/block_delete.php` | POST `/nordicblocks/block_delete` (AJAX): удалить запись |

Правило для боевого backend CRUD:

1. `block_create`, `block_save`, `block_delete` принимаются только от admin-сессии.
2. Все изменяющие запросы требуют валидный `csrf_token`.
3. V2-shell редактор и список блоков должны передавать `csrf_token` явно, без неявных допущений.

---

## 9. Интеграция с InstantCMS

### 9.1 Изображения

InstantCMS хранит загруженные файлы в `/upload/`. Для выбора из медиабиблиотеки:
- Открываем модальный попап — список файлов из `/upload/`
- После выбора — записываем относительный путь в поле `image` (`/upload/images/...`)
- В `render.php` подставляем как `<img src="<?= h($props['image']) ?>">` после санитайзации

### 9.2 Иконки

InstantCMS использует **Font Awesome 5 Free** (уже подключён в assets). Поле типа `icon`:
- В инспекторе: кнопка с текущей иконкой → попап-сетка FA-иконок
- Хранится строка: `"fas fa-rocket"` (full CSS class)
- В `render.php`: `<i class="<?= h($props['icon'] ?? 'fas fa-circle') ?>"></i>`

### 9.3 Ссылки

Поле типа `url`:
- Поле текстового ввода для произвольного URL
- Кнопка «Выбрать страницу» — открывает dropdown/модальное окно со страницами `cms_content`
- После выбора → автоматически вставляет URL страницы

---

## 10. Дизайн-система

Файлы: `system/controllers/nordicblocks/assets/tokens.css`, `blocks.css`
Копии: `templates/admincoreui/css/nb-tokens.css`, `nb-blocks.css`

CSS-переменные: `--nb-color-primary`, `--nb-color-bg`, `--nb-font-family`, `--nb-space-*`, и т.д.

Определяются в таблице `cms_nordicblocks_design` (preset + JSON-токены). Применяются как `<style>:root { --nb-color-primary:#... }</style>`.

---

## 11. Состояние реализации

### ✅ Готово

- 4 типа блоков: `hero`, `features`, `text_section`, `cta`
- Дизайн-система с 5 пресетами + токены CSS
- Canvas endpoint редактора работает в backend-режиме: `GET /admin/controllers/edit/nordicblocks/block_canvas/{block_id}`
- Iframe-редактор (editor.tpl.php) — топбар + iframe + инспектор
- Блок-пикер (модалка) с wireframe-превью и поиском
- Поля инспектора: text, textarea, select, color
- Виджет `nordicblocks_block` — рендер по `block_id`
- Кнопка в Компоненты (`is_backend = 1`)

### ⏳ Следующие шаги

1. **Быстрое размещение из редактора** — one-click bind в выбранную позицию
2. **Inspector: поле `image`** — медиабиблиотека (popup)
3. **Inspector: поле `url`** — текстовое поле + выбор страницы
4. **Inspector: поле `icon`** — FA-сетка
5. **Очистка legacy pages UI** — убрать вводящие в заблуждение следы старого сценария

### 🔜 Позже

- `richtext` поле (встроенный WYSIWYG)
- Дублирование блока
- Drag-to-pick FA иконки — расширенный поиск
- Предпросмотр в списке блоков (thumbnail)

---

## 12. Файловая структура (итоговая)

```
system/controllers/nordicblocks/
  frontend.php                   — GET /nordicblocks/{key} (legacy, redirect?)
  backend.php                    — Компоненты → NordicBlocks
  model.php                      — DB методы
  routes.php                     — маршруты
  actions/
    canvas.php                   — legacy canvas endpoint `/nordicblocks/canvas/*`
    view.php                     — фронтенд рендер (legacy)
  backend/
    actions/
      blocks.php                 — список блоков
      block_create.php           — создать блок
      block_canvas.php           — iframe preview `/admin/.../block_canvas/{id}`
      block_edit.php             — редактор блока
      block_save.php             — AJAX сохранение props
      block_delete.php           — AJAX удаление
      design.php                 — дизайн-токены
  blocks/
    hero/schema.json + render.php
    features/schema.json + render.php
    text_section/schema.json + render.php
    cta/schema.json + render.php
  assets/
    tokens.css
    blocks.css

system/widgets/
  nordicblocks_block/
    widget.php                   — рендер блока по block_id
  nordicblocks_page/             — legacy (страницы)
  nordicbuilder_render/          — дополнительный виджет

templates/admincoreui/controllers/nordicblocks/backend/
  blocks.tpl.php                 — список блоков
  editor.tpl.php                 — редактор с iframe
```

---

## 13. Архитектурная оценка и влияние на загрузку страницы

### Короткий вывод

Текущая архитектура выбрана правильно: «блок выбирается в модалке → настраивается → вставляется в стандартную сетку виджетом». Для InstantCMS это наиболее безопасный путь по совместимости и поддержке.

### Почему это архитектурно корректно

1. Не ломает базовый механизм InstantCMS (Структура/Схема/Виджеты)
2. Не требует отдельного runtime-конструктора страниц на фронте
3. SSR-рендер предсказуем для SEO и для одинакового preview/live поведения
4. Можно внедрять поэтапно, сохраняя legacy pages до полного перехода

### Повлияет ли на скорость загрузки

Сильно не повлияет при соблюдении 4 правил:

1. Включить кэш виджета с четкой инвалидацией
2. Не тянуть тяжелые JS-бандлы на публичную страницу (редакторный JS только в админке)
3. Дедуплицировать inline CSS и избегать повторного подключения одинаковых стилей
4. Минимизировать число SQL-запросов (request-level memoization + индекс по нужным полям)

При таком подходе основная нагрузка сопоставима с обычными кастомными виджетами InstantCMS.

### Какие есть альтернативы

1. Отдельный визуальный page-builder с собственным runtime
  - Плюс: больше визуальной свободы
  - Минус: сложнее поддержка, выше риск расхождения preview/live, тяжелее интеграция с текущей админкой
2. Полностью статическая сборка секций
  - Плюс: максимум скорости
  - Минус: хуже управляемость контентом из админки

Для твоей цели (рабочий, продаваемый addon под InstantCMS) текущий вариант с блоками-виджетами выглядит самым сбалансированным.

---

## 14. Операционный контур (production)

### Почему это нужно

Регистрация `nordicblocks_block` больше не делается в runtime внутри `backend/actions/blocks.php`.

Это убирает скрытую побочную логику из обычного запроса и делает поведение предсказуемым:
- установка/обновление отвечает за структуру БД и реестр виджетов;
- runtime отвечает только за рендер и CRUD.

### Синхронизация структуры (idempotent)

Скрипт: `scripts/nordicblocks-sync.php`

Проверка без изменений:

```bash
/opt/php84/bin/php scripts/nordicblocks-sync.php
```

Применение изменений:

```bash
/opt/php84/bin/php scripts/nordicblocks-sync.php --apply
```

Что синхронизируется:
1. Таблица `cms_nordicblocks_blocks`
2. Виджет `nordicblocks_block` (title: `NordicBlocks: блок`)
3. Legacy виджет `nordicblocks_page` (для обратной совместимости)

### Smoke-проверка потока

Скрипт: `scripts/nordicblocks-flow-smoke.php`

```bash
/opt/php84/bin/php scripts/nordicblocks-flow-smoke.php
```

Проверяет:
1. Создание блока
2. Сохранение props
3. Чтение блока после save
4. Наличие виджета `nordicblocks_block` в реестре
5. Очистку тестового блока

### Smoke-проверка FAQ `content_list` adapter

Скрипт: `scripts/nordicblocks-faq-content-list-smoke.php`

```bash
/opt/php84/bin/php scripts/nordicblocks-faq-content-list-smoke.php
```

Проверяет:
1. Создание временного FAQ-блока
2. Сохранение `data.listSource` в contract-first формате
3. Повторное чтение блока после save
4. Hydration через `DataSourceResolver + BindingMapper + BlockPayloadHydrator`
5. Подмену ручного fallback-списка реальными записями InstantCMS
6. SSR-рендер FAQ после hydration
7. Очистку тестового блока

### Перед применением на проде

1. Сделать backup БД
2. Сделать checkpoint файлов
3. Запустить sync `--apply`
4. Запустить flow-smoke

---

## 15. План v3 — следующая итерация NordicBlocks

### Легенда статусов

- 🟢 Сделано — уже реализовано и подтверждено в runtime
- 🟡 В работе — частично есть в коде, но контракт не завершён
- ⚪ Запланировано — ещё не реализовано, входит в следующий этап
- 🔴 Риск — место, которое может сломать масштабирование или усложнить поддержку

### 15.1 Цель v3

Собрать из NordicBlocks не просто библиотеку статичных секций, а взрослую блоковую систему для InstantCMS, где:

1. блоки размещаются только через стандартную сетку виджетов InstantCMS;
2. сам блок настраивается глубоко: фон, поверхность, типографика, spacing, кнопки, desktop/mobile overrides;
3. данные системы подключаются к блоку через адаптеры, а не копируются вручную в поля;
4. preview и live всегда используют один и тот же SSR-рендер;
5. один универсальный виджет `nordicblocks_block` остаётся единственной точкой вывода в структуру сайта.

### 15.2 Что берём из nordic-builder.ru, а что не копируем 1:1

#### 🟢 Берём как целевой уровень продукта

1. Инспектор уровня “по-взрослому”: стабильные группы настроек, понятные секции, desktop/mobile различия.
2. Богатую настройку блока: фон, контейнер, типографика, кнопки, surface-слой, repeaters.
3. Контракт сущностей внутри блока: title, subtitle, button, media, itemSurface и т.д.
4. Адаптеры данных и mapping данных системы в слоты блока.
5. Единый дизайн-системный слой вместо набора случайных локальных стилей.

#### ⚪ Не копируем 1:1

1. Отдельный page-builder runtime поверх InstantCMS.
2. Собственную сетку страниц поверх штатной схемы InstantCMS.
3. Полноценный on-canvas редактор всей страницы как обязательный сценарий.

#### Итоговое правило

NordicBlocks v3 = почти уровень nordic-builder.ru по качеству настройки блока, но с placement через штатный widget/layout механизм InstantCMS.

### 15.3 Архитектурный вектор v3

#### 🟢 Уже правильно

1. Один боевой виджет `nordicblocks_block`.
2. Один SSR-источник рендера: `blocks/{type}/render.php`.
3. Preview редактора и live должны собираться из одного контрактного render-слоя.

#### ⚪ Фиксируем как обязательную архитектуру v3

1. Placement остаётся в InstantCMS.
2. Редактор блока отвечает только за конфигурацию блока.
3. Адаптеры данных живут в NordicBlocks, а не в виджете InstantCMS.
4. Виджет не знает про внутреннюю механику блока, кроме `block_id`.

#### 🔴 Что нельзя допустить

1. Второй page-builder поверх штатной схемы InstantCMS.
2. Расхождение preview/live из-за отдельного JS-only runtime.
3. Рост `props_json` без формального контракта и миграций.

### 15.4 Контракт блока v3

#### 🟡 Текущее состояние

Сейчас блок в основном описывается как `type + title + props_json`, что подходит для MVP, но не подходит для зрелой системы сложных настроек.

#### ⚪ Целевой контракт

Каждый блок логически должен жить в такой структуре:

```json
{
  "meta": {
   "type": "hero",
   "title": "Hero — Главная",
   "status": "active",
   "version": 3
  },
  "content": {},
  "design": {},
  "layout": {
   "desktop": {},
   "mobile": {}
  },
  "data": {
   "bindings": {},
   "fallbacks": {}
  },
  "entities": {}
}
```

#### Смысл слоёв

1. `meta` — служебная информация блока.
2. `content` — ручной контент: тексты, кнопки, изображения, repeaters.
3. `design` — фон, цвета, типографика, surface, эффекты, стили кнопок.
4. `layout.desktop/mobile` — spacing, width, min-height, alignment, visibility.
5. `data` — адаптеры источников данных и mapping.
6. `entities` — внутренняя карта style slots и будущих quick edits.

#### DoD

- ⚪ Новый формат описан в документе как канонический.
- ⚪ Новые блоки создаются уже в логике `content/design/layout/data`.
- ⚪ Для старых блоков предусмотрен режим миграции из плоского `props_json`.

### 15.5 Схема полей и Inspector v2

#### 🟡 Текущее состояние

1. Уже есть schema-driven редактор.
2. Уже есть базовые типы полей: text, textarea, select, color, image.
3. Пока нет полноценного разделения по вкладкам и группам уровня продукта.

#### ⚪ Целевой Inspector v2

У редактора блока должны быть 4 верхнеуровневые вкладки:

1. Контент
2. Дизайн
3. Макет
4. Данные

Внутри вкладок нужны стабильные группы:

1. Фон
2. Контент
3. Медиа
4. Ссылки
5. Типографика
6. Кнопки
7. Surface / Контейнер
8. Spacing / Размеры
9. Data bindings

#### ⚪ Schema field v3 должен уметь описывать

1. `tab`
2. `section`
3. `group`
4. `responsive`
5. `bindable`
6. `unit`
7. `role`
8. `repeatable`
9. `visibility_depend`

#### Пример поля v3

```json
{
  "key": "titleDesktopPx",
  "label": "Размер заголовка — desktop",
  "type": "number",
  "tab": "design",
  "section": "typography",
  "group": "title",
  "responsive": "desktop",
  "unit": "px",
  "default": 64,
  "min": 12,
  "max": 200,
  "bindable": false
}
```

#### DoD

- ⚪ Появляется schema renderer v2.
- ⚪ Новый инспектор не превращается в одну длинную простыню.
- ⚪ Новые блоки собираются через единый IA-контракт.

### 15.6 Дизайн-система блока v3

#### 🟡 Текущее состояние

Есть базовые токены и темы секций, но пока не хватает уровня гибкости, похожего на nordic-builder.ru.

#### ⚪ Обязательные группы настроек

1. Фон секции
  - solid color
  - gradient
  - image
  - overlay color
  - overlay opacity
  - background position / size / repeat

2. Контейнер секции
  - content width preset
  - custom max width
  - min-height
  - vertical align
  - padding desktop
  - padding mobile

3. Типографика
  - title size desktop/mobile
  - subtitle size desktop/mobile
  - body size desktop/mobile
  - weight
  - line-height
  - letter-spacing
  - color
  - heading tag

4. Surface / карточки
  - bgColor
  - bgOpacity
  - borderColor
  - radius
  - shadow preset

5. Кнопки
  - label
  - url
  - style preset
  - text color
  - bg color
  - border color
  - radius
  - paddingX / paddingY
  - size desktop/mobile

#### ⚪ Правило v3

Новые блоки больше не должны ограничиваться только полями “heading + subheading + theme + button”.

#### DoD

- ⚪ У каждого нового продаваемого блока есть полноценные дизайн-настройки.
- ⚪ Desktop/mobile типографика управляется отдельно.
- ⚪ Поверхности и карточки оформляются через единый контракт, а не вручную в каждом render-файле.

Дополнительный зафиксированный вектор по локальному design-layer вынесен отдельно:

- `docs/nordicblocks/BLOCK-DESIGN-MANIFEST-V1.md`

### 15.7 Responsive и mobile overrides

#### 🔴 Проблема

Без desktop/mobile delta-модели блоки быстро упрутся в потолок: один размер шрифта и один spacing недостаточны для коммерческих секций.

#### ⚪ План

1. Ввести `layout.desktop` и `layout.mobile`.
2. Mobile хранить как дельту поверх desktop, а не как отдельную полную копию.
3. Для ключевых полей включить независимое управление:
  - font sizes
  - padding
  - gap
  - min-height
  - alignment
  - visibility

#### DoD

- ⚪ В редакторе можно управлять хотя бы spacing и typography отдельно для desktop/mobile.
- ⚪ Live и preview одинаково применяют mobile overrides.

### 15.8 Data Adapter v1 — интеграция данных InstantCMS

#### 🔴 Это главный следующий слой ценности

Без адаптеров NordicBlocks останется библиотекой статичных секций. Для InstantCMS этого недостаточно.

#### ⚪ Важное правило продукта: data adapters нужны не всем блокам

Начиная с 2026-04-17 фиксируем явное правило:

1. не каждый блок NordicBlocks обязан получать данные из системы;
2. adapter layer подключается только там, где это действительно даёт продуктовую ценность;
3. статичные продающие блоки могут оставаться полностью manual;
4. data-driven блоки строятся поверх того же контракта и того же SSR runtime, но с активным `data`-слоем.

Практически блоки делим на 3 класса:

1. `manual-first` блоки
  - Пример: преимущества, оффер, CTA, trust-блоки, декоративные промо-секции.
  - Правило: контент задаётся руками, adapter не обязателен.
2. `content-item` блоки
  - Пример: hero новости, hero статьи, cover записи, promo-блок одного объекта.
  - Правило: берут одну запись InstantCMS и маппят её в слоты блока.
3. `content-list` блоки
  - Пример: новости, статьи, кейсы, FAQ из записей, grid/list card blocks.
  - Правило: берут список записей и заполняют repeater/collection-слоты.

#### Зафиксировано для Hero `content_item` на 2026-04-18

Для Hero на одной записи фиксируем v1-правило по умолчанию:

1. рекомендуемый meta-набор для новости: `category`, `author`, `date`, `views`, `comments`;
2. `eyebrow` может использоваться как отдельный dynamic slot и по умолчанию хорошо подходит под `category.title`;
3. каждый meta-элемент можно скрыть пустой привязкой без пустого воздуха в секции;
4. meta-строка считается отдельной сущностью блока и должна иметь собственную типографику в инспекторе, а не хардкод в render;
5. если у типа контента есть bindable custom field, его можно присваивать в существующие слоты блока, если тип данных совместим.

Для текущего Hero runtime также зафиксировано:

1. inspector-поля типографики для `eyebrow`, `title`, `subtitle`, `meta` и `buttonsText` должны доходить до live preview и SSR отдельно для desktop и mobile;
2. inspector-поля `layout.desktop/mobile.contentGap` и `layout.desktop/mobile.actionsGap` считаются рабочими layout-контролами Hero, а не декоративными полями без эффекта;
3. если значение цвета текста не задано явно, Hero продолжает брать цвет из темы блока, а не ломает theme fallback.

Важно для первой волны:

1. текущая динамика в NordicBlocks остаётся `slot-based`, а не freeform как в Tilda collections;
2. это значит, что данные можно привязывать к каноническим слотам блока (`eyebrow`, `title`, `subtitle`, `image`, `meta`, `button url`), а не к произвольному тексту в DOM;
3. для старта этого достаточно для Hero, FAQ и будущих news card blocks;
4. свободную привязку «любой текст к любому полю» считаем следующей итерацией, а не обязательной частью первой волны.

Итоговое архитектурное правило:

1. единый runtime должен одинаково уметь обслуживать `manual`, `content_item` и `content_list`;
2. наличие `data` у блока является возможностью, а не обязательством;
3. отсутствие adapter-слоя не делает блок «устаревшим», если это правильно для его сценария.

#### ⚪ Обязательные источники v1

1. Manual
2. Current page
3. Current content item
4. Content list
5. Current user
6. Site settings

#### ⚪ Slot mapping v1

Блок должен уметь маппить источники данных в слоты:

1. `title`
2. `subtitle`
3. `description`
4. `image`
5. `imageAlt`
6. `primaryButton.label`
7. `primaryButton.url`
8. `items[]`
9. `badge`
10. `meta`

#### ⚪ Режимы binding

1. Manual — значение задаётся руками.
2. Bound — значение берётся только из адаптера.
3. Mixed — адаптер основной, ручное значение fallback.

#### ⚪ Для repeaters

Нужен адаптер списка:

1. content type
2. filters
3. sorting
4. limit
5. field mapping item-level
6. empty state

#### DoD

- ⚪ Можно привязать хотя бы hero и features к данным системы.
- ⚪ Можно собрать динамический список карточек из InstantCMS без ручного дублирования item1/item2/item3.

### 15.9 Repeaters вместо item1/item2/item3

#### 🔴 Проблема

Текущая модель `item1_*`, `item2_*`, `item3_*` в features-блоках неудобна для масштабирования, адаптеров данных и повторного использования.

#### ⚪ План

1. Ввести повторяемое поле `items[]`.
2. Для каждого item поддержать schema подслотов:
  - icon
  - title
  - text
  - image
  - link
3. Сделать редактор repeater-списка в инспекторе.

#### DoD

- ⚪ Новый grid/features блок не использует фиксированные item1/item2/item3.
- ⚪ Адаптер списка контента может напрямую наполнять repeater.

### 15.10 Контракт сущностей блока

#### ⚪ Вводим canonical entity keys

Для новых блоков фиксируем канонические сущности, похожие по смыслу на nordic-builder.ru:

1. `badge`
2. `title`
3. `subtitle`
4. `body`
5. `primaryButton`
6. `secondaryButton`
7. `mediaSurface`
8. `itemSurface`
9. `itemTitle`
10. `itemText`

#### Зачем это нужно

1. Чтобы типографика и style slots были предсказуемыми.
2. Чтобы быстрые правки можно было вводить позже без хаоса.
3. Чтобы один и тот же адаптер знал, куда подставлять системные данные.

#### DoD

- ⚪ Для новых блоков сущности называются одинаково.
- ⚪ Документ и schema contracts больше не плодят произвольные ключи без правила.

### 15.11 Runtime, performance и cache

#### 🟡 Уже частично правильно

1. SSR-рендер есть.
2. Widget placement через стандартную схему есть.
3. Базовая cache-идея уже зафиксирована.

#### ⚪ Что нужно дожать

1. Кэшировать block render по `block_id + updated_at + design_version + adapter_context_hash`.
2. Ввести request-level memoization для повторного рендера одного блока на странице.
3. Не тянуть редакторные assets в публичный runtime.
4. Подготовить точку расширения для data-adapter cache.

#### DoD

- ⚪ Один и тот же блок не делает лишние SQL/JSON операции в рамках одного запроса.
- ⚪ Динамические блоки с адаптерами не ломают страницу по производительности.

### 15.12 Legacy cleanup

#### 🟡 Текущее состояние

Legacy `pages` слой оставлен для совместимости, но не должен определять развитие продукта.

#### ⚪ План

1. Убрать legacy terminology из основного UX.
2. Спрятать legacy page-flow из главного сценария.
3. Оставить compatibility layer без активного расширения.

#### DoD

- ⚪ Главный сценарий продукта описывается только как block library + widget placement.

### 15.13 Порядок реализации v3

#### Этап 1

- 🟡 В работе: Placement и базовый block editor уже есть.
- 🟢 В документе зафиксированы Block Contract v3 и Adapter Contract v1.
- ⚪ Следующее: утвердить Inspector IA v2 как отдельный формальный UI-контракт.

Implementation-ready registry слой для этого этапа вынесен отдельно:

- `docs/nordicblocks/INSPECTOR-V2-IMPLEMENTATION-REGISTRY.md`

#### Этап 2

- ⚪ Реализовать schema renderer v2.
- ⚪ Разделить инспектор на Контент / Дизайн / Макет / Данные.
- ⚪ Добавить background + typography + spacing desktop/mobile.

#### Этап 3

- ⚪ Перевести новые блоки на repeaters.
- ⚪ Ввести canonical entity keys.
- ⚪ Поднять первые 2-3 donor hero-блока как reference implementation.

#### Этап 4

- ⚪ Реализовать Data Adapter v1.
- ⚪ Подключить Current content item и Content list.
- ⚪ Сделать первый динамический features/grid блок.

### 15.13.1 Зафиксированный вектор на 2026-04-17

На ближайший рабочий цикл фиксируем не расширение библиотеки в ширину, а добивку симметрии runtime.

#### Этап 1

- 🟢 Добить `content_item` adapter для hero.
- 🟢 Поднять одиночную запись через один и тот же hydration pipeline, который уже работает для FAQ `content_list`.
- 🟢 Минимальные slot-ы: `title`, `subtitle`, `image`, `imageAlt`, `date`, `views`, `comments`, `primaryButton.url`.

#### Этап 2

- 🟢 Обобщить вкладку `Данные`, чтобы она была не FAQ-специфичной, а общей для `content_item` и `content_list`.
- 🟢 Сохранить правило: если блок manual-first, data panel остаётся опциональной и не мешает ручному сценарию.

#### Этап 3

- 🟢 Дожат adapter-aware runtime hardening:
  - cache key теперь учитывает adapter context;
  - widget и legacy view используют единый render cache profile;
  - preview/live остаются на одном hydration/merge pipeline;
  - добавлен smoke для cache-context isolation, single-record и list-record сценариев.
- Подробный отдельный контур и итог этапа зафиксированы в `docs/nordicblocks/STAGE-3-RUNTIME-HARDENING.md`.

#### Этап 4

- ⚪ После стабилизации runtime поднять отдельный block class для вывода списка контента.
- ⚪ Это должен быть не второй FAQ, а более универсальный content-list block:
  - заголовок;
  - фото;
  - текст/анонс;
  - ссылка;
  - meta-поля при необходимости.

#### Почему выбран именно такой порядок

1. Сначала доказываем, что один runtime одинаково работает для одиночной записи и для списка.
2. Потом закрепляем общий inspector/data UI, а не плодим частные панели под каждый блок.
3. Только после этого поднимаем отдельный list-output block, чтобы он опирался уже на зрелый runtime, а не на временный код.

#### Этап 5

- ⚪ Дожать cache/invalidation/performance.
- ⚪ Упростить legacy слой.
- ⚪ Подготовить product-ready комплект блоков и reference docs.

### 15.14 Definition of Done для следующей итерации

Следующая итерация считается завершённой, если одновременно выполнены условия:

1. 🟢 Placement блока через стандартный виджет остаётся рабочим.
2. 🟢 В документе утверждён Block Contract v3.
3. 🟢 В документе утверждён Adapter Contract v1.
4. ⚪ В коде появляется schema renderer v2 или его стабильный промежуточный слой.
5. ⚪ Хотя бы один эталонный блок получает взрослые настройки:
  - фон
  - типографика desktop/mobile
  - spacing
  - кнопки
  - surface
6. ⚪ Хотя бы один блок получает bindable data slots.
7. ⚪ Поведение preview/live не расходится.
8. ⚪ Первая волна блоков ограничена 2-3 hero-вариантами, без расползания в большую библиотеку.

### 15.15 Рекомендация по продукту

Лучший путь развития NordicBlocks:

1. не строить второй page-builder поверх InstantCMS;
2. не усложнять placement;
3. усиливать именно внутренний контракт блока, инспектор и data adapters;
4. довести систему до уровня “как nordic-builder.ru по качеству блока”, но “как InstantCMS по placement-механике”.

---

## 16. Adapter Contract v1 — данные InstantCMS → слоты блока

### 16.1 Цель

Adapter Contract v1 нужен, чтобы любой блок NordicBlocks мог брать данные не только из ручных полей, но и из самой системы InstantCMS.

Главный принцип v1:

1. источником данных являются типы контента InstantCMS и их поля;
2. у каждого проекта могут быть свои content types и свои custom fields;
3. механика подключения должна быть универсальной и одинаковой для любого content type;
4. блок не знает заранее, какие именно поля есть у пользователя, он работает через общий adapter/mapping слой.

### 16.2 Легенда статусов

- 🟢 Сделано — уже реализовано и подтверждено
- 🟡 В работе — часть контракта есть, но без полного runtime
- ⚪ Запланировано — входит в Adapter Contract v1
- 🔴 Риск — место, где легко сломать универсальность или UX

### 16.3 Основное правило v1

#### ⚪ Канонический источник данных

В v1 главным источником считаем:

1. `content type`
2. `record` или `records`
3. `field map`

То есть адаптер работает не от конкретного компонента сайта, а от абстракции:

```text
Тип контента → запись / список записей → поля записи → slot mapping блока
```

#### 🔴 Что запрещено

1. Жёстко кодировать блок под один конкретный content type.
2. Делать отдельный adapter под каждый пользовательский ctype вручную.
3. Встраивать логику чтения полей прямо в `render.php` каждого блока.

### 16.4 Словарь Adapter Contract v1

#### Источник данных (`source`)

Определяет, откуда брать данные.

Поддерживаемые режимы v1:

1. `manual` — ручной ввод как сейчас
2. `content_item` — одна запись выбранного типа контента
3. `content_list` — список записей выбранного типа контента

#### Слот блока (`slot`)

Это именованная точка внутри блока, куда можно подставить данные:

1. `title`
2. `subtitle`
3. `description`
4. `image`
5. `imageAlt`
6. `date`
7. `views`
8. `comments`
9. `primaryButton.label`
10. `primaryButton.url`
11. `items[]`

#### Mapping rule

Правило, которое говорит:

1. из какого поля системы берём значение;
2. как его преобразуем;
3. куда в блок подставляем;
4. что делать, если значение пустое.

### 16.5 Источники данных v1

#### ⚪ Source: `manual`

Текущее поведение без адаптера.

Используется как:

1. базовый режим блока;
2. fallback для всех других adapter-режимов;
3. способ заполнить слоты, если binding не задан.

#### ⚪ Source: `content_item`

Используется, когда блок должен показать одну запись выбранного content type.

Примеры:

1. Hero с данными одной статьи
2. CTA с данными одной записи услуги
3. Cover-блок с данными конкретного кейса

Источник должен уметь выбрать запись в одном из режимов:

1. `current` — текущая запись страницы
2. `by_id` — запись по ID
3. `latest` — последняя запись выбранного типа
4. `by_filter` — первая запись по фильтру

#### ⚪ Source: `content_list`

Используется, когда блок должен показать список записей.

Примеры:

1. grid/features блок с карточками
2. лента статей
3. блок кейсов / услуг / отзывов

Источник должен поддерживать:

1. `ctype`
2. `filter`
3. `sort`
4. `limit`
5. `offset`
6. `with_pagination = false` на v1 уровне

### 16.6 Какие поля считаем доступными для mapping

#### ⚪ Системные поля записи

Любой adapter уровня `content_item` и `content_list` должен уметь видеть системные поля записи, если они реально существуют в данном типе контента:

1. `id`
2. `title`
3. `slug`
4. `url`
5. `date_pub`
6. `date_updated`
7. `hits_count` / просмотры
8. `comments_count`
9. `user_id`
10. `category_id`

#### ⚪ Пользовательские поля content type

Если пользователь создал свои поля в типе контента, они тоже должны быть доступны для mapping через тот же механизм.

Примеры:

1. `price`
2. `cover_image`
3. `short_text`
4. `gallery`
5. `address`
6. `phone`
7. `cta_text`
8. `cta_link`

#### ⚪ Runtime-мета поля

Дополнительно adapter может подмешивать вычисляемые поля:

1. `record_url`
2. `record_title`
3. `record_image_url`
4. `record_date_iso`
5. `record_date_human`
6. `record_comments_count`
7. `record_views_count`

### 16.7 Совместимость типов данных и слотов

#### ⚪ Базовая матрица совместимости

1. `text slot` ← text, textarea, string-like fields, formatted numbers, dates
2. `richtext slot` ← textarea/html-rich fields
3. `image slot` ← image/file field, photo field, gallery first image
4. `url slot` ← slug/url/link field, generated record URL
5. `number slot` ← numeric field, views, comments, counters
6. `items[] slot` ← content_list adapter only

#### 🔴 Правило совместимости

UI adapter-мэппинга не должен показывать пользователю все поля подряд.

Он должен фильтровать доступные поля по совместимому типу:

1. в слот картинки не предлагать произвольные textarea;
2. в слот числа не предлагать image;
3. в слот ссылки не предлагать несвязанные значения без formatter.

### 16.8 Режимы привязки слота

#### ⚪ Для каждого slot поддерживаем 3 режима

1. `manual`
   - слот заполняется только руками
2. `bound`
   - слот полностью берётся из adapter source
3. `mixed`
   - slot использует adapter как primary source, а ручное значение как fallback

#### Пример

```json
{
  "title": {
    "mode": "bound",
    "source": "content_item",
    "field": "title"
  },
  "subtitle": {
    "mode": "mixed",
    "source": "content_item",
    "field": "short_text",
    "fallback": "Ручной подзаголовок"
  }
}
```

#### ⚪ JSON-структура `data.bindings` в block contract

В v1 bindings должны храниться не россыпью по `props_json`, а в отдельном data-слое блока.

Минимальная структура:

```json
{
  "data": {
    "source": {
      "type": "content_item",
      "ctype": "articles",
      "resolver": {
        "mode": "current"
      }
    },
    "bindings": {
      "title": {
        "mode": "bound",
        "field": "title",
        "formatter": "plain_text",
        "emptyBehavior": "fallback"
      },
      "image": {
        "mode": "mixed",
        "field": "cover_image",
        "formatter": "image_url",
        "fallbackMode": "manual",
        "fallbackValue": "/upload/nordicblocks/demo/hero.jpg"
      },
      "date": {
        "mode": "bound",
        "field": "date_pub",
        "formatter": "date_human",
        "emptyBehavior": "hide"
      },
      "views": {
        "mode": "bound",
        "field": "hits_count",
        "formatter": "number",
        "emptyBehavior": "hide"
      },
      "comments": {
        "mode": "bound",
        "field": "comments_count",
        "formatter": "number",
        "emptyBehavior": "hide"
      },
      "icon": {
        "mode": "manual",
        "fallbackValue": "star",
        "ui": {
          "picker": "instantcms_icon_modal"
        }
      },
      "primaryButton.url": {
        "mode": "mixed",
        "field": "slug",
        "formatter": "record_url",
        "fallbackMode": "manual",
        "fallbackValue": "/catalog"
      }
    }
  }
}
```

#### ⚪ Обязательные ключи `data`

1. `source`
   - общий источник данных блока
2. `bindings`
   - slot-level mapping rules
3. `listSource`
   - отдельная конфигурация для `items[]`, если блок работает как список
4. `meta`
   - служебные версии контракта и runtime hints

#### ⚪ Обязательные ключи одного binding

1. `mode`
2. `field`
3. `formatter`
4. `fallbackMode`
5. `fallbackValue`
6. `emptyBehavior`
7. `ui` — только для editor/runtime hints, не для бизнес-логики

#### 🔴 Правило разделения ответственности

1. `source` описывает, какую запись или список читаем;
2. `bindings` описывает, как конкретные slot-ы заполняются из этих данных;
3. `ui` внутри binding не влияет на live-рендер и нужен только для редактора;
4. ручные значения фото и иконок тоже хранятся в `fallbackValue`, но выбираются штатным UI InstantCMS.

### 16.9 Mapping rules v1

#### ⚪ Базовый формат mapping rule

```json
{
  "slot": "title",
  "source": "content_item",
  "field": "title",
  "formatter": "plain_text",
  "fallbackMode": "manual",
  "fallbackValue": "",
  "emptyBehavior": "hide"
}
```

#### Поля mapping rule

1. `slot` — куда подставляем
2. `source` — откуда берём
3. `field` — имя поля записи
4. `formatter` — как приводим значение
5. `fallbackMode` — что делаем если пусто
6. `fallbackValue` — ручное значение, если нужно
7. `emptyBehavior` — hide / empty / fallback

#### ⚪ Formatters v1

Минимальный набор:

1. `plain_text`
2. `rich_text_safe`
3. `image_url`
4. `record_url`
5. `date_human`
6. `date_iso`
7. `number`
8. `first_image`
9. `first_non_empty`

### 16.10 Repeaters и списки записей

#### ⚪ Для slot `items[]` нужен отдельный mapping layer

Пример контракта:

```json
{
  "slot": "items",
  "source": "content_list",
  "ctype": "articles",
  "limit": 3,
  "sort": "date_pub_desc",
  "map": {
    "title": "title",
    "text": "short_text",
    "image": "cover",
    "url": "record_url",
    "date": "date_pub",
    "views": "hits_count",
    "comments": "comments_count"
  }
}
```

#### ⚪ Для каждого item доступны канонические sub-slots

1. `title`
2. `text`
3. `image`
4. `imageAlt`
5. `url`
6. `meta`
7. `date`
8. `views`
9. `comments`

#### DoD

- ⚪ Блок сетки может показать список записей из любого выбранного ctype.
- ⚪ Пользователь не настраивает item1/item2/item3 вручную.

### 16.11 UI Adapter Inspector v1

#### ⚪ Вкладка `Данные` должна содержать 4 шага

1. Выбор источника
   - manual / content item / content list
2. Выбор типа контента
3. Выбор записи или параметров списка
4. Mapping slot → field

#### ⚪ UX-правила

1. Пользователь сначала выбирает `ctype`.
2. После выбора система автоматически подгружает список доступных полей.
3. Для каждого slot показываются только совместимые поля.
4. Если binding пустой — блок продолжает работать на ручных значениях.
5. Пользовательский текст вкладки `Данные` и всего inspector-а показывается на русском; английские служебные ключи в интерфейс не выводим.

#### 🔴 Нельзя делать

1. Ручной ввод имени поля как основной сценарий.
2. Смешивать настройки дизайна и настройки адаптера в одной группе.
3. Требовать от пользователя знания внутренней структуры таблиц InstantCMS.

#### ⚪ Вкладка `Данные` должна иметь явные зоны UI

1. `Источник`
  - выбор режима: manual / content item / content list
2. `Тип контента`
  - выбор пользовательского ctype
3. `Запись или список`
  - current / by id / latest / filter / sort / limit
4. `Привязки`
  - slot → field
5. `Fallback`
  - ручные значения для mixed/manual режимов

#### ⚪ Штатные InstantCMS-модалки для image/icon

Фото и иконки не должны получать отдельный самодельный picker внутри NordicBlocks.

Нужно использовать стандартные механики InstantCMS:

1. для `image` / `imageAlt` / image-like fallback:
  - стандартное модальное окно выбора/загрузки изображения InstantCMS;
  - после выбора в binding пишется относительный путь или нормализованный asset URL;
2. для `icon` / icon-like fallback:
  - стандартное модальное окно выбора иконки InstantCMS;
  - после выбора в binding пишется канонический icon key / class name;
3. для `manual` и `mixed` режимов editor показывает рядом:
  - текущее значение;
  - кнопку `Выбрать фото` или `Выбрать иконку`;
  - кнопку очистки значения;
4. если слот работает в режиме `bound`, picker скрыт или переводится в секцию fallback.

#### ⚪ Правила UI для image/icon bindings

1. Если системное поле типа `image` привязано к slot `image`, редактор показывает preview текущего значения.
2. Если у slot `image` включён `mixed`, рядом показывается fallback-preview и кнопка штатного выбора фото.
3. Если slot `icon` работает вручную, значение выбирается только через штатную икон-модалку InstantCMS.
4. В `bindings.ui.picker` допустимы только hints уровня:
  - `instantcms_image_modal`
  - `instantcms_icon_modal`
5. Нельзя тащить в contract детали DOM, CSS selectors или JS-реализацию модалки.

#### ⚪ Пример editor binding для image/icon

```json
{
  "image": {
   "mode": "mixed",
   "field": "cover_image",
   "formatter": "image_url",
   "fallbackMode": "manual",
   "fallbackValue": "/upload/nordicblocks/fallback/cover.jpg",
   "ui": {
    "picker": "instantcms_image_modal"
   }
  },
  "icon": {
   "mode": "manual",
   "fallbackValue": "bolt",
   "ui": {
    "picker": "instantcms_icon_modal"
   }
  }
}
```

#### DoD

- ⚪ Вкладка `Данные` описана как отдельный сценарий, а не как абстрактная идея.
- ⚪ Для image/icon зафиксировано использование штатных InstantCMS-модалок.
- ⚪ Контракт не требует писать отдельный NordicBlocks media manager.

### 16.12 Runtime pipeline v1

#### ⚪ Этапы выполнения

1. Виджет получает `block_id`.
2. Загружается блок из `cms_nordicblocks_blocks`.
3. Читается `data.bindings`.
4. Adapter runtime получает source config.
5. Adapter runtime читает данные из выбранного content type.
6. Mapping engine собирает slot values.
7. Merge engine накладывает bound values на manual content.
8. `render.php` получает уже готовый payload.

#### Важно

`render.php` не должен сам заниматься поиском контента в базе. Он должен получать уже подготовленные значения.

### 16.13 Cache и invalidation для adapter blocks

#### ⚪ Cache key v1 должен учитывать

1. `block_id`
2. `block.updated_at`
3. `design.updated_at` или version
4. `adapter source config hash`
5. `adapter result identity`

#### Примеры

1. Для `content_item/by_id` в ключ должен входить `ctype + item_id`.
2. Для `content_list` в ключ должен входить `ctype + filter + sort + limit`.
3. Для `current item` в ключ должен входить контекст текущей страницы/записи.

#### 🔴 Риск

Если adapter-контекст не включать в cache key, один и тот же блок начнёт показывать чужие данные на других страницах.

### 16.14 Ограничения v1

#### ⚪ Осознанно не делаем в v1

1. сложные multi-source expressions
2. полноценный визуальный query builder
3. вложенные relation traversals любой глубины
4. кастомный JS formatter
5. on-canvas data mapping

#### Что достаточно для v1

1. один источник на slot;
2. один content type на adapter config;
3. простой список formatters;
4. predictable fallback behavior.

### 16.15 Definition of Done для Adapter Contract v1

Adapter Contract v1 считается принятым, если одновременно выполнены условия:

1. ⚪ Документ описывает универсальную механику через content types, а не через частные кейсы.
2. ⚪ Любой пользовательский ctype может быть выбран как источник данных блока.
3. ⚪ Системные поля и custom fields доступны в едином mapping UI.
4. ⚪ Hero-блок умеет брать `title`, `image`, `date`, `views`, `comments` из выбранной записи.
5. ⚪ Grid/features блок умеет брать `items[]` из списка записей.
6. ⚪ Preview и live используют одинаковый adapter runtime.
7. ⚪ Empty/fallback behavior формально описан и реализуем без догадок.

---

## 17. Block Contract v3 — полный JSON-контракт блока

### 17.1 Цель

Block Contract v3 нужен, чтобы блок перестал быть просто набором случайных `props` и стал нормальной структурой из слоёв:

1. контент;
2. дизайн;
3. макет;
4. данные;
5. сущности;
6. runtime-мета.

Главная идея:

1. editor редактирует один канонический JSON-контракт;
2. preview и live читают один и тот же контракт;
3. data adapters не внедряются в `render.php` напрямую, а входят в отдельный слой `data`;
4. image/icon остаются совместимыми со штатным UI InstantCMS.

### 17.2 Верхнеуровневая структура

#### ⚪ Канонические root-ключи

```json
{
  "meta": {},
  "content": {},
  "design": {},
  "layout": {},
  "data": {},
  "entities": {},
  "runtime": {}
}
```

#### ⚪ Значение каждого слоя

1. `meta`
   - версия контракта, тип блока, служебная информация
2. `content`
   - ручные контентные значения блока
3. `design`
   - цвета, типографика, поверхности, кнопки, фон
4. `layout`
   - контейнер, spacing, desktop/mobile overrides
5. `data`
   - adapter source, bindings, listSource
6. `entities`
   - canonical entity keys для style/data semantics
7. `runtime`
   - runtime hints, cache scope, feature flags

### 17.3 Минимальный пример Block Contract v3

```json
{
  "meta": {
    "contractVersion": 3,
    "blockType": "hero",
    "schemaVersion": 2,
    "label": "Hero Classic",
    "status": "active"
  },
  "content": {
    "badge": "Запуск за 7 дней",
    "title": "Соберите страницу под свой бизнес",
    "subtitle": "Блоки, стили и данные без takeover-магии",
    "description": "SEO-совместимый SSR runtime и стандартное размещение через виджеты InstantCMS.",
    "image": "/upload/nordicblocks/demo/hero.jpg",
    "imageAlt": "Превью hero-блока",
    "primaryButton": {
      "label": "Запустить",
      "url": "/catalog"
    },
    "items": []
  },
  "design": {
    "theme": "light",
    "background": {
      "type": "solid",
      "color": "#F4F0E8"
    },
    "typography": {
      "title": {
        "desktop": {
          "size": 64,
          "lineHeight": 1.05,
          "weight": 700
        },
        "mobile": {
          "size": 38
        }
      },
      "body": {
        "desktop": {
          "size": 18
        },
        "mobile": {
          "size": 16
        }
      }
    },
    "buttons": {
      "primary": {
        "stylePreset": "solid",
        "bgColor": "#1A1A1A",
        "textColor": "#FFFFFF",
        "radius": 999
      }
    }
  },
  "layout": {
    "container": {
      "widthPreset": "xl",
      "maxWidth": 1280
    },
    "desktop": {
      "paddingTop": 96,
      "paddingBottom": 96,
      "gap": 40,
      "align": "center"
    },
    "mobile": {
      "paddingTop": 56,
      "paddingBottom": 56,
      "gap": 24
    }
  },
  "data": {
    "source": {
      "type": "content_item",
      "ctype": "articles",
      "resolver": {
        "mode": "current"
      }
    },
    "bindings": {
      "title": {
        "mode": "bound",
        "field": "title",
        "formatter": "plain_text",
        "emptyBehavior": "fallback"
      },
      "image": {
        "mode": "mixed",
        "field": "cover_image",
        "formatter": "image_url",
        "fallbackMode": "manual",
        "fallbackValue": "/upload/nordicblocks/demo/hero.jpg",
        "ui": {
          "picker": "instantcms_image_modal"
        }
      }
    }
  },
  "entities": {
    "title": {
      "kind": "text",
      "styleSlot": "title"
    },
    "primaryButton": {
      "kind": "button",
      "styleSlot": "primaryButton"
    },
    "mediaSurface": {
      "kind": "media",
      "styleSlot": "mediaSurface"
    }
  },
  "runtime": {
    "renderMode": "ssr",
    "cacheScope": "page",
    "featureFlags": {
      "useAdapter": true,
      "useResponsiveOverrides": true
    }
  }
}
```

### 17.4 Правила по слоям

#### ⚪ `meta`

Обязательные ключи:

1. `contractVersion`
2. `blockType`
3. `schemaVersion`

Опциональные:

1. `label`
2. `status`
3. `preset`
4. `origin`

#### ⚪ `content`

Содержит только ручные значения контента.

Важно:

1. `content` не хранит информацию о том, откуда пришли данные;
2. `content` остаётся fallback-слоем даже при активном adapter binding;
3. ручные `image` и `icon` значения выбираются через штатный InstantCMS UI.

#### ⚪ `design`

Содержит только визуальные настройки:

1. background
2. typography
3. surfaces
4. buttons
5. colors
6. radius/shadow/border

#### ⚪ `layout`

Содержит только структурные настройки:

1. container width
2. alignment
3. gap
4. min-height
5. padding/margin
6. desktop/mobile delta

#### ⚪ `data`

Содержит:

1. общий источник данных;
2. slot bindings;
3. list source для repeater-слотов;
4. editor hints для image/icon pickers.

#### ⚪ `entities`

Нужен для канонических имён и будущей предсказуемости:

1. style slots;
2. data slots;
3. shared semantics между блоками.

#### ⚪ `runtime`

Содержит только runtime hints:

1. render mode;
2. cache scope;
3. feature flags;
4. compatibility flags.

#### 🔴 Что запрещено в контракте

1. Смешивать `design` и `data` в одном объекте.
2. Хранить SQL-логику или raw query в JSON-контракте.
3. Тащить в `ui` детали DOM, CSS selectors или JS callback names.
4. Писать случайные ключи без canonical entity names.

### 17.5 Правила хранения в текущем репозитории

#### 🟡 Переходный режим хранения

Пока в базе уже используется `props_json`, в переходной фазе Block Contract v3 можно физически хранить там же.

Правило перехода:

1. физически JSON может оставаться в `props_json`;
2. логически он уже считается полным block contract;
3. runtime должен читать его как `contract`, а не как плоский набор props;
4. будущий перенос в `contract_json` не должен менять editor/live API.

#### ⚪ Совместимость со старыми блоками

Если блок ещё старого формата, нужен compatibility adapter:

1. старые ключи вроде `heading`, `subheading`, `button_text` маппятся в `content`;
2. старые theme/padding ключи маппятся в `design` и `layout`;
3. отсутствие `data` трактуется как `manual` mode.

### 17.6 Merge-правило для preview/live

#### ⚪ Порядок сборки payload

1. Загружается block contract.
2. Читается `content` как manual base.
3. Если есть `data.source`, выполняется adapter runtime.
4. `data.bindings` накладываются поверх `content` по правилам `manual/bound/mixed`.
5. `design` и `layout` применяются как presentation layer.
6. `entities` используются как semantic map для render helpers.
7. `render.php` получает единый hydrated payload.

#### Важно

Preview и live обязаны проходить через один и тот же merge pipeline.

### 17.7 Definition of Done для Block Contract v3

Block Contract v3 считается принятым, если одновременно выполнены условия:

1. ⚪ У блока есть канонические слои `meta/content/design/layout/data/entities/runtime`.
2. ⚪ `content` и `data.bindings` не смешаны.
3. ⚪ Preview и live читают один и тот же контракт.
4. ⚪ Image/icon совместимы со штатными InstantCMS-модалками.
5. ⚪ Старый flat props-формат можно поднять через compatibility layer.

---

## 18. Реализация v1 — runtime adapter и вкладка `Данные`

### 18.1 Цель

Этот раздел фиксирует не идею, а рабочий техконтур, по которому можно начинать реализацию без догадок.

### 18.2 Минимальные runtime-модули

#### ⚪ Нужны 5 отдельных слоёв

1. `BlockContractReader`
   - читает JSON блока из текущего storage
2. `BlockContractNormalizer`
   - поднимает старый flat format в v3 shape
3. `DataSourceResolver`
   - получает запись или список записей выбранного ctype
4. `BindingMapper`
   - собирает slot values по `data.bindings`
5. `BlockPayloadHydrator`
   - сливает `content + adapter data + design/layout`

#### 🔴 Что не должен делать `render.php`

1. искать content type;
2. выполнять mapping полей;
3. решать fallback-логику;
4. знать, manual это слот или bound.

### 18.3 Runtime pipeline

#### ⚪ Канонический порядок вызовов

1. Widget/editor preview получает `block_id`.
2. `BlockContractReader` читает JSON.
3. `BlockContractNormalizer` приводит его к v3 shape.
4. `DataSourceResolver` получает данные InstantCMS по `data.source`.
5. `BindingMapper` строит hydrated slot values.
6. `BlockPayloadHydrator` собирает итоговый payload.
7. `render.php` рендерит только готовый payload.

#### ⚪ Обязательное правило

Один и тот же pipeline используется в:

1. backend preview iframe;
2. widget runtime;
3. legacy public view, пока он не удалён.

### 18.4 Вкладка `Данные` — UI-сценарий v1

#### ⚪ Экран разбивается на 5 зон

1. `Источник`
   - manual / content item / content list
2. `Тип контента`
   - список ctype из InstantCMS
3. `Режим выборки`
   - current / by id / latest / filter / sort / limit
4. `Привязки`
   - slot → field + formatter + empty behavior
5. `Fallback`
   - ручные значения для mixed/manual

#### ⚪ Поведение пользователя

1. Пользователь выбирает source mode.
2. Если mode не `manual`, система подгружает список content types.
3. После выбора ctype система подгружает поля.
4. Для каждого slot UI показывает только совместимые поля.
5. Для `mixed` появляется fallback control.
6. Для `manual` data-binding controls скрываются.

### 18.5 Источники данных для UI

#### ⚪ Editor должен уметь получить

1. список доступных content types;
2. список полей выбранного ctype;
3. список системных runtime fields вроде `record_url`, `comments_count`, `hits_count`;
4. нормализованный field meta:
   - `name`
   - `label`
   - `type`
   - `isSystem`
   - `isBindableTo`

#### ⚪ Правило универсальности

UI не должен знать конкретные ctype заранее. Он всегда работает через runtime introspection выбранного типа контента.

### 18.6 Фото и иконки в UI

#### ⚪ Только штатные InstantCMS-механики

Для fallback/manual значений:

1. `image` и image-like поля используют стандартную InstantCMS-модалку выбора/загрузки изображения;
2. `icon` и icon-like поля используют стандартную InstantCMS-модалку выбора иконки;
3. NordicBlocks не создаёт собственный media manager;
4. NordicBlocks не создаёт собственный icon registry UI как основной сценарий.

#### ⚪ Что делает редактор

1. открывает штатную модалку;
2. получает итоговое значение;
3. пишет его в `fallbackValue` или в ручной `content`;
4. обновляет preview без отдельной логики хранения файла.

### 18.7 Этапы внедрения

#### Этап A

- ⚪ Ввести reader/normalizer для v3 contract без поломки старых блоков.
- ⚪ Поднять текущие hero/features/cta через compatibility mapping.

#### Этап B

- ⚪ Собрать `DataSourceResolver` для `content_item`.
- ⚪ Реализовать `BindingMapper` для простых slot-ов:
  - title
  - subtitle
  - description
  - image
  - date
  - views
  - comments

#### Этап C

- ⚪ Добавить вкладку `Данные` в инспектор.
- ⚪ Подключить получение ctype и field metadata.
- ⚪ Подключить fallback controls с InstantCMS image/icon modal.

#### Этап D

- ⚪ Реализовать `content_list` для repeater slot `items[]`.
- ⚪ Обновить один эталонный grid/features блок.

#### Этап E

- ⚪ Дожать preview/live parity.
- ⚪ Добавить cache key с учётом adapter context.
- ⚪ Сделать smoke для одного `content_item` и одного `content_list` сценария.

### 18.8 Минимальный scope первой реализации

Если делать без расползания, для первой рабочей версии достаточно:

1. 2-3 hero-блока donor-first волны;
2. первый hero-блок на `content_item`;
3. один features/grid или faq/advantages блок на `content_list` после hero-волны;
4. bindings только для `title`, `image`, `date`, `views`, `comments`, `items[]`;
5. fallback image/icon через штатные InstantCMS-модалки;
6. единый runtime pipeline для preview/live.

### 18.9 Definition of Done

Реализация v1 считается готовой, если одновременно выполнены условия:

1. ⚪ В коде есть отдельные reader/normalizer/resolver/mapper/hydrator слои.
2. ⚪ Вкладка `Данные` умеет выбрать ctype и поле без ручного ввода технических ключей.
3. ⚪ Hero может показать данные текущей или выбранной записи.
4. ⚪ Features/grid может показать список записей через `items[]`.
5. ⚪ Image/icon fallback работает через штатные InstantCMS-модалки.
6. ⚪ Preview и live рендерят один и тот же hydrated payload.
7. ⚪ Первая donor-first волна из 2-3 hero-блоков работает без отдельной архитектуры под каждый блок.

### 18.10 Что реально реализовано на 2026-04-16

#### 🟢 Первый закрытый vertical slice

Сейчас фактически закрыт первый рабочий data-driven repeater slice:

1. блок: `faq`
2. storage: contract-first через Block Contract v3
3. repeater storage: `content.items[]`
4. data source: `data.listSource`
5. source type: `content_list`
6. runtime: единый hydration pipeline до `render.php`

#### 🟢 Что уже умеет FAQ adapter

1. брать записи из любого доступного content type InstantCMS;
2. ограничивать список через `limit`;
3. сортировать через `date_pub_*`, `title_*`, `hits_*`, `comments_*`;
4. маппить поля в `question` и `answer`;
5. использовать системные поля вроде `title`, `date_pub`, `hits_count`, `comments_count`, `category.title`, `user.nickname`;
6. оставлять ручной `content.items[]` как fallback, если `emptyBehavior = fallback`;
7. показывать пустой список, если `emptyBehavior = empty`.

#### 🟢 Где этот runtime уже работает одинаково

1. backend preview iframe: `backend/actions/block_canvas.php`
2. widget runtime: `system/widgets/nordicblocks_block/widget.php`
3. legacy canvas: `actions/canvas.php`
4. legacy public view: `actions/view.php`

#### 🟡 Текущие сознательные ограничения

1. пока поддержан только `faq` как первый adapter-backed repeater;
2. пока поддержан только source `content_list`;
3. пока поддержан только item-level mapping `question` и `answer`;
4. `content_item` для hero ещё не поднят;
5. для dynamic block в legacy public view пока используется безопасная стратегия без SSR-cache reuse, а не финальный adapter-aware cache key.

### 18.11 Вектор на 2026-04-17

Следующий подтверждённый курс разработки такой:

1. сначала `hero + content_item`;
2. потом общий data inspector для single/list scenarios;
3. потом adapter-aware cache/runtime hardening;
4. затем отдельный блок вывода списка контента с карточкой записи.

Критичное правило этого этапа:

1. не все блоки обязаны быть data-driven;
2. manual-first блоки остаются частью целевой архитектуры;
3. data adapters подключаются только там, где они действительно усиливают сценарий блока.

---

## 19. Design Block mode — свободный блок «как в Tilda», но без поломки продукта

### 19.1 Почему это может быть очень сильным ходом

Да, отдельный `design block` действительно может дать тот самый эффект “как в Tilda”:

1. свободная композиция внутри одной секции;
2. не только типовые hero/features/cta, но и сложные авторские промо-блоки;
3. больше продающей силы для агентских и дизайнерских сценариев;
4. возможность делать нестандартные лендинговые экраны без написания нового PHP-render для каждого визуального кейса.

### 19.2 Главное правило

#### 🔴 Design Block не должен заменить обычные блоки

Правильная модель для NordicBlocks:

1. обычные блоки остаются основой продукта;
2. `design block` становится отдельным продвинутым режимом;
3. widget placement остаётся тем же стандартным placement InstantCMS;
4. preview/live всё так же обязаны работать через единый SSR/runtime pipeline.

То есть это не “вместо Hero/Features/CTA”, а “ещё один block type для сложных, свободных секций”.

### 19.3 Как это должно выглядеть архитектурно

#### ⚪ Рекомендуемая модель

Внутри NordicBlocks должен появиться ещё один тип блока:

1. `hero`
2. `features`
3. `cta`
4. `design_block`

При этом `design_block` подчиняется тем же общим правилам:

1. хранится как block contract;
2. размещается тем же виджетом `nordicblocks_block`;
3. имеет editor preview и live render из одного источника;
4. использует общий cache/invalidation подход;
5. не создаёт второй параллельный продукт рядом с NordicBlocks.

### 19.4 Что именно даёт `design_block`

#### ⚪ Слой свободы

В отличие от обычного schema-блока, `design_block` может содержать:

1. абсолютное и контейнерное позиционирование элементов;
2. текст, кнопки, изображения, shape, icon, video, divider;
3. z-index порядок и слои;
4. desktop/tablet/mobile patches;
5. фон, overlays, glass, shadows, blur, эффекты;
6. визуальную сборку секции без создания нового PHP-шаблона на каждый кейс.

#### ⚪ Но в границах одной секции

Это важно: `design_block` должен быть свободной секцией, а не page-builder на весь сайт.

### 19.5 Что нельзя сломать

#### 🔴 Жёсткие ограничения

1. Нельзя превращать NordicBlocks в takeover-конструктор страниц.
2. Нельзя делать отдельный live runtime только для `design_block`.
3. Нельзя плодить особый placement-сценарий только для него.
4. Нельзя ломать preview/live parity.
5. Нельзя выносить бизнес-логику данных в визуальный editor.

### 19.6 Связь с текущим v3-планом

#### ⚪ Правильный порядок внедрения

`design_block` стоит делать не первым, а вторым большим слоем после стабилизации базовой системы:

1. сначала Block Contract v3;
2. потом Adapter Contract v1;
3. потом Inspector IA v2;
4. потом runtime/data binding для обычных блоков;
5. и только затем `design_block` как premium/freeform mode.

Причина простая:

если сделать `design_block` раньше, он затянет на себя всё внимание и продукт снова расползётся в сложный редактор до того, как базовые блоки станут зрелыми.

### 19.7 Как встроить его без архитектурного хаоса

#### ⚪ Общий контракт

`design_block` тоже должен жить в Block Contract v3, но со своей content-моделью:

```json
{
  "meta": {
    "contractVersion": 3,
    "blockType": "design_block"
  },
  "content": {
    "elements": []
  },
  "design": {},
  "layout": {
    "desktop": {},
    "tablet": {},
    "mobile": {}
  },
  "data": {},
  "entities": {},
  "runtime": {}
}
```

#### ⚪ Отличие от типового блока

1. обычный блок = schema-driven form + фиксированный render template;
2. `design_block` = element-driven editor + section renderer;
3. но storage, placement, preview/live, cache, publish-контур остаются общими.

### 19.8 Интеграция с данными InstantCMS

#### ⚪ `design_block` не должен быть «только декоративным»

После запуска базового Adapter Contract v1 `design_block` тоже должен уметь получать данные из системы:

1. текстовые элементы могут биндинговаться к `title`, `subtitle`, `price`, `date`;
2. image-элементы могут брать `cover_image` и другие image-поля;
3. иконки и fallback-картинки всё равно выбираются через штатные InstantCMS-модалки;
4. repeater/collection-элементы позже могут брать данные из `content_list`.

#### 🔴 Но не в первой итерации

Первый релиз `design_block` должен сначала решить:

1. layout;
2. editor UX;
3. preview/live parity;
4. storage contract.

А уже потом получать data binding как второй слой.

### 19.9 Практическая рекомендация

#### 🟢 Да, это стоит делать

Но правильно это делать так:

1. оставить NordicBlocks продуктом из нормальных SSR-блоков;
2. добавить `design_block` как отдельный block type для нестандартных секций;
3. не делать его первой или единственной моделью продукта;
4. брать из Tilda идею свободной секции, но не копировать её page-builder модель целиком.

### 19.10 Definition of Done

Идея `design_block` считается принятой в продуктовую стратегию, если зафиксированы условия:

1. ⚪ `design_block` описан как отдельный тип блока, а не как замена всей библиотеке.
2. ⚪ Он использует тот же widget placement, что и остальные блоки.
3. ⚪ Он подчиняется общему Block Contract v3.
4. ⚪ Он не ломает SSR/runtime parity.
5. ⚪ Его внедрение поставлено после стабилизации обычных блоков и data adapters.

---

## 20. Donor-first стратегия блоков и первая волна

### 20.1 Продуктовое решение

На ближайшую итерацию принимаем простое правило:

1. визуальную библиотеку блоков не изобретаем заново;
2. первые блоки берём прямо из donor-репозитория `nordic-builder.ru`;
3. локально адаптируем их под Block Contract v3, widget placement и SSR-runtime InstantCMS;
4. сразу много блоков не делаем — идём короткой первой волной.

### 20.2 Почему это правильно

#### 🟢 Это снижает риск расползания

Если начинать рисовать много новых блоков с нуля, команда снова уйдёт в бесконечный дизайн и рассыпанные частные решения.

Donor-first подход даёт:

1. готовую визуальную базу;
2. уже понятную продуктовую планку;
3. меньше споров про внешний вид;
4. фокус на правильной архитектуре и runtime, а не на бесконечном придумывании новых секций.

### 20.3 Первая волна блоков

#### ⚪ Ограничение первой волны

Первая волна должна включать только `hero`-семейство.

Правило:

1. стартуем с 2-3 hero-блоков;
2. не добавляем сразу features, faq, pricing, testimonials пачками;
3. сначала доводим hero-конвейер до стабильного состояния;
4. только после этого расширяем библиотеку дальше.

#### ⚪ Что уже видно в доноре

В donor-репозитории уже есть как минимум `blocks-library/hero-classic`.

Текущий статус:

1. 🟡 В NordicBlocks уже поднят первый donor-based блок `hero_classic`.
2. 🟡 Runtime научен читать не только старый local `schema.fields`, но и donor-style связку `meta.json + schema.json`.
3. ⚪ Следующий шаг — добрать ещё 1-2 hero-варианта в ту же схему без отдельной архитектуры на каждый блок.

Его `meta.json` и `schema.json` подтверждают, что donor-блок уже содержит нужную для NordicBlocks логику:

1. `title`
2. `subtitle`
3. `badge`
4. `buttonText`
5. `buttonLink`
6. desktop/mobile typography
7. простые display toggles

То есть donor-блоки действительно подходят как стартовая база для NordicBlocks.

### 20.4 Repeaters — обязательны для повторяющихся элементов

#### 🔴 Это не опция, а обязательное правило

Если в блоке есть повторяющиеся элементы, их нельзя описывать через `item1`, `item2`, `item3`.

Это относится к:

1. преимуществам;
2. карточкам features;
3. FAQ вопрос/ответ;
4. steps;
5. testimonials;
6. любым однотипным повторяющимся сущностям.

#### ⚪ Каноническая модель

Для таких блоков в contract должен использоваться repeater-массив:

```json
{
  "content": {
    "items": [
      {
        "title": "Быстрый запуск",
        "text": "Запуск за 1 день",
        "icon": "bolt"
      },
      {
        "title": "Нативно для InstantCMS",
        "text": "Без второго page-builder",
        "icon": "layers"
      }
    ]
  }
}
```

Для FAQ аналогично:

```json
{
  "content": {
    "items": [
      {
        "question": "Это работает через виджеты?",
        "answer": "Да, блок размещается штатным виджетом InstantCMS."
      }
    ]
  }
}
```

### 20.5 Порядок внедрения после hero-волны

#### ⚪ Следующий шаг после первых hero

После того как 2-3 hero-блока стабильно работают в NordicBlocks, следующим должен идти не ещё десяток разных секций, а один эталонный repeater-блок.

Приоритет:

1. либо `features/advantages`;
2. либо `faq`;
3. но только один класс блока за раз.

Причина:

именно repeater-блок быстрее всего проверит правильность нового контракта, inspector UI и content list adapter.

Текущий статус на 2026-04-16:

1. 🟢 В качестве первого repeater-пилота уже поднят `faq`.
2. 🟢 FAQ переведён на contract-first storage/runtime.
3. 🟢 Вопросы и ответы хранятся канонически через `content.items[]`, а SSR-рендер и preview читают один и тот же контракт.
4. 🟢 Для FAQ уже поднят первый data-driven repeater через `data.listSource` + `content_list` adapter.
5. 🟡 Следующий шаг после этого — не второй FAQ-вариант, а расширение adapter-слоя: `content_item` для hero или более богатый repeater-блок с item-level mapping.

### 20.6 Практическая рекомендация

#### 🟢 Ближайший рабочий курс

1. взять donor `hero-classic` как первый reference block;
2. добавить ещё 1-2 hero-варианта из donor-подхода;
3. не расширять библиотеку массово до стабилизации hero-контура;
4. после этого поднять один repeater-блок для advantages или FAQ.

### 20.7 Definition of Done

Первая donor-first волна считается принятой, если:

1. 🟡 В NordicBlocks уже перенесён первый hero-блок `hero_classic`; целевой минимум — 2-3 блока.
2. ⚪ Они работают через единый Block Contract v3 и общий runtime.
3. ⚪ Для них не создана отдельная архитектура под каждый шаблон.
4. ⚪ Повторяющиеся элементы в новых блоках описываются только через repeater-массивы.
5. 🟢 В роли первого эталонного repeater-блока уже выбран и поднят `faq`.
6. 🟢 FAQ уже закрывает первый data-driven repeater-блок через `content_list` adapter.
7. ⚪ Следующий шаг после этого — не массовый импорт библиотеки, а добивка второго adapter-сценария (`content_item`) и расширение mapping-слоя.
