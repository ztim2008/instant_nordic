function nbhBuildRepeaterControlRenderers() {
    return {
        'repeater-items-panel': function() {
            if (nbhHasCapability('repeaterContent') && nbhHasEntity('items')) {
                return nbhRepeaterEditor();
            }
            return '<div class="nbh-note">Редактор повторяющихся элементов будет подключён следующим этапом, после стабилизации hero runtime.</div>';
        },
        'slider-slides-panel': function() {
            if (nbhHasCapability('hasSlides') && nbhHasEntity('slide')) {
                return nbhRepeaterEditor();
            }
            return '<div class="nbh-note">Слайды пока недоступны: для этой панели нужен managed slider contract с сущностью slide и capability hasSlides.</div>';
        },
        __default: function(panel, bp, control) {
            return '<div class="nbh-note">Панель ' + (control && control.label ? control.label : nbhPanelControlKey(panel)) + ' пока не подключена.</div>';
        }
    };
}