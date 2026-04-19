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

## NordicBlocks Scaffold Policy

Если агент создаёт новый block type для NordicBlocks в рамках поддерживаемых scaffold profiles и manifest-first family:

1. По умолчанию использовать `scripts/nordicblocks-scaffold-block.php`, а не ручное копирование существующего блока.
2. После scaffold apply обязательно прогонять `scripts/nordicblocks-validate-block.php --block=<slug>`.
3. Считать scaffold + validator стандартным путём для новых managed block types, а ручную сборку с нуля использовать только как осознанное исключение.
4. Не считать scaffold финальной работой: после него всё ещё обязательны доработка production markup при необходимости и ручной live smoke в editor/runtime.

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
