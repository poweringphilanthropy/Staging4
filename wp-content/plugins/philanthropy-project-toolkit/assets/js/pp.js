(function($){
	var PP = {
		initPlugins: function(){

			if($.fn.popup){
				$('.pp-popup').each(function(){

					var thisOpts = $.extend({}, {
						transition: 'all 0.3s',
						blur: false,
						scrolllock: true,
						beforeopen: function(el) {
						    $(el).trigger( "popup:beforeopen" );
					  	},
						onclose: function(el) {
						    $(el).trigger( "popup:close" );
					  	}
					}, $(this).data() );

					$(this).popup(thisOpts);
				});
			}
		},
		resetForm: function(elem){
			var $form = $(elem);

			$form.find('input:text, input:password, input:file, select, textarea').val('');
		    $form.find('input:radio, input:checkbox')
		         .removeAttr('checked').removeAttr('selected');

		    $form.trigger('pp:reset-form');
		},
		init: function(){
			this.initPlugins();
		},
	};

	PP.init();



})(jQuery);

jQuery(window).load(function(){
	jQuery( ".allow_only" ).autocomplete({
	select:  function(event,ui){
		// jQuery(this).val((ui.item ? ui.item.value : ""));
	},
	change: function(event,ui){
	  jQuery(this).val((ui.item ? ui.item.value : ""));
	}
});
});
	