<?php
/**
 * Renders the donation details meta box for the Donation post type.
 *
 * @deprecated 1.8.0
 *
 * @author Studio 164a
 * @since  1.5.0
 * @since  1.5.9 Deprecated. views/metaboxes/actions.php is used instead.
 */

if ( ! array_key_exists( 'actions', $view_args ) ) {
	$view_args['actions'] = charitable_get_donation_actions();
}

charitable_admin_view( 'metaboxes/actions', $view_args );
