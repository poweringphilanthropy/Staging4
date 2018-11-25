<?php
/**
 * PP_Modal Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Modal
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Modal class.
 */
class PP_Modal {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Modal();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'wp_head', array($this, 'attach_modal'), 0 );
        add_action( 'wp_footer', array($this, 'add_modal_template') );

        /**
         * Ajax action
         */
        add_action( 'wp_ajax_modal_donate', array($this, 'process_donation_form_submission') );
        add_action( 'wp_ajax_nopriv_modal_donate', array($this, 'process_donation_form_submission') );

        add_action( 'wp_ajax_modal_tickets', array($this, 'process_tickets_form_submission') );
        add_action( 'wp_ajax_nopriv_modal_tickets', array($this, 'process_tickets_form_submission') );

        add_action( 'wp_ajax_modal_merchandise', array($this, 'process_merchandise_form_submission') );
        add_action( 'wp_ajax_nopriv_modal_merchandise', array($this, 'process_merchandise_form_submission') );
    }

    public function attach_modal(){

        if(!is_singular( 'campaign' ))
            return;

        add_action( 'charitable_single_campaign_before', array($this, 'dislay_button_on_campaign_before'), 4 );

        // modal
        add_action( 'philanthropy_heading_button', array($this, 'add_modal_button_event') );
        add_action( 'philanthropy_modal_event_tickets', array($this, 'display_ticket_form') );
        add_action( 'philanthropy_modal_template', array($this, 'add_modal_template_event') );

        add_action( 'philanthropy_heading_button', array($this, 'add_modal_button_merchandise') );
        add_action( 'philanthropy_modal_template', array($this, 'add_modal_template_merchandise') );

        add_action( 'philanthropy_heading_button', array($this, 'add_modal_button_volunteer') );
        add_action( 'philanthropy_modal_template', array($this, 'add_modal_template_volunteer') );

        add_action( 'philanthropy_heading_button', array($this, 'add_modal_button_donate') );
        add_action( 'philanthropy_modal_template', array($this, 'add_modal_template_donate') );
    }

    public function dislay_button_on_campaign_before($campaign){

        if ( Charitable::CAMPAIGN_POST_TYPE != get_post_type() ) {
            return;
        }

        $display_option = charitable_get_option( 'donation_form_display', 'separate_page' );
        if($display_option != 'modal'){
            return;
        }

        $campaign = charitable_get_current_campaign();

        // if ( $campaign->has_ended() ) {
        //     return;
        // }

        // Enqueue script
        wp_enqueue_script( 'philanthropy-modal' );
        wp_localize_script( 'philanthropy-modal', 'PHILANTHROPY_MODAL', array(
            'ajax_url' => admin_url( 'admin-ajax.php', 'relative' ),
            'default_error_message' => __('Unable to process request.', 'pp-toolkit')
        ) );

        pp_toolkit_template( 'campaign-loop/heading-button.php', array( 'campaign' => $campaign ) );
    }

    /**
     * Modal
     * @param [type] $campaign [description]
     */
    public function add_modal_button_event($campaign){

        $events = philanthropy_get_campaign_event_ids($campaign->ID);
        if(empty($events))
            return;

        pp_toolkit_template('campaign/button-modal.php', array(
            'campaign' => $campaign,
            'id' => 'philanthropy-donation-form-modal-event',
            'label' => __('Attend Our Event', 'philanthropy')
        ) );

    }

    public function display_ticket_form($ticket_ids){

        if(empty($ticket_ids))
            return;

        pp_toolkit_template('tickets/ticket-form.php', array(
            'ticket_ids' => $ticket_ids,
            'label' => __('Tickets', 'philanthropy')
        ) );
    }

    public function add_modal_template_event($campaign){

        $events = philanthropy_get_campaign_event_ids($campaign->ID);
        if(empty($events))
            return;

        pp_toolkit_template('modal/modal-event.php', array(
            'campaign' => $campaign,
            'id' => 'philanthropy-donation-form-modal-event',
            'label' => __('Attend Our Event', 'philanthropy'),
            'events' => $events,
        ) );

    }

    public function add_modal_button_merchandise($campaign){

        $merchandise = philanthropy_get_campaign_merchandise_ids($campaign->ID);

        if(empty($merchandise))
            return;

        pp_toolkit_template('campaign/button-modal.php', array(
            'campaign' => $campaign,
            'id' => 'philanthropy-donation-form-modal-merchandise',
            'label' => __('Buy Merchandise', 'philanthropy')
        ) );

    }

    public function add_modal_template_merchandise($campaign){

        $merchandise = philanthropy_get_campaign_merchandise_ids($campaign->ID);
        if(empty($merchandise))
            return;

        pp_toolkit_template('modal/modal-merchandise.php', array(
            'campaign' => $campaign,
            'id' => 'philanthropy-donation-form-modal-merchandise',
            'label' => __('Buy Merchandise', 'philanthropy'),
            'merchandise' => $merchandise
        ) );

    }

    public function add_modal_button_volunteer($campaign){
        
        $volunteers = philanthropy_get_volunteers($campaign);
        if(empty($volunteers))
            return;

        pp_toolkit_template('campaign/button-modal.php', array(
            'campaign' => $campaign,
            'id' => 'philanthropy-donation-form-modal-volunteer',
            'label' => __('Volunteer', 'philanthropy')
        ) );

    }

    public function add_modal_template_volunteer($campaign){

        $volunteers = philanthropy_get_volunteers($campaign);
        if(empty($volunteers))
            return;

        pp_toolkit_template('modal/modal-volunteer.php', array(
            'campaign' => $campaign,
            'id' => 'philanthropy-donation-form-modal-volunteer',
            'label' => __('Volunteer', 'philanthropy'),
            'volunteers' => $volunteers,
        ) );

    }

    public function add_modal_button_donate($campaign){

        pp_toolkit_template('campaign/button-modal.php', array(
            'campaign' => $campaign,
            'id' => 'philanthropy-donation-form-modal-donate',
            'label' => __('Donate', 'philanthropy')
        ) );

    }

    public function add_modal_template_donate($campaign){

        pp_toolkit_template('modal/modal-donate.php', array(
            'campaign' => $campaign,
            'id' => 'philanthropy-donation-form-modal-donate',
            'label' => __('Donate', 'philanthropy'),
            'form_id' => 'charitable-donation-amount-form'
        ) );

    }

    public function add_modal_template(){

        if ( Charitable::CAMPAIGN_POST_TYPE != get_post_type() ) {
            return;
        }

        $display_option = charitable_get_option( 'donation_form_display', 'separate_page' );
        if($display_option != 'modal'){
            return;
        }

        $campaign = charitable_get_current_campaign();

        // if ( $campaign->has_ended() ) {
        //     return;
        // }

        do_action( 'philanthropy_modal_template', $campaign );
    }

    public function process_donation_form_submission(){

        if ( ! isset( $_POST['campaign_id'] ) ) {
            wp_send_json_error( new WP_Error( 'missing_campaign_id', __( 'Campaign ID was not found. Unable to create donation.', 'charitable' ) ) );
        }

        $campaign_id = intval($_POST['campaign_id']);
		$payout_type =  get_post_meta($campaign_id , '_campaign_payout_options',true);
		if($payout_type == "direct"){
			// $orgs = pp_edds_get_connected_organizations();
			// print_R($orgs); 
			$cam_title = get_the_title( $campaign_id );
		}else{
			$cam_title = get_the_title( $campaign_id );
		}
        $amount = Charitable_Donation_Form::get_donation_amount();

        $response = array(
            'success'     => false,
            'amount' =>$amount,
            'id' => $campaign_id,
        );

        $add_to_cart = Charitable_EDD_Cart::add_donation_fee_to_cart( $campaign_id, $amount );
        
        //success
        if(isset($add_to_cart['charitable_donation_campaign-' . $campaign_id])){
            $items = '';

            $response['success'] = true;
            $response['notice'] = sprintf( __('Donation %s for %s added to cart.', 'philanthropy'), edd_currency_filter( edd_format_amount( $amount ) ), $cam_title );
            $response['donation'] = $add_to_cart['charitable_donation_campaign-' . $campaign_id];
            $response['cart'] = array(
                'subtotal'      => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
                'total'         => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
                'cart_item'     => $items,
                'cart_quantity' => html_entity_decode( edd_get_cart_quantity() )
            );
        } else {
            $errors = charitable_get_notices()->get_errors();

            if ( empty( $errors ) ) {
                $errors = array( __( 'Unable to process donation.', 'charitable' ) );
            } elseif (is_array($errors)) {
                // use last error message
                $errors = array_values(array_slice($errors, -1))[0];
            }

            $response = array(
                'success' => false,
                'notice'  => $errors,
            );
        }


        wp_send_json( $response );

        // $result = $this->process_donation();

        // if ( $result ) {

        //     $processor = Charitable_Donation_Processor::get_instance();
        //     $campaign  = $processor->get_campaign();
        //     $form = $campaign->get_donation_form();
        //     $values = $form->get_donation_values();

        //     $notice = sprintf( __('Donation added to cart. Go to <a href="%s">Checkout</a>', 'philanthropy'), edd_get_checkout_uri() );
        //     if(isset($values['campaigns']) && !empty($values['campaigns'])):
        //     $c = array_shift(array_slice($values['campaigns'], 0, 1));
        //     $notice = sprintf( __('Donation %s for %s added to cart.', 'philanthropy'), edd_currency_filter( edd_format_amount( $c['amount'] ) ), get_the_title( $c['campaign_id'] ) );
        //     endif;
            
        //     // ob_start();
        //     // echo "<pre>";
        //     // print_r($values);
        //     // echo "</pre>";
        //     // $notice = ob_get_clean();

        //     $response = array(
        //         'success'     => true,
        //         'notice' => $notice,
        //     );
            
        //     $response['cart'] = array(
        //         'subtotal'      => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
        //         'total'         => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
        //         'cart_item'     => $items,
        //         'cart_quantity' => html_entity_decode( edd_get_cart_quantity() )
        //     );
        // } else {
        //     $errors = charitable_get_notices()->get_errors();

        //     if ( empty( $errors ) ) {
        //         $errors = array( __( 'Unable to process donation.', 'charitable' ) );
        //     } elseif (is_array($errors)) {
        //         // use last error message
        //         $errors = array_values(array_slice($errors, -1))[0];
        //     }

        //     $response = array(
        //         'success' => false,
        //         'notice'  => $errors,
        //     );
        // }

        // wp_send_json( $response );

        exit();
    }

    public function process_tickets_form_submission(){
        
        // if(!isset( $_REQUEST[ '_nonce' ] ) && !wp_verify_nonce( $_REQUEST[ '_nonce' ], 'form_modal_tickets' )){
        //  wp_send_json_error( new WP_Error( 'nonce_failed', __( 'Unable to process request ( Unknown nonce ).', 'philanthropy' ) ) );
        // }
        
        // default
        $response = array(
            'success' => false,
            'notice'  => __('Unable to process request ( 0 item processed. )', 'philanthropy'),
        );

        $campaign_id = (isset($_REQUEST['campaign_id'])) ? $_REQUEST['campaign_id'] : 0;
        $tickets = (isset($_REQUEST['tickets'])) ? $_REQUEST['tickets'] : array();

        // _tribe_eddticket_attendee_optout

        $processed = array();
        if(!empty($tickets)):
        foreach ($tickets as $ticket_id => $data) {
            $qty = absint( $data['qty'] );
            if($qty < 1) continue;

            $options = array();
            if(isset($_REQUEST['tribe-tickets-meta'][$ticket_id])){
                // not sure, maybe later we need it
                $options['tribe-tickets-meta'] = $_REQUEST['tribe-tickets-meta'][$ticket_id];
                $options['_tribe_eddticket_attendee_optout'] = false;
            }

            $options['quantity'] = $qty;
            $options['campaign_id'] = $campaign_id;
            edd_add_to_cart( $ticket_id, $options );
            
            
            $processed[] = $ticket_id;
        }
        endif;

        if(!empty($processed)){
            $items = '';

            $response['success'] = true;
            $response['notice'] = __('Tickets added to cart.', 'philanthropy');
            $response['cart'] = array(
                'subtotal'      => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
                'total'         => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
                'cart_item'     => $items,
                'cart_quantity' => html_entity_decode( edd_get_cart_quantity() )
            );
        }

        wp_send_json( $response );
    }

    public function process_merchandise_form_submission(){
        // if(!isset( $_REQUEST[ '_nonce' ] ) && !wp_verify_nonce( $_REQUEST[ '_nonce' ], 'form_modal_merchandise' )){
        //  wp_send_json_error( new WP_Error( 'nonce_failed', __( 'Unable to process request.', 'philanthropy' ) ) );
        // }
        
        // default
        $response = array(
            'success' => false,
            'notice'  => __('Unable to process request ( 0 item processed. )', 'philanthropy'),
        );

        $campaign_id = (isset($_REQUEST['campaign_id'])) ? $_REQUEST['campaign_id'] : 0;
        $merchandise = (isset($_REQUEST['merchandise'])) ? $_REQUEST['merchandise'] : array();

        $processed = array();
        $items = '';
        if(!empty($merchandise)):
        foreach ($merchandise as $download_id => $data) {
            
            if( isset($data['variation']) ){ // variable

                $i = 0;
                foreach ($data['variation'] as $price_id => $variation) {
                    $qty = absint( $variation['qty'] );
                    if($qty < 1) continue;

                    $options = array(
                        'price_id' => $price_id,
                        'quantity' => $qty,
                        'campaign_id' => $campaign_id,
                    );

                    // add options to cart 
                    edd_add_to_cart( $download_id, $options );

                    $item = array(
                        'id'      => $download_id,
                        'options' => $options
                    );

                    $item   = apply_filters( 'edd_ajax_pre_cart_item_template', $item );
                    $items .= html_entity_decode( edd_get_cart_item_template( $key, $item, true ), ENT_COMPAT, 'UTF-8' );

                $i++;
                }

                $processed[] = $download_id;

            } else {
                $qty = absint( $data['qty'] );
                if($qty < 1) continue;

                edd_add_to_cart( $download_id, array('quantity' => $qty) );

                $item = array(
                    'id'      => $download_id,
                    'options' => array('quantity' => $qty)
                );

                $item   = apply_filters( 'edd_ajax_pre_cart_item_template', $item );
                $items .= html_entity_decode( edd_get_cart_item_template( $key, $item, true ), ENT_COMPAT, 'UTF-8' );

                $processed[] = $download_id;
            }

            
        }
        endif;


        if(!empty($processed)){
            $response['success'] = true;
            $response['notice'] = __('Merchandise added to cart.', 'philanthropy');
            $response['cart'] = array(
                'subtotal'      => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
                'total'         => html_entity_decode( edd_currency_filter( edd_format_amount( edd_get_cart_total() ) ), ENT_COMPAT, 'UTF-8' ),
                'cart_item'     => $items,
                'cart_quantity' => html_entity_decode( edd_get_cart_quantity() )
            );
        }


        // debug 
        // $debug = ob_get_clean();
        // $response = array(
        //  'success' => false,
        //  'notice' => $debug
        // );

        wp_send_json( $response );

    }

    public function process_donation() {
        $processor = Charitable_Donation_Processor::get_instance();
        $campaign  = $processor->get_campaign();

        if ( ! $campaign ) {
            return;
        }

        /* Validate the form submission and retrieve the values. */
        $form = $campaign->get_donation_form();

        /**
         * Remove actions with redirect,
         * and change with our own, copy from `pp_donation_no_redirect_to_checkout`, but leave out the redirect
         */
        remove_action( 'charitable_before_process_donation_form', array( Charitable_EDD_Checkout::get_instance(), 'donation_redirect_to_checkout' ), 10, 2 );
        remove_action( 'charitable_before_process_donation_amount_form', array( Charitable_EDD_Checkout::get_instance(), 'donation_redirect_to_checkout' ), 10, 2 );
        remove_action( 'charitable_before_process_donation_form', array( PP_Charitable::init(), 'pp_donation_no_redirect_to_checkout'), 10 );
        remove_action( 'charitable_before_process_donation_amount_form', array( PP_Charitable::init(), 'pp_donation_no_redirect_to_checkout'), 10 );

        add_action( 'charitable_before_process_donation_form', array($this, 'donation_no_redirect_to_checkout'), 10, 2 );

        /**
         * @hook charitable_before_process_donation_form
         */
        do_action( 'charitable_before_process_donation_form', $processor, $form );


        /**
         * custom validate with our nonce
         */
        
        add_filter( 'charitable_validate_donation_form_submission_security_check', array($this, 'validate_donation_form_submission_security_check'), 10, 2 );

        if ( ! $form->validate_submission() ) {
            return false;
        }

        $values = $form->get_donation_values();

        $gateway = $values['gateway'];

        /* Validate the gateway values */
        if ( ! apply_filters( 'charitable_validate_donation_form_submission_gateway', true, $gateway, $values ) ) {

            return false;
        }

        // $donation_id = $processor->save_donation( $values );

        /**
         * Set a transient to allow plugins to act on this donation on the next page load.
         */
        set_transient( 'charitable_donation_' . charitable_get_session()->get_session_id(), $processor );

        do_action( 'charitable_process_donation_' . $gateway, $donation_id, $processor );

        return true;
    }

    /**
     * Copy of `pp_donation_no_redirect_to_checkout`
     * or Charitable_EDD_Checkout::get_instance(), 'donation_redirect_to_checkout'
     * but leave out redirect
     * @return [type] [description]
     */
    public function donation_no_redirect_to_checkout(Charitable_Donation_Processor $processor, Charitable_Donation_Form_Interface $form){

        /**
         * Compare the cart total against the donation amount field. Any
         * excess in the donation amount field is added to the checkout
         * as a fee.
         */
        // $cart_total = pp_add_downloads_to_cart();
        
        $amount = Charitable_Donation_Form::get_donation_amount();
        $campaign_id = $processor->get_campaign()->ID;

        if ( $amount ) {
            Charitable_EDD_Cart::add_donation_fee_to_cart( $campaign_id, $amount );
        }

        /* Redirect to the checkout. */
        // wp_redirect( edd_get_checkout_uri() );
        // edd_die();
    }

    public function validate_donation_form_submission_security_check($ret, $form){

        $submitted = $form->get_submitted_values();
        $validated = isset( $submitted[ '_charitable_donation_amount_nonce' ] ) && wp_verify_nonce( $submitted[ '_charitable_donation_amount_nonce' ], 'charitable_donation_amount' );

        if($validated){
            $ret = true;
        }

        return $ret;
    }

    public function includes(){

    }

}

PP_Modal::init();