;( function( $ ) {

    function update_benefit_amount( event ) {
        var postData = {
            action: 'charitable_edd_update_benefit_amount'
        };

        $.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: edd_global_vars.ajaxurl,
            xhrFields: {
                withCredentials: true
            },
            success: function (response) {
                if ( response ) {
                    $( '.charitable-edd-benefit .benefit-amount' ).html( response.benefit );
                }
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log( data );
            }
        });

        // Re-attach the event handler.
        attach_event();

        return false;        
    };

    function attach_event() {
        $('body').one( 'edd_quantity_updated', update_benefit_amount );
    }

    $(document).on( 'ready', attach_event );    

})( jQuery )