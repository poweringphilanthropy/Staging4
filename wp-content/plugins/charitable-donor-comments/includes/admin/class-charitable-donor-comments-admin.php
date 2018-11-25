<?php
/**
 * The class responsible for adding & saving extra settings in the Charitable admin.
 *
 * @package     Charitable Donor Comments/Classes/Charitable_Donor_Comments_Admin
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Donor_Comments_Admin' ) ) :

	/**
	 * Charitable_Donor_Comments_Admin
	 *
	 * @since       1.0.0
	 */
	class Charitable_Donor_Comments_Admin {

		/**
		 * The single static class instance.
		 *
		 * @since   1.0.0
		 *
		 * @var     Charitable_Donor_Comments_Admin
		 */
		private static $instance = null;

		/**
		 * Boolean flag noting whether comment styles have been added.
		 *
		 * @since 	1.0.0
		 *
		 * @var 	boolean
		 */
		private $comment_styles_added = false;

		/**
		 * Create class object. Private constructor.
		 *
		 * @since   1.0.0
		 */
		private function __construct() {
			require_once( 'upgrades/class-charitable-donor-comments-upgrade.php' );
			require_once( 'upgrades/charitable-donor-comments-upgrade-hooks.php' );
		}

		/**
		 * Create and return the class object.
		 *
		 * @since   1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Donor_Comments_Admin();
			}

			return self::$instance;
		}

		/**
		 * Add custom links to the plugin actions.
		 *
		 * @since   1.0.0
		 *
		 * @param   string[] $links Links to be added to plugin actions row.
		 * @return  string[]
		 */
		public function add_plugin_action_links( $links ) {
			$links[] = '<a href="' . admin_url( 'admin.php?page=charitable-settings&tab=extensions' ) . '">' . __( 'Settings', 'charitable-newsletter-connect' ) . '</a>';
			return $links;
		}

		/**
		 * Add settings to the Extensions settings tab.
		 *
		 * @since   1.0.0
		 *
		 * @param   array[] $fields Settings to display in tab.
		 * @return  array[]
		 */
		public function add_donor_comments_settings( $fields = array() ) {
			if ( ! charitable_is_settings_view( 'extensions' ) ) {
				return $fields;
			}

			$custom_fields = array(
				'section_donor_comments' => array(
					'title'             => __( 'Donor Comments', 'charitable-donor-comments' ),
					'type'              => 'heading',
					'priority'          => 70,
				),
				'show_donor_comments_in_comments_template' => array(
					'title'             => __( 'Show donor comments in campaign comments', 'charitable-donor-comments' ),
					'type'              => 'checkbox',
					'priority'          => 70.2,
					'default'           => true,
					'help'				=> __( 'By enabling this option, the comments your donors leave when they donate will automatically be included in the comments section on your campaign page.', 'charitable-donor-comments' ),
				),
			);

			$fields = array_merge( $fields, $custom_fields );

			return $fields;
		}

		/**
		 * Register admin stylesheet.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function setup_admin_stylesheet() {
			wp_register_style(
				'charitable-donor-comments-admin-styles',
				charitable_donor_comments()->get_path( 'directory', false ) . 'assets/css/charitable-donor-comments-admin.css',
				array( 'charitable-admin' ),
				'1.0.0',
				'all'
			);
		}

		/**
		 * Add the donor's comment to the donation details page.
		 *
		 * @since   1.0.0
		 *
		 * @param 	Charitable_Donor    $donor    Donor object.
		 * @param 	Charitable_Donation $donation Donation object.
		 * @return  boolean Whether a comment was added.
		 */
		public function add_comment_to_admin_donation_page( $donor, $donation ) {
			$comment = Charitable_Donor_Comments_Donation::get_instance()->get_donor_comment( $donation->ID );

			if ( ! $comment ) {
				return false;
			}

			if ( ! wp_style_is( 'charitable-donor-comments-admin-styles', 'enqueued' ) ) {
				wp_enqueue_style( 'charitable-donor-comments-admin-styles' );
			}

			$status = wp_get_comment_status( $comment );

			if ( false == $status ) {
				return false;
			}

			$edit_url = add_query_arg( array(
				'c'      => $comment->comment_ID,
				'action' => 'editcomment',
			), admin_url( 'comment.php' ) );

			$approve_url = ( 'approved' == $status ) ? '' : add_query_arg( array(
				'c'        => $comment->comment_ID,
				'action'   => 'approvecomment',
				'_wpnonce' => wp_create_nonce( 'approve-comment_' . $comment->comment_ID ),
			), admin_url( 'comment.php' ) );

			$output  = '<div class="donor-comment donor-comment-' . esc_attr( $status ) . '">';
			$output .= wpautop( get_comment_text( $comment ) );
			$output .= '<div class="donor-comment-actions">';
			$output .= '<a href="' . esc_url( $edit_url ) . '">' . __( 'Edit', 'charitable-donor-comments' ) . '</a>';
			if ( strlen( $approve_url ) ) {
				$output .= '<a href="' . esc_url( $approve_url ) . '">' . __( 'Approve', 'charitable-donor-comments' ) . '</a>';
			}
			$output .= '</div>';
			$output .= '<div class="donor-comment-status">' . $status . '</div>';
			$output .= '</div>';

			echo $output;

			return true;
		}
	}

endif;
