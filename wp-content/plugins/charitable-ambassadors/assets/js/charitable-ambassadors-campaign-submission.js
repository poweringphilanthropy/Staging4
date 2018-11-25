( function($){

    var add_suggested_amount_row = function() {
        var $table = $( '#charitable-campaign-suggested-donations tbody' ),
            row = function() {
                var row = CHARITABLE_AMBASSADORS_VARS.suggested_amount_row,
                    $rows = $table.find( '[data-index]' ), 
                    index = 0;

                if ( $rows.length ) {
                    index = parseInt( $rows.last().data( 'index' ), 10 ) + 1;
                }

                return row.replace( /{index}/g, index );
            }();

        $table.find( '.no-suggested-amounts' ).hide();
        $table.append( row );
    };  

    var remove_table_row = function( index, $button ) {
        var $table = $button.parents( 'table' ), 
            $row = $table.find( 'tr[data-index=' + index + ']' );

        $row.remove();
    };

    var set_recipient_type = function( $element ) {
        $( '.charitable-recipient-type.selected' )
            .removeClass( 'selected' )
            .find( 'input' ).prop( 'checked', false );
        $element
            .addClass( 'selected' )
            .find( 'input' ).prop( 'checked', true );

        $( '.charitable-recipient-type-fields > div:not(.hidden)' ).addClass( 'hidden' );
        $( '.charitable-recipient-type-fields [data-recipient-type=' + $element.data( 'recipient-type' ) + ']' ).removeClass( 'hidden' );            
    };  

    $(document).ready( function(){

        $( '.charitable-campaign-form-table' ).on( 'click', '[data-charitable-add-row]', function( event ) {
            var type = $( this ).data( 'charitable-add-row' );

            if ( 'suggested-amount' === type ) {
                add_suggested_amount_row();
            }

            event.preventDefault();
        });

        $( '.charitable-campaign-form-table' ).on( 'click', '[data-charitable-remove-row]', function( event ) {
            
            var index = $( this ).data( 'charitable-remove-row' );
            
            remove_table_row( index, $(this) );

            event.preventDefault();
        });

        $( '.charitable-recipient-type input[type=radio]' ).addClass( 'hidden' );
        $( '.charitable-recipient-type' ).on( 'click', function( event ) {
            set_recipient_type( $(this) );
        });
    });

})( jQuery );