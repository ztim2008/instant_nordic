# NordicBlocks Free Block Catalog MVP — Future Plan

Дата: 2026-04-23

## 1. Статус документа

Этот документ фиксирует продуктовую идею на будущее.

Важно:

1. это не старт реализации;
2. это не commit к немедленной разработке;
3. это не SaaS launch plan;
4. это продуктовая запись направления, к которому можно вернуться после стабилизации design-block платформы.

## 2. Исходная идея

Вместо того чтобы сразу строить платный SaaS-каталог блоков, первая правильная промежуточная ступень:

1. сделать небольшой внутренний каталог блоков прямо внутри компонента NordicBlocks;
2. начать с free-модели;
3. использовать каталог как curated browser для installable managed blocks;
4. открыть через него 2–3 hero-блока как первую пилотную линейку.

Итоговая логика:

сначала проверяем, нужен ли пользователю сам формат каталога и установки блоков из библиотеки,
и только потом думаем о подписке, лицензировании и удалённой доставке.

## 3. Почему этот путь сильнее, чем сразу SaaS

Сразу идти в продажу было бы преждевременно.

Причины:

1. ещё не доказан сам пользовательский сценарий выбора блока из каталога внутри компонента;
2. пока важнее проверить UX-модель `категория -> превью -> демо -> установить`, чем monetization;
3. free MVP даст реальное понимание, какие families и block shapes чаще всего нужны;
4. внутренняя каталоговая модель снизит архитектурный риск по сравнению с ранним remote marketplace;
5. pilot на 2–3 hero-блоках даст быстрый сигнал без перегруза платформы.

## 4. Основная продуктовая гипотеза

Гипотеза MVP:

если в компоненте NordicBlocks появится небольшой встроенный каталог качественных бесплатных managed blocks,
то пользователь будет воспринимать компонент не только как редактор,
но и как библиотеку готовых visual sections, которые можно быстро поставить и адаптировать.

Если эта гипотеза подтвердится, дальше можно двигаться в сторону:

1. расширения каталога;
2. family-based collections;
3. обновлений блоков;
4. только потом — опционального hosted catalog или subscription-tier модели.

## 5. Что именно считать MVP

MVP здесь должен быть минимальным и очень дисциплинированным.

В него входят:

1. небольшой browser каталога прямо внутри компонента;
2. категории или family-группы;
3. карточка блока с названием, превью, коротким описанием и демо-состоянием;
4. install flow внутрь текущего компонента;
5. только free блоки;
6. стартовый набор из 2–3 hero-блоков.

## 6. Чего в MVP делать не нужно

Чтобы идея не расползлась, в первую волну сознательно не входят:

1. SaaS delivery;
2. подписка;
3. платные тарифы;
4. удалённая лицензия;
5. маркетплейс с внешними авторами;
6. cloud runtime dependency;
7. десятки block families;
8. сложные integration blocks;
9. автоматический апдейт из облака;
10. полноценный storefront.

## 7. Почему hero family подходит первой

Для пилота hero family подходит лучше всего.

Причины:

1. hero-блоки понятны пользователю визуально и легко продают ценность каталога даже в free-модели;
2. hero family уже логически подготовлена к managed/scaffold-first развитию;
3. эти блоки в основном visual-first и хорошо ложатся на design-block-first направление;
4. для hero-blocks не нужен тяжёлый integration path с InstantCMS данными;
5. 2–3 hero варианта уже достаточно, чтобы проверить browse/install/edit loop.

## 8. Рекомендуемый формат пилота

Для первого пилота фиксируется такой формат:

1. одна категория `Hero`;
2. два или три curated hero-блока;
3. каждый блок открывается и редактируется через design block editor;
4. после установки блок живёт локально в компоненте, а не зависит от внешнего сервиса;
5. пользователь может взять блок как готовую основу и дальше кастомизировать его через существующий editor shell.

## 9. Продуктовые правила для этого направления

### 9.1 Catalog-first, not store-first

В первой волне это каталог выбора и установки,
а не магазин и не тарифная система.

### 9.2 Local install only

Даже если потом появится hosted source,
установленный блок должен жить локально в компоненте.

### 9.3 Managed blocks only

В каталог первой волны должны попадать только managed blocks,
которые работают на предсказуемом contract-first/design-block-first контуре.

### 9.4 Classic integration blocks stay separate

Старый block path остаётся для сложных сценариев:

1. tight InstantCMS data integration;
2. database-heavy logic;
3. permissions/workflow-heavy blocks;
4. server-side business behavior.

## 10. Возможный UX внутри компонента

Внутри NordicBlocks это может выглядеть так:

1. пользователь открывает раздел каталога;
2. видит категории;
3. заходит в `Hero`;
4. видит 2–3 блока с превью и коротким описанием;
5. выбирает блок;
6. устанавливает его в свой набор;
7. открывает его уже в design block editor;
8. адаптирует контент, layout, motion и sequence под свой проект.

## 11. Почему это важно стратегически

Этот шаг может стать мостом между двумя слоями развития NordicBlocks:

1. от просто редактора блоков;
2. к платформе готовых installable visual sections.

Именно этот промежуточный слой позволит понять:

1. нужен ли пользователю встроенный каталог вообще;
2. какие categories/families наиболее ценны;
3. насколько design-block-first модель действительно масштабируется как основной delivery path;
4. стоит ли позже идти в hosted catalog / SaaS модель.

## 12. Предварительный порядок будущих шагов

Когда команда вернётся к этой идее, рекомендуемый порядок такой:

1. спроектировать внутренний catalog browser внутри компонента;
2. сделать только одну family-категорию `Hero`;
3. подготовить 2–3 hero managed blocks;
4. проверить install/edit/live loop;
5. только после этого решать, расширять ли каталог и нужно ли выносить его в hosted service.

## 13. Decision Summary

На текущий момент фиксируется такая позиция:

1. идея SaaS-каталога признаётся сильной, но ранний запуск monetization считается преждевременным;
2. первым шагом должен быть free internal catalog внутри компонента;
3. пилотный scope — 2–3 hero-блока;
4. первым anchor block для pilot install loop выбирается `Hero: Wide Panels`;
5. каноническое имя будущего раздела внутри UI — `Каталог блоков`;
6. это future-plan, а не активная реализация текущего спринта.

## 14. Связанные документы

1. [docs/nordicblocks/NORDICBLOCKS-V2-ROADMAP.md](docs/nordicblocks/NORDICBLOCKS-V2-ROADMAP.md)
2. [docs/nordicblocks/HERO-FAMILY-V1.md](docs/nordicblocks/HERO-FAMILY-V1.md)
3. [docs/nordicblocks/CATALOG-BROWSER-V1.md](docs/nordicblocks/CATALOG-BROWSER-V1.md)
4. [docs/nordicblocks/DESIGN-BLOCK-MODE-V1.md](docs/nordicblocks/DESIGN-BLOCK-MODE-V1.md)
5. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-BROWSER-MINI-SPEC.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-BROWSER-MINI-SPEC.md)
6. [docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md](docs/nordicblocks/HERO-PILOT-SHORTLIST-2026-04-23.md)
7. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md)
8. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md)
9. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md)
10. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-SCREEN-WIREFRAME-V1.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-SCREEN-WIREFRAME-V1.md)
11. [docs/nordicblocks/HERO-WIDE-PANELS-ANCHOR-MANIFEST-V1.md](docs/nordicblocks/HERO-WIDE-PANELS-ANCHOR-MANIFEST-V1.md)
12. [docs/nordicblocks/FIRST-CATALOG-INSTALL-LOOP-ACCEPTANCE-CRITERIA.md](docs/nordicblocks/FIRST-CATALOG-INSTALL-LOOP-ACCEPTANCE-CRITERIA.md)