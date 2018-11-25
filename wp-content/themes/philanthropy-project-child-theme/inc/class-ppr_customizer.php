<?php
/**
 * PPR_Customizer Class.
 *
 * @class       PPR_Customizer
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PPR_Customizer class.
 */
class PPR_Customizer {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PPR_Customizer();
        }

        return $instance;
    }

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_filter( 'reach_customizer_footer_section', array($this, 'footer_section'), 10, 1 );
	}

	public function footer_section($footer_settings){

		$footer_settings['settings']['footer_form_id'] = array(
			'setting'   => array(
				// 'transport' => 'postMessage',
				'default'   => '',
				'sanitize_callback' => 'absint',
			),
			'control'   => array(
				'label'     => __( 'Form ID', 'reach' ),
				'type'      => 'text',
				'priority'  => 10,
			),
		);

		$footer_settings['settings']['footer_image'] = array(
			'setting'   => array(
				'default'       => '',
				// 'transport'     => 'postMessage',
				'sanitize_callback' => 'esc_url_raw',
			),
			'control'   => array(
				'control_type'  => 'Reach_Customizer_Retina_Image_Control',
				'priority'      => 20,
			),
		);

		// echo "<pre>";
		// print_r($footer_settings);
		// echo "</pre>";
		// exit();

		return $footer_settings;
	}

	public function includes(){

	}

}

PPR_Customizer::init();