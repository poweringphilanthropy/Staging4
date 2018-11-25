<?php
/**
 * Plugin Name:     Philanthropy Project - Toolkit
 * Plugin URI:      http://www.poweringphilanthropy.com/
 * Description:     Custom features for The Philanthropy Project
 * Author:          Powering Philanthropy
 * Author URI:      http://www.poweringphilanthropy.com/
 * Author Email:    info@poweringphilanthropy.com
 * Version:         2.0
 * Text Domain:     pp
 * Domain Path:     /languages/ 
 */

if (!defined('ABSPATH')) exit;


/**
 * Philanthropy_Project
 *
 * @since       1.0.0
 */
class Philanthropy_Project {

    /**
     * @var Philanthropy_Project
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
     * Setting these to public until a better function can be built than "get_path()"
     *
     * @var     string
     * @access  private
     */
    public $directory_path;

    /**
     * The root directory of the plugin as a URL.  
     *
     * @var     string
     * @access  private
     */
    public $directory_url;

    /**
	 * @var string
	 */
	public $version = '2.0';

    /**
     * Returns the original instance of this class. 
     * 
     * @return  Charitable
     * @since   1.0.0
     */
    public static function get_instance() {

        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create class instance. 
     * 
     * @return  void
     * @since   1.0.0
     */
    public function __construct() {    

        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        do_action( 'pp_loaded' );
    }

    /**
     * Hook into actions and filters
     * @since  2.3
     */
    private function init_hooks() {

        register_activation_hook( __FILE__, array( $this, 'install' ) );
        register_uninstall_hook( __FILE__, 'uninstall' );

        add_action( 'init', array($this, 'register_scripts') );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'charitable_start', array( $this, 'start' ), 10 );

        /**
         * Load our campaign class on philanthropy_project_start
         */
        add_action( 'philanthropy_project_start', array( 'PP_Charitable_Campaign_Form', 'start' ) );
    }

    /**
     * Define Philanthropy_Project Constants
     */
    private function define_constants() {   

        /**
         * Legacy
         * @var [type]
         */
        $this->plugin_file      = __FILE__;
        $this->directory_path   = plugin_dir_path( __FILE__ ) . 'legacy/';
        $this->directory_url    = plugin_dir_url( __FILE__ ) . 'legacy/';

        $this->define( 'PP_PLUGIN_FILE', __FILE__ );
        $this->define( 'PP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        $this->define( 'PP_VERSION', $this->version );
        $this->define( 'EP_LEADERBOARD', 8388608 ); // 8388608 = 2^23, https://code.tutsplus.com/articles/the-rewrite-api-post-types-taxonomies--wp-25488
    }

    /**
     * Start the plugin functionality. 
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function start() {

        /* If we've already started (i.e. run this function once before), do not pass go. */
        if ( $this->started() ) {
            return;
        }

        /* Set static instance */
        self::$instance = $this;

        /* Hook in here to do something when the plugin is first loaded */
        do_action('philanthropy_project_start', $this);
    }

    /**
     * Returns whether we are currently in the start phase of the plugin. 
     *
     * @return  bool
     * @access  public
     * @since   1.0.0
     */
    public function is_start() {
        return current_filter() == 'philanthropy_project_start';
    }

    /**
     * Returns whether the plugin has already started.
     * 
     * @return  bool
     * @access  public
     * @since   1.0.0
     */
    public function started() {
        return did_action( 'philanthropy_project_start' ) || current_filter() == 'philanthropy_project_start';
    }

    /**
     * All install stuff
     * @return [type] [description]
     */
    public function install() {
        
        do_action( 'on_pp_toolkit_install' );
    }

    /**
     * All uninstall stuff
     * @return [type] [description]
     */
    public function uninstall() {

        do_action( 'on_pp_toolkit_uninstall' );
    }

    /**
     * Include necessary files.
     * 
     * @return  void
     * @access  private
     * @since   1.0.0
     */
    private function includes() {
        
        /**
         * DEPENDENCIES
         */
        if(file_exists( __DIR__ . '/vendor/autoload.php') ){
            require __DIR__ . '/vendor/autoload.php';
        }

        // autoload dependencies
        include_once( 'dependencies/wp-router/wp-router.php' );

        // abstracts
        include_once( 'includes/abstracts/abstract-pp-db.php' );

        // load functions
        include_once( 'includes/class-pp-core-functions.php' );

        // dbs

        // data
        // include_once( 'includes/data/class-pp-dashboard-data.php' ); // @todo | remove
        include_once( 'includes/data/class-pp-leaderboard-data.php' );

        // helpers
        include_once( 'includes/helpers/class-pp-importer.php' );
        include_once( 'includes/helpers/class-pp-template.php' );

        // modules
        include_once( 'includes/class-pp-cpt.php' );
        include_once( 'includes/class-pp-account.php' );
        include_once( 'includes/class-pp-campaign.php' );
        include_once( 'includes/class-pp-leaderboard.php' );
        include_once( 'includes/class-pp-chapters.php' );
        include_once( 'includes/class-pp-exports.php');

        /**
         * ================
         * TODO | Filter dependencies below and move to the new version of plugin
         */
        /**
         * Main PP Toolkit Functions
         */
        include_once( $this->directory_path . 'includes/functions-pp-toolkit.php' );

        // Bootstrap Campaigns
        include_once( $this->directory_path . 'includes/class-pp-charitable-campaign-form.php');

        /**
         * Helper Classes
         */
        include_once( $this->directory_path . 'helpers/cuztom/cuztom.php');
        include_once( $this->directory_path . 'helpers/class-rename-wp-login.php');
        include_once( $this->directory_path . 'helpers/class-aq_resizer.php');
        // include_once( $this->directory_path . 'helpers/class-pp-charitable-leaderboard.php');

        /**
         * Reports
         */
        include_once( $this->directory_path . 'includes/reports/class-pp-campaign-reports.php');

        /**
         * Query
         */
        include_once( $this->directory_path . 'query/class-pp-fundraisers-query.php');
        include_once( $this->directory_path . 'query/class-pp-merchandise-donations-query.php');
        include_once( $this->directory_path . 'query/class-pp-ticket-donations-query.php');

        /**
         * Plugin modifications
         */
        include_once( $this->directory_path . 'plugin-modifications/class-pp-charitable.php');
        include_once( $this->directory_path . 'plugin-modifications/class-pp-charitable-ambassadors.php');
        include_once( $this->directory_path . 'plugin-modifications/class-pp-charitable-edd.php');
        include_once( $this->directory_path . 'plugin-modifications/class-pp-edd.php');
        include_once( $this->directory_path . 'plugin-modifications/class-pp-edd-stripe.php');
        include_once( $this->directory_path . 'plugin-modifications/class-pp-ninja-form.php');

        /**
         * Our classes
         */
        if(is_admin()){
            include_once( $this->directory_path . 'includes/class-pp-admin.php');
        }

        include_once( $this->directory_path . 'includes/class-pp-templates.php');
        include_once( $this->directory_path . 'includes/class-pp-embed-media.php');
        include_once( $this->directory_path . 'includes/class-pp-frontend.php');
        include_once( $this->directory_path . 'includes/class-pp-shortcodes.php');
        include_once( $this->directory_path . 'includes/class-pp-widgets.php');
        include_once( $this->directory_path . 'includes/class-pp-reports.php');
        include_once( $this->directory_path . 'includes/class-pp-users.php');
        include_once( $this->directory_path . 'includes/class-pp-emails.php');
        // include_once( $this->directory_path . 'includes/class-pp-chapters.php');
        // include_once( $this->directory_path . 'includes/class-pp-leaderboard.php');
        include_once( $this->directory_path . 'includes/class-pp-team-fundraising.php');
        include_once( $this->directory_path . 'includes/class-pp-rest-api.php');
    }

    public function register_scripts(){

        /**
         * NEW SCRIPTS
         */
        wp_register_script( 'autocomplete', $this->plugin_url() . '/assets/js/jquery.easy-autocomplete.js', array(), '1.3.5', true );
        wp_register_script( 'popup', $this->plugin_url() . '/assets/js/jquery.popup.overlay.js', array(), '1.7.13', true );
        
        wp_register_style( 'pp', $this->plugin_url() . '/assets/css/pp.css', false, $this->version );
        wp_register_script( 'pp', $this->plugin_url() . '/assets/js/pp.js', array('jquery', 'popup', 'jquery-ui-datepicker'), $this->version, true );
        
        /* admin */
        wp_register_style( 'pp-toolkit-admin', $this->directory_url . 'assets/css/pp-toolkit-admin.css' );
        wp_register_script( 'pp-toolkit-admin', $this->directory_url . 'assets/js/pp-toolkit-admin.js', array('jquery'), $this->version, true );

        wp_register_script( 'pp-registration-handler', $this->directory_url.'assets/js/registration-handler.js', array( 'jquery-ui-datepicker' ), '1.0.0', true );


        /* This script is registered here, but enqueued by the form class to ensure that it's only loaded when we're looking at the form. */
        wp_register_script( 'cropit', $this->directory_url . 'assets/js/jquery.cropit.js', array('jquery'), $this->version, true );
        wp_register_script( 'smartWizard', $this->directory_url . 'assets/js/jquery.smartWizard.js', array('jquery'), $this->version, true );

        wp_register_style( 'select2', $this->directory_url . 'assets/css/select2.css', array(), '4.0.5' );
        wp_register_script( 'select2', $this->directory_url . 'assets/js/select2.min.js', array('jquery'), '4.0.5', true );
        
        wp_register_style( 'selectize', $this->directory_url . 'assets/css/selectize.css', array(), '0.12.4' );
        wp_register_script( 'selectize', $this->directory_url . 'assets/js/selectize.min.js', array('jquery'), '0.12.4', true );
        
        wp_register_script( 'sweetalert', $this->directory_url . 'assets/js/sweetalert.min.js', array(), $this->version, true );
        wp_register_script( 'handlebars', $this->directory_url . 'assets/js/handlebars-v4.0.11.js', array(), '4.0.11', true );
       
        wp_register_style( 'pp-toolkit-campaign-submission', $this->directory_url . 'assets/css/pp-toolkit-campaign-submission.css', array('selectize'), $this->version );
        wp_register_script( 'pp-toolkit-campaign-submission', $this->directory_url . 'assets/js/pp-toolkit-campaign-submission.js', array('jquery', 'smartWizard', 'jquery-ui-autocomplete', 'jquery-ui-datepicker', 'selectize'), $this->version, true );

        wp_register_script( 'philanthropy-modal', $this->directory_url . 'assets/js/philanthropy-modal.js', array('jquery'), $this->version, true );

        wp_register_style( 'pp-toolkit', $this->directory_url.'assets/css/pp-toolkit.css', false, '1.0' );
        wp_register_script( 'pp-toolkit', $this->directory_url . 'assets/js/pp-toolkit.js', array('jquery', 'jquery-ui-autocomplete'), $this->version, true );

        wp_register_script( 'pp-reports', $this->directory_url . 'assets/js/pp-reports.js', array('jquery', 'handlebars'), $this->version, true );

        /* For Volunteer, but should extend to all modals eventually */
        // wp_register_style( 'animate', '//cdnjs.cloudflare.com/ajax/libs/animate.css/3.2.0/animate.min.css', false, '3.2.0');
        // wp_register_script( 'pt-animatedModal', $this->directory_url.'assets/js/animatedModal.min.js', false, '1.0');
        // wp_register_script('font-awesome', 'https://use.fontawesome.com/4897c4c6a4.js', false, false);
    
        // dashboard embed related
        wp_register_script( 'iframeResizer', $this->directory_url . 'assets/js/iframe-resizer.min.js', array(), $this->version, true );
    }

    /**
     * Load custom scripts.  
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function enqueue_scripts() {

        /**
         * NEW SCRIPTS
         */
        wp_enqueue_style( 'pp' );
        wp_enqueue_script( 'pp' );
        wp_localize_script( 'pp', 'PP_OBJ', apply_filters( 'pp-localize-data', array(
            'ajax_url' => $this->ajax_url(),
            'currect_user_id' => get_current_user_id(),
        ) ) );



        wp_enqueue_style( 'pp-toolkit' );
        wp_enqueue_script( 'pp-toolkit' );
        // wp_enqueue_style('animate');
        // wp_enqueue_script('pt-animatedModal');
        // wp_enqueue_script('font-awesome');
        
        wp_enqueue_script( 'sweetalert' );

        // TODO | Check if dashboard widget page
        if(charitable_is_page( 'leaderboard_widget' )){
            wp_enqueue_script( 'iframeResizer' );
        }

        if ( charitable_is_page( 'registration_page' ) ) {
            wp_enqueue_script( 'pp-registration-handler' );
        }

        if ( charitable_is_page( 'campaign_submission_page' ) ) {

            wp_enqueue_script( 'charitable-script' );
            wp_enqueue_script( 'charitable-plup-fields' );
            wp_enqueue_style( 'charitable-plup-styles' );

            wp_enqueue_style( 'pp-toolkit-campaign-submission' );
            wp_enqueue_script( 'pp-toolkit-campaign-submission' );

            ob_start();

            pp_toolkit_template( 'form-fields/donation-levels-row.php', array(
                'index'       => '{index}',
                'key'         => 'suggested_donations',
                'amount'      => '',
                'description' => '',
            ) );

            $row = ob_get_clean();

            ob_start();

            pp_toolkit_template( 'form-fields/sponsors-row.php', array(
                'index'       => '{index}',
                'key'         => 'sponsors',
                'value'      => '',
            ) );

            $sponsors_row = ob_get_clean();

            $vars = array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'donation_levels_row' => $row,
                'sponsors_row' => $sponsors_row,
            );

            wp_localize_script(
                'pp-toolkit-campaign-submission',
                'PP_CAMPAIGN_SUBMISSION',
                $vars
            );
            
        }
    }

    /**
     * Define constant if not already set
     * @param  string $name
     * @param  string|bool $value
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Get the plugin url.
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Get the plugin path.
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Get image url from assets dir
     * @param  string $name [description]
     * @return [type]       [description]
     */
    public function get_image_url( $name = '' ){
        $url = $this->plugin_url() . '/assets/images/' . $name;
        return apply_filters( 'pp_image_url', $url, $name );
    }

    /**
     * Get Ajax URL.
     * @return string
     */
    public function ajax_url() {
        return admin_url( 'admin-ajax.php', 'relative' );
    }

} // end class


/**
 * Notice if dependencies not activated
 * @return [type] [description]
 */
function pp_toolkit_need_deps(){
	?>
    <div class="updated">
        <p><?php _e('Please activate required plugins', 'pp-toolkit'); ?></p>
    </div>
    <?php
}

/**
 * Returns the main instance of PP_Toolkit to prevent the need to use globals.
 *
 * @since  1.0
 * @return PP_Toolkit
 */
function PP() {

	$needed = array(
		'easy-digital-downloads/easy-digital-downloads.php',
		'charitable/charitable.php',
		'charitable-ambassadors/charitable-ambassadors.php',
		'charitable-edd/charitable-edd.php',
		'the-events-calendar/the-events-calendar.php',
		'event-tickets/event-tickets.php',
		'event-tickets-plus/event-tickets-plus.php',
	);

	$activated = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

	$pass = count(array_intersect($needed, $activated)) == count($needed);

	// var_dump($pass);
	// echo "<pre>";
	// print_r($activated);
	// echo "</pre>";
	// exit();

	if ( $pass ) {
		return Philanthropy_Project::get_instance();
	} else {
		add_action( 'admin_notices', 'pp_toolkit_need_deps' );
	}
}

// for legacy
function pp_toolkit(){
    return PP();
}

PP();