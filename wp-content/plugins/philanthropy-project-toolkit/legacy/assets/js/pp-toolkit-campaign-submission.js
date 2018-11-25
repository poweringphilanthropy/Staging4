(function($){
	var dateToday = new Date();
	$('input[name=post_date]').datepicker({ minDate:dateToday,maxDate:dateToday, dateFormat : 'MM d, yy'}).attr('readonly','readonly');  
	$("input[name=post_date]").datepicker("setDate", new Date());
	
	
	var max_length = 365,
                min_length = 7,
                start_date = new Date(),
                elapsed = ( start_date.getTime() - Date.now() ) / ( 1000 * 60 * 60 * 24 ), 
                min_end = Math.ceil( elapsed + min_length ), 
                max_end = Math.ceil( elapsed + max_length ),
                $end_date_field = $( 'input[name=end_date]' );

            if ( false === $end_date_field.hasClass( 'hasDatepicker' ) ) {
                // $end_date_field.datepicker();   
            }

            $end_date_field
                .datepicker( { minDate:min_end , maxDate : max_end  });
                // .datepicker( "option", "maxDate", max_end );
							
	var get_next_table_index = function( $table ) {
        var $rows = $table.find( '[data-index]' ), 
            index = 0;

        if ( $rows.length ) {
            index = parseInt( $rows.last().data( 'index' ), 10 ) + 1;
        }

        return index;
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
            row = '<tr class="volunteers-need repeatable-fieldx" data-index="' + index + '">'
                + '<td colspan="3">'
                + '<div class="repeatable-field-wrapper">'
                + '<div class="charitable-form-field odd" style="width: 100%;">'
                + '<label for=volunteers_' + index + '_need>Task Description</label>'
                + '<input type="text" id="volunteers_' + index + '_need" name="volunteers[' + index + '][need]" />'
                + '</div>'
				
                + '<button data-pp-charitable-remove-row="' + index + '" class="remove">x</button>'
                + '</div>'
                + '</td>'
                + '</tr>';                

        $table.find( '.no-suggested-amounts' ).hide();
        $table.append( row );
        update_add_more_button( $table.closest('.pp-fundraising-table') );
    };  

	var add_volunteers_need_admin_row = function() {

		var $table = $( '#charitable-campaign-volunteers-need tbody' ),

			index = function() {

				var $rows = $table.find( '[data-index]' ), 

					index = 0;



				if ( $rows.length ) {

					index = parseInt( $rows.last().data( 'index' ), 10 ) + 1;

				}



				return index;

			}(),

			row = '<tr data-index="' + index + '">'

				+ '<td><input type="text" id="campaign_suggested_donations_' + index + '" name="volunteers[' + index + '][need]" placeholder="' + CHARITABLE.volunteers_need_placeholder + '" />'

				+ '</tr>';



		$table.find( '.no-suggested-amounts' ).hide();

		$table.append( row );

	};



    var add_donation_levels_row = function() {
        var $table = $( '#charitable-campaign-suggested-donations tbody' ),
            row = function() {
                var row = PP_CAMPAIGN_SUBMISSION.donation_levels_row,
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



    var add_sponsors_row = function() {
        var $table = $( '#charitable-campaign-sponsors tbody' ),
            row = function() {
                var row = PP_CAMPAIGN_SUBMISSION.sponsors_row,
                    $rows = $table.find( '[data-index]' ), 
                    index = 0;

                if ( $rows.length ) {
                    index = parseInt( $rows.last().data( 'index' ), 10 ) + 1;
                }

                return row.replace( /{index}/g, index );
            }();

        // $table.find( '.no-suggested-amounts' ).hide();
        $table.append( row );

        update_add_more_button( $table.closest('.pp-fundraising-table') );

        $('body').trigger('new_fundraising_table_added');
    };  



    var add_referrer_row = function() {
        $( '#charitable-campaign-referrer tbody tr.referrer.hidden' ).removeClass('hidden');

        // $('body').trigger('new_fundraising_table_added');
    };  

    

    var add_variable_price_row = function( $el ) {
        var $tbody = $el.parents( '.pp-edd-variable-prices' ).find( 'tbody' ),
            index = get_next_table_index( $tbody ),
            identifier = $el.data( 'form-identifier' ),
            row = '<tr class="variable-price repeatable-fieldx" data-index="' + index + '">'
                + '<td>'
                + '<div class="repeatable-field-wrapper">'
                + '<div class="charitable-form-field three-columns">'
                + '<label for="merchandise_' + identifier + '_variable_prices_' + index + '_name">Size/Style Options</label>'
                + '<input type="text" id="merchandise_' + identifier + '_variable_prices_' + index + '_name" name="merchandise[' + identifier + '][variable_prices][' + index + '][name]" placeholder="Small, Red or One-Size-Fits-All" />'
                + '</div>'
                + '<div class="charitable-form-field three-columns">'
                + '<label for="merchandise_' + identifier + '_variable_prices_' + index + '_amount">Price</label>'
                + '<input type="number" id="merchandise_' + identifier + '_variable_prices_' + index + '_amount" name="merchandise[' + identifier + '][variable_prices][' + index + '][amount]" min="0" step="0.01" />'
                + '</div>'
                + '<div class="charitable-form-field three-columns">'
                + '<label for="merchandise_' + identifier + '_variable_prices_' + index + '_purchase_limit">Quantity Available</label>'
                + '<input type="number" id="merchandise_' + identifier + '_variable_prices_' + index + '_purchase_limit" name="merchandise[' + identifier + '][variable_prices][' + index + '][purchase_limit]" min="-1" />'
                + '</div>'
                + '<button data-pp-charitable-remove-row="' + index + '" class="remove">x</button>'
                + '</div>'
                + '</td>'
                + '</tr>';                

        $tbody.append( row );

        update_add_more_button( $tbody.closest('.pp-fundraising-table') );

        console.trace();
    };  

    var remove_table_row = function( index, $button ) {
        var $table = $button.parents( 'table' ).first(), 
            $row = $table.find( 'tr[data-index=' + index + ']' );

        $row.remove();
    };

    var setup_toggle_price_fields = function() {
        $( '.toggle-variable-pricing input[type=checkbox]' ).each( function() {
            toggle_price_fields( $( this ) );
        });
    }; 

    var toggle_price_fields = function( $el ) {
        var variable_pricing_on = $el.is( ':checked' );
        
        $el.parent().nextAll( '.variable-pricing' ).toggleClass( 'hidden', ! variable_pricing_on );
        $el.parent().nextAll( '.standard-pricing' ).toggleClass( 'hidden', variable_pricing_on );
    }; 

    var add_merchandise_form_row = function( $el ) {
        var $table = $( '#pp-merchandise' ),            
            index = get_next_table_index( $table ),            
            data = {
                'action'    : 'add_merchandise_form', 
                'index'     : index, 
                'nonce'     : $el.data( 'nonce' )
            };

        $table.addClass( 'loading' );

        $.post( CHARITABLE_AMBASSADORS_VARS.ajaxurl, data, function( response ) {
            $table.removeClass( 'loading' );
			$table.find( 'tbody .loading-row' ).before( response );
			
			$table.find('.charitable-drag-drop').each( function() {
                new CHARITABLE.Uploader( $(this) );
            });

            $('body').trigger('new_merchandise_form_row_added');

            $('body').trigger('new_fundraising_table_added');

            update_add_more_button( $table );
        });
        
    };

    var add_event_form_row = function( $el ) {
        var $table = $( '#pp-event' ),            
            index = get_next_table_index( $table ),            
            data = {
                'action'    : 'add_event_form', 
                'index'     : index, 
                'nonce'     : $el.data( 'nonce' )
            };

        $table.addClass( 'loading' );

        $.post( CHARITABLE_AMBASSADORS_VARS.ajaxurl, data, function( response ) {
            $table.removeClass( 'loading' );
            $table.find( '> tbody > .loading-row' ).before( response );
            
            $table.find('.charitable-drag-drop').each( function() {
                new CHARITABLE.Uploader( $(this) );
            });

            update_add_more_button( $table );

            $('body').trigger('new_fundraising_table_added');
        });
    };

    var setup_event_handlers = function() {
        $( '#pp-event' )
        .on( 'change', 'input[name=all_day]', function() {
            var is_checked = $( this ).is( ':checked' );

            $( '#charitable_field_start_date_time, #charitable_field_end_date_time' ).find( '.at-time, select' ).toggleClass( 'hidden', is_checked );
        })
        
        .on( 'change', '.datepicker_start_date_time', function() {
            var $end_date = $(this).parents( 'fieldset' ).find( '.datepicker_end_date_time' );

            // Set the min date to the start date.
            $end_date.datepicker( 'option', 'minDate', $(this).val() );
            
            // Set the end date min date and default date in two cases:
            // 1. No end date set yet
            // 2. The end date is set, but it's before the start date
            if ( null === $end_date.datepicker( 'getDate' ) 
                || $(this).datepicker( 'getDate' ) > $end_date.datepicker( 'getDate' ) ) { 

                // The first call to datepicker() is to initialize the datepicker first. 
                $end_date.datepicker().datepicker( 'setDate', $(this).val() );
            }        
        });
    };  

    var add_ticket_form_row = function( $el ) {
        var $table = $el.parents( '.pp-tickets' ),
            $loading_el = $table.find( 'tbody tr.loading-row' ),
            index = get_next_table_index( $table ),            
            data = {                
                'action'    : 'add_ticket_form', 
                'event_id'  : $el.data( 'event-id' ),
                'namespace' : $el.data( 'namespace' ),
                'index'     : index, 
                'nonce'     : $el.data( 'nonce' )
            };

        $table.addClass( 'loading' );

        $.post( CHARITABLE_AMBASSADORS_VARS.ajaxurl, data, function( response ) {
            $table.removeClass( 'loading' );
            $(response).insertBefore($loading_el);
            
            // $table.find( 'tbody' ).prepend( response );

            update_add_more_button( $table );
        });


    };

    var update_add_more_button = function( table ){
        var index = get_next_table_index(table);
        var add_more = table.find('.add-more');

        // alert(index);
        if(index <= 0){
            add_more.addClass('hide');
        } else {
            add_more.removeClass('hide');
        }
    };

    var toggle_payment_details = function( $el ) {
        var selected = $el.val(), 
            $container = $( '#charitable_field_funds_recipient' );

        if ( 'non-profit' === selected ) {
            $container.siblings( '.toggle-non-profit' ).removeClass( 'hidden' );
            $container.siblings( '.toggle-direct' ).addClass( 'hidden' );
        }
        else {
            $container.siblings( '.toggle-non-profit' ).addClass( 'hidden' );
            $container.siblings( '.toggle-direct' ).removeClass( 'hidden' );
        }
    };

    $(document).ready( function(){

        setup_toggle_price_fields();
        $('body').on('new_merchandise_form_row_added', function(){
            setup_toggle_price_fields();
        });

        $( 'body' ).on( 'change', '.toggle-variable-pricing input[type=checkbox]', function() {
            toggle_price_fields( $(this ) );
        });

        // Set up payment fields
        $( '[name=funds_recipient]' ).on( 'change', function() {
            toggle_payment_details( $(this) );
        })
        .trigger( 'change' );

        // Add form rows dynamically
        $( 'body' )
        .on( 'click', '[data-charitable-add-row]', function( event ) {
            var type = $( this ).data( 'charitable-add-row' ),
                need_scroll = false;

            switch ( type ) {

                case 'donation-levels' :
                    add_donation_levels_row( $( this ) );
                    update_add_more_button( $(this).closest('.pp-fundraising-table') );
                    need_scroll = true;
                    break;

                case 'variable-price' : 
                    add_variable_price_row( $( this ) );
                    break;

                case 'merchandise-form' : 
                    add_merchandise_form_row( $( this ) );
                    need_scroll = true;
                    break;

                case 'event-form' : 
                    add_event_form_row( $( this ) );
                    need_scroll = true;
                    break;

                case 'ticket-form' : 
                    add_ticket_form_row( $( this ) );
                    break;

                case 'volunteers-need' :
                    add_volunteers_need_row( $( this ) );
                    need_scroll = true;
                    break;

                case 'sponsors' :
                    add_sponsors_row( $( this ) );
                    need_scroll = true;
                    break;

                case 'referrer' :
                    add_referrer_row( $( this ) );
                    need_scroll = true;
                    break;

                case 'volunteers-admin' :
                    add_volunteers_need_admin_row( $( this ) );
                    break;

            }

            if(need_scroll){
                var last_tr_distance = $(this).closest('.pp-fundraising-table').find('tbody tr:last').offset().top;
                // alert(last_tr_distance);

                $('html, body').animate({
                    scrollTop: last_tr_distance - 10
                }, 500);
            }

            event.preventDefault();
        })
        
        // Remove form rows
        .on( 'click', '[data-pp-charitable-remove-row]', function() {
        
            var index = $( this ).data( 'pp-charitable-remove-row' );
            var table = $( this ).parents( 'table' ).first()
            
            remove_table_row( index, $(this) );

            update_add_more_button(table);

            event.preventDefault();
        })

        // remove tickets / child 
        .on( 'click', '[data-pp-charitable-remove-child-row]', function() {
        
            var index = $( this ).data( 'pp-charitable-remove-child-row' );
            var $table = $(this).closest( 'table' ), 
                $row = $table.find( 'tr[data-index=' + index + ']' );

            // alert('remove on index '+index);
            // $table.css('border', 'solid');
            // $row.css('background', 'red');

            $row.remove();

            event.preventDefault();
        });

        // Set up datepicker dynamically
        $( 'body' ).on( 'focus', '.datepicker', function() {
            if ( false === $( this ).hasClass( 'hasDatepicker' ) ) {
                $( this ).datepicker();
            }        
        });

        // Set the max & min dates of post_date and end_date fields.
        $( 'input[name=post_date]' ).on( 'change', function(){
            var max_length = 365,
                min_length = 7,
                start_date = new Date( $(this).val() ),
                elapsed = ( start_date.getTime() - Date.now() ) / ( 1000 * 60 * 60 * 24 ), 
                min_end = Math.ceil( elapsed + min_length ), 
                max_end = Math.ceil( elapsed + max_length ),
                $end_date_field = $( 'input[name=end_date]' );

            if ( false === $end_date_field.hasClass( 'hasDatepicker' ) ) {
                $end_date_field.datepicker();   
            }

            $end_date_field
                .datepicker( "option", "minDate", min_end )
                .datepicker( "option", "maxDate", max_end );
        }); 

        $( 'input[name=end_date]' ).on( 'change', function(){
            var max_length = 365,
                min_length = 7,
                start_date = new Date( $(this).val() ),
                elapsed = ( start_date.getTime() - Date.now() ) / ( 1000 * 60 * 60 * 24 ), 
                min_start = Math.ceil( elapsed - max_length ), 
                max_start = Math.ceil( elapsed - min_length ),
                $start_date_field = $( 'input[name=post_date]' );

            if ( false === $start_date_field.hasClass( 'hasDatepicker' ) ) {
                // $start_date_field.datepicker();   
            }

            // $start_date_field
                // .datepicker( "option", "minDate", min_start )
                // .datepicker( "option", "maxDate", max_start );
        }); 

        // Set up handlers for event form
        setup_event_handlers();
    });

	/**
	 * New js
	 */
	PP_Toolkit_Submission = function() {
		var self = this;

		self.init = function() {

            self.initEnableTeamFundraising();
            self.initTeamFundraisingForm();

            var chkReadyState = setInterval(function() {
                if (document.readyState == "complete") {
                    // clear the interval
                    clearInterval(chkReadyState);
                    // finally your page is loaded. 
                    self.initWizard();
                }
            }, 100);

            $('body').on('new_fundraising_table_added', function(){
                // alert('ok');
                // self.initCropit();
                self.setupCropitPlaceholder();
            });

            $(document).on('click', ".cropit-placeholder", function(e){
                e.preventDefault();
                var $img_crop = $(this).closest('.image-crop');
                if($(this).hasClass('loaded')){
                    self.openCroperPopup($img_crop.find('.imgcrop-popup-wrapper'), false);
                } else {
                    self.openCroperPopup($img_crop.find('.imgcrop-popup-wrapper'), true);
                }
                
            });
            
            $(document).on('click', '.imgcrop-popup-wrapper .imgcrop-cancel', function(e){
                e.preventDefault();
                self.closeCroperPopup($(this).closest('.imgcrop-popup-wrapper'));
            } );

            $(document).on('click', ".cropit-preview-image-container:not('.loaded'), .select-image, .cropit-placeholder", function(e){
                e.preventDefault();
                var editor = $(this).closest('.image-editor');
                editor.find('.cropit-image-input').trigger('click');
            });

            $( document ).on('click', '[editor-action]', self.editorAction );
            $( document ).on('click', '.save-image', self.saveImage );

            $( document ).on('click', '.your-account-login', self.openLoginOnCreateCampaign );
            $( document ).on('click', '.your-account-signup', self.openSignupOnCreateCampaign );
            $( document ).on('click', '#login-button-on-create-campaign', self.LoginOnCreateCampaign );

			
            self.setupCropitPlaceholder();
            self.initSelectize();

            // self.initCropit();
            self.initPayoutOptions();
            // self.initHoldPreviewButton();
			self.initFinishedButtons();
            $(document).on('click', '.charitable-submit-field input[type="submit"]', function(e){

                // check empty value
                var wrapper = $('#form-wizard');
                var emptyValue = self.checkEmptyValues(wrapper);
                if(emptyValue.length > 0){
                    alertMessage = emptyValue.join('</li><li>');
                    swal({
                      title: 'PLEASE FILL IN THE REQUIRED FIELD:',
                      text: '<ul class="error-list"><li>'+alertMessage+'</li></ul>',
                      type: 'warning',
                      html: true
                    });
                    return false;
                }

                // var name = $(this).attr('name');
                // alert(name);

                // Reset dialog, and allow submit
                window.onbeforeunload = null;

                // return false;
            });

            /**
             * Add dialog when user leave the form
             * @param  {[type]} e [description]
             * @return {[type]}   [description]
             */
            if($('#charitable-campaign-submission-form').length){
                window.onbeforeunload = function (e) {
                    var message = "Changes you made may not be saved.";
                    var firefox = /Firefox[\/\s](\d+)/.test(navigator.userAgent);

                    if (firefox) {
                        //Add custom dialog
                        //Firefox does not accept window.showModalDialog(), window.alert(), window.confirm(), and window.prompt() furthermore
                        var dialog = document.createElement("div");
                        document.body.appendChild(dialog);
                        dialog.id = "dialog";
                        dialog.style.visibility = "hidden";
                        dialog.innerHTML = message; 
                        var left = document.body.clientWidth / 2 - dialog.clientWidth / 2;
                        dialog.style.left = left + "px";
                        dialog.style.visibility = "visible";  
                        var shadow = document.createElement("div");
                        document.body.appendChild(shadow);
                        shadow.id = "shadow";       
                        //tip with setTimeout
                        setTimeout(function () {
                            document.body.removeChild(document.getElementById("dialog"));
                            document.body.removeChild(document.getElementById("shadow"));
                        }, 0);
                    }

                    return message;
                }
            }
		};

        self.initSelectize = function(){

            if(!$.fn.selectize){
                return;
            }

            // alert('ok');

            // $('#select-non-profit').selectize({
            //     onChange: function(value){
            //         var _opt_data = $('#select-non-profit').attr('data-orgs');
            //         var opt_data = JSON.parse(_opt_data);
            //         var current_org = opt_data[value];
            //         // alert(current_org);

            //         if(typeof(current_org) != 'undefined' ){

            //             console.log(current_org);

            //             $('#non-profit-data .org_logo').html('<img src="'+current_org['logo']+'" alt="'+current_org['name']+'">');
            //             $('#non-profit-data .org_name').html(current_org['name']);
            //             $('#non-profit-data .org_link').html('<a href="'+current_org['url']+'" target="_blank">'+current_org['url']+'</a>');
            //             $('#non-profit-data .org_tax_id').html(current_org['tax_id']);

            //             $('#non-profit-data').removeClass('hidden');

            //             // for (var key in current_org) {
            //             //     if (current_org.hasOwnProperty(key)) {
            //             //         console.log(key + " -> " + current_org[key]);

            //             //         $('#non-profit-data .')
            //             //     }
            //             // }
            //         }
            //     }
            // });


                    
            var _opt_data = $('#select-non-profit').attr('data-orgs');
            var opt_data = {};
            if(_opt_data){
                opt_data = JSON.parse(_opt_data);
            }
            

            $('#select-non-profit').selectize({
                persist: false,
                openOnFocus: false,
                valueField: 'stripe_id',
                labelField: 'name',
                searchField: 'name',
                create: true,
                options: [],
                maxItems: 1,
                // preload: 'focus',
                create: function(input) {
                    // alert(input);

                    $('input[name="payout_organization_name"]').val(input);
                    $('html, body').animate({
                        scrollTop: $("#unregistered_nonprofit-container").offset().top - 250
                    }, 500);

                    return {
                        stripe_id : '',
                        name  : input
                    };
                },
                onChange: function(value){

                    var current_org = opt_data[value];

                    if (!value.length || !current_org){
                        // alert('unregistered');

                        $('#non-profit-data').addClass('hidden');
                        $('#unregistered_nonprofit-container').removeClass('hidden');
                        $('#unregistered_nonprofit-container .required').each(function(){
                            $(this).closest('.charitable-form-field').addClass('required-field');
                        });
                    } else {
                        console.log(current_org);
                        // alert('registered');

                        $('#non-profit-data .org_logo').html('<img src="'+current_org['logo']+'" alt="'+current_org['name']+'">');
                        $('#non-profit-data .org_name').html(current_org['name']);
                        $('#non-profit-data .org_link').html('<a href="'+current_org['url']+'" target="_blank">'+current_org['url']+'</a>');
                        $('#non-profit-data .org_tax_id').html(current_org['tax_id']);

                        $('#non-profit-data').removeClass('hidden');
                        $('#unregistered_nonprofit-container').addClass('hidden');
                        $('#unregistered_nonprofit-container .required').each(function(){
                            $(this).closest('.charitable-form-field').removeClass('required-field');
                        });
                    }
                },
                render: {
                    option: function(item, escape) {
                        return '<div>' + item.name + '</div>';
                    },
                    option_create: function(data, escape) {
                        return '<div class="create">Add unregistered non profit with name: <strong>' + escape(data.input) + '</strong></div>';
                    }
                },
                load: function(query, callback) {
                    if (!query.length) return callback();

                    // $.ajax({
                    //     url: 'http://dev.funkmo/charitable/wp-json/pwph/get_non_profit_options',
                    //     type: 'GET',
                    //     dataType: 'json',
                    //     data: {
                    //         q: query,
                    //     },
                    //     error: function() {
                    //         callback();
                    //     },
                    //     success: function(res) {
                    //         callback(res);
                    //     }
                    // });

                    // console.log(result);
                    
                    var result = Object.keys(opt_data).map(function(key) {
                        return [Number(key), opt_data[key]];
                    });
                    callback( result );
                }
            });

            // $('select[name="_campaign_connected_stripe_id"]').
        };

        self.initEnableTeamFundraising = function(){
            if(!$('input[name="team_fundraising"]').length){
                return;
            }

            $(document).on('change', 'input[name="team_fundraising"]', function(){
                if ($(this).is(":checked")) {
                    $('#charitable-campaign-referrer').closest('fieldset').hide();
                } else{
                    $('#charitable-campaign-referrer').closest('fieldset').show();
                }
            });

            $('input[name="team_fundraising"]').trigger('change');
        };

        self.initTeamFundraisingForm = function(){
            if(!$('#charitable-team-fundraising-form').length){
                return;
            }

            $(document).on('change', '.toggle-override', function(){
                if ($(this).is(":checked")) {
                    $(this).closest('.field-container').find('.field-wrapper').addClass('hidden');
                } else{
                    var field_wrapper = $(this).closest('.field-container').find('.field-wrapper');
                    field_wrapper.removeClass('hidden');
                    if(field_wrapper.find('.cropit-placeholder.initial').length){
                        self.setupCropitPlaceholder();
                    }
                }
            });

            $('.toggle-override').trigger('change');
        };

        self.setupCropitPlaceholder = function(){
            $('.cropit-placeholder').not(".inited").each(function(){
                var $placeholder = $(this),
                    wrapper = $(this).closest('.image-crop'),
                    _width = wrapper.attr('data-width'),
                    _height = wrapper.attr('data-height'),
                    placeholder_height = $placeholder.outerWidth() / (_width / _height);

                // alert(placeholder_height);

                $placeholder.css('height', placeholder_height + 'px');
                $placeholder.addClass('inited');
            });
        };


        self.closeCroperPopup = function(wrapper){
            wrapper.removeClass('pop');
        };

        self.openCroperPopup = function(popup_wrapper, open_file){
            popup_wrapper.addClass('pop');

            // init cropit
            var $img_crop = popup_wrapper.closest('.image-crop'),
                img_crop_content = popup_wrapper.find('.imgcrop-content'),
                editor = popup_wrapper.find('.image-editor'),
                $preview = editor.find('.cropit-preview'),
                height = $img_crop.attr('data-height'),
                width = $img_crop.attr('data-width'),
                maxZoom = $img_crop.attr('data-max-zoom') || 1,
                $preview_height = $preview.innerWidth() / (width / height),
                export_zoom = width / $preview.innerWidth(),
                img_url = editor.find('.image-url').val(),
                timeout = null;

            console.log('width : ' + width);
            console.log('height : ' + height);
            console.log('$preview.innerWidth() : ' + $preview.innerWidth());
            console.log('$preview_height) : ' + $preview_height);

            /**
             * Setup image preview
             */
            $preview.css('height', $preview_height + 'px');

            editor.cropit({ 
                height: $preview_height,
                width: $preview.innerWidth(),
                exportZoom: export_zoom,
                smallImage: 'allow',
                initialZoom: 'min',
                minZoom: 'fit',
                maxZoom: maxZoom,
                // freeMove: true,
                // imageBackground: true,
                // imageBackgroundBorderWidth: [15,100,15,100], // Width of background border
                onImageLoaded: function(){
                    editor.find('.cropit-preview-image-container').addClass('loaded');
                    editor.find('.cropit-placeholder').hide();
                    editor.find('.controls-wrapper').removeClass('hidden').find('.save-image').addClass('focused');
                },
                onImageError: function(error){
                    alert(error.message);
                },
                onOffsetChange: function(offset){
                    // clearTimeout(timeout);
                    // timeout = setTimeout(function() {
                 //     var imageData = editor.cropit('export');
                    //  console.log(imageData);
                    //  editor.find('.image-uri').val(imageData);

                 //    }, 1000);
                    editor.find('.save-image').removeClass('disabled hidden');
                },
            });

            editor.addClass('inited');
            
            if(img_url){
                // alert(img_url);
                editor.cropit('imageSrc', img_url);
                editor.cropit('maxZoom', export_zoom);
                editor.cropit('initialZoom', 'min');
            }

            if(open_file){
                editor.find('.cropit-image-input').trigger('click');
            }
        };

		// self.initCropit = function(){

		// 	if(!$.fn.cropit)
		// 		return false;

		// 	$('.image-editor').not(".inited").each(function(){
		// 		var $el = $(this),
		// 			$preview = $el.find('.cropit-preview'),
		// 			height = $(this).attr('data-height'),
		// 			width = $(this).attr('data-width'),
		// 			export_zoom = $(this).attr('data-export-zoom') || 1,
		// 			maxZoom = $(this).attr('data-max-zoom') || 1,
		// 			$preview_height = $preview.outerWidth() / (width / height),
		// 			export_zoom = width / $preview.outerWidth(),
		// 			img_url = $el.find('.image-url').val(),
		// 			timeout = null;

		// 		/**
		// 		 * Setup image preview
		// 		 */
		// 		$preview.css('height', $preview_height + 'px');
  //               $el.find('.cropit-placeholder').css('height', $preview_height + 'px');
		// 		// alert( width / $preview.outerWidth() );


		// 		$el.cropit({ 
		// 			height: $preview_height,
		// 			width: $preview.outerWidth(),
		// 			initialZoom: 'min',
		// 			minZoom: 'fit',
		// 			maxZoom: maxZoom,
  //                   // freeMove: true,
		// 			// imageBackground: true,
		// 			// imageBackgroundBorderWidth: [15,100,15,100], // Width of background border
		// 			onImageLoaded: function(){
		// 				$el.find('.cropit-preview-image-container').addClass('loaded');
  //                       $el.find('.cropit-placeholder').hide();
  //                       $el.find('.controls-wrapper').removeClass('hidden').find('.save-image').addClass('focused');
		// 			},
		// 			smallImage: 'allow',
		// 			onImageError: function(error){
		// 				alert(error.message);
		// 			},
		// 			onOffsetChange: function(offset){
		// 				// clearTimeout(timeout);
		// 				// timeout = setTimeout(function() {
		// 			 //    	var imageData = $el.cropit('export');
		// 				// 	console.log(imageData);
		// 				// 	$el.find('.image-uri').val(imageData);

		// 			 //    }, 1000);
		// 				$el.find('.save-image').removeClass('disabled hidden');
		// 			},
		// 			exportZoom: export_zoom,
		// 		});

  //               $el.addClass('inited');
				
		// 		if(img_url){
  //                   // alert(img_url);
		// 			$el.cropit('imageSrc', img_url);
		// 			$el.cropit('maxZoom', export_zoom);
		// 			$el.cropit('initialZoom', 'min');
		// 		}
				

		// 	});
		// };

		self.saveImage = function(e){
			e.preventDefault();

			var button = $(this),
                image_id = button.closest('.input-wrapper').find('.image-id').val() || 0,
				button_text = button.text(),
				editor = button.closest('.image-editor'),
                $img_crop = editor.closest('.image-crop'),
				nonce = $img_crop.attr('data-nonce'),
				imageData = editor.cropit('export', {
                  type: 'image/jpeg',
                  quality: .9,
                  fillBg: '#fff'
                });

            // alert(image_id);
            // return; 

            if(button.hasClass('disabled'))
                return;

            button.text('Processing..').addClass('disabled');

            var blob = self.convertToBlob(imageData);
            var fd = new FormData();
            fd.append("imgfile", blob);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', CHARITABLE_AMBASSADORS_VARS.ajaxurl + '?action=save_imagedata&nonce='+nonce+'&image_id='+image_id, true);
            
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                  var percentComplete = (e.loaded / e.total) * 100;
                  console.log(percentComplete + '% uploaded');
                }
            };

            xhr.onload = function() {
                if (this.status == 200) {
                    var res = JSON.parse(this.response);

                    console.log('Server got:', res);

                    if(res.success){
                        editor.find('.image-id').val(res.result.id);
                        editor.find('.image-url').val(res.result.url);
                        editor.find('.save-image').addClass('disabled hidden');

                        $img_crop.find('.cropit-placeholder').html('<img src="'+res.result.url+'" class="cropit-result">').addClass('loaded');

                        self.closeCroperPopup($img_crop.find('.imgcrop-popup-wrapper'));
                    }
                    // alert(res.message);
                } else {
                    alert( "Failed to save image" );
                }

                button.text(button_text);
            };

            xhr.send(fd);
		};

        self.convertToBlob = function(dataURI){
            // convert base64 to raw binary data held in a string
            // doesn't handle URLEncoded DataURIs - see SO answer #6850276 for code that does this
            var byteString = atob(dataURI.split(',')[1]);

            // separate out the mime component
            var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

            // write the bytes of the string to an ArrayBuffer
            var ab = new ArrayBuffer(byteString.length);
            var dw = new DataView(ab);
            for(var i = 0; i < byteString.length; i++) {
                dw.setUint8(i, byteString.charCodeAt(i));
            }

            // write the ArrayBuffer to a blob, and you're done
            return new Blob([ab], {type: mimeString});
        };

		self.editorAction = function(e){
			e.preventDefault();
			var editor = $(this).closest('.image-editor'),
				action = $(this).attr('editor-action');

			editor.cropit(action);
		};

		self.initWizard = function(){

			if(!$.fn.smartWizard)
				return false;

			// Step show event 
            $("#form-wizard").on("showStep", function(e, anchorObject, stepNumber, stepDirection, stepPosition) {
               	
                var total = $(this).find('.step-anchor li').length,
                    current_step = stepNumber + 1;
                
                $(this).find('.step-label').html('Step ' + current_step + ' of ' + total);

               	if(stepPosition === 'first'){
               		$(".sw-btn-prev").hide();
               	} else {
               		$(".sw-btn-prev").show();
               	}
               
               	if(stepPosition === 'final'){
               		$(".sw-btn-next").hide();
                   	// $('input[name="preview-campaign"], input[name="submit-campaign"]').show();
               	} else {
               		$(".sw-btn-next").show();
               		// $('input[name="preview-campaign"], input[name="submit-campaign"]').hide();
               	}

                // var wizard_nav = $("#form-wizard .wizard-nav").children('li').length - 1,
                //     wizard_nav_done = $("#form-wizard .wizard-nav").children("li.done").length;

                // if(wizard_nav == wizard_nav_done){
                //     $('input[name="preview-campaign"], input[name="submit-campaign"]').attr('disabled','disabled');
                // } else {
                //     $('input[name="preview-campaign"], input[name="submit-campaign"]').removeAttr('disabled');
                // }
            });

            $("#form-wizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection){

                $('html, body').animate({
                    scrollTop: $("#form-wizard").offset().top - 250
                }, 500);
                
                var step_wrapper = $("#form-wizard").find("[data-step='" + stepNumber + "']");
                var emptyValue = self.checkEmptyValues(step_wrapper);

                self.validateFields(step_wrapper);

                // // console.log(empty);
                if(emptyValue.length > 0){
                    alertMessage = emptyValue.join('</li><li>');
                    swal({
                      title: 'PLEASE FILL IN THE REQUIRED FIELD:',
                      text: '<ul class="error-list"><li>'+alertMessage+'</li></ul>',
                      type: 'warning',
                      html: true
                    });
                    return false;
                }

                if(step_wrapper.find('input[name="not_login_type"]').length && (step_wrapper.find('input[name="not_login_type"]').val() == 'login') && (step_wrapper.attr('data-user-id') == null )){
                    swal({
                      title: 'PLEASE LOGIN BEFORE CONTINUE.',
                      html: true
                    });
                    return false;
                }

                self.maybeEnableSubmitCampaign();
            });
            
            // Smart Wizard
            $('#form-wizard').smartWizard({ 
                selected: 0, 
                theme: 'default',
                transitionEffect:'none',
                transitionSpeed: 1,
                showStepURLhash: false,
                toolbarSettings: {
                	toolbarPosition: 'none'
                },
                anchorSettings: {
                    anchorClickable: true, // Enable/Disable anchor navigation
                    enableAllAnchors: true, // Activates all anchors clickable all times
                    markDoneStep: true, // Add done css
                    markAllPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
                    removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
                    enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
                },
                autoAdjustHeight: false,
                keyNavigation: false,
                // disabledSteps: [4],
                onFinish: function(){
                    alert('Finish!');
                }
            });

            // display nav wizard
            $("#form-wizard").find('.wizard-nav').css('display', 'inline-block');

            self.maybeEnableSubmitCampaign();

            var campaign_id = $("#form-wizard").closest('form').find('input[name="ID"]');
            if(campaign_id.val()){
                $('#form-wizard').find('.show_campaign_id').removeClass('hidden').find('.show_c_id').text(campaign_id.val());
            }
		};

        self.validateFields = function(wrapper){
            var emptyValue = [];
            wrapper.find('.required-field').not('.hidden').each(function(i){

                var field_wrapper = $(this);

                self.removeErrorMessage(field_wrapper);

                var _payout_wrapper = $(this).closest('.payout-options-wrapper');
                if(_payout_wrapper.length && _payout_wrapper.hasClass('disabled')){
                    return;
                }

                // console.log($(this).attr('class'));

                if($(this).hasClass('pp-image-radio')){
                    var dataname = $(this).attr('data-name'),
                        datalabel = $(this).attr('data-label');

                    // alert($(this).find("input:radio[name="+dataname+"]:checked").val());
                    if( !$(this).find("input:radio[name="+dataname+"]:checked").val() ){
                        self.addErrorMessage(field_wrapper);
                        emptyValue.push( datalabel );
                    }
                } else {
                    $(this).find(':input').each(function(i){
                        var type = $(this).attr('type');
                        var label = $(this).closest('.required-field').find('label').first().text();
                        label = $.trim(label.replace(/[\t\n\*]+/g,''));

                        if($(this).hasClass('wp-editor-area')){
                            var editorContent = tinyMCE.activeEditor.getContent();
                            if ((editorContent === '' || editorContent === null)) {
                                // Do stuff when TinyMCE is empty
                                self.addErrorMessage(field_wrapper);
                                emptyValue.push( label );
                            }
                        } else {
                            switch(type) {
                                case 'checkbox':
                                    if(!$(this).is(':checked')){
                                        self.addErrorMessage(field_wrapper);
                                        emptyValue.push( label );
                                    }
                                    break;
                                default:

                                    if(!$(this).val() && $(this).attr('name') ){
                                        self.addErrorMessage(field_wrapper);
                                        emptyValue.push( label );
                                    }
                            }
                        }
                    });
                }
            });

            // alert(emptyValue.length);
            // console.log(emptyValue);

            return emptyValue.length < 1;
        };

        self.addErrorMessage = function(field_wrapper){
            field_wrapper.addClass('error');
        }

        self.removeErrorMessage = function(field_wrapper){
            field_wrapper.removeClass('error');
        }

        self.maybeEnableSubmitCampaign = function(){
            // maybe enable submit button value
            var emptyValue = self.checkEmptyValues($('#form-wizard'));
            if(emptyValue.length <= 0){
                $('#form-wizard').find('.charitable-submit-field').removeClass('hidden');
            }
        };

        self.checkEmptyValues = function(wrapper){
            var emptyValue = [];
            wrapper.find('.required-field').not('.hidden').each(function(i){

                var _payout_wrapper = $(this).closest('.payout-options-wrapper');
                if(_payout_wrapper.length && _payout_wrapper.hasClass('disabled')){
                    return;
                }

                // console.log($(this).attr('class'));

                if($(this).hasClass('pp-image-radio')){
                    var dataname = $(this).attr('data-name'),
                        datalabel = $(this).attr('data-label');

                    // alert($(this).find("input:radio[name="+dataname+"]:checked").val());
                    if( !$(this).find("input:radio[name="+dataname+"]:checked").val() ){
                        emptyValue.push( datalabel );
                    }
                } else {
                    $(this).find(':input').each(function(i){
                        var type = $(this).attr('type');
                        var label = $(this).closest('.required-field').find('label').first().text();
                        label = $.trim(label.replace(/[\t\n\*]+/g,''));

                        if($(this).hasClass('wp-editor-area')){
                            var editorContent = tinyMCE.activeEditor.getContent();
                            if ((editorContent === '' || editorContent === null)) {
                                // Do stuff when TinyMCE is empty
                                emptyValue.push( label );
                            }
                        } else {
                            switch(type) {
                                case 'checkbox':
                                    if(!$(this).is(':checked')){
                                        emptyValue.push( label );
                                    }
                                    break;
                                default:

                                    if(!$(this).val() && $(this).attr('name') ){
                                        emptyValue.push( label );
                                    }
                            }
                        }
                    });
                }
            });

            return emptyValue;
        };
		
        // self.initHoldPreviewButton = function(){
        //     var hold = true;
        //     $(document).on('click', '.charitable-submit-field input[name="preview-campaign"]', function(e){
            
        //         // alert('sss'); 
        //         // e.preventDefault();
        //         // return;

        //         if(!hold){
        //             return;
        //         }

        //         var form = $(this).closest('form'),
        //             input_post_id = form.find('input[name="ID"]'),
        //             button = $(this);

        //         if(input_post_id.val()) return;

        //         e.preventDefault();

        //         // debug
        //         // var xx = (hold) ? 'hold' : 'alse';
        //         // alert(xx);
        //         // var input_tit = form.find('input[name="post_title"]');
        //         // input_tit.val('test');

        //         var data = {
        //                 'action'    : 'create_dummy_post'
        //             };

        //         $.post( CHARITABLE_AMBASSADORS_VARS.ajaxurl, data, function( response ) {
        //             if(response > 0){
        //                 input_post_id.val(response);
        //                 hold = false;
        //                 button.trigger('click');
        //             }else{
        //                 alert('Failed to save campaign.');
        //             } 
        //         });
        //     });
        // };

        self.initPayoutOptions = function(){

            $(document).on('click', '.payout-option-container', function(e){


                if( $(this).closest('.payout-options-wrapper').hasClass('disabled') ){
                    return;
                }

                $(this).find('input:radio[name="payout_options"]').attr('checked', true).trigger('change');
            });

            // $('#charitable_field_payout_options').on('click', '.description', function(e){
            //     $(this).closest('.payout-option-container').find('input:radio[name="payout_options"]').attr('checked', true).trigger('change');
            // });

            $('#charitable_field_payout_options').on({
                mouseenter: function () {
                    $(this).closest('.payout-option-container').addClass('hovered');
                },
                mouseleave: function () {
                    $(this).closest('.payout-option-container').removeClass('hovered');
                }
            }, '.description');

            $(document).on('change', '.charitable-form-field-payout-options input[type="radio"]', self.loadPayoutFields );
        };

        self.initFinishedButtons = function(){
            $('.finished-buttons-container').on('click', '.trigger-submit', function(e){
                var trigger_name = $(this).data('trigger');

                $('.charitable-submit-field input[name="'+trigger_name+'"]').trigger('click');
            });
        };

        self.loadPayoutFields = function(e){

            var $el = $(this).closest('.payout-options-wrapper'),
                val = $('.charitable-form-field-payout-options input[type="radio"]:checked').val(),
                data = {
                    'action'    : 'add_payout_options_form', 
                    'key'     : val, 
                    'nonce'     : $el.data( 'nonce' )
                };

            if( $el.hasClass('disabled') ){
                return;
            }

            // alert($el.data( 'nonce' ));
            
            if(!val || self.processing_add_payout_options_form ){
                return false;
            }


            self.processing_add_payout_options_form = true;

            // remove existing
            $el.find('.payout-options-fields-wrapper').empty();

            // if(val == 'direct'){
            //     $el.find('.payout-options-fields-wrapper').html('');
            //     return;
            // }
            
            $el.find('.loading').show();

            $.ajax({
                type: "POST",
                data: data,
                dataType: "html",
                url: CHARITABLE_AMBASSADORS_VARS.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {
                    // alert(response);
                    $el.find('.loading').hide();
                    $el.find('.payout-options-fields-wrapper').html(response);
                },
                complete: function(){
                    self.initSelectize();
                    self.processing_add_payout_options_form = false;
                }
            });
        };

        self.openLoginOnCreateCampaign = function(e){
            e.preventDefault();
            // alert('login');
            
            $(this).closest('.section-content').find(':input[name=not_login_type]').val('login');
            $(this).closest('.section-content').find('.pp-login-signup-button').removeClass('hidden').find('.required-field').removeClass('hidden');
            $(this).closest('.section-content').find('.your-account-fields').addClass('hidden');
        };

        self.openSignupOnCreateCampaign = function(e){
            e.preventDefault();
            // alert('signup');

            $(this).closest('.section-content').find(':input[name=not_login_type]').val('register');
            $(this).closest('.section-content').find('.pp-login-signup-button').addClass('hidden').find('.required-field').addClass('hidden');
            $(this).closest('.section-content').find('.your-account-fields').removeClass('hidden');
        };

        self.LoginOnCreateCampaign = function(e){
            e.preventDefault();

            var section_content = $(this).closest('.section-content');

            var user_login = $(this).closest('.pp-login-signup-button').find('input[name="user_login"]').val(),
                pass = $(this).closest('.pp-login-signup-button').find('input[name="password"]').val(),
                nonce = $(this).attr('data-nonce'),
                button = $(this),
                default_text = $(this).text();

            if(!user_login.trim() || !pass.trim() ){
                alert('Please enter your user login and password!');
                return;
            }

            button.text('Please wait..');

            $.ajax({
                type: "POST",
                data: {
                    action : 'login_on_create_campaign',
                    user_login: user_login,
                    user_pass: pass,
                    nonce: nonce,
                },
                dataType: "json",
                url: CHARITABLE_AMBASSADORS_VARS.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {

                    // alert(response.success);
                    // console.log( response );

                    if(response.success){
                        var data = response.data;
                        $.each(data, function(name, value) {
                            if(name == 'avatar_html'){
                                section_content.find('#charitable_field_avatar #avatar-dragdrop-images').html(value);
                                section_content.find('#charitable_field_avatar #avatar-dragdrop-dropzone').hide();
                            } else {
                                section_content.find(':input[name='+name+']').val(value);
                            }
                        });

                        section_content.find('input[name="agree_tos"]').prop('checked', true);

                        section_content.attr('data-user-id', data.user_id);
                        section_content.find(':input[name=not_login_type]').val('login');
                        section_content.find('.pp-login-signup-button').addClass('hidden');
                        section_content.find('.your-account-fields').removeClass('hidden');
                        section_content.find('.signup-form').remove();
                    } else {
                        var messages = $(response.message);
                        messages.find('a').attr('target', '_blank');
                        section_content.find('.messages').html(messages);
                    }
                    
                }

            }).fail(function (response, textStatus, errorThrown) {

                if ( window.console && window.console.log ) {
                    console.log( response );
                }

                alert('Failed to login.')

            }).always(function (response) {
                button.text(default_text);
            });
        };

		return self;
	}

	var PP_Toolkit_Submission_Js;

	$(document).ready(function() {
		if(PP_Toolkit_Submission_Js == null) {
			PP_Toolkit_Submission_Js = new PP_Toolkit_Submission();
			PP_Toolkit_Submission_Js.init();
		}
	});

})(jQuery);