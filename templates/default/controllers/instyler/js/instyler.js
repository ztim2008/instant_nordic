function Instyler(options){

    var app = this;

    app.pageURL = options.initialURL;

    app.SCOPE_GLOBAL = 1,
    app.SCOPE_HOME = 2,
    app.SCOPE_CUSTOM = 3;

    app.SCOPE_TYPE_ACTIVE = 1;
    app.SCOPE_TYPE_GLOBAL = 2;
    app.SCOPE_TYPE_INACTIVE = 3;

    var $panel, $frame, $page;
    var list, picker, editor, settings;

    var selectors = [], styleTags = {};

    var exporter = new InstylerExporter(app);

    function init(){

        app.showLoading();

        $frame = $('#page-frame');

        initFrame();
        initUI();

        list = new InstylerList(app);
        picker = new InstylerPicker(app);
        editor = new InstylerEditor(app, picker);

        app.imagesExplorer = new InstylerImagesExplorer(app);

        editor.setSaveCallback(function(selector){
            app.saveSelector(selector);
        });

        editor.setCancelCallback(function(unmodifiedSelector){
            app.revertSelectorChanges(unmodifiedSelector);
            list.show();
        });

		settings = new InstylerSettings(app, function(){
			list.show();
		});

        loadSelectors(function(selectors, images){

            list.buildSelectorsList(selectors);

            openPage();

        });

    }

    function initFrame(){

        $frame.load(function(){
            if (this.contentWindow.location.search.indexOf('instyler_inject=1') < 0){
				if (app.pageURL != '/'){
					app.pageURL = this.contentWindow.location.pathname;
				}
                openPage();
                return;
            }
            onPageLoaded();
        });

    }

    function initUI(){

        $panel = $('#instyler-panel').show();

        $panel.draggable({
            handle: 'header',
            iframeFix: true
        });

        $('.input', $panel).keydown(function(){
            $(this).removeClass('error');
        });

        $('header .settings-button', $panel).click(function(e){
            e.preventDefault();
            settings.show();
        });

        $('header .close-button', $panel).click(function(e){
            e.preventDefault();
            top.location.href = app.pageURL;
        });

    }

    function openPage(){

        app.showLoading();
        app.showPageLoading();

        var frameURL = app.pageURL;

        if (frameURL.indexOf('?') < 0){
            frameURL = frameURL + '?instyler_inject=1';
        } else {
            frameURL = frameURL + '&instyler_inject=1';
        }

        $frame.attr('src', frameURL);

    }

    function onPageLoaded(){

        $page = $frame.contents();

        initPageLinks();

        $.post(options.urls.get_scope_type, {uri: app.pageURL, all: true}, function(result){

            $.each(result.scope_types, function(id, scope_type){
                $.each(selectors, function(index, selector){
                    if (selector.id == id){
                        selector.scope_type = scope_type;
                    }
                });
            });

            list.updateSelectorsScopeTypes(selectors);

            buildSelectorsStyles(function(){
                app.hideLoading();
                app.hidePageLoading();
            });

            picker.bindPageListener();

        }, 'json');

    }

    function initPageLinks(){

        $("a[href^='"+options.host+"'], a[href^='/']", $page).each(function(){
            var $link = $(this);
            if ($link.hasClass('ajax-modal') || $link.parent().hasClass('ajax-modal')) { return; }
            var url = $link.attr('href');
            if (url === options.host) { url = '/'; }
            $link.click(function(e){
                e.preventDefault();
                if (picker.isSelecting()) { return; }
                app.pageURL = url;
                openPage();
            });
        });

        $('nav select', $page).off('change');

    }

    this.getFrame = function(){
        return $frame;
    };

    this.getPane = function(paneId){
        return $('#pane-' + paneId, $panel);
    };

    this.getPageElement = function(path){
        return $(path, $page);
    };

    this.showPane = function($pane){

        $('.pane', $panel).hide();
        $pane.show();

    };

    this.showLoading = function(){
        $('#loading', $panel).show();
    };

    this.hideLoading = function(){
        $('#loading', $panel).hide();
    };

    this.showPageLoading = function(){
        $('#page-loading').show();
    };

    this.hidePageLoading = function(){
        $('#page-loading').hide();
    };

    function loadSelectors(callback){

        $.post(options.urls.load_selectors, {uri: app.pageURL}, function(result){

            selectors = result.selectors;

            $.each(selectors, function(index, selector){
                $.each(selector.styles, function(state, styles){
                    if (Object.keys(styles).length == 0){
                        selector.styles[state] = {};
                    }
                });
            });

            callback(result.selectors, result.images);

        }, 'json');

    };

    function buildSelectorsStyles(callback){

        app.getPageElement('style.instyler-style').remove();
        styleTags = {};

        $.each(selectors, function(index, selector){
            if (selector.scope_type != app.SCOPE_TYPE_INACTIVE && selector.is_enabled){
                removeSelectorCSS(selector);
                buildSelectorCSS(selector);
            }
        });

        if (callback){
            callback();
        }

    }

    this.startSelectorAdding = function(){

        picker.setPickedCallback(function(path, title){
            app.addSelector(path, title);
        });

        picker.setCancelCallback(function(){
            list.show();
        });

        picker.start();

    };

    this.addSelector = function(path, title){

        var selector = {
            path: path,
            title: title,
            styles: {
                base: {},
                hover: {},
                active: {}
            },
            custom: {
                base: '',
                hover: '',
                active: ''
            },
            is_enabled: true,
            is_important: true,
            scope: app.SCOPE_GLOBAL,
            scope_type: app.SCOPE_TYPE_GLOBAL,
            mask_pos: '',
            mask_neg: '',
            width: list.getCurrentDeviceWidth()
        };

        createSelector(selector, function(){
            app.editSelector(selector);
        });

    };

    function createSelector(selector, callback){

        app.showLoading();

        $.post(options.urls.add_selector, {uri: app.pageURL, selector: JSON.stringify(selector)}, function(result){

            app.hideLoading();

            if (!result.success){
                list.show();
                return;
            }

            selector.id = result.id;
            selector.scope_type = result.scope_type;
            selectors.push(selector);

            list.addSelectorToList(selector);

            if (callback) { callback(); }

        }, 'json');

    }

    this.cloneSelector = function(original, width){

        var selector = $.extend(true, {}, original);

        delete selector.id;

        selector.width = width;

        if (width == original.width) { selector.title += ' ' + lang.copy_label; }

        createSelector(selector, function(){
            list.refresh();
        });

    };

    this.editSelector = function(selector){

        var defaultTab = Object.keys(selector.styles.base).length > 0 ? 'used' : 'settings';

		console.log(selector);

        editor.start(selector, defaultTab);

    };

    this.saveSelector = function(selector){

        app.showLoading();

        list.updateSelector(selector);

        $.post(options.urls.save_selector, {uri: app.pageURL, selector: JSON.stringify(selector)}, function(result){

            app.hideLoading();
            list.show();

        }, 'json');

    };

    this.deleteSelector = function(selector){

        $.post(options.urls.delete_selector, {id: selector.id}, function(result){

            if (!result.success){
                return;
            }

            var indexToDelete;

            $.each(selectors, function(index, sel){
                if (sel.id == selector.id){
                    indexToDelete = index;
                }
            });

            selectors.splice(indexToDelete);

            if (selector.id in styleTags){
                styleTags[selector.id].remove();
            }

        }, 'json');

    };

    this.toggleSelector = function(selector){

        var isEnabled = !selector.is_enabled;

        if (isEnabled){
            buildSelectorCSS(selector);
        } else {
            removeSelectorCSS(selector);
        }

        selector.is_enabled = isEnabled;

        app.showLoading();

        $.post(options.urls.toggle_selector, {id: selector.id, is_enabled: isEnabled ? 1 : 0}, function(){
            app.hideLoading();
        }, 'json');

    };

    this.moveSelector = function(old_index, new_index){

        app.showLoading();

        selectors.splice(new_index, 0, selectors.splice(old_index, 1)[0]);

        $.post(options.urls.move, {from: old_index+1, to: new_index+1}, function(){
            buildSelectorsStyles(function(){
                app.hideLoading();
            });
        });

    };

    this.revertSelectorChanges = function (selector){

        $.each(selectors, function(index, sel){
            if (sel.id == selector.id){

                selectors[index] = $.extend(true, sel, selector);

                if (selector.scope_type == app.SCOPE_TYPE_INACTIVE) {
                    removeSelectorCSS(selector);
                } else {
                    buildSelectorCSS(selector);
                }

            }
        });

        this.onSelectorUpdate(selector);

    };

    this.onSelectorUpdate = function(selector){

        if (selector.scope_type != app.SCOPE_TYPE_INACTIVE){
            buildSelectorCSS(selector);
        }

    };

    this.onSelectorScopeUpdate = function(selector){

        app.showLoading();

        $.post(options.urls.get_scope_type, {uri: app.pageURL, selector: JSON.stringify(selector)}, function(result){

            app.hideLoading();

            selector.scope_type = result.scope_type;

            if (selector.scope_type == app.SCOPE_TYPE_INACTIVE) {
                removeSelectorCSS(selector);
            } else {
                buildSelectorCSS(selector);
            }

            list.updateSelectorScopeType(selector);

        }, 'json');

    };

    function buildSelectorCSS(selector){

        var $styleTag;

        var isStyleTagCached = selector.id in styleTags;

        if (isStyleTagCached){

            $styleTag = styleTags[selector.id];

        } else {

            $styleTag = app.getPageElement('style#selector-'+selector.id);

            if ($styleTag.length < 1){

                $styleTag = $('<style/>').addClass('instyler-style').attr('id', 'selector-'+selector.id);

                app.getPageElement('head').append($styleTag);

            }

            styleTags[selector.id] = $styleTag;

        }

        var css = getSelectorCSS(selector);

        $styleTag.empty().html(css);

    }

    function removeSelectorCSS(selector){

        app.getPageElement('style#selector-'+selector.id).remove();

        if (selector.id in styleTags) {
            delete styleTags[selector.id];
        }

    }

    function getSelectorCSS(selector){

        var css = '', media = '';

        if (selector.width>0){
            media = '@media screen and (max-width:' + selector.width + 'px) {\n$}\n';
        }

        $.each(selector.styles, function(state, styles){

            var isStyles = (Object.keys(styles).length > 0);
            var isCustom = selector.custom[state].length > 0;

            if (!isStyles && !isCustom){ return; }

            css += (media?'\t':'') + selector.path;
            if (state == 'hover') { css += ':hover'; }
            if (state == 'active') { css += ':active'; }
            css += '{';

            if (isStyles){
                $.each(styles, function(style, value){
                    css += '\n\t' + (media?'\t':'') + style + ':' + value + (selector.is_important ? ' !important' : '') + ';';
                });
            }

            if (isCustom){
                var customCSS = selector.custom[state];
                if (selector.is_important){
                    customCSS = customCSS.replace(/;/g, ' !important;');
                }
                css += '\n\t' + customCSS.trim().split('\n').join('\n\t');
            };

            css += '\n' + (media?'\t':'') + '}\n';

        });

        if (selector.width>0 && css.length > 0){
            css = media.replace('$', css);
        }

        return css;

    }

    this.exportSelector = function(selector){

        exporter.start(getSelectorCSS(selector));

    };

    this.exportAll = function(){

        var css = '';

        $.each(selectors, function(index, selector){

            if (selector.scope_type == app.SCOPE_TYPE_INACTIVE) { return; }

            css += getSelectorCSS(selector);

        });

        exporter.start(css);

    };

	this.getOptions = function(){
        return options;
    };

    init();

}

function Tabs($tabbedView, options){

    var $tabs = $('.tabs', $tabbedView);
    var value;

    function init(){

        if (!options) { options = {}; }

        $('li', $tabs).click(function(e){

            e.preventDefault();

            var $tab = $(this);

            $('.active', $tabs).removeClass('active');
            $tab.addClass('active');

            var target = $tab.data('target');

            $('.tab', $tabbedView).hide();
            $('.tab-'+target, $tabbedView).show();

            value = target;

            if ('onChange' in options){
                options.onChange(target);
            }

        }).eq(0).click();

    }

    this.getValue = function(){
        return value;
    };

    this.getTabs = function(){
        return $('li', $tabs);
    };

    this.openTab = function(target){
        $('li[data-target='+target+']', $tabs).click();
    };

    this.openFirstTab = function(){
        $('li', $tabs).eq(0).click();
    };

    this.refresh = function(){
        $('.active', $tabs).click();
    };

    init();

}

function InstylerExporter(app){

    var $window = $('#window-export-css');
    var $code = $('.code', $window);

    this.start = function(css){

        $code.html(css);

        var buttons = {};

        buttons[lang.close] = function(){
            $(this).dialog('close');
        };

        $window.dialog({
            modal:true,
            width:600,
            buttons: buttons
        });

    };

}

function InstylerImagesExplorer(app){

    var isLoaded = false;

    var $window = $('#window-images');
    var $imagesList = $('.images-list', $window);
    var $loading = $('#image-loading', $window);
    var $emptyMessage = $('.no-images', $window);
    var $fileUpload;
    var callback;

    var page = 1;

    function init(){

        $fileUpload = $('#fileupload', $window);

        $fileUpload.fileupload({
            dataType: 'json',
            submit: function () {
                $loading.show();
            },
            always: function (e, data) {

                $loading.hide();

                if (!data.result.success){
                    alert(data.result.error);
                    return;
                }

                addImageToList(data.result.image);

            }
        });

    }

    this.start = function(resultCallback){

        callback = resultCallback;

        var buttons = {};

        buttons[lang.upload] = function(){
            $('#fileupload', $window).click();
        };

        buttons[lang.close] = function(){
            $(this).dialog('close');
        };

        $window.dialog({
            modal:true,
            width:695,
            height:570,
            buttons: buttons,
            open: function(){
                if (!isLoaded){
                    loadImages();
                }
            }
        });

    };

    function loadImages(){

        $loading.show();

        $.post(app.getOptions().urls.images, {page: page}, function(result){

            $imagesList.empty();

            if (result.images.length > 0){
                $.each(result.images, function(index, image){
                    addImageToList(image);
                });
            }

            if (!isLoaded){
                $('#pagination', $window).pagination({
                    items: result.total,
                    itemsOnPage: result.perpage,
                    prevText: '<i class="fa fa-caret-left"></i>',
                    nextText: '<i class="fa fa-caret-right"></i>',
                    onPageClick: function(pageNumber){

                        page = pageNumber;
                        loadImages();

                    }
                });
            }

            isLoaded = true;

            $loading.hide();

        }, 'json');

    }

    function addImageToList(image){

        var $imageItem = $('<li/>').css('background-image', 'url("'+image.url+'")').attr('title', image.title);

        if (!image.is_pattern){
            $imageItem.addClass('no-repeat');
        }

        var $actions = $('<div/>').addClass('actions');

        var $selectBtn = $('<button/>').addClass('select').html('<i class="fa fa-check"></i>').appendTo($actions);
        var $deleteBtn = $('<button/>').addClass('delete').html('<i class="fa fa-trash"></i>').appendTo($actions);

        $selectBtn.click(function(e){

            e.preventDefault();

            callback(image.title, image.url);

            $window.dialog('close');

        });

        $deleteBtn.click(function(e){

            e.preventDefault();

            deleteImage($imageItem, image);

        });

        $actions.appendTo($imageItem);

        $imageItem.appendTo($imagesList);

        $emptyMessage.hide();

    }

    function deleteImage($imageItem, image){

        if (!confirm(lang.delete_image)){ return; }

        $imageItem.fadeOut(function(){

            $(this).remove();

            if ($('li', $imagesList).length == 0){
                $emptyMessage.show();
            }

        });

        delete images[image.id];

        $.post(app.getOptions().urls.delete_image, {id: image.id});

    }

    init();

}