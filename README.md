# Nordic Builder Store

Рабочий репозиторий проекта на базе InstantCMS с документацией по Landing Builder и локальным набором инструментов для безопасной разработки на живом сервере.

## Статус этапа

- Первый этап разработки NordicBlocks завершён.
- Базовый installable архив собран, проверен на fresh-install в `/instant` и принят как опорный дистрибутив этапа.
- Дальнейшие поставки планируются как update-пакеты компонента поверх этого базового архива, а не как новая ручная пересборка структуры с нуля.

## Что уже есть

- Базовый код InstantCMS в корне проекта.
- Документы по Landing Builder в корне проекта.
- Вендорная dev-папка `instantcms-mcp-main` для MCP-инструментов разработки.
- Git-база, документация по процессу, rollback и работе с агентом.

## Ключевые документы

- [NORDICBLOCKS-INSPECTOR-WEEK-PLAN-2026-04-17.md](NORDICBLOCKS-INSPECTOR-WEEK-PLAN-2026-04-17.md)
- [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
- [docs/PROJECT-OVERVIEW.md](docs/PROJECT-OVERVIEW.md)
- [docs/DEVELOPMENT-WORKFLOW.md](docs/DEVELOPMENT-WORKFLOW.md)
- [docs/ROLLBACK-AND-RECOVERY.md](docs/ROLLBACK-AND-RECOVERY.md)
- [docs/WORKLOG.md](docs/WORKLOG.md)
- [docs/nordicblocks/GLOBAL-DESIGN-FOUNDATION-V2.md](docs/nordicblocks/GLOBAL-DESIGN-FOUNDATION-V2.md)
- [docs/nordicblocks/CATALOG-BROWSER-V1.md](docs/nordicblocks/CATALOG-BROWSER-V1.md)
- [AGENTS.md](AGENTS.md)
- [AGENT-CHEATSHEET.md](AGENT-CHEATSHEET.md)

## Канонические документы по Landing Builder

- [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
- [LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md](LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md)
- [LANDING-BUILDER-ROADMAP-2026-04-03.md](LANDING-BUILDER-ROADMAP-2026-04-03.md)
- [LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md](LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md)
- [LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md](LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md)
- [LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md)
- [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
- [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md)
- [LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md](LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md)
- [LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md](LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md)
- [LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md](LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md)

## Архив

- Исторические и промежуточные документы перенесены в [docs/archive/landingbuilder-2026-04-04/README.md](docs/archive/landingbuilder-2026-04-04/README.md)

## Практика работы

1. Перед рискованными изменениями создать checkpoint:
   `./scripts/pre-change-checkpoint.sh "checkpoint: before <task>"`
2. Работать маленькими, проверяемыми шагами.
3. Для `Нордик` использовать runtime-first режим: сначала правки в рабочем InstantCMS-контуре, затем синхронизация `packages/landingbuilder/package/` на каждом стабильном шаге.
4. Для коммерческого core-компонента `nordicbuilder` source-of-truth дистрибутива лежит в `packages/nordicbuilder/`, а installable zip собирается через `bash scripts/build-nordicbuilder-package.sh`.
5. Обновлять docs при каждом изменении архитектуры, процесса или контракта.
6. Если меняется блок Landing Builder, обновлять [docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json](docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json).

## Политика релизов NordicBlocks

- Базовый установочный архив считается главным опорным релизом первого этапа.
- Следующие поставки выпускать как обновления компонента `nordicblocks`, совместимые с установкой поверх базового архива.
- В installable архив не включать рабочую markdown/txt документацию и внутренние notes, если они не нужны рантайму или установщику.

## CLI примечание

- Для shell-команд по этому проекту использовать `/opt/php84/bin/php`, потому что боевой домен работает через php84 stack.
- Системный `php` 8.1 на этом сервере не проходит `bootstrap.php` из-за отсутствующего `mbstring`.
