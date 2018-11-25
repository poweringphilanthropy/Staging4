( function( $ ){

    var check_terms = function( $form ) {
        var $terms = $form.find( 'input[name=user_confirmation]' ), 
            message = "You must agree to Greeks4Good's Terms of Service + Privacy Policy before you can register.";

        $terms[0].setCustomValidity( message );

        $terms.on( 'change', function() {

            this.setCustomValidity( this.validity.valueMissing ? message : "" );

        });
    };

    var on_date_change = function() {
        var dob = new Date( $('input[name=dob]').val() ),
            today = new Date(),
            years = (today.getFullYear() - dob.getFullYear());
        
        if ( today.getMonth() < dob.getMonth() ||
            today.getMonth() == dob.getMonth() && today.getDate() < dob.getDate()) {
            years--;
        }

        if (years < 13) {
            // $( "#charitable_field_user_email" ).insertBefore( "#charitable_field_first_name" ).addClass( 'odd' ).removeClass( 'even' );
            // $( "#charitable_field_first_name" ).addClass( 'even' ).removeClass( 'odd' );
            $( "#charitable_field_first_name label" ).html( "Child's First Name <abbr class=\"required\" title=\"required\">*</abbr>" );
            $( "#charitable_field_last_name" ).addClass( "hidden" );
            $( "#charitable_field_last_name input" ).prop('required', false);
            $( "#charitable_field_user_email label" ).html( "Parent's Email Address <abbr class=\"required\" title=\"required\">*</abbr>" );
            // $( "#charitable_field_user_login" ).removeClass( "even" ).addClass( "odd" );
                                    
        } else {
            // $( "#charitable_field_user_email" ).insertAfter( "#charitable_field_last_name" );
            $( "#charitable_field_first_name label" ).html( "First Name <abbr class=\"required\" title=\"required\">*</abbr>" );
            $( "#charitable_field_last_name" ).removeClass( "hidden" );
            $( "#charitable_field_last_name input" ).prop('required', true);
            $( "#charitable_field_user_email label" ).html( "Email Address <abbr class=\"required\" title=\"required\">*</abbr>" );
            // $( "#charitable_field_user_login" ).removeClass( "odd" ).addClass( "even" );
            // $( "#charitable_field_user_pass" ).removeClass( "even" ).addClass( "odd" );
        }  
    };

    var setup_datepicker = function( $form ) {
        $form.find( 'input[name=dob]' ).datepicker({            
            changeMonth: true, 
            changeYear: true, 
            yearRange: "-100:+0", 
            maxDate: 0,
            onSelect: function () {         
                on_date_change();                     
            }
        })
        .on( 'change', function() {
            on_date_change();
        });
    };

    var add_volunteers_need_row = function() {
        var $table = $( '#charitable-campaign-volunteers-need tbody' ),
            index = function() {
                var $rows = $table.find( '[data-index]' ), 
                    index = 0;

                if ( $rows.length ) {
                    index = parseInt( $rows.last().data( 'index' ), 10 ) + 1;
                }

                return index;
            }(),
            row = '<tr class="volunteers-need repeatable-field" data-index="' + index + '">'
                + '<td>'
                + '<div class="repeatable-field-wrapper">'
                + '<div class="charitable-form-field odd" style="width: 100%;">'
                + '<label for=suggested_donations_' + index + '>' + CHARITABLE_AMBASSADORS_VARS.volunteers_need_label + 'add</label>'
                + '<input type="text" id="suggested_donations_' + index + '" name="volunteers_need[' + index + '][need]" />'
                + '</div>'
				
                + '<button data-charitable-remove-row="' + index + '" class="remove">x</button>'
                + '</div>'
                + '</td>'
                + '</tr>';                

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
            .find( 'input' ).attr( 'checked', false );
        $element
            .addClass( 'selected' )
            .find( 'input' ).attr( 'checked', true );

        $( '.charitable-recipient-type-fields > div:not(.hidden)' ).addClass( 'hidden' );
        $( '.charitable-recipient-type-fields [data-recipient-type=' + $element.data( 'recipient-type' ) + ']' ).removeClass( 'hidden' );            
    };  

    $( document ).ready( function() {

        var $registration_form = $( '#charitable-registration-form' );

        if ( $registration_form.length ) {

            check_terms( $registration_form );
            setup_datepicker( $registration_form );
            
        }        
		
        $( '.charitable-campaign-form-table' ).on( 'click', '[data-charitable-add-row]', function( event ) {
            var type = $( this ).data( 'charitable-add-row' );

			if('volunteers-need' === type){
				add_volunteers_need_row();
				}

            event.preventDefault();
        });
				
    });

})( jQuery );      