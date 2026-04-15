<?php
/** @var cmsTemplate $this */

$rules = $rules ?? [];
$csrf_token = $csrf_token ?? '';
$errors = $errors ?? [];
$current = $current ?? null;
$base_url = $base_url ?? '';
$build_url = $build_url ?? null;
$tokens_url = $tokens_url ?? '';
$legacy_rules = $legacy_rules ?? [];
$applicability_schema_ready = !empty($applicability_schema_ready);
$scope_options = $scope_options ?? [];

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

<div class="notice" style="margin: 1rem 0; padding: 1rem; background: #f7f8fb; border: 1px solid #d7dce5; border-radius: 10px;">
    <strong>Поддерживаются два формата YAML:</strong><br>
    1. legacy: состояния на верхнем уровне (`default`, `hover`, `active`)<br>
    2. device-aware: сначала ветка (`desktop`, `tablet`, `mobile`), потом состояния. Legacy `base` тоже читается для совместимости.<br>
    Состояния: `default`, `hover`, `active`, `focus`, `focus-visible`, `visited`, `before`, `after`.
</div>

<?php if ($legacy_rules) { ?>
    <div class="notice" style="margin: 1rem 0; padding: 1rem; background: #fff8e7; border: 1px solid #e7c97c; border-radius: 10px;">
        <strong>Найдены legacy-правила на raw selector: <?php echo count($legacy_rules); ?></strong><br>
        Новый интерфейс больше не создает такие привязки. Эти правила нужно перевязать через live picker на node:... или удалить, если они уже не нужны.
        <div style="margin-top: .75rem; display: grid; gap: .35rem;">
            <?php foreach ($legacy_rules as $legacy_rule) { ?>
                <div>
                    #<?php echo (int)($legacy_rule['id'] ?? 0); ?>
                    <?php html((string)($legacy_rule['title'] ?? '')); ?>
                    <code><?php html((string)($legacy_rule['path'] ?? '')); ?></code>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>

<?php if (!$applicability_schema_ready) { ?>
    <div class="notice" style="margin: 1rem 0; padding: 1rem; background: #fff8e7; border: 1px solid #e7c97c; border-radius: 10px;">
        <strong>Applicability пока в режиме совместимости.</strong><br>
        Поля `scope`, `mask_pos`, `mask_neg` появятся после применения SQL migration `008_add_rule_applicability.sql`.
    </div>
<?php } ?>

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
                <label>Target path (обязательно)</label>
                <input class="input" type="text" name="path" value="<?php html((string)$val('path', ''), true); ?>">
                <div class="hint">Разрешены node:..., role:... и :root для токенов.</div>
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

        <?php if ($applicability_schema_ready) { ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Область применения</label>
                    <select class="input" name="scope">
                        <?php foreach ($scope_options as $scope_key => $scope_label) { ?>
                            <option value="<?php html((string)$scope_key, true); ?>" <?php if ((string)$val('scope', 'site') === (string)$scope_key) { ?>selected<?php } ?>><?php html((string)$scope_label); ?></option>
                        <?php } ?>
                    </select>
                    <?php if (!empty($errors['scope'])) { ?><div class="hint"><?php html($errors['scope']); ?></div><?php } ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="width: 100%;">
                    <label>Положительные маски URI</label>
                    <textarea class="textarea" name="mask_pos" rows="4" placeholder="*&#10;catalog/*&#10;news/*"><?php html((string)$val('mask_pos', ''), true); ?></textarea>
                    <div class="hint">Используются только для `custom`. Одна маска на строку. Если список пуст, берется `*`.</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="width: 100%;">
                    <label>Исключающие маски URI</label>
                    <textarea class="textarea" name="mask_neg" rows="4" placeholder="catalog/private/*"><?php html((string)$val('mask_neg', ''), true); ?></textarea>
                    <div class="hint">Работают только вместе с положительными масками.</div>
                </div>
            </div>
        <?php } ?>

        <div class="form-row">
            <div class="form-group" style="width: 100%;">
                <label>Styles YAML (legacy или device-aware)</label>
                <textarea class="textarea" name="styles_yaml" rows="10" placeholder="desktop:\n  default:\n    color: '#333'\n  hover:\n    color: '#000'\ntablet:\n  default:\n    padding: '16px'\n"><?php html((string)$val('styles', ''), true); ?></textarea>
                <?php if (!empty($errors['styles_yaml'])) { ?><div class="hint"><?php html($errors['styles_yaml']); ?></div><?php } ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="width: 100%;">
                <label>Custom YAML (legacy или device-aware)</label>
                <textarea class="textarea" name="custom_yaml" rows="10" placeholder="desktop:\n  default: |\n    transition: all .2s ease;\nmobile:\n  default: |\n    min-height: 44px;\n"><?php html((string)$val('custom', ''), true); ?></textarea>
                <?php if (!empty($errors['custom_yaml'])) { ?><div class="hint"><?php html($errors['custom_yaml']); ?></div><?php } ?>
            </div>
        </div>

        <details style="margin: 1rem 0;">
            <summary>Примеры YAML</summary>
            <div style="margin-top: .75rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                <div>
                    <strong>Legacy states</strong>
                    <pre style="white-space: pre-wrap; background: #f7f8fb; padding: .75rem; border-radius: 8px; border: 1px solid #d7dce5;">default:
  color: '#333'
hover:
  color: '#000'</pre>
                </div>
                <div>
                    <strong>Device-aware</strong>
                                        <pre style="white-space: pre-wrap; background: #f7f8fb; padding: .75rem; border-radius: 8px; border: 1px solid #d7dce5;">desktop:
  default:
    color: '#333'
  hover:
    color: '#000'
tablet:
  default:
    padding: '16px'</pre>
                </div>
            </div>
        </details>

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
                <th>Target path</th>
                <?php if ($applicability_schema_ready) { ?><th>Scope</th><?php } ?>
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
                    <td>
                        <code><?php html((string)($r['path'] ?? '')); ?></code>
                        <?php if (!empty($r['path']) && strpos((string)$r['path'], 'node:') !== 0 && (string)$r['path'] !== ':root') { ?>
                            <div class="hint">legacy selector</div>
                        <?php } ?>
                    </td>
                    <?php if ($applicability_schema_ready) { ?><td><?php html((string)($scope_options[$r['scope'] ?? 'site'] ?? ($r['scope'] ?? 'site'))); ?></td><?php } ?>
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
