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
