<?php
/**
 * Philanthropy_EDD Class.
 *
 * @class       Philanthropy_EDD
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Philanthropy_EDD class.
 */
class Philanthropy_EDD {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		// add_filter( 'edd_purchase_form_variation_quantity_input', array($this, 'edd_purchase_form_variation_quantity_input_zero'), 10, 4 );
		// add_action( 'edd_after_download_content', array($this, 'change_edd_purchase_link_top') );
		
		// add_filter( 'edd_price_option_checked', array($this, 'uncheck_price_option_by_default'), 10, 3 );
		
		add_filter( 'edds_create_charge_args', array($this, 'change_edd_strpe_args'), 10, 2 );
		add_filter( 'edd_email_tags', array($this, 'pp_edd_email_tags'), 10, 1 );

		// fix bug edd simple shipping 2.1.5, we will remove this code once plugin updated / fixed
		add_action( 'plugins_loaded', array( $this, 'remove_shipping_from_payment_receipt_after' ), 11 );
	}

	public function remove_shipping_from_payment_receipt_after(){
		// $edd_simple_shipping = EDD_Simple_Shipping::get_instance();
		// remove_filters_for_anonymous_class( 'edd_payment_receipt_after', 'EDD_Simple_Shipping', 'payment_receipt_after', 10 );
		// remove_action( 'edd_payment_receipt_after', array( EDD_Simple_Shipping::get_instance(), 'payment_receipt_after'), 10 );
		
		/**
		 * Use remove all actions since above codes not working
		 */
		remove_all_actions( 'edd_payment_receipt_after', 10 );
		add_action( 'edd_payment_receipt_after', array( $this, 'payment_receipt_after' ), 10, 2 );
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

	public function pp_edd_email_tags($email_tags){

		if(empty($email_tags) || !is_array($email_tags) || !class_exists('Charitable_EDD_Cart'))
			return $email_tags;

		foreach ($email_tags as $key => $email_tag) {
			if(isset($email_tag['tag']) && ($email_tag['tag'] != 'download_list') )
				continue;

			$email_tags[$key]['function'] = ('text/html' == EDD()->emails->get_content_type()) ? 'pp_edd_email_tag_download_list' : 'pp_edd_email_tag_download_list_plain';
		}

		// echo "<pre>";
		// print_r($email_tags);
		// echo "</pre>";
		// exit();

		return $email_tags;
	}

	public function change_edd_strpe_args($args, $purchase_data){

		if(isset($args['description'])){
			$args['description'] = 'Greeks4Good.com';
		}
		if(isset($args['statement_descriptor'])){
			$args['statement_descriptor'] = 'Greeks4Good.com';
		}

		return $args;
	}

	public function uncheck_price_option_by_default($checked_key, $download_id, $key){
		$checked_key = -1;
		return $checked_key;
	}	

	public function change_edd_purchase_link_top(){
		
	}

	public function edd_purchase_form_variation_quantity_input_zero($quantity_input, $download_id, $key, $price){
		ob_start();

		// get default option
		// $default = edd_get_default_variable_price( $download_id );
		$value = 0; // if default option value will be 1 as default
		?>
			<div class="edd_download_quantity_wrapper edd_download_quantity_price_option_<?php echo sanitize_key( $price['name'] ) ?>">
				<span class="edd_price_option_sep">&nbsp;x&nbsp;</span>
				<input type="number" min="0" step="1" name="edd_download_quantity_<?php echo esc_attr( $key ) ?>" class="edd-input edd-item-quantity" value="<?php echo $value; ?>" />
			</div>
		<?php
		$quantity_input = ob_get_clean();

		return $quantity_input;
	}

	public function includes(){
		
	}

}

$GLOBALS['philanthropy_edd'] = new Philanthropy_EDD();