<?php
/**
 * The template used to display the payout fields.
 *
 * @author  Studio 164a 
 * @since   1.0.0
 * @version 1.0.0
 */


if ( ! isset( $view_args[ 'form' ] ) ) {
    return;
}

$form       = $view_args[ 'form' ];
$fields 	= $view_args[ 'fields' ];
?>
<div class="payout-form test">
    <?php 

    // echo "<pre>";
    // print_r($form);
    // echo "</pre>";

    $i = 1;
// $form->view()->render_fields( $fields );
	// $form->view()->render_notices();
	// $form->view()->render_honeypot();
	// $form->view()->render_hidden_fields();
	foreach ( $fields as $key => $fieldset_field ) :
		 $form->view()->render_field( $fieldset_field, $key, array(
            'index' => $i,
        ) );

        $i += apply_filters( 'charitable_form_field_increment', 1, $fieldset_field, $key, $form , $i);
		
		// do_action( 'charitable_form_field', $fieldset_field, $key, $form, $i );

		// $i += apply_filters( 'charitable_form_field_increment', 1, $fieldset_field, $key, $form );

	endforeach;

    ?>
</div>