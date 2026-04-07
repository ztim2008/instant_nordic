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
4. Для нового contract-first core-компонента `nordicbuilder` изменения зеркалятся в `packages/nordicbuilder/package/`, а installable zip собирается из `packages/nordicbuilder/` как пользовательский коммерческий дистрибутив.
5. Если меняется SQL-слой, одновременно обновляется соответствующий `install.sql` и отдельно оценивается upgrade-path для уже установленных копий.
6. Для `nordicbuilder` SQL-изменения оформляются через `packages/nordicbuilder/migrations/*.sql` с сохранением backward compatibility.
7. Нельзя откладывать packaging «на потом», если runtime-слой уже изменился заметно.

Основные рабочие зоны этого режима:

- `system/controllers/landingbuilder`
- `system/controllers/nordicbuilder`
- `templates/admincoreui/controllers/landingbuilder`
- `templates/default/controllers/landingbuilder`
- `templates/nordic`
- `packages/nordic/package`
- `packages/landingbuilder/package`
- `packages/landingbuilder/install.sql`
- `packages/nordicbuilder/package`
- `packages/nordicbuilder/install.sql`

## PHP CLI для этого репозитория

- Боевой сайт `nordic-builder.store` обслуживается через отдельный apache/php84 stack, а не через системный `php` 8.1.
- Для CLI-проверок, bootstrap-скриптов и ad-hoc команд по проекту использовать `/opt/php84/bin/php`.
- Системный `php` и `php8.1` на сервере не подходят для bootstrap этого репозитория: на них отсутствует `mbstring`, и `require 'bootstrap.php'` падает на `mb_internal_encoding()`.
- Если нужна одноразовая проверка runtime-контекста из shell, использовать форму: `/opt/php84/bin/php -r 'require "bootstrap.php"; /* code */'`.

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
4. Синхронизировать installable package в `packages/landingbuilder/package/` или `packages/nordicbuilder/package/` в зависимости от слоя.
5. Обновить docs при изменении поведения.
6. Обновить [docs/checklists/NEW_BLOCK_CHECKLIST_STATUS.json](checklists/NEW_BLOCK_CHECKLIST_STATUS.json).

## Коммерческий zip `nordicbuilder`

Для `nordicbuilder` целевой формат поставки это installable zip для пользователей конструктора.

Базовый поток такой:

1. синхронизировать `packages/nordicbuilder/` с live source;
2. обновить `VERSION` и `CHANGELOG.md` по SemVer;
3. проверить `manifest.ru.ini`, `manifest.json`, `install.sql`, `migrations/`, `install.php` и `package/`;
4. собрать архив командой `bash scripts/build-nordicbuilder-package.sh`;
5. получить `dist/nordicbuilder.zip` и versioned-копию в `dist/`.

Подробный регламент релиза и миграций:

- `docs/NORDICBUILDER-RELEASES-AND-MIGRATIONS.md`
