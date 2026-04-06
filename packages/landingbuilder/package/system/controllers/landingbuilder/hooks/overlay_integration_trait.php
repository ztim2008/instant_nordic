<?php

require_once __DIR__ . '/../helpers/runtime_styles.php';

trait landingbuilderOverlayIntegrationTrait {

	protected function applyOverlayIntegration(array $integration, array $placements) {

		$this->cms_template->addLayoutParams([
			'landingbuilder_shell_runtime' => $integration['runtime']['shell'] ?? []
		]);

		$zones = is_array($integration['zones'] ?? null) ? $integration['zones'] : [];
		$rendered_blocks = [];

		foreach ($placements as $placement) {

			$zone_key = (string) ($placement['zone'] ?? '');
			$block_name = (string) ($placement['block'] ?? '');

			if (!$zone_key || !$block_name) {
				continue;
			}

			$zone = is_array($zones[$zone_key] ?? null) ? $zones[$zone_key] : null;
			if (!$zone || empty($zone['sections'])) {
				continue;
			}

			$html = $this->renderOverlayZone($zone, $integration['page']);
			if (!$html) {
				continue;
			}

			$rendered_blocks[] = [
				'block'   => $block_name,
				'html'    => $html,
				'prepend' => !empty($placement['prepend'])
			];
		}

		if (!$rendered_blocks) {
			return false;
		}

		if (function_exists('landingbuilder_inject_runtime_site_styles')) {
			landingbuilder_inject_runtime_site_styles($this->cms_template);
		}

		$this->injectOverlayStyles();

		if (!empty($integration['page']['status']) && $integration['page']['status'] !== 'published' && cmsUser::isAdmin()) {
			$rendered_blocks[0]['html'] = $this->renderDraftNotice($integration['page']) . $rendered_blocks[0]['html'];
		}

		foreach ($rendered_blocks as $rendered_block) {
			$this->cms_template->addToBlock($rendered_block['block'], $rendered_block['html'], $rendered_block['prepend']);
		}

		return true;
	}

	protected function renderOverlayZone(array $zone, array $page) {

		ob_start();
		$this->cms_template->renderControllerChild('landingbuilder', 'overlay_zone', [
			'zone' => $zone,
			'page' => $page
		]);
		return ob_get_clean();
	}

	protected function renderDraftNotice(array $page) {

		$status_titles = [
			'draft'     => 'черновик',
			'prototype' => 'прототип',
			'idea'      => 'идея',
			'published' => 'опубликовано'
		];

		$status = $page['status'] ?? 'draft';
		$status_title = $status_titles[$status] ?? $status;

		ob_start();
		?>
		<div class="lb-overlay-note alert alert-warning border-0 mb-4">
			<div class="font-weight-bold mb-1">Слой Нордик подключен в режиме предпросмотра</div>
			<div>Эта builder-надстройка для страницы «<?php html($page['title'] ?? $page['key'] ?? 'Страница'); ?>» пока имеет статус «<?php html($status_title); ?>», поэтому сейчас видна только администратору.</div>
		</div>
		<?php
		return ob_get_clean();
	}

	protected function injectOverlayStyles() {

		static $is_injected = false;

		if ($is_injected) {
			return;
		}

		$is_injected = true;

		$this->cms_template->addToBlock('before_body', '<style>
		.lb-overlay-note{background:#fff7df;color:#6c5618;border-left:4px solid #f1c85b}
		.lb-overlay-zone{margin:0 0 1.5rem;color:var(--lb-text-color,#173042);font-family:var(--lb-font-body,inherit)}
		.lb-overlay-zone__head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:1rem}
		.lb-overlay-zone__kicker{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--lb-text-muted,#6c7f90);margin-bottom:6px}
		.lb-overlay-zone__title{margin:0;font-size:1.35rem;color:var(--lb-heading-color,#163040);font-family:var(--lb-font-heading,inherit)}
		.lb-overlay-zone__desc{margin:.35rem 0 0;color:var(--lb-text-muted,#5e7385)}
		.lb-overlay-pill{display:inline-flex;align-items:center;padding:.45rem .75rem;border-radius:999px;border:1px solid var(--lb-border-color,#d8e1e8);background:var(--lb-zone-pill-background,#fff);color:var(--lb-zone-pill-color,#355065);font-size:.82rem}
		.lb-overlay-section.card{overflow:hidden;border:1px solid var(--lb-border-color,#dde6ec);background:var(--lb-surface-color,#fff);border-radius:var(--lb-radius-md,18px);box-shadow:var(--lb-shadow-md,0 12px 32px rgba(18,36,52,.06))}
		.lb-overlay-section__inner{width:100%}
		.lb-overlay-section__head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:1rem}
		.lb-overlay-section__kicker{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--lb-text-muted,#73889a);margin-bottom:6px}
		.lb-overlay-section__title{margin:0;font-size:1.15rem;color:var(--lb-heading-color,#163040);font-family:var(--lb-font-heading,inherit)}
		.lb-overlay-column-title{font-size:.9rem;font-weight:600;color:var(--lb-heading-color,#355065);margin:0 0 .75rem}
		.lb-node-card{height:100%;border:1px solid var(--lb-card-border,var(--lb-border-color,#dde6ec));border-radius:16px;background:var(--lb-card-background,#fff);box-shadow:var(--lb-card-shadow,0 10px 24px rgba(19,41,61,.05));margin-bottom:1rem}
		.lb-node-card__body{padding:1rem 1rem 1.1rem}
		.lb-node-label{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--lb-text-muted,#7a8fa1);margin-bottom:.65rem}
		.lb-node-card__body h2,.lb-node-card__body h3{color:var(--lb-heading-color,#173243);font-family:var(--lb-font-heading,inherit)}
		.lb-node-card__body h2{font-size:1.35rem;margin:0 0 .55rem}
		.lb-node-card__body h3{font-size:1.05rem;margin:0 0 .5rem}
		.lb-node-card__body p,.lb-node-card__body li,.lb-node-meta,.lb-node-note{color:var(--lb-text-muted,#5e7385)}
		.lb-node-meta,.lb-node-note{margin-top:.7rem;font-size:.92rem}
		.lb-empty-zone{padding:1rem 1.1rem;border:1px dashed var(--lb-border-color,#cad6df);border-radius:14px;background:var(--lb-surface-soft,#f8fbfd);color:var(--lb-text-muted,#687d8f)}
		.lb-section--container-text .lb-overlay-section__inner{max-width:760px;margin:0 auto}
		.lb-section--container-standard .lb-overlay-section__inner{max-width:1120px;margin:0 auto}
		.lb-section--container-wide .lb-overlay-section__inner{max-width:1320px;margin:0 auto}
		.lb-section--spacing-sm>.card-body{padding-top:.9rem!important;padding-bottom:.9rem!important}
		.lb-section--spacing-md>.card-body{padding-top:1.5rem!important;padding-bottom:1.5rem!important}
		.lb-section--spacing-lg>.card-body{padding-top:2rem!important;padding-bottom:2rem!important}
		.lb-section--spacing-xl>.card-body{padding-top:2.75rem!important;padding-bottom:2.75rem!important}
		.lb-section--tone-base.card{background:var(--lb-surface-color,#fff)}
		.lb-section--tone-brand-soft.card{background:var(--lb-accent-soft,#e8f3f8)}
		.lb-section--tone-muted.card{background:var(--lb-surface-muted,#eef3f8)}
		.lb-section--tone-brand-strong.card{background:var(--lb-accent-color,#2f7aa1);color:var(--lb-accent-contrast,#fff);border-color:transparent;box-shadow:none}
		.lb-section--tone-contrast.card,.lb-section--tone-inverse.card{background:var(--lb-contrast-surface,#173042);color:var(--lb-contrast-text,#f7fbff);border-color:transparent;box-shadow:none}
		.lb-section--tone-brand-strong .lb-overlay-section__title,.lb-section--tone-contrast .lb-overlay-section__title,.lb-section--tone-inverse .lb-overlay-section__title,.lb-section--tone-brand-strong .lb-node-card__body h2,.lb-section--tone-brand-strong .lb-node-card__body h3,.lb-section--tone-contrast .lb-node-card__body h2,.lb-section--tone-contrast .lb-node-card__body h3,.lb-section--tone-inverse .lb-node-card__body h2,.lb-section--tone-inverse .lb-node-card__body h3{color:inherit}
		.lb-section--tone-brand-strong .lb-overlay-pill,.lb-section--tone-contrast .lb-overlay-pill,.lb-section--tone-inverse .lb-overlay-pill{background:rgba(255,255,255,.12);color:inherit;border-color:rgba(255,255,255,.18)}
		.lb-section--tone-brand-strong .lb-node-card,.lb-section--tone-contrast .lb-node-card,.lb-section--tone-inverse .lb-node-card{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.18);box-shadow:none}
		.lb-section--tone-brand-strong .lb-node-card__body p,.lb-section--tone-brand-strong .lb-node-card__body li,.lb-section--tone-brand-strong .lb-node-meta,.lb-section--tone-brand-strong .lb-node-note,.lb-section--tone-contrast .lb-node-card__body p,.lb-section--tone-contrast .lb-node-card__body li,.lb-section--tone-contrast .lb-node-meta,.lb-section--tone-contrast .lb-node-note,.lb-section--tone-inverse .lb-node-card__body p,.lb-section--tone-inverse .lb-node-card__body li,.lb-section--tone-inverse .lb-node-meta,.lb-section--tone-inverse .lb-node-note{color:rgba(255,255,255,.82)}
		.lb-section--style-hero .lb-overlay-section__title,.lb-section--style-hero-split .lb-overlay-section__title{font-size:clamp(1.8rem,3vw,2.4rem)}
		.lb-section--style-cards .lb-node-card{box-shadow:none;background:var(--lb-surface-soft,#f8fbfd)}
		@media (max-width: 767.98px){.lb-overlay-zone__head,.lb-overlay-section__head{display:block}.lb-overlay-pill{margin-top:.65rem}}
		</style>', true);
	}
}