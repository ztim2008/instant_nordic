# Project Overview

## Цель

Собрать и развивать проект Nordic Builder Store как рабочую площадку для реализации Landing Builder поверх InstantCMS.

## Что находится в репозитории

- Базовый код InstantCMS в корне.
- Рабочая конфигурация прод-сайта на сервере.
- Документация по будущему Landing Builder.
- Вендорная dev-папка `instantcms-mcp-main`.
- Проектные документы по процессу, rollback и работе с агентом.

## Главный принцип

Этот репозиторий живет на сервере, поэтому изменения должны быть обратимыми, документированными и сопровождаться checkpoint перед рискованными действиями.

## Источники истины

1. Для архитектуры Landing Builder: документы `LANDING-BUILDER-*.md` в корне.
2. Для правил работы в текущем репозитории: документы в `docs/`.
3. Для поведения AI-агента: [AGENTS.md](../AGENTS.md) и [.github/copilot-instructions.md](../.github/copilot-instructions.md).

## Ближайший этап

Сначала создать skeleton компонента `landingbuilder` внутри InstantCMS, затем завести SQL-модель и backend CRUD, и только после этого переходить к editor UI.
