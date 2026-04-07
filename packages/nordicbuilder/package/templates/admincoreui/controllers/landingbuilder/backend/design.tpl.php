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
	'title' => 'Страницы',
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
	.lb-design-summary__item {padding:.8rem .9rem;border:1px solid var(--lb-border-color,#dce4ea);border-radius:14px;background:rgba(255,255,255,.74)}
	.lb-design-summary__label {font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--lb-text-muted,#64748b);margin-bottom:.25rem}
	.lb-design-summary__value {font-size:.95rem;color:var(--lb-heading-color,#142c3d)}
	.lb-ds-grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
	.lb-ds-panel {padding:1rem;border:1px solid var(--lb-border-color,#dce4ea);border-radius:16px;background:var(--lb-card-background,#fff);box-shadow:var(--lb-card-shadow,0 8px 20px rgba(18,36,52,.05))}
	.lb-ds-panel h4 {margin:0 0 .75rem;font-size:1rem}
	.lb-ds-panel p {margin:0;color:var(--lb-text-muted,#5b7282)}
	.lb-ds-type-row {display:grid;grid-template-columns:72px minmax(0,1fr);gap:.65rem;align-items:baseline;padding:.28rem 0;border-bottom:1px dashed rgba(0,0,0,.06)}
	.lb-ds-type-row:last-child {border-bottom:0}
	.lb-ds-type-label {font-size:10px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--lb-text-muted,#64748b)}
	.lb-ds-type-display {font-size:var(--lb-hero-title-size,40px);line-height:1.06}
	.lb-ds-type-h2 {font-size:32px;line-height:1.1}
	.lb-ds-type-h3 {font-size:24px;line-height:1.15}
	.lb-ds-type-body {font-size:16px;line-height:1.55}
	.lb-ds-type-small {font-size:13px;line-height:1.4;color:var(--lb-text-muted,#5b7282)}
	.lb-ds-swatches {display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.55rem}
	.lb-ds-swatch {padding:.55rem;border-radius:10px;border:1px solid rgba(0,0,0,.08);min-height:64px;display:flex;flex-direction:column;justify-content:space-between;font-size:11px;font-weight:600}
	.lb-ds-swatch--text-light {color:#fff}
	.lb-ds-states {display:grid;gap:.5rem}
	.lb-ds-btn-row {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.55rem}
	.lb-ds-btn {display:flex;align-items:center;justify-content:center;min-height:36px;padding:.45rem .7rem;border:1px solid var(--lb-button-border,transparent);border-radius:999px;font-size:13px;font-weight:600}
	.lb-ds-btn--primary {background:var(--lb-button-background,var(--lb-accent-soft));color:var(--lb-button-color,var(--lb-accent-color))}
	.lb-ds-btn--secondary {background:transparent;color:var(--lb-text-color,#173042);border-color:var(--lb-border-color,#dce4ea)}
	.lb-ds-btn--disabled {opacity:.45;filter:grayscale(.2)}
	.lb-ds-status {display:grid;gap:.35rem;margin-top:.35rem}
	.lb-ds-status-line {padding:.42rem .6rem;border-radius:10px;font-size:12px;font-weight:600}
	.lb-ds-status-info {background:#e8f3f8;color:#235e80}
	.lb-ds-status-ok {background:#e7f5ea;color:#1f7140}
	.lb-ds-status-warn {background:#fff4dd;color:#8a5a10}
	.lb-ds-status-err {background:#fde9e9;color:#8f1d14}
	.lb-ds-dark {background:var(--lb-contrast-surface,#173042);color:var(--lb-contrast-text,#f7fbff);border-color:transparent;box-shadow:0 18px 34px rgba(10,17,25,.24)}
	.lb-ds-dark h4 {color:var(--lb-contrast-text,#f7fbff)}
	.lb-ds-dark .lb-design-kicker {color:rgba(255,255,255,.66)}
	.lb-ds-dark .lb-ds-btn--secondary {border-color:rgba(255,255,255,.35);color:#fff}
	.lb-design-note {padding:1rem 1.1rem;border:1px dashed var(--lb-border-color,#cad5df);border-radius:14px;background:var(--lb-surface-soft,#f8fbfd);color:var(--lb-text-muted,#5b7282);margin-top:1rem}
	@media (max-width: 1199.98px) {.lb-ds-grid{grid-template-columns:1fr}}
	@media (max-width: 991.98px) {.lb-design-summary{grid-template-columns:1fr}}
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
			<div class="lb-design-kicker">Глобальная дизайн-система</div>
			<h2 class="lb-design-title">Стилевая матрица сайта: типографика, цвета, состояния</h2>
			<p class="lb-design-lead">Это не просто форма, а единый стандарт внешнего вида для всего сайта. После сохранения выбранные значения становятся глобальной базой для каркаса и новых страниц.</p>

			<div class="lb-design-summary">
				<?php foreach ($screen['summary'] as $item) { ?>
					<div class="lb-design-summary__item">
						<div class="lb-design-summary__label"><?php html($item['label']); ?></div>
						<div class="lb-design-summary__value"><?php html($item['value']); ?></div>
					</div>
				<?php } ?>
			</div>

			<div class="lb-ds-grid">
				<section class="lb-ds-panel">
					<div class="lb-design-kicker">Типографика</div>
					<h4>Шкала заголовков и текста</h4>
					<div class="lb-ds-type-row">
						<div class="lb-ds-type-label">Display</div>
						<div class="lb-ds-type-display">Aa Заголовок витрины</div>
					</div>
					<div class="lb-ds-type-row">
						<div class="lb-ds-type-label">H2</div>
						<div class="lb-ds-type-h2">Заголовок раздела</div>
					</div>
					<div class="lb-ds-type-row">
						<div class="lb-ds-type-label">H3</div>
						<div class="lb-ds-type-h3">Подзаголовок блока</div>
					</div>
					<div class="lb-ds-type-row">
						<div class="lb-ds-type-label">Body</div>
						<div class="lb-ds-type-body">Основной текст карточек, описаний и контентных секций страницы.</div>
					</div>
					<div class="lb-ds-type-row">
						<div class="lb-ds-type-label">Small</div>
						<div class="lb-ds-type-small">Служебный текст, пояснения, подписи и вторичная информация.</div>
					</div>
				</section>

				<section class="lb-ds-panel">
					<div class="lb-design-kicker">Цвета</div>
					<h4>Палитра интерфейса</h4>
					<div class="lb-ds-swatches">
						<div class="lb-ds-swatch lb-ds-swatch--text-light" style="background:var(--lb-heading-color,#142c3d)">
							<span>Текст</span><span>Heading</span>
						</div>
						<div class="lb-ds-swatch" style="background:var(--lb-surface-color,#ffffff)">
							<span>Фон</span><span>Surface</span>
						</div>
						<div class="lb-ds-swatch" style="background:var(--lb-surface-muted,#eef3f8)">
							<span>Мягкий фон</span><span>Muted</span>
						</div>
						<div class="lb-ds-swatch lb-ds-swatch--text-light" style="background:var(--lb-accent-color,#2f7aa1)">
							<span>Акцент</span><span>Primary</span>
						</div>
						<div class="lb-ds-swatch" style="background:var(--lb-accent-soft,#e8f3f8)">
							<span>Акцент soft</span><span>Tint</span>
						</div>
						<div class="lb-ds-swatch lb-ds-swatch--text-light" style="background:var(--lb-contrast-surface,#173042)">
							<span>Контраст</span><span>Dark surface</span>
						</div>
					</div>
				</section>
			</div>

			<div class="lb-ds-grid mt-3">
				<section class="lb-ds-panel">
					<div class="lb-design-kicker">Состояния компонентов</div>
					<h4>Кнопки и системные сообщения</h4>
					<div class="lb-ds-states">
						<div class="lb-ds-btn-row">
							<span class="lb-ds-btn lb-ds-btn--primary">Normal</span>
							<span class="lb-ds-btn lb-ds-btn--secondary">Secondary</span>
						</div>
						<div class="lb-ds-btn-row">
							<span class="lb-ds-btn lb-ds-btn--primary" style="filter:brightness(.94)">Hover</span>
							<span class="lb-ds-btn lb-ds-btn--secondary" style="background:rgba(0,0,0,.03)">Active</span>
						</div>
						<div class="lb-ds-btn-row">
							<span class="lb-ds-btn lb-ds-btn--primary lb-ds-btn--disabled">Disabled</span>
							<span class="lb-ds-btn lb-ds-btn--secondary lb-ds-btn--disabled">Disabled</span>
						</div>
					</div>
					<div class="lb-ds-status">
						<div class="lb-ds-status-line lb-ds-status-info">Info: нейтральная подсказка</div>
						<div class="lb-ds-status-line lb-ds-status-ok">Success: действие выполнено</div>
						<div class="lb-ds-status-line lb-ds-status-warn">Warning: проверьте настройки</div>
						<div class="lb-ds-status-line lb-ds-status-err">Error: есть конфликт параметров</div>
					</div>
				</section>

				<section class="lb-ds-panel lb-ds-dark">
					<div class="lb-design-kicker">Контрастная плоскость</div>
					<h4>Тёмный вариант интерфейса</h4>
					<p>Та же система токенов работает и на контрастных секциях: читаемость, кнопки и карточки остаются предсказуемыми.</p>
					<div class="lb-ds-btn-row mt-3">
						<span class="lb-ds-btn lb-ds-btn--primary">Primary</span>
						<span class="lb-ds-btn lb-ds-btn--secondary">Ghost</span>
					</div>
				</section>
			</div>

			<div class="lb-design-note">После сохранения этот стиль становится глобальной основой: шаблон, палитра, типографика, контейнеры и состояния компонентов применяются как стартовый стандарт для всего сайта. Детальная настройка отдельной страницы остаётся на холсте.</div>
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