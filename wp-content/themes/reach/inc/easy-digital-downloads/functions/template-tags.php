<?php
/**
 * Custom template tags used when Easy Digital Downloads is enabled.
 *
 * @package     Reach
 * @category    Functions
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! function_exists( 'reach_edd_purchase_form_quantity_input' ) ) :

	/**
	 * Display quantity input field with quantity label.
	 *
	 * @param   string $quantity_input
	 * @return  string
	 * @since   1.0.0
	 */
	function reach_edd_purchase_form_quantity_input( $quantity_input ) {
		ob_start();
		?>      
		<div class="edd_download_quantity_wrapper">
			<label for="edd_download_quantity"><?php _e( 'Qty', 'reach' ) ?></label>
			<input type="number" min="1" step="1" name="edd_download_quantity" class="edd-input edd-item-quantity" value="1" />
		</div>

		<?php

		return ob_get_clean();
	}

endif;


if ( ! function_exists( 'reach_edd_purchase_form_quantity_input' ) ) :

	/**
	 * Display quantity input field with quantity label.
	 *
	 * @param   string $quantity_input
	 * @param   string $key
	 * @param   string $price
	 * @return  string
	 * @since   1.0.0
	 */
	function reach_edd_purchase_form_variation_quantity_input( $quantity_input, $key, $price ) {
		ob_start();
		?>      
		<div class="edd_download_quantity_wrapper edd_download_quantity_price_option_<?php echo sanitize_key( $price['name'] ) ?>">
			<span class="edd_price_option_sep">&nbsp;x&nbsp;</span>
			<input type="number" min="1" step="1" name="edd_download_quantity_<?php echo esc_attr( $key ) ?>" class="edd-input edd-item-quantity" value="1" />
		</div>
		<?php

		return ob_get_clean();
	}

endif;

if ( ! function_exists( 'reach_edd_purchase_link_text' ) ) :

	/**
	 * Filter the purchase link text.
	 *
	 * @param   array $args
	 * @return  array
	 * @since   1.0.0
	 */
	function reach_edd_purchase_link_text( $args ) {
		if ( false !== strpos( $args['text'], '&nbsp;&ndash;&nbsp;' ) ) {
			list( $p, $text ) = explode( '&nbsp;&ndash;&nbsp;', $args['text'] );
			$args['text'] = $text;
		}

		return $args;
	}

endif;

if ( ! function_exists( 'reach_edd_show_price' ) ) :

	/**
	 * Display price before product purchase form.
	 *
	 * @param   int    $download_id
	 * @param   array  $args
	 * @param   string $price
	 * @return  void
	 * @since   1.0.0
	 */
	function reach_edd_show_price( $download_id, $args, $price = null ) {
		if ( isset( $args['price'] ) && 'no' === $args['price'] ) {
			return;
		}

		if ( edd_has_variable_prices( $download_id ) || edd_item_in_cart( $download_id ) ) {
			return;
		}

		if ( is_null( $price ) ) {
			$price = reach_get_edd_product_price( $download_id, $args );
		}

		if ( false === $price ) {
			return;
		}
?>
		<div class="download-price"><?php echo edd_currency_filter( edd_format_amount( $price ) ) ?></div>
<?php

	}

endif;
