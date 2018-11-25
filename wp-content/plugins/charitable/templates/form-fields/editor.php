<?php
/**
 * The template used to display the WP Editor in a form.
 *
 * @author 	Studio 164a
 * @package Charitable/Templates/Form Fields
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$form 			= $view_args['form'];
$field 			= $view_args['field'];
$classes 		= $view_args['classes'];
$is_required 	= isset( $field['required'] ) ? $field['required'] : false;
$value			= isset( $field['value'] ) ? $field['value'] : '';
$editor_args 	= isset( $field['editor'] ) ? wpautop( $field['editor'] ) : array();

/**
 * Change the editor settings.
 *
 * @see   https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
 *
 * @since 1.5.0
 *
 * @param array $settings The default settings.
 */
$default_editor_args = array(
	'media_buttons' => true,
	'teeny'         => true,
	'quicktags'     => false,
	'tinymce'       => array(
		'theme_advanced_path'     => false,
		'theme_advanced_buttons1' => 'bold,italic,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink',
	),
);

$editor_args = wp_parse_args( $editor_args, $default_editor_args );
?>
<div id="charitable_field_<?php echo $field['key'] ?>" class="<?php echo $classes ?>">
	<?php if ( isset( $field['label'] ) ) : ?>
		<label for="<?php echo esc_attr( $field['key'] ) ?>">
			<?php echo $field['label'] ?>
			<?php if ( $is_required ) : ?>
				<abbr class="required" title="required">*</abbr>
			<?php endif ?>
		</label>
	<?php endif ?>
	<?php
		wp_editor( $value, $field['key'], $editor_args );
	?>
</div>
