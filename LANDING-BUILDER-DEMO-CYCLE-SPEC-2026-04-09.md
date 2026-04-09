# LANDING BUILDER DEMO CYCLE SPEC (2026-04-09)

## Зачем нужен demo cycle

После установки компонента пользователь должен видеть не пустой canvas, а готовый пример сайта, который можно сразу редактировать.

Цели:
- показать продуктовый сценарий visual-first за 3-5 минут;
- дать понятный путь изучения: страница -> секция -> блок -> правило применения -> preview/live;
- ускорить отлов багов route/binding/runtime на реальных связках страниц;
- дать основу для коммерческого starter-шаблона.

## Product принципы

1. Demo не должен выглядеть как dev-консоль и технический прототип.
2. Demo должен быть безопасным: idempotent install и предсказуемое удаление.
3. Demo должен проверять ключевую архитектуру: explicit global source + deterministic priority.
4. Demo не должен требовать ручного JSON для первого успешного результата.

## Два режима поставки

### 1) Quick Demo (по умолчанию)

Цель: быстрый onboarding и smoke за 5 минут.

Состав:
- 5-7 страниц;
- простой текстовый контент в блоках;
- минимальный набор bindings с понятными именами;
- 1 явный источник сквозных секций;
- 2 конкурирующих правила с разным priority для наглядного winner-поведения.

### 2) Full Template

Цель: почти готовый продаваемый каркас сайта.

Состав:
- полноценная структура лендинга и внутренних страниц;
- единая дизайн-система (токены, типографика, отступы, контрасты);
- расширенный набор bindings по типовым маршрутам;
- стартовые SEO-поля и шаблонные тексты.

## Канонический набор demo страниц (Quick Demo)

1. demo-homepage
- Назначение: главная витрина.
- Применение: homepage.
- Блоки: hero, преимущества, CTA.

2. demo-site-frame
- Назначение: источник сквозных секций.
- Применение: all_except_homepage.
- Флаги: use_as_global_sections_source = 1.
- Блоки: верхняя сервисная плашка, нижний CTA-футер.

3. demo-content-list
- Назначение: общий список контента.
- Применение: content/index.
- Блоки: заголовок, описание, инфо-блок.

4. demo-content-item
- Назначение: карточка материала.
- Применение: content/item.
- Блоки: заголовок статьи, врезка, CTA.

5. demo-category-board
- Назначение: категория ctype board.
- Применение: overlay content_category (board).
- Блоки: заголовок категории, фильтр-пояснение.

6. demo-user-profile
- Назначение: профиль пользователя.
- Применение: overlay user_profile.
- Блоки: обложка, короткий bio-block.

7. demo-priority-high (техническая страница для QA)
- Назначение: контроль winner по priority.
- Применение: тот же route-context, что у demo-content-list.
- Особенность: priority выше, чем у конкурента.

## Binding matrix (Quick Demo)

Обязательные правила:

1. page.demo_homepage
- page_key: demo-homepage
- priority: 100
- route_params: homepage

2. page.demo_internal_default
- page_key: demo-site-frame
- priority: 40
- route_params: content/index
- adapter_key: internal_content_generic

3. page.demo_content_list_low
- page_key: demo-content-list
- priority: 120
- route_params: content/index

4. page.demo_content_list_high
- page_key: demo-priority-high
- priority: 900
- route_params: content/index
- Назначение: детерминированный winner в конфликте.

5. page.demo_content_item
- page_key: demo-content-item
- priority: 150
- route_params: content/item

6. overlay.demo_board_category
- page_key: demo-category-board
- priority: 300
- route_params: overlay=content_category, ctype=board

7. overlay.demo_user_profile
- page_key: demo-user-profile
- priority: 300
- route_params: overlay=user_profile

## Правила данных и безопасности

1. Все demo-сущности должны иметь префикс demo- или demo. в ключах.
2. Повторный запуск install не должен дублировать страницы/правила.
3. Изменения пользователя в demo-страницах допускаются и не должны ломать runtime.
4. Удаление demo должно удалять только demo-сущности.
5. Опции компонента должны хранить маркер режима demo: off | quick | full.

## UX сценарий после установки

1. В админке есть явный блок Demo Content.
2. Доступны кнопки:
- Установить Quick Demo
- Установить Full Template
- Удалить Demo
3. После установки показывается отчет:
- создано страниц;
- создано/обновлено правил;
- выбранный источник сквозных секций;
- ссылка на стартовую страницу canvas.

## Критерии приемки (DoD)

1. Новый пользователь открывает компонент и за 3-5 минут понимает:
- где страницы;
- где правила применения;
- как работает priority;
- как работает источник сквозных секций.

2. Поведение resolver предсказуемо:
- при конфликте правил побеждает higher priority;
- при равенстве работает deterministic tie-break.

3. Demo install/remove безопасен:
- install идемпотентен;
- remove не затрагивает не-demo данные.

4. Quick smoke проходит на маршрутах:
- /
- /board
- /board/<slug>
- /board/<item>.html
- /users/1

## План реализации

Этап 1. Backend seed service
- сервис сборки demo pages и demo bindings;
- install quick/full;
- remove demo;
- отчет по операциям.

Этап 2. Admin UX
- блок Demo Content в экране страниц;
- кнопки install/remove;
- модальное подтверждение и отчет результата.

Этап 3. Smoke и фиксация
- автоматизируемый quick smoke;
- чеклист в docs/checklists;
- фиксация результатов в docs/WORKLOG.

## Риски

1. Перезапись пользовательских тестовых страниц при неаккуратных ключах.
2. Расхождение live и package mirrors при быстром развитии seed-логики.
3. Сложный full-template может замедлить onboarding, если включать его по умолчанию.

## Rollback

Перед внедрением seed/remove в код:
- создать checkpoint (backup БД + snapshot tag);
- отдельно проверить rollback только demo-операций на тестовом окружении.

## Что не входит в этот spec

1. Полный визуальный арт-дирекшн шаблона (цвета, детальная типографика, иллюстрации).
2. Импорт внешних маркетплейс-шаблонов.
3. Миграция существующих пользовательских страниц в demo-режим.
