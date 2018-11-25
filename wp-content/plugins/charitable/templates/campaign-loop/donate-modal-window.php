<?php
/**
 * Sets up the modal window for the campaigns in the loop.
 *
 * Override this template by copying it to yourtheme/charitable/campaign-loop/donate-modal-window.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.3.2
 */

$modal_class = apply_filters( 'charitable_modal_window_class', 'charitable-modal' );

wp_print_scripts( 'lean-modal' );
wp_enqueue_style( 'lean-modal-css' );
?>
<div id="charitable-donation-form-modal-loop" style="display: none;" class="<?php echo esc_attr( $modal_class ) ?>">
	<a class="modal-close"></a>
	<div class="donation-form-wrapper"></div>
</div>
<script type="text/javascript">
( function( $ ) {
	var resize_modal = function(){
		$.fn.leanModal( 'resize', $('#charitable-donation-form-modal-loop') );
	};

	$( '[data-trigger-modal]' ).on( 'click', function(){
		var campaign_id = $( this ).data( 'campaign-id' ),
			$wrapper = $( '#charitable-donation-form-modal-loop .donation-form-wrapper' );

		if ( ! campaign_id ) {
			return;
		}

		$wrapper.html( "<img src=\"<?php echo charitable()->get_path( 'assets', false ) ?>/images/charitable-loading.gif\" width=\"60\" height=\"60\" alt=\"<?php esc_attr_e( 'Loading&hellip;', 'charitable' ) ?>\" />" );

		resize_modal();

		$.ajax({
			type: "POST",
			data: {
				action : 'get_donation_form',
				campaign_id : campaign_id
			},
			dataType: "json",
			url: CHARITABLE_VARS.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function ( response ) {
				if ( response.success ) {
					$wrapper.html( response.data );
	
					new CHARITABLE.Donation_Form( $wrapper.find( '#charitable-donation-form' ) );

					CHARITABLE.Toggle();

					resize_modal();                    

					return;
				}

				$wrapper.html( "<?php _e( 'Unfortunately, something went wrong while trying to retrieve the donation form. Please reload the page and try again.', 'charitable' ) ?>" );
			},
			error: function() {
				$wrapper.html( "<?php _e( 'Unfortunately, something went wrong while trying to retrieve the donation form. Please reload the page and try again.', 'charitable' ) ?>" );
			}
		}).fail(function ( response ) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});

		return false;
	});
})( jQuery );
</script>
