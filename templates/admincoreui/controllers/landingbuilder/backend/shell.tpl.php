<?php

$scope_titles = [
	'site'     => 'Глобальный shell',
	'homepage' => 'Главная',
	'content'  => 'Материалы',
	'category' => 'Категории',
	'profile'  => 'Профили',
	'landing'  => 'Лендинги',
	'custom'   => 'Кастомный вариант'
];

$header_titles = $catalog['header_variant'];
$footer_titles = $catalog['footer_variant'];
$layout_titles = $catalog['body_layout'];

$this->setPageTitle('Нордик: Shell Builder');
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Shell Builder');
$this->addMenuItems('admin_toolbar', $menu);

?>
<div class="card mb-4">
	<div class="card-body">
		<h3 class="h5 mb-3">Shell Builder: варианты каркаса сайта</h3>
		<p class="text-muted mb-0">Это первый backend-срез shell-уровня Нордик. Здесь настраиваются header, footer, menu placement, hero и контентные зоны без raw layout rows и legacy positions.</p>
	</div>
</div>

<div class="card">
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover mb-0">
				<thead>
					<tr>
						<th>Вариант</th>
						<th>Область применения</th>
						<th>Header / Footer</th>
						<th>Контентный каркас</th>
						<th>Активные слоты</th>
						<th class="text-right">Действие</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($variants as $variant) { ?>
						<tr>
							<td>
								<div class="font-weight-bold"><?php html($variant['title']); ?></div>
								<div class="small text-muted mt-1"><?php html($variant['description']); ?></div>
								<div class="small text-muted mt-2">Ключ: <code><?php html($variant['key']); ?></code><?php if (!empty($variant['is_system'])) { ?> <span class="badge badge-light">system</span><?php } ?></div>
							</td>
							<td>
								<div><?php html($scope_titles[$variant['scope']] ?? $variant['scope']); ?></div>
								<div class="small text-muted mt-1"><?php html($variant['target_label']); ?></div>
							</td>
							<td>
								<div>Header: <?php html($header_titles[$variant['header_variant']] ?? $variant['header_variant']); ?></div>
								<div class="small text-muted mt-1">Footer: <?php html($footer_titles[$variant['footer_variant']] ?? $variant['footer_variant']); ?></div>
							</td>
							<td>
								<div><?php html($layout_titles[$variant['body_layout']] ?? $variant['body_layout']); ?></div>
								<div class="small text-muted mt-1">Hero: <?php echo !empty($variant['show_hero']) ? 'включен' : 'выключен'; ?>, before: <?php echo !empty($variant['show_before_content']) ? 'да' : 'нет'; ?>, after: <?php echo !empty($variant['show_after_content']) ? 'да' : 'нет'; ?></div>
							</td>
							<td>
								<div class="small"><?php html(implode(', ', array_map(function ($slot_name) use ($variant) {
									return $variant['slot_titles'][$slot_name] ?? $slot_name;
								}, $variant['active_slots']))); ?></div>
							</td>
							<td class="text-right">
								<a class="btn btn-sm btn-primary" href="<?php html($variant['edit_url']); ?>">Открыть variant</a>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>