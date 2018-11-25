(function($) {
   var PP_Donor_Covers_Fee = {
      init: function() {
         $(document).on('change', '#donor-covers-fee', this.updateDonorCoversFee);
         $('body').on('edd_gateway_loaded', function(){
            $('#update_donor_value').val('yes');
         });
         
      },
      updateDonorCoversFee: function() {
         $checkbox = $(this);
         var donor_selection = $checkbox.is(":checked");
		 if(donor_selection == true){
			 $('#update_donor_value').val('yes');
		 }else{
			 $(document).find('#update_donor_value').val(' ');
			 $(document).find('#update_donor_value').attr('rel' , 'test');
		 }
		 /* --- AJAX --- */
		 var postData = {
			action: 'edd_refresh_cart_based_on_donor_select',
			donor_selection: donor_selection,
		};
		
		$.ajax({
		type: "POST",
		data: postData,
		dataType: "json",
		url: edd_global_vars.ajaxurl,
		xhrFields: {
			withCredentials: true
		},
		success: function (response) {

			$('.edd_cart_subtotal_amount').each(function() {
				$(this).text(response.subtotal);
			});

			$('.edd_cart_tax_amount').each(function() {
				$(this).text(response.taxes);
			});
			
			if(response.is_donor == true){
				$(".covered_fees_amount").text(response.total_fees);
				$(".covered_fees_tr").show();
			}else{
				$(".covered_fees_tr").hide();
			}

			$('.edd_cart_amount').each(function() {
				var total = response.total;
				var subtotal = response.subtotal;

				$(this).text(total);
				$("#edd_purchase_submit .edd_cart_amount").text(float_total);
				var float_total = parseFloat(total.replace(/[^0-9\.-]+/g,""));
				var float_subtotal = parseFloat(subtotal.replace(/[^0-9\.-]+/g,""));
				$(this).attr('data-total', float_total);
				$(this).attr('data-subtotal', float_subtotal);
				
				$("#edd_purchase_submit .edd_cart_amount").attr('data-subtotal' , float_subtotal);
				$("#edd_purchase_submit .edd_cart_amount").attr('data-total' , float_total);
				$body.trigger('edd_quantity_updated', [ response ]);
			});
			
		}
	}).fail(function (data) {
		if ( window.console && window.console.log ) {
			console.log( data );
		}
	});
	
		
		/* --- AJAX --- */
      },
   };
   PP_Donor_Covers_Fee.init();
   
   
function update_item_quantities_custom(event) {

	var $this = $(this),
		quantity = $this.val(),
		key = $this.data('key'),
		download_id = $this.closest('.edd_cart_item').data('download-id'),
		options = $this.parent().find('input[name="edd-cart-download-' + key + '-options"]').val(),
		is_donor = $("#donor-covers-fee").is(":checked");

	var edd_cc_address = $('#edd_cc_address');
	var billing_country = edd_cc_address.find('#billing_country').val(),
		card_state      = edd_cc_address.find('#card_state').val();

	var postData = {
		action: 'edd_update_quantity_custom',
		quantity: quantity,
		download_id: download_id,
		options: options,
		billing_country: billing_country,
		card_state: card_state,
		donor_selection : is_donor
	};

	// edd_discount_loader.show();

	$.ajax({
		type: "POST",
		data: postData,
		dataType: "json",
		url: edd_global_vars.ajaxurl,
		xhrFields: {
			withCredentials: true
		},
		success: function (response) {

			$('.edd_cart_subtotal_amount').each(function() {
				$(this).text(response.subtotal);
			});

			$('.edd_cart_tax_amount').each(function() {
				$(this).text(response.taxes);
			});
			
			if(response.is_donor == true){
				$(".covered_fees_amount").text(response.total_fees);
				$(".covered_fees_tr").show();
			}else{
				$(".covered_fees_tr").hide();
			}
			
			$('.edd_cart_amount').each(function() {
				var total = response.total;
				var subtotal = response.subtotal;

				$(this).text(total);
				$("#edd_purchase_submit .edd_cart_amount").text(float_total);
				var float_total = parseFloat(total.replace(/[^0-9\.-]+/g,""));
				var float_subtotal = parseFloat(subtotal.replace(/[^0-9\.-]+/g,""));
				
				$("#edd_purchase_submit .edd_cart_amount").attr('data-subtotal' , float_subtotal);
				$("#edd_purchase_submit .edd_cart_amount").attr('data-total' , float_total);
				$(this).attr('data-total', float_total);
				$(this).attr('data-subtotal', float_subtotal);

				$body.trigger('edd_quantity_updated', [ response ]);
			});
			
		}
	}).fail(function (data) {
		if ( window.console && window.console.log ) {
			console.log( data );
		}
	});

	return false;
}
$body = $(document.body);
$body.on('change', '.edd-item-quantity-custom', update_item_quantities_custom);


})(jQuery);


