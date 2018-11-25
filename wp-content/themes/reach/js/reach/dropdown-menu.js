/*--------------------------------------------------------
 * Dropdown Menus
---------------------------------------------------------*/
REACH.DropdownMenus = ( function( $ ){	
	return {
		init : function() {
			$('.menu li')
			.on( 'mouseover', function() {
				$(this).addClass( 'hovering' );
			})
			.on( 'mouseout', function() {
				$(this).removeClass( 'hovering' );
			});
		}
	};
})( jQuery );