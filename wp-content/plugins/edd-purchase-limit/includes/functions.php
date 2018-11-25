<?php
/**
 * Add Helper Functions and Template Overrides
 *
 * @package     EDD\PurchaseLimit\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Gets the number of purchases for a variably priced download
 *
 * @since       1.0.6
 * @param       int $download_id The ID for this download
 * @param       int $price_id The price ID for this item
 * @param       string $user_email The email for the purchaser
 * @return      mixed $purchases
 */
function edd_pl_get_file_purchases( $download_id = 0, $price_id = false, $user_email = false ) {
	global $post;

	$post_old  = $post;
	$scope     = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
	$purchased = 0;

	if ( $scope !== 'site-wide' ) {
		// Retrieve all sales of this download
		$query_args = array(
			'download' => $download_id,
			'number'   => -1
		);

		// Override global search if site-wide isn't selected
		if( $scope != 'site-wide' ) {
			if( ! $user_email ) {
				$current_user = wp_get_current_user();
			}

			$query_args['s'] = $current_user->user_email;

			$query     = new EDD_Payments_Query( $query_args );
			$payments  = $query->get_payments();

			// Count purchases
			if( is_array( $payments ) ) {
				foreach( $payments as $payment_id => $payment_data ) {
					if( is_object( $payment_data ) && property_exists( $payment_data, 'cart_details' ) && is_array( $payment_data->cart_details ) ) {
						foreach( $payment_data->cart_details as $cart_item ) {
							if( edd_has_variable_prices( $download_id ) ) {
								if( isset( $cart_item['item_number']['options']['quantity'] ) ) {
									$purchased = $purchased + $cart_item['item_number']['options']['quantity'];
								} else {
									$purchased = $purchased + 1;
								}
							} else {
								if( isset( $cart_item['item_number'] ) ) {
									if( isset( $cart_item['item_number']['options']['quantity'] ) ) {
										$purchased = $purchased + $cart_item['item_number']['options']['quantity'];
									} else {
										// This REALLY shouldn't happen
										$purchased = $purchased + 1;
									}
								}
							}
						}
					}
				}
			}
		}
	} else {
		// Get all purchases for this download
		$logs_query = new EDD_Logging();
		$log_args   = array(
			'posts_per_page' => - 1,
			'log_type'       => 'sale',
			'post_parent'    => $download_id,
		);

		if ( false !== $price_id ) {
			$log_args['meta_query'] = array( array(
				'key'     => '_edd_log_price_id',
				'value'   => $price_id,
			) );
		}

		$logs = $logs_query->get_connected_logs( $log_args );
		if ( ! empty( $logs ) ) {
			$purchased = count( $logs );
		}


	}
	wp_reset_postdata();
	$post = $post_old;

	return $purchased;
}


/**
 * Gets the file purchase limit for a particular download
 *
 * This function is only necessary if variable pricing isn't enabled
 *
 * @since       1.0.0
 * @param       int $download_id The ID for this download
 * @param       int $type Optional override to force return of a specific type
 * @param       int $price_id The ID for a specific item
 * @return      mixed $limit File purchase limit
 */
function edd_pl_get_file_purchase_limit( $download_id = 0, $type = null, $price_id = null ) {
	$ret = 0;

	if( edd_has_variable_prices( $download_id ) && ( isset( $type ) && $type == 'standard' ) ) {
		$limit  = get_post_meta( $download_id, '_edd_purchase_limit', true );
	} elseif( edd_has_variable_prices( $download_id ) || ( isset( $type ) && $type == 'variable' ) ) {
		$prices = edd_get_variable_prices( $download_id );

		if( isset( $price_id ) ) {
			$limit = ( isset( $prices[$price_id]['purchase_limit'] ) ? absint( $prices[$price_id]['purchase_limit'] ) : '0' );
		} else {
			$limit = array();

			if ( $prices ) {
				foreach( $prices as $item_id => $item_data ) {
					$limit[$item_id] = ( isset( $item_data['purchase_limit'] ) ? absint( $item_data['purchase_limit'] ) : '0' );
				}
			}
		}
	} else {
		$limit  = get_post_meta( $download_id, '_edd_purchase_limit', true );
	}

	$limit = is_numeric( $limit ) ? $limit == -1 ? $limit : absint( $limit ) : 0;
	$ret   = ! empty( $limit ) ? ! is_array( $limit ) ? $limit : $limit : 0;

	return apply_filters( 'edd_file_purchase_limit', $ret, $download_id );
}


/**
 * Check if an item in a product is sold out
 *
 * @since       1.0.0
 * @param       int $download_id The download ID to check
 * @param       int $price_id The ID of the item to check
 * @param       mixed $user_email The email of the user to check
 * @param       bool $inclusive Whether to include this purchase in the check
 * @return      boolean $sold_out
 */
function edd_pl_is_item_sold_out( $download_id = 0, $price_id = 0, $user_email = false, $inclusive = true ) {
	$sold_out = false;

	$max_purchases = edd_pl_get_file_purchase_limit( $download_id, null, $price_id );
	$purchased = edd_pl_get_file_purchases( $download_id, $price_id );

	if( ( $purchased >= $max_purchases && $max_purchases > 0 ) || $max_purchases == -1 ) {
		$sold_out = true;
	}

	if( edd_item_in_cart( $download_id, array( 'price_id' => $price_id ) ) ) {
		$purchased++;

		if( $inclusive ) {
			if( $purchased >= $max_purchases && $max_purchases > 0 ) {
				$sold_out = true;
			}
		} else {
			if( $purchased > $max_purchases && $max_purchases > 0 ) {
				$sold_out = true;
			}
		}
	}

	return $sold_out;
}


/**
 * Check if an item is date restricted
 *
 * @since       1.0.6
 * @param       int $download_id The download ID to check
 * @return      mixed array if restricted, null otherwise
 */
function edd_pl_is_date_restricted( $download_id = 0 ) {
	date_default_timezone_set( edd_get_timezone_id() );

	$range = null;

	if( edd_get_option( 'edd_purchase_limit_restrict_date' ) ) {
		$range['start'] = explode( ' ', get_post_meta( $download_id, '_edd_purchase_limit_start_date', true ) );
		$range['end']   = explode( ' ', get_post_meta( $download_id, '_edd_purchase_limit_end_date', true ) );

		if( edd_get_option( 'edd_purchase_limit_g_start_date' ) && empty( $range['start'][0] ) ) {
			$range['start'] = explode( ' ', edd_get_option( 'edd_purchase_limit_g_start_date') );
		}

		if( edd_get_option( 'edd_purchase_limit_g_end_date' ) && empty( $range['end'][0] ) ) {
			$range['end'] = explode( ' ', edd_get_option( 'edd_purchase_limit_g_end_date' ) );
		}

		foreach( $range as $key => &$value ) {
			if( is_array( $value ) ) {
				$value = array_filter( $value );
				$range[$key] = $value;
			}
		}

		$range = array_filter( $range );

		if( count( $range ) == 0 ) {
			$range = null;
		}

		// Maintain backwards compatibility
		if( isset( $range['start'][0] ) && ! isset( $range['start'][1] ) ) {
			$range['start'][1] = '00:00';
		}

		if( isset( $range['end'][0] ) && ! isset( $range['end'][1] ) ) {
			$range['end'][1] = '00:00';
		}
	}

	return $range;
}


/**
 * Override the add to cart button if the max purchase limit has been reached
 *
 * @since       1.0.0
 * @param       string $purchase_form the actual purchase form code
 * @param       array $args the info for the specific download
 * @global      string $user_email The email address of the current user
 * @global      boolean $edd_prices_sold_out Variable price sold out check
 * @return      string $purchase_form if conditions are not met
 * @return      string $sold_out if conditions are met
 */
function edd_pl_override_purchase_button( $purchase_form, $args ) {
	global $user_email, $edd_prices_sold_out;

	// Get options
	$sold_out_label = edd_get_option( 'edd_purchase_limit_sold_out_label' ) ? edd_get_option( 'edd_purchase_limit_sold_out_label' ) : __( 'Sold Out', 'edd-purchase-limit' );
	$scope          = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
	$form_id        = ! empty( $args['form_id'] ) ? $args['form_id'] : 'edd_purchase_' . $args['download_id'];

	// Get purchase limits
	$max_purchases  = edd_pl_get_file_purchase_limit( $args['download_id'] );
	$is_sold_out    = false;
	$date_range     = edd_pl_is_date_restricted( $args['download_id'] );

	if( $scope == 'site-wide' ) {
		$purchases = edd_get_download_sales_stats( $args['download_id'] );

		if( ( $max_purchases && $purchases >= $max_purchases ) || !empty( $edd_prices_sold_out ) ) {
			$is_sold_out = true;
		}
	} elseif( is_user_logged_in() ) {
		$purchases = edd_pl_get_user_purchase_count( get_current_user_id(), $args['download_id'] );

		if( ( $max_purchases && $purchases >= $max_purchases ) || !empty( $edd_prices_sold_out ) ) {
			$is_sold_out = true;
		}
	}

	if( $is_sold_out ) {
		$purchase_form  = '<form id="' . $form_id . '" class="edd_download_purchase_form">';
		$purchase_form .= '<div class="edd_purchase_submit_wrapper">';

		if( edd_is_ajax_enabled() ) {
			$purchase_form .= sprintf(
				'<div class="edd-add-to-cart %1$s"><span>%2$s</span></a>',
				implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
				esc_attr( $sold_out_label )
			);
			$purchase_form .= '</div>';
		} else {
			$purchase_form .= sprintf(
				'<input type="submit" class="edd-no-js %1$s" name="edd_purchase_download" value="%2$s" disabled />',
				implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
				esc_attr( $sold_out_label )
			);
		}

		$purchase_form .= '</div></form>';
	} elseif( is_array( $date_range ) ) {
		$now = date( 'YmdHi' );
		$date_label = null;

		if( isset( $date_range['start'] ) ) {
			$start_time = date( 'YmdHi', strtotime( $date_range['start'][0] . $date_range['start'][1] ) );

			if( $start_time > $now ) {
				$date_label = edd_get_option( 'edd_purchase_limit_pre_date_label' ) ? edd_get_option( 'edd_purchase_limit_pre_date_label' ) : __( 'This product is not yet available!', 'edd-purchase-limit' );
			}
		}

		if( isset( $date_range['end'] ) ) {
			$end_time = date( 'YmdHi', strtotime( $date_range['end'][0] . $date_range['end'][1] ) );

			if( $end_time < $now ) {
				$date_label = edd_get_option( 'edd_purchase_limit_post_date_label' ) ? edd_get_option( 'edd_purchase_limit_post_date_label' ) : __( 'This product is no longer available!', 'edd-purchase-limit' );
			}
		}

		if( isset( $date_label ) ) {
			$purchase_form  = '<form id="' . $form_id . '" class="edd_download_purchase_form">';
			$purchase_form .= '<div class="edd_purchase_submit_wrapper">';

			if( edd_is_ajax_enabled() ) {
				$purchase_form .= sprintf(
					'<div class="edd-add-to-cart %1$s"><span>%2$s</span></div>',
					implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
					esc_attr( $date_label )
				);
			} else {
				$purchase_form .= sprintf(
					'<input type="submit" class="edd-no-js %1$s" name="edd_purchase_download" value="%2$s" disabled />',
					implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
					esc_attr( $date_label )
				);
			}

			$purchase_form .= '</div></form>';
		} elseif( edd_get_option( 'edd_purchase_limit_show_counts' ) ) {
			$label            = edd_get_option( 'add_to_cart_text' ) ? edd_get_option( 'add_to_cart_text' ) : __( 'Purchase', 'edd' );
			$remaining_label  = edd_get_option( 'edd_purchase_limit_remaining_label' ) ? edd_get_option( 'edd_purchase_limit_remaining_label' ) : __( 'Remaining', 'edd-purchase-limit' );
			$variable_pricing = edd_has_variable_prices( $args['download_id'] );

			if( ! $variable_pricing ) {
				$remaining = $max_purchases - $purchases;

				if( $remaining > '0' ) {
					$purchase_form = str_replace( $label . '</span>', $label . '</span> <span class="edd-pl-remaining-label">(' . $remaining . ' ' . $remaining_label . ')</span>', $purchase_form );
				}
			}
		}
	} elseif( edd_get_option( 'edd_purchase_limit_show_counts' ) ) {
		$label            = edd_get_option( 'add_to_cart_text' ) ? edd_get_option( 'add_to_cart_text' ) : __( 'Purchase', 'edd' );
		$remaining_label  = edd_get_option( 'edd_purchase_limit_remaining_label' ) ? edd_get_option( 'edd_purchase_limit_remaining_label' ) : __( 'Remaining', 'edd-purchase-limit' );
		$variable_pricing = edd_has_variable_prices( $args['download_id'] );

		if( ! $variable_pricing ) {
			$remaining = $max_purchases - $purchases;

			if( $remaining > '0' ) {
				$purchase_form = str_replace( $label . '</span>', $label . '</span> <span class="edd-pl-remaining-label">(' . $remaining . ' ' . $remaining_label . ')</span>', $purchase_form );
			}
		}
	}

	return $purchase_form;
}
add_filter( 'edd_purchase_download_form', 'edd_pl_override_purchase_button', 200, 2 );


/**
 * Override the add to cart button if the max purchase limit has been reached
 *
 * Variable prices function
 *
 * @since       1.0.4
 * @param       int $download_id the ID for the specific download
 * @global      boolean $edd_prices_sold_out Variable price sold out check
 * @return      string $purchase_form if conditions are not met
 * @return      string $sold_out if conditions are met
 */
function edd_pl_override_variable_pricing( $download_id = 0 ) {
	global $edd_prices_sold_out;

	$variable_pricing = edd_has_variable_prices( $download_id );

	if( $variable_pricing ) {
		// Get options
		$sold_out_label = edd_get_option( 'edd_purchase_limit_sold_out_label' ) ? edd_get_option( 'edd_purchase_limit_sold_out_label' ) : __( 'Sold Out', 'edd-purchase-limit' );
		$scope          = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
		$type           = edd_single_price_option_mode( $download_id ) ? 'checkbox' : 'radio';
		$sold_out       = array();

		// Get variable prices
		$prices = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $download_id ), $download_id );

		do_action( 'edd_before_price_options', $download_id );

		echo '<div class="edd_price_options">';
		echo '<ul>';

		if( $prices ) {
			$disable_all = get_post_meta( $download_id, '_edd_purchase_limit_variable_disable', true );
			$disabled    = false;

			if( $disable_all ) {
				foreach( $prices as $price_id => $price_data ) {
					if( edd_pl_is_item_sold_out( $download_id, $price_id ) ) {
						$disabled = true;
						break;
					}
				}
			}

			foreach( $prices as $price_id => $price_data ) {
				$checked_key = isset( $_GET['price_option'] ) ? absint( $_GET['price_option'] ) : edd_get_default_variable_price( $download_id );

				// Output label
				echo '<li id="edd_price_option_' . $download_id . '_' . sanitize_key( $price_data['name'] ) . '">';

				// Output option or 'sold out'
				if( edd_pl_is_item_sold_out( $download_id, $price_id ) || $disabled ) {
					// Update $sold_out
					$sold_out[] = $price_id;

					printf(
						'<label for="%2$s"><input type="%1$s" name="edd_options[price_id][]" id="%2$s" class="%3$s" value="%4$s" disabled /> %5$s</label>',
						$type,
						esc_attr( 'edd_price_option_' . $download_id . '_' . $price_id ),
						esc_attr( 'edd_price_option_' . $download_id ),
						esc_attr( $price_id ),
						'<span class="edd_price_option_name">' . esc_html( $price_data['name'] ) . '</span><span class="edd_price_option_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_option_price">' . $sold_out_label . '</span>'
					);
				} else {
					$max_purchases    = edd_pl_get_file_purchase_limit( $download_id, null, $price_id );
					$purchases        = edd_pl_get_file_purchases( $download_id, $price_id );
					$remaining        = $max_purchases - $purchases;
					$remaining_output = null;

					if( edd_get_option( 'edd_purchase_limit_show_counts' ) && ( $remaining > '0' ) ) {
						$remaining_label  = edd_get_option( 'edd_purchase_limit_remaining_label' ) ? edd_get_option( 'edd_purchase_limit_remaining_label' ) : __( 'Remaining', 'edd-purchase-limit' );
						$remaining_output = ' <span class="edd-pl-variable-remaining-label">(' . $remaining . ' ' . $remaining_label . ')</span>';
					}

					printf(
						'<label for="%3$s"><input type="%2$s" %1$s name="edd_options[price_id][]" id="%3$s" class="%4$s" value="%5$s" %7$s/> %6$s</label>',
						checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $price_id ), $price_id, false ),
						$type,
						esc_attr( 'edd_price_option_' . $download_id . '_' . $price_id ),
						esc_attr( 'edd_price_option_' . $download_id ),
						esc_attr( $price_id ),
						'<span class="edd_price_option_name">' . esc_html( $price_data['name'] ) . '</span><span class="edd_price_option_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_option_price">' . edd_currency_filter( edd_format_amount( $price_data['amount'] ) ) . '</span>' . ( isset( $remaining_output ) ? $remaining_output : '' ),
						checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $price_id ), $price_id, false )
					);
				}

				remove_action( 'edd_after_price_option', 'edd_variable_price_quantity_field', 10, 3 );
				do_action( 'edd_after_price_option', $price_id, $price_data, $download_id );
				echo '</li>';
			}
		} else {
			foreach( $prices as $price_id => $price_data ) {
				// Output label
				echo '<li id="edd_price_option_' . $download_id . '_' . sanitize_key( $price_data['name'] ) . '">';

				// Output option or 'sold out'
				printf(
					'<label for="%3$s"><input type="%2$s" %1$s name="edd_options[price_id][]" id="%3$s" class="%4$s" value="%5$s" %7$s/> %6$s</label>',
					checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $price_id ), $price_id, false ),
					$type,
					esc_attr( 'edd_price_option_' . $download_id . '_' . $price_id ),
					esc_attr( 'edd_price_option_' . $download_id ),
					esc_attr( $price_id ),
					'<span class="edd_price_option_name">' . esc_html( $price_data['name'] ) . '</span><span class="edd_price_option_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_option_price">' . edd_currency_filter( edd_format_amount( $price_data['amount'] ) ) . '</span>',
					checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $price_id ), $price_id, false )
				);

				remove_action( 'edd_after_price_option', 'edd_variable_price_quantity_field', 10, 3 );
				do_action( 'edd_after_price_option', $price_id, $price_data, $download_id );
				echo '</li>';
			}
		}

		do_action( 'edd_after_price_options_list', $download_id, $prices, $type );

		echo '</ul>';
		echo '</div>';

		if( count( $sold_out ) == count( $prices ) ) {
			$edd_prices_sold_out = true;
		} else {
			$edd_prices_sold_out = false;
		}

		do_action( 'edd_after_price_options', $download_id );
	}
}
remove_action( 'edd_purchase_link_top', 'edd_purchase_variable_pricing', 10 );
add_action( 'edd_purchase_link_top', 'edd_pl_override_variable_pricing', 10 );


/**
 * Override edd_pre_add_to_cart so users can't add through direct linking
 *
 * @since       1.0.0
 * @param       int $download_id The ID of a specific download
 * @param       array $options The options for this downloads
 * @return      void
 */
function edd_pl_override_add_to_cart( $download_id, $options ) {
	// Get options
	$scope            = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
	$sold_out         = false;
	$variable_pricing = edd_has_variable_prices( $download_id );

	if ( $variable_pricing ) {

		// Get variable prices.
		$prices   = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $download_id ), $download_id );
		$price_id = isset( $options['price_id'] ) && array_key_exists( $options['price_id'], $prices ) ? absint( $options['price_id'] ) : edd_get_default_variable_price( $download_id );

		// Only check past purchase history, if this variable price has a purchase limit set on it.
		if ( isset( $prices[ $price_id ] ) && ! empty( $prices[ $price_id ]['purchase_limit'] ) ) {
			$sold_out = edd_pl_is_item_sold_out( $download_id, $price_id );
		}

	} else {

		// Get purchase limits
		$max_purchases = edd_pl_get_file_purchase_limit( $download_id );
		if ( empty( $max_purchases ) ) {
			return;
		}

		if ( $scope == 'site-wide' ) {
			$purchases = edd_get_download_sales_stats( $download_id );

			if ( ( $max_purchases && $purchases >= $max_purchases ) ) {
				$sold_out = true;
			}
		} else if ( is_user_logged_in() ) {
			$purchases = edd_pl_get_user_purchase_count( get_current_user_id(), $download_id );

			if( ( $max_purchases && $purchases >= $max_purchases ) ) {
				$sold_out = true;
			}
		}

		if ( edd_item_in_cart( $download_id ) ) {
			$purchases = edd_get_cart_item_quantity( $download_id );
			$purchases++;

			if ( $purchases >= $max_purchases ) {
				$sold_out = true;
			}
		}

	}

	if ( $sold_out === true ) {
		if ( edd_get_option( 'edd_purchase_limit_error_handler', 'std' ) == 'redirect' ) {
			$message      = sprintf( __( 'This %s is sold out!', 'edd-purchase-limit' ), edd_get_label_singular( true ) );
			$message      = edd_get_option( 'edd_purchase_limit_error_message', $message );
			$redirect_url = edd_get_option( 'edd_purchase_limit_redirect_url', false );

			if ( $redirect_url ) {
				wp_redirect( get_permalink( $redirect_url ) );
				exit;
			} else {
				wp_die( $message );
			}
		} else {
			$message = sprintf( __( 'This %s is sold out!', 'edd-purchase-limit' ), edd_get_label_singular( true ) );
			$message = edd_get_option( 'edd_purchase_limit_error_message', $message );

			wp_die( $message );
		}
	}
}
add_action( 'edd_pre_add_to_cart', 'edd_pl_override_add_to_cart', 200, 2 );


/**
 * Add extra error-checking to checkout process to prevent race conditions on purchase
 *
 * @since       1.0.5
 * @param       array $valid_data Valid data for this purchase
 * @param       array $post_data The data for this specific purchase
 * @return      void
 */
function edd_pl_check_limit_on_purchase( $valid_data=array(), $post_data=array() ) {
	// Get options
	$scope      = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
	$sold_out   = false;
	$cart_items = edd_get_cart_contents();

	foreach( $cart_items as $key => $item ) {
		$variable_pricing = edd_has_variable_prices( $item['id'] );

		if( $variable_pricing ) {
			// Get variable prices
			$prices = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $item['id'] ), $item['id'] );
			$price_id = isset( $item['options']['price_id'] ) && array_key_exists( $item['options']['price_id'], $prices ) ? absint( $item['options']['price_id'] ) : edd_get_default_variable_price( $item['id'] );

			// Only check past purchase history, if this variable price has a purchase limit set on it.
			if ( isset( $prices[ $price_id ] ) && ! empty( $prices[ $price_id ]['purchase_limit'] ) ) {
				$sold_out = edd_pl_is_item_sold_out( $item['id'], $price_id, null, false );
			}
		} else {
			// Get purchase limits
			$max_purchases = edd_pl_get_file_purchase_limit( $item['id'] );

			if ( empty( $max_purchases ) ) {
				continue;
			}

			if( $scope == 'site-wide' ) {
				$purchases = edd_get_download_sales_stats( $item['id'] );

				if( ( $max_purchases && $purchases >= $max_purchases ) ) {
					$sold_out = true;
				}
			} else if ( is_user_logged_in() ) {
				$purchases = edd_pl_get_user_purchase_count( get_current_user_id(), $item['id'] );

				if( ( $max_purchases && $purchases >= $max_purchases ) ) {
					$sold_out = true;
				}
			}
		}
	}

	if( $sold_out === true ) {
		edd_set_error( 'purchase_limit_reached', sprintf( __( 'The product \'%s\' is sold out!', 'edd-purchase-limit' ), get_the_title( $item['id'] ) ) );
	}
}
add_action( 'edd_checkout_error_checks', 'edd_pl_check_limit_on_purchase', 10, 2 );

/**
 * Get user purchase count for download
 *
 * @since       1.0.1
 * @param       int $user_id the ID of the user to check
 * @param       int $download_id the download ID to check
 * @param       int $variable_price_id the variable price ID to check
 * @return      int $count the number of times the product has been purchased
 */
function edd_pl_get_user_purchase_count( $user_id, $download_id, $variable_price_id = null ) {
	if( ! is_user_logged_in() ) return 0;

	$users_purchases = edd_get_users_purchases( $user_id, 999 );
	$count           = 0;
	$download_id     = array( $download_id );

	if( $users_purchases ) {
		foreach( $users_purchases as $purchase ) {
			$purchased_files = edd_get_payment_meta_downloads( $purchase->ID );

			if( is_array( $purchased_files ) ) {
				foreach( $purchased_files as $download ) {
					if( isset( $download['id'] ) && in_array( $download['id'], $download_id ) ) {
						$count++;
					}
				}
			}
		}
	}

	return $count;
}


/**
 * Override the standard quantity field for variably priced products
 *
 * @since       1.2.10
 * @param       int $key The price ID of this download
 * @param       array $price The price option array
 * @param       int $download_id The ID of this download
 * @return      void
 */
function edd_pl_variable_price_quantity_field( $key, $price, $download_id ) {
	if( ! edd_item_quantities_enabled() ) {
		return;
	}

	if( ! edd_single_price_option_mode() ) {
		return;
	}

	// Get options
	$scope         = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
	$sold_out      = array();
	$disabled      = ( edd_pl_is_item_sold_out( $download_id, $key ) ? 'disabled="disabled" ' : '' );
	$max_purchases = edd_pl_get_file_purchase_limit( $download_id, null, $key );
	$max           = 'max="" ';

	if( $max_purchases ) {
		$purchases = edd_pl_get_file_purchases( $download_id, $key );
		$remaining = $max_purchases - $purchases;
		$max       = 'max="' . $remaining . '" ';
	}

	echo '<div class="edd_download_quantity_wrapper edd_download_quantity_price_option_' . sanitize_key( $price['name'] ) . '">';
	echo '<span class="edd_price_option_sep">&nbsp;x&nbsp;</span>';
	echo '<input type="number" min="1" ' . $max . 'step="1" ' . $disabled . 'name="edd_download_quantity_' . esc_attr( $key ) . '" class="edd-input edd-item-quantity" value="1" />';
	echo '</div>';
}
add_action( 'edd_after_price_option', 'edd_pl_variable_price_quantity_field', 10, 3 );


/**
 * Override the standard quantity field for non-variably priced products
 *
 * @since       1.2.10
 * @param       int $download_id The ID of this download
 * @param       array $args Array of arguments
 * @return      void
 */
function edd_pl_download_purchase_form_quantity_field( $download_id = 0, $args = array() ) {
	global $edd_prices_sold_out;

	if( ! edd_item_quantities_enabled() ) {
		return;
	}

	if( edd_item_in_cart( $download_id ) && ! edd_has_variable_prices( $download_id ) ) {
		return;
	}

	if( edd_single_price_option_mode( $download_id ) && edd_has_variable_prices( $download_id ) && ! edd_item_in_cart( $download_id ) ) {
		return;
	}

	if( edd_single_price_option_mode( $download_id ) && edd_has_variable_prices( $download_id ) && edd_item_in_cart( $download_id ) ) {
		return;
	}

	if( ! edd_single_price_option_mode( $download_id ) && edd_has_variable_prices( $download_id ) && edd_item_in_cart( $download_id ) ) {
		return;
	}

	// Get options
	$scope         = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
	$max_purchases = edd_pl_get_file_purchase_limit( $download_id );
	$disabled      = '';

	if( $scope == 'site-wide' ) {
		$purchases = edd_get_download_sales_stats( $download_id );
	} elseif( is_user_logged_in() ) {
		$purchases = edd_pl_get_user_purchase_count( get_current_user_id(), $download_id );
	}

	if( $purchases ) {
		if( ( $max_purchases && $purchases >= $max_purchases ) || ! empty( $edd_prices_sold_out ) ) {
			$disabled = 'disabled="disabled"';
		}
	}

	$max = 'max="" ';

	if( $max_purchases ) {
		$remaining = $max_purchases - $purchases;
		$max       = 'max="' . $remaining . '" ';
	}

	echo '<div class="edd_download_quantity_wrapper">';
	echo '<input type="number" min="1" ' . $max . 'step="1" ' . $disabled . 'name="edd_download_quantity" class="edd-input edd-item-quantity" value="1" />';
	echo '</div>';
}
remove_action( 'edd_purchase_link_top', 'edd_download_purchase_form_quantity_field', 10, 2 );
add_action( 'edd_purchase_link_top', 'edd_pl_download_purchase_form_quantity_field', 10, 2 );


/**
 * Ensure cart quantities are OK
 *
 * @since       1.0.0
 * @return      void
 */
function edd_pl_checkout_errors( $valid_data, $posted ) {
	global $edd_prices_sold_out;

	$cart   = edd_get_cart_contents();
	$scope  = edd_get_option( 'edd_purchase_limit_scope' ) ? edd_get_option( 'edd_purchase_limit_scope' ) : 'site-wide';
	$errors = array();

	foreach( $cart as $item ) {
		if( edd_has_variable_prices( $item['id'] ) ) {
			if( edd_pl_is_item_sold_out( $item['id'], $item['options']['price_id'], false, false ) ) {
				$errors[] = array(
					'id'    => $item['id'],
					'price' => $item['options']['price_id'],
					'type'  => 'soldout',
					'avail' => null
				);
			}
		} else {
			$max_purchases = edd_pl_get_file_purchase_limit( $item['id'] );

			if( $scope == 'site-wide' ) {
				$purchases = edd_get_download_sales_stats( $item['id'] );

				if( ( $max_purchases && $purchases >= $max_purchases ) || ! empty( $edd_prices_sold_out ) ) {
					$errors[] = array(
						'id'    => $item['id'],
						'price' => null,
						'type'  => 'soldout',
						'avail' => null
					);
				}
			} else {
				if( is_user_logged_in() ) {
					$purchases = edd_pl_get_user_purchase_count( get_current_user_id(), $item['id'] );

					if( ( $max_purchases && $purchases >= $max_purchases ) || ! empty( $edd_prices_sold_out ) ) {
						$errors[] = array(
							'id'    => $item['id'],
							'price' => null,
							'type'  => 'soldout',
							'avail' => null
						);
					}
				}
			}
		}

		if( edd_item_in_cart( $item['id'] ) ) {
			if( edd_has_variable_prices( $item['id'] ) ) {
				$max_purchases = edd_pl_get_file_purchase_limit( $item['id'], null, $item['options']['price_id'] );
				$purchases     = edd_pl_get_file_purchases( $item['id'], $item['options']['price_id'] );
			}

			if( $max_purchases > 0 ) {
				$cart_qty = edd_get_cart_item_quantity( $item['id'] );
				$total    = $purchases + $cart_qty;

				if( $total > $max_purchases ) {
					$errors[] = array(
						'id'    => $item['id'],
						'price' => ( edd_has_variable_prices( $item['id'] ) ? $item['options']['price_id'] : null ),
						'type'  => 'toomany',
						'avail' => $max_purchases - $purchases
					);
				}
			}
		}
	}

	if( count( $errors ) > 0 ) {
		foreach( $errors as $error ) {
			$product = get_post( $error['id'] );

			if( $error['type'] == 'soldout' ) {
				edd_set_error( 'purchase_limit_reached', sprintf( __( 'The %s "%s" is sold out!', 'edd-purchase-limit' ), strtolower( edd_get_label_singular() ), $product->post_title ) );
			} elseif( $error['type'] == 'toomany' ) {
				edd_set_error( 'purchase_limit_exceeded', sprintf( _n( 'There is only %s available for the %s "%s"!', 'There are only %s available for the %s "%s"!', $error['avail'], 'edd-purchase-limit' ), $error['avail'], strtolower( edd_get_label_singular() ), $product->post_title ) );
			}
		}
	}
}
add_action( 'edd_checkout_error_checks', 'edd_pl_checkout_errors', 10, 2 );
