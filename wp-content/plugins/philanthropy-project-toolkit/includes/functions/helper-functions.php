<?php
/**
 * Helper functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays a template.
 *
 * @param 	string|string[] $template_name A single template name or an ordered array of template.
 * @param 	mixed[]         $args 	       Optional array of arguments to pass to the view.
 * @return 	PP_Template
 * @since 	1.0.0
 */
function pp_template( $template_name, array $args = array() ) {
	if ( empty( $args ) ) {
		$template = new PP_Template( $template_name );
	} else {
		$template = new PP_Template( $template_name, false );
		$template->set_view_args( $args );
		$template->render();
	}

	return $template;
}

/**
 * Return the template path if the template exists. Otherwise, return default.
 *
 * @param 	string|string[] $template
 * @return  string The template path if the template exists. Otherwise, return default.
 * @since   1.0.0
 */
function pp_get_template_path( $template, $default = '' ) {
	$t = new PP_Template( $template, false );
	$path = $t->locate_template();

	if ( ! file_exists( $path ) ) {
		$path = $default;
	}

	return $path;
}

/**
 * Orders an array by the priority key.
 *
 * @param 	array $a First element.
 * @param 	array $b Element to compare against.
 * @return 	int
 * @since 	1.0.0
 */
function pp_priority_sort( $a, $b ) {
	foreach ( array( $a, $b ) as $item ) {
		if ( ! array_key_exists( 'priority', $item ) ) {
			error_log( 'Priority missing from field: ' . json_encode( $item ) );
		}
	}

	if ( $a['priority'] == $b['priority'] ) {
		return 0;
	}

	return $a['priority'] < $b['priority'] ? -1 : 1;
}