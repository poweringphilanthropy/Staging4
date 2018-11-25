<?php
/**
 * The template used to display the suggested amounts field.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$form    = $view_args['form'];
$field   = $view_args['field'];
$classes = $view_args['classes'];

if ( ! isset( $field['content'] ) ) {
	return;
}
?>
<p class="<?php echo $classes; ?>">
	<?php echo $field['content']; ?>
</p>
