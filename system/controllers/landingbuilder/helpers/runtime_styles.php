<?php

if (!function_exists('landingbuilder_get_runtime_site_styles_css')) {

	function landingbuilder_get_runtime_site_styles_css() {
		return trim((string) <<<CSS
		.lb-runtime-embed{color:var(--lb-text-color,#173042);font-family:var(--lb-font-body,inherit)}
		.lb-runtime-embed h1,.lb-runtime-embed h2,.lb-runtime-embed h3{color:var(--lb-heading-color,#142c3d);font-family:var(--lb-font-heading,inherit)}
		.lb-section{padding:32px 0}
		.lb-section-inner{width:100%;max-width:1120px;margin:0 auto;padding:0 20px}
		.lb-columns{display:grid;gap:18px;grid-template-columns:var(--lb-grid-desktop,minmax(0,1fr))}
		.lb-grid-1{grid-template-columns:var(--lb-grid-desktop,minmax(0,1fr))}
		.lb-grid-2{grid-template-columns:var(--lb-grid-desktop,repeat(2,minmax(0,1fr)))}
		.lb-grid-sidebar-left{grid-template-columns:var(--lb-grid-desktop,minmax(220px,.8fr) minmax(0,1.6fr))}
		.lb-grid-sidebar-right{grid-template-columns:var(--lb-grid-desktop,minmax(0,1.6fr) minmax(220px,.8fr))}
		.lb-grid-3{grid-template-columns:var(--lb-grid-desktop,repeat(3,minmax(0,1fr)))}
		.lb-column{min-width:0}
		.lb-runtime-block{min-width:0}
		.lb-runtime-block--autoscale{position:relative;left:50%;right:50%;width:100vw;max-width:100vw;margin-left:-50vw;margin-right:-50vw}
		.lb-runtime-block--autoscale .container,.lb-runtime-block--autoscale .container-sm,.lb-runtime-block--autoscale .container-md,.lb-runtime-block--autoscale .container-lg,.lb-runtime-block--autoscale .container-xl,.lb-runtime-block--autoscale .container-xxl{max-width:none!important;width:100%!important}
		.lb-native-body-layout{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:24px;align-items:start}
		.lb-native-body-layout--autoscale{position:relative;left:50%;right:50%;width:100vw;max-width:100vw;margin-left:-50vw;margin-right:-50vw;padding-left:var(--lb-native-body-full-padding,20px);padding-right:var(--lb-native-body-full-padding,20px)}
		.lb-native-body-col{min-width:0}
		.lb-native-body-runtime{min-width:0}
		.lb-native-body-runtime--autoscale{position:relative;left:50%;right:50%;width:100vw;max-width:100vw;margin-left:-50vw;margin-right:-50vw;padding-left:var(--lb-native-body-full-padding,20px);padding-right:var(--lb-native-body-full-padding,20px)}
		.lb-native-body-runtime--autoscale .container,.lb-native-body-runtime--autoscale .container-sm,.lb-native-body-runtime--autoscale .container-md,.lb-native-body-runtime--autoscale .container-lg,.lb-native-body-runtime--autoscale .container-xl,.lb-native-body-runtime--autoscale .container-xxl{max-width:none!important;width:100%!important}
		.lb-section--autoscale-base-blocks,.lb-section--autoscale-base-blocks .lb-section-inner,.lb-section--autoscale-base-blocks .lb-columns,.lb-section--autoscale-base-blocks .lb-column{overflow:visible}
		.lb-section--autoscale-wide .lb-section-inner{max-width:none;padding-left:0;padding-right:0}
		.lb-section--container-text .lb-section-inner{max-width:760px}
		.lb-section--container-standard .lb-section-inner{max-width:1120px}
		.lb-section--container-wide .lb-section-inner{max-width:1320px}
		.lb-section--container-full .lb-section-inner{max-width:none;padding-left:0;padding-right:0}
		.lb-section--spacing-sm{padding-top:20px;padding-bottom:20px}
		.lb-section--spacing-md{padding-top:32px;padding-bottom:32px}
		.lb-section--spacing-lg{padding-top:48px;padding-bottom:48px}
		.lb-section--spacing-xl{padding-top:64px;padding-bottom:64px}
		.lb-section--tone-base{background:var(--lb-surface-color,#fff)}
		.lb-section--tone-brand-soft{background:var(--lb-accent-soft,#e8f3f8)}
		.lb-section--tone-muted{background:var(--lb-surface-muted,#eef3f8)}
		.lb-section--tone-brand-strong{background:var(--lb-accent-color,#2f7aa1);color:var(--lb-accent-contrast,#fff)}
		.lb-section--tone-contrast,.lb-section--tone-inverse{background:var(--lb-contrast-surface,#173042);color:var(--lb-contrast-text,#f7fbff)}
		.lb-section--tone-brand-strong h1,.lb-section--tone-brand-strong h2,.lb-section--tone-brand-strong h3,.lb-section--tone-contrast h1,.lb-section--tone-contrast h2,.lb-section--tone-contrast h3,.lb-section--tone-inverse h1,.lb-section--tone-inverse h2,.lb-section--tone-inverse h3{color:inherit}
		.nordic-lb-hero{border-radius:var(--lb-radius-lg,24px);background:var(--lb-hero-background,linear-gradient(135deg,#f4f6f8 0%,#ffffff 60%,#eef3f8 100%));border:1px solid var(--lb-border-color,#dbe3ea);box-shadow:var(--lb-shadow-lg,0 18px 48px rgba(19,41,61,.08));padding:clamp(22px,4vw,48px)}
		.nordic-lb-hero__inner{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,.9fr);gap:clamp(18px,3vw,36px);align-items:center}
		.nordic-lb-hero__content{min-width:0}
		.nordic-lb-hero__eyebrow{font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:var(--lb-text-muted,#5b7282);margin-bottom:10px}
		.nordic-lb-hero__title{margin:0 0 12px;font-size:var(--lb-hero-title-size,40px);line-height:1.08}
		.nordic-lb-hero__text{margin:0 0 18px;color:var(--lb-text-muted,#5b7282);font-size:18px;line-height:1.45;max-width:52ch}
		.nordic-lb-hero__actions{display:flex;gap:12px;flex-wrap:wrap}
		.nordic-lb-hero__btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 18px;border-radius:999px;text-decoration:none;font-weight:600;line-height:1.1;border:1px solid var(--lb-button-border,transparent);background:var(--lb-button-background,var(--lb-accent-soft,#e8f3f8));color:var(--lb-button-color,var(--lb-accent-color,#2f7aa1))}
		.nordic-lb-hero__btn.disabled,.nordic-lb-hero__btn[aria-disabled="true"]{opacity:.55;pointer-events:none}
		.nordic-lb-hero__media{width:100%;max-width:520px;margin-left:auto}
		.nordic-lb-hero__media img{width:100%;height:auto;display:block;border-radius:var(--lb-radius-md,18px);box-shadow:var(--lb-shadow-md,0 12px 32px rgba(18,36,52,.08))}
		@media (max-width: 991.98px){.nordic-lb-hero__inner{grid-template-columns:minmax(0,1fr)}.nordic-lb-hero__media{max-width:100%;margin-left:0}.lb-columns{grid-template-columns:var(--lb-grid-tablet,var(--lb-grid-desktop,minmax(0,1fr)))}.lb-native-body-layout{grid-template-columns:minmax(0,1fr);gap:20px}.lb-native-body-col{grid-column:1 / -1!important}}
		@media (max-width: 767.98px){.lb-columns{grid-template-columns:var(--lb-grid-mobile,var(--lb-grid-tablet,var(--lb-grid-desktop,minmax(0,1fr))))!important}.lb-section-inner{padding:0 16px}.lb-native-body-layout{gap:16px}.nordic-lb-hero__text{font-size:16px}}
		CSS);
	}

	function landingbuilder_inject_runtime_site_styles(cmsTemplate $template) {
		static $is_injected = false;
		if ($is_injected) {
			return;
		}
		$is_injected = true;

		$css = landingbuilder_get_runtime_site_styles_css();
		if ($css === '') {
			return;
		}

		$template->addToBlock('before_body', '<style>' . $css . '</style>', true);
	}
}
