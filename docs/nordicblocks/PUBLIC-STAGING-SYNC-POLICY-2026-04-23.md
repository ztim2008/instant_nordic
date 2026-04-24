# NordicBlocks Public Staging Sync Policy

## Решение

Для NordicBlocks public staging должен обновляться не вручную по случаю, а как обязательная часть каждого release contour.

Это означает:

1. после сборки install release;
2. после сборки update release;
3. после обновления VERSION, manifest.ru.ini и CHANGELOG.md;
4. после выбора публичного набора docs.

нужно сразу синхронизировать staged public repo.

## Зачем это нужно

Без этого staging быстро превращается в устаревшую витрину:

1. VERSION расходится с manifest;
2. dist не совпадает с текущим release;
3. changelog опаздывает относительно артефактов;
4. public repo перестаёт быть trustworthy source для GitHub release.

## Каноническая команда

```bash
bash scripts/nordicblocks-sync-public-staging.sh
```

Если нужен единый release contour из приватного workspace, каноническая orchestration-команда такая:

```bash
bash scripts/nordicblocks-release-public-contour.sh 0.2.0
```

Она выполняет version sync при переданном аргументе, собирает install/update архивы и затем сразу дублирует их в public staging.

## Что синхронизируется

1. packages/nordicblocks/VERSION;
2. packages/nordicblocks/manifest.ru.ini;
3. packages/nordicblocks/install.php;
4. packages/nordicblocks/install.sql;
5. packages/nordicblocks/CHANGELOG.md;
6. packages/nordicblocks/package/;
7. product-safe scripts;
8. selected public docs;
9. dist/nordicblocks/start/;
10. dist/nordicblocks/updates/<version>/;
11. flat compatibility aliases in dist/ when they exist.

## Правило процесса

Release contour NordicBlocks теперь считается незавершённым, если install/update сборки выполнены, но staged public repo не синхронизирован.