<?php
/**
 * Meta boxes
 *
 * @package     EDD\PurchaseLimit\Admin\Downloads\MetaBoxes
 * @since       2.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Render the purchase limit row in the download configuration metabox
 *
 * @since       2.0.0
 * @param       int $post_id The ID of this download
 * @return      void
 */
function edd_purchase_limit_main_metabox_row( $post_id = 0 ) {
	$enabled        = edd_has_variable_prices( $post_id );
	$display        = $enabled ? ' style="display: none;"' : '';
	$purchase_limit = edd_pl_get_file_purchase_limit( $post_id, 'standard' );

	echo '<div id="edd_purchase_limit"' . $display . '>';
	echo '<p><strong>' . __( 'Purchase Limit:', 'edd-purchase-limit' ) . '</strong></p>';
	echo '<label for="edd_purchase_limit_field">';
	echo '<input type="text" name="_edd_purchase_limit" id="edd_purchase_limit_field" value="' . esc_attr( $purchase_limit ) . '" size="30" style="width: 100px;" placeholder="0" /> ';
	echo __( 'Leave blank or set to 0 for unlimited, set to -1 to mark a product as sold out.', 'edd-purchase-limit' );
	echo '</label>';
	echo '</div>';
}
add_action( 'edd_meta_box_fields', 'edd_purchase_limit_main_metabox_row', 20 );


/**
 * Render the date restriction row in the download configuration metabox
 *
 * @since       2.0.0
 * @param       int $post_id The ID of this download
 * @return      void
 */
function edd_purchase_limit_date_metabox_row( $post_id = 0 ) {
	if( ! edd_get_option( 'edd_purchase_limit_restrict_date' ) ) return;

	$start_date = get_post_meta( $post_id, '_edd_purchase_limit_start_date', true );
	$end_date   = get_post_meta( $post_id, '_edd_purchase_limit_end_date', true );

	echo '<div id="edd_purchase_limit_date_range">';
	echo '<p><strong>' . __( 'Restrict Purchases to Date Range:', 'edd-purchase-limit' ) . '</strong></p>';
	echo '<label for="edd_purchase_limit_start_date">' . __( 'Start Date', 'edd-purchase-limit' ) . ' ';
	echo '<input type="text" name="_edd_purchase_limit_start_date" id="edd_purchase_limit_start_date" class="edd_pl_datepicker" value="' . esc_attr( $start_date ) . '" placeholder="mm/dd/yyyy" />';
	echo '</label>';
	echo '<label for="edd_purchase_limit_end_date" style="margin-left: 15px;">' . __( 'End Date', 'edd-purchase-limit' ) . ' ';
	echo '<input type="text" name="_edd_purchase_limit_end_date" id="edd_purchase_limit_end_date" class="edd_pl_datepicker" value="' . esc_attr( $end_date ) . '" placeholder="mm/dd/yyyy" />';
	echo '</label>';
	echo '</div>';
}
add_action( 'edd_meta_box_fields', 'edd_purchase_limit_date_metabox_row', 20 );


/**
 * Add the header cell to the variable pricing table
 *
 * @since       2.0.0
 * @param       int $post_id The ID of this download
 * @return      void
 */
function edd_purchase_limit_price_header( $post_id = 0 ) {
	echo '<th class="edd_purchase_limit_var_title">' . __( 'Purchase Limit', 'edd-purchase-limit' ) . '</th>';
}
add_action( 'edd_download_price_table_head', 'edd_purchase_limit_price_header', 10 );


/**
 * Add the table cell to the variable pricing table
 *
 * @since       2.0.0
 * @param       int $post_id The ID of this download
 * @param       int $price_key The key of this download item
 * @param       array $args Args to pass for this row
 * @return      void
 */
function edd_purchase_limit_price_row( $post_id = 0, $price_key = 0, $args = array() ) {
	$prices         = edd_get_variable_prices( $post_id );
	$purchase_limit = edd_pl_get_file_purchase_limit( $post_id, 'variable', $price_key );

	echo '<td class="edd_purchase_limit_var_field">';
	echo '<label for="edd_variable_prices[' . $price_key . '][purchase_limit]">';
	echo '<input type="text" value="' . $purchase_limit . '" id="edd_variable_prices[' . $price_key . '][purchase_limit]" name="edd_variable_prices[' . $price_key . '][purchase_limit]" style="float:left;width:100px;" placeholder="0" />';
	echo '</label>';
	echo '</td>';
}
add_action( 'edd_download_price_table_row', 'edd_purchase_limit_price_row', 20, 3 );


/**
 * Add field to allow globally disabling product on sold out variable
 *
 * @since       2.0.0
 * @param       int $post_id The ID of this post
 * @return      void
 */
function edd_purchase_limit_variable_disable( $post_id = 0 ) {
	$disabled = get_post_meta( $post_id, '_edd_purchase_limit_variable_disable', true );

	echo '<p>';
	echo '<input type="checkbox" name="_edd_purchase_limit_variable_disable" id="_edd_purchase_limit_variable_disable" value="1" ' . checked( true, $disabled, false ) . ' />&nbsp;';
	echo '<label for="_edd_purchase_limit_variable_disable">' . __( 'Disable product when any item sells out', 'edd-purchase-limit' ) . '</label>';
	echo '</p>';
}
add_action( 'edd_after_price_field', 'edd_purchase_limit_variable_disable', 20, 1 );


/**
 * Add purchase limit to saved fields
 *
 * @since       2.0.0
 * @param       array $fields The current fields EDD is saving
 * @return      array The updated fields to save
 */
function edd_purchase_limit_save_fields( $fields ) {
	$extra_fields = array(
		'_edd_purchase_limit',
		'_edd_purchase_limit_start_date',
		'_edd_purchase_limit_end_date',
		'_edd_purchase_limit_variable_disable'
	);

	return array_merge( $fields, $extra_fields );
}
add_filter( 'edd_metabox_fields_save', 'edd_purchase_limit_save_fields' );