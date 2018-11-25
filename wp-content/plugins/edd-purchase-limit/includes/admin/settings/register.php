<?php
/**
 * Settings
 *
 * @package     EDD\PurchaseLimit\Admin\Settings\Register
 * @since       2.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add settings section
 *
 * @since       2.0.0
 * @param       array $sections The existing extensions sections
 * @return      array The modified extensions settings
 */
function edd_purchase_limit_add_settings_section( $sections ) {
	$sections['purchase-limit'] = __( 'Purchase Limit', 'edd-purchase-limit' );

	return $sections;
}
add_filter( 'edd_settings_sections_extensions', 'edd_purchase_limit_add_settings_section' );


/**
 * Add extension settings
 *
 * @since       2.0.0
 * @param       array $settings The existing plugin settings
 * @return      array The modified plugin settings
 */
function edd_purchase_limit_add_settings( $settings ) {
	if( EDD_VERSION >= '2.5' ) {
		$new_settings = array(
			'purchase-limit' => apply_filters( 'edd_purchase_limit_settings', array(
				array(
					'id'   => 'edd_purchase_limit_settings',
					'name' => '<strong>' . __( 'Purchase Limit Settings', 'edd-purchase-limit' ) . '</strong>',
					'desc' => __( 'Configure Purchase Limit Settings', 'edd-purchase-limit' ),
					'type' => 'header',
				),
				array(
					'id'   => 'edd_purchase_limit_sold_out_label',
					'name' => __( 'Sold Out Button Label', 'edd-purchase-limit' ),
					'desc' => __( 'Enter the text you want to use for the button on sold out items', 'edd-purchase-limit' ),
					'type' => 'text',
					'size' => 'regular',
					'std'  => __( 'Sold Out', 'edd-purchase-limit' )
				),
				array(
					'id'      => 'edd_purchase_limit_scope',
					'name'    => __( 'Scope', 'edd-purchase-limit' ),
					'desc'    => __( 'Choose whether you want purchase limits to apply site-wide or per-user', 'edd-purchase-limit' ),
					'type'    => 'select',
					'std'     => 'site-wide',
					'options' => array(
						'site-wide' => __( 'Site Wide', 'edd-purchase-limit' ),
						'per-user'  => __( 'Per User', 'edd-purchase-limit' )
					)
				),
				array(
					'id'   => 'edd_purchase_limit_show_counts',
					'name' => __( 'Show Remaining Purchases', 'edd-purchase-limit' ),
					'desc' => __( 'Specify whether or not you want to display remaining purchase counts on downloads', 'edd-purchase-limit' ),
					'type' => 'checkbox'
				),
				array(
					'id'   => 'edd_purchase_limit_remaining_label',
					'name' => __( 'Remaining Purchases Label', 'edd-purchase-limit' ),
					'desc' => __( 'Enter the text you want to use for the remaining purchases label', 'edd-purchase-limit' ),
					'type' => 'text',
					'size' => 'regular',
					'std'  => __( 'Remaining', 'edd-purchase-limit' )
				),
				array(
					'id'   => 'edd_purchase_limit_restrict_date',
					'name' => __( 'Enable Date Restriction', 'edd-purchase-limit' ),
					'desc' => __( 'Specify whether or not to enable restriction by date range', 'edd-purchase-limit' ),
					'type' => 'checkbox'
				),
				array(
					'id'   => 'edd_purchase_limit_g_start_date',
					'name' => __( 'Global Start Date', 'edd-purchase-limit' ),
					'desc' => __( 'Define a global start date', 'edd-purchase-limit' ),
					'type' => 'text',
					'size' => 'regular'
				),
				array(
					'id'   => 'edd_purchase_limit_g_end_date',
					'name' => __( 'Global End Date', 'edd-purchase-limit' ),
					'desc' => __( 'Define a global end date', 'edd-purchase-limit' ),
					'type' => 'text',
					'size' => 'regular'
				),
				array(
					'id'   => 'edd_purchase_limit_pre_date_label',
					'name' => __( 'Pre-Date Label', 'edd-purchase-limit' ),
					'desc' => __( 'Enter the text you want to use for items which are not yet available', 'edd-purchase-limit' ),
					'type' => 'text',
					'size' => 'regular',
					'std'  => __( 'This product is not yet available!', 'edd-purchase-limit' )
				),
				array(
					'id'   => 'edd_purchase_limit_post_date_label',
					'name' => __( 'Post-Date Label', 'edd-purchase-limit' ),
					'desc' => __( 'Enter the text you want to use for items which are no longer available', 'edd-purchase-limit' ),
					'type' => 'text',
					'size' => 'regular',
					'std'  => __( 'This product is no longer available!', 'edd-purchase-limit' )
				),
				array(
					'id'      => 'edd_purchase_limit_error_handler',
					'name'    => __( 'Error Handler', 'edd-purchase-limit' ),
					'desc'    => __( 'How should we handle non-inline errors?', 'edd-purchase-limit' ),
					'type'    => 'select',
					'std'     => 'std',
					'options' => array(
						'std'      => __( 'Standard', 'edd-purchase-limit' ),
						'redirect' => __( 'Redirect', 'edd-purchase-limit' )
					)
				),
				array(
					'id'   => 'edd_purchase_limit_error_message',
					'name' => __( 'Error Message', 'edd-purchase-limit' ),
					'desc' => __( 'Enter the text you want to use for the error message', 'edd-purchase-limit' ),
					'type' => 'text',
					'std'  => sprintf( __( 'This %s is sold out!', 'edd-purchase-limit' ), edd_get_label_singular( true ) )
				),
				array(
					'id'      => 'edd_purchase_limit_redirect_url',
					'name'    => __( 'Error Redirect', 'edd-purchase-limit' ),
					'desc'    => __( 'Where should we redirect on error?', 'edd-purchase-limit' ),
					'type'    => 'select',
					'options' => edd_get_pages()
				)
			) )
		);

		$settings = array_merge( $settings, $new_settings );
	}

	return $settings;
}
add_filter( 'edd_settings_extensions', 'edd_purchase_limit_add_settings' );


/**
 * Add extension settings (pre-2.5)
 *
 * @since       2.0.0
 * @param       array $settings The existing plugin settings
 * @return      array The modified plugin settings
 */
function edd_purchase_limit_add_settings_pre25( $settings ) {
	if( EDD_VERSION < '2.5' ) {
		$new_settings = apply_filters( 'edd_purchase_limit_settings', array(
			array(
				'id'   => 'edd_purchase_limit_settings',
				'name' => '<strong>' . __( 'Purchase Limit Settings', 'edd-purchase-limit' ) . '</strong>',
				'desc' => __( 'Configure Purchase Limit Settings', 'edd-purchase-limit' ),
				'type' => 'header',
			),
			array(
				'id'   => 'edd_purchase_limit_sold_out_label',
				'name' => __( 'Sold Out Button Label', 'edd-purchase-limit' ),
				'desc' => __( 'Enter the text you want to use for the button on sold out items', 'edd-purchase-limit' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'Sold Out', 'edd-purchase-limit' )
			),
			array(
				'id'      => 'edd_purchase_limit_scope',
				'name'    => __( 'Scope', 'edd-purchase-limit' ),
				'desc'    => __( 'Choose whether you want purchase limits to apply site-wide or per-user', 'edd-purchase-limit' ),
				'type'    => 'select',
				'std'     => 'site-wide',
				'options' => array(
					'site-wide' => __( 'Site Wide', 'edd-purchase-limit' ),
					'per-user'  => __( 'Per User', 'edd-purchase-limit' )
				)
			),
			array(
				'id'   => 'edd_purchase_limit_show_counts',
				'name' => __( 'Show Remaining Purchases', 'edd-purchase-limit' ),
				'desc' => __( 'Specify whether or not you want to display remaining purchase counts on downloads', 'edd-purchase-limit' ),
				'type' => 'checkbox'
			),
			array(
				'id'   => 'edd_purchase_limit_remaining_label',
				'name' => __( 'Remaining Purchases Label', 'edd-purchase-limit' ),
				'desc' => __( 'Enter the text you want to use for the remaining purchases label', 'edd-purchase-limit' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'Remaining', 'edd-purchase-limit' )
			),
			array(
				'id'   => 'edd_purchase_limit_restrict_date',
				'name' => __( 'Enable Date Restriction', 'edd-purchase-limit' ),
				'desc' => __( 'Specify whether or not to enable restriction by date range', 'edd-purchase-limit' ),
				'type' => 'checkbox'
			),
			array(
				'id'   => 'edd_purchase_limit_g_start_date',
				'name' => __( 'Global Start Date', 'edd-purchase-limit' ),
				'desc' => __( 'Define a global start date', 'edd-purchase-limit' ),
				'type' => 'text',
				'size' => 'regular'
			),
			array(
				'id'   => 'edd_purchase_limit_g_end_date',
				'name' => __( 'Global End Date', 'edd-purchase-limit' ),
				'desc' => __( 'Define a global end date', 'edd-purchase-limit' ),
				'type' => 'text',
				'size' => 'regular'
			),
			array(
				'id'   => 'edd_purchase_limit_pre_date_label',
				'name' => __( 'Pre-Date Label', 'edd-purchase-limit' ),
				'desc' => __( 'Enter the text you want to use for items which are not yet available', 'edd-purchase-limit' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'This product is not yet available!', 'edd-purchase-limit' )
			),
			array(
				'id'   => 'edd_purchase_limit_post_date_label',
				'name' => __( 'Post-Date Label', 'edd-purchase-limit' ),
				'desc' => __( 'Enter the text you want to use for items which are no longer available', 'edd-purchase-limit' ),
				'type' => 'text',
				'size' => 'regular',
				'std'  => __( 'This product is no longer available!', 'edd-purchase-limit' )
			),
			array(
				'id'      => 'edd_purchase_limit_error_handler',
				'name'    => __( 'Error Handler', 'edd-purchase-limit' ),
				'desc'    => __( 'How should we handle non-inline errors?', 'edd-purchase-limit' ),
				'type'    => 'select',
				'std'     => 'std',
				'options' => array(
					'std'      => __( 'Standard', 'edd-purchase-limit' ),
					'redirect' => __( 'Redirect', 'edd-purchase-limit' )
				)
			),
			array(
				'id'   => 'edd_purchase_limit_error_message',
				'name' => __( 'Error Message', 'edd-purchase-limit' ),
				'desc' => __( 'Enter the text you want to use for the error message', 'edd-purchase-limit' ),
				'type' => 'text',
				'std'  => sprintf( __( 'This %s is sold out!', 'edd-purchase-limit' ), edd_get_label_singular( true ) )
			),
			array(
				'id'      => 'edd_purchase_limit_redirect_url',
				'name'    => __( 'Error Redirect', 'edd-purchase-limit' ),
				'desc'    => __( 'Where should we redirect on error?', 'edd-purchase-limit' ),
				'type'    => 'select',
				'options' => edd_get_pages()
			)
		) );

		$settings = array_merge( $settings, $new_settings );
	}

	return $settings;
}
add_filter( 'edd_settings_extensions', 'edd_purchase_limit_add_settings_pre25' );


/**
 * Add debug option if the S214 Debug plugin is enabled
 *
 * @since       2.0.0
 * @param       array $settings The current settings
 * @return      array $settings The updated settings
 */
function edd_purchase_limit_add_debug( $settings ) {
	if( class_exists( 'S214_Debug' ) ) {
		$debug_setting[] = array(
			'id'   => 'edd_purchase_limit_debugging',
			'name' => '<strong>' . __( 'Debugging', 'edd-purchase-limit' ) . '</strong>',
			'desc' => '',
			'type' => 'header'
		);

		$debug_setting[] = array(
			'id'   => 'edd_purchase_limit_enable_debug',
			'name' => __( 'Enable Debug', 'edd-purchase-limit' ),
			'desc' => sprintf( __( 'Log plugin errors. You can view errors %s.', 'edd-purchase-limit' ), '<a href="' . admin_url( 'tools.php?page=s214-debug-logs' ) . '">' . __( 'here', 'edd-purchase-limit' ) . '</a>' ),
			'type' => 'checkbox'
		);

		$settings = array_merge( $settings, $debug_setting );
	}

	return $settings;
}
add_filter( 'edd_purchase_limit_settings', 'edd_purchase_limit_add_debug' );