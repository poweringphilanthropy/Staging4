<?php
/**
 * The template for displaying the campaign heading image
 *
 * Override this template by copying it to your-child-theme/charitable/campaign-loop/heading-thumbnail.php
 *
 * @author lafif <[<email address>]>
 * @since 1.0 [<description>]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * @var 	Charitable_Campaign
 */
$campaign = $view_args['campaign'];

if ( has_post_thumbnail( $campaign->ID ) ) : 
$thumb_url = get_the_post_thumbnail_url( $campaign->ID, 'full' );
$thumb_url = aq_resize( $thumb_url, 1900, 600, true, true, true );
?>
<section class="section-heading-thumbnail">
	<div class="heading-thumbnail-image">
		<img src="<?php echo $thumb_url; ?>" alt="">
	</div>
</section><!-- .campaign-summary -->
<?php 
endif;