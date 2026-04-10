<?php
/** @var cmsTemplate $this */

$rules = $rules ?? [];
$csrf_token = $csrf_token ?? '';
$errors = $errors ?? [];
$current = $current ?? null;
$base_url = $base_url ?? '';
$build_url = $build_url ?? null;
$tokens_url = $tokens_url ?? '';

$val = function($key, $default = '') use ($current) {
    if (is_array($current) && array_key_exists($key, $current)) {
        return $current[$key];
    }
    return $default;
};
?>

<h1>NordicStyl — правила стилей</h1>

<div class="pills">
    <a class="button" href="<?php html($base_url); ?>">Список</a>
    <?php if ($tokens_url) { ?><a class="button" href="<?php html($tokens_url); ?>">Токены (:root)</a><?php } ?>
</div>

<div style="margin: 1rem 0;">
    <form method="post" action="<?php html($base_url); ?>">
        <input type="hidden" name="csrf_token" value="<?php html($csrf_token); ?>">
        <input type="hidden" name="submit" value="1">
        <input type="hidden" name="id" value="<?php echo (int)$val('id', 0); ?>">

        <div class="form-row">
            <div class="form-group">
                <label>Название</label>
                <input class="input" type="text" name="title" value="<?php html((string)$val('title', ''), true); ?>">
            </div>
            <div class="form-group">
                <label>Селектор (обязательно)</label>
                <input class="input" type="text" name="path" value="<?php html((string)$val('path', ''), true); ?>">
                <?php if (!empty($errors['path'])) { ?><div class="hint"><?php html($errors['path']); ?></div><?php } ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Ordering</label>
                <input class="input" type="number" name="ordering" value="<?php echo (int)$val('ordering', 0); ?>" min="0" max="1000000">
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="is_enabled" value="1" <?php if (!empty($val('is_enabled', 1))) { ?>checked<?php } ?>> Включено</label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="is_important" value="1" <?php if (!empty($val('is_important', 0))) { ?>checked<?php } ?>> !important</label>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="width: 100%;">
                <label>Styles YAML (опционально)</label>
                <textarea class="textarea" name="styles_yaml" rows="6" placeholder="default:\n  color: '#333'\nhover:\n  color: '#000'\n"><?php html((string)$val('styles', ''), true); ?></textarea>
                <?php if (!empty($errors['styles_yaml'])) { ?><div class="hint"><?php html($errors['styles_yaml']); ?></div><?php } ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="width: 100%;">
                <label>Custom YAML (опционально)</label>
                <textarea class="textarea" name="custom_yaml" rows="6" placeholder="default: |\n  background: red;\n  padding: 12px;\n"><?php html((string)$val('custom', ''), true); ?></textarea>
                <?php if (!empty($errors['custom_yaml'])) { ?><div class="hint"><?php html($errors['custom_yaml']); ?></div><?php } ?>
            </div>
        </div>

        <div class="form-row">
            <button class="button" type="submit">Сохранить</button>
            <?php if (!empty($val('id', 0))) { ?>
                <a class="button" href="<?php html($base_url); ?>">Отменить редактирование</a>
            <?php } ?>
        </div>
    </form>
</div>

<h2>Список правил</h2>

<?php if (!$rules) { ?>
    <p>Пока нет правил.</p>
<?php } else { ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Селектор</th>
                <th>Ordering</th>
                <th>On</th>
                <th>!imp</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rules as $r) { ?>
                <tr>
                    <td><?php echo (int)($r['id'] ?? 0); ?></td>
                    <td><?php html((string)($r['title'] ?? '')); ?></td>
                    <td><code><?php html((string)($r['path'] ?? '')); ?></code></td>
                    <td><?php echo (int)($r['ordering'] ?? 0); ?></td>
                    <td><?php echo !empty($r['is_enabled']) ? 'yes' : 'no'; ?></td>
                    <td><?php echo !empty($r['is_important']) ? 'yes' : 'no'; ?></td>
                    <td style="white-space:nowrap;">
                        <a class="button" href="<?php html($base_url . '?' . http_build_query(['edit' => (int)$r['id']])); ?>">Редактировать</a>
                        <a class="button" href="<?php html($base_url . '?' . http_build_query(['toggle' => 1, 'id' => (int)$r['id'], 'csrf_token' => $csrf_token])); ?>">On/Off</a>
                        <a class="button" onclick="return confirm('Удалить правило?');" href="<?php html($base_url . '?' . http_build_query(['delete' => 1, 'id' => (int)$r['id'], 'csrf_token' => $csrf_token])); ?>">Удалить</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>
