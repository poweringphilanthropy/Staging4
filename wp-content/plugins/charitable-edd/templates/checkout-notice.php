<?php
/**
 * Display a notice on the EDD checkout page showing the amount to be donated.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 */

$cart = new Charitable_EDD_Cart( edd_get_cart_contents(), edd_get_cart_fees( 'item' ) );

?>
<tr>
	<th colspan="<?php echo edd_checkout_cart_columns(); ?>" class="charitable-edd-benefit">
		<?php printf( '%s: <span class="benefit-amount">%s</span>', __( 'Donation included in total', 'charitable-edd' ), charitable_get_currency_helper()->get_monetary_amount( strval( $cart->get_total_benefit_amount() ) ) ) ?>
	</t>
</tr>