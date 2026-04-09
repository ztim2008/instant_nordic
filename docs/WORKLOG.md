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

## 2026-04-08

- Что планировалось:
	- закрыть регресс native body в режиме `100%`: full width не должен отключать `content_sidebar_left/right`;
	- стабилизировать UX в canvas для A/A+ и переключения `12/12 <-> 100%`.
- Что сделано:
	- подтвержден root-cause визуальной "поломки": autoscale применялся к sidebar-слотам и создавал эффект overlay;
	- в runtime отключен base-autoscale для `content_sidebar_left/right`, чтобы сайдбар-контент всегда рендерился в своей колонке;
	- в canvas отключены режимы A/A+ для sidebar-зон (UI + guard в action/input flow);
	- добавлен явный режим ширины native body (`grid` / `full`) и slider `native_body_full_padding` (0..60, default 20);
	- устранена связка, из-за которой `full` принудительно переводил body в single-column и скрывал sidebars;
	- в Nordic runtime шаблоне full width отвязан от условия наличия сайдбаров: `100%` теперь поддерживает 0/1/2 sidebars;
	- добавлен отдельный CSS-режим `lb-native-body-layout--autoscale` для full-width три-колоночного native body layout с управляемым padding;
	- синхронизированы package mirrors для `landingbuilder`, `nordicbuilder`, `nordic`.
	- для page-bindings зафиксирован runtime-resolver: `page.homepage` работает без native body, любые остальные `page.*` автоматически идут через `internal_content_generic` (native body + sidebars).
	- как канонический вектор зафиксирована модель "надстройка без правки ядра": update-safe интеграция через tokens/contracts/adapters;
	- составлена карта глобального дизайн-контроля для дизайнера (что уже покрыто и чем можно управлять по всему сайту).
	- добавлен взрослый roadmap развития глобальной дизайн-системы для журнального курса (этапы, критерии готовности, DoD).
	- добавлен практический 2-недельный execution-план по коду: приоритеты P0/P1, задачи по дням, конкретные файлы и единая система статусов 🟡/🔵/🟢.
	- в карточки экрана «Страницы сайта» добавлен быстрый переход «Правила применения» с фильтром по `page_key`, чтобы править ошибочные маски без ручного поиска правила;
	- backend action `bindings` научен принимать `page_key`: если правило уже есть, оно авто-открывается; если нет — форма предзаполняется этим `page_key` для быстрого создания;
	- в action `pages` добавлен `bindings_url` для nordicbuilder и синхронизированы package mirrors.
	- экран `Правила применения` получил простой режим для новичков: выбор «где показывать» через dropdown и исключение типов контента через чекбоксы (без ручного JSON/масок);
	- в простой режим подгружаются все доступные content types из `content->getContentTypes()`, для overlay category автоматически собирается `matching.route_params` с учетом исключений;
	- редиректы в `nordicbuilder/bindings` переведены на явный admin URL (`/admin/controllers/edit/nordicbuilder/bindings...`), чтобы исключить 404 после сохранения/удаления.
	- в модалке «Новый макет страницы» добавлен простой сценарий без ручных URL-масок: новый вариант применения «Категории контента (выбрать типы, без масок)»;
	- для этого варианта подгружаются все content types и показываются чекбоксы «не показывать», а route_params собирается автоматически;
	- поле «URL-маски» помечено как экспертное, чтобы новичок не заходил в ручной ввод без необходимости.
	- для `content_category_generic` в adapter-зонах включена полноценная схема Instant-категории: `content_sidebar_left` + `content_sidebar_right` + `after_content`;
	- shell variant `category-pages` переведен в режим `two_sidebars` + `show_after_content=1`, чтобы базовая категория повторяла логику «центр + 2 сайдбара + низ»;
	- preview URL в экране «Страницы» стал контекстным по bindings: для category/profile правил открывается реальный маршрут (например `/news`, `/board`, `/users/1`), а не только `/nordicbuilder/view/{key}`.
	- зафиксирована граница overlay-first: для маршрутов `content/category` и `users/profile` отключен generic full-takeover `page.*`, чтобы нативный body/grid InstantCMS оставался базой, а builder работал как надстройка;
	- в publish-пайплайне добавлен guard против пустого SSR HTML: вместо `NULL` пишется безопасный placeholder-комментарий, чтобы исключить 503 при записи в БД;
	- создание страницы из модалки переведено в truly-empty режим: через флаг `disable_starter_seed` отключено автозаполнение starter-секциями.
	- отключен synthetic fallback demo-страниц в `landingbuilder`: список страниц после установки теперь действительно пустой, а неудаляемые fallback-карточки больше не появляются в админке.
	- удаление страницы усилено для legacy-ключей: `deletePageByKey()` теперь ищет запись и по sanitized key, и по исходному raw key.
	- шаг 1 для homepage-flow: в мастере создания страниц вариант «Главная страница» теперь явно назначает adapter `standalone_landing`, чтобы не попадать в internal/native-body сценарий;
	- в `nordicbuilder/backend/actions/create_page.php` добавлен прокид `disable_starter_seed`, чтобы поведение «пустого старта» работало одинаково и через nordicbuilder action.
	- шаг 2 (MVP) для canvas: добавлены быстрые операции для секций и элементов — `Дублировать` и `Скрыть/Показать на текущем устройстве` прямо из inspector, с сохранением текущей схемы и выделения.
	- устранено перекрытие на canvas: панель `СТЕК/T/M/I/A` больше не закрывает кнопку `+` добавления блока в колонке (зарезервирован верхний отступ внутри секции).
	- Step 2.1: в самой карточке секции на canvas добавлены контекстные quick-actions (дублирование и скрыть/показать на текущем устройстве) рядом с удалением, без необходимости открывать inspector.
	- Step 2.2: в topbar canvas добавлены `Импорт схемы` и `Экспорт схемы` (JSON): экспорт выгружает текущую схему страницы в файл, импорт принимает JSON (wrapper `schema` или прямой объект с `sections`) и заменяет текущий canvas после подтверждения.
- Какие файлы затронуты:
	- [templates/default/controllers/landingbuilder/runtime_renderer.php](../templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [templates/nordic/main.tpl.php](../templates/nordic/main.tpl.php)
	- [system/controllers/landingbuilder/helpers/runtime_styles.php](../system/controllers/landingbuilder/helpers/runtime_styles.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [packages/nordicbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php](../packages/nordicbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/model.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordic/package/templates/nordic/main.tpl.php](../packages/nordic/package/templates/nordic/main.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/helpers/runtime_styles.php](../packages/landingbuilder/package/system/controllers/landingbuilder/helpers/runtime_styles.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/helpers/runtime_styles.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/helpers/runtime_styles.php)
	- [docs/worklogs/2026-04-08-native-body-fullwidth-sidebars.md](worklogs/2026-04-08-native-body-fullwidth-sidebars.md)
	- [docs/NORDICBUILDER-DESIGN-SYSTEM-GLOBAL-CONTROL-MAP.md](NORDICBUILDER-DESIGN-SYSTEM-GLOBAL-CONTROL-MAP.md)
	- [LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md](../LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md)
	- [LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md](../LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md)
	- [system/controllers/nordicbuilder/backend/actions/pages.php](../system/controllers/nordicbuilder/backend/actions/pages.php)
	- [system/controllers/nordicbuilder/backend/actions/bindings.php](../system/controllers/nordicbuilder/backend/actions/bindings.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/pages.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/pages.php)
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/model.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/bindings.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/bindings.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/admincoreui/controllers/nordicbuilder/backend/bindings.tpl.php](../templates/admincoreui/controllers/nordicbuilder/backend/bindings.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/nordicbuilder/backend/bindings.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/nordicbuilder/backend/bindings.tpl.php)
	- [system/controllers/landingbuilder/backend/actions/pages.php](../system/controllers/landingbuilder/backend/actions/pages.php)
	- [system/controllers/nordicbuilder/backend/actions/pages.php](../system/controllers/nordicbuilder/backend/actions/pages.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/pages.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/pages.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/pages.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/pages.php)
	- [system/controllers/nordicbuilder/model.php](../system/controllers/nordicbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/model.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/model.php)
	- [system/controllers/landingbuilder/backend/actions/create_page.php](../system/controllers/landingbuilder/backend/actions/create_page.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php)
- Что проверено:
	- `php -l` проходит на измененных live и package mirror файлах (`canvas.tpl.php`, `main.tpl.php`, `runtime_renderer.php`, `runtime_styles.php`, `model.php`);
	- `php -l` дополнительно проходит на новых изменениях `nordicbuilder/pages.php`, `nordicbuilder/bindings.php` и `pages.tpl.php` (live + mirrors);
	- editor diagnostics не показывают новых ошибок в затронутых файлах;
	- ручная проверка подтверждает ожидаемое поведение: `100%` работает вместе с 1/2 sidebars.
	- `php -l` проходит для обновленных `nordicbuilder/backend/actions/bindings.php` и `nordicbuilder/backend/bindings.tpl.php` (live + mirror).
	- `php -l` проходит для обновленных `landingbuilder/nordicbuilder backend pages actions` и `pages.tpl.php` (live + mirrors).
	- `php -l` проходит для обновленных `landingbuilder model.php` и `pages.php` actions (live + mirrors).
	- `php -l` проходит для обновленных `nordicbuilder/model.php`, `landingbuilder/backend/actions/create_page.php`, `templates/nordic/main.tpl.php` (live + mirrors).
	- `php -l` проходит для обновленных `landingbuilder/model.php` (live + mirrors) после отключения synthetic fallback demo-страниц.
	- `php -l` повторно проходит для `landingbuilder/model.php` (live + mirrors) после усиления удаления по legacy/raw key.
	- `php -l` проходит для обновленных `nordicbuilder/backend/actions/create_page.php` и `landingbuilder/backend/pages.tpl.php` (live + mirrors) после фикса homepage-flow.
	- `php -l` проходит для обновленных `canvas.tpl.php` (live + mirrors) после добавления операций duplicate/hide на уровне inspector.
	- `php -l` проходит для обновленных `canvas.tpl.php` (live + mirrors) после CSS-фикса перекрытия `СТЕК/T/M/I/A` и кнопки `+` на canvas.
	- `php -l` проходит для обновленных `canvas.tpl.php` (live + mirrors) после реализации Step 2.1/2.2 (section quick-actions на canvas + JSON import/export schema).
- Какие риски остались:
	- не выполнен отдельный мобильный smoke на длинных страницах с кастомными legacy-виджетами в сайдбарах;
	- у отдельных старых виджетов с фиксированной шириной возможен локальный overflow в full-width режиме.
	- если для одного `page_key` существует несколько binding-правил, автопереход по кнопке откроет первое найденное в индексе правило.
	- визуальный smoke новой формы `Правила применения` не выполнен в встроенном браузере из-за 403 (неавторизованная сессия админа в инструменте).
	- не выполнен полный авторизованный smoke нового сценария: удаление старого binding -> создание truly-empty страницы -> привязка -> preview/publish в одной сессии.
- Следующий шаг:
	- сделать checkpoint-коммит этой итерации и выполнить короткий visual smoke (`/`, `/news`, `/board`, `/users/1`) для desktop/mobile + проверку удаления bindings и truly-empty create flow.

## 2026-04-08 (конец дня, архитектурный стоп)

- Что планировалось:
	- завершить день без наращивания новых patch-веток и зафиксировать управляемый стоп по текущему тупику.
- Что сделано:
	- зафиксирован инженерный deadlock: часть inspector-настроек не влияет на canvas/runtime, а route-binding поведение остается неоднородным;
	- принято решение о частичном архитектурном повороте: переписать проблемный слой компонента с сохранением основной продуктовой логики visual-first;
	- зафиксирована стратегия: единый pipeline выбора страницы, временно единый policy для диагностики без усложняющей role-разницы, cleanup inspector-controls по принципу «влияет или удаляем»;
	- оформлен отдельный документ с антикризисным планом фаз A-D и DoD выхода из тупика.
- Какие файлы затронуты:
	- [docs/worklogs/2026-04-08-architecture-deadlock-pivot-strategy.md](worklogs/2026-04-08-architecture-deadlock-pivot-strategy.md)
	- [docs/WORKLOG.md](WORKLOG.md)
- Что проверено:
	- документ стратегии создан и доступен в `docs/worklogs`;
	- общий журнал обновлен ссылкой на стратегический документ.
- Какие риски остались:
	- до архитектурного cut сохраняется риск непредсказуемого поведения route/binding в части внутренних сценариев;
	- до cleanup inspector возможны ложные ожидания от неэффективных controls.
- Следующий шаг:
	- перед кодовым рефактором сделать отдельный checkpoint и стартовать P0-аудит route matrix + инвентаризацию inspector controls по новой стратегии.

## 2026-04-09

- Что планировалось:
	- закрыть практическую логистику создания страниц в стиле Instant: главная с демо, общесайтовая страница со сквозными секциями, и отдельный сценарий страницы для одного типа контента.
- Что сделано:
	- в мастере создания страниц добавлен новый сценарий «Категория одного типа контента» с явным выбором ctype;
	- создание страницы теперь передает дополнительные флаги в backend: `inherit_global_sections` и `use_as_global_sections_source`;
	- для `all_except_homepage` включена роль источника сквозных секций, для homepage/overlay-сценариев включено наследование сквозных секций;
	- для homepage и общесайтовой страницы включен demo starter seed вместо принудительно пустого документа;
	- в `landingbuilder` добавлен runtime-механизм наследования сквозных секций из одной общесайтовой страницы-источника;
	- добавлен starter schema для `internal_content_generic` (сквозная навигация сверху и нижний CTA-блок), чтобы быстрее собрать общий каркас сайта;
	- добавлены layout-флаги в normalize pipeline, чтобы поведение было детерминированным и в live, и после сохранения/чтения схемы;
	- live-изменения синхронизированы в package mirrors `landingbuilder` и `nordicbuilder`.
	- утвержден RFC по модели коммерческой надстройки без правок ядра (ADR-0002);
	- выполнен P0-аудит route matrix и зафиксирован отдельным документом;
	- выполнена P0-инвентаризация inspector controls (active/review/candidate-deprecated) отдельным документом;
	- усилен backup/checkpoint flow: `db-backup.sh` получил preflight-диагностику и override dump-учетки через `DB_DUMP_*`, а `pre-change-checkpoint.sh` получил режимы `required|best-effort|skip`.
	- восстановлен строгий checkpoint перед runtime-рефактором: backup БД прошел через dump override (`DB_DUMP_USER=root`, `DB_DUMP_HOST=localhost`), затем создан snapshot tag `snapshot/20260409-073048`;
	- в runtime добавлен debug trace выбора `effective page key` на двух уровнях: resolver (`landingbuilder/model.php`) и шаблонные ветки (`templates/nordic/main.tpl.php`);
	- для admin-debug добавлен HTML trace marker `lb-effective-page-trace` при `lb_trace=1`, чтобы подтверждать ветку выбора напрямую через HTTP smoke;
	- выполнен авторизованный smoke по 5 маршрутам (`/`, `/board`, `/board/7-prodam-kvartiru-v-novostroike.html`, `/board/nedvizhimost`, `/users/1`) с фиксацией route-context/effective key.
	- выполнен targeted route-classifier cut: ctype-style маршруты (`/board`, `/board/<slug>`, `/board/<item>.html`) и `/users/<id>` нормализуются в `content/index|category|item` и `users/profile`;
	- повторный авторизованный smoke по тем же 5 маршрутам подтвердил, что category/profile детерминированно уходят в overlay-ветку;
	- P0 route-matrix переведен в статус closed (runtime-verified по всем 5 веткам).
	- подготовлен отдельный ручной чеклист визуального parity-smoke для category/profile после route-classifier cut.
	- parity-smoke чеклист закрыт автоматическим проходом (admin+guest): все целевые URL вернули `200`, category/profile подтвердили overlay-ветку по trace, статус чеклиста `PASS`.
	- стартован P1 `Inspector sanity`, первая порция cleanup выполнена: в Node Inspector скрыто поле `notes` как deprecated/no-runtime-effect control.
	- cleanup сделан безопасно: runtime-контракты не менялись, старые данные `notes` в JSON сохранены для backward compatibility.
	- выполнен P1 pass 2: технические поля оформления (`settings.css_class`, `settings.background_class`, `class_name`) скрыты в базовом режиме inspector и доступны только через `lb_inspector_advanced=1`.
	- выполнен P1 pass 3: упрощены редакторские подписи `title/label` (`Имя секции`, `Имя колонки`, `Имя элемента`), а подробные подсказки для них показываются только в advanced mode.
	- выполнен короткий авторизованный canvas-smoke: `homepage` на desktop/tablet/mobile и page keys `home`, `ver`, `glav` на desktop отдают `HTTP 200` и содержат ожидаемые маркеры (`Имя секции`, quick-actions дублирования/видимости, `lb_inspector_advanced`).
	- выполнен интерактивный admin-smoke quick-actions на `homepage`: клики `duplicate/toggle visibility` для section и node, затем `Сохранить` и `reload`; изменения persisted (дубликаты и состояния видимости сохранились).
	- закрыт шаг 1 по backup для non-root: создан тех-пользователь `lbops` и отдельная password dump-учетка `lb_dump_ops`, backup успешно выполняется от `lbops` без root override.
	- `scripts/db-backup.sh` обновлен: поддержка локального env (`backups/db/.db-dump.env`), корректная передача пароля в `mysql/mysqldump` через аргументы, поддержка пустого `DB_DUMP_PASS`, и `mysqldump --no-tablespaces`.
	- выполнен P1 pass 4 (cleanup remaining review-полей): в колонке `align` переименован в понятный `Расположение внутри колонки`, у блока `source_key` переименован в `Тип элемента`, а техполе `source_key` для non-block скрыто в базовом режиме и оставлено только в advanced.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/backend/actions/create_page.php](../system/controllers/landingbuilder/backend/actions/create_page.php)
	- [system/controllers/nordicbuilder/backend/actions/create_page.php](../system/controllers/nordicbuilder/backend/actions/create_page.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/model.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/create_page.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/create_page.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [docs/adr/ADR-0002-COMMERCIAL-OVERLAY-NO-CORE-CHANGES.md](adr/ADR-0002-COMMERCIAL-OVERLAY-NO-CORE-CHANGES.md)
	- [docs/worklogs/2026-04-09-route-matrix-audit.md](worklogs/2026-04-09-route-matrix-audit.md)
	- [docs/worklogs/2026-04-09-inspector-controls-inventory.md](worklogs/2026-04-09-inspector-controls-inventory.md)
	- [scripts/db-backup.sh](../scripts/db-backup.sh)
	- [scripts/pre-change-checkpoint.sh](../scripts/pre-change-checkpoint.sh)
	- [docs/ROLLBACK-AND-RECOVERY.md](ROLLBACK-AND-RECOVERY.md)
	- [templates/nordic/main.tpl.php](../templates/nordic/main.tpl.php)
	- [packages/nordic/package/templates/nordic/main.tpl.php](../packages/nordic/package/templates/nordic/main.tpl.php)
	- [templates/nordic/page_context.php](../templates/nordic/page_context.php)
	- [packages/nordic/package/templates/nordic/page_context.php](../packages/nordic/package/templates/nordic/page_context.php)
	- [docs/checklists/NORDICBUILDER-ROUTE-PARITY-SMOKE-2026-04-09.md](checklists/NORDICBUILDER-ROUTE-PARITY-SMOKE-2026-04-09.md)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
- Что проверено:
	- `php -l` без ошибок для всех измененных live/mirror PHP и tpl-файлов;
	- `cmp -s` подтверждает parity между live и package mirrors;
	- editor diagnostics не показывают новых ошибок в измененных файлах.
	- `bash -n` проходит для `scripts/db-backup.sh` и `scripts/pre-change-checkpoint.sh`.
	- `scripts/db-backup.sh` в текущем окружении по-прежнему репортит MySQL 1045, но теперь дает явную диагностику и поддерживает override dump-учетки.
	- strict checkpoint успешно выполнен через `pre-change-checkpoint.sh` (режим `required`): создан backup `backups/db/builders-20260409-073048.sql.gz` и git tag `snapshot/20260409-073048`.
	- `php -l` проходит для обновленных `landingbuilder/model.php` и `templates/nordic/main.tpl.php` (live + package mirrors).
	- авторизованный smoke (`icms[auth]` + `lb_trace=1`) по 5 маршрутам возвращает `HTTP 200` и отдает trace marker `lb-effective-page-trace` на каждом маршруте.
	- в trace зафиксированы фактические route-context и итог выбора: homepage -> `template.takeover-applied`, остальные 4 маршрута -> `template.takeover-skip (empty-effective-page-key)`.
	- дополнительный strict checkpoint перед route-cut выполнен: backup `backups/db/builders-20260409-075202.sql.gz`, git tag `snapshot/20260409-075203`.
	- `php -l` проходит для обновленных `templates/nordic/page_context.php` и `templates/nordic/main.tpl.php` (live + package mirrors).
	- повторный trace smoke подтверждает ожидаемую нормализацию и ветвление:
	  - `/board` -> `content/index`;
	  - `/board/7-prodam-kvartiru-v-novostroike.html` -> `content/item`;
	  - `/board/nedvizhimost` -> `content/category` + `overlay` ветка;
	  - `/users/1` -> `users/profile` + `overlay` ветка.
	- авто-smoke (admin+guest) по parity-checklist завершен со статусом `PASS`; результат зафиксирован в [docs/checklists/NORDICBUILDER-ROUTE-PARITY-SMOKE-2026-04-09.md](checklists/NORDICBUILDER-ROUTE-PARITY-SMOKE-2026-04-09.md).
	- strict checkpoint перед P1 pass 1 выполнен: backup `backups/db/builders-20260409-081238.sql.gz`, git tag `snapshot/20260409-081239`.
	- `php -l` проходит для обновленных `canvas.tpl.php` (live + package mirrors) после скрытия `notes`.
	- strict checkpoint перед P1 pass 2 выполнен: backup `backups/db/builders-20260409-081627.sql.gz`, git tag `snapshot/20260409-081628`.
	- `php -l` проходит для обновленных `canvas.tpl.php` (live + package mirrors) после ввода `advanced mode` для технических полей.
	- strict checkpoint перед P1 pass 3 выполнен: backup `backups/db/builders-20260409-082012.sql.gz`, git tag `snapshot/20260409-082013`.
	- `php -l` проходит для обновленных `canvas.tpl.php` (live + package mirrors) после cleanup `title/label`.
	- `cmp -s` подтверждает parity между live `canvas.tpl.php` и package mirrors после P1 pass 3.
	- авторизованный admin-smoke по canvas подтверждает `HTTP 200` на `homepage` (desktop/tablet/mobile) и `home`/`ver`/`glav` (desktop), с наличием ожидаемых P1-маркеров в HTML.
	- интерактивный smoke quick-actions подтвержден в браузере на `homepage`: `duplicate-section`, `toggle-section-device-visibility`, `duplicate-node`, `toggle-node-device-visibility` отрабатывают и сохраняются после `Сохранить` + `reload`.
	- backup успешно выполняется от `lbops` через `./scripts/db-backup.sh` (non-root сценарий подтвержден).
	- `php -l` и `cmp -s` подтверждают корректность и parity `canvas.tpl.php` (live + package mirrors) после pass 4.
	- runtime HTML `canvas/homepage` содержит новые маркеры cleanup (`Расположение внутри колонки`, `Тип элемента`, `Ключ источника (тех.)` в advanced-ветке).
	- визуальный smoke после pass 4 выполнен в режимах `Планшет` и `Телефон`: viewport корректно переключается (`820px` и `430px`), кнопки быстрых действий показывают правильный device-контекст (`Скрыть секцию на Планшет/Телефон`).
	- в recovery-док добавлен runbook по ротации пароля dump-учетки (`lb_dump_ops`) и проверке backup после ротации.
	- выполнен targeted fix «телефонная сетка не реагирует»: middle-zone канвы теперь строится по текущему `Body Layout` (`1`, `2-left`, `2-right`, `3`) и реально перестраивается при клике режимов в preview `Телефон`.
	- в inspector секции добавлена понятная подсказка для кейса с одной колонкой (почему сетка может визуально не меняться).
	- browser-check подтверждает реакцию канвы в `Телефон`: `Body Layout` переключается между `1`, `3`, `2-right`, и CSS grid `lb-zone-workspace__middle` меняет фактические колонки (`1`, `3`, `2`).
	- `php -l` и `cmp -s` повторно проходят для `canvas.tpl.php` (live + package mirrors) после phone-grid fix.
	- для предсказуемого размещения блоков по страницам в resolver удален неявный fallback global source (`site-all/site-frame/all-site`): источник сквозных секций теперь только explicit (`global_sections_source_page_key`) или явно отмеченная страница `use_as_global_sections_source`.
	- при наличии нескольких подходящих binding-правил выбор теперь детерминирован: используется rank (priority + specificity) и стабильный tie-break по `binding_key`, вместо зависимости от `updated_at`.
	- для `resolveAdapterKeyFromBindingOptions` добавлен тот же deterministic rank, чтобы adapter не "прыгал" при одинаковых масках и частых правках правил.
	- `php -l` проходит для обновленного `landingbuilder/model.php` (live + package mirrors) после deterministic update в `global sections source` и `binding resolver`.
	- в UI экрана `Страницы` добавлен явный селектор `Источник сквозных секций` с сохранением в `landingbuilder` options (`global_sections_source_page_key`) через новые AJAX actions.
	- в UI экрана `Правила применения` добавлено поле `Priority` (форма + таблица), а мастер создания страницы теперь передает `priority` при быстром создании binding.
	- согласован следующий продуктовый шаг: full-cycle demo после установки компонента, чтобы новый пользователь видел рабочий пример вместо пустого canvas.
	- создан канонический spec demo-cycle с двумя режимами (`Quick Demo` и `Full Template`), binding matrix, правилами idempotent install/remove и DoD для onboarding + bug-hunting.
	- реализован Stage 1 backend seed service для `Quick Demo`: в `nordicbuilder` добавлены idempotent методы `installQuickDemo()` и `removeQuickDemo()` с отчетом по страницам/правилам и управлением `global_sections_source_page_key`.
	- добавлены новые AJAX actions `install_demo` и `remove_demo` в backend `nordicbuilder` (live + package mirror) для вызова demo install/remove из админки.
	- выполнен e2e прогон full-cycle: первый `install_demo` создает 7 demo-страниц и 7 demo-правил, повторный `install_demo` обновляет их без дублей, `remove_demo` удаляет demo-сущности и сбрасывает explicit source в auto.
	- реализован Stage 2 UI в экране `Страницы`: добавлен блок `Demo Content` с кнопками `Установить Quick Demo` и `Удалить Demo`, текущим статусом режима и JS-обработчиками с confirm/summary/reload.
	- выполнен интерактивный browser-smoke по UI-кнопкам: `Удалить Demo` переключает статус в `Demo выключен`, `Установить Quick Demo` возвращает `Quick Demo установлен` и поднимает demo-страницы/правила обратно.
	- исправлена регрессия в ссылке `Правила применения` на карточках страниц: убран двойной префикс `/admin/controllers/edit`, URL теперь ведет на корректный admin-route `.../nordicbuilder/bindings?page_key=...`.
	- реализован Stage 3 demo-cycle: добавлен отдельный checklist `NORDICBUILDER-DEMO-QUICK-SMOKE-2026-04-09.md` и automatable smoke script `scripts/nordicbuilder-demo-quick-smoke.sh`.
	- выполнен автоматический demo quick-smoke (guest+admin trace) по маршрутам `/`, `/board`, `/board/nedvizhimost`, `/board/<item>.html`, `/users/1`; статус прогона `PASS`.
- Какие файлы затронуты:
	- [LANDING-BUILDER-DEMO-CYCLE-SPEC-2026-04-09.md](../LANDING-BUILDER-DEMO-CYCLE-SPEC-2026-04-09.md)
	- [system/controllers/nordicbuilder/model.php](../system/controllers/nordicbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/model.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/model.php)
	- [system/controllers/nordicbuilder/backend/actions/install_demo.php](../system/controllers/nordicbuilder/backend/actions/install_demo.php)
	- [system/controllers/nordicbuilder/backend/actions/remove_demo.php](../system/controllers/nordicbuilder/backend/actions/remove_demo.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/install_demo.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/install_demo.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/remove_demo.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/remove_demo.php)
	- [system/controllers/nordicbuilder/backend/actions/pages.php](../system/controllers/nordicbuilder/backend/actions/pages.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/pages.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/pages.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [scripts/nordicbuilder-demo-quick-smoke.sh](../scripts/nordicbuilder-demo-quick-smoke.sh)
	- [docs/checklists/NORDICBUILDER-DEMO-QUICK-SMOKE-2026-04-09.md](checklists/NORDICBUILDER-DEMO-QUICK-SMOKE-2026-04-09.md)
- Какие риски остались:
	- пароль dump-учетки хранится в локальном `backups/db/.db-dump.env` (вне git), поэтому нужно следить за правами файла и периодически ротировать пароль.
	- при запуске через CLI видно стандартное предупреждение MySQL про пароль в аргументах; на работоспособность backup это не влияет.
	- `Full Template` режим пока не реализован в коде (на этапе 1 доступен только `Quick Demo`).
- Следующий шаг:
	- добавить в UI короткий отчет последней demo-операции (время, создано/обновлено/удалено), чтобы админ видел результат без всплывающего окна.
	- запланировать Stage 4 для режима `Full Template` (install/remove + smoke-матрица) поверх уже закрытого `Quick Demo`.

## 2026-04-07

- Что планировалось:
	- закрыть сценарий «все внутренние страницы кроме главной» так, чтобы `content_body` брался из нативного рендера InstantCMS для любых типов внутреннего контента.
- Что сделано:
	- в canvas inspector добавлен визуальный выбор `Зона в shell` для секции (auto / before_content / content_sidebar_left / content_sidebar_right / after_content / content_body по доступности), чтобы сценарии «блоки над native body + сайдбары + блоки под body» настраивались без ручного JSON;
	- backend screen `page_shell` теперь отдает `section_zone_options` и `default_section_zone`, а frontend синхронизирует `settings.zone_key` <-> `section.zone_key` для корректного runtime-роутинга секций по зонам;
	- в `landingbuilder` добавлен универсальный adapter `internal_content_generic` с native-слотом `content_body` и builder-зонами `before_content`/`after_content`;
	- `createPage()` начал принимать `adapter_key` и записывать его в `schema.adapter_key` при создании страницы;
	- backend actions `create_page` для `landingbuilder` и `nordicbuilder` начали прокидывать `adapter_key` из AJAX-запроса;
	- в мастере создания страницы (`pages.tpl.php`) добавлен resolver adapter по режиму применения:
	  - `all_except_homepage` -> `internal_content_generic`;
	  - `overlay_content_category_board` -> `content_category_generic`;
	  - `overlay_user_profile` -> `user_profile`.
	- синхронизированы package mirrors для `landingbuilder` и `nordicbuilder`.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/backend/actions/create_page.php](../system/controllers/landingbuilder/backend/actions/create_page.php)
	- [system/controllers/landingbuilder/backend/actions/pages.php](../system/controllers/landingbuilder/backend/actions/pages.php)
	- [system/controllers/landingbuilder/backend/actions/set_global_sections_source.php](../system/controllers/landingbuilder/backend/actions/set_global_sections_source.php)
	- [system/controllers/nordicbuilder/backend/actions/create_page.php](../system/controllers/nordicbuilder/backend/actions/create_page.php)
	- [system/controllers/nordicbuilder/backend/actions/pages.php](../system/controllers/nordicbuilder/backend/actions/pages.php)
	- [system/controllers/nordicbuilder/backend/actions/bindings.php](../system/controllers/nordicbuilder/backend/actions/bindings.php)
	- [system/controllers/nordicbuilder/backend/actions/create_binding.php](../system/controllers/nordicbuilder/backend/actions/create_binding.php)
	- [system/controllers/nordicbuilder/backend/actions/set_global_sections_source.php](../system/controllers/nordicbuilder/backend/actions/set_global_sections_source.php)
	- [system/controllers/nordicbuilder/model.php](../system/controllers/nordicbuilder/model.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/admincoreui/controllers/nordicbuilder/backend/bindings.tpl.php](../templates/admincoreui/controllers/nordicbuilder/backend/bindings.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/model.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/create_page.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/create_page.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
- Что проверено:
	- `php -l` проходит на всех измененных PHP-файлах в live и package mirrors;
	- editor diagnostics не показывают новых ошибок в измененных файлах;
	- `cmp -s` подтверждает parity source/mirror для модели, actions и шаблона.
- Какие риски остались:
	- новый adapter применяется к новым страницам через мастер; для уже созданных страниц нужно вручную сменить `schema.adapter_key` на `internal_content_generic`.
	- для сценариев с нестандартными shell-variant может понадобиться ручная фиксация `layout.shell_variant` на странице.
- Следующий шаг:
	- вручную проверить в админке создание страницы через «Все внутренние страницы (кроме главной)» и убедиться, что на внутренних маршрутах отображается нативный `content_body` с builder-обвязкой.

## 2026-04-05

- Что планировалось:
	- зафиксировать разворот разработки Нордик из form-first логики в visual-first builder и синхронизировать ключевые продуктовые документы.
	- определить, остается ли текущий `landingbuilder` финальным продуктовым контейнером или становится переходным мостом к взрослой архитектуре.
- Что сделано:
	- создан отдельный pivot-документ, который фиксирует новый главный маршрут: работа на живом canvas, а не через отдельный экран настроек;
	- сначала active plan был переписан под промежуточный visual-first срез: `Visual Page Builder`, `Live Inspector`, `Global Style Defaults`, `Shell / Expert Layer`, `Widget/Data Layer`;
	- дополнительно зафиксирована взрослая архитектура: `InstantCMS 2 backend -> nordic runtime template -> design system / global defaults -> visual builder workspace -> component library -> widget/data adapter layer`;
	- текущий `landingbuilder` формально переведен в статус переходного bridge-слоя, а не финальной продуктовой границы;
	- product blueprint переписан так, чтобы canvas и live inspector стали центром продукта, а MVP был разложен на 3 этапа: foundation, editor core, system integration;
	- техспека экрана `Дизайн сайта` перепозиционирована в secondary screen `Глобальные стили` для редких site-wide defaults и отделена от design system как слоя продукта;
	- product map и master plan синхронизируются под новую взрослую архитектуру, а не только под локальный UX-pivot текущего компонента;
	- в документах явно заморожено дальнейшее развитие form-first сценария как главного UX builder-а;
	- active plan затем досинхронизирован уже под взрослый execution order: foundation layer -> visual editor core -> system integration layer;
	- создан отдельный документ [LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md](../LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md), который стартует stage 1 contract-first и опирает его на schema-first дисциплину из `instantcms-mcp-main`;
	- зафиксировано имя нового целевого builder component: `nordicbuilder`;
	- поднят минимальный scaffold компонента `nordicbuilder` и его installable package mirror;
	- внутри `nordicbuilder` поднят file-based contract registry для пяти foundation contracts и backend browser `Контракты`, который читает registry как source-of-truth;
	- поверх contract registry добавлены storage stub и detail screen, чтобы foundation layer описывал не только schema-реестр, но и целевой persistence map для каждого контракта;
	- в `packages/nordicbuilder/install.sql` добавлены первые три builder-хранилища, а в модели `nordicbuilder` появился минимальный SQL-backed persistence layer и базовая validation проверка required fields по contract registry;
	- workspace `nordicbuilder` теперь показывает состояние foundation persistence layer, чтобы следующий save/load loop строился уже от реальных таблиц, а не от абстрактной схемы;
	- workspace `nordicbuilder` расширен до первого живого цикла page document: список сохраненных документов, JSON editor, save/load flow и bridge import страницы из `landingbuilder`;
	- validation для `page-document` усилена beyond required fields: теперь отдельно проверяются `kind`, `page_type`, `editor_mode`, `meta` и структура `zones`.
	- bridge-слой `landingbuilder` теперь умеет читать `page-document` из `nordicbuilder` и сохранять canvas-изменения обратно в новый contract storage, если страница уже переведена на новый документ;
	- section/block semantics для `page-document` усилены: валидируются zone sections, section uid/title/columns, column uid/nodes и базовые требования к block/system widget nodes.
	- добавлен массовый migration route `landingbuilder -> nordicbuilder`: import теперь читает legacy source напрямую, не через bridge-resolved page, а workspace показывает pending/imported статус, bulk import all и подробный migration report;
	- backend `nordicbuilder` переведен на русский интерфейс: заголовок компонента, меню, workspace и contract screens больше не показывают англоязычные подписи пользователю;
	- после live install удалена лишняя дубль-запись `nordicbuilder` из таблицы `controllers`, чтобы компонент показывался в админке один раз;
	- текущий bridge-canvas `landingbuilder` получил первый semantic-aware слой для блоков: backend теперь отдает единый block catalog, inspector умеет переключать semantic-пресет и редактировать preset-поля, а runtime-preview рендерит типизированные hero/cards/features/filter/stats состояния вместо одной общей заглушки;
	- `nordicbuilder` подключен к file-based `block_manifests.php`: `page-document` теперь нормализует и валидирует block nodes по manifest-registry, а legacy import из `landingbuilder` прогоняет policy-слой для alias resolution, fallback custom blocks, promotion заметок в block props и section inference;
	- live smoke test подтвержден на реальном bootstrap для `homepage`, `ads-category` и `profile-cover`: importLandingbuilderPage проходит с новой manifest-aware migration policy и сохраняет page-document без contract errors;
	- product course дополнительно зафиксирован: contracts/storage/json/adapters признаны внутренним dev/system tooling, а не главным user-facing builder UI;
	- в active plan добавлен обязательный этап `Vertical Slice (MVP loop)` между foundation и взрослым editor core, чтобы проверять contracts через реальный пользовательский сценарий, а не только через backend tooling;
	- `nordicbuilder` переведен на visual-first entry: default route и основное меню теперь ведут в живой canvas, а прежний workspace сохранен как служебная панель dev/system слоя;
	- добавлен safe starter `vertical-slice-home`: если документа еще нет, backend автоматически создает 1 страницу с 2 секциями и 5 базовыми semantic-блоками, после чего открывает ее в bridge-canvas `landingbuilder`, который уже сохраняет изменения в `nordicbuilder page-document`;
	- добавлен build-script `scripts/build-nordicbuilder-package.sh`, чтобы `packages/nordicbuilder/` собирался в versioned installable zip для коммерческой поставки пользователям.
	- в `landingbuilder` добавлен product-level слой `template preset`, чтобы выбор шаблона сайта и страницы работал как управляемый сценарий поверх существующего shell/runtime, а не как технический preview-template toggle;
	- экран `Дизайн сайта` теперь показывает выбор шаблона сайта, а canvas page inspector получил page-level выбор шаблона страницы с мгновенным пересчетом effective shell прямо в браузере без сохранения;
	- bridge-save между `landingbuilder` и `nordicbuilder` теперь сохраняет `layout.template`, чтобы выбранный шаблон не терялся при переходе между legacy page и contract-first page-document.
	- после smoke-проверки доработан сам canvas: клиентский resolver template preset теперь повторяет server-side route-логику по page key / adapter / page mode, а смена шаблона страницы сразу синхронизирует `layout.template` и effective shell без скрытого рассинхрона;
	- параллельно дочищены наиболее заметные UX-шероховатости в admin UI: из canvas/design screen убраны оставшиеся полуаңглоязычные формулировки про `defaults`, `visual inspector` и `site-wide`.
	- расширен каталог управляемых template preset-ов: к базовым сценариям добавлены `nordic_editorial`, `nordic_catalog`, `nordic_warm_market` и `nordic_compact` на уровне live-модели, canvas fallback catalog и package mirror;
	- устранен источник 404 в admin UX: переходы в canvas, глобальные стили и redirect из `nordicbuilder` переведены с frontend component route на явный admin edit route `/admin/controllers/edit/...`, чтобы builder не уводил пользователя в несуществующий публичный URL;
	- smoke checklist для canvas обновлен под актуальный admin path, чтобы ручная проверка больше не опиралась на устаревший маршрут `/admin/landingbuilder/...`.
	- user-facing admin flow дополнительно переведен на один компонент `nordicbuilder`: появились собственные actions `pages`, `create_page`, `canvas`, `defaults` и proxy-endpoints `widgets_catalog`, `widget_options`, `canvas_save`, `versions`, `version_restore`, поэтому пользователь больше не должен ходить по backend-URL старого `landingbuilder` компонента;
	- `packages/nordicbuilder/` сделан self-contained для single-component доставки: в package payload добавлен внутренний bridge-слой `landingbuilder` и связанные admin/runtime templates, чтобы коммерческий zip не требовал второй отдельной установки builder-компонента.
	- стартовый пользовательский вход переведен с пустого полотна на готовый starter product: `vertical-slice-home` и обычное создание новой страницы теперь используют один и тот же базовый лендинг-каркас с оффером, преимуществами и финальным CTA, который нужно редактировать под свой продукт, а не собирать с нуля.
	- visual layer `templates/nordic` отвязан от прямого импорта `modern/css/theme.css`: вынесен собственный `foundation.css`, `theme.css` переведен на Nordic-only слой, а SCSS разбит на partials `tokens`, `base`, `shell`, `components`; те же изменения зеркально заведены в `packages/nordic/package/templates/nordic`.
	- в канонических спеках закреплено продуктовое правило: Bootstrap 4 остается только внутренним legacy-foundation для совместимости, а современный видимый UI конструктора обязан идти из собственного Nordic design system.
	- active plan дополнен цветным статусом по modern UI foundation: отдельно отмечено, что уже сделано, что находится в переходном состоянии и что еще не завершено.
	- shared admin UI builder-а переведен на Nordic-styled visual слой: в `canvas` снят основной bootstrap-look у topbar, inspector controls, library cards и version cards, а экраны `pages`, `shell` и `shell variant` перестроены из bootstrap-таблиц в собственные product-style panels и cards.
	- подготовлен отдельный manual smoke checklist для нового admin UI маршрута `nordicbuilder/pages -> canvas -> defaults` и bridge-screen `landingbuilder/shell`, чтобы финальный ручной проход проверял уже современный продуктовый слой, а не только старый canvas.
- Какие файлы затронуты:
	- [LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md](../LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md)
	- [LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md](../LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md)
	- [LANDING-BUILDER-VISUAL-FIRST-PIVOT-2026-04-05.md](../LANDING-BUILDER-VISUAL-FIRST-PIVOT-2026-04-05.md)
	- [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](../LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md)
	- [LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md](../LANDING-BUILDER-DESIGN-SYSTEM-SPEC-2026-04-04.md)
	- [LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md](../LANDING-BUILDER-FOUNDATION-LAYER-SPEC-2026-04-05.md)
	- [LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md](../LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md)
	- [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
	- [docs/WORKLOG.md](WORKLOG.md)
	- [system/controllers/nordicbuilder/backend.php](../system/controllers/nordicbuilder/backend.php)
	- [system/controllers/nordicbuilder/backend/actions/canvas.php](../system/controllers/nordicbuilder/backend/actions/canvas.php)
	- [system/controllers/nordicbuilder/backend/actions/pages.php](../system/controllers/nordicbuilder/backend/actions/pages.php)
	- [system/controllers/nordicbuilder/backend/actions/create_page.php](../system/controllers/nordicbuilder/backend/actions/create_page.php)
	- [system/controllers/nordicbuilder/backend/actions/widgets_catalog.php](../system/controllers/nordicbuilder/backend/actions/widgets_catalog.php)
	- [system/controllers/nordicbuilder/backend/actions/widget_options.php](../system/controllers/nordicbuilder/backend/actions/widget_options.php)
	- [system/controllers/nordicbuilder/backend/actions/canvas_save.php](../system/controllers/nordicbuilder/backend/actions/canvas_save.php)
	- [system/controllers/nordicbuilder/backend/actions/versions.php](../system/controllers/nordicbuilder/backend/actions/versions.php)
	- [system/controllers/nordicbuilder/backend/actions/version_restore.php](../system/controllers/nordicbuilder/backend/actions/version_restore.php)
	- [system/controllers/nordicbuilder/backend/actions/workspace.php](../system/controllers/nordicbuilder/backend/actions/workspace.php)
	- [system/controllers/nordicbuilder/model.php](../system/controllers/nordicbuilder/model.php)
	- [templates/admincoreui/controllers/nordicbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/nordicbuilder/backend/pages.tpl.php)
	- [templates/admincoreui/controllers/nordicbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/nordicbuilder/backend/canvas.tpl.php)
	- [templates/admincoreui/controllers/nordicbuilder/backend/defaults.tpl.php](../templates/admincoreui/controllers/nordicbuilder/backend/defaults.tpl.php)
	- [templates/modern/controllers/nordicbuilder/backend/workspace.tpl.php](../templates/modern/controllers/nordicbuilder/backend/workspace.tpl.php)
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/backend/actions/design.php](../system/controllers/landingbuilder/backend/actions/design.php)
	- [system/controllers/landingbuilder/backend/forms/form_design.php](../system/controllers/landingbuilder/backend/forms/form_design.php)
	- [system/controllers/landingbuilder/backend/forms/form_options.php](../system/controllers/landingbuilder/backend/forms/form_options.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/design.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/design.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php)
	- [system/controllers/landingbuilder/backend/actions/canvas.php](../system/controllers/landingbuilder/backend/actions/canvas.php)
	- [system/controllers/landingbuilder/backend/actions/pages.php](../system/controllers/landingbuilder/backend/actions/pages.php)
	- [system/controllers/landingbuilder/backend/actions/create_page.php](../system/controllers/landingbuilder/backend/actions/create_page.php)
	- [system/controllers/nordicbuilder/backend/actions/canvas.php](../system/controllers/nordicbuilder/backend/actions/canvas.php)
	- [docs/checklists/LANDINGBUILDER-CANVAS-ADMIN-SMOKE-TEST-2026-04-04.md](checklists/LANDINGBUILDER-CANVAS-ADMIN-SMOKE-TEST-2026-04-04.md)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/canvas.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/canvas.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/pages.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/pages.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/create_page.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/design.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/design.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/canvas.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/canvas.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/pages.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/pages.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/create_page.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/create_page.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/widgets_catalog.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/widgets_catalog.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/widget_options.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/widget_options.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/canvas_save.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/canvas_save.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/versions.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/versions.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/version_restore.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/version_restore.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/nordicbuilder/backend/pages.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/nordicbuilder/backend/pages.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/nordicbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/nordicbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/nordicbuilder/backend/defaults.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/nordicbuilder/backend/defaults.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/forms/form_design.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/forms/form_design.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/forms/form_options.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/forms/form_options.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/design.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/design.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php)
	- [docs/checklists/NORDICBUILDER-ADMIN-UI-SMOKE-2026-04-05.md](checklists/NORDICBUILDER-ADMIN-UI-SMOKE-2026-04-05.md)
	- [templates/nordic/css/foundation.css](../templates/nordic/css/foundation.css)
	- [templates/nordic/css/theme.css](../templates/nordic/css/theme.css)
	- [templates/nordic/scss/theme/theme.scss](../templates/nordic/scss/theme/theme.scss)
	- [templates/nordic/scss/theme/_tokens.scss](../templates/nordic/scss/theme/_tokens.scss)
	- [templates/nordic/scss/theme/_base.scss](../templates/nordic/scss/theme/_base.scss)
	- [templates/nordic/scss/theme/_shell.scss](../templates/nordic/scss/theme/_shell.scss)
	- [templates/nordic/scss/theme/_components.scss](../templates/nordic/scss/theme/_components.scss)
	- [packages/nordic/package/templates/nordic/css/foundation.css](../packages/nordic/package/templates/nordic/css/foundation.css)
	- [packages/nordic/package/templates/nordic/css/theme.css](../packages/nordic/package/templates/nordic/css/theme.css)
	- [packages/nordic/package/templates/nordic/scss/theme/theme.scss](../packages/nordic/package/templates/nordic/scss/theme/theme.scss)
	- [packages/nordic/package/templates/nordic/scss/theme/_tokens.scss](../packages/nordic/package/templates/nordic/scss/theme/_tokens.scss)
	- [packages/nordic/package/templates/nordic/scss/theme/_base.scss](../packages/nordic/package/templates/nordic/scss/theme/_base.scss)
	- [packages/nordic/package/templates/nordic/scss/theme/_shell.scss](../packages/nordic/package/templates/nordic/scss/theme/_shell.scss)
	- [packages/nordic/package/templates/nordic/scss/theme/_components.scss](../packages/nordic/package/templates/nordic/scss/theme/_components.scss)
- Что проверено:
	- ключевые документы читаются в одном направлении и больше не спорят между собой о primary flow;
	- отдельный экран глобальных стилей больше не описан как главный экран ежедневной работы;
	- зафиксировано, что новый builder boundary должен проектироваться отдельно от текущего bridge-слоя;
	- active plan больше не отрывается от взрослой архитектуры и не живет в старой промежуточной visual-first формуле;
	- первый semantic-aware слой для блоков проходит через одну и ту же логику: block catalog в модели, semantic inspector на canvas и типизированный runtime preview.
	- в документах явно отделен user product от dev/system layer, чтобы backend tooling не подменял собой builder для обычного пользователя.
	- новый visual-first entry в `nordicbuilder` не дал новых syntax/diagnostics ошибок в backend/model/template файлах.
	- `php -l` проходит на измененных файлах `landingbuilder`/`nordicbuilder`, а editor diagnostics не показывают новых ошибок в model/forms/canvas template.
	- прямой HTTP smoke по admin-маршрутам (`/admin`, `landingbuilder/design`, `landingbuilder/canvas`, `nordicbuilder/canvas`) подтверждает, что live UI закрыт `403 Forbidden` без авторизованной admin-сессии, поэтому в этой сессии smoke был ограничен route-check + code-level UX review + syntax/diagnostics verification.
	- после route-fix не осталось совпадений по старым builder path-pattern: поиск больше не находит генерацию `href_to('landingbuilder', 'canvas', ...)`, старый `design_url` через component root и примеры `/admin/landingbuilder/canvas` в затронутых документах;
	- `php -l` дополнительно проходит на package mirror, поэтому live и installable package остаются синхронны и без syntax regression.
	- `nordicbuilder` user-facing actions и proxy-endpoints проходят syntax/diagnostics checks, а canvas больше не зависит от backend AJAX URL старого компонента `landingbuilder`;
	- single-component package policy подтверждена технически: build target остается один (`packages/nordicbuilder/`), а bridge-файлы `landingbuilder` включены в его payload для self-contained поставки.
	- editor diagnostics не показывают новых ошибок в live/package `templates/nordic/css/theme.css`, `foundation.css`, `templates/nordic/scss/theme/theme.scss` и связанных файлах, а поиск по Nordic шаблону больше не находит прямого визуального импорта `modern/css/theme.css`.
	- active plan, foundation spec и design system spec теперь одинаково фиксируют правило: Bootstrap не является продуктовой идентичностью конструктора и остается только внутренним техническим слоем.
	- shared admin templates для `canvas`, `pages`, `shell` и `shell variant`, а также их package mirrors, проходят editor diagnostics без новых ошибок после перевода на Nordic-styled UI слой.
- Какие риски остались:
	- кодовая реализация foundation уже перевела bridge read/save на page-document для импортированных страниц и получила массовый migration route, но еще не покрывает полноценную schema-aware validation конкретных block props и не делает более умные policy-миграции для сложных legacy edge-cases;
	- runtime и editor пока переведены только частично: теперь есть visual-first entry и starter vertical slice, но сам холст все еще физически работает через bridge-canvas `landingbuilder`, а не через собственный canvas route `nordicbuilder`.
	- template preset уже расширен до первой пользовательской линейки (`nordic_classic`, `nordic_editorial`, `nordic_catalog`, `nordic_warm_market`, `nordic_compact`, `nm_landing`), но еще не проверен вручную через полный admin UX-проход в авторизованной сессии;
	- у `templates/nordic/manifest.php` все еще сохраняется `inherit => ['modern']` как техническая база runtime-совместимости, поэтому визуальная независимость уже достигнута на уровне CSS-слоя, но полная инфраструктурная независимость шаблона от `modern` еще не завершена;
	- builder UI по-прежнему местами опирается на старую bootstrap-semantic markup, поэтому современный Nordic visual language зафиксирован в доках, но еще не полностью вынесен в собственные first-class primitives на всех экранах;
	- ручной smoke checklist уже подготовлен, но сам авторизованный admin-проход по новому UI еще не выполнен в этой сессии;
	- builder admin URLs сейчас исправлены точечно в action-слое; без общей helper-обвязки остается риск, что новые ссылки позже снова кто-то соберет через frontend route helper;
	- preview/runtime пока еще используют bridge-route `landingbuilder/view/...`, поэтому single-component delivery уже решен на уровне install/admin flow, но frontend runtime naming cleanup еще не завершен.
- Следующий шаг:
	- пройти авторизованный admin smoke по `nordicbuilder/pages -> canvas -> defaults`, затем убрать оставшийся runtime naming tail `landingbuilder/view/...` и только после этого продолжить UX-полировку canvas под повседневный сценарий обычного пользователя.

## 2026-04-06

- Что планировалось:
	- упростить создание «нулевого» макета страницы и сразу задавать область применения (главная/маски), как в inthemer.
- Что сделано:
	- добавлен AJAX endpoint `nordicbuilder/create_binding` для сохранения binding rule из мастера создания страницы;
	- в `nordicbuilder/pages` прокинут `create_binding_url` в шаблон;
	- в admincoreui-реестре страниц внедрён мастер создания страницы (модалка вместо `prompt()`) с подсказками `?` и полями масок (положительные/отрицательные);
	- добавлена явная кнопка **Новая страница** внутри шапки экрана (на случай, если тулбар админки скрывает toolbutton).
	- добавлены базовые источники контента для section-first canvas: блоки `core.text` (заголовок+текст) и `core.raw-html` (HTML), плюс пресеты секций для быстрого добавления.
- Какие файлы затронуты:
	- `system/controllers/nordicbuilder/backend/actions/create_binding.php`
	- `system/controllers/nordicbuilder/backend/actions/pages.php`
	- `templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php`
	- package mirrors в `packages/nordicbuilder/package/` и `packages/landingbuilder/package/`
	- `system/controllers/landingbuilder/model.php`
	- `system/controllers/nordicbuilder/data/block_manifests.php`
	- `templates/default/controllers/landingbuilder/runtime_renderer.php`
	- package mirrors в `packages/nordicbuilder/package/` и `packages/landingbuilder/package/`
	- `docs/worklogs/2026-04-06-nordicbuilder-pages-wizard.md`
- Что проверено:
	- `php -l` на новых/изменённых action’ах и шаблонах.
	- базовая связность: новые source_key присутствуют в landingbuilder catalog и в nordicbuilder manifests; runtime renderer умеет отрисовать оба блока.
- Какие риски остались:
	- возможна задержка отображения изменений из-за OPCache/кэша браузера (лечится hard refresh / ожиданием).
- Следующий шаг:
	- пройти ручной smoke по `/admin/controllers/edit/nordicbuilder/pages`: видимость кнопки, открытие модалки, создание страницы + опционального binding.

## 2026-04-06 (Full takeover: runtime в shell-слоты)

- Что планировалось:
	- сделать поведение «по‑взрослому»: если для страницы активен takeover от builder-а, то на фронтенде не должны просачиваться legacy-виджеты/контент, а секции builder-а должны попадать в реальные shell‑слоты (`hero/before/content/after`), без дублей и debug‑обвеса.
- Что сделано:
	- Nordic runtime takeover переведен с режима «include landingbuilder/view.tpl.php внутри content_body» на slot-aware рендер: builder‑зоны рендерятся прямо в соответствующих shell‑слотах `hero`, `before_content`, `content_body` (через `content_slot`), `after_content`.
	- при активном takeover подавлены legacy-виджеты в `hero/before/after/content_body` и отключены контентные сайдбары, чтобы на takeover‑странице не оставалось «старого сайта».
	- CSS variables темы builder-а прокинуты на корневой контейнер `.nordic-shell` через inline `style`, чтобы визуальные пресеты страницы корректно работали в live-рендере.
	- добавлен admin-only dev-режим "modern skin" для отладки «движка» без влияния Nordic-дизайна: по умолчанию для админа подключается `templates/modern/css/theme.css` и рендерится стандартная layout-схема (без `nordic-shell` разметки); форсировать Nordic можно через `?nordic_skin=nordic`.
	- изменения синхронизированы в package mirror шаблона Nordic.
- Какие файлы затронуты:
	- [templates/nordic/main.tpl.php](../templates/nordic/main.tpl.php)
	- [packages/nordic/package/templates/nordic/main.tpl.php](../packages/nordic/package/templates/nordic/main.tpl.php)
- Что проверено:
	- `php -l` проходит на обоих `main.tpl.php` (live + package mirror).
- Какие риски остались:
	- если takeover активен, но в соответствующей зоне нет секций, слот будет пустым (это ожидаемо для full takeover, но визуально может выглядеть как «пропало»).
	- inline CSS vars применяются на `.nordic-shell` только в takeover‑режиме; если где-то есть жёсткие переопределения, возможны точечные визуальные расхождения.
- Следующий шаг:
	- ручной smoke в браузере: открыть главную `/` (гость и админ) и убедиться, что legacy-виджеты не рендерятся, секции builder-а распределены по слотам без дублей, а меню/шапка/футер остаются shell-уровнем.

## 2026-04-04

- Что планировалось:
	- довести `Shell Builder` от backend storage до реального runtime composition layer.
- Что сделано:
	- в `landingbuilder` добавлен runtime resolver, который выбирает shell variant по page layout override, page key, adapter и page mode;
	- runtime shell теперь возвращает resolved variant metadata: `variant_key`, `assignment_source`, `body_layout`, `active_slots`, `chrome`, `body_classes`;
	- runtime zones фильтруются по active shell slots, чтобы overlay и shell zones не рендерили отключенные области;
	- page inspector в canvas теперь показывает page-level `shell_variant` и `content_slot`, поэтому resolver управляется не только code/schema слоем, но и UX страницы;
	- добавлен общий helper `runtime_renderer.php`, который централизует rendering для zones, sections, Nordic blocks и system widgets в preview и overlay;
	- preview и overlay templates переведены на shared slot-aware renderer и больше не держат дублирующие closures для block/widget/section rendering;
	- preview action и content-category overlay hook теперь прокидывают resolved shell в layout params шаблона;
	- `templates/nordic/main.tpl.php` и package mirror переведены на чтение active slots из runtime, поэтому shell regions и sidebars теперь управляются variant, а не только статической схемой и наличием widget positions.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [system/controllers/landingbuilder/actions/view.php](../system/controllers/landingbuilder/actions/view.php)
	- [system/controllers/landingbuilder/hooks/process_render_content_category_view.php](../system/controllers/landingbuilder/hooks/process_render_content_category_view.php)
	- [templates/default/controllers/landingbuilder/runtime_renderer.php](../templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [templates/default/controllers/landingbuilder/view.tpl.php](../templates/default/controllers/landingbuilder/view.tpl.php)
	- [templates/default/controllers/landingbuilder/overlay_zone.tpl.php](../templates/default/controllers/landingbuilder/overlay_zone.tpl.php)
	- [templates/nordic/main.tpl.php](../templates/nordic/main.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/actions/view.php](../packages/landingbuilder/package/system/controllers/landingbuilder/actions/view.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/hooks/process_render_content_category_view.php](../packages/landingbuilder/package/system/controllers/landingbuilder/hooks/process_render_content_category_view.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/view.tpl.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/view.tpl.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/overlay_zone.tpl.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/overlay_zone.tpl.php)
	- [packages/nordic/package/templates/nordic/main.tpl.php](../packages/nordic/package/templates/nordic/main.tpl.php)
	- [LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md](../LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md)
- Что проверено:
	- `php -l` проходит на всех измененных live и package PHP-файлах;
	- editor diagnostics по измененным файлам не показывают новых ошибок.
- Какие риски остались:
	- live system overlay пока связан только с content categories, а не со всеми типами системных страниц;
	- page-level override пока вынесен в canvas inspector, но еще не представлен в отдельных быстрых edit forms вне canvas;
	- shared renderer уже общий для preview и overlay, но Nordic blocks пока в основном показывают runtime placeholders, а не полный data-driven props layer.
- Следующий шаг:
	- расширить shared renderer и overlay coverage на следующие adapters/system routes, затем открыть отдельный экран `Design System`.

- Что планировалось:
	- начать реальную реализацию MVP `Shell Builder` после фиксации новой продуктовой карты.
- Что сделано:
	- в backend меню `landingbuilder` добавлен отдельный экран `Shell Builder`;
	- в model `landingbuilder` добавлены option-backed helpers для shell variants без SQL-миграции;
	- заведены системные shell variants: базовый shell сайта, главная, материалы, категории, профили и лендинги;
	- добавлен backend flow `список вариантов -> редактирование -> сохранение`;
	- на экране variant добавлен preview shell slots по `nordic_shell_v1`, чтобы настройки читались в терминах продукта, а не raw positions;
	- live и package mirror синхронизированы по backend/menu/model/actions/forms/templates.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/backend.php](../system/controllers/landingbuilder/backend.php)
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/backend/actions/shell.php](../system/controllers/landingbuilder/backend/actions/shell.php)
	- [system/controllers/landingbuilder/backend/actions/shell_edit.php](../system/controllers/landingbuilder/backend/actions/shell_edit.php)
	- [system/controllers/landingbuilder/backend/forms/form_shell_variant.php](../system/controllers/landingbuilder/backend/forms/form_shell_variant.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/shell.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/shell.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/shell_edit.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/actions/shell_edit.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/backend/forms/form_shell_variant.php](../packages/landingbuilder/package/system/controllers/landingbuilder/backend/forms/form_shell_variant.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/shell_variant.tpl.php)
	- [LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md](../LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md)
- Что проверено:
	- для новых live и package PHP-файлов будет выполнен `php -l`;
	- editor diagnostics будут проверены отдельно после патча.
- Какие риски остались:
	- текущий MVP пока не применяет shell variants в runtime `nordic`, это только backend-level storage и admin UX;
	- пока нет custom create/delete flow для новых variant, редактируются системно заданные сценарии первой очереди.
- Следующий шаг:
	- связать shell variant с runtime shell resolution и page-level режимами участия.

- Что планировалось:
	- убрать англоязычие из canvas theme controls и довести page theme presets до реального runtime/frontend применения.
- Что сделано:
	- backend menu, form options, section preset titles и canvas controls переведены на русский язык;
	- в model contract добавлены канонические `theme_option_catalog` и `default_section_layout`;
	- section presentation contract выровнен между canvas, save path и runtime через top-level поля `style_preset`, `background_tone`, `container_preset`, `spacing_preset`;
	- добавлен общий helper `runtime_theme.php` для standalone preview и overlay runtime;
	- `view.tpl.php`, `overlay_zone.tpl.php` и overlay hook переведены на CSS variables и runtime classes, чтобы page theme presets реально влияли на frontend.
	- собран отдельный ручной чек-лист для smoke-test canvas в админке и overlay/runtime-проверки.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/backend/forms/form_options.php](../system/controllers/landingbuilder/backend/forms/form_options.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [templates/default/controllers/landingbuilder/runtime_theme.php](../templates/default/controllers/landingbuilder/runtime_theme.php)
	- [templates/default/controllers/landingbuilder/view.tpl.php](../templates/default/controllers/landingbuilder/view.tpl.php)
	- [templates/default/controllers/landingbuilder/overlay_zone.tpl.php](../templates/default/controllers/landingbuilder/overlay_zone.tpl.php)
	- [system/controllers/landingbuilder/hooks/process_render_content_category_view.php](../system/controllers/landingbuilder/hooks/process_render_content_category_view.php)
	- [docs/checklists/LANDINGBUILDER-CANVAS-ADMIN-SMOKE-TEST-2026-04-04.md](checklists/LANDINGBUILDER-CANVAS-ADMIN-SMOKE-TEST-2026-04-04.md)
- Что проверено:
	- редакторские проверки ошибок по live и package mirror не показывают новых проблем;
	- `php -l` проходит на изменённых live PHP-файлах;
	- публичный HTTP-ответ сайта `https://nordic-builder.store/` возвращает `200 OK`.
- Какие риски остались:
	- живой preview route `landingbuilder/view/*` для draft/prototype страниц по-прежнему закрыт для неадмина, поэтому полноценный frontend smoke-test без админ-сессии не завершён;
	- визуальная тема теперь применяется через CSS variables, но финальную UX-полировку canvas лучше делать уже по живому админскому проходу.
- Следующий шаг:
	- зайти в админский canvas, руками проверить пресеты страницы и device preview, затем собрать список точечных UX-шероховатостей.

- Дополнительное продуктовое уточнение по canvas workspace:
	- зафиксирован отдельный UX spec для взрослой editor shell-оболочки;
	- принято решение двигаться не от локальной косметики, а от refactor canvas shell: wide desktop canvas, overlay drawers, viewport-based device switching и улучшенная навигация;
	- в качестве референса учтён паттерн editor workspace из соседнего `nordic-builder.ru`, где desktop panel state запоминается и панели не должны разрушать рабочую ширину preview.

- Phase 1 canvas workspace shell выполнен в коде:
	- `templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php` переведен с трехколоночного bootstrap-layout на sticky top bar, широкий workspace viewport и overlay drawers слева/справа;
	- библиотека и инспектор теперь живут поверх canvas, могут скрываться и запоминают состояние отдельно для desktop и mobile через `localStorage`;
	- top bar получил явный возврат к списку страниц, device viewport label и быстрые drawer toggles;
	- package mirror `packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php` синхронизирован с live-шаблоном.

- Что проверено дополнительно:
	- editor diagnostics не показывают новых ошибок в live и package canvas template;
	- `php -l templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php` проходит;
	- `php -l packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php` проходит.

- Точка отката:
	- создан локальный git tag `checkpoint/lb-canvas-phase1-base-20260404` на текущем HEAD `c933d20` без коммита dirty tree.

- Продуктовая модель Нордик дополнительно формализована:
	- создана каноническая карта продукта [LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md](../LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md), которая разводит `InstantCMS 2 backend`, `nordic runtime template`, `design system / global defaults`, `visual builder workspace`, `component library` и `widget/data adapter layer`;
	- создан отдельный документ [LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md](../LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md) с MVP-требованиями к header, footer, menu placement, global slots и homepage shell layout;
	- master plan, roadmap и backend settings spec синхронизированы с новой канонической моделью экранов продукта.

- Новый критерий выбора следующего кода:
	- после этой фиксации следующим кодовым шагом не считать дальнейшую локальную полировку page canvas;
	- выбирать между foundation layer, MVP `Shell Builder`, secondary UI `Глобальные стили` и `Visual Builder Workspace`.

- Дополнительно затронуты файлы:
	- [LANDING-BUILDER-CANVAS-WORKSPACE-UX-SPEC-2026-04-04.md](../LANDING-BUILDER-CANVAS-WORKSPACE-UX-SPEC-2026-04-04.md)
	- [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](../LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md)
	- [LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md](../LANDING-BUILDER-PRODUCT-MAP-2026-04-04.md)
	- [LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md](../LANDING-BUILDER-SHELL-BUILDER-SPEC-2026-04-04.md)

## 2026-04-03

- Добавлен единый active tracker [LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md](../LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md), чтобы текущая реализация не расползалась между несколькими plan-документами.
- Стартовала реализация MVP `Shell Builder` без SQL-миграции: первый backend-срез хранит shell variants в options компонента `landingbuilder`.

- Зафиксирован отдельный продуктовый blueprint visual builder:
	- добавлен [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](../LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md);
	- в blueprint конкретно описаны главный экран конструктора, допустимые sidebar controls, MVP section presets и связь builder с global design system Нордик;
	- отдельно зафиксировано, что widgets scheme `nordic` остается служебным shell editor и не становится главным UX для новичка.

- Каноничные архитектурные документы синхронизированы с этим продуктовым решением:
	- master plan теперь включает отдельный visual builder blueprint в верхний порядок чтения;
	- canvas editor spec ссылается на blueprint как на продуктовый UX-источник;
	- theme system architecture spec уточняет связь visual builder с global tokens и preset-слоем.

- `nordic_shell_v1` вынесен из одноразового CLI в общий install/runtime helper:
	- добавлен общий helper `templates/nordic/install_helpers/shell_migration.php` и пакет-зеркало `packages/nordic/package/templates/nordic/install_helpers/shell_migration.php`;
	- `scripts/nordic-shell-migration.php` переведен на использование общего helper вместо дублирования SQL-логики;
	- dry-run после рефакторинга подтвердил прежний результат: `rows = 9`, `cols = 11`, `binds_to_touch = 0`, `unmapped_positions = none`.

- Для пакета `nordic` добавлен install/update hook:
	- создан root installer `packages/nordic/install.php`;
	- после копирования файлов пакета installer автоматически вызывает apply-path для `nordic_shell_v1` и очищает widget/layout cache тем же helper.

- Для admin widgets UX добавлена отдельная shell-map схема `nordic`:
	- добавлен `templates/nordic/scheme.php` и пакет-зеркало;
	- `system/controllers/admin/actions/widgets.php` для `nordic` теперь рендерит статичную shell map сверху и сохраняет обычный dynamic layout editor ниже;
	- preview не создает live `{position:*}` placeholders, чтобы не дублировать `pos-*` контейнеры и не ломать drag-and-drop.

- Под `nordic_shell_v1` собран и прогнан reproducible migration script:
	- добавлен [scripts/nordic-shell-migration.php](scripts/nordic-shell-migration.php) с режимами dry-run и apply;
	- перед apply создан ручной DB backup `backups/db/manual-before-nordic-shell-apply-20260404-100229.sql`;
	- перед apply создан git snapshot `snapshot/20260404-100230`.

- Выполнена чистка copied `nordic` layout scheme в БД:
	- `layout_rows` для `nordic` пересобраны из copied `modern` схемы в 9 собственных shell rows;
	- `layout_cols` для `nordic` пересобраны в 11 canonical positions `site_top`, `header_primary`, `header_secondary`, `hero`, `before_content`, `content_body`, `content_sidebar_left`, `content_sidebar_right`, `after_content`, `footer_primary`, `footer_secondary`;
	- `widgets_bind_pages.position` для `nordic` переведены с legacy keys `pos_*` и `con_header` на canonical shell positions.

- Повторный smoke-test после apply прошел:
	- пользовательский `system/config/config.php` уже был переключен на `nordic`, поэтому отдельное временное переключение не выполнялось;
	- главная и `/board` продолжают отдавать `templates/nordic/css/theme.css` и `nordic-shell`;
	- итоговая bind-карта `nordic` в БД больше не использует legacy positions `pos_*` и `con_header`.

- Для `nordic` заведена собственная code-level layout scheme:
	- добавлен каноничный source of truth `templates/nordic/shell_scheme.php` и пакет-зеркало `packages/nordic/package/templates/nordic/shell_scheme.php`;
	- shell scheme получила ключ `nordic_shell_v1`;
	- `layout.scheme` добавлен в runtime contract страницы рядом с `layout.template` и `layout.content_slot`.

- Для `nordic` зафиксирован перевод bind-позиций из copied `modern` scheme в shell slots:
	- `pos_22` -> `site_top`;
	- `pos_26`, `pos_27`, `pos_29`, `pos_31` -> header slots;
	- `pos_33` -> `hero`;
	- `con_header`, `pos_10` -> `before_content`;
	- `pos_8` -> `content_body`;
	- `pos_9` -> `content_sidebar_right`;
	- `pos_38`, `pos_39`, `pos_40` -> `footer_primary`;
	- `pos_11`, `pos_32` -> `footer_secondary`.

- Чтобы новая shell scheme не дублировала header/footer в теле страницы:
	- добавлен `templates/nordic/layout_childs/main_scheme.tpl.php`;
	- он фильтрует reserved shell positions из dynamic layout rows перед fallback-рендером через `modern/layout_childs/main_scheme.tpl.php`;
	- это подготавливает безопасный перевод `widgets_bind_pages.position` на новые shell keys без двойного вывода.

- Связка `content_body` доведена до рабочего контура:
	- runtime `landingbuilder` теперь нормализует legacy zone keys `main`, `native_content`, `sidebar` в `content_body` и `content_sidebar_right`;
	- standalone pages по умолчанию маппятся в `content_body`;
	- page contracts расширены под `layout.content_slot` и `shell_slots`.

- Проведен временный smoke-test переключения сайта на `nordic`:
	- перед тестом создан checkpoint `snapshot/20260404-093842`;
	- для честного теста в БД скопированы `layout_rows`, `layout_cols` и `widgets_bind_pages` из `modern` в `nordic`;
	- главная и `/board` успешно отдали `nordic-shell` и `data-slot="content_body"`;
	- после теста активный шаблон возвращен на `modern`.

- Вывод по widget positions после smoke-test:
	- у `nordic` пока используются legacy-позиции из `modern` (`pos_8`, `pos_9`, `con_header` и др.);
	- собственные shell positions `header_primary`, `content_body`, `footer_primary` еще не стали основной bind-схемой;
	- следующий этап: вынести layout scheme `nordic` из fallback-совместимости в собственную карту позиций.

- Стартовала отдельная template-ветка `nordic`:
	- добавлен runtime scaffold `templates/nordic` с отдельным `main.tpl.php`, `manifest.php`, `options.form.php` и собственным `theme.css`;
	- добавлен стартовый shell со слотами `site_top`, `header_primary`, `header_secondary`, `hero`, `before_content`, `after_content`, `footer_primary`, `footer_secondary`;
	- добавлен дефолтный theme config `system/config/theme_nordic.yml`;
	- добавлен package mirror `packages/nordic` для install/update discipline шаблона.

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
	- [LANDING-BUILDER-DOCS-PACK-2026-04-03.md](archive/landingbuilder-2026-04-04/LANDING-BUILDER-DOCS-PACK-2026-04-03.md)
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
	- [LANDING-BUILDER-DOCS-PACK-2026-04-03.md](archive/landingbuilder-2026-04-04/LANDING-BUILDER-DOCS-PACK-2026-04-03.md)
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
	- [LANDING-BUILDER-DOCS-PACK-2026-04-03.md](archive/landingbuilder-2026-04-04/LANDING-BUILDER-DOCS-PACK-2026-04-03.md)
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
	- добавлена шпаргалка [LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md](archive/landingbuilder-2026-04-04/LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md);
	- зафиксированы 4 зоны экрана: верхняя панель, левая библиотека, центральный холст, правый inspector;
	- отдельно зафиксирован MVP-набор: секции, колонки, device toggles, builder blocks и system widgets на одном экране.
- Какие файлы затронуты:
	- [LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md](archive/landingbuilder-2026-04-04/LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md)
	- [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
	- [LANDING-BUILDER-DOCS-PACK-2026-04-03.md](archive/landingbuilder-2026-04-04/LANDING-BUILDER-DOCS-PACK-2026-04-03.md)
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

### 2026-04-04 / первая живая интеграция в системную страницу

- Что планировалось:
	- выбрать один безопасный реальный сценарий и провести `landingbuilder` из preview-маршрута в живую страницу сайта без тотального takeover шаблона.
- Что сделано:
	- в качестве первой живой цели выбрана страница категории объявлений `ads-category`;
	- checkpoint через полный pre-change script уперся в отказ `mysqldump`, поэтому как рабочая точка отката создан git snapshot `snapshot/20260404-083022`;
	- в `modelLandingbuilder` добавлены helper-методы для controlled overlay-интеграции category page;
	- добавлен hook `process_render_content_category_view`, который подмешивает builder HTML в block-позиции `before_content_items_list_html` и `after_content_items_list_html` без правки активного шаблона сайта;
	- после первого запуска исправлен контракт hook: `process_render_*` в InstantCMS передает один payload-массив, а не три отдельных аргумента;
	- исправлен маппинг реального content type проекта: для объявлений здесь используется `board`, поэтому `ads-category` теперь резолвится от `ctype board`, а не от `ads`;
	- добавлен reusable partial `overlay_zone.tpl.php` для живого рендера секций и системных widgets в overlay-зонах;
	- hook зарегистрирован в таблице событий InstantCMS;
	- package mirror синхронизирован сразу вместе с runtime-исходниками.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/hooks/process_render_content_category_view.php](../system/controllers/landingbuilder/hooks/process_render_content_category_view.php)
	- [templates/default/controllers/landingbuilder/overlay_zone.tpl.php](../templates/default/controllers/landingbuilder/overlay_zone.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/hooks/process_render_content_category_view.php](../packages/landingbuilder/package/system/controllers/landingbuilder/hooks/process_render_content_category_view.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/overlay_zone.tpl.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/overlay_zone.tpl.php)
- Что проверено:
	- `php -l` проходит на новых и измененных source/package PHP-файлах;
	- Problems panel не показывает новых ошибок в исходниках `landingbuilder`;
	- запись `landingbuilder:process_render_content_category_view` подтверждена в таблице событий;
	- страница `ads-category` подтверждена в БД как `prototype` + `hybrid_overlay`.
- Какие риски остались:
	- текущая живая интеграция для `ads-category` из-за статуса `prototype` видна только администратору;
	- на этом шаге реально подключены зоны `before_content` и `after_content`, а не полный sidebar/overlay-контур;
	- полный browser regression живой страницы еще нужно пройти из админ-сессии.
- Следующий шаг:
	- зайти в живую страницу категории объявлений под администратором и проверить полный цикл: overlay на странице, save, version restore, preview, затем решить публиковать ли `ads-category` шире или расширять интеграцию на следующую зону.

### 2026-04-04 / архитектурная очистка документации

- Что планировалось:
	- привести документацию к взрослой и устойчивой схеме после пересмотра архитектуры Нордик как отдельной системы темы, а не только overlay-конструктора.
- Что сделано:
	- добавлен новый канонический документ [LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md](../LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md);
	- обновлены опорные документы [README.md](../README.md), [docs/PROJECT-OVERVIEW.md](PROJECT-OVERVIEW.md), [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md), [LANDING-BUILDER-ROADMAP-2026-04-03.md](../LANDING-BUILDER-ROADMAP-2026-04-03.md);
	- из канонического набора исключены промежуточные документы `DOCS-PACK` и `CANVAS-UX-CHEATSHEET`;
	- для архивных документов добавлен индекс [docs/archive/landingbuilder-2026-04-04/README.md](archive/landingbuilder-2026-04-04/README.md).
- Какие файлы затронуты:
	- [LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md](../LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md)
	- [README.md](../README.md)
	- [docs/PROJECT-OVERVIEW.md](PROJECT-OVERVIEW.md)
	- [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
	- [LANDING-BUILDER-ROADMAP-2026-04-03.md](../LANDING-BUILDER-ROADMAP-2026-04-03.md)
	- [docs/archive/landingbuilder-2026-04-04/README.md](archive/landingbuilder-2026-04-04/README.md)
- Что проверено:
	- архитектурный фокус документов теперь совпадает с новой целевой моделью: `landingbuilder` + `nordic` + design system + migration mode;
	- канонический список документов сокращен и больше не смешивает мастер-спеки с временными шпаргалками.
- Какие риски остались:
	- часть более глубоких технических спецификаций еще сохраняет старую терминологию и будет постепенно выравниваться под новую модель по мере реализации template layer и theme tokens.
- Следующий шаг:
	- физически перенести промежуточные документы в архив и затем расширить contracts под global theme settings и shell slots.

### 2026-04-07 / второй уровень глобальной DS (radius, density, contrast)

- Что планировалось:
	- добавить второй уровень глобальной дизайн-системы как отдельные пресеты радиуса, плотности и контраста, чтобы настройки влияли на весь Nordic runtime, а не только на локальные элементы формы.
- Что сделано:
	- в каталогах опций `landingbuilder` добавлены `radius_preset`, `density_preset`, `contrast_preset` с дефолтами и названиями для админ-интерфейса;
	- форма глобального дизайна расширена отдельным fieldset `Второй уровень DS` с тремя новыми полями;
	- сохранение настроек и fallback-merge при ошибках валидации обновлены в обоих backend actions (`landingbuilder/design` и `nordicbuilder/defaults`);
	- runtime theme catalog расширен тремя новыми preset maps, добавлены соответствующие `default_*` mapping keys и merge-порядок CSS vars;
	- Nordic theme tokens/shell слой переведен на новые runtime vars (радиусы, плотность, контраст), включая `theme.css` и SCSS partials;
	- live и package mirrors синхронизированы по всем затронутым файлам.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/backend/forms/form_design.php](../system/controllers/landingbuilder/backend/forms/form_design.php)
	- [system/controllers/landingbuilder/backend/actions/design.php](../system/controllers/landingbuilder/backend/actions/design.php)
	- [system/controllers/nordicbuilder/backend/actions/defaults.php](../system/controllers/nordicbuilder/backend/actions/defaults.php)
	- [templates/default/controllers/landingbuilder/runtime_theme.php](../templates/default/controllers/landingbuilder/runtime_theme.php)
	- [templates/nordic/scss/theme/_tokens.scss](../templates/nordic/scss/theme/_tokens.scss)
	- [templates/nordic/scss/theme/_shell.scss](../templates/nordic/scss/theme/_shell.scss)
	- [templates/nordic/css/theme.css](../templates/nordic/css/theme.css)
	- package mirrors в `packages/landingbuilder/package/`, `packages/nordicbuilder/package/`, `packages/nordic/package/`.
- Что проверено:
	- editor diagnostics: новых ошибок в измененных PHP/SCSS/CSS файлах нет;
	- побайтная сверка `cmp` подтверждает синхронность live и package mirror-копий для всех измененных файлов.
- Какие риски остались:
	- часть визуальных элементов по-прежнему может использовать legacy hardcoded spacing/colors из старого CSS слоя и потребовать дополнительной токенизации;
	- для финальной UX-оценки нужен ручной проход в админке и на frontend-страницах с переключением новых пресетов.
- Следующий шаг:
	- пройти ручной smoke: смена `radius/density/contrast` в `nordicbuilder/defaults` и проверка эффекта на homepage/category/content page в live runtime.

### 2026-04-07 / стабилизация canvas и 1:1 parity preview/live

- Что планировалось:
	- восстановить работоспособность canvas после JS-падения;
	- добиться совпадения header/footer между preview и публичной главной;
	- убрать временную диагностику JS после стабилизации.
- Что сделано:
	- выявлена и исправлена причина падения canvas: `ReferenceError: isFullTakeover is not defined` в shell-map рендере;
	- для публичной главной в ветке `modern + takeover` внедрен row-based рендер chrome-рядов (header/footer) вокруг builder-content, чтобы структура совпадала с preview;
	- подключение runtime CSS сохранено для takeover-режима, чтобы стиль builder-блоков не расходился между preview/live;
	- временная JS-телеметрия отключена полностью: удалены browser hooks в canvas templates, удален `js_error_url` из API, удалены action-файлы `js_error.php` в live и package mirror;
	- все изменения синхронизированы в package mirrors.
- Какие файлы затронуты:
	- [templates/nordic/main.tpl.php](../templates/nordic/main.tpl.php)
	- [packages/nordic/package/templates/nordic/main.tpl.php](../packages/nordic/package/templates/nordic/main.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [system/controllers/nordicbuilder/backend/actions/canvas.php](../system/controllers/nordicbuilder/backend/actions/canvas.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/canvas.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/canvas.php)
	- удалены временные файлы:
		- `system/controllers/nordicbuilder/backend/actions/js_error.php`
		- `packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/js_error.php`
- Что проверено:
	- `php -l` проходит на всех измененных PHP/template файлах;
	- route smoke (guest): `/` и `/nordicbuilder/view/glav` отвечают `200`, admin routes без сессии корректно `403`;
	- parity-маркеры `icms-header__top/middle/bottom` и `icms-footer__middle/bottom` присутствуют и на `/`, и на `/nordicbuilder/view/glav`;
	- access-log admin-сессии подтверждает рабочий цикл `pages -> canvas -> widgets_catalog -> preview` c `200`.
- Какие риски остались:
	- финальная визуальная оценка 1:1 still требует ручной проверки глазами в браузере (guest/admin, с hard refresh), т.к. CLI smoke проверяет структуру и статусы, но не pixel-perfect рендер.
- Следующий шаг:
	- выполнить короткий ручной regression проход (guest/admin): homepage, preview `glav`, canvas save/publish/preview, после чего зафиксировать release checkpoint.

### 2026-04-07 / UX-полировка canvas, этап 3 (первый patch)

- Что планировалось:
	- начать реализацию нового UX по референсам: компактная шапка, модальная библиотека, постоянно видимый инспектор, менее навязчивые рамки и быстрый вызов библиотеки из колонки.
- Что сделано:
	- topbar переведен в более компактный режим (плотнее сетка, меньше отступы и высота action-кнопок);
	- правая панель инспектора закреплена открытой на desktop (состояние принудительно держится в JS-shell sync);
	- левая библиотека переведена из drawer в modal-overlay c backdrop, закрытием по `Esc` и клику по фону;
	- в колонках добавлен контекстный вызов библиотеки через кнопку `+` (action `open-library`, сразу в tab `blocks` и с выбором текущей колонки);
	- после вставки секции/блока/виджета библиотека автоматически закрывается, чтобы пользователь сразу видел live-результат на холсте;
	- визуальные рамки секций/колонок/нод сделаны более спокойными (меньше контраста и плотности), чтобы не перекрывать восприятие дизайна;
	- изменения синхронизированы в package mirrors.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [docs/WORKLOG.md](WORKLOG.md)
- Что проверено:
	- editor diagnostics: `No errors found` для live + обеих package mirror копий `canvas.tpl.php`.
- Какие риски остались:
	- UX-валидность нужно подтвердить ручным проходом в браузере (desktop/mobile), т.к. это визуально-поведенческие изменения;
	- следующий patch все еще нужен для дополнительной полировки micro-copy и spacing на мобильном холсте.
- Следующий шаг:
	- провести ручной smoke UX: открытие библиотеки из topbar/из `+` в колонке, вставка блока/виджета, автозакрытие modal, постоянная доступность инспектора справа на desktop.

### 2026-04-07 / Автоскейл базовых блоков (12/12 -> 100%)

- Что планировалось:
	- добавить отдельную настройку секции для автоскейла базовых блоков на всю ширину экрана;
	- сохранить совместимость с текущей 12-колоночной моделью и режимом full-container;
	- синхронизировать изменения в live и package mirrors.
- Что сделано:
	- в инспектор секции добавлен новый флаг `Автоскейл базовых блоков (12/12 -> 100% экрана)`;
	- в schema normalizer (backend PHP + canvas JS) добавлен default `settings.autoscale_base_blocks = false`;
	- в runtime renderer добавлен контекстный autoscale для базовых блоков:
		- если флаг секции включен и колонка на активном устройстве имеет `12/12`, блок получает класс `lb-runtime-block--autoscale`;
		- автоскейл применяется без изменения поведения системных виджетов;
	- в runtime/preview CSS добавлены стили full-bleed для `lb-runtime-block--autoscale`;
	- изменения синхронизированы в `packages/nordicbuilder` и `packages/landingbuilder`.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [templates/default/controllers/landingbuilder/runtime_renderer.php](../templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [system/controllers/landingbuilder/helpers/runtime_styles.php](../system/controllers/landingbuilder/helpers/runtime_styles.php)
	- [templates/default/controllers/landingbuilder/view.tpl.php](../templates/default/controllers/landingbuilder/view.tpl.php)
	- package mirrors в `packages/nordicbuilder/package/` и `packages/landingbuilder/package/`.
- Что проверено:
	- editor diagnostics: `No errors found` по всем измененным live + mirror файлам;
	- grep-smoke подтвердил наличие новой настройки в canvas/model и autoscale-ветки в runtime/css;
	- `cmp` подтвердил parity между live и обоими package mirrors для canvas/runtime renderer.
- Какие риски остались:
	- это кодовый regression (контракты/шаблоны), но не полный ручной визуальный e2e проход в браузере;
	- для UX-подтверждения нужен короткий click-smoke на реальной странице с комбинацией `container_preset=standard` + `autoscale_base_blocks=true`.

### 2026-04-07 / Автоскейл: inline toggle A + проверка preview/live

- Что сделано:
	- в canvas breakpoint-панель секции добавлена inline-кнопка `A` (`toggle-section-autoscale-base-blocks`) рядом с T/M/I;
	- панель теперь может показывать кнопку `A` и для 1-колоночной секции (даже когда stack-кнопки не актуальны);
	- усилен full-bleed CSS для autoscale:
		- переход на более устойчивый шаблон `position:relative + left/right 50% + margin -50vw`;
		- добавлен `overflow: visible` для autoscale-секции и внутренних оберток, чтобы убрать clipping от базового контейнера;
	- синхронизация live -> package mirrors подтверждена `cmp`.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [system/controllers/landingbuilder/helpers/runtime_styles.php](../system/controllers/landingbuilder/helpers/runtime_styles.php)
	- [templates/default/controllers/landingbuilder/view.tpl.php](../templates/default/controllers/landingbuilder/view.tpl.php)
	- package mirrors в `packages/nordicbuilder/package/` и `packages/landingbuilder/package/`.
- Что проверено:
	- diagnostics: `No errors found` для всех измененных live + mirror файлов;
	- smoke HTTP (guest):
		- `/` отвечает `200`, но не содержит runtime grid/autoscale маркеров (на этой странице builder runtime сейчас не активен);
		- `/nordicbuilder/view/*` для guest отвечает `404` (preview route недоступен без admin-сессии или при текущем статусе страницы), поэтому публичным curl нельзя проверить autoscale-визуал на preview напрямую.
- Какие риски остались:
	- для финального подтверждения "расширяется/не расширяется" нужен короткий ручной проход в админ-сессии на реальной preview-странице с включенной `A` и 12/12 колонкой.

### 2026-04-07 / Автоскейл: override внутренних Bootstrap container

- Что сделано:
	- добавлен scoped override только для autoscale-режима, чтобы внутренние Bootstrap-контейнеры не удерживали max-width:
		- `.lb-runtime-block--autoscale .container{max-width:none;width:100%}` (+ размеры `-sm/-md/-lg/-xl/-xxl`);
	- правило добавлено в runtime helper CSS и preview CSS для parity live/preview;
	- изменения синхронизированы в package mirrors.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/helpers/runtime_styles.php](../system/controllers/landingbuilder/helpers/runtime_styles.php)
	- [templates/default/controllers/landingbuilder/view.tpl.php](../templates/default/controllers/landingbuilder/view.tpl.php)
	- package mirrors в `packages/nordicbuilder/package/` и `packages/landingbuilder/package/`.
- Что проверено:
	- diagnostics: `No errors found` для live + mirror файлов;
	- `cmp` подтвердил parity live и mirrors для обоих файлов.
- Какие риски остались:
	- внешний preview-route (`/nordicbuilder/view/*`) для guest по-прежнему `404`, поэтому финальный визуальный smoke нужно делать в admin-сессии.

### 2026-04-07 / Bridge save: устранен откат статуса страницы в draft

- Симптом:
	- после сохранения canvas часть страниц начинала отдаваться как preview (`status != published`), из-за чего для guest маршрут `/nordicbuilder/view/{page_key}` возвращал `404`.
- Причина:
	- при bridge-сохранении статус брался из fallback-страницы и мог неявно возвращаться в `draft`.
- Что изменено:
	- в `landingbuilder/model.php` для bridge save добавлен расчет `effective_status` с защитой от деградации статуса (приоритет `published`, затем валидные статусы-кандидаты);
	- в `nordicbuilder/model.php` (`saveBridgePageSchema`) выровнен приоритет статуса: используется вычисленный `effective_status` и для page payload, и для `savePageDocument`.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/nordicbuilder/model.php](../system/controllers/nordicbuilder/model.php)
	- package mirrors в `packages/nordicbuilder/package/` и `packages/landingbuilder/package/`.
- Что проверено:
	- diagnostics: `No errors found` по всем измененным live + mirror файлам;
	- `cmp` parity подтвержден для всех синхронизированных моделей.

### 2026-04-07 / canvas: русификация stack + целевой regression (3 сценария)

- Что планировалось:
	- снизить англоязычность в адаптивных подсказках canvas;
	- подтвердить, что full-width секция работает и доступна в UI;
	- выполнить короткий целевой regression-прогон по 3 сценариям и зафиксировать результат.
- Что сделано:
	- в canvas заменены англоязычные формулировки в адаптивной панели и hover-подсказках:
		- `stack` -> `стек`;
		- `mixed` -> `смешано`;
		- `stack (1col)` -> `в столбик (1 колонка)`;
		- источник `layout` в user-facing подсказке -> `сетка`;
	- уточнена подсказка в инспекторе секции: для 100% ширины явно указано выбрать «Во всю ширину» в `Пресет контейнера`;
	- правки синхронизированы в package mirrors.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [docs/WORKLOG.md](WORKLOG.md)
- Что проверено (целевой regression, 3 сценария):
	- Сценарий 1: full-width секция
		- в catalog есть `container_preset = full` c title «Во всю ширину»;
		- runtime стили содержат `.lb-section--container-full .lb-section-inner`.
	- Сценарий 2: русификация canvas
		- в breakpoint-панели секции отображается `стек`;
		- hover-подсказки колонок и источник ширины показывают русские формулировки (`в столбик`, `смешано`, `сетка`).
	- Сценарий 3: parity runtime для stack/inherit
		- в runtime renderer присутствуют функции device/inheritance резолва ширин;
		- runtime CSS использует responsive grid vars (`--lb-grid-desktop/tablet/mobile`).
	- editor diagnostics: `No errors found` для трех измененных canvas-файлов.
- Какие риски остались:
	- regression был целевым и кодовым (контракт/шаблоны), без ручного визуального клика в браузере guest/admin;
	- для pixel-level подтверждения UX нужен короткий ручной проход в интерфейсе.
- Точка отката:
	- `backups/checkpoints/20260407-143558-canvas-russian-stack-regression`.

### 2026-04-07 / Autoscale: массовое включение для всех секций

- Что планировалось:
	- убрать ограничение UX, когда `A` нужно включать по одной секции;
	- дать быстрый сценарий «сделать весь лендинг широким» одним действием.
- Что сделано:
	- в breakpoint-панель секции добавлена кнопка `A+`, которая включает/выключает `autoscale_base_blocks` сразу во всех секциях текущей страницы;
	- в правую панель секции добавлена отдельная кнопка `Включить/Выключить автоскейл во всех секциях`;
	- добавлен единый action-handler `toggle-all-sections-autoscale-base-blocks` с массовым обновлением `section.settings.autoscale_base_blocks` по всей схеме;
	- изменения синхронизированы в package mirrors.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [docs/WORKLOG.md](WORKLOG.md)
- Что проверено:
	- editor diagnostics: `No errors found` для live + mirror `canvas.tpl.php`;
	- побайтная parity-проверка `cmp` подтверждает синхронность live и двух mirrors.
- Какие риски остались:
	- требуется короткий ручной UX smoke в браузере на странице с 2+ секциями, чтобы визуально подтвердить массовое переключение.
- Точка отката:
	- `backups/manual-checkpoints/20260407-153352-autoscale-all-sections`.

### 2026-04-07 / Autoscale: 2+ колонки и устранение путаницы A/A+

- Симптом:
	- в секциях с двумя колонками включение `A` не давало ожидаемого эффекта «широкой секции»;
	- при единственной секции на странице `A` визуально выглядел связанным с `A+`, что путало UX.
- Что изменено:
	- runtime: добавлен расчет видимых колонок секции и новый класс `lb-section--autoscale-wide` для случая `autoscale_base_blocks=true` и `visible_columns_count > 1`;
	- CSS (live + preview): для `lb-section--autoscale-wide` секция растягивается на всю ширину (`.lb-section-inner{max-width:none;padding-left:0;padding-right:0}`);
	- full-bleed поведение базовых блоков через `lb-runtime-block--autoscale` оставлено только для 12/12, чтобы не ломать 2-колоночную сетку перекрытиями;
	- canvas UX: `A+` показывается только если на странице больше одной секции;
	- подпись/tooltip `A` уточнены: `1 колонка 12/12 -> блок 100vw; 2+ колонки -> секция на всю ширину`.
- Какие файлы затронуты:
	- [templates/default/controllers/landingbuilder/runtime_renderer.php](../templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [system/controllers/landingbuilder/helpers/runtime_styles.php](../system/controllers/landingbuilder/helpers/runtime_styles.php)
	- [templates/default/controllers/landingbuilder/view.tpl.php](../templates/default/controllers/landingbuilder/view.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- package mirrors в `packages/nordicbuilder/package/` и `packages/landingbuilder/package/`.
- Что проверено:
	- editor diagnostics: `No errors found` по live + mirror копиям всех измененных файлов;
	- `cmp` parity подтвержден для runtime renderer, runtime styles, preview view и canvas template.
- Точка отката:
	- `backups/manual-checkpoints/20260407-155006-autoscale-2col-wide`.

### 2026-04-07 / Autoscale: отложенные фиксы (backlog)

- Зафиксировано как отложенные задачи:
	- развести настройки на два явных режима в UI: `wide section` и `full-bleed block`, чтобы `A` не совмещала два сценария;
	- добавить визуальный индикатор активного режима на секции (бейдж в header секции и в правом инспекторе);
	- добавить guard для 2+ колонок: если выбран режим full-bleed блока, показывать пояснение и не применять конфликтный рендер;
	- сделать короткий smoke-чеклист для `1col 12/12`, `2col 6/6`, `2col sidebar`, `stack mobile/tablet`, `container full/standard`;
	- проверить и выровнять parity preview/live на тех же сценариях в авторизованной сессии.
- Критерий закрытия backlog-пункта:
	- пользователь без дополнительных пояснений понимает, какой именно режим включен;
	- в 2-колоночных секциях не возникает ожидания full-bleed для каждой колонки;
	- ручной smoke по матрице сценариев проходит без визуальных расхождений preview/live.

### 2026-04-07 / SSR publish + внутренние Instant-страницы (bindings)

- Симптом:
	- при `Опубликовать SSR` возникала ошибка про отсутствующую таблицу `nordicbuilder_page_renders`;
	- для внутренних страниц нужен быстрый путь привязки к native Instant-ссылкам без ручного JSON редактирования.
- Что изменено:
	- в `nordicbuilder model` добавлен auto-heal: при publish вызывается `ensurePageRenderTable()`, которая создает `nordicbuilder_page_renders` при отсутствии;
	- `Published Page Renders` добавлен в persistence summary, чтобы отсутствие таблицы было видно в служебной панели;
	- в `publish_page` сообщение об ошибке уточнено: если auto-create не сработал, указывается на права БД (`CREATE TABLE`);
	- в мастере `Новый макет страницы` добавлены готовые пресеты привязки внутренних страниц Instant:
		- `Категория объявлений (board)` -> `overlay.content_category.board`;
		- `Профиль пользователя` -> `overlay.user_profile.default`.
- Какие файлы затронуты:
	- [system/controllers/nordicbuilder/model.php](../system/controllers/nordicbuilder/model.php)
	- [system/controllers/nordicbuilder/backend/actions/publish_page.php](../system/controllers/nordicbuilder/backend/actions/publish_page.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- package mirrors в `packages/nordicbuilder/package/` и `packages/landingbuilder/package/`.
- Что проверено:
	- editor diagnostics: `No errors found` по всем измененным live + mirror файлам;
	- `cmp` parity подтвержден для model/action/template между live и mirrors.
- Какие риски остались:
	- для `content item` (карточка контента) отдельный overlay-hook пока не реализован, поэтому из коробки закрыты сценарии `content category` и `user profile`.
- Точка отката:
	- `backups/manual-checkpoints/20260407-160830-ssr-publish-and-internal-bindings`.

### 2026-04-07 / Сценарий "все внутренние страницы кроме главной" + native content_body

- Что изменено:
	- в matching `route_params` для bindings добавлена поддержка отрицания по значению через префикс `!`;
	- в мастере "Новый макет страницы" добавлен пресет:
		- `Все внутренние страницы (кроме главной)`;
		- создает правило `page.all_internal` с `route_params_json: { "page_type": "!homepage" }`;
	- в `templates/nordic/main.tpl.php` (и package mirror) исправлен full takeover рендер:
		- если выбранный content slot помечен как `native`, теперь выводится системный `$this->body()`;
		- это закрывает сценарий страницы-обертки, где builder рендерит зоны до/после, а нативный контент Instant остается в `content_body`.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php)
	- [templates/nordic/main.tpl.php](../templates/nordic/main.tpl.php)
	- package mirrors в `packages/landingbuilder/package/`, `packages/nordicbuilder/package/`, `packages/nordic/package/`.
- Что проверено:
	- синтаксические проверки/diagnostics для измененных PHP/TPL файлов — без ошибок;
	- пресет и генерация `route_params_json` присутствуют в live + mirrors;
	- parity live/mirrors подтверждено по ключевым измененным файлам.
- Риски:
	- отрицание `!` реализовано для скалярного значения route-параметра; массивные комбинированные отрицания не поддерживаются (не требовались в данном сценарии).
- Точка отката:
	- `backups/manual-checkpoints/20260407-183500-internal-all-except-home-and-native-body`.

### 2026-04-07 / Устранена блокировка visual workspace по schema check

- Симптом:
	- при открытии canvas появлялось сообщение: `Сначала нужно установить persistence-таблицы nordicbuilder...` даже в сценарии, где рабочие таблицы уже есть.
- Причина:
	- `hasInstalledSchema()` проверял все persistence-таблицы, включая `nordicbuilder_page_renders`; на инстансах со старой схемой отсутствие этой таблицы блокировало вход в visual workspace.
- Что изменено:
	- в `modelNordicbuilder::hasPersistenceTables()` добавлен auto-heal для `nordicbuilder_page_renders`:
		- если таблица отсутствует, вызывается `ensurePageRenderTable()`;
		- при успешном создании schema-check продолжает работу без ложного fail.
- Какие файлы затронуты:
	- [system/controllers/nordicbuilder/model.php](../system/controllers/nordicbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/model.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/model.php)
- Что проверено:
	- diagnostics: `No errors found` по live + package model;
	- parity `cmp` для `model.php` live/package подтвержден.
- Риски:
	- если у БД нет права `CREATE TABLE`, auto-heal не сработает и schema-check останется красным (ожидаемое поведение).
- Точка отката:
	- `backups/manual-checkpoints/20260407-190100-schema-installed-autofix`.

### 2026-04-07 / Canvas redirect fix + пункт Workspace в меню

- Симптом:
	- часть страниц (например свежесозданная `vnytri`) при попытке открыть canvas перебрасывались в `workspace`.
- Причина:
	- schema-check в `nordicbuilder` считал persistence «неустановленным», если отсутствовала любая из ключевых таблиц.
- Что изменено:
	- в `modelNordicbuilder` добавлен auto-heal не только для `page_renders`, но и для базовых таблиц:
		- `nordicbuilder_page_documents`
		- `nordicbuilder_preset_tokens`
		- `nordicbuilder_binding_options`
		- `nordicbuilder_page_renders`
	- в backend-меню конструктора добавлен прямой пункт `Workspace`.
- Какие файлы затронуты:
	- [system/controllers/nordicbuilder/model.php](../system/controllers/nordicbuilder/model.php)
	- [system/controllers/nordicbuilder/backend.php](../system/controllers/nordicbuilder/backend.php)
	- package mirrors в `packages/nordicbuilder/package/`.
- Что проверено:
	- diagnostics: `No errors found` по измененным live + package файлам;
	- parity `cmp` для `model.php` и `backend.php` между live/package подтвержден.
- Риски:
	- если у БД нет `CREATE TABLE`, авто-восстановление не сможет создать таблицы и редирект в `workspace` останется (ожидаемо, нужна ручная SQL-установка).
- Точка отката:
	- `backups/manual-checkpoints/20260407-193000-canvas-open-and-workspace-menu`.

### 2026-04-07 / Debug persistence guard для canvas

- Симптом:
	- даже после пересоздания страниц canvas продолжал редиректить в `workspace` с сообщением про неустановленные persistence-таблицы.
- Причина:
	- guard в `actionNordicbuilderCanvas` опирается на `getWorkspaceSummary()->persistence`, а `getPersistenceSummary()` до этого выполнял только пассивную проверку `isTableExists`.
- Что изменено:
	- `getPersistenceSummary()` переведен в активный режим:
		- для каждой отсутствующей persistence-таблицы выполняется попытка auto-heal через `ensurePersistenceTable()`;
		- в summary добавлено поле `missing_tables`.
	- сообщение в `canvas` стало диагностическим:
		- выводит точный список отсутствующих таблиц;
		- добавляет явную подсказку проверить права БД на `CREATE TABLE`, если авто-создание не сработало.
- Какие файлы затронуты:
	- [system/controllers/nordicbuilder/model.php](../system/controllers/nordicbuilder/model.php)
	- [system/controllers/nordicbuilder/backend/actions/canvas.php](../system/controllers/nordicbuilder/backend/actions/canvas.php)
	- package mirrors в `packages/nordicbuilder/package/`.
- Что проверено:
	- diagnostics: `No errors found` по измененным live + package файлам;
	- parity `cmp` для `model.php` и `backend/actions/canvas.php` между live/package подтвержден.
- Точка отката:
	- `backups/manual-checkpoints/20260407-201500-canvas-persistence-debug`.

### 2026-04-07 / Hotfix: canvas не блокируется при отсутствии только page_renders

- Симптом:
	- guard в canvas продолжал редиректить в `workspace`, когда отсутствовала только таблица `nordicbuilder_page_renders`.
- Что изменено:
	- в `actionNordicbuilderCanvas` блокирующей ошибкой теперь считаются только отсутствующие базовые persistence-таблицы документов;
	- если отсутствует только `nordicbuilder_page_renders`, canvas открывается, но показывается warning, что SSR publish недоступен до появления таблицы.
- Какие файлы затронуты:
	- [system/controllers/nordicbuilder/backend/actions/canvas.php](../system/controllers/nordicbuilder/backend/actions/canvas.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/canvas.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/backend/actions/canvas.php)
- Что проверено:
	- diagnostics: `No errors found`;
	- parity `cmp` live/package подтвержден.
- Точка отката:
	- `backups/manual-checkpoints/20260407-203500-canvas-unblock-when-only-ssr-missing`.

### 2026-04-07 / Canvas UX: быстрые секции вокруг body

- Что изменено:
	- в библиотеке секций добавлены быстрые карточки: `Блок над body`, `Блок в левый sidebar`, `Блок в правый sidebar`, `Блок под body`;
	- каждая карточка создает секцию сразу с нужной `zone_key`, без ручной настройки в инспекторе;
	- добавлена отдельная карточка `Body (системный)` с пояснением, что native `content_body` создается автоматически и не добавляется как секция.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
- Что проверено:
	- `php -l` для live + mirror `canvas.tpl.php` без ошибок;
	- diagnostics: `No errors found` для live + mirrors;
	- mirrors синхронизированы копированием live-файла.
- Точка отката:
	- `backups/manual-checkpoints/20260407-180452-quick-zone-section-buttons`.

### 2026-04-07 / Canvas UX: видимый системный body на холсте

- Что изменено:
	- для внутренних страниц (не standalone) на холсте добавлен видимый блок `Body (системный, auto)`;
	- внутри блока добавлены кнопки быстрого добавления секций в зоны: `Над body`, `Левый sidebar`, `Правый sidebar`, `Под body`;
	- пустой starter-экран больше не скрывает логику на внутренних страницах: пользователь сразу видит, что body уже существует автоматически.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
- Что проверено:
	- `php -l` без ошибок для live + mirrors;
	- diagnostics: `No errors found` для live + mirrors;
	- parity live/mirrors подтверждена копированием и проверкой.
- Точка отката:
	- `backups/manual-checkpoints/20260407-182450-native-body-visible-scaffold`.

### 2026-04-07 / Public overlay + green zone highlight

- Что изменено:
	- снято ограничение предпросмотра/overlay только для админа: страницы landingbuilder теперь рендерятся и для обычных пользователей даже при статусе, отличном от `published`;
	- улучшен матчинг route params: отрицательные условия вида `!value` больше не ломают биндинг, если конкретный ключ отсутствует в route context;
	- на canvas добавлена визуальная green-подсветка:
		- системный `Body (системный, auto)` теперь выделяется зеленой рамкой;
		- секции с явно заданной `Зона в shell` выделяются зеленой рамкой и бейджем зоны.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/landingbuilder/actions/view.php](../system/controllers/landingbuilder/actions/view.php)
	- [templates/nordic/main.tpl.php](../templates/nordic/main.tpl.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/model.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/actions/view.php](../packages/landingbuilder/package/system/controllers/landingbuilder/actions/view.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/actions/view.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/actions/view.php)
	- [packages/nordic/package/templates/nordic/main.tpl.php](../packages/nordic/package/templates/nordic/main.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
- Что проверено:
	- `php -l` без ошибок для live + всех mirror-файлов;
	- diagnostics: `No errors found` для ключевых live-файлов;
	- parity подтверждена через `sha256sum` (live == mirrors).
- Точка отката:
	- `backups/manual-checkpoints/20260407-221500-public-overlay-and-canvas-zone-highlight`.

### 2026-04-07 / Body frame pro-mode: 1/2/3 колонки вокруг native body + ширины

- Что изменено:
	- для overlay-страниц добавлен режим body-frame `1 / 2-left / 2-right / 3` в `schema.layout`;
	- добавлены управляемые ширины колонок (`body_left_span`, `body_right_span`) с нормализацией и ограничениями;
	- runtime-shell теперь учитывает body-frame режим при активации sidebar slots (`content_sidebar_left/right`), а не только системный variant;
	- в `nordic/main.tpl.php` builder-sidebars начали рендериться как полноценные колонки вокруг системного body в takeover-режиме;
	- в canvas native-body scaffold добавлены controls для выбора режима 1/2/3 и drag-диапазоны ширины sidebar;
	- в CSS `nordic/theme.css` включены переменные `--lb-content-left-span/right-span/main-span` для фактической ширины колонок на live.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [templates/nordic/main.tpl.php](../templates/nordic/main.tpl.php)
	- [templates/nordic/css/theme.css](../templates/nordic/css/theme.css)
	- mirrors в `packages/landingbuilder/package/`, `packages/nordicbuilder/package/`, `packages/nordic/package/`.
- Что проверено:
	- `php -l` без ошибок для live + mirror копий измененных PHP/TPL файлов;
	- `cmp` parity = `0` по всем парам live/mirror для model/canvas/main/theme;
	- diagnostics `No errors found` по ключевым live-файлам.
- Риски:
	- для старых страниц с вручную сохраненной зоной `content_body` у секции нужен один цикл «открыть canvas -> сохранить», чтобы схема полностью очистилась от legacy-конфликтов;
	- если для выбранного режима не добавлены секции в sidebar-зоны, колонка не появится (ожидаемое поведение).
- Точки отката:
	- `backups/manual-checkpoints/20260407-234500-body-layout-dnd-start`;
	- `backups/manual-checkpoints/20260407-232300-native-body-hard-guard`.
	- `backups/manual-checkpoints/20260407-235500-body-frame-pro-mode-complete`.

### 2026-04-08 / Canvas UX cleanup: зоны вокруг body + упрощенный инспектор секции

- Что изменено:
	- в canvas для native-body режима секции сгруппированы по рабочим зонам: `перед body`, `левый sidebar`, `body`, `правый sidebar`, `после body`;
	- добавлены явные empty-state панели с кнопкой `+ Добавить` для каждой зоны, чтобы было понятно, почему sidebar может быть пустым;
	- системный `Body (системный, auto)` теперь живет в центральной колонке middle-области, а не «сверху всего»;
	- в инспекторе секции оставлен базовый набор полей (название, layout, зона, контейнер, ритм), а `Тип секции` / `Стилевой пресет` / служебные CSS-поля перенесены в сворачиваемый блок `Расширенные настройки секции`;
	- добавлена responsive-адаптация middle-области зон для узких экранов.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
- Что проверено:
	- `php -l` без ошибок для live + mirror `canvas.tpl.php`;
	- parity `cmp` live/mirror = `0` для обеих mirrors;
	- diagnostics: `No errors found` по live `canvas.tpl.php`.
- Риски:
	- reorder секций по-прежнему общий (не отдельный per-zone), поэтому при активном DnD стоит дополнительно контролировать поле `Зона в shell`;
	- для страниц со старыми схемами может понадобиться одно сохранение, чтобы визуальная группировка и runtime совпали полностью.
- Точка отката:
	- `backups/manual-checkpoints/20260408-000500-canvas-zone-workspace-simplify`.

### 2026-04-08 / Hotfix: убран дублирующийся native body scaffold в canvas

- Симптом:
	- после UX cleanup системный блок `Body (системный, auto)` показывался дважды: в центре zone-workspace и отдельно сверху холста.
- Что изменено:
	- в рендере canvas удален дополнительный верхний вывод `nativeBodyScaffoldMarkup`; scaffold остается только внутри центральной колонки zone-workspace.
- Какие файлы затронуты:
	- [templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/landingbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
	- [packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php](../packages/nordicbuilder/package/templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php)
- Что проверено:
	- `php -l` без ошибок для live + mirror `canvas.tpl.php`;
	- parity `cmp` live/mirror = `0` для обеих mirrors;
	- diagnostics: `No errors found` по live `canvas.tpl.php`.
- Риски:
	- нет известных функциональных рисков; изменение ограничено HTML-сборкой одной ветки рендера canvas.
- Точка отката:
	- `backups/manual-checkpoints/20260408-000500-canvas-zone-workspace-simplify`.

### 2026-04-07 / Закрытие дня: canvas-shell стабилизация и UX вокруг body

- Что планировалось:
	- довести сценарий внутренних страниц до состояния, где native `content_body` стабильно виден, а builder предсказуемо работает вокруг него;
	- упростить canvas UX для нетехнического пользователя.
- Что сделано:
	- завершена серия фиксов runtime/template/canvas для native body (fallback adapter/bindings, hard guard зон, shell-aware routing секций);
	- доведен body frame pro-mode (`1/2-left/2-right/3` + spans) от схемы до live runtime;
	- canvas переведен в zone-first представление (`перед body` / `левый` / `body` / `правый` / `после body`) и упрощен inspector секции;
	- внесен hotfix дублирования `Body (системный, auto)` на холсте;
	- синхронизированы live + package mirrors для `landingbuilder`, `nordicbuilder`, `nordic`.
- Какие файлы затронуты:
	- core/model/actions/runtime/template/canvas файлы в `system/`, `templates/`, `packages/landingbuilder/`, `packages/nordicbuilder/`, `packages/nordic/`;
	- журналы: `docs/WORKLOG.md`, `docs/worklogs/2026-04-07-instant-page-compatibility-pivot.md`, `LANDING-BUILDER-ACTIVE-PLAN-2026-04-04.md`.
- Что проверено:
	- серия `php -l` по измененным live+mirror PHP/TPL файлам проходит;
	- parity проверка `cmp` для ключевых live/mirror пар проходит;
	- editor diagnostics по ключевым измененным файлам без новых ошибок.
- Какие риски остались:
	- ручной продуктовый smoke (guest/admin, preview/live, сценарии зон вокруг body) еще не закрыт;
	- reorder секций в canvas пока остается общим, не отдельным per-zone.
- План на завтра (2026-04-08):
	1. Пройти ручной smoke по матрице: `homepage`, внутренняя `/board`, профиль пользователя; guest/admin; canvas/preview/live parity.
	2. Добить UX-полировку per-zone reorder и сделать поведение «куда упала секция» максимально очевидным.
	3. Зафиксировать результаты в `docs/WORKLOG.md` и закрыть regression-checklist для body-frame + native-body зон.
- Точка отката:
	- `backups/manual-checkpoints/20260408-000500-canvas-zone-workspace-simplify`.

### 2026-04-07 / Подготовлен короткий блог-материал про вектор Nordic Builder

- Что сделано:
	- подготовлена короткая статья для публикации в блоге о векторе развития конструктора, целях и практических преимуществах для пользователя и команды.
- Где лежит:
	- [docs/blog/2026-04-07-nordicbuilder-vector.md](blog/2026-04-07-nordicbuilder-vector.md)
- Примечание:
	- текст ориентирован на product-аудиторию, без перегруза техническими деталями, и может использоваться как черновик для публичной публикации.

### 2026-04-08 / Проверка сценариев native body + секции вокруг body

- Цель проверки:
	- `content_body` в native-режимах остается под управлением системы;
	- builder рендерит только зоны вокруг body;
	- секции не могут захватить `content_body` даже при legacy `zone_key`.
- Что подтверждено по коду:
	- в runtime `buildRuntimeZones()` включен hard-guard: секция с `zone_key=content_body` при native-адаптере автоматически уводится в разрешенную builder-зону;
	- в canvas нормализация секций и zone selector не позволяют закреплять секции в native `content_body`;
	- в `templates/nordic/main.tpl.php` при native slot/overlay fallback рендерится системный `$this->body()`, а builder-sidebars выводятся отдельно.
- Live-проверка маршрутов:
	1. Сценарий «body для всего сайта + секции вокруг него»:
		- `/` открывается с системным содержимым и доп. блоками builder вокруг body.
	2. Сценарий «body только для одного типа контента + блоки»:
		- `/board` (категория board) открывается с нативным body категории и builder-блоками вокруг.
		- `/users/profile` возвращает `404` (маршрут невалидный на этом инстансе), рабочий профильный маршрут: `/users/1`.
- Что проверено технично:
	- `php -l` без ошибок для `system/controllers/landingbuilder/model.php`, `templates/nordic/main.tpl.php`, `templates/admincoreui/controllers/landingbuilder/backend/canvas.tpl.php`;
	- diagnostics: `No errors found` по этим файлам;
	- parity `cmp` live/mirror = `0` для model/canvas/main.
- Риски:
	- для режима «весь сайт» нужен явный набор bindings (как минимум `homepage` + `all_except_homepage`), иначе часть маршрутов не попадет под нужный page key;
	- для отдельных overlay-сценариев нужно проверять корректный рабочий route конкретного проекта (пример: профиль — `/users/{id}`).

### 2026-04-08 / Применение canonical bindings для сценария «весь сайт»

- Что сделано:
	- в таблице `cms_nordicbuilder_binding_options` ключи правил приведены к каноническим:
		- `page.glav` -> `page.homepage`
		- `page.all_ver` -> `page.all_internal`
	- синхронизированы и внутренние JSON-ключи `options_json.key`.
- Что проверено:
	- в БД подтверждены правила:
		- `page.homepage` -> `glav`
		- `page.all_internal` -> `ver` (`route_params.page_type = !homepage`);
	- live DOM-проверка:
		- `/` содержит builder-секции (`data-slot-key=content_body`, count=4);
		- `/board` содержит builder-секцию (`data-slot-key=after_content`, count=1);
		- `/users/1` содержит builder-секцию (`data-slot-key=after_content`, count=1).
- Точка отката данных:
	- `backups/manual-checkpoints/20260408-090500-bindings-canonicalize/cms_nordicbuilder_binding_options.sql`.

## 2026-04-09 / Pivot на 3 advanced PRO-блока

- Что планировалось:
	- уйти от большого числа однотипных блоков к 2-3 мощным конфигурируемым блокам;
	- реализовать минимум один блок с гибкой сменой структуры, цветов и внутренней композиции.
- Что сделано:
	- в block catalog добавлены 3 блока: `pro.flex-composer`, `pro.metrics-grid-pro`, `pro.faq-adaptive-pro`;
	- в manifest registry добавлены соответствующие runtime-контракты, defaults и meta;
	- в runtime renderer добавлены title map и реальные ветки рендера для всех 3 блоков;
	- `pro.flex-composer` реализован как структурно-гибкий блок: `split-left/split-right/stack-center/media-background`, ratio колонок, surface mode, палитра, media fit/shape/shadow, CTA и feature pills;
	- live-файлы синхронизированы в package mirrors (`landingbuilder` + `nordicbuilder`).
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [system/controllers/nordicbuilder/data/block_manifests.php](../system/controllers/nordicbuilder/data/block_manifests.php)
	- [templates/default/controllers/landingbuilder/runtime_renderer.php](../templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/model.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/data/block_manifests.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/data/block_manifests.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [packages/nordicbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php](../packages/nordicbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json](checklists/NEW_BLOCK_CHECKLIST_STATUS.json)
	- [docs/WORKLOG.md](WORKLOG.md)
- Что проверено:
	- pre-change checkpoint выполнен: создан backup `backups/db/builders-20260409-131647.sql.gz` и snapshot `snapshot/20260409-131648`;
	- `php -l` без ошибок для обновленного `runtime_renderer.php`;
	- parity-синхронизация live -> package mirrors выполнена.
- Какие риски остались:
	- не выполнен полный авторизованный visual smoke в canvas/preview/live для новых блоков на desktop/tablet/mobile;
	- не выполнен целевой publish-check для подтверждения SSR parity в production flow.
- Следующий шаг:
	- пройти короткий smoke по 3 блокам (создание на canvas -> сохранение -> preview/live), затем зафиксировать результат отдельным checkpoint-коммитом.

## 2026-04-09 / Dynamic data source для кастомных блоков (MVP)

- Что планировалось:
	- сделать еще один «взрослый» шаг: подключить к кастомным блокам данные из типов контента InstantCMS, чтобы блоки работали не только от ручного ввода.
- Что сделано:
	- в block catalog для `pro.metrics-grid-pro` и `pro.faq-adaptive-pro` добавлены inspector-поля data source:
		- `data_source_mode` (`manual` / `ctype.list`),
		- `data_ctype_name`, `data_limit`, `data_sort`,
		- field mapping (`data_value_field`/`data_label_field`/`data_note_field` и `data_question_field`/`data_answer_field`);
	- в runtime renderer добавлен безопасный fetch данных из InstantCMS:
		- автонормализация ctype,
		- fallback на ctype из route context,
		- выборка через `cmsCore::getModel('content')->getContentItems()`,
		- fallback на ручной `items_text`, если dynamic источник пустой или недоступен;
	- в `nordicbuilder` block manifests расширены props schema + defaults под новые поля data source;
	- live и package mirrors синхронизированы.
- Какие файлы затронуты:
	- [system/controllers/landingbuilder/model.php](../system/controllers/landingbuilder/model.php)
	- [templates/default/controllers/landingbuilder/runtime_renderer.php](../templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [system/controllers/nordicbuilder/data/block_manifests.php](../system/controllers/nordicbuilder/data/block_manifests.php)
	- [packages/landingbuilder/package/system/controllers/landingbuilder/model.php](../packages/landingbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/nordicbuilder/package/system/controllers/landingbuilder/model.php](../packages/nordicbuilder/package/system/controllers/landingbuilder/model.php)
	- [packages/landingbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php](../packages/landingbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [packages/nordicbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php](../packages/nordicbuilder/package/templates/default/controllers/landingbuilder/runtime_renderer.php)
	- [packages/nordicbuilder/package/system/controllers/nordicbuilder/data/block_manifests.php](../packages/nordicbuilder/package/system/controllers/nordicbuilder/data/block_manifests.php)
	- [docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json](checklists/NEW_BLOCK_CHECKLIST_STATUS.json)
- Что проверено:
	- `php -l` без ошибок для всех измененных live/mirror PHP файлов;
	- `cmp -s` подтверждает parity между live и package mirrors по model/manifests/runtime_renderer.
- Какие риски остались:
	- не пройден авторизованный визуальный smoke в canvas/preview/live по кейсам `manual -> ctype.list`;
	- не проверены edge-cases для ctype с нетиповой структурой полей (например, пустой `teaser`/`description`).
- Следующий шаг:
	- пройти быстрый smoke на 2 сценариях (`pro.metrics-grid-pro`, `pro.faq-adaptive-pro`) с реальным ctype и проверить fallback на `items_text`.
