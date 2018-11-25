<?php 
/**
 * Displays the ticket table form
 *
 * @author Lafif <[<email address>]>
 * @since   1.0
 */

$ticket_ids = $view_args[ 'ticket_ids' ];

$is_there_any_product         = false;
$is_there_any_product_to_sell = false;

$stock = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->stock();

// sort by ticket id
sort($ticket_ids);

// echo "<pre>";
// print_r($ticket_ids);
// echo "</pre>";

if(!empty($ticket_ids)):
	?>
	<hr>
	<div class="item-subtitle"><h3><?php _e('Tickets', 'philanthropy'); ?></h3></div>
	<?php
	/**
	 * Display tickets
	 */
	?>
	<table class="responsive">
		<thead>
			<tr>
				<th><?php _e('Ticket', 'philanthropy'); ?></th>
				<th><?php _e('Price', 'philanthropy'); ?></th>
				<th class="qty-column"><?php _e('Quantity', 'philanthropy'); ?></th>
			</tr>
		</thead>
		<tbody>

			<?php  
			ob_start();

			/**
			 * Loop for tickets
			 */
			foreach ($ticket_ids as $ticket_id) {

				$ticket = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->get_ticket( get_the_id() ,$ticket_id );
				
				$product = edd_get_download( $ticket->ID );

				if($ticket->date_in_range( current_time( 'timestamp' ) )){

					$is_there_any_product = true;

					/**
					 * Get meta
					 */
					$meta = Tribe__Tickets_Plus__Main::instance()->meta();

					$meta_fields = $meta->get_meta_fields_by_ticket( $ticket->ID );

					// echo "<pre>";
					// print_r($ticket);
					// echo "</pre>";

					?>
					<tr data-ticket-id="<?php echo $ticket->ID; ?>" data-meta-enabled="<?php echo ($meta->meta_enabled( $ticket->ID )) ? 'yes' : 'no'; ?>">
						<td data-label="Ticket">
							<span class="ticket-title"><?php echo get_the_title( $ticket->ID ); ?></span>
							<?php 

							$global_stock = new Tribe__Tickets__Global_Stock( $ticket->ID );
							$global_stock_enabled = $global_stock->is_enabled();

							// For global stock enabled tickets with a cap, use the cap as the max quantity
							if ( $global_stock_enabled && Tribe__Tickets__Global_Stock::CAPPED_STOCK_MODE === $ticket->global_stock_mode()) {
								$remaining = $ticket->global_stock_cap();
							}
							else {
								$remaining = $ticket->remaining();
							}

							if ( $remaining ) {
								?>
								<span class="tribe-tickets-remaining-inline">
									<?php
									echo sprintf( esc_html__( '(%1$s out of %2$s available)', 'event-tickets-plus' ), esc_html( $remaining ), esc_html( $ticket->original_stock() ) );
									?>
								</span>
								<?php
							} ?>
							<div class="event-ticket-description">
								<?php echo $ticket->description; ?>
							</div>


							<?php

							// echo "<pre>";
							// print_r($meta_fields);
							// echo "</pre>";
							if ( $meta->meta_enabled( $ticket->ID ) ) {
							?>

							<div class="ticket-meta-attendee"></div>
							<?php 
							
							/**
							 * Add repeatable template
							 */
							?>
							<script class="tribe-event-tickets-plus-meta-fields-tpl" id="tribe-event-tickets-plus-meta-fields-tpl-<?php echo esc_attr( $ticket->ID ); ?>" type="text/template">
								<div class="tribe-event-tickets-plus-meta-attendee">
									<header><?php esc_html_e( 'Attendee', 'event-tickets-plus' ); ?></header>
									<?php
									foreach ( $meta_fields as $field ) {
										echo $field->render();
									}
									?>
								</div>
							</script>
							
							<?php } // $meta->meta_enabled( $ticket->ID )  ?>
						</td>
						<td data-label="Price">
							<?php 
							$price_html = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->get_price_html( $ticket_id );
							echo $price_html; 
							?>
						</td>
						<td data-label="Quantity">
							<?php  
							if ( $stock->available_units( $product->ID ) ) {
								$managing_stock = $ticket->managing_stock();
								// reset if unlimited
								if($stock->available_units( $product->ID ) == Tribe__Tickets_Plus__Commerce__EDD__Main::UNLIMITED){
									$managing_stock = false;
								}
								
								$max = '';
								if ( $managing_stock ) {
									$max = 'max="' . absint( $remaining ) . '"';
								}

								if(!$managing_stock || ($managing_stock && absint( $remaining ) > 0) ){
									
									echo '<input data-product-id="'.$ticket->ID.'" name="tickets['.$ticket->ID.'][qty]" class="product_qty" type="number" min="0" '.$max.' value="0">';
									$is_there_any_product_to_sell = true;
								} else {
									echo esc_html__( 'Out of stock!', 'event-tickets-plus' );
								}
								
							} else {
								echo esc_html__( 'Out of stock!', 'event-tickets-plus' );
							}

							?>
						</td>
					</tr>
					<?php
				} // end $ticket->date_in_range( current_time( 'timestamp' ) )
			}

			$content = ob_get_clean();
			if($is_there_any_product){
				echo $content;
			} else {
				?>
				<tr>
					<td colspan="3"><?php _e( 'Tickets are no longer available.', 'event-tickets' ); ?></td> 
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<?php

endif;