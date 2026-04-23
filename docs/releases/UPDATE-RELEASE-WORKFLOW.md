# NordicBlocks: update release workflow после базового installable архива

Дата: 2026-04-22

## Статус

Первый этап NordicBlocks закрыт базовым installable архивом.

Дальше delivery-модель такая:

1. базовый архив `nordicblocks.zip` используется как опорная свежая установка;
2. новые поставки выпускаются как update-пакеты компонента `nordicblocks`;
3. update-пакет собирается из того же package source-of-truth, но проходит отдельный release contour и публикуется отдельным именем архива.

Структура `dist` для NordicBlocks теперь фиксируется так:

1. `dist/nordicblocks/start/` — installable baseline и его versioned копии;
2. `dist/nordicblocks/updates/<version>/` — артефакты update-релиза для конкретной версии;
3. плоские файлы в `dist/` сохраняются как совместимые алиасы последней сборки для старых команд и быстрых ручных smoke.

## Source of truth

Для update-релизов источником правды остаётся директория:

1. корень публичного product repo

Версия компонента теперь ведётся в двух связанных точках:

1. `VERSION`
2. `manifest.ru.ini`

Release notes по версиям вести в:

3. `CHANGELOG.md`

Синхронизация выполняется только через:

```bash
bash scripts/nordicblocks-version-sync.sh 0.1.1
```

или без аргумента, если целевая версия уже записана в `VERSION`.

## Команды релиза

Базовая сборка installable архива:

```bash
bash scripts/build-nordicblocks-package.sh
```

Update-сборка поверх того же package tree:

```bash
bash scripts/build-nordicblocks-update-package.sh 0.1.1
```

Результат update-сборки:

1. `dist/nordicblocks-update.zip`
2. `dist/nordicblocks-update-<version>.zip`
3. `dist/nordicblocks/updates/<version>/nordicblocks-update.zip`
4. `dist/nordicblocks/updates/<version>/nordicblocks-update-<version>.zip`

Результат installable-сборки:

1. `dist/nordicblocks.zip`
2. `dist/nordicblocks-<version>.zip`
3. `dist/nordicblocks/start/nordicblocks.zip`
4. `dist/nordicblocks/start/nordicblocks-<version>.zip`

Важно: по структуре это тот же совместимый InstantCMS package, но он публикуется как update-artifact и должен ставиться поверх уже установленного базового релиза.

## Release checklist

Перед выпуском update-пакета обязательно:

1. синхронизировать runtime source и `package/`;
2. прогнать целевой узкий smoke по изменённому scope;
3. при изменении runtime/admin файлов проверить package mirror parity;
4. обновить версию через `scripts/nordicblocks-version-sync.sh`;
5. обновить `CHANGELOG.md` и кратко записать, что вошло в релиз;
	Для ускорения можно копировать шаблон следующей записи из нижней части `CHANGELOG.md`.
6. собрать `nordicblocks-update` архив;
7. проверить состав архива через `unzip -l`;
8. убедиться, что в архив не попали рабочие docs, `*.md`, `*.txt`, внутренние notes;
9. сделать manual update smoke поверх уже установленного компонента;
10. зафиксировать release outcome в worklog.

## Минимальный проверочный контур

```bash
bash scripts/build-nordicblocks-update-package.sh 0.1.1
unzip -l dist/nordicblocks-update.zip
/opt/php84/bin/php -l install.php
/opt/php84/bin/php scripts/nordicblocks-flow-smoke.php
```

Если change-set затрагивает editor/runtime parity, этого недостаточно без ручного smoke на установленном компоненте.

## Политика публикации

1. Новый full install archive не считается стандартным способом поставки после первого этапа.
2. Стандартный путь релиза теперь: `base install once -> component updates`.
3. Полный installable архив сохраняется как опорный baseline и как fallback для чистой установки.
4. При ручной навигации по `dist/` ориентироваться сначала на `dist/nordicblocks/start/`, а для обновлений — на `dist/nordicblocks/updates/<version>/`.