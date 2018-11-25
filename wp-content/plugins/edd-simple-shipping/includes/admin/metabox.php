<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class EDD_Simple_Shipping_Metabox {

	public function __construct() {
		add_action( 'edd_meta_box_fields',           array( $this, 'metabox' ), 10 );
		add_action( 'edd_updated_edited_purchase',   array( $this, 'save_payment' ) );
		add_action( 'edd_download_price_table_head', array( $this, 'price_header' ), 700 );
		add_action( 'edd_download_price_table_row',  array( $this, 'price_row' ), 700, 3 );
		add_filter( 'edd_metabox_fields_save',       array( $this, 'meta_fields_save' ) );
	}

	/**
	 * Render the extra meta box fields
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	public function metabox( $post_id = 0 ) {
		$currency_position = edd_get_option( 'currency_position', 'before' );

		$download = new EDD_Download( $post_id );

		$enabled          = get_post_meta( $post_id, '_edd_enable_shipping', true );
		$variable_pricing = $download->has_variable_prices();
		$display          = $enabled && ! $variable_pricing ? '' : 'style="display:none;"';
		$domestic         = get_post_meta( $post_id, '_edd_shipping_domestic', true );
		$international    = get_post_meta( $post_id, '_edd_shipping_international', true );
		?>
		<div id="edd_simple_shipping">
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('#edd_enable_shipping').on('click',function() {
						var variable_pricing = $('#edd_variable_pricing').is(':checked');
						var enabled          = $(this).is(':checked');
						if ( enabled ) {
							if ( variable_pricing ) {
								$('.edd_prices_shipping').show();
							} else {
								$('#edd_simple_shipping_fields').show();
							}
						} else {
							$('#edd_simple_shipping_fields,.edd_prices_shipping').hide();
						}
					});

					$('#edd_variable_pricing').on('click', function() {
						var enabled  = $(this).is(':checked');
						var shipping = $('#edd_enable_shipping').is(':checked');

						if ( ! shipping ) {
							return;
						}

						if ( enabled ) {
							$('.edd_prices_shipping').show();
							$('#edd_simple_shipping_fields').hide();
						} else {
							$('#edd_simple_shipping_fields').show();
							$('.edd_prices_shipping').hide();
						}
					});
				});</script>
			<p><strong><?php _e( 'Shipping Options', 'edd-simple-shipping' ); ?></strong></p>
			<p>
				<label for="edd_enable_shipping">
					<input type="checkbox" name="_edd_enable_shipping" id="edd_enable_shipping" value="1"<?php checked( 1, $enabled ); ?>/>
					<?php printf( __( 'Enable shipping for this %s', 'edd-simple-shipping' ), edd_get_label_singular() ); ?>
				</label>
			</p>
			<div id="edd_simple_shipping_fields" <?php echo $display; ?>>
				<table>
					<tr>
						<td>
							<label for="edd_shipping_domestic"><?php _e( 'Domestic Rate:', 'edd-simple-shipping' ); ?>&nbsp;</label>
						</td>
						<td>
							<?php if( 'before' === $currency_position ) : ?>
								<span><?php echo edd_currency_filter( '' ); ?></span><input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $domestic ); ?>" id="edd_shipping_domestic" name="_edd_shipping_domestic"/>
							<?php else : ?>
								<input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $domestic ); ?>" id="edd_shipping_domestic" name="_edd_shipping_domestic"/><?php echo edd_currency_filter( '' ); ?>
							<?php endif; ?>
						</td>
						<td>
							<label for="edd_shipping_international"><?php _e( 'International Rate:', 'edd-simple-shipping' ); ?>&nbsp;</label>
						</td>
						<td>
							<?php if( $currency_position == 'before' ) : ?>
								<span><?php echo edd_currency_filter( '' ); ?></span><input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $international ); ?>" id="edd_shipping_international" name="_edd_shipping_international"/>
							<?php else : ?>
								<input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $international ); ?>" id="edd_shipping_international" name="_edd_shipping_international"/><?php echo edd_currency_filter( '' ); ?>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php
	}

	/**
	 * Save the shipping details on payment edit
	 *
	 * @since 1.5
	 *
	 * @access private
	 * @return void
	 */
	public function save_payment( $payment_id = 0 ) {

		$address = isset( $_POST['edd-payment-shipping-address'] ) ? $_POST['edd-payment-shipping-address'] : false;
		if( ! $address ) {
			return;
		}


		$meta      = edd_get_payment_meta( $payment_id );
		$user_info = $meta['user_info'];

		$user_info['shipping_info'] = $address[0];

		$meta['user_info'] = $user_info;
		update_post_meta( $payment_id, '_edd_payment_meta', $meta );

		if( isset( $_POST['edd-payment-shipped'] ) ) {
			update_post_meta( $payment_id, '_edd_payment_shipping_status', '2' );
		} elseif( get_post_meta( $payment_id, '_edd_payment_shipping_status', true ) ) {
			update_post_meta( $payment_id, '_edd_payment_shipping_status', '1' );
		}
	}

	/**
	 *Add the table header cell for price shipping
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	function price_header( $post_id = 0 ) {
		$enabled = get_post_meta( $post_id, '_edd_enable_shipping', true );
		$styles  = 'width:30%;';
		$styles .= $enabled ? '' : 'display:none;';
		?>
		<th class="edd_prices_shipping" style="<?php echo $styles; ?>"><?php _e( 'Shipping', 'edd-simple-shipping' ); ?></th>
	<?php
	}

	/**
	 *Add the table cell for price shipping
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	function price_row( $post_id = 0, $price_key = 0, $args = array() ) {
		$enabled           = get_post_meta( $post_id, '_edd_enable_shipping', true );
		$currency_position = edd_get_option( 'currency_position', 'before' );
		$display           = $enabled ? '' : 'style="display:none;"';
		$prices            = edd_get_variable_prices( $post_id );
		$shipping          = isset( $prices[ $price_key ]['shipping'] ) ? $prices[ $price_key ]['shipping'] : false;

		$domestic = '';
		$international = '';

		if ( is_array( $shipping ) ) {
			$domestic      = $shipping['domestic'];
			$international = $shipping['international'];
		} elseif ( ! empty( $shipping ) ) {
			$domestic      = get_post_meta( $post_id, '_edd_shipping_domestic', true );
			$international = get_post_meta( $post_id, '_edd_shipping_international', true );
		}
		?>
		<td class="edd_prices_shipping"<?php echo $display; ?>>

			<?php _e( 'Domestic', 'edd-simple-shipping' ); ?>
			<?php if( $currency_position == 'before' ) : ?>
				<?php echo edd_currency_filter( '' ); ?>
				<input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $domestic ); ?>" id="edd_shipping_domestic" name="edd_variable_prices[<?php echo $price_key; ?>][shipping][domestic]"/>
			<?php else : ?>
				<input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $domestic ); ?>" id="edd_shipping_domestic" name="edd_variable_prices[<?php echo $price_key; ?>][shipping][domestic]"/>
				<?php echo edd_currency_filter( '' ); ?>
			<?php endif; ?>

			<?php _e( 'International', 'edd-simple-shipping' ); ?>
			<?php if( $currency_position == 'before' ) : ?>
				<?php echo edd_currency_filter( '' ); ?>
				<input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $international ); ?>" id="edd_shipping_domestic" name="edd_variable_prices[<?php echo $price_key; ?>][shipping][international]"/>
			<?php else : ?>
				<input type="number" min="0" step="0.01" class="small-text" value="<?php esc_attr_e( $international ); ?>" id="edd_shipping_domestic" name="edd_variable_prices[<?php echo $price_key; ?>][shipping][international]"/>
				<?php echo edd_currency_filter( '' ); ?>
			<?php endif; ?>

		</td>
	<?php
	}


	/**
	 * Save our extra meta box fields
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return array
	 */
	public function meta_fields_save( $fields ) {

		// Tell EDD to save our extra meta fields
		$fields[] = '_edd_enable_shipping';
		$fields[] = '_edd_shipping_domestic';
		$fields[] = '_edd_shipping_international';
		return $fields;

	}
}