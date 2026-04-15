function InstylerPremadeListPicker(app){

    var picker = this;

    var $paneList = app.getPane('premade');
	var $list = $('#theme-elements-list', $paneList);
	var isLoaded = false;
	var selectedPath = '', selectedTitle = '';
    var pickedCallback, previewCallback, cancelCallback;

    function init(){

        $('#btn-premade-select', $paneList).click(function(){
			if (selectedPath.length == 0) { return; }
            pickedCallback(selectedPath, selectedTitle);
        });

        $('#btn-premade-cancel', $paneList).click(function(){
            cancelCallback();
        });

    }

    this.start = function(){

		app.showPane($paneList);
		
		$('a.active', $list).removeClass('active');
		
		selectedPath = '', selectedTitle = '';

		if (!isLoaded){
			loadAndBuildList();
			return;
		}

    };

    this.setPickedCallback = function(callback){
        pickedCallback = callback;
    };

    this.setPreviewCallback = function(callback){
        previewCallback = callback;
    };

    this.setCancelCallback = function(callback){
        cancelCallback = callback;
    };

	function loadAndBuildList(){
		
		app.showLoading();
		
		var url = app.getOptions().urls.premade_list;
		
		$.get(url, {}, function(list){
			
			buildList(list);
			
			isLoaded = true;
			
			app.hideLoading();
			
		}, 'json');
		
	}
	
	function buildList(list){
		
		$list.empty();
		
		$.each(list, function(title, data){
			buildListEntry($list, title, data).addClass('opened');
		});
		
	}
	
	function buildListEntry($parent, title, data){
		
		var $item = $('<li/>').appendTo($parent);
		
		if (typeof(data) === 'string'){
			
			var $link = $('<a/>').html(title).attr('href', '#').attr('data-path', data).appendTo($item);
			
			$link.click(function(e){
				
				e.preventDefault();				
				
				var $link = $(this);
				
				$('a.active', $list).removeClass('active');
				
				selectedPath = $link.addClass('active').data('path');
				
				var parentTitle = $item.parent('ul').parent('li').find('span').first().text();
				var selfTitle = $link.text();
				
				selectedTitle = (parentTitle == selfTitle) ? parentTitle : parentTitle + ': ' + selfTitle;
				
				previewCallback(selectedPath);
				
			});
			
			return;
			
		}
		
		$item.addClass('folder');
		
		var $itemLink = $('<a/>').attr('href', '#').appendTo($item);
		
		$itemLink.click(function(e){
			e.preventDefault();
			$(this).parent('li').toggleClass('opened');
		});
		
		$('<i class="fa"></i>').appendTo($itemLink);
		$('<span/>').html(title).appendTo($itemLink);
		
		var $innerList = $('<ul/>').appendTo($item);
		
		$.each(data, function(title, data){
			buildListEntry($innerList, title, data);
		});
		
		$item.appendTo($parent);
		
		return $item;
		
	}
	
    init();

}
