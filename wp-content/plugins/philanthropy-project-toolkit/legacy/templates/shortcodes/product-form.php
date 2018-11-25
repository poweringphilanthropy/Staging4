<?php
/**
 * The template used to display the product submission form.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$form   = $view_args[ 'form' ];

/**
 * @hook    pp_product_submission_before
 */
do_action('pp_product_submission_before');

?>
<form method="post" id="pp-product-submission-form" class="charitable-form" enctype="multipart/form-data">
    <?php 
    /**
     * @hook    charitable_form_before_fields
     */ 
    do_action( 'charitable_form_before_fields', $form ) ?>
    
    <div class="charitable-form-fields cf">
    <?php 
	// $form->view()->render_notices();
	// $form->view()->render_honeypot();
	// $form->view()->render_hidden_fields();
    $i = 1;

    foreach ( $form->get_fields() as $key => $field ) :
	
		$form->view()->render_field( $field, $key, array(
            'index' => $i,
        ) );

        $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form , $i);

        // do_action( 'charitable_form_field', $field, $key, $form, $i );

        // $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form );

    endforeach;

    ?>    
    </div>
    <div class="charitable-form-field charitable-submit-field">
        <input class="button button-primary" type="submit" name="save-product" value="<?php esc_attr_e( 'Save Product', 'pp-toolkit' ) ?>" />
    </div>
</form>
<?php

/**
 * @hook    pp_product_submission_after
 */
do_action('pp_product_submission_after');