<?php
/**
 * The template used to display the donor's current details.
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Donation Form
 * @since   1.0.0
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var Charitable_User
 */
$user = $view_args['user'];

if ( ! $user && ! is_customize_preview() ) {
	return;
}

?>
<div class="charitable-donor-details">
	<address class="donor-address"><?php echo $user->get_address(); ?></address>
	<p class="donor-contact-details">
		<?php
		/* translators: %s: email address */
		printf( __( 'Email: %s', 'charitable ' ), $user->user_email );

		if ( $user->__isset( 'donor_phone' ) ) :
			/* translators: %s: phone number */
			echo '<br />' . sprintf( __( 'Phone number: %s', 'charitable' ), $user->get( 'donor_phone' ) );
		endif;
		?>
	</p>
	<p class="charitable-change-user-details">
		<a href="#" data-charitable-toggle="charitable-user-fields"><?php _e( 'Update your details', 'charitable' ); ?></a>
	</p><!-- .charitable-change-user-details -->
</div><!-- .charitable-donor-details -->
