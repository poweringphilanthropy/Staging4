<?php
/**
 * Class to assist with the setup of extension licenses.
 *
 * @package     Charitable/Classes/Charitable_Licenses
 * @version     1.4.20
 * @author      Eric Daams
 * @copyright   Copyright (c) 2018, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Licenses' ) ) :

	/**
	 * Charitable_Licenses
	 *
	 * @since   1.0.0
	 */
	class Charitable_Licenses {

		/* @var string */
		const UPDATE_URL = 'https://www.wpcharitable.com';

		/**
		 * The single instance of this class.
		 *
		 * @var Charitable_Licenses|null
		 */
		private static $instance = null;

		/**
		 * All the registered products requiring licensing.
		 *
		 * @var array
		 */
		private $products;

		/**
		 * All the stored licenses.
		 *
		 * @var array
		 */
		private $licenses;

		/**
		 * Cached update data.
		 *
		 * @var array
		 */
		private $update_data;

		/**
		 * Returns and/or create the single instance of this class.
		 *
		 * @since  1.2.0
		 *
		 * @return Charitable_Licenses
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Create class object.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			$this->products    = array();
			$this->update_data = array();

			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
			add_action( 'charitable_deactivate_license', array( $this, 'deactivate_license' ) );
			add_filter( 'upgrader_pre_download', array( $this, 'set_upgrader_error_message' ), 10, 3 );
			add_filter( 'upgrader_package_options', array( $this, 'set_upgrader_package_options' ) );			
		}

		/**
		 * Checks for any Charitable extensions with updates.
		 *
		 * @since  1.4.0
		 *
		 * @param  array $_transient_data The plugin updates data.
		 * @return array
		 */
		public function check_for_updates( $_transient_data ) {
			global $pagenow;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new stdClass;
			}

			if ( 'plugins.php' == $pagenow && is_multisite() ) {
				return $_transient_data;
			}

			/* Loop over our licensed products and check whether any are missing transient data. */
			$missing_data = array();

			foreach ( $this->get_products() as $product ) {
				if ( $this->is_missing_version_info( $product, $_transient_data ) ) {
					$missing_data[] = $product;
				}
			}

			/* If we are missing data for any of our products, check whether any have an update. */
			if ( ! empty( $missing_data ) ) {

				$versions = $this->get_versions();

				unset( $versions['request_speed'] );

				if ( ! empty( $versions ) ) {

					$versions_name_lookup = wp_list_pluck( $versions, 'name' );

					foreach ( $missing_data as $product ) {

						if ( ! in_array( $product['name'], $versions_name_lookup ) ) {
							continue;
						}

						$plugin_file             = plugin_basename( $product['file'] );
						$product_key             = array_search( $product['name'], wp_list_pluck( $this->get_products(), 'name' ) );
						$version_info            = $versions[ array_search( $product['name'], $versions_name_lookup ) ];
						$version_info['license'] = $this->get_license_details( $product_key ); 

						if ( version_compare( $product['version'], $version_info['new_version'], '<' ) ) {

							if ( isset( $version_info['sections'] ) ) {
								$version_info['sections'] = maybe_unserialize( $version_info['sections'] );
							}

							$_transient_data->response[ $plugin_file ] = (object) $version_info;

						}

						$_transient_data->last_checked            = time();
						$_transient_data->checked[ $plugin_file ] = $product['version'];

					}//end foreach
				}//end if
			}//end if

			return $_transient_data;
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @uses   api_request()
		 *
		 * @since  1.4.20
		 *
		 * @param  mixed  $_data   Default set of data.
		 * @param  string $_action The current action.
		 * @param  object $_args   Request args. 
		 * @return object $_data
		 */
		public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
			if ( 'plugin_information' != $_action ) {
				return $_data;
			}

			if ( ! isset( $_args->slug ) ) {
				return $_data;
			}

			$plugin_key = str_replace( '-', '_', $_args->slug );

			if ( ! array_key_exists( $plugin_key, $this->products ) ) {
				return $_data;
			}

			$version_info = $this->get_version_info( plugin_basename( $this->products[ $plugin_key ]['file'] ) );

			if ( $version_info ) {
				$_data = $version_info;
			}

			return $_data;
		}

		/**
		 * Return whether a particular plugin is missing version info.
		 *
		 * @since  1.4.20
		 *
		 * @param  array        $product      Product details array.
		 * @param  false|object $update_cache Optional argument to pass update cache.
		 * @return boolean
		 */
		public function is_missing_version_info( $product, $update_cache = false ) {
			return ! $this->get_version_info( plugin_basename( $product['file'] ), $update_cache );
		}

		/**
		 * Return the version update info for a particular plugin.
		 *
		 * @since  1.4.20
		 *
		 * @param  string       $slug         The plugin slug.
		 * @param  false|object $update_cache Optional argument to pass update cache.
		 * @return array|false Array if an update is available. False otherwise.
		 */
		public function get_version_info( $slug, $update_cache = false ) {
			if ( ! $update_cache ) {
				$update_cache = get_site_transient( 'update_plugins' );
			}			

			if ( ! is_object( $update_cache ) || empty( $update_cache->response ) || ! array_key_exists( $slug, $update_cache->response ) ) {
				return false;
			}

			return $update_cache->response[ $slug ];
		}

		/**
		 * Display an error message when users attempt to update a plugin
		 * without a license or with an expired license.
		 *
		 * @since  1.4.20
		 * 
		 * @param  false|WP_Error  $reply    The messaage to return. Set to false by default.
		 * @param  string          $package  Package URL. For Charitable extensions without a
		 *                                   license or that have expired, we set this to a 
		 *                                   key. Kind of a hack to pass a message to this hook.
		 * @param  Plugin_Upgrader $upgrader The Plugin_Upgrader object.
		 * @return false|WP_Error
		 */
		public function set_upgrader_error_message( $reply, $package, $upgrader ) {
			$ajax_skin = 'WP_Ajax_Upgrader_Skin' == get_class( $upgrader->skin );

			if ( 'missing_license' == $package ) {
				if ( $ajax_skin ) {
					$message = sprintf( __( 'You have not activated your license key. Activate your license to update: %s', 'charitable' ),
						admin_url( 'admin.php?page=charitable-settings&tab=licenses' )
					);
				} else {
					$message = sprintf( __( 'You have not activated your license key. <a href="%s" target="_top">Activate your license to update.</a>', 'charitable' ),
						admin_url( 'admin.php?page=charitable-settings&tab=licenses' )
					);
				}

				return new WP_Error( 'missing_license_key', $message );
			}

			if ( false !== strpos( $package, 'expired_license:' ) ) {
				
				$renewal_link = str_replace( 'expired_license:', '', $package );

				if ( $ajax_skin ) {
					$message = sprintf( __( 'Your license has expired. Renew your license key: %s', 'charitable' ),
						$renewal_link
					);
				} else {
					$message = sprintf( __( 'Your license has expired. <a href="%s" target="_blank">Renew your license key.</a>', 'charitable' ),
						esc_url( $renewal_link )
					);
				}

				return new WP_Error( 'expired_license_key', $message );
			}

			return $reply;
		}

		/**
		 * Set upgrader package options.
		 *
		 * This is used for expired licenses and in certain cases for products that do
		 * not have a license activated.
		 *
		 * @since  1.4.20
		 *
		 * @param  array $options Upgrader package options.
		 * @return array
		 */ 
		public function set_upgrader_package_options( $options ) {
			if ( 'expired_license' != $options['package'] && empty( $options['package'] ) ) {
				return $options;
			}

			if ( ! array_key_exists( 'hook_extra', $options ) || ! array_key_exists( 'plugin', $options['hook_extra'] ) ) {
				return $options;
			}

			list( $plugin_key, ) = explode( '/', str_replace( '-', '_', $options['hook_extra']['plugin'] ) );

			if ( ! array_key_exists( $plugin_key, $this->products ) ) {
				return $options;
			}

			/* Set up the renewal link for expired licenses. */
			if ( 'expired_license' == $options['package'] ) {
				$options['package'] = $this->get_expired_license_package( $options['hook_extra']['plugin'] );					

				return $options;						
			}

			$license_details = $this->get_license_details( $plugin_key );				

			if ( ! is_array( $license_details ) || ! array_key_exists( 'license', $license_details ) || empty( $license_details['license'] ) ) {
				$options['package'] = 'missing_license';
			}

			return $options;
		}

		/**
		 * Return the package string for an expired license.
		 *
		 * @since  1.4.20
		 *
		 * @param  string $plugin Plugin basename.
		 * @return string
		 */
		public function get_expired_license_package( $plugin ) {
			$version_info     = $this->get_version_info( $plugin );
			$base_renewal_url = isset( $version_info->renewal_link ) ? $version_info->renewal_link : 'https://www.wpcharitable.com/account';		

			return sprintf( 'expired_license:%s', add_query_arg( array(
				'utm_source'   => 'plugin-upgrades', 
				'utm_medium'   => 'wordpress-dashboard',
				'utm_campaign' => 'expired-license',
			), $base_renewal_url ) );
		}

		/**
		 * Register a product that requires licensing.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item_name The title of the product.
		 * @param  string $author    The author of the product.
		 * @param  string $version   The current product version we have installed.
		 * @param  string $file      The path to the plugin file.
		 * @param  string $url       The base URL where the plugin is licensed. Defaults to Charitable_Licenses::UPDATE_URL.
		 * @return void
		 */
		public function register_licensed_product( $item_name, $author, $version, $file, $url = false ) {
			if ( ! $url ) {
				$url = Charitable_Licenses::UPDATE_URL;
			}

			$product_key = $this->get_item_key( $item_name );

			$this->products[ $product_key ] = array(
				'name'    => $item_name,
				'author'  => $author,
				'version' => $version,
				'url'     => $url,
				'file'    => $file,
			);

			$licenses = $this->get_licenses();
			$license = isset( $licenses[ $product_key ]['license'] ) ? trim( $licenses[ $product_key ]['license'] ) : '';

			new Charitable_Plugin_Updater( $url, $file, array(
				'version'   => $version,
				'license'   => $license,
				'item_name' => $item_name,
				'author'    => $author,
			) );
		}

		/**
		 * Return the list of products requiring licensing.
		 *
		 * @since  1.0.0
		 *
		 * @return array[]
		 */
		public function get_products() {
			return $this->products;
		}

		/**
		 * Return a specific product's details.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item The item for which we are getting product details.
		 * @return string[]
		 */
		public function get_product_license_details( $item ) {
			return isset( $this->products[ $item ] ) ? $this->products[ $item ] : false;
		}

		/**
		 * Returns whether the given product has a valid license.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item The item to check.
		 * @return boolean
		 */
		public function has_valid_license( $item ) {
			$license = $this->get_license_details( $item );

			if ( ! $license || ! isset( $license['valid'] ) ) {
				return false;
			}

			return $license['valid'];
		}

		/**
		 * Returns the license details for the given product.
		 *
		 * @since   1.0.0
		 *
		 * @param  string $item The item to get the license for.
		 * @return mixed[]
		 */
		public function get_license( $item ) {
			$license = $this->get_license_details( $item );

			if ( ! $license || ! is_array( $license )  ) {
				return false;
			}

			return $license['license'];
		}

		/**
		 * Returns the active license details for the given product.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item The item to get active licensing details for.
		 * @return mixed[]
		 */
		public function get_license_details( $item ) {
			$licenses = $this->get_licenses();

			if ( ! isset( $licenses[ $item ] ) ) {
				return false;
			}

			return $licenses[ $item ];
		}

		/**
		 * Return the list of licenses.
		 *
		 * Note: The licenses are not necessarily valid. If a user enters an invalid
		 * license, the license will be stored but it will be flagged as invalid.
		 *
		 * @since  1.0.0
		 *
		 * @return array[]
		 */
		public function get_licenses() {
			if ( ! isset( $this->licenses ) ) {
				$this->licenses = charitable_get_option( 'licenses', array() );
			}

			return $this->licenses;
		}

		/**
		 * Verify a license.
		 *
		 * @since  1.0.0
		 *
		 * @param  string  $item    The item to verify.
		 * @param  string  $license The license key for the item.
		 * @param  boolean $force   Whether to force the verification check.
		 * @return mixed[]
		 */
		public function verify_license( $item, $license, $force = false ) {
			$license = trim( $license );

			if ( $license === $this->get_license( $item ) && ! $force ) {
				return $this->get_license_details( $item );
			}

			$product_details = $this->get_product_license_details( $item );

			/* This product was not correctly registered. */
			if ( ! $product_details ) {
				return;
			}

			/* Data to send in our API request */
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $product_details['name'] ),
				'url'        => home_url(),
			);

			/* Call the custom API */
			$response = wp_remote_post( $product_details['url'], array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			) );

			/* Make sure the response came back okay */
			if ( is_wp_error( $response ) ) {
				return;
			}

			$this->flush_update_cache();

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			return array(
				'license'         => $license,
				'expiration_date' => $license_data->expires,
				'valid'           => ( 'valid' === $license_data->license ),
			);
		}

		/**
		 * Return the URL to deactivate a specific license.
		 *
		 * @since   1.0.0
		 *
		 * @param  string $item The item to deactivate.
		 * @return string
		 */
		public function get_license_deactivation_url( $item ) {
			return esc_url( add_query_arg( array(
				'charitable_action' => 'deactivate_license',
				'product_key'       => $item,
				'_nonce'            => wp_create_nonce( 'license' ),
			), admin_url( 'admin.php?page=charitable-settings&tab=licenses' ) ) );
		}

		/**
		 * Deactivate a license.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function deactivate_license() {
			if ( ! wp_verify_nonce( $_REQUEST['_nonce'], 'license' ) ) {
				wp_die( esc_attr__( 'Cheatin\' eh?!', 'charitable' ) );
			}

			$product_key = isset( $_REQUEST['product_key'] ) ? $_REQUEST['product_key'] : false;

			/* Product key must be set */
			if ( false === $product_key ) {
				wp_die( esc_attr__( 'Missing product key', 'charitable' ) );
			}

			$product = $this->get_product_license_details( $product_key );

			/* Make sure we have a valid product with a valid license. */
			if ( ! $product || ! $this->has_valid_license( $product_key ) ) {
				wp_die( esc_attr__( 'This product is not valid or does not have a valid license key.', 'charitable' ) );
			}

			$license = $this->get_license( $product_key );

			/* Data to send to wpcharitable.com to deactivate the license. */
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $product['name'] ),
				'url'        => home_url(),
			);

			/* Call the custom API. */
			$response = wp_remote_post( $product['url'], array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			/* Make sure the response came back okay */
			if ( is_wp_error( $response ) ) {
				return;
			}
			
			$this->flush_update_cache();

			/* Decode the license data */
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			$settings = get_option( 'charitable_settings' );

			unset( $settings['licenses'][ $product_key ] );

			update_option( 'charitable_settings', $settings );
		}

		/**
		 * Flush the version update cache.
		 *
		 * @since  1.4.20
		 *
		 * @return void
		 */
		protected function flush_update_cache() {
			wp_cache_delete( 'plugin_versions', 'charitable' );
			set_site_transient( 'update_plugins', null );
		}

		/**
		 * Return a key for the item, based on the item name.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $item_name Name of the item.
		 * @return string
		 */
		protected function get_item_key( $item_name ) {
			return strtolower( str_replace( ' ', '_', $item_name ) );
		}

		/**
		 * Return the latest versions of Charitable plugins.
		 *
		 * @since  1.4.0
		 *
		 * @return array
		 */
		protected function get_versions() {
			$versions = wp_cache_get( 'plugin_versions', 'charitable' );

			if ( false === $versions ) {

				$licenses = array();

				foreach ( $this->get_licenses() as $license ) {
					if ( isset( $license['license'] ) ) {
						$licenses[] = $license['license'];
					}
				}

				$response = wp_remote_post(
					Charitable_Licenses::UPDATE_URL . '/edd-api/versions-v2/',
					array(
						'sslverify' => false,
						'timeout'   => 15,
						'body'      => array(
							'licenses' => $licenses,
							'url'      => home_url(),
						),
					)
				);

				$versions = wp_remote_retrieve_body( $response );

				$versions = json_decode( $versions, true );

				wp_cache_set( 'plugin_versions', $versions, 'charitable' );
			}//end if

			return $versions;
		}
	}

endif;
