<?php
/**
 * Philanthropy Project template
 *
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Philanthropy_Template
 *
 * @since       1.0.0
 */
class Philanthropy_Template extends Charitable_Template {      

    /**
     * Return the base template path.
     *
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function get_base_template_path() {
        return Philanthropy()->plugin_path().'/templates/';
    }
}
