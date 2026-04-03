# Development Workflow

## Базовый режим работы

1. Любая заметная задача начинается с checkpoint кода и базы.
2. Любая задача должна иметь понятный результат: код, документ, схема, миграция или проверка.
3. Любое изменение архитектуры, JSON-контракта или backend settings должно быть отражено в docs.
4. Любая risky-операция на прод-сервере делается только после фиксации точки отката.

## Git-правила

- Основная ветка: `main`.
- Для крупных задач рекомендуется новая ветка: `feature/<short-name>`.
- Один логический шаг = один commit.
- Сообщения commit должны быть короткими и предметными.

Примеры:

- `init: project ops foundation`
- `docs: add landingbuilder workflow`
- `feat: add landingbuilder manifest skeleton`
- `fix: correct binding resolution`

## Обязательный pre-change checkpoint

Перед изменениями, которые затрагивают:

- SQL-структуру
- системные контроллеры
- routing
- шаблоны
- деплой на сайт

нужно выполнить:

`./scripts/pre-change-checkpoint.sh "checkpoint: before <task>"`

## Работа с документацией

- `README.md` держит общую навигацию.
- `docs/PROJECT-OVERVIEW.md` описывает проектный контекст.
- `docs/DEVELOPMENT-WORKFLOW.md` описывает процесс.
- `docs/ROLLBACK-AND-RECOVERY.md` описывает откат.
- `docs/WORKLOG.md` фиксирует ход работ.

## Работа с Landing Builder

Если меняется логика нового блока или нового adapter flow:

1. Проверить документы из `LANDING-BUILDER-*.md`.
2. Обновить код.
3. Обновить docs при изменении поведения.
4. Обновить [docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json](checklists/NEW_BLOCK_CHECKLIST_STATUS.json).
