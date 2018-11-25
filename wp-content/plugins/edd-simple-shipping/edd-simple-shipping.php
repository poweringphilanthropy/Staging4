<?php
/*
Plugin Name: Easy Digital Downloads - Simple Shipping
Plugin URI: https://easydigitaldownloads.com/downloads/simple-shipping
Description: Provides the ability to charge simple shipping fees for physical products in EDD
Version: 2.3.7
Author: Easy Digital Downloads
Author URI:  https://easydigitaldownloads.com
Contributors: easydigitaldownloads, mordauk, cklosows
Text Domain: edd-simple-shipping
Domain Path: languages
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

class EDD_Simple_Shipping {

	private static $instance;

	/**
	 * Flag for domestic / international shipping
	 *
	 * @since 1.0
	 *
	 * @access protected
	 */
	protected $is_domestic = true;

	/**
	 * Flag for whether Frontend Submissions is enabled
	 *
	 * @since 2.0
	 *
	 * @access protected
	 */
	protected $is_fes = false;

	public $plugin_path = null;
	public $plugin_url  = null;

	public $settings;
	public $metabox;
	public $admin;
	public $tracking;
	public $fes;

	/**
	 * Get active object instance
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new EDD_Simple_Shipping();
		}

		return self::$instance;
	}

	/**
	 * Initialise the rest of the plugin
	 */
	private function __construct() {

		// do nothing if EDD is not activated
		if( ! class_exists( 'Easy_Digital_Downloads', false ) ) {
			return;
		}

		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		$this->plugin_url  = untrailingslashit( plugin_dir_url( __FILE__ ) );
		$this->setup_constants();
		$this->filters();
		$this->actions();
		$this->init();
	}

	private function setup_constants() {
		if ( ! defined( 'EDD_SIMPLE_SHIPPING_VERSION' ) ) {
			define( 'EDD_SIMPLE_SHIPPING_VERSION', '2.3.7' );
		}
	}

	public function filters() {
		add_filter( 'edd_purchase_data_before_gateway',    array( $this, 'set_shipping_info' ), 10, 2 );
		add_filter( 'edd_paypal_redirect_args',            array( $this, 'send_shipping_to_paypal' ), 10, 2 );
		add_filter( 'edd_sale_notification',               array( $this, 'admin_sales_notice' ), 10, 3 );
	}

	public function actions() {
		add_action( 'init',                                  array( $this, 'textdomain' ) );
		add_action( 'init',                                  array( $this, 'apply_shipping_fees' ) );
		add_action( 'wp_ajax_edd_get_shipping_rate',         array( $this, 'ajax_shipping_rate' ) );
		add_action( 'wp_ajax_nopriv_edd_get_shipping_rate',  array( $this, 'ajax_shipping_rate' ) );
		add_action( 'edd_purchase_form_after_cc_form',       array( $this, 'address_fields' ), 999 );
		add_action( 'edd_checkout_error_checks',             array( $this, 'error_checks' ), 10, 2 );
		add_action( 'edd_view_order_details_billing_after',  array( $this, 'show_shipping_details' ), 10 );
		add_action( 'edd_insert_payment',                    array( $this, 'set_as_not_shipped' ), 10, 2 );
		add_action( 'edd_edit_payment_bottom',               array( $this, 'edit_payment_option' ) );

		add_action( 'edd_profile_editor_address',            array( $this, 'profile_editor_addresses' ), 10 );
		add_action( 'edd_profile-remove-shipping-address',   array( $this, 'process_profile_editor_remove_address' ) );

		add_action( 'admin_enqueue_scripts',                 array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts',                    array( $this, 'enqueue_styles' ) );

	}

	/**
	 * Run action and filter hooks.
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return void
	 */
	protected function init() {

		// Include the necessary files.
		require_once $this->plugin_path . '/includes/admin/settings.php';
		require_once $this->plugin_path . '/includes/tracking.php';
		require_once $this->plugin_path . '/includes/privacy-functions.php';

		if ( is_admin() ) {
			require_once $this->plugin_path . '/includes/admin/admin.php';
			require_once $this->plugin_path . '/includes/admin/metabox.php';
			require_once $this->plugin_path . '/includes/admin/upgrades.php';
		}

		// Load all the settings into local variables so we can use them.
		$this->settings = new EDD_Simple_shipping_Settings();
		$this->tracking = new EDD_Simple_Shipping_Tracking();
		if ( is_admin() ) {
			$this->admin = new EDD_Simple_Shipping_Admin();
			$this->metabox = new EDD_Simple_shipping_Metabox();
		}

		$this->plugins_check();

		// auto updater
		if( is_admin() ) {

			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Simple Shipping', EDD_SIMPLE_SHIPPING_VERSION, 'Pippin Williamson', 'edd_simple_shipping_license_key' );
			}
		}
	}

	/**
	 * Register any scripts we need for Simple Shipping
	 *
	 * @since 2.3
	 * @return void
	 */
	public function admin_scripts() {
		wp_register_script( 'edd-simple-shipping-admin', $this->plugin_url . '/assets/js/admin-scripts.js', array( 'jquery' ), EDD_SIMPLE_SHIPPING_VERSION );
		wp_enqueue_script( 'edd-simple-shipping-admin' );

		wp_register_style( 'edd-simple-shipping-admin', $this->plugin_url . '/assets/css/admin-styles.css', EDD_SIMPLE_SHIPPING_VERSION );
		wp_enqueue_style( 'edd-simple-shipping-admin' );
	}

	/**
	 * Register any styles we need for Simple Shipping
	 *
	 * @since 2.3
	 * @return void
	 */
	public function enqueue_styles() {
		$needs_styles = edd_is_purchase_history_page();

		if ( false === $needs_styles ) {
			return;
		}

		wp_register_style( 'edd-simple-shipping-styles', $this->plugin_url . '/assets/css/styles.css', EDD_SIMPLE_SHIPPING_VERSION );
		wp_enqueue_style( 'edd-simple-shipping-styles' );
	}

	/**
	 * Load plugin text domain
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	public function textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = $this->plugin_path . '/languages/';
		$lang_dir = apply_filters( 'edd_simple_shipping_lang_directory', $lang_dir );

		// Load the translations
		load_plugin_textdomain( 'edd-simple-shipping', false, $lang_dir );

	}

	/**
	 * Determine if dependent plugins are loaded and set flags appropriately
	 *
	 * @since 2.0
	 *
	 * @access private
	 * @return void
	 */
	public function plugins_check() {

		if( class_exists( 'EDD_Front_End_Submissions' ) ) {
			$this->is_fes = true;
			require_once $this->plugin_path . '/includes/integrations/edd-fes.php';
			$this->fes = new EDD_Simple_Shipping_FES();


			if ( ! isset( $this->admin ) ) {
				require_once $this->plugin_path . '/includes/admin/admin.php';
				$this->admin = new EDD_Simple_Shipping_Admin();
			}
			add_action( 'fes-order-table-column-title', array( $this->admin, 'shipped_column_header' ), 10 );
			add_action( 'fes-order-table-column-value', array( $this->admin, 'shipped_column_value' ), 10 );

			add_action( 'edd_payment_receipt_after',    array( $this, 'payment_receipt_after' ), 10, 2 );
			add_action( 'edd_toggle_shipped_status',    array( $this, 'frontend_toggle_shipped_status' ) );

			if ( version_compare( fes_plugin_version, '2.3', '>=' ) ) {
				add_action( 'fes_load_fields_require',  array( $this->fes, 'edd_fes_simple_shipping' ) );
			} else {
				add_action( 'fes_custom_post_button',               array( $this->fes, 'edd_fes_simple_shipping_field_button' ) );
				add_action( 'fes_admin_field_edd_simple_shipping',  array( $this->fes, 'edd_fes_simple_shipping_admin_field' ), 10, 3 );
				add_filter( 'fes_formbuilder_custom_field',         array( $this->fes, 'edd_fes_simple_shipping_formbuilder_is_custom_field' ), 10, 2 );
				add_action( 'fes_submit_submission_form_bottom',    array( $this->fes, 'edd_fes_simple_shipping_save_custom_fields' ) );
				add_action( 'fes_render_field_edd_simple_shipping', array( $this->fes, 'edd_fes_simple_shipping_field' ), 10, 3 );
			}
		}

	}

	/**
	 * Determine if a product has snipping enabled
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function item_has_shipping( $item_id = 0, $price_id = 0 ) {
		$enabled          = get_post_meta( $item_id, '_edd_enable_shipping', true );
		$variable_pricing = edd_has_variable_prices( $item_id );

		if( $variable_pricing && ! $this->price_has_shipping( $item_id, $price_id ) ) {
			$enabled = false;
		}

		return (bool) apply_filters( 'edd_simple_shipping_item_has_shipping', $enabled, $item_id );
	}


	/**
	 * Determine if a price option has snipping enabled
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function price_has_shipping( $item_id = 0, $price_id = 0 ) {
		$prices = edd_get_variable_prices( $item_id );
		$ret    = false;

		// Backwards compatibility checks
		$has_shipping = isset( $prices[ $price_id ]['shipping'] ) ? $prices[ $price_id ]['shipping'] : false;
		if ( false !== $has_shipping && ! is_array( $has_shipping ) ) {
			$ret = true;
		} elseif ( is_array( $has_shipping ) ) {
			$domestic = $has_shipping['domestic'];
			$international = $has_shipping['international'];

			// If the price has either domestic or international prices, we have shipping.
			$ret = ( ! empty( $domestic ) || ! empty( $international ) ) ? true : false;
		}

		// Keep this old filter for backwards compatibility.
		$ret = apply_filters( 'edd_simple_shipping_price_hasa_shipping', $ret, $item_id, $price_id );

		return (bool) apply_filters( 'edd_simple_shipping_price_has_shipping', $ret, $item_id, $price_id );
	}

	/**
	 * Get the shipping price for a specific price ID
	 *
	 * @since 2.2.3
	 * @param int    $download_id The Download ID to look up.
	 * @param null   $price_id    The Price ID to look up.
	 * @param string $region      The region to pull for (domestic or international).
	 *
	 * @return float
	 */
	public function get_price_shipping_cost( $download_id = 0, $price_id = null, $region = 'domestic' ) {
		$download = new EDD_Download( $download_id );
		$amount   = 0;
		if ( $download->has_variable_prices() ) {
			$prices = $download->get_prices();
			foreach ( $prices as $key => $price ) {

				// If it's not the right price ID, move along.
				if ( (int) $key !== (int) $price_id ) {
					continue;
				}

				if ( isset( $price['shipping'] ) && is_array( $price['shipping'] ) ) {
					// If the region requested isn't set, continue;
					if ( ! isset( $price['shipping'][ $region ] ) ) {
						continue;
					}

					$amount = $price['shipping'][ $region ];
				} elseif ( isset( $price['shipping'] ) ) {
					switch( $region ) {
						case 'domestic':
							$amount = get_post_meta( $download_id, '_edd_shipping_domestic', true );
							break;
						case 'international':
							$amount = get_post_meta( $download_id, '_edd_shipping_international', true );
							break;
					}
				}
			}
		}

		return apply_filters( 'edd_shipping_variable_price_cost', (float) $amount, $download_id, $price_id, $region );
	}


	/**
	 * Determine if shipping costs need to be calculated for the cart
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function cart_needs_shipping() {
		$cart_contents = edd_get_cart_contents();
		$ret = false;
		if( is_array( $cart_contents ) ) {
			foreach( $cart_contents as $item ) {
				$price_id = isset( $item['options']['price_id'] ) ? (int) $item['options']['price_id'] : null;
				if( $this->item_has_shipping( $item['id'], $price_id ) ) {
					$ret = true;
					break;
				}
			}
		}
		return (bool) apply_filters( 'edd_simple_shipping_cart_needs_shipping', $ret );
	}


	/**
	 * Get the base country (where the store is located)
	 *
	 * This is used for determining if customer should be charged domestic or international shipping
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_base_region( $download_id = 0 ) {

		if( ! empty( $download_id ) ) {

			$author  = get_post_field( 'post_author', $download_id );
			$country = get_user_meta( $author, 'vendor_country', true );
			if( $country ) {
				$countries   = edd_get_country_list();
				$code        = array_search( $country, $countries );
				if( false !== $code ) {
					$base_region = $code;
				}
			}

		}

		$base_region = isset( $base_region ) ? $base_region : edd_get_option( 'edd_simple_shipping_base_country', 'US' );

		return $base_region;

	}

	/**
	 * Update the shipping costs via ajax
	 *
	 * This fires when the customer changes the country they are shipping to
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	public function ajax_shipping_rate() {

		// Calculate new shipping
		$shipping = $this->apply_shipping_fees();

		ob_start();
		edd_checkout_cart();
		$cart = ob_get_clean();
		
		$response = array(
			'html'  => $cart,
			'total' => html_entity_decode( edd_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
		);
		$charges = pp_get_donor_fees_on_checkout();
		if($_POST['donor_selection'] == "true"){
			if($charges['donor-covers-fee'] ): 
			$currency_helper = charitable_get_currency_helper();
			$response['total'] = html_entity_decode( $currency_helper->get_monetary_amount( $charges['charge-to-donor']), ENT_COMPAT, 'UTF-8' );
			$response['total_fees'] = html_entity_decode( $currency_helper->get_monetary_amount( $charges['total-fee-amount']), ENT_COMPAT, 'UTF-8' );
			$response['is_donor'] = true;
			endif;
		}
		echo json_encode( $response );

		die();
	}


	/**
	 * Apply the shipping fees to the cart
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	public function apply_shipping_fees() {

		$this->remove_shipping_fees();

		if( ! $this->cart_needs_shipping() ) {
			return;
		}

		$cart_contents = edd_get_cart_content_details();

		if( ! is_array( $cart_contents ) ) {
			return;
		}

		$amount = 0.00;
		$no_tax = edd_get_option( 'simple_shipping_disable_tax_on_shipping', false );

		foreach( $cart_contents as $key => $item ) {

			$price_id = isset( $item['item_number']['options']['price_id'] ) ? (int) $item['item_number']['options']['price_id'] : null;

			if( ! $this->item_has_shipping( $item['id'], $price_id ) ) {
				continue;
			}

			if( is_user_logged_in() && empty( $_POST['country'] ) ) {

				$address = get_user_meta( get_current_user_id(), '_edd_user_address', true );
				if( isset( $address['country'] ) && $address['country'] != $this->get_base_region( $item['id'] ) ) {
					$this->is_domestic = false;
				} else {
					$this->is_domestic = true;
				}

			} else {

				$country = ! empty( $_POST['country'] ) ? $_POST['country'] : $this->get_base_region();

				if( $country != $this->get_base_region( $item['id'] ) ) {
					$this->is_domestic = false;
				} else {
					$this->is_domestic = true;
				}
			}

			if( $this->is_domestic ) {
				if ( null !== $price_id ) {
					$amount = $this->get_price_shipping_cost( $item['id'], $price_id, 'domestic' );
				} else {
					$amount = (float) get_post_meta( $item['id'], '_edd_shipping_domestic', true );
				}
			} else {
				if ( null !== $price_id ) {
					$amount = $this->get_price_shipping_cost( $item['id'], $price_id, 'international' );
				} else {
					$amount = (float) get_post_meta( $item['id'], '_edd_shipping_international', true );
				}
			}

			$has_shipping = false;
			$fee_label    = sprintf( __( '%s Shipping', 'edd-simple-shipping' ), get_the_title( $item['id'] ) );
			if ( ! empty( $item['fees'] ) ) {
				foreach ( $item['fees'] as $fee ) {
					if ( $fee['label'] === $fee_label ) {
						$has_shipping = true;
						break;
					}
				}
			}

			if( $amount > 0 && false === $has_shipping ) {

				EDD()->fees->add_fee( array(
					'amount'      => $amount,
					'label'       => $fee_label,
					'id'          => 'simple_shipping_' . $key,
					'download_id' => $item['id'],
					'price_id'    => $price_id,
					'no_tax'      => $no_tax,
				) );

			}

		}

	}

	/**
	 * Removes all shipping fees from the cart
	 *
	 * @since 2.1
	 *
	 * @access public
	 * @return void
	 */
	public function remove_shipping_fees() {

		$fees = EDD()->fees->get_fees( 'all' );
		if( empty( $fees ) ) {
			return;
		}
		foreach( $fees as $key => $fee ) {

			if( false === strpos( $key, 'simple_shipping' ) ) {
				continue;
			}

			unset( $fees[ $key ] );

		}
		EDD()->session->set( 'edd_cart_fees', $fees );

	}


	/**
	 * Determine if the shipping fields should be displayed
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function needs_shipping_fields() {
		return $this->cart_needs_shipping();

	}


	/**
	 * Determine if the current payment method has billing fields
	 *
	 * If no billing fields are present, the shipping fields are always displayed
	 *
	 * @since 1.0
	 *
	 * @access protected
	 * @return bool
	 */
	protected function has_billing_fields() {

		$did_action = did_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );
		if( ! $did_action && edd_use_taxes() )
			$did_action = did_action( 'edd_purchase_form_after_cc_form', 'edd_checkout_tax_fields' );

		// Have to assume all gateways are using the default CC fields (they should be)
		return ( $did_action || isset( $_POST['card_address'] ) );

	}


	/**
	 * Shipping info fields
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function address_fields() {

		if( ! $this->needs_shipping_fields() )
			return;

		$display = $this->has_billing_fields() ? ' style="display:none;"' : '';

		ob_start();
		?>
		<script type="text/javascript">var edd_global_vars; jQuery(document).ready(function($) {

				$('body').on('change', 'select[name=shipping_country],select[name=billing_country]',function() {
					var billing = true;

					if( $('select[name=billing_country]').length && ! $('#edd_simple_shipping_show').is(':checked') ) {
						var val = $('select[name=billing_country]').val();
					} else {
						var val = $('select[name=shipping_country]').val();
						billing = false;
					}

					if( billing && edd_global_vars.taxes_enabled == '1' ) {
						return; // EDD core will recalculate on billing address change if taxes are enabled
					}

					if( val == 'US' ) {
						$('#shipping_state_other').hide();$('#shipping_state_us').show();$('#shipping_state_ca').hide();
					} else if(  val =='CA') {
						$('#shipping_state_other').hide();$('#shipping_state_us').hide();$('#shipping_state_ca').show();
					} else {
						$('#shipping_state_other').show();$('#shipping_state_us').hide();$('#shipping_state_ca').hide();
					}

					edd_shipping_trigger_address_change( val );
				});

				$('body').on('edd_taxes_recalculated', function( event, data ) {

					if( $('#edd_simple_shipping_show').is(':checked') ) {
						return;
					}

					edd_shipping_trigger_address_change( data.postdata.billing_country, data.postdata.state );

				});

				$('select#edd-gateway, input.edd-gateway').change( function (e) {
					edd_shipping_trigger_address_change( 'US' );
				});

				$('#edd_simple_shipping_show').change(function() {
					$('#edd_simple_shipping_fields_wrap').toggle();
				});

				$('body').on('change', '.edd-existing-shipping-addresses', function() {
					var existing_value = $(this).val();
					var target  = $('#edd-shipping-new-address-wrapper');
					var country = false;

					if ( 'new' === existing_value ) {
						target.show();
						if ( $( 'select.shipping-country' ).val().length ) {
							country = $( 'select.shipping-country' ).val();
						}
					} else {
						target.hide();
						country = $(this).find(':selected').data('country');
					}

					if ( false !== country ) {
						edd_shipping_trigger_address_change( country );
					}
				});

				/**
				 * Given a country and state, trigger an update of the shipping charges
				 * @param country
				 * @param state
				 */
				function edd_shipping_trigger_address_change( country = '', state = '' ) {
					var purchase_button = $('#edd-purchase-button');
					purchase_button.prop("disabled", true);
					var is_donor = $("#donor-covers-fee").is(":checked");

					var postData = {
						action: 'edd_get_shipping_rate',
						country: country,
						billing_country: country,
						donor_selection : is_donor
					};

					if ( state != '' ) {
						postData.state = state;
					}

					$.ajax({
						type: "POST",
						data: postData,
						dataType: "json",
						url: edd_global_vars.ajaxurl,
						success: function (response) {
							$('#edd_checkout_cart_form').replaceWith(response.html);
							$('.edd_cart_amount').each(function() {
								$(this).text(response.total);
							});
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});
					purchase_button.prop("disabled", false);
				}
			});</script>

		<div id="edd_simple_shipping">
			<?php if( $this->has_billing_fields() ) : ?>
				<fieldset id="edd_simple_shipping_diff_address">
					<label for="edd_simple_shipping_show">
						<input type="checkbox" id="edd_simple_shipping_show" name="edd_use_different_shipping" value="1"/>
						<?php _e( 'Ship to Different Address?', 'edd-simple-shipping' ); ?>
					</label>
				</fieldset>
			<?php endif; ?>
			<div id="edd_simple_shipping_fields_wrap"<?php echo $display; ?>>
				<fieldset id="edd_simple_shipping_fields">
					<?php do_action( 'edd_shipping_address_top' ); ?>
					<legend><?php _e( 'Shipping Details', 'edd-simple-shipping' ); ?></legend>
					<?php
						$existing_addresses = array();
						if ( is_user_logged_in() ) {
							$customer  = EDD()->customers->get_customer_by( 'user_id', get_current_user_id() );
							if ( ! empty( $customer->id ) ) {
								$existing_addresses = $this->get_customer_shipping_addresses( $customer->id );
							}
						}

						$options = array();
						foreach ( $existing_addresses as $key => $values ) {
							$address_label = array();
							$address_label[] = ! empty( $values['address'] )  ? $values['address']  : '';
							$address_label[] = ! empty( $values['address2'] ) ? $values['address2'] : '';
							$address_label[] = ! empty( $values['city'] )     ? $values['city']     : '';
							$address_label[] = ! empty( $values['state'] )    ? $values['state']    : '';
							$address_label[] = ! empty( $values['zip'] )      ? $values['zip']      : '';
							$address_label   = array_values( array_filter( $address_label ) );

							$options[ $key ] = implode( ', ', $address_label );
						}

						$show_address_fields = empty( $options ) ? '' : ' style="display:none;';
					?>
					<?php if ( ! empty( $options ) ) : ?>
					<div class="edd-existing-shipping-addresses-wrapper">
						<p>
							<select name="existing_shipping_address" class="edd-select edd-existing-shipping-addresses" id="edd-existing-shipping-addresses">
								<?php foreach ( $options as $key => $option ) : ?>
									<?php $address_country = $existing_addresses[ $key ]['country']; ?>
									<option value="<?php echo $key; ?>" data-country="<?php echo $address_country; ?>"><?php echo $option; ?></option>
								<?php endforeach; ?>
								<option value="new"><?php _e( 'Add new address', 'edd-simple-shipping' ); ?></option>
							</select>
						</p>
					</div>
					<?php endif; ?>
					<div id="edd-shipping-new-address-wrapper"<?php echo $show_address_fields; ?>>
						<p id="edd-shipping-address-wrap">
							<label class="edd-label"><?php _e( 'Shipping Address', 'edd-simple-shipping' ); ?></label>
							<span class="edd-description"><?php _e( 'The address to ship your purchase to.', 'edd-simple-shipping' ); ?></span>
							<input type="text" name="shipping_address" class="shipping-address edd-input" placeholder="<?php _e( 'Address line 1', 'edd-simple-shipping' ); ?>"/>
						</p>
						<p id="edd-shipping-address-2-wrap">
							<label class="edd-label"><?php _e( 'Shipping Address Line 2', 'edd-simple-shipping' ); ?></label>
							<span class="edd-description"><?php _e( 'The suite, apt no, PO box, etc, associated with your shipping address.', 'edd-simple-shipping' ); ?></span>
							<input type="text" name="shipping_address_2" class="shipping-address-2 edd-input" placeholder="<?php _e( 'Address line 2', 'edd-simple-shipping' ); ?>"/>
						</p>
						<p id="edd-shipping-city-wrap">
							<label class="edd-label"><?php _e( 'Shipping City', 'edd-simple-shipping' ); ?></label>
							<span class="edd-description"><?php _e( 'The city for your shipping address.', 'edd-simple-shipping' ); ?></span>
							<input type="text" name="shipping_city" class="shipping-city edd-input" placeholder="<?php _e( 'City', 'edd-simple-shipping' ); ?>"/>
						</p>
						<p id="edd-shipping-country-wrap">
							<label class="edd-label"><?php _e( 'Shipping Country', 'edd-simple-shipping' ); ?></label>
							<span class="edd-description"><?php _e( 'The country for your shipping address.', 'edd-simple-shipping' ); ?></span>
							<select name="shipping_country" class="shipping-country edd-select">
								<?php
								$countries = edd_get_country_list();
								foreach( $countries as $country_code => $country ) {
									echo '<option value="' . $country_code . '">' . $country . '</option>';
								}
								?>
							</select>
						</p>
						<p id="edd-shipping-state-wrap">
							<label class="edd-label"><?php _e( 'Shipping State / Province', 'edd-simple-shipping' ); ?></label>
							<span class="edd-description"><?php _e( 'The state / province for your shipping address.', 'edd-simple-shipping' ); ?></span>
							<input type="text" size="6" name="shipping_state_other" id="shipping_state_other" class="shipping-state edd-input" placeholder="<?php _e( 'State / Province', 'edd-simple-shipping' ); ?>" style="display:none;"/>
							<select name="shipping_state_us" id="shipping_state_us" class="shipping-state edd-select">
								<?php
								$states = edd_get_states_list();
								foreach( $states as $state_code => $state ) {
									echo '<option value="' . $state_code . '">' . $state . '</option>';
								}
								?>
							</select>
							<select name="shipping_state_ca" id="shipping_state_ca" class="shipping-state edd-select" style="display: none;">
								<?php
								$provinces = edd_get_provinces_list();
								foreach( $provinces as $province_code => $province ) {
									echo '<option value="' . $province_code . '">' . $province . '</option>';
								}
								?>
							</select>
						</p>
						<p id="edd-shipping-zip-wrap">
							<label class="edd-label"><?php _e( 'Shipping Zip / Postal Code', 'edd-simple-shipping' ); ?></label>
							<span class="edd-description"><?php _e( 'The zip / postal code for your shipping address.', 'edd-simple-shipping' ); ?></span>
							<input type="text" size="4" name="shipping_zip" class="shipping-zip edd-input" placeholder="<?php _e( 'Zip / Postal code', 'edd-simple-shipping' ); ?>"/>
						</p>
					</div>
					<?php do_action( 'edd_shipping_address_bottom' ); ?>
				</fieldset>
			</div>
		</div>
		<?php 	echo ob_get_clean();
	}


	/**
	 * Perform error checks during checkout
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function error_checks( $valid_data, $post_data ) {

		// Only perform error checks if we have a product that needs shipping
		if( ! $this->cart_needs_shipping() ) {
			return;
		}

		// Check to see if shipping is different than billing
		if( isset( $post_data['edd_use_different_shipping'] ) || ! $this->has_billing_fields() ) {

			if ( ! isset( $post_data['existing_shipping_address'] ) || 'new' === $post_data['existing_shipping_address'] ) {

				// Shipping address is different

				if ( empty( $post_data[ 'shipping_address' ] ) ) {
					edd_set_error( 'missing_address', __( 'Please enter a shipping address', 'edd-simple-shipping' ) );
				}

				if ( empty( $post_data[ 'shipping_city' ] ) ) {
					edd_set_error( 'missing_city', __( 'Please enter a city for shipping', 'edd-simple-shipping' ) );
				}

				if ( empty( $post_data[ 'shipping_zip' ] ) ) {
					edd_set_error( 'missing_zip', __( 'Please enter a zip/postal code for shipping', 'edd-simple-shipping' ) );
				}

				if ( empty( $post_data[ 'shipping_country' ] ) ) {
					edd_set_error( 'missing_country', __( 'Please select your country', 'edd-simple-shipping' ) );
				}

				if ( 'US' == $post_data[ 'shipping_country' ] ) {

					if ( empty( $post_data[ 'shipping_state_us' ] ) ) {
						edd_set_error( 'missing_state', __( 'Please select your state', 'edd-simple-shipping' ) );
					}

				} elseif ( 'CA' == $post_data[ 'shipping_country' ] ) {

					if ( empty( $post_data[ 'shipping_state_ca' ] ) ) {
						edd_set_error( 'missing_province', __( 'Please select your province', 'edd-simple-shipping' ) );
					}

				}

			}

		} else {

			// Shipping address is the same as billing
			if( empty( $post_data['card_address'] ) ) {
				edd_set_error( 'missing_address', __( 'Please enter a shipping address', 'edd-simple-shipping' ) );
			}

			if( empty( $post_data['card_city'] ) ) {
				edd_set_error( 'missing_city', __( 'Please enter a city for shipping', 'edd-simple-shipping' ) );
			}

			if( empty( $post_data['card_zip'] ) ) {
				edd_set_error( 'missing_zip', __( 'Please enter a zip/postal code for shipping', 'edd-simple-shipping' ) );
			}

			if( 'US' == $post_data['billing_country'] ) {

				if( empty( $post_data['card_state'] ) ) {
					edd_set_error( 'missing_state', __( 'Please select your state', 'edd-simple-shipping' ) );
				}

			} elseif( 'CA' == $post_data['billing_country'] ) {

				if( empty( $post_data['card_state'] ) ) {
					edd_set_error( 'missing_province', __( 'Please select your province', 'edd-simple-shipping' ) );
				}

			}

		}

	}


	/**
	 * Attach our shipping info to the payment gateway data
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return array
	 */
	public function set_shipping_info( $purchase_data, $valid_data ) {

		if( ! $this->cart_needs_shipping() ) {
			return $purchase_data;
		}

		$shipping_info = array();
		$customer      = EDD()->customers->get_customer_by( 'user_id', get_current_user_id() );

		// Check to see if shipping is different than billing
		if( isset( $_POST['edd_use_different_shipping'] ) || ! $this->has_billing_fields() ) {
			if ( ! isset( $_POST['existing_shipping_address'] ) || 'new' === $_POST['existing_shipping_address'] ) {
				$shipping_info['address']  = sanitize_text_field( $_POST['shipping_address'] );
				$shipping_info['address2'] = sanitize_text_field( $_POST['shipping_address_2'] );
				$shipping_info['city']     = sanitize_text_field( $_POST['shipping_city'] );
				$shipping_info['zip']      = sanitize_text_field( $_POST['shipping_zip'] );
				$shipping_info['country']  = sanitize_text_field( $_POST['shipping_country'] );

				// Shipping address is different
				switch ( $_POST['shipping_country'] ) :
					case 'US' :
						$shipping_info['state'] = isset( $_POST['shipping_state_us'] ) ? sanitize_text_field( $_POST['shipping_state_us'] ) : '';
						break;
					case 'CA' :
						$shipping_info['state'] = isset( $_POST['shipping_state_ca'] ) ? sanitize_text_field( $_POST['shipping_state_ca'] ) : '';
						break;
					default :
						$shipping_info['state'] = isset( $_POST['shipping_state_other'] ) ? sanitize_text_field( $_POST['shipping_state_other'] ) : '';
						break;
				endswitch;
			} else {
				if ( ! empty( $customer->id ) ) {
					$address_key   = absint( $_POST['existing_shipping_address'] );
					$shipping_info = $this->get_customer_shipping_address( $customer->id, $address_key );
				}
			}

		} else {

			$shipping_info['address']  = sanitize_text_field( $_POST['card_address'] );
			$shipping_info['address2'] = sanitize_text_field( $_POST['card_address_2'] );
			$shipping_info['city']     = sanitize_text_field( $_POST['card_city'] );
			$shipping_info['zip']      = sanitize_text_field( $_POST['card_zip'] );
			$shipping_info['state']    = sanitize_text_field( $_POST['card_state'] );
			$shipping_info['country']  = sanitize_text_field( $_POST['billing_country'] );

		}

		if ( ! empty( $customer->id ) ) {
			$this->add_customer_shipping_address( $customer->id, $shipping_info );
		}

		$purchase_data['user_info']['shipping_info'] = $shipping_info;

		return $purchase_data;

	}


	/**
	 * Sets up the shipping details for PayPal
	 *
	 * This makes it possible to use the Print Shipping Label feature in PayPal
	 *
	 * @since 1.1
	 *
	 * @access public
	 * @return array
	 */
	public function send_shipping_to_paypal( $paypal_args = array(), $purchase_data = array() ) {

		if( ! $this->cart_needs_shipping() ) {
			return $paypal_args;
		}

		$shipping_info = $purchase_data['user_info']['shipping_info'];

		$paypal_args['no_shipping'] = '0';
		$paypal_args['address1']    = $shipping_info['address'];
		$paypal_args['address2']    = $shipping_info['address2'];
		$paypal_args['city']        = $shipping_info['city'];
		$paypal_args['state']       = $shipping_info['country'] == 'US' ? $shipping_info['state'] : null;
		$paypal_args['country']     = $shipping_info['country'];
		$paypal_args['zip']         = $shipping_info['zip'];


		return $paypal_args;

	}


	/**
	 * Set a purchase as not shipped
	 *
	 * This is so that we can grab all purchases in need of being shipped
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_as_not_shipped( $payment_id = 0, $payment_data = array() ) {

		$shipping_info = ! empty( $payment_data['user_info']['shipping_info'] ) ? $payment_data['user_info']['shipping_info'] : false;

		if( ! $shipping_info ) {
			return;
		}

		// Indicate that this purchase needs shipped
		update_post_meta( $payment_id, '_edd_payment_shipping_status', '1' );

	}

	/**
	 * Determins if a payment needs shipping.
	 * @param int $payment_id
	 *
	 * @since 2.2.3
	 * @return bool
	 */
	public function payment_needs_shipping( $payment_id = 0 ) {
		$payment = new EDD_Payment( $payment_id );

		if ( empty( $payment->ID ) ) {
			return false;
		}

		$user_info     = edd_get_payment_meta_user_info( $payment->ID );

		$needs_shipping = ! empty( $user_info['shipping_info'] ) ? true : false;

		return apply_filters( 'edd_payment_needs_shipping', $needs_shipping, $payment_id );
	}

	/**
	 * Display shipping details in the View Details popup
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function show_shipping_details( $payment_id = 0 ) {

		if( empty( $payment_id ) ) {
			$payment_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		}

		$needs_shipping = $this->payment_needs_shipping( $payment_id );

		if( ! $needs_shipping ) {
			return;
		}

		$user_info = edd_get_payment_meta_user_info( $payment_id );
		$address   = ! empty( $user_info['shipping_info'] ) ? $user_info['shipping_info'] : array();

		$status  = get_post_meta( $payment_id, '_edd_payment_shipping_status', true );

		$shipped = $status == '2' ? true : false;
		?>
		<div id="edd-shipping-details" class="postbox">
			<h3 class="hndle">
				<span><?php _e( 'Shipping Address', 'edd' ); ?></span>
			</h3>
			<div class="inside edd-clearfix">

				<div id="edd-order-shipping-address">

					<div class="order-data-address">
						<div class="data column-container">
							<div class="column">
								<p>
									<strong class="order-data-address-line"><?php _e( 'Street Address Line 1:', 'edd' ); ?></strong><br/>
									<input type="text" name="edd-payment-shipping-address[0][address]" value="<?php esc_attr_e( $address['address'] ); ?>" class="medium-text" />
								</p>
								<p>
									<strong class="order-data-address-line"><?php _e( 'Street Address Line 2:', 'edd' ); ?></strong><br/>
									<input type="text" name="edd-payment-shipping-address[0][address2]" value="<?php esc_attr_e( $address['address2'] ); ?>" class="medium-text" />
								</p>

							</div>
							<div class="column">
								<p>
									<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'edd' ); ?></strong><br/>
									<input type="text" name="edd-payment-shipping-address[0][city]" value="<?php esc_attr_e( $address['city'] ); ?>" class="medium-text"/>

								</p>
								<p>
									<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'edd' ); ?></strong><br/>
									<input type="text" name="edd-payment-shipping-address[0][zip]" value="<?php esc_attr_e( $address['zip'] ); ?>" class="medium-text"/>

								</p>
							</div>
							<div class="column">
								<p id="edd-order-address-country-wrap">
									<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'edd' ); ?></strong><br/>
									<?php
									echo EDD()->html->select( array(
										'options'          => edd_get_country_list(),
										'name'             => 'edd-payment-shipping-address[0][country]',
										'selected'         => $address['country'],
										'show_option_all'  => false,
										'show_option_none' => false
									) );
									?>
								</p>
								<p id="edd-order-address-state-wrap">
									<strong class="order-data-address-line"><?php echo _x( 'State / Province:', 'State / province of address', 'edd' ); ?></strong><br/>
									<?php
									$states = edd_get_shop_states( $address['country'] );
									if( ! empty( $states ) ) {
										echo EDD()->html->select( array(
											'options'          => $states,
											'name'             => 'edd-payment-shipping-address[0][state]',
											'selected'         => $address['state'],
											'show_option_all'  => false,
											'show_option_none' => false
										) );
									} else { ?>
										<input type="text" name="edd-payment-shipping-address[0][state]" value="<?php esc_attr_e( $address['state'] ); ?>" class="medium-text"/>
									<?php
									} ?>
								</p>
							</div>
						</div>
						<label for="edd-payment-shipped">
							<input type="checkbox" id="edd-payment-shipped" name="edd-payment-shipped" value="1"<?php checked( $shipped, true ); ?>/>
							<?php _e( 'Check if this purchase has been shipped.', 'edd-simple-shipping' ); ?>
						</label>
					</div>
				</div><!-- /#edd-order-address -->

				<?php do_action( 'edd_payment_shipping_details', $payment_id ); ?>

			</div><!-- /.inside -->
		</div><!-- /#edd-shipping-details -->
	<?php
	}

	/**
	 * Add the shipping info to the admin sales notice
	 *
	 * @access      public
	 * @since       1.1
	 * @return      string
	 */
	public function admin_sales_notice( $email = '', $payment_id = 0, $payment_data = array() ) {

		$payment = new EDD_Payment( $payment_id );
		$shipped = $payment->get_meta( '_edd_payment_shipping_status' );


		// Only modify the email if shipping info needs to be added
		if( '1' == $shipped ) {

			$shipping_info = $payment->user_info['shipping_info'];

			$country_name = edd_get_country_name( $shipping_info['country'] );
			$state_name   = edd_get_state_name( $shipping_info['country'], $shipping_info['state'] );

			$email .= "<p><strong>" . __( 'Shipping Details:', 'edd-simple-shipping' ) . "</strong></p>";
			$email .= __( 'Address:', 'edd-simple-shipping' ) . " " . $shipping_info['address'] . "<br/>";
			$email .= __( 'Address Line 2:', 'edd-simple-shipping' ) . " " . $shipping_info['address2'] . "<br/>";
			$email .= __( 'City:', 'edd-simple-shipping' ) . " " . $shipping_info['city'] . "<br/>";
			$email .= __( 'Zip/Postal Code:', 'edd-simple-shipping' ) . " " . $shipping_info['zip'] . "<br/>";
			$email .= __( 'Country:', 'edd-simple-shipping' ) . " " . $country_name . "<br/>";
			$email .= __( 'State:', 'edd-simple-shipping' ) . " " . $state_name . "<br/>";

		}

		return $email;

	}

	/**
	 * Add the shipping address to the end of the payment receipt.
	 *
	 * @since 2.0
	 *
	 * @param object $payment
	 * @param array $edd_receipt_args
	 * @return void
	 */
	public function payment_receipt_after( $payment, $edd_receipt_args ) {

		$user_info = edd_get_payment_meta_user_info( $payment->ID );
		$address   = ! empty( $user_info['shipping_info'] ) ? $user_info['shipping_info'] : false;

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
		echo '<td>' . self::format_address( $user_info, $address ) . '<td>';
		echo '</tr>';

		if( current_user_can( 'edit_shop_payments' ) || ( function_exists( 'EDD_FES' ) && EDD_FES()->vendors->vendor_is_vendor() ) ) {

			echo '<tr>';
			echo '<td colspan="2">';
			echo '<a href="' . $toggle_url . '" class="edd-simple-shipping-toggle-status">' . $toggle_text . '</a>';
			echo '</td>';
			echo '</tr>';

		}
	}

	/**
	 * Format an address based on name and address information.
	 *
	 * For translators, a sample default address:
	 *
	 * (1) First (2) Last
	 * (3) Street Address 1
	 * (4) Street Address 2
	 * (5) City, (6) State (7) ZIP
	 * (8) Country
	 *
	 * @since 2.0
	 *
	 * @param array $user_info
	 * @param array $address
	 * @return string $address
	 */
	public static function format_address( $user_info, $address ) {

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

	/**
	 * Mark a payment as shipped.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	function frontend_toggle_shipped_status() {

		$payment_id = absint( $_GET[ 'order_id' ] );
		$status     = ! empty( $_GET['new_status'] ) ? absint( $_GET['new_status'] ) : '1';
		$key        = edd_get_payment_key( $payment_id );

		if( function_exists( 'EDD_FES' ) ) {
			if ( ! EDD_FES()->vendors->vendor_can_view_receipt( false, $key ) ) {
				wp_safe_redirect( wp_get_referer() ); exit;
			}
		}

		update_post_meta( $payment_id, '_edd_payment_shipping_status', $status );

		wp_safe_redirect( wp_get_referer() );

		exit();
	}

	/**
	 * Add a shipping address to the customer meta
	 *
	 * @since 2.2.3
	 * @param int   $customer_id
	 * @param array $address
	 *
	 * @return bool
	 */
	public function add_customer_shipping_address( $customer_id = 0, $address = array() ) {
		global $wpdb;
		if ( ! is_array( $address ) ) {
			return false;
		}

		$customer = new EDD_Customer( $customer_id );
		if ( empty( $customer->id ) ) {
			return false;
		}

		ksort( $address );

		// See if we have an existing address
		$serialized_address = serialize( $address );
		$address_query      = $wpdb->prepare( "SELECT meta_id FROM $wpdb->customermeta WHERE customer_id = %d AND meta_key ='shipping_address' AND meta_value = %s LIMIT 1", $customer->id, $serialized_address );
		$address_exists     = $wpdb->get_var( $address_query );

		if ( ! empty( $address_exists ) ) {
			return false;
		}

		return $customer->add_meta( 'shipping_address', $address );
	}

	/**
	 * Remove a specific customer shipping address
	 *
	 * @since 2.2.3
	 * @param int  $customer_id
	 * @param bool $address_key
	 *
	 * @return bool
	 *
	 */
	public function remove_customer_shipping_address( $customer_id = 0, $address_key = false ) {
		if ( false === $address_key ) {
			return false;
		}

		$customer = new EDD_Customer( $customer_id );
		if ( empty( $customer->id ) ) {
			return false;
		}

		$address = $this->get_customer_shipping_address( $customer_id, $address_key );
		if ( empty( $address ) ) {
			return false;
		}

		return $customer->delete_meta( 'shipping_address', $address );
	}

	/**
	 * Get a specific customer shipping address
	 *
	 * @since 2.2.3
	 * @param int $customer_id
	 * @param int $address_key
	 *
	 * @return array|boolean
	 */
	public function get_customer_shipping_address( $customer_id = 0, $address_key = 0 ) {
		$addresses = $this->get_customer_shipping_addresses( $customer_id );
		if ( isset( $addresses[ $address_key ] ) ) {
			return $addresses[ $address_key ];
		}

		return false;
	}

	/**
	 * Get all the customer shipping addresses
	 *
	 * @since 2.2.3
	 * @param int $customer_id
	 *
	 * @return array
	 */
	public function get_customer_shipping_addresses( $customer_id = 0 ) {
		$customer = new EDD_Customer( $customer_id );
		if ( empty( $customer->id ) ) {
			return array();
		}

		return $customer->get_meta( 'shipping_address', false );
	}

	/**
	 * Output the user shipping addresses on the profile editor
	 *
	 * @since 2.2.3
	 * @return void
	 */
	public function profile_editor_addresses() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$customer = EDD()->customers->get_customer_by( 'user_id', get_current_user_id() );
		if ( empty( $customer->id ) ) {
			return;
		}

		$addresses = $this->get_customer_shipping_addresses( $customer->id );
		if ( empty( $addresses ) ) {
			return;
		}
		?>

		<?php if ( ! empty( $addresses ) ) : ?>
			<legend for="edd_shipping_addresses"><?php _e( 'Shipping Addresses', 'edd-simple-shipping' ); ?></legend>
			<ul class="edd-profile-shipping-addresses">
			<?php foreach ( $addresses as $key => $address ) : ?>
				<li class="edd-profile-shipping-address">
					<span class="edd-ss-address"><?php echo $address['address']; ?></span>
					<span class="actions">
						&mdash;
						<?php
							$remove_url = wp_nonce_url(
								add_query_arg(
									array(
										'address_key' => $key,
										'edd_action'  => 'profile-remove-shipping-address',
										'redirect'    => esc_url( edd_get_current_page_url() ),
									)
								),
								'edd-remove-customer-shipping-address'
							);
						?>
						<a href="<?php echo $remove_url ?>" class="delete"><?php _e( 'Remove', 'edd-simple-shipping' ); ?></a>
					</span>
					<br />
					<?php if ( ! empty( $address['address2'] ) ) : ?>
						<span class="edd-ss-address2"><?php echo $address['address2']; ?></span><br />
					<?php endif; ?>

					<?php if ( ! empty( $address['city'] ) ) : ?>
						<span class="edd-ss-city"><?php echo $address['city']; ?></span>
					<?php endif; ?>

					<?php if ( ! empty( $address['state'] ) ) : ?>
						<span class="edd-ss-state">
							<?php if ( ! empty( $address['city'] ) ) : ?>,&nbsp;<?php endif; ?>
							<?php echo $address['state']; ?>
						</span>
						<br />
					<?php endif; ?>

					<?php if ( ! empty( $address['country'] ) ) : ?>
						<span class="edd-ss-country"><?php echo $address['country']; ?></span><br />
					<?php endif; ?>

					<?php if ( ! empty( $address['zip'] ) ) : ?>
						<span class="edd-ss-zip"><?php echo $address['zip']; ?></span><br />
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php
	}

	/**
	 * Process the 'remove' URL on the profile editor when customers wish to remove a shipping address
	 *
	 * @since  2.2.3
	 * @return void
	 */
	function process_profile_editor_remove_address() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Pending users can't edit their profile
		if ( edd_user_pending_verification() ) {
			return false;
		}

		// Nonce security
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd-remove-customer-shipping-address' ) ) {
			return false;
		}

		if ( ! isset( $_GET['address_key'] ) || (int) $_GET['address_key'] !== absint( $_GET['address_key'] ) ) {
			return false;
		}

		$customer = new EDD_Customer( get_current_user_id(), true );

		if ( (int) $customer->user_id !== (int) get_current_user_id() ) {
			return;
		}

		if ( $this->remove_customer_shipping_address( $customer->id, absint( $_GET['address_key'] ) ) ) {

			$url = add_query_arg( 'updated', true, $_GET['redirect'] );

			$user          = wp_get_current_user();
			$user_login    = ! empty( $user->user_login ) ? $user->user_login : 'EDDBot';
			$customer_note = __( sprintf( 'Shipping address removed by %s', $user_login ), 'edd-simple-shipping' );
			$customer->add_note( $customer_note );

		} else {
			edd_set_error( 'profile-remove-shipping_address-failure', __( 'Error removing shipping address from profile. Please try again later.', 'edd-simple-shipping' ) );
			$url = $_GET['redirect'];
		}

		wp_safe_redirect( $url );
		exit;
	}

}


/**
 * Get everything running
 *
 * @since 1.0
 *
 * @access private
 * @return object EDD_Simple_Shipping
 */
function edd_simple_shipping_load() {
	return EDD_Simple_Shipping::get_instance();
}
add_action( 'plugins_loaded', 'edd_simple_shipping_load', 0 );

/**
 * A nice function name to retrieve the instance that's created on plugins loaded
 *
 * @since 2.2.3
 * @return object EDD_Simple_Shipping
 */
function edd_simple_shipping() {
	return edd_simple_shipping_load();
}

function edd_simple_shipping_install() {

	$current_version = get_option( 'edd_simple_shipping_version' );

	edd_simple_shipping(); // Initialize the plugin itself so that constants are defined

	if ( ! $current_version ) {

		require_once untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/includes/admin/upgrades.php';

		// When new upgrade routines are added, mark them as complete on fresh install
		$upgrade_routines = array(
			'ss_upgrade_customer_addresses',
		);

		foreach ( $upgrade_routines as $upgrade ) {
			edd_set_upgrade_complete( $upgrade );
		}

	}

	add_option( 'edd_simple_shipping_version', EDD_SIMPLE_SHIPPING_VERSION, '', false );

}
register_activation_hook( __FILE__, 'edd_simple_shipping_install' );
