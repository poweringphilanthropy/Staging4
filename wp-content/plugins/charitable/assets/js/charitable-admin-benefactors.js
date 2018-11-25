( function($){
    var add_campaign_benefactor = function( $el ) {
        var idx = parseInt( $el.attr( 'data-campaign-benefactor-add' ) ), 
            $block = $( '#campaign_benefactor__' + idx ), 
            extension = $el.data( 'extension' );

        // The block is already there, so show it.
        $block.show();
        
        idx = idx + 1;

        // Fetch the next block with AJAX.
        $.ajax({
            type: "POST", 
            data: {
                action: 'charitable_add_benefactor',
                idx: idx, 
                extension: extension
            }, 
            url: ajaxurl, 
            xhrFields: {
                withCredentials: true
            }, 
            success: function( response ) {
                $(response).insertBefore( $el.parent() );

                CHARITABLE_ADMIN.SetupDatepicker( $( '#campaign_benefactor__' + idx + ' .charitable-datepicker' ) );
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log( data );
            }
        });

        // Update the data attribute so that we will show the next block next time.
        $el.attr( 'data-campaign-benefactor-add', idx );

        return false;
    };

    var cancel_new_campaign_benefactor = function( $el ) {
        $el.parent().remove();

        return false;
    };

    var delete_campaign_benefactor = function( $el ) {
        var $block = $el.parents( '.charitable-benefactor' ),
            data = {
                action          : 'charitable_delete_benefactor',
                benefactor_id   : $el.data( 'campaign-benefactor-delete' ), 
                nonce           : $el.data( 'nonce' )
            };

        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: ajaxurl,
            xhrFields: {
                withCredentials: true
            },
            success: function (response) {
                if ( response.deleted ) {
                    $block.remove();
                }
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log( 'failure' );
                console.log( data );
            }
        });

        return false;
    };

    $(document).ready( function() {
        $('body').on( 'click', '[data-campaign-benefactor-delete]', function() {
            return delete_campaign_benefactor( $(this) );        
        });

        $('[data-campaign-benefactor-add]').on( 'click', function() {
            return add_campaign_benefactor( $(this) );
        });

        $('body').on( 'click', '.charitable-benefactor-form-cancel', function() {
            return cancel_new_campaign_benefactor( $(this) );
        });
    });
})( jQuery );