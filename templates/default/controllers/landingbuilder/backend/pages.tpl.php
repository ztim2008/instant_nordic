<?php

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
    <h3>Skeleton backend для landingbuilder</h3>
    <p>Это первый безопасный каркас. Здесь уже виден главный вход в редактор страниц и переход в visual canvas.</p>

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
                    <td><code><?php html($page['mode']); ?></code></td>
                    <td><?php html($page['status']); ?></td>
                    <td><?php html($page['updated_at']); ?></td>
                    <td><a href="<?php html($page['canvas_url']); ?>">Открыть canvas</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>