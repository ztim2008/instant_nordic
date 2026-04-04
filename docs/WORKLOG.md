# Worklog

Использовать этот файл как простой журнал хода проекта.

## Template

### YYYY-MM-DD

- Что планировалось:
- Что сделано:
- Какие файлы затронуты:
- Что проверено:
- Какие риски остались:
- Следующий шаг:

## 2026-04-03

- Что планировалось:
	- развернуть проектную основу, распаковать docs pack и упорядочить ТЗ по Landing Builder.
- Что сделано:
	- инициализирован git-репозиторий и создан baseline tag;
	- добавлены docs по процессу, rollback и работе с агентом;
	- распакованы `instantcms-mcp-main` и пакет документов Landing Builder;
	- создан главный трекер [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md);
	- перелинкованы ключевые ТЗ-документы;
	- зафиксирована продуктовая рамка: `Нордик`, русский интерфейс, InstantCMS 2.
- Какие файлы затронуты:
	- корневые `LANDING-BUILDER-*.md`
	- [README.md](../README.md)
	- [docs/PROJECT-OVERVIEW.md](PROJECT-OVERVIEW.md)
	- [docs/WORKLOG.md](WORKLOG.md)
- Что проверено:
	- scripts проходят `bash -n`;
	- git baseline создан;
	- документы распакованы и доступны в корне.
- Какие риски остались:
	- код skeleton компонента еще не начат;
	- техническое имя компонента пока не закреплено в коде;
	- трекер паков пока проектный, а не кодовый.
- Следующий шаг:
	- старт skeleton компонента `landingbuilder` в `system/controllers/landingbuilder`.

### 2026-04-03 / уточнение архитектуры

- Что планировалось:
	- уточнить три критических проектных решения до старта кода.
- Что сделано:
	- зафиксировано, что основная dev-база разработки это [instantcms-mcp-main](../instantcms-mcp-main);
	- зафиксировано, что продукт должен выпускаться как цифровой продукт с installer и update mechanism;
	- зафиксировано, что продукт должен включать frontend template `nordic`, доступный как шаблон по умолчанию в настройках InstantCMS;
	- добавлен новый документ [LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md](../LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md);
	- roadmap и master plan пересобраны под hybrid-модель component + template.
- Какие файлы затронуты:
	- [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
	- [LANDING-BUILDER-DOCS-PACK-2026-04-03.md](../LANDING-BUILDER-DOCS-PACK-2026-04-03.md)
	- [LANDING-BUILDER-ROADMAP-2026-04-03.md](../LANDING-BUILDER-ROADMAP-2026-04-03.md)
	- [LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md](../LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md)
- Что проверено:
	- логика документов согласована по главному трекеру и порядку чтения.
- Какие риски остались:
	- hybrid-модель потребует сразу делать skeleton не только компонента, но и шаблона.
- Следующий шаг:
	- начать technical skeleton для `landingbuilder` и `nordic` одновременно.

### 2026-04-03 / динамические источники и режимы участия страниц

- Что планировалось:
	- формализовать, как блоки будут забирать данные из контентных типов и как builder будет участвовать в страницах частично или полностью.
- Что сделано:
	- добавлен документ [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](../LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md);
	- в master plan добавлен отдельный этап по data source resolver и query collections;
	- roadmap обновлен под модель manual + dynamic + query-driven data sources;
	- JSON contracts расширены под `query.collection`, route context и collection-capable blocks;
	- adapters/backend settings синхронизированы с participation modes: `full_takeover`, `hybrid_overlay`, `zone_injection`.
- Какие файлы затронуты:
	- [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](../LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md)
	- [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
	- [LANDING-BUILDER-DOCS-PACK-2026-04-03.md](../LANDING-BUILDER-DOCS-PACK-2026-04-03.md)
	- [LANDING-BUILDER-ROADMAP-2026-04-03.md](../LANDING-BUILDER-ROADMAP-2026-04-03.md)
	- [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](../LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
	- [LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md](../LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md)
	- [LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md](../LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md)
- Что проверено:
	- логика документов согласована по терминологии source types, collection blocks и participation modes.
- Какие риски остались:
	- механизм resolver/query collections пока существует только на уровне архитектуры;
	- еще не начат кодовый skeleton block registry, adapters и template integration.
- Следующий шаг:
	- сделать checkpoint и перейти к коду: skeleton `system/controllers/landingbuilder` + `templates/nordic`.

### 2026-04-03 / canvas, колонки и системные widgets

- Что планировалось:
	- разобрать принцип текущей grid/widget архитектуры InstantCMS и переложить его в продуктовую модель Нордик без показа технических row/col терминов пользователю.
- Что сделано:
	- исследован текущий pipeline InstantCMS: `layout_rows -> layout_cols -> position -> widgets_bind_pages -> cmsWidget render`;
	- зафиксировано, что responsive-логика у InstantCMS уже есть на уровне widths/order по breakpoint'ам;
	- добавлен отдельный документ [LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md](../LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md);
	- зафиксирована каноническая visual-модель Нордик: `section -> columns -> nodes`;
	- зафиксировано, что на холст должны добавляться и builder blocks, и стандартные widgets InstantCMS;
	- JSON contracts и roadmap обновлены под многосекционный canvas, 1/2/3-column presets и desktop/tablet/mobile toggles.
- Какие файлы затронуты:
	- [LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md](../LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md)
	- [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
	- [LANDING-BUILDER-DOCS-PACK-2026-04-03.md](../LANDING-BUILDER-DOCS-PACK-2026-04-03.md)
	- [LANDING-BUILDER-ROADMAP-2026-04-03.md](../LANDING-BUILDER-ROADMAP-2026-04-03.md)
	- [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](../LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
- Что проверено:
	- логика canvas docs согласована с текущей архитектурой layout и widgets InstantCMS.
- Какие риски остались:
	- bridge для системных widgets на canvas еще не реализован кодом;
	- потребуется отдельно продумать import/edit сценарии для уже существующих widget positions сайта.
- Следующий шаг:
	- сделать checkpoint и начать skeleton editor contracts и backend-экранов под canvas.

### 2026-04-03 / UX-шпаргалка canvas editor

- Что планировалось:
	- сделать короткий ориентир по одному главному экрану редактора, чтобы перед кодом было видно весь UX целиком.
- Что сделано:
	- добавлена шпаргалка [LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md](../LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md);
	- зафиксированы 4 зоны экрана: верхняя панель, левая библиотека, центральный холст, правый inspector;
	- отдельно зафиксирован MVP-набор: секции, колонки, device toggles, builder blocks и system widgets на одном экране.
- Какие файлы затронуты:
	- [LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md](../LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md)
	- [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
	- [LANDING-BUILDER-DOCS-PACK-2026-04-03.md](../LANDING-BUILDER-DOCS-PACK-2026-04-03.md)
	- [docs/WORKLOG.md](WORKLOG.md)
- Что проверено:
	- порядок чтения docs обновлен под быстрый UX-ориентир.
- Какие риски остались:
	- UX экрана уже зафиксирован, но backend endpoints и tpl skeleton еще не созданы.
- Следующий шаг:
	- сделать checkpoint и создать минимальный skeleton `landingbuilder` backend под pages/canvas.

### 2026-04-03 / backend skeleton landingbuilder

- Что планировалось:
	- начать кодовую фазу с безопасного backend skeleton под список страниц и visual canvas, без тяжелой реализации editor logic.
- Что сделано:
	- создан компонентный каркас `system/controllers/landingbuilder`;
	- добавлены `backend.php`, `frontend.php`, `model.php`;
	- добавлены backend actions `pages` и `canvas`;
	- добавлена базовая форма опций для canvas editor;
	- добавлены backend templates для `admincoreui` и `default`, чтобы skeleton сразу открывался в админке.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/backend.php](../system/controllers/landingbuilder/backend.php)
	- [system/controllers/landingbuilder/frontend.php](../system/controllers/landingbuilder/frontend.php)
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/backend/actions/pages.php](../system/controllers/landingbuilder/backend/actions/pages.php)
	- [system/controllers/landingbuilder/backend/actions/canvas.php](../system/controllers/landingbuilder/backend/actions/canvas.php)
	- [system/controllers/landingbuilder/backend/forms/form_options.php](../system/controllers/landingbuilder/backend/forms/form_options.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [templates/default/controllers/landingbuilder/backend/pages.tpl.php](../templates/default/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/default/controllers/landingbuilder/backend/canvas.tpl.php](../templates/default/controllers/landingbuilder/backend/canvas.tpl.php)
- Что проверено:
	- checkpoint создан перед кодовой фазой.
- Какие риски остались:
	- пока это skeleton без SQL, registry и runtime persistence;
	- системные widgets на холсте пока только как bridge-модель, без реального сохранения и исполнения.
- Следующий шаг:
	- добавить persistent data model для pages/canvas documents и backend endpoint'ы сохранения.

### 2026-04-03 / installable package, persistence и живой canvas backend

- Что планировалось:
	- перевести landingbuilder из mock backend в installable компонент с SQL persistence, bridge для системных widgets и рабочим backend-циклом сохранения canvas.
- Что сделано:
	- собран installable package в [packages/landingbuilder](../packages/landingbuilder) по схеме `manifest.ru.ini + install.sql + package/`;
	- добавлены таблицы `landingbuilder_pages`, `landingbuilder_page_versions`, `landingbuilder_page_widgets`;
	- model `landingbuilder` переведена на SQL-backed работу с fallback до установки пакета;
	- добавлены backend actions для `widgets_catalog`, `widget_options`, `canvas_save`, `create_page`, `versions`, `version_restore`;
	- admincoreui canvas переведен в интерактивный backend-экран: библиотека blocks/widgets, выбор колонки, вставка node, загрузка widget form, сохранение схемы и восстановление версий;
	- installable package синхронизирован с исходниками компонента.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/backend/actions/canvas.php](../system/controllers/landingbuilder/backend/actions/canvas.php)
	- [system/controllers/landingbuilder/backend/actions/pages.php](../system/controllers/landingbuilder/backend/actions/pages.php)
	- [system/controllers/landingbuilder/backend/actions/widgets_catalog.php](../system/controllers/landingbuilder/backend/actions/widgets_catalog.php)
	- [system/controllers/landingbuilder/backend/actions/widget_options.php](../system/controllers/landingbuilder/backend/actions/widget_options.php)
	- [system/controllers/landingbuilder/backend/actions/canvas_save.php](../system/controllers/landingbuilder/backend/actions/canvas_save.php)
	- [system/controllers/landingbuilder/backend/actions/create_page.php](../system/controllers/landingbuilder/backend/actions/create_page.php)
	- [system/controllers/landingbuilder/backend/actions/versions.php](../system/controllers/landingbuilder/backend/actions/versions.php)
	- [system/controllers/landingbuilder/backend/actions/version_restore.php](../system/controllers/landingbuilder/backend/actions/version_restore.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/manifest.ru.ini](../packages/landingbuilder/manifest.ru.ini)
	- [packages/landingbuilder/install.sql](../packages/landingbuilder/install.sql)
- Что проверено:
	- `php -l` проходит на model и новых backend actions;
	- Problems panel не показывает новых ошибок;
	- package/ содержит актуальные копии backend actions и шаблонов.
- Какие риски остались:
	- admincoreui canvas уже интерактивный, но default backend template пока заметно слабее по UX;
	- пока нет drag-and-drop, только управляемая вставка и редактирование;
	- frontend preview/runtime-рендер builder pages еще не реализован.
- Следующий шаг:
	- связать сохраненный canvas document с frontend/template runtime и начать реальный page adapter/render pipeline.

### 2026-04-03 / drag-and-drop и расширенный inspector canvas

- Что планировалось:
	- довести backend canvas до более взрослого состояния: полноценное перемещение секций и node-элементов, а также убрать зависимость от prompt-редактирования в inspector.
- Что сделано:
	- `normalizeSchema()` в model расширен значениями по умолчанию для visibility, widths, settings и node meta-полей;
	- admincoreui canvas переведен на нативный drag-and-drop для секций и node между колонками;
	- правый inspector переведен на структурированное редактирование section/column/node свойств без prompt-диалогов;
	- в canvas добавлена device-aware индикация скрытых элементов для текущего breakpoint;
	- installable package синхронизирован по актуальным копиям model и admincoreui canvas template.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
- Что проверено:
	- `php -l` проходит на model и admincoreui canvas template;
	- Problems panel не показывает новых ошибок в измененных исходниках;
	- package mirror обновлен после изменения исходников.
- Какие риски остались:
	- default backend template пока не доведен до того же UX-уровня, что и admincoreui;
	- frontend runtime и page adapters еще не используют сохраненный canvas schema;
	- drag-and-drop проверен на уровне структуры и синтаксиса, но еще не проходил отдельный ручной smoke-test в браузере.
- Следующий шаг:
	- подключить сохраненный canvas schema к frontend runtime, затем собрать первый рабочий page adapter pipeline для режимов участия страницы.

### 2026-04-03 / зафиксирован runtime-first workflow

- Что планировалось:
	- закрепить рабочий режим разработки так, чтобы он не потерялся между этапами реализации и packaging.
- Что сделано:
	- в docs зафиксирован runtime-first workflow для `Нордик`;
	- отдельно закреплено правило: сначала работа и проверка в InstantCMS-контуре, затем синхронизация `packages/landingbuilder/package/` на каждом стабильном шаге;
	- README и project docs синхронизированы с этим режимом.
- Какие файлы затронуты:
	- [docs/DEVELOPMENT-WORKFLOW.md](DEVELOPMENT-WORKFLOW.md)
	- [docs/PROJECT-OVERVIEW.md](PROJECT-OVERVIEW.md)
	- [README.md](../README.md)
- Что проверено:
	- правило отражено и в process docs, и в общей навигации репозитория.
- Какие риски остались:
	- сам workflow зафиксирован, но его еще нужно последовательно выдерживать на следующих кодовых этапах.
- Следующий шаг:
	- продолжить backend/frontend разработку `Нордик` уже в зафиксированном runtime-first режиме.

### 2026-04-03 / русификация backend-редактора

- Что планировалось:
	- привести backend UI `Нордик` к русскому, простому и нетехническому виду для редакторов.
- Что сделано:
	- переведены основные пользовательские тексты в `admincoreui` и `default` шаблонах страниц и редактора;
	- заменены технические подписи режимов, статусов, устройств, layout-схем и типов узлов на понятные русские названия;
	- для библиотеки блоков добавлены русские названия и короткие описания;
	- в inspector добавлены help tooltip-подсказки у ключевых полей;
	- обновлены package-копии шаблонов после финальной правки формулировок.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/default/controllers/landingbuilder/backend/canvas.tpl.php](../templates/default/controllers/landingbuilder/backend/canvas.tpl.php)
	- [templates/default/controllers/landingbuilder/backend/pages.tpl.php](../templates/default/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/backend/pages.tpl.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/backend/pages.tpl.php)
- Что проверено:
	- `php -l` проходит на source и package-копиях обоих canvas templates;
	- финальная синхронизация source -> package выполнена после последних правок.
- Какие риски остались:
	- ручной browser smoke-test тултипов, drag-and-drop и загрузки системных виджетов еще не проведен;
	- в следующих backend/frontend экранах нужно сразу держать тот же стандарт простого русского интерфейса.
- Следующий шаг:
	- вручную пройти editor flow в админке и затем перейти к frontend runtime/page adapters.

### 2026-04-03 / первый frontend runtime и adapter pipeline

- Что планировалось:
	- подключить сохраненную canvas schema к frontend runtime, собрать первый adapter pipeline и убрать функциональный разрыв между `admincoreui` и `default` backend templates.
- Что сделано:
	- добавлен frontend action preview/runtime для `landingbuilder` с маршрутом просмотра страницы по ключу;
	- в model добавлен первый adapter registry и runtime pipeline для `standalone_landing`, `content_category_generic`, `user_profile`;
	- runtime теперь группирует секции по adapter zones и подмешивает реальные данные системных виджетов в schema перед рендером;
	- добавлен frontend template runtime с рендером builder blocks, штатных widgets и preview-состояния для неопубликованных страниц;
	- в backend добавлены ссылки предпросмотра из списка страниц и из canvas editor;
	- `default` backend templates переведены на общий источник `admincoreui`, чтобы больше не отставать по UX и не дублировать логику;
	- добиты русские названия для блоков `ads.filter-bar` и `profile.quick-stats`.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/actions/view.php](../system/controllers/landingbuilder/actions/view.php)
	- [system/controllers/landingbuilder/backend/actions/pages.php](../system/controllers/landingbuilder/backend/actions/pages.php)
	- [system/controllers/landingbuilder/backend/actions/canvas.php](../system/controllers/landingbuilder/backend/actions/canvas.php)
	- [templates/default/controllers/landingbuilder/view.tpl.php](../templates/default/controllers/landingbuilder/view.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [templates/default/controllers/landingbuilder/backend/pages.tpl.php](../templates/default/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/default/controllers/landingbuilder/backend/canvas.tpl.php](../templates/default/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/actions/view.php](../packages/landingbuilder/package/system/controllers/landingbuilder/actions/view.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/view.tpl.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/view.tpl.php)
- Что проверено:
	- `php -l` проходит на source и package-копиях model, frontend action, backend actions и шаблонов;
	- Problems panel не показывает новых ошибок в измененных файлах;
	- source и package mirror синхронизированы после всех правок этой фазы.
- Какие риски остались:
	- ручной smoke-test drag-and-drop, сохранения и восстановления версий в браузере все еще нужно пройти руками;
	- runtime пока работает как первый preview/pipeline и еще не внедрен в системные страницы сайта через hooks/overlay поверх их реального HTML.
- Следующий шаг:
	- вручную пройти smoke-test в админке и затем развивать runtime из preview в полноценный overlay/injection pipeline для системных страниц.

### 2026-04-03 / стабилизация сохранения, предпросмотра и закрытие дня

- Что планировалось:
	- добить рабочий цикл редактора без 503-ошибок, убрать остатки технических формулировок и подтвердить, что пользовательский сценарий реально проходит в живой админке.
- Что сделано:
	- найдена и исправлена причина падения сохранения и восстановления версий: при синхронизации widget-узлов `widget_name` больше не уходит в `NULL`;
	- сохранение и восстановление версий обернуты в защитный `try/catch` с понятным русским сообщением об ошибке;
	- найден и исправлен runtime-сбой предпросмотра: frontend-контроллер `landingbuilder` приведен к ожидаемому контракту InstantCMS по имени класса;
	- в frontend runtime добавлены безопасные ключи данных для системных виджетов, чтобы рендер не падал на внутренних ожиданиях ядра;
	- дочищены видимые технические формулировки в редакторе, чтобы не торчало слово `class` в пользовательских подписях;
	- после исправлений source и package mirror повторно синхронизированы.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/backend/actions/canvas_save.php](../system/controllers/landingbuilder/backend/actions/canvas_save.php)
	- [system/controllers/landingbuilder/backend/actions/version_restore.php](../system/controllers/landingbuilder/backend/actions/version_restore.php)
	- [system/controllers/landingbuilder/frontend.php](../system/controllers/landingbuilder/frontend.php)
	- [system/controllers/landingbuilder/actions/view.php](../system/controllers/landingbuilder/actions/view.php)
	- [templates/default/controllers/landingbuilder/view.tpl.php](../templates/default/controllers/landingbuilder/view.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/frontend.php](../packages/landingbuilder/package/system/controllers/landingbuilder/frontend.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/view.tpl.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/view.tpl.php)
- Что проверено:
	- `php -l` проходит на исходниках и package-копиях измененных PHP-файлов;
	- просмотр логов показал устранение предыдущих корневых причин: ошибки по `widget_name = NULL`, падение preview и загрузчик frontend-контроллера были разобраны и исправлены;
	- итоговый пользовательский smoke-test пройден: сохранение, предпросмотр и общий рабочий сценарий в админке снова работают.
- Какие риски остались:
	- preview/runtime уже стабилен для текущего сценария, но следующий этап с overlay/injection в системные страницы все равно потребует отдельной ручной проверки;
	- CLI-проверки полного runtime-контура ограничены локальной конфигурацией PHP CLI, поэтому основная валидация по-прежнему завязана на живой контур и логи сайта.
- Следующий шаг:
	- на следующей сессии переходить от preview-маршрута к встраиванию builder в реальные системные страницы и зоны.

### 2026-04-03 / ориентир на следующую сессию

- С чего начать без повторной раскопки:
	- сначала создать новый checkpoint перед этапом интеграции в реальные страницы;
	- затем определить первую целевую системную страницу для внедрения: лучше начать с одной управляемой точки, а не со всего сайта сразу.
- Ближайший рабочий порядок:
	- подключить `landingbuilder` не только к preview route, а к реальной странице через безопасный hook/adapter pipeline;
	- выбрать и реализовать первый режим участия страницы: `zone_injection` или `hybrid_overlay` для одного конкретного сценария;
	- проверить, как builder-секции встраиваются в живой HTML страницы без поломки штатного layout и системных widgets;
	- после этого пройти короткий regression-check: canvas save, version restore, preview, реальная страница;
	- в конце шага снова синхронизировать `packages/landingbuilder/package/` и обновить docs.
- Что не делать в лоб:
	- не пытаться сразу подключать все типы страниц;
	- не разъезжаться между runtime и package mirror;
	- не трогать одновременно overlay, data-resolver и массовую локализацию новых экранов в одном заходе.
- Цель следующей сессии:
	- получить первый рабочий сценарий, где builder влияет уже не только на предпросмотр, а на реальную системную страницу сайта в контролируемой зоне.
