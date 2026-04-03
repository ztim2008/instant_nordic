# Техспека: data sources, dynamic blocks и режимы участия страниц

## Статусы

- ⚪ Не начато
- 🟡 Запланировано
- 🔵 В работе
- 🧪 На проверке
- 🟢 Готово
- ⛔ Блокер

## Навигация

- Главный трекер: [LANDING-BUILDER-MASTER-PLAN-2026-04-03.md](LANDING-BUILDER-MASTER-PLAN-2026-04-03.md)
- Предыдущий документ: [LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md](LANDING-BUILDER-DATA-MODEL-SPEC-2026-04-03.md)
- Следующий документ: [LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md](LANDING-BUILDER-JSON-CONTRACTS-SPEC-2026-04-03.md)

## 1. Цель документа

Этот документ фиксирует два ключевых принципа Нордик:

1. любой block instance должен уметь работать не только с ручными props, но и с динамическими данными из InstantCMS;
2. страницы сайта должны подключаться к builder не одним способом, а через несколько режимов участия.

## 2. Базовый принцип

### 🟢 Готово: принцип зафиксирован

Нордик не должен быть только редактором статических лендингов.

Он должен уметь:

- подключать данные любого opt-in content type;
- собирать блоки из single item, list, category, profile и route context;
- работать как full page builder, как overlay layer и как частичный zone builder.

## 3. Universal data source model

### 🔵 В работе: модель источников данных зафиксирована

Для block instance нужны не только режимы `manual` и `dynamic`, а более предметная модель источников.

### 3.1. Базовые source types для MVP

1. `manual`
2. `context.item`
3. `context.list`
4. `context.category`
5. `context.profile`
6. `ctype.list`
7. `ctype.item`
8. `ctype.related`
9. `query.collection`
10. `mixed.hybrid`

### 3.2. Практический пример

Пример с карточками объявлений:

- пользователь добавляет блок `cards-grid`;
- в sidebar выбирает источник `ctype.list`;
- выбирает ctype `Объявления`;
- настраивает limit, sorting, category filter и другие правила;
- блок динамически выводит карточки объявлений.

Тот же block instance потом можно переключить на:

- ручной режим;
- связанный список новостей;
- похожие записи;
- query collection.

## 4. Query collections как современный слой

### 🟡 Запланировано

Полезный и современный шаг для Нордик: не ограничивать dynamic blocks только жесткой привязкой к одному ctype.

Нужен query-driven слой:

- reusable collections;
- saved queries;
- фильтры;
- sorting;
- limit;
- шаблон выдачи одного блока.

## 5. Режимы участия страниц сайта

### 🟢 Готово: модель зафиксирована

У страниц сайта должно быть несколько режимов участия в builder.

### 5.1. `full_takeover`

Страница полностью принадлежит Нордик.

### 5.2. `hybrid_overlay`

Системная страница сохраняет нативный контент, но вокруг него доступны hero, before-content, after-content, sidebar и styling tokens.

### 5.3. `zone_injection`

Builder получает право управлять только конкретными разрешенными зонами.

### 5.4. `data_only`

Страница почти не меняется структурно, но конкретные blocks на ней могут использовать builder data contracts и tokenized rendering.

## 6. Что лучше для MVP

### 🟢 Готово: порядок внедрения зафиксирован

Для первой версии разумнее делать так:

1. `full_takeover` для standalone и homepage;
2. `hybrid_overlay` для content item и category;
3. `zone_injection` как controlled mode для сложных страниц;
4. `data_only` оставить как второй этап после стабилизации contracts.

## 7. Что хранить в bindings

### 🔵 В работе

Binding должен уметь хранить не только target page type, но и mode участия страницы.

Минимально нужны поля уровня binding options:

1. `participation_mode`
2. `allowed_zone_keys`
3. `allow_dynamic_blocks`
4. `allow_manual_blocks`
5. `default_data_sources`
6. `allowed_ctypes`

## 8. Что хранить в block data contract

### 🔵 В работе

Block manifest должен уметь описывать:

1. какие source types блок понимает;
2. какие поля mapping обязательны;
3. это single item или collection block;
4. можно ли использовать context source без явного выбора ctype;
5. есть ли fallback на manual.

## 9. Современные идеи, которые стоит заложить сразу

### 🟡 Запланировано

1. `source presets` для быстрых сценариев вроде “Последние объявления” или “Похожие записи”.
2. `block variants`, чтобы один блок карточек менял layout без смены data contract.
3. `empty state rules`, чтобы dynamic block не ломал страницу при пустой выборке.
4. `fallback to manual`, если источник временно недоступен.
5. `slot-based homepage`, где части главной подключаются как zone_injection, а не только как full takeover.

## 10. Итоговый вывод

### 🟢 Готово

Нордик должен проектироваться как builder, который:

- умеет собирать статические лендинги;
- умеет подключать любые opt-in content types как источник данных для blocks;
- умеет забирать всю страницу, только часть страницы или только отдельные зоны;
- умеет постепенно внедряться в живой сайт без тотального takeover на старте.