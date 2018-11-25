(function($){

    Philanthropy_Modal = function() {
        var self = this,
            p_popup = '',
            form = '',
            attendee = {},
            process_submit = false,
            price_updating = false;

        self.init = function() {
            // attach event handler
            $(document).on('click', '[data-p_popup-open]', self.openPopup);
            $(document).on('click', '[data-p_popup-close]', self.closePopup);
            $(document).on('click', '.notice .dismis-notice', self.dismisNotice);
            $(document).on('click', '.p_popup', self.clickOuterPopup);

            $(document).on('click', '.disabled', function(e){ e.preventDefault(); });
            $(window).on('resize', self.setupFormHeight );

            $(document).on('change', '.philanthropy-modal .product_qty', self.qtyChanged);

            $(document).on('submit', '#charitable-donation-amount-form.form-donate', self.submitDonate);
            $(document).on('submit', '.philanthropy-modal.form-default', self.submitModalForm);
            
            // volunteer
            $(document).on('beforeSubmit', self.beforeSubmitNinjaForm);
            $(document).on('submitResponse', self.responseNinjaForm);

            self.initLogServiceHour();

        };

        self.initLogServiceHour = function(){
            $( 'body' ).on( 'focus', '.datepicker', function() {
                if ( false === $( this ).hasClass( 'hasDatepicker' ) ) {
                    $( this ).datepicker();
                }        
            });
                
            $(document).on('click', '.form-service-hours .add-additional-hours', self.addAdditionalHours);
            $(document).on('click', '.form-service-hours .remove-repeatable-fields a', self.removeAdditionalHours);
        };

        self.qtyChanged = function(e){
            e.preventDefault();

            if(self.p_popup == 'philanthropy-donation-form-modal-event' ){
                // special for event
                self.setupAttendee( $(this) );

                // return;
            }

            if($(this).val() > 0){
                self.form.find('.philanthropy-add-to-cart').prop('disabled', false);
            }
        };

        self.setupAttendee = function(elem){
            var tr_ticket = elem.closest('tr');

            if(tr_ticket.attr('data-meta-enabled') != 'yes')
                return;

            self.attendee.ticket_id = tr_ticket.attr('data-ticket-id');
            self.attendee.target = tr_ticket.find('.ticket-meta-attendee');
            self.attendee.itemContainer = '.tribe-event-tickets-plus-meta-attendee';
            self.attendee.template = tr_ticket.find('.tribe-event-tickets-plus-meta-fields-tpl');
            self.attendee.total = self.attendee.target.find(self.attendee.itemContainer).length;
            
            var val = elem.val();
            if(val > self.attendee.total){
                var diff = val - self.attendee.total;
                self.addAttendeeField(diff);
            } else {
                var diff = self.attendee.total - val;
                self.removeAttendeeField(diff);
            }

            // alert('total : '+self.attendee.total);
            // console.log(self.attendee);
        };

        self.addAttendeeField = function(diff){
            for (i = 0; i < diff; i++) { 
                self.getUniqueTemplate().appendTo(self.attendee.target);
                self.attendee.total++;
            }
        };

        self.removeAttendeeField = function(diff){
            self.attendee.target.find( self.attendee.itemContainer + ':nth-last-child(-n+' + diff + ')' ).remove();
        };

        self.getUniqueTemplate = function(){
            var template = self.attendee.template.html();
            // template = template.replace(/{\?}/g, "new" + self.attendee.total++);  // {?} => iterated placeholder
            // template = template.replace(/\{[^\?\}]*\}/g, "");   // {valuePlaceholder} => ""

            template = template.replace( /tribe-tickets-meta\[\]/g, 'tribe-tickets-meta[' + self.attendee.ticket_id + '][' + ( self.attendee.total ) + ']' );
            template = template.replace( /tribe-tickets-meta_([a-z0-9\-]+)_/g, 'tribe-tickets-meta_$1_' + ( self.attendee.total ) + '_' );
            return $(template);
        };

        self.setupFormHeight = function(){

            if(self.p_popup == '')
                return;

            var wHeight = $(window).height() - 50,
                popupInner = $('[data-p_popup=' + self.p_popup + ']'),
                popupHeight = popupInner.find('.p_popup-inner').outerHeight(),
                headerHeight = popupInner.find('.p_popup-header').outerHeight(),
                footerHeight = popupInner.find('.p_popup-footer').outerHeight(),
                popupContent = popupInner.find('.p_popup-content');

            // alert(wHeight);

            // console.log('window : ' + wHeight);
            // console.log('popup : ' + popupHeight);
            // console.log('headerHeight : ' + headerHeight);
            // console.log('footerHeight : ' + footerHeight);

            if(popupHeight > wHeight){
                popupHeight = wHeight;
                // console.log('popup changed : ' + popupHeight);
            }

            var maxContentHeight = popupHeight - (headerHeight + footerHeight);
            // console.log('maxContentHeight : ' + maxContentHeight);
            popupContent.css('max-height', maxContentHeight );
        };

        self.setupVolunteerForm = function(){
            var pcontent = $('[data-p_popup=' + self.p_popup + ']').find('.p_popup-content'),
                volunteers = pcontent.attr('data-volunteers').split(','),
                user_id =  pcontent.attr('data-user'),
                user_email =  pcontent.attr('data-user-email'),
                email_nonce = pcontent.attr('data-nonce'),
                p_popuptitle = $('[data-p_popup=' + self.p_popup + ']').find('.p_popup-title'),
                footer_el = $('[data-p_popup=' + self.p_popup + ']').find('.p_popup-footer');

            // alert(volunteers);
            // console.log(volunteers);

            $(document).on('click', '#submit-volunteers', function(e){
                e.preventDefault();
                // alert('ok');
                pcontent.find('form').submit();
            });

            $('.cc_id').val(user_id);
            $('.cc_email').val(user_email);
            $('.cc_nonce').val(email_nonce);

            // move element
            pcontent.find('.submit-wrap .ninja-forms-field-error').detach().appendTo(p_popuptitle);
            // pcontent.find('#nf_processing_1').css('float', 'right').detach().appendTo(footer_el);
            pcontent.find('.submit-wrap').hide();

            var volunteerSelect = $('#ninja_forms_field_14');
            for(var i=0; i<volunteers.length; i++) {
                var option = document.createElement('option');
                option.text = volunteers[i];
                option.value = volunteers[i];
                volunteerSelect.append(option)
            }
        };

        self.beforeSubmitNinjaForm = function(){

            if(self.p_popup == '')
                return;

            $('[data-p_popup=' + self.p_popup + ']').find('.philanthropy-add-to-cart').html('Processing..');

            // alert('oks');
        };

        self.responseNinjaForm = function(e, data){
            if(self.p_popup == '')
                return;

            // alert(data.success);
            // console.log(data);

            if(data.success){
                $('[data-p_popup=' + self.p_popup + ']').find('.philanthropy-add-to-cart').hide();
            }

            $('[data-p_popup=' + self.p_popup + ']').find('.philanthropy-add-to-cart').html('Send');
        };

        self.addAdditionalHours = function(e){
            e.preventDefault();
            var wrapper = $(this).closest('.form-service-hours'),
                container = wrapper.find('.repeateable-fields-container'),
                total = container.find('.repeateable-fields').length,
                template = wrapper.find('.additional-hours-template').html();

            template = template.replace(/{\?}/g, total);  // {?} => iterated placeholder
            // alert(total);
            container.removeClass('hidden').append(template);
            
            var popupContent = wrapper.find('.p_popup-content');
            popupContent.animate({scrollTop: popupContent.prop("scrollHeight")}, 500);
        };

        self.removeAdditionalHours = function(e){
            e.preventDefault();
            var wrapper = $(this).closest('.form-service-hours'),
                container = wrapper.find('.repeateable-fields-container');

            $(this).closest('.repeateable-fields').remove();

            var total = container.find('.repeateable-fields').length;
            if(total <= 0 ){
                container.addClass('hidden');
            }
            
            return false;
        };

        self.openPopup = function(e){
            self.p_popup = $(this).attr('data-p_popup-open');
            self.form = $('[data-p_popup=' + self.p_popup + ']').find('form');

            self.clearNotice();

            if(self.p_popup == 'philanthropy-donation-form-modal-event' ){
                // special for event
                self.attendee = {};
            }

            if(self.p_popup == 'philanthropy-donation-form-modal-volunteer' ){
                // special for volunteer
                self.setupVolunteerForm();
            }

            $('[data-p_popup=' + self.p_popup + ']').fadeIn(350);

            self.setupFormHeight();
     
            e.preventDefault();
        };

        self.closePopup = function(e){

            if(self.p_popup == '')
                return;
     
            $('[data-p_popup=' + self.p_popup + ']').fadeOut(350);
 
            e.preventDefault();
        };

        self.clickOuterPopup = function(e){
            var container = $('.p_popup-inner');
            if (!container.is(e.target) // if the target of the click isn't the container...
                && container.has(e.target).length === 0) // ... nor a descendant of the container
            {
               self.closePopup(e);
            }
        };

        self.addNotice = function(type, messages){

            if( typeof messages === 'string' ) {
                messages = [ messages ];
            }
            var e = messages,
                i = 0,
                count = e.length,
                output = '';

            if ( 0 === count ) {
                return;
            }

            output += '<div class="notice notice-'+type+'">';
            output += e.join( '<span class="dismis-notice">x</span></div><div class="notice notice-'+type+'">' );    
            output += '<span class="dismis-notice">x</span></div>';

            $('[data-p_popup=' + self.p_popup + ']').find( '.p_popup-notices' ).prepend(output).fadeIn(350);;
        };

        self.dismisNotice = function(e){
            $(this).closest('.notice').remove();
            e.preventDefault();
        }

        self.clearNotice = function(){
            $('[data-p_popup=' + self.p_popup + ']').find( '.p_popup-notices' ).html('').fadeOut(350);
        };

        self.getFormData = function(form){
            return form.serializeArray().reduce( function( obj, item ) {
                obj[ item.name ] = item.value;
                return obj;
            }, {} );
        };

        self.submitDonate = function(e){

            if(self.process_submit) return;

            var form = $( this ),
                data = self.getFormData(form),
                button = form.find('.philanthropy-add-to-cart'),
                default_text = button.html();

            // console.log(data);
            // alert(PHILANTHROPY_MODAL.ajax_url);

            self.process_submit = true;
            button.html('Processing..');
            self.form.find('.philanthropy-go-to-checkout').addClass('disabled').fadeIn(350);
            // resetup content height
            self.setupFormHeight();

            /* Cancel the default Charitable action, but pass it along as the form_action variable */       
            data.action = 'modal_donate';
            data.form_action = data.charitable_action;          
            delete data.charitable_action;

            $.ajax({
                type: "POST",
                data: data,
                dataType: "json",
                url: PHILANTHROPY_MODAL.ajax_url,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {

                    // alert('ok');
                    // console.log(response);

                    self.clearNotice();

                    if(response.success){
                        self.addNotice('success', [ response.notice ] );
                        self.form.find('.philanthropy-go-to-checkout').removeClass('disabled');

                        if(typeof(response.cart) != 'undefined'){
                            self.updateCart(response.cart);
                        }
                        
                    } else {
                        self.addNotice('error', [ response.notice ] );
                    }

                    // alert(response.notice);
                    // console.log( response );
                }

            }).fail(function (response, textStatus, errorThrown) {

                if ( window.console && window.console.log ) {
                    console.log( response );
                }

                self.addNotice('error', [ PHILANTHROPY_MODAL.default_error_message ] );

            }).always(function (response) {

                self.process_submit = false;
                button.html(default_text);

                // resetup content height
                self.setupFormHeight();

                // console.log(response);

            });
            
            e.preventDefault();
        };

        self.submitModalForm = function(e){

            if(self.process_submit) return;

            var form = $( this ),
                data = self.getFormData(form),
                action = form.attr('data-action') || '',
                button = form.find('.philanthropy-add-to-cart'),
                default_text = button.html();

            self.process_submit = true;
            button.html('Processing..');
            self.form.find('.philanthropy-go-to-checkout').addClass('disabled').fadeIn(350);

            // resetup content height
            self.setupFormHeight();

            data.action = 'modal_'+action;
            
            // alert(action);
            // console.log(data);

            $.ajax({
                type: "POST",
                data: data,
                dataType: "json",
                url: PHILANTHROPY_MODAL.ajax_url,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {

                    self.clearNotice();

                    // trigger wpmenucart if installed
                    if(typeof(wpmenucart_ajax) != 'undefined'){
                        $('.wpmenucartli').load(wpmenucart_ajax.ajaxurl+'?action=wpmenucart_ajax&_wpnonce='+wpmenucart_ajax.nonce);
                        $('div.wpmenucart-shortcode span.reload_shortcode').load(wpmenucart_ajax.ajaxurl+'?action=wpmenucart_ajax&_wpnonce='+wpmenucart_ajax.nonce);
                    }
                    

                    if(typeof(response.cart) != 'undefined'){
                        self.updateCart(response.cart);
                    }
                    

                    if(response.success){
                        self.addNotice('success', [ response.notice ] );
                        self.form.find('.philanthropy-go-to-checkout').removeClass('disabled');
                    } else {
                        self.addNotice('error', [ response.notice ] );
                    }
                }

            }).fail(function (response, textStatus, errorThrown) {

                if ( window.console && window.console.log ) {
                    console.log( response );
                }

                self.addNotice('error', [ PHILANTHROPY_MODAL.default_error_message ] );

            }).always(function (response) {

                self.process_submit = false;
                button.html(default_text);

                // resetup content height
                self.setupFormHeight();

                // console.log(response);

            });
            
            e.preventDefault();
        };

        self.updateCart = function(response){
            // Add the new item to the cart widget
            if ( edd_scripts.taxes_enabled === '1' ) {
                $('.cart_item.edd_subtotal').show();
                $('.cart_item.edd_cart_tax').show();
            }

            $('.cart_item.edd_total').show();
            $('.cart_item.edd_checkout').show();

            if ($('.cart_item.empty').length) {
                $('.cart_item.empty').hide();
            }

            $('.widget_edd_cart_widget .edd-cart').each( function( cart ) {

                var target = $(this).find('.edd-cart-meta:first');
                $(response.cart_item).insertBefore(target);

                var cart_wrapper = $(this).parent();
                if ( cart_wrapper ) {
                    cart_wrapper.addClass('cart-not-empty')
                    cart_wrapper.removeClass('cart-empty');
                }

            });

            // Update the totals
            if ( edd_scripts.taxes_enabled === '1' ) {
                $('.edd-cart-meta.edd_subtotal span').html( response.subtotal );
                $('.edd-cart-meta.edd_cart_tax span').html( response.tax );
            }

            $('.edd-cart-meta.edd_total span').html( response.total );

            // Update the cart quantity
            var items_added = $( '.edd-cart-item-title', response.cart_item ).length;

            $('span.edd-cart-quantity').each(function() {
                $(this).text(response.cart_quantity);
                $('body').trigger('edd_quantity_updated', [ response.cart_quantity ]);
            });

            // Show the "number of items in cart" message
            if ( $('.edd-cart-number-of-items').css('display') == 'none') {
                $('.edd-cart-number-of-items').show('slow');
            }

            $('body').trigger('edd_cart_item_added', [ response ]);
        };

        return self;
    }

    var Philanthropy_Modal_Js;

    $(document).ready(function() {
        if(Philanthropy_Modal_Js == null) {
            Philanthropy_Modal_Js = new Philanthropy_Modal();
            Philanthropy_Modal_Js.init();
        }
    });

})(jQuery);