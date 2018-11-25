<?php
/**
 * Collection of custom functions for Philanthropy Project
 * @author lafif <[<email address>]>
 * @since 1.0 [<description>]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function philanthropy_template( $template_name, array $args = array() ) {
	if ( empty( $args ) ) {
		$template = new Philanthropy_Template( $template_name );
	} else {
		$template = new Philanthropy_Template( $template_name, false );
		$template->set_view_args( $args );
		$template->render();
	}

	return $template;
}
if (!function_exists('philanthropy_get_connected_downloads')){  
function philanthropy_get_connected_downloads($edd_campaign){
	$campaign_downloads = $edd_campaign->get_connected_downloads();
	if( (false === $campaign_downloads) || empty($campaign_downloads) )
		return false;

	// echo "<pre>";
	// print_r($campaign_downloads);
	// echo "</pre>";

	$downloads = array();
	foreach ($campaign_downloads as $download) {
		$post_id = is_a( $download, 'WP_Post' ) ? $download->ID : $download[ 'id' ];
		$download_category = get_the_terms( $post_id, 'download_category' );
		$download_category = (isset($download_category[0])) ? $download_category[0] : false;
		if($download_category){
			$downloads[$download_category->slug]['term'] = $download_category;
			// for ticket we need to group with event id
			if($download_category->slug == 'ticket'){
				$event_id = get_post_meta( $post_id, '_tribe_eddticket_for_event', true );
				$downloads[$download_category->slug]['posts'][$event_id][] = $download;
			} else {
				$downloads[$download_category->slug]['posts'][] = $download;
			}
		} else {
			$downloads[] = $download;
		}
	}

	// echo "<pre>";
	// print_r($downloads);
	// echo "</pre>";

	return $downloads;
}}
if (function_exists('philanthropy_get_campaign_merchandise_ids')) {
function philanthropy_get_campaign_merchandise_ids($campaign_id){
	$downloads = charitable()->get_db_table('edd_benefactors')->get_single_download_campaign_benefactors($campaign_id);
    $download_ids = wp_list_pluck( $downloads, 'edd_download_id' );

    $merchandise = array();
    if(!empty($download_ids)):
    foreach ($download_ids as $id) {
    	if( !has_term( 'merchandise', 'download_category', $id ) || in_array($id, $merchandise) )
    		continue;

    	$merchandise[] = $id;
    }
    endif;

    return $merchandise;

}}
if (function_exists('philanthropy_get_campaign_event_ids')) {
function philanthropy_get_campaign_event_ids($campaign_id){
	$events = get_post_meta($campaign_id, '_campaign_events', true);

	return $events;
}
}
function philanthropy_get_volunteers($campaign){

	$return = array();

	if(is_int($campaign)){
		$campaign = new Charitable_Campaign( $campaign );
	}

	$needs 	= $campaign->volunteers;
	if(!empty($needs)){
		
		$volunteers = wp_list_pluck( $needs, 'need' );
		if(is_array($volunteers) && !empty($volunteers)) 
			$return = array_filter($volunteers);
	}
	
	
	return $return;
}

function philanthropy_get_donors($campaign_id = false){

	$defaults = array (
 		'number' => -1,
 		'output' => "donors",
 		'orderby' => "amount",
 		'campaign' => charitable_get_current_campaign_id(),
 		'distinct_donors' => 'on',
	);
	
	// Parse incoming $args into an array and merge it with $defaults
	$query_args = wp_parse_args( $args, $defaults );

	if($campaign_id){
		$query_args['campaign'] = $campaign_id;
	}

	return new Charitable_Donor_Query( $query_args );
}

function order_by_post_id_and_campaign_id($query_group, $Charitable_Query){

	if ( ! $Charitable_Query->get( 'distinct_donors', false ) ) {
		global $wpdb;

		$query_group = "GROUP BY {$wpdb->posts}.ID, cd.campaign_id";
	}

	return $query_group;
}

function philanthropy_get_multiple_donors($campaign_ids, $distinct_donors = false ){

	add_filter( 'charitable_query_groupby', 'order_by_post_id_and_campaign_id', 100, 2 );

	$query_args = array(
	    'number' => -1,
	    'output' => 'donors',
	    'campaign' => $campaign_ids,
	    'distinct_donors' => $distinct_donors,
	    'distinct' => false,
	);
	$donors = new Charitable_Donor_Query( $query_args );

	remove_filter( 'charitable_query_groupby', 'order_by_post_id_and_campaign_id', 100 );

	return $donors;
}

/**
 * Email template tag: download_list
 * A list of download links for each download purchased
 *
 * @param int $payment_id
 *
 * @return string download_list
 */
function pp_edd_email_tag_download_list( $payment_id ) {
	
	// $cart = Charitable_EDD_Cart::create_with_payment( $payment_id );
	$donation_id    = get_post_meta( $payment_id, 'charitable_donation_from_edd_payment', true );

	if(empty($donation_id))
		return edd_email_tag_download_list( $payment_id );
	
	/**
	 * Display donation and downloads
	 * @var EDD_Payment
	 */
	$payment = new EDD_Payment( $payment_id );

	$payment_data  = $payment->get_meta();
	$download_list = '<ul>';
	$cart_items    = $payment->cart_details;
	$cart_fees    = $payment->fees;
	$email         = $payment->email;

	// $donation_log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
	// $donation = charitable_get_donation( $donation_id );
	
	// echo "<pre>";
	// print_r($cart_fees);
	// echo "</pre>";

	// echo "<pre>";
	// print_r($cart_items);
	// echo "</pre>";
	
	// echo "<pre>";
	// print_r($donation_log);
	// echo "</pre>";

	// echo "<pre>";
	// print_r($donation);
	// echo "</pre>";


	/**
	 * Display Donations
	 */
	if ( !empty($cart_fees) ) {

		foreach ($cart_fees as $fee) {
			if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) )
				continue;

			$download_list .= '<li>' . sprintf(__('<strong>%s</strong> (%s)', 'philanthropy'), $fee['label'], charitable_format_money($fee['amount']) ) . '</li>';
		}
	}

	/**
	 * Display Donations
	 */
	if ( !empty($cart_fees) ) {

		foreach ($cart_fees as $fee) {
			if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) )
				continue;

			$download_list .= '<li>' . sprintf(__('<strong>%s</strong> (%s)', 'philanthropy'), $fee['label'], charitable_format_money($fee['amount']) ) . '</li><br>';
		}
	}

	/**
	 * Display downloads,
	 * assume all downloads with 100% campaign benefactor relationship
	 */
	if ( $cart_items ) {
		$show_names = apply_filters( 'edd_email_show_names', true );
		$show_links = apply_filters( 'edd_email_show_links', true );

		foreach ( $cart_items as $item ) {

			if ( edd_use_skus() ) {
				$sku = edd_get_download_sku( $item['id'] );
			}

			if ( edd_item_quantities_enabled() ) {
				$quantity = $item['quantity'];
			}

			$price_id = edd_get_cart_item_price_id( $item );
			if ( $show_names ) {

				$title = '<strong>' . get_the_title( $item['id'] ) . '</strong>';

				// if ( ! empty( $quantity ) && $quantity > 1 ) {
				// 	$title .= "&nbsp;&ndash;&nbsp;" . __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
				// }

				if ( ! empty( $sku ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
				}

				if ( edd_has_variable_prices( $item['id'] ) && isset( $price_id ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id, $payment_id );
				}

				$download_list .= '<li>' . $item['quantity'] . 'x ' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . ' ('.charitable_format_money($item['price']).')<br/>';
			}

			$files = edd_get_download_files( $item['id'], $price_id );

			if ( ! empty( $files ) ) {

				foreach ( $files as $filekey => $file ) {

					if ( $show_links ) {
						$download_list .= '<div>';
						$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
						$download_list .= '<a href="' . esc_url_raw( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
						$download_list .= '</div>';
					} else {
						$download_list .= '<div>';
						$download_list .= edd_get_file_name( $file );
						$download_list .= '</div>';
					}

				}

			} elseif ( edd_is_bundled_product( $item['id'] ) ) {

				$bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'] ), $item, $payment_id, 'download_list' );

				foreach ( $bundled_products as $bundle_item ) {

					$download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

					$files = edd_get_download_files( $bundle_item );

					foreach ( $files as $filekey => $file ) {
						if ( $show_links ) {
							$download_list .= '<div>';
							$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
							$download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
							$download_list .= '</div>';
						} else {
							$download_list .= '<div>';
							$download_list .= edd_get_file_name( $file );
							$download_list .= '</div>';
						}
					}
				}
			}


			// if ( '' != edd_get_product_notes( $item['id'] ) ) {
			// 	$download_list .= ' &mdash; <small>' . edd_get_product_notes( $item['id'] ) . '</small>';
			// }


			if ( $show_names ) {
				$download_list .= '</li><br>';
			}
		}
	}

	if ( ( $fees = edd_get_payment_fees( $payment->ID, 'fee' ) ) ){
		foreach ($fees as $fee) {
			$download_list .= '<li>';
			$download_list .= sprintf(__('<strong>%s</strong> (%s)', 'philanthropy'), $fee['label'], charitable_format_money($fee['amount']));
			$download_list .= '</li><br>';
		}
	}


	$download_list .= '</ul>';

	return $download_list;
}

/**
 * Email template tag: download_list
 * A list of download links for each download purchased in plaintext
 *
 * @since 2.1.1
 * @param int $payment_id
 *
 * @return string download_list
 */
function pp_edd_email_tag_download_list_plain( $payment_id ) {
	// $cart = Charitable_EDD_Cart::create_with_payment( $payment_id );
	$donation_id    = get_post_meta( $payment_id, 'charitable_donation_from_edd_payment', true );

	if(empty($donation_id))
		return edd_email_tag_download_list( $payment_id );
	
	/**
	 * Display donation and downloads
	 * @var EDD_Payment
	 */
	$payment = new EDD_Payment( $payment_id );

	$payment_data  = $payment->get_meta();
	$download_list = '<ul>';
	$cart_items    = $payment->cart_details;
	$cart_fees    = $payment->fees;
	$email         = $payment->email;

	// $donation_log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
	// $donation = charitable_get_donation( $donation_id );

	/**
	 * Display Donations
	 */
	if ( !empty($cart_fees) ) {

		foreach ($cart_fees as $fee) {
			if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) )
				continue;

			$download_list .= '<li>' . sprintf(__('<strong>%s</strong> (%s)', 'philanthropy'), $fee['label'], charitable_format_money($fee['amount']) ) . '</li>';
		}
	}

	/**
	 * Display downloads
	 */
	if ( $cart_items ) {
		$show_names = apply_filters( 'edd_email_show_names', true );
		$show_links = apply_filters( 'edd_email_show_links', true );

		foreach ( $cart_items as $item ) {

			if ( edd_use_skus() ) {
				$sku = edd_get_download_sku( $item['id'] );
			}

			if ( edd_item_quantities_enabled() ) {
				$quantity = $item['quantity'];
			}

			$price_id = edd_get_cart_item_price_id( $item );
			if ( $show_names ) {

				$title = '<strong>' . get_the_title( $item['id'] ) . '</strong>';

				if ( ! empty( $quantity ) && $quantity > 1 ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
				}

				if ( ! empty( $sku ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
				}

				if ( edd_has_variable_prices( $item['id'] ) && isset( $price_id ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id, $payment_id );
				}

				$download_list .= '<li>' . $item['quantity'] . 'x ' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . ' ('.charitable_format_money($item['price']).')<br/>';
			}

			// display shipping
			if(!empty($item['fees'])){
				foreach ($item['fees'] as $key => $item_fee) {
					$download_list .= '<div>';
					$download_list .= sprintf(__('%s (%s)', 'philanthropy'), $item_fee['label'], charitable_format_money($item_fee['amount']));
					$download_list .= '</div>';
				}
			}
			

			$files = edd_get_download_files( $item['id'], $price_id );

			if ( ! empty( $files ) ) {

				foreach ( $files as $filekey => $file ) {

					if ( $show_links ) {
						$download_list .= '<div>';
							$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
							$download_list .= '<a href="' . esc_url_raw( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
							$download_list .= '</div>';
					} else {
						$download_list .= '<div>';
							$download_list .= edd_get_file_name( $file );
						$download_list .= '</div>';
					}

				}

			} elseif ( edd_is_bundled_product( $item['id'] ) ) {

				$bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'] ), $item, $payment_id, 'download_list' );

				foreach ( $bundled_products as $bundle_item ) {

					$download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

					$files = edd_get_download_files( $bundle_item );

					foreach ( $files as $filekey => $file ) {
						if ( $show_links ) {
							$download_list .= '<div>';
							$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
							$download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
							$download_list .= '</div>';
						} else {
							$download_list .= '<div>';
							$download_list .= edd_get_file_name( $file );
							$download_list .= '</div>';
						}
					}
				}
			}


			if ( '' != edd_get_product_notes( $item['id'] ) ) {
				$download_list .= ' &mdash; <small>' . edd_get_product_notes( $item['id'] ) . '</small>';
			}


			if ( $show_names ) {
				$download_list .= '</li>';
			}
		}
	}


	$download_list .= '</ul>';

	return $download_list;
}

function filter_by_value($array, $index, $value){ 
    $newarray = array();
    if(is_array($array) && count($array)>0){ 
        foreach(array_keys($array) as $key){ 
            $temp[$key] = $array[$key][$index]; 
             
            if ($temp[$key] == $value){ 
                $newarray[$key] = $array[$key]; 
            } 
        } 
      } 
  return $newarray; 
} 

/**
 * Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
 */
if(!function_exists('remove_filters_with_method_name')):
function remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;
	
	// Take only filters on right hook name and priority
	if ( !isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]) )
		return false;
	
	// Loop on filters registered
	foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
			// Test if object is a class and method is equal to param !
			if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && $filter_array['function'][1] == $method_name ) {
				unset($wp_filter[$hook_name][$priority][$unique_id]);
			}
		}
		
	}
	
	return false;
}
endif;

/**
 * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
 */
if(!function_exists('remove_filters_for_anonymous_class')):
function remove_filters_for_anonymous_class( $hook_name = '', $class_name ='', $method_name = '', $priority = 0 ) {
	global $wp_filter;
	
	// Take only filters on right hook name and priority
	if ( !isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]) )
		return false;
	
	// Loop on filters registered
	foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
			// Test if object is a class, class and method is equal to param !
			if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && get_class($filter_array['function'][0]) == $class_name && $filter_array['function'][1] == $method_name ) {
				unset($wp_filter[$hook_name][$priority][$unique_id]);
			}
		}
		
	}
	
	return false;
}
endif;

// $campaign = charitable_get_current_campaign();
// $events = philanthropy_get_campaign_event_ids($campaign->ID);
// $merchandise = philanthropy_get_campaign_merchandise_ids($campaign->ID);
// $volunteers = $campaign->get('volunteers');

// echo $campaign->ID;
// echo "<pre>";
// print_r($volunteers);
// echo "</pre>";