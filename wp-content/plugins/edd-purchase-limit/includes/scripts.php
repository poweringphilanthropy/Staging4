<?php
/**
 * Scripts
 *
 * @package     EDD\PurchaseLimit\Scripts
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function edd_pl_load_admin_scripts() {
	global $pagenow, $typenow;

	if( ( $pagenow == 'edit.php' && isset( $_GET['page'] ) && $_GET['page'] == 'edd-settings' ) || $typenow == 'download' ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'edd-pl-timepicker', EDD_PURCHASE_LIMIT_URL . 'assets/js/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ) );
		wp_enqueue_script( 'edd-clearable', EDD_PURCHASE_LIMIT_URL . 'assets/js/jquery.clearable.js', array( 'edd-pl-timepicker' ) );
		wp_enqueue_script( 'edd-pl-admin', EDD_PURCHASE_LIMIT_URL . 'assets/js/admin.js' );

		if( get_user_option( 'admin_color' ) == 'classic' ) {
			wp_enqueue_style( 'jquery-ui', EDD_PURCHASE_LIMIT_URL . 'assets/css/jquery-ui-classic.css' );
		} else {
			wp_enqueue_style( 'jquery-ui', EDD_PURCHASE_LIMIT_URL . 'assets/css/jquery-ui-fresh.css' );
		}

		wp_enqueue_style( 'edd_pl_css', EDD_PURCHASE_LIMIT_URL . 'assets/css/style.css' );
	}
}
add_action( 'admin_enqueue_scripts', 'edd_pl_load_admin_scripts' );
