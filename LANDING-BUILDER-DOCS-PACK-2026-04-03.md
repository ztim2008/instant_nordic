# Landing Builder Docs Pack

## Назначение

Этот файл нужен как точка входа для переноса всей связки builder-документов в новый проект.

Если комплект будет распакован в другом workspace или на чистой инсталляции InstantCMS, именно этот файл должен объяснить:

1. какие документы входят в pack;
2. в каком порядке их читать;
3. что уже зафиксировано;
4. какой следующий шаг по реализации.

## Состав pack

В pack должны входить следующие файлы:

1. `LANDING-BUILDER-DOCS-PACK-2026-04-03.md`
2. `LANDING-BUILDER-ROADMAP-2026-04-03.md`
3. `LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md`
4. `LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md`
5. `LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md`
6. `LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md`
7. `LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md`

## Порядок чтения

Правильный порядок чтения такой:

1. `LANDING-BUILDER-DOCS-PACK-2026-04-03.md`
2. `LANDING-BUILDER-ROADMAP-2026-04-03.md`
3. `LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md`
4. `LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md`
5. `LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md`
6. `LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md`
7. `LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md`

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
2. Текущая рекомендуемая база рендера это `modern` или его наследник.
3. Собственная БД-модель builder разделена на pages, page_versions, bindings, presets и registry.
4. JSON documents для page schema, block manifest, adapter manifest, preset tokens и binding options уже определены.
5. Adapter layer и editable zones для существующих страниц уже описаны.
6. Lifecycle registry, preset inheritance, page versioning и runtime binding resolution уже описан.

## Что не начинать без clean install

Не рекомендуется начинать первую реализацию прямо в текущем сильно кастомизированном проекте.

Правильный порядок:

1. поднять отдельный чистый InstantCMS;
2. создать отдельную ветку;
3. желательно использовать отдельный workspace или worktree;
4. перенести туда этот docs-pack;
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

## Контрольный чек-лист при переносе

Перед стартом в новом проекте проверить:

1. Все 7 файлов из pack реально перенесены.
2. Имена файлов сохранены без переименований.
3. Новая ветка создана отдельно от текущего кастомизированного сайта.
4. Работа ведётся на clean InstantCMS instance.
5. Следующий шаг согласован как `component skeleton first`.