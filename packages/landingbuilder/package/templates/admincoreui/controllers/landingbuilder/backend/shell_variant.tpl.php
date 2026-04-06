<?php

$scope_titles = [
	'site'     => 'Каркас сайта (общий)',
	'homepage' => 'Главная',
	'content'  => 'Материалы',
	'category' => 'Категории',
	'profile'  => 'Профили',
	'landing'  => 'Лендинги',
	'custom'   => 'Пользовательский вариант'
];

$this->setPageTitle('Каркас: ' . $variant['title']);
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Каркас сайта', $this->href_to('shell'));
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
<style>
	.lb-shell-variant-ui {
		display: grid;
		gap: 1.25rem;
		margin-bottom: 2rem;
	}

	.lb-admin-eyebrow {
		margin-bottom: 0.4rem;
		font-size: 11px;
		font-weight: 700;
		letter-spacing: 0.12em;
		text-transform: uppercase;
		color: #6a7d90;
	}

	.lb-admin-title {
		margin: 0;
		font-size: 28px;
		font-weight: 700;
		color: #132236;
	}

	.lb-admin-copy {
		margin: 0.7rem 0 0;
		max-width: 54rem;
		color: #5f7284;
		line-height: 1.6;
	}

	.lb-shell-variant-grid {
		display: grid;
		gap: 1rem;
		grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.95fr);
	}

	.lb-shell-panel {
		border: 1px solid #d8e2ea;
		border-radius: 26px;
		background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
		box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
		padding: 1.4rem 1.5rem;
	}

	.lb-shell-panel__title {
		margin: 0 0 0.35rem;
		font-size: 20px;
		font-weight: 700;
		color: #132236;
	}

	.lb-shell-panel__copy {
		margin: 0;
		color: #5f7284;
		line-height: 1.55;
	}

	.lb-slot-row {
		margin-top: 1rem;
		padding: 1rem;
		border: 1px solid #e4ebf2;
		border-radius: 20px;
		background: #fbfdff;
	}

	.lb-slot-row:first-of-type {
		margin-top: 0;
	}

	.lb-slot-row__title {
		margin: 0 0 0.85rem;
		font-size: 15px;
		font-weight: 700;
		color: #173042;
	}

	.lb-slot-grid {
		display: grid;
		gap: 0.75rem;
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}

	.lb-slot-card {
		padding: 0.9rem;
		border-radius: 18px;
		border: 1px solid #dce5ec;
		background: #f7fbff;
	}

	.lb-slot-card.is-active {
		border-color: #a7d3bf;
		background: #ffffff;
		box-shadow: 0 14px 30px rgba(21, 128, 61, 0.1);
	}

	.lb-slot-card.is-muted {
		border-color: #d9e2ea;
		color: #718499;
		background: #f5f8fb;
	}

	.lb-slot-card__title {
		margin: 0 0 0.3rem;
		font-size: 14px;
		font-weight: 700;
	}

	.lb-slot-card__key,
	.lb-slot-card__state,
	.lb-summary-kv__label {
		font-size: 11px;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: #718499;
	}

	.lb-slot-card__state {
		margin-top: 0.55rem;
	}

	.lb-summary-stack {
		display: grid;
		gap: 0.7rem;
	}

	.lb-summary-kv {
		display: flex;
		justify-content: space-between;
		gap: 1rem;
		padding-bottom: 0.7rem;
		border-bottom: 1px solid #edf2f7;
	}

	.lb-summary-kv:last-child {
		padding-bottom: 0;
		border-bottom: 0;
	}

	.lb-summary-kv__value {
		text-align: right;
		color: #173042;
	}

	@media (max-width: 1199.98px) {
		.lb-shell-variant-grid {
			grid-template-columns: 1fr;
		}
	}

	@media (max-width: 767.98px) {
		.lb-slot-grid {
			grid-template-columns: 1fr;
		}
	}
</style>

<div class="lb-shell-variant-ui">
	<section class="lb-shell-panel">
		<div class="lb-admin-eyebrow">Вариант каркаса</div>
		<h3 class="lb-admin-title"><?php html($variant['title']); ?></h3>
		<p class="lb-admin-copy"><?php html($variant['description']); ?></p>
		<p class="lb-admin-copy">Область применения: <?php html($scope_titles[$variant['scope']] ?? $variant['scope']); ?><?php if ($variant['target_label']) { ?> | <?php html($variant['target_label']); ?><?php } ?> | Ключ: <?php html($variant['key']); ?></p>
	</section>

	<div class="lb-shell-variant-grid">
		<section class="lb-shell-panel">
			<h4 class="lb-shell-panel__title">Предпросмотр зон каркаса</h4>
			<p class="lb-shell-panel__copy">Активные зоны показываются в этом варианте каркаса, а приглушенные скрыты.</p>
			<?php foreach ($preview_rows as $row) { ?>
				<div class="lb-slot-row">
					<h5 class="lb-slot-row__title"><?php html($row['title']); ?></h5>
					<div class="lb-slot-grid">
						<?php foreach ($row['cols'] as $col) { ?>
							<div class="lb-slot-card<?php if ($col['is_active']) { ?> is-active<?php } else { ?> is-muted<?php } ?>">
								<div class="lb-slot-card__title"><?php html($col['title']); ?></div>
								<div class="lb-slot-card__key"><?php html($col['name']); ?></div>
								<div class="lb-slot-card__state"><?php echo $col['is_active'] ? 'Показывается в каркасе' : 'Скрыт в этом варианте'; ?></div>
							</div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</section>
		<div class="lb-shell-variant-ui">
			<section class="lb-shell-panel">
				<h4 class="lb-shell-panel__title">Краткая сводка</h4>
				<div class="lb-summary-stack">
					<div class="lb-summary-kv">
						<span class="lb-summary-kv__label">Шапка</span>
						<span class="lb-summary-kv__value"><?php html($catalog['header_variant'][$variant['header_variant']] ?? $variant['header_variant']); ?></span>
					</div>
					<div class="lb-summary-kv">
						<span class="lb-summary-kv__label">Подвал</span>
						<span class="lb-summary-kv__value"><?php html($catalog['footer_variant'][$variant['footer_variant']] ?? $variant['footer_variant']); ?></span>
					</div>
					<div class="lb-summary-kv">
						<span class="lb-summary-kv__label">Размещение меню</span>
						<span class="lb-summary-kv__value"><?php html($catalog['menu_placement'][$variant['menu_placement']] ?? $variant['menu_placement']); ?></span>
					</div>
					<div class="lb-summary-kv">
						<span class="lb-summary-kv__label">Контентный каркас</span>
						<span class="lb-summary-kv__value"><?php html($catalog['body_layout'][$variant['body_layout']] ?? $variant['body_layout']); ?></span>
					</div>
				</div>
			</section>
			<section class="lb-shell-panel">
				<h4 class="lb-shell-panel__title">Настройки варианта</h4>
				<?php $this->renderForm($form, $variant, [
					'action' => '',
					'method' => 'post'
				], $errors); ?>
			</section>
		</div>
	</div>
</div>