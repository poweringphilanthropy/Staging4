( function( $ ) {

    var update_highlight_colour = function( colour ) {
        var $styles = $( '#charitable-highlight-colour-styles' );
        $styles.html("")
            .append( ".campaign-raised .amount, .campaign-figures .amount, .donors-count, .time-left, .charitable-form-field a:not(.button), .charitable-form-fields .charitable-fieldset a:not(.button), .charitable-notice,.charitable-notice .errors a{ color: " + colour + ";}" )
            .append( ".campaign-progress-bar .bar, .donate-button, #charitable-donation-form .donation-amount.selected, #charitable-donation-amount-form .donation-amount.selected { background-color: " + colour + ";}" )
            .append( "#charitable-donation-form .donation-amount.selected, #charitable-donation-amount-form .donation-amount.selected, .charitable-notice { border-color: " + colour + ";}" );
    };    

    wp.customize( 'charitable_settings[highlight_colour]', function( value ) {
        value.bind( function( newval ) {
            update_highlight_colour( newval );
        });
    });

})( jQuery );