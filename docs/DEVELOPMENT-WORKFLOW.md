# Development Workflow

## Базовый режим работы

1. Любая заметная задача начинается с checkpoint кода и базы.
2. Любая задача должна иметь понятный результат: код, документ, схема, миграция или проверка.
3. Любое изменение архитектуры, JSON-контракта или backend settings должно быть отражено в docs.
4. Любая risky-операция на прод-сервере делается только после фиксации точки отката.

## Runtime-First Workflow для Нордик

Для `Нордик` принят основной режим разработки: сначала рабочий InstantCMS-контур, затем синхронизация installable package на каждом стабильном шаге.

Это означает:

1. Основная разработка идет в живых runtime-файлах InstantCMS.
2. Результат сразу проверяется через админку и рабочий сайт.
3. После каждого завершенного стабильного шага изменения зеркалятся в `packages/landingbuilder/package/`.
4. Если меняется SQL-слой, одновременно обновляется `packages/landingbuilder/install.sql` и отдельно оценивается upgrade-path для уже установленных копий.
5. Нельзя откладывать packaging «на потом», если runtime-слой уже изменился заметно.

Основные рабочие зоны этого режима:

- `system/controllers/landingbuilder`
- `templates/admincoreui/controllers/landingbuilder`
- `templates/default/controllers/landingbuilder`
- `packages/landingbuilder/package`
- `packages/landingbuilder/install.sql`

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
3. Проверить результат в runtime-контуре InstantCMS.
4. Синхронизировать installable package в `packages/landingbuilder/package/`.
5. Обновить docs при изменении поведения.
6. Обновить [docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json](checklists/NEW_BLOCK_CHECKLIST_STATUS.json).
