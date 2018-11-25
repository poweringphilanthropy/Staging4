/*--------------------------------------------------------
 * Accordion
---------------------------------------------------------*/
REACH.Accordion = ( function( $ ){
	return {
		init : function() {
			if ( $.fn.accordion ) {
				$('.accordion').accordion({
					heightStyle: "content"
				});
			}
		}
	}
})( jQuery );

