<?php
/**
 * Displays the donation receipt.
 *
 * Override this template by copying it to yourtheme/charitable/content-donation-receipt.php
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Donation Receipt
 * @since   1.0.0
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$content  = $view_args['content'];
$donation = $view_args['donation'];

/**
 * Add something before the donation receipt and the page content.
 *
 * @since   1.5.0
 *
 * @param   Charitable_Donation $donation The Donation object.
 */
do_action( 'charitable_donation_receipt_before', $donation );

echo $content;

/**
 * Display the donation receipt content.
 *
 * @since   1.5.0
 *
 * @param   Charitable_Donation $donation The Donation object.
 */
do_action( 'charitable_donation_receipt', $donation );

/**
 * Add something after the donation receipt.
 *
 * @since   1.5.0
 *
 * @param   Charitable_Donation $donation The Donation object.
 */
do_action( 'charitable_donation_receipt_after', $donation );
