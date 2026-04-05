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

$this->setPageTitle('Shell Variant: ' . $variant['title']);
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Shell Builder', $this->href_to('shell'));
$this->addBreadcrumb($variant['title']);
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
	'class' => 'save process-save',
	'title' => 'Сохранить',
	'href'  => '#',
	'icon'  => 'save'
]);

$this->addToolButton([
	'class' => 'cancel',
	'title' => 'Назад к вариантам',
	'href'  => $this->href_to('shell'),
	'icon'  => 'undo'
]);

?>
<div class="card mb-4">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-start flex-wrap">
			<div>
				<h3 class="h5 mb-2"><?php html($variant['title']); ?></h3>
				<div class="text-muted mb-2"><?php html($variant['description']); ?></div>
				<div class="small text-muted">Область применения: <?php html($scope_titles[$variant['scope']] ?? $variant['scope']); ?><?php if ($variant['target_label']) { ?> | <?php html($variant['target_label']); ?><?php } ?></div>
			</div>
			<div class="small text-muted mt-3 mt-md-0">Ключ варианта: <code><?php html($variant['key']); ?></code></div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-7 mb-4">
		<div class="card h-100">
			<div class="card-header">Preview shell slots</div>
			<div class="card-body">
				<div class="small text-muted mb-3">Зеленые зоны участвуют в текущем variant, серые отключены на уровне shell-настроек.</div>
				<?php foreach ($preview_rows as $row) { ?>
					<div class="border rounded p-3 mb-3 bg-light">
						<div class="font-weight-bold mb-2"><?php html($row['title']); ?></div>
						<div class="row">
							<?php foreach ($row['cols'] as $col) { ?>
								<div class="col-md-6 mb-2">
									<div class="border rounded p-2 h-100 <?php if ($col['is_active']) { ?>border-success bg-white<?php } else { ?>border-secondary text-muted bg-light<?php } ?>">
										<div class="font-weight-bold"><?php html($col['title']); ?></div>
										<div class="small"><code><?php html($col['name']); ?></code></div>
										<div class="small mt-1"><?php echo $col['is_active'] ? 'Активен в shell' : 'Скрыт этим variant'; ?></div>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="col-xl-5 mb-4">
		<div class="card mb-4">
			<div class="card-header">Краткая сводка</div>
			<div class="card-body">
				<div class="small text-muted mb-2">Header</div>
				<div class="mb-3"><?php html($catalog['header_variant'][$variant['header_variant']] ?? $variant['header_variant']); ?></div>
				<div class="small text-muted mb-2">Footer</div>
				<div class="mb-3"><?php html($catalog['footer_variant'][$variant['footer_variant']] ?? $variant['footer_variant']); ?></div>
				<div class="small text-muted mb-2">Размещение меню</div>
				<div class="mb-3"><?php html($catalog['menu_placement'][$variant['menu_placement']] ?? $variant['menu_placement']); ?></div>
				<div class="small text-muted mb-2">Контентный каркас</div>
				<div><?php html($catalog['body_layout'][$variant['body_layout']] ?? $variant['body_layout']); ?></div>
			</div>
		</div>
		<div class="card">
			<div class="card-header">Настройки variant</div>
			<div class="card-body">
				<?php $this->renderForm($form, $variant, [
					'action' => '',
					'method' => 'post'
				], $errors); ?>
			</div>
		</div>
	</div>
</div>