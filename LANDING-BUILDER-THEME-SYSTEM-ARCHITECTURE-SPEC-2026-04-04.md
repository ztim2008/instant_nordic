# Архитектура: Нордик как theme system для InstantCMS

## Зачем этот документ

Этот документ фиксирует архитектурный поворот проекта после анализа подхода `inthemer` и собственных требований Нордик.

Главная мысль:

- Нордик не должен остаться только overlay-конструктором поверх `modern`.
- Нордик должен быть полноценной системой темы и макетов для InstantCMS.

Итоговая модель продукта:

1. компонент `landingbuilder`;
2. frontend template `nordic`;
3. встроенная дизайн-система;
4. режимы полного управления страницей и постепенного внедрения в существующий сайт.

Отдельный продуктовый UX visual builder и связь с global design system подробно зафиксированы в [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md).

## 1. Что считаем целевой моделью

Нордик это не просто page builder.

Нордик это visual theme and layout system для InstantCMS.

Это значит:

1. пользователь может выбрать `nordic` как шаблон сайта в системных настройках;
2. пользователь управляет макетами и секциями через backend компонента `landingbuilder`;
3. пользователь меняет не только содержимое секций, но и глобальный визуальный язык сайта;
4. система умеет работать как в полном режиме своей темы, так и в режиме аккуратного внедрения по зонам.

## 2. Режимы продукта

### 2.1. Полный режим

Сайт работает на шаблоне `nordic`.

В этом режиме Нордик управляет:

1. общим shell сайта;
2. header и footer;
3. контейнером и сеткой;
4. глобальными цветами и типографикой;
5. макетами страниц;
6. зонами для системного контента и стандартных widgets.

Это основной целевой режим продукта.

### 2.2. Режим внедрения

Сайт временно остается на существующем шаблоне, а `landingbuilder` встраивает свои зоны в отдельные страницы.

Этот режим нужен как безопасная миграция:

1. для живых проектов;
2. для постепенного внедрения;
3. для проверки adapter logic без полного переключения темы.

Этот режим важен, но не считается основной конечной архитектурой.

## 3. Из чего должен состоять Нордик

### 3.1. Компонент `landingbuilder`

Компонент отвечает за:

1. backend редактор;
2. страницы и их версии;
3. adapters и bindings;
4. runtime resolution;
5. packs и registry;
6. глобальные theme/layout настройки;
7. экспорт, импорт и update discipline.

### 3.2. Шаблон `nordic`

Шаблон отвечает за:

1. frontend shell сайта;
2. slots и зоны страницы;
3. вывод header, footer, menus, wrappers;
4. подключение CSS tokens и theme assets;
5. аккуратную вставку системного контента InstantCMS в управляемые макеты.

### 3.3. Дизайн-система

Отдельный слой внутри продукта должен описывать:

1. цвета;
2. шрифты;
3. размеры контейнера;
4. вертикальный ритм;
5. сетку;
6. кнопки;
7. карточки;
8. формы;
9. меню;
10. базовые states и interactive rules.

Это не частная настройка страницы, а общий визуальный фундамент сайта.

## 4. Что должно быть настраиваемо глобально

Нужен отдельный уровень theme settings, а не только page canvas.

Минимально:

1. ширина сайта;
2. ширина контентного контейнера;
3. палитра бренда;
4. фон сайта и фон секций;
5. шрифтовая пара;
6. размеры заголовков и текста;
7. кнопки и их варианты;
8. отступы по scale;
9. стили карточек;
10. header behavior;
11. footer layout;
12. мобильные breakpoints.

### 4.1. Продуктовый принцип global design system

Пользователь должен редактировать не CSS-классы, а глобальные tokens и presets.

Визуальный конструктор страницы должен брать оттуда:

1. brand colors;
2. typography;
3. section spacing scale;
4. container presets;
5. button/card/form variants;
6. header/footer variants.

## 5. Что должно быть настраиваемо по страницам

Отдельно от глобальной дизайн-системы страница должна уметь описывать:

1. свой layout;
2. свои секции;
3. placement системного body content;
4. placement widgets;
5. page-level token overrides;
6. режим участия страницы: full takeover, hybrid overlay, zone injection.

При этом page builder должен оставаться визуальным section editor, а не техническим shell/grid editor.

## 6. Как маппить это на InstantCMS

Правильная взрослая схема такая:

1. шаблон сайта в настройках InstantCMS: `nordic`;
2. backend-конструктор в админке: компонент `landingbuilder`;
3. системный контент InstantCMS не исчезает, а вставляется в layout через специальные slots;
4. стандартные widgets остаются совместимыми и могут жить внутри макетов Нордик;
5. `modern` используется как reference и временный migration path, а не как единственный shell продукта.

## 7. Какие slots нужны шаблону `nordic`

Минимальный набор для первой версии:

1. `site_top`;
2. `header_primary`;
3. `header_secondary`;
4. `hero`;
5. `before_content`;
6. `content_body`;
7. `content_sidebar_left`;
8. `content_sidebar_right`;
9. `after_content`;
10. `footer_primary`;
11. `footer_secondary`.

Отдельно нужен slot для системного body content, чтобы страница контента или категория могли жить внутри layout Нордик, а не вне его.

## 8. Что это меняет в текущем курсе работ

Это не отменяет уже сделанное.

Сохраняем:

1. SQL-модель страниц и версий;
2. canvas editor;
3. runtime page resolution;
4. adapters и participation modes;
5. package mirror discipline.

Меняем фокус:

1. overlay-путь больше не считается единственной основной моделью;
2. приоритет у отдельного шаблона `nordic`;
3. отдельно выделяется слой theme system settings;
4. page contracts должны дальше расширяться под shell slots и global tokens.

Текущее уточнение runtime-слоя:

1. page-level theme presets должны жить не только в editor state, но и попадать в runtime через единый helper context;
2. standalone preview и overlay runtime должны использовать один и тот же mapping preset -> CSS variables -> section presentation classes;
3. канонический catalog preset-ов должен определяться на стороне модели, а не дублироваться вручную в canvas и runtime.

## 9. Практический порядок реализации

Следующий порядок считаем правильным:

1. завершить минимально рабочий overlay regression, чтобы не оставить сломанный промежуточный режим;
2. начать frontend template skeleton `nordic`;
3. описать global theme tokens и contract для theme settings;
4. сделать первый shell `nordic` с управляемыми слотами;
5. вставить в shell системный контент InstantCMS через `content_body` slot;
6. затем перевести builder pages из preview/overlay в полноценные макеты внутри `nordic`.

## 10. Каноничные документы после очистки

После наведения порядка каноничными считаются:

1. [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
2. [LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md](LANDING-BUILDER-THEME-SYSTEM-ARCHITECTURE-SPEC-2026-04-04.md)
3. [LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md](LANDING-BUILDER-VISUAL-BUILDER-BLUEPRINT-2026-04-04.md)
4. [LANDING-BUILDER-ROADMAP-2026-04-03.md](LANDING-BUILDER-ROADMAP-2026-04-03.md)
5. [LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md](LANDING-BUILDER-PACKAGING-AND-UPDATES-SPEC-2026-04-03.md)
6. [LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md)
7. [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)
8. [LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-SOURCES-AND-PAGE-MODES-SPEC-2026-04-03.md)
9. [LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md](LANDING-BUILDER-PAGE-ADAPTERS-SPEC-2026-04-03.md)
10. [LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md](LANDING-BUILDER-CANVAS-EDITOR-SPEC-2026-04-03.md)
11. [LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md](LANDING-BUILDER-BACKEND-SETTINGS-SPEC-2026-04-03.md)
12. [LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md](LANDING-BUILDER-LIFECYCLE-SPEC-2026-04-03.md)

Промежуточные шпаргалки и экспортные навигационные файлы переводятся в архив.