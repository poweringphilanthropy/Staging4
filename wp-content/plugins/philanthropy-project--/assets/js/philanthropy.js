(function($){
	/**
	 * Trigger checkbox on change quantity
	 * @author lafif
	 * @since 1.0
	 */
	// $(document).on('change', '.edd_price_options .edd-item-quantity', function(e){
	// 	e.preventDefault();
	// 	// alert('ok');
	// 	var val = $(this).val(),
	// 		checkbox = $(this).closest('li').find('input[type="checkbox"][data-price]');
		
	// 	if(parseInt(val) > 0){
	// 		checkbox.prop('checked', true);
	// 	} else {
	// 		checkbox.prop('checked', false);
	// 	}
	// });

	var hold = true;
	$(document).on('click', '.charitable-submit-field input[name="preview-campaign"]', function(e){
		if(!hold) return;

		var form = $(this).closest('form'),
			input_post_id = form.find('input[name="ID"]'),
			button = $(this);

		if(input_post_id.val()) return;

		e.preventDefault();

		// debug
		// var xx = (hold) ? 'hold' : 'alse';
		// alert(xx);
		// var input_tit = form.find('input[name="post_title"]');
		// input_tit.val('test');

		var data = {
                'action'    : 'create_dummy_post'
            };

        $.post( CHARITABLE_AMBASSADORS_VARS.ajaxurl, data, function( response ) {
            if(response > 0){
            	input_post_id.val(response);
            	hold = false;
				button.trigger('click');
            }
        });
	});
})(jQuery);