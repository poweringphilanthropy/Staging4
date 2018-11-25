<?php
/**
 * Charitable EDD Benefactors DB class.
 *
 * @package     Charitable EDD/Classes/Charitable_EDD_Benefactors_DB
 * @version    	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
*/

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_EDD_Benefactors_DB' ) ) :

	/**
	 * Charitable_EDD_Benefactors_DB
	 *
	 * @since 		1.0.0
	 */
	class Charitable_EDD_Benefactors_DB extends Charitable_DB {

		/**
		 * The version of our database table
		 *
		 * @access  public
		 * @since   1.0.0
		 */
		public $version = '1.0.0';

		/**
		 * The name of the primary column
		 *
		 * @access  public
		 * @since   1.0.0
		 */
		public $primary_key = 'campaign_benefactor_id';

		/**
		 * Set up the database table name.
		 *
		 * @return 	void
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function __construct() {
			global $wpdb;

			$this->table_name = $wpdb->prefix . 'charitable_edd_benefactors';
		}

		/**
		 * Create the table.
		 *
		 * @global 	$wpdb
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function create_table() {
			global $wpdb;

	        $charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
					`campaign_benefactor_id` bigint(20) NOT NULL AUTO_INCREMENT,
					`edd_download_id` bigint(20) DEFAULT NULL,
					`edd_download_category_id` bigint(20) DEFAULT NULL,
					`edd_is_global_contribution` tinyint(1) NOT NULL DEFAULT 0,
					PRIMARY KEY (`campaign_benefactor_id`),
					KEY `download` (`edd_download_id`), 
					KEY `download_category` (`edd_download_category_id`), 
					KEY `global_contribution` (`edd_is_global_contribution`)
					) $charset_collate;";

			$this->_create_table( $sql );
		}

		/**
		 * Return an instance of the parent table's helper class.
		 *
		 * @return 	Charitable_Benefactors_DB
		 * @access  public
		 * @since 	1.0.0
		 */
		public function get_parent_table() {
			global $wpdb;

			return $wpdb->prefix . 'charitable_benefactors';
		}

		/**
		 * Whitelist of columns.
		 *
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_columns() {
			return array(
				'campaign_benefactor_id'		=> '%d',
				'edd_download_id'				=> '%d',
				'edd_download_category_id'		=> '%d',
				'edd_is_global_contribution'	=> '%d',
			);
		}

		/**
		 * Default column values.
		 *
		 * @return 	array
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_column_defaults() {
			return array(
				'edd_is_global_contribution'	=> 0,
				'edd_download_id'				=> 0,
				'edd_download_category_id'		=> 0,
			);
		}

		/**
		 * Add EDD benefactor relationships. Called after parent benefactor record is created.
		 *
		 * @param 	int 		$campaign_benefactor_id
		 * @param 	array 		$benefactor_details
		 * @param 	array 		$data
		 * @return 	void
		 * @access  public
		 * @static
		 * @since 	1.0.0
		 */
		public static function charitable_benefactor_added( $campaign_benefactor_id, $benefactor_details, $data ) {
			if ( 'charitable_benefactor_added' != current_filter() ) {
				_doing_it_wrong( __METHOD__, 'This method should only be called on the `charitable_benefactor_added` hook.', '1.0.0' );
				return;
			}

			$benefactor_details['campaign_benefactor_id'] = $campaign_benefactor_id;

			$db = new Charitable_EDD_Benefactors_DB();
			$db->insert( $benefactor_details );
		}

		/**
		 * Update a benefactor relationship.
		 *
		 * @param 	int 		$row_id
		 * @param 	array 		$data
		 * @param 	string 		$where 			Column used in where argument.
		 * @return 	void
		 * @access  public
		 * @static
		 * @since 	1.0.0
		 */
		public static function charitable_benefactor_updated( $row_id, $data = array(), $where = '' ) {
			if ( 'charitable_benefactor_updated' != current_filter() ) {
				_doing_it_wrong( __METHOD__, 'This method should only be called on the `charitable_benefactor_updated` hook.', '1.0.0' );
				return;
			}

			$db = new Charitable_EDD_Benefactors_DB();
			$db->update( $row_id, $data, $where );
		}

		/**
		 * Delete a benefactor relationship.
		 *
		 * @param 	int 		$row_id
		 * @return 	boolean
		 * @access  public
		 * @static
		 * @since 	1.0.0
		 */
		public static function charitable_benefactor_deleted( $row_id ) {
			if ( 'charitable_benefactor_deleted' != current_filter() ) {
				_doing_it_wrong( __METHOD__, 'This method should only be called on the `charitable_benefactor_deleted` hook.', '1.0.0' );
				return;
			}

			$db = new Charitable_EDD_Benefactors_DB();
			$db->delete( $row_id );
		}

		/**
		 * Add a new benefactor object.
		 *
		 * @param 	array 	$data
		 * @return 	int
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function insert( $data, $type = 'campaign_edd_benefactor' ) {
			return parent::insert( $data, $type );
		}

		/**
		 * Return a record containing the values of the core benefactor table as well as the EDD table.
		 *
		 * @param 	int 	$benefactor_id
		 * @return  object|null     Returns the object if successful. Null otherwise.
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_composite_benefactor_object( $benefactor_id ) {
			global $wpdb;

			$sql = "SELECT edd.campaign_benefactor_id, 
						edd.edd_is_global_contribution, 
						edd.edd_download_id, 
						edd.edd_download_category_id, 
						ch.campaign_id, 
						ch.contribution_amount, 
						ch.contribution_amount_is_percentage, 
						ch.contribution_amount_is_per_item
					FROM $this->table_name edd
					INNER JOIN {$this->get_parent_table()} ch
					ON ch.campaign_benefactor_id = edd.campaign_benefactor_id
					WHERE ch.campaign_benefactor_id = %d;";

			return $wpdb->get_row( $wpdb->prepare( $sql, intval( $benefactor_id ) ) );
		}

		/**
		 * Count the number of benefactor relationships between a given campaign and download.
		 *
		 * @global 	WPDB 	$wpdb
		 * @param 	int 	$campaign_id
		 * @param 	int 	$download_id
		 * @return 	int
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function count_campaign_download_benefactors( $campaign_id, $download_id ) {
			global $wpdb;

			$sql = "SELECT COUNT(*)
					FROM $this->table_name edd
					INNER JOIN {$this->get_parent_table()} ch
					ON ch.campaign_benefactor_id = edd.campaign_benefactor_id
					WHERE 
					(
						edd.edd_download_id = %d
						AND ch.campaign_id = %d
						AND ch.date_created <= UTC_TIMESTAMP()
						AND ( ch.date_deactivated = '0000-00-00 00:00:00' OR ch.date_deactivated > UTC_TIMESTAMP() )
					);";

			return $wpdb->get_var( $wpdb->prepare( $sql, $download_id, $campaign_id ) );
		}

		/**
		 * Get all active benefactors for one or more products.
		 *
		 * @global 	WPDB 	         $wpdb
		 * @param 	array 	         $downloads
		 * @param 	boolean|DateTime $at_date Get benefactors at specified date. May be a string or a boolean.
		 * 									  If true (default), this will get any benefactors that are currently active.
		 * 									  If false, this will get any benefactors.
		 * 									  If a DateTime object, this will get any benefactors active at the date.
		 * @return 	Object
		 * @access  public
		 * @since 	1.0.0
		 */
		public function get_benefactors_for_downloads( $downloads = array(), $at_date = true ) {
			global $wpdb;

			if ( empty( $downloads ) ) {
				return array();
			}

			$download_ids = array();
			$download_category_ids = array();

			foreach ( $downloads as $download ) {
				$download_ids[] = $download['id'];
				$download_category_ids = array_merge( $download_category_ids, $download['category_ids'] );
			}

			$download_ids = implode( ',', $download_ids );
			$download_category_ids = implode( ',', array_unique( $download_category_ids ) );

			$or_in_downloads = "OR edd.edd_download_id IN ( $download_ids )";
			$or_in_download_categories = strlen( $download_category_ids ) ? "OR edd.edd_download_category_id IN ( $download_category_ids )"	: '';

			if ( false == $at_date ) {

				$date_clause = '';

			} else {

				$date = is_a( $at_date, 'DateTime' ) ? '"' . $at_date->format( 'Y-m-d H:i:s' ) . '"' : 'UTC_TIMESTAMP()';

				$date_clause = sprintf( 'AND (
					ch.date_created <= %1$s
					AND ( ch.date_deactivated = "0000-00-00 00:00:00" OR ch.date_deactivated > %1$s )
					AND p.post_status = "publish"
				)', $date );

			}

			$sql = "SELECT edd.campaign_benefactor_id, 
						edd.edd_is_global_contribution, 
						edd.edd_download_id, 
						edd.edd_download_category_id, 
						ch.campaign_id, 
						ch.contribution_amount, 
						ch.contribution_amount_is_percentage, 
						ch.contribution_amount_is_per_item
					FROM $this->table_name edd
					INNER JOIN {$this->get_parent_table()} ch
					ON ch.campaign_benefactor_id = edd.campaign_benefactor_id
					INNER JOIN $wpdb->posts p
					ON p.ID = ch.campaign_id
					WHERE 
					(
						edd.edd_is_global_contribution
						$or_in_downloads
						$or_in_download_categories				
					)
					$date_clause;";

			return $wpdb->get_results( $sql, OBJECT_K );
		}

		/**
		 * Get all active benefactors for a single download.
		 *
		 * @global 	WPDB $wpdb
		 * @param 	int $download_id
		 * @return 	Object
		 * @access  public
		 * @since 	1.0.0
		 */
		public function get_benefactors_for_download( $download_id ) {
			global $wpdb;

			$download_category_ids = wp_get_object_terms( $download_id, 'download_category', array( 'fields' => 'ids' ) );
			$download_category_ids = implode( ',', $download_category_ids );

			$or_in_downloads = "OR edd.edd_download_id = $download_id";
			$or_in_download_categories = strlen( $download_category_ids ) ? "OR edd.edd_download_category_id IN ( $download_category_ids )"	: '';

			$sql = "SELECT edd.campaign_benefactor_id, 
						edd.edd_is_global_contribution, 
						edd.edd_download_id, 
						edd.edd_download_category_id, 
						ch.campaign_id, 
						ch.contribution_amount, 
						ch.contribution_amount_is_percentage, 
						ch.contribution_amount_is_per_item
					FROM $this->table_name edd
					INNER JOIN {$this->get_parent_table()} ch
					ON ch.campaign_benefactor_id = edd.campaign_benefactor_id
					INNER JOIN $wpdb->posts p 
					ON ch.campaign_id = p.ID
					WHERE 
					(
						edd.edd_is_global_contribution
						$or_in_downloads
						$or_in_download_categories
					)
					AND (
						ch.date_created <= UTC_TIMESTAMP()
						AND ( ch.date_deactivated = '0000-00-00 00:00:00' OR ch.date_deactivated > UTC_TIMESTAMP() )
						AND p.post_status = 'publish'
					);";

			return $wpdb->get_results( $sql, OBJECT_K );
		}

		/**
		 * Return campaign benefactors.
		 *
		 * @global 	WPDB 	$wpdb
		 * @param 	int 	$campaign_id
		 * @param 	boolean $exclude_expired
		 * @return 	Object
		 * @access  public
		 * @since 	1.0.0
		 */
		public function get_campaign_benefactors( $campaign_id, $exclude_expired = true ) {
			global $wpdb;

			if ( $exclude_expired ) {
				$exclude_expired_clause = "AND (
					ch.date_created <= UTC_TIMESTAMP()
					AND ( ch.date_deactivated = '0000-00-00 00:00:00' OR ch.date_deactivated > UTC_TIMESTAMP() )					
				)";
			} else {
				$exclude_expired_clause = '';
			}

			$sql = "SELECT 
						edd.campaign_benefactor_id, 
						edd.edd_is_global_contribution, 
						edd.edd_download_id, 
						edd.edd_download_category_id, 
						ch.campaign_id, 
						ch.contribution_amount, 
						ch.contribution_amount_is_percentage, 
						ch.contribution_amount_is_per_item, 
						ch.date_created, 
						ch.date_deactivated,
						( SELECT ch.date_deactivated = '0000-00-00 00:00:00' OR ch.date_deactivated > UTC_TIMESTAMP() ) as is_active
					FROM $this->table_name edd
					INNER JOIN {$this->get_parent_table()} ch
					ON edd.campaign_benefactor_id = ch.campaign_benefactor_id
					LEFT JOIN $wpdb->posts p 
					ON edd.edd_download_id = p.ID
					WHERE ch.campaign_id = %d
					AND ( edd.edd_download_id = 0 
						OR ( edd.edd_download_id = p.ID AND p.post_status = 'publish' )
					)
					$exclude_expired_clause
					ORDER BY is_active DESC;";

			return $wpdb->get_results( $wpdb->prepare( $sql, $campaign_id ), OBJECT_K );
		}

		/**
		 * Get the active benefactors for the campaign that are based on one-to-one relationship (one campaign to one download).
		 *
		 * @global 	WPDB 		$wpdb
		 * @param 	int 		$campaign_id
		 * @return  Object
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_single_download_campaign_benefactors( $campaign_id ) {
			global $wpdb;

			$sql = "SELECT * 
					FROM $this->table_name edd
					INNER JOIN {$this->get_parent_table()} ch
					ON edd.campaign_benefactor_id = ch.campaign_benefactor_id
					WHERE ch.campaign_id = %d
					AND edd.edd_download_id > 0
					AND ch.date_created < UTC_TIMESTAMP()
					AND ( ch.date_deactivated = '0000-00-00 00:00:00' OR ch.date_deactivated > UTC_TIMESTAMP() );";

			return $wpdb->get_results( $wpdb->prepare( $sql, $campaign_id ), OBJECT_K );
		}
	}

endif;
