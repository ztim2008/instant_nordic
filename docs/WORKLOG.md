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
