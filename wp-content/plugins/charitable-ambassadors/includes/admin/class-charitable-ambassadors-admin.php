<?php
/**
 * Class responsible for adding Charitable Ambassadors settings in admin area.
 *
 * @package		Charitable Ambassadors/Classes/Charitable_Ambassadors_Admin
 * @version 	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Admin' ) ) :

	/**
	 * Charitable_Ambassadors_Admin
	 *
	 * @since 		1.0.0
	 */
	class Charitable_Ambassadors_Admin {

		/**
		 * The single instance of this class.
		 *
		 * @var     Charitable_Ambassadors_Admin|null
		 * @access  private
		 * @static
		 */
		private static $instance = null;

		/**
		 * Set up the class.
		 *
		 * Note that the only way to instantiate an object is with the charitable_start method,
		 * which can only be called during the start phase. In other words, don't try
		 * to instantiate this object.
		 *
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function __construct() {
			$this->load_dependencies();
		}

		/**
		 * Returns and/or create the single instance of this class.
		 *
		 * @return  Charitable_Ambassadors_Admin
		 * @access  public
		 * @since   1.1.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Ambassadors_Admin();
			}

			return self::$instance;
		}


		/**
		 * Include admin-only files.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function load_dependencies() {
			$admin_dir = charitable_ambassadors()->get_path( 'admin' );

			require_once( $admin_dir . 'settings/class-charitable-ambassadors-settings.php' );
			require_once( $admin_dir . 'settings/charitable-ambassadors-settings-hooks.php' );
		}

		/**
		 * Add a meta boxes to the campaign page.
		 *
		 * @param 	array[] $meta_boxes
		 * @return 	array[]
		 * @access  public
		 * @since 	1.0.0
		 */
		public function register_meta_boxes( $meta_boxes ) {
			$meta_boxes[] = array(
				'id'				=> 'campaign-funds-recipient',
				'title'				=> __( 'Funds Recipient', 'charitable-ambassadors' ),
				'context'			=> 'campaign-advanced',
				'priority'			=> 'high',
				'view'				=> 'metaboxes/campaign-funds-recipient',
				'view_source'		=> 'charitable-ambassadors',
			);

			$meta_boxes[] = array(
				'id'				=> 'campaign-parent',
				'title'				=> __( 'Campaign Parent', 'charitable-ambassadors' ),
				'context'			=> 'campaign-advanced',
				'priority'			=> 'high',
				'view'				=> 'metaboxes/campaign-parent',
				'view_source'		=> 'charitable-ambassadors',
			);

			return $meta_boxes;
		}

		/**
		 * Set the admin view path to our views folder for any of our views.
		 *
		 * @param 	string $path
		 * @param 	string $view
		 * @param 	array  $view_args
		 * @return 	string
		 * @access  public
		 * @since 	1.0.0
		 */
		public function admin_view_path( $path, $view, $view_args ) {
			if ( ! isset( $view_args['view_source'] ) ) {
				return $path;
			}

			if ( 'charitable-ambassadors' == $view_args['view_source'] ) {
				$path = charitable_ambassadors()->get_path( 'admin' ) . 'views/' . $view . '.php';
			}

			return $path;
		}

		/**
		 * Add custom links to the plugin actions.
		 *
		 * @param 	string[] $links
		 * @return 	string[]
		 * @access  public
		 * @since 	1.0.1
		 */
		public function add_plugin_action_links( $links ) {
			$links[] = '<a href="' . admin_url( 'admin.php?page=charitable-settings&tab=ambassadors' ) . '">' . __( 'Settings', 'charitable-ambassadors' ) . '</a>';
			return $links;
		}
	}

endif; // End class_exists check
