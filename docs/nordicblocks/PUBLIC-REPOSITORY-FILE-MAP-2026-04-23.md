# NordicBlocks Public Repository File Map

## Назначение

Этот документ фиксирует, какие текущие папки и файлы рабочего репозитория должны перейти в публичный продуктовый repo NordicBlocks, а какие должны остаться только в приватном проекте.

## Переносить в публичный продуктовый repo

### Product root

1. packages/nordicblocks/manifest.ru.ini -> manifest.ru.ini
2. packages/nordicblocks/install.php -> install.php
3. packages/nordicblocks/install.sql -> install.sql
4. packages/nordicblocks/CHANGELOG.md -> CHANGELOG.md

### Runtime payload

1. packages/nordicblocks/package/static -> package/static
2. packages/nordicblocks/package/system -> package/system
3. packages/nordicblocks/package/templates -> package/templates

### Build and release scripts

1. scripts/build-nordicblocks-package.sh -> scripts/build-nordicblocks-package.sh
2. scripts/build-nordicblocks-update-package.sh -> scripts/build-nordicblocks-update-package.sh
3. scripts/nordicblocks-package-preflight.sh -> scripts/nordicblocks-package-preflight.sh
4. scripts/nordicblocks-version-sync.sh -> scripts/nordicblocks-version-sync.sh

### Block scaffold and validator tooling

1. scripts/nordicblocks-scaffold-block.php -> scripts/nordicblocks-scaffold-block.php
2. scripts/nordicblocks-scaffold-lib.php -> scripts/nordicblocks-scaffold-lib.php
3. scripts/nordicblocks-validate-block.php -> scripts/nordicblocks-validate-block.php

### Product docs

Нужно переносить выборочно, а не весь текущий docs/nordicblocks пакет.

В первую волну подходят:

1. docs/nordicblocks/INSTALLABLE-ZIP-2026-04-20.md
2. docs/nordicblocks/BLOCK-SCAFFOLD-PIPELINE-STAGE3.md
3. docs/nordicblocks/BLOCK-SCAFFOLD-VALIDATOR-SPEC-V1.md
4. docs/nordicblocks/NORDICBLOCKS-V2-ROADMAP.md
5. docs/nordicblocks/NEWS-FAMILY-V1.md
6. docs/nordicblocks/HERO-FAMILY-V1.md
7. docs/nordicblocks/CATALOG-FAMILY-V1.md

При переносе их лучше нормализовать в более публичные имена и разложить по тематическим папкам docs/.

### Smoke scripts

Переносить только те smoke/preflight checks, которые можно запускать без live-server-specific секретов и без зависимости от production state.

## Оставлять только в приватном проекте

### Полный сайт и platform root

1. system/
2. templates/
3. static/
4. upload/
5. cache/
6. backups/
7. bootstrap.php
8. index.php
9. cron.php

Причина: это структура полного сайта на платформе, а не структура продукта NordicBlocks.

### Server-specific and secret-sensitive

1. system/config/config.php
2. любые локальные env-specific конфиги
3. live-generated caches
4. DB backups и checkpoints

### Working and operational docs

Оставлять приватными по умолчанию:

1. day plans;
2. live incident worklogs;
3. internal rollout maps;
4. temporary migration notes;
5. server operational checklists;
6. любые документы, описывающие внутреннюю кухню рабочего сервера, а не продукт.

### Private integration scripts

В приватном repo должны остаться скрипты, завязанные на конкретный сайт или на текущий server root:

1. scripts/pre-change-checkpoint.sh
2. scripts/db-backup.sh
3. scripts/git-snapshot.sh
4. scripts/nordicblocks-clear-ssr-cache.php
5. любые live smoke scripts, которые зависят от конкретных block ids, URLs или server deployment.

## Спорная зона: что переносить после ревизии

Следующие области не нужно переносить автоматически. Их надо сначала просмотреть и разделить на product-safe и project-specific части.

1. docs/nordicblocks/WORKLOG-*.md
2. scripts/nordicblocks-*-smoke.php
3. templates/admincoreui/controllers/nordicblocks/backend/* в корне рабочего проекта
4. system/controllers/nordicblocks/* в корне рабочего проекта

Правильный источник правды для публичного repo здесь должен быть package-представление, а не разросшийся live-root.

## Правило по source of truth

Для будущего публичного repo source of truth должен быть таким:

1. product repo хранит canonical product files;
2. рабочий private project потребляет релизы продукта;
3. изменения из private project поднимаются обратно в product repo осознанно, а не через слепой mirror всего дерева.