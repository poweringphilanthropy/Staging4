<?php
/**
 *  This template is used to display the Checkout page when items are in the cart
 */ 

global $post; ?>
<table id="edd_checkout_cart" class="checkout-responsive pp-responsive-table <?php if ( ! edd_is_ajax_disabled() ) { echo 'ajaxed'; } ?>">
    <thead>
        <tr class="edd_cart_header_row">
            <?php do_action( 'edd_checkout_table_header_first' ); ?>
            <th class="edd_cart_item_name"><?php _e( 'Item Name', 'edd' ); ?></th>
            <th class="edd_cart_item_price"><?php _e( 'Item Price', 'edd' ); ?></th>
            <th class="edd_cart_item_qty"><?php _e( 'Qty', 'edd' ); ?></th>
            <th class="edd_cart_actions"><?php _e( 'Actions', 'edd' ); ?></th>
            <?php do_action( 'edd_checkout_table_header_last' ); ?>
        </tr>
    </thead>
    <tbody>        
        <?php $cart_items = edd_get_cart_contents(); ?>
        <?php $charitable_edd_cart = new Charitable_EDD_Cart( $cart_items ) ?>        
        <?php $download_benefits = $charitable_edd_cart->get_benefits_by_downloads() ?>
        <?php do_action( 'edd_cart_items_before' ); ?>
        <?php if ( $cart_items ) : ?>
            <?php foreach ( $cart_items as $key => $item ) : ?>
                <tr class="edd_cart_item" id="edd_cart_item_<?php echo esc_attr( $key ) . '_' . esc_attr( $item['id'] ); ?>" data-download-id="<?php echo esc_attr( $item['id'] ); ?>">
                    <?php do_action( 'edd_checkout_table_body_first', $item ); ?>
                    <td data-label="<?php _e( 'Item Name', 'edd' ); ?>" class="edd_cart_item_name">
                        <?php
                            if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $item['id'] ) ) {
                                echo '<div class="edd_cart_item_image">';
                                    echo get_the_post_thumbnail( $item['id'], apply_filters( 'edd_checkout_image_size', array( 25,25 ) ) );
                                echo '</div>';
                            }
                            $item_title = get_the_title( $item['id'] );
                            if ( ! empty( $item['options'] ) && edd_has_variable_prices( $item['id'] ) ) {
                                $item_title .= ' - ' . edd_get_cart_item_price_name( $item );
                            }
                            echo '<span class="edd_checkout_cart_item_title">' . esc_html( $item_title ) . '</span>';                            
                        ?>
                        <?php if ( count( $download_benefits[ $item[ 'id' ] ] ) ) : ?>
                            <span class="charitable-edd-download-benefits">
                                <?php printf( '%s: %s', 
                                    _n( 'Campaign receiving contribution', 'Campaigns receiving contributions', count( $download_benefits[ $item[ 'id' ] ] ), 'philanthropy-project' ), 
                                    $charitable_edd_cart->get_download_benefits( $item[ 'id' ], true ) 
                                ) ?>
                            </span>
                        <?php endif ?>
                    </td>
                    <td data-label="<?php _e( 'Item Price', 'edd' ); ?>" class="edd_cart_item_price"><?php echo edd_cart_item_price( $item['id'], $item['options'] ); ?></td>
                    <td data-label="<?php _e( 'Qty', 'edd' ); ?>" class="edd_cart_item_qty">
                        <?php if( edd_item_quantities_enabled() ) : ?>
                            <input type="number" min="1" step="1" name="edd-cart-download-<?php echo $key; ?>-quantity" data-key="<?php echo $key; ?>" class="edd-input edd-item-quantity-custom" value="<?php echo edd_get_cart_item_quantity( $item['id'], $item['options'] ); ?>"/>
                            <input type="hidden" name="edd-cart-downloads[]" value="<?php echo $item['id']; ?>"/>
                            <input type="hidden" name="edd-cart-download-<?php echo $key; ?>-options" value="<?php echo esc_attr( serialize( $item['options'] ) ); ?>"/>
                        <?php else : ?>
                            1
                        <?php endif; ?>
                    </td>
                    <td data-label="<?php _e( 'Actions', 'edd' ); ?>" class="edd_cart_actions">                        
                        <a class="edd_cart_remove_item_btn" href="<?php echo esc_url( wp_nonce_url( edd_remove_item_url( $key ), 'edd-remove-from-cart-' . $key, 'edd_remove_from_cart_nonce' ) ); ?>"><?php _e( 'Remove', 'edd' ); ?></a>
                    </td>
                    <?php do_action( 'edd_checkout_table_body_last', $item ); ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php do_action( 'edd_cart_items_middle' ); ?>
        <!-- Show any cart fees, both positive and negative fees -->
        <?php if( edd_cart_has_fees() ) : ?>
            <?php  foreach( edd_get_cart_fees() as $fee_id => $fee ) : ?>
                <tr class="edd_cart_fee" id="edd_cart_fee_<?php echo $fee_id; ?>">
                    <td data-label="<?php _e( 'Item Name', 'edd' ); ?>" class="edd_cart_fee_label"><?php echo $fee['label'] ?></td>
                    <td data-label="<?php _e( 'Item Price', 'edd' ); ?>" class="edd_cart_fee_amount"><?php echo esc_html( edd_currency_filter( edd_format_amount( $fee['amount'] ) ) ); ?></td>
                    <td data-label="<?php _e( 'Actions', 'edd' ); ?>" colspan="2" class="<?php echo ( ! empty( $fee['type'] ) && 'item' == $fee['type'] ) ? '' : 'hide-mobile'; ?>">
                        <?php if( ! empty( $fee['type'] ) && 'item' == $fee['type'] ) : ?>
                            <a href="<?php echo esc_url( edd_remove_cart_fee_url( $fee_id ) ); ?>"><?php _e( 'Remove', 'edd' ); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php do_action( 'edd_cart_items_after' ); ?>
    </tbody>
    <tfoot>

        <?php if( has_action( 'edd_cart_footer_buttons' ) ) : ?>
            <tr class="edd_cart_footer_row<?php if ( edd_is_cart_saving_disabled() ) { echo ' edd-no-js'; } ?>">
                <th colspan="<?php echo edd_checkout_cart_columns(); ?>">
                    <?php do_action( 'edd_cart_footer_buttons' ); ?>
                </th>
            </tr>
        <?php endif; ?>

        <?php if( edd_use_taxes() ) : ?>
            <tr class="edd_cart_footer_row edd_cart_subtotal_row"<?php if ( ! edd_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
                <?php do_action( 'edd_checkout_table_subtotal_first' ); ?>
                <th colspan="<?php echo edd_checkout_cart_columns(); ?>" class="edd_cart_subtotal">
                    <?php _e( 'Subtotal', 'edd' ); ?>:&nbsp;<span class="edd_cart_subtotal_amount"><?php echo edd_cart_subtotal(); ?></span>
                </th>
                <?php do_action( 'edd_checkout_table_subtotal_last' ); ?>
            </tr>
        <?php endif; ?>
        
        <tr class="edd_cart_footer_row edd_cart_discount_row" <?php if( ! edd_cart_has_discounts() )  echo ' style="display:none;"'; ?>>
            <?php do_action( 'edd_checkout_table_discount_first' ); ?>
            <th colspan="<?php echo edd_checkout_cart_columns(); ?>" class="edd_cart_discount">
                <?php edd_cart_discounts_html(); ?>
            </th>
            <?php do_action( 'edd_checkout_table_discount_last' ); ?>
        </tr>

        <?php if( edd_use_taxes() ) : ?>
            <tr class="edd_cart_footer_row edd_cart_tax_row"<?php if( ! edd_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
                <?php do_action( 'edd_checkout_table_tax_first' ); ?>
                <th colspan="<?php echo edd_checkout_cart_columns(); ?>" class="edd_cart_tax">
                    <?php _e( 'Tax', 'edd' ); ?>:&nbsp;<span class="edd_cart_tax_amount" data-tax="<?php echo edd_get_cart_tax( false ); ?>"><?php echo esc_html( edd_cart_tax() ); ?></span>
                </th>
                <?php do_action( 'edd_checkout_table_tax_last' ); ?>
            </tr>

        <?php endif; ?>
		
		<?php 
			$charges = pp_get_donor_fees_on_checkout();
			$campaign_title = $charges['campaign_title'];
			$platform_percent_fee = $charges['platform_percent_fee'];
			$stripe_percent_fee = $charges['stripe_percent_fee'];
			$enable_donor_covers_fee = $charges['donor-covers-fee'];
			$currency_helper = charitable_get_currency_helper();
			?>
		<tr>
		<?php if($charges['donor-covers-fee'] ): ?>
			<td colspan="3">
				<label for="donor-covers-fee"><?php _e('Cover Fee', 'pp-toolkit'); ?></label>
				<p>I'd like to cover the processing fee so that 100% of my donation goes to <?php echo $campaign_title; ?>.</p>
			</td>
			<td>
				<div id="switch-covers-fee" class="switch-button">
					<input id="donor-covers-fee" type="checkbox" name="charge_data[donor-covers-fee]" value="yes" data-total="<?php echo edd_get_cart_total(); ?>" data-platform-fee="<?php echo $platform_percent_fee; ?>" data-stripe-percent-fee="<?php echo $stripe_percent_fee; ?>" 
					<?php if((isset($_POST['donor_selection']) && $_POST['donor_selection'] == "true") || !isset($_POST['donor_selection'])){
					 checked($enable_donor_covers_fee); }?>>
					
					<label for="donor-covers-fee"></label>
				</div>
			</td>
	
		</tr>
	
		<?php 	
		$style = "";
		if((isset($_POST['donor_selection']) && $_POST['donor_selection'] == "false") ){
			$style = 'style="display:none"';
		}
					?>

		<tr class="edd_cart_footer_row covered_fees_tr " <?php echo $style;?>>
			 <th colspan="<?php echo edd_checkout_cart_columns(); ?>" class=""><?php _e( 'Covered Fees', 'edd' ); ?>: <span class="covered_fees_amount"><?php echo  $currency_helper->get_monetary_amount($charges['total-fee-amount']); ?></span></th>
		</tr>

		<?php  endif;
		?>
	
		<?php 
		if((isset($_POST['donor_selection']) && $_POST['donor_selection'] == "true") || !isset($_POST['donor_selection'])){
		if($charges['donor-covers-fee']  ) {
			$total =  $currency_helper->get_monetary_amount($charges['gross-amount']);
			$data_total = $charges['gross-amount'];
		}else{
			$currency_helper = charitable_get_currency_helper();
			$total = $currency_helper->get_monetary_amount(edd_get_cart_total()); 
			$data_total = edd_get_cart_total(); 
		}}else {
			$currency_helper = charitable_get_currency_helper();
			$total = $currency_helper->get_monetary_amount(edd_get_cart_total()); 
			$data_total = edd_get_cart_total(); 
		}
		?>
		
        <tr class="edd_cart_footer_row">
            <?php do_action( 'edd_checkout_table_footer_first' ); ?>
            <th colspan="<?php echo edd_checkout_cart_columns(); ?>" class="edd_cart_total"><?php _e( 'Total', 'edd' ); ?>: <span class="edd_cart_amount" data-subtotal="<?php echo $data_total; ?>" data-total="<?php echo $data_total; ?>"><?php echo $total; ?></span></th>
            <?php do_action( 'edd_checkout_table_footer_last' ); ?>
        </tr>
    </tfoot>
</table>
