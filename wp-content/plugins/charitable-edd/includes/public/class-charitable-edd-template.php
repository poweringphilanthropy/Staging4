<?php
/**
 * Charitable EDD template
 *
 * @version		1.0.0
 * @package		Charitable EDD/Classes/Charitable_EDD_Template
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_EDD_Template' ) ) : 

    /**
     * Charitable_EDD_Template
     *
     * @since 		1.0.0
     */
    class Charitable_EDD_Template extends Charitable_Template {
    	
    	/**
         * Set theme template path. 
         *
         * @return  string
         * @access  public
         * @since   1.0.0
         */
        public function get_theme_template_path() {
            return trailingslashit( apply_filters( 'charitable_edd_theme_template_path', 'charitable/charitable-edd' ) );
        }

        /**
         * Return the base template path.
         *
         * @return  string
         * @access  public
         * @since   1.0.0
         */
        public function get_base_template_path() {
            return charitable_edd()->get_path( 'templates' );
        }
    }

endif;
