<?php

require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockContractNormalizer.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockRenderPayloadBuilder.php';
require_once cmsConfig::get('root_path') . 'system/controllers/nordicblocks/libs/DesignBlockElementRenderer.php';

$design_contract = (isset($block_contract) && is_array($block_contract) && (($block_contract['meta']['blockType'] ?? '') === 'design_block'))
    ? $block_contract
    : NordicblocksDesignBlockContractNormalizer::normalize([
        'id'     => isset($block['id']) ? (int) $block['id'] : 0,
        'type'   => 'design_block',
        'title'  => (string) ($block['title'] ?? 'Design Block'),
        'status' => (string) ($block['status'] ?? 'active'),
        'props'  => is_array($props ?? null) ? $props : [],
    ]);

$design_payload = NordicblocksDesignBlockRenderPayloadBuilder::build($design_contract, [
    'blockId'  => isset($block['id']) ? (int) $block['id'] : 0,
    'blockUid' => isset($block_uid) ? (string) $block_uid : ('block_' . (isset($block['id']) ? (int) $block['id'] : 0)),
]);

$section_tag   = in_array((string) ($design_payload['section']['tag'] ?? 'section'), ['section', 'div'], true) ? (string) $design_payload['section']['tag'] : 'section';
$section_id    = htmlspecialchars((string) ($design_payload['sectionId'] ?? 'nb-design-block'), ENT_QUOTES, 'UTF-8');
$section_name  = htmlspecialchars((string) ($design_payload['section']['name'] ?? 'Design Block'), ENT_QUOTES, 'UTF-8');
$section_style = htmlspecialchars(NordicblocksDesignBlockCssBuilder::buildSectionInlineStyle($design_payload), ENT_QUOTES, 'UTF-8');
$section_css   = (string) ($design_payload['css']['all'] ?? '');
$has_motion_elements = false;

foreach (['desktop', 'tablet', 'mobile'] as $motion_breakpoint) {
    foreach ((array) ($design_payload['flatElements'] ?? []) as $motion_element) {
        $motion_props = is_array($motion_element[$motion_breakpoint]['props'] ?? null) ? $motion_element[$motion_breakpoint]['props'] : [];
        if (in_array((string) ($motion_props['motionTrigger'] ?? 'none'), ['entry', 'scroll'], true)) {
            $has_motion_elements = true;
            break 2;
        }
    }
}

echo '<' . $section_tag . ' id="' . $section_id . '" class="nb-design-block" data-nb-block="design_block" aria-label="' . $section_name . '" style="' . $section_style . '">';
echo '<style>' . $section_css . '</style>';
echo '<div class="nb-design-block__stage">';
echo NordicblocksDesignBlockElementRenderer::render($design_payload);
echo '</div>';
if ($has_motion_elements) {
    $section_js_id = json_encode(htmlspecialchars_decode($section_id, ENT_QUOTES), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo '<script>(function(){var section=document.getElementById(' . $section_js_id . ');var raf=window.requestAnimationFrame||function(cb){return setTimeout(cb,16);};var observer=null;var resizeRaf=0;function getActiveBreakpoint(){if(window.matchMedia&&window.matchMedia("(max-width: 640px)").matches){return"mobile";}if(window.matchMedia&&window.matchMedia("(max-width: 991px)").matches){return"tablet";}return"desktop";}function getMotionValue(el,name,breakpoint){return el.getAttribute("data-"+name+"-"+breakpoint)||"";}function sortForStagger(items){return items.slice().sort(function(a,b){if(Math.abs(a.top-b.top)<=24){return a.left-b.left;}return a.top-b.top;});}function applyStagger(items){var byTrigger={};items.forEach(function(item){if(!byTrigger[item.trigger]){byTrigger[item.trigger]=[];}byTrigger[item.trigger].push(item);});Object.keys(byTrigger).forEach(function(trigger){sortForStagger(byTrigger[trigger]).forEach(function(item,index){item.el.style.setProperty("--nb-design-motion-runtime-delay",String(index*80)+"ms");});});}function syncResponsiveMotion(){var breakpoint=getActiveBreakpoint();var active=[];elements.forEach(function(el){var trigger=getMotionValue(el,"motion-trigger",breakpoint)||"none";var preset=getMotionValue(el,"motion-preset",breakpoint)||"fade-up";el.classList.remove("is-motion-active");el.style.removeProperty("--nb-design-motion-runtime-delay");if(trigger!=="entry"&&trigger!=="scroll"){el.removeAttribute("data-motion-active-trigger");el.removeAttribute("data-motion-active-preset");return;}el.setAttribute("data-motion-active-trigger",trigger);el.setAttribute("data-motion-active-preset",preset);if(window.getComputedStyle(el).display==="none"){return;}active.push({el:el,trigger:trigger,top:el.getBoundingClientRect().top,left:el.getBoundingClientRect().left});});applyStagger(active);return active;}function isVisibleInViewport(el){var rect=el.getBoundingClientRect();var viewportHeight=window.innerHeight||document.documentElement.clientHeight||0;return rect.bottom>0&&rect.top<viewportHeight*0.92;}if(!section){return;}var prefersReduced=window.matchMedia&&window.matchMedia("(prefers-reduced-motion: reduce)").matches;var elements=Array.prototype.slice.call(section.querySelectorAll(".nb-design-el[data-motion]"));if(!elements.length){return;}section.classList.add("nb-design-block--motion-ready");if(prefersReduced){elements.forEach(function(el){var breakpoint=getActiveBreakpoint();var trigger=getMotionValue(el,"motion-trigger",breakpoint)||"none";var preset=getMotionValue(el,"motion-preset",breakpoint)||"fade-up";if(trigger==="entry"||trigger==="scroll"){el.setAttribute("data-motion-active-trigger",trigger);el.setAttribute("data-motion-active-preset",preset);}el.classList.add("is-motion-active");});return;}function activateEntry(items){items.forEach(function(item){if(item.trigger!=="entry"){return;}setTimeout(function(){item.el.classList.add("is-motion-active");},34);});}function observeScroll(items){var scrollItems=items.filter(function(item){return item.trigger==="scroll";});if(observer){observer.disconnect();observer=null;}if(!scrollItems.length){return;}scrollItems.forEach(function(item){if(isVisibleInViewport(item.el)){item.el.classList.add("is-motion-active");}});scrollItems=scrollItems.filter(function(item){return !item.el.classList.contains("is-motion-active");});if(!scrollItems.length){return;}if(!("IntersectionObserver" in window)){scrollItems.forEach(function(item){item.el.classList.add("is-motion-active");});return;}observer=new IntersectionObserver(function(entries){entries.forEach(function(entry){if(entry.isIntersecting||entry.intersectionRatio>0.18){entry.target.classList.add("is-motion-active");observer.unobserve(entry.target);}});},{threshold:0.18,rootMargin:"0px 0px -8% 0px"});scrollItems.forEach(function(item){observer.observe(item.el);});}function run(){var active=syncResponsiveMotion();activateEntry(active);observeScroll(active);}run();window.addEventListener("resize",function(){if(resizeRaf){return;}resizeRaf=raf(function(){resizeRaf=0;run();});});})();</script>';
}
echo '</' . $section_tag . '>';