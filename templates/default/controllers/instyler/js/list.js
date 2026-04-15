function InstylerList(app){

    var list = this;

    var $pane = app.getPane('home');
    var $emptyMessage = $('.no-selectors', $pane);
    var $selectorsList = $('.selectors-list', $pane);

    /* {pro} */
    var $clonePicker;
    /* {/pro} */

    var scopesTabs, devicesTabs;
    var width = 0;

    var filter = {
        width: 0,
        scopeType: 0,
        active: false,
        title: ''
    };

    function init(){

        $selectorsList.sortable({
			handle: ".drag",
            start: function(event, ui){
                ui.item.startIndex = $selectorsList.children('li').index(ui.item);
            },
            update: function(event, ui){

                var endIndex = $selectorsList.children('li').index(ui.item);

                console.log(ui.item.startIndex + ' to ' + $selectorsList.children('li').index(ui.item));

                app.moveSelector(ui.item.startIndex, endIndex);

            }
        });

        $('#btn-add-selector', $pane).click(function(){
            app.startSelectorAdding();
        });

        var $titleFilterCancelButton = $('#title-filter .cancel', $pane);
        var $titleFilterInput = $('#input-title-filter', $pane);

        $titleFilterCancelButton.click(function(){

            filter.title = '';
            $titleFilterInput.val('').blur();
            $titleFilterCancelButton.hide();

            list.applyFilter();

        });

        $titleFilterInput.keyup(function(){

            filter.title = $titleFilterInput.val();

            $titleFilterCancelButton.toggle(filter.title.length > 0);

            list.applyFilter();

        });

        scopesTabs = new Tabs($('#scopes', $pane), {
            onChange: function(scopeType){

                filter.active = false;

                if (scopeType == 0){
                    filter.scope = 0;
                    filter.active = true;
                } else if (scopeType == 100){
                    filter.scope = 0;
                } else {
                    filter.scope = scopeType;
                }

                list.applyFilter();

            }
        });

        devicesTabs = new Tabs($('#devices', $pane), {
            onChange: function(deviceWidth){

                width = deviceWidth;
                var frameWidth;

                if (!width) { frameWidth = '100%'; } else { frameWidth = width + 'px'; }

                app.getFrame().animate({width: frameWidth}, 500);

                filter.width = width;
                list.applyFilter();

            }
        });

        /* {pro} */
        buildClonePicker();
        /* {/pro} */

    }

    this.show = function(){
        app.showPane($pane);
    };

    this.getCurrentDeviceWidth = function(){
        return width;
    };

    /* {pro} */
    function buildClonePicker(){

        $clonePicker = $('<div/>').addClass('clone-picker').appendTo($pane);

        devicesTabs.getTabs().each(function(){

            var $deviceTab = $(this);

            var icon = $deviceTab.data('icon');
            var type = $deviceTab.data('type');
            var width = $deviceTab.data('target');
            var title = $deviceTab.attr('title');

            var $button = $('<a/>').addClass('device-'+type).attr('title', title).appendTo($clonePicker);

            $('<i class="fa fa-'+icon+'"></i>').appendTo($button);

            $button.click(function(e){
                e.preventDefault();
                app.cloneSelector($clonePicker.selector, width);
                $clonePicker.hide();
            });

        });

        $clonePicker.mouseleave(function(){
            $clonePicker.hide();
        });

    }
    /* {/pro} */

    this.buildSelectorsList = function(selectors){

        this.show();

        if (!selectors.length){
            $emptyMessage.show();
            return;
        }

        $.each(selectors, function(index, selector){
            list.addSelectorToList(selector);
        });

        $selectorsList.show();

        scopesTabs.refresh();
        devicesTabs.refresh();

    };

    this.addSelectorToList = function(selector){

        $emptyMessage.hide();

        var $item = $('<li/>').attr('id', 'selector-' + selector.id);

        $item.attr('data-width', selector.width);
        $item.attr('data-scope-type', selector.scope_type);

        $('<i/>').addClass('fa').addClass('fa-hashtag').appendTo($item);
        $('<span/>').addClass('title').appendTo($item).text(selector.title);

        var $actions = $('<div/>').addClass('actions').appendTo($item);

        var $dragButton = $('<a/>').addClass('drag').html('<i class="fa fa-arrows"></i>').appendTo($actions);
        var $cloneButton = $('<a/>').addClass('clone').attr('title', lang.clone).html('<i class="fa fa-clone"></i>').appendTo($actions);
        var $toggleButton = $('<a/>').addClass('toggle').attr('title', lang.toggle).html('<i class="fa"></i>').appendTo($actions);
        var $deleteButton = $('<a/>').addClass('delete').attr('title', lang.delete).html('<i class="fa fa-times"></i>').appendTo($actions);

        if (selector.scope_type != app.SCOPE_TYPE_INACTIVE){
            $item.addClass('active');
        }

        if (!selector.is_enabled){
            $item.addClass('disabled');
            $toggleButton.find('.fa').addClass('fa-eye');
        } else {
            $toggleButton.find('.fa').addClass('fa-eye-slash');
        }

        $cloneButton.click(function(e){

            e.stopPropagation();

            if (scopesTabs.getTabs().length == 0){
                app.cloneSelector(selector, 0);
                return;
            }

            var $btn = $(this);
            var btnOffset = $btn.offset();
            var paneOffset = $pane.offset();

            var left = btnOffset.left - paneOffset.left + $btn.width()/2 - $clonePicker.width()/2 + 5;
            var top = btnOffset.top - paneOffset.top - 4;

            $clonePicker.selector = selector;

            $clonePicker.css({
                left: left + 'px',
                top: top + 'px'
            }).show();

        });

        $toggleButton.click(function(e){
            e.stopPropagation();
            $(this).find('.fa').toggleClass('fa-eye').toggleClass('fa-eye-slash');
            app.toggleSelector(selector);
            toggleSelector(selector);
        });

        $deleteButton.click(function(e){
            e.stopPropagation();
            if (!confirm(lang.delete_selector)){ return; }
            app.deleteSelector(selector);
            deleteSelectorFromList(selector);
        });

        $item.click(function(e){
            e.preventDefault();
            app.editSelector(selector);
        });

        $item.appendTo($selectorsList);

        list.onUpdate();

        $selectorsList.show();

    };

    this.updateSelector = function(selector){

        var $selectorItem = $('#selector-'+selector.id, $selectorsList);

        $('.title', $selectorItem).text(selector.title);

    };

    /* {pro} */
    this.updateSelectorScopeType = function(selector){

        var $selectorItem = $('#selector-'+selector.id, $selectorsList);

        $selectorItem.attr('data-scope-type', selector.scope_type);

        if (selector.scope_type == app.SCOPE_TYPE_INACTIVE){
            $selectorItem.removeClass('active');
            scopesTabs.openTab(100);
        } else {
            $selectorItem.addClass('active');
            scopesTabs.openTab(0);
        }

    };

    this.updateSelectorsScopeTypes = function(selectors){

        $.each(selectors, function(index, selector){
            list.updateSelectorScopeType(selector);
        });

        scopesTabs.openFirstTab();

    };
    /* {/pro} */

    function deleteSelectorFromList(selector){

        $('#selector-'+selector.id, $selectorsList).fadeOut(function(){

            $(this).remove();

            if ($selectorsList.children('li:visible').length == 0){
                $selectorsList.hide();
                $emptyMessage.show();
            }

            list.onUpdate();

        });

    }

    function toggleSelector(selector){

        $('#selector-'+selector.id, $selectorsList).toggleClass('disabled');

    }

    this.applyFilter = function(){

        $('li', $selectorsList).hide();

        var filterSelector = '';

        if (filter.title.length > 0){
            filterSelector += ':contains("'+filter.title+'")';
        }

        filterSelector += '[data-width='+filter.width+']';

        if (filter.scope > 0){
            filterSelector += '[data-scope-type='+filter.scope+']';
        }

        if (filter.active){
            filterSelector += '.active';
        }

        var $filteredItems = $('li' + filterSelector);

        $filteredItems.show();

        if ($filteredItems.length==0){
            $emptyMessage.show();
            $selectorsList.hide();
        } else {
            $emptyMessage.hide();
            $selectorsList.show();
        }

        list.onUpdate();

    };

    this.onUpdate = function(){

        if (!devicesTabs) { return; }

        devicesTabs.getTabs().each(function(){

            var $tab = $(this);
            var width = $tab.data('target');

            var countSelector = 'li[data-width='+width+']';

            if (filter.scope > 0){
                countSelector += 'li[data-scope-type='+filter.scope+']';
            }

            var count = $(countSelector).length;

            $tab.find('.dot').toggle(count > 0);

        });

    };

    this.refresh = function(){

        scopesTabs.refresh();
        devicesTabs.refresh();

    };

    init();

}

$.expr[":"].contains = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});