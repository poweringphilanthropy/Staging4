<?php
/**
 * Displays what amount of the download purchase will go towards a fundraising campaign.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-edd/download-contribution.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

if ( ! isset( $view_args['download_id'] ) ) {
	return;
}

$benefactors = charitable_get_table( 'edd_benefactors' )->get_benefactors_for_download( $view_args['download_id'] );

?>
<style>
.charitable-edd-contribution-note { font-size: smaller; font-weight: bolder; font-style: italic; margin-top: 1em; }
.charitable-edd-contribution-note span { display: block; margin-bottom: 0.5em; }
</style>
<p class="charitable-edd-contribution-note">
	<?php foreach ( $benefactors as $benefactor ) : ?>
		<span><?php echo new Charitable_EDD_Benefactor( $benefactor ) ?></span>
	<?php endforeach ?>
</p><!--.charitable-edd-contribution-note-->
