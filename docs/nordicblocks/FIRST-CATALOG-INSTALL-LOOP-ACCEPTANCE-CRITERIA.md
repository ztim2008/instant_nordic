# NordicBlocks First Catalog Install Loop — Acceptance Criteria

Дата: 2026-04-23

## 1. Статус документа

Этот документ фиксирует acceptance criteria для первого pilot install loop внутри будущего раздела Каталог блоков.

Важно:

1. это не тестовый автосьют;
2. это не implementation checklist для одного коммита;
3. это product-level критерии готовности первого browse/install/edit сценария.

## 2. Scope acceptance

Acceptance criteria относятся только к первому узкому сценарию:

1. пользователь открывает Каталог блоков;
2. видит family `Hero`;
3. выбирает anchor block `Hero: Wide Panels`;
4. устанавливает его локально;
5. открывает его в editor.

## 3. Что считается успешным loop

Loop считается успешным, если пользователь проходит путь:

1. browse;
2. выбор;
3. install;
4. open editor;
5. базовое ощущение владения блоком после установки.

## 4. Product acceptance criteria

### 4.1 Entry point clarity

1. пользователь понимает, что раздел `Каталог блоков` содержит готовые installable blocks;
2. раздел не воспринимается как магазин, тарифная зона или внешний маркетплейс.

### 4.2 Card clarity

1. карточка `Hero: Wide Panels` объясняет назначение блока без чтения внутренней документации;
2. preview достаточно информативен для первого выбора;
3. title, subtitle и summary не конфликтуют между собой по смыслу.

### 4.3 Install clarity

1. кнопка `Установить` воспринимается как локальная установка, а не как просмотр или избранное;
2. после действия пользователю ясно, что блок теперь доступен внутри компонента;
3. install state отражается на карточке явно и без двусмысленности.

### 4.4 Editor continuity

1. после установки есть прямое действие `Открыть в редакторе`;
2. пользователь не попадает в новый или неожиданный editor flow;
3. edit experience продолжает знакомую design-block модель.

## 5. Functional acceptance criteria

1. в каталоге видна категория `Hero`;
2. в grid присутствует карточка `Hero: Wide Panels`;
3. карточка имеет корректный initial state `Не установлен`;
4. после install карточка переходит в state `Установлен`;
5. после install доступно действие `Открыть в редакторе`;
6. установленный блок появляется в локально доступном контуре компонента;
7. открытие блока ведёт в managed design-block editor path.

## 6. Non-functional acceptance criteria

1. сценарий не требует внешней лицензии или cloud account;
2. runtime опубликованного блока не зависит от каталога после установки;
3. пользователь не теряет установленный блок после выхода из раздела;
4. терминология экрана остаётся consistent и не скатывается в marketplace language.

## 7. UX rejection criteria

Первый loop нельзя считать готовым, если происходит хотя бы одно из следующего:

1. пользователь не понимает, установлен блок или нет;
2. install выглядит как техническая операция администратора;
3. после install непонятно, где искать блок дальше;
4. editor path после install ощущается как новый продукт, а не продолжение NordicBlocks;
5. карточка anchor block слишком абстрактна и не помогает принять решение.

## 8. Manual pilot validation

Когда команда вернётся к реализации, minimum manual validation должна проверить:

1. понятность entry-point названия `Каталог блоков`;
2. понятность карточки `Hero: Wide Panels`;
3. понятность install state transitions;
4. предсказуемость перехода в editor;
5. ощущение, что блок действительно стал локальной частью системы.

## 9. Decision summary

На текущий момент фиксируется:

1. первый acceptance loop строится вокруг одного anchor block;
2. критичнее всего не техническая ширина, а ясность browse/install/edit semantics;
3. MVP считается удачным только если install не ломает знакомую модель владения блоком.

## 10. Связанные документы

1. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md)
2. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-SCREEN-WIREFRAME-V1.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-SCREEN-WIREFRAME-V1.md)
3. [docs/nordicblocks/HERO-WIDE-PANELS-ANCHOR-MANIFEST-V1.md](docs/nordicblocks/HERO-WIDE-PANELS-ANCHOR-MANIFEST-V1.md)
4. [docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md](docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md)
5. [docs/nordicblocks/FREE-BLOCK-CATALOG-MVP-FUTURE-PLAN.md](docs/nordicblocks/FREE-BLOCK-CATALOG-MVP-FUTURE-PLAN.md)
