<?php 

/**
 * Load a view from the admin/views folder. 
 * 
 * If the view is not found, an Exception will be thrown.
 *
 * Example usage: charitable_edd_admin_view(('metaboxes/cause-metabox');
 *
 * @param 	string 		$view 			The view to display. 
 * @param 	array 		$view_args 		Optional. Arguments to pass through to the view itself
 * @return 	void
 * @since 	1.0.0
 */
function charitable_edd_admin_view( $view, $view_args = array() ) {
	$filename = charitable_edd()->get_path( 'admin' ) . 'views/' . $view . '.php';

	if ( ! is_readable( $filename ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'Passed view (' . $filename . ') not found or is not readable.', 'charitable-edd' ), '1.0.0' );
	}

	ob_start();

	include( $filename );

	ob_end_flush();
}
