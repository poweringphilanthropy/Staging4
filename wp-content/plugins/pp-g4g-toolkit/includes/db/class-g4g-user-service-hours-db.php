<?php
/**
 * G4G_User_Service_Hours_DB Class.
 *
 * @class       G4G_User_Service_Hours_DB
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * G4G_User_Service_Hours_DB base class
 * @since       1.0
*/
 if ( class_exists( 'PP_DB' ) ){
class G4G_User_Service_Hours_DB extends PP_DB  {

    /**
     * The name of the cache group.
     *
     * @access public
     * @since  1.0
     * @var string
     */
    public $cache_group = 'user_service_hours';

    /**
     * Get things started
     *
     * @access  public
     * @since   1.0
     */
    public function __construct() {
    	global $wpdb;

		$this->table_name  = G4G_USER_SERVICE_HOURS_TABLE_NAME;
		$this->primary_key = 'id';
		$this->version     = '1.3';

        add_action( 'init', array($this, 'check_db_version') );
    }

    public function check_db_version(){
        $db_on_option = get_option( $this->table_name . '_db_version', '0.1');
        // version_compare(version1, version2)
        if( version_compare( $db_on_option, $this->version, '<' ) ) {
            $this->create_table();
        }
    }

    /**
     * Whitelist of columns
     *
     * @access  public
     * @since   1.0
     * @return  array
     */
    public function get_columns() {
		return array(
			'id'                 => '%d',
			'user_id'            => '%d',
            'campaign_id'        => '%d',
			'title'              => '%s',
			'description'        => '%s',
            'member_name'        => '%s',
            'member_email'       => '%s',
			'service_hours'      => '%d',
            'service_date'       => '%s',
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
            'id'                 => 0,
            'user_id'            => 0,
            'campaign_id'        => 0,
            'title'              => '',
            'description'        => '',
            'member_name'        => '',
            'member_email'       => '',
            'service_hours'      => 0,
            'service_date'       => '',
        );
    }

    public function get_campaigns_total_service_hours( $campaign_ids ){
        global $wpdb;

        $how_many = count($campaign_ids);
        $placeholders = array_fill(0, $how_many, '%d');
        $format = implode(', ', $placeholders);

        $res = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(service_hours) FROM $this->table_name
            WHERE campaign_id IN($format);", $campaign_ids) );
        
        return $res;
    }

    public function get_report_service_hours( $campaign_ids ){
        global $wpdb;
        
        $how_many = count($campaign_ids);
        $placeholders = array_fill(0, $how_many, '%d');
        $format = implode(', ', $placeholders);

        $res = $wpdb->get_results( $wpdb->prepare( "SELECT meta.meta_value chapter, service.* FROM $this->table_name service
            LEFT JOIN $wpdb->usermeta meta ON (meta.user_id = service.user_id AND meta.meta_key = 'chapter')
            WHERE campaign_id IN($format)
            GROUP BY member_name
            ORDER BY service_hours DESC;", $campaign_ids), ARRAY_A );
        return $res;
    }

    public function get_all_service_hours_by( $column, $row_value ){
        global $wpdb;
        $column = esc_sql( $column );

        $column_formats = $this->get_columns();
        $format = isset($column_formats[$column]) ? $column_formats[$column] : '%s';

        $res = $wpdb->get_results( $wpdb->prepare( "SELECT meta.meta_value chapter, service.* FROM $this->table_name service
            LEFT JOIN $wpdb->usermeta meta ON (meta.user_id = service.user_id AND meta.meta_key = 'chapter')
            WHERE service.$column = $format
            GROUP BY member_name
            ORDER BY service_hours DESC;", $row_value ), ARRAY_A );

        return $res;
    }

    public function get_members_service_hours_from_user_id($user_id){
        global $wpdb;

        $res = $wpdb->get_results( $wpdb->prepare( "SELECT member_name, COUNT(id) qty, SUM(service_hours) amount FROM $this->table_name
            WHERE user_id = %d
            GROUP BY member_name
            ORDER BY amount DESC;", $user_id ), ARRAY_A );

        return $res;
    }

    public function add_members($data){

        if( !isset($data['members']) || empty($data['members']) ){
            return false;
        }

        $_members = explode(PHP_EOL, $data['members']);
        $members = array_values( array_filter( array_map('trim', $_members) ) );
        if(empty($members))
            return false;

        $added = array();

        unset($data['members']);

        $data['member_name'] = '';
        $data['member_email'] = '';
        foreach ($members as $member_name) {
            
            $_data = array_map('trim', explode(',', $member_name));


            $data['member_name'] = isset($_data[0]) ? $_data[0] : '';
            if(isset($_data[1])){
                $data['member_name'] .= ' ' . $_data[1];
            }

            if(isset($_data[2])){
                $data['member_email'] = sanitize_email( $_data[2] );
            }

            $added[] = $this->add($data);

            // $added[] = $data;
        }

        // echo "<pre>";
        // print_r($added);
        // echo "</pre>";

        do_action( 'g4g_on_members_user_service_hours_added', $added, $data );

        return true;
    }

    public function add( $data = array() ) {

        $defaults = array(
            'user_id' => '',
            'member_name' => '',
        );

        $args = wp_parse_args( $data, $defaults );

        if( empty( $args['user_id'] ) || empty($args['member_name']) ) {
            return false;
        }

        $result = parent::insert( $data, 'user_service_hours' );

        if ( $result ) {
            do_action( 'g4g_on_user_service_hours_added', $data );
            $this->set_last_changed();
        }

        return $result;

    }

    public function get_total_hours($ids = array()){
        global $wpdb;
        
        $how_many = count($ids);
        $placeholders = array_fill(0, $how_many, '%d');
        $format = implode(', ', $placeholders);

        $total = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(service_hours) total FROM $this->table_name
                WHERE id IN($format);", $ids) );

        return $total;
    }

    public function get_campaign_total_hours($campaign_id){
        global $wpdb;

        $total = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(service_hours) total FROM $this->table_name
                WHERE campaign_id = %d", $campaign_id) );

        return $total;
    }

    /**
     * Sets the last_changed cache key for customers.
     *
     * @access public
     * @since  1.0
     */
    public function set_last_changed() {
        wp_cache_set( 'last_changed', microtime(), $this->cache_group );
    }

    /**
     * Retrieves the value of the last_changed cache key for customers.
     *
     * @access public
     * @since  1.0
     */
    public function get_last_changed() {
        if ( function_exists( 'wp_cache_get_last_changed' ) ) {
            return wp_cache_get_last_changed( $this->cache_group );
        }

        $last_changed = wp_cache_get( 'last_changed', $this->cache_group );
        if ( ! $last_changed ) {
            $last_changed = microtime();
            wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
        }

        return $last_changed;
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
            if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$this->table_name}` LIKE 'member_email';" ) ) {
                $wpdb->query( "ALTER TABLE {$this->table_name} ADD COLUMN `member_email` varchar(128) DEFAULT NULL AFTER `member_name`;" );
            }
        } else {
            $sql = "CREATE TABLE " . $this->table_name . " (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) DEFAULT '0',
                `campaign_id` bigint(20) DEFAULT '0',
                `title` text,
                `description` text,
                `member_name` varchar(128) DEFAULT NULL,
                `member_email` varchar(128) DEFAULT NULL,
                `service_date` date DEFAULT NULL,
                `service_hours` int(11) DEFAULT '0',
                PRIMARY KEY (`id`)
            ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

            dbDelta( $sql );
        }

        

        update_option( $this->table_name . '_db_version', $this->version );
    }


}}
