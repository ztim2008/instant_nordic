function InstylerSettings(app, closeCallback){

    var list = this;

    var $pane = app.getPane('settings');

    function init(){

		$('#btn-settings-close', $pane).click(function(e){
			e.preventDefault();
			closeCallback();
		});

		$('#selector-export', $pane).click(function(e){
			e.preventDefault();
			app.exportAll();
		});

        $('#import-upload').fileupload({
            dataType: 'json',
            submit: function () {
                app.showLoading();
            },
            always: function (e, data) {

                app.hideLoading();

                if (data.result.success){
                    location.reload();
                    return;
                }

                alert(data.result.error);

            }
        });

    }

    this.show = function(){
        app.showPane($pane);
    };

    init();

}
