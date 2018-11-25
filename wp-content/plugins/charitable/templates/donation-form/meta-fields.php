<?php
/**
 * The template used to display the user fields.
 *
 * @author 	Studio 164a
 * @package Charitable/Templates/Donation Form
 * @since   1.0.0
 * @version 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$form   = $view_args['form'];
$field  = $view_args['field'];
$fields = isset( $field['fields'] ) ? $field['fields'] : array();

if ( empty( $fields ) ) {
	return;
}

?>
<div id="charitable-meta-fields">
	<?php if ( isset( $field['legend'] ) ) : ?>
		<div class="charitable-form-header"><?php echo $field['legend']; ?></div>
	<?php endif; ?>
	<?php $form->view()->render_fields( $fields ); ?>
</div><!-- #charitable-meta-fields -->
