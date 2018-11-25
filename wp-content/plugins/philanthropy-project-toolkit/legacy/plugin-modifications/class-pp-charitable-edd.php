<?php
/**
 * PP_Charitable_EDD Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Charitable_EDD
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Charitable_EDD class.
 */
class PP_Charitable_EDD {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Charitable_EDD();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
		add_action( 'init', array($this, 'add_donation_to_cart_if_any') );
        add_action( 'charitable_edd_start', array($this, 'remove_hooks') );
        
        add_action( 'edd_payment_receipt_after_table', array( $this, 'donations_table_receipt' ) );
        
        // add_action( 'charitable_edd_donation_form', array( $this, 'override_downloads_template' ) );
        // add_action( 'charitable_edd_donation_form_downloads', array( $this, 'select_downloads_template' ) );

        add_filter( 'charitable_edd_donation_form_show_contribution_note', '__return_false' );
        add_filter( 'charitable_campaign_get_meta_value', array($this, 'pp_edd_show_contribution_form'), 10, 2 );
        add_filter( 'charitable_edd_donation_fee_args', array($this, 'pp_edd_fee_donation_args'), 10, 2 );

        // add_filter('edd_payment_meta', array($this, 'pp_edd_store_custom_fields'));
        // add_action('edd_purchase_form_user_info', array($this, 'pp_edd_custom_checkout_fields'));
    
        /**
         * Save referrer (edd payment meta to charitable donation meta)
         * to use on query get donor by referrer
         */
        add_filter( 'charitable_edd_donation_values', array($this, 'add_payment_meta_to_donation_args'), 10, 3 );

        add_action( 'charitable_resync_donation_from_edd_payment', array( $this, 'clear_cache_on_resync' ) );
    }

    public function remove_hooks(){
        remove_action( 'edd_checkout_table_footer_last', array( Charitable_EDD_Checkout::get_instance(), 'donation_amount_notice_checkout' ) );
        
        remove_action( 'edd_payment_receipt_after_table', array( Charitable_EDD_Payment::get_instance(), 'donations_table_receipt' ) );
        remove_action( 'edd_payment_receipt_after_table', array( Charitable_EDD_Payment::get_instance(), 'products_table_receipt' ) );

        remove_action( 'charitable_before_process_donation_form', array( Charitable_EDD_Checkout::get_instance(), 'donation_redirect_to_checkout' ), 10, 2 );
        remove_action( 'charitable_before_process_donation_amount_form', array( Charitable_EDD_Checkout::get_instance(), 'donation_redirect_to_checkout' ), 10, 2 );
    }

    /**
     * Load our custom merchandise and events tables on the payment receipt page. 
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function donations_table_receipt() {
        // new PP_Toolkit_Template( 'payment-receipt/products.php' );
        pp_toolkit_template( 'payment-receipt/donations.php' );
    }

    /**
     * Use our own custom template to display the download selections. 
     *
     * @param   Charitable_EDD_Donation_Form $form
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function override_downloads_template( Charitable_EDD_Donation_Form $form ) {
        remove_action( 'charitable_edd_donation_form_before_downloads', array( $form, 'enter_downloads_section_header' ) );
        remove_action( 'charitable_edd_donation_form_downloads', array( $form, 'select_downloads' ) );        
    }

    /**
     * Display our downloads selection template. 
     *
     * @param   Charitable_EDD_Donation_Form $form
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function select_downloads_template( Charitable_EDD_Donation_Form $form ) {
        $template = new PP_Toolkit_Template( 'donation-form/select-downloads.php', false );
        $template->set_view_args( $form->get_view_args() );
        $template->render();
    }

    /**
     * Always show the contribution options block on campaign pages.
     *
     * @param   boolean $value
     * @param   string $key
     * @return  boolean
     * @since   1.0.9
     */
    public function pp_edd_show_contribution_form( $value, $key ) {
        if ( '_campaign_edd_show_contribution_options' == $key ) {
            $value = true;        
        }

        return $value;
    }

    /**
     * Customise the label of the donation fee before it is passed to EDD.
     *
     * @param   mixed[] $args
     * @param   int     $campaign_id
     * @return  mixed[]
     * @since   1.0.0
     */
    public function pp_edd_fee_donation_args( $args, $campaign_id ) {
        $args[ 'label' ] = sprintf( '%s <a href="%s" title="%s">%s</a>', 
            _x( 'Donation to', 'donation to campaign', 'pp-toolkit' ), 
            get_permalink( $campaign_id ),
            get_the_title( $campaign_id ),
            get_the_title( $campaign_id ) 
        );

        return $args;
    }

    /** 
     * Store the user thumbnail in the payment meta.
     */
    public function pp_edd_store_custom_fields($payment_meta) {
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/admin.php' );
        }

        $upload_overrides = array( 'test_form' => false );

        if ( '' != $_FILES[ 'edd_user_thumbnail' ] ) {
            $upload = wp_handle_upload( $_FILES[ 'edd_user_thumbnail' ], $upload_overrides );
            $payment_meta['user_thumbnail'] = $upload['url'];       
        }

        return $payment_meta;
    }

    /** 
     * Output our custom field HTML
     */
    public function pp_edd_custom_checkout_fields() {
    ?>
        <p id="edd-user-thumbnail-wrap">
            <label class="edd-label" for="edd-user-thumbnail"><?php _e('Picture', 'pippin_edd'); ?></label>
            <span class="edd-description"><?php _e( 'Upload Your Photo (optional). Adding your photo helps personalize your donation and build community around the campaign.', 'pippin_edd' ); ?></span>
            <input class="edd-input" type="file" name="edd_user_thumbnail" id="edd-user-thumbnail" value=""/>
        </p>
    <?php
    }

    /**
     * Add EDD payment metas to donation meta
     * @param [type] $meta                          [description]
     * @param [type] $donation_id                   [description]
     * @param [type] $Charitable_Donation_Processor [description]
     */
    public function add_payment_meta_to_donation_args($donation_args, $payment_id, $args){
        $payment = new EDD_Payment( $payment_id );
        $payment_meta = $payment->get_meta();

        $donation_args['meta'] = array('payment_id' => $payment_id);
        if(isset($payment_meta['referral'])){
            $donation_args['meta']['referral'] = pp_get_referrer_name($payment_meta['referral']);
        }

        return $donation_args;
    }

    public function clear_cache_on_resync($donation_id){
        if ( ! array_key_exists( 'post', $_GET ) ) {
            return;
        }

        $donation_id = $_GET['post'];

        $payment_id = Charitable_EDD_Payment::get_payment_for_donation( $donation_id );
        $edd_cart   = Charitable_EDD_Cart::create_with_payment( $payment_id );
        $campaign_ids = $edd_cart->get_benefiting_campaigns();

        if(empty($campaign_ids) || !is_array($campaign_ids))
            return;

        foreach ($campaign_ids as $key => $campaign_id) {
            Charitable_Campaign::flush_donations_cache($campaign_id);
        }
    }

    public function includes(){
	}
    public function add_donation_to_cart_if_any(){
		/* $donation_caart = edd_get_cart_fees('item');
		EDD()->fees->add_fee( array(
					'amount'      => $donation_caart['charitable_donation_campaign-42235']['amount'],
					'label'       => $donation_caart['charitable_donation_campaign-42235']['fee_label'],
					'id'          => 'charitable_donation_campaign-' . $donation_caart['charitable_donation_campaign-42235']['campaign_id'],
					'download_id' => 0,
					'price_id'    => 0,
					'no_tax'      => 1,
				) ); */
    }

}

PP_Charitable_EDD::init();