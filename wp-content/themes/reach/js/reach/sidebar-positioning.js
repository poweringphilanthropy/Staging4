/*--------------------------------------------------------
 * Sidebar Positioning
---------------------------------------------------------*/
REACH.SidebarPositioning = ( function( $ ) {
    return {
        init : function() {
            var body_width = $('body').outerWidth(),
                $banner = $( '.banner' ), 
                $sidebar = $( '#secondary' ),
                margin;            

            if ( ! $banner.length || ! $sidebar.length ) {
                return;
            }

            if ( body_width < 785 ) {
                $sidebar[0].style.marginTop = "";
                return;
            }

            $sidebar.css( 'marginTop', $banner.outerHeight() );            
        }
    }
})( jQuery );