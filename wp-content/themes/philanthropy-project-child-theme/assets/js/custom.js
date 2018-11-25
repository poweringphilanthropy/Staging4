jQuery(function( $ ){
	
	$(document).ready(function() {
	    $(".campaigns-grid-wrapper").jscroll({
	        autoTrigger: false,
	        nextSelector: 'a.load-more-button',
	        callback: function() {
	            setTimeout(delayedMasonry, 600)
	        }
	    });
	});
	
	function delayedMasonry() {
	    $(".masonry-grid").masonry();
	}
	
	$(document).ready(function(){
		//jQuery(".edd-input.edd-item-quantity option[value='1']").remove();
		$('.campaign-event-ticket-form [type="number"].edd-input.edd-item-quantity').attr({
	       //"max" : 10,        // substitute your own
	       "value" : ''          // values (or variables) here
	    });
	    
		/**
		 * This one causes empty value on quality
		 * changed by @lafif
		 */
	    // $('.edd_download_purchase_form [type="number"].edd-input.edd-item-quantity').attr({
	    //    //"max" : 10,        // substitute your own
	    //    "value" : ''          // values (or variables) here
	    // });
	    
	    $('body').on('click.eddAddToCart', '.edd-add-to-cart', function (e) {
			$(this).find('.edd_download_quantity_wrapper').removeAttr('style');
		});
		/*
		//$('.charitable-edd-connected-download.widget-block .edd_download_quantity_wrapper').removeAttr('style').css('display', 'block');
		$('.edd_download_purchase_form a.edd-add-to-cart').css( 'display', '' );
		$('.edd_download_purchase_form a.edd-add-to-cart').click(function () {
            $('.edd_download_purchase_form .edd_download_quantity_wrapper').removeAttr('style').css('display', 'block');

        });
        */
		
	});
	
});
