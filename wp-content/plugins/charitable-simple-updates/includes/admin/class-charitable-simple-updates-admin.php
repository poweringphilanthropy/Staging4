<?php
/**
 * Class responsible for adding Charitable EDD settings in admin area.
 *
 * @package     Charitable EDD/Classes/Charitable_Simple_Updates_Admin
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Charitable_Simple_Updates_Admin' ) ) : 

/**
 * Charitable_Simple_Updates_Admin
 *
 * @since       1.0.0
 */
class Charitable_Simple_Updates_Admin {

    /**
     * Instantiate the class, but only during the start phase.
     * 
     * @param   Charitable_Simple_Updates $charitable_su
     * @return  void
     * @static 
     * @access  public
     * @since   1.0.0
     */
    public static function start( Charitable_Simple_Updates $charitable_su ) {
        if ( $charitable_su->started() ) {
            return;
        }

        new Charitable_Simple_Updates_Admin();
    }

    /**
     * Set up the class. 
     * 
     * Note that the only way to instantiate an object is with the charitable_start method, 
     * which can only be called during the start phase. In other words, don't try 
     * to instantiate this object. 
     *
     * @access  private
     * @since   1.0.0
     */
    private function __construct() {
        $this->attach_hooks_and_filters();
    }

    /**
     * Load required files. 
     *
     * @return  void
     * @access  private
     * @since   1.0.0
     */
    private function load_dependencies() {
        
    }

    /**
     * Set up hooks and filters. 
     *
     * @return  void
     * @access  private
     * @since   1.0.0
     */
    private function attach_hooks_and_filters() {
        add_filter( 'charitable_campaign_meta_boxes', array( $this, 'register_campaign_updates_meta_box' ) );        
        add_filter( 'charitable_admin_view_path', array( $this, 'admin_view_path' ), 10, 3 );
        add_filter( 'charitable_campaign_meta_keys', array( $this, 'save_campaign_updates' ) );
    }

    /**
     * Register campaign updates section in campaign metabox.
     *
     * @param   array[] $meta_boxes
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function register_campaign_updates_meta_box( $meta_boxes ) {
        $meta_boxes[] = array(
            'id'                => 'campaign-updates', 
            'title'             => __( 'Updates', 'charitable-simple-updates' ), 
            'context'           => 'campaign-advanced', 
            'priority'          => 'high', 
            'view'              => 'metaboxes/campaign-updates', 
            'view_source'       => 'charitable-simple-updates'
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
        if ( isset( $view_args[ 'view_source' ] ) && 'charitable-simple-updates' == $view_args[ 'view_source' ] ) {

            $path = charitable_simple_updates()->get_path( 'includes' ) . 'admin/views/' . $view . '.php';

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
    public function save_campaign_updates( $meta_keys ) {
        $meta_keys[] = '_campaign_updates';
        return $meta_keys;
    }
}

endif; // End class_exists check