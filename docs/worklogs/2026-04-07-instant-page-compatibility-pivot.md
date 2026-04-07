# 2026-04-07 — Pivot: совместимость Nordic Builder с страницами InstantCMS

## Что подтвердилось (проблема реальная)

Сейчас архитектура действительно расходится с моделью движка InstantCMS.

Критичные признаки:

1. Новый экран `nordicbuilder/pages` создает страницы через bridge-модель `landingbuilder`, а не как нативные страницы Instant.
2. Внутренний source-of-truth страницы хранится в таблицах `landingbuilder_*`.
3. В `landingbuilder` по-прежнему присутствуют legacy-режимы `full_takeover`, `hybrid_overlay`, `zone_injection`.
4. `nordicbuilder/view` делегирует рендер в `landingbuilder/view`.
5. В шаблоне `templates/nordic/main.tpl.php` есть большой takeover-контур, но интеграция выключена флагом `$lb_front_integration_enabled = false`.

Практический эффект:

- пользователь редактирует "страницу конструктора", но это не равно нативной странице Instant;
- ожидание "изменил страницу сайта" и фактическое "изменил внутренний документ" расходятся;
- preview/live parity теряется на уровне ожиданий, даже если рендер технически работает.

## Продуктовый вывод

Тезис пользователя корректный: компонент в текущем виде не должен считаться fully Instant-native page builder.

## Новый целевой принцип (Instant-native)

Builder не владеет страницей сайта.
Builder владеет только контентом зоны `content_body`.

Канон:

1. Каноническая страница — это обычная страница/route InstantCMS.
2. Builder хранит только документ рендера и мета-данные, привязанные к route/page-key.
3. Live вывод — один виджет `nordicbuilder_render` в `content_body`.
4. Никакого takeover/hybrid runtime.
5. Никаких отдельный "виртуальных" страниц как продуктовой сущности для пользователя.

## План миграции (со статусами)

Статусы:
- ⚪ не начато
- 🟡 в работе
- 🟢 готово

### Фаза A. Отключить архитектурный конфликт

1. ⚪ Заморозить создание новых страниц через `landingbuilder` из UI `nordicbuilder/pages`.
2. ⚪ Убрать из UX выбор `full_takeover/hybrid/zone_injection`.
3. ⚪ Зафиксировать в коде и документации единый режим runtime: только `content_body`.

### Фаза B. Перевести вход в редактор на Instant-страницы

1. ⚪ Ввести "источник редактирования": route/URI или ID нативной страницы Instant.
2. ⚪ На входе в canvas открывать документ по route-key, а не по сущности `landingbuilder_page`.
3. ⚪ Если документа нет — создавать только draft PageDocument (без создания отдельной страницы компонента).

### Фаза C. Привязка и публикация

1. ⚪ Binding хранить как правило соответствия route -> page_document_key.
2. ⚪ Publish сохраняет SSR render в `nordicbuilder_page_renders` по route-key.
3. ⚪ Live-виджет читает только опубликованный render по текущему URI.

### Фаза D. Вычистить legacy bridge

1. ⚪ Убрать из пользовательского UI/маршрутов takeover-контур.
2. ⚪ Перевести `landingbuilder` в internal migration-only слой.
3. ⚪ Оставить в пользовательской поставке один installable путь `nordicbuilder`.

### Фаза E. Проверка совместимости

1. ⚪ Smoke на 3 типах страниц Instant: homepage, content list, content item.
2. ⚪ Проверка parity: canvas preview == published live.
3. ⚪ Проверка fallback: если published render отсутствует, страница остается штатной страницей Instant.

## Что важно не делать

1. Не включать обратно takeover как быстрый путь.
2. Не смешивать page CRUD конструктора с page CRUD Instant.
3. Не тащить глобальные JS/CSS пакеты в runtime "на всякий случай".

## Критерий готовности pivot

Пользователь редактирует реальную страницу сайта, а не внутреннюю сущность компонента.

Проверка простая:

"Открываю страницу Instant -> вхожу в canvas -> меняю блок -> publish -> на этой же странице сайта вижу ровно это же".
