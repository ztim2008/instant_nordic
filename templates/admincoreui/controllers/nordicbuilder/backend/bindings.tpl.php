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

$content_types = isset($content_types) && is_array($content_types) ? $content_types : [];
$bindings_base_url = (string) ($bindings_base_url ?? '');
$page_key_filter = (string) ($page_key_filter ?? '');
$list_action_url = $bindings_base_url !== '' ? $bindings_base_url : '';

$form_query = [];
if ($binding_key !== '') {
	$form_query['key'] = $binding_key;
} elseif ($page_key_filter !== '') {
	$form_query['page_key'] = $page_key_filter;
}

$form_action_url = $bindings_base_url;
if ($form_action_url === '') {
	$form_action_url = '';
}
if ($form_action_url !== '' && $form_query) {
	$form_action_url .= '?' . http_build_query($form_query);
}

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
				<form method="post" action="<?php html($list_action_url); ?>">
					<?php echo html_csrf_token(); ?>
					<?php if ($page_key_filter !== '') { ?>
						<input type="hidden" name="page_key_filter" value="<?php html($page_key_filter); ?>">
					<?php } ?>
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
										<form method="post" action="<?php html($list_action_url); ?>" style="display:inline-block" onsubmit="return confirm('Удалить это правило?');">
											<?php echo html_csrf_token(); ?>
											<input type="hidden" name="binding_key" value="<?php html($item['binding_key'] ?? ''); ?>">
											<?php if ($page_key_filter !== '') { ?>
												<input type="hidden" name="page_key_filter" value="<?php html($page_key_filter); ?>">
											<?php } ?>
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
				<form method="post" action="<?php html($form_action_url); ?>" id="nb-binding-form">
					<?php echo html_csrf_token(); ?>

					<div class="alert alert-light border">
						<div class="fw-semibold mb-2">Простой режим для новичков</div>
						<div class="text-muted small mb-3">Выберите, где показывать макет, и отметьте галочками, где не показывать. Ручной JSON не нужен.</div>

						<div class="mb-3">
							<label class="form-label">Где показывать</label>
							<select class="form-select" id="nb-simple-scope">
								<option value="custom">Свой вариант (эксперт)</option>
								<option value="homepage">Только главная страница</option>
								<option value="all_internal">Все внутренние страницы (кроме главной)</option>
								<option value="overlay_content_category">Страницы категорий контента (overlay)</option>
								<option value="overlay_user_profile">Страницы профиля пользователя (overlay)</option>
							</select>
						</div>

						<div class="mb-3" id="nb-exclude-ctypes-wrap" style="display:none;">
							<label class="form-label mb-2">Не показывать в типах контента</label>
							<?php if ($content_types) { ?>
								<div class="row g-2">
									<?php foreach ($content_types as $ctype) { ?>
										<div class="col-md-6">
											<div class="form-check">
												<input class="form-check-input" type="checkbox" value="<?php html($ctype['name'] ?? ''); ?>" id="nb-exclude-ctype-<?php html($ctype['name'] ?? ''); ?>" data-nb-exclude-ctype="1">
												<label class="form-check-label" for="nb-exclude-ctype-<?php html($ctype['name'] ?? ''); ?>"><?php html($ctype['title'] ?? $ctype['name'] ?? ''); ?></label>
											</div>
										</div>
									<?php } ?>
								</div>
								<div class="text-muted small mt-2">Если отметить тип, макет не будет применяться к этому типу контента.</div>
							<?php } else { ?>
								<div class="text-muted small">Типы контента не найдены.</div>
							<?php } ?>
						</div>

						<div class="form-check">
							<input class="form-check-input" type="checkbox" value="1" id="nb-expert-mode">
							<label class="form-check-label" for="nb-expert-mode">Показать экспертные поля (JSON и URL-маски)</label>
						</div>
					</div>

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

					<div id="nb-expert-fields" style="display:none;">

					<div class="mb-3">
						<label class="form-label">matching.route_params (JSON)</label>
						<textarea name="route_params_json" class="form-control" rows="6" placeholder='{"overlay":"content_category","ctype":"board"}'><?php html($route_params_json); ?></textarea>
						<?php if (!empty($errors['route_params_json'])) { ?><div class="text-danger small mt-1"><?php html($errors['route_params_json']); ?></div><?php } ?>
						<div class="text-muted small mt-1">Самый простой способ настроить условия: совпадение по параметрам overlay. Доступные ключи сейчас: <code>overlay</code>, <code>ctype</code>, <code>category_id</code>, <code>category_key</code>, <code>user_id</code>, <code>group_id</code>.</div>
					</div>

					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">matching.url_masks</label>
							<textarea name="url_masks" class="form-control" rows="5" placeholder="news/*
board/*"><?php html($mask_lines); ?></textarea>
							<div class="text-muted small mt-1">По одной маске на строку. <code>*</code> — любой хвост.</div>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">matching.exclude_masks</label>
							<textarea name="exclude_masks" class="form-control" rows="5" placeholder="news/admin/*"><?php html($exclude_lines); ?></textarea>
							<div class="text-muted small mt-1">Если совпало — правило не применится.</div>
						</div>
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

<?php ob_start(); ?>
<script>
(function () {
	const form = document.getElementById('nb-binding-form');
	if (!form) {
		return;
	}

	const scopeSelect = document.getElementById('nb-simple-scope');
	const expertToggle = document.getElementById('nb-expert-mode');
	const expertFields = document.getElementById('nb-expert-fields');
	const excludeWrap = document.getElementById('nb-exclude-ctypes-wrap');
	const routeInput = form.querySelector('textarea[name="route_params_json"]');
	const masksInput = form.querySelector('textarea[name="url_masks"]');
	const excludeMasksInput = form.querySelector('textarea[name="exclude_masks"]');
	const bindingKeyInput = form.querySelector('input[name="binding_key"]');

	if (!scopeSelect || !routeInput || !masksInput || !excludeMasksInput || !bindingKeyInput) {
		return;
	}

	function parseRouteParams() {
		const raw = String(routeInput.value || '').trim();
		if (!raw) {
			return {};
		}

		try {
			const parsed = JSON.parse(raw);
			return parsed && typeof parsed === 'object' ? parsed : {};
		} catch (e) {
			return {};
		}
	}

	function detectPreset(params) {
		if (params.overlay === 'content_category') {
			return 'overlay_content_category';
		}
		if (params.overlay === 'user_profile') {
			return 'overlay_user_profile';
		}
		if (params.page_type === '!homepage') {
			return 'all_internal';
		}
		if (params.page_type === 'homepage' || (params.ctrl === '' && params.action === 'index')) {
			return 'homepage';
		}
		return 'custom';
	}

	function setExcludedFromParams(params) {
		const boxes = form.querySelectorAll('[data-nb-exclude-ctype="1"]');
		if (!boxes.length) {
			return;
		}

		const allTypes = Array.from(boxes).map((box) => String(box.value || ''));
		let allowed = [];
		if (Array.isArray(params.ctype)) {
			allowed = params.ctype.map((item) => String(item));
		} else if (typeof params.ctype === 'string' && params.ctype) {
			allowed = [params.ctype];
		}

		boxes.forEach((box) => {
			if (!allowed.length) {
				box.checked = false;
				return;
			}

			const value = String(box.value || '');
			box.checked = allTypes.includes(value) && !allowed.includes(value);
		});
	}

	function syncVisibility() {
		const preset = scopeSelect.value;
		const isExpert = !!expertToggle.checked;

		expertFields.style.display = isExpert || preset === 'custom' ? '' : 'none';
		excludeWrap.style.display = preset === 'overlay_content_category' ? '' : 'none';
	}

	function maybeSuggestBindingKey(preset, routeParams) {
		const current = String(bindingKeyInput.value || '').trim();
		if (current !== '') {
			return;
		}

		if (preset === 'homepage') {
			bindingKeyInput.value = 'page.homepage';
			return;
		}
		if (preset === 'all_internal') {
			bindingKeyInput.value = 'page.all_internal';
			return;
		}
		if (preset === 'overlay_user_profile') {
			bindingKeyInput.value = 'overlay.user_profile.default';
			return;
		}
		if (preset === 'overlay_content_category') {
			const ctype = Array.isArray(routeParams.ctype) ? String(routeParams.ctype[0] || '') : String(routeParams.ctype || '');
			bindingKeyInput.value = ctype ? ('overlay.content_category.' + ctype) : 'overlay.content_category.default';
		}
	}

	function collectRouteParamsByPreset() {
		const preset = scopeSelect.value;
		if (preset === 'custom') {
			return null;
		}

		if (preset === 'homepage') {
			return { ctrl: '', action: 'index', page_type: 'homepage' };
		}
		if (preset === 'all_internal') {
			return { page_type: '!homepage' };
		}
		if (preset === 'overlay_user_profile') {
			return { overlay: 'user_profile' };
		}
		if (preset === 'overlay_content_category') {
			const boxes = Array.from(form.querySelectorAll('[data-nb-exclude-ctype="1"]'));
			const allTypes = boxes.map((box) => String(box.value || '')).filter(Boolean);
			const excluded = boxes.filter((box) => box.checked).map((box) => String(box.value || ''));
			const allowed = allTypes.filter((name) => !excluded.includes(name));

			if (allTypes.length && allowed.length === 0) {
				window.alert('Нельзя исключить все типы контента. Оставьте хотя бы один тип, где макет показывается.');
				return false;
			}

			const params = { overlay: 'content_category' };
			if (allowed.length && allowed.length < allTypes.length) {
				params.ctype = allowed;
			}
			return params;
		}

		return null;
	}

	const initialParams = parseRouteParams();
	const detectedPreset = detectPreset(initialParams);
	scopeSelect.value = detectedPreset;
	setExcludedFromParams(initialParams);
	syncVisibility();

	scopeSelect.addEventListener('change', syncVisibility);
	expertToggle.addEventListener('change', syncVisibility);

	form.addEventListener('submit', function (event) {
		const params = collectRouteParamsByPreset();
		if (params === false) {
			event.preventDefault();
			return;
		}

		if (params && typeof params === 'object') {
			routeInput.value = JSON.stringify(params, null, 2);
			masksInput.value = '';
			excludeMasksInput.value = '';
			maybeSuggestBindingKey(scopeSelect.value, params);
		}
	});
})();
</script>
<?php $this->addBottom(ob_get_clean()); ?>
