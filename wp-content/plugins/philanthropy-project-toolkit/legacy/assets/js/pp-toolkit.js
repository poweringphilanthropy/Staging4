(function($){
	PP_Toolkit = function() {
		var self = this;

		self.init = function() {
			self.initLoadMoreTables();
			self.initAutoCloseNotices();
            self.initAutoComplete();
		};

		self.initLoadMoreTables = function(){
			$(document).on('click', 'a.load-more-button', function(e){
                e.preventDefault();

                var container = $(this).closest('.load-more-table-container'),
                    table = container.find('table');

                table.find('.more-tbody').show(); 
                $(this).hide(); 
                
            });
		};

		self.initAutoCloseNotices = function(){
			if($(".pp-notice-wrapper.autoclose").length > 0){
				setTimeout(function(){
					console.log('dasd');
					$(".pp-notice-wrapper.autoclose").fadeOut(300, function() { 
						$(this).remove(); 
					});
				},3000);
			}
		};

        self.initAutoComplete = function(){

            if(!$.fn.autocomplete){
                return;
            }

            $('.autocomplete').each(function(){

                // if( $( this ).data('ui-autocomplete') != undefined ){
                //     return;
                // }

                var _source = JSON.parse($(this).attr('data-source'));

                $( this ).autocomplete({
                  minLength: 0,
                  source: _source,
                  autoFocus: true
                });
            });
        };

		return self;
	}

	var PP_Toolkit_Js;

	$(document).ready(function() {
		if(PP_Toolkit_Js == null) {
			PP_Toolkit_Js = new PP_Toolkit();
			PP_Toolkit_Js.init();
		}
	});
})(jQuery);