<?php
/**
 * Display a single donor within a loop.
 *
 * Override this template by copying it to yourtheme/charitable/donor-loop/donor.php
 *
 * @package Charitable/Templates/Donor
 * @author  Studio 164a
 * @since   1.5.0
 * @version 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Donor has to be included in the view args. */
if ( ! array_key_exists( 'donor', $view_args ) ) {
	return;
}

/* @var Charitable_Donor */
$donor = $view_args['donor'];

/* @var int */
$campaign_id = $view_args['campaign'];

?>
<li class="donor">
	<?php
	/**
	 * Add output before the donor's avatar, name, etc.
	 *
	 * @since 1.5.0
	 *
	 * @param Charitable_Donor $donor     The Donor object.
	 * @param array            $view_args View arguments.
	 */
	do_action( 'charitable_donor_loop_before_donor', $donor, $view_args );

	if ( $view_args['show_avatar'] ) :
		echo $donor->get_avatar();
	endif;

	if ( $view_args['show_name'] ) :
	?>
		<p class="donor-name">
		<?php
			/**
			 * Filter the name displayed for the donor.
			 *
			 * @since 1.5.0
			 *
			 * @param string $name      The name to be displayed.
			 * @param array  $view_args View arguments.
			 */
			echo apply_filters( 'charitable_donor_loop_donor_name', $donor->get_name(), $view_args );
		?>
		</p>
	<?php
	endif;

	if ( $view_args['show_location'] && strlen( $donor->get_location() ) ) :
	?>
		<div class="donor-location">
		<?php
			/**
			 * Filter the location displayed for the donor.
			 *
			 * @since 1.5.0
			 *
			 * @param string $location  The location to be displayed.
			 * @param array  $view_args View arguments.
			 */
			echo apply_filters( 'charitable_donor_loop_donor_location', $donor->get_location(), $view_args );
		?>
		</div>
	<?php
	endif;

	if ( $view_args['show_amount'] ) :
	?>
		<div class="donor-donation-amount">
		<?php
			/**
			 * Filter the amount displayed for the donor.
			 *
			 * @since 1.5.0
			 *
			 * @param string $amount    The amount to be displayed.
			 * @param array  $view_args View arguments.
			 */
			echo apply_filters( 'charitable_donor_loop_donor_amount', charitable_format_money( $donor->get_amount( $campaign_id ) ), $view_args );
		?>
		</div>
	<?php
	endif;

	/**
	 * Add output after the donor's avatar, name, etc.
	 *
	 * @since 1.5.0
	 *
	 * @param Charitable_Donor $donor     The Donor object.
	 * @param array            $view_args View arguments.
	 */
	do_action( 'charitable_donor_loop_after_donor', $donor, $view_args );
	?>
</li><!-- .donor-<?php echo $donor->donor_id; ?> -->

