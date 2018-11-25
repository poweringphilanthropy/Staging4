jQuery(document).ready(function ($) {
	$('#edd-tracking-info-notify-customer').click( function(e) {
		e.preventDefault();
		var spinner = $(this).parent().find('.spinner');
		spinner.css('visibility', 'visible');
		$(this).prop('disabled', 'disabled');

		var payment_id      = $(this).data('payment');
		var nonce           = $('#edd-ti-send-tracking').val();
		var notice_wrapper  = $('.edd-tracking-info-email-message');

		notice_wrapper.hide();
		notice_wrapper.html('').removeClass('edd-ti-success edd-ti-fail');

		var postData = {
			edd_action:   'send-tracking',
			payment_id: payment_id,
			nonce: nonce,
		};

		$.post(ajaxurl, postData, function (response) {
			if ( true == response.success ) {
				notice_wrapper.addClass('edd-ti-success');
				setTimeout(function(){ location.reload(); }, 2000);
			} else {
				notice_wrapper.addClass('edd-ti-fail');
			}
			notice_wrapper.show();
			notice_wrapper.html(response.message);
			spinner.css('visibility', 'hidden');
			$(this).prop('disabled', '');
		}, 'json');

	});
});