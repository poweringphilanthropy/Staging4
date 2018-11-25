<?php
/**
 * Profile endpoint.
 *
 * @package   Charitable/Classes/Charitable_Profile_Endpoint
 * @author    Eric Daams
 * @copyright Copyright (c) 2018, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.5.0
 * @version   1.5.4
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Profile_Endpoint' ) ) :

	/**
	 * Charitable_Profile_Endpoint
	 *
	 * @since  1.5.0
	 */
	class Charitable_Profile_Endpoint extends Charitable_Endpoint {

		/* @var string */
		const ID = 'profile';

		/**
		 * Object instantiation.
		 *
		 * @since 1.5.4
		 */
		public function __construct() {
			$this->cacheable = false;
		}

		/**
		 * Return the endpoint ID.
		 *
		 * @since  1.5.0
		 *
		 * @return string
		 */
		public static function get_endpoint_id() {
			return self::ID;
		}

		/**
		 * Return the endpoint URL.
		 *
		 * @since  1.5.0
		 *
		 * @global WP_Rewrite $wp_rewrite
		 * @param  array $args Mixed args.
		 * @return string
		 */
		public function get_page_url( $args = array() ) {
			$page = charitable_get_option( 'profile_page', false );

			return $page ? get_permalink( $page ) : '';
		}

		/**
		 * Return whether we are currently viewing the endpoint.
		 *
		 * @since  1.5.0
		 *
		 * @global WP_Post $post
		 * @param  array $args Mixed args.
		 * @return boolean
		 */
		public function is_page( $args = array() ) {
			global $post;

			if ( is_null( $post ) ) {
				return false;
			}

			$page = charitable_get_option( 'profile_page', false );

			return false != $page && $page == $post->ID;
		}
	}

endif;
