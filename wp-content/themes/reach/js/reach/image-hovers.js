/*--------------------------------------------------------
 * Image Hovers
---------------------------------------------------------*/
REACH.ImageHovers = ( function( $ ) {
	return {
		init : function() {
			$('.on-hover').each( function() {
				var $parent = $(this).parent(), 
					$image = $parent.find('img');

				// Set the width and offset of the hover to match the image
				$(this).css({ width : $image.width(), left : $image.position().left });
				
				// Set up the parent, along with its event handlers
				$parent
				.addClass('hover-parent')
				.on( 'mouseover', function() {
					$(this).addClass('hovering');
				})
				.on('mouseout', function() {
					$(this).removeClass('hovering');
				});
			});
		}
	}
})( jQuery );