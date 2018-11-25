<?php 
/**
 * Displays the modal content
 *
 * @author Lafif <[<email address>]>
 * @since   1.0
 */

$campaign = $view_args[ 'campaign' ];
$form_id     = (isset($view_args[ 'form_id' ])) ? $view_args[ 'form_id' ] : $view_args[ 'id' ];
?>
<div class="p_popup" data-p_popup="<?php echo $view_args[ 'id' ]; ?>">
    <div class="p_popup-inner">
        <a class="p_popup-close" data-p_popup-close="<?php echo $view_args[ 'id' ] ?>" href="#">x</a>
        
    	<form id="<?php echo $form_id; ?>" class="philanthropy-modal form-default" data-action="merchandise"  method="post" style="padding: 0px;">
	        <div class="p_popup-header">
				<h2 class="p_popup-title"><span><?php echo $view_args[ 'label' ] ?></span></h2>
				<div class="p_popup-notices uk-width-1-1"></div>
	        </div>
	        <div class="p_popup-content">
	
				<input type="hidden" name="campaign_id" value="<?php echo $campaign->ID; ?>">

	        	<?php  
	        	// add nonce
				wp_nonce_field( 'form_modal_merchandise', '_nonce' );

	        	$merchandise_ids = $view_args['merchandise'];
	        	// echo "<pre>";
	        	// print_r($merchandise_ids);
	        	// echo "</pre>";

	        	if(!empty($merchandise_ids)):
	        	foreach ($merchandise_ids as $key => $download_id) {

	        		$download = new EDD_Download( $download_id );

	        		if( empty( $download->ID ) ) {
						continue;
					}

					if( 'publish' !== $download->post_status && ! current_user_can( 'edit_product', $download->ID ) ) {
						continue;
					}

					?>
					<div class="modal-items">
						<div class="uk-grid item-content">
							<div class="uk-width-1-1">
								<div class="item-title">
									<span><?php echo $download->get_name(); ?></span>
								</div>
							</div>

							<?php if(has_post_thumbnail( $download->ID )): ?>
							<div class="uk-width-1-1 p_popup-item-image uk-width-small-4-10">
								<?php echo get_the_post_thumbnail( $download->ID, array(250,250) ); ?>
							</div>
							<?php endif; ?>

							<div class="uk-width-1-1 uk-width-small-6-10">
								<div class="uk-width-1-1">
									<?php echo apply_filters( 'the_content', $download->post_content ); ?>
								</div>

								<?php if( get_post_meta( $download->ID, '_edd_enable_shipping', true ) > 0 ): ?>
								<div class="uk-width-1-1 shipping-desc">
									<div class="item-subtitle"><?php _e('Shipping Rate (per checkout)', 'philanthropy'); ?></div>
									<div class="local"><?php echo sprintf( __('Local Shipping : %s', 'philanthropy'), edd_currency_filter( edd_format_amount( get_post_meta( $download->ID, '_edd_shipping_domestic', true ) ) )  ); ?></div>
									<div class="international"><?php echo sprintf( __('International Shipping : %s', 'philanthropy'), edd_currency_filter( edd_format_amount( get_post_meta( $download->ID, '_edd_shipping_international', true ) ) ) ); ?></div>
								</div>
							<?php endif; ?>
							</div>
						</div>

						<?php 
			        	if ( $campaign->has_ended() ) : 
			        		echo sprintf('<p>Merchandise are no longer available</p>');
			        	else:
			        	?>

							<table class="responsive">
								<thead>
									<tr>
										<th><?php _e('Merchandise', 'philanthropy'); ?></th>
										<th><?php _e('Price', 'philanthropy'); ?></th>
										<th class="qty-column"><?php _e('Quantity', 'philanthropy'); ?></th>
									</tr>
								</thead>
								<tbody>
								<?php
								$variable_pricing = $download->has_variable_prices();

								if ( $variable_pricing ) {

									$prices = $download->get_prices();

									if(!empty($prices)):
									foreach ($prices as $variable_key => $variable) {

									$max_purchases = edd_pl_get_file_purchase_limit( $download->ID, null, $variable_key );
									$remaining = pp_get_remaining_merchandise_stock($download->ID, $variable_key);
									$remaining_label = '';
									$max_attr = '';
									$out_of_stock = false;

									if(($remaining !== 'unlimited') && ($remaining > 0)){
										$remaining_label = sprintf( esc_html__( '(%1$s out of %2$s available)', 'pp-toolkit' ), esc_html( $remaining ), esc_html( $max_purchases ) );
										$max_attr = 'max="'.$remaining.'"';
									} elseif (($remaining !== 'unlimited') && ($remaining <= 0)){
										$out_of_stock = true;
										$remaining_label = esc_html__( 'Out of stock!', 'pp-toolkit' );
										$max_attr = 'max="'.$remaining.'" disabled="disabled"';
									}


									?>
									<tr data-download-id="<?php echo $download->ID; ?>" data-variable-key="<?php echo $variable_key; ?>">
										<td data-label="<?php _e('Merchandise', 'philanthropy'); ?>">
											<span class="product-title"><?php echo $download->get_name(); ?></span>
											<span class="variable-name">( <?php echo $variable['name']; ?> )</span>
											<span class="merchandise-remaining-inline">
											<?php
											echo $remaining_label;
											?>
										</span>
										</td>
										<td data-label="Price">
											<span class="edd_price" id="edd_price_<?php echo $download->ID .'_'.$variable_key; ?>"><?php echo edd_currency_filter( edd_format_amount( $variable['amount'] ) ); ?></span>					
										</td>
										<td data-label="Quantity">
											<input data-product-id="<?php echo $download->ID; ?>" data-variable-key="<?php echo $variable_key; ?>" name="merchandise[<?php echo $download->ID; ?>][variation][<?php echo $variable_key; ?>][qty]" class="product_qty" type="number" min="0" <?php echo $max_attr; ?> value="0">
										</td>
									</tr>
									<?php
									}
									endif;

								} elseif ( ! $variable_pricing ) {
									?>
									<tr data-download-id="<?php echo $download->ID; ?>">
										<td data-label="Merchandise">
											<span class="product-title"><?php echo $download->get_name(); ?></span>
										</td>
										<td data-label="Price">
											<span class="edd_price" id="edd_price_<?php echo $download->ID; ?>"><?php echo edd_currency_filter( edd_format_amount( $download->get_price() ) ); ?></span>					
										</td>
										<td data-label="Quantity">
											<input data-product-id="<?php echo $download->ID; ?>" name="merchandise[<?php echo $download->ID; ?>][qty]" class="product_qty" type="number" min="0" value="0">
										</td>
									</tr>
									<?php
								}
								?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
					<?php
	        		
	        	}
	        	endif;
	        	?>
	        </div>
	        <div class="p_popup-footer">

	        	<?php if ( ! $campaign->has_ended() ) : ?>
				<div class="p_popup-submit-field">
					<button disabled="disabled" class="button button-primary philanthropy-add-to-cart uk-width-1-1 uk-width-small-5-10 uk-width-medium-4-10" type="submit" name="add_to_cart"><?php _e( 'Add to Cart', 'philanthropy' ); ?></button>
					<a href="<?php echo edd_get_checkout_uri(); ?>" class="button philanthropy-go-to-checkout disabled uk-width-1-1 uk-width-small-5-10"><?php _e('Proceed to Checkout', 'philanthropy'); ?></a>
				</div>
				<?php endif; ?>

	        </div>
	    </form>
    </div>
</div>