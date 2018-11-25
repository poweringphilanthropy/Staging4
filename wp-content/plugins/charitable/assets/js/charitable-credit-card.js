CHARITABLE = window.CHARITABLE || {};

( function( $ ) {

    /**
     * Closure variable for on_input_card_number event handler.
     *
     * @access  private
     */
    var card_number_prev_val = '';

    /**
     * Input event handler for credit card number. Prevents invalid characters,
     * invalid length, and automatically inserts spaces for readability.
     *
     * @access  private
     */
    var on_input_card_number = function(e) {
        var current_val = e.target.value;
        var is_amex = /^3(4|7)/.test( current_val );
        var max_length = is_amex ? 17 : 19;

        // Record cursor position so we can return it to the proper position after modifying input
        var cursor_position = this.selectionStart;

        // Copy current_val to current_val_formatted, adding and removing spaces where appropriate
        var current_val_formatted = '';
        current_val.split('').forEach(function(char){
            var idx = current_val_formatted.length;

            // Handle American Express cards
            if( is_amex ) {
                if ( ( idx === 4 || idx === 11 ) && char !== ' ' ) {
                    current_val_formatted += ' ';
                    if ( cursor_position === idx + 1 ) {
                        cursor_position++;
                    }
                }
                else if( idx !== 4 && idx !== 11 && char === ' ' ) {
                    return;
                }
            }

            // Handle all other cards
            else {
                if( ( idx + 1 ) % 5 === 0 && char !== ' ' ) {
                    current_val_formatted += ' ';
                    if ( cursor_position === idx + 1 ) {
                        cursor_position++;
                    }
                }
                else if( ( idx + 1 ) % 5 !== 0 && char === ' ' ) {
                    return;
                }
            }

            current_val_formatted += char;
        });

        // If attempted input contains invalid characters, or is invalid length, revert to previous value
        if( /[^\d^\s]/.test(current_val)  ||  current_val.length > max_length ) {
            e.target.value = card_number_prev_val;
            this.setSelectionRange( cursor_position - 1, cursor_position - 1 );
        }

        // Otherwise, update the input field and card_number_prev_val variable with the correctly formatted input
        else {
            card_number_prev_val = e.target.value = current_val_formatted;
            this.setSelectionRange( cursor_position, cursor_position );
        }

    };

    /**
     * Validate the card number using the Luhn algorithm.
     *
     * @return  boolean
     */
    CHARITABLE.Donation_Form.prototype.is_valid_card_number = function() {

        /**
         * Luhn algorithm in JavaScript: validate credit card number supplied as string of numbers
         * @author ShirtlessKirk. Copyright (c) 2012.
         * @license WTFPL (http://www.wtfpl.net/txt/copying)
         */
        // Closure compiled version (updated Feb 11, 2015):
        var luhnChk=function(a){return function(c){for(var l=c.length,b=1,s=0,v;l;)v=parseInt(c.charAt(--l),10),s+=(b^=1)?a[v]:v;return s&&0===s%10}}([0,2,4,6,8,1,3,5,7,9]);

        var cc_number = this.get_cc_number().replace(/ /g,''); // Remove any spaces

        return luhnChk( cc_number );

    };

    /**
     * Validate the card number. If not valid, set an error.
     *
     * @return  boolean
     */
    CHARITABLE.Donation_Form.prototype.validate_card_number = function() {
    
        var require_cc = $( '#charitable-gateway-fields-' + this.get_payment_method() + ' #charitable_field_cc_number' );

        if ( 0 === require_cc.length ) {
            return true;
        }

        if ( false === this.is_valid_card_number() ) {
            this.add_error( CHARITABLE_VARS.error_invalid_cc_number );
            return false;
        }
    
        return true;

    };

    var $body = $( 'body' );

    $body.on( 'charitable:form:initialize', function() {
        $body.on( 'input', 'input[name="cc_number"]', on_input_card_number );
    });

    $body.on( 'charitable:form:validate', function( event, helper ) {
        helper.validate_card_number();
    });

})( jQuery );
