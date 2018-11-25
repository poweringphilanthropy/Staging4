(function($){
	var pp_edd_admin = {
		init: function() {
			this.initHideShow();
			this.initSelectNonProfit();
			
		},
		initHideShow: function(){

			$(document).on('change', '.hide-show-trigger', function(e){
				e.preventDefault();

				var tag = $(this).tagName,
					type = $(this).attr("type"),
					val = $(this).val();

				if (tag == "input" && (type == "checkbox" || type == "radio")){
					val = $(this).is(":checked") ? $(this).attr('data-val') : '';
				}

				// alert(val);

				$(this).closest('.wrapper-hide-show').find('.hide-show').addClass('hidden');
				$(this).closest('.wrapper-hide-show').find('.hide-show.show-if-'+val).removeClass('hidden');
				
			});

			$('.hide-show-trigger').each(function(){
				$(this).trigger('change');
			});
		},
		initSelectNonProfit: function(){
			// $('.remote-select-non-profit');
		}
	};

	pp_edd_admin.init();

})(jQuery);