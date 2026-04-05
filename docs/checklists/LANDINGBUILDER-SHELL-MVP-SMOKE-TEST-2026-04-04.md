# Shell Builder MVP Smoke Checklist

Короткий ручной smoke-checklist для состояния, когда `Shell Builder` почти готов к переходу на экран `Design System`.

## Что проверить

1. Открыть `Shell Builder` и убедиться, что список variant-ов виден без raw CMS-терминов.
2. Зайти в любой shell variant и сохранить его без изменений. Сохранение должно пройти без ошибки.
3. Переключить `menu placement` между `header_primary`, `header_secondary` и `site_top`. На frontend меню должно переезжать в выбранную shell-зону.
4. Включить и выключить `show_site_top`. Верхняя служебная зона должна появляться и исчезать предсказуемо.
5. Включить и выключить `show_before_content` и `show_after_content`. Соответствующие shell-зоны должны участвовать в runtime только когда они включены.
6. Проверить `body_layout`: без сайдбаров, левый, правый, два сайдбара. Shell должен включать только нужные content-slots.
7. Проверить variant для `category pages`. Builder-надстройка должна появляться вокруг системной категории без поломки native content.
8. Проверить variant для `profile pages`. Верхняя и нижняя builder-зоны профиля должны подключаться без поломки штатного профиля.
9. Проверить `homepage shell mode = shell_hero`. На главной shell hero остается shell-уровнем, а page sections живут ниже.
10. Проверить `homepage shell mode = page_hero`. Первый экран главной должен переходить в hero-зону страницы, а shell hero не должен быть главным первым экраном.
11. Проверить `homepage shell mode = mixed`. Shell hero и page first screen должны уметь жить вместе как два разных слоя.
12. Проверить хотя бы один published и один draft/prototype сценарий. Черновой runtime должен быть виден только администратору.

## Что считаем критической поломкой

1. Shell variant сохраняется, но не влияет на frontend.
2. После переключения variant-ов исчезает основной content_body.
3. Меню не переезжает между shell-зонами при смене `menu placement`.
4. Главная не меняет поведение при смене `homepage shell mode`.
5. Category/profile overlay ломает системную страницу вместо аккуратного встраивания.

## Минимальный вывод перед переходом к Design System

Если все пункты выше проходят без ручных правок шаблонов и без raw JSON-обходов, `Shell Builder MVP` можно считать почти закрытым и переводить следующий основной фокус на экран `Design System`.