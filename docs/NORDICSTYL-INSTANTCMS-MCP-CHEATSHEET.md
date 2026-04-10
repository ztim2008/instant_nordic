# NordicStyl + InstantCMS MCP: практическая шпаргалка

## Зачем нам это

Папка `instantcms-mcp-main` полезна не как готовая часть продукта, а как локальный справочник по структуре InstantCMS:

- где лежат контроллеры и экшены
- как устроены шаблонные override-пути
- какие системные виджеты есть в ядре
- какие поля и ключи используются в layout-схеме
- какие tool-ы уже описывают типовые InstantCMS-паттерны

Главная мысль:
- использовать как шпаргалку для навигации и проектирования
- не переносить его код в продукт без разбора

## Что реально полезно уже сейчас

### 1. Карта контроллеров и экшенов

Файлы:
- `instantcms-mcp-main/src/data/controllers-map.ts`
- `instantcms-mcp-main/src/tools/controllers-tool.ts`

Что даёт:
- быстрый список frontend/backend контроллеров
- список экшенов, className и путей к файлам
- понимание, есть ли у контроллера backend-папка и модель

Где применять у нас:
- искать правильные точки встраивания для builder-а внутри `nordicstyl`
- быстро сверять naming и структуру новых action-файлов
- не блуждать по `system/controllers/*` вручную, когда нужно понять, где уже есть похожий системный сценарий

Практический вывод:
- для новых экранов и внутренних flow сначала сверяться с картой контроллеров, потом уже лезть в код конкретного action

### 2. Карта системных виджетов

Файлы:
- `instantcms-mcp-main/src/data/widgets-map.ts`
- `instantcms-mcp-main/src/tools/source-tool.ts`

Что даёт:
- список системных виджетов ядра
- controller виджета
- путь к widget.php
- наличие `options.form.php`

Где применять у нас:
- собрать стартовую библиотеку системных виджетов для canvas
- понять, какие виджеты можно адаптировать быстрее всего
- находить формы опций для будущего bridge между builder и системным widget options UI

Практический вывод:
- helper можно использовать как инвентарь виджетов для builder-library, но не как готовый runtime-источник отображения

### 3. Карта маршрутов

Файлы:
- `instantcms-mcp-main/src/data/routes-map.ts`
- `instantcms-mcp-main/src/tools/source-tool.ts`

Что даёт:
- список route-patterns системных контроллеров
- привязку route → action
- понимание параметров action

Где применять у нас:
- для route-aware builder target pages
- для понимания, какие страницы и action-и стоит поддержать в page switcher
- для будущего UI выбора целевой страницы/режима редактирования

Практический вывод:
- это не источник текущих widget bindings, но полезная карта для навигации по типам страниц и экранов

### 4. Layout tool как схема полей, а не как генератор вслепую

Файл:
- `instantcms-mcp-main/src/tools/layout-tool.ts`

Что даёт:
- явное описание структуры row/col схемы
- список ключей layout options: `container`, `container_tag`, `container_class`, `no_gutters`, `nested_position`
- список ключей колонок: `col`, `col_md`, `col_lg`, `col_xl`, `col_class`, `order`, `cut_before`, `wrapper`, `type`
- понимание, как helper мыслит nested rows через `parent_col`

Где применять у нас:
- при сборке save/load формата для builder-а
- при будущей записи изменений обратно в layout rows/cols
- как словарь допустимых layout-полей, чтобы не выдумывать свои названия

Практический вывод:
- использовать как контракт-подсказку по структуре layout, а не как готовый production-генератор схемы

### 5. Template overrides как карта правильных путей

Файлы:
- `instantcms-mcp-main/src/tools/template-overrides-tool.ts`
- `instantcms-mcp-main/src/tools/layout-override-tool.ts`

Что даёт:
- список типовых override-файлов под `templates/{theme}/controllers/{controller}/...`
- примеры путей для index/view/item/profile и других экранов
- подтверждение структуры override-слоя в шаблонах InstantCMS

Где применять у нас:
- при развитии `templates/nordics` как тонкого child-template
- когда нужно понять, какой tpl override допустим и где он должен лежать
- при проектировании builder-aware view-слоя, не ломая системную структуру шаблонов

Практический вывод:
- полезно как карта override-точек, особенно чтобы не плодить случайные пути в шаблоне

### 6. Server catalog как список готовых направлений исследования

Файл:
- `instantcms-mcp-main/src/server.ts`

Что даёт:
- полный список MCP tool-ов
- быстрый обзор, какие домены уже покрыты: hooks, layouts, widgets, controllers, templates, permissions, migrations

Где применять у нас:
- как список направлений, по которым можно быстро проводить внутреннее исследование движка
- как напоминание, что helper уже разбит по темам, и не нужно каждый раз искать всё вручную

Практический вывод:
- это скорее карта возможностей helper-а, чем код для прямого переноса

## Что полезно частично, но не надо брать в лоб

### Template tool

Файл:
- `instantcms-mcp-main/src/tools/template-tool.ts`

Почему осторожно:
- делает шаблон с нуля
- ориентирован на standalone template scaffold
- не учитывает наш вектор: тонкий child-template над `modern`

Что можно взять:
- общую структуру template-артефактов
- идеи по naming и составу файлов

Что не брать:
- готовую генерацию main.tpl/layout как основу `nordics`

### Widget tool

Файл:
- `instantcms-mcp-main/src/tools/widget-tool.ts`

Почему осторожно:
- это generic scaffold
- в шаблонах есть inline-style подходы, не совпадающие с нашим курсом

Что можно взять:
- структуру widget class/options/template
- быстрый каркас для библиотечных секций и адаптерных виджетов

Что не брать:
- прямую генерацию как production-код без ручной адаптации

## Что это даёт именно нашей реализации

### Для builder-а

- можно быстро собрать dev-список системных виджетов и их опций
- можно держать единый словарь layout-полей и не изобретать свой формат
- можно понять, какие tpl override-точки нужны для child-template

### Для picker и style-layer

- helper помогает быстро находить контроллеры, route-паттерны и template-tochki для разных экранов сайта
- это упрощает цель “редактировать прямо на сайте”, потому что легче понимать, какой экран и какой tpl реально открыт

### Для дальнейшего inspector-а

- можно разложить inspector не абстрактно, а на реальные сущности InstantCMS: widget, layout row, layout col, template override, controller action, route target

## Конкретные next steps

1. На основе `widgets-map.ts` собрать первую человеко-понятную библиотеку системных виджетов для builder sidebar.
2. На основе `layout-tool.ts` описать внутренний контракт сохранения rows/cols для будущего save action.
3. На основе `template-overrides-tool.ts` зафиксировать допустимые override-точки для `templates/nordics`.
4. На основе `controllers-map.ts` и `routes-map.ts` продумать page switcher для builder-а: главная, content view, category, профиль и другие важные типы страниц.

## Короткий вывод

`instantcms-mcp-main` полезен как инженерная карта движка.

Самые ценные для нас сейчас опоры:
- `src/data/controllers-map.ts`
- `src/data/widgets-map.ts`
- `src/data/routes-map.ts`
- `src/tools/layout-tool.ts`
- `src/tools/template-overrides-tool.ts`

Это не замена чтению кода ядра, а ускоритель поиска правильной точки в InstantCMS.