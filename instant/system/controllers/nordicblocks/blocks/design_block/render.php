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
    $motion_runtime = <<<'HTML'
<script>(function(){
var section=document.getElementById(__SECTION_ID__);
var raf=window.requestAnimationFrame||function(cb){return setTimeout(cb,16);};
var observer=null;
var resizeRaf=0;
function getActiveBreakpoint(){
    if(window.matchMedia&&window.matchMedia("(max-width: 640px)").matches){return"mobile";}
    if(window.matchMedia&&window.matchMedia("(max-width: 991px)").matches){return"tablet";}
    return"desktop";
}
function getMotionValue(el,name,breakpoint){return el.getAttribute("data-"+name+"-"+breakpoint)||"";}
function getSequenceValue(el,name,breakpoint){return el.getAttribute("data-sequence-"+name+"-"+breakpoint)||"";}
function sortForStagger(items){
    return items.slice().sort(function(a,b){
        if(Math.abs(a.top-b.top)<=24){return a.left-b.left;}
        return a.top-b.top;
    });
}
function applyDefaultStagger(items){
    var byTrigger={};
    items.forEach(function(item){
        if(!byTrigger[item.trigger]){byTrigger[item.trigger]=[];}
        byTrigger[item.trigger].push(item);
    });
    Object.keys(byTrigger).forEach(function(trigger){
        sortForStagger(byTrigger[trigger]).forEach(function(item,index){
            item.el.style.setProperty("--nb-design-motion-runtime-delay",String(index*80)+"ms");
        });
    });
}
function resolveSequenceTrigger(item){
    return item.sequenceTrigger&&item.sequenceTrigger!=="inherit"?item.sequenceTrigger:item.trigger;
}
function applySequenceStagger(groups){
    Object.keys(groups).forEach(function(groupKey){
        var group=groups[groupKey];
        var sorted=group.items.slice().sort(function(a,b){
            if(a.sequenceStep!==b.sequenceStep){return a.sequenceStep-b.sequenceStep;}
            if(Math.abs(a.top-b.top)<=24){return a.left-b.left;}
            return a.top-b.top;
        });
        if(!sorted.length){return;}
        group.items=sorted;
        group.anchorEl=sorted[0].el;
        group.gap=sorted[0].sequenceGap;
        sorted.forEach(function(item){
            item.el.style.setProperty("--nb-design-motion-runtime-delay",String(Math.max(0,item.sequenceStep)*group.gap)+"ms");
        });
    });
}
function activateSequenceGroup(group){
    group.items.forEach(function(item){
        item.el.classList.add("is-motion-active");
    });
}
function deactivateSequenceGroup(group){
    group.items.forEach(function(item){
        item.el.classList.remove("is-motion-active");
    });
}
function buildRuntimeModel(){
    var breakpoint=getActiveBreakpoint();
    var singleItems=[];
    var sequenceGroups={};
    elements.forEach(function(el){
        var trigger=getMotionValue(el,"motion-trigger",breakpoint)||"none";
        var preset=getMotionValue(el,"motion-preset",breakpoint)||"fade-up";
        var sequenceMode=getSequenceValue(el,"mode",breakpoint)||"none";
        var sequenceId=getSequenceValue(el,"id",breakpoint)||"";
        var sequenceStep=parseInt(getSequenceValue(el,"step",breakpoint)||"0",10);
        var sequenceGap=parseInt(getSequenceValue(el,"gap",breakpoint)||"80",10);
        var sequenceTrigger=getSequenceValue(el,"trigger",breakpoint)||"inherit";
        var sequenceReplay=getSequenceValue(el,"replay",breakpoint)||"once";
        var rect;
        var item;
        var groupKey;

        el.classList.remove("is-motion-active");
        el.style.removeProperty("--nb-design-motion-runtime-delay");

        if(trigger!=="entry"&&trigger!=="scroll"){
            el.removeAttribute("data-motion-active-trigger");
            el.removeAttribute("data-motion-active-preset");
            return;
        }

        el.setAttribute("data-motion-active-trigger",trigger);
        el.setAttribute("data-motion-active-preset",preset);

        if(window.getComputedStyle(el).display==="none"){
            return;
        }

        rect=el.getBoundingClientRect();
        item={
            el:el,
            trigger:trigger,
            top:rect.top,
            left:rect.left,
            sequenceStep:isNaN(sequenceStep)?0:sequenceStep,
            sequenceGap:isNaN(sequenceGap)?80:Math.max(0,sequenceGap),
            sequenceTrigger:sequenceTrigger,
            sequenceReplay:sequenceReplay
        };

        if(sequenceMode==="orchestrated"&&sequenceId!==""){
            item.trigger=resolveSequenceTrigger(item);
            if(item.trigger!=="entry"&&item.trigger!=="scroll"){
                item.trigger=trigger;
            }
            groupKey=sequenceId+"::"+item.trigger;
            if(!sequenceGroups[groupKey]){
                sequenceGroups[groupKey]={
                    id:sequenceId,
                    trigger:item.trigger,
                    replay:sequenceReplay,
                    items:[],
                    anchorEl:null,
                    gap:item.sequenceGap
                };
            }
            sequenceGroups[groupKey].items.push(item);
            return;
        }

        singleItems.push(item);
    });

    applyDefaultStagger(singleItems);
    applySequenceStagger(sequenceGroups);

    return {
        singles:singleItems,
        sequences:Object.keys(sequenceGroups).map(function(groupKey){return sequenceGroups[groupKey];})
    };
}
function isVisibleInViewport(el){
    var rect=el.getBoundingClientRect();
    var viewportHeight=window.innerHeight||document.documentElement.clientHeight||0;
    return rect.bottom>0&&rect.top<viewportHeight*0.92;
}
if(!section){return;}
var prefersReduced=window.matchMedia&&window.matchMedia("(prefers-reduced-motion: reduce)").matches;
var elements=Array.prototype.slice.call(section.querySelectorAll(".nb-design-el[data-motion]"));
if(!elements.length){return;}
section.classList.add("nb-design-block--motion-ready");
if(prefersReduced){
    elements.forEach(function(el){
        var breakpoint=getActiveBreakpoint();
        var trigger=getMotionValue(el,"motion-trigger",breakpoint)||"none";
        var preset=getMotionValue(el,"motion-preset",breakpoint)||"fade-up";
        if(trigger==="entry"||trigger==="scroll"){
            el.setAttribute("data-motion-active-trigger",trigger);
            el.setAttribute("data-motion-active-preset",preset);
        }
        el.classList.add("is-motion-active");
    });
    return;
}
function activateEntry(model){
    model.singles.forEach(function(item){
        if(item.trigger!=="entry"){return;}
        setTimeout(function(){item.el.classList.add("is-motion-active");},34);
    });
    model.sequences.forEach(function(group){
        if(group.trigger!=="entry"){return;}
        setTimeout(function(){activateSequenceGroup(group);},34);
    });
}
function observeScroll(model){
    var scrollSingles=model.singles.filter(function(item){return item.trigger==="scroll";});
    var scrollSequences=model.sequences.filter(function(group){return group.trigger==="scroll";});
    if(observer){observer.disconnect();observer=null;}
    scrollSingles.forEach(function(item){
        if(isVisibleInViewport(item.el)){item.el.classList.add("is-motion-active");}
    });
    scrollSingles=scrollSingles.filter(function(item){return !item.el.classList.contains("is-motion-active");});
    scrollSequences.forEach(function(group){
        if(!group.anchorEl){return;}
        if(isVisibleInViewport(group.anchorEl)){
            activateSequenceGroup(group);
            return;
        }
        if(group.replay==="repeat-on-reentry"){
            deactivateSequenceGroup(group);
        }
    });
    scrollSequences=scrollSequences.filter(function(group){
        if(!group.anchorEl){return false;}
        if(group.replay==="repeat-on-reentry"){
            return true;
        }
        return !group.anchorEl.classList.contains("is-motion-active");
    });
    if(!scrollSingles.length&&!scrollSequences.length){return;}
    if(!("IntersectionObserver" in window)){
        scrollSingles.forEach(function(item){item.el.classList.add("is-motion-active");});
        scrollSequences.forEach(function(group){activateSequenceGroup(group);});
        return;
    }
    observer=new IntersectionObserver(function(entries){
        entries.forEach(function(entry){
            var sequenceGroup=entry.target.__nbSequenceGroup||null;
            if(sequenceGroup){
                if(sequenceGroup.replay==="repeat-on-reentry"){
                    if(entry.isIntersecting||entry.intersectionRatio>0.18){
                        activateSequenceGroup(sequenceGroup);
                    }else{
                        deactivateSequenceGroup(sequenceGroup);
                    }
                    return;
                }
                if(!entry.isIntersecting&&entry.intersectionRatio<=0.18){return;}
                activateSequenceGroup(sequenceGroup);
                observer.unobserve(entry.target);
            }else{
                if(!entry.isIntersecting&&entry.intersectionRatio<=0.18){return;}
                entry.target.classList.add("is-motion-active");
                observer.unobserve(entry.target);
            }
        });
    },{threshold:0.18,rootMargin:"0px 0px -8% 0px"});
    scrollSingles.forEach(function(item){
        observer.observe(item.el);
    });
    scrollSequences.forEach(function(group){
        if(!group.anchorEl){return;}
        group.anchorEl.__nbSequenceGroup=group;
        observer.observe(group.anchorEl);
    });
}
function run(){
    var model=buildRuntimeModel();
    activateEntry(model);
    observeScroll(model);
}
run();
window.addEventListener("resize",function(){
    if(resizeRaf){return;}
    resizeRaf=raf(function(){
        resizeRaf=0;
        run();
    });
});
})();</script>
HTML;
    echo str_replace('__SECTION_ID__', $section_js_id, $motion_runtime);
}
echo '</' . $section_tag . '>';