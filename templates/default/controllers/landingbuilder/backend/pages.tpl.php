<?php

$shared_template = cmsConfig::get('root_path') . 'templates/admincoreui/controllers/landingbuilder/backend/pages.tpl.php';
if (is_readable($shared_template)) {
    include $shared_template;
    return;
}

$page_mode_titles = [
    'instant_content_body' => 'Нативная страница Instant (content_body)',
    'full_takeover'  => 'Полностью своя страница',
    'hybrid_overlay' => 'Поверх существующей страницы',
    'zone_injection' => 'Встраивание в зону страницы',
    'data_only'      => 'Только данные для блоков'
];

$page_status_titles = [
    'draft'     => 'Черновик',
    'prototype' => 'Прототип',
    'idea'      => 'Идея',
    'published' => 'Опубликовано'
];

$this->setPageTitle('Нордик: страницы');
$this->setMenuItems('backend', $menu);
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Страницы');

$this->addToolButton([
    'class' => 'add',
    'title' => 'Новая страница',
    'href'  => '#'
]);

?>
<div class="padded">
    <h3>Нордик: реестр страниц</h3>
    <p>
        <?php if ($is_schema_installed) { ?>
            Компонент работает на собственных таблицах landingbuilder и хранит версии макетов.
        <?php } else { ?>
            Таблицы компонента ещё не установлены, поэтому сейчас показан временный набор страниц.
        <?php } ?>
    </p>

    <table class="table">
        <thead>
            <tr>
                <th>Страница</th>
                <th>Ключ</th>
                <th>Режим</th>
                <th>Статус</th>
                <th>Обновлено</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pages as $page) { ?>
                <tr>
                    <td><?php html($page['title']); ?></td>
                    <td><code><?php html($page['key']); ?></code></td>
                    <td><?php html($page_mode_titles[$page['mode']] ?? $page['mode']); ?></td>
                    <td><?php html($page_status_titles[$page['status']] ?? $page['status']); ?></td>
                    <td><?php html($page['updated_at']); ?></td>
                    <td><a href="<?php html($page['canvas_url']); ?>">Открыть редактор</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>