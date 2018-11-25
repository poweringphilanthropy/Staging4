/*--------------------------------------------------------
 * Header Layout
---------------------------------------------------------*/
REACH.HeaderLayout = ( function( $ ) {

    return {
        init : function() {
            if ( REACH_VARS.primary_navigation_offset.length ) {            
                return;
            }

            // We can't calculate the navigation width when on a small screen
            if ( $( window ).width() < 800 ) {
                return;
            }

            var offset_t = $('.site-branding').outerHeight() - 45, // 45px is the height of the nav when it's a single row
                offset_l = $('.site-branding').outerWidth() + 1, 
                stylesheet = ( function(){
                    var style = document.createElement("style");
                    style.appendChild(document.createTextNode(""));
                    document.head.appendChild(style);
                    return style.sheet;
                })();



            stylesheet.insertRule('@media screen and (min-width: 50em) { .site-navigation { margin-top:' + offset_t + 'px; max-width: -webkit-calc(100% - ' + offset_l + + 'px); max-width: -moz-calc(100% - ' + offset_l + 'px); max-width: calc(100% - ' + offset_l + 'px);) } }', 0);

            $.ajax({
                type: "POST",
                data: {
                    action : 'set_primary_navigation_offset', 
                    offset_t : offset_t, 
                    offset_l : offset_l
                },
                dataType: "json",
                url: REACH_VARS.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function ( response ) {
                    console.log( response );
                },
                error: function( error ) {
                    console.log( error );
                }
            }).fail(function ( response ) {
                if ( window.console && window.console.log ) {
                    console.log( response );
                }
            });
        }
    }
})( jQuery );