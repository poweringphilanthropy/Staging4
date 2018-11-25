<?php
/**
 * Display a donation form.
 *
 * This is a pre-checkout form that allows donors/customers to donate an
 * arbitrary amount and/or choose an associated EDD download to purchase. The
 * downloads listed are any that will contribute to the campaign goal (i.e. 
 * there is a beneficiary relationship between the campaign and the download). 
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 */

$campaign = $view_args[ 'campaign' ];
$form = $view_args[ 'form' ];

if ( $campaign->get( 'edd_show_contribution_options' ) ) :

    wp_enqueue_script( 'charitable-edd-donation-page' );
    
endif;
?>
<form id="charitable-donation-form" class="charitable-edd-donation-form" method="post">
	<?php 
    /**
     * @hook    charitable_form_before_fields
     */
    do_action( 'charitable_form_before_fields', $form ) ?>
    
    <div class="charitable-form-fields cf">

    <?php 

    $i = 1;

    foreach ( $form->get_fields() as $key => $field ) :
		$form->view()->render_field( $field, $key, array(
            'index' => $i,
        ) );

        $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form , $i);

        // do_action( 'charitable_form_field', $field, $key, $form, $i );
    
        // $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form, $i );

    endforeach;

    ?>
    
    </div>

    <?php
    /**
     * @hook    charitable_form_after_fields
     */
    do_action( 'charitable_form_after_fields', $form );

    ?>
	<div class="charitable-form-field charitable-submit-field">
		<button class="button button-primary" type="submit" name="donate"><?php _e( 'Donate', 'charitable-edd' ) ?></button>
	</div>
</form>