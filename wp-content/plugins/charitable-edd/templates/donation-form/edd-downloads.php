<?php
/**
 * Display list of downloads that the donor can purchase which will
 * make a donation to the campaign.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 */

$form 		  = $view_args[ 'form' ];
$campaign 	  = $form->get_campaign();
$edd_campaign = $form->get_edd_campaign();
$downloads 	  = $edd_campaign->get_connected_downloads();

if ( false === $downloads ) {
	return;
}

if ( $downloads ) :

	/**
	 * @hook 	charitable_edd_donation_form_before_downloads
	 */
	do_action( 'charitable_edd_donation_form_before_downloads', $campaign, $downloads ); ?>	

	<div class="charitable-connected-downloads">
	<?php 
	/**
	 * @hook 	charitable_edd_donation_form_before_first_download
	 */
	do_action( 'charitable_edd_donation_form_before_first_download', $downloads );	

	foreach ( $downloads as $download ) :
	
		/**
		 * @hook 	charitable_edd_donation_form_download
		 */
		do_action( 'charitable_edd_donation_form_download', $download );
			
	endforeach;

	?>	
	</div>

	<?php 
	/**
	 * @hook 	charitable_edd_donation_form_after_downloads
	 */
	do_action( 'charitable_edd_donation_form_after_downloads', $campaign, $downloads );
	
endif;