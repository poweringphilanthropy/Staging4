;( function( $ ) {

	// Returns all the inputs that are checked, except for the no-download option.
	var get_checked_inputs = function() {
		return $( '.charitable-edd-download-select:checked:not(.select-no-download)' );
	}

	// Returns the no-download input field. 
	var get_no_download_input = function() {
		return $( '.select-no-download' );
	}

	// Returns all checked price inputs. 
	var get_checked_price_inputs = function() {
		return $( '.charitable-edd-connected-download input[data-price]:checked' );
	}

	// Handles click of download box. 
	var process_download_box_click = function( self, e ) {
		var $this		= $(self),
			$checkbox 	= $this.find( '.charitable-edd-download-select' ),
			checked		= ! $checkbox.is( ':checked' );

		$this.toggleClass( 'selected', checked );
		$checkbox.prop( 'checked', checked );

		if ( checked ) {
			update_selected_elements( $this.hasClass( 'no-download' ) );
		}
	}

	// Update any elements that were selected previously. 
	var update_selected_elements = function( ticked_no_download_option ) {

		if ( ticked_no_download_option ) {
			get_checked_inputs().prop( 'checked', false ).parents('.charitable-edd-connected-download').removeClass( 'selected' );
		}
		else {
			get_no_download_input().prop( 'checked', false ).parents('.charitable-edd-connected-download').removeClass( 'selected' );
		}		
	}

	// Set up event handlers on document ready event.
	$( document ).ready( function() {

		$( '.charitable-edd-download-select:checked' ).each( function() {
			$(this).parents('.charitable-edd-connected-download').addClass( 'selected' );
		});

		$( '.charitable-edd-connected-download' ).on( 'click', function( e ) {
			var target = $( e.target );

			// We don't do anything if a variable price option is ticked. 
			if ( false === ( target.is( '.edd-price-option label' ) || target.parent().is( '.edd-price-option label' ) ) ) {
				process_download_box_click( this ); 
			}
		} );

	});

	/**
	 * Check whether a download has been checked.
	 */
	var has_checked_download = function( form ) {
		var fields = form.serializeArray(),
			len = fields.length,
			i = 0;

		for ( i; i < len; i += 1 ) {
			if ( fields[i].name.includes('downloads') ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Make sure that the submitted amount is valid.
	 *
	 * @return  boolean
	 */
	CHARITABLE.Donation_Form.prototype.is_valid_amount = function() {
		return this.get_amount() > 0 ? true : has_checked_download( this.form );
	};

})( jQuery );