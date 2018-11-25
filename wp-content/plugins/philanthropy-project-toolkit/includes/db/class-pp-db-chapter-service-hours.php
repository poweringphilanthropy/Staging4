<?php
/**
 * PP_DB_Chapter_Service_Hours Class.
 *
 * @class       PP_DB_Chapter_Service_Hours
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
class PP_DB_Chapter_Service_Hours extends PP_DB  {

    /**
     * Get things started
     *
     * @access  public
     * @since   1.0
     */
    public function __construct() {
        global $wpdb;

		$this->table_name  = $wpdb->prefix . 'chapter_service_hours';
		$this->primary_key = 'id';
		$this->version     = '1.0';

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
            'chapter_id'    => '%d',
            'first_name'    => '%s',
            'last_name'     => '%s',
            'description'   => '%s',
            'parent'        => '%d',
            'service_hours' => '%d',
            'service_date'  => '%s',
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
            'chapter_id'    => 0,
            'first_name'    => '',
            'last_name'     => '',
            'description'   => '',
            'parent'        => 0,
            'service_hours' => 0,
            'service_date'  => '',
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
        $wpdb->chapter_service_hours = $this->table_name;
    }

    public function insert( $data, $type = '' ) {
        $result = parent::insert( $data, $type );

        if($result){
            do_action( 'pp_on_insert_chapter_service_hours', $data );
        }

        return $result;
    }

    /**
     * Create the table
     *
     * @access  public
     * @since   1.0
    */
    public function create_table() {

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE {$this->table_name} (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `chapter_id` bigint(20) DEFAULT '0',
            `first_name` varchar(128) DEFAULT NULL,
            `last_name` varchar(128) DEFAULT NULL,
            `description` text,
            `parent` int(11) DEFAULT '0',
            `service_hours` int(11) DEFAULT '0',
            `service_date` date DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta( $sql );

        update_option( $this->table_name . '_db_version', $this->version );
    }

}
