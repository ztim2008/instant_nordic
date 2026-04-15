function InstylerEditor(app, picker){

    var editor = this, state = 'base';
    var selector, $element;

    var selectorUndoCopy;

    var $pane = app.getPane('editor');
    var $propsList, $fields, $element;

    var stateTabs, propsTabs;

    var fields = InstylerEditorFields();
    var handlers = InstylerEditorHandlers(app, editor);

    /* {pro} */
    var scopeSelector = new InstylerScopeSelector(app);
    var cssEditor = new InstylerCSSEditor(app);
    /* {/pro} */

    function init(){

        $propsList = $('#properties .fields', $pane);

        buildPropertiesFields();

        initTabs();

        $('#input-editor-title', $pane).keyup(function(){
            selector.title = $(this).val();
        });

        $('#btn-show-selector', $pane).click(function(){
            var $button = $(this);
            if ($button.hasClass('active')){
                $button.removeClass('active');
                picker.clearSelection();
            } else {
                $button.addClass('active');
                picker.showSelection($element);
            }
        });

        $('#selector-path', $pane).click(function(e){

            e.preventDefault();

            var $pathLabel = $(this);

            picker.setPickedCallback(function(path, title){

                $pathLabel.text(path);

                selector.path = path;

                app.onSelectorUpdate(selector);

                app.showPane($pane);

            });

            picker.setCancelCallback(function(){
                app.showPane($pane);
            });

            picker.start(selector);

        });

        $('#selector-important').change(function(e){

            var isImportant = $(this).prop('checked');

            selector.is_important = isImportant;

            app.onSelectorUpdate(selector);

        });

        /* {pro} */
        $('#selector-scope', $pane).click(function(e){

            e.preventDefault();

            scopeSelector.start(selector, function(scope, maskPos, maskNeg){

                selector.scope = scope;
                initSettingsFields();

                if (scope == app.SCOPE_CUSTOM){
                    selector.mask_pos = maskPos;
                    selector.mask_neg = maskNeg;
                }

                app.onSelectorScopeUpdate(selector);

            });

        });

        $('#selector-export', $pane).click(function(e){

            e.preventDefault();

            app.exportSelector(selector);

        });

        $('#selector-edit-css', $pane).click(function(e){

            e.preventDefault();

            var defaults = $.extend(true, {}, selector.custom);

            cssEditor.setSaveCallback(function(){
                updateCustomCSSUsed();
                app.showPane($pane);
            });

            cssEditor.setCancelCallback(function(){
                selector.custom = $.extend(true, {}, defaults);
                app.onSelectorUpdate(selector);
                app.showPane($pane);
            });

            cssEditor.start(selector, state);

        });
        /* {/pro} */

    }

    function initTabs(){

        propsTabs = new Tabs($('#properties', $pane));

        stateTabs = new Tabs($('.states', $pane), {
            onChange: function(state){
                editor.setCurrentState(state);
                initPropertiesFields();
                propsTabs.refresh();
            }
        });

    }

    this.setSaveCallback = function(callback){
        $('#btn-editor-save', $pane).off('click').click(function(){
            callback(selector);
        });
    };

    this.setCancelCallback = function(callback){
        $('#btn-editor-cancel', $pane).off('click').click(function(){
            callback(selectorUndoCopy);
        });
    };

    this.getHandlers = function(){
        return handlers;
    };

    function buildPropertiesFields(){

        $.each(fields, function(style, field){

            var tabClass = 'tab-' + field.tab;

            var $prop = $('<li/>').addClass(tabClass).addClass('tab').addClass('field').data('style', style);

            $('<label/>').text(lang.styleNames[style]).appendTo($prop);

            var $value = $('<div/>').addClass('value').appendTo($prop);

            field.handler = new handlers[field.type](function(newValue){

                fieldChangeCallback(state, style, newValue);

            }, field, app);

            field.handler.getInput().appendTo($value);

            $prop.appendTo($propsList);

        });

        $fields = $('.field', $propsList);

        $fields.each(function(){

            var $field = $(this);
            var style = $field.data('style');

            if ($field.hasClass('fixed')){ return; }

            $field.find('label').click(function(){

                if ($field.hasClass('tab-used')){
                    fieldChangeCallback(state, style, null);
                } else {
					var defaultValue = $element.css(style);
					if (defaultValue){
						fields[style].handler.setValue(defaultValue);
					}
                }

                $field.toggleClass('tab-used');

            });

        });

    }

    function fieldChangeCallback(state, style, value){

        if (!selector) { return; }

        if (value === null){
            delete selector.styles[state][style];
        } else {
            selector.styles[state][style] = value;
        }

        app.onSelectorUpdate(selector, {
            state: state,
            style: style,
            value: value
        });

    }

    this.start = function(selectorToEdit, defaultPropertiesTab){

        selector = selectorToEdit;
        $element = app.getPageElement(selector.path);

        selectorUndoCopy = $.extend(true, {}, selectorToEdit);

        this.setCurrentState('base');

        initSettingsFields();
        initPropertiesFields();

        stateTabs.openFirstTab();
        propsTabs.openTab(defaultPropertiesTab);

        app.showPane($pane);

    };

    function initSettingsFields(){

        $pane.find('#selector-title .input').val(selector.title);

		$pane.find('#selector-path').text(selector.path);

        $pane.find('#selector-important').prop('checked', selector.is_important);

        /* {pro} */
        $pane.find('#selector-scope').text(lang.scopes[selector.scope]);
        /* {/pro} */

    }

    function initPropertiesFields(){

        if (!selector) { return; }

        $fields.each(function(){

            var $field = $(this);

            var style = $field.data('style');

            if (!(style in selector.styles[state])) {
                $field.removeClass('tab-used');
                return;
            }

            fields[style].handler.setValue(selector.styles[state][style]);

            $field.addClass('tab-used');

        });

        /* {pro} */
        updateCustomCSSUsed();
        /* {/pro} */

    };

    /* {pro} */
    function updateCustomCSSUsed(){
        var isCustomCSS = false;
        $.each(selector.custom, function(state, css){
            if (css.length > 0) { isCustomCSS = true; }
        });
        $('#custom-css-field', $pane).toggleClass('tab-used', isCustomCSS);
    }
    /* {/pro} */

    function saveSelector(){

        var $input = $('#input-editor-title', $pane);
        var title = $input.val();

        $input.removeClass('error');

        if (!title) {
            $input.addClass('error');
            return;
        }

        selector.title = title;

        app.saveSelector(selector);

    }

    this.setCurrentState = function(value){
        state = value;
    };

    init();

}

/* {pro} */
function InstylerScopeSelector(app){

    var $window = $('#window-scope-select');
    var $maskPosInput = $('#mask-pos', $window);
    var $maskNegInput = $('#mask-neg', $window);
	var $customRadio = $('input[name=scope][value='+app.SCOPE_CUSTOM+']', $window);

    function init(){

        $('textarea', $window).keyup(function(e){
            $customRadio.prop('checked', true);
        });

		$('a.add-current, a.add-nested, a.add-all', $window).click(function(e){
			var url = app.pageURL.startsWith('/') ? app.pageURL.substring(1) : app.pageURL;
			var patterns = {
				all: '*',
				current: url,
				nested: url + '/*'
			};
			var $link = $(this);
			var $input = $link.parents('.mask').find('textarea');
			var masks = $input.val();
			var result = (masks + '\n' + patterns[$link.data('add')]).trim();
			$input.val(result);
			$customRadio.prop('checked', true);
		});

    }

    this.start = function(selector, callback){

        $('input[name=scope][value='+selector.scope+']', $window).prop('checked', true);

        $maskPosInput.val(selector.mask_pos);
        $maskNegInput.val(selector.mask_neg);

        var buttons = {};

        buttons[lang.save] = function(){

            var scope = $('input:checked', $window).val();
            var maskPos = $maskPosInput.val();
            var maskNeg = $maskNegInput.val();

            callback(scope, maskPos, maskNeg);

            $(this).dialog('close');

        };

        buttons[lang.cancel] = function(){
            $(this).dialog('close');
        };

        $window.dialog({
            modal:true,
            width:350,
            buttons: buttons
        });

    };

    init();

}

function InstylerCSSEditor(app){

    var $pane = app.getPane('edit-css');
	var $input = $('#css-editor', $pane);
    var $path = $('#css-path-start', $pane);
    var editor;
    var stateTabs, state = 'base';

    var selector, saveCallback, cancelCallback;

    function init(){

        editor = CodeMirror.fromTextArea($input.get(0));

        $('.CodeMirror', $pane).keyup(function(e){
            selector.custom[state] = editor.getValue();
            app.onSelectorUpdate(selector);
        });

        $('#btn-edit-css-save', $pane).click(function(e){
            e.preventDefault();
            saveCallback(editor.getValue());
        });

        $('#btn-edit-css-cancel', $pane).click(function(e){
            e.preventDefault();
            cancelCallback();
        });

        stateTabs = new Tabs($('.states', $pane), {
            onChange: function(value){
                state = value;
                updateState();
            }
        });

    }

    this.setSaveCallback = function(callback){
        saveCallback = callback;
    };

    this.setCancelCallback = function(callback){
        cancelCallback = callback;
    };

    this.start = function(selectorToEdit, currentState){

        selector = selectorToEdit;
        state = currentState;

        stateTabs.openTab(currentState);

        app.showPane($pane);

        $('.CodeMirror:visible', $pane).each(function(i, el){
            el.CodeMirror.refresh();
        });

    };

    function updateState(){

        if (!selector) { return; }

        var pathName = selector.path + (state == 'base' ? '' : ':'+state);
        $path.text(pathName);

        editor.setValue(selector.custom[state]);

    }

    init();

}

/* {/pro} */