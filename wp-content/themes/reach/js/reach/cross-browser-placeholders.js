/*--------------------------------------------------------
 * Cross-Browser Placeholders
 *
 * Ensure that browsers which don't support the placeholder 
 * attribute will still display the placeholder value as a 
 * preset value inside the element.
---------------------------------------------------------*/
REACH.CrossBrowserPlaceholders = ( function( $ ) {	
	return {
		init : function() {
			var $form_elements = $(':text,textarea');

			// Make sure there are text inputs
			if ( $form_elements.length ) {

				// Only proceed if placeholder isn't supported
				if ( ! ( 'placeholder' in $form_elements.first()[0] ) ) {
					var active = document.activeElement;

					$form_elements.focus( function() {
						if ( $(this).attr('placeholder') != null ) {
							$(this).val('');
							if ( $(this).val() !== $(this).attr('placeholder') ) {
								$(this).removeClass('hasPlaceholder');
							}
						}
					}).blur( function() {
						if ( $(this).attr('placeholder') != null && ($(this).val() === '' || $(this).val() === $(this).attr('placeholder'))) {
							$(this).val($(this).attr('placeholder')).addClass('hasPlaceholder');
						}
					});
					$form_elements.blur();
					$(active).focus();
					$('form').submit(function () {
						$(this).find('.hasPlaceholder').each(function() { $(this).val(''); });
					});
				}
			}
		}
	}
})( jQuery );