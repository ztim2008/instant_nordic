# Nordic Builder Store

Рабочий репозиторий проекта на базе InstantCMS с документацией по Landing Builder и локальным набором инструментов для безопасной разработки на живом сервере.

## Что уже есть

- Базовый код InstantCMS в корне проекта.
- Документы по Landing Builder в корне проекта.
- Вендорная dev-папка `instantcms-mcp-main` для MCP-инструментов разработки.
- Git-база, документация по процессу, rollback и работе с агентом.

## Ключевые документы

- [docs/PROJECT-OVERVIEW.md](docs/PROJECT-OVERVIEW.md)
- [docs/DEVELOPMENT-WORKFLOW.md](docs/DEVELOPMENT-WORKFLOW.md)
- [docs/ROLLBACK-AND-RECOVERY.md](docs/ROLLBACK-AND-RECOVERY.md)
- [docs/WORKLOG.md](docs/WORKLOG.md)
- [AGENTS.md](AGENTS.md)
- [AGENT-CHEATSHEET.md](AGENT-CHEATSHEET.md)

## Канонические документы по Landing Builder

- [LANDING-BUILDER-DOCS-PACK-2026-04-03.md](LANDING-BUILDER-DOCS-PACK-2026-04-03.md)
- [LANDING-BUILDER-ROADMAP-2026-04-03.md](LANDING-BUILDER-ROADMAP-2026-04-03.md)
- [LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md](LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md)
- [LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md)
- [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
- [LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md](LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md)
- [LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md](LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md)

## Практика работы

1. Перед рискованными изменениями создать checkpoint:
   `./scripts/pre-change-checkpoint.sh "checkpoint: before <task>"`
2. Работать маленькими, проверяемыми шагами.
3. Обновлять docs при каждом изменении архитектуры, процесса или контракта.
4. Если меняется блок Landing Builder, обновлять [docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json](docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json).
