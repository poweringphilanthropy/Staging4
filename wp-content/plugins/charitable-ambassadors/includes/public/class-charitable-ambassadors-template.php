<?php
/**
 * Charitable Ambassadors template
 *
 * @version		1.0.0
 * @package		Charitable Ambassadors/Classes/Charitable_Ambassadors_Template
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Ambassadors_Template' ) ) : 

/**
 * Charitable_Ambassadors_Template
 *
 * @since 		1.0.0
 */
class Charitable_Ambassadors_Template extends Charitable_Template {
	
	/**
     * Set theme template path. 
     *
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function get_theme_template_path() {
        return trailingslashit( apply_filters( 'charitable_ambassadors_theme_template_path', 'charitable/charitable-ambassadors' ) );
    }

    /**
     * Return the base template path.
     *
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function get_base_template_path() {
        return charitable_ambassadors()->get_path( 'templates' );
    }
}

endif;