<?php
/**
 * Renders the EDD tickets table/form
 *
 * @version 4.2
 *
 * @var bool $must_login
 */
global $edd_options;

$is_there_any_product = false;
$is_there_any_product_to_sell = false;
$stock = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->stock();
$cnt = 0;

ob_start();
?>

<form action="<?php echo esc_url( add_query_arg( 'eddtickets_process', 1, edd_get_checkout_uri() ) ); ?>" class="cart" method="post" enctype='multipart/form-data'>
	<table width="100%" class="tribe-events-tickets">
			<?php
			foreach ( $tickets as $ticket ) {

				$product = edd_get_download( $ticket->ID );

				if ( $ticket->date_in_range( current_time( 'timestamp' ) ) ) {

					$is_there_any_product = true;
					$data_product_id = 'data-product-id="' . esc_attr( $ticket->ID ) . '"';

					echo sprintf( '<input type="hidden" name="product_id[]"" value="%d">', esc_attr( $ticket->ID ) );

					echo '<tr>';
					echo '<td width="75" class="edd quantity" data-product-id="' . esc_attr( $ticket->ID ) . '">';


					if ( $stock->available_units( $product->ID ) ) {

						// For global stock enabled tickets with a cap, use the cap as the max quantity
						if ( $global_stock_enabled && Tribe__Tickets__Global_Stock::CAPPED_STOCK_MODE === $ticket->global_stock_mode()) {
							$remaining = $ticket->global_stock_cap();
						}
						else {
							$remaining = $ticket->remaining();
						}

						$max = '';
						if ( $ticket->managing_stock() ) {
							$max = 'max="' . absint( $remaining ) . '"';
						}

						echo '<input type="number" class="" value="0" />';

						$is_there_any_product_to_sell = true;


					}
					else {
						echo '<span class="tickets_nostock">' . esc_html__( 'Out of stock!', 'event-tickets-plus' ) . '</span>';
					}

					echo '</td>';
					
					echo '<td class="tickets_price">';
						echo edd_price( $product->ID );
					echo '</td>';

					echo '<td class="tickets_name" colspan="2">';
					echo $ticket->name;
					if ( $remaining ) {
						?>
						<!--//GUM Edit <span class="tribe-tickets-remaining">-->
						<span>
								<?php
								echo sprintf( esc_html__( '%1$s available', 'event-tickets-plus' ),
									'<span class="available-stock" ' . $data_product_id . '>(' . esc_html( $remaining ) . ')</span>'
								);
								?>
							</span>
						<?php
					}
					echo '<div class="event-ticket-description">'. get_post_field('post_content', $ticket->ID) .'</div>';
					
					echo '</td>';
					
					//GUM Edit - Begin
					echo '<td class="">';
//show add to cart
//echo do_shortcode('[purchase_link id="' . esc_attr( $ticket->ID ) . '" text="Add to Cart" style="button" color="blue"]');

					echo '<div class="edd_purchase_submit_wrapper">';
					?>
					<a href="#" 
						class="button blue edd-submit edd-has-js edd-add-to-cart" 
							data-action="edd_add_to_cart" 
							data-download-id="<?php echo $ticket->ID ;?>" 
							data-variable-price="no" 
							data-price-mode="single" 
							data-price="<?php edd_price( $product->ID, false ) ;?>" 
							data-edd-loading="">
					<span class="edd-add-to-cart-label" style="opacity: 1;">Add to Cart</span> <span class="edd-loading" style="margin-left: 5px; margin-top: -7px; opacity: 0;"><i class="edd-icon-spinner edd-icon-spin"></i></span>
					</a>
					<?php
						
					echo '</div>';
	
					echo '</td>';
					//GUM Edit - End

				echo '</tr>';

					include Tribe__Tickets_Plus__Main::instance()->get_template_hierarchy( 'meta.php' );
					
				}

			}
			?>

		<?php 
			/* GUM EDIT Out
		if ( $is_there_any_product_to_sell ) :
			$color = isset( $edd_options[ 'checkout_color' ] ) ? $edd_options[ 'checkout_color' ] : 'gray';
			$color = ( $color == 'inherit' ) ? '' : $color;
			?>
			<tr>
				<td colspan="5" class="eddtickets-add">
					<?php if ( $must_login ): ?>
						<?php include Tribe__Tickets_Plus__Main::instance()->get_template_hierarchy( 'login-to-purchase' ); ?>
					<?php else: ?>
						<button type="submit" class="edd-submit button <?php echo esc_attr( $color ); ?>"><?php esc_html_e( 'Add to cart', 'event-tickets-plus' );?></button>
							
								
								
					<?php endif; ?>
				</td>
			</tr>
		<?php 
		endif; 
		*/
		?>
		
		
	</table>
</form>


<?php
/* GUM EDIT - new ticket purchase functions
echo '<div class="campaign-event-ticket-form">';
echo '<form class="cart">';
echo '<table width="100%" class="tribe-events-tickets">';

foreach ( $tickets as $ticket ) :

	$product = edd_get_download( $ticket->ID );

	if ( $ticket->date_in_range( current_time( 'timestamp' ) ) ) {

		$is_there_any_product = true;
		$data_product_id = 'data-product-id="' . esc_attr( $ticket->ID ) . '"';

		//echo sprintf( '<input type="hidden" name="product_id[]"" value="%d">', esc_attr( $ticket->ID ) );

		echo '<tr>';
		echo '<td class="edd quantity" data-product-id="' . esc_attr( $ticket->ID ) . '">';


		if ( $stock->available_units( $product->ID ) ) {

			// For global stock enabled tickets with a cap, use the cap as the max quantity
			if ( $global_stock_enabled && Tribe__Tickets__Global_Stock::CAPPED_STOCK_MODE === $ticket->global_stock_mode()) {
				$remaining = $ticket->global_stock_cap();
			}
			else {
				$remaining = $ticket->remaining();
			}

			$max = '';
			if ( $ticket->managing_stock() ) {
				$max = 'max="' . absint( $remaining ) . '"';
			}

			//echo '<input type="number" class="edd-input" min="0" ' . $max . ' name="quantity_' . esc_attr( $ticket->ID ) . '" value="0" ' . disabled( $must_login, true, false ) . '/>';
			
			//show add to cart
			echo do_shortcode('[purchase_link id="' . esc_attr( $ticket->ID ) . '" text="Add to Cart" style="button" color="blue"]');
			
			echo '<div class="event-ticket-description">'. get_post_field('post_content', $ticket->ID) .'</div>';

			$is_there_any_product_to_sell = true;


		} else {
			echo '<span class="tickets_nostock">' . esc_html__( 'Out of stock!', 'event-tickets-plus' ) . '</span>';
		}

		echo '</td>';

		echo '<td class="tickets_name" colspan="2">';
		echo $ticket->name;
		
			if ( $remaining ) {
				?>
				<span class="tribe-tickets-remaining">
						<?php
						echo sprintf( esc_html__( '%1$s available', 'event-tickets-plus' ),
							'<span class="available-stock" ' . $data_product_id . '>' . esc_html( $remaining ) . '</span>'
						);
						?>
					</span>
				<?php
			}
		echo '</td>';


		include Tribe__Tickets_Plus__Main::instance()->get_template_hierarchy( 'meta.php' );
		
	}
	
endforeach;

echo '</table>';
echo '</form>';
echo '</div>';

*/

//GUM Edit End

$contents = ob_get_clean();

if ( $is_there_any_product ) {
	echo $contents;
} else {
	$unavailability_message = $this->get_tickets_unavailable_message( $tickets );

	// if there isn't an unavailability message, bail
	if ( ! $unavailability_message ) {
		return;
	}

	?>
	<div class="tickets-unavailable">
		<?php echo esc_html( $unavailability_message ); ?>
	</div>
	<?php
}
