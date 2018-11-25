<?php
/**
 * Charitable Simple Updates template
 *
 * @version     1.0.0
 * @package     Charitable EDD/Classes/Charitable_Simple_Updates_Template
 * @author      Eric Daams
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Charitable_Simple_Updates_Template' ) ) : 

/**
 * Charitable_Simple_Updates_Template
 *
 * @since       1.0.0
 */
class Charitable_Simple_Updates_Template extends Charitable_Template {
    
    /**
     * Set theme template path. 
     *
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function get_theme_template_path() {
        return trailingslashit( apply_filters( 'charitable_simple_updates_theme_template_path', 'charitable/charitable-simple-updates' ) );
    }

    /**
     * Return the base template path.
     *
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function get_base_template_path() {
        return charitable_simple_updates()->get_path( 'templates' );
    }
}

endif;