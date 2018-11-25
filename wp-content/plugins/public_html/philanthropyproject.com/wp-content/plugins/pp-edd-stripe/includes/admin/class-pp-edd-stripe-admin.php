<?php
/**
 * PP_EDD_Stripe_Admin Class.
 *
 * @class       PP_EDD_Stripe_Admin
 * @version		1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PP_EDD_Stripe_Admin class.
 */
class PP_EDD_Stripe_Admin {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_EDD_Stripe_Admin();
        }

        return $instance;
    }

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		
		add_action( 'admin_enqueue_scripts', array($this, 'load_admin_scripts') );

		add_filter( 'charitable_campaign_meta_boxes', array($this, 'add_metaboxes'), 10, 1 );
		add_filter( 'charitable_campaign_meta_keys', array( $this, 'save_campaign_metabox' ) );
        add_filter( 'charitable_admin_view_path', array( $this, 'admin_view_path' ), 10, 3 );
	}



    public function add_metaboxes($meta_boxes){

        $meta_boxes[] = array(
            'id'                => 'campaign-payout-options',
            'title'             => __( 'Payout Options', 'charitable-ambassadors' ),
            'context'           => 'campaign-advanced',
            'priority'          => 'high',
            'view'              => 'metaboxes/campaign-payout-options',
            'view_source'       => 'pp-edd-stripe',
        );

        return $meta_boxes;
    }

	/**
	 * Save campaign payout.
	 *
	 * @param   string[] $meta_keys
	 * @return  string[]
	 * @access  public
	 * @since   1.0.0
	 */
	public function save_campaign_metabox( $meta_keys ) {
		$meta_keys[] = '_campaign_payout_destination';
		$meta_keys[] = '_campaign_connected_stripe_id';
		$meta_keys[] = '_campaign_platform_fee';
		return $meta_keys;
	}

    /**
     * Set the admin view path to our views folder for any of our views.
     *
     * @param   string $path
     * @param   string $view
     * @param   array  $view_args
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function admin_view_path( $path, $view, $view_args ) {
        if ( ! isset( $view_args['view_source'] ) ) {
            return $path;
        }

        if ( 'pp-edd-stripe' == $view_args['view_source'] ) {
            $path = EDDS_PLUGIN_DIR . '/includes/admin/views/' . $view . '.php';
        }

        return $path;
    }

	public function load_admin_scripts(){
		wp_register_script( 'pp-edd-admin', EDDSTRIPE_PLUGIN_URL . 'assets/js/pp-edd-admin.js', array( 'jquery' ), EDD_STRIPE_VERSION, true );
	}

	public function includes(){

	}

}

PP_EDD_Stripe_Admin::init();