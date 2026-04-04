# Project Overview

## Цель

Собрать и развивать проект Nordic Builder Store как рабочую площадку для реализации Нордик как полноценной theme system для InstantCMS.

Публичное имя конструктора в проекте: `Нордик`.
Рабочее техническое имя компонента: `landingbuilder`.
Рабочее техническое имя frontend template: `nordic`.

## Что находится в репозитории

- Базовый код InstantCMS в корне.
- Рабочая конфигурация прод-сайта на сервере.
- Документация по будущему Landing Builder.
- Вендорная dev-папка `instantcms-mcp-main`.
- Проектные документы по процессу, rollback и работе с агентом.

## Зафиксированные проектные решения

1. Основная dev-база разработки в этом workspace: [instantcms-mcp-main](../instantcms-mcp-main).
2. Итоговый продукт должен включать не только компонент, но и install/update pipeline.
3. Итоговый продукт должен включать frontend template `nordic`, который будет виден в настройках сайта как шаблон по умолчанию.
4. Целевая архитектура продукта: `landingbuilder` + `nordic` + встроенная дизайн-система + режим постепенной миграции через overlay.

## Главный принцип

Этот репозиторий живет на сервере, поэтому изменения должны быть обратимыми, документированными и сопровождаться checkpoint перед рискованными действиями.

Отдельно для `Нордик` зафиксирован runtime-first режим: сначала изменение и проверка в рабочем InstantCMS-контуре, затем немедленная синхронизация installable package, чтобы runtime и дистрибутив не расходились.

## Источники истины

1. Для архитектуры Landing Builder: документы `LANDING-BUILDER-*.md` в корне.
2. Для правил работы в текущем репозитории: документы в `docs/`.
3. Для поведения AI-агента: [AGENTS.md](../AGENTS.md) и [.github/copilot-instructions.md](../.github/copilot-instructions.md).
4. Для фактического маршрута работ: [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md).
5. Для новой целевой модели template + design system: [LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md](../LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md).

## Ближайший этап

Сначала довести безопасный regression overlay-режима, затем перейти к template skeleton `nordic`, global theme tokens и управляемым shell slots отдельной темы.
