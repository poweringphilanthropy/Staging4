/*--------------------------------------------------------
 * Responsive Menus
---------------------------------------------------------*/
REACH.ResponsiveMenu = ( function( $ ) {
	return {
		init : function() {
			var $container = $( '.site-navigation' ), 
				$account_links = $( '.account-links' ),
				$button, 
				$menu;

			if ( ! $container ) {
				return;
			}

			$button = $container.find( '.menu-toggle' );

			if ( ! $button ) {
				return;
			}

			// Append account-links div
			if ( $account_links.length ) {
				$container.find( '.menu-site' ).append( '<div class="account-links">' + $account_links.html() + '</div>' );
			}			

			$menu = $container.find( 'ul' );

			// Hide menu toggle button if menu is empty and return early.
			if ( ! $menu ) {
				$button.hide();
				return;
			}

			$menu.attr( 'aria-expanded', 'false' );

			if ( ! $menu.hasClass( 'nav-menu' ) ) {
				$menu.addClass( 'nav-menu' );
			}
		 
			$button.on( 'click', function() {
				if ( $container.hasClass( 'toggled' ) ) {
					$container.removeClass( 'toggled' );
					$button.attr( 'aria-expanded', 'false' );
					$menu.attr( 'aria-expanded', 'false' );
				} else {
					$container.addClass( 'toggled' );
					$button.attr( 'aria-expanded', 'true' );
					$menu.attr( 'aria-expanded', 'true' );
				}
			});
		}
	}
})( jQuery );