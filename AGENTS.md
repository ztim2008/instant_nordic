# AGENTS.md

## Scope

Эти правила относятся ко всему репозиторию.

## Repo Priorities

1. Репозиторий живет на рабочем сервере, поэтому безопасность отката важнее скорости.
2. Документы `LANDING-BUILDER-*.md` в корне являются каноническими для проектирования Landing Builder.
3. Документы в `docs/` являются каноническими для процесса разработки именно в этом репозитории.

## Before Significant Changes

Перед изменениями, которые затрагивают SQL, контроллеры, routing, шаблоны, backend settings или контракты JSON:

1. Предложить или создать checkpoint.
2. Напомнить про backup базы, если меняется SQL.
3. Не менять `system/config/config.php`, если это не явно требуется пользователем.

## Landing Builder Rules

Если агент работает над `landingbuilder`:

1. Сначала свериться с `LANDING-BUILDER-*.md`.
2. Не изобретать структуру поверх документов без явной причины.
3. При добавлении нового блока обновить [docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json](docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json).
4. Поддерживать docs в актуальном состоянии, если меняется архитектура, contracts или workflow.

## Repo Hygiene

- Не коммитить runtime secrets.
- Не коммитить `system/config/config.php`.
- Не коммитить `backups/`.
- Держать commits маленькими и осмысленными.

## Completion Standard

В конце заметной задачи агент должен сообщить:

1. Что изменено.
2. Что проверено.
3. Какие риски остались.
4. Какая точка отката актуальна.
