function InstylerPicker(app){

    var picker = this;

    var $paneChoose = app.getPane('choose');
    var $panePicker = app.getPane('selectors');

    var isSelecting = false, selectingMode = 'new', oldMode;

    var $manualInput, $titleInput, $pathsList, $pickerHint;
    var $selection, $selectedElement, pathsList;

    var pickedCallback, cancelCallback;
	
	var premadePicker = new InstylerPremadeListPicker(app);

    function init(){

        $pathsList = $('ul', $panePicker);

		if (!app.getOptions().isPremadeList){
			$('.pick-premade', $paneChoose).remove();
			$('.pick-custom .number', $paneChoose).html('2');
		}

		$('#btn-pick-premade', $paneChoose).click(function(e){
			
			e.preventDefault();
			
			premadePicker.setPickedCallback(function(path, title){
				$titleInput.val(title);
				pick(path);
				app.showPane($panePicker);
			});
			
			premadePicker.setPreviewCallback(function(path){
				picker.clearSelection();
				selectElement(path);
			});
			
			premadePicker.setCancelCallback(function(){
				app.showPane($paneChoose);
			});
			
			premadePicker.start();
			
		});

        $manualInput = $('textarea', $paneChoose);
        $titleInput = $('#input-picker-title', $panePicker);

        $('#btn-picker-manual', $paneChoose).click(function(){
            pickManualSelector();
        });

        $('#btn-picker-select', $panePicker).click(function(){
            submitSelection();
        });

        $('.btn-picker-cancel', $paneChoose).click(function(){
            picker.cancelSelection();
            cancelCallback();
        });

        $('.btn-picker-cancel', $panePicker).click(function(){
            picker.cancelSelection();
            cancelCallback();
        });

        initToolbarButtons();

    }

    function initToolbarButtons(){

        $('#btn-dom-up', $panePicker).click(function(){
            if (!picker.hasSelection()) { return; }
            selectElementChild();
        });

        $('#btn-dom-down', $panePicker).click(function(){
            if (!picker.hasSelection()) { return; }
            selectElementParent();
        });

        $('#btn-dom-parent', $panePicker).click(function(){

			if (!picker.hasSelection()) { return; }

			var $btn = $(this);

			if (selectingMode == 'parent'){
				selectingMode = oldMode;
				$('.selected', $pathsList).click();
				$btn.removeClass('active');
			} else {
				oldMode = selectingMode;
				selectingMode = 'parent';
				picker.showSelection($selectedElement, 'single');
				$btn.addClass('active');
			}

        });

        $('#btn-dom-similar', $panePicker).click(function(){

			if (!picker.hasSelection()) { return; }

			var $btn = $(this);

			if (selectingMode == 'similar'){
				selectingMode = oldMode;
				$('.selected', $pathsList).click();
				$btn.removeClass('active');
			} else {
				oldMode = selectingMode;
				selectingMode = 'similar';
				picker.showSelection($selectedElement, 'single');
				$btn.addClass('active');
			}

        });

        $('#btn-dom-strict', $panePicker).click(function(){

			if (!picker.hasSelection()) { return; }

			var currentIndex = $('.selected', $pathsList).data('index');
			var currentSelector = pathsList[currentIndex];

			if (currentSelector.path.indexOf('>') >= 0){
				currentSelector.path = currentSelector.path.replace(new RegExp('([ ]?)>([ ]?)', 'g'), ' ');				
			} else {
				currentSelector.path = currentSelector.path.replace(new RegExp('([ ]+)', 'g'), ' > ');
			}

			$('li', $pathsList).eq(currentIndex).html(currentSelector.path).click();

        });

    }

    this.bindPageListener = function(){

        $pickerHint = $('<div/>').attr('id', 'picker-hint').hide();
        app.getPageElement('body').append($pickerHint);

        $paneChoose.mouseenter(function(){
            if (!isSelecting) { return; }
            $pickerHint.hide();
        });

        $panePicker.mouseenter(function(){
            if (!isSelecting) { return; }
            $pickerHint.hide();
        });

        app.getPageElement('body *').click(function(e){

			if (!isSelecting) { return; }

            e.stopPropagation();
            e.preventDefault();

            var $element = $(e.target);
            var isStrict = e.ctrlKey ? true : false;

			$pickerHint.hide();

            pick($element, isStrict);

		}).mousemove(function(e){

            if (!isSelecting) { return; }

            var $frame = app.getFrame();
            var $element = $(e.target);

            $pickerHint.css({
                width: $element.outerWidth(),
                height: $element.outerHeight(),
                left: $element.offset().left - $frame.scrollLeft(),
                top: $element.offset().top - $frame.scrollTop()
            }).show();

        });

    };

    this.start = function(selector){

        $manualInput.removeClass('error').val('');
        $titleInput.removeClass('error').val('');

        isSelecting = true;

        if (!selector){

            $('#picker-title', $panePicker).hide();
            $titleInput.show();

            selectingMode = 'new';

            app.showPane($paneChoose);

        }

        if (selector){

            $('#picker-title', $panePicker).show();
            $('#picker-title span', $panePicker).text(selector.title).show();
            $titleInput.hide();

            selectElement(selector.path);

            selectingMode = 'edit';

            app.showPane($panePicker);

        }

    };

    this.setPickedCallback = function(callback){
        pickedCallback = callback;
    };

    this.setCancelCallback = function(callback){
        cancelCallback = callback;
    };

    this.isSelecting = function(){
        return isSelecting;
    };

    this.hasSelection = function(){
        if (!$selectedElement) { return false; }
        return $selectedElement.length > 0;
    };

    function submitSelection(){

        var title = '';

        if (selectingMode === 'new'){

            title = $titleInput.val();

            $titleInput.removeClass('error');

            if (!title) {
                $titleInput.addClass('error');
                return;
            }

        }

        picker.cancelSelection();

        pickedCallback(getSelectedPath().path, title);

    };

    function pickManualSelector(){

        var path = $manualInput.val();

        $manualInput.removeClass('error');

        if (!path) {
            $manualInput.addClass('error');
            return;
        }

        var $element = app.getPageElement(path);

        if (!$element.length || $element.length <= 0) {
            $manualInput.addClass('error');
            return;
        }

        pick(path);

    }

    function getSelectedPath(){
        return pathsList[$('.selected', $pathsList).data('index')];
    };

	function pick($element, isStrict){

		if (selectingMode == 'new' || selectingMode == 'edit'){

            if (!isStrict){
                selectElement($element);
            } else {
                var elementPath = getElementPath($element);
                selectElement(elementPath);
            }

            app.showPane($panePicker);

		}

		if (selectingMode == 'parent'){
			pickParent($element);
		}

		if (selectingMode == 'similar'){
			pickSimilar($element);
		}

	};

    function pickParent($element){

		selectingMode = oldMode;

		$('#btn-dom-parent', $panePicker).removeClass('active');

        var parentPath = getElementPath($element);
        var sourceParentPath = getElementPath($selectedElement.parent());

        if (parentPath == sourceParentPath){
            parentPath = getElementPath($element, true);
        }
		
        var currentSelector = getSelectedPath();

        var resultPath = parentPath + ' ' + currentSelector.path;
        var $resultElement = app.getPageElement(resultPath).eq(0);

        if (!$resultElement || $resultElement.length == 0){
            $('.selected', $pathsList).click();
            return;
        }

        selectElement(resultPath);

	};

    function pickSimilar($pickedElement){

        selectingMode = oldMode;
		$('#btn-dom-similar', $panePicker).removeClass('active');

        var $sharedParent = false;

        $pickedElement.parents().each(function(){

            if ($sharedParent !== false){ return; }

            var $parent = $(this);

            if ($selectedElement.parents($parent[0]).length > 0){
                $sharedParent = $parent;
            }

        });

        var sharedParentPath = getSuggestedSelectors($sharedParent)[0].path;

        var pickedElementNode = $pickedElement.get(0);
        var pickedElementClasses = $pickedElement.attr('class') ? $pickedElement.attr('class').trim().split(/\s+/) : [];
        var pickedTagName = pickedElementNode.tagName.toLowerCase();

        var childPath = false;

        $.each(pickedElementClasses, function(index, className){

            if (childPath !== false) { return; }

            var candidate = pickedTagName + '.' + className;

            if ($selectedElement.is(candidate)){
                childPath = candidate;
            }

        });

        if (childPath === false) { childPath = pickedTagName; }

        selectElement(sharedParentPath + ' ' + childPath);

	};

   function selectElement($elementOrPath){

        var defaultSelectorPath = false;

        var $element = $elementOrPath;

        if (typeof($elementOrPath) === 'string'){
            defaultSelectorPath = $elementOrPath;
            $element = app.getPageElement(defaultSelectorPath);
        }

        if (!$element.length) { return; }

        $selectedElement = $element;

        pathsList = getSuggestedSelectors($element);

        if (defaultSelectorPath){
            pathsList.unshift({
                path: defaultSelectorPath
            });
        }

        $selection = app.getPageElement(pathsList[0].path);

        picker.showSelection();

        $pathsList.empty();

        $.each(pathsList, function(index, pathInfo){

            var $selectorItem = $('<li/>').html(pathInfo.path).data('index', index);

            $selectorItem.click(function(e){

				$('#btn-dom-parent', $panePicker).removeClass('active');

                var $item = $(this);
                var index = $item.data('index');

                var selector = pathsList[index];

                if (e.ctrlKey){

                    var currentIndex = $('li.selected', $pathsList).data('index');
                    var currentSelector = pathsList[currentIndex];

                    if (!selector.parent || !currentSelector.parent){
                        return;
                    }

                    var combinedParent, combinedElement, maxLevel, resultIndex;

                    if (currentSelector.level < selector.level){
                        combinedParent = selector.parent + ' ' + currentSelector.parent;
                        combinedElement = selector.element;
                        maxLevel = selector.level;
                        resultIndex = index;
                    } else if (currentSelector.level > selector.level) {
                        combinedParent = currentSelector.parent + ' ' + selector.parent;
                        combinedElement = currentSelector.element;
                        maxLevel = currentSelector.level;
                        resultIndex = currentIndex;
                    } else {
                        return;
                    }

                    pathsList[resultIndex] = {
                        path: combinedParent + ' ' + combinedElement,
                        parent: combinedParent,
                        element: combinedElement,
                        level: maxLevel
                    };

                    $('li', $pathsList).eq(resultIndex).html(pathsList[resultIndex].path).click();

                    return;

                }

                $('li.selected', $pathsList).removeClass('selected');
                $item.addClass('selected');

                $selection = app.getPageElement(selector.path);
                picker.showSelection();

            });

            $selectorItem.appendTo($pathsList);

        });

        $pathsList.find('li').eq(0).addClass('selected');

    };

    function selectElementChild(){
        selectElement($selectedElement.children().eq(0));
    };

    function selectElementParent(){
        selectElement($selectedElement.parent());
    };

    this.showSelection = function($elements, cursorClass){

        if (!$elements) { $elements = $selection; }

        this.clearSelection();

        var $frame = app.getFrame();
        var $pageBody = app.getPageElement('body');

        $elements.each(function(){

            var $element = $(this);

            if ($element.is(':hidden') || $element.css('visibility') == 'hidden'){ return; }

            var $cursor = $('<span/>').addClass('picker-cursor');

			if (cursorClass){ $cursor.addClass(cursorClass); }

            $cursor.css({
                width: $element.outerWidth(),
                height: $element.outerHeight(),
                left: $element.offset().left - $frame.scrollLeft(),
                top: $element.offset().top - $frame.scrollTop()
            }).show();

            $pageBody.append($cursor);

        });

    };

    this.clearSelection = function(){
		$pickerHint.hide();
        app.getPageElement('body .picker-cursor').remove();
    };

    this.cancelSelection = function(){
        isSelecting = false;
        $pickerHint.hide();
        this.clearSelection();
    };

    function getSuggestedSelectors($element){

        if ($element.length <= 0) return false;

        var selectors = [];
        var classes = $element.attr('class') ? $element.attr('class').trim().split(/\s+/) : [];
        var elementNode = $element.get(0);
        var tagName = elementNode.localName.toLowerCase();

        var level = 1;

        while($element.length){

            var $parent = $element.parent();

            if (!$parent) { break; }

            var parentClasses = $parent.attr('class') ? $parent.attr('class').trim().split(/\s+/) : [];
			var parentNode = $parent.get(0);
            var parentTagName = parentNode.localName;

            if (!parentTagName) { break; }
			
			if (parentTagName == 'html' || parentTagName == 'body') { break; }

            parentTagName = parentTagName.toLowerCase();

            var parentSelectors = [];

			if (parentNode.id){
				parentSelectors.push('#'+parentNode.id);
			}

            $.each(parentClasses, function(index, parentClassItem) {
                parentSelectors.push('.'+parentClassItem);
            });
			
			if (parentSelectors.length == 0){
				parentSelectors.push(parentTagName);
			}

            $.each(parentSelectors, function(index, parentPath) {

                $.each(classes, function(index, classItem) {

                    selectors.push({
                        path: parentPath +' '+ tagName+'.'+classItem,
                        parent: parentPath,
                        element: tagName+'.'+classItem,
                        level: level
                    });

                });

                if (elementNode.id){

                    selectors.push({
                        path: parentPath +' '+ tagName+'#'+elementNode.id,
                        parent: parentPath,
                        element: tagName +'#'+elementNode.id,
                        level: level
                    });

                }

                selectors.push({
                    path: parentPath +' '+ tagName,
                    parent: parentPath,
                    element: tagName,
                    level: level
                });

            });						

            $element = $parent;

            level++;

        }

        $.each(classes, function(index, classItem) {
            selectors.push({
                path: tagName+'.'+classItem,
            });
        });

        if (elementNode.id){
            selectors.push({
                path: tagName+'#'+elementNode.id,
            });
        }

        selectors.push({
            path: tagName
        });

        return selectors;

    };

	function getElementPath($element, excludeSelf){

		var path = [];
		var isFound = false;

		var elementNode = $element.get(0);
		var elementTag = elementNode.tagName.toLowerCase();
		var elementDomSelector = getElementDomSelector($element);

		if (!excludeSelf){

            var $isSingleFound = false;

            var $singleFound = app.getPageElement(elementDomSelector)

			if ($singleFound.length == 1 && $singleFound[0] == $element[0]) {
				return elementDomSelector;
			}

            var $siblings = $element.siblings(elementDomSelector);

			if ($siblings.length > 0){
				elementDomSelector += ':nth-child(' + ($element.parent().children(elementDomSelector).index($element)+1) + ')';
			}

			path.push(elementDomSelector);

		}

		$element.parents().not('html').not('body').each(function(){

			if (isFound) { return; }

			var $parent = $(this);
			var parentDomSelector = getElementDomSelector($parent);
			var $siblings = $parent.siblings(parentDomSelector);

			if ($siblings.length > 0){
				parentDomSelector += ':eq(' + $parent.parent().children(parentDomSelector).index($parent) + ')';
			}

			path.push(parentDomSelector);

			var currentPath = path.slice().reverse().join(' ');

            var $found = app.getPageElement(currentPath);

			if ($found.length == 1 && ($found[0] == $element[0])) {
                console.log($found.length);
				isFound = true;
			}

		});

		path.reverse();

		return path.join(' ');

	};

	function getElementDomSelector($element){

		if (!$element.length || $element.length <= 0){ return false; }

		var elementNode = $element.get(0);
		var elementTag = elementNode.tagName.toLowerCase();
		var elementClasses = elementNode.className;

		if (elementNode.id){
			return '#' + elementNode.id;
		}

		if (elementClasses){
			return elementTag + '.' + elementNode.className.trim().replace(/ /g, '.');
		}

		return elementTag;

	}


    init();

}
