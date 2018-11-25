( function( $ ){

    $( document ).ready( function() {

        $( '.reach-notice' ).each( function(){
            var $el = $( this ),
                $button = $( '<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>' ),
                btnText = commonL10n.dismiss || '';

            // Ensure plain text
            $button.find( '.screen-reader-text' ).text( btnText );
            
            $button.on( 'click.reach-dismiss-notice', function( event ) {
                event.preventDefault();
                
                $el.fadeTo( 100, 0, function() {
                    $el.slideUp( 100, function() {
                        $el.remove();
                    });
                });            

                $.ajax({
                    type: "POST",
                    data: {
                        action : 'reach_dismiss_notice', 
                        notice : $el.data( 'notice' )
                    },
                    dataType: "json",
                    url: ajaxurl,
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
            });

            $el.css( 'position', 'relative' ).append( $button );
        });
    });

} )( jQuery );