/*--------------------------------------------------------
 * Fitvids
---------------------------------------------------------*/
REACH.Fitvids = ( function( $ ){
	return {
		init : function() {
			$( '.fit-video, .video-player' ).fitVids();
		}
	}
})( jQuery );