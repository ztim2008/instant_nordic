# NordicBlocks Hero Pilot Shortlist

Дата: 2026-04-23

## 1. Статус документа

Этот документ фиксирует shortlist из трёх hero-блоков для будущего free catalog pilot.

Важно:

1. это не команда к немедленному scaffold apply;
2. это не implementation backlog текущего дня;
3. это curated product shortlist для пилота внутреннего каталога.

## 2. Принцип отбора

Для первого pilot-ассортимента hero-блоки должны быть:

1. визуально различимыми;
2. полезными в реальных лендингах;
3. совместимыми с design-block-first развитием;
4. достаточно разными по композиции, чтобы каталог ощущался живым уже на 2–3 примерах;
5. не требующими тяжёлой data/integration логики.

## 3. Shortlist v1

### 3.1 Hero A — Editorial Split

Рабочее название:

`Hero: Editorial Split`

Роль:

1. сильный заголовочный hero для брендовых, editorial и magazine-like landing pages;
2. подходит для проектов, где важны типографика, баланс текста и image area;
3. должен показывать, что NordicBlocks умеет не только generic hero, но и характерный visual-first layout.

Почему брать в pilot:

1. хорошо показывает дизайнерскую силу платформы;
2. универсален для brand, studio, content, portfolio и product intro;
3. близок к user-preferred editorial direction.

### 3.2 Hero B — Wide Panels / CTA Hero

Рабочее название:

`Hero: Wide Panels`

Роль:

1. более прямой, конверсионный hero с акцентом на CTA;
2. подходит для small business, services, startup и offer-first страниц;
3. должен быть проще и шире по применению, чем editorial variant.

Почему брать в pilot:

1. закрывает более массовый сценарий использования;
2. легче объясняется пользователю каталога;
3. создаёт хороший контраст с более характерным editorial hero.

### 3.3 Hero C — Media / Showcase Hero

Рабочее название:

`Hero: Media Showcase`

Роль:

1. hero с акцентом на визуальный media-block, mockup или product shot;
2. подходит для app, product, studio, launch и portfolio showcase сценариев;
3. должен демонстрировать, что family может быть не только тексто-центричной.

Почему брать в pilot:

1. добавляет третий явно отличимый visual archetype;
2. показывает силу media/layout composition;
3. позволяет проверить install demand на более образный и product-like hero.

## 4. Почему именно три блока

Три блока — это хороший pilot-balance.

Причины:

1. уже создаётся ощущение выбора;
2. при этом не размывается качество;
3. команда не уходит в масштабирование ассортимента до проверки базового install loop;
4. можно проверить три разных archetype-сценария: editorial, CTA-first и media-first.

## 5. Что эти три блока должны доказать

Shortlist нужен не просто ради ассортимента.

Он должен помочь проверить:

1. что пользователь вообще открывает каталог ради hero-блоков;
2. какие visual archetypes чаще выбирают;
3. достаточно ли design block editor для post-install кастомизации;
4. нужно ли после pilot расширять именно hero family или переходить к следующей категории.

## 6. Жёсткие ограничения pilot-shortlist

Для первой волны не стоит включать:

1. hero с тяжёлой интеграцией данных;
2. сложные animated storytelling layouts с уникальным runtime;
3. slider-like hero как часть этого pilot;
4. блоки, которые требуют отдельного private inspector.

## 7. Предварительный порядок запуска shortlist

Когда команда вернётся к реализации, порядок лучше такой:

1. сначала выбрать один anchor hero для самого первого install loop;
2. затем довести второй hero как более массовый вариант;
3. третьим добавить media/showcase variant;
4. после этого уже смотреть, нужен ли четвёртый hero или пора открывать следующую family-категорию.

## 8. Решение по первому anchor hero

Для первого pilot anchor фиксируется:

`Hero: Wide Panels`

Почему именно он:

1. он проще всего объясняется пользователю как готовый installable hero;
2. у него самый низкий риск для первого browse/install/edit loop;
3. он шире по применению для малого бизнеса, сервисов и offer-first лендингов;
4. он лучше подходит на роль первого массового шаблона, чем более характерный editorial hero;
5. в family уже есть близкий scaffold-managed вектор через `hero_panels_wide`, что делает решение более приземлённым.

## 9. Роль остальных двух hero после выбора anchor

После фиксации первого anchor порядок смысла такой:

1. `Hero: Wide Panels` — базовый первый install candidate;
2. `Hero: Editorial Split` — второй wave block, который показывает характер и дизайнерскую глубину;
3. `Hero: Media Showcase` — третий block для расширения visual диапазона.

## 10. Связанные документы

1. [docs/nordicblocks/FREE-BLOCK-CATALOG-MVP-FUTURE-PLAN.md](docs/nordicblocks/FREE-BLOCK-CATALOG-MVP-FUTURE-PLAN.md)
2. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-BROWSER-MINI-SPEC.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-BROWSER-MINI-SPEC.md)
3. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-INSTALL-FLOW.md)
4. [docs/nordicblocks/HERO-FAMILY-V1.md](docs/nordicblocks/HERO-FAMILY-V1.md)
5. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-CARD-MANIFEST-FIELDS.md)
6. [docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md](docs/nordicblocks/INTERNAL-BLOCK-CATALOG-UI-SECTION-DECISION-2026-04-23.md)