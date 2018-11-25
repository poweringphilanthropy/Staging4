<?php
/**
 * Philanthropy_Charitable Class.
 *
 * @class       Philanthropy_Charitable
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Philanthropy_Charitable class.
 */
class Philanthropy_Charitable {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'init', array($this, 'remove_hooks') );

		add_action( 'template_redirect', array($this, 'maybe_add_notices') );

		// add_filter( 'charitable_form_field_template', array($this, 'change_charitable_form_field_template'), 12, 3 );
		// add_filter( 'charitable_locate_template', array($this, 'change_modal_template'), 10, 2 );
	
		add_action( 'charitable_creator_download_donations', array( $this, 'download_donations_csv' ), 9 );
		add_action( 'charitable_profile_updated', array($this, 'notice_success_profile_updated'), 10, 3 );
		
		add_action( 'wp_ajax_create_dummy_post', array($this, 'create_dummy_post_id') );

		add_filter( 'charitable_email_content_field_value_donation_summary', array($this, 'get_donation_summary'), 11, 3 );
	}

	/**
	 * Change email content for Admin: New Donation Notification
	 * we need to dsplay all data
	 */
	public function get_donation_summary($value, $args, $email){
		$donation = $email->get_donation();
		$donation_id = $donation->get_donation_id();

		$payment_id = Charitable_EDD_Payment::get_payment_for_donation( $donation_id );

		$payment = new EDD_Payment( $payment_id );

		$payment_data  = $payment->get_meta();
		$download_list = '<ul>';
		$cart_items    = $payment->cart_details;
		$cart_fees    = $payment->fees;
		$email         = $payment->email;

		$donation_log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
		
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

	public function create_dummy_post_id(){
		$id = wp_insert_post(array('post_type' => 'campaign'));
		if( is_wp_error( $id ) ) {
		    $id = 0;
		}

		echo $id;
		exit();
	}

	public function remove_hooks(){
		// remove_filter( 'authenticate', array( Charitable_User_Management::get_instance(), 'maybe_redirect_at_authenticate' ), 101);
		add_filter( 'authenticate', array( $this, 'maybe_redirect_at_authenticate' ), 100, 2 );
	}

	/**
	 * Check if a failed user login attempt originated from Charitable login form. 
	 *
	 * If so redirect user to Charitable login page.
	 *
	 * @param 	WP_User|WP_Error $user_or_error
	 * @param 	string 			 $username
	 * @return  WP_User|void
	 * @access  public
	 * @since   1.4.0
	 */
	public function maybe_redirect_at_authenticate( $user_or_error, $username ) {

		if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
			return $user_or_error;
		}
		
		if ( ! is_wp_error( $user_or_error ) ) {
			return $user_or_error;
		}

		if ( ! isset( $_POST['charitable'] ) || ! $_POST['charitable'] ) {
			return $user_or_error;
		}

		if(empty($user_or_error->errors)){
			return $user_or_error;
		}

		$errors = implode(',', array_keys($user_or_error->errors));

		$redirect_url = charitable_get_permalink( 'login_page' );

		if ( strlen( $username ) ) {
			$redirect_url = add_query_arg( 'username', $username, $redirect_url );
		}

		if ( strlen( $errors ) ) {
			$redirect_url = add_query_arg( 'errors', $errors, $redirect_url );
		}

		wp_safe_redirect( esc_url_raw( $redirect_url ) );

		exit();
	}

	public function maybe_add_notices(){
		if(!isset($_GET['errors']) || empty($_GET['errors']))
			return;

		$errors = explode(',', $_GET['errors']);
		// echo "<pre>";
		// print_r($errors);
		// echo "</pre>";
		// exit();

		if(!empty($errors)):
		foreach ( $errors as $error ) {

			/* Make sure the error messages link to our forgot password page, not WordPress' */
			switch ( $error ) {

				case 'invalid_username' :
					
					$error = __( '<strong>ERROR</strong>: Invalid username.', 'charitable' ) .
						' <a href="' . esc_url( charitable_get_permalink( 'forgot_password_page' ) ) . '">' .
						__( 'Lost your password?' ) .
						'</a>';
					
					break;

				case 'invalid_email' :
					
					$error = __( '<strong>ERROR</strong>: Invalid email address.', 'charitable' ) .
						' <a href="' . esc_url( charitable_get_permalink( 'forgot_password_page' ) ) . '">' .
						__( 'Lost your password?' ) .
						'</a>';
					
					break;

				case 'incorrect_password' : 
					
					$error = sprintf(
						/* translators: %s: email address */
						__( '<strong>ERROR</strong>: The password you entered for the email address %s is incorrect.' ),
						'<strong>' . $email . '</strong>'
					) .
					' <a href="' . esc_url( charitable_get_permalink( 'forgot_password_page' ) ) . '">' .
					__( 'Lost your password?' ) .
					'</a>';
					
					break;

				default : 
					$error = false;

			}

			if($error){
				charitable_get_notices()->add_error( $error );
			}

		}

		charitable_get_session()->add_notices();

		endif;

	}

	public function notice_success_profile_updated($submitted, $fields, $form){

		// echo "<pre>";
		// var_dump($form->is_changing_password());
		// echo "</pre>";
		// echo "string"; exit();

		if($form->is_changing_password()){
			charitable_get_notices()->add_error( 'Your password has been successfully changed!', 'charitable' );
		} else {
			// charitable_get_notices()->add_error( 'Profile updated.', 'charitable' );
		}
	}

	public function change_charitable_form_field_template($template, $field, $form){
		$need_our_templates = array( 
			'edd-downloads', 
			// 'variable-prices', 
			// 'merchandise', 
			// 'event', 
			// 'datetime', 
			// 'ticket', 
			// 'volunteers' 
		);
		
		if ( in_array( $field[ 'type' ], $need_our_templates ) ) {
	        $template = new Philanthropy_Template( $form->get_template_name( $field ), false );
	    }        

	    return $template;
	}

	public function change_modal_template($template, $template_names){
		$need_our_templates = array( 
			'campaign/donate-modal.php', 
			'campaign-loop/donate-modal.php',
			// 'donation-form/form-donation.php',
		);

		$found_modal_template = array_intersect($need_our_templates, $template_names);
		if(count($found_modal_template) > 0){
			$template = Philanthropy()->plugin_path() . '/templates/' . $found_modal_template[0];
		}
		
		return $template;
	}

	public function download_donations_csv(){

		if ( ! isset( $_GET['campaign_id'] ) ) {
				return false;
		}

		$campaign_id = $_GET['campaign_id'];

		/* Check for nonce existence. */
		if ( ! charitable_verify_nonce( 'download_donations_nonce', 'download_donations_' . $campaign_id ) ) {
			return false;
		}

		if ( ! charitable_is_current_campaign_creator( $campaign_id ) ) {
			return false;
		}

		require_once( 'charitable/class-philanthropy-export-donations.php' );

		add_filter( 'charitable_export_capability', '__return_true' );

		$export_args = apply_filters( 'charitable_ambassadors_campaign_creator_donations_export_args', array(
			'campaign_id'   => $campaign_id,
		) );

		/**
		 * Use our custom class to change csv filename, and custom data source (get data from edd payment)
		 */
		if(function_exists('ppcde_add_export_data')) 
			remove_filter( 'charitable_export_data_key_value', 'ppcde_add_export_data' );
		
		// add_filter( 'charitable_export_data_key_value', array($this, 'change_export_data_key_value'), 11, 3);
		
		new Philanthropy_Export_Donations( $export_args );

		exit();
	}

	public function change_export_data_key_value($value, $key, $data){

		// if($key == 'purchase_details'){
		// 	$matched_items = array();
		// 	$row_has_shipping = false;

		// 	$donation_log = get_post_meta( $data[ 'donation_id' ], 'donation_from_edd_payment_log', true );
		// 	$payment_id = Charitable_EDD_Payment::get_payment_for_donation( $data[ 'donation_id' ] );

  //           foreach ( $donation_log as $idx => $campaign_donation ) {
  //               /* If we have already listed this donation ID, skip */
  //               if ( isset( $matched_items[ $data[ 'donation_id' ] ][ $idx ] ) ) {
  //                   continue;
  //               }

  //               /* If the campaign_id in the donation log entry does not match the current campaign donation record, skip */
  //               if ( $campaign_donation[ 'campaign_id' ] != $data[ 'campaign_id' ] ) {
  //                   continue;
  //               }

  //           //     /* If the amount does not match, skip */
  //           //     if ( $campaign_donation[ 'amount' ] != $data[ 'amount' ] ) {
  //           //         continue;
  //           //     }

  //               /* At this point, we know it matches. Check if it's a fee */
  //               if ( $campaign_donation[ 'edd_fee' ] ) {

  //                   $value = __( 'Donation', 'ppcde' );

  //               }
  //               /* If not, work through the purchased downloads and find the matching one. */
  //               else {
  //                   $payment_id = Charitable_EDD_Payment::get_payment_for_donation( $data[ 'donation_id' ] );

  //                   foreach ( edd_get_payment_meta_cart_details( $payment_id ) as $download ) {

  //                       /* The download ID must match */
  //                       if ( $download[ 'id' ] != $campaign_donation[ 'download_id' ] ) {
  //                           continue;
  //                       }

  //                       // The amount for this particular download must also match 
  //                       if ( $download[ 'subtotal' ] != $data[ 'amount' ] ) {
  //                           continue;
  //                       }
                    
  //                       $download_description = $download[ 'name' ];

  //                       if ( isset( $download[ 'item_number' ][ 'options' ][ 'price_id' ] ) ) {
  //                           $download_description = sprintf( '%s (%s)', $download_description, edd_get_price_option_name( $download[ 'id' ], $download[ 'item_number' ][ 'options' ][ 'price_id' ] ) );
  //                       }

  //                       /* If we get here, we have a match. */
  //                       $value = sprintf( '%s x %d', strip_tags( $download_description ), $download[ 'quantity' ] );

  //                       /* Check for shipping fees associated with this download */
  //                       if ( empty( $download[ 'fees' ] ) ) {
  //                           break;
  //                       }
                        
  //                       foreach ( $download[ 'fees' ] as $fee_id => $fee ) {
  //                           if ( false === strpos( $fee_id, 'simple_shipping' ) ) {
  //                               continue;
  //                           }

  //                           /* This row has shipping, so store the $fee in our static variable */
  //                           $row_has_shipping = $fee;
  //                           break;
  //                       }

  //                       break;

  //                       die;
  //                   }
  //               }

  //               // $matched_items[ $data[ 'donation_id' ] ][ $idx ] = $value;
  //           }
   			
  //  			$value = array(
  //  				'donation_log' => $donation_log,
  //  				'payment_id' => $payment_id,
  //  				'cart' =>  edd_get_payment_meta_cart_details( $payment_id )
  //  			);
		// }

		return $value;
	}

	public function includes(){
		
	}

}

$GLOBALS['philanthropy_charitable'] = new Philanthropy_Charitable();