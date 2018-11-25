<?php
/**
 * The class responsible for adding & saving extra settings in the Charitable admin.
 *
 * @package     Charitable Videos/Classes/Charitable_Videos_Admin
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Videos_Admin' ) ) :

	/**
	 * Charitable_Videos_Admin
	 *
	 * @since       1.0.0
	 */
	class Charitable_Videos_Admin {

		/**
		 * @var     Charitable_Videos_Admin
		 * @access  private
		 * @static
		 * @since   1.0.0
		 */
		private static $instance = null;

		/**
		 * Create class object. Private constructor.
		 *
		 * @access  private
		 * @since   1.0.0
		 */
		private function __construct() {
			require_once( 'upgrades/class-charitable-videos-upgrade.php' );
			require_once( 'upgrades/charitable-videos-upgrade-hooks.php' );
		}

		/**
		 * Create and return the class object.
		 *
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Videos_Admin();
			}

			return self::$instance;
		}

		/**
		 * Register campaign updates section in campaign metabox.
		 *
		 * @param   array[] $meta_boxes
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function register_campaign_video_meta_box( $meta_boxes ) {
			$meta_boxes[] = array(
				'id'          => 'campaign-video',
				'title'       => __( 'Video', 'charitable-videos' ),
				'context'     => 'campaign-advanced',
				'priority'    => 'high',
				'view'        => 'metaboxes/campaign-video',
				'view_source' => 'charitable-videos',
			);

			return $meta_boxes;
		}

		/**
		 * Set the admin view path to our views folder for any of our views.
		 *
		 * @param   string  $path
		 * @param   string  $view
		 * @param   array   $view_args
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function admin_view_path( $path, $view, $view_args ) {
			if ( isset( $view_args['view_source'] ) && 'charitable-videos' == $view_args['view_source'] ) {

				$path = charitable_videos()->get_path( 'includes' ) . 'admin/views/' . $view . '.php';

			}

			return $path;
		}

		/**
		 * Save campaign updates when saving campaign via the admin editor.
		 *
		 * @param   string[] $meta_keys
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_campaign_video( $meta_keys ) {
			$meta_keys[] = '_campaign_video';
			$meta_keys[] = '_campaign_video_id';
			return $meta_keys;
		}

		/**
		 * Register scripts.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function register_scripts() {
			wp_register_script(
				'charitable-videos',
				charitable_videos()->get_path( 'directory', false ) . 'assets/js/charitable-videos.js',
				array( 'jquery-core' ),
				'1.0.0',
				true
			);
		}
	}

endif; // End class_exists check
