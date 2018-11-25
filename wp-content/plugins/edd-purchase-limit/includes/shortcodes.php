<?php
/**
 * Shortcodes
 *
 * @package     EDD\PurchaseLimit\Shortcodes
 * @since       1.0.1
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Purchases Remaining
 *
 * @since       1.0.1
 * @param       array $atts Arguments to pass to the shortcode
 * @global      object $post The object related to this post
 * @return      void
 */
function edd_purchase_limit_remaining_shortcode( $atts ) {
	global $post;

	$scope          = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
	$sold_out_label = edd_get_option( 'edd_purchase_limit_sold_out_label' ) ? edd_get_option( 'edd_purchase_limit_sold_out_label' ) : __( 'Sold Out', 'edd-purchase-limit' );

	$defaults = array(
		'download_id' => $post->ID,
	);
	$atts = wp_parse_args( $atts, $defaults );

	$max_purchases = edd_pl_get_file_purchase_limit( $atts['download_id'] );

	if( $scope == 'site-wide' && $max_purchases ) {
		$purchases = edd_get_download_sales_stats( $atts['download_id'] );
	} elseif( $scope == 'per-user' && $max_purchases ) {
		$purchases = edd_pl_get_user_purchase_count( get_current_user_id(), $atts['download_id'] );
	}

	if( $purchases < $max_purchases ) {
		$purchases_left = $max_purchases - $purchases;

		return '<span class="edd_purchases_left">' . $purchases_left . '</span>';
	} else {
		return '<span class="edd_purchases_left edd_sold_out">' . $sold_out_label . '</span>';
	}
}
add_shortcode( 'remaining_purchases', 'edd_purchase_limit_remaining_shortcode' );
