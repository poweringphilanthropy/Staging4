<?php
/**
 * PP_EDD Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_EDD
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_EDD class.
 */
class PP_EDD {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_EDD();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'plugins_loaded', array($this, 'remove_hooks'), 100 );

        /**
         * Wepay related
         */
        add_action( 'edd_insert_payment', array($this, 'pp_edd_wepay_log_fees') );
        add_filter( 'edd_wepay_checkout_args', array($this, 'pp_edd_wepay_short_description'), 10 );

        add_action('edd_purchase_form_after_user_info', array($this, 'add_user_info') );
        add_action('edd_register_fields_before', array($this, 'add_user_info') );
        add_filter('edd_purchase_form_required_fields', array($this, 'require_user_info'));

        // add_action( 'edd_checkout_error_checks', array($this, 'sumobi_edd_validate_checkout_fields'), 10, 2 );
        
        add_filter('edd_payment_meta', array($this, 'store_user_info'));
        add_action('edd_payment_personal_details_list', array($this, 'view_user_info_details'));
    }

    public function remove_hooks(){
        remove_shortcode( 'download_checkout', 'edd_checkout_form_shortcode' );
        add_shortcode( 'download_checkout', array($this, 'pp_edd_checkout_form_shortcode') );

        // $edd_simple_shipping = EDD_Simple_Shipping::get_instance();
        // remove_filters_for_anonymous_class( 'edd_payment_receipt_after', 'EDD_Simple_Shipping', 'payment_receipt_after', 10 );
        // remove_action( 'edd_payment_receipt_after', array( EDD_Simple_Shipping::get_instance(), 'payment_receipt_after'), 10 );
        /**
         * Use remove all actions since above codes not working
         */
        remove_all_actions( 'edd_payment_receipt_after', 10 );
        add_action( 'edd_payment_receipt_after', array( $this, 'payment_receipt_after' ), 10, 2 );
        
        // remove function that set user as pending member and send verification email
        remove_action( 'user_register', 'edd_add_past_purchases_to_new_user', 10 );
        // remove_action( 'edd_user_register', 'edd_process_register_form', 10 );
    }

    /**
     * Checkout Form Shortcode
     *
     * Show the checkout form.
     *
     * @since 1.0
     * @param array $atts Shortcode attributes
     * @param string $content
     * @return string
     */
    public function pp_edd_checkout_form_shortcode( $atts, $content = null ) {
        return pp_edd_checkout_form();
    }

    public function pp_edd_wepay_log_fees( $payment_id ) {
        $fees = edd_get_payment_fees( $payment_id, 'item' );
        PP_EDD_Payment_Fees::set( $fees );
    }

    /**
     * Include the donation fees in the short description.
     *
     * @param   array   $args
     * @return  array   $args
     * @since   1.0.0
     */
    public function pp_edd_wepay_short_description( $args ) {
        if ( ! function_exists( 'charitable_edd' ) ) {
            return $args;
        }

        $fees = PP_EDD_Payment_Fees::get();

        if ( empty( $fees ) ) {
            return $args;
        }

        $summary = $args[ 'short_description' ];

        foreach ( $fees as $fee ) {
            if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) ) {
                continue;
            }

            $prepend = strlen( $summary ) ? ', ' : '';

            $summary .= sprintf( '%s%s %s', $prepend, charitable_get_currency_helper()->get_monetary_amount( $fee[ 'amount' ] ), $fee[ 'label' ] );
        }

        substr( $summary, 0, -2 );

        $args[ 'short_description' ] = stripslashes_deep( html_entity_decode( wp_strip_all_tags( $summary ) ) );

        return $args;
    }

    private function get_referrer_by_team_fundraising($campaign_ids){

        foreach ( (array) $campaign_ids as $campaign_id) {
            $parent_campaign_id = wp_get_post_parent_id( $campaign_id );
            if(empty($parent_campaign_id))
                continue;

            $campaign = new Charitable_Campaign( $parent_campaign_id );

            if($campaign->get('team_fundraising') != 'on')
                continue;

            return 'child_campaign_'.$campaign_id;
            break;
        }

        return false;
    }

    private function get_cart_referrer($campaign_ids){

        $referrer = array();
        foreach ( (array) $campaign_ids as $campaign_id) {
            $campaign = new Charitable_Campaign( $campaign_id );

            if($campaign->get('team_fundraising') == 'on')
                continue;

            if(empty($campaign->get('referrer')))
                continue;

            $_ref = array_filter(explode(PHP_EOL, $campaign->get('referrer')));

            foreach ($_ref as $key => $value) {
               $referrer[$value] = $value;
            }
        }

        return $referrer;
    }

    public function add_user_info() {

        $cart = new Charitable_EDD_Cart( edd_get_cart_contents(), edd_get_cart_fees( 'item' ) );
        $campaign_ids = $cart->get_benefiting_campaigns();

        if(empty($campaign_ids))
            return false;

        ?>
        <fieldset class="edd-do-validate philanthropy-additional-info">
           

           <!--  <p id="edd-chapter-wrap">
                <label class="edd-label" for="edd-chapter">Your Fraternity/Sorority Name <span class="edd-required-indicator">*</span></label>
                <span class="edd-description">This may be used for merchandise delivery or tracking support.</span>
                <input class="edd-input" type="text" name="edd_chapter" id="edd-chapter" placeholder="<?php _e('Enter N/A if you don\'t belong to a Fraternity/Sorority.', 'pp-toolkit'); ?>">
                <span class="info show-mobile"><?php // _e('Enter N/A if you don\'t belong to a Fraternity/Sorority.', 'pp-toolkit'); ?></span>
            </p> -->
        <?php

        $referrer_by_team_fundraising = $this->get_referrer_by_team_fundraising($campaign_ids);
        if(!empty($referrer_by_team_fundraising)){
            ?>
            <input type="hidden" name="edd_referral" value="<?php echo $referrer_by_team_fundraising; ?>">
            <?php
        } else {
            $referrer = $this->get_cart_referrer($campaign_ids);
            ?>
			 <legend>Additional Information</legend>
                <?php if(!empty($referrer)){ ?>
                <p id="edd-referral-wrap">
                    <label class="edd-label" for="edd-referral">Referred By <span class="edd-required-indicator">*</span></label>
                    <span class="edd-description">Help us give credit to the individual who told you about this campaign! Please enter his/her name here.</span>
                    <select name="edd_referral" id="edd-referral">
                        <option value="n/a"><?php _e('Please select', 'pp-toolkit'); ?></option>
                        <?php foreach ($referrer as $k => $v) { ?>
                        <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                        <?php } ?>
                    </select>
                </p>
                <?php } else { ?>
                <p id="edd-referral-wrap">
                    <label class="edd-label" for="edd-referral">Referred By <span class="edd-required-indicator">*</span></label>
                    <span class="edd-description">Help us give credit to the individual who told you about this campaign! Please enter his/her name here.</span>
                    <input class="edd-input" type="text" name="edd_referral" id="edd-referral" placeholder="<?php _e('Enter n/a if you weren\'t referred by anyone.', 'pp-toolkit'); ?>">
                    <span class="info show-mobile"><?php _e('Enter n/a if you weren\'t referred by anyone.', 'pp-toolkit'); ?></span>
                </p>
                <?php } ?>
            <?php
        }
        ?>
        </fieldset>
        <?php
    }

    public function require_user_info($required_fields) {

        $cart = new Charitable_EDD_Cart( edd_get_cart_contents(), edd_get_cart_fees( 'item' ) );
        $campaign_ids = $cart->get_benefiting_campaigns();
        $referrer = $this->get_cart_referrer($campaign_ids);

        // $required_fields['edd_chapter'] = [
        //     'error_id'      => 'invalid_chapter',
        //     'error_message' => 'Please enter "n/a" if you are not affiliated with a chapter.'
        // ];

        if(!empty($referrer)):
        
        $required_fields['edd_referral'] = [
            'error_id'      => 'invalid_referral',
            'error_message' => 'Please enter "n/a" if you were not referred by anyone.'
        ];

        endif;

        return $required_fields;
    }

    public function sumobi_edd_validate_checkout_fields( $valid_data, $data ) {
        // if ( empty( $data['edd_chapter'] ) ) {
        //     edd_set_error( 'invalid_chapter', 'Please enter "n/a" if you are not affiliated with a chapter' );
        // }
        if ( empty( $data['edd_referral'] ) ) {
            edd_set_error( 'invalid_referral', 'Please enter "n/a" if you weren\'t referred by anyone' );
        }
    }

    public function store_user_info($payment_meta) {
        // $payment_meta['chapter'] = isset($_POST['edd_chapter']) ? sanitize_text_field($_POST['edd_chapter']) : '';
        $payment_meta['referral'] = isset($_POST['edd_referral']) ? sanitize_text_field($_POST['edd_referral']) : '';

        return $payment_meta;
    }
    
    public function view_user_info_details($payment_meta) {
        $chapter = isset($payment_meta['chapter']) ? $payment_meta['chapter'] : 'none';
        $referral = isset($payment_meta['referral']) ? $payment_meta['referral'] : 'none';
        ?>
        <div class="column-container">
            <div class="column">
                <strong>Chapter: </strong>
                <?php echo $chapter; ?>
            </div>
        </div>
        <div class="column-container">
            <div class="column">
                <strong>Referred By: </strong>
                <?php echo pp_get_referrer_name($referral); ?>
            </div>
        </div>

        <?php
    }

    public function payment_receipt_after( $payment, $edd_receipt_args ) {

        $user_info = edd_get_payment_meta_user_info( $payment->ID );
        $address   = ! empty( $user_info[ 'shipping_info' ] ) ? $user_info[ 'shipping_info' ] : false;

        if ( ! $address ) {
            return;
        }

        $shipped = get_post_meta( $payment->ID, '_edd_payment_shipping_status', true );
        if( $shipped == '2' ) {
            $new_status = '1';
        } else {
            $new_status = '2';
        }

        $toggle_url = esc_url( add_query_arg( array(
            'edd_action' => 'toggle_shipped_status',
            'order_id'   => $payment->ID,
            'new_status' => $new_status
        ) ) );

        $toggle_text = $shipped == '2' ? __( 'Mark as not shipped', 'edd-simple-shipping' ) : __( 'Mark as shipped', 'edd-simple-shipping' );

        echo '<tr>';
        echo '<td><strong>' . __( 'Shipping Address', 'edd-simple-shipping' ) . '</strong></td>';
        echo '<td>' . $this->format_address( $user_info, $address ) . '</td>';
        echo '</tr>';
    }

    public function format_address( $user_info, $address ) {

        $address = apply_filters( 'edd_shipping_address_format', sprintf(
            __( '<div><strong>%1$s %2$s</strong></div><div>%3$s</div><div>%4$s</div>%5$s, %6$s %7$s</div><div>%8$s</div>', 'edd-simple-shipping' ),
            $user_info[ 'first_name' ],
            $user_info[ 'last_name' ],
            $address[ 'address' ],
            $address[ 'address2' ],
            $address[ 'city' ],
            $address[ 'state' ],
            $address[ 'zip' ],
            $address[ 'country' ]
        ), $address, $user_info );

        return $address;
    }

    public function includes(){
        include_once( pp_toolkit()->directory_path . 'helpers/class-pp-edd-payment-fees.php' );
    }

}

PP_EDD::init();