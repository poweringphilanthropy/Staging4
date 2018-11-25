<?php
/**
 * Display a download that the donor can purchase which will
 * make a donation to the campaign.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 */

$download = $view_args['download'];

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