# Активный план: Нордик

## Зачем этот файл

Это текущий execution tracker по Нордик после решения стартовать не от локальной полировки bridge-canvas, а от взрослой архитектуры продукта.

Файл нужен, чтобы команда держала один коридор: сначала foundation layer, потом visual editor core, потом system integration layer.

Отдельно зафиксировано: продукт делается для обычных людей, а не для разработчиков. Внутренние contracts, storage, JSON и adapters нужны команде, но не должны становиться главным пользовательским интерфейсом.

Дополнительное обязательное правило на текущий этап: пользовательский интерфейс должен быть понятным для новичков и русскоязычным. Англоязычные подписи и технический жаргон в ежедневных экранах не допускаются.

## Легенда статусов

- ⚪ Не начато
- 🔵 В работе
- 🟡 Частично сделано / переходный этап
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Каноническая формула продукта

`InstantCMS 2 backend -> nordic runtime template -> design system / global defaults -> visual builder workspace -> component library -> widget/data adapter layer`

## Канон: модель интеграции (надстройка без правки ядра)

Основной вектор Нордик: строить **надстройку** над InstantCMS, а не переписывать ядро и не хард-форкать шаблон.

Что это означает на практике:

1. Не менять core-логику InstantCMS ради builder-сценариев.
2. Не делать зависимость продукта от прямых patch-правок в `system/` и базовых шаблонах.
3. Подключаться только через контролируемые точки расширения: виджеты, шаблонные слоты, options, contracts, adapters.
4. Канонический пользовательский результат: дизайнер управляет глобальным стилем и блоками, а движок продолжает обновляться независимо.
5. Любая новая функция должна сначала проверяться по критерию "переживет ли update движка/шаблона без ручного ремонта".

Что запрещено как основной путь:

1. Лечить продуктовые задачи прямыми вмешательствами в core-механику движка.
2. Встраивать runtime через takeover/hybrid-магии, ломающие boundary между Instant-зонами и builder-зоной.
3. Проектировать дизайн-систему как набор разрозненных ad hoc CSS-правок без контрактов и токенов.

## Канон: правило зон (Variant B, без takeover)

В runtime действует жесткое правило владения зонами: **builder управляет только зоной `content_body`**, а весь остальной каркас сайта (header/footer/sidebar и любые shell‑позиции темы) всегда остается обычным InstantCMS‑рендером. Это убирает конфликты, “магические” ветки в шаблоне и гарантирует parity preview/live, потому что на live вставляется один опубликованный SSR‑рендер.

- **Builder‑зона:** только `content_body` (один виджет‑рендер `nordicbuilder.render` → опубликованный SSR HTML + SEO meta через `cms_template`).
- **Instant‑зоны (не трогать builder‑ом):** `header`, `footer`, `sidebar_left`, `sidebar_right`, `before_content`, `after_content` и любые другие shell‑слоты темы.
- **Запрещено в Variant B:** takeover/hybrid‑режимы в runtime, подавление чужих зон, инклюд preview‑шаблона внутрь body, “start widget” с глобальным инжектом тяжёлых JS/CSS.

## Канон: как живет «тело сайта» (shell/body)

Тело сайта живет как обычная страница InstantCMS в выбранной теме: `<body>` и shell‑структура (контейнеры, шапка, подвал, сайдбары, сетка) рендерятся темой и виджетами как раньше. Единственное отличие для landing‑страниц Variant B — внутри `content_body` вместо штатного контента выводится опубликованный SSR‑контент `nordicbuilder` (в обертке‑контейнере), чтобы стили и семантика страницы оставались предсказуемыми.

## Канон: единый root‑namespace для builder‑контента

Весь SSR‑контент `nordicbuilder`, вставляемый в `content_body`, обязан быть обернут в один корневой контейнер с **единым namespace‑классом** (например `.nb-runtime`). Все стили component library и блоков должны быть привязаны к этому namespace (без глобальных селекторов на `body`, `h1`, `.container` и т.п.), чтобы исключить конфликты со стилями темы и другими инстантовыми виджетами.

- Root wrapper: `<div class="nb-runtime" data-nb-page-key="..." data-nb-version="...">...</div>`
- CSS правило: все селекторы начинаются с `.nb-runtime ...`
- Запрещено: глобальные reset/типографика без namespace, стилизация общих bootstrap‑классов без префикса.

## Что считаем зафиксированным на сейчас

1. Текущий `landingbuilder` больше не считается финальной продуктовой границей.
2. Он используется как переходный bridge-слой для runtime, preview и части editor UX.
3. Новый builder boundary `nordicbuilder` должен проектироваться отдельно, contract-first и schema-first.
4. Основная dev-опора для дисциплины структуры и contracts в этом workspace это `instantcms-mcp-main`.
5. `Visual Builder Workspace` это главный ежедневный экран.
6. `Глобальные стили` это secondary UI над `design system / global defaults`.
7. `Shell Builder` это expert/system layer, а не главный пользовательский маршрут.
8. `Контракты`, `Хранилища`, `JSON документы`, `binding options`, `adapters` и migration tooling считаются внутренним dev/system слоем, а не главным продуктовым опытом.
9. Текущий backend `nordicbuilder` на этом этапе допустим как internal tooling, но не как конечный главный интерфейс builder-а.
10. Пользовательская поставка должна идти как один installable component `nordicbuilder`; `landingbuilder` допускается только как внутренний bridge payload внутри этого пакета, а не как второй отдельный компонент для установки пользователем.
11. Визуальный курс дизайн-системы на ближайший этап: журнальный деловой медиа-минимализм, full-width режим и 12-колоночная сетка как baseline.
12. Скругления по умолчанию отключены, включаются только через управляемые настройки внешнего вида.

## Канонические роли экранов

1. `Visual Builder Workspace`: ежедневная сборка страницы из page/section/block/element.
2. `Глобальные стили`: редкие site-wide defaults.
3. `Shell Builder`: header/footer/menu placement, shell rules и assignment.
4. `Dev Panel`: contracts, storage, adapters, JSON documents, migration diagnostics. Этот слой нужен команде, но должен быть скрыт от обычного пользователя.

Экранные роли больше не подменяют архитектурные слои продукта.

## Два слоя продукта

### 1. User Product

Это то, что должно видеть большинство пользователей.

- `Visual Builder Workspace`
- `Страницы`
- `Шаблоны`
- `Глобальные стили` как редкий secondary flow

Главный критерий: человек без сильной технической подготовки должен открыть страницу и начать собирать ее через canvas, секции и блоки, а не через schema/JSON/contracts.

### 2. Dev/System Layer

Это слой для команды и внутренней эксплуатации.

- `Контракты`
- `Хранилища`
- `JSON документы`
- `binding options`
- `adapters`
- migration и diagnostic tooling

Главный критерий: этот слой помогает строить и отлаживать продукт, но не подменяет собой пользовательский builder.

## Канонический MVP-коридор

### 1. Foundation Layer

Статус: 🔵 В работе

#### Статус modern UI foundation

- 🟢 У `nordic` поднят собственный foundation-слой: локальный `foundation.css` больше не зависит от прямого визуального импорта `modern`.
- 🟢 `theme.css` и SCSS-слой `tokens/base/shell/components` стали собственным source-of-truth для видимого дизайна Nordic.
- 🟢 Зафиксировано продуктовое правило: Bootstrap допустим только как внутренний технический фундамент, а не как лицо конструктора.
- 🟡 Bootstrap 4 пока остается legacy-base для сетки, базовой механики форм и runtime-совместимости.
- 🟡 Shared admin UI для `pages`, `canvas`, `defaults`, `shell` и `shell variant` уже переведен на Nordic-styled слой; самые заметные bootstrap-look controls в главном визуальном маршруте сняты.
- 🟡 First-class UI primitives builder-а еще не вынесены полностью из bootstrap-semantic markup и старых visual patterns: штатные widget/forms и часть служебной разметки все еще сидят на legacy semantics под капотом.
- ⚪ Полная инфраструктурная независимость `nordic` от `modern` еще не достигнута: в `manifest.php` сохраняется `inherit => ['modern']` как техническая опора.
- 🟢 Подготовлен отдельный manual smoke checklist для актуального admin UI маршрута `pages -> canvas -> defaults -> shell`.
- ⚪ Авторизованный smoke test современного user-facing UI еще не закрыт как финальная визуальная проверка.

Это первый взрослый этап. Без него дальше нельзя масштабировать editor и runtime без расползания по bridge-логике.

- Что фиксируем на этом этапе:
  - канонический JSON schema contract;
  - единый runtime contract для canvas, preview и live frontend;
  - design token model и порядок наследования;
  - минимальное ядро component library;
  - boundary компонента `nordicbuilder`;
  - bridge-стратегию переиспользования текущего `landingbuilder` и `nordic`.
- Что уже запущено:
  - синхронизирован product canon под взрослую архитектуру;
  - добавляется отдельный foundation spec как опорный документ этапа;
  - active plan перестраивается под 3-этапный MVP, а не под локальный visual-first slice;
  - в `nordicbuilder` поднят file-based contract registry на 5 канонических contracts;
  - в backend `nordicbuilder` добавлен browser `Контракты`, который читает registry как source-of-truth;
  - поверх registry добавлены storage stub и detail screen для contract tooling следующего слоя.
  - в `nordicbuilder` добавлен минимальный SQL-backed persistence layer для `page-document`, `preset-token` и `binding-options`, плюс workspace summary по состоянию foundation storage.
  - workspace `nordicbuilder` начал жить как первый реальный page-document loop: список документов, JSON save/load, bridge import из `landingbuilder` и более строгая validation структуры page document.
  - bridge-слой `landingbuilder` начал читать и сохранять страницы через `nordicbuilder page-document`, если для page key уже существует новый contract document.
  - в workspace `nordicbuilder` добавлен массовый migration route из `landingbuilder`: pending/imported каталог legacy-страниц, bulk import с опцией overwrite existing и отчет по результатам миграции.
  - live backend `nordicbuilder` переведен на русский интерфейс, а дублирующая install-запись компонента в админке устранена.
  - bridge-canvas `landingbuilder` начал получать первый semantic-aware слой: block catalog идет из модели, inspector редактирует preset-поля блока, а runtime-preview показывает типизированные сценарии по смыслу блока.
  - `nordicbuilder` начал реально использовать свой `block manifest registry`: `page-document` нормализует block props по manifest defaults, валидирует их по supports/props schema, а import из `landingbuilder` прогоняет policy-слой для alias resolution и fallback normalization.
  - live smoke test на `homepage`, `ads-category` и `profile-cover` подтвердил, что manifest-aware import уже проходит через реальный bootstrap без contract errors.
  - user-facing admin flow переведен под `nordicbuilder`: страницы, canvas, глобальные стили и canvas AJAX endpoints больше не должны требовать прямой работы пользователя через backend старого `landingbuilder` компонента.
  - package strategy ужесточена: `packages/nordicbuilder/` теперь является единственным installable delivery target, а bridge-файлы `landingbuilder` должны входить в этот payload как внутренняя совместимость, а не как отдельная пользовательская установка.
  - стартовый пользовательский flow усилен: новый visual seed и создание обычной страницы теперь должны открывать готовый продуктовый starter preset, который пользователь редактирует под себя, а не пустой холст.
  - зафиксирован отдельный demo-cycle spec (`Quick Demo` + `Full Template`) как следующий шаг после установки: пользователь должен видеть готовые demo-страницы, bindings и deterministic priority-сценарий для быстрого onboarding и bug-hunting.
  - visual layer шаблона `nordic` отвязан от прямого CSS-импорта `modern`: поднят собственный `foundation.css`, `theme.css` переведен на Nordic-only слой, а SCSS разложен на partials `tokens`, `base`, `shell`, `components` с синхронным package mirror; `inherit => ['modern']` пока сохраняется только как техническая runtime-совместимость, а не как источник видимого дизайна.
  - shared admin UI `landingbuilder`/`nordicbuilder` переведен на взрослый Nordic-styled слой: canvas получил собственные кнопки, controls, library cards и version cards, а `pages`, `shell` и `shell variant` ушли от bootstrap-таблиц в собственные product-style cards/panels.
  - для этого UI подготовлен отдельный ручной smoke checklist под актуальные admin routes `nordicbuilder/pages -> canvas -> defaults` и bridge-screen `landingbuilder/shell`.
- Ограничение этапа:
  - foundation не должен превращаться в бесконечное проектирование идеальной платформы;
  - internal tooling допустим как инженерная опора, но не должен выдаваться за главный builder UI;
  - любой новый contract/tooling слой должен либо поддерживать текущий vertical slice, либо откладываться.
- Что должно выйти из этапа:
  - понятный набор contracts, от которых можно строить и PHP, и runtime payload, и editor UI;
  - ясная граница между bridge-слоем текущего кода и будущим builder core;
  - минимальный список first-class entities: page, section, block, element, preset, adapter.
- Что считаем готовностью этапа:
  - новый код можно писать от contract boundary, а не от хаотичных форм и payload-ов;
  - понятно, какие части живут в `nordicbuilder`, какие остаются в `nordic`, а какие временно сидят в bridge-слое;
  - Visual Builder Workspace не проектируется больше вслепую без token и schema модели;
  - foundation layer имеет уже не только registry, но и реальную persistence base, от которой можно строить save/load loop.

### 2. Vertical Slice (MVP loop)

Статус: 🔵 В работе и обязательно сейчас

Это обязательный проверочный слой между foundation и взрослым editor core.

- Зачем нужен:
  - проверить, что contracts жизнеспособны в реальном пользовательском сценарии;
  - проверить, что builder ощущается как визуальный продукт, а не как dev console;
  - заставить runtime, editor и persistence пройти один полный цикл без теоретических допущений.
- Scope текущего vertical slice:
  - 1 страница;
  - 2 секции;
  - до 5 базовых блоков;
  - изменение текста и базовых настроек;
  - save/load;
  - preview.
  - старт не с пустой страницы, а с готового starter preset.
- Критерий успеха:
  - страницу можно собрать без JSON-ручного вмешательства;
  - пользователь видит canvas как главный экран;
  - данные проходят полный цикл `editor -> save -> runtime preview`;
  - vertical slice дает реальную обратную связь для переработки contracts, если они окажутся неудобными.
- Что нельзя делать на этом этапе:
  - откладывать vertical slice до завершения “идеального foundation”;
  - считать internal tooling достаточным доказательством готовности продукта;
  - строить component library только из теории без живого canvas-использования.

### 3. Visual Editor Core

Статус: ⚪ Не начато как отдельный взрослый этап

Bridge-прототипы canvas уже существуют, но взрослый editor core начнется только после того, как vertical slice подтвердит жизнеспособность contracts и пользовательского потока.

- Что будет целью этапа:
  - live inspector с продуктовой, а не технической структурой;
  - page/section/block editing прямо на холсте;
  - устойчивый save/preview/publish loop на одном runtime contract;
  - page tree, outline и insertion flow на базе component library.
- Что нельзя делать на этом этапе:
  - продолжать расширять bridge-canvas без опоры на contracts и tokens;
  - подменять component library случайным набором ad hoc controls.

### 4. System Integration Layer

Статус: ⚪ Не начато

Этот этап подключает сильные стороны InstantCMS уже после того, как foundation и editor core перестанут быть moving target.

- Что войдет сюда:
  - widget/data adapters;
  - shell participation visibility внутри workspace;
  - reusable component packs и page templates;
  - финальная упрощенная роль secondary screen `Глобальные стили`.

## Что теперь считается правильным ежедневным сценарием

- пользователь открывает страницу в `Visual Builder Workspace`;
- работает с page/section/block через живой canvas и context inspector;
- site-wide defaults меняет редко и отдельно;
- shell-структуру трогает только когда реально меняет каркас сайта;
- preview и live runtime используют тот же contract, а не параллельную самодельную логику.

Это сценарий для обычного человека, а не для инженера. Основной продуктовый экран обязан давать ощущение “я собираю страницу”, а не “я управляю схемой”.

## Что считаем неправильным направлением

- шлифовать текущий bridge-canvas как будто он уже и есть финальный builder;
- развивать продукт как набор экранов `Дизайн сайта + Shell Builder + canvas`, не фиксируя слой contracts;
- смешивать design system, component library и adapters в один пользовательский сценарий;
- принимать payload или структуру editor-а без связи с каноническими schema contracts.
- показывать пользователю contracts/storage/json tooling как основной продуктовый интерфейс;
- пытаться допроектировать builder полностью заранее без обязательного vertical slice.

## Ближайший implementation order

1. Зафиксировать foundation layer в отдельной спеки: contracts, tokens, component library core, bridge strategy.
2. Определить boundary компонента `nordicbuilder` и его install/package discipline по опоре `instantcms-mcp-main`.
3. После этого сделать первый минимальный scaffold `nordicbuilder`.
4. Поднять contract registry и browser внутри `nordicbuilder`.
5. Затем расширять contract tooling уже от живого registry/detail/storage/persistence слоя, но не отрывая его от пользовательского сценария.
6. Сразу после этого собрать обязательный vertical slice: `1 page -> 2 sections -> blocks -> save/load -> preview`.
7. Только после этого переходить к взрослой реализации `Visual Builder Workspace`.
8. Shell Builder, `Глобальные стили` и widget/data adapters расширять уже поверх foundation и vertical slice, а не вместо них.

## Reference-документы

- [LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md](LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md)
- [LANDING-BUILDER-VISUAL-FIRST-PIVOT-2026-04-05.md](LANDING-BUILDER-VISUAL-FIRST-PIVOT-2026-04-05.md)
- [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md)
- [LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md](LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md)
- [docs/NORDICBUILDER-DESIGN-SYSTEM-GLOBAL-CONTROL-MAP.md](docs/NORDICBUILDER-DESIGN-SYSTEM-GLOBAL-CONTROL-MAP.md)
- [LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md](LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md)
- [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
- [LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md](LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md)

## Имя нового builder component

Зафиксированное имя нового отдельного builder component: `nordicbuilder`.

---

## Лог изменений — 5 апреля 2026

### Сессия: UX-упрощение для новичков (Вариант Б — Canvas-first)

**Задача сессии:** убрать весь developer-facing интерфейс из пользовательского маршрута. Пользователь открывает nordicbuilder → видит холст, а не таблицы и технические термины.

#### Готово ✅

| Что | Файл | Суть |
|-----|------|------|
| Google Fonts always-on | `templates/nordic/main.tpl.php` | PT Serif + Roboto Condensed загружаются всегда, не по флагу |
| CSS utility-классы | `templates/nordic/css/theme.css` | nordic-bg-*, text-on-dark, nordic-hero, homepage fullwidth |
| YAML layout scheme | `packages/nordic/InstantCMS-Widgets-Scheme.yaml` | 20 строк / 36 колонок, nm→nordic, бонус для power users |
| shell_scheme.php legacy_bind_map | `templates/nordic/shell_scheme.php` | 35+ nordic_* → shell slots |
| **actionIndex() → canvas** | `system/controllers/nordicbuilder/backend.php` | При входе в nordicbuilder сразу открывается canvas |
| **Меню: 4 → 3 пункта** | `system/controllers/nordicbuilder/backend.php` | "Мой сайт" / "Все страницы" / "Внешний вид"; убран "Каркас сайта" |
| **Скрыт lb-shell-map** | `canvas.tpl.php` | `#lb-shell-map` скрыт через `display:none` |
| **Убраны технические термины** | `canvas.tpl.php` | "Источник:", "Сценарий корпуса: body_layout" убраны из Inspector; "Shell" → "Каркас"; "Визуальный язык" → "Внешний вид" |
| **Стартовый экран пустого холста** | `canvas.tpl.php` | Вместо текста-заглушки — 4 кнопки: 🏠 Первый экран / ⭐ Преимущества / 💬 Отзывы / ＋ Чистая секция |
| Синхронизация packages/ | — | backend.php + canvas.tpl.php синхронизированы в packages/nordicbuilder/ и packages/landingbuilder/ |

#### Не сделано / следующий шаг ⬇️

| Приоритет | Что | Почему |
|-----------|-----|--------|
| 🔥 1 | **Smoke test UI в браузере** | Открыть nordicbuilder в живом сайте, проверить меню + canvas + пустой экран |
| 🔥 2 | **Empty canvas welcome: preset кнопки кликают** | Убедиться что `data-role="insert-section-preset"` handlers работают для стартовых кнопок из lb-starter |
| 🔥 3 | **Страница `homepage` существует и открывается** | canvas без page → error404; проверить что primary document resolve работает |
| 🟡 4 | **pages.tpl.php: убрать "adapter_key" колонку** | В списке страниц торчит техническая колонка |
| 🟡 5 | **canvas: режимы страниц** | `full_takeover / hybrid_overlay` в селекторе page mode — переименовать в русские понятные названия |
| ⚪ 6 | **Финальный smoke checklist** | Проверка всего маршрута: вход → меню → canvas → добавить секцию → сохранить → preview |

---

## Лог изменений — CSS-first архитектура (продолжение сессии)

### Сессия: page_context + design system расширение

**Задача:** реализовать универсальный детектор типа страницы и подключить его в runtime шаблона.

**Ключевое архитектурное решение:**
- НЕ переопределяем PHP-шаблоны `modern` (ломаются при обновлениях)
- DO: CSS-first через Bootstrap class overrides под `.nordic-shell`
- Используем `controller+action` паттерны InstantCMS вместо имён типов контента
- Это даёт универсальность: работает у любого клиента с любыми компонентами

#### Готово ✅

| Что | Файл | Суть |
|-----|------|------|
| **page_context.php** | `templates/nordic/page_context.php` | Матрица `controller+action → shell_preset+body_class+flags`; 11 паттернов + wildcard fallback; результат в `$nordic_context` |
| **context_rules** | `templates/nordic/shell_scheme.php` | Добавлен раздел `context_rules`: пресеты боковых колонок + always_active slots |
| **Интеграция в main.tpl.php** | `templates/nordic/main.tpl.php` | Загружает `page_context.php`; применяет shell_preset когда builder не переопределяет active_slots; добавляет `data-page-type` на `<body>` |
| **_components.scss расширение** | `templates/nordic/scss/theme/_components.scss` | +150 строк: типографика контента, листинг карточек, meta/tags, пагинация, сайдбар |
| Синхронизация packages/ | — | page_context.php + shell_scheme.php + main.tpl.php + _components.scss синхронизированы в packages/nordic/ |

#### Как работает page_context

```
Запрос → controller+action → матрица → $nordic_context['shell_preset']
                                      → $nordic_context['body_class']    → <body class>
                                      → $nordic_context['page_type']     → <body data-page-type>
                                      → $nordic_context['can_use_builder']
```

Когда `landingbuilder_shell_runtime` не задан (обычная страница) — shell_preset из page_context определяет видимость sidebar-ов. Когда builder задаёт `active_slots` — builder выигрывает.

#### Покрытые паттерны

| controller | action | preset | body_class |
|------------|--------|--------|------------|
| (empty) | index | no_sidebars | page-homepage |
| content | index | right_sidebar | page-content-list |
| content | category | right_sidebar | page-content-category |
| content | item | right_sidebar | page-content-item |
| users | profile | no_sidebars | page-user-profile |
| auth | * | no_sidebars | page-auth |
| photos | image | no_sidebars | page-media-item |
| landingbuilder | * | no_sidebars | page-landing |
| * | * | no_sidebars | page-generic |

#### Следующие приоритеты ⬇️

| Приоритет | Что | Почему |
|-----------|-----|--------|
| 🔥 1 | **Smoke test page_context в браузере** | Проверить что `data-page-type` появляется на body, sidebar скрывается на homepage |
| 🔥 2 | **Компиляция SCSS → theme.css** | _components.scss расширен — нужно запустить sass build |
| 🟡 3 | **widgets_bind_nordic.sql** | Стартовый набор виджетов для позиций nordic (по образцу modern) |
| 🟡 4 | **PHP override: content/item_view** | Единственный оправданный override — для hero-зоны у single article |
| ⚪ 5 | **Shell Manager UI** | Экран управления shell slot assignments в nordicbuilder |

---

## Текущий фокус

**Vertical Slice: 🔵 В работе**

CSS-first foundation layer завершён. page_context определяет тип страницы universally.

**Что проверять прямо сейчас:**
1. Открыть любую страницу сайта → inspect `<body>` → должен быть `data-page-type="content-list"` (или другой)
2. На главной `data-page-type="homepage"` + правый сайдбар должен скрыться
3. На странице записи — правый сайдбар включен (`page-content-item`)
4. Компилировать SCSS если есть build-скрипт

---

## Лог изменений — 6 апреля 2026 (takeover/runtime: «по‑взрослому»)

**Статус: 🧪 На проверке** (нужен ручной smoke на `/` + preview + canvas)

Цель дня: убрать «магические» дубли и просачивание legacy‑сайта, привести live frontend ближе к тому, что видим в canvas/preview.

### Готово ✅

| Что | Где | Суть |
|-----|-----|------|
| **Full takeover через реальные shell‑слоты** | `templates/nordic/main.tpl.php` (+ package mirror) | Builder‑зоны рендерятся напрямую в `hero / before_content / content_body / after_content`, без инклуда целого preview‑шаблона внутрь body |
| **Подавление legacy‑виджетов при takeover** | `templates/nordic/main.tpl.php` | При активном takeover отключаются виджеты `content_body` и контентные сайдбары, а fallback‑рендер слотов `hero/before/after` не выполняется |
| **Чистый runtime HTML (surface=site)** | `templates/default/controllers/landingbuilder/runtime_renderer.php` (+ mirrors) | Убраны debug‑обёртки/лейблы при рендере для live‑поверхности, чтобы не было «второй секции» из служебной разметки |
| **Compact preview / embed‑режим** | `system/controllers/landingbuilder/actions/view.php`, `templates/default/controllers/landingbuilder/view.tpl.php` (+ mirrors) | Preview по умолчанию рендерит только секции builder‑зон, пропускает пустые зоны; при embed‑takeover включается compact |
| **Homepage контекст для `/`** | `templates/nordic/page_context.php` (+ package mirror) | Корень сайта (`$core->uri` пустой) нормализуется как homepage (`ctrl=''`, `action='index'`), чтобы takeover/bindings работали предсказуемо |
| **Canvas: меньше «красных ошибок»** | `system/controllers/nordicbuilder/backend/actions/widget_preview.php` (+ mirror) | Для menu‑widget без выбранного меню возвращается подсказка без `error=true`, чтобы новичок не видел «падение» |

### Что проверять после этих изменений 🔥

1. Главная `/`: builder‑контент виден, legacy‑виджеты/сайдбары не просачиваются.
2. Preview для этой же страницы: количество секций совпадает с canvas (без «дубля»).
3. Canvas: добавление menu‑widget без выбора меню не даёт красной ошибки, а показывает подсказку.

### Документация

- Результаты дня дополнительно записаны в `docs/WORKLOG.md`.

---

## Закрытие дня — 7 апреля 2026

### Итог статуса

- Foundation Layer: 🟡 Частично сделано (рабочий persistence/bridge/runtime контур есть, но требуется финальный smoke и частичная UX-дошлифовка).
- Vertical Slice: 🔵 В работе (практический loop `canvas -> save -> preview/live` сильно продвинут, но нужен обязательный ручной прогон).
- Visual Editor Core: ⚪ Не начато как отдельная фаза (идут точечные UX-улучшения в bridge-слое).
- System Integration Layer: ⚪ Не начато.

### Что зафиксировано за день

1. Стабилизирован сценарий внутренних страниц с native `content_body` и секциями вокруг body.
2. Реализован и доведен body-frame режим `1/2-left/2-right/3` с управлением ширинами колонок.
3. Canvas переведен на более понятную zone-first модель и упрощенный inspector.
4. Закрыт регресс с дублирующимся системным body-блоком на холсте.

### План на завтра (8 апреля 2026)

1. Пройти ручной smoke матрицу: guest/admin, canvas/preview/live, маршруты `/`, `/board`, user profile.
2. Довести UX-reorder секций по зонам до полностью предсказуемого поведения.
3. Закрыть regression-checklist по body-frame/native-body и зафиксировать результат в документации.

### Критерий завершения следующего дня

- Пользователь без техподготовки понимает, куда добавляется секция и почему.
- Preview/live не расходятся в ключевых сценариях.
- Результаты smoke подтверждены в `docs/WORKLOG.md`.

---

## Закрытие этапа — 8 апреля 2026 (native body width + sidebars)

### Что закрыто

1. Full width (`100%`) больше не отключает sidebars в runtime.
2. Режим ширины native body развязан от режима колонок body.
3. Для sidebar-зон отключены A/A+ (в UI и в action guards).
4. Добавлен управляемый full-width padding (`native_body_full_padding`).
5. Runtime получил отдельный full-width layout-класс для случая `100% + sidebars`.

### Продуктовый эффект

- Пользователь может работать в `100%` и при этом сохранять 0/1/2 sidebars.
- Переключение `12/12 <-> 100%` перестало ломать структуру колонок и вызывать визуальные артефакты в боковых слотах.

### Где зафиксировано подробно

- `docs/WORKLOG.md` (запись за 2026-04-08)
- `docs/worklogs/2026-04-08-native-body-fullwidth-sidebars.md`
