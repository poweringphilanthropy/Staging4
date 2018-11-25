<?php
/**
 * Display a notice on the EDD checkout page showing the amount to be donated.
 *
 * @since 		1.0.0
 * @author 		Eric Daams 
 * @copyright 	Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */
global $edd_receipt_args;
$payment_id = $edd_receipt_args['id'];

$cart = Charitable_EDD_Cart::create_with_payment( $payment_id );

$donation_id    = get_post_meta( $payment_id, 'charitable_donation_from_edd_payment', true );
$donation_log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
$donation = charitable_get_donation( $donation_id );

// echo "<pre>";
// print_r($donation_log);
// echo "</pre>";

// echo "<pre>";
// print_r($donation->get_campaign_donations());
// echo "</pre>";

if ( $cart->has_benefactors() || $cart->has_donations_as_fees() ) :
?>
	<h3><?php _e( 'Donations', 'charitable-edd' ) ?></h3>
	<table id="charitable-edd-donations">
		<thead>
			<th><?php _e( 'Campaign', 'charitable-edd' ) ?></th>
			<th style="width: 110px;"><?php _e( 'Amount', 'charitable-edd' ) ?></th>
		</thead>
		<tbody>
		<?php
		$campaign_donations = (method_exists($donation,'get_campaign_donations')) ? $donation->get_campaign_donations() : array(); 
		
		if(is_array($campaign_donations)):

		$i = 0;
		// loop
		foreach ( $campaign_donations as $d ) : 

		$campaign_id = (isset($donation_log[$i]['campaign_id'])) ? $donation_log[$i]['campaign_id'] : false;
		$download_id = (isset($donation_log[$i]['download_id'])) ? $donation_log[$i]['download_id'] : false;
		$title = get_the_title( $campaign_id );
		if($download_id){
			$i++;
			continue; // skip download

			// $title .= ' - ' . get_the_title( $download_id );
		} else {
			$title .= ' - ' . __('Donation', 'philanthropy');
		}
		?>
			<tr>
				<td><?php echo $title; ?></td>				
				<td><?php echo charitable_format_money( $d->amount ); ?></td>
			</tr>

		<?php 
		$i++;
		endforeach; 

		endif; // is_array($campaign_donations)
		?> 
		</tbody>
	</table>
<?php 
endif;