( function( $ ){

    $( document ).ready( function() {

        $( '.charitable-notice.is-dismissible' ).each( function(){
            var $el = $( this ), $button = $el.find( '.notice-dismiss' );

            $button.on( 'click', function( event ) {
                event.preventDefault();           

                $.ajax({
                    type: "POST",
                    data: {
                        action : 'charitable_dismiss_notice', 
                        notice : $el.data( 'notice' )
                    },
                    dataType: "json",
                    url: ajaxurl,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function ( response ) {
                        if ( window.console && window.console.log ) {
                            console.log( response );
                        }
                    },
                    error: function( error ) {
                        if ( window.console && window.console.log ) {
                            console.log( error );
                        }
                    }
                }).fail(function ( response ) {
                    if ( window.console && window.console.log ) {
                        console.log( response );
                    }
                });
            });

            $el.css( 'position', 'relative' );
        });
    });

} )( jQuery );