function InstylerEditorHandlers(app, editor){

    return {

        color: function(valueCallback){

            var handler = this, picker, $input, $preview;

            this.getInput = function(){

                var $wrap = $('<div/>').addClass('input-colorpicker');

                $preview = $('<span/>').addClass('preview').appendTo($wrap);

                $input = $('<input/>').attr('type', 'text').addClass('input').appendTo($wrap);

                picker = $input.colorPicker({
                    doRender: false,
                    renderCallback: function($elm, toggled) {
                        handler.setValue(this.color.toString($elm._colorMode));
                    }
                });

                $preview.click(function(){
                    $input.click();
                });

                return $wrap;

            };

            this.setValue = function(value){
                $preview.css('background-color', value);
                $input.val(value);
                valueCallback(value);
            };

        },

        number: function(valueCallback, field){

            var handler = this, $input, $slider, $unitsToggler;
            var units = 'px';

            this.getInput = function(){

                units = ('units' in field.options) ? field.options.units : 'px';

                var $wrap = $('<div/>').addClass('input-number');

                if ('icon' in field.options){
                    $('<i class="fa fa-'+field.options.icon+'"></i>').appendTo($wrap);
                    $wrap.addClass('with-icon');
                }

                $input = $('<input/>').attr('type', 'text').addClass('input').addClass('input');

                $slider = $('<div/>').addClass('input-slider').appendTo($wrap).slider({
                    range: "min",
                    min: field.options.min,
                    max: field.options.max,
                    slide: function( event, ui ) {
                        $input.val(ui.value).keyup();
                    }
                });

                $input.keyup(function(e){
                    var value = $input.val();
                    if ($.isNumeric(value)){
                        value = parseInt(value);
                        $slider.slider('value', value);
                        handler.setValue(value + units);
                    }
                });

                $input.appendTo($wrap);

                $unitsToggler = $('<button/>').text(units).appendTo($wrap);

                $unitsToggler.click(function(e){

                    e.preventDefault();

                    var $btn = $(this);

                    if (units == 'px') { units = '%'; } else { units = 'px'; }

                    $btn.text(units);

                    $input.keyup();

                });

                return $wrap;
            };

            this.setValue = function(value){

                units = 'px';

                if (value.indexOf('px') >= 0){
                    value = value.replace('px', '');
                }
                if (value.indexOf('%') >= 0){
                    value = value.replace('%', '');
                    units = '%';
                }

                if (!$.isNumeric(value)){
                    value = 0;
                }
                value = parseInt(value);
                valueCallback(value + units);
                $input.val(value);
                $slider.slider('value', value);
            };

        },

        float: function(valueCallback, field){

            var handler = this, $input, $slider;
            var isInverted;

            this.getInput = function(){

                isInverted = ('options' in field) && field.options.inverted;

                var $wrap = $('<div/>').addClass('input-number');

                $input = $('<input/>').attr('type', 'text').addClass('input').addClass('input');

                $slider = $('<div/>').addClass('input-slider').appendTo($wrap).slider({
                    range: "min",
                    min: 0,
                    max: 100,
                    slide: function( event, ui ) {
                        $input.val(ui.value).keyup();
                    }
                });

                $input.keyup(function(e){
                    var value = $input.val();
                    if (!$.isNumeric(value)){ return; }
                    value = parseInt(value);
                    $slider.slider('value', value);
                    if (isInverted) { value = 100 - value; }
                    valueCallback(value/100);
                });

                $input.appendTo($wrap);

				$('<span/>').text('%').appendTo($wrap);

                return $wrap;

            };

            this.setValue = function(value){

                if (!$.isNumeric(value)){
                    value = 0;
                }

                valueCallback(value);

                var percent = Number(value * 100);

                if (isInverted) { percent = 100 - percent; }

                $input.val(percent);
                $slider.slider('value', percent);

            };

        },

        flatnumber: function(valueCallback, field){

            var handler = this, $input;

            this.getInput = function(){

                var $wrap = $('<div/>').addClass('input-number');

                $input = $('<input/>').attr('type', 'text').addClass('input').addClass('input');

                $input.keyup(function(e){
                    var value = $input.val();
                    if ($.isNumeric(value)){
                        value = parseInt(value);
                        handler.setValue(value);
                    }
                });

                $input.appendTo($wrap);

                return $wrap;
            };

            this.setValue = function(value){
                if (!$.isNumeric(value)){
                    value = 0;
                }
                value = parseInt(value);
                valueCallback(value);
                $input.val(value);
            };

        },

        iconset: function(valueCallback, field){

            var handler = this, $buttons;

            this.getInput = function(){

                $buttons = $('<div/>').addClass('input-iconset');

                $.each(field.options.buttons, function(index, button){

                    var $button = $('<button/>').attr('data-value', button.value);

                    if (button.icon){
                        $('<i/>').addClass('fa').addClass('fa-'+button.icon).appendTo($button);
                    }

                    if (button.title){
                        $button.addClass('text').html(button.title);
                    }

                    $button.click(function(){
                        $('.active', $buttons).removeClass('active');
                        $button.addClass('active');
                        valueCallback($button.attr('data-value'));
                    });

                    $button.appendTo($buttons);

                });

                return $buttons;

            };

            this.setValue = function(value){
                $('button[data-value=' + value + ']', $buttons).click();
            };

        },

        image: function(valueCallback, field, app){

            var handler = this, $noneBtn, $selectBtn, $label;

            this.getInput = function(){

                var $wrap = $('<div/>').addClass('input-image');

                $noneBtn = $('<button/>').html('<i class="fa fa-ban"></i>').appendTo($wrap);

                $selectBtn  = $('<button/>').text(lang.select).appendTo($wrap);

                $label = $('<a/>').attr('target', '_blank').addClass('label').appendTo($wrap);

                $noneBtn.click(function(e){
                    e.stopPropagation();
                    $(this).addClass('active');
                    $label.text('');
                    valueCallback('none');
                });

                $selectBtn.click(function(e){
                    e.stopPropagation();
                    app.imagesExplorer.start(function(imageTitle, imageURL){
                        $noneBtn.removeClass('active');
                        $label.text(imageTitle).attr('href', imageURL);
                        valueCallback('url("'+imageURL+'")');
                    });
                });

                return $wrap;

            };

            this.setValue = function(value){

                if (value == 'none'){
                    $noneBtn.addClass('active');
                    $label.text('');
                    return;
                }

                var fileURL = value.replace('url(','').replace(')','').replace(/"/g, '');
                var file = fileURL.substring(fileURL.lastIndexOf('/')+1);

                $noneBtn.removeClass('active');
                $label.text(file).attr('href', fileURL);

            };

        },

        list: function(valueCallback, field){

            var handler = this, $select;

            this.getInput = function(){

                $select = $('<select/>').addClass('input');

                $.each(field.options, function(index, option){

					var title = (option in lang.styleOptions) ? lang.styleOptions[option] : option;

                    var $option = $('<option/>').attr('value', option).text(title);

                    $option.appendTo($select);

                });

                $select.change(function(e){
                    e.preventDefault();
                    handler.setValue($select.val());
                });

                return $select;

            };

            this.setValue = function(value){
				$select.val(value);
                valueCallback(value);
            };

        },

        shadow: function(valueCallback, field){

            var handler = this;
            var $wrap;

            var shadow = {
                'type': {type: 'list', options: ['none', 'outset', 'inset'], default: 'outset'},
                'color': {type: 'color', options: {}, default: '#000'},
                'x': {type: 'number', options: {icon: 'caret-right', min:0, max:50}, default: '5px'},
                'y': {type: 'number', options: {icon: 'caret-down', min:0, max:50}, default: '5px'},
                'blur': {type: 'number', options: {icon: 'paint-brush', min:0, max:100}, default: '5px'},
                'stretch': {type: 'number', options: {icon: 'arrows', min:0, max:100}, default: '0px'},
            };

            this.getInput = function(){

                if ('options' in field){
                    if (field.options.no_stretch){
                        delete shadow.stretch;
                    }
                    if (field.options.no_inset){
                        shadow.type.options.splice(shadow.type.options.indexOf('inset'), 1);
                    }
                }

                $wrap = $('<div/>').addClass('input-shadow');

                var handlers = editor.getHandlers();

                $.each(shadow, function(name, part){

                    part.handler = new handlers[part.type](function(value){

                        updateShadowOption(name, value);

                        if (name === 'type'){
                            $('.shadow-option', $wrap).toggle(value !== 'none');
                        }

                    }, {options: part.options}, app);

                    part.$field = part.handler.getInput().appendTo($wrap);

                    if (name !== 'type'){
                        part.$field.addClass('shadow-option');
                    }

                    part.handler.setValue(part.default);

                });

                return $wrap;

            };

            this.setValue = function(value){

                $.each(shadow, function(name, part){
                    part.value = part.default;
                });

                if (value == 'none'){
                    shadow.type.handler.setValue('none');
                    return;
                }

                var isColorFound = false;

                var colorRE = [
                    /rgb(a?)\(([0-9,\. ]+)\)/,
                    /#([a-zA-Z0-9]+)/
                ];

                $.each(colorRE, function(index, regExp){
                    if (isColorFound) { return; }
                    var colorMatch = value.match(regExp);
                    if (colorMatch){
                        shadow.color.handler.setValue(colorMatch[0]);
                        value = value.replace(colorMatch[0], '');
                        isColorFound = true;
                    }
                });

                var segments = value.trim().split(' ');
                var pxSegments = ['x', 'y', 'blur', 'stretch'];
                var pxCurrent = 0;

                $.each(segments, function(index, segment){

                    if (segment == 'inset') {
                        shadow.type.handler.setValue('inset');
                        return;
                    }

                    if (segment.match(/^([0-9]+)px$/)){
                        shadow[pxSegments[pxCurrent]].handler.setValue(segment);
                        pxCurrent++;
                    }

                });

            };

            function updateShadowOption(option, value){
                shadow[option].value = value;
                var style = buildShadow();
                valueCallback(style);
            }

            function buildShadow(){

                if (shadow.type.value === 'none'){
                    return 'none';
                }

                var result = [];

                $.each(shadow, function(name, part){

                    var value = ('value' in part) ? part.value : part.default;

                    if (name == 'type' && value == 'outset'){
                        value = '';
                    }

                    result.push(value);

                });

                return result.join(' ').trim();

            }

        },

    };
}
