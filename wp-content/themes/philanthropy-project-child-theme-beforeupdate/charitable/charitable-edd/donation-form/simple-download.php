<?php
/**
 * Display a download that the donor can purchase which will
 * make a donation to the campaign.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 */

$download = $view_args[ 'download' ];

?>
<div class="charitable-edd-connected-download simple-download">
	<?php 
	if ( apply_filters( 'charitable_edd_donation_form_show_thumbnail', true ) ) : ?>

		<div class="charitable-edd-download-media">
			<?php echo get_the_post_thumbnail( $download->ID, array( 150, 150 ) ) ?>
		</div><!--.download-media-->

		<?php
	endif 
	?>
	<div class="charitable-edd-download-details">		
		<?php 
		if ( apply_filters( 'charitable_edd_donation_form_show_title', true ) ) : ?>

			<h5 class="download-title"><?php echo get_the_title( $download->ID ) ?></h5>

		<?php 
		endif;

		if ( apply_filters( 'charitable_edd_donation_form_show_excerpt', false ) ) :

			echo apply_filters( 'the_excerpt', get_post_field( 'post_content', $download->ID ) );

		endif;
		?>
		<!-- //GUM EDIT		
		<input type="checkbox" 
			class="charitable-edd-download-select" 
			name="downloads[<?php echo $download->ID ?>]" 
			value="<?php echo $download->ID ?>" 
			data-price="<?php echo edd_get_download_price( $download->ID ) ?>" 
			<?php checked( edd_item_in_cart( $download->ID ) ) ?>
		/>
		<span class="edd-price-option-price" itemprop="price">
			<?php edd_price( $download->ID ) ?>
			<span class="currency"><?php echo edd_get_currency() ?></span>
		</span>
		-->
		<div class="event-ticket-description"><?php echo get_post_field('post_content', $download->ID) ;?></div>
		<!--
		<form id="edd_purchase_<?php echo $download->ID ;?>" class="edd_download_purchase_form edd_purchase_<?php echo $download->ID ;?>" method="post">
		-->

		<div class="edd_download_quantity_wrapper">
			<label for="edd_download_quantity">Qty</label>
			<input type="number" min="" step="1" name="edd_download_quantity" class="edd-input edd-item-quantity" value="1">
		</div>
		
		<div class="download-price"><?php edd_price( $download->ID ) ;?></div>

		
		<div class="edd_purchase_submit_wrapper">
			<a href="#" 
				class="button blue edd-submit edd-has-js edd-add-to-cart" 
				data-action="edd_add_to_cart" 
				data-download-id="<?php echo $download->ID ;?>" 
				data-variable-price="no" 
				data-price-mode="single" 
				data-price="<?php edd_price( $download->ID, false ) ;?>" 
				data-edd-loading="">
					
				<span class="edd-add-to-cart-label" style="opacity: 1;">Add to Cart</span> 
				<span class="edd-loading" style="margin-left: 5px; margin-top: -7px; opacity: 0;">
				<i class="edd-icon-spinner edd-icon-spin"></i></span>		
			</a>
			
			<input type="submit" 
				class="edd-add-to-cart edd-no-js button blue edd-submit" 
				name="edd_purchase_download" 
				value="Add to Cart" 
				data-action="edd_add_to_cart" 
				data-download-id="<?php echo $download->ID ;?>" 
				data-variable-price="no" 
				data-price-mode="single" 
				style="display: none;">
			
			<a href="<?php edd_get_checkout_uri() ;?>" class="edd_go_to_checkout button blue edd-submit" style="display: inline-block;">Checkout</a>
			
				<span class="edd-cart-ajax-alert" aria-live="assertive">
					<span class="edd-cart-added-alert" style="display: none;">
						<i class="edd-icon-ok" aria-hidden="true"></i> Added to cart 
					</span>
				</span>
		</div><!--end .edd_purchase_submit_wrapper-->
		

		<!--<input type="hidden" name="download_id" value="<?php echo $download->ID ;?>">-->
		<input type="hidden" name="edd_action" class="edd_action_input" value="add_to_cart">
		
		
		
	<!--</form>-->
		
		<?php
		
		//* Working with EDD shortcode
					
		//echo do_shortcode('[purchase_link id="' . esc_attr( $download->ID ) . '" text="Add to Cart" style="button" color="blue"]');
		//echo '<br>';
		//echo '<div class="event-ticket-description">'. get_post_field('post_content', $download->ID) .'</div>';
		
$meta = Tribe__Tickets_Plus__Main::instance()->meta();
if ( ! $meta->meta_enabled( $download->ID ) ) {
	return;
}
$meta_fields = $meta->get_meta_fields_by_ticket( $download->ID );
?>

<table>
	<tbody>
	<tr class="tribe-event-tickets-plus-meta" id="tribe-event-tickets-plus-meta-<?php echo esc_attr( $download->ID ); ?>" data-ticket-id="<?php echo esc_attr( $download->ID ); ?>">
		<td colspan="4">
			<p class="tribe-event-tickets-meta-required-message">
				<?php esc_html_e( 'Please fill in all required fields', 'event-tickets-plus' ); ?>
			</p>
			<div class="tribe-event-tickets-plus-meta-fields" id="tribe-event-tickets-plus-meta-fields-<?php echo esc_attr( $download->ID ); ?>"></div>
			<script class="tribe-event-tickets-plus-meta-fields-tpl" id="tribe-event-tickets-plus-meta-fields-tpl-<?php echo esc_attr( $download->ID ); ?>" type="text/template">
				<div class="tribe-event-tickets-plus-meta-attendee">
					<header><?php esc_html_e( 'Attendee', 'event-tickets-plus' ); ?></header>
					<?php
					foreach ( $meta_fields as $field ) {
						echo $field->render();
					}
					?>
				</div>
			</script>
		</td>
	</tr>
	</tbody>
</table>

	
	</div><!--.download-details-->
	<?php 
	if ( apply_filters( 'charitable_edd_donation_form_show_contribution_note', true ) ) : ?>
	
		<p class="charitable-edd-contribution-note">
			<?php foreach ( $download->benefactors as $benefactor ) : ?>
				<?php echo new Charitable_EDD_Benefactor( $benefactor ) ?>
			<?php endforeach ?>
		</p><!--.charitable-edd-contribution-note-->

	<?php endif ?>
</div><!--.charitable-edd-connected-download-->