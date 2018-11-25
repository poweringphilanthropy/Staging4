( function($) {

    $(document).ready( function() {

        $( '#charitable-campaign-submission-form' ).on( 'submit', function(){
            var $form = $(this), 
                recipient_type = $form.find( '[name=recipient]:checked' ).val(), 
                $recipient_fields = $( '.charitable-recipient-type-fields [data-recipient-type=' + recipient_type + ']' ), 
                valid = true;

            $recipient_fields.each( function(){
                var $field = $(this).find( 'input,select,textarea' ), 
                    required = $field.data( 'required' );

                if ( ! required ) {
                    return;
                }

                if ( null === $field.val() || '' == $field.val() ) {
                    valid = false;
                }
            });

            if ( ! valid ) {
                $( '<div class="charitable-notice">Please fill out all required fields.</div>' ).insertBefore( '#charitable-campaign-submission-form .charitable-submit-field' );
            }

            return valid;
        });

        $( '.charitable-campaign-recipient-search .select2' ).each( function(){
            var $el = $(this);
            
            $el.select2({
                ajax: {
                    url: CHARITABLE_AMBASSADORS_VARS.ajaxurl,
                    method: 'POST',
                    dataType: 'json',
                    delay: 250,
                    quietMillis: 100,
                    data: function (params) {
                        return {
                            q: params.term,                        
                            action: 'charitable_recipient_search',
                            recipient_type: $el.data( 'recipient-type' )
                        };
                    },
                    success: function( response ) {
                        console.log( response );
                    },
                    processResults: function (data) {
                        return {
                            results: $.map( data, function( item ) {
                                return {
                                    id : item.id,
                                    text : item.text
                                }
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength : 3, 
                templateSelection : function( result ) {
                    return result.text;
                }, 
                templateResult : function( result ) {
                    return result.text;
                }
            });
        });        
    });

})( jQuery );