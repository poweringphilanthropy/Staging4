<?php
/**
 * Class responsible for adding Charitable EDD settings in admin area.
 *
 * @package		Charitable EDD/Classes/Charitable_EDD_Admin
 * @version 	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Admin' ) ) :

	/**
	 * Charitable_EDD_Admin
	 *
	 * @since 		1.0.0
	 */
	class Charitable_EDD_Admin {

		/**
		 * @var     Charitable_EDD_Admin
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
			require_once( 'charitable-edd-core-admin-functions.php' );
			require_once( 'upgrades/class-charitable-edd-upgrade.php' );
			require_once( 'upgrades/charitable-edd-upgrade-hooks.php' );
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
				self::$instance = new Charitable_EDD_Admin();
			}

			return self::$instance;
		}

		/**
		 * Register campaign meta box table.
		 *
		 * @param 	array 		$meta_boxes
		 * @return 	array
		 * @access  public
		 * @since 	1.0.0
		 */
		public function register_campaign_meta_box( $meta_boxes ) {
			$meta_boxes[] = array(
				'id'		=> 'campaign-edd-benefactors',
				'title'		=> __( 'EDD Connect', 'charitable-edd' ),
				'context'	=> 'campaign-advanced',
				'priority'	=> 'high',
				'view'		=> 'metaboxes/campaign-benefactors',
				'extension' => 'charitable-edd',
			);

			return $meta_boxes;
		}

		/**
		 * Add EDD settings fields into the campaign's donation options metabox.
		 *
		 * @global  WP_Post $post
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_edd_metabox_fields( $fields ) {
			global $post;

			$fields['section-edd-separator'] = array(
				'view'              => 'metaboxes/field-types/hr',
				'priority'          => 20,
			);

			$fields['section-edd-heading'] = array(
				'view'              => 'metaboxes/field-types/heading',
				'priority'          => 22,
				'title'             => __( 'Easy Digital Downloads - Contributions from Purchases', 'charitable-edd' ),
			);

			$fields['campaign-edd-benefactors'] = array(
				'label'             => __( 'EDD Connect', 'charitable-edd' ),
				'view'              => 'metaboxes/campaign-benefactors',
				'extension'         => 'charitable-edd',
				'priority'          => 24,
			);

			if ( charitable_get_table( 'edd_benefactors' )->get_campaign_benefactors( $post->ID ) ) {

				$fields['edd-show-contribution-options'] = array(
					'view'              => 'metaboxes/field-types/checkbox',
					'priority'          => 26,
					'label'             => __( 'Show product contributing options in donation form', 'charitable-edd' ),
					'meta_key'          => '_campaign_edd_show_contribution_options',
					'default'           => 0,
				);

				$fields['edd-contribution-options-title'] = array(
					'view'              => 'metaboxes/field-types/text',
					'priority'          => 28,
					'label'             => __( 'Contributions options header', 'charitable-edd' ),
					'meta_key'          => '_campaign_edd_contribution_options_title',
					'default'           => __( 'Other ways to contribute', 'charitable-edd' ),
				);

				$fields['edd-contribution-options-explanation'] = array(
					'view'              => 'metaboxes/field-types/textarea',
					'priority'          => 30,
					'label'             => __( 'Contributions options explanation', 'charitable-edd' ),
					'meta_key'          => '_campaign_edd_contribution_options_explanation',
					'default'           => __( 'When you purchase one of these products, a contribution will be made to this fundraising campaign.', 'charitable-edd' ),
				);

			}

			return $fields;
		}

		/**
		 * Add EDD specific settings to capaign benefactors metabox.
		 *
		 * @param 	Charitable_Benefactor $benefactor
		 * @param 	string $extension
		 * @param   int $index
		 * @return 	void
		 * @access  public
		 * @since 	1.0.0
		 */
		public function benefactor_form_fields( $benefactor, $extension, $index ) {
			if ( 'charitable-edd' != $extension ) {
				return;
			}

			charitable_edd_admin_view( 'metaboxes/campaign-benefactor-settings', array( 'benefactor' => $benefactor, 'index' => $index ) );
		}

		/**
		 * Sanitize benefactor data.
		 *
		 * @param 	array 			$data
		 * @return 	array
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function sanitize_benefactor_data( $data ) {
			if ( isset( $data['edd'] ) ) {

				if ( 'global' == $data['edd'] ) {
					$data['benefactor'] = array(
						'edd_is_global_contribution' 	=> 1,
						'edd_download_id'				=> 0,
						'edd_download_category_id'		=> 0,
					);
				} elseif ( false !== $data['edd'] ) {
					list( $type, $id ) = explode( '-', $data['edd'] );

					if ( 'download' == $type ) {
						$data['benefactor'] = array(
							'edd_is_global_contribution' => 0,
							'edd_download_id'			 => $id,
							'edd_download_category_id'	 => 0,
						);
					} elseif ( 'category' == $type ) {
						$data['benefactor'] = array(
							'edd_is_global_contribution' => 0,
							'edd_download_id'			 => 0,
							'edd_download_category_id'	 => $id,
						);
					}
				}

				unset( $data['edd'] );

			}

			return $data;
		}

		/**
		 * Add additional meta fields to be saved when updating a campaign.
		 *
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_meta_keys( $keys ) {
			array_push( $keys,
				'_campaign_edd_show_contribution_options',
				'_campaign_edd_contribution_options_title',
				'_campaign_edd_contribution_options_explanation'
			);

			return $keys;
		}

		/**
		 * Add settings to the General tab in Charitable settings area.
		 *
		 * @param   array[] $fields
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_extension_settings( $fields ) {
			if ( ! charitable_is_settings_view( 'extensions' ) ) {
				return $fields;
			}

			$extra_fields = array(
				'section_edd' => array(
					'title'    => __( 'Easy Digital Downloads', 'charitable-edd' ),
					'type'     => 'heading',
					'priority' => 60,
				),
				'show_product_contribution_note' => array(
					'title'    => __( 'Show Contribution Amount on Downloads', 'charitable-edd' ),
					'type'     => 'checkbox',
					'priority' => 62,
					'help'     => __( 'Show how much each download contributes to fundraising campaigns.', 'charitable-edd' ),
				),
				'enable_edd_sync_tool' => array(
					'title'    => __( 'Enable Sync Tool on Donation Detail Pages', 'charitable-edd' ),
					'type'     => 'checkbox',
					'priority' => 64,
					'help'     => __( 'Enabling this will allow you to re-sync individual donations with the payment they are linked to.', 'charitable-edd' ),
				),
			);

			$fields = array_merge( $fields, $extra_fields );

			return $fields;
		}

		/**
		 * Add settings to the Payment Gateways tab in Charitable settings area.
		 *
		 * @param   array[] $fields
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_payment_gateway_settings( $fields ) {
			if ( ! charitable_is_settings_view( 'gateways' ) ) {
				return $fields;
			}

			unset( $fields['section_gateways'], $fields['gateways'], $fields['test_mode'] );

			$fields['edd_gateway_notice'] = array(
				'type'          => 'content',
				'content'       => sprintf( esc_html__( 'You\'re currently using Easy Digital Downloads Connect. All donations are made through the Easy Digital Downloads checkout. You can manage your payment gateways in the %1$sEasy Digital Downloads settings area%2$s.', 'charitable-edd' ),
					'<a href="' . admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways' ) . '">',
					'</a>'
				),
				'priority'      => 2,
			);

			return $fields;
		}

		/**
		 * Customize the donation view.
		 *
		 * @since  1.1.2
		 *
		 * @param  Charitable_Donor    $donor    The donor object.
		 * @param  Charitable_Donation $donation The donation object.
		 * @return boolean True if custom view loaded. False otherwise.
		 */
		public function load_custom_donation_view_class( $donor, $donation ) {
			if ( 'EDD' != get_post_meta( $donation->ID, 'donation_gateway', true ) ) {
				return false;
			}

			require_once( 'donations/class-charitable-edd-donation-view.php' );

			new Charitable_EDD_Donation_View( $donation->ID );
		}

		/**
		 * Register the donation resync action.
		 *
		 * @since  1.1.2
		 *
		 * @return void
		 */
		public function register_resync_donation_action() {
			if ( ! charitable_get_option( 'enable_edd_sync_tool', false ) ) {
				return;
			}

			charitable_get_donation_actions()->register( 'resync_donation_from_edd_payment', array(
				'label'           => __( 'Sync Donation with Payment', 'charitable-edd' ),
				'callback'        => array( $this, 'resync_donation_from_edd_payment' ),
				'button_text'     => __( 'Re-sync', 'charitable-edd' ),
				'active_callback' => array( 'Charitable_EDD_Payment', 'get_payment_for_donation' ),
				'success_message' => '20',
			), __( 'Easy Digital Downloads', 'charitable' ) );
		}
		
		/**
		 * Register the Resync post message.
		 *
		 * @since  1.1.2
		 *
		 * @param  array $messages All current registered messages.
		 * @return array
		 */
		public function register_resync_post_message( $messages ) {
			$messages[ Charitable::DONATION_POST_TYPE ][20] = __( 'Donation synced with payment.', 'charitable-edd' );

			return $messages;
		}
		

		/**
		 * Resync a donation with its linked EDD payment.
		 *
		 * @since  1.1.0
		 * @since  1.1.2 Added $success and $donation_id parameters.
		 *
		 * @return boolean
		 */
		public function resync_donation_from_edd_payment( $success = false, $donation_id = '' ) {
			$redirect = false;
			
			/* Backwards-compatibility for Charitable < 1.5. */
			if ( empty( $donation_id ) ) {

				if ( ! array_key_exists( 'post', $_GET ) ) {
					return;
				}
				
				$redirect    = true;
				$donation_id = $_GET['post'];
			} 

			$success = Charitable_EDD_Payment::get_instance()->resync_donation_from_payment( $donation_id );
			
			if ( $redirect && $success ) {

				charitable_get_admin_notices()->add_success( __( 'Donation synced with payment.', 'charitable-edd' ), false, true );
				
				$redirect = add_query_arg( array(
		            'post'   => $donation_id,
		            'action' => 'edit',
		        ),
		        admin_url( 'post.php' ) );
		        
		        wp_safe_redirect( $redirect );
		        
		        exit();

			} elseif ( ! $success ) {

				charitable_get_admin_notices()->add_error( sprintf( __( 'Donation #%d was deleted after syncing. All campaign donations were removed.', 'charitable-edd' ), $donation_id ), false, true );
				
				$redirect = add_query_arg( array(
		            'post_type' => CHARITABLE::DONATION_POST_TYPE,
		        ),
		        admin_url( 'edit.php' ) );
		        
		        wp_safe_redirect( $redirect );

				exit();

			}

			return $success;
		}
		
		/**
		 * Add an option to resync donations to the bulk actions select on the donations table.
		 *
		 * @param	string[] $actions
		 * @return	string[]
		 * @since	1.1.0
		 */
		public function add_resync_bulk_action( $actions ) {

			if ( ! charitable_get_option( 'enable_edd_sync_tool', false ) ) {
				return $actions;
			}
			
			$actions['sync-donations'] = __( 'Re-sync Donations with EDD Payments', 'charitable-edd' );
			
			return $actions;
		
		}
		
		/**
		 * Handle the resync bulk action.
		 *
		 * @param   int    $redirect_to
		 * @param   string $action
		 * @param   int[]  $post_ids
		 * @return  string
		 * @since   1.1.0
		 */
		public function handle_resync_bulk_action( $redirect_to, $action, $post_ids ) {

			if ( 'sync-donations' != $action ) {
				return $redirect_to;
			}

			$synced  = 0;
			$deleted = 0;

			foreach ( $post_ids as $donation_id ) {
				
				if ( 'EDD' != strtoupper( get_post_meta( $donation_id, 'donation_gateway', true ) ) ) {
					continue;
				}
				
				$updated = Charitable_EDD_Payment::get_instance()->resync_donation_from_payment( $donation_id );
				
				if ( $updated ) {
					$synced  += 1;	
				} else {
					$deleted += 1;
				}
				
			}
			
			if ( $synced && $deleted ) {
				$message = sprintf( __( '%d donations synced and %d deleted', 'charitable-donations' ), $synced, $deleted );
			} elseif ( $synced ) {
				$message = sprintf( __( '%d donations synced.', 'charitable-donations' ), $synced );
			} else {
				$message = sprintf( __( '%d donations deleted.', 'charitable-donations' ), $deleted );
			}

			charitable_get_admin_notices()->add_success( $message, false, true );

			return add_query_arg( $action, $synced, $redirect_to );

		}

		/**
		 * Add version update notices to the dashboard.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.1.0
		 */
		public function add_version_update_notices() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$notices = array();

			$notices['edd-release-110'] = sprintf( __( "Version 1.1.0 of Charitable Easy Digital Downloads Connect fixes an important bug and improves how donations are recorded. <a href='%s'>Read more</a>.", 'charitable-edd' ),
				'https://www.wpcharitable.com/how-we-improved-our-easy-digital-downloads-integration/?utm_source=notice&utm_medium=wordpress-dashboard&utm_campaign=release-notes&utm_content=edd-release-110'
			);

			$helper = charitable_get_admin_notices();

			foreach ( $notices as $notice => $message ) {

				if ( ! get_transient( 'charitable_' . $notice . '_notice' ) ) {
					continue;
				}

				$helper->add_version_update( $message, $notice );

			}

		}

		/**
		 * Display the export box in the EDD exports tab.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function export_box() {
			charitable_edd_admin_view( 'export-box' );
		}

		/**
		 * Registers the batch exporter.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function register_exporter() {
			add_action( 'edd_batch_export_class_include', array( self::$instance, 'include_exporter' ), 10, 1 );
		}

		/**
		 * Loads the customers batch process if needed
		 *
		 * @param   string $class   The class being requested to run for the batch export
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function include_exporter( $class ) {
			if ( 'Charitable_EDD_Batch_Export_Campaign_Payments' === $class ) {
				require_once 'class-charitable-edd-batch-export-campaign-payments.php';
			}
		}

		/**
		 * Add the Re-sync Donation meta box to the donation management page.
		 *
		 * @deprecated 1.3.0
		 *
		 * @since  1.1.0
		 * @since  1.1.2 Deprecated. Sync tool has been added to the Donation
		 *               Actions meta box in Charitable 1.5+.
		 *
		 * @param  array $meta_boxes Registered meta boxes.
		 * @return array
		 */
		public function add_resync_donation_meta_box( $meta_boxes ) {

			if ( ! charitable_get_option( 'enable_edd_sync_tool', false ) ) {
				return $meta_boxes;
			}

			$meta_boxes['resync-donation'] = array(
				'title'    => __( 'Re-sync Donation', 'charitable-edd' ),
				'context'  => 'side',
				'priority' => 'low',
				'view'     => 'metaboxes/donation/resync-donation',
			);

			return $meta_boxes;
		}

		/**
		 * Override the default donation display in the admin.
		 *
		 * @deprecated 1.3.0
		 *
		 * @since  1.1.0
		 * @since  1.1.2 Deprecated. Neither view is required as
		 *               of Charitable 1.5.
		 *
		 * @param  string $path
		 * @param  string $view
		 * @return string
		 */
		public function load_custom_donation_overview_view( $path, $view ) {
			$custom_views = array(
				'metaboxes/donation/donation-overview',
				'metaboxes/donation/resync-donation',
			);

			if ( ! in_array( $view, $custom_views ) ) {
				return $path;
			}

			global $post;

			/* Only use the donation overview view if EDD is the gateway and Charitable is less than 1.5.0 */
			if ( 'metaboxes/donation/donation-overview' == $view && ( 'EDD' != get_post_meta( $post->ID, 'donation_gateway', true ) ) ) {
				return $path;
			}

			return charitable_edd()->get_path( 'admin' ) . 'views/' . $view . '.php';

		}
	}

endif; // End class_exists check
