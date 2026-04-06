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
