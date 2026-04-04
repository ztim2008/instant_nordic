<?php

class onLandingbuilderProcessRenderContentCategoryView extends cmsAction {

	public function run($_data) {

		list($tpl_file, $data, $request) = $_data;

		if (!$request || $request->isInternal()) {
			return $_data;
		}

		$ctype = is_array($data['ctype'] ?? null) ? $data['ctype'] : [];
		$category = is_array($data['category'] ?? null) ? $data['category'] : [];

		$integration = $this->model->getContentCategoryOverlay($ctype, $category, cmsUser::isAdmin());
		if (!$integration) {
			return $_data;
		}

		$zones = $integration['zones'];
		$before_zone = $zones['before_content'] ?? null;
		$after_zone = $zones['after_content'] ?? null;

		$before_html = $before_zone && !empty($before_zone['sections'])
			? $this->renderOverlayZone($before_zone, $integration['page'])
			: '';
		$after_html = $after_zone && !empty($after_zone['sections'])
			? $this->renderOverlayZone($after_zone, $integration['page'])
			: '';

		if (!$before_html && !$after_html) {
			return $_data;
		}

		$this->injectOverlayStyles();

		if (!empty($integration['page']['status']) && $integration['page']['status'] !== 'published' && cmsUser::isAdmin()) {
			$before_html = $this->renderDraftNotice($integration['page']) . $before_html;
		}

		if ($before_html) {
			$this->cms_template->addToBlock('before_content_items_list_html', $before_html, true);
		}

		if ($after_html) {
			$this->cms_template->addToBlock('after_content_items_list_html', $after_html);
		}

		return $_data;
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
		.lb-overlay-zone{margin:0 0 1.5rem}
		.lb-overlay-zone__head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:1rem}
		.lb-overlay-zone__kicker{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#6c7f90;margin-bottom:6px}
		.lb-overlay-zone__title{margin:0;font-size:1.35rem;color:#163040}
		.lb-overlay-zone__desc{margin:.35rem 0 0;color:#5e7385}
		.lb-overlay-pill{display:inline-flex;align-items:center;padding:.45rem .75rem;border-radius:999px;border:1px solid #d8e1e8;background:#fff;color:#355065;font-size:.82rem}
		.lb-overlay-section.card{overflow:hidden;border:1px solid #dde6ec;background:linear-gradient(180deg,#ffffff 0%,#f8fbfd 100%)}
		.lb-overlay-section__head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:1rem}
		.lb-overlay-section__kicker{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#73889a;margin-bottom:6px}
		.lb-overlay-section__title{margin:0;font-size:1.15rem;color:#163040}
		.lb-overlay-column-title{font-size:.9rem;font-weight:600;color:#355065;margin:0 0 .75rem}
		.lb-node-card{height:100%;border:1px solid #dde6ec;border-radius:16px;background:#fff;box-shadow:0 10px 24px rgba(19,41,61,.05);margin-bottom:1rem}
		.lb-node-card__body{padding:1rem 1rem 1.1rem}
		.lb-node-label{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#7a8fa1;margin-bottom:.65rem}
		.lb-node-card__body h2,.lb-node-card__body h3{color:#173243}
		.lb-node-card__body h2{font-size:1.35rem;margin:0 0 .55rem}
		.lb-node-card__body h3{font-size:1.05rem;margin:0 0 .5rem}
		.lb-node-meta,.lb-node-note{margin-top:.7rem;font-size:.92rem;color:#5e7385}
		.lb-empty-zone{padding:1rem 1.1rem;border:1px dashed #cad6df;border-radius:14px;background:#f8fbfd;color:#687d8f}
		@media (max-width: 767.98px){.lb-overlay-zone__head,.lb-overlay-section__head{display:block}.lb-overlay-pill{margin-top:.65rem}}
		</style>', true);
	}
}