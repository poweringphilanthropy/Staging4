<?php
/**
 * The template for displaying the campaign content
 *
 * Override this template by copying it to your-child-theme/charitable/campaign-loop/heading-content.php
 *
 * @author lafif <[<email address>]>
 * @since 1.0 [<description>]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * @var 	Charitable_Campaign
 */
$campaign = $view_args['campaign'];

$classes = 'campaign-summary current-campaign cf';

if ( ! reach_campaign_has_media( $campaign->ID ) ) {
	$classes .= ' no-media';
}

if ( $campaign->has_ended() ) {
	$classes .= ' campaign-ended';
}

if ( 0 < $campaign->get( 'end_date' ) ) {
	$classes .= ' campaign-has-countdown';
}

if ( $campaign->has_goal() ) {
	$classes .= ' campaign-has-goal';
}

?>
<section class="section-heading-content <?php echo $classes; ?>">

	<div class="layout-wrapper">

		<?php
		/**
		 * @hook philanthropy_heading_content_before
		 */
		do_action( 'philanthropy_heading_content_before', $campaign );
		?>

		<div class="content-area main-content-heading">
		<?php
		/**
		 * @hook philanthropy_heading_content
		 */
		do_action( 'philanthropy_heading_content_area', $campaign );

		?>
		</div>

		<div class="sidebar sidebar-heading cf">
		<?php
		/**
		 * @hook philanthropy_heading_content
		 */
		do_action( 'philanthropy_heading_content_sidebar', $campaign );

		?>

		</div>

		<?php

		/**
		 * @hook philanthropy_heading_content_after
		 */
		do_action( 'philanthropy_heading_content_after', $campaign );
		?>

	</div>
</section>
