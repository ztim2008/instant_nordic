# Landing Builder Docs Pack

## Рабочая навигация в этом репозитории

- Главный трекер: [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
- Публичное имя продукта: `Нордик`
- Язык продукта: русский
- Целевая платформа: InstantCMS 2
- Development base: `instantcms-mcp-main`

## Назначение

Этот файл нужен как точка входа для переноса всей связки builder-документов в новый проект.

Если комплект будет распакован в другом workspace или на чистой инсталляции InstantCMS, именно этот файл должен объяснить:

1. какие документы входят в pack;
2. в каком порядке их читать;
3. что уже зафиксировано;
4. какой следующий шаг по реализации.

## Состав pack

В исходный pack должны входить следующие файлы:

1. `LANDING-BUILDER-DOCS-PACK-2026-04-03.md`
2. `LANDING-BUILDER-ROADMAP-2026-04-03.md`
3. `LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md`
4. `LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md`
5. `LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md`
6. `LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md`
7. `LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md`

В текущем рабочем репозитории дополнительно есть управляющий документ:

8. `LANDING-BUILDER-MASTER-PLAN-2026-04-03.md`
9. `LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md`
10. `LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md`
11. `LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md`
12. `LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md`

## Порядок чтения

Правильный порядок чтения в текущем репозитории такой:

1. `LANDING-BUILDER-MASTER-PLAN-2026-04-03.md`
2. `LANDING-BUILDER-DOCS-PACK-2026-04-03.md`
3. `LANDING-BUILDER-ROADMAP-2026-04-03.md`
4. `LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md`
5. `LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md`
6. `LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md`
7. `LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md`
8. `LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md`
9. `LANDING-BUILDER-CANVAS-UX-CHEATSHEET-2026-04-03.md`
10. `LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md`
11. `LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md`
12. `LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md`

Такой порядок нужен потому что:

- roadmap объясняет продукт и scope;
- backend spec объясняет admin model;
- data model объясняет SQL и persistent layer;
- JSON contracts объясняют форматы документов;
- page adapters spec объясняет integration layer;
- lifecycle spec объясняет, как всё это движется в runtime и через registry.

## Что уже зафиксировано

На текущем этапе зафиксированы следующие уровни архитектуры:

1. Конструктор живёт как отдельный компонент InstantCMS, а не как замена темы.
2. Продукт должен поставляться как hybrid-модель: компонент `landingbuilder` + template `nordic`.
3. Шаблон `nordic` должен быть доступен в настройках сайта как шаблон по умолчанию.
4. `instantcms-mcp-main` в корне является основной dev-базой проекта.
5. Любой блок должен уметь работать с manual и dynamic источниками данных.
6. Страницы сайта должны подключаться к builder в нескольких режимах: full takeover, hybrid overlay и partial zone injection.
7. Canvas должен работать по модели `section -> columns -> nodes`, а не по голым row/col терминам InstantCMS.
8. Стандартные widgets InstantCMS должны добавляться на холст как визуальные элементы.
9. Собственная БД-модель builder разделена на pages, page_versions, bindings, presets и registry.
10. JSON documents для page schema, block manifest, adapter manifest, preset tokens и binding options уже определены.
11. Adapter layer и editable zones для существующих страниц уже описаны.
12. Lifecycle registry, preset inheritance, page versioning и runtime binding resolution уже описан.

## Что не начинать без clean install

Не рекомендуется начинать первую реализацию прямо в текущем сильно кастомизированном проекте.

Правильный порядок:

1. поднять отдельный чистый InstantCMS;
2. создать отдельную ветку;
3. желательно использовать отдельный workspace или worktree;
4. перенести туда этот docs-pack и master plan;
5. только после этого стартовать код skeleton компонента.

## Что должно быть первым кодовым шагом

Когда docs-pack будет перенесён в новый проект, первым шагом должен быть не editor UI, а каркас компонента:

1. `backend.php`
2. `frontend.php`
3. `install.sql`
4. `backend/forms/form_options.php`
5. `backend/actions/pages.php`
6. `backend/actions/bindings.php`
7. registry refresh skeleton

## Минимальный handoff summary

Если этот pack будет распакован в новом проекте, новое рабочее сообщение можно формулировать так:

> Стартуем реализацию `landingbuilder` на чистой инсталляции InstantCMS. Архитектура и contracts уже зафиксированы в docs-pack. Первый этап: каркас компонента, install.sql, backend menu, pages/bindings CRUD skeleton и registry refresh flow без editor UI.

Для текущего проекта handoff уточняется так:

> Стартуем Нордик как цифровой продукт для InstantCMS 2. Основа разработки в этом workspace это `instantcms-mcp-main`. Результат должен включать компонент `landingbuilder`, frontend template `nordic`, install/update pipeline и дальнейшие release-пакеты.

## Контрольный чек-лист при переносе

Перед стартом в новом проекте проверить:

1. Все 7 файлов из pack реально перенесены.
2. Имена файлов сохранены без переименований.
3. Новая ветка создана отдельно от текущего кастомизированного сайта.
4. Работа ведётся на clean InstantCMS instance.
5. Следующий шаг согласован как `component skeleton first`.