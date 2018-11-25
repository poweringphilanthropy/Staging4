<?php
/**
 * Display a notice on the EDD checkout page showing the amount to be donated.
 *
 * @since 		1.0.0
 * @author 		Eric Daams 
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */
global $edd_receipt_args;

$cart = Charitable_EDD_Cart::create_with_payment( $edd_receipt_args['id'] );

if ( $cart->has_benefactors() || $cart->has_donations_as_fees() ) :
?>
	<h3><?php _e( 'Donations', 'charitable-edd' ) ?></h3>
	<table id="charitable-edd-donations">
		<thead>
			<th><?php _e( 'Campaign', 'charitable-edd' ) ?></th>
			<th><?php _e( 'Donation', 'charitable-edd' ) ?></th>
		</thead>
		<tbody>
		<?php foreach ( $cart->get_benefiting_campaigns() as $campaign_id ) : ?>
			<tr>
				<td><?php echo get_the_title( $campaign_id ) ?></td>				
				<td><?php echo charitable_format_money( $cart->get_total_campaign_benefit_amount( $campaign_id ) ) ?></td>
			</tr>
		<?php endforeach ?> 
		</tbody>
	</table>
<?php 
endif;