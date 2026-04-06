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
<style>
	.lb-shell-ui {
		display: grid;
		gap: 1.25rem;
		margin-bottom: 2rem;
	}

	.lb-shell-hero,
	.lb-shell-card {
		border: 1px solid #d8e2ea;
		border-radius: 26px;
		background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
		box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
	}

	.lb-shell-hero {
		padding: 1.5rem 1.65rem;
	}

	.lb-shell-grid {
		display: grid;
		gap: 1rem;
		grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
	}

	.lb-shell-card {
		padding: 1.2rem 1.25rem;
	}

	.lb-shell-card__title {
		margin: 0;
		font-size: 20px;
		font-weight: 700;
		color: #132236;
	}

	.lb-shell-card__desc {
		margin: 0.45rem 0 0.85rem;
		color: #607285;
		line-height: 1.55;
	}

	.lb-shell-card__meta {
		display: grid;
		gap: 0.7rem;
		margin-bottom: 1rem;
	}

	.lb-shell-kv {
		display: flex;
		justify-content: space-between;
		gap: 1rem;
		padding-bottom: 0.7rem;
		border-bottom: 1px solid #edf2f7;
	}

	.lb-shell-kv:last-child {
		padding-bottom: 0;
		border-bottom: 0;
	}

	.lb-shell-kv__label {
		font-size: 11px;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: #718499;
	}

	.lb-shell-kv__value {
		color: #173042;
		text-align: right;
	}

	.lb-shell-chip-row {
		display: flex;
		flex-wrap: wrap;
		gap: 0.45rem;
		margin-bottom: 1rem;
	}

	.lb-shell-chip {
		display: inline-flex;
		align-items: center;
		padding: 0.38rem 0.62rem;
		border-radius: 999px;
		background: #eef4f8;
		color: #355168;
		font-size: 11px;
		font-weight: 700;
		letter-spacing: 0.06em;
		text-transform: uppercase;
	}

	.lb-shell-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 40px;
		padding: 0.7rem 0.95rem;
		border-radius: 999px;
		background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
		color: #fff8f3;
		font-size: 11px;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		box-shadow: 0 14px 30px rgba(234, 88, 12, 0.24);
		transition: transform 0.18s ease, box-shadow 0.18s ease;
	}

	.lb-shell-btn:hover,
	.lb-shell-btn:focus {
		color: #ffffff;
		text-decoration: none;
		transform: translateY(-1px);
		box-shadow: 0 18px 34px rgba(234, 88, 12, 0.3);
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
</style>

<div class="lb-shell-ui">
	<section class="lb-shell-hero">
		<div class="lb-admin-eyebrow">Shell Builder</div>
		<h3 class="lb-admin-title">Варианты каркаса сайта</h3>
		<p class="lb-admin-copy">Это первый backend-срез shell-уровня Нордик. Здесь настраиваются header, footer, menu placement, hero и контентные зоны без raw layout rows и legacy positions.</p>
	</section>

	<section class="lb-shell-grid">
		<?php foreach ($variants as $variant) { ?>
			<article class="lb-shell-card">
				<h4 class="lb-shell-card__title"><?php html($variant['title']); ?></h4>
				<p class="lb-shell-card__desc"><?php html($variant['description']); ?></p>
				<div class="lb-shell-chip-row">
					<span class="lb-shell-chip"><?php html($scope_titles[$variant['scope']] ?? $variant['scope']); ?></span>
					<?php if (!empty($variant['is_system'])) { ?><span class="lb-shell-chip">system</span><?php } ?>
				</div>
				<div class="lb-shell-card__meta">
					<div class="lb-shell-kv">
						<span class="lb-shell-kv__label">Ключ</span>
						<span class="lb-shell-kv__value"><?php html($variant['key']); ?></span>
					</div>
					<div class="lb-shell-kv">
						<span class="lb-shell-kv__label">Header / Footer</span>
						<span class="lb-shell-kv__value">Header: <?php html($header_titles[$variant['header_variant']] ?? $variant['header_variant']); ?><br>Footer: <?php html($footer_titles[$variant['footer_variant']] ?? $variant['footer_variant']); ?></span>
					</div>
					<div class="lb-shell-kv">
						<span class="lb-shell-kv__label">Каркас</span>
						<span class="lb-shell-kv__value"><?php html($layout_titles[$variant['body_layout']] ?? $variant['body_layout']); ?></span>
					</div>
					<div class="lb-shell-kv">
						<span class="lb-shell-kv__label">Сценарий</span>
						<span class="lb-shell-kv__value">Hero: <?php echo !empty($variant['show_hero']) ? 'включен' : 'выключен'; ?><br>Before: <?php echo !empty($variant['show_before_content']) ? 'да' : 'нет'; ?><br>After: <?php echo !empty($variant['show_after_content']) ? 'да' : 'нет'; ?></span>
					</div>
					<div class="lb-shell-kv">
						<span class="lb-shell-kv__label">Активные слоты</span>
						<span class="lb-shell-kv__value"><?php html(implode(', ', array_map(function ($slot_name) use ($variant) {
							return $variant['slot_titles'][$slot_name] ?? $slot_name;
						}, $variant['active_slots']))); ?></span>
					</div>
				</div>
				<a class="lb-shell-btn" href="<?php html($variant['edit_url']); ?>">Открыть variant</a>
			</article>
		<?php } ?>
	</section>
</div>