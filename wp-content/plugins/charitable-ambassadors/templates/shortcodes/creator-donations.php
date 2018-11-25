<?php
/**
 * The template used to display the donations received by the user.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/shortcodes/creator-donations.php
 *
 * @author  Studio 164a
 * @since   1.1.0
 * @version 1.1.0
 */

$user = new Charitable_User( wp_get_current_user() );

$campaigns = $user->get_campaigns( array(
	'posts_per_page' => -1,
	'post_status' => array( 'future', 'publish' ),
	'fields' => 'ids',
) );

if ( ! $campaigns->have_posts() ) : ?>

	<p class="no-campaigns"><?php _e( 'You have not created any campaigns yet.', 'charitable-ambassadors' ) ?></p>

<?php return;
endif;

$donations = charitable_get_table( 'campaign_donations' )->get_donations_report( array(
	'campaign_id' => $campaigns->posts,
	'orderby'     => 'date',
	'order'       => 'DESC',
) );

charitable_ambassadors_enqueue_styles();

/**
 * @hook    charitable_creator_donations_before
 */
do_action( 'charitable_creator_donations_before', $donations );

if ( empty( $donations ) ) : ?>

	<p><?php _e( 'You have not received any donations yet.', 'charitable-ambassadors' ) ?></p>

<?php else : ?>

	<table class="charitable-creator-donations">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Date', 'charitable-ambassadors' ) ?></th>
				<th scope="col"><?php _e( 'Donor', 'charitable-ambassadors' ) ?></th>
				<th scope="col"><?php _e( 'Campaign', 'charitable-ambassadors' ) ?></th>				
				<th scope="col"><?php _e( 'Amount', 'charitable-ambassadors' ) ?></th>
				<th scope="col"><?php _e( 'Status', 'charitable' ) ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $donations as $record ) : ?>
				<?php $donation = charitable_get_donation( $record->donation_id ) ?>
				<tr>
					<td><?php echo $donation->get_date( 'l, F j, Y' ) ?></td>
					<td><?php echo $donation->get_donor() ?></td>
					<td><?php echo $record->campaign_name ?></td>
					<td><?php echo charitable_format_money( $record->amount ) ?></td>
					<td><?php echo $donation->get_status_label() ?></td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>

<?php endif;

/**
 * @hook    charitable_creator_donations_after
 */
do_action( 'charitable_creator_donations_after', $donations );
