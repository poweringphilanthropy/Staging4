<?php

/**
 * Charitable EDD Connect Core Functions.
 *
 * General core functions.
 *
 * @author 		Studio164a
 * @category 	Core
 * @package 	Charitable EDD
 * @subpackage 	Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * This returns the original Charitable_EDD object.
 *
 * Use this whenever you want to get an instance of the class. There is no
 * reason to instantiate a new object, though you can do so if you're stubborn :)
 *
 * @return 	Charitable_EDD
 * @since 	1.0.0
 */
function charitable_edd() {
	return Charitable_EDD::get_instance();
}

/**
 * Displays a template.
 *
 * @param 	string|array     $template_name  A single template name or an ordered array of template
 * @param 	array            $args           Optional array of arguments to pass to the template.
 * @return 	Charitable_EDD_Template
 * @since 	1.0.0
 */
function charitable_edd_template( $template_name, array $args = array() ) {
	if ( ! class_exists( 'Charitable_EDD_Template' ) ) {
		require_once( charitable_edd()->get_path( 'includes' ) . 'public/class-charitable-edd-template.php' );
	}

	if ( empty( $args ) ) {
		$template = new Charitable_EDD_Template( $template_name );
	} else {
		$template = new Charitable_EDD_Template( $template_name, false );
		$template->set_view_args( $args );
		$template->render();
	}

	return $template;
}
