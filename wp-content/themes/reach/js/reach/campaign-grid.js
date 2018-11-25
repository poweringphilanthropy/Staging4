/*--------------------------------------------------------
 * Campaign Grid
---------------------------------------------------------*/
REACH.Grid = ( function( $ ) {

	var $grids = $('.masonry-grid');

	var initGrid = function($grid) {
		$grid.masonry();
	};

	return {

		init : function() {

			if ( $(window).width() > 400 ) {
				$grids.each( function() {
					initGrid( $(this) );
				});
			}
						
		}, 

		getGrids : function() {
			return $grids;
		}, 

		resizeGrid : function() {
			$grids.each( function(){
				initGrid( $(this) );
			})
		}			
	}
})( jQuery );