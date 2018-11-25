<?php
/**
 * The template used to display error messages.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.5.0
 */

if ( ! array_key_exists( 'errors', $view_args ) || empty( $view_args['errors'] ) ) {
	return;
}

?>
<div class="charitable-form-errors charitable-notice">
	<ul class="errors">
		<?php foreach ( $view_args['errors'] as $error ) : ?>
			<li><?php echo $error ?></li>
		<?php endforeach ?>
	</ul>
</div>
