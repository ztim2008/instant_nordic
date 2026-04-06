<?php

$theme_helper = cmsConfig::get('root_path') . 'templates/default/controllers/landingbuilder/runtime_theme.php';
if (is_readable($theme_helper)) {
	require_once $theme_helper;
}

$preview_context = function_exists('landingbuilder_get_runtime_theme_context_from_theme')
	? landingbuilder_get_runtime_theme_context_from_theme($screen['theme'], $screen['theme'])
	: ['vars' => [], 'theme' => $screen['theme']];
$preview_style = function_exists('landingbuilder_render_css_vars') ? landingbuilder_render_css_vars($preview_context['vars']) : '';

$this->setPageTitle('Нордик: Глобальный стиль');
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Глобальный стиль');
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
	'class' => 'save process-save',
	'title' => 'Сохранить',
	'href'  => '#',
	'icon'  => 'save'
]);

$this->addToolButton([
	'class' => 'list',
	'title' => 'Макеты',
	'href'  => $this->href_to('pages'),
	'icon'  => 'file-alt'
]);

?>
<style>
	.lb-design-layout {margin-bottom:2rem}
	.lb-design-preview {padding:1.5rem;border:1px solid #d7e0e7;border-radius:26px;background:var(--lb-page-background,#f4f7fa);color:var(--lb-text-color,#173042);box-shadow:0 20px 48px rgba(15,23,42,.08);font-family:var(--lb-font-body,"Segoe UI",Tahoma,sans-serif)}
	.lb-design-preview h2,.lb-design-preview h3,.lb-design-preview h4 {font-family:var(--lb-font-heading,"Segoe UI",Tahoma,sans-serif);color:var(--lb-heading-color,#142c3d)}
	.lb-design-kicker {margin:0 0 .5rem;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--lb-text-muted,#64748b)}
	.lb-design-title {margin:0;font-size:1.9rem;line-height:1.1}
	.lb-design-lead {margin:.7rem 0 1.2rem;color:var(--lb-text-muted,#5b7282);max-width:42rem}
	.lb-design-summary {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;margin-bottom:1.25rem}
	.lb-design-summary__item {padding:.8rem .9rem;border:1px solid var(--lb-border-color,#dce4ea);border-radius:16px;background:rgba(255,255,255,.72)}
	.lb-design-summary__label {font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--lb-text-muted,#64748b);margin-bottom:.25rem}
	.lb-design-summary__value {font-size:.95rem;color:var(--lb-heading-color,#142c3d)}
	.lb-design-stage {display:grid;gap:1rem}
	.lb-design-hero {padding:1.4rem;border:1px solid var(--lb-border-color,#dce4ea);border-radius:24px;background:var(--lb-hero-background,linear-gradient(135deg,#f4f6f8 0%,#fff 60%,#eef3f8 100%));box-shadow:var(--lb-shadow-lg,0 18px 48px rgba(19,41,61,.10))}
	.lb-design-actions {display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1rem}
	.lb-design-button {display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:.7rem 1rem;border-radius:999px;border:1px solid var(--lb-button-border,transparent);font-weight:600}
	.lb-design-button--primary {background:var(--lb-button-background,var(--lb-accent-soft));color:var(--lb-button-color,var(--lb-accent-color))}
	.lb-design-button--secondary {background:transparent;color:var(--lb-text-color,#173042);border-color:var(--lb-border-color,#dce4ea)}
	.lb-design-grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
	.lb-design-card {padding:1rem;border-radius:18px;border:1px solid var(--lb-card-border,var(--lb-border-color,#dce4ea));background:var(--lb-card-background,#fff);box-shadow:var(--lb-card-shadow,0 8px 20px rgba(18,36,52,.05))}
	.lb-design-card p {margin:0;color:var(--lb-text-muted,#5b7282)}
	.lb-design-note {padding:1rem 1.1rem;border:1px dashed var(--lb-border-color,#cad5df);border-radius:18px;background:var(--lb-surface-soft,#f8fbfd);color:var(--lb-text-muted,#5b7282)}
	 @media (max-width: 991.98px) {.lb-design-summary,.lb-design-grid{grid-template-columns:1fr}}
</style>

<div class="card mb-4">
	<div class="card-body">
		<h3 class="h5 mb-3">Глобальный стиль</h3>
		<p class="text-muted mb-0">Этот экран больше не является главным маршрутом конструктора. Здесь выбирается шаблон сайта и редкие общие настройки: стартовая палитра, типографика, контейнеры, кнопки и карточки по умолчанию. Основная визуальная работа со страницей и локальным стилем должна происходить прямо на холсте.</p>
	</div>
</div>

<div class="row lb-design-layout">
	<div class="col-xl-5 mb-4">
		<div class="lb-design-preview" style="<?php html($preview_style); ?>">
			<div class="lb-design-kicker">Шаблон и база стиля</div>
			<h2 class="lb-design-title">Шаблон сайта и его базовый визуальный язык</h2>
			<p class="lb-design-lead">Превью ниже показывает, какой характер получит сайт целиком. После сохранения шаблон и эти значения становятся базой для каркаса сайта и оформления страниц, но не заменяют настройку конкретной страницы прямо на холсте.</p>

			<div class="lb-design-summary">
				<?php foreach ($screen['summary'] as $item) { ?>
					<div class="lb-design-summary__item">
						<div class="lb-design-summary__label"><?php html($item['label']); ?></div>
						<div class="lb-design-summary__value"><?php html($item['value']); ?></div>
					</div>
				<?php } ?>
			</div>

			<div class="lb-design-stage">
				<section class="lb-design-hero">
					<div class="lb-design-kicker">Шапка, каркас и первый экран</div>
					<h3>Сайт сразу получает выбранный шаблон и общий тон первого экрана</h3>
					<p class="mb-0">Этот экран нужен для того, чтобы один раз выбрать шаблон сайта и стартовый визуальный язык, а не повторять те же решения на каждой странице по отдельности.</p>
					<div class="lb-design-actions">
						<span class="lb-design-button lb-design-button--primary">Основная кнопка</span>
						<span class="lb-design-button lb-design-button--secondary">Вторичное действие</span>
					</div>
				</section>

				<div class="lb-design-grid">
					<article class="lb-design-card">
						<div class="lb-design-kicker">Карточки</div>
						<h4>Карточки и поверхности</h4>
						<p>Списки, преимущества и каталожные блоки начинают выглядеть предсказуемо без ручной правки каждой секции.</p>
					</article>
					<article class="lb-design-card">
						<div class="lb-design-kicker">Контейнеры</div>
						<h4>Контейнеры и ритм</h4>
						<p>Ширина страницы и расстояние между секциями настраиваются как часть общего стиля сайта.</p>
					</article>
				</div>

				<div class="lb-design-note">После сохранения шаблон сайта и эти значения становятся общими значениями по умолчанию для каркаса, превью и стартового состояния холста. Повседневная работа со страницей и секциями должна происходить в инспекторе рядом с холстом.</div>
			</div>
		</div>
	</div>
	<div class="col-xl-7 mb-4">
		<div class="card h-100">
			<div class="card-header">Шаблон сайта и редкие общие настройки</div>
			<div class="card-body">
				<?php $this->renderForm($form, $theme, [
					'action' => '',
					'method' => 'post'
				], $errors); ?>
			</div>
		</div>
	</div>
</div>