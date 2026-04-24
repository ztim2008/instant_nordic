# NordicBlocks Public Repository Blueprint

## Цель

Этот документ фиксирует целевую структуру публичного GitHub-репозитория NordicBlocks как продуктового репозитория, а не snapshot рабочего сайта на InstantCMS.

Базовый принцип:

1. В публичный репозиторий попадает только продукт NordicBlocks.
2. Полный сайт, production-конфиг, runtime-данные и серверная среда остаются в приватном проекте.

## Что является продуктом

В продукт NordicBlocks входят:

1. install/update package;
2. runtime-код компонента;
3. backend editor и admin templates компонента;
4. static assets компонента;
5. release scripts, которые собирают install/update архивы;
6. продуктовая документация по установке, обновлению, совместимости и roadmap;
7. smoke/preflight scripts, если они не привязаны к приватному серверу.

## Что не является продуктом

В публичный репозиторий не должны попадать:

1. весь InstantCMS core целиком;
2. production-конфиги и server-specific пути;
3. runtime cache, backups, upload, generated files;
4. данные реального сайта;
5. приватные интеграционные костыли конкретного проекта;
6. любые артефакты, завязанные на живой сервер как единственную среду запуска.

## Целевая структура публичного repo

```text
nordicblocks/
├── .github/
│   └── workflows/
├── docs/
│   ├── install/
│   ├── releases/
│   ├── blocks/
│   └── roadmap/
├── package/
│   ├── static/
│   ├── system/
│   └── templates/
├── scripts/
│   ├── build-nordicblocks-package.sh
│   ├── build-nordicblocks-update-package.sh
│   ├── nordicblocks-package-preflight.sh
│   ├── nordicblocks-version-sync.sh
│   ├── nordicblocks-scaffold-block.php
│   └── nordicblocks-validate-block.php
├── tests/
│   └── smoke/
├── dist/
│   └── nordicblocks/
│       ├── start/
│       └── updates/
├── manifest.ru.ini
├── install.php
├── install.sql
├── CHANGELOG.md
├── README.md
└── .gitignore
```

## Принципы по верхнему уровню

1. Верхний уровень публичного repo должен быть продуктовым и коротким.
2. Нельзя тащить туда весь текущий monorepo дерева рабочего сайта.
3. Все product-critical файлы должны быть доступны без знания структуры production-проекта.

## Обязательные файлы верхнего уровня

1. README.md
2. CHANGELOG.md
3. manifest.ru.ini
4. install.php
5. install.sql
6. .gitignore

## Обязательные папки

1. package/
2. scripts/
3. docs/
4. dist/

## Позиция по InstantCMS

InstantCMS является целевой платформой NordicBlocks, но не должен быть основным содержимым публичного repo NordicBlocks.

Правильная модель:

1. NordicBlocks = продукт.
2. InstantCMS = платформа установки.
3. Конкретный сайт = отдельный private project.

## Позиция по истории разработки

Если feature рождается в приватном рабочем проекте:

1. сначала она стабилизируется в боевом/рабочем контуре;
2. потом переносится в продуктовую структуру NordicBlocks;
3. после этого попадает в публичный changelog и release.

Нельзя использовать публичный GitHub repo как dump всего рабочего сервера.

## Позиция по документации

В публичный repo попадает только документация, которая помогает:

1. установить продукт;
2. обновить продукт;
3. понять архитектуру продукта;
4. развивать продукт как reusable систему.

Внутренние day-plan, handoff, incident notes и live-server-specific worklog лучше не считать частью публичной витрины по умолчанию.