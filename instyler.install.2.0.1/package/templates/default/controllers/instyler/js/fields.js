function InstylerEditorFields(){

    return {
        'color': {tab: 'text', type: 'color'},
        'font-size': {tab: 'text', type: 'number', options: {min: 8, max:100}},
        'font-weight': {tab: 'text', type: 'iconset', options: {
            buttons: [
                {icon: 'ban', value: 'normal'},
                {icon: 'check', value: 'bold'},
            ]
        }},
        'font-style': {tab: 'text', type: 'iconset', options: {
            buttons: [
                {icon: 'ban', value: 'normal'},
                {icon: 'check', value: 'italic'},
            ]
        }},
        'text-decoration': {tab: 'text', type: 'iconset', options: {
            buttons: [
                {icon: 'ban', value: 'none'},
                {icon: 'underline', value: 'underline'},
                {icon: 'strikethrough', value: 'line-through'},
            ]
        }},
        'text-align': {tab: 'text', type: 'iconset', options: {
            buttons: [
                {icon: 'align-left', value: 'left'},
                {icon: 'align-center', value: 'center'},
                {icon: 'align-right', value: 'right'},
                {icon: 'align-justify', value: 'justify'},
            ]
        }},
        'text-transform': {tab: 'text', type: 'iconset', options: {
            buttons: [
                {icon: 'ban', value: 'none'},
                {title: 'UP', value: 'uppercase'},
                {title: 'lo', value: 'lowercase'},
                {title: 'Ca', value: 'capitalize'},
            ]
        }},
        'line-height': {tab: 'text', type: 'number', options: {min: 0, max:100}},
        'text-indent': {tab: 'text', type: 'number', options: {min: 0, max:50}},
        'word-spacing': {tab: 'text', type: 'number', options: {min: 0, max:50}},
        'letter-spacing': {tab: 'text', type: 'number', options: {min: 0, max:50}},
        'text-shadow': {tab: 'text', type: 'shadow', options: {no_stretch: true, no_inset: true}},
        'background-color': {tab: 'bg', type: 'color'},
        'background-image': {tab: 'bg', type: 'image'},
        'background-repeat': {tab: 'bg', type: 'list', options: ['repeat', 'repeat-x', 'repeat-y', 'no-repeat']},
        'background-position-x': {tab: 'bg', type: 'number', options: {min: 0, max:100, units: '%'}},
        'background-position-y': {tab: 'bg', type: 'number', options: {min: 0, max:100, units: '%'}},
        'background-attachment': {tab: 'bg', type: 'list', options: ['scroll', 'fixed']},
        'background-size': {tab: 'bg', type: 'list', options: ['auto', 'cover', 'contain']},
        'width': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'min-width': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'max-width': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'height': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'min-height': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'max-height': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'position': {tab: 'size', type: 'list', options: ['static', 'absolute', 'fixed', 'relative']},
        'left': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'right': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'top': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'bottom': {tab: 'size', type: 'number', options: {min: 0, max:100}},
        'z-index': {tab: 'size', type: 'flatnumber'},
        'padding': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
        'padding-top': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
        'padding-bottom': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
        'padding-left': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
        'padding-right': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
        'margin': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
        'margin-top': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
        'margin-bottom': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
        'margin-left': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
        'margin-right': {tab: 'padding', type: 'number', options: {min: 0, max:100}},
		'border-style': {tab: 'border', type: 'list', options: ['none', 'solid', 'dotted', 'dashed', 'inset', 'outset', 'double', 'groove', 'ridge']},
        'border-color': {tab: 'border', type: 'color'},
		'border-width': {tab: 'border', type: 'number', options: {min: 0, max:10}},
		'border-top-width': {tab: 'border', type: 'number', options: {min: 0, max:10}},
		'border-bottom-width': {tab: 'border', type: 'number', options: {min: 0, max:10}},
		'border-left-width': {tab: 'border', type: 'number', options: {min: 0, max:10}},
		'border-right-width': {tab: 'border', type: 'number', options: {min: 0, max:10}},
		'border-radius': {tab: 'border', type: 'number', options: {min: 0, max:30}},
		'border-top-left-radius': {tab: 'border', type: 'number', options: {min: 0, max:30}},
		'border-top-right-radius': {tab: 'border', type: 'number', options: {min: 0, max:30}},
		'border-bottom-left-radius': {tab: 'border', type: 'number', options: {min: 0, max:30}},
		'border-bottom-right-radius': {tab: 'border', type: 'number', options: {min: 0, max:30}},
        'display': {tab: 'display', type: 'list', options: ['none','block','inline','inline-block','inline-table','inline-flex','flex','list-item','run-in','table','table-caption','table-cell','table-column-group','table-column','table-footer-group','table-header-group','table-row','table-row-group']},
        'visibility': {tab: 'display', type: 'list', options: ['hidden','visible','collapse']},
        'opacity': {tab: 'display', type: 'float', options: {inverted: true}},
		'box-shadow': {tab: 'display', type: 'shadow'},
    };

}
