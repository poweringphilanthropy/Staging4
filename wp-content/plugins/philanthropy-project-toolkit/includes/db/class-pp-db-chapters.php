<?php
/**
 * PP_DB_Chapters Class.
 *
 * @class       PP_DB_Chapters
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * EAB DB base class
 * @since       1.0
*/
class PP_DB_Chapters extends PP_DB  {

    /**
     * Get things started
     *
     * @access  public
     * @since   1.0
     */
    public function __construct() {
        global $wpdb;

		$this->table_name  = $wpdb->prefix . 'chapters';
		$this->primary_key = 'id';
		$this->version     = '1.1';

		// use init instead of plugin activation hook, since we are on mu plugins
		add_action( 'init', array($this, 'check_db_version') );
        add_action( 'plugins_loaded', array( $this, 'register_table' ), 11 );
    }

    public function check_db_version(){
        $db_on_option = get_option( $this->table_name . '_db_version', '0.1');
        // version_compare(version1, version2)
        if( version_compare( $db_on_option, $this->version, '<' ) ) {
            $this->create_table();
        }
    }

    /**
     * Get table columns and data types
     *
     * @access  public
     * @since   1.0
    */
    public function get_columns() {
        return array(
            'id'            => '%d',
            'dashboard_id'  => '%d',
            'term_id'       => '%d',
            'name'          => '%s',
        );
    }

    /**
     * Default column values
     *
     * @access  public
     * @since   1.0
     * @return  array
     */
    public function get_column_defaults() {
        return array(
            'id'            => 0,
            'dashboard_id'  => 0,
            'term_id'       => 0,
            'name'          => '',
        );
    }

    /**
     * Register the table with $wpdb so the metadata api can find it
     *
     * @access  public
     * @since   1.0
    */
    public function register_table() {
        global $wpdb;
        $wpdb->chapters = $this->table_name;
    }

    /**
     * Create the table
     *
     * @access  public
     * @since   1.0
    */
    public function create_table() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$this->table_name}';" ) ) {
            // for backward compatibility
            if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$this->table_name}` LIKE 'term_id';" ) ) {
                $wpdb->query( "ALTER TABLE {$this->table_name} ADD COLUMN `term_id` bigint(20) DEFAULT '0' AFTER `dashboard_id`;" );
            }
        } else {
            $sql = "CREATE TABLE {$this->table_name} (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `dashboard_id` bigint(20) DEFAULT '0',
                `term_id` bigint(20) DEFAULT '0',
                `name` varchar(128) DEFAULT NULL,
                PRIMARY KEY (`id`)

                ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

            dbDelta( $sql );
        }

        update_option( $this->table_name . '_db_version', $this->version );
    }

}
