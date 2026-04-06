<?php

$this->setPageTitle('Нордик: Правила применения');
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Правила применения');
$this->addMenuItems('admin_toolbar', $menu);

$binding_key = (string) ($doc['key'] ?? $selected_key ?? '');
$title = (string) ($doc['title'] ?? '');
$page_key = (string) ($doc['page_key'] ?? '');

$matching = isset($doc['matching']) && is_array($doc['matching']) ? $doc['matching'] : [];
$url_masks = isset($matching['url_masks']) && is_array($matching['url_masks']) ? $matching['url_masks'] : [];
$exclude_masks = isset($matching['exclude_masks']) && is_array($matching['exclude_masks']) ? $matching['exclude_masks'] : [];
$route_params = isset($matching['route_params']) && is_array($matching['route_params']) ? $matching['route_params'] : [];
$require_https = !empty($matching['require_https']);

$fallback = isset($doc['fallback']) && is_array($doc['fallback']) ? $doc['fallback'] : [];
$fallback_strategy = (string) ($fallback['strategy'] ?? 'theme');

$rules = isset($doc['rules']) && is_array($doc['rules']) ? $doc['rules'] : [];
$allow_structural_overlay = array_key_exists('allow_structural_overlay', $rules) ? (bool) $rules['allow_structural_overlay'] : true;
$allow_dynamic_blocks = array_key_exists('allow_dynamic_blocks', $rules) ? (bool) $rules['allow_dynamic_blocks'] : true;
$allow_custom_css = array_key_exists('allow_custom_css', $rules) ? (bool) $rules['allow_custom_css'] : false;

$route_params_json = $route_params ? json_encode($route_params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';

$mask_lines = implode("\n", array_map('strval', $url_masks));
$exclude_lines = implode("\n", array_map('strval', $exclude_masks));

?>

<div class="card mb-4">
	<div class="card-body">
		<h3 class="h5 mb-3">Правила применения макетов (binding rules)</h3>
		<p class="text-muted mb-0">Здесь задаётся, какой макет (page_key) применять для системных overlay-страниц по простым условиям: route_params и URL маскам. Это заменяет хардкод вида <strong>ads-category</strong>/<strong>profile-cover</strong> и позволяет назначать разные макеты для разных сценариев.</p>
	</div>
</div>

<div class="row">
	<div class="col-xl-5 mb-4">
		<div class="card h-100">
			<div class="card-header d-flex align-items-center justify-content-between">
				<span>Список правил</span>
				<form method="post" action="">
					<?php echo html_csrf_token(); ?>
					<button type="submit" name="seed_examples" value="1" class="btn btn-sm btn-outline-primary">Создать примеры</button>
				</form>
			</div>
			<div class="card-body p-0">
				<?php if (empty($items)) { ?>
					<div class="p-3 text-muted">Пока нет ни одного правила.</div>
				<?php } else { ?>
					<div class="table-responsive">
						<table class="table table-hover mb-0">
							<thead>
							<tr>
								<th>binding_key</th>
								<th>page_key</th>
								<th class="text-end">Действия</th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ($items as $item) { ?>
								<tr class="<?php echo ($selected_key && $selected_key === ($item['binding_key'] ?? '')) ? 'table-active' : ''; ?>">
									<td>
										<div class="fw-semibold"><a href="<?php html($item['edit_url']); ?>"><?php html($item['binding_key'] ?? ''); ?></a></div>
										<?php if (!empty($item['title'])) { ?><div class="text-muted small"><?php html($item['title']); ?></div><?php } ?>
									</td>
									<td><?php html($item['page_key'] ?? ''); ?></td>
									<td class="text-end">
										<form method="post" action="" style="display:inline-block" onsubmit="return confirm('Удалить это правило?');">
											<?php echo html_csrf_token(); ?>
											<input type="hidden" name="binding_key" value="<?php html($item['binding_key'] ?? ''); ?>">
											<button type="submit" name="delete" value="1" class="btn btn-sm btn-outline-danger">Удалить</button>
										</form>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } ?>
			</div>
			<div class="card-footer text-muted small">
				Подсказка: для overlay-правил используйте префиксы <code>overlay.content_category.*</code> и <code>overlay.user_profile.*</code>.
			</div>
		</div>
	</div>

	<div class="col-xl-7 mb-4">
		<div class="card h-100">
			<div class="card-header">Создать / редактировать правило</div>
			<div class="card-body">
				<form method="post" action="">
					<?php echo html_csrf_token(); ?>

					<div class="mb-3">
						<label class="form-label">binding_key <span class="text-danger">*</span></label>
						<input type="text" name="binding_key" class="form-control" value="<?php html($binding_key); ?>" placeholder="overlay.content_category.board">
						<?php if (!empty($errors['binding_key'])) { ?><div class="text-danger small mt-1"><?php html($errors['binding_key']); ?></div><?php } ?>
						<div class="text-muted small mt-1">Уникальный ключ правила. Рекомендуемый формат: <code>overlay.&lt;kind&gt;.&lt;variant&gt;</code>.</div>
					</div>

					<div class="mb-3">
						<label class="form-label">Заголовок</label>
						<input type="text" name="title" class="form-control" value="<?php html($title); ?>" placeholder="Например: Категории объявлений (board)">
					</div>

					<div class="mb-3">
						<label class="form-label">page_key <span class="text-danger">*</span></label>
						<input type="text" name="page_key" class="form-control" value="<?php html($page_key); ?>" placeholder="ads-category">
						<?php if (!empty($errors['page_key'])) { ?><div class="text-danger small mt-1"><?php html($errors['page_key']); ?></div><?php } ?>
						<div class="text-muted small mt-1">Ключ макета из списка макетов Nordic (например: <code>ads-category</code>, <code>profile-cover</code> или ваш кастомный ключ).</div>
					</div>

					<div class="mb-3">
						<label class="form-label">matching.route_params (JSON)</label>
						<textarea name="route_params_json" class="form-control" rows="6" placeholder='{"overlay":"content_category","ctype":"board"}'><?php html($route_params_json); ?></textarea>
						<?php if (!empty($errors['route_params_json'])) { ?><div class="text-danger small mt-1"><?php html($errors['route_params_json']); ?></div><?php } ?>
						<div class="text-muted small mt-1">Самый простой способ настроить условия: совпадение по параметрам overlay. Доступные ключи сейчас: <code>overlay</code>, <code>ctype</code>, <code>category_id</code>, <code>category_key</code>, <code>user_id</code>, <code>group_id</code>.</div>
					</div>

					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">matching.url_masks</label>
							<textarea name="url_masks" class="form-control" rows="5" placeholder="news/*\nboard/*\n"><?php html($mask_lines); ?></textarea>
							<div class="text-muted small mt-1">По одной маске на строку. <code>*</code> — любой хвост.</div>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">matching.exclude_masks</label>
							<textarea name="exclude_masks" class="form-control" rows="5" placeholder="news/admin/*"><?php html($exclude_lines); ?></textarea>
							<div class="text-muted small mt-1">Если совпало — правило не применится.</div>
						</div>
					</div>

					<div class="form-check mb-3">
						<input class="form-check-input" type="checkbox" name="require_https" value="1" id="require_https" <?php echo $require_https ? 'checked' : ''; ?>>
						<label class="form-check-label" for="require_https">Требовать HTTPS</label>
					</div>

					<div class="mb-3">
						<label class="form-label">fallback.strategy</label>
						<select name="fallback_strategy" class="form-select">
							<?php foreach (['theme' => 'theme', 'binding_default' => 'binding_default', 'page_default' => 'page_default', 'disabled' => 'disabled'] as $value => $label) { ?>
								<option value="<?php html($value); ?>" <?php echo $fallback_strategy === $value ? 'selected' : ''; ?>><?php html($label); ?></option>
							<?php } ?>
						</select>
						<div class="text-muted small mt-1">Для MVP обычно достаточно <code>theme</code>.</div>
					</div>

					<div class="mb-3">
						<label class="form-label">rules</label>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="allow_structural_overlay" value="1" id="allow_structural_overlay" <?php echo $allow_structural_overlay ? 'checked' : ''; ?>>
							<label class="form-check-label" for="allow_structural_overlay">allow_structural_overlay</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="allow_dynamic_blocks" value="1" id="allow_dynamic_blocks" <?php echo $allow_dynamic_blocks ? 'checked' : ''; ?>>
							<label class="form-check-label" for="allow_dynamic_blocks">allow_dynamic_blocks</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="allow_custom_css" value="1" id="allow_custom_css" <?php echo $allow_custom_css ? 'checked' : ''; ?>>
							<label class="form-check-label" for="allow_custom_css">allow_custom_css</label>
						</div>
					</div>

					<button type="submit" name="submit" value="1" class="btn btn-primary">Сохранить правило</button>
				</form>
			</div>
			<?php if (!empty($examples)) { ?>
			<div class="card-footer">
				<div class="small text-muted mb-2">Примеры (можно создать кнопкой слева):</div>
				<?php foreach ($examples as $ex) { ?>
					<div class="mb-2"><code><?php html($ex['binding_key']); ?></code></div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
