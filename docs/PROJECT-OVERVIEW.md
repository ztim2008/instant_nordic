# Project Overview

## Цель

Собрать и развивать проект Nordic Builder Store как рабочую площадку для реализации Landing Builder поверх InstantCMS.

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

## Главный принцип

Этот репозиторий живет на сервере, поэтому изменения должны быть обратимыми, документированными и сопровождаться checkpoint перед рискованными действиями.

## Источники истины

1. Для архитектуры Landing Builder: документы `LANDING-BUILDER-*.md` в корне.
2. Для правил работы в текущем репозитории: документы в `docs/`.
3. Для поведения AI-агента: [AGENTS.md](../AGENTS.md) и [.github/copilot-instructions.md](../.github/copilot-instructions.md).
4. Для фактического маршрута работ: [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](../LANDING-BUILDER-MASTER-PLAN-2026-04-03.md).

## Ближайший этап

Сначала создать skeleton компонента `landingbuilder` внутри InstantCMS, затем завести SQL-модель и backend CRUD, и только после этого переходить к editor UI.
