# Rollback And Recovery

## Основное правило

Откат кода без отката базы может вернуть проект в неконсистентное состояние. Если меняется SQL или бизнес-данные, нужен и git-checkpoint, и backup базы.

## Безопасная схема перед изменениями

1. Сделать backup базы:
   `./scripts/db-backup.sh`
2. Сделать git snapshot:
   `./scripts/git-snapshot.sh "checkpoint: before <task>"`
3. Для удобства можно сделать оба шага одной командой:
   `./scripts/pre-change-checkpoint.sh "checkpoint: before <task>"`

### Режимы checkpoint при проблемах доступа к БД

По умолчанию checkpoint работает в строгом режиме и останавливается, если backup БД не удался.

1. Строгий режим (default):
   `CHECKPOINT_DB_BACKUP_MODE=required ./scripts/pre-change-checkpoint.sh "checkpoint: before <task>"`
2. Мягкий режим (для срочной фиксации кода, когда backup временно недоступен):
   `CHECKPOINT_DB_BACKUP_MODE=best-effort ./scripts/pre-change-checkpoint.sh "checkpoint: before <task>"`
3. Пропуск backup (только осознанно):
   `CHECKPOINT_DB_BACKUP_MODE=skip ./scripts/pre-change-checkpoint.sh "checkpoint: before <task>"`

Важно: `best-effort` и `skip` не заменяют реальный backup БД перед SQL-рисками.

### Override учетных данных для дампа

Если runtime-учетка из `system/config/config.php` не имеет прав на dump, можно временно задать отдельную dump-учетку:

`DB_DUMP_HOST=localhost DB_DUMP_BASE=builders DB_DUMP_USER=<dump_user> DB_DUMP_PASS=<dump_pass> ./scripts/db-backup.sh`

Те же переменные работают и через `pre-change-checkpoint.sh`.

## Безопасный rollback для проверки

Чтобы посмотреть старое состояние, не ломая текущую рабочую ветку:

`./scripts/git-restore-branch.sh <tag-or-commit>`

Это создает отдельную ветку от указанной точки.

## Жесткий rollback

Жесткий rollback в текущей ветке допустим только после проверки и понимания последствий.

Пример:

`git reset --hard <tag-or-commit>`

Использовать только осознанно. Перед этим лучше создать backup branch.

## База данных

- Backup базы складывается в `backups/db/`.
- Каталог `backups/` исключен из git.
- Backup перед SQL-изменениями обязателен.

## Когда rollback обязателен

- после неудачной миграции
- после поломки routing
- после критической ошибки frontend/backend
- после некорректной интеграции нового блока или adapter
