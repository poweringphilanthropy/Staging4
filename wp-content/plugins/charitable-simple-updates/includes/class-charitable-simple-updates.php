<?php
/**
 * The main Charitable Simple Updates class.
 * 
 * The responsibility of this class is to load all the plugin's functionality.
 *
 * @package     Charitable Simple Updates
 * @copyright   Copyright (c) 2014, Eric Daams  
 * @license     http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Charitable_Simple_Updates' ) ) :

/**
 * Charitable_Simple_Updates
 *
 * @since   1.0.0
 */
class Charitable_Simple_Updates {

    /**
     * @var string
     */
    const VERSION = '1.1.1';

    /**
     * @var string  A date in the format: YYYYMMDD
     */
    const DB_VERSION = '20150717';  

    /**
     * @var Charitable_Simple_Updates
     */
    private static $instance = null;

    /**
     * The root file of the plugin. 
     * 
     * @var     string
     * @access  private
     */
    private $plugin_file; 

    /**
     * The root directory of the plugin.  
     *
     * @var     string
     * @access  private
     */
    private $directory_path;

    /**
     * The root directory of the plugin as a URL.  
     *
     * @var     string
     * @access  private
     */
    private $directory_url;

    /**
     * @var     array       Store of registered objects.  
     * @access  private
     */
    private $registry;

    /**
     * Create class instance. 
     * 
     * @return  void
     * @since   1.0.0
     */
    public function __construct( $plugin_file ) {
        $this->plugin_file      = $plugin_file;
        $this->directory_path   = plugin_dir_path( $plugin_file );
        $this->directory_url    = plugin_dir_url( $plugin_file );

        add_action( 'charitable_start', array( $this, 'start' ), 6 );
    }

    /**
     * Returns the original instance of this class. 
     * 
     * @return  Charitable
     * @since   1.0.0
     */
    public static function get_instance() {
        return self::$instance;
    }

    /**
     * Run the startup sequence on the charitable_start hook. 
     *
     * This is only ever executed once.  
     * 
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function start() {
        // If we've already started (i.e. run this function once before), do not pass go. 
        if ( $this->started() ) {
            return;
        }

        // Set static instance
        self::$instance = $this;

        $this->load_dependencies();

        $this->maybe_start_admin();

        $this->maybe_start_ambassadors();

        $this->attach_hooks_and_filters();

        // Hook in here to do something when the plugin is first loaded.
        do_action('charitable_simple_updates_start', $this);
    }

    /**
     * Include necessary files.
     * 
     * @return  void
     * @access  private
     * @since   1.0.0
     */
    private function load_dependencies() {      
        require_once( $this->get_path( 'includes' ) . 'charitable-simple-updates-core-functions.php' );
        require_once( $this->get_path( 'includes' ) . 'class-charitable-simple-updates-template.php' );
        require_once( $this->get_path( 'includes' ) . 'widgets/class-charitable-simple-updates-widget.php' );        
    }

    /**
     * Set up hook and filter callback functions.
     * 
     * @return  void
     * @access  private
     * @since   1.0.0
     */
    private function attach_hooks_and_filters() {       
        add_action( 'widgets_init', array( $this, 'register_widget' ) );
    }

    /**
     * Load the admin-only functionality. 
     *
     * @return  void
     * @access  private
     * @since   1.0.0
     */
    private function maybe_start_admin() {
        if ( ! is_admin() ) {
            return;
        }

        require_once( $this->get_path( 'includes' ) . 'admin/class-charitable-simple-updates-admin.php' );

        Charitable_Simple_Updates_Admin::start( $this );
    }

    /**
     * Load up the Ambassadors integration if Ambassadors is installed.
     *
     * @return  void
     * @access  private
     * @since   1.0.0
     */
    private function maybe_start_ambassadors() {
        if ( ! class_exists( 'Charitable_Ambassadors' ) ) {
            return;
        }

        require_once( $this->get_path( 'includes' ) . 'ambassadors/class-charitable-simple-updates-campaign-form.php' );

        add_action( 'charitable_simple_updates_start', array( 'Charitable_Simple_Updates_Campaign_Form', 'start' ) );
    }

    /**
     * Registers the Campaign Updates Widget on the widgets_init hook.
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function register_widget() {
        register_widget( 'Charitable_Simple_Updates_Widget' );
    }

    /**
     * Returns whether we are currently in the start phase of the plugin. 
     *
     * @return  bool
     * @access  public
     * @since   1.0.0
     */
    public function is_start() {
        return current_filter() == 'charitable_simple_updates_start';
    }

    /**
     * Returns whether the plugin has already started.
     * 
     * @return  bool
     * @access  public
     * @since   1.0.0
     */
    public function started() {
        return did_action( 'charitable_simple_updates_start' ) || current_filter() == 'charitable_simple_updates_start';
    }

    /**
     * Returns the plugin's version number. 
     *
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function get_version() {
        return self::VERSION;
    }

    /**
     * Returns plugin paths. 
     *
     * @param   string $path            // If empty, returns the path to the plugin.
     * @param   bool $absolute_path     // If true, returns the file system path. If false, returns it as a URL.
     * @return  string
     * @since   1.0.0
     */
    public function get_path($type = '', $absolute_path = true ) {      
        $base = $absolute_path ? $this->directory_path : $this->directory_url;

        switch( $type ) {
            case 'includes' : 
                $path = $base . 'includes/';
                break;

            case 'templates' : 
                $path = $base . 'templates/';
                break;

            case 'directory' : 
                $path = $base;
                break;

            default :
                $path = $this->plugin_file;
        }

        return $path;
    }

    /**
     * Stores an object in the plugin's registry.
     *
     * @param   mixed       $object
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function register_object( $object ) {
        if ( ! is_object( $object ) ) {
            return;
        }

        $class = get_class( $object );

        $this->registry[ $class ] = $object;
    }

    /**
     * Returns a registered object.
     * 
     * @param   string      $class  The type of class you want to retrieve.
     * @return  mixed               The object if its registered. Otherwise false.
     * @access  public
     * @since   1.0.0
     */
    public function get_object( $class ) {
        return isset( $this->registry[ $class ] ) ? $this->registry[ $class ] : false;
    }

    /**
     * Throw error on object clone. 
     *
     * This class is specifically designed to be instantiated once. You can retrieve the instance using charitable()
     *
     * @since   1.0.0
     * @access  public
     * @return  void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'chartiable-simple-updates' ), '1.0.0' );
    }

    /**
     * Disable unserializing of the class. 
     *
     * @since   1.0.0
     * @access  public
     * @return  void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'chartiable-simple-updates' ), '1.0.0' );
    }           
}

endif; // End if class_exists check