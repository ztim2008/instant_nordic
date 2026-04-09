# NordicBuilder: аудит bridge-слоя (2026-04-09)

## Зачем этот документ

Сегодня зафиксирован инженерный тупик вокруг текущего bridge-слоя. Этот документ:

- фиксирует фактическую архитектуру и точки входа;
- выделяет сильные/слабые стороны текущего состояния;
- объясняет, почему именно bridge начинает «ломать темп»;
- предлагает стратегию развития, согласованную с каноном из `LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md`.

## Канон (как должно быть в конечном продукте)

Опорный принцип (Variant B): builder управляет только зоной `content_body`, а остальное (shell: header/footer/sidebars/слоты темы) остаётся обычным InstantCMS-рендером.

Ключевые следствия канона:

- один installable продукт: `nordicbuilder` (а `landingbuilder` — только внутренний payload/совместимость);
- preview/live должны опираться на один и тот же runtime contract;
- на live вставляется опубликованный SSR HTML через один виджет (`nordicbuilder_render`) и один root-namespace `.nb-runtime`.

## Что есть сейчас (факты по репозиторию)

### 1) Где живут данные

- Черновики/документы: `nordicbuilder_page_documents` (modelNordicbuilder::PAGE_DOCUMENT_TABLE) — хранит нормализованный page-document.
- Опубликованный SSR: `nordicbuilder_page_renders` (modelNordicbuilder::PAGE_RENDER_TABLE) — хранит HTML+meta (title/description/keywords/theme) и `content_hash`.

### 2) Основной save-loop canvas (bridge)

Фактический путь сохранения canvas:

- admin canvas вызывает AJAX: `system/controllers/nordicbuilder/backend/actions/canvas_save.php`
- он вызывает `landingbuilder` модель: `modelLandingbuilder::savePageSchema()`
- `landingbuilder` при наличии документа в nordicbuilder делает bridge-save: `modelNordicbuilder::saveBridgePageSchema()` → `savePageDocument()`

Это означает:

- `landingbuilder` сейчас остаётся фасадом, через который проходит canvas contract и часть UX.
- Source-of-truth для новых страниц уже начинает быть `nordicbuilder_page_documents`, но вход/выход всё ещё через `landingbuilder`.

### 3) Runtime / view сейчас смешивает два пути

Есть два разных «канонических» пути показа страницы:

A) Bridge runtime через landingbuilder view:

- `system/controllers/nordicbuilder/actions/view.php` просто делегирует в `landingbuilder/view`.
- `system/controllers/landingbuilder/actions/view.php` рендерит runtime из `getRuntimePage($page)` и шаблона `templates/nordic/controllers/landingbuilder/view.tpl.php`.

B) Variant B SSR widget (то, что нужно по канону):

- publish endpoint: `system/controllers/nordicbuilder/backend/actions/publish_page.php` рендерит только `content_body` и сохраняет SSR в `nordicbuilder_page_renders`.
- виджет: `system/widgets/nordicbuilder_render/widget.php` вытаскивает опубликованный SSR render и выставляет SEO meta + css vars.
- шаблон виджета: `templates/default/widgets/nordicbuilder_render/nordicbuilder_render.tpl.php` оборачивает HTML в `.nb-runtime`.

Главная текущая проблема: эти два пути пока не сведены в один «единственный правильный» runtime.

### 4) Автосоздание страниц в canvas

- `system/controllers/nordicbuilder/backend/actions/canvas.php` при отсутствии страницы вызывает `landingbuilder->createPage()`.
- при этом создаётся страница с `mode = full_takeover`.

Это ускоряет прототипирование, но усиливает главный риск bridge: «растёт площадь магии» и растёт стоимость поддержки parity preview/live.

## Сильные стороны текущего состояния

- Есть реальный contract-first storage (`page-document`, `preset-token`, `binding-options`) и workspace summary — это зрелая инженерная база.
- Bridge реализован так, что `landingbuilder` может работать поверх нового хранилища без полной переписки UI прямо сейчас.
- Есть отдельный publish-пайплайн под Variant B: сохранение SSR HTML и отдельный runtime-виджет с `.nb-runtime` namespace.
- Дизайн-система уже выражается через vars/пресеты и может прокидываться в SSR через runtime_theme.
- Пакетная дисциплина (mirrors в `packages/nordicbuilder/package/...`) уже учитывает installable delivery.

## Слабые стороны / риски (то, что и создаёт «тупик»)

### 1) Два runtime-источника правды

- Bridge view показывает runtime из landingbuilder-схемы.
- Variant B по канону должен показывать опубликованный SSR из `nordicbuilder_page_renders`.

Пока оба пути существуют параллельно, любое изменение в runtime/зонах/шаблонах может проявляться по-разному в разных режимах (canvas preview vs live route vs nordicbuilder/view).

### 2) Пересечение режимов (takeover/hybrid) с каноном Variant B

- В `canvas.php` при автосоздании страниц используется `full_takeover`.
- В каноне Variant B takeover/hybrid в runtime запрещены как основной путь.

Это типичный источник инженерного тупика: чтобы «всё работало везде», приходится добавлять исключения и fallback-ветки в shell, зоны и рендер.

### 3) Dual-source-of-truth на уровне сущностей

Даже если документ хранится в `nordicbuilder`, часть жизненного цикла страницы (создание, статусы, некоторые экраны) всё ещё управляется через `landingbuilder` сущности/методы.

Это создаёт:

- расхождения статусов;
- сложность миграции;
- сложность удаления/чистки;
- рост числа «если есть документ — делай так, иначе — иначе».

### 4) Зоны и shell-слоты: высокая сложность без продуктовой отдачи

Каждый шаг поддержки множества зон/слотов в runtime увеличивает комбинаторику:

- разные shell variants;
- включенные/выключенные слоты;
- разные adapter page types;
- разные preview поверхности.

А по канону продукта большинство пользователей не должно платить за эту сложность: им нужна предсказуемая редактируемая зона `content_body`.

### 5) Publish/Live контракт не является «центром мира»

Publish уже существует, но он ещё не оформлен как единственная гарантия parity.

Пока live/view не «питаются» из SSR render table как из source-of-truth, parity остаётся условной и требует постоянной ручной дисциплины.

## Почему это инженерный тупик (корень)

Bridge-слой пытается одновременно:

- быть временной совместимостью (landingbuilder UI/модель),
- быть runtime и preview для реального продукта,
- и параллельно строить новый `nordicbuilder` boundary.

Это делает bridge не «тонким адаптером», а вторым ядром продукта. В этот момент любое улучшение начинает требовать правки сразу в нескольких местах (landingbuilder runtime + nordicbuilder documents + shell logic + adapters), и темп падает.

## Стратегия развития

Ниже стратегия, которая минимально ломает текущую систему, но быстро выводит из тупика.

### Этап 0 (1–2 дня): зафиксировать единственный runtime-канон

Цель: один понятный ответ на вопрос «что такое live output?».

- Принять решение: Variant B = live рендерится из `nordicbuilder_page_renders` через виджет `nordicbuilder_render`.
- Любые улучшения preview должны стремиться визуально совпасть с этим output.

Артефакт готовности этапа:

- документированное правило: “Published page = SSR render table + widget in content_body”.

### Этап 1 (3–7 дней): “publish loop как продуктовая гарантия”

Цель: parity preview/live становится свойством системы, а не дисциплиной.

- Сделать publish не опциональным, а центральным действием (MVP loop: save → preview → publish → смотреть на реальном маршруте).
- Вывести чёткие инварианты:
  - опубликованная страница = есть запись в `nordicbuilder_page_renders`;
  - SSR рендер всегда обёрнут `.nb-runtime`;
  - SEO meta берётся из meta render-а.

### Этап 2 (1–2 недели): сузить bridge до “совместимости”, убрать takeover как основу

Цель: bridge становится тонким и предсказуемым.

- Перестать создавать новые страницы в `full_takeover` по умолчанию (или ограничить это только demo/dev режимом).
- Привести default page modes к `instant_content_body` там, где это не ломает текущий vertical slice.
- Сфокусировать зоны редактирования на `content_body` (остальное — shell/Instant).

### Этап 3 (1–2 месяца): nordicbuilder становится единственным owner для page lifecycle

Цель: убрать dual-source-of-truth.

- `nordicbuilder_page_documents` = source-of-truth для страницы, версий и статусов.
- `landingbuilder` остаётся как:
  - совместимый renderer/adapter (если нужно),
  - или полностью уходит внутрь `nordicbuilder` как библиотека.

## Рекомендуемые “контрольные точки” (чтобы не повторять тупик)

1. Любая фича должна отвечать на вопрос: она усиливает Variant B (content_body + SSR) или раздувает takeover/hybrid?
2. Любая новая сущность должна иметь один owner (nordicbuilder или InstantCMS), иначе будет дрейф.
3. Любая правка runtime должна быть проверяема через publish-loop (SSR) — иначе parity будет случайной.

## Ключевые файлы (ориентиры)

- Save bridge:
  - `system/controllers/nordicbuilder/backend/actions/canvas_save.php`
  - `system/controllers/landingbuilder/model.php` (`savePageSchema`)
  - `system/controllers/nordicbuilder/model.php` (`saveBridgePageSchema`, `savePageDocument`)
- Publish SSR:
  - `system/controllers/nordicbuilder/backend/actions/publish_page.php`
  - `system/widgets/nordicbuilder_render/widget.php`
  - `templates/default/widgets/nordicbuilder_render/nordicbuilder_render.tpl.php`
  - `templates/default/css/nordicbuilder_runtime.css`
- Bridge view (текущий runtime путь):
  - `system/controllers/nordicbuilder/actions/view.php`
  - `system/controllers/landingbuilder/actions/view.php`
  - `templates/nordic/controllers/landingbuilder/view.tpl.php`
- Canvas авто-создание/режимы:
  - `system/controllers/nordicbuilder/backend/actions/canvas.php`
