# Copilot Instructions

## Project Context

- Это рабочий репозиторий на сервере.
- Для Landing Builder использовать документы `LANDING-BUILDER-*.md` в корне как основной источник проектных решений.
- Для процесса и rollback использовать документы в `docs/`.

## Working Rules

- Перед рискованными изменениями предлагать checkpoint.
- При SQL-изменениях учитывать backup базы.
- Не коммитить `system/config/config.php`, `backups/`, runtime-generated файлы и архивы.
- Если меняется новый блок Landing Builder, обновлять `docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json`.
- Документацию обновлять вместе с изменениями процесса, архитектуры и контрактов.
