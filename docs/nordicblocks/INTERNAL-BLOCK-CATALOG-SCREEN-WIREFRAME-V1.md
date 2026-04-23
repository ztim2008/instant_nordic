# NordicBlocks Каталог блоков — Screen Wireframe V1

Дата: 2026-04-23

## 1. Статус документа

Этот документ фиксирует screen-level mini-wireframe для будущего раздела Каталог блоков.

Важно:

1. это не visual design;
2. это не implementation layout;
3. это wireframe уровня продукта и информационной архитектуры.

## 2. Цель wireframe

Этот wireframe нужен, чтобы заранее понять:

1. какой один экран нужен для MVP;
2. как пользователь понимает, где он находится;
3. как на одном экране уживаются category switching, карточки и install state;
4. где проходит граница между browse и install.

## 3. Канонический экран v1

Для MVP фиксируется один основной экран раздела:

`Каталог блоков`

Этот экран должен закрывать весь первый browse/install сценарий без обязательного второго уровня сложности.

## 4. Каркас экрана

Экран v1 состоит из пяти основных зон:

1. header раздела;
2. short intro;
3. category rail;
4. main card grid;
5. install state / next action layer.

## 5. Wireframe в линейном виде

Ниже канонический screen outline без привязки к конкретному CSS:

1. Верхняя зона:
   `Каталог блоков`
   краткая строка о том, что это curated free library готовых секций.
2. Зона категорий:
   горизонтальный или компактный список family/category tabs.
   В MVP активна одна категория: `Hero`.
3. Главная зона контента:
   grid из 2–3 карточек hero-блоков.
4. Каждая карточка:
   preview image;
   title;
   subtitle;
   short summary;
   badges;
   primary CTA;
   secondary CTA.
5. Нижний уровень действия:
   после install карточка не исчезает, а меняет state на `Установлен` и показывает следующий шаг `Открыть в редакторе`.

## 6. Header area

Header должен отвечать на три вопроса:

1. что это за раздел;
2. что тут можно сделать;
3. почему пользователь вообще должен остаться на экране.

Минимальный состав header:

1. title: `Каталог блоков`;
2. supporting line: curated free library готовых блоков для локальной установки;
3. без store-like copy и без обещаний marketplace.

## 7. Category rail

Даже если в MVP пока одна категория,
category rail лучше закладывать сразу,
чтобы экран с самого начала ощущался как масштабируемый каталог.

Для v1:

1. активная категория `Hero`;
2. остальные families не обязаны показываться;
3. если показываются будущие категории, то только в мягком disabled/soon state.

## 8. Card grid

Grid должен быть главным визуальным слоем экрана.

Для MVP важно:

1. не перегрузить экран лишними фильтрами;
2. дать 2–3 сильные карточки вместо длинного списка;
3. сделать preview главной точкой выбора.

## 9. Card anatomy

Каждая карточка v1 должна читаться сверху вниз так:

1. preview;
2. title;
3. subtitle;
4. summary;
5. badges;
6. CTA-row.

Предварительный набор badges:

1. family;
2. category;
3. availability.

CTA-row:

1. primary action: `Установить` или `Открыть в редакторе`;
2. secondary action: `Подробнее` или `Демо`.

## 10. Install states на экране

Card-level state обязателен уже в wireframe.

Карточка должна уметь показывать:

1. `Не установлен`;
2. `Устанавливается`;
3. `Установлен`;
4. `Ошибка установки`.

Это важно, потому что именно карточка является местом принятия решения,
а не отдельный system dialog.

## 11. Первый экранный сценарий

Пользовательский сценарий на этом wireframe:

1. пользователь открывает Каталог блоков;
2. видит категорию `Hero`;
3. сравнивает 2–3 карточки;
4. выбирает `Hero: Wide Panels` как наиболее понятный вариант;
5. нажимает `Установить`;
6. видит card state `Установлен`;
7. нажимает `Открыть в редакторе`.

## 12. Что сознательно не включено в wireframe v1

В этот экран не надо включать:

1. pricing block;
2. author pages;
3. ratings/reviews;
4. advanced filters;
5. remote account UX;
6. marketplace storefront semantics.

## 13. Decision summary

На текущий момент фиксируется:

1. MVP держится на одном основном browse/install экране;
2. экран строится вокруг card grid, а не вокруг сложной фильтрации;
3. install state обязан быть виден прямо на карточке;
4. первая каноническая browse-category — `Hero`.

## 14. Связанные документы

1. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-BROWSER-MINI-SPEC.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-BROWSER-MINI-SPEC.md)
2. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md)
3. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md)
4. [docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md](docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md)
5. [docs/nordicblocks/HERO-WIDE-PANELS-ANCHOR-MANIFEST-V1.md](docs/nordicblocks/HERO-WIDE-PANELS-ANCHOR-MANIFEST-V1.md)
6. [docs/nordicblocks/FIRST-CATALOG-INSTALL-LOOP-ACCEPTANCE-CRITERIA.md](docs/nordicblocks/FIRST-CATALOG-INSTALL-LOOP-ACCEPTANCE-CRITERIA.md)
