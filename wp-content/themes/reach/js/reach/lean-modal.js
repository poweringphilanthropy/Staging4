/*--------------------------------------------------------
 * Lean Modal
---------------------------------------------------------*/
REACH.LeanModal = ( function( $ ){
	return {
		init : function() {
            if ( $.fn.leanModal ) {
                $('[data-trigger-modal]').leanModal({
                    closeButton : ".close-modal"
                });
            }			
		}
	}
})( jQuery );