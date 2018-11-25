<?php
/**
 * Display a donation form.
 *
 * This is a pre-checkout form that allows donors/customers to donate an
 * arbitrary amount and/or choose an associated EDD download to purchase. The
 * downloads listed are any that will contribute to the campaign goal (i.e.
 * there is a beneficiary relationship between the campaign and the download).
 *
 * @author  Studio 164a
 * @since   1.1.3
 * @version 1.1.3
 */

$form = $view_args['form'];
$i    = 1;

foreach ( $form->get_fields() as $key => $field ) :

    do_action( 'charitable_form_field', $field, $key, $form, $i );

    $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form, $i );

endforeach;