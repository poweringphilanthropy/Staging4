<?php
/**
 * The template used to display the user fields.
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Donation Form
 * @since   1.0.0
 * @version 1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$form    = $view_args['form'];
$field   = $view_args['field'];
$fields  = isset( $field['fields'] ) ? $field['fields'] : array();
$classes = array();

if ( $form->should_hide_user_fields() ) {
	$classes[] = 'charitable-hidden';
}

if ( count( $form->get_meta_fields() ) ) {
	$classes[] = 'bordered';
}

$class = empty( $classes ) ? '' : 'class="' . implode( ' ', $classes ) . '"';

if ( empty( $fields ) ) {
	return;
}

if ( isset( $field['legend'] ) ) :
?>
	<div class="charitable-form-header"><?php echo $field['legend']; ?></div>
<?php
endif;

/**
 * Add something before the donor fields.
 *
 * @since 1.0.0
 *
 * @param Charitable_Donation_Form $form The donation form instance.
 */
do_action( 'charitable_donation_form_donor_fields_before', $form );

?>
	<div id="charitable-user-fields" <?php echo $class; ?>>
		<?php $form->view()->render_fields( $fields ); ?>
	</div><!-- #charitable-user-fields -->
<?php

/**
 * Add something after the donor fields.
 *
 * @since 1.0.0
 *
 * @param Charitable_Donation_Form $form The donation form instance.
 */
do_action( 'charitable_donation_form_donor_fields_after', $form );
