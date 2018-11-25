<?php
/**
 * Display the site donation stats.
 *
 * Override this template by copying it to yourtheme/charitable/donation-stats.php
 *
 * @package Charitable/Templates/Widgets
 * @author  Studio 164a
 * @since   1.5.0
 * @version 1.5.0
 */

$campaigns_count = Charitable_Campaigns::query( array( 'posts_per_page' => -1, 'fields' => 'ids' ) )->found_posts;
$campaigns_text  = 1 == $campaigns_count ? __( 'Campaign', 'charitable' ) : __( 'Campaigns', 'charitable' );

/**
 * Filter the donation stats to show.
 *
 * @since   1.5.0
 *
 * @param   array $donation_stats The default stats to show.
 * @param 	array $view_args      All arguments passed to the view.
 */
$donation_stats = apply_filters( 'charitable_donation_stats', array(
	'campaign_count' => array(
		'amount'      => $campaigns_count,
		'description' => $campaigns_text,
	),
	'donated' => array(
		'amount'      => charitable_format_money( charitable_get_table( 'campaign_donations' )->get_total() ),
		'description' => __( 'Donated', 'charitable' ),
	),
	'donor_count' => array(
		'amount'      => charitable_get_table( 'donors' )->count_donors_with_donations(),
		'description' => __( 'Donors', 'charitable' ),
	),
), $view_args );

?>
<ul class="donation-stats">
<?php
foreach ( $donation_stats as $stat ) :
	printf( '<li><span class="figure">%s</span> %s</li>', $stat['amount'], $stat['description'] );
endforeach;
?>
</ul><!-- .donation-stats -->