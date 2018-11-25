/*--------------------------------------------------------
 * Fancy Select Elements
---------------------------------------------------------*/
REACH.FancySelect = ( function( $ ){
	return {
		init : function() {
			var $select = $('select'), 
				toggleWrapper = function($el) {
					$el.parent().css('display', $el.css('display'))
				};

			if ( ($select).parent().hasClass('select-wrapper') ) {
				return toggleWrapper;
			}

			$select.wrap('<div class="select-wrapper" />')
			.on('change', function() {
				toggleWrapper($(this))
			});

			$select.each( function() {
				toggleWrapper($(this)); 
			});

			return toggleWrapper;
		}
	}
})( jQuery );