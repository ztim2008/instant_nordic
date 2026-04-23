# NordicBlocks: installable ZIP для новой установки

Дата: 2026-04-20

## Цель

Для NordicBlocks канонический дистрибутив должен ставиться в InstantCMS из одного ZIP-архива через стандартную админскую установку дополнений.

Это означает:

1. не multi-component bundle;
2. не архив с `components/nordicblocks`;
3. не отдельный ручной post-copy сценарий;
4. обычный single-package ZIP, который понимает стандартный `cmsInstaller`.

## Каноническая структура архива

Архив `nordicblocks.zip` должен содержать в корне:

1. `manifest.ru.ini`
2. `install.php`
3. `install.sql`
4. `package/`

Именно такой layout ожидают:

1. `system/controllers/admin/actions/install.php`
2. `system/controllers/admin/actions/install_ftp.php`
3. `system/core/installer.php`

Важно: текущий стандартный installer InstantCMS читает `manifest.ru.ini`, а не `manifest.json`, и работает с корневым `package/`, а не с вложенным `components/<name>/package/`.

## Source of truth

Исходником installable package для NordicBlocks является директория:

1. корень публичного product repo

В неё должны входить:

1. install manifest `manifest.ru.ini`
2. installer hooks `install.php`
3. schema/bootstrap SQL `install.sql`
4. payload tree `package/`

## Сборка архива

Каноническая команда:

```bash
bash scripts/build-nordicblocks-package.sh
```

Результат:

1. `dist/nordicblocks.zip`
2. `dist/nordicblocks-<version>.zip`

Версия архива берётся из секции `[version]` в `manifest.ru.ini`.

После завершения первого этапа этот installable ZIP считается базовым архивом для fresh-install.

Дальнейшие поставки нужно выпускать отдельным update-контуром:

```bash
bash scripts/build-nordicblocks-update-package.sh 0.1.1
```

Update-архив публикуется как:

1. `dist/nordicblocks-update.zip`
2. `dist/nordicblocks-update-<version>.zip`

Отдельный release workflow зафиксирован в `docs/releases/UPDATE-RELEASE-WORKFLOW.md`.

## Что именно проверять перед поставкой

Минимальная валидация installable ZIP:

1. архив собирается без ошибок;
2. в корне архива есть `manifest.ru.ini`, `install.php`, `install.sql`, `package/`;
3. `manifest.ru.ini` содержит секции `[info]`, `[version]`, `[author]`, `[install]`;
4. `install.php` проходит syntax check;
5. `install.sql` и `install.php` идемпотентны для новой установки;
6. runtime smoke текущего установленного компонента остаётся зелёным после сборки.

## Практический проверочный контур

Для репозитория NordicBlocks достаточно следующего набора команд:

```bash
bash scripts/build-nordicblocks-package.sh
unzip -l dist/nordicblocks.zip
/opt/php84/bin/php -l install.php
/opt/php84/bin/php scripts/nordicblocks-sync.php
/opt/php84/bin/php scripts/nordicblocks-flow-smoke.php
```

Где:

1. `unzip -l` проверяет installable layout архива;
2. `php -l` проверяет installer hook на syntax-level;
3. `nordicblocks-sync.php` подтверждает post-install sync contract;
4. `nordicblocks-flow-smoke.php` подтверждает, что блоковый CRUD/runtime контур остаётся рабочим.

## Ограничение текущей проверки

Эта проверка подтверждает:

1. архив структурно совместим со стандартным installer InstantCMS;
2. package metadata и installer hooks валидны;
3. текущий live-контур NordicBlocks не сломан.

Она не заменяет отдельный smoke на полностью чистой InstantCMS-установке.

Для release-grade финала всё равно нужен отдельный manual install smoke на fresh instance:

1. загрузка `nordicblocks.zip` в админке InstantCMS;
2. прохождение install wizard без ручного вмешательства;
3. появление контроллера `nordicblocks`;
4. появление виджета `nordicblocks_block`;
5. создание и вывод тестового блока после установки.