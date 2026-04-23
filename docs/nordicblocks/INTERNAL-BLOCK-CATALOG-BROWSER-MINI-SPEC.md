# NordicBlocks Internal Block Catalog Browser — Mini Spec

Дата: 2026-04-23

## 1. Статус документа

Этот документ фиксирует mini-spec для будущего внутреннего browser-каталога блоков внутри компонента NordicBlocks.

Важно:

1. это не активная реализация;
2. это не backend ТЗ для текущего спринта;
3. это продуктово-архитектурная рамка для будущего MVP;
4. первая версия каталога предполагается только free и только для curated managed blocks.

## 2. Назначение browser-каталога

Catalog browser внутри компонента нужен для одной базовой задачи:

дать пользователю возможность открыть встроенную библиотеку готовых блоков,
выбрать нужную visual section,
установить её локально в свой NordicBlocks-компонент
и дальше редактировать через существующий editor path.

Это не магазин и не маркетплейс.

Это встроенный блок-браузер для curated installable blocks.

## 3. Границы MVP

В первую волну browser-каталог обязан быть минимальным.

В MVP входят:

1. одна внутренняя точка входа внутри NordicBlocks;
2. список категорий или family-секций;
3. grid карточек блоков внутри выбранной категории;
4. превью и краткое описание блока;
5. статус доступности `free`;
6. действие `Установить`;
7. действие `Открыть в редакторе` после установки.

В MVP не входят:

1. платежи;
2. тарифы;
3. удалённое лицензирование;
4. облачная синхронизация;
5. пользовательские отзывы;
6. внешние авторы;
7. marketplace moderation;
8. рекомендательная система.

## 4. Место внутри компонента

Предварительная позиция:

browser-каталог живёт как отдельный внутренний раздел NordicBlocks,
но не ломает существующий путь работы с уже установленными блоками.

То есть у пользователя остаются два действия:

1. работать с уже существующими блоками;
2. открыть каталог и поставить новый curated block.

Каноническое продуктовое имя раздела для MVP:

`Каталог блоков`

## 5. Базовая UX-модель

Канонический сценарий пользователя:

1. открыть NordicBlocks;
2. перейти в каталог;
3. выбрать категорию `Hero`;
4. увидеть 2–3 карточки блоков;
5. открыть preview или demo;
6. нажать `Установить`;
7. получить локально установленный block type или block preset;
8. открыть его уже через design block editor;
9. адаптировать контент и визуал под проект.

## 6. Канонический экран v1

Минимальный browser screen должен состоять из:

1. header раздела;
2. короткого объяснения, что это free catalog;
3. блока категорий;
4. grid карточек выбранной категории;
5. карточки блока;
6. install state для уже установленного элемента.

## 7. Категории v1

Для MVP фиксируется только одна категория:

1. `Hero`.

Причина:

одной категории достаточно, чтобы проверить browse/install/edit loop,
не распыляя платформу на широкий ассортимент.

## 8. Карточка блока v1

Каждая карточка блока v1 должна содержать:

1. название блока;
2. короткий subtitle или product-role;
3. preview image;
4. family/category label;
5. статус `Free`;
6. install state;
7. кнопку `Подробнее` или `Демо`;
8. кнопку `Установить`.

## 9. Состояния карточки

Карточка должна уметь минимум такие состояния:

1. `доступен к установке`;
2. `уже установлен`;
3. `скоро будет` — только если команда сознательно показывает roadmap-slot;
4. `недоступен в этой версии компонента` — на будущее, если позже появится version compatibility.

## 10. Принципы browser-каталога

### 10.1 Curated only

Каталог v1 не является открытым реестром.

В него попадают только curated blocks,
которые команда сама считает production-готовыми для free distribution.

### 10.2 Visual-first

В первой волне каталог должен продавать ценность через preview, а не через технические параметры.

### 10.3 Local-first install

После установки блок живёт локально в компоненте.

### 10.4 Design-block-first target

Если блок подходит под managed visual path,
он должен открываться в design block editor,
а не тащить за собой новый custom inspector.

## 11. Что считать успешным результатом MVP

MVP browser-каталога считается успешным, если:

1. пользователь понимает разницу между каталогом и уже установленными блоками;
2. установка выглядит как простой и безопасный шаг;
3. после установки блок открывается предсказуемо и без нового mental model;
4. 2–3 hero-блока уже дают ощущение библиотеки, а не случайных демо-образцов;
5. команда получает ясный сигнал, стоит ли расширять каталог дальше.

## 12. Связанные документы

1. [docs/nordicblocks/FREE-BLOCK-CATALOG-MVP-FUTURE-PLAN.md](docs/nordicblocks/FREE-BLOCK-CATALOG-MVP-FUTURE-PLAN.md)
2. [docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md](docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md)
3. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md)
4. [docs/nordicblocks/HERO-FAMILY-V1.md](docs/nordicblocks/HERO-FAMILY-V1.md)
5. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md)
6. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md)
7. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-SCREEN-WIREFRAME-V1.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-SCREEN-WIREFRAME-V1.md)
8. [docs/nordicblocks/HERO-WIDE-PANELS-ANCHOR-MANIFEST-V1.md](docs/nordicblocks/HERO-WIDE-PANELS-ANCHOR-MANIFEST-V1.md)
9. [docs/nordicblocks/FIRST-CATALOG-INSTALL-LOOP-ACCEPTANCE-CRITERIA.md](docs/nordicblocks/FIRST-CATALOG-INSTALL-LOOP-ACCEPTANCE-CRITERIA.md)