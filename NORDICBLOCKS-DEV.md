# NordicBlocks — Документ разработки

> Версия: 2.1 | Дата: 2026-04-15 | Автор: проектная документация

---

## 1. Концепция

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
| `richtext` | TinyMCE / встроенный WYS | 🔜 |

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

### Перед применением на проде

1. Сделать backup БД
2. Сделать checkpoint файлов
3. Запустить sync `--apply`
4. Запустить flow-smoke
